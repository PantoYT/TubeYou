<?php
$isDev = ($_ENV['APP_ENV'] ?? 'production') === 'development';
ini_set('display_errors', $isDev ? '1' : '0');
error_reporting(E_ALL);

require_once __DIR__ . '/../bootstrap.php';

set_exception_handler(function (Throwable $e) {
    error_log('[exception] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    while (ob_get_level() > 0) ob_end_clean();
    http_response_code(500);
    render('errors/500');
    exit;
});

set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
    if (!(error_reporting() & $errno)) return false;
    if ($errno & (E_DEPRECATED | E_USER_DEPRECATED | E_NOTICE | E_USER_NOTICE)) return false;
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

function render($view, $data = []) {
    $GLOBALS['_view_data'] = $data;
    extract($data);
    ob_start();
    require __DIR__ . "/../views/$view.php";
    $content = ob_get_clean();
    require __DIR__ . "/../views/layout.php";
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$routes = include __DIR__ . '/../config/routes.php';

if (isset($routes[$method][$uri])) {
    $route = $routes[$method][$uri];
    
    $controller = match ($route['controller']) {
        'AuthController' => new AuthController($userRepo),
        'VideoController' => new VideoController(
            $videoRepo, $likeRepo, $subRepo, 
            $commentRepo, $feedRepo, $tagRepo, $playlistRepo
        ),
        'LikeController' => new LikeController($likeRepo),
        'SubController' => new SubController($subRepo, $notifRepo),
        'SettingsController' => new SettingsController($userRepo),
        'CommentController' => new CommentController($commentRepo, $notifRepo),
        'ChannelController' => new ChannelController($userRepo, $videoRepo, $subRepo),
        'FeedController' => new FeedController($feedRepo),
        'NotificationController' => new NotificationController($notifRepo),
        'PlaylistController' => new PlaylistController($playlistRepo),
        'StudioController' => $studioController,
        default => null
    };

    $action = $route['action'];

    if (!$controller || !method_exists($controller, $action)) 
    {
        http_response_code(404);
        render('errors/404');
        exit;
    }

    $controller?->$action();

} else {
    http_response_code(404);
    render('errors/404');
}