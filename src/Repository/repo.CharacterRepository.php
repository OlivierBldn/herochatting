<?php // path: src/Repository/repo.CharacterRepository.php

require __DIR__ . '/../Class/class.DBConnectorFactory.php';
require __DIR__ . '/../../config/db_config.php';

class CharacterRepository
{
    private $dbConnector;
    private $dbType;

    public function __construct()
    {
        $this->dbType = $GLOBALS['dbinfos']['database_type'];
        
        $this->dbConnector = DBConnectorFactory::getConnector();
    }

    public function create($characterData)
    {
        $newCharacter = Character::fromMap($characterData);

        if ($newCharacter === null) {
            return false;
        }

        $requestUri = $_SERVER['REQUEST_URI'];

        $segments = explode('/', $requestUri);

        if(!isset($segments[3])) {
            http_response_code(400);
            echo json_encode(['message' => 'URL malformée']);
            return;
        }

        $universeId = (int) $segments[3];

        if ($universeId <= 0) {
            http_response_code(400);
            echo json_encode(['message' => 'Univers invalide']);
            return;
        }

        $name = $newCharacter->getName();
        $description = $newCharacter->getDescription();
        $image = $newCharacter->getImage();

        switch ($this->dbType) {
            case 'mysql':
            case 'sqlite':
                $sql = 'INSERT INTO `character` (name, description, image) 
                        VALUES (:name, :description, :image)';
                        
                $parameters = [
                    ':name' => $name,
                    ':description' => $description,
                    ':image' => $image,
                ];
                break;
            case 'pgsql':
                $sql = 'INSERT INTO "character" (name, description, image) 
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
                $characterId = $this->dbConnector->lastInsertRowID();

                $this->linkCharacterToUniverse($universeId, $characterId);

                return $characterId;
            } else {
                throw new Exception("Erreur lors de la création du personnage");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la création du personnage : " . $e->getMessage());
        }
    }

    public function linkCharacterToUniverse($universeId, $characterId) {
        switch ($this->dbType) {
            case 'mysql':
            case 'sqlite':
                $sql = 'INSERT INTO `universe_character`
                        VALUES (:universeId, :characterId)';
                $parameters = [
                    ':universeId' => $universeId,
                    ':characterId' => $characterId
                ];
                break;
            case 'pgsql':
                $sql = 'INSERT INTO "universe_character"
                        VALUES ($1, $2)';
                $parameters = [
                    $universeId,
                    $characterId
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
                throw new Exception("Erreur lors de l'association du personnage à l'univers");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de l'association du personnage à l'univers : " . $e->getMessage());
        }
    }
    
    public function getAll()
    {
        switch ($this->dbType) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT * FROM `character`';
                break;
            case 'pgsql':
                $sql = 'SELECT * FROM "character"';
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }

        try {
            $allCharactersArraySql = $this->dbConnector->select($sql);

            $allCharactersArrayObject = [];

            foreach ($allCharactersArraySql as $key => $character) {
                $allCharactersArrayObject[] = Character::fromMap($character);
            }

            return $allCharactersArrayObject;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération de tous les personnages : " . $e->getMessage());
        }
    }

    public function getAllByUniverseId($universeId)
    {
        // $sql = 'SELECT * FROM `character` WHERE id_universe = :id_universe';
        switch ($this->dbType) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT * FROM `character` WHERE id_universe = :id_universe';

                $params = [':id_universe' => $universeId];
                break;
            case 'pgsql':
                $sql = 'SELECT * FROM "character" WHERE id_universe = $1';

                $params = [$universeId];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }


        try {
            $characters = $this->dbConnector->select($sql, $params);
            $characterObjects = [];

            foreach ($characters as $character) {
                $characterObjects[] = Character::fromMap($character);
            }

            return $characterObjects;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des personnages de l'univers : " . $e->getMessage());
        }
    }

    public function getById($id)
    {
        switch ($this->dbType) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT * FROM `character` WHERE id = :id';

                $params = [':id' => $id];
                break;
            case 'pgsql':
                $sql = 'SELECT * FROM "character" WHERE id = $1';

                $params = [$id];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }
    
        try {
            $characterMap = $this->dbConnector->select($sql, $params);
    
            if (count($characterMap) === 1) {
                return Character::fromMap($characterMap[0]);
            } else {
                return null;
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération du personnage : " . $e->getMessage());
        }
    }

    public function update($characterId, $characterData)
    {
        $existingCharacter = $this->getById($characterId);
    
        if (!$existingCharacter) {
            throw new Exception("Personnage non trouvé");
        }
    
        $name = $characterData['name'] ?? $existingCharacter->getName();
        $description = $characterData['description'] ?? $existingCharacter->getDescription();
        $image = $characterData['image'] ?? $existingCharacter->getImage();
        $universeId = $characterData['universeId'] ?? $existingCharacter->getUniverseId();

        switch ($this->dbType) {
            case 'mysql':
            case 'sqlite':
                $sql = 'UPDATE `character` SET name = :name, description = :description, image = :image, id_universe = :id_universe WHERE id = :characterId';
                        
                $parameters = [
                    ':name' => $name,
                    ':description' => $description,
                    ':image' => $image,
                    ':id_universe' => $universeId,
                    ':characterId' => $characterId
                ];
                break;
            case 'pgsql':
                $sql = 'UPDATE "character" SET name = $1, description = $2, image = $3, id_universe = $4 WHERE id = $5';

                $parameters = [$name, $description, $image, $universeId, $characterId];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }
    
        try {
            $success = $this->dbConnector->execute($sql, $parameters);
    
            if ($success) {
                return true;
            } else {
                throw new Exception("Erreur lors de la mise à jour du personnage");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la mise à jour du personnage : " . $e->getMessage());
        }
    }

    public function delete($id)
    {
        switch ($this->dbType) {
            case 'mysql':
            case 'sqlite':
                $sql = 'DELETE FROM `character` WHERE id = :id';

                $params = [':id' => $id];
                break;
            case 'pgsql':
                $sql = 'DELETE FROM "character" WHERE id = $1';

                $parameters = [$id];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }

        $success = $this->dbConnector->execute($sql, $params);

        if (!$success) {
            throw new Exception("Erreur lors de la suppression du personnage");
        }

        return $success;
    }
}