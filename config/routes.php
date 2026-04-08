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
        '/settings' => ['controller' => 'SettingsController', 'action' => 'index'],
        '/verify' => ['controller' => 'AuthController', 'action' => 'verify'],
        '/channel' => ['controller' => 'ChannelController', 'action' => 'index'],
        '/forgot'        => ['controller' => 'AuthController', 'action' => 'forgotForm'],
        '/reset'         => ['controller' => 'AuthController', 'action' => 'resetForm'],
    ],
    'POST' => [
        '/login' => ['controller' => 'AuthController', 'action' => 'login'],
        '/register' => ['controller' => 'AuthController', 'action' => 'register'],
        '/upload' => ['controller' => 'VideoController', 'action' => 'upload'],
        '/like/toggle' => ['controller' => 'LikeController','action' => 'toggle'],
        '/sub/toggle' => ['controller' => 'SubController','action' => 'toggle'],
        '/settings' => ['controller' => 'SettingsController', 'action' => 'update'],
        '/comment/store'  => ['controller' => 'CommentController', 'action' => 'store'],
        '/comment/delete' => ['controller' => 'CommentController', 'action' => 'delete'],
        '/comment/pin'    => ['controller' => 'CommentController', 'action' => 'pin'],
        '/comment/like'   => ['controller' => 'CommentController', 'action' => 'like'],
        '/comment/edit' => ['controller' => 'CommentController', 'action' => 'edit'],
        '/forgot'        => ['controller' => 'AuthController', 'action' => 'forgot'],
        '/reset'         => ['controller' => 'AuthController', 'action' => 'reset'],
        '/account/delete'=> ['controller' => 'SettingsController', 'action' => 'deleteAccount'],
    ]
];
