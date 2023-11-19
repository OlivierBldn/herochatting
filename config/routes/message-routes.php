<?php // path: config/routes/message-routes.php

$messageRoutes = [
    '~^/users/(\d+)/chat/(\d+)/messages$~' => [
        'class' => 'Message',
        'controller' => 'MessageController',
        'methods' => [
            'GET' => 'getAllMessages',
            'POST' => 'createMessage',
        ],
    ],
    '~^/messages/(\d+)$~' => [
        'class' => 'Message',
        'controller' => 'MessageController',
        'methods' => [
            'GET' => 'getMessageById',
            'PUT' => 'updateMessage',
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
    // '~^/messages/chat/(\d+)$~' => [
    //     'class' => 'Message',
    //     'controller' => 'MessageController',
    //     'methods' => [
    //         'GET' => 'getMessagesByChatId',
    //     ],
    // ],
    // '~^/messages/user/(\d+)$~' => [
    //     'class' => 'Message',
    //     'controller' => 'MessageController',
    //     'methods' => [
    //         'GET' => 'getMessagesByUserId',
    //     ],
    // ],
];

return $messageRoutes;