<?php // path: src/Class/class.DBConnectorFactory.php

require_once __DIR__ . '/../../config/cfg_dbConfig.php';

/**
 * Class DBConnectorFactory
 * 
 * This class is the factory for the database connection.
 * The user can choose between multiple database types: SQLite, MySQL and PostgreSQL.
 * 
 */
class DBConnectorFactory
{
    /**
     * Function to get the right database connection depending on the database type
     * The database type is defined in the config file.
     *
     * @return DatabaseProxy
     */
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