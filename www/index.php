<?php
/**
 * Page d'accueil Moncine.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';

use Moncine\FilmRepository;
use Moncine\View;

$films = new FilmRepository();

View::render('home', [
    'pageTitle' => 'Accueil',
    'filmCount' => $films->count(),
    'setupDone' => isset($_GET['setup']) && (string) $_GET['setup'] === '1',
]);
