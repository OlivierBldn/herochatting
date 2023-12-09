<?php // path: src/Class/class.AbstractRepository.php

require_once __DIR__ . '/../Class/class.DBConnectorFactory.php';
require_once __DIR__ . '/../../config/cfg_dbConfig.php';

/**
 * Class AbstractRepository
 * 
 * This class is the abstract class for the repositories.
 * It contains the database connection.
 * Gathers the common methods for the repositories.
 * 
 */
abstract class AbstractRepository {
    protected $dbConnector;

    public function __construct()
    {
        $this->dbConnector = DBConnectorFactory::getConnector();
    }


    /**
     * Function that uses the original query from the repository to check if a user is the owner of the requested entity
     * 
     * @param string $sql
     * @param array $params
     * 
     * @return bool
     */
    protected function executeOwnershipQuery($sql, $params) {

        // Count the number of rows returned by the query to check if the user is the owner of the entity
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
                    throw new Exception("Type de base de donnees non reconnu");
            }
            return $count > 0;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la verification de la propriete : " . $e->getMessage());
        }
    }


    /**
     * Function that uses the original query from the repository to execute the image referencing in the database
     * 
     * @param string $sql
     * @param array $params
     * 
     * @return bool
     */
    protected function executeImageReferencing($sql, $parameters) {
        try {
            $success = $this->dbConnector->execute($sql, $parameters);
            return $success;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de l'ajout de la reference d'image : " . $e->getMessage());
        }
    }


    /**
     * Function that uses the original query from the repository to check if an image is used by other entities
     * 
     * @param string $sql
     * @param array $params
     * 
     * @return bool
     */
    protected function executeImageTracking($sql, $params) {
        try {
            $result = $this->dbConnector->select($sql, $params);
            $count = 0;
            switch (__DB_INFOS__['database_type']) {
                case 'mysql':
                case 'sqlite':
                    $count = $result[0]['COUNT(*)'] ?? 0;
                    break;
                case 'pgsql':
                    $count = $result[0]['count'] ?? 0;
                    break;
            }
            return $count > 0;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la verification de l'utilisation de l'image par d'autres entites : " . $e->getMessage());
        }
    }
}