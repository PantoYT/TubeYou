<?php $title = htmlspecialchars($channel['displayName']); ?>

<div class="channel-page">
    <div class="channel-banner">
        <?php if ($channel['banner'] ?? null): ?>
            <img src="<?= htmlspecialchars($channel['banner']) ?>" alt="banner">
        <?php endif; ?>
    </div>

    <div class="channel-header">
        <?= renderAvatar($channel['avatar'] ?? null, '80px') ?>
        <div class="channel-header-info">
            <h1><?= htmlspecialchars($channel['displayName']) ?></h1>
            <span class="channel-subs"><?= formatNumber($subCount) ?> subscribers</span>
            <?php if ($channel['bio'] ?? null): ?>
                <p class="channel-bio"><?= nl2br(htmlspecialchars($channel['bio'])) ?></p>
            <?php endif; ?>
        </div>
        <?php if (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] !== (int)$channel['id']): ?>
            <button class="sub-btn <?= $isSubbed ? 'subbed' : '' ?>"
                    id="sub-btn"
                    data-subscribed-to="<?= (int)$channel['id'] ?>">
                <?= $isSubbed ? 'Subscribed' : 'Subscribe' ?>
            </button>
        <?php elseif (isset($_SESSION['user']['id']) && $_SESSION['user']['id'] === (int)$channel['id']): ?>
            <a href="/settings" class="btn">Edit channel</a>
        <?php endif; ?>
    </div>

    <div class="video-grid" style="margin-top:2rem;">
        <?php foreach ($videos as $video): ?>
            <div class="video-card">
                <a href="/watch?id=<?= $video['id'] ?>" class="video-thumbnail">
                    <img src="<?= htmlspecialchars($video['thumbnail']) ?>"
                         alt="<?= htmlspecialchars($video['title']) ?>">
                    <span class="video-duration"><?= gmdate('i:s', $video['duration'] ?? 0) ?></span>
                </a>
                <div class="video-info">
                    <h3><a href="/watch?id=<?= $video['id'] ?>"><?= htmlspecialchars($video['title']) ?></a></h3>
                    <p class="video-meta">
                        <?= formatNumber($video['views'] ?? 0) ?> views •
                        <?= date('M d, Y', strtotime($video['createdAt'])) ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
const csrf = document.querySelector('meta[name="csrf"]').content;
document.getElementById('sub-btn')?.addEventListener('click', async () => {
    const id  = document.getElementById('sub-btn').dataset.subscribedTo;
    const res = await fetch('/sub/toggle', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'subscribedToId=' + id + '&csrf_token=' + csrf
    });
    if (res.ok) location.reload();
});
</script>