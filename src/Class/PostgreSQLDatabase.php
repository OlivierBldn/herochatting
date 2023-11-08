<?php // path: src/Class/PostgreSQLDatabase.php

require __DIR__ . '/../../config/db_config.php';
require __DIR__ . '/iface.dbconnector.php';

class PostgreSQLDatabase implements DBConnectorInterface
{
    private static $instance;
    private $connection;

    private function __construct()
    {
        global $dbinfos;

        $pgsqlConfig = $dbinfos['postgresql'];

        try {
            $this->connection = new PDO(
                'pgsql:host=' . $pgsqlConfig['host'] . ';dbname=' . $pgsqlConfig['dbname'],
                $pgsqlConfig['username'],
                $pgsqlConfig['password']
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données PostgreSQL : " . $e->getMessage());
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
        return $this->connection->lastInsertId();
    }
}