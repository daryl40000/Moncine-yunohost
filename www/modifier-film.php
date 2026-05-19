<?php
/**
 * Enregistre les modifications manuelles d’une fiche film.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Moncine\Csrf;
use Moncine\FilmManualEdit;
use Moncine\FilmRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /films.php');
    exit;
}

$filmId = (int) ($_POST['film_id'] ?? 0);
if ($filmId <= 0) {
    header('Location: /films.php');
    exit;
}

Csrf::rejectUnlessValid($_POST, '/film.php?id=' . $filmId . '&edit=1');

$parsed = FilmManualEdit::parseExemplaireFromPost($_POST);
if (!$parsed['ok']) {
    $params = http_build_query([
        'save_error' => $parsed['error'],
        'edit' => '1',
    ]);
    header('Location: /film.php?id=' . $filmId . '&' . $params);
    exit;
}

$result = (new FilmRepository())->updateManual($filmId, $parsed['data']);
if ($result !== true) {
    $params = http_build_query([
        'save_error' => (string) $result,
        'edit' => '1',
    ]);
    header('Location: /film.php?id=' . $filmId . '&' . $params);
    exit;
}

header('Location: /film.php?id=' . $filmId . '&saved=1');
exit;
