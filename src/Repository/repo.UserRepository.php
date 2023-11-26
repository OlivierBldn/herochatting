<?php // path: src/Repository/repo.UserRepository.php

require_once __DIR__ . '/repo.UniverseRepository.php';
require_once __DIR__ . '/../../config/cfg_dbConfig.php';

class UserRepository
{
    private $dbConnector;

    public function __construct()
    {
        $this->dbConnector = DBConnectorFactory::getConnector();
    }

    public function create($userData)
    {

        if (isset($userData['password'])) {
            $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        }
        
        $newUser = User::fromMap($userData);

        if ($newUser === null) {
            return false;
        }

        $email = $newUser->getEmail();
        $password = $newUser->getPassword();
        $username = $newUser->getUsername();
        $firstName = $newUser->getFirstName();
        $lastName = $newUser->getLastName();

        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'INSERT INTO `user` (email, password, username, firstName, lastName) 
                        VALUES (:email, :password, :username, :firstName, :lastName)';
                        
                $parameters = [
                    ':email' => $email,
                    ':password' => $password,
                    ':username' => $username,
                    ':firstName' => $firstName,
                    ':lastName' => $lastName
                ];
                break;
            case 'pgsql':
                $sql = 'INSERT INTO "user" (email, password, username, "firstName", "lastName") 
                        VALUES ($1, $2, $3, $4, $5)';
                                
                $parameters = [
                    $email,
                    $password,
                    $username,
                    $firstName,
                    $lastName
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
                throw new Exception("Erreur lors de la création de l'utilisateur");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la création de l'utilisateur : " . $e->getMessage());
        }
    }

    public function getAll()
    {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT * FROM `user`';
                break;
            case 'pgsql':
                $sql = 'SELECT * FROM "user"';
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }        

        try {
            $allUsersArraySql = $this->dbConnector->select($sql);

            $allUsersArrayObject = [];

            foreach ($allUsersArraySql as $key => $user) {
                $allUsersArrayObject[] = User::fromMap($user);
            }

            return $allUsersArrayObject;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération de tous les utilisateurs : " . $e->getMessage());
        }
    }

    public function getById($id)
    {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT * FROM `user` WHERE id = :id';
                        
                $params = [':id' => $id];
                break;
            case 'pgsql':
                $sql = 'SELECT * FROM "user" WHERE id = $1';
                    
                $params = [$id];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }
    
        try {
            $userMap = $this->dbConnector->select($sql, $params);
    
            if (count($userMap) === 1) {
                return User::fromMap($userMap[0]);
            } else {
                return null;
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération de l'utilisateur : " . $e->getMessage());
        }
    }

    public function getByEmail($email)
    {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT * FROM `user` WHERE email = :email';
                        
                $params = [':email' => $email];
                break;
            case 'pgsql':
                $sql = 'SELECT * FROM "user" WHERE email = $1';
                    
                $params = [$email];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }
    
        try {
            $userMap = $this->dbConnector->select($sql, $params);
    
            if (count($userMap) === 1) {
                return User::fromMap($userMap[0]);
            } else {
                return null;
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération de l'utilisateur : " . $e->getMessage());
        }
    }

    public function update($userId, $userData)
    {
        $existingUser = $this->getById($userId);
    
        if (!$existingUser) {
            throw new Exception("Utilisateur non trouvé");
        }
    
        // Utiliser les setters pour assurer un traitement correct des données
        $existingUser->setEmail($userData['email'] ?? $existingUser->getEmail());
        $existingUser->setUsername($userData['username'] ?? $existingUser->getUsername());
        $existingUser->setFirstName($userData['firstName'] ?? $existingUser->getFirstName());
        $existingUser->setLastName($userData['lastName'] ?? $existingUser->getLastName());

        // Hacher le mot de passe s'il est présent dans $userData
        // if (isset($userData['password']) && !empty($userData['password'])) {
        //     $existingUser->setPassword($userData['password']);
        // }

        if (isset($userData['password']) && !empty($userData['password'])) {
            $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
            $existingUser->setPassword($userData['password']);
        }
    
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'UPDATE `user` SET email = :email, username = :username, firstName = :firstName, lastName = :lastName, password = :password WHERE id = :userId';
                        
                $parameters = [
                    ':email' => $existingUser->getEmail(),
                    ':username' => $existingUser->getUsername(),
                    ':firstName' => $existingUser->getFirstName(),
                    ':lastName' => $existingUser->getLastName(),
                    ':userId' => $userId,
                    ':password' => $existingUser->getPassword() 
                ];
                break;
            case 'pgsql':
                case 'pgsql':
                    $sql = 'UPDATE "user" SET email = $1, username = $2, "firstName" = $3, "lastName" = $4, password = $5 WHERE id = $6';
                
                    $parameters = [
                        $existingUser->getEmail(),
                        $existingUser->getUsername(),
                        $existingUser->getFirstName(),
                        $existingUser->getLastName(),
                        $existingUser->getPassword(),
                        $userId
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
                throw new Exception("Erreur lors de la mise à jour de l'utilisateur");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la mise à jour de l'utilisateur : " . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            // Commencer une transaction
            $this->dbConnector->beginTransaction();

            $universeRepository = new UniverseRepository();

            $universesToDelete = $universeRepository->getAllByUserId($id);

            foreach ($universesToDelete as $universe) {
                $universeRepository->delete($universe->getId());
            }

            // Supprimer l'utilisateur
            switch (__DB_INFOS__['database_type']) {
                case 'mysql':
                case 'sqlite':
                    $sqlDeleteUser = 'DELETE FROM `user` WHERE id = :id';
                    break;
                case 'pgsql':
                    $sqlDeleteUser = 'DELETE FROM "user" WHERE id = $1';
                    break;
                default:
                    throw new Exception("Type de base de données non reconnu");
            }

            // Exécuter la requête de suppression de l'utilisateur
            $this->dbConnector->execute($sqlDeleteUser, __DB_INFOS__['database_type'] === 'pgsql' ? [$id] : [':id' => $id]);

            // Valider la transaction
            $this->dbConnector->commit();

            return true;
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->dbConnector->rollBack();
            throw new Exception("Erreur lors de la suppression de l'utilisateur et de ses univers : " . $e->getMessage());
        }
    }
}