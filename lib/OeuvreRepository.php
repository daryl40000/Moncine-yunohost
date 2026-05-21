<?php
/**
 * Catalogue d’œuvres Moncine (métadonnées partagées, indépendantes de TMDB à terme).
 */

declare(strict_types=1);

namespace Moncine;

use PDO;

final class OeuvreRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        $stmt = $this->db->prepare('SELECT * FROM oeuvres WHERE id = ?');
        $stmt->execute([$id]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findByTitreAndRealisateur(string $titre, string $realisateur): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM oeuvres WHERE titre = ? AND realisateur = ?'
        );
        $stmt->execute([$titre, $realisateur]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Recherche dans le catalogue par début de titre (autocomplétion).
     *
     * @return list<array<string, mixed>>
     */
    public function searchByTitrePrefix(string $query, int $limit = 20): array
    {
        $query = trim($query);
        if (mb_strlen($query) < 2) {
            return [];
        }

        $limit = max(1, min(30, $limit));
        $pattern = LikePattern::containsFragment($query);
        $stmt = $this->db->prepare(
            'SELECT * FROM oeuvres
             WHERE LOWER(titre) LIKE LOWER(?) ESCAPE \'\\\'
             ORDER BY titre COLLATE FRENCH_NOCASE, realisateur COLLATE FRENCH_NOCASE
             LIMIT ' . $limit
        );
        $stmt->execute([$pattern]);

        return $stmt->fetchAll() ?: [];
    }

    public function deleteById(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        $stmt = $this->db->prepare('DELETE FROM oeuvres WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    public function countBibliothequeLinks(int $oeuvreId): int
    {
        if ($oeuvreId <= 0) {
            return 0;
        }

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM bibliotheque WHERE oeuvre_id = ?');
        $stmt->execute([$oeuvreId]);

        return (int) $stmt->fetchColumn();
    }

    /** Vide le catalogue (supprime aussi les entrées bibliothèque liées — CASCADE). */
    public function deleteAll(): void
    {
        $this->db->exec('DELETE FROM oeuvres');
        $this->syncAutoincrementSequence();
    }

    public function findByTmdbId(int $tmdbId): ?array
    {
        if ($tmdbId <= 0) {
            return null;
        }
        $stmt = $this->db->prepare('SELECT * FROM oeuvres WHERE tmdb_id = ? LIMIT 1');
        $stmt->execute([$tmdbId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @param array<string, mixed> $payload champs oeuvres
     */
    public function insert(array $payload): int
    {
        $fields = CatalogSchema::OEUVRE_FIELDS;
        $columns = implode(', ', $fields);
        $placeholders = implode(', ', array_map(static fn (string $f): string => ':' . $f, $fields));
        $stmt = $this->db->prepare("INSERT INTO oeuvres ($columns) VALUES ($placeholders)");
        $stmt->execute($this->filterPayload($payload, $fields));
        $id = (int) $this->db->lastInsertId();
        $this->touchUpdated($id);

        return $id;
    }

    /**
     * Crée une œuvre avec un ID catalogue imposé (import / migration depuis une autre instance).
     *
     * @param array<string, mixed> $payload
     */
    public function insertWithId(int $id, array $payload): void
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID catalogue invalide.');
        }

        if ($this->findById($id) !== null) {
            throw new \RuntimeException('ID catalogue ' . $id . ' déjà utilisé.');
        }

        $fields = array_merge(['id'], CatalogSchema::OEUVRE_FIELDS);
        $columns = implode(', ', $fields);
        $placeholders = implode(', ', array_map(static fn (string $f): string => ':' . $f, $fields));
        $params = $this->filterPayload($payload, CatalogSchema::OEUVRE_FIELDS);
        $params['id'] = $id;

        $stmt = $this->db->prepare("INSERT INTO oeuvres ($columns) VALUES ($placeholders)");
        $stmt->execute($params);
        $this->touchUpdated($id);
    }

    /** Réaligne le compteur AUTOINCREMENT SQLite après des insertions avec ID explicite. */
    public function syncAutoincrementSequence(): void
    {
        $max = (int) $this->db->query('SELECT COALESCE(MAX(id), 0) FROM oeuvres')->fetchColumn();
        $this->db->exec(
            "INSERT OR REPLACE INTO sqlite_sequence (name, seq) VALUES ('oeuvres', " . max(0, $max) . ')'
        );
    }

    /**
     * @param array<string, mixed> $payload
     * @param list<string> $onlyFields
     */
    public function update(int $id, array $payload, array $onlyFields = []): void
    {
        if ($id <= 0) {
            return;
        }
        $fields = $onlyFields !== [] ? $onlyFields : CatalogSchema::OEUVRE_FIELDS;
        $sets = [];
        foreach ($fields as $field) {
            if (in_array($field, CatalogSchema::OEUVRE_FIELDS, true)) {
                $sets[] = $field . ' = :' . $field;
            }
        }
        if ($sets === []) {
            return;
        }
        $params = $this->filterPayload($payload, $fields);
        $params['id'] = $id;
        $stmt = $this->db->prepare(
            'UPDATE oeuvres SET ' . implode(', ', $sets) . ', updated_at = datetime(\'now\') WHERE id = :id'
        );
        $stmt->execute($params);
    }

    private function touchUpdated(int $id): void
    {
        $this->db->prepare('UPDATE oeuvres SET updated_at = datetime(\'now\') WHERE id = ?')->execute([$id]);
    }

    /**
     * @param array<string, mixed> $payload
     * @param list<string> $fields
     * @return array<string, mixed>
     */
    private function filterPayload(array $payload, array $fields): array
    {
        $out = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $payload)) {
                $out[$field] = $payload[$field];
            }
        }

        return $out;
    }

    /**
     * Toutes les œuvres du catalogue (export admin).
     *
     * @return list<array<string, mixed>>
     */
    public function findAllForExport(): array
    {
        $stmt = $this->db->query(
            'SELECT * FROM oeuvres ORDER BY titre COLLATE FRENCH_NOCASE, realisateur COLLATE FRENCH_NOCASE'
        );

        return $stmt->fetchAll() ?: [];
    }

}
