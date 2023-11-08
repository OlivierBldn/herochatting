<?php // path: src/Class/MySQLDatabaseProxy.php

// require_once __DIR__ . '/../../config/db_config.php';
// require_once __DIR__ . '/iface.dbconnector.php';

// class MySQLDatabaseProxy implements DBConnectorInterface
// {
//     private $realMySQLDatabase = null;

//     public function getConnection()
//     {
//         $this->initializeRealMySQLDatabase();
//         return $this->realMySQLDatabase->getConnection();
//     }

//     public function select($query, $params = []): array
//     {
//         $this->initializeRealMySQLDatabase();
//         return $this->realMySQLDatabase->select($query, $params);
//     }

//     public function execute($query, $params = []): bool
//     {
//         $this->initializeRealMySQLDatabase();
//         return $this->realMySQLDatabase->execute($query, $params);
//     }

//     public function lastInsertRowID(): int
//     {
//         $this->initializeRealMySQLDatabase();
//         return $this->realMySQLDatabase->lastInsertRowID();
//     }

//     private function initializeRealMySQLDatabase()
//     {
//         if ($this->realMySQLDatabase === null) {
//             $this->realMySQLDatabase = MySQLDatabase::getInstance();
//         }
//     }
// }