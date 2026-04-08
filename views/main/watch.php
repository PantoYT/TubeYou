<?php require_once __DIR__ . '/../../helpers/formatNumber.php'; ?>

<div class="watch-layout">

    <div class="watch-main">

        <div class="video-player">
            <video id="player" controls>
                <source src="<?= htmlspecialchars($video['src']) ?>" type="video/mp4">
                <source src="<?= htmlspecialchars(preg_replace('/\.mp4$/', '_720p.mp4', $video['src'])) ?>" type="video/mp4">
                <source src="<?= htmlspecialchars(preg_replace('/\.mp4$/', '_480p.mp4', $video['src'])) ?>" type="video/mp4">
            </video>
            <div class="quality-selector">
                <?php foreach (['1080p', '720p', '480p', '360p'] as $q): ?>
                    <button class="quality-btn" onclick="changeQuality('<?= $q ?>')"><?= $q ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <h1><?= htmlspecialchars($video['title']) ?></h1>

        <div class="video-header">
            <div class="channel-info">
                <?= renderAvatar($video['creatorAvatar'] ?? null, '40px', '/channel?id=' . (int)$video['userId']) ?>
                <div class="channel-meta">
                    <span class="channel-name"><?= htmlspecialchars($video['creatorName'] ?? 'Unknown') ?></span>
                    <span class="channel-subs"><?= formatNumber($subCount) ?> subscribers</span>
                </div>
                <?php if (!isset($_SESSION['user']) || (int)$_SESSION['user']['id'] !== (int)$video['userId']): ?>
                    <button id="sub-btn" class="sub-btn <?= $isSubbed ? 'subbed' : '' ?>"
                            data-subscribed-to="<?= (int)$video['userId'] ?>">
                        <?= $isSubbed ? 'Subscribed' : 'Subscribe' ?>
                    </button>
                <?php endif; ?>
            </div>

            <div class="video-actions">
                <button id="like-btn" class="action-btn" data-video="<?= (int)$video['id'] ?>">
                    <img src="/images/icons/thumb-up<?= $isLiked ? '-filled' : '' ?>.svg">
                </button>
                <span class="action-count"><?= formatNumber($likeCount) ?></span>
                <div class="action-divider"></div>
                <button id="dislike-btn" class="action-btn" data-video="<?= (int)$video['id'] ?>">
                    <img src="/images/icons/thumb-down<?= $isDisliked ? '-filled' : '' ?>.svg">
                </button>
                <span class="action-count"><?= formatNumber($dislikeCount) ?></span>
            </div>
        </div>

        <div class="video-meta-bar">
            <?= formatNumber($video['views'] ?? 0) ?> views •
            <?= date('F j, Y', strtotime($video['createdAt'])) ?>
        </div>

        <div class="video-description">
            <p><?= nl2br(htmlspecialchars($video['description'])) ?></p>
        </div>

        <?php if (!empty($tags)): ?>
            <div class="video-tags">
                <?php foreach ($tags as $tag): ?>
                    <a href="/tag?name=<?= urlencode($tag) ?>" class="tag-pill">#<?= htmlspecialchars($tag) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="comments-section" id="comments">

            <h3 class="comments-title"><?= formatNumber($commentCount) ?> Comments</h3>

            <?php if (isset($_SESSION['user'])): ?>
                <form method="POST" action="/comment/store" class="comment-form">
                    <?= csrfField() ?>
                    <input type="hidden" name="videoId" value="<?= (int)$video['id'] ?>">
                    <?= renderAvatar($_SESSION['user']['avatar'] ?? null, '36px', '/channel?id=' . (int)$_SESSION['user']['id']) ?>
                    <div class="comment-input-wrap">
                        <textarea name="content" placeholder="Add a comment..." rows="1" class="comment-input"></textarea>
                        <div class="comment-actions">
                            <button type="submit" class="btn btn-primary">Comment</button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>

            <div class="comments-list">
                <?php foreach ($comments as $comment): ?>
                    <div class="comment <?= $comment['pinned'] ? 'comment-pinned' : '' ?>"
                         id="comment-<?= $comment['id'] ?>">

                        <?= renderAvatar($comment['avatar'] ?? null, '36px', '/channel?id=' . (int)$comment['userId']) ?>

                        <div class="comment-body">
                            <div class="comment-header">
                                <span class="comment-author"><?= htmlspecialchars($comment['displayName']) ?></span>
                                <span class="comment-time"><?= date('M j, Y', strtotime($comment['createdAt'])) ?></span>
                                <?php if ($comment['pinned']): ?>
                                    <span class="comment-pin-badge">📌 Pinned</span>
                                <?php endif; ?>
                            </div>

                            <p class="comment-content"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>

                            <div class="comment-footer">
                                <form method="POST" action="/comment/like" class="inline-form">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="commentId" value="<?= $comment['id'] ?>">
                                    <input type="hidden" name="videoId"   value="<?= (int)$video['id'] ?>">
                                    <input type="hidden" name="type"      value="1">
                                    <button type="submit" class="comment-action-btn">
                                        <img src="/images/icons/thumb-up.svg">
                                        <?= formatNumber((int)($comment['likes'] ?? 0)) ?>
                                    </button>
                                </form>

                                <form method="POST" action="/comment/like" class="inline-form">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="commentId" value="<?= $comment['id'] ?>">
                                    <input type="hidden" name="videoId"   value="<?= (int)$video['id'] ?>">
                                    <input type="hidden" name="type"      value="-1">
                                    <button type="submit" class="comment-action-btn">
                                        <img src="/images/icons/thumb-down.svg">
                                        <?= formatNumber((int)($comment['dislikes'] ?? 0)) ?>
                                    </button>
                                </form>

                                <?php if (isset($_SESSION['user'])): ?>
                                    <button type="button" class="comment-action-btn reply-toggle"
                                            data-parent="<?= $comment['id'] ?>">Reply</button>
                                <?php endif; ?>

                                <?php if (isset($_SESSION['user']) && (int)$_SESSION['user']['id'] === (int)$comment['userId']): ?>
                                    <button class="comment-action-btn edit-toggle"
                                            data-comment="<?= $comment['id'] ?>">Edit</button>

                                    <form method="POST" action="/comment/delete" class="inline-form">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="commentId" value="<?= $comment['id'] ?>">
                                        <input type="hidden" name="videoId"   value="<?= (int)$video['id'] ?>">
                                        <button type="submit" class="comment-action-btn comment-delete">Delete</button>
                                    </form>
                                <?php endif; ?>

                                <?php if (isset($_SESSION['user']) && (int)$_SESSION['user']['id'] === (int)$video['userId']): ?>
                                    <form method="POST" action="/comment/pin" class="inline-form">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="commentId" value="<?= $comment['id'] ?>">
                                        <input type="hidden" name="videoId"   value="<?= (int)$video['id'] ?>">
                                        <button type="submit" class="comment-action-btn">
                                            <?= $comment['pinned'] ? 'Unpin' : 'Pin' ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>

                            <div class="edit-form" id="edit-<?= $comment['id'] ?>" style="display:none;margin-top:8px;">
                                <form method="POST" action="/comment/edit">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="commentId" value="<?= $comment['id'] ?>">
                                    <input type="hidden" name="videoId"   value="<?= (int)$video['id'] ?>">
                                    <textarea name="content" class="comment-input"
                                              rows="2"><?= htmlspecialchars($comment['content']) ?></textarea>
                                    <div class="comment-actions" style="margin-top:6px;">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </form>
                            </div>

                            <?php if (isset($_SESSION['user'])): ?>
                                <form method="POST" action="/comment/store"
                                      class="comment-form reply-form" id="reply-<?= $comment['id'] ?>"
                                      style="display:none;margin-top:0.75rem;">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="videoId"  value="<?= (int)$video['id'] ?>">
                                    <input type="hidden" name="parentId" value="<?= $comment['id'] ?>">
                                    <?= renderAvatar($_SESSION['user']['avatar'] ?? null, '28px', '/channel?id=' . (int)$_SESSION['user']['id']) ?>
                                    <div class="comment-input-wrap">
                                        <textarea name="content" placeholder="Reply..." rows="1"
                                                  class="comment-input"></textarea>
                                        <div class="comment-actions">
                                            <button type="submit" class="btn btn-primary">Reply</button>
                                        </div>
                                    </div>
                                </form>
                            <?php endif; ?>

                            <?php if (!empty($comment['replies'])): ?>
                                <div class="replies">
                                    <?php foreach ($comment['replies'] as $reply): ?>
                                        <div class="comment reply" id="comment-<?= $reply['id'] ?>">
                                            <?= renderAvatar($reply['avatar'] ?? null, '28px', '/channel?id=' . (int)$reply['userId']) ?>
                                            <div class="comment-body">
                                                <div class="comment-header">
                                                    <span class="comment-author"><?= htmlspecialchars($reply['displayName']) ?></span>
                                                    <span class="comment-time"><?= date('M j, Y', strtotime($reply['createdAt'])) ?></span>
                                                </div>
                                                <p class="comment-content"><?= nl2br(htmlspecialchars($reply['content'])) ?></p>
                                                <div class="comment-footer">
                                                    <form method="POST" action="/comment/like" class="inline-form">
                                                        <?= csrfField() ?>
                                                        <input type="hidden" name="commentId" value="<?= $reply['id'] ?>">
                                                        <input type="hidden" name="videoId"   value="<?= (int)$video['id'] ?>">
                                                        <input type="hidden" name="type"      value="1">
                                                        <button type="submit" class="comment-action-btn">
                                                            <img src="/images/icons/thumb-up.svg">
                                                            <?= formatNumber((int)($reply['likes'] ?? 0)) ?>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="/comment/like" class="inline-form">
                                                        <?= csrfField() ?>
                                                        <input type="hidden" name="commentId" value="<?= $reply['id'] ?>">
                                                        <input type="hidden" name="videoId"   value="<?= (int)$video['id'] ?>">
                                                        <input type="hidden" name="type"      value="-1">
                                                        <button type="submit" class="comment-action-btn">
                                                            <img src="/images/icons/thumb-down.svg">
                                                            <?= formatNumber((int)($reply['dislikes'] ?? 0)) ?>
                                                        </button>
                                                    </form>
                                                    <?php if (isset($_SESSION['user']) && (int)$_SESSION['user']['id'] === (int)$reply['userId']): ?>
                                                        <form method="POST" action="/comment/delete" class="inline-form">
                                                            <?= csrfField() ?>
                                                            <input type="hidden" name="commentId" value="<?= $reply['id'] ?>">
                                                            <input type="hidden" name="videoId"   value="<?= (int)$video['id'] ?>">
                                                            <button type="submit" class="comment-action-btn comment-delete">Delete</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>

        <?= renderPagination($commentPage, $commentPages, '/watch?id=' . (int)$video['id'] . '&cpage=') ?>

    </div>

    <div class="watch-sidebar">
        <p class="watch-sidebar-title">Up next</p>
        <div class="suggested-list">
            <?php foreach ($suggested as $s): ?>
                <a href="/watch?id=<?= $s['id'] ?>" class="suggested-card">
                    <div class="suggested-thumb">
                        <img src="<?= htmlspecialchars($s['thumbnail']) ?>"
                             alt="<?= htmlspecialchars($s['title']) ?>">
                        <span class="video-duration"><?= gmdate('i:s', $s['duration'] ?? 0) ?></span>
                    </div>
                    <div class="suggested-info">
                        <p class="suggested-title"><?= htmlspecialchars($s['title']) ?></p>
                        <p class="suggested-meta">
                            <?= htmlspecialchars($s['creatorName']) ?> •
                            <?= formatNumber($s['views']) ?> views
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<script>
const csrf = document.querySelector('meta[name="csrf"]').content;
const originalSrc = <?= json_encode($video['src']) ?>;
const videoBase   = <?= json_encode(preg_replace('/[^\/]+\.mp4$/', '', $video['src'])) ?>;

