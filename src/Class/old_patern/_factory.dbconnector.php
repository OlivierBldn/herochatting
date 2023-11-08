<?php // path: src/Class/factory.dbconnector.php

require __DIR__ . '/../../config/db_config.php';

class DBConnectorFactory {
    public static function getConnector(): DBConnectorInterface 
    {
        $config = require(__DIR__ . '/../../config/db_config.php');

        $databaseType = $config['database_type'];

        switch ($databaseType) {
            case 'sqlite':
                return SQLiteDatabase::getInstance();
            case 'mysql':
                return MySQLDatabase::getInstance();
            case 'postgresql':
                return PostgreSQLDatabase::getInstance();
            default:
                throw new Exception("Type de base de données non supporté");
        }
    }
}