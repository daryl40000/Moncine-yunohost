<?php
/**
 * Schéma catalogue Moncine : œuvres (catalogue) + bibliothèque (relation utilisateur).
 */

declare(strict_types=1);

namespace Moncine;

final class CatalogSchema
{
    /** Champs stockés dans la table bibliotheque (exemplaire personnel). */
    public const LIBRARY_FIELDS = [
        'support_physique',
        'format_image',
        'format_son',
        'saga',
        'saga_ordre',
        'saison_numero',
        'saison_label',
        'ean',
    ];

    /** Champs stockés dans la table oeuvres (catalogue partagé). */
    public const OEUVRE_FIELDS = [
        'titre',
        'titre_original',
        'realisateur',
        'duree_min',
        'styles',
        'annee',
        'nationalite',
        'tmdb_id',
        'tmdb_media_type',
        'tmdb_tv_kind',
        'realisateur_tmdb_id',
        'acteur_1',
        'acteur_1_tmdb_id',
        'acteur_2',
        'acteur_2_tmdb_id',
        'acteur_3',
        'acteur_3_tmdb_id',
        'poster_url',
        'synopsis',
        'moncine_kind',
        'omdb_imdb_id',
        'omdb_enriched_at',
    ];

    public const JOIN = 'bibliotheque b INNER JOIN oeuvres o ON o.id = b.oeuvre_id';

    public static function selectFilmRow(): string
    {
        $oeuvre = [];
        foreach (self::OEUVRE_FIELDS as $field) {
            $oeuvre[] = 'o.' . $field;
        }

        return 'b.id, b.user_id, b.oeuvre_id, b.statut, b.support_physique, b.format_image, b.format_son, '
            . 'b.saga, b.saga_ordre, b.saison_numero, b.saison_label, b.ean, b.created_at, '
            . implode(', ', $oeuvre);
    }

    /**
     * @return array{0: string, 1: array<string, int|string>}
     */
    public static function userFilter(int $userId, ?string $statut): array
    {
        $where = ['b.user_id = :catalog_user_id'];
        $params = ['catalog_user_id' => $userId];
        if ($statut !== null) {
            $where[] = 'b.statut = :catalog_statut';
            $params['catalog_statut'] = $statut;
        }

        return [implode(' AND ', $where), $params];
    }

    public static function usesCatalogTables(\PDO $db): bool
    {
        $stmt = $db->query(
            "SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = 'bibliotheque' LIMIT 1"
        );

        return (bool) $stmt->fetchColumn();
    }
}
