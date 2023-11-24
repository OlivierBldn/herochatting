<?php // path: config/routes/chat-routes.php

$chatRoutes = [
    '~^/chats$~' => [
        'class' => 'Chat',
        'controller' => 'ChatController',
        'methods' => [
            'GET' => 'getAllChats',
            'POST' => 'createChat',
        ],
    ],
    '~^/chats/(\d+)$~' => [
        'class' => 'Chat',
        'controller' => 'ChatController',
        'methods' => [
            'GET' => 'getChatById',
            'PUT' => 'updateChat',
            'DELETE' => 'deleteChat',
        ],
    ],
    '~^/users/(\d+)/chats$~' => [
        'class' => 'Chat',
        'controller' => 'ChatController',
        'methods' => [
            'GET' => 'getChatsByUserId',
        ],
    ],
    '~^/users/(\d+)/chats/(\d+)/messages$~' => [
        'class' => 'Chat',
        'controller' => 'ChatController',
        'methods' => [
            'GET' => 'getMessagesByChatId',
        ],
    ],
    '~^/characters/(\d+)/chats$~' => [
        'class' => 'Chat',
        'controller' => 'ChatController',
        'methods' => [
            'GET' => 'getChatByCharacterId',
        ],
    ],
];

return $chatRoutes;