<?php // path: src/Class/class.DatabaseProxy.php

/**
 * Class DatabaseProxy
 * 
 * This class is the database proxy class.
 * Implements the interface DBConnectorInterface.
 * Used to proxy the database connection and add extra functionality.
 * 
 */
class DatabaseProxy implements DBConnectorInterface
{
    private $realDatabase;

    public function __construct(DBConnectorInterface $realDatabase)
    {
        $this->realDatabase = $realDatabase;
    }

    //Function to select data from the database
    public function select($query, $params = []): array
    {
        return $this->realDatabase->select($query, $params);
    }

    // Function to execute a query on the database
    public function execute($query, $params = []): bool
    {
        return $this->realDatabase->execute($query, $params);
    }

    // Function to get the last inserted row ID
    public function lastInsertRowID(): int
    {
        return $this->realDatabase->lastInsertRowID();
    }

    // Function to begin a transaction and execute multiple queries
    public function beginTransaction() {
        $this->realDatabase->beginTransaction();
    }

    // Function to commit the transaction
    public function commit() {
        $this->realDatabase->commit();
    }

    // Function to rollback the transaction and cancel the queries
    public function rollBack() {
        $this->realDatabase->rollBack();
    }
}