<?php // path: src/Repository/CharacterRepository.php

// require __DIR__ . '/../Class/factory.dbconnector.php';
require __DIR__ . '/../Class/DBConnectorFactory.php';

class CharacterRepository
{
    private $dbConnector;
    private $dbType;

    public function __construct()
    {
        // $this->dbType = $GLOBALS['dbinfos']['database_type'];
        
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
        $universeId = $newCharacter->getUniverseId();

        $sql = 'INSERT INTO `character` (name, description, image, id_universe) 
                VALUES (:name, :description, :image, :id_universe)';

        $parameters = [
            ':name' => $name,
            ':description' => $description,
            ':image' => $image,
            ':id_universe' => $universeId
        ];

        try {
            $success = $this->dbConnector->execute($sql, $parameters);

            if ($success) {
                $id = $this->dbConnector->lastInsertRowID();

                return $id;
            } else {
                throw new Exception("Erreur lors de la création du personnage");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la création du personnage : " . $e->getMessage());
        }
    }
    
    public function getAll()
    {
        $sql = 'SELECT * FROM `character`';

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
        $sql = 'SELECT * FROM `character` WHERE id_universe = :id_universe';
        $params = [':id_universe' => $universeId];

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
        $sql = 'SELECT * FROM `character` WHERE id = :id';
        $params = [':id' => $id];
    
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
    
        $sql = 'UPDATE `character` SET name = :name, description = :description, image = :image, id_universe = :id_universe WHERE id = :characterId';
    
        $parameters = [
            ':name' => $name,
            ':description' => $description,
            ':image' => $image,
            ':id_universe' => $universeId,
            ':characterId' => $characterId
        ];
    
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
        $sql = "DELETE FROM `character` WHERE id = :id";
        $params = [':id' => $id];

        $success = $this->dbConnector->execute($sql, $params);

        if (!$success) {
            throw new Exception("Erreur lors de la suppression du personnage");
        }

        return $success;
    }
}