<?php // path: src/Class/Database.php

require __DIR__ . '/../../config/db_config.php';

class Database
{
    private static $instance;
    private $connection;

    private function __construct()
    {
        // Establish connection to MySQL database
        $this->connection = new mysqli(__DB_HOST__, __DB_USER__, __DB_PASS__, __DB_NAME__);


        // Handle errors
        if ($this->connection->connect_error) {
            die("Erreur de connexion à la base de données : " . $this->connection->connect_error);
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
}