<?php
/**
 * Supprime un film de la collection (fiche film).
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Moncine\Csrf;
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

Csrf::rejectUnlessValid($_POST, '/film.php?id=' . $filmId);

$repo = new FilmRepository();
$film = $repo->findById($filmId);
if ($film === null) {
    header('Location: /films.php?bulk_error=' . rawurlencode('Film introuvable ou déjà supprimé.'));
    exit;
}

$titre = (string) ($film['titre']);
if (!$repo->deleteById($filmId)) {
    header('Location: /film.php?id=' . $filmId . '&delete_error=' . rawurlencode('Impossible de supprimer ce film.'));
    exit;
}

header('Location: /films.php?deleted=1&deleted_title=' . rawurlencode($titre));
exit;
