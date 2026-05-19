<?php
/** @var string $query */
/** @var list<array<string, mixed>> $films */
/** @var bool $searched */
/** @var list<string> $suggestions */
?>
<section class="personnes-page">
    <h1>Films par acteur ou réalisateur</h1>
    <p class="lead">
        Trouvez vos films où une personne est
        <strong>réalisateur</strong> ou <strong>acteur principal</strong>
        (données issues de l’enrichissement TMDB).
    </p>

    <form method="get" action="/personnes.php" class="personnes-search import-form">
        <label for="q">Nom de l’acteur ou du réalisateur</label>
        <input type="search" name="q" id="q" list="personnes-suggestions"
               value="<?= Moncine\View::escape($query) ?>"
               placeholder="ex. Harrison Ford, Denis Villeneuve…"
               autofocus required>
        <?php if ($suggestions !== []): ?>
            <datalist id="personnes-suggestions">
                <?php foreach ($suggestions as $name): ?>
                    <option value="<?= Moncine\View::escape($name) ?>"></option>
                <?php endforeach; ?>
            </datalist>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary">Rechercher</button>
    </form>

    <?php if ($searched): ?>
        <?php if ($films === []): ?>
            <p class="alert alert-warning">
                Aucun film trouvé pour « <?= Moncine\View::escape($query) ?> ».
                Vérifiez l’orthographe ou <a href="/import.php">enrichissez vos films</a>
                pour remplir réalisateurs et acteurs.
            </p>
        <?php else: ?>
            <p class="stats">
                <?= count($films) ?> film<?= count($films) > 1 ? 's' : '' ?>
                pour « <strong><?= Moncine\View::escape($query) ?></strong> »
            </p>
            <p class="table-scroll-hint show-mobile-only">Faites glisser le tableau horizontalement pour voir toutes les colonnes.</p>
            <div class="table-scroll">
            <table class="films-table personnes-results">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Année</th>
                        <th>Rôle</th>
                        <th>Réalisateur</th>
                        <th>Acteurs</th>
                        <th>Dernière vue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($films as $film):
                        $acteurs = Moncine\FilmManualEdit::acteursList($film);
                        $roles = $film['roles'] ?? [];
                        ?>
                        <tr>
                            <td>
                                <a href="/film.php?id=<?= (int) $film['id'] ?>" class="film-link">
                                    <?= Moncine\View::escape($film['titre']) ?>
                                </a>
                            </td>
                            <td><?= (int) ($film['annee'] ?? 0) > 0 ? (int) $film['annee'] : '—' ?></td>
                            <td>
                                <?php foreach ($roles as $role): ?>
                                    <span class="tag tag--role"><?= Moncine\View::escape($role) ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td><?= ($film['realisateur'] ?? '') !== '' ? Moncine\View::escape($film['realisateur']) : '—' ?></td>
                            <td><?= $acteurs !== [] ? Moncine\View::escape(implode(', ', $acteurs)) : '—' ?></td>
                            <td><?= !empty($film['derniere_vue'])
                                ? Moncine\View::escape(Moncine\HistoriqueRepository::formatDateVue((string) $film['derniere_vue']))
                                : '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>
    <?php elseif ($suggestions === []): ?>
        <p class="hint">
            Aucun réalisateur ni acteur en base pour l’instant.
            <a href="/import.php">Enrichissez vos films</a> avec TMDB pour activer cette recherche.
        </p>
    <?php else: ?>
        <p class="hint">Saisissez un nom puis validez. La liste déroulante propose les personnes déjà connues parmi vos films.</p>
    <?php endif; ?>
</section>
