<?php // path: config/cfg_dbConfig.php

const __DB_INFOS__ = [
    'database_type' => 'sqlite',
    'mysql' => [
        'host' => 'Your_Host',
        'dbname' => 'Your_Database_Name',
        'username' => 'Your_Username',
        'password' => 'Your_Password',
    ],
    'sqlite' => [
        'database_file' => 'herochatting.db',
    ],
    'pgsql' => [
        'host' => 'Your_Host',
        'port' => 'Your_Port',
        'dbname' => 'Your_Database_Name',
        'username' => 'Your_Username',
        'password' => 'Your_Password',
    ],
];