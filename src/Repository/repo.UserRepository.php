<?php // path: src/Repository/repo.UserRepository.php

require __DIR__ . '/../Class/class.DBConnectorFactory.php';
require __DIR__ . '/../../config/db_config.php';

class UserRepository
{
    private $dbConnector;
    private $dbType;

    public function __construct()
    {
        $this->dbType = $GLOBALS['dbinfos']['database_type'];
        
        $this->dbConnector = DBConnectorFactory::getConnector();
    }

    public function create($userData)
    {
        $newUser = User::fromMap($userData);

        if ($newUser === null) {
            return false;
        }

        $email = $newUser->getEmail();
        $password = $newUser->getPassword();
        $username = $newUser->getUsername();
        $firstName = $newUser->getFirstName();
        $lastName = $newUser->getLastName();

        switch ($this->dbType) {
            case 'mysql':
            case 'sqlite':
                $sql = 'INSERT INTO "user" (email, password, username, firstName, lastName) 
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
        switch ($this->dbType) {
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
        switch ($this->dbType) {
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

    public function update($userId, $userData)
    {
        $existingUser = $this->getById($userId);
    
        if (!$existingUser) {
            throw new Exception("Utilisateur non trouvé");
        }
    
        $email = $userData['email'] ?? $existingUser->getEmail();
        $username = $userData['username'] ?? $existingUser->getUsername();
        $firstName = $userData['firstName'] ?? $existingUser->getFirstName();
        $lastName = $userData['lastName'] ?? $existingUser->getLastName();
        $password = $userData['password'] ?? $existingUser->getPassword();
    
        switch ($this->dbType) {
            case 'mysql':
            case 'sqlite':
                $sql = 'UPDATE `user` SET email = :email, username = :username, firstName = :firstName, lastName = :lastName, password = :password WHERE id = :userId';
                        
                $parameters = [
                    ':email' => $email,
                    ':username' => $username,
                    ':firstName' => $firstName,
                    ':lastName' => $lastName,
                    ':userId' => $userId,
                    ':password' => $password
                ];
                break;
            case 'pgsql':
                case 'pgsql':
                    $sql = 'UPDATE "user" SET email = $1, username = $2, "firstName" = $3, "lastName" = $4, password = $5 WHERE id = $6';
                
                    $parameters = [$email, $username, $firstName, $lastName, $password, $userId];
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
        switch ($this->dbType) {
            case 'mysql':
            case 'sqlite':
                $sql = 'DELETE FROM `user` WHERE id = :id';
                        
                $params = [':id' => $id];     
                break;
            case 'pgsql':
                $sql = 'DELETE FROM "user" WHERE id = $1';

                $params = [$id];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }   

        $success = $this->dbConnector->execute($sql, $params);

        if (!$success) {
            throw new Exception("Erreur lors de la suppression de l'utilisateur");
        }

        return $success;
    }
}