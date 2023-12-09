<?php // path: src/Repository/repo.UserRepository.php

/**
 * Class UserRepository
 * 
 * This class is the repository for the User class.
 * It contains all the queries to the database regarding the User class.
 * 
 */
class UserRepository extends AbstractRepository
{
    /**
     * Function to create a new user
     *
     * @param array $userData
     * @return int
     */
    public function create($userData)
    {
        // Ensure that the password is hashed
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
                throw new Exception("Type de base de donnees non reconnu");
        }

        try {
            $success = $this->dbConnector->execute($sql, $parameters);

            if ($success) {
                $id = $this->dbConnector->lastInsertRowID();

                return $id;
            } else {
                throw new Exception("Erreur lors de la creation de l'utilisateur");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la creation de l'utilisateur : " . $e->getMessage());
        }
    }

    /**
     * Function to get all the users
     *
     * @return array
     */
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
                throw new Exception("Type de base de donnees non reconnu");
        }        

        try {
            $allUsersArraySql = $this->dbConnector->select($sql);

            $allUsersArrayObject = [];

            foreach ($allUsersArraySql as $key => $user) {
                $allUsersArrayObject[] = User::fromMap($user);
            }

            return $allUsersArrayObject;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la recuperation de tous les utilisateurs : " . $e->getMessage());
        }
    }

    /**
     * Function to get a User by its id
     *
     * @param int $id
     * @return User|null
     */
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
                throw new Exception("Type de base de donnees non reconnu");
        }
    
        try {
            $userMap = $this->dbConnector->select($sql, $params);
    
            if (count($userMap) === 1) {
                return User::fromMap($userMap[0]);
            } else {
                return null;
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la recuperation de l'utilisateur : " . $e->getMessage());
        }
    }

    /**
     * Function to get a User by its email
     *
     * @param string $email
     * @return User|null
     */
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
                throw new Exception("Type de base de donnees non reconnu");
        }
    
        try {
            $userMap = $this->dbConnector->select($sql, $params);
    
            if (count($userMap) === 1) {
                return User::fromMap($userMap[0]);
            } else {
                return null;
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la recuperation de l'utilisateur : " . $e->getMessage());
        }
    }

    /**
     * Function to update a User
     * 
     * @param int $userId
     * @param array $userData
     * 
     * @return bool
     */
    public function update($userId, $userData)
    {
        $existingUser = $this->getById($userId);
    
        if (!$existingUser) {
            throw new Exception("Utilisateur non trouve");
        }
    
        $existingUser->setEmail($userData['email'] ?? $existingUser->getEmail());
        $existingUser->setUsername($userData['username'] ?? $existingUser->getUsername());
        $existingUser->setFirstName($userData['firstName'] ?? $existingUser->getFirstName());
        $existingUser->setLastName($userData['lastName'] ?? $existingUser->getLastName());

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
                throw new Exception("Type de base de donnees non reconnu");
        }
    
        try {
            $success = $this->dbConnector->execute($sql, $parameters);
    
            if ($success) {
                return true;
            } else {
                throw new Exception("Erreur lors de la mise a jour de l'utilisateur");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la mise a jour de l'utilisateur : " . $e->getMessage());
        }
    }

    /**
     * Function to delete a User
     * 
     * @param int $id
     * 
     * @return bool
     */
    public function delete($id)
    {
        try {
            // Begin transaction to execute multiple queries
            $this->dbConnector->beginTransaction();

            $universeRepository = new UniverseRepository();

            $universesToDelete = $universeRepository->getAllByUserId($id);

            // Delete all the universes of the user
            foreach ($universesToDelete as $universe) {
                $universeRepository->delete($universe->getId());
            }

            // Delete the user
            switch (__DB_INFOS__['database_type']) {
                case 'mysql':
                case 'sqlite':
                    $sqlDeleteUser = 'DELETE FROM `user` WHERE id = :id';
                    break;
                case 'pgsql':
                    $sqlDeleteUser = 'DELETE FROM "user" WHERE id = $1';
                    break;
                default:
                    throw new Exception("Type de base de donnees non reconnu");
            }

            // Execute the query to delete the user regarding the database type
            $this->dbConnector->execute($sqlDeleteUser, __DB_INFOS__['database_type'] === 'pgsql' ? [$id] : [':id' => $id]);

            // Commit the transaction
            $this->dbConnector->commit();

            return true;
        } catch (Exception $e) {
            // Cancel the transaction if an error occurs
            $this->dbConnector->rollBack();
            throw new Exception("Erreur lors de la suppression de l'utilisateur et de ses univers : " . $e->getMessage());
        }
    }

    /**
     * Function to check if a User is the owner of the requested User entity
     * 
     * @param int $selfId
     * @param int $userId
     * 
     * @return bool
     */
    public function isUserSelfOwner($selfId, $userId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT COUNT(*) FROM `user` WHERE id = :userId AND id = :selfId';
                $params = [':userId' => $userId, ':selfId' => $selfId];
                break;
            case 'pgsql':
                $sql = 'SELECT COUNT(*) FROM "user" WHERE id = $1 AND id = $2';
                $params = [$userId, $selfId];
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }

        return $this->executeOwnershipQuery($sql, $params);
    }
}