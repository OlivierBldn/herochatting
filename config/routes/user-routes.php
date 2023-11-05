<?php  // path: config/routes/user-routes.php

$userRoutes = [
    '~^/users$~' => [
        'class' => 'User',
        'controller' => 'UserController',
        'methods' => [
            'POST' => 'createUser',
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