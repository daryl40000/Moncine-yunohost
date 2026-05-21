<?php
/**
 * Création et révocation des liens de partage visiteur.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Moncine\Auth;
use Moncine\Csrf;
use Moncine\ShareLinkRepository;
use Moncine\ShareLinkScope;
use Moncine\ShareLinkService;
use Moncine\UserContext;
use Moncine\View;

$userId = Auth::currentUserId();
$foyerId = UserContext::currentFoyerId();
$service = new ShareLinkService();
$repo = new ShareLinkRepository();

$flash = '';
$flashError = '';
$newShareUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::rejectUnlessValid($_POST, '/gerer-partages.php');
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'create') {
        $scope = ShareLinkScope::normalize((string) ($_POST['scope'] ?? ''));
        $label = trim((string) ($_POST['label'] ?? ''));
        $result = $service->create($userId, $foyerId, $scope, $label);
        if (is_string($result)) {
            $flashError = $result;
        } else {
            $rawToken = (string) $result['token'];
            $scopeNorm = ShareLinkScope::normalize((string) ($result['link']['scope'] ?? ''));
            $newShareUrl = ShareLinkService::listUrl($rawToken, $scopeNorm);
            $flash = 'Lien créé. Copiez l’URL ci-dessous : elle ne sera plus affichée en entier après rechargement.';
        }
    } elseif ($action === 'revoke') {
        $linkId = (int) ($_POST['link_id'] ?? 0);
        if ($service->revoke($linkId, $userId)) {
            $flash = 'Lien révoqué.';
        } else {
            $flashError = 'Impossible de révoquer ce lien.';
        }
    }
}

$links = $repo->listForUser($userId);

$defaultScope = ShareLinkScope::normalize((string) ($_GET['scope'] ?? ShareLinkScope::COLLECTION));

View::render('gerer-partages', [
    'pageTitle' => 'Liens de partage',
    'links' => $links,
    'flash' => $flash,
    'flashError' => $flashError,
    'newShareUrl' => $newShareUrl,
    'foyerId' => $foyerId,
    'defaultScope' => $defaultScope,
]);
