<?php
/**
 * Export PDF de la collection ou des envies (phase 10, Dompdf).
 */

declare(strict_types=1);

namespace Moncine;

use Dompdf\Dompdf;
use Dompdf\Options;
use RuntimeException;

final class ExportPdf
{
    /** Limite de lignes pour éviter les PDF trop lourds. */
    public const MAX_ROWS = 400;

    public static function isAvailable(): bool
    {
        return class_exists(Dompdf::class);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchRows(string $scope, string $sortBy, string $sortDir, string $searchQuery, string $kindFilter): array
    {
        $repo = new FilmRepository();
        $scope = ExportPdfScope::normalize($scope);

        if ($scope === ExportPdfScope::WISHLIST) {
            if (!$repo->usesCatalogModel()) {
                return [];
            }

            return $repo->findAllWishlist($sortBy, $sortDir, $searchQuery);
        }

        return $repo->findAll($sortBy, $sortDir, $searchQuery, $kindFilter);
    }

    /**
     * @param list<array<string, mixed>> $films
     */
    public function renderBinary(
        string $scope,
        array $films,
        string $sortBy,
        string $sortDir,
        string $searchQuery,
        string $kindFilter,
        bool $includePosters
    ): string {
        if (!self::isAvailable()) {
            throw new RuntimeException(
                'Dompdf n’est pas installé. Exécutez « composer install » sur le serveur.'
            );
        }

        $total = count($films);
        if ($total > self::MAX_ROWS) {
            $films = array_slice($films, 0, self::MAX_ROWS);
        }

        $html = $this->renderHtml($scope, $films, $sortBy, $sortDir, $searchQuery, $kindFilter, $includePosters, $total);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->setChroot([MONCINE_ROOT, MONCINE_WWW]);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * @param list<array<string, mixed>> $films
     */
    public function sendDownload(
        string $scope,
        array $films,
        string $sortBy,
        string $sortDir,
        string $searchQuery,
        string $kindFilter,
        bool $includePosters
    ): void {
        $binary = $this->renderBinary($scope, $films, $sortBy, $sortDir, $searchQuery, $kindFilter, $includePosters);
        $filename = ExportSpreadsheet::buildFilename(
            $scope === ExportPdfScope::WISHLIST ? 'moncine-envies' : 'moncine-films',
            'pdf'
        );

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . (string) strlen($binary));
        header('Cache-Control: no-store');

        echo $binary;
    }

    /**
     * @param list<array<string, mixed>> $films
     */
    private function renderHtml(
        string $scope,
        array $films,
        string $sortBy,
        string $sortDir,
        string $searchQuery,
        string $kindFilter,
        bool $includePosters,
        int $totalBeforeLimit
    ): string {
        $scope = ExportPdfScope::normalize($scope);
        $title = $scope === ExportPdfScope::WISHLIST
            ? LibraryStatut::label(LibraryStatut::WISHLIST)
            : 'Mes films';

        $rows = [];
        foreach ($films as $film) {
            $rows[] = $this->rowForTemplate($film, $includePosters);
        }

        $subtitleParts = $this->filterSummaryParts($scope, $sortBy, $sortDir, $searchQuery, $kindFilter);
        $truncated = $totalBeforeLimit > count($films);
        $showPosterColumn = false;
        if ($includePosters) {
            foreach ($rows as $row) {
                if (($row['poster_src'] ?? '') !== '') {
                    $showPosterColumn = true;
                    break;
                }
            }
        }

        ob_start();
        require MONCINE_ROOT . '/templates/pdf/library-list.php';

        return (string) ob_get_clean();
    }

    /**
     * @param array<string, mixed> $film
     * @return array{
     *   titre: string,
     *   meta: string,
     *   support: string,
     *   styles: string,
     *   poster_src: string
     * }
     */
    private function rowForTemplate(array $film, bool $includePosters): array
    {
        $titre = trim((string) ($film['titre'] ?? ''));
        $annee = (int) ($film['annee'] ?? 0);
        $realisateur = trim((string) ($film['realisateur'] ?? ''));
        $duree = (int) ($film['duree_min'] ?? 0);

        $metaParts = [];
        if ($annee > 0) {
            $metaParts[] = (string) $annee;
        }
        if ($realisateur !== '') {
            $metaParts[] = $realisateur;
        }
        if ($duree > 0) {
            $metaParts[] = $duree . ' min';
        }

        $posterSrc = '';
        if ($includePosters) {
            $posterSrc = $this->localPosterPathForPdf((string) ($film['poster_url'] ?? ''));
        }

        return [
            'titre' => $titre !== '' ? $titre : 'Sans titre',
            'meta' => implode(' · ', $metaParts),
            'support' => SupportPhysique::label((string) ($film['support_physique'] ?? '')),
            'styles' => trim((string) ($film['styles'] ?? '')),
            'poster_src' => $posterSrc,
        ];
    }

    private function localPosterPathForPdf(string $posterUrl): string
    {
        $path = PosterStorage::filesystemPathFromWeb($posterUrl);
        if ($path === null || !is_file($path)) {
            return '';
        }

        return $path;
    }

    /**
     * @return list<string>
     */
    private function filterSummaryParts(
        string $scope,
        string $sortBy,
        string $sortDir,
        string $searchQuery,
        string $kindFilter
    ): array {
        $parts = [];

        $searchQuery = trim($searchQuery);
        if ($searchQuery !== '') {
            $parts[] = 'Recherche : « ' . $searchQuery . ' »';
        }

        if ($scope === ExportPdfScope::COLLECTION) {
            $kindFilter = ContentKindFilter::normalize($kindFilter);
            if ($kindFilter !== ContentKindFilter::ALL) {
                $choices = ContentKindFilter::choices();
                $parts[] = 'Type : ' . ($choices[$kindFilter] ?? $kindFilter);
            }
        }

        $sortLabel = $this->sortColumnLabel($sortBy);
        $dirLabel = strtolower($sortDir) === 'desc' ? 'décroissant' : 'croissant';
        $parts[] = 'Tri : ' . $sortLabel . ' (' . $dirLabel . ')';

        return $parts;
    }

    private function sortColumnLabel(string $sortBy): string
    {
        return match ($sortBy) {
            'annee' => 'Année',
            'realisateur' => 'Réalisateur',
            'duree_min' => 'Durée',
            'styles' => 'Style',
            'support_physique' => 'Support',
            'note' => 'Note',
            'saga' => 'Saga',
            default => 'Titre',
        };
    }
}
