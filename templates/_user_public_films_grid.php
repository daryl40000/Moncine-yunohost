<?php
/**
 * Grille lecture seule des films d’un autre utilisateur.
 *
 * @var list<array<string, mixed>> $films
 * @var int $targetUserId
 * @var string $listMode
 * @var string $sortBy
 * @var string $sortDir
 */
$sortLink = static function (string $label, string $column) use ($targetUserId, $listMode, $sortBy, $sortDir): void {
    $active = $sortBy === $column;
    ?>
    <a href="<?= Moncine\View::escape(
        Moncine\View::userProfileListUrl($targetUserId, $listMode, $column, $sortBy, $sortDir)
    ) ?>"
       class="collection-grid-sort__link<?= $active ? ' is-active' : '' ?>">
        <?= Moncine\View::escape($label) ?><?= Moncine\View::filmsSortIndicator($column, $sortBy, $sortDir) ?>
    </a>
    <?php
};
?>
<?php if ($films === []): ?>
    <p class="hint">Aucun film dans cette liste.</p>
<?php else: ?>
    <p class="stats"><?= count($films) ?> film<?= count($films) > 1 ? 's' : '' ?></p>
    <nav class="collection-grid-sort social-profile-list-sort" aria-label="Trier">
        <span class="collection-grid-sort__label">Trier par</span>
        <?php $sortLink('Titre', 'titre'); ?>
        <?php $sortLink('Année', 'annee'); ?>
        <?php $sortLink('Réalisateur', 'realisateur'); ?>
    </nav>
    <ul class="collection-grid social-profile-grid" role="list">
        <?php foreach ($films as $film):
            $posterSrc = Moncine\View::posterSrc($film['poster_url'] ?? null);
            $titre = (string) ($film['titre'] ?? '');
            $annee = (int) ($film['annee'] ?? 0);
            ?>
            <li class="collection-grid__item" role="listitem">
                <article class="collection-grid__card">
                    <div class="collection-grid__link social-profile-grid__card">
                        <?php if ($posterSrc !== ''): ?>
                            <div class="collection-grid__poster-wrap">
                                <img class="collection-grid__poster" src="<?= $posterSrc ?>"
                                     alt="Affiche de <?= Moncine\View::escape($titre) ?>"
                                     loading="lazy" decoding="async">
                            </div>
                        <?php else: ?>
                            <div class="collection-grid__poster-wrap collection-grid__poster-wrap--empty">
                                <span class="collection-grid__poster-placeholder">?</span>
                            </div>
                        <?php endif; ?>
                        <div class="collection-grid__meta">
                            <span class="collection-grid__title"><?= Moncine\View::escape($titre) ?></span>
                            <?php if ($annee > 0): ?>
                                <span class="collection-grid__year"><?= $annee ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
