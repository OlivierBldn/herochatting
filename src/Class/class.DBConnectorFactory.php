<?php // path: src/Class/class.DBConnectorFactory.php

class DBConnectorFactory
{
    public static function getConnector()
    {

        $config = require(__DIR__ . '/../../config/db_config.php');

        $databaseType = $config['database_type'];

        switch ($databaseType) {
            case 'sqlite':
                $realDatabase = new SQLiteDatabase();
                break;
            case 'mysql':
                $realDatabase = new MySQLDatabase();
                break;
            case 'postgresql':
                $realDatabase = new PostgreSQLDatabase();
                break;
            default:
                throw new Exception("Type de base de données non supporté");
        }

        return new DatabaseProxy($realDatabase);
    }
}