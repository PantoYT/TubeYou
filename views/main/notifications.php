<?php $title = 'Notifications'; ?>

<div style="max-width:600px;margin:0 auto;">
    <h2 style="font-size:1.1rem;font-weight:600;margin-bottom:1.5rem;">Notifications</h2>

    <?php if (empty($notifications)): ?>
        <div class="alert alert-info">No notifications yet.</div>
    <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:2px;">
            <?php foreach ($notifications as $n): ?>
                <?php
                    $link = match($n['type']) {
                        'comment', 'reply', 'like' => '/watch?id=' . $n['videoId'] . '#comments',
                        'sub'  => '/channel?id=' . $n['fromUserId'],
                        default => '/'
                    };
                    $text = match($n['type']) {
                        'comment' => 'commented on <strong>' . htmlspecialchars($n['videoTitle'] ?? '') . '</strong>',
                        'reply'   => 'replied to your comment',
                        'like'    => 'liked <strong>' . htmlspecialchars($n['videoTitle'] ?? '') . '</strong>',
                        'sub'     => 'subscribed to your channel',
                        default   => 'interacted with your content'
                    };
                ?>
                <a href="<?= $link ?>" class="notif-item <?= $n['isRead'] ? '' : 'notif-unread' ?>">
                    <?= renderAvatar($n['fromAvatar'] ?? null, '36px', '/channel?id=' . $n['fromUserId']) ?>
                    <div class="notif-body">
                        <span class="notif-name"><?= htmlspecialchars($n['fromName']) ?></span>
                        <?= $text ?>
                        <span class="notif-time"><?= date('M j, g:i a', strtotime($n['createdAt'])) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>