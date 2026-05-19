<?php
/**
 * Liste des films « Mes envies » (wishlist).
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Moncine\Csrf;
use Moncine\FilmRepository;
use Moncine\LibraryStatut;
use Moncine\SupportPhysique;
use Moncine\View;

if (!(new FilmRepository())->usesCatalogModel()) {
    header('Location: /films.php');
    exit;
}

$sortBy = (string) ($_GET['sort'] ?? $_POST['sort'] ?? 'titre');
$sortDir = (string) ($_GET['dir'] ?? $_POST['dir'] ?? 'asc');
$query = trim((string) ($_GET['q'] ?? $_POST['q'] ?? ''));

$repo = new FilmRepository();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectUrl = View::wishlistUrl($query, $sortBy, $sortDir);
    Csrf::rejectUnlessValid($_POST, $redirectUrl);

    $filmId = (int) ($_POST['film_id'] ?? 0);
    $supportRaw = (string) ($_POST['support_physique'] ?? '');
    $supportKey = SupportPhysique::normalize($supportRaw);

    if ($filmId <= 0) {
        header('Location: ' . $redirectUrl . '?promote_error=' . rawurlencode('Film invalide.'));
        exit;
    }

    if (!$repo->promoteToCollection($filmId, $supportKey)) {
        header('Location: ' . $redirectUrl . '?promote_error=' . rawurlencode('Impossible d’ajouter ce film à vos films.'));
        exit;
    }

    $film = $repo->findById($filmId);
    $titre = $film !== null ? (string) ($film['titre'] ?? '') : '';
    $params = ['promoted' => '1'];
    if ($titre !== '') {
        $params['promoted_title'] = $titre;
    }
    header('Location: /films.php?' . http_build_query($params));
    exit;
}

$films = $repo->findAllWishlist($sortBy, $sortDir, $query);
$totalCount = $repo->countWishlist();

View::render('souhaits', [
    'pageTitle' => LibraryStatut::label(LibraryStatut::WISHLIST),
    'films' => $films,
    'sortBy' => $sortBy,
    'sortDir' => $sortDir,
    'query' => $query,
    'searched' => $query !== '',
    'totalCount' => $totalCount,
]);
