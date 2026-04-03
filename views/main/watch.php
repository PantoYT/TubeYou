<?php $title = htmlspecialchars($video['title']); ?>

<div class="watch-container">
    <div class="video-player">
        <video width="100%" height="600" controls>
            <source src="<?= htmlspecialchars($video['src']) ?>" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
    
    <div class="video-details">
        <h1><?= htmlspecialchars($video['title']) ?></h1>
        <p class="video-meta">By <strong><?= htmlspecialchars($video['creatorName'] ?? 'Unknown') ?></strong> • Posted on <?= date('F j, Y', strtotime($video['createdAt'])) ?></p>
        
        <div class="video-description">
            <p><?= nl2br(htmlspecialchars($video['description'])) ?></p>
        </div>
    </div>
</div>
