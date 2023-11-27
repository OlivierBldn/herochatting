<?php // path: config/routes/message-routes.php

$messageRoutes = [
    '~^/users/(\d+)/chats/(\d+)/messages$~' => [
        'class' => 'Message',
        'controller' => 'MessageController',
        'methods' => [
            'GET' => 'getMessagesByChatId',
            'POST' => 'createMessage',
        ],
    ],
    '~^/messages/(\d+)$~' => [
        'class' => 'Message',
        'controller' => 'MessageController',
        'methods' => [
            'GET' => 'getMessageById',
            'DELETE' => 'deleteMessage',
        ],
    ],
    '~^/messages$~' => [
        'class' => 'Message',
        'controller' => 'MessageController',
        'methods' => [
            'GET' => 'getAllMessages',
        ],
    ],
];

return $messageRoutes;