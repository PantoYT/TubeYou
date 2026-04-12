<?php $title = htmlspecialchars($playlist['title']); ?>

<div>
    <div style="margin-bottom:1.5rem;">
        <h2 style="font-size:1.2rem;font-weight:700;"><?= htmlspecialchars($playlist['title']) ?></h2>
        <?php if ($playlist['description']): ?>
            <p style="color:var(--text-muted);font-size:0.9rem;margin-top:4px;">
                <?= htmlspecialchars($playlist['description']) ?>
            </p>
        <?php endif; ?>
        <p style="font-size:0.82rem;color:var(--text-muted);margin-top:4px;">
            <?= count($videos) ?> videos
        </p>
    </div>

    <?php if (empty($videos)): ?>
        <div class="alert alert-info">No videos in this playlist.</div>
    <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:8px;">
            <?php foreach ($videos as $i => $video): ?>
                <div style="display:flex;gap:12px;align-items:center;
                            padding:8px;background:var(--surface);
                            border:1.5px solid var(--border);border-radius:var(--radius);">
                    <span style="font-size:0.85rem;color:var(--text-muted);
                                 min-width:24px;text-align:center;">
                        <?= $i + 1 ?>
                    </span>
                    <a href="/watch?id=<?= $video['id'] ?>" style="flex-shrink:0;">
                        <div style="width:100px;aspect-ratio:16/9;border-radius:4px;overflow:hidden;background:#000;">
                            <img src="<?= htmlspecialchars($video['thumbnail']) ?>"
                                 style="width:100%;height:100%;object-fit:cover;" loading="lazy">
                        </div>
                    </a>
                    <div style="flex:1;min-width:0;">
                        <a href="/watch?id=<?= $video['id'] ?>"
                           style="font-weight:600;font-size:0.9rem;text-decoration:none;color:var(--text);">
                            <?= htmlspecialchars($video['title']) ?>
                        </a>
                        <p style="font-size:0.8rem;color:var(--text-muted);margin-top:2px;">
                            <?= htmlspecialchars($video['creatorName']) ?>
                        </p>
                    </div>
                    <?php if (isset($_SESSION['user']['id']) && (int)$_SESSION['user']['id'] === (int)$playlist['userId']): ?>
                        <form method="POST" action="/playlist/remove-video" style="margin:0;">
                            <?= csrfField() ?>
                            <input type="hidden" name="playlistId" value="<?= $playlist['id'] ?>">
                            <input type="hidden" name="videoId"    value="<?= $video['id'] ?>">
                            <button type="submit" class="comment-action-btn comment-delete">Remove</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>