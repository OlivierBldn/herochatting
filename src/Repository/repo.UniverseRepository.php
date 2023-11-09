<?php // path: src/Repository/repo.UniverseRepository.php

require __DIR__ . '/../Class/class.DBConnectorFactory.php';
require __DIR__ . '/../../config/db_config.php';

class UniverseRepository
{
    private $dbConnector;
    private $dbType;

    public function __construct()
    {
        $this->dbType = $GLOBALS['dbinfos']['database_type'];
        
        $this->dbConnector = DBConnectorFactory::getConnector();
    }

    public function create($universeData)
    {
        $newUniverse = Universe::fromMap($universeData);

        if ($newUniverse === null) {
            return false;
        }

        $name = $newUniverse->getName();
        $description = $newUniverse->getDescription();
        $image = $newUniverse->getImage();
        $userId = $newUniverse->getUserId();

        switch ($this->dbType) {
            case 'mysql':
            case 'sqlite':
                $sql = 'INSERT INTO `universe` (name, description, image, id_user) 
                        VALUES (:name, :description, :image, :id_user)';
        
                $parameters = [
                    ':name' => $name,
                    ':description' => $description,
                    ':image' => $image,
                    ':id_user' => $userId
                ];
                break;
            case 'pgsql':
                $sql = 'INSERT INTO "universe" (name, description, image, id_user) 
                        VALUES ($1, $2, $3, $4)';
        
                $parameters = [
                    $name,
                    $description,
                    $image,
                    $userId
                ];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }

        try {
            $success = $this->dbConnector->execute($sql, $parameters);

            if ($success) {
                $id = $this->dbConnector->lastInsertRowID();

                return $id;
            } else {
                throw new Exception("Erreur lors de la création de l'univers");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la création de l'univers : " . $e->getMessage());
        }
    }

    public function getAll()
    {
        switch ($this->dbType) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT * FROM `universe`';
                break;
            case 'pgsql':
                $sql = 'SELECT * FROM "universe"';
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }

        try {
            $allUniversesArraySql = $this->dbConnector->select($sql);

            $allUniversesArrayObject = [];

            foreach ($allUniversesArraySql as $key => $universe) {
                $allUniversesArrayObject[] = Universe::fromMap($universe);
            }

            return $allUniversesArrayObject;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération de tous les univers : " . $e->getMessage());
        }
    }

    public function getAllByUserId($userId)
    {
        switch ($this->dbType) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT * FROM `universe` WHERE id_user = :userId';

                $params = [':userId' => $userId];
                break;
                case 'pgsql':
                    $sql = 'SELECT * FROM "universe" WHERE id_user = $1';
                
                    $params = [$userId];
                break;                
            default:
                throw new Exception("Type de base de données non reconnu");
        }


        try {
            $universes = $this->dbConnector->select($sql, $params);
            $universeObjects = [];

            foreach ($universes as $universe) {
                $universeObjects[] = Universe::fromMap($universe);
            }

            return $universeObjects;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des univers de l'utilisateur : " . $e->getMessage());
        }
    }

    public function getById($id)
    {
        switch ($this->dbType) {
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
                throw new Exception("Type de base de données non reconnu");
        }
    
        try {
            $universeMap = $this->dbConnector->select($sql, $params);
    
            if (count($universeMap) === 1) {
                return Universe::fromMap($universeMap[0]);
            } else {
                return null;
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération de l'univers : " . $e->getMessage());
        }
    }

    public function update($universeId, $universeData)
    {
        $existingUniverse = $this->getById($universeId);
    
        if (!$existingUniverse) {
            throw new Exception("Univers non trouvé");
        }
    
        $name = $universeData['name'] ?? $existingUniverse->getName();
        $description = $universeData['description'] ?? $existingUniverse->getDescription();
        $image = $universeData['image'] ?? $existingUniverse->getImage();
        $userId = $universeData['userId'] ?? $existingUniverse->getUserId();
    
        switch ($this->dbType) {
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
                throw new Exception("Type de base de données non reconnu");
        }
    
        try {
            $success = $this->dbConnector->execute($sql, $parameters);
    
            if ($success) {
                return true;
            } else {
                throw new Exception("Erreur lors de la mise à jour de l'univers");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la mise à jour de l'univers : " . $e->getMessage());
        }
    }

    public function delete($id)
    {
        switch ($this->dbType) {
            case 'mysql':
            case 'sqlite':
                $sql = 'DELETE FROM `universe` WHERE id = :id';

                $parameters = [':id' => $id];
                break;
            case 'pgsql':
                $sql = 'DELETE FROM "universe" WHERE id = $1';

                $parameters = [$id];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }

        $success = $this->dbConnector->execute($sql, $parameters);

        if (!$success) {
            throw new Exception("Erreur lors de la suppression de l'univers");
        }

        return $success;
    }
}
