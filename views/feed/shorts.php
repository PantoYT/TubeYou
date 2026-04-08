<?php $title = 'Shorts'; ?>
<div>
    <h2 style="margin-bottom:1rem;font-size:1.1rem;font-weight:600;">Shorts</h2>
    <?php if (empty($videos)): ?>
        <div class="alert alert-info">No shorts yet. Upload a video under 60 seconds!</div>
    <?php else: ?>
        <div class="shorts-grid">
            <?php foreach ($videos as $video): ?>
                <a href="/watch?id=<?= $video['id'] ?>" class="short-card">
                    <div class="short-thumb">
                        <img src="<?= htmlspecialchars($video['thumbnail']) ?>"
                             alt="<?= htmlspecialchars($video['title']) ?>">
                        <span class="video-duration"><?= gmdate('i:s', $video['duration'] ?? 0) ?></span>
                    </div>
                    <p class="short-title"><?= htmlspecialchars($video['title']) ?></p>
                    <p class="short-meta"><?= formatNumber($video['views'] ?? 0) ?> views</p>
                </a>
            <?php endforeach; ?>
        </div>
        <?= renderPagination($page, $pages, '/shorts?page=') ?>
    <?php endif; ?>
</div>