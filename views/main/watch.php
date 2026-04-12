<?php require_once __DIR__ . '/../../helpers/formatNumber.php'; ?>

<div class="watch-layout">

    <div class="watch-main">

    <div class="video-player">
        <video id="player" controls>
            <source src="<?= htmlspecialchars($video['src']) ?>" type="video/mp4">
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
                <span class="action-count" id="like-count"><?= formatNumber($likeCount) ?></span>
                <div class="action-divider"></div>
                <button id="dislike-btn" class="action-btn" data-video="<?= (int)$video['id'] ?>">
                    <img src="/images/icons/thumb-down<?= $isDisliked ? '-filled' : '' ?>.svg">
                </button>
                <span class="action-count" id="dislike-count"><?= formatNumber($dislikeCount) ?></span>
                <?php if (isset($_SESSION['user'])): ?>
                    <div class="action-divider"></div>
                    <button id="wl-btn" class="action-btn" data-video="<?= (int)$video['id'] ?>"
                            title="<?= $isWatchLater ? 'Remove from Watch Later' : 'Save to Watch Later' ?>">
                        <img src="/images/icons/<?= $isWatchLater ? 'bookmark-filled' : 'bookmark' ?>.svg">
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="video-meta-bar">
            <?= formatNumber($video['views'] ?? 0) ?> views •
            <?= date('F j, Y', strtotime($video['createdAt'])) ?>
        </div>

        <div class="video-description">
            <p><?= nl2br(htmlspecialchars($video['description'])) ?></p>
        </div>

        <?php if (isset($_SESSION['user']['id']) && (int)$_SESSION['user']['id'] === (int)$video['userId']): ?>
            <div style="display:flex;gap:8px;margin-top:0.75rem;">
                <a href="/video/edit?id=<?= (int)$video['id'] ?>" class="btn">Edit video</a>
                <form method="POST" action="/video/delete"
                    onsubmit="return confirm('Delete this video?')" style="margin:0;">
                    <?= csrfField() ?>
                    <input type="hidden" name="videoId" value="<?= (int)$video['id'] ?>">
                    <button type="submit" class="btn btn-danger">Delete video</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['user']['id']) && !empty($userPlaylists ?? [])): ?>
            <div style="margin-top:8px;">
                <select id="playlist-select" style="height:36px;padding:0 8px;border:1.5px solid var(--border);
                        border-radius:var(--radius);background:var(--surface);color:var(--text);font-size:0.85rem;">
                    <option value="">Add to playlist...</option>
                    <?php foreach ($userPlaylists as $pl): ?>
                        <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button onclick="addToPlaylist()" class="btn" style="margin-left:6px;">Add</button>
            </div>
        <?php endif; ?>

        <?php if (!empty($tags)): ?>
            <div class="video-tags">
                <?php foreach ($tags as $tag): ?>
                    <a href="/tag?name=<?= urlencode($tag) ?>" class="tag-pill">#<?= htmlspecialchars($tag) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="comments-section" id="comments">

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                <h3 class="comments-title" style="margin:0;"><?= formatNumber($commentCount) ?> Comments</h3>
                <div style="display:flex;gap:6px;">
                    <a href="?id=<?= (int)$video['id'] ?>&sort=new<?= $commentPage > 1 ? '&cpage='.$commentPage : '' ?>"
                    class="btn <?= $sort === 'new' ? 'btn-primary' : '' ?>" style="height:30px;font-size:0.8rem;">New</a>
                    <a href="?id=<?= (int)$video['id'] ?>&sort=top<?= $commentPage > 1 ? '&cpage='.$commentPage : '' ?>"
                    class="btn <?= $sort === 'top' ? 'btn-primary' : '' ?>" style="height:30px;font-size:0.8rem;">Top</a>
                </div>
            </div>

            <?php if (isset($_SESSION['user'])): ?>
                <form class="comment-form" id="main-comment-form">
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
                                <!-- Like -->
                                <button class="comment-action-btn comment-like-btn"
                                        data-comment="<?= $comment['id'] ?>"
                                        data-video="<?= (int)$video['id'] ?>"
                                        data-type="1">
                                    <img src="/images/icons/thumb-up<?= (int)($comment['myLike'] ?? 0) === 1 ? '-filled' : '' ?>.svg">
                                    <span class="clikes"><?= formatNumber((int)($comment['likes'] ?? 0)) ?></span>
                                </button>
                                <!-- Dislike -->
                                <button class="comment-action-btn comment-like-btn"
                                        data-comment="<?= $comment['id'] ?>"
                                        data-video="<?= (int)$video['id'] ?>"
                                        data-type="-1">
                                    <img src="/images/icons/thumb-down<?= (int)($comment['myLike'] ?? 0) === -1 ? '-filled' : '' ?>.svg">
                                    <span class="cdislikes"><?= formatNumber((int)($comment['dislikes'] ?? 0)) ?></span>
                                </button>

                                <?php if (isset($_SESSION['user'])): ?>
                                    <button type="button" class="comment-action-btn reply-toggle"
                                            data-parent="<?= $comment['id'] ?>">Reply</button>
                                <?php endif; ?>

                                <?php if (isset($_SESSION['user']) && (int)$_SESSION['user']['id'] === (int)$comment['userId']): ?>
                                    <button class="comment-action-btn edit-toggle"
                                            data-comment="<?= $comment['id'] ?>">Edit</button>
                                    <button class="comment-action-btn comment-delete-btn comment-delete"
                                            data-comment="<?= $comment['id'] ?>"
                                            data-video="<?= (int)$video['id'] ?>">Delete</button>
                                <?php endif; ?>

                                <?php if (isset($_SESSION['user']) && (int)$_SESSION['user']['id'] === (int)$video['userId']): ?>
                                    <button class="comment-action-btn comment-pin-btn"
                                            data-comment="<?= $comment['id'] ?>"
                                            data-video="<?= (int)$video['id'] ?>"
                                            data-pinned="<?= $comment['pinned'] ?>">
                                        <?= $comment['pinned'] ? 'Unpin' : 'Pin' ?>
                                    </button>
                                <?php endif; ?>
                            </div>

                            <div class="edit-form" id="edit-<?= $comment['id'] ?>" style="display:none;margin-top:8px;">
                                <textarea class="comment-input edit-textarea" rows="2"
                                          data-comment="<?= $comment['id'] ?>"
                                          data-video="<?= (int)$video['id'] ?>"><?= htmlspecialchars($comment['content']) ?></textarea>
                                <div class="comment-actions" style="margin-top:6px;">
                                    <button class="btn btn-primary save-edit-btn"
                                            data-comment="<?= $comment['id'] ?>">Save</button>
                                    <button class="btn cancel-edit-btn"
                                            data-comment="<?= $comment['id'] ?>">Cancel</button>
                                </div>
                            </div>

                            <?php if (isset($_SESSION['user'])): ?>
                                <form class="comment-form reply-form" id="reply-<?= $comment['id'] ?>"
                                      style="display:none;margin-top:0.75rem;"
                                      data-parent="<?= $comment['id'] ?>">
                                    <input type="hidden" name="videoId" value="<?= (int)$video['id'] ?>">
                                    <input type="hidden" name="parentId" value="<?= $comment['id'] ?>">
                                    <?= renderAvatar($_SESSION['user']['avatar'] ?? null, '28px', '/channel?id=' . (int)$_SESSION['user']['id']) ?>
                                    <div class="comment-input-wrap">
                                        <textarea name="content" placeholder="Reply..." rows="1" class="comment-input"></textarea>
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
                                                    <button class="comment-action-btn comment-like-btn"
                                                            data-comment="<?= $reply['id'] ?>"
                                                            data-video="<?= (int)$video['id'] ?>"
                                                            data-type="1">
                                                        <img src="/images/icons/thumb-up<?= (int)($reply['myLike'] ?? 0) === 1 ? '-filled' : '' ?>.svg">
                                                        <span class="clikes"><?= formatNumber((int)($reply['likes'] ?? 0)) ?></span>
                                                    </button>
                                                    <button class="comment-action-btn comment-like-btn"
                                                            data-comment="<?= $reply['id'] ?>"
                                                            data-video="<?= (int)$video['id'] ?>"
                                                            data-type="-1">
                                                        <img src="/images/icons/thumb-down<?= (int)($reply['myLike'] ?? 0) === -1 ? '-filled' : '' ?>.svg">
                                                        <span class="cdislikes"><?= formatNumber((int)($reply['dislikes'] ?? 0)) ?></span>
                                                    </button>
                                                    <?php if (isset($_SESSION['user']) && (int)$_SESSION['user']['id'] === (int)$reply['userId']): ?>
                                                        <button class="comment-action-btn comment-delete-btn comment-delete"
                                                                data-comment="<?= $reply['id'] ?>"
                                                                data-video="<?= (int)$video['id'] ?>">Delete</button>
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
const csrf        = document.querySelector('meta[name="csrf"]').content;
const originalSrc = <?= json_encode($video['src']) ?>;
const videoDir    = <?= json_encode(dirname($video['src']) . '/') ?>;
const rawName     = <?= json_encode(pathinfo($video['src'], PATHINFO_FILENAME)) ?>;
const baseName    = rawName.replace(/_\d+p$/, '');
const isOwner     = <?= isset($_SESSION['user']['id']) && (int)$_SESSION['user']['id'] === (int)$video['userId'] ? 'true' : 'false' ?>;
const myId        = <?= (int)($_SESSION['user']['id'] ?? 0) ?>;

