<?php // path: src/Class/SQLiteDatabase.php

require __DIR__ . '/iface.dbconnector.php';
require __DIR__ . '/../../config/db_config.php';

class SQLiteDatabase implements DBConnectorInterface
{
    private static $instance;
    private $connection;

    private function __construct()
    {
        global $dbinfos;

        $databaseFile = __DIR__ . '/../../database/'.$dbinfos['sqlite']['database_file'];

        try {
            $this->connection = new SQLite3($databaseFile);
        } catch (Exception $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

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

    public function select($query, $params = []): array
    {
        try {
            $stmt = $this->connection->prepare($query);

            if ($stmt === false) {
                die("Erreur de préparation de la requête SQLite : " . $this->connection->lastErrorMsg());
            }

            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }

            $result = $stmt->execute();

            if ($result === false) {
                die("Erreur lors de l'exécution de la requête SQLite : " . $this->connection->lastErrorMsg());
            }

            $return = [];

            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $return[] = $row;
            }

            return $return;
        } catch (Exception $e) {
            die("Erreur lors de la sélection dans la base de données SQLite : " . $e->getMessage());
        }
    }

    public function execute($query, $params = []): bool
    {
        try {
            $stmt = $this->connection->prepare($query);

            if ($stmt === false) {
                die("Erreur de préparation de la requête SQLite : " . $this->connection->lastErrorMsg());
            }

            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }

            $result = $stmt->execute();

            if ($result === false) {
                die("Erreur lors de l'exécution de la requête SQLite : " . $this->connection->lastErrorMsg());
            }

            return true;
        } catch (Exception $e) {
            die("Erreur lors de l'exécution de la requête SQLite : " . $e->getMessage());
        }
    }

    
    public function lastInsertRowID()
    {
        return $this->connection->lastInsertRowID();
    }
}