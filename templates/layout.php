<?php
/** @var string $templateFile Fichier de contenu injecté par View::render */
use Moncine\Auth;
use Moncine\NotificationService;

$isAdminCatalog = Moncine\CatalogAdmin::canAccess();
$submissionsAvailable = Moncine\CatalogSubmission::isAvailable();
$canProposeToCatalog = $submissionsAvailable && !$isAdminCatalog;
$pendingSubmissions = $isAdminCatalog && $submissionsAvailable
    ? (new Moncine\CatalogSubmission())->countPending()
    : 0;
$notificationsAvailable = NotificationService::isAvailable() && Auth::currentUserId() > 0;
$unreadNotifications = $notificationsAvailable
    ? (new NotificationService())->countUnread(Auth::currentUserId())
    : 0;
$currentPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= Moncine\View::escape($pageTitle ?? MONCINE_APP_NAME) ?> — <?= Moncine\View::escape(MONCINE_APP_NAME) ?></title>
    <link rel="icon" href="/assets/img/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/img/favicon-16x16.png">
    <link rel="apple-touch-icon" href="/assets/img/apple-touch-icon.png">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/responsive.css">
    <?php if ($currentPath === '/catalogue.php'): ?>
        <script>
            (function () {
                if (window.location.hash !== '#catalog-list-nav') {
                    return;
                }
                if ('scrollRestoration' in history) {
                    history.scrollRestoration = 'manual';
                }
                window.scrollTo(0, 0);
            })();
        </script>
    <?php endif; ?>
