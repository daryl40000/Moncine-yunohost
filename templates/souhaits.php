<?php
/** @var list<array<string, mixed>> $films */
/** @var string $sortBy */
/** @var string $sortDir */
/** @var string $query */
/** @var bool $searched */
/** @var int $totalCount */

$query = $query ?? '';
$searched = $searched ?? false;
$totalCount = (int) ($totalCount ?? count($films));
$resultCount = count($films);

$sortHeader = static function (string $label, string $column) use ($sortBy, $sortDir, $query): void {
    $active = $sortBy === $column;
    $aria = $active
        ? (strtolower($sortDir) === 'desc' ? 'descending' : 'ascending')
        : 'none';
    ?>
    <th class="<?= $active ? 'sorted' : '' ?>" aria-sort="<?= $aria ?>">
        <a href="<?= Moncine\View::escape(Moncine\View::wishlistSortUrl($column, $sortBy, $sortDir, $query)) ?>">
            <?= Moncine\View::escape($label) ?><?= Moncine\View::filmsSortIndicator($column, $sortBy, $sortDir) ?>
        </a>
    </th>
    <?php
};
?>
<section class="collection-page wishlist-page">
    <div class="collection-page__head">
        <h1><?= Moncine\View::escape(Moncine\LibraryStatut::label(Moncine\LibraryStatut::WISHLIST)) ?></h1>
    </div>
    <p class="lead">
        Films que vous aimeriez voir ou posséder. Quand vous les avez, ajoutez-les à vos films.
    </p>

    <form method="get" action="/souhaits.php" class="collection-search import-form">
        <label for="wishlist_q">Rechercher</label>
        <div class="collection-search__row">
            <input type="search" name="q" id="wishlist_q"
                   value="<?= Moncine\View::escape($query) ?>"
                   placeholder="Titre, réalisateur, acteur, style…"
                   autocomplete="off">
            <input type="hidden" name="sort" value="<?= Moncine\View::escape($sortBy) ?>">
            <input type="hidden" name="dir" value="<?= Moncine\View::escape($sortDir) ?>">
            <button type="submit" class="btn btn-primary">Rechercher</button>
            <?php if ($searched): ?>
                <a href="<?= Moncine\View::escape(Moncine\View::wishlistUrl('', $sortBy, $sortDir)) ?>"
                   class="btn btn-secondary">Effacer</a>
            <?php endif; ?>
        </div>
        <div class="collection-search__add">
            <a class="btn btn-primary" href="<?= Moncine\View::escape(Moncine\View::addFilmUrl(Moncine\LibraryStatut::WISHLIST)) ?>">
                Ajouter un film
            </a>
        </div>
    </form>

    <?php if (!empty($_GET['promote_error'])): ?>
        <p class="alert alert-warning"><?= Moncine\View::escape((string) $_GET['promote_error']) ?></p>
    <?php endif; ?>

    <?php if ($searched): ?>
        <p class="stats">
            <?= $resultCount ?> résultat<?= $resultCount > 1 ? 's' : '' ?>
            pour « <?= Moncine\View::escape($query) ?> »
        </p>
    <?php else: ?>
        <p class="stats"><?= $totalCount ?> film<?= $totalCount > 1 ? 's' : '' ?> dans vos envies</p>
    <?php endif; ?>

    <p class="hint collection-page__hint">
        Importez un CSV avec la colonne <strong>Statut</strong> = « wishlist », « mes envies » ou « à acheter » pour pré-remplir cette liste.
        <a href="/import.php">Page Importer</a>
    </p>

    <?php if ($totalCount === 0 && !$searched): ?>
        <p>Aucun film dans vos envies. <a href="/import.php">Importer une liste</a> ou ajoutez des titres via un fichier CSV.</p>
    <?php elseif ($films === []): ?>
        <p class="alert alert-warning">Aucun résultat pour cette recherche.</p>
    <?php else: ?>
        <p class="table-scroll-hint show-mobile-only">Faites glisser le tableau horizontalement pour voir toutes les colonnes.</p>
        <div class="table-scroll">
        <table class="films-table films-table--sortable">
            <thead>
                <tr>
                    <?php $sortHeader('Titre', 'titre'); ?>
                    <?php $sortHeader('Année', 'annee'); ?>
                    <th>Nationalité</th>
                    <?php $sortHeader('Réalisateur', 'realisateur'); ?>
                    <?php $sortHeader('Style', 'styles'); ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($films as $film):
                    $filmId = (int) $film['id'];
                    ?>
                    <tr>
                        <td>
                            <a href="/film.php?id=<?= $filmId ?>" class="film-link">
                                <?= Moncine\View::escape($film['titre']) ?>
                            </a>
                        </td>
                        <td><?= (int) ($film['annee'] ?? 0) > 0 ? (int) $film['annee'] : '—' ?></td>
                        <td><?= Moncine\View::escape(
                            Moncine\FilmRepository::formatNationalite((string) ($film['nationalite'] ?? ''))
                        ) ?></td>
                        <td><?= Moncine\View::escape($film['realisateur']) ?></td>
                        <td><?= Moncine\View::escape($film['styles']) ?></td>
                        <td class="wishlist-actions">
                            <form method="post" action="/souhaits.php" class="wishlist-promote-form import-form">
                                <?php require MONCINE_ROOT . '/templates/_csrf_field.php'; ?>
                                <input type="hidden" name="film_id" value="<?= $filmId ?>">
                                <input type="hidden" name="sort" value="<?= Moncine\View::escape($sortBy) ?>">
                                <input type="hidden" name="dir" value="<?= Moncine\View::escape($sortDir) ?>">
                                <input type="hidden" name="q" value="<?= Moncine\View::escape($query) ?>">
                                <select name="support_physique" aria-label="Support pour <?= Moncine\View::escape($film['titre']) ?>">
                                    <option value="">Support ?</option>
                                    <?php foreach (Moncine\SupportPhysique::choices() as $key => $label): ?>
                                        <option value="<?= Moncine\View::escape($key) ?>"><?= Moncine\View::escape($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm">J’ai acheté</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>

    <p class="collection-page__footer-links">
        <a href="/films.php">← Mes films</a>
    </p>
</section>
