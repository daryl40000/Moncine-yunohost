<?php
/**
 * Historique des films déjà vus.
 */

declare(strict_types=1);

namespace Moncine;

use PDO;

final class HistoriqueRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function markVu(int $filmId, ?int $note = null): void
    {
        $this->recordViewing($filmId, date('Y-m-d'), $note);
    }

    /** Date du jour au format jj/mm/aaaa (affichage). */
    public static function todayForInput(): string
    {
        return date('d/m/Y');
    }

    /** Date du jour au format aaaa-mm-jj (champ HTML date). */
    public static function todayForInputIso(): string
    {
        return date('Y-m-d');
    }

    /**
     * Convertit une date saisie en aaaa-mm-jj (SQLite).
     * Formats : aaaa-mm-jj, jj/mm/aaaa, jj-mm-aaaa, jj.mm.aaaa.
     */
    public static function parseVueDate(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $formats = ['Y-m-d', 'd/m/Y', 'd/m/y', 'd-m-Y', 'd-m-y', 'd.m.Y', 'd.m.y'];
        foreach ($formats as $format) {
            $dt = \DateTimeImmutable::createFromFormat('!' . $format, $raw);
            if ($dt === false) {
                continue;
            }
            $errors = \DateTimeImmutable::getLastErrors();
            if ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
                continue;
            }

            return $dt->format('Y-m-d');
        }

        return null;
    }

    /**
     * Valide une date saisie. Vide = aujourd’hui.
     *
     * @return array{ok: true, date: string}|array{ok: false, error: string}
     */
    public static function parseDateVueInput(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return ['ok' => true, 'date' => date('Y-m-d')];
        }

        $iso = self::parseVueDate($raw);
        if ($iso === null) {
            return [
                'ok' => false,
                'error' => 'Date invalide. Utilisez le calendrier ou le format jj/mm/aaaa (ex. 16/05/2024).',
            ];
        }

        if ($iso > date('Y-m-d')) {
            return [
                'ok' => false,
                'error' => 'La date de vision ne peut pas être dans le futur.',
            ];
        }

        return ['ok' => true, 'date' => $iso];
    }

    /**
     * Note sur 10 (optionnelle). Vide = pas de note.
     *
     * @return array{ok: true, note: ?int}|array{ok: false, error: string}
     */
    public static function parseNoteInput(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return ['ok' => true, 'note' => null];
        }

        if (!is_numeric($raw)) {
            return [
                'ok' => false,
                'error' => 'Note invalide. Choisissez un nombre de 1 à 10.',
            ];
        }

        $note = ImportCsv::parseNote($raw);
        if ($note === null) {
            return [
                'ok' => false,
                'error' => 'La note doit être entre 1 et 10.',
            ];
        }

        return ['ok' => true, 'note' => $note];
    }

    /**
     * Enregistre une vision (import CSV ou bouton « vu ce soir »).
     * Met à jour la note si la même date existe déjà.
     */
    public function recordViewing(int $filmId, string $dateVue, ?int $note = null): bool
    {
        if (!$this->libraryEntryExists($filmId)) {
            throw new \RuntimeException('Cette fiche est introuvable dans votre bibliothèque.');
        }

        $check = $this->db->prepare(
            'SELECT id FROM historique WHERE film_id = ? AND date_vue = ?'
        );
        $check->execute([$filmId, $dateVue]);
        $existingId = $check->fetchColumn();

        if ($existingId !== false) {
            if ($note !== null) {
                $upd = $this->db->prepare(
                    'UPDATE historique SET note = ? WHERE id = ?'
                );
                $upd->execute([$note, $existingId]);
            }
            return false;
        }

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO historique (film_id, date_vue, note) VALUES (?, ?, ?)'
            );
            $stmt->execute([$filmId, $dateVue, $note]);
        } catch (PDOException $e) {
            if ($this->isForeignKeyFailure($e)) {
                HistoriqueSchema::repairForeignKeyIfNeeded($this->db);
                $stmt = $this->db->prepare(
                    'INSERT INTO historique (film_id, date_vue, note) VALUES (?, ?, ?)'
                );
                $stmt->execute([$filmId, $dateVue, $note]);
            } else {
                throw $e;
            }
        }

        return true;
    }

    private function libraryEntryExists(int $filmId): bool
    {
        if ($filmId <= 0) {
            return false;
        }

        if (CatalogSchema::usesCatalogTables($this->db)) {
            $stmt = $this->db->prepare(
                'SELECT 1 FROM bibliotheque WHERE id = ? AND user_id = ? LIMIT 1'
            );
            $stmt->execute([$filmId, UserContext::currentUserId()]);

            return (bool) $stmt->fetchColumn();
        }

        $stmt = $this->db->prepare('SELECT 1 FROM films WHERE id = ? LIMIT 1');
        $stmt->execute([$filmId]);

        return (bool) $stmt->fetchColumn();
    }

    private function isForeignKeyFailure(PDOException $e): bool
    {
        return str_contains($e->getMessage(), 'FOREIGN KEY')
            || str_contains($e->getMessage(), 'foreign key');
    }

    public function wasEverSeen(int $filmId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM historique WHERE film_id = ? LIMIT 1');
        $stmt->execute([$filmId]);
        return (bool) $stmt->fetchColumn();
    }

    /** @return list<array{id: int, date_vue: string, note: ?int}> Toutes les visions d’un film. */
    public function findViewingsByFilm(int $filmId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, date_vue, note FROM historique
             WHERE film_id = ?
             ORDER BY date_vue DESC, id DESC'
        );
        $stmt->execute([$filmId]);

        return $stmt->fetchAll();
    }

    /** Supprime une entrée d’historique (vérifie qu’elle appartient au film). */
    public function deleteViewing(int $historiqueId, int $filmId): bool
    {
        if ($historiqueId <= 0 || $filmId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            'DELETE FROM historique WHERE id = ? AND film_id = ?'
        );
        $stmt->execute([$historiqueId, $filmId]);

        return $stmt->rowCount() > 0;
    }

    /** Vision la plus récente (date la plus élevée en base). */
    public function getLastViewing(int $filmId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT date_vue, note FROM historique
             WHERE film_id = ?
             ORDER BY date_vue DESC, id DESC
             LIMIT 1'
        );
        $stmt->execute([$filmId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /** Meilleure note enregistrée pour ce film (1 à 10), ou null si jamais noté. */
    public function getNoteSur10(int $filmId): ?int
    {
        $stmt = $this->db->prepare(
            'SELECT MAX(note) FROM historique
             WHERE film_id = ? AND note IS NOT NULL AND note >= 1'
        );
        $stmt->execute([$filmId]);
        $note = $stmt->fetchColumn();
        if ($note === false || $note === null) {
            return null;
        }
        $n = (int) $note;

        return $n >= 1 ? min(10, $n) : null;
    }

    /** Affichage « 8/10 » pour la fiche film. */
    public static function formatNoteSur10(?int $note): string
    {
        if ($note === null || $note < 1) {
            return '';
        }

        return min(10, $note) . '/10';
    }

    /** Affiche une date de vision en jj-mm-aaaa (ex. 16-05-2026). */
    public static function formatDateVue(?string $date): string
    {
        if ($date === null || trim($date) === '') {
            return '';
        }
        $date = trim($date);

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $date, $m)) {
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})/', $date, $m)) {
            return sprintf('%02d-%02d-%04d', (int) $m[1], (int) $m[2], (int) $m[3]);
        }

        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})/', $date, $m)) {
            return sprintf('%02d-%02d-%04d', (int) $m[1], (int) $m[2], (int) $m[3]);
        }

        return $date;
    }

    /** @return list<array<string, mixed>> Toutes les visions, avec titre du film. */
    public function findAllWithFilmTitles(): array
    {
        if (CatalogSchema::usesCatalogTables($this->db)) {
            $stmt = $this->db->prepare(
                'SELECT h.id, h.film_id, b.oeuvre_id, o.titre, o.realisateur, h.date_vue, h.note
                 FROM historique h
                 INNER JOIN bibliotheque b ON b.id = h.film_id
                 INNER JOIN oeuvres o ON o.id = b.oeuvre_id
                 WHERE b.user_id = ?
                 ORDER BY h.date_vue DESC, o.titre COLLATE FRENCH_NOCASE'
            );
            $stmt->execute([UserContext::currentUserId()]);

            return $stmt->fetchAll();
        }

        $stmt = $this->db->query(
            'SELECT h.id, h.film_id, f.titre, f.realisateur, h.date_vue, h.note
             FROM historique h
             INNER JOIN films f ON f.id = h.film_id
             ORDER BY h.date_vue DESC, f.titre COLLATE FRENCH_NOCASE'
        );

        return $stmt->fetchAll();
    }

    /** Nombre de jours depuis la dernière vision (null si jamais vu). */
    public function daysSinceLastView(int $filmId): ?int
    {
        $stmt = $this->db->prepare(
            'SELECT CAST(julianday("now") - julianday(MAX(date_vue)) AS INTEGER) FROM historique WHERE film_id = ?'
        );
        $stmt->execute([$filmId]);
        $days = $stmt->fetchColumn();
        return $days !== false ? (int) $days : null;
    }
}