</head>
<body<?= !empty($wideLayout) ? ' class="page-wide"' : '' ?>>
    <header class="site-header" id="site-header">
        <div class="container site-header__inner">
            <div class="site-header__brand">
                <a href="/" class="logo">
                    <img class="logo__img" src="/assets/img/logo.png"
                         alt="<?= Moncine\View::escape(MONCINE_APP_NAME) ?>"
                         width="56" height="56" decoding="async">
                </a>
                <?php if ($notificationsAvailable): ?>
                    <?php
                    $notifLabel = 'Notifications';
                    if ($unreadNotifications > 0) {
                        $notifLabel .= ' — ' . (int) $unreadNotifications . ' non lue' . ($unreadNotifications > 1 ? 's' : '');
                    }
                    ?>
                    <a href="/notifications.php"
                       class="header-notifications<?= $currentPath === '/notifications.php' ? ' header-notifications--current' : '' ?>"
                       aria-label="<?= Moncine\View::escape($notifLabel) ?>"
                       title="<?= Moncine\View::escape($notifLabel) ?>">
                        <svg class="header-notifications__icon" width="22" height="22" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path fill="currentColor" d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Zm7-6V11a7 7 0 0 0-5.25-6.77V3a.75.75 0 0 0-1.5 0v1.26A7.002 7.002 0 0 0 5 11v5l-2 2v1h18v-1l-2-2Z"/>
                        </svg>
                        <?php if ($unreadNotifications > 0): ?>
                            <span class="header-notifications__badge" aria-hidden="true"><?= $unreadNotifications > 9 ? '9+' : (int) $unreadNotifications ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            </div>
            <button type="button" class="nav-toggle" id="nav-toggle"
                    aria-expanded="false" aria-controls="site-nav"
                    aria-label="Ouvrir le menu">
                <span class="nav-toggle__bar" aria-hidden="true"></span>
                <span class="nav-toggle__bar" aria-hidden="true"></span>
                <span class="nav-toggle__bar" aria-hidden="true"></span>
            </button>
            <nav class="site-nav" id="site-nav" aria-label="Navigation principale">
                <a href="/"<?= $currentPath === '/' ? ' aria-current="page"' : '' ?>>Accueil</a>
                <a href="/quiz.php"<?= $currentPath === '/quiz.php' ? ' aria-current="page"' : '' ?>>Ce soir</a>
                <a href="/films.php"<?= $currentPath === '/films.php' ? ' aria-current="page"' : '' ?>>Mes films</a>
                <a href="/souhaits.php"<?= $currentPath === '/souhaits.php' ? ' aria-current="page"' : '' ?>>Mes envies</a>
                <a href="/statistiques.php"<?= $currentPath === '/statistiques.php' ? ' aria-current="page"' : '' ?>>Statistiques</a>

                <?php
                $parametresPaths = [
                    '/parametres.php',
                    '/mon-compte.php',
                    '/import.php',
                    '/proposer-oeuvre.php',
                    '/mes-soumissions.php',
                    '/rechercher-utilisateurs.php',
                ];
                $parametresOpen = in_array($currentPath, $parametresPaths, true);
                ?>
                <details class="site-nav__menu site-nav__menu--parametres"<?= $parametresOpen ? ' open' : '' ?>>
                    <summary class="site-nav__menu-summary site-nav__settings">Paramètres</summary>
                    <div class="site-nav__submenu" role="group" aria-label="Paramètres et compte">
                        <a href="/parametres.php"<?= in_array($currentPath, ['/parametres.php', '/mon-compte.php'], true) ? ' aria-current="page"' : '' ?>>Compte</a>
                        <a href="/rechercher-utilisateurs.php"<?= $currentPath === '/rechercher-utilisateurs.php' ? ' aria-current="page"' : '' ?>>Rechercher des utilisateurs</a>
                        <?php if ($canProposeToCatalog): ?>
                            <a href="/proposer-oeuvre.php"<?= in_array($currentPath, ['/proposer-oeuvre.php', '/mes-soumissions.php'], true) ? ' aria-current="page"' : '' ?>>
                                Proposer au catalogue
                            </a>
                        <?php endif; ?>
                        <a href="/import.php"<?= $currentPath === '/import.php' ? ' aria-current="page"' : '' ?>>Importer</a>
                    </div>
                </details>

                <?php if ($isAdminCatalog): ?>
                    <?php
                    $gestionPaths = [
                        '/catalogue.php',
                        '/soumissions-catalogue.php',
                        '/maintenance-catalogue.php',
                        '/foyers.php',
                        '/utilisateurs.php',
                    ];
                    $gestionOpen = in_array($currentPath, $gestionPaths, true);
                    ?>
                    <details class="site-nav__menu site-nav__menu--gestion"<?= $gestionOpen ? ' open' : '' ?>>
                        <summary class="site-nav__menu-summary site-nav__admin">Gestion</summary>
                        <div class="site-nav__submenu" role="group" aria-label="Gestion administrateur">
                            <a href="/catalogue.php" class="site-nav__admin"<?= $currentPath === '/catalogue.php' ? ' aria-current="page"' : '' ?>>Catalogue</a>
                            <?php if ($submissionsAvailable): ?>
                                <a href="/soumissions-catalogue.php" class="site-nav__admin"<?= $currentPath === '/soumissions-catalogue.php' ? ' aria-current="page"' : '' ?>>
                                    Soumissions<?= $pendingSubmissions > 0 ? ' (' . (int) $pendingSubmissions . ')' : '' ?>
                                </a>
                            <?php endif; ?>
                            <a href="/maintenance-catalogue.php" class="site-nav__admin"<?= $currentPath === '/maintenance-catalogue.php' ? ' aria-current="page"' : '' ?>>Maintenance</a>
                            <a href="/foyers.php" class="site-nav__admin"<?= $currentPath === '/foyers.php' ? ' aria-current="page"' : '' ?>>Foyers</a>
                            <a href="/utilisateurs.php" class="site-nav__admin"<?= $currentPath === '/utilisateurs.php' ? ' aria-current="page"' : '' ?>>Comptes utilisateurs</a>
                        </div>
                    </details>
                <?php endif; ?>

                <?php /* POST + jeton CSRF : évite une déconnexion forcée par un simple lien */ ?>
                <form method="post" action="/deconnexion.php" class="inline-form site-nav__logout-form">
                    <?php require MONCINE_ROOT . '/templates/_csrf_field.php'; ?>
                    <button type="submit" class="site-nav__logout btn-link">Déconnexion</button>
                </form>
            </nav>
        </div>
    </header>
    <main class="container<?= !empty($wideLayout) ? ' container--wide' : '' ?>">
        <?php if (!empty($_GET['csrf_error'])): ?>
            <p class="alert alert-warning"><?= Moncine\View::escape(Moncine\Csrf::REJECT_MESSAGE) ?></p>
        <?php endif; ?>
        <?php require $templateFile; ?>
    </main>
    <footer class="site-footer container">
        <p>Dvdthèque personnelle — <?= Moncine\View::escape(MONCINE_APP_NAME) ?></p>
    </footer>
    <script src="/assets/js/app.js" defer></script>
</body>
</html>
