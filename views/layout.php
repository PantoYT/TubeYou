<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'TubeYou' ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="icon" href="/favicon.ico?v=1" type="image/x-icon">
    <meta name="csrf" content="<?= csrfToken() ?>">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-left">
            <a href="/" class="navbar-brand">TubeYou</a>
        </div>
        
        <div class="navbar-center">
            <form action="/search" method="GET" class="search-form">
                <input type="text" name="q" placeholder="Search videos..." required>
                <button type="submit">
                    <img src="/images/icons/search.svg" class="nav-icon nav-icon-white">
                </button>
            </form>
        </div>
        
        <div class="navbar-right">
            <?php if (isset($_SESSION['user'])): ?>
                <a href="/upload" class="btn btn-primary">
                    <img src="/images/icons/plus.svg" class="nav-icon nav-icon-white">
                    Upload
                </a>

                <?php if (isset($_SESSION['user'])): ?>
                    <?php
                        $unread = isset($notifRepo) ? $notifRepo->countUnread($_SESSION['user']['id']) : 0;
                    ?>
                    <a href="/notifications" class="btn" style="position:relative;padding:0 0.6rem;">
                        <img src="/images/icons/<?= $unread > 0 ? 'bell-ringing' : 'bell' ?>.svg" class="nav-icon" style="margin:0;">
                        <?php if ($unread > 0): ?>
                            <span style="
                                position:absolute;top:-4px;right:-4px;
                                background:var(--primary);color:white;
                                border-radius:50%;width:16px;height:16px;
                                font-size:0.65rem;font-weight:700;
                                display:flex;align-items:center;justify-content:center;
                            "><?= min($unread, 99) ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <div class="avatar-menu">
                    <button class="avatar-trigger" id="avatar-trigger">
                        <?= renderAvatar($_SESSION['user']['avatar'] ?? null, '32px') ?>
                    </button>
                    <div class="avatar-dropdown" id="avatar-dropdown">
                        <div class="avatar-dropdown-header">
                            <strong><?= htmlspecialchars($_SESSION['user']['displayName']) ?></strong>
                        </div>
                        <a href="/settings">Settings</a>
                        <button id="theme-toggle" class="dropdown-theme-toggle">
                            <img id="theme-icon" src="/images/icons/moon.svg" class="nav-icon">
                            <span id="theme-label">Dark mode</span>
                        </button>
                        <a href="/logout">Logout</a>
                    </div>
                </div>

            <?php else: ?>
                <button id="theme-toggle" class="btn" style="padding:0.4rem 0.6rem;">
                    <img id="theme-icon" src="/images/icons/moon.svg" class="nav-icon" style="margin:0;">
                </button>
                <a href="/login" class="btn">
                    <img src="/images/icons/login.svg" class="nav-icon">
                    Login
                </a>
                <a href="/register" class="btn btn-primary">
                    <img src="/images/icons/plus.svg" class="nav-icon nav-icon-white">
                    Register
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="app-layout">
        <aside class="sidebar">
            <a href="/" class="sidebar-link <?= $uri === '/' ? 'active' : '' ?>">
                <img src="/images/icons/home.svg" class="nav-icon"> Home
            </a>
            <a href="/shorts" class="sidebar-link">
                <img src="/images/icons/bolt.svg" class="nav-icon"> Shorts
            </a>
            <?php if (isset($_SESSION['user'])): ?>
                <hr class="sidebar-divider">
                <a href="/subscriptions" class="sidebar-link">
                    <img src="/images/icons/bell.svg" class="nav-icon"> Subscriptions
                </a>
                <a href="/history" class="sidebar-link">
                    <img src="/images/icons/clock.svg" class="nav-icon"> History
                </a>
                <a href="/liked" class="sidebar-link">
                    <img src="/images/icons/thumb-up.svg" class="nav-icon"> Liked
                </a>
                <a href="/channel?id=<?= $_SESSION['user']['id'] ?>" class="sidebar-link">
                    <img src="/images/icons/user.svg" class="nav-icon"> Your channel
                </a>
                <a href="/watch-later" class="sidebar-link">
                    <img src="/images/icons/bookmark.svg" class="nav-icon"> Watch Later
                </a>
                <a href="/notifications" class="sidebar-link">
                    <img src="/images/icons/bell.svg" class="nav-icon"> Notifications
                </a>
            <?php endif; ?>
        </aside>
        <main class="container">
            <?= $content ?? '' ?>
        </main>
    </div>

    <footer>
        <p>&copy; 2026 TubeYou. All rights reserved.</p>
    </footer>
</body>
<script>
(function() {
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark');
    }
})();

const toggle = document.getElementById('theme-toggle');
const icon   = document.getElementById('theme-icon');

function updateIcon(isDark) {
    icon.src = isDark ? '/images/icons/sun.svg' : '/images/icons/moon.svg';
}

updateIcon(document.body.classList.contains('dark'));

toggle?.addEventListener('click', () => {
    const isDark = document.body.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    updateIcon(isDark);
});

const avatarTrigger  = document.getElementById('avatar-trigger');
const avatarDropdown = document.getElementById('avatar-dropdown');

avatarTrigger?.addEventListener('click', (e) => {
    e.stopPropagation();
    avatarDropdown.classList.toggle('open');
});

document.addEventListener('click', (e) => {
    if (!e.target.closest('.avatar-menu')) {
        avatarDropdown?.classList.remove('open');
    }
});
</script>
</html>