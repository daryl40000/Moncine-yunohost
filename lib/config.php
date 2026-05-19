<?php
/**
 * Configuration Moncine — édition paquet YunoHost (développement).
 *
 * Production figée (export uniquement) : ../Moncine (origine)/
 *
 * Structure :
 *   www/     racine web
 *   lib/     code PHP
 *   data/    SQLite, clés API, affiches (hors git)
 */

declare(strict_types=1);

// Racine du projet = parent de lib/
define('MONCINE_ROOT', dirname(__DIR__));

// Dossier web (www) — utile pour les liens et includes de templates
define('MONCINE_WWW', MONCINE_ROOT . '/www');

// Données : sur YunoHost, MONCINE_DATA_PATH pointe vers /home/yunohost.app/moncine/ (voir extra_php-fpm.conf).
$dataPath = getenv('MONCINE_DATA_PATH');
if ($dataPath === false || $dataPath === '') {
    $dataPath = MONCINE_ROOT . '/data';
}
define('MONCINE_DATA', $dataPath);

define('MONCINE_DB_FILE', MONCINE_DATA . '/moncine.db');

// Graine d’installation (CSV catalogue + ZIP affiches) — voir install_seed/README.md
define('MONCINE_INSTALL_SEED_PACKAGE_DIR', MONCINE_ROOT . '/install_seed');
define('MONCINE_INSTALL_SEED_DATA_DIR', MONCINE_DATA . '/install_seed');

// Nom de l'application (affiché dans les pages)
define('MONCINE_APP_NAME', 'Moncine');

// Version affichée / paquet (semver)
define('MONCINE_PACKAGE_VERSION', '2.1.0-dev');

// Repère affiché sur la page import (vérifier que le serveur a bien le dernier code).
define('MONCINE_IMPORT_ENGINE_BUILD', '2026-05-18-library-oeuvre-first');
define('MONCINE_PACKAGE_EDITION', 'yunohost');

// Encodage CSV attendu à l'import (UTF-8 recommandé)
define('MONCINE_CSV_DELIMITER', ';');

// Clé API TMDB (synopsis en français) : variable d'environnement ou fichier data/tmdb_api_key.txt
define('MONCINE_TMDB_KEY_FILE', MONCINE_DATA . '/tmdb_api_key.txt');

// Films traités par clic sur « Enrichir » (évite les timeouts PHP)
define('MONCINE_ENRICH_BATCH_SIZE', 8);

// Films déjà vus : proposables à nouveau après cette durée (24 mois ≈ 2 ans)
define('MONCINE_MIN_DAYS_SINCE_REVIEW_OK', 730);

// Taille maximale du fichier CSV à l’import (5 Mo)
define('MONCINE_CSV_MAX_BYTES', 5 * 1024 * 1024);

// Affiche téléchargée (2 Mo max)
define('MONCINE_POSTER_MAX_BYTES', 2 * 1024 * 1024);

// Archive ZIP d’affiches à l’import (admin)
define('MONCINE_POSTERS_ZIP_MAX_BYTES', 80 * 1024 * 1024);
