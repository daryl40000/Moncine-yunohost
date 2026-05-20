<?php
/**
 * @var list<array<string, mixed>> $notifications
 * @var int $unreadCount
 * @var bool $allMarked
 */
?>
<section class="notifications-page">
    <h1>Notifications</h1>
    <p class="lead">
        Alertes sur vos propositions au catalogue et, pour les administrateurs, les nouvelles demandes à traiter.
    </p>

    <?php if ($allMarked): ?>
        <p class="alert alert-success">Toutes les notifications ont été marquées comme lues.</p>
    <?php endif; ?>

    <?php if ($unreadCount > 0): ?>
        <form method="post" action="/marquer-notifications-lues.php" class="inline-form notifications-page__mark-all">
            <?php require MONCINE_ROOT . '/templates/_csrf_field.php'; ?>
            <input type="hidden" name="action" value="all">
            <button type="submit" class="btn btn-secondary">
                Tout marquer comme lu (<?= (int) $unreadCount ?>)
            </button>
        </form>
    <?php endif; ?>

    <?php if ($notifications === []): ?>
        <p class="hint">Aucune notification pour le moment.</p>
    <?php else: ?>
        <ul class="notification-list">
            <?php foreach ($notifications as $note): ?>
                <?php
                $id = (int) ($note['id'] ?? 0);
                $isUnread = ($note['read_at'] ?? null) === null;
                $link = trim((string) ($note['link_url'] ?? ''));
                $href = $id > 0
                    ? '/notifications.php?read=' . $id
                    : ($link !== '' ? $link : '/notifications.php');
                ?>
                <li class="notification-list__item<?= $isUnread ? ' notification-list__item--unread' : '' ?>">
                    <a href="<?= Moncine\View::escape($href) ?>" class="notification-list__link">
                        <span class="notification-list__title"><?= Moncine\View::escape((string) ($note['title'] ?? '')) ?></span>
                        <?php if (trim((string) ($note['body'] ?? '')) !== ''): ?>
                            <span class="notification-list__body"><?= Moncine\View::escape((string) $note['body']) ?></span>
                        <?php endif; ?>
                        <span class="notification-list__date hint"><?= Moncine\View::escape((string) ($note['created_at'] ?? '')) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
