<?php
/**
 * Profil et changement de mot de passe.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Moncine\Auth;
use Moncine\Csrf;
use Moncine\FoyerRepository;
use Moncine\UtilisateurRepository;
use Moncine\View;

$userId = Auth::currentUserId();
$repo = new UtilisateurRepository();
$user = $repo->findById($userId);

if ($user === null) {
    header('Location: /connexion.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::rejectUnlessValid($_POST, '/mon-compte.php');
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'profile') {
        $result = $repo->updateProfile(
            $userId,
            (string) ($_POST['nom'] ?? ''),
            (string) ($_POST['email'] ?? '')
        );
        if ($result === true) {
            $success = 'Profil mis à jour.';
            $user = $repo->findById($userId) ?? $user;
        } else {
            $error = (string) $result;
        }
    } elseif ($action === 'password') {
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['new_password_confirm'] ?? '');
        if ($new !== $confirm) {
            $error = 'Les deux nouveaux mots de passe ne correspondent pas.';
        } else {
            $result = $repo->changePassword(
                $userId,
                (string) ($_POST['current_password'] ?? ''),
                $new
            );
            if ($result === true) {
                $success = 'Mot de passe modifié.';
            } else {
                $error = (string) $result;
            }
        }
    }
}

View::render('mon-compte', [
    'pageTitle' => 'Mon compte',
    'user' => $user,
    'foyer' => (new FoyerRepository())->findForUser($userId),
    'error' => $error,
    'success' => $success,
]);
