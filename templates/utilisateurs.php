<?php
/**
 * @var list<array<string, mixed>> $users
 * @var string $error
 * @var string $success
 * @var int $currentUserId
 */
?>
<section class="users-admin-page">
    <h1>Comptes utilisateurs</h1>
    <p class="lead">Réservé aux administrateurs. Chaque personne a sa bibliothèque et ses envies.</p>

    <?php if ($success !== ''): ?>
        <p class="alert alert-success"><?= Moncine\View::escape($success) ?></p>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <p class="alert alert-warning"><?= Moncine\View::escape($error) ?></p>
    <?php endif; ?>

    <details class="catalog-admin-panel" open>
        <summary class="catalog-admin-panel__summary">Ajouter un compte</summary>
        <div class="catalog-admin-panel__body">
            <form method="post" action="/utilisateurs.php" class="import-form auth-form">
                <?php require MONCINE_ROOT . '/templates/_csrf_field.php'; ?>
                <input type="hidden" name="action" value="create">

                <label for="new_nom">Nom</label>
                <input type="text" name="nom" id="new_nom" required>

                <label for="new_email">E-mail</label>
                <input type="email" name="email" id="new_email" required>

                <label for="new_password">Mot de passe provisoire</label>
                <input type="password" name="password" id="new_password" required
                       minlength="<?= Moncine\UtilisateurRepository::MIN_PASSWORD_LENGTH ?>"
                       maxlength="<?= Moncine\UtilisateurRepository::MAX_PASSWORD_LENGTH ?>"
                       autocomplete="new-password">

                <label for="new_role">Rôle</label>
                <select name="role" id="new_role">
                    <option value="<?= Moncine\View::escape(Moncine\UserRole::USER) ?>">
                        <?= Moncine\View::escape(Moncine\UserRole::label(Moncine\UserRole::USER)) ?>
                    </option>
                    <option value="<?= Moncine\View::escape(Moncine\UserRole::ADMIN) ?>">
                        <?= Moncine\View::escape(Moncine\UserRole::label(Moncine\UserRole::ADMIN)) ?>
                    </option>
                </select>

                <button type="submit" class="btn btn-primary">Créer le compte</button>
            </form>
        </div>
    </details>

    <h2>Comptes existants</h2>
    <div class="table-scroll">
        <table class="films-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>E-mail</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user):
                    $uid = (int) ($user['id'] ?? 0);
                    $active = (int) ($user['actif'] ?? 0) === 1;
                    ?>
                    <tr>
                        <td><?= Moncine\View::escape((string) ($user['nom'] ?? '')) ?></td>
                        <td><?= Moncine\View::escape((string) ($user['email'] ?? '')) ?></td>
                        <td><?= Moncine\View::escape(Moncine\UserRole::label((string) ($user['role'] ?? ''))) ?></td>
                        <td><?= $active ? 'Actif' : 'Désactivé' ?></td>
                        <td class="users-admin-page__actions">
                            <?php if ($uid !== $currentUserId): ?>
                                <form method="post" action="/utilisateurs.php" class="inline-form users-admin-page__action-form">
                                    <?php require MONCINE_ROOT . '/templates/_csrf_field.php'; ?>
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="user_id" value="<?= $uid ?>">
                                    <input type="hidden" name="actif" value="<?= $active ? '0' : '1' ?>">
                                    <button type="submit" class="btn btn-secondary btn-sm">
                                        <?= $active ? 'Désactiver' : 'Réactiver' ?>
                                    </button>
                                </form>
                                <form method="post" action="/utilisateurs.php" class="inline-form users-admin-page__action-form"
                                      onsubmit="return confirm('Générer un nouveau mot de passe provisoire pour ce compte ?');">
                                    <?php require MONCINE_ROOT . '/templates/_csrf_field.php'; ?>
                                    <input type="hidden" name="action" value="reset_password">
                                    <input type="hidden" name="user_id" value="<?= $uid ?>">
                                    <button type="submit" class="btn btn-secondary btn-sm">Réinit. MDP</button>
                                </form>
                                <form method="post" action="/utilisateurs.php" class="inline-form users-admin-page__action-form"
                                      onsubmit="return confirm('Supprimer définitivement ce compte et toute sa bibliothèque (films, envies, historique) ?');">
                                    <?php require MONCINE_ROOT . '/templates/_csrf_field.php'; ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?= $uid ?>">
                                    <button type="submit" class="btn btn-danger-text btn-sm">Supprimer</button>
                                </form>
                            <?php else: ?>
                                <span class="hint">Vous</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p class="collection-page__footer-links">
        <a href="/">← Accueil</a>
    </p>
</section>
