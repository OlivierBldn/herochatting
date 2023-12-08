<?php // path: src/Class/PostgreSQLDatabase.php

// require_once __DIR__ . '/../../config/cfg_dbConfig.php';
// require __DIR__ . '/Interface/iface.DBConnectorInterface.php';

/**
 * Class PostgreSQLDatabase
 * 
 * This class is the PostgreSQL database connection.
 * It represents the database connection to a PostgreSQL database and contains the functions to execute queries.
 * 
 */
class PostgreSQLDatabase extends AbstractDatabase
{
    protected $connection;

    /**
     * 
     * PostgreSQLDatabase constructor.
     * This constructor creates the connection to the database using the config file and PostgreSQL functions.
     * 
     */
    public function __construct()
    {
        $pgsqlConfig = __DB_INFOS__['pgsql'];

        $connectionString = "host={$pgsqlConfig['host']} port={$pgsqlConfig['port']} dbname={$pgsqlConfig['dbname']} user={$pgsqlConfig['username']} password={$pgsqlConfig['password']}";

        $this->connection = pg_connect($connectionString);

        if (!$this->connection) {
            die("Erreur de connexion à la base de données : " . pg_last_error());
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
        if (!empty($params)) {
            $result = pg_query_params($this->connection, $query, $params);
        } else {
            $result = pg_query($this->connection, $query);
        }

        if (!$result) {
            die("Erreur lors de la sélection dans la base de données PostgreSQL : " . pg_last_error($this->connection));
        }

        return pg_fetch_all($result);
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
        $result = pg_query_params($this->connection, $query, $params);

        if (!$result) {
            die("Erreur lors de l'exécution de la requête PostgreSQL : " . pg_last_error($this->connection));
        }

        return true;
    }

    /**
     * Function to close the connection to the database
     * 
     */
    public function close()
    {
        pg_close($this->connection);
    }

    /**
     * Function to get the last inserted row ID
     * 
     * @return int
     */
    public function lastInsertRowId(): int
    {
        $result = pg_query($this->connection, "SELECT lastval()");
    
        if ($result) {
            $row = pg_fetch_row($result);
            $lastOid = $row[0];
    
            if ($lastOid !== false) {
                return (int)$lastOid;
            }
        }
    
        return 0;
    }

    /**
     * Function to begin a transaction and execute multiple queries
     */
    public function beginTransaction() {
        pg_query($this->connection, "BEGIN");
    }

    /**
     * Function to commit the transaction
     */
    public function commit() {
        pg_query($this->connection, "COMMIT");
    }

    /**
     * Function to rollback the transaction and cancel the queries
     */
    public function rollBack() {
        pg_query($this->connection, "ROLLBACK");
    }
}