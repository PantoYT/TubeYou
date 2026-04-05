<?php

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once __DIR__ . '/database/connection.php';
require_once __DIR__ . '/helpers/csrf.php';
require_once __DIR__ . '/views/partials/avatar.php';
require_once __DIR__ . '/services/MailService.php';

foreach (glob(__DIR__ . '/models/*.php') as $file) require_once $file;
foreach (glob(__DIR__ . '/controllers/*.php') as $file) require_once $file;
foreach (glob(__DIR__ . '/services/*.php') as $file) require_once $file;
foreach (glob(__DIR__ . '/helpers/*.php') as $file) require_once $file;

session_start();

$db        = Database::getInstance();
$userRepo  = new UserRepository($db);
$videoRepo = new VideoRepository($db);
$likeRepo  = new LikeRepository($db);
$subRepo   = new SubRepository($db);