// ── Quality ──
function changeQuality(q) {
    const vid = document.getElementById('player');
    const time = vid.currentTime, paused = vid.paused;
    vid.src = videoDir + baseName + '_' + q + '.mp4';
    vid.addEventListener('error', function onErr() {
        vid.src = originalSrc; vid.load(); vid.currentTime = time;
        if (!paused) vid.play();
        vid.removeEventListener('error', onErr);
    }, { once: true });
    vid.load(); vid.currentTime = time;
    if (!paused) vid.play();
    document.querySelectorAll('.quality-btn').forEach(b => b.classList.toggle('active', b.textContent === q));
}

// ── Like / Dislike video ──
const likeBtn      = document.getElementById('like-btn');
const dislikeBtn   = document.getElementById('dislike-btn');
const likeCountEl  = document.getElementById('like-count');
const dislikeCountEl = document.getElementById('dislike-count');

async function toggleLike(type) {
    const likeImg    = likeBtn.querySelector('img');
    const dislikeImg = dislikeBtn.querySelector('img');
    let likes    = parseInt(likeCountEl.textContent.replace(/\D/g,'')) || 0;
    let dislikes = parseInt(dislikeCountEl.textContent.replace(/\D/g,'')) || 0;
    const liked    = likeImg.src.includes('-filled');
    const disliked = dislikeImg.src.includes('-filled');

    if (type === 1) {
        likeImg.src = liked ? '/images/icons/thumb-up.svg' : '/images/icons/thumb-up-filled.svg';
        if (disliked) { dislikeImg.src = '/images/icons/thumb-down.svg'; dislikes--; }
        likes += liked ? -1 : 1;
    } else {
        dislikeImg.src = disliked ? '/images/icons/thumb-down.svg' : '/images/icons/thumb-down-filled.svg';
        if (liked) { likeImg.src = '/images/icons/thumb-up.svg'; likes--; }
        dislikes += disliked ? -1 : 1;
    }
    likeCountEl.textContent = likes;
    dislikeCountEl.textContent = dislikes;

    const res = await fetch('/like/toggle', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `videoId=${likeBtn.dataset.video}&type=${type}&csrf_token=${csrf}`
    });
    if (res.ok) {
        const data = await res.json();
        likeCountEl.textContent    = data.likeCount;
        dislikeCountEl.textContent = data.dislikeCount;
    }
}
likeBtn?.addEventListener('click',    () => toggleLike(1));
dislikeBtn?.addEventListener('click', () => toggleLike(-1));

