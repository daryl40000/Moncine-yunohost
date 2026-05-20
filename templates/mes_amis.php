<?php
/**
 * @var list<array<string, mixed>> $friends
 * @var list<array<string, mixed>> $pendingReceived
 * @var list<array<string, mixed>> $pendingSent
 * @var bool $socialAvailable
 * @var string $error
 * @var string $success
 */
?>
<section class="account-page social-page">
    <h1>Mes amis</h1>
    <p class="lead">
        Gérez vos amis Moncine. Les amis peuvent ensuite créer ou rejoindre un groupe famille ensemble.
    </p>

    <?php if ($success !== ''): ?>
        <p class="alert alert-success"><?= Moncine\View::escape($success) ?></p>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <p class="alert alert-warning"><?= Moncine\View::escape($error) ?></p>
    <?php endif; ?>

    <?php if (!$socialAvailable): ?>
        <p class="hint">La fonctionnalité amis n’est pas encore activée sur ce serveur (migration en attente).</p>
    <?php else: ?>

        <p class="collection-page__footer-links">
            <a href="/rechercher-utilisateurs.php">Rechercher des utilisateurs</a>
        </p>

        <?php if ($pendingReceived !== []): ?>
            <h2>Demandes reçues</h2>
            <ul class="user-search-results">
                <?php foreach ($pendingReceived as $row): ?>
                    <?php $fid = (int) ($row['id'] ?? 0); ?>
                    <li class="user-search-results__item">
                        <span class="user-search-results__name">
                            <?= Moncine\View::escape(Moncine\UserProfile::displayName($row)) ?>
                        </span>
                        <form method="post" action="/mes-amis.php" class="inline-form">
                            <?php require MONCINE_ROOT . '/templates/_csrf_field.php'; ?>
                            <input type="hidden" name="action" value="accept">
                            <input type="hidden" name="friendship_id" value="<?= $fid ?>">
                            <button type="submit" class="btn btn-primary btn-sm">Accepter</button>
                        </form>
                        <form method="post" action="/mes-amis.php" class="inline-form">
                            <?php require MONCINE_ROOT . '/templates/_csrf_field.php'; ?>
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="friendship_id" value="<?= $fid ?>">
                            <button type="submit" class="btn btn-secondary btn-sm">Refuser</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if ($pendingSent !== []): ?>
            <h2>Demandes envoyées</h2>
            <ul class="user-search-results">
                <?php foreach ($pendingSent as $row): ?>
                    <?php $fid = (int) ($row['id'] ?? 0); ?>
                    <li class="user-search-results__item">
                        <span class="user-search-results__name">
                            <?= Moncine\View::escape(Moncine\UserProfile::displayName($row)) ?>
                        </span>
                        <span class="user-search-results__meta">En attente</span>
                        <form method="post" action="/mes-amis.php" class="inline-form">
                            <?php require MONCINE_ROOT . '/templates/_csrf_field.php'; ?>
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="friendship_id" value="<?= $fid ?>">
                            <button type="submit" class="btn btn-secondary btn-sm">Annuler</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <h2>Mes amis (<?= count($friends) ?>)</h2>
        <?php if ($friends === []): ?>
            <p class="hint">Vous n’avez pas encore d’ami. Utilisez la recherche pour en trouver.</p>
        <?php else: ?>
            <ul class="user-search-results">
                <?php foreach ($friends as $row): ?>
                    <li class="user-search-results__item">
                        <span class="user-search-results__name">
                            <?= Moncine\View::escape(Moncine\UserProfile::displayName($row)) ?>
                        </span>
                        <?php if (trim((string) ($row['ville'] ?? '')) !== ''): ?>
                            <span class="user-search-results__meta"><?= Moncine\View::escape((string) $row['ville']) ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>

    <p class="collection-page__footer-links">
        <a href="/mes-groupes.php">Mon groupe famille</a>
        ·
        <a href="/">← Accueil</a>
    </p>
</section>
