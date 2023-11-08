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
        // Redirigez l'appel vers la base de données réelle
        return $this->realDatabase->select($query, $params);
    }

    public function execute($query, $params = []): bool
    {
        // Redirigez l'appel vers la base de données réelle
        return $this->realDatabase->execute($query, $params);
    }

    public function lastInsertRowID(): int
    {
        // Redirigez l'appel vers la base de données réelle
        return $this->realDatabase->lastInsertRowID();
    }
}