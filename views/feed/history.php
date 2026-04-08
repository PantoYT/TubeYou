<?php $title = 'Watch History'; ?>
<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
        <h2 style="font-size:1.1rem;font-weight:600;">Watch History</h2>
        <?php if (!empty($videos)): ?>
            <form method="POST" action="/history">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="clear">
                <button class="btn btn-danger"
                        onclick="return confirm('Clear all history?')">Clear history</button>
            </form>
        <?php endif; ?>
    </div>
    <?php
        $emptyMsg = 'No watch history yet.';
        include __DIR__ . '/../partials/video_grid.php';
    ?>
</div>