// ── Sub ──
document.getElementById('sub-btn')?.addEventListener('click', async function() {
    const subsEl   = document.querySelector('.channel-subs');
    let subs       = parseInt(subsEl.textContent.replace(/\D/g,'')) || 0;
    const wasSubbed = this.classList.contains('subbed');
    this.classList.toggle('subbed');
    this.textContent = wasSubbed ? 'Subscribe' : 'Subscribed';
    subs += wasSubbed ? -1 : 1;
    subsEl.textContent = subs + ' subscribers';
    const res = await fetch('/sub/toggle', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `subscribedToId=${this.dataset.subscribedTo}&csrf_token=${csrf}`
    });
    if (res.ok) {
        const data = await res.json();
        if (data.subCount !== undefined) subsEl.textContent = data.subCount + ' subscribers';
    }
});

// ── Watch Later ──
document.getElementById('wl-btn')?.addEventListener('click', async function() {
    const img = this.querySelector('img');
    const wasAdded = img.src.includes('bookmark-filled');
    img.src   = wasAdded ? '/images/icons/bookmark.svg' : '/images/icons/bookmark-filled.svg';
    this.title = wasAdded ? 'Save to Watch Later' : 'Remove from Watch Later';
    const res = await fetch('/watch-later/toggle', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `videoId=${this.dataset.video}&csrf_token=${csrf}`
    });
    if (res.ok) {
        const data = await res.json();
        img.src    = data.added ? '/images/icons/bookmark-filled.svg' : '/images/icons/bookmark.svg';
        this.title = data.added ? 'Remove from Watch Later' : 'Save to Watch Later';
    } else {
        img.src    = wasAdded ? '/images/icons/bookmark-filled.svg' : '/images/icons/bookmark.svg';
        this.title = wasAdded ? 'Remove from Watch Later' : 'Save to Watch Later';
    }
});

