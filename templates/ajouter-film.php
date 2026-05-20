<?php
/** @var bool $showChoice */
/** @var string $statut */
/** @var string $statutLabel */
/** @var list<string> $sagaSuggestions */
/** @var bool $usesCatalog */
/** @var string $saveError */
/** @var bool $hasTmdbKey */
/** @var bool $canManageCatalog */

$showChoice = $showChoice ?? true;
$usesCatalog = $usesCatalog ?? true;
$saveError = $saveError ?? '';
$sagaSuggestions = $sagaSuggestions ?? [];
$hasTmdbKey = $hasTmdbKey ?? false;
$canManageCatalog = $canManageCatalog ?? false;
?>
<section class="add-film-page">
    <?php if ($showChoice): ?>
        <h1>Ajouter un film</h1>
        <p class="lead">Où souhaitez-vous enregistrer ce titre ?</p>

        <div class="add-film-choice">
            <a href="<?= Moncine\View::escape(Moncine\View::addFilmUrl(Moncine\LibraryStatut::COLLECTION)) ?>"
               class="add-film-choice__card">
                <span class="add-film-choice__title">Mes films</span>
                <span class="add-film-choice__hint">Film que vous possédez déjà (DVD, Blu-ray…)</span>
            </a>
            <?php if ($usesCatalog): ?>
                <a href="<?= Moncine\View::escape(Moncine\View::addFilmUrl(Moncine\LibraryStatut::WISHLIST)) ?>"
                   class="add-film-choice__card add-film-choice__card--wishlist">
                    <span class="add-film-choice__title"><?= Moncine\View::escape(Moncine\LibraryStatut::label(Moncine\LibraryStatut::WISHLIST)) ?></span>
                    <span class="add-film-choice__hint">Film que vous aimeriez voir ou posséder un jour</span>
                </a>
            <?php endif; ?>
        </div>

        <p class="collection-page__footer-links">
            <a href="/">← Accueil</a>
        </p>
    <?php else: ?>
        <h1>Ajouter un film</h1>
        <p class="lead">
            Nouvelle entrée dans <strong><?= Moncine\View::escape($statutLabel) ?></strong>.
            Indiquez le titre et la catégorie (film, série, documentaire, spectacle).
            <?php if ($canManageCatalog): ?>
                Vous pouvez enregistrer avec enrichissement TMDB (affiche, synopsis, acteurs…)
                ou compléter la fiche plus tard depuis le catalogue.
            <?php elseif (Moncine\CatalogSubmission::canSubmit()): ?>
                Choisissez une œuvre déjà au catalogue, ou
                <a href="/proposer-oeuvre.php">proposez une nouvelle fiche</a> à l’administrateur.
            <?php else: ?>
                Choisissez une œuvre déjà présente au catalogue partagé.
            <?php endif; ?>
        </p>

        <?php if ($prefillOeuvreId > 0 && is_array($prefillFilm)): ?>
            <p class="alert alert-info">
                Cette œuvre est déjà dans le catalogue partagé : les champs ci-dessous sont préremplis.
                Enregistrez pour l’ajouter à votre bibliothèque sans créer de doublon.
            </p>
        <?php endif; ?>

        <?php if ($saveError !== ''): ?>
            <p class="alert alert-warning"><?= Moncine\View::escape($saveError) ?></p>
        <?php endif; ?>

        <?php if ($canManageCatalog && !$hasTmdbKey): ?>
            <p class="alert alert-info">
                <a href="/import.php">Configurez une clé API TMDB</a> pour enrichir automatiquement vos fiches.
            </p>
        <?php endif; ?>

        <?php
        $prefillOeuvreId = (int) ($prefillOeuvreId ?? 0);
        $prefillFilm = $prefillFilm ?? null;
        $film = [
            'titre' => '',
            'titre_original' => '',
            'realisateur' => '',
            'acteur_1' => '',
            'acteur_2' => '',
            'acteur_3' => '',
            'annee' => 0,
            'nationalite' => '',
            'duree_min' => 0,
            'styles' => '',
            'saga' => '',
            'saga_ordre' => 0,
            'format_image' => '',
            'format_son' => '',
            'support_physique' => '',
            'poster_url' => '',
            'synopsis' => '',
            'tmdb_id' => 0,
            'tmdb_media_type' => '',
            'moncine_kind' => Moncine\MoncineContentKind::FILM,
            'saison_numero' => 0,
            'saison_label' => '',
            'ean' => '',
        ];
        if (is_array($prefillFilm)) {
            $film = array_merge($film, [
                'titre' => (string) ($prefillFilm['titre'] ?? ''),
                'titre_original' => (string) ($prefillFilm['titre_original'] ?? ''),
                'realisateur' => (string) ($prefillFilm['realisateur'] ?? ''),
                'acteur_1' => (string) ($prefillFilm['acteur_1'] ?? ''),
                'acteur_2' => (string) ($prefillFilm['acteur_2'] ?? ''),
                'acteur_3' => (string) ($prefillFilm['acteur_3'] ?? ''),
                'annee' => (int) ($prefillFilm['annee'] ?? 0),
                'nationalite' => (string) ($prefillFilm['nationalite'] ?? ''),
                'duree_min' => (int) ($prefillFilm['duree_min'] ?? 0),
                'styles' => (string) ($prefillFilm['styles'] ?? ''),
                'poster_url' => (string) ($prefillFilm['poster_url'] ?? ''),
                'synopsis' => (string) ($prefillFilm['synopsis'] ?? ''),
                'tmdb_id' => (int) ($prefillFilm['tmdb_id'] ?? 0),
                'tmdb_media_type' => (string) ($prefillFilm['tmdb_media_type'] ?? ''),
                'tmdb_tv_kind' => (string) ($prefillFilm['tmdb_tv_kind'] ?? ''),
                'moncine_kind' => (string) ($prefillFilm['moncine_kind'] ?? Moncine\MoncineContentKind::FILM),
            ]);
        }
        $formStatut = $statut;
        $cancelUrl = $statut === Moncine\LibraryStatut::WISHLIST ? '/souhaits.php' : '/films.php';
        if ($prefillOeuvreId > 0) {
            $cancelUrl = Moncine\View::oeuvreUrl($prefillOeuvreId);
        }
        require MONCINE_ROOT . '/templates/_film_add_form.php';
        ?>
    <?php endif; ?>
</section>
