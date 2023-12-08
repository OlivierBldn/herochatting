<?php // path: src/Class/class.AbstractDatabase.php

require_once __DIR__ . '/../../config/cfg_dbConfig.php';
require_once __DIR__ . '/Interface/iface.DBConnectorInterface.php';

/**
 * Class AbstractDatabase
 * 
 * This class is the abstract class for the database connection.
 * Implements the interface DBConnectorInterface.
 * 
 */
abstract class AbstractDatabase implements DBConnectorInterface
{
    protected $connection;
    protected static $instance;

    /**
     * Function to get the instance of the AbstractDatabase
     *
     * @return AbstractDatabase
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Function to get the connection to the database
     *
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }
}