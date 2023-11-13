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

        $name = $newCharacter->getName();
        $description = $newCharacter->getDescription();
        $image = $newCharacter->getImage();
        $universeId = $characterData['universeId'];

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
                $sql = 'SELECT c.* FROM `character` c 
                        INNER JOIN `universe_character` uc ON c.id = uc.characterId 
                        WHERE uc.universeId = :universeId';
                $parameters = [':universeId' => $universeId];
                break;
            case 'pgsql':
                $sql = 'SELECT c.* FROM "character" c 
                        INNER JOIN "universe_character" uc ON c.id = uc.characterId 
                        WHERE uc."universeId" = $1';
                $parameters = [$universeId];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }


        try {
            $characters = $this->dbConnector->select($sql, $parameters);
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

    public function getByName($name)
    {
        switch ($this->dbType) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT * FROM `character` WHERE name = :name';
                $params = [':name' => $name];
                break;
            case 'pgsql':
                $sql = 'SELECT * FROM "character" WHERE name = $1';
                $params = [$name];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }

        try {
            $character = $this->dbConnector->select($sql, $params);

            // Convertir le résultat en objet Character si un personnage est trouvé
            return $character ? Character::fromMap($character[0]) : null;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des personnages par nom : " . $e->getMessage());
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

        switch ($this->dbType) {
            case 'mysql':
            case 'sqlite':
                $sql = 'UPDATE `character` SET name = :name, description = :description, image = :image WHERE id = :characterId';
                        
                $parameters = [
                    ':name' => $name,
                    ':description' => $description,
                    ':image' => $image,
                    ':characterId' => $characterId
                ];
                break;
            case 'pgsql':
                $sql = 'UPDATE "character" SET name = $1, description = $2, image = $3 WHERE id = $4';

                $parameters = [$name, $description, $image, $characterId];
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

    public function delete($characterId) {
        try {
            // Commencer une transaction
            $this->dbConnector->beginTransaction();
    
            // Supprimer les enregistrements liés dans universe_character (si applicable)
            switch ($this->dbType) {
                case 'mysql':
                case 'sqlite':
                    $sql = 'DELETE FROM `universe_character` WHERE characterId = :characterId';
                    $params = [':characterId' => $characterId];
                    break;
                case 'pgsql':
                    $sql = 'DELETE FROM "universe_character" WHERE "characterId" = $1';
                    $params = [$characterId];
                    break;
                default:
                    throw new Exception("Type de base de données non reconnu");
            }
            $this->dbConnector->execute($sql, $params);
    
            // Supprimer le personnage
            switch ($this->dbType) {
                case 'mysql':
                case 'sqlite':
                    $sql = 'DELETE FROM `character` WHERE id = :characterId';
                    break;
                case 'pgsql':
                    $sql = 'DELETE FROM "character" WHERE id = $1';
                    break;
            }
            $this->dbConnector->execute($sql, $params);
    
            // Valider la transaction
            $this->dbConnector->commit();
    
            return true;
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->dbConnector->rollBack();
            throw new Exception("Erreur lors de la suppression du personnage : " . $e->getMessage());
        }
    }

    public function universeExists($universeId) {
        // La requête SQL varie en fonction du type de la base de données
        switch ($this->dbType) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT COUNT(*) FROM `universe` WHERE id = :id';
                $params = [':id' => $universeId];
                break;
            case 'pgsql':
                $sql = 'SELECT COUNT(*) FROM "universe" WHERE id = $1';
                $params = [$universeId];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }

        try {
            $result = $this->dbConnector->select($sql, $params);
        
            switch ($this->dbType) {
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
            throw new Exception("Erreur lors de la vérification de l'existence de l'univers: " . $e->getMessage());
        }
    }
}