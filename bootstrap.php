<?php

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

require_once __DIR__ . '/database/connection.php';
require_once __DIR__ . '/helpers/csrf.php';
require_once __DIR__ . '/helpers/formatNumber.php';
require_once __DIR__ . '/helpers/sanitize.php';
require_once __DIR__ . '/helpers/RateLimiter.php';
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/helpers/cache.php';
require_once __DIR__ . '/views/partials/avatar.php';
require_once __DIR__ . '/views/partials/pagination.php';

$redisHost = $_ENV['REDIS_HOST'] ?? getenv('REDIS_HOST') ?: '';
$redisPort = (int)($_ENV['REDIS_PORT'] ?? getenv('REDIS_PORT') ?: 6379);
if ($redisHost !== '' && redisClient() !== null) {
    ini_set('session.save_handler', 'redis');
    ini_set(
        'session.save_path',
        "tcp://{$redisHost}:{$redisPort}?timeout=1&read_timeout=1&prefix=tubeyou:session:"
    );
    ini_set('redis.session.locking_enabled', '1');
}

session_start();

$db           = Database::getInstance();
$userRepo     = new UserRepository($db);
$videoRepo    = new VideoRepository($db);
$likeRepo     = new LikeRepository($db);
$subRepo      = new SubRepository($db);
$commentRepo  = new CommentRepository($db);
$feedRepo     = new FeedRepository($db);
$tagRepo      = new TagRepository($db);
$notifRepo    = new NotificationRepository($db);
$playlistRepo = new PlaylistRepository($db);
$studioController = new StudioController(
    $videoRepo,
    $subRepo,
    $tagRepo
);
