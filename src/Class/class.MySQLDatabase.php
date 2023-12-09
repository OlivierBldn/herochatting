<?php // path: src/Class/class.MySQLDatabase.php

/**
 * Class MySQLDatabase
 * 
 * This class is the MySQL database class.
 * It represents the database connection to a MySQL database and contains the functions to interact with it.
 * 
 */
class MySQLDatabase extends AbstractDatabase
{
    /**
     * 
     * MySQLDatabase constructor.
     * This constructor creates the connection to the MySQL database using PDO.
     * 
     */
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
            die("Erreur de connexion a la base de donnees : " . $e->getMessage());
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
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erreur lors de la selection dans la base de donnees MySQL : " . $e->getMessage());
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
            return $stmt->execute($params);
        } catch (PDOException $e) {
            die("Erreur lors de l'execution de la requete MySQL : " . $e->getMessage());
        }
    }


    /**
     * Function to get the last inserted row ID
     * 
     * @return int
     */
    public function lastInsertRowID(): int
    {
        return $this->connection->lastInsertID();
    }

    /**
     * Function to begin a transaction and execute multiple queries
     */
    public function beginTransaction() {
        $this->connection->beginTransaction();
    }

    /**
     * Function to commit the transaction
     */
    public function commit() {
        $this->connection->commit();
    }

    /**
     * Function to rollback the transaction and cancel the queries
     */
    public function rollBack() {
        $this->connection->rollBack();
    }
}
