<?php // path: src/Class/class.AbstractDatabase.php

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

    public function lastInsertRowID(): int
    {
        return $this->connection->lastInsertRowID();
    }
}