// ── Reply toggle ──
document.addEventListener('click', e => {
    if (e.target.closest('.reply-toggle')) {
        const btn  = e.target.closest('.reply-toggle');
        const form = document.getElementById('reply-' + btn.dataset.parent);
        if (form) form.style.display = form.style.display === 'none' ? 'flex' : 'none';
    }
});

// ── Edit toggle ──
document.addEventListener('click', e => {
    if (e.target.closest('.edit-toggle')) {
        const id   = e.target.closest('.edit-toggle').dataset.comment;
        const form = document.getElementById('edit-' + id);
        if (form) form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
});

// ── Cancel edit ──
document.addEventListener('click', e => {
    if (e.target.closest('.cancel-edit-btn')) {
        const id = e.target.closest('.cancel-edit-btn').dataset.comment;
        document.getElementById('edit-' + id).style.display = 'none';
    }
});

// ── Save edit (optimistic) ──
document.addEventListener('click', async e => {
    if (!e.target.closest('.save-edit-btn')) return;
    const id      = e.target.closest('.save-edit-btn').dataset.comment;
    const textarea = document.querySelector(`.edit-textarea[data-comment="${id}"]`);
    const videoId  = textarea.dataset.video;
    const content  = textarea.value.trim();
    if (!content) return;

    // optimistic — update text immediately
    const commentEl = document.getElementById('comment-' + id);
    const contentEl = commentEl.querySelector('.comment-content');
    const oldText   = contentEl.innerHTML;
    contentEl.innerHTML = content.replace(/\n/g, '<br>');
    document.getElementById('edit-' + id).style.display = 'none';

    const res = await fetch('/comment/edit', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `commentId=${id}&videoId=${videoId}&content=${encodeURIComponent(content)}&csrf_token=${csrf}`
    });
    if (!res.ok) contentEl.innerHTML = oldText; // rollback
});

// ── Delete comment (optimistic) ──
document.addEventListener('click', async e => {
    if (!e.target.closest('.comment-delete-btn')) return;
    const btn       = e.target.closest('.comment-delete-btn');
    const id        = btn.dataset.comment;
    const videoId   = btn.dataset.video;
    const commentEl = document.getElementById('comment-' + id);
    if (!commentEl) return;

    // optimistic — hide immediately
    commentEl.style.opacity = '0.4';
    commentEl.style.pointerEvents = 'none';

    const res = await fetch('/comment/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `commentId=${id}&videoId=${videoId}&csrf_token=${csrf}`
    });

    if (res.ok) {
        commentEl.remove();
        const countEl = document.querySelector('.comments-title');
        if (countEl) {
            const n = parseInt(countEl.textContent) || 0;
            countEl.textContent = Math.max(0, n - 1) + ' Comments';
        }
    } else {
        commentEl.style.opacity = '';
        commentEl.style.pointerEvents = '';
    }
});

