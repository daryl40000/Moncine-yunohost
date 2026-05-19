<?php
/**
 * Enregistre une vision (aujourd’hui ou date passée).
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Moncine\Csrf;
use Moncine\HistoriqueRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

$filmId = (int) ($_POST['film_id'] ?? 0);
$return = (string) ($_POST['return'] ?? '');
$dateRaw = (string) ($_POST['date_vue'] ?? '');
$noteRaw = (string) ($_POST['note'] ?? '');

if ($filmId <= 0) {
    header('Location: /');
    exit;
}

$redirectError = static function (string $message) use ($return, $filmId): void {
    if ($return === 'film') {
        header('Location: /film.php?id=' . $filmId . '&vu_error=' . rawurlencode($message));
        exit;
    }
    if ($return === 'resultat') {
        header('Location: /resultat.php?film_id=' . $filmId . '&vu_error=' . rawurlencode($message));
        exit;
    }
    header('Location: /?vu_error=' . rawurlencode($message));
    exit;
};

if (!Csrf::validateFromPost($_POST)) {
    $redirectError(Csrf::REJECT_MESSAGE);
}

$parsedDate = HistoriqueRepository::parseDateVueInput($dateRaw);
if (!$parsedDate['ok']) {
    $redirectError($parsedDate['error']);
}

$parsedNote = HistoriqueRepository::parseNoteInput($noteRaw);
if (!$parsedNote['ok']) {
    $redirectError($parsedNote['error']);
}

$historique = new HistoriqueRepository();
$dateIso = $parsedDate['date'];
try {
    $historique->recordViewing($filmId, $dateIso, $parsedNote['note']);
} catch (\Throwable $e) {
    $message = $e->getMessage();
    if ($message === '' || str_contains($message, 'SQLSTATE')) {
        $message = 'Impossible d’enregistrer la vision. Rechargez la page et réessayez.';
    }
    $redirectError($message);
}

$dateDisplay = HistoriqueRepository::formatDateVue($dateIso);

$params = [
    'vu' => '1',
    'vu_date' => $dateDisplay,
];
if ($parsedNote['note'] !== null) {
    $params['vu_note'] = (string) $parsedNote['note'];
}

if ($return === 'film') {
    header('Location: /film.php?id=' . $filmId . '&' . http_build_query($params));
    exit;
}

if ($return === 'resultat') {
    header('Location: /resultat.php?film_id=' . $filmId . '&' . http_build_query($params));
    exit;
}

header('Location: /?vu=1');
