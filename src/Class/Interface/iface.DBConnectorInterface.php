<?php // path: src/Class/interface/iface.DBConnectorInterface.php

/**
 * DBConnectorInterface
 * Interface for the DBConnector
 * Defines the methods that must be implemented by the DBConnector
 * Implements the Singleton pattern
 */
interface DBConnectorInterface
{
    /**
     * Function to select data from the database
     *
     * @param string $query
     * @param array $params
     * @return array
     */
    public function select($query, $params = []): array;

    /**
     * Function to insert or update data into the database
     *
     * @param string $query
     * @param array $params
     * @return bool
     */
    public function execute($query, $params = []): bool;

    /**
     * Function to get the last inserted row ID
     * Used to get the ID of the last object created when inserting data into the database
     *
     * @return int
     */
    public function lastInsertRowID(): int;
}