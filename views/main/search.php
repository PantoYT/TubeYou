<?php $title = 'Search Results'; ?>

<div class="search-results">
    <h1>Results for "<?= htmlspecialchars($query) ?>"</h1>

    <p class="result-count">Found <?= count($videos) ?> video(s)</p>

    <?php if (empty($videos)): ?>
        <div class="alert alert-info">No videos found.</div>
    <?php else: ?>
        <?php require dirname(__DIR__) . '/partials/video_grid.php'; ?>
    <?php endif; ?>

    <?= renderPagination($page, $pages, '/?page=') ?>

</div>