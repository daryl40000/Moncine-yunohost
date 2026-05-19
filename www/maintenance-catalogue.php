<?php
/**
 * Maintenance du catalogue (admin) : doublons, fusion, nettoyage, journal.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Moncine\Auth;
use Moncine\CatalogAdmin;
use Moncine\CatalogAuditLog;
use Moncine\CatalogMaintenance;
use Moncine\Csrf;
use Moncine\View;

CatalogAdmin::denyUnlessAccess();

$maintenance = new CatalogMaintenance();
$audit = new CatalogAuditLog();
$message = '';
$error = '';
$adminUserId = Auth::currentUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::rejectUnlessValid($_POST, '/maintenance-catalogue.php');

    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'merge_oeuvres') {
        $keepId = (int) ($_POST['keep_id'] ?? 0);
        $removeId = (int) ($_POST['remove_id'] ?? 0);
        $result = $maintenance->mergeOeuvres($keepId, $removeId, $adminUserId);
        if ($result === true) {
            $message = 'Fusion réussie : fiche #' . $removeId . ' intégrée dans #' . $keepId . '.';
        } else {
            $error = (string) $result;
        }
    } elseif ($action === 'purge_orphan_posters') {
        $result = $maintenance->purgeOrphanPosters($adminUserId);
        $message = $result['deleted'] . ' affiche(s) orpheline(s) supprimée(s).';
        if ($result['errors'] !== []) {
            $error = 'Échec pour : ' . implode(', ', $result['errors']);
        }
    }
}

View::render('maintenance-catalogue', [
    'pageTitle' => 'Maintenance catalogue',
    'wideLayout' => true,
    'stats' => $maintenance->dashboardStats(),
    'duplicateTitleGroups' => $maintenance->findDuplicateGroupsByTitle(),
    'duplicateTmdbGroups' => $maintenance->findDuplicateGroupsByTmdb(),
    'incompleteOeuvres' => $maintenance->findIncompleteOeuvres(),
    'orphanPosters' => $maintenance->findOrphanPosterFiles(),
    'auditLog' => $audit->listRecent(25),
    'message' => $message,
    'error' => $error,
]);
