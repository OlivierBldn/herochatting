<?php // path: src/Repository/repo.UniverseRepository.php

require_once __DIR__ . '/../Class/class.DBConnectorFactory.php';
// require __DIR__ . '/../../config/cfg_dbConfig.php';
require_once __DIR__ . '/../../config/cfg_dbConfig.php';

class UniverseRepository
{
    private $dbConnector;
    // private $dbType;

    public function __construct()
    {
        // $this->dbType = $GLOBALS['dbinfos']['database_type'];

        // $this->dbType = __DB_INFOS__['database_type'];
        
        $this->dbConnector = DBConnectorFactory::getConnector();
    }

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
                throw new Exception("Type de base de données non reconnu");
        }

        try {
            $success = $this->dbConnector->execute($sql, $parameters);

            if ($success) {

                $universeId= $this->dbConnector->lastInsertRowID();
                $requestUri = $_SERVER['REQUEST_URI'];
                $segments = explode('/', $requestUri);
                $userId = $segments[3];

                $this->linkUniverseToUser($userId, $universeId);

                return $universeId;
            } else {
                throw new Exception("Erreur lors de la création de l'univers");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la création de l'univers : " . $e->getMessage());
        }
    }

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
                throw new Exception("Type de base de données non reconnu");
        }
    
        try {
            $success = $this->dbConnector->execute($sql, $parameters);

            if ($success) {
                return true;
            } else {
                throw new Exception("Erreur lors de l'association de l'univers à l'utilisateur");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de l'association de l'univers à l'utilisateur : " . $e->getMessage());
        }
    }

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
                throw new Exception("Type de base de données non reconnu");
        }
    
        try {
            $universes = $this->dbConnector->select($sql, $parameters);
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
                throw new Exception("Type de base de données non reconnu");
        }

        try {
            $universe = $this->dbConnector->select($sql, $params);

            // Convertir le résultat en objet Universe si un univers est trouvé
            return $universe ? Universe::fromMap($universe[0]) : null;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des univers par nom : " . $e->getMessage());
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

    public function delete($universeId) {
        try {
            // Commencer une transaction
            $this->dbConnector->beginTransaction();

            // Supprimer les enregistrements liés dans user_universe
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
                    throw new Exception("Type de base de données non reconnu");
            }
            $this->dbConnector->execute($sql, $params);

            // Supprimer l'univers
            switch (__DB_INFOS__['database_type']) {
                case 'mysql':
                case 'sqlite':
                    $sql = 'DELETE FROM `universe` WHERE id = :universeId';
                    break;
                case 'pgsql':
                    $sql = 'DELETE FROM "universe" WHERE id = $1';
                    break;
                // Pas besoin de default case ici car déjà géré ci-dessus
            }
            $this->dbConnector->execute($sql, $params);

            // Valider la transaction
            $this->dbConnector->commit();

            return true;
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->dbConnector->rollBack();
            throw new Exception("Erreur lors de la suppression de l'univers : " . $e->getMessage());
        }
    }

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
                throw new Exception("Type de base de données non reconnu");
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
                    throw new Exception("Type de base de données non reconnu");
            }
        
            return $count > 0;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la vérification de l'existence de l'utilisateur : " . $e->getMessage());
        }
    }
}
