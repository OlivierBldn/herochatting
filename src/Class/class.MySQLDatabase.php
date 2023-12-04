<?php // path: src/Class/class.MySQLDatabase.php

// require_once __DIR__ . '/../../config/cfg_dbConfig.php';
// require __DIR__ . '/Interface/iface.DBConnectorInterface.php';

class MySQLDatabase extends AbstractDatabase
{
    public function __construct()
    {
        $mysqlConfig = __DB_INFOS__['mysql'];

        try {
            $this->connection = new PDO(
                'mysql:host=' . $mysqlConfig['host'] . ';dbname=' . $mysqlConfig['dbname'],
                $mysqlConfig['username'],
                $mysqlConfig['password']
            );

            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    public function select($query, $params = []): array
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erreur lors de la sélection dans la base de données MySQL : " . $e->getMessage());
        }
    }

    public function execute($query, $params = []): bool
    {
        try {
            $stmt = $this->connection->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            die("Erreur lors de l'exécution de la requête MySQL : " . $e->getMessage());
        }
    }


    public function lastInsertRowID(): int
    {
        return $this->connection->lastInsertID();
    }

    public function beginTransaction() {
        $this->connection->beginTransaction();
    }

    public function commit() {
        $this->connection->commit();
    }

    public function rollBack() {
        $this->connection->rollBack();
    }
}
