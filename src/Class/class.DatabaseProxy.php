<?php // path: src/Class/class.DatabaseProxy.php

class DatabaseProxy implements DBConnectorInterface
{
    private $realDatabase;

    public function __construct(DBConnectorInterface $realDatabase)
    {
        $this->realDatabase = $realDatabase;
    }

    public function select($query, $params = []): array
    {
        return $this->realDatabase->select($query, $params);
    }

    public function execute($query, $params = []): bool
    {
        return $this->realDatabase->execute($query, $params);
    }

    public function lastInsertRowID(): int
    {
        return $this->realDatabase->lastInsertRowID();
    }
}