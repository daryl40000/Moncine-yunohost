<?php
/**
 * Confirmation d’inscription par jeton reçu par e-mail.
 *
 * Le lien e-mail ouvre cette page en GET (affichage uniquement).
 * La confirmation effective se fait en POST (évite les scanners de messagerie).
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Moncine\Auth;
use Moncine\Csrf;
use Moncine\RegistrationService;
use Moncine\View;

if (Auth::isLoggedIn()) {
    header('Location: /');
    exit;
}

$service = new RegistrationService();

if (!RegistrationService::isAvailable() || !$service->settings()->isPublicRegistrationEnabled()) {
    header('Location: /connexion.php');
    exit;
}

$token = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
$redirectWithToken = '/confirmer-inscription.php';
if ($token !== '') {
    $redirectWithToken .= '?token=' . rawurlencode($token);
}

$outcome = '';
$message = '';
$tokenValid = false;
$confirmed = false;

if ($token !== '') {
    $tokenValid = $service->isConfirmTokenValid($token);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::rejectUnlessValid($_POST, $redirectWithToken);
    $token = trim((string) ($_POST['token'] ?? ''));
    $tokenValid = $service->isConfirmTokenValid($token);

    if (!$tokenValid) {
        $outcome = 'error';
        $message = 'Lien invalide ou expiré. Vous pouvez refaire une demande d’inscription si besoin.';
    } else {
        $result = $service->confirmEmail($token);
        $outcome = (string) ($result['outcome'] ?? 'error');
        $message = (string) ($result['message'] ?? '');
        $confirmed = $outcome === 'ready' || $outcome === 'pending_admin';
        $tokenValid = false;
    }
}

View::render('confirmer-inscription', [
    'pageTitle' => 'Confirmation d’inscription',
    'token' => $token,
    'tokenValid' => $tokenValid,
    'outcome' => $outcome,
    'message' => $message,
    'confirmed' => $confirmed,
    'layout' => false,
]);
