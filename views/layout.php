<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'TubeYou' ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-left">
            <a href="/" class="navbar-brand">TubeYou</a>
        </div>
        
        <div class="navbar-center">
            <form action="/search" method="GET" class="search-form">
                <input type="text" name="q" placeholder="Search videos..." required>
                <button type="submit">Search</button>
            </form>
        </div>
        
        <div class="navbar-right">
            <?php if (isset($_SESSION['user'])): ?>
                <span class="user-welcome"><?= htmlspecialchars($_SESSION['user']['displayName']) ?></span>
                <a href="/upload" class="btn btn-primary">Upload</a>
                <a href="/logout" class="btn btn-danger">Logout</a>
            <?php else: ?>
                <a href="/login" class="btn">Login</a>
                <a href="/register" class="btn btn-primary">Register</a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="container">
        <?= $content ?? '' ?>
    </main>

    <footer>
        <p>&copy; 2026 TubeYou. All rights reserved.</p>
    </footer>
</body>
</html>