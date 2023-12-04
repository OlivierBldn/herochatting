<?php // path: src/Class/class.AbstractRepository.php

require_once __DIR__ . '/../Class/class.DBConnectorFactory.php';
require_once __DIR__ . '/../../config/cfg_dbConfig.php';

abstract class AbstractRepository {
    protected $dbConnector;

    public function __construct() {
        $this->dbConnector = DBConnectorFactory::getConnector();
    }

    protected function executeOwnershipQuery($sql, $params) {
        try {
            $result = $this->dbConnector->select($sql, $params);
            switch (__DB_INFOS__['database_type']) {
                case 'mysql':
                case 'sqlite':
                    $count = $result[0]['COUNT(*)'] ?? 0;
                    break;
                case 'pgsql':
                    $count = $result[0]['count'] ?? 0;
                    break;
                default:
                    throw new Exception("Type de base de données non reconnu");
            }
            return $count > 0;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la vérification de la propriété : " . $e->getMessage());
        }
    }
}