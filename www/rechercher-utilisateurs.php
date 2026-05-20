<?php
/**
 * Recherche d’utilisateurs par pseudo et ville (comptes visibles uniquement).
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Moncine\Auth;
use Moncine\UserProfile;
use Moncine\UtilisateurRepository;
use Moncine\View;

$userId = Auth::currentUserId();
if ($userId <= 0) {
    header('Location: /connexion.php');
    exit;
}

$pseudoQuery = trim((string) ($_GET['pseudo'] ?? ''));
$villeQuery = trim((string) ($_GET['ville'] ?? ''));
$searched = $pseudoQuery !== '' || $villeQuery !== '';

$results = [];
if ($searched) {
    $results = (new UtilisateurRepository())->searchDiscoverableUsers($pseudoQuery, $villeQuery, $userId);
}

View::render('rechercher_utilisateurs', [
    'pageTitle' => 'Rechercher des utilisateurs',
    'pseudoQuery' => $pseudoQuery,
    'villeQuery' => $villeQuery,
    'searched' => $searched,
    'results' => $results,
]);