document.addEventListener('click', async e => {
    if (!e.target.closest('.comment-like-btn')) return;

    const btn        = e.target.closest('.comment-like-btn');
    const commentId  = btn.dataset.comment;
    const videoId    = btn.dataset.video;
    const type       = parseInt(btn.dataset.type);
    const footer     = btn.closest('.comment-footer');
    const likeBtn    = footer.querySelector('[data-type="1"]');
    const dislikeBtn = footer.querySelector('[data-type="-1"]');
    const likeImg    = likeBtn.querySelector('img');
    const dislikeImg = dislikeBtn.querySelector('img');
    const likeCount    = likeBtn.querySelector('.clikes');
    const dislikeCount = dislikeBtn.querySelector('.cdislikes');

    const wasLiked    = likeImg.src.includes('-filled');
    const wasDisliked = dislikeImg.src.includes('-filled');

    if (type === 1) {
        if (wasLiked) {
            likeImg.src = '/images/icons/thumb-up.svg';
            likeCount.textContent = Math.max(0, parseInt(likeCount.textContent) - 1);
        } else {
            likeImg.src = '/images/icons/thumb-up-filled.svg';
            likeCount.textContent = parseInt(likeCount.textContent) + 1;
            if (wasDisliked) {
                dislikeImg.src = '/images/icons/thumb-down.svg';
                dislikeCount.textContent = Math.max(0, parseInt(dislikeCount.textContent) - 1);
            }
        }
    } else {
        if (wasDisliked) {
            dislikeImg.src = '/images/icons/thumb-down.svg';
            dislikeCount.textContent = Math.max(0, parseInt(dislikeCount.textContent) - 1);
        } else {
            dislikeImg.src = '/images/icons/thumb-down-filled.svg';
            dislikeCount.textContent = parseInt(dislikeCount.textContent) + 1;
            if (wasLiked) {
                likeImg.src = '/images/icons/thumb-up.svg';
                likeCount.textContent = Math.max(0, parseInt(likeCount.textContent) - 1);
            }
        }
    }

    const res = await fetch('/comment/like', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `commentId=${commentId}&videoId=${videoId}&type=${type}&csrf_token=${csrf}`
    });

    if (res.ok) {
        const data = await res.json();
        likeCount.textContent    = data.likes;
        dislikeCount.textContent = data.dislikes;
        if (data.userType === 1) {
            likeImg.src    = '/images/icons/thumb-up-filled.svg';
            dislikeImg.src = '/images/icons/thumb-down.svg';
        } else if (data.userType === -1) {
            likeImg.src    = '/images/icons/thumb-up.svg';
            dislikeImg.src = '/images/icons/thumb-down-filled.svg';
        } else {
            likeImg.src    = '/images/icons/thumb-up.svg';
            dislikeImg.src = '/images/icons/thumb-down.svg';
        }
    }
});

// ── Pin comment (optimistic) ──
document.addEventListener('click', async e => {
    if (!e.target.closest('.comment-pin-btn')) return;
    const btn       = e.target.closest('.comment-pin-btn');
    const commentId = btn.dataset.comment;
    const videoId   = btn.dataset.video;
    const wasPinned = btn.dataset.pinned === '1';
    const commentEl = document.getElementById('comment-' + commentId);

    // optimistic
    btn.dataset.pinned = wasPinned ? '0' : '1';
    btn.textContent    = wasPinned ? 'Pin' : 'Unpin';
    commentEl.classList.toggle('comment-pinned', !wasPinned);

    const res = await fetch('/comment/pin', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `commentId=${commentId}&videoId=${videoId}&csrf_token=${csrf}`
    });
    if (!res.ok) {
        btn.dataset.pinned = wasPinned ? '1' : '0';
        btn.textContent    = wasPinned ? 'Unpin' : 'Pin';
        commentEl.classList.toggle('comment-pinned', wasPinned);
    }
});

// ── Main comment submit (optimistic) ──
document.getElementById('main-comment-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const textarea = this.querySelector('textarea');
    const content  = textarea.value.trim();
    const videoId  = this.querySelector('[name=videoId]').value;
    if (!content) return;

    const avatar = <?= json_encode(renderAvatar($_SESSION['user']['avatar'] ?? null, '36px', '/channel?id=' . (int)($_SESSION['user']['id'] ?? 0))) ?>;
    const name   = <?= json_encode($_SESSION['user']['displayName'] ?? '') ?>;
    const now    = new Date().toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'});

    // optimistic — add immediately with temp id
    const tempId = 'temp-' + Date.now();
    const el = document.createElement('div');
    el.className = 'comment';
    el.id = 'comment-' + tempId;
    el.innerHTML = `
        ${avatar}
        <div class="comment-body">
            <div class="comment-header">
                <span class="comment-author">${name}</span>
                <span class="comment-time">${now}</span>
            </div>
            <p class="comment-content">${content.replace(/\n/g, '<br>')}</p>
            <div class="comment-footer"></div>
        </div>
    `;
    document.querySelector('.comments-list').prepend(el);
    textarea.value = '';

    const countEl = document.querySelector('.comments-title');
    if (countEl) countEl.textContent = ((parseInt(countEl.textContent) || 0) + 1) + ' Comments';

    const res = await fetch('/comment/store', {
        method: 'POST',
        body: new URLSearchParams({ videoId, content, csrf_token: csrf })
    });

    if (!res.ok) {
        el.remove();
        if (countEl) countEl.textContent = ((parseInt(countEl.textContent) || 0) - 1) + ' Comments';
    }
    // real id comes on next page load — fine for optimistic
});

