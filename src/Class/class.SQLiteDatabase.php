<?php // path: src/Class/class.SQLiteDatabase.php

require_once __DIR__ . '/../../config/cfg_dbConfig.php';
require __DIR__ . '/Interface/iface.DBConnectorInterface.php';

/**
 * Class SQLiteDatabase
 * 
 * This class is the SQLite database connection.
 * It represents the database connection to a SQLite database and contains the functions to execute queries.
 * 
 */
class SQLiteDatabase extends AbstractDatabase
{
    /**
     * 
     * SQLiteDatabase constructor.
     * This constructor creates the connection to the database using the config file and SQLite3 functions.
     * 
     */
    public function __construct()
    {
        $databaseFile = __DIR__ . '/../../database/'.__DB_INFOS__['sqlite']['database_file'];

        try {
            $this->connection = new SQLite3($databaseFile);
        } catch (Exception $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }
    
    /**
     * Function to select data from the database
     * 
     * @param string $query
     * @param array $params
     * 
     * @return array
     */
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

    /**
     * Function to execute a query on the database
     * 
     * @param string $query
     * @param array $params
     * 
     * @return bool
     */
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

    /**
     * Function to get the last inserted row ID
     * 
     * @return int
     */
    public function lastInsertRowID(): int
    {
        return $this->connection->lastInsertRowID();
    }

    /**
     * Function to begin a transaction and execute multiple queries
     */
    public function beginTransaction() {
        $this->connection->exec('BEGIN TRANSACTION');
    }

    /**
     * Function to commit the transaction
     */
    public function commit() {
        $this->connection->exec('COMMIT');
    }

    /**
     * Function to rollback the transaction and cancel the queries
     */
    public function rollBack() {
        $this->connection->exec('ROLLBACK');
    }
}
