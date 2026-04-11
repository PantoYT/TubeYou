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
                    class="btn <?= $sort === 'new' ? 'btn-primary' : '' ?>" style="height:30px;font-size:0.8rem;">
                        New
                    </a>
                    <a href="?id=<?= (int)$video['id'] ?>&sort=top<?= $commentPage > 1 ? '&cpage='.$commentPage : '' ?>"
                    class="btn <?= $sort === 'top' ? 'btn-primary' : '' ?>" style="height:30px;font-size:0.8rem;">
                        Top
                    </a>
                </div>
            </div>

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
const csrf        = document.querySelector('meta[name="csrf"]').content;
const originalSrc = <?= json_encode($video['src']) ?>;
const videoDir    = <?= json_encode(dirname($video['src']) . '/') ?>;
const baseName    = <?= json_encode(pathinfo($video['src'], PATHINFO_FILENAME)) ?>;

function changeQuality(q) {
    const vid    = document.getElementById('player');
    const time   = vid.currentTime;
    const paused = vid.paused;
    vid.src      = videoDir + baseName + '_' + q + '.mp4';
    vid.addEventListener('error', function onErr() {
        vid.src = originalSrc;
        vid.load();
        vid.currentTime = time;
        if (!paused) vid.play();
        vid.removeEventListener('error', onErr);
    }, { once: true });
    vid.load();
    vid.currentTime = time;
    if (!paused) vid.play();
    document.querySelectorAll('.quality-btn').forEach(b => {
        b.classList.toggle('active', b.textContent === q);
    });
}

// ── Like / Dislike ──
const likeBtn    = document.getElementById('like-btn');
const dislikeBtn = document.getElementById('dislike-btn');
const likeCount    = document.getElementById('like-count');
const dislikeCount = document.getElementById('dislike-count');

async function toggleLike(type) {
    const btn = type === 1 ? likeBtn : dislikeBtn;
    const videoId = btn.dataset.video;

    const likeImg    = likeBtn.querySelector('img');
    const dislikeImg = dislikeBtn.querySelector('img');

    let likes    = parseInt(likeCount.textContent.replace(/\D/g,'')) || 0;
    let dislikes = parseInt(dislikeCount.textContent.replace(/\D/g,'')) || 0;

    const liked = likeImg.src.includes('-filled');
    const disliked = dislikeImg.src.includes('-filled');

    if (type === 1) {
        likeImg.src = liked
            ? '/images/icons/thumb-up.svg'
            : '/images/icons/thumb-up-filled.svg';

        if (disliked) {
            dislikeImg.src = '/images/icons/thumb-down.svg';
            dislikes--;
        }

        likes += liked ? -1 : 1;
    } else {
        dislikeImg.src = disliked
            ? '/images/icons/thumb-down.svg'
            : '/images/icons/thumb-down-filled.svg';

        if (liked) {
            likeImg.src = '/images/icons/thumb-up.svg';
            likes--;
        }

        dislikes += disliked ? -1 : 1;
    }

    likeCount.textContent = likes;
    dislikeCount.textContent = dislikes;

    const res = await fetch('/like/toggle', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `videoId=${videoId}&type=${type}&csrf_token=${csrf}`
    });

    if (!res.ok) {
        location.reload();
        return;
    }

    const data = await res.json();

    likeCount.textContent    = data.likeCount;
    dislikeCount.textContent = data.dislikeCount;
}

likeBtn?.addEventListener('click',    () => toggleLike(1));
dislikeBtn?.addEventListener('click', () => toggleLike(-1));

// ── Sub ──
const subBtn = document.getElementById('sub-btn');

subBtn?.addEventListener('click', async function() {
    const subsEl = document.querySelector('.channel-subs');

    let subs = parseInt(subsEl.textContent.replace(/\D/g,'')) || 0;
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

    if (!res.ok) {
        location.reload();
        return;
    }

    const data = await res.json();

    if (data.subCount !== undefined) {
        subsEl.textContent = data.subCount + ' subscribers';
    }
});

// ── Watch Later ──
document.getElementById('wl-btn')?.addEventListener('click', async function() {
    const btn = this;
    const img = btn.querySelector('img');

    const wasAdded = img.src.includes('bookmark-filled');

    // ── optimistic UI ──
    img.src = wasAdded
        ? '/images/icons/bookmark.svg'
        : '/images/icons/bookmark-filled.svg';

    btn.title = wasAdded
        ? 'Save to Watch Later'
        : 'Remove from Watch Later';

    const res = await fetch('/watch-later/toggle', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `videoId=${btn.dataset.video}&csrf_token=${csrf}`
    });

    if (!res.ok) {
        // rollback if failed
        img.src = wasAdded
            ? '/images/icons/bookmark-filled.svg'
            : '/images/icons/bookmark.svg';

        btn.title = wasAdded
            ? 'Remove from Watch Later'
            : 'Save to Watch Later';

        return;
    }

    const data = await res.json();

    // backend truth (just in case)
    img.src = data.added
        ? '/images/icons/bookmark-filled.svg'
        : '/images/icons/bookmark.svg';

    btn.title = data.added
        ? 'Remove from Watch Later'
        : 'Save to Watch Later';
});

// ── Reply toggle ──
document.querySelectorAll('.reply-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const form = document.getElementById('reply-' + btn.dataset.parent);
        if (!form) return;
        form.style.display = form.style.display === 'none' ? 'flex' : 'none';
    });
});

// ── Edit toggle ──
document.querySelectorAll('.edit-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const form = document.getElementById('edit-' + btn.dataset.comment);
        if (!form) return;
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    });
});

// ── Comment submit (AJAX) ──
document.querySelector('.comment-form:not(.reply-form)')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const form    = this;
    const data    = new FormData(form);
    const content = data.get('content')?.trim();
    if (!content) return;

    const res = await fetch('/comment/store', {
        method: 'POST',
        body: new URLSearchParams({
            videoId:    data.get('videoId'),
            content:    content,
            csrf_token: csrf
        })
    });

    if (!res.ok) return;

    const textarea = form.querySelector('textarea');
    textarea.value = '';

    const avatar  = <?= json_encode(renderAvatar($_SESSION['user']['avatar'] ?? null, '36px', '/channel?id=' . (int)($_SESSION['user']['id'] ?? 0))) ?>;
    const name    = <?= json_encode($_SESSION['user']['displayName'] ?? '') ?>;
    const now     = new Date().toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'});

    const el = document.createElement('div');
    el.className = 'comment';
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

    const list = document.querySelector('.comments-list');
    list.prepend(el);

    const countEl = document.querySelector('.comments-title');
    if (countEl) {
        const n = parseInt(countEl.textContent) || 0;
        countEl.textContent = (n + 1) + ' Comments';
    }
});

// ── Reply submit (AJAX) ──
document.querySelectorAll('.reply-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const data    = new FormData(this);
        const content = data.get('content')?.trim();
        if (!content) return;

        const res = await fetch('/comment/store', {
            method: 'POST',
            body: new URLSearchParams({
                videoId:    data.get('videoId'),
                parentId:   data.get('parentId'),
                content:    content,
                csrf_token: csrf
            })
        });

        if (!res.ok) return;

        this.style.display = 'none';
        this.querySelector('textarea').value = '';

        const avatar = <?= json_encode(renderAvatar($_SESSION['user']['avatar'] ?? null, '28px', '/channel?id=' . (int)($_SESSION['user']['id'] ?? 0))) ?>;
        const name   = <?= json_encode($_SESSION['user']['displayName'] ?? '') ?>;
        const now    = new Date().toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'});

        const parentId = data.get('parentId');
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
    });
});
</script>