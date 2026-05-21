<?php
/** @var list<array<string, mixed>> $links */
?>
<section>
    <h1>Liens de partage</h1>
    <p class="hint">
        Créez un lien lecture seule pour montrer vos films (collection du foyer) ou vos envies à quelqu’un
        sans compte Moncine. Vous pouvez révoquer un lien à tout moment.
    </p>

    <?php if (!empty($flash)): ?>
        <p class="alert alert-success"><?= Moncine\View::escape($flash) ?></p>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
        <p class="alert alert-warning"><?= Moncine\View::escape($flashError) ?></p>
    <?php endif; ?>
    <?php if (!empty($newShareUrl)): ?>
        <p class="alert alert-success">
            URL du lien :
            <a href="<?= Moncine\View::escape($newShareUrl) ?>"><?= Moncine\View::escape($newShareUrl) ?></a>
        </p>
    <?php endif; ?>

    <section class="share-manage__create">
        <h2>Nouveau lien</h2>
        <form method="post" action="/gerer-partages.php" class="import-form">
            <?= Moncine\View::csrfField() ?>
            <input type="hidden" name="action" value="create">

            <label for="share_scope">Contenu à partager</label>
            <?php $defaultScope = Moncine\ShareLinkScope::normalize($defaultScope ?? Moncine\ShareLinkScope::COLLECTION); ?>
            <select name="scope" id="share_scope" required>
                <option value="<?= Moncine\ShareLinkScope::COLLECTION ?>"
                    <?= $defaultScope === Moncine\ShareLinkScope::COLLECTION ? ' selected' : '' ?>>
                    Mes films (collection du foyer)
                </option>
                <option value="<?= Moncine\ShareLinkScope::WISHLIST ?>"
                    <?= $defaultScope === Moncine\ShareLinkScope::WISHLIST ? ' selected' : '' ?>>
                    Mes envies (liste personnelle)
                </option>
            </select>

            <label for="share_label">Libellé interne (optionnel)</label>
            <input type="text" name="label" id="share_label" maxlength="120"
                   placeholder="Ex. Lien pour la famille">

            <button type="submit" class="btn btn-primary">Créer un lien</button>
        </form>
        <p class="hint">
            Chaque lien expire au bout de 90 jours. Maximum
            <?= (int) Moncine\ShareLinkService::MAX_ACTIVE_LINKS_PER_USER ?> liens actifs par compte.
            L’URL complète n’est affichée qu’une fois à la création.
        </p>
    </section>

    <section class="share-manage__list">
        <h2>Liens actifs</h2>
        <?php if ($links === []): ?>
            <p class="hint">Aucun lien actif pour le moment.</p>
        <?php else: ?>
            <ul class="share-link-list">
                <?php foreach ($links as $link): ?>
                    <?php
                    $scope = Moncine\ShareLinkScope::normalize((string) ($link['scope'] ?? ''));
                    $expires = (string) ($link['expires_at'] ?? '');
                    ?>
                    <li class="share-link-list__item">
                        <strong><?= Moncine\View::escape(Moncine\ShareLinkScope::label($scope)) ?></strong>
                        <?php if (trim((string) ($link['label'] ?? '')) !== ''): ?>
                            — <?= Moncine\View::escape((string) $link['label']) ?>
                        <?php endif; ?>
                        <span class="hint">
                            Créé le <?= Moncine\View::escape((string) ($link['created_at'] ?? '')) ?>
                            <?php if ($expires !== ''): ?>
                                — expire le <?= Moncine\View::escape($expires) ?>
                            <?php endif; ?>
                            — <?= (int) ($link['access_count'] ?? 0) ?> consultation<?= (int) ($link['access_count'] ?? 0) > 1 ? 's' : '' ?>
                        </span>
                        <form method="post" action="/gerer-partages.php" class="inline-form">
                            <?= Moncine\View::csrfField() ?>
                            <input type="hidden" name="action" value="revoke">
                            <input type="hidden" name="link_id" value="<?= (int) ($link['id'] ?? 0) ?>">
                            <button type="submit" class="btn btn-secondary btn--small">Révoquer</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <p><a href="/parametres.php">← Retour aux paramètres</a></p>
</section>
