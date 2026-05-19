<?php
/**
 * Fiche détaillée d’un film de la collection.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Moncine\FilmRepository;
use Moncine\HistoriqueRepository;
use Moncine\TmdbConfig;
use Moncine\UserContext;
use Moncine\View;

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: /films.php');
    exit;
}

$repo = new FilmRepository();
$film = $repo->findById($id);
if ($film === null) {
    http_response_code(404);
    View::render('film', [
        'pageTitle' => 'Film introuvable',
        'film' => null,
        'derniereVue' => null,
    ]);
    exit;
}

$historique = new HistoriqueRepository();
$lastViewing = $historique->getLastViewing($id);
$derniereVue = $lastViewing['date_vue'] ?? null;
$noteSur10 = $historique->getNoteSur10($id);
$noteFoyerMoyenne = $historique->getFoyerAverageNote($id);
$viewings = $historique->findViewingsByFilm($id);

$enrichStatus = null;
$enrichMessage = '';
if (isset($_GET['enrich'])) {
    $enrichStatus = match ((string) $_GET['enrich']) {
        'ok' => 'ok',
        'not_found' => 'not_found',
        default => 'error',
    };
    $enrichMessage = (string) ($_GET['enrich_msg'] ?? '');
    $refreshed = $repo->findById($id);
    if ($refreshed !== null) {
        $film = $refreshed;
    }
}

$saveError = (string) ($_GET['save_error'] ?? '');
$editOpen = isset($_GET['edit']) || $saveError !== '';

View::render('film', [
    'pageTitle' => (string) $film['titre'],
    'film' => $film,
    'derniereVue' => $derniereVue,
    'noteSur10' => $noteSur10,
    'noteFoyerMoyenne' => $noteFoyerMoyenne,
    'viewings' => $viewings,
    'saved' => isset($_GET['saved']),
    'saveError' => $saveError,
    'editOpen' => $editOpen,
    'everSeen' => $historique->wasEverSeen($id),
    'hasTmdbKey' => TmdbConfig::hasApiKey(),
    'enrichStatus' => $enrichStatus,
    'enrichMessage' => $enrichMessage,
    'returnPage' => 'film',
    'currentTmdbId' => (int) ($film['tmdb_id'] ?? 0),
    'currentTmdbMediaType' => (string) ($film['tmdb_media_type'] ?? ''),
    'currentTmdbTvKind' => (string) ($film['tmdb_tv_kind'] ?? ''),
    'filmId' => $id,
    'sagaSuggestions' => $repo->distinctSagas(),
    'canManageCatalog' => UserContext::canManageCatalog(),
    'showTmdbEnrich' => UserContext::canManageCatalog(),
]);
