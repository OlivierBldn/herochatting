<?php // path: config/routes/universe-routes.php

$universeRoutes = [
    '~^/universes$~' => [
        'class' => 'Universe',
        'controller' => 'UniverseController',
        'methods' => [
            'POST' => 'createUniverse',
            'GET' => 'getAllUniverses',
        ],
    ],
    '~^/universes/(\d+)$~' => [
        'class' => 'Universe',
        'controller' => 'UniverseController',
        'methods' => [
            'GET' => 'getUniverseById',
            'PUT' => 'updateUniverse',
            'DELETE' => 'deleteUniverse',
        ],
    ],
    '~^/users/(\d+)/universes$~' => [
        'class' => 'Universe',
        'controller' => 'UniverseController',
        'methods' => [
            'GET' => 'getAllUniversesByUserId',
        ],
    ],
];

return $universeRoutes;