<?php
/**
 * Bibliothèque personnelle : lien utilisateur ↔ œuvre (collection ou wishlist).
 */

declare(strict_types=1);

namespace Moncine;

use PDO;

final class BibliothequeRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id, int $userId): ?array
    {
        if ($id <= 0) {
            return null;
        }
        $stmt = $this->db->prepare(
            'SELECT ' . CatalogSchema::selectFilmRow() . '
             FROM ' . CatalogSchema::JOIN . '
             WHERE b.id = ? AND b.user_id = ?'
        );
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findByOeuvreId(int $oeuvreId, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM bibliotheque WHERE oeuvre_id = ? AND user_id = ? LIMIT 1'
        );
        $stmt->execute([$oeuvreId, $userId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @param array<string, mixed> $libraryData support_physique, format_*, saga, saga_ordre, statut
     */
    public function insert(int $userId, int $oeuvreId, array $libraryData): int
    {
        $statut = LibraryStatut::normalize((string) ($libraryData['statut'] ?? LibraryStatut::COLLECTION));
        $stmt = $this->db->prepare(
            'INSERT INTO bibliotheque (
                user_id, oeuvre_id, statut, support_physique, format_image, format_son,
                saga, saga_ordre, saison_numero, saison_label, ean
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $oeuvreId,
            $statut,
            SupportPhysique::normalize((string) ($libraryData['support_physique'] ?? '')),
            trim((string) ($libraryData['format_image'] ?? '')),
            trim((string) ($libraryData['format_son'] ?? '')),
            trim((string) ($libraryData['saga'] ?? '')),
            trim((string) ($libraryData['saga'] ?? '')) === ''
                ? 0
                : max(0, (int) ($libraryData['saga_ordre'] ?? 0)),
            max(0, (int) ($libraryData['saison_numero'] ?? 0)),
            trim((string) ($libraryData['saison_label'] ?? '')),
            trim((string) ($libraryData['ean'] ?? '')),
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * @param array<string, mixed> $libraryData
     */
    public function update(int $id, array $libraryData): void
    {
        if ($id <= 0) {
            return;
        }
        $sets = [];
        $params = ['id' => $id];
        foreach ([
            'support_physique',
            'format_image',
            'format_son',
            'saga',
            'saga_ordre',
            'statut',
            'saison_numero',
            'saison_label',
            'ean',
        ] as $field) {
            if (array_key_exists($field, $libraryData)) {
                $sets[] = $field . ' = :' . $field;
                $params[$field] = $libraryData[$field];
            }
        }
        if ($sets === []) {
            return;
        }
        $stmt = $this->db->prepare(
            'UPDATE bibliotheque SET ' . implode(', ', $sets) . ' WHERE id = :id'
        );
        $stmt->execute($params);
    }

    public function promoteToCollection(int $id, int $userId, string $supportKey = ''): bool
    {
        $item = $this->findById($id, $userId);
        if ($item === null || ($item['statut'] ?? '') === LibraryStatut::COLLECTION) {
            return false;
        }
        $data = ['statut' => LibraryStatut::COLLECTION];
        if ($supportKey !== '') {
            $data['support_physique'] = SupportPhysique::normalize($supportKey);
        }
        $this->update($id, $data);

        return true;
    }

    public function deleteById(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM bibliotheque WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);

        return $stmt->rowCount() > 0;
    }

    public function countByStatut(int $userId, string $statut): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM bibliotheque WHERE user_id = ? AND statut = ?'
        );
        $stmt->execute([$userId, $statut]);

        return (int) $stmt->fetchColumn();
    }
}
