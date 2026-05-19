<?php
/**
 * Gestion des foyers (administrateur) — collection partagée entre membres.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Moncine\Auth;
use Moncine\Csrf;
use Moncine\FoyerRepository;
use Moncine\UtilisateurRepository;
use Moncine\View;

Auth::denyUnlessAdmin('/');

$foyerRepo = new FoyerRepository();
$userRepo = new UtilisateurRepository();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::rejectUnlessValid($_POST, '/foyers.php');
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'create') {
        $result = $foyerRepo->create((string) ($_POST['nom'] ?? ''));
        $success = is_int($result) ? 'Foyer créé.' : '';
        $error = is_int($result) ? '' : (string) $result;
    } elseif ($action === 'update') {
        $result = $foyerRepo->update((int) ($_POST['foyer_id'] ?? 0), (string) ($_POST['nom'] ?? ''));
        if ($result === true) {
            $success = 'Foyer mis à jour.';
        } else {
            $error = (string) $result;
        }
    } elseif ($action === 'delete') {
        $result = $foyerRepo->delete((int) ($_POST['foyer_id'] ?? 0));
        if ($result === true) {
            $success = 'Foyer supprimé.';
        } else {
            $error = (string) $result;
        }
    } elseif ($action === 'assign_user') {
        $result = $foyerRepo->assignUser(
            (int) ($_POST['user_id'] ?? 0),
            (int) ($_POST['foyer_id'] ?? 0)
        );
        if ($result === true) {
            $success = 'Membre affecté au foyer.';
        } else {
            $error = (string) $result;
        }
    }
}

View::render('foyers', [
    'pageTitle' => 'Foyers',
    'foyers' => $foyerRepo->listAll(),
    'users' => $userRepo->listAll(),
    'error' => $error,
    'success' => $success,
]);
