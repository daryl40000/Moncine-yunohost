<?php
/**
 * @var array<string, mixed> $user
 * @var string $displayName
 * @var array<string, mixed>|null $foyer
 * @var string $error
 * @var string $success
 * @var int $maxPseudoLength
 * @var int $maxVilleLength
 * @var bool $isSearchable
 */
$minLen = Moncine\UtilisateurRepository::MIN_PASSWORD_LENGTH;
$maxLen = Moncine\UtilisateurRepository::MAX_PASSWORD_LENGTH;
?>
<section class="account-page">
    <h1>Mon compte</h1>
    <p class="lead">
        Modifiez les informations de votre compte
        (<strong><?= Moncine\View::escape($displayName) ?></strong>).
    </p>

    <?php if ($success !== ''): ?>
        <p class="alert alert-success"><?= Moncine\View::escape($success) ?></p>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <p class="alert alert-warning"><?= Moncine\View::escape($error) ?></p>
    <?php endif; ?>

    <details class="catalog-admin-panel" open>
        <summary class="catalog-admin-panel__summary">Informations du compte</summary>
        <div class="catalog-admin-panel__body">
            <form method="post" action="/parametres.php" class="import-form auth-form account-form">
                <?php require MONCINE_ROOT . '/templates/_csrf_field.php'; ?>
                <input type="hidden" name="action" value="profile">

                <label for="account_prenom">Prénom</label>
                <input type="text" name="prenom" id="account_prenom" autocomplete="given-name"
                       value="<?= Moncine\View::escape((string) ($user['prenom'] ?? '')) ?>">

                <label for="account_nom">Nom</label>
                <input type="text" name="nom" id="account_nom" autocomplete="family-name"
                       value="<?= Moncine\View::escape((string) ($user['nom'] ?? '')) ?>">

                <label for="account_pseudo">Pseudo</label>
                <input type="text" name="pseudo" id="account_pseudo" autocomplete="nickname"
                       maxlength="<?= (int) $maxPseudoLength ?>"
                       placeholder="Optionnel — affiché à la place du prénom et du nom"
                       value="<?= Moncine\View::escape((string) ($user['pseudo'] ?? '')) ?>">
                <p class="hint">Si vous renseignez un pseudo, c’est lui qui sera affiché dans l’application.</p>

                <label for="account_ville">Ville</label>
                <input type="text" name="ville" id="account_ville" autocomplete="address-level2"
                       maxlength="<?= (int) $maxVilleLength ?>"
                       placeholder="Optionnel — pour une future recherche « autour de moi »"
                       value="<?= Moncine\View::escape((string) ($user['ville'] ?? '')) ?>">
                <p class="hint">La ville n’est pas obligatoire. Elle pourra servir plus tard à trouver des utilisateurs proches de chez vous.</p>

                <label class="checkbox-label">
                    <input type="hidden" name="searchable" value="0">
                    <input type="checkbox" name="searchable" value="1"
                           <?= $isSearchable ? ' checked' : '' ?>>
                    Apparaître dans la recherche d’utilisateurs (par pseudo et ville)
                </label>
                <p class="hint">Si vous décochez cette case, les autres ne pourront pas vous trouver via la recherche.</p>

                <label for="account_email">E-mail</label>
                <input type="email" name="email" id="account_email" required autocomplete="email"
                       value="<?= Moncine\View::escape((string) ($user['email'] ?? '')) ?>">

                <p class="hint">Rôle : <?= Moncine\View::escape(Moncine\UserRole::label((string) ($user['role'] ?? ''))) ?></p>
                <?php if ($foyer !== null): ?>
                    <p class="hint">Foyer : <?= Moncine\View::escape((string) ($foyer['nom'] ?? '')) ?>
                        — collection partagée avec les autres membres.</p>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        </div>
    </details>

    <details class="catalog-admin-panel">
        <summary class="catalog-admin-panel__summary">Mot de passe</summary>
        <div class="catalog-admin-panel__body">
            <form method="post" action="/parametres.php" class="import-form auth-form account-form">
                <?php require MONCINE_ROOT . '/templates/_csrf_field.php'; ?>
                <input type="hidden" name="action" value="password">

                <label for="current_password">Mot de passe actuel</label>
                <input type="password" name="current_password" id="current_password" required
                       autocomplete="current-password" maxlength="<?= $maxLen ?>">

                <label for="new_password">Nouveau mot de passe</label>
                <input type="password" name="new_password" id="new_password" required
                       autocomplete="new-password" minlength="<?= $minLen ?>" maxlength="<?= $maxLen ?>">

                <label for="new_password_confirm">Confirmer le nouveau mot de passe</label>
                <input type="password" name="new_password_confirm" id="new_password_confirm" required
                       autocomplete="new-password" minlength="<?= $minLen ?>" maxlength="<?= $maxLen ?>">

                <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
            </form>
        </div>
    </details>

    <p class="collection-page__footer-links">
        <a href="/mes-amis.php">Mes amis</a>
        ·
        <a href="/mes-groupes.php">Mon groupe famille</a>
        ·
        <a href="/rechercher-utilisateurs.php">Rechercher des utilisateurs</a>
        ·
        <a href="/">← Accueil</a>
    </p>
</section>
