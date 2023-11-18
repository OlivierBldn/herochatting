<?php // path: config/cfg_dbConfig.php

const __DB_INFOS__ = [
    'database_type' => 'mysql',
    'mysql' => [
        'host' => 'localhost',
        'dbname' => 'chatting_ai',
        'username' => 'root',
        'password' => '',
    ],
    'sqlite' => [
        'database_file' => 'chatting_api.db',
    ],
    'pgsql' => [
        'host' => 'flora.db.elephantsql.com',
        'port' => '5432',
        'dbname' => 'ofxjzngu',
        'username' => 'ofxjzngu',
        'password' => 'YujytayzdwApnpvzyzq1IutJ7GRrafAS',
    ],
];