<?php // path: src/Repository/repo.ChatRepository.php

// require_once __DIR__ . '/../Class/class.DBConnectorFactory.php';
// require_once __DIR__ . '/../../config/cfg_dbConfig.php';
require_once __DIR__ . '/../Class/Builder/bldr.ChatBuilder.php';

/**
 * Class ChatRepository
 * 
 * This class is the repository for the chats.
 * It contains all the queries to the database regarding the Chats.
 * 
 */
class ChatRepository extends AbstractRepository
{
    /**
     * Function to create a new chat
     *
     * @param int $userId
     * @param int $characterId
     * 
     * @return int
     */
    public function create(Chat $chat) {
        try {
            switch (__DB_INFOS__['database_type']) {
                case 'mysql':
                    $sql = 'INSERT INTO `chat` SET `id` = NULL';
                    break;
                case 'sqlite':
                    $sql = 'INSERT INTO `chat` DEFAULT VALUES';
                    break;
                case 'pgsql':
                    $sql = 'INSERT INTO "chat" DEFAULT VALUES';
                default:
                    throw new Exception("Type de base de données non reconnu");
            }
    
            $this->dbConnector->execute($sql);
            $chatId = $this->dbConnector->lastInsertRowID();
    
            // Get the participants of the chat and link them to the chat
            foreach ($chat->getParticipants() as $participant) {
                if ($participant instanceof User) {
                    $this->linkChatToUser($chatId, $participant->getId());
                } elseif ($participant instanceof Character) {
                    $this->linkChatToCharacter($chatId, $participant->getId());
                }
            }
    
            return $chatId;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la création de la conversation : " . $e->getMessage());
        }
    }

    
    /**
     * Function to get all the chats
     *
     * @return array
     */
    public function getAll() {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT * FROM `chat`';
                break;
            case 'pgsql':
                $sql = 'SELECT * FROM "chat"';
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }
    
