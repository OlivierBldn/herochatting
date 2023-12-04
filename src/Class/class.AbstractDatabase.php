<?php // path: src/Class/class.AbstractDatabase.php

require_once __DIR__ . '/../../config/cfg_dbConfig.php';
require_once __DIR__ . '/Interface/iface.DBConnectorInterface.php';

abstract class AbstractDatabase implements DBConnectorInterface
{
    protected $connection;
    protected static $instance;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }
}