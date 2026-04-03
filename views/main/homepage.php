<?php $title = 'Home'; ?>

<div class="homepage">
    <h1>Latest Videos</h1>
    
    <?php if (empty($videos)): ?>
        <div class="alert alert-info">No videos available. Come back later!</div>
    <?php else: ?>
        <div class="video-grid">
            <?php foreach ($videos as $video): ?>
                <div class="video-card">
                    <a href="/watch?id=<?= $video['id'] ?>" class="video-thumbnail">
                        <img src="<?= htmlspecialchars($video['thumbnail']) ?>" alt="<?= htmlspecialchars($video['title']) ?>">
                        <span class="video-duration"><?= gmdate('i:s', $video['duration'] ?? 0) ?></span>
                    </a>
                    <div class="video-info">
                        <h3><a href="/watch?id=<?= $video['id'] ?>"><?= htmlspecialchars($video['title']) ?></a></h3>
                        <p class="video-meta">
                            <?= htmlspecialchars($video['creatorName'] ?? 'Unknown') ?>
                            <?= $video['views'] ?? 0 ?> views
                            <?= date('M d, Y', strtotime($video['createdAt'])) ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
