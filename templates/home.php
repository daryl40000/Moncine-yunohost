<section class="hero">
    <?php if (!empty($setupDone)): ?>
        <p class="alert alert-success">Compte administrateur créé. Vous êtes connecté.</p>
    <?php endif; ?>
    <h1>Quel film ce soir ?</h1>
    <p class="lead">
        Moncine vous aide à choisir parmi votre dvdthèque, comme un petit questionnaire,
        en tenant compte des films déjà vus.
    </p>

    <?php if ($filmCount === 0): ?>
        <div class="alert alert-info">
            <p><strong>Aucun film en base.</strong> Commencez par importer votre liste (CSV exporté depuis Excel).</p>
            <a class="btn btn-primary" href="/import.php">Importer ma dvdthèque</a>
            <a class="btn btn-secondary" href="<?= Moncine\View::escape(Moncine\View::addFilmChoiceUrl()) ?>">Ajouter un film</a>
        </div>
    <?php else: ?>
        <p class="stats"><?= (int) $filmCount ?> film<?= $filmCount > 1 ? 's' : '' ?> dans vos films.</p>
        <div class="hero-actions">
            <a class="btn btn-primary btn-lg" href="/quiz.php">Lancer le questionnaire</a>
            <a class="btn btn-secondary" href="<?= Moncine\View::escape(Moncine\View::addFilmChoiceUrl()) ?>">Ajouter film</a>
            <a class="btn btn-secondary" href="/films.php">Voir mes films</a>
            <a class="btn btn-secondary" href="/statistiques.php">Statistiques</a>
        </div>
    <?php endif; ?>
</section>
