<?php
/**
 * @var list<array<string, mixed>> $foyers
 * @var list<array<string, mixed>> $users
 * @var string $error
 * @var string $success
 */
?>
<section class="users-admin-page">
    <h1>Foyers</h1>
    <p class="lead">
        Un foyer regroupe plusieurs comptes qui partagent la même collection de films.
        Chaque personne garde ses propres envies et son historique de visions.
    </p>

    <?php if ($success !== ''): ?>
        <p class="alert alert-success"><?= Moncine\View::escape($success) ?></p>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <p class="alert alert-warning"><?= Moncine\View::escape($error) ?></p>
    <?php endif; ?>

    <details class="catalog-admin-panel" open>
        <summary class="catalog-admin-panel__summary">Créer un foyer</summary>
        <div class="catalog-admin-panel__body">
            <form method="post" action="/foyers.php" class="import-form auth-form">
                <?php require MONCINE_ROOT . '/templates/_csrf_field.php'; ?>
                <input type="hidden" name="action" value="create">
                <label for="foyer_nom">Nom du foyer</label>
                <input type="text" name="nom" id="foyer_nom" required placeholder="Ex. Famille Martin">
                <button type="submit" class="btn btn-primary">Créer le foyer</button>
            </form>
        </div>
    </details>

    <h2>Foyers existants</h2>
    <?php if ($foyers === []): ?>
        <p class="hint">Aucun foyer pour l’instant. Le premier compte en créera un automatiquement.</p>
    <?php else: ?>
        <div class="table-scroll">
            <table class="films-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Membres</th>
                        <th>Films</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($foyers as $foyer):
                        $fid = (int) ($foyer['id'] ?? 0);
                        ?>
                        <tr>
                            <td>
                                <form method="post" action="/foyers.php" class="inline-form">
                                    <?php require MONCINE_ROOT . '/templates/_csrf_field.php'; ?>
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="foyer_id" value="<?= $fid ?>">
                                    <input type="text" name="nom" class="input-inline"
                                           value="<?= Moncine\View::escape((string) ($foyer['nom'] ?? '')) ?>" required>
                                    <button type="submit" class="btn btn-secondary btn-sm">Renommer</button>
                                </form>
                            </td>
                            <td><?= (int) ($foyer['member_count'] ?? 0) ?></td>
                            <td><?= (int) ($foyer['collection_count'] ?? 0) ?></td>
                            <td>
                                <form method="post" action="/foyers.php" class="inline-form"
                                      onsubmit="return confirm('Supprimer ce foyer vide ?');">
                                    <?php require MONCINE_ROOT . '/templates/_csrf_field.php'; ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="foyer_id" value="<?= $fid ?>">
                                    <button type="submit" class="btn btn-danger-text btn-sm">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <h2>Affecter un membre à un foyer</h2>
    <form method="post" action="/foyers.php" class="import-form auth-form">
        <?php require MONCINE_ROOT . '/templates/_csrf_field.php'; ?>
        <input type="hidden" name="action" value="assign_user">
        <label for="assign_user_id">Compte</label>
        <select name="user_id" id="assign_user_id" required>
            <?php foreach ($users as $user): ?>
                <option value="<?= (int) ($user['id'] ?? 0) ?>">
                    <?= Moncine\View::escape((string) ($user['nom'] ?? '')) ?>
                    (<?= Moncine\View::escape((string) ($user['email'] ?? '')) ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <label for="assign_foyer_id">Foyer</label>
        <select name="foyer_id" id="assign_foyer_id" required>
            <?php foreach ($foyers as $foyer): ?>
                <option value="<?= (int) ($foyer['id'] ?? 0) ?>">
                    <?= Moncine\View::escape((string) ($foyer['nom'] ?? '')) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Affecter</button>
    </form>

    <p class="collection-page__footer-links">
        <a href="/utilisateurs.php">Comptes utilisateurs</a> · <a href="/">Accueil</a>
    </p>
</section>
