<?php // path: config/routes/auth-routes.php

$authRoutes = [
    '~^/auth/login$~' => [
        'class' => 'JWTKeySingleton',
        'controller' => 'AuthController',
        'methods' => [
            'POST' => 'authenticate',
        ],
    ],
    // '~^/auth/logout$~' => [
    //     'class' => 'Auth',
    //     'controller' => 'AuthController',
    //     'methods' => [
    //         'POST' => 'logout',
    //     ],
    // ],
    // '~^/auth/refresh$~' => [
    //     'class' => 'Auth',
    //     'controller' => 'AuthController',
    //     'methods' => [
    //         'POST' => 'refreshToken',
    //     ],
    // ],
    // '~^/auth/verify$~' => [
    //     'class' => 'Auth',
    //     'controller' => 'AuthController',
    //     'methods' => [
    //         'POST' => 'verifyToken',
    //     ],
    // ],
];

return $authRoutes;
