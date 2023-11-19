<?php // path: src/Class/PostgreSQLDatabase.php

require_once __DIR__ . '/../../config/cfg_dbConfig.php';
require __DIR__ . '/Interface/iface.DBConnectorInterface.php';

class PostgreSQLDatabase extends AbstractDatabase
{
    protected $connection;

    public function __construct()
    {
        $pgsqlConfig = __DB_INFOS__['pgsql'];

        $connectionString = "host={$pgsqlConfig['host']} port={$pgsqlConfig['port']} dbname={$pgsqlConfig['dbname']} user={$pgsqlConfig['username']} password={$pgsqlConfig['password']}";

        $this->connection = pg_connect($connectionString);

        if (!$this->connection) {
            die("Erreur de connexion à la base de données : " . pg_last_error());
        }
    }

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

    public function execute($query, $params = []): bool
    {
        $result = pg_query_params($this->connection, $query, $params);

        if (!$result) {
            die("Erreur lors de l'exécution de la requête PostgreSQL : " . pg_last_error($this->connection));
        }

        return true;
    }

    public function close()
    {
        pg_close($this->connection);
    }

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

    public function beginTransaction() {
        pg_query($this->connection, "BEGIN");
    }

    public function commit() {
        pg_query($this->connection, "COMMIT");
    }

    public function rollBack() {
        pg_query($this->connection, "ROLLBACK");
    }
}