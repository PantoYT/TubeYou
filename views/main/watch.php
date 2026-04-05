<?php
function formatNumber($n) {
    if ($n >= 1000000) return round($n/1000000, 1) . 'M';
    if ($n >= 1000) return round($n/1000, 1) . 'K';
    return $n;
}
?>

<div class="watch-layout">

    <!-- MAIN -->
    <div class="watch-main">

        <div class="video-player">
            <video controls>
                <source src="<?= htmlspecialchars($video['src']) ?>" type="video/mp4">
            </video>
        </div>

        <h1><?= htmlspecialchars($video['title']) ?></h1>

        <div class="video-header">

            <div class="channel-info">
                <?= renderAvatar($video['creatorAvatar'] ?? null, '40px') ?>
                <div class="channel-meta">
                    <span class="channel-name">
                        <?= htmlspecialchars($video['creatorName'] ?? 'Unknown') ?>
                    </span>
                    <span class="channel-subs">
                        <?= formatNumber($subCount) ?> subscribers
                    </span>
                </div>
                <?php if (!isset($_SESSION['user']) || (int)$_SESSION['user']['id'] !== (int)$video['userId']): ?>
                    <button
                        id="sub-btn"
                        class="sub-btn <?= $isSubbed ? 'subbed' : '' ?>"
                        data-subscribed-to="<?= (int)$video['userId'] ?>"
                    >
                        <?= $isSubbed ? 'Subscribed' : 'Subscribe' ?>
                    </button>
                <?php endif; ?>
            </div>

            <div class="video-actions">
                <button id="like-btn" class="action-btn" data-video="<?= (int)$video['id'] ?>">
                    <img src="/images/icons/thumb-up<?= $isLiked ? '-filled' : '' ?>.svg">
                </button>
                <span class="action-count"><?= formatNumber($likeCount) ?></span>
                <div class="action-divider"></div>
                <button id="dislike-btn" class="action-btn" data-video="<?= (int)$video['id'] ?>">
                    <img src="/images/icons/thumb-down<?= $isDisliked ? '-filled' : '' ?>.svg">
                </button>
                <span class="action-count"><?= formatNumber($dislikeCount) ?></span>
            </div>

        </div>

        <div class="video-meta-bar">
            <?= formatNumber($video['views'] ?? 0) ?> views •
            <?= date('F j, Y', strtotime($video['createdAt'])) ?>
        </div>

        <div class="video-description">
            <p><?= nl2br(htmlspecialchars($video['description'])) ?></p>
        </div>

    </div>

    <!-- SIDEBAR -->
    <div class="watch-sidebar">
        <p class="watch-sidebar-title">Up next</p>
        <!-- propozycje filmów — następny krok -->
    </div>

</div>

<script>
const csrf = document.querySelector('meta[name="csrf"]').content;

document.getElementById('like-btn')?.addEventListener('click', async () => {
    const videoId = document.getElementById('like-btn').dataset.video;
    const res = await fetch('/like/toggle', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'videoId=' + videoId + '&type=1&csrf_token=' + csrf
    });
    if (res.ok) location.reload();
});

document.getElementById('dislike-btn')?.addEventListener('click', async () => {
    const videoId = document.getElementById('dislike-btn').dataset.video;
    const res = await fetch('/like/toggle', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'videoId=' + videoId + '&type=2&csrf_token=' + csrf
    });
    if (res.ok) location.reload();
});

document.getElementById('sub-btn')?.addEventListener('click', async () => {
    const subscribedToId = document.getElementById('sub-btn').dataset.subscribedTo;
    const res = await fetch('/sub/toggle', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'subscribedToId=' + subscribedToId + '&csrf_token=' + csrf
    });
    if (res.ok) location.reload();
});
</script>