// ── Reply submit (optimistic) ──
document.addEventListener('submit', async function(e) {
    if (!e.target.closest('.reply-form')) return;
    e.preventDefault();
    const form     = e.target.closest('.reply-form');
    const textarea = form.querySelector('textarea');
    const content  = textarea.value.trim();
    const videoId  = form.querySelector('[name=videoId]').value;
    const parentId = form.dataset.parent;
    if (!content) return;

    const avatar = <?= json_encode(renderAvatar($_SESSION['user']['avatar'] ?? null, '28px', '/channel?id=' . (int)($_SESSION['user']['id'] ?? 0))) ?>;
    const name   = <?= json_encode($_SESSION['user']['displayName'] ?? '') ?>;
    const now    = new Date().toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'});

    form.style.display = 'none';
    textarea.value = '';

    const parentEl = document.getElementById('comment-' + parentId);
    if (!parentEl) return;
    let repliesEl = parentEl.querySelector('.replies');
    if (!repliesEl) {
        repliesEl = document.createElement('div');
        repliesEl.className = 'replies';
        parentEl.querySelector('.comment-body').appendChild(repliesEl);
    }
    const el = document.createElement('div');
    el.className = 'comment reply';
    el.innerHTML = `
        ${avatar}
        <div class="comment-body">
            <div class="comment-header">
                <span class="comment-author">${name}</span>
                <span class="comment-time">${now}</span>
            </div>
            <p class="comment-content">${content.replace(/\n/g, '<br>')}</p>
            <div class="comment-footer"></div>
        </div>
    `;
    repliesEl.appendChild(el);

    await fetch('/comment/store', {
        method: 'POST',
        body: new URLSearchParams({ videoId, parentId, content, csrf_token: csrf })
    });
});

// ── Keyboard shortcuts ──
const player = document.getElementById('player');
document.addEventListener('keydown', (e) => {
    if (['INPUT','TEXTAREA'].includes(document.activeElement.tagName)) return;
    switch(e.key) {
        case ' ': case 'k': e.preventDefault(); player.paused ? player.play() : player.pause(); break;
        case 'ArrowRight': case 'l': e.preventDefault(); player.currentTime = Math.min(player.duration, player.currentTime + 5); break;
        case 'ArrowLeft':  case 'j': e.preventDefault(); player.currentTime = Math.max(0, player.currentTime - 5); break;
        case 'ArrowUp':    e.preventDefault(); player.volume = Math.min(1, player.volume + 0.1); break;
        case 'ArrowDown':  e.preventDefault(); player.volume = Math.max(0, player.volume - 0.1); break;
        case 'f': e.preventDefault(); document.fullscreenElement ? document.exitFullscreen() : player.requestFullscreen(); break;
        case 'm': e.preventDefault(); player.muted = !player.muted; break;
    }
});

// ── Autoplay + Repeat ──
let repeatOn = localStorage.getItem('repeat') === '1';
const autoplayBar = document.createElement('div');
autoplayBar.style.cssText = 'display:flex;align-items:center;gap:12px;padding:8px 0;font-size:0.85rem;color:var(--text-muted);';
autoplayBar.innerHTML = `
    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
        <input type="checkbox" id="repeat-toggle" ${repeatOn ? 'checked' : ''}>Repeat
    </label>
    <span style="color:var(--border);">|</span>
    <span style="font-size:0.8rem;">Space/K=pause · J/L=±5s · F=fullscreen · M=mute · ↑↓=volume</span>
`;
document.querySelector('.video-player').after(autoplayBar);
document.getElementById('repeat-toggle').addEventListener('change', function() {
    repeatOn = this.checked;
    localStorage.setItem('repeat', repeatOn ? '1' : '0');
    player.loop = repeatOn;
});
player.loop = repeatOn;

<?php if (!empty($suggested)): ?>
const nextVideoUrl = <?= json_encode('/watch?id=' . (int)$suggested[0]['id']) ?>;
player.addEventListener('ended', () => { if (!repeatOn) window.location.href = nextVideoUrl; });
<?php endif; ?>

function addToPlaylist() {
    const select = document.getElementById('playlist-select');
    const playlistId = select.value;
    if (!playlistId) return;
    fetch('/playlist/add-video', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `playlistId=${playlistId}&videoId=<?= (int)$video['id'] ?>&csrf_token=${csrf}`
    }).then(r => r.json()).then(() => { select.value = ''; alert('Added to playlist!'); });
}
</script>