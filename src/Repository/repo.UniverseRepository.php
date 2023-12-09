<?php // path: src/Repository/repo.UniverseRepository.php

/**
 * Class UniverseRepository
 * 
 * This class is the repository for the Universe class.
 * It contains all the queries to the database regarding the Universe class.
 * 
 */
class UniverseRepository extends AbstractRepository
{
    /**
     * Function to create a new Universe
     * 
     * @param array $universeData
     * @param int $userId
     * @return int
     */
    public function create($universeData, $userId)
    {
        $newUniverse = Universe::fromMap($universeData);

        if ($newUniverse === null) {
            return false;
        }

        $name = $newUniverse->getName();
        $description = $newUniverse->getDescription();
        $image = $newUniverse->getImage();

        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'INSERT INTO `universe` (name, description, image) 
                        VALUES (:name, :description, :image)';
        
                $parameters = [
                    ':name' => $name,
                    ':description' => $description,
                    ':image' => $image,
                ];
                break;
            case 'pgsql':
                $sql = 'INSERT INTO "universe" (name, description, image) 
                        VALUES ($1, $2, $3)';
        
                $parameters = [
                    $name,
                    $description,
                    $image,
                ];
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }

        try {
            $success = $this->dbConnector->execute($sql, $parameters);

            if ($success) {
                $universeId= $this->dbConnector->lastInsertRowID();

                $this->linkUniverseToUser($userId, $universeId);

                return $universeId;
            } else {
                throw new Exception("Erreur lors de la creation de l'univers");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la creation de l'univers : " . $e->getMessage());
        }
    }

    /**
     * Function to link a Universe to a User
     * 
     * @param int $userId
     * @param int $universeId
     * @return bool
     */
    public function linkUniverseToUser($userId, $universeId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'INSERT INTO `user_universe`
                        VALUES (:userId, :universeId)';
                $parameters = [
                    ':userId' => $userId,
                    ':universeId' => $universeId
                ];
                break;
            case 'pgsql':
                $sql = 'INSERT INTO "user_universe"
                        VALUES ($1, $2)';
                $parameters = [
                    $userId,
                    $universeId
                ];
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }
    
        try {
            $success = $this->dbConnector->execute($sql, $parameters);

            if ($success) {
                return true;
            } else {
                throw new Exception("Erreur lors de l'association de l'univers a l'utilisateur");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de l'association de l'univers a l'utilisateur : " . $e->getMessage());
        }
    }

    /**
     * Function to get all the Universes
     * 
     * @return array
     */
    public function getAll()
    {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT * FROM `universe`';
                break;
            case 'pgsql':
                $sql = 'SELECT * FROM "universe"';
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }

