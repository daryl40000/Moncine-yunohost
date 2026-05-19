<?php
/** @var string $templateFile Fichier de contenu injecté par View::render */
$isAdminCatalog = Moncine\CatalogAdmin::canAccess();
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
</head>
<body<?= !empty($wideLayout) ? ' class="page-wide"' : '' ?>>
    <header class="site-header" id="site-header">
        <div class="container site-header__inner">
            <a href="/" class="logo">
                <img class="logo__img" src="/assets/img/logo.png"
                     alt="<?= Moncine\View::escape(MONCINE_APP_NAME) ?>"
                     width="56" height="56" decoding="async">
            </a>
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
                <?php if (!$isAdminCatalog): ?>
                    <a href="/import.php"<?= $currentPath === '/import.php' ? ' aria-current="page"' : '' ?>>Importer</a>
                <?php endif; ?>

                <a href="/parametres.php" class="site-nav__settings"<?= $currentPath === '/parametres.php' ? ' aria-current="page"' : '' ?>>
                    Paramètres
                </a>

                <?php if ($isAdminCatalog): ?>
                    <?php
                    $gestionPaths = [
                        '/import.php',
                        '/catalogue.php',
                        '/maintenance-catalogue.php',
                        '/foyers.php',
                        '/utilisateurs.php',
                    ];
                    $gestionOpen = in_array($currentPath, $gestionPaths, true);
                    ?>
                    <details class="site-nav__menu site-nav__menu--gestion"<?= $gestionOpen ? ' open' : '' ?>>
                        <summary class="site-nav__menu-summary site-nav__admin">Gestion</summary>
                        <div class="site-nav__submenu" role="group" aria-label="Gestion administrateur">
                            <a href="/import.php" class="site-nav__admin"<?= $currentPath === '/import.php' ? ' aria-current="page"' : '' ?>>Importer</a>
                            <a href="/catalogue.php" class="site-nav__admin"<?= $currentPath === '/catalogue.php' ? ' aria-current="page"' : '' ?>>Catalogue</a>
                            <a href="/maintenance-catalogue.php" class="site-nav__admin"<?= $currentPath === '/maintenance-catalogue.php' ? ' aria-current="page"' : '' ?>>Maintenance</a>
                            <a href="/foyers.php" class="site-nav__admin"<?= $currentPath === '/foyers.php' ? ' aria-current="page"' : '' ?>>Foyers</a>
                            <a href="/utilisateurs.php" class="site-nav__admin"<?= $currentPath === '/utilisateurs.php' ? ' aria-current="page"' : '' ?>>Comptes</a>
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
