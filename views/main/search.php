<?php $title = 'Search Results'; ?>

<div class="search-results">
    <h1>Results for "<?= htmlspecialchars($query) ?>"</h1>

    <?php if (empty($videos)): ?>
        <div class="alert alert-info">No videos found. Try a different search.</div>
    <?php else: ?>
        <p class="result-count">Found <?= count($videos) ?> video(s)</p>
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
                            <?= renderAvatar($video['creatorAvatar'] ?? null, '24px') ?>
                            <p class="video-meta">
                                <?= htmlspecialchars($video['creatorName'] ?? 'Unknown') ?> •
                                <?= $video['views'] ?? 0 ?> views
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>