        try {
            $allUniversesArraySql = $this->dbConnector->select($sql);

            $allUniversesArrayObject = [];

            foreach ($allUniversesArraySql as $key => $universe) {
                $allUniversesArrayObject[] = Universe::fromMap($universe);
            }

            return $allUniversesArrayObject;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la recuperation de tous les univers : " . $e->getMessage());
        }
    }

    /**
     * Function to get all the Universes of a User
     * 
     * @param int $userId
     * @return array
     */
    public function getAllByUserId($userId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT u.* FROM `universe` u 
                        INNER JOIN `user_universe` uu ON u.id = uu.universeId 
                        WHERE uu.userId = :userId';
                $parameters = [':userId' => $userId];
                break;
            case 'pgsql':
                $sql = 'SELECT u.* FROM "universe" u 
                        INNER JOIN "user_universe" uu ON u.id = uu.universeId 
                        WHERE uu."userId" = $1';
                $parameters = [$userId];
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }
    
        try {
            $universes = $this->dbConnector->select($sql, $parameters);
            $universeObjects = [];
    
            foreach ($universes as $universe) {
                $universeObjects[] = Universe::fromMap($universe);
            }
    
            return $universeObjects;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la recuperation des univers de l'utilisateur : " . $e->getMessage());
        }
    }

    /**
     * Function to get a Universe by its id
     * 
     * @param int $id
     * @return Universe|null
     */
    public function getById($id)
    {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT * FROM `universe` WHERE id = :id';
                
                $params = [':id' => $id];
                break;
            case 'pgsql':
                $sql = 'SELECT * FROM "universe" WHERE id = $1';

                $params = [$id];
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }
    
        try {
            $universeMap = $this->dbConnector->select($sql, $params);
    
            if (count($universeMap) === 1) {
                return Universe::fromMap($universeMap[0]);
            } else {
                return null;
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la recuperation de l'univers : " . $e->getMessage());
        }
    }

    /**
     * Function to get a Universe by its name
     * 
     * @param string $name
     * @return Universe|null
     */
    public function getByName($name)
    {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT * FROM `universe` WHERE name = :name';
                $params = [':name' => $name];
                break;
            case 'pgsql':
                $sql = 'SELECT * FROM "universe" WHERE name = $1';
                $params = [$name];
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }

        try {
            $universe = $this->dbConnector->select($sql, $params);

            return $universe ? Universe::fromMap($universe[0]) : null;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la recuperation des univers par nom : " . $e->getMessage());
        }
    }

    /**
     * Function to update a Universe
     * 
     * @param int $universeId
     * @param array $universeData
     * @return bool
     */
    public function update($universeId, $universeData)
    {
        $existingUniverse = $this->getById($universeId);
    
        if (!$existingUniverse) {
            throw new Exception("Univers non trouve");
        }
    
        $name = $universeData['name'] ?? $existingUniverse->getName();
        $description = $universeData['description'] ?? $existingUniverse->getDescription();
        $image = $universeData['image'] ?? $existingUniverse->getImage();
    
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'UPDATE `universe` SET name = :name, description = :description, image = :image WHERE id = :universeId';

                $parameters = [
                    ':name' => $name,
                    ':description' => $description,
                    ':image' => $image,
                    ':universeId' => $universeId
                ];
                break;
            case 'pgsql':
                $sql = 'UPDATE "universe" SET name = $1, description = $2, image = $3 WHERE id = $4';

                $parameters = [
                    $name,
                    $description,
                    $image,
                    $universeId
                ];
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }
    
        try {
            $success = $this->dbConnector->execute($sql, $parameters);
    
            if ($success) {
                return true;
            } else {
                throw new Exception("Erreur lors de la mise a jour de l'univers");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la mise a jour de l'univers : " . $e->getMessage());
        }
    }

    /**
     * Function to delete a Universe
     * 
     * @param int $universeId
     * @param string $entityType
     * @return bool
     */
    public function delete($universeId, $entityType = 'universe') {
        try {
            // Begin transaction to execute multiple queries
            $this->dbConnector->beginTransaction();

            // Delete all the references to the universe in the user_universe table
            switch (__DB_INFOS__['database_type']) {
                case 'mysql':
                case 'sqlite':
                    $sql = 'DELETE FROM `user_universe` WHERE universeId = :universeId';
                    $params = [':universeId' => $universeId];
                    break;
                case 'pgsql':
                    $sql = 'DELETE FROM "user_universe" WHERE "universeId" = $1';
                    $params = [$universeId];
                    break;
                default:
                    throw new Exception("Type de base de donnees non reconnu");
            }
            $this->dbConnector->execute($sql, $params);

            // Delete all the references to the universe in the image_references table
            switch (__DB_INFOS__['database_type']) {
                case 'mysql':
                case 'sqlite':
                    $sql = 'DELETE FROM `image_references`
                            WHERE entity_id = :universeId
                            AND entity_type = :entityType';
                    $params = [
                        ':universeId' => $universeId,
                        ':entityType' => $entityType,
                    ];
                    break;
                case 'pgsql':
                    $sql = 'DELETE FROM "image_references"
                            WHERE entity_id = $1
                            AND entity_type = $2';
                    $params = [
                        $universeId,
                        $entityType,
                    ];
                    break;
                default:
                    throw new Exception("Type de base de donnees non reconnu");
            }
            $this->dbConnector->execute($sql, $params);

            // Delete the universe
            switch (__DB_INFOS__['database_type']) {
                case 'mysql':
                case 'sqlite':
                    $sql = 'DELETE FROM `universe` WHERE id = :universeId';
                    $params = [':universeId' => $universeId];
                    break;
                case 'pgsql':
                    $sql = 'DELETE FROM "universe" WHERE id = $1';
                    $params = [$universeId];
                    break;
                default:
                    throw new Exception("Type de base de donnees non reconnu");
            }
            $this->dbConnector->execute($sql, $params);

            // Commit the transaction
            $this->dbConnector->commit();

            return true;
        } catch (Exception $e) {
            // Cancel the transaction if an error occurs
            $this->dbConnector->rollBack();
            throw new Exception("Erreur lors de la suppression de l'univers : " . $e->getMessage());
        }
    }

    /**
     * Function to check if a User exists
     * 
     * @param int $userId
     * @return bool
     */
    public function userExists($userId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT COUNT(*) FROM `user` WHERE id = :userId';
                $params = [':userId' => $userId];
                break;
            case 'pgsql':
                $sql = 'SELECT COUNT(*) FROM "user" WHERE id = $1';
                $params = [$userId];
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }
    
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
            throw new Exception("Erreur lors de la verification de l'existence de l'utilisateur : " . $e->getMessage());
        }
    }

    /**
     * Function to check if a User is the owner of a Universe
     * 
     * @param int $universeId
     * @param int $userId
     * 
     * @return bool
     */
    public function isUserUniverseOwner($universeId, $userId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT COUNT(*) FROM `user_universe` WHERE universeId = :universeId AND userId = :userId';
                $params = [':universeId' => $universeId, ':userId' => $userId];
                break;
            case 'pgsql':
                $sql = 'SELECT COUNT(*) FROM "user_universe" WHERE "universeId" = $1 AND "userId" = $2';
                $params = [$universeId, $userId];
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }

        return $this->executeOwnershipQuery($sql, $params);
    }

    /**
     * Function to check if an image is used by other entities
     * 
     * @param string $imageFileName
     * @param int $entityId
     * @param string $entityType
     * 
     * @return bool
     */
    public function isImageUsedByOthers($imageFileName, $entityId, $entityType) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT COUNT(*) FROM `image_references` 
                        WHERE image_file_name = :imageFileName 
                        AND entity_id != :entityId
                        AND entity_type = :entityType';
                $params = [
                    ':imageFileName' => $imageFileName,
                    ':entityId' => $entityId,
                    ':entityType' => $entityType
                ];
                break;
            case 'pgsql':
                $sql = 'SELECT COUNT(*) FROM "image_references" 
                        WHERE image_file_name = $1 
                        AND entity_id != $2 
                        AND entity_type = $3';
                $params = [
                    $imageFileName,
                    $entityId,
                    $entityType
                ];
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }

        return $this->executeImageTracking($sql, $params);
    }

    /**
     * Function to add an image reference to the database for a specific entity
     * 
     * @param string $imageFileName
     * @param int $entityId
     * @param string $entityType
     * 
     * @return bool
     */
    public function addImageReference($imageFileName, $entityId, $entityType) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'INSERT INTO `image_references` (image_file_name, entity_id, entity_type) 
                        VALUES (:imageFileName, :entityId, :entityType)';
                $parameters = [
                    ':imageFileName' => $imageFileName,
                    ':entityId' => $entityId,
                    ':entityType' => $entityType,
                ];
                break;
            case 'pgsql':
                $sql = 'INSERT INTO "image_references" (image_file_name, entity_id, entity_type) 
                        VALUES (:imageFileName, :entityId, :entityType)';
                $parameters = [
                    ':imageFileName' => $imageFileName,
                    ':entityId' => $entityId,
                    ':entityType' => $entityType,
                ];
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }
    
        return $this->executeImageReferencing($sql, $parameters);
    }

    /**
     * Function to get the id of the User who owns a Universe
     * 
     * @param int $universeId
     * 
     * @return int $userId
     */
    public function getUniverseUserId($universeId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT userId FROM `user_universe` WHERE universeId = :universeId';
                $params = [':universeId' => $universeId];
                break;
            case 'pgsql':
                $sql = 'SELECT "userId" FROM "user_universe" WHERE "universeId" = $1';
                $params = [$universeId];
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }

        try {
            $result = $this->dbConnector->select($sql, $params);
        
            switch (__DB_INFOS__['database_type']) {
                case 'mysql':
                case 'sqlite':
                    $userId = $result[0]['userId'] ?? 0;
                    break;
        
                case 'pgsql':
                    $userId = $result[0]['userId'] ?? 0;
                    break;
        
                default:
                    throw new Exception("Type de base de donnees non reconnu");
            }
        
            return $userId;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la recuperation de l'utilisateur de l'univers : " . $e->getMessage());
        }
    }


    /**
     * Function to execute a query to check if a Universe already exists fora User
     * 
     * @param string $sql
     * @param array $params
     * 
     * @return bool
     */
    public function universeExistsForUser($userId, $universeName) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT COUNT(*) FROM `universe` u
                        JOIN `user_universe` uu ON u.id = uu.universeId
                        WHERE u.name = :universeName AND uu.userId = :userId';
                $params = [':universeName' => $universeName, ':userId' => $userId];
                break;
            case 'pgsql':
                $sql = 'SELECT COUNT(*) FROM "universe" u
                        JOIN "user_universe" uu ON u.id = uu."universeId"
                        WHERE u.name = $1 AND uu."userId" = $2';
                $params = [$universeName, $userId];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }

        try {
            $result = $this->dbConnector->select($sql, $params);
            return $result[0]['COUNT(*)'] > 0;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la vérification de l'existence de l'univers : " . $e->getMessage());
        }
    }
}
