<?php
/**
 * Téléchargement export PDF (collection ou envies) — phase 10.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Moncine\ContentKindFilter;
use Moncine\Csrf;
use Moncine\ExportPdf;
use Moncine\ExportPdfScope;
use Moncine\FilmRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /films.php');
    exit;
}

$returnUrl = trim((string) ($_POST['return'] ?? '/films.php'));
if ($returnUrl === '' || !str_starts_with($returnUrl, '/')) {
    $returnUrl = '/films.php';
}

Csrf::rejectUnlessValid($_POST, $returnUrl);

$scope = ExportPdfScope::normalize((string) ($_POST['scope'] ?? ''));
$sortBy = (string) ($_POST['sort'] ?? 'titre');
$sortDir = (string) ($_POST['dir'] ?? 'asc');
$query = trim((string) ($_POST['q'] ?? ''));
$kindFilter = ContentKindFilter::normalize((string) ($_POST['kind'] ?? ''));
$includePosters = !isset($_POST['include_posters']) || (string) $_POST['include_posters'] !== '0';

$redirectError = static function (string $code) use ($returnUrl): never {
    $sep = str_contains($returnUrl, '?') ? '&' : '?';
    header('Location: ' . $returnUrl . $sep . 'pdf_error=' . rawurlencode($code));
    exit;
};

if (!ExportPdf::isAvailable()) {
    $redirectError('dompdf');
}

if ($scope === ExportPdfScope::WISHLIST && !(new FilmRepository())->usesCatalogModel()) {
    $redirectError('wishlist');
}

$exporter = new ExportPdf();
$films = $exporter->fetchRows($scope, $sortBy, $sortDir, $query, $kindFilter);

if ($films === []) {
    $redirectError('empty');
}

try {
    $exporter->sendDownload($scope, $films, $sortBy, $sortDir, $query, $kindFilter, $includePosters);
} catch (\Throwable) {
    $redirectError('failed');
}

exit;
