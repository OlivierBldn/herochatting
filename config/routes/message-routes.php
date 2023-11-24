<?php // path: config/routes/message-routes.php

$messageRoutes = [
    '~^/users/(\d+)/chats/(\d+)/messages$~' => [
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
    // '~^/messages/user/(\d+)$~' => [
    //     'class' => 'Message',
    //     'controller' => 'MessageController',
    //     'methods' => [
    //         'GET' => 'getMessagesByUserId',
    //     ],
    // ],
];

return $messageRoutes;