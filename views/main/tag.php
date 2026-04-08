<?php $title = '#' . htmlspecialchars($tag); ?>
<div>
    <h2 style="margin-bottom:1rem;font-size:1.1rem;font-weight:600;">
        #<?= htmlspecialchars($tag) ?>
    </h2>
    <?php if (empty($videos)): ?>
        <div class="alert alert-info">No videos with this tag.</div>
    <?php else: ?>
        <?php require dirname(__DIR__) . '/partials/video_grid.php'; ?>
    <?php endif; ?>
</div>