        try {
            $chatsArray = $this->dbConnector->select($sql);
            $chats = [];

            foreach ($chatsArray as $chatData) {
                $builder = new ChatBuilder();
                $chat = $builder->withId($chatData['id'])
                                    ->loadParticipants($chatData['id'])
                                    ->loadMessages($chatData['id'])
                                    ->build();
                $chats[] = $chat;
            }
            return $chats;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la recuperation des conversations : " . $e->getMessage());
        }
    }


    /**
     * Function to get a Chat by its id
     *
     * @param int $chatId
     * 
     * @return Chat | null
     */
    public function getById($chatId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT * FROM `chat` WHERE id = :chatId';
                $params = [':chatId' => $chatId];
                break;
            case 'pgsql':
                $sql = 'SELECT * FROM "chat" WHERE id = $1';
                $params = [$chatId];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }
        
        try {
            $result= $this->dbConnector->select($sql, $params);

            if (!empty($result)) {
                $chatData = $result[0];
                $chat = new Chat();
                $builder = new ChatBuilder();
                $chat = $builder->withId($chatData['id'])
                                    ->loadParticipants($chatData['id'])
                                    ->loadMessages($chatData['id'])
                                    ->build();
                return $chat;
            } else {
                return null;
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération du chat : " . $e->getMessage());
        }
    }


    /**
     * Function to get all the chats of a User
     *
     * @param int $userId
     * @return Chat[] $chats
     */
    public function getByUserId($userId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT c.* FROM `chat` c 
                        JOIN `user_chat` uc ON c.id = uc.chatId 
                        WHERE uc.userId = :userId';
                break;
            case 'pgsql':
                $sql = 'SELECT c.* FROM "chat" c 
                        JOIN "user_chat" uc ON c.id = uc."chatId" 
                        WHERE uc."userId" = $1';
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }

        $params = [':userId' => $userId];
        
        try {
            $chatRows = $this->dbConnector->select($sql, $params);
            $chats = [];

            foreach ($chatRows as $chatRow) {
                $chat = new Chat();
                $chat->setId($chatRow['id']);

                $chats[] = $chat;
            }

            return $chats;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des chats pour l'utilisateur : " . $e->getMessage());
        }
    }



    /**
     * Function to get a Chat by a Character id
     *
     * @param int $characterId
     * 
     * @return Chat
     */
    public function getByCharacterId($characterId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT c.* FROM `chat` c INNER JOIN `character_chat` cc ON c.id = cc.chatId WHERE cc.characterId = :characterId';
                $params = [':characterId' => $characterId];
                break;
            case 'pgsql':
                $sql = 'SELECT c.* FROM "chat" c INNER JOIN "character_chat" cc ON c.id = cc."chatId" WHERE cc."characterId" = $1';
                $params = [$characterId];
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }
    
        try {
            $chatsArray = $this->dbConnector->select($sql, $params);
            $chats = [];
            foreach ($chatsArray as $chatData) {
                $builder = new ChatBuilder();
                $chat = $builder->withId($chatData['id'])
                                ->loadMessages($chatData['id'])
                                ->build();
                $chats[] = $chat;
            }
            return $chats;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la recuperation des conversations par personnage : " . $e->getMessage());
        }
    }

    /**
     * Function to get a Chat by a Universe id
     *
     * @param int $universeId
     * 
     * @return Chat
     */
    public function getByUniverseId($universeId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = "SELECT c.* FROM `chat` c 
                        INNER JOIN `character_chat` cc ON c.id = cc.chatId
                        INNER JOIN `character` ch ON cc.characterId = ch.id
                        INNER JOIN `universe_character` uc ON ch.id = uc.characterId
                        WHERE uc.universeId = :universeId";
                $params = [':universeId' => $universeId];
                break;
            case 'pgsql':
                $sql = "SELECT c.* FROM \"chat\" c 
                        INNER JOIN \"character_chat\" cc ON c.id = cc.\"chatId\"
                        INNER JOIN \"character\" ch ON cc.\"characterId\" = ch.id
                        INNER JOIN \"universe_character\" uc ON ch.id = uc.\"characterId\"
                        WHERE uc.\"universeId\" = $1";
                $params = [$universeId];
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }
    
        try {
            $chatsArray = $this->dbConnector->select($sql, $params);
            $chats = [];
            foreach ($chatsArray as $chatData) {
                $builder = new ChatBuilder();
                $chat = $builder->withId($chatData['id'])
                                ->loadMessages($chatData['id'])
                                ->build();
                $chats[] = $chat;
            }
            return $chats;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la recuperation des chats par univers : " . $e->getMessage());
        }
    }

    /**
     * Function to delete a Chat by its id
     *
     * @param int $chatId
     * 
     * @return bool
     */
    public function delete($chatId) {
        try {
            $this->unlinkChatFromUser($chatId);
            $this->unlinkChatFromCharacter($chatId);
    
            switch (__DB_INFOS__['database_type']) {
                case 'mysql':
                case 'sqlite':
                    $sql = 'DELETE FROM `chat` WHERE id = :id';
                    break;
                case 'pgsql':
                    $sql = 'DELETE FROM "chat" WHERE id = $1';
                    break;
                default:
                    throw new Exception("Type de base de donnees non reconnu");
            }
            $params = ['id' => $chatId];
            $this->dbConnector->execute($sql, $params);
            return true;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la suppression de la conversation : " . $e->getMessage());
        }
    }    

    /**
     * Function to link a Chat to a User
     *
     * @param int $chatId
     * @param int $userId
     * 
     * @return bool
     */
    public function linkChatToUser($chatId, $userId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'INSERT INTO `user_chat` (userId, chatId) VALUES (:userId, :chatId)';
                $params = [':userId' => $userId, ':chatId' => $chatId];
                break;
            case 'pgsql':
                $sql = 'INSERT INTO "user_chat" ("userId", "chatId") VALUES ($1, $2)';
                $params = [$userId, $chatId];
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }

        try {
            $this->dbConnector->execute($sql, $params);
            return true;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de l'association de la conversation a l'utilisateur : " . $e->getMessage());
        }
    }

    /**
     * Function to link a Chat to a Character
     *
     * @param int $chatId
     * @param int $characterId
     * 
     * @return bool
     */
    public function linkChatToCharacter($chatId, $characterId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'INSERT INTO `character_chat` (characterId, chatId) VALUES (:characterId, :chatId)';
                $params = [':characterId' => $characterId, ':chatId' => $chatId];
                break;
            case 'pgsql':
                $sql = 'INSERT INTO "character_chat" ("characterId", "chatId") VALUES ($1, $2)';
                $params = [$characterId, $chatId];
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }

        try {
            $this->dbConnector->execute($sql, $params);
            return true;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de l'association de la conversation au personnage " . $e->getMessage());
        }
    }

    /**
     * Function to unlink a Chat from a User
     *
     * @param int $chatId
     * 
     * @return bool
     */
    public function unlinkChatFromUser($chatId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'DELETE FROM `user_chat` WHERE chatId = :chatId';
                break;
            case 'pgsql':
                $sql = 'DELETE FROM "user_chat" WHERE "chatId" = $1';
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }
        $params = ['chatId' => $chatId];
        
        try {
            $this->dbConnector->execute($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la suppression de la liaison chat-utilisateur : " . $e->getMessage());
        }
    }
    
    /**
     * Function to unlink a Chat from a Character
     *
     * @param int $chatId
     * 
     * @return bool
     */
    public function unlinkChatFromCharacter($chatId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'DELETE FROM `character_chat` WHERE `chatId` = :chatId';
                break;
            case 'pgsql':
                $sql = 'DELETE FROM "character_chat" WHERE "chatId" = $1';
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }
        $params = ['chatId' => $chatId];
        
        try {
            $this->dbConnector->execute($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la suppression de la liaison chat-personnage : " . $e->getMessage());
        }
    }

    /**
     * Function to check if a User exists
     *
     * @param int $userId
     * 
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
     * Function to check if a Character exists
     *
     * @param int $characterId
     * 
     * @return bool
     */
    public function characterExists($characterId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT COUNT(*) FROM `character` WHERE id = :characterId';
                $params = [':characterId' => $characterId];
                break;
            case 'pgsql':
                $sql = 'SELECT COUNT(*) FROM "character" WHERE id = $1';
                $params = [$characterId];
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }
    
        try {
            $result = $this->dbConnector->select($sql, $params);
            $count = $result[0]['COUNT(*)'] ?? 0;
            return $count > 0;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la verification de l'existence du personnage : " . $e->getMessage());
        }
    }

    /** 
     * Function to get the details of a Character by its linked chatId
     * 
     * @param int $chatId
     * 
     * @return Character
     */
    public function getCharacterDetailsByChatId($chatId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT c.* FROM `character` AS c 
                        JOIN `character_chat` AS cc ON c.id = cc.characterId 
                        WHERE cc.chatId = :chatId';
                $params = [':chatId' => $chatId];
                break;
            case 'pgsql':
                $sql = 'SELECT c.* FROM "character" AS c 
                        JOIN "character_chat" AS cc ON c.id = cc.characterId 
                        WHERE cc.chatId = $1';
                $params = [$chatId];
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }

        try {
            $characterData = $this->dbConnector->select($sql, $params);
            if (!empty($characterData)) {
                return Character::fromMap($characterData[0]);
            } else {
                throw new Exception("Personnage non trouve pour le chatId specifie.");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la recuperation des details du personnage : " . $e->getMessage());
        }
    }

    /**
     * Function to check if a User is the owner of a Chat
     * 
     * @param int $chatId
     * @param int $userId
     * 
     * @return bool
     */
    public function isUserChatOwner($chatId, $userId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT COUNT(*) FROM `user_chat` WHERE chatId = :chatId AND userId = :userId';
                $params = [':chatId' => $chatId, ':userId' => $userId];
                break;
            case 'pgsql':
                $sql = 'SELECT COUNT(*) FROM "user_chat" WHERE id = $1 AND "userId" = $2';
                $params = [$chatId, $userId];
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }
    
        return $this->executeOwnershipQuery($sql, $params);
    }

    public function getParticipantsByChatId($chatId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $userSql = 'SELECT u.* FROM `user` u JOIN `user_chat` uc ON u.id = uc.userId WHERE uc.chatId = :chatId';
                $characterSql = 'SELECT c.* FROM `character` c JOIN `character_chat` cc ON c.id = cc.characterId WHERE cc.chatId = :chatId';
                break;
            case 'pgsql':
                $userSql = 'SELECT u.* FROM "user" u JOIN "user_chat" uc ON u.id = uc."userId" WHERE uc."chatId" = $1';
                $characterSql = 'SELECT c.* FROM "character" c JOIN "character_chat" cc ON c.id = cc."characterId" WHERE cc."chatId" = $1';
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }
    
        $params = [':chatId' => $chatId];
    
        try {
            $participants = [];
    
            $usersArray = $this->dbConnector->select($userSql, $params);
            foreach ($usersArray as $userData) {
                $participants[] = User::fromMap($userData);
            }
    
            $charactersArray = $this->dbConnector->select($characterSql, $params);
            foreach ($charactersArray as $characterData) {
                $participants[] = Character::fromMap($characterData);
            }
    
            return $participants;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des participants de la conversation : " . $e->getMessage());
        }
    } 
    
    
    /**
     * Check if a Chat exists for a Character
     *
     * @param int $characterId
     * @return bool
     */
    public function chatExistsForCharacter($characterId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT COUNT(*) FROM `character_chat` WHERE characterId = :characterId';
                $params = [':characterId' => $characterId];
                break;
            case 'pgsql':
                $sql = 'SELECT COUNT(*) FROM "character_chat" WHERE "characterId" = $1';
                $params = [$characterId];
                break;
            default:
                throw new Exception("Type de base de donnees non reconnu");
        }

        try {
            $result = $this->dbConnector->select($sql, $params);
            return $result[0]['COUNT(*)'] > 0;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la vérification de l'existence de la conversation: " . $e->getMessage());
        }
    }
}