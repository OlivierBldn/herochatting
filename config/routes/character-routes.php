<?php // path: config/routes/character-routes.php

$characterRoutes = [
    '~^/characters$~' => [
        'class' => 'Character',
        'controller' => 'CharacterController',
        'methods' => [
            'GET' => 'getAllCharacters',
        ],
    ],
    '~^/characters/(\d+)$~' => [
        'class' => 'Character',
        'controller' => 'CharacterController',
        'methods' => [
            'GET' => 'getCharacterById',
            'PUT' => 'updateCharacter',
            'DELETE' => 'deleteCharacter',
        ],
    ],
    '~^/universes/(\d+)/characters$~' => [
        'class' => 'Character',
        'controller' => 'CharacterController',
        'methods' => [
            'GET' => 'getAllCharactersByUniverseId',
            'POST' => 'createCharacter',
        ],
    ],
];

return $characterRoutes;