<?php 
$title = 'Home';
require_once __DIR__ . '/../../helpers/formatNumber.php';
?>
<div class="homepage">
    <?php if (empty($videos)): ?>
        <div class="alert alert-info">No videos found.</div>
    <?php else: ?>
        <?php require dirname(__DIR__) . '/partials/video_grid.php'; ?>
    <?php endif; ?>

    <?= renderPagination($page, $pages, '/?page=') ?>
    
</div>