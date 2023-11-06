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

    public function select($query): array
    {
        try {
            $result = $this->connection->query($query);
            $return = [];

            if ($result) {
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $return[] = $row;
                }
            }

            return $return;
        } catch (Exception $e) {
            die("Erreur lors de la sélection dans la base de données SQLite : " . $e->getMessage());
        }
    }

    public function execute($query): bool
    {
        try {
            return $this->connection->exec($query);
        } catch (Exception $e) {
            die("Erreur lors de l'exécution de la requête SQLite : " . $e->getMessage());
        }
    }
}