<?php $title = 'Watch Later'; ?>
<div>
    <h2 style="margin-bottom:1rem;font-size:1.1rem;font-weight:600;">Watch Later</h2>
    <?php if (empty($videos)): ?>
        <div class="alert alert-info">Nothing saved yet.</div>
    <?php else: ?>
        <div class="video-grid">
            <?php foreach ($videos as $video): ?>
                <div class="video-card">
                    <a href="/watch?id=<?= $video['id'] ?>" class="video-thumbnail">
                        <img src="<?= htmlspecialchars($video['thumbnail']) ?>"
                             alt="<?= htmlspecialchars($video['title']) ?>">
                        <span class="video-duration"><?= gmdate('i:s', $video['duration'] ?? 0) ?></span>
                    </a>
                    <div class="video-info">
                        <h3><a href="/watch?id=<?= $video['id'] ?>"><?= htmlspecialchars($video['title']) ?></a></h3>
                        <div class="video-card-meta">
                            <?= renderAvatar($video['creatorAvatar'] ?? null, '24px', '/channel?id=' . (int)$video['userId']) ?>
                            <p class="video-meta">
                                <?= htmlspecialchars($video['creatorName'] ?? 'Unknown') ?> •
                                <?= formatNumber($video['views'] ?? 0) ?> views
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>