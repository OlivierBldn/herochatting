<?php  // path: config/routes/user-routes.php

$userRoutes = [
    '~^/register$~' => [
        'class' => 'User',
        'controller' => 'UserController',
        'methods' => [
            'POST' => 'createUser',
        ],
    ],
    '~^/users$~' => [
        'class' => 'User',
        'controller' => 'UserController',
        'methods' => [
            'GET' => 'getAllUsers',
        ],
    ],
    '~^/users/(\d+)$~' => [
        'class' => 'User',
        'controller' => 'UserController',
        'methods' => [
            'GET' => 'getUserById',
            'PUT' => 'updateUser',
            'DELETE' => 'deleteUser',
        ],
    ],
];

return $userRoutes;