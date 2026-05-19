<?php
/**
 * Enrichissement TMDB d’un seul film.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Moncine\CatalogAdmin;
use Moncine\Csrf;
use Moncine\FilmEnricher;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /films.php');
    exit;
}

CatalogAdmin::denyUnlessAccess();

$filmId = (int) ($_POST['film_id'] ?? 0);
$action = (string) ($_POST['action'] ?? 'enrich');

if ($filmId <= 0) {
    header('Location: /films.php');
    exit;
}

$return = (string) ($_POST['return'] ?? 'film');
$failUrl = $return === 'resultat'
    ? '/resultat.php?film_id=' . $filmId
    : '/film.php?id=' . $filmId;
Csrf::rejectUnlessValid($_POST, $failUrl);

$enricher = new FilmEnricher();

if ($action === 'tmdb') {
    $result = $enricher->correctWithTmdbId($filmId, (string) ($_POST['tmdb_id'] ?? ''));
} else {
    $result = $enricher->enrichOne($filmId);
}

$status = $result['ok'] ? 'ok' : ($result['not_found'] ? 'not_found' : 'error');
$params = http_build_query([
    'enrich' => $status,
    'enrich_msg' => $result['message'],
]);

if ($return === 'resultat') {
    header('Location: /resultat.php?film_id=' . $filmId . '&' . $params);
    exit;
}

header('Location: /film.php?id=' . $filmId . '&' . $params);
exit;