function changeQuality(q) {
    const video  = document.getElementById('player');
    const time   = video.currentTime;
    const paused = video.paused;
    video.src = videoBase + '_' + q + '.mp4';
    video.addEventListener('error', function onErr() {
        video.src = originalSrc;
        video.currentTime = time;
        if (!paused) video.play();
        video.removeEventListener('error', onErr);
    }, { once: true });
    video.currentTime = time;
    if (!paused) video.play();
}

document.querySelectorAll('.reply-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const form = document.getElementById('reply-' + btn.dataset.parent);
        if (!form) return;
        form.style.display = form.style.display === 'none' ? 'flex' : 'none';
    });
});

document.querySelectorAll('.edit-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const form = document.getElementById('edit-' + btn.dataset.comment);
        if (!form) return;
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    });
});

document.getElementById('like-btn')?.addEventListener('click', async () => {
    const videoId = document.getElementById('like-btn').dataset.video;
    const res = await fetch('/like/toggle', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'videoId=' + videoId + '&type=1&csrf_token=' + csrf
    });
    if (res.ok) location.reload();
});

document.getElementById('dislike-btn')?.addEventListener('click', async () => {
    const videoId = document.getElementById('dislike-btn').dataset.video;
    const res = await fetch('/like/toggle', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'videoId=' + videoId + '&type=-1&csrf_token=' + csrf
    });
    if (res.ok) location.reload();
});

document.getElementById('sub-btn')?.addEventListener('click', async () => {
    const subscribedToId = document.getElementById('sub-btn').dataset.subscribedTo;
    const res = await fetch('/sub/toggle', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'subscribedToId=' + subscribedToId + '&csrf_token=' + csrf
    });
    if (res.ok) location.reload();
});
</script>