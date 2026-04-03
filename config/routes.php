<?php

return [
    'GET' => [
        '/' => ['controller' => 'VideoController', 'action' => 'homepage'],
        '/watch' => ['controller' => 'VideoController', 'action' => 'watch'],
        '/login' => ['controller' => 'AuthController', 'action' => 'loginForm'],
        '/register' => ['controller' => 'AuthController', 'action' => 'registerForm'],
        '/search' => ['controller' => 'VideoController', 'action' => 'search'],
        '/upload' => ['controller' => 'VideoController', 'action' => 'uploadForm'],
        '/logout' => ['controller' => 'AuthController', 'action' => 'logout'],
    ],
    'POST' => [
        '/login' => ['controller' => 'AuthController', 'action' => 'login'],
        '/register' => ['controller' => 'AuthController', 'action' => 'register'],
        '/upload' => ['controller' => 'VideoController', 'action' => 'upload'],
    ]
];
