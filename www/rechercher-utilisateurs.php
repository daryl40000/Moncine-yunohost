<?php
/**
 * Recherche d’utilisateurs par pseudo et ville (comptes visibles uniquement).
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Moncine\Auth;
use Moncine\FriendshipRepository;
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
$relations = [];
if ($searched) {
    $results = (new UtilisateurRepository())->searchDiscoverableUsers($pseudoQuery, $villeQuery, $userId);
    if (FriendshipRepository::isAvailable()) {
        $friendRepo = new FriendshipRepository();
        foreach ($results as $row) {
            $oid = (int) ($row['id'] ?? 0);
            $relations[$oid] = $friendRepo->relationStatus($userId, $oid);
        }
    }
}

$success = '';
$error = '';
if ((string) ($_GET['ami'] ?? '') === 'envoye') {
    $success = 'Demande d’ami envoyée.';
} elseif ((string) ($_GET['ami'] ?? '') === 'accepte') {
    $success = 'Vous êtes maintenant amis.';
}
if ((string) ($_GET['ami_erreur'] ?? '') !== '') {
    $error = (string) $_GET['ami_erreur'];
}

View::render('rechercher_utilisateurs', [
    'pageTitle' => 'Rechercher des utilisateurs',
    'pseudoQuery' => $pseudoQuery,
    'villeQuery' => $villeQuery,
    'searched' => $searched,
    'results' => $results,
    'relations' => $relations,
    'socialAvailable' => FriendshipRepository::isAvailable(),
    'error' => $error,
    'success' => $success,
]);
