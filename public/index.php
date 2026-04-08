<?php

require_once __DIR__ . '/../bootstrap.php';

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
        'VideoController' => new VideoController($videoRepo,$likeRepo,$subRepo,$commentRepo,$feedRepo, $tagRepo),
        'LikeController' => new LikeController($likeRepo),
        'SubController' => new SubController($subRepo),
        'SettingsController' => new SettingsController($userRepo),
        'CommentController' => new CommentController($commentRepo),
        'ChannelController' => new ChannelController($userRepo, $videoRepo, $subRepo),
        'FeedController' => new FeedController($feedRepo),
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