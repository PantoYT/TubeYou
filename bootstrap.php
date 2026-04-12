<?php

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

require_once __DIR__ . '/database/connection.php';
require_once __DIR__ . '/helpers/csrf.php';
require_once __DIR__ . '/helpers/formatNumber.php';
require_once __DIR__ . '/helpers/sanitize.php';
require_once __DIR__ . '/helpers/RateLimiter.php';
require_once __DIR__ . '/views/partials/avatar.php';
require_once __DIR__ . '/views/partials/pagination.php';

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
    $likeRepo,
    $commentRepo,
    $subRepo,
    $tagRepo
);