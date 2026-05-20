<?php
/**
 * @var string $pseudoQuery
 * @var string $villeQuery
 * @var bool $searched
 * @var list<array<string, mixed>> $results
 */
?>
<section class="account-page user-search-page">
    <h1>Rechercher des utilisateurs</h1>
    <p class="lead">
        Trouvez d’autres membres par <strong>pseudo</strong> et/ou <strong>ville</strong>.
        Seuls les comptes qui acceptent d’apparaître dans la recherche sont listés.
    </p>

    <form method="get" action="/rechercher-utilisateurs.php" class="import-form auth-form user-search-form">
        <label for="search_pseudo">Pseudo</label>
        <input type="search" name="pseudo" id="search_pseudo" autocomplete="off"
               placeholder="Ex. CineFan"
               value="<?= Moncine\View::escape($pseudoQuery) ?>">

        <label for="search_ville">Ville</label>
        <input type="search" name="ville" id="search_ville" autocomplete="off"
               placeholder="Ex. Lyon"
               value="<?= Moncine\View::escape($villeQuery) ?>">

        <button type="submit" class="btn btn-primary">Rechercher</button>
    </form>

    <?php if ($searched): ?>
        <?php if ($results === []): ?>
            <p class="hint user-search-page__empty">Aucun utilisateur trouvé pour ces critères.</p>
        <?php else: ?>
            <ul class="user-search-results">
                <?php foreach ($results as $row): ?>
                    <?php
                    $display = Moncine\UserProfile::displayName($row);
                    $pseudo = trim((string) ($row['pseudo'] ?? ''));
                    $ville = trim((string) ($row['ville'] ?? ''));
                    ?>
                    <li class="user-search-results__item">
                        <span class="user-search-results__name"><?= Moncine\View::escape($display) ?></span>
                        <?php if ($pseudo !== ''): ?>
                            <span class="user-search-results__meta">@<?= Moncine\View::escape($pseudo) ?></span>
                        <?php endif; ?>
                        <?php if ($ville !== ''): ?>
                            <span class="user-search-results__meta"><?= Moncine\View::escape($ville) ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p class="hint">Résultats limités à 50. La demande d’ami sera disponible à la phase 6.</p>
        <?php endif; ?>
    <?php else: ?>
        <p class="hint">Saisissez au moins un pseudo ou une ville, puis lancez la recherche.</p>
    <?php endif; ?>

    <p class="collection-page__footer-links">
        <a href="/parametres.php">← Mon compte</a>
    </p>
</section>
