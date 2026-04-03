<?php $title = 'Search Results'; ?>

<div class="search-results">
    <h1>Search Results for "<?= htmlspecialchars($query) ?>"</h1>
    
    <?php if (empty($videos)): ?>
        <div class="alert alert-info">No videos found for "<?= htmlspecialchars($query) ?>". Try a different search.</div>
    <?php else: ?>
        <p class="result-count">Found <?= count($videos) ?> video(s)</p>
        <div class="video-grid">
            <?php foreach ($videos as $video): ?>
                <div class="video-card">
                    <a href="/watch?id=<?= $video['id'] ?>" class="video-thumbnail">
                        <img src="<?= htmlspecialchars($video['thumbnail']) ?>" alt="<?= htmlspecialchars($video['title']) ?>">
                    </a>
                    <div class="video-info">
                        <h3><a href="/watch?id=<?= $video['id'] ?>"><?= htmlspecialchars($video['title']) ?></a></h3>
                        <p class="video-description"><?= htmlspecialchars(substr($video['description'], 0, 100)) ?>...</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>