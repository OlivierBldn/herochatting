<?php // path: src/Repository/repo.UserRepository.php

require __DIR__ . '/../Class/class.DBConnectorFactory.php';

class UserRepository
{
    private $dbConnector;
    private $dbType;

    public function __construct()
    {
        $this->dbType = $GLOBALS['dbinfos']['database_type'];
        
        $this->dbConnector = DBConnectorFactory::getConnector($this->dbType);
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

        $sql = 'INSERT INTO user (email, password, username, firstName, lastName) 
                VALUES (:email, :password, :username, :firstName, :lastName)';

        $parameters = [
            ':email' => $email,
            ':password' => $password,
            ':username' => $username,
            ':firstName' => $firstName,
            ':lastName' => $lastName
        ];

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

    public function getById($id)
    {
        $sql = 'SELECT * FROM user WHERE id = :id';
        $params = [':id' => $id];
    
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
    
        $sql = 'UPDATE user SET email = :email, username = :username, firstName = :firstName, lastName = :lastName, password = :password WHERE id = :userId';
    
        $parameters = [
            ':email' => $email,
            ':username' => $username,
            ':firstName' => $firstName,
            ':lastName' => $lastName,
            ':userId' => $userId,
            ':password' => $password
        ];
    
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
        $sql = "DELETE FROM user WHERE id = :id";
        $params = [':id' => $id];

        $success = $this->dbConnector->execute($sql, $params);

        if (!$success) {
            throw new Exception("Erreur lors de la suppression de l'utilisateur");
        }

        return $success;
    }

    public function getAll()
    {
        $sql = 'SELECT * FROM user';

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
}