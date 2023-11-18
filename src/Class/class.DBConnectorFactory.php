<?php // path: src/Class/class.DBConnectorFactory.php

// require __DIR__ . '/../../config/cfg_dbConfig.php';
require_once __DIR__ . '/../../config/cfg_dbConfig.php';

class DBConnectorFactory
{
    public static function getConnector()
    {
        switch (__DB_INFOS__['database_type']) {
            case 'sqlite':
                $realDatabase = new SQLiteDatabase();
                break;
            case 'mysql':
                $realDatabase = new MySQLDatabase();
                break;
            case 'pgsql':
                $realDatabase = new PostgreSQLDatabase();
                break;
            default:
                throw new Exception("Type de base de données non supporté");
        }

        return new DatabaseProxy($realDatabase);
    }
}