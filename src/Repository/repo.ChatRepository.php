<?php // path: src/Repository/repo.ChatRepository.php

// require_once __DIR__ . '/../Class/class.DBConnectorFactory.php';
// require_once __DIR__ . '/../../config/cfg_dbConfig.php';
require_once __DIR__ . '/../Class/Builder/bldr.ChatBuilder.php';

class ChatRepository extends AbstractRepository
{
    // private $dbConnector;

    // public function __construct() {
    //     $this->dbConnector = DBConnectorFactory::getConnector();
    // }

    public function create($userId, $characterId) {
        try {
            switch (__DB_INFOS__['database_type']) {
                case 'mysql':
                case 'sqlite':
                    $sql = 'INSERT INTO chat () VALUES ()';
                    break;
                case 'pgsql':
                    $sql = 'INSERT INTO "chat" DEFAULT VALUES';
                    break;
                default:
                    throw new Exception("Type de base de données non reconnu");
            }
    
            $this->dbConnector->execute($sql);
            $chatId = $this->dbConnector->lastInsertRowID();
    
            $this->linkChatToUser($chatId, $userId);
            $this->linkChatToCharacter($chatId, $characterId);
    
            return $chatId;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la création de la conversation : " . $e->getMessage());
        }
    }
    

    public function getAll() {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT * FROM chat';
                break;
            case 'pgsql':
                $sql = 'SELECT * FROM "chat"';
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }
    
        try {
            $chatsArray = $this->dbConnector->select($sql);
            $chats = [];
            foreach ($chatsArray as $chatData) {
                $builder = new ChatBuilder();
                $chat = $builder->withId($chatData['id'])
                                ->build();
                $chats[] = $chat;
            }
            return $chats;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des conversations : " . $e->getMessage());
        }
    }

    public function getById($chatId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT * FROM chat WHERE id = :id';
                $params = [':id' => $chatId];
                break;
            case 'pgsql':
                $sql = 'SELECT * FROM "chat" WHERE id = $1';
                $params = [$chatId];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }
    
        try {
            $result = $this->dbConnector->select($sql, $params);
            if (!empty($result)) {
                $builder = new ChatBuilder();
                $chat = $builder->withId($chatId)
                                ->build();
                return $chat;
            } else {
                return null;
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération de la conversation : " . $e->getMessage());
        }
    }

    public function getByUserId($userId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT c.* FROM chat c INNER JOIN user_chat uc ON c.id = uc.chatId WHERE uc.userId = :userId';
                $params = [':userId' => $userId];
                break;
            case 'pgsql':
                $sql = 'SELECT c.* FROM "chat" c INNER JOIN "user_chat" uc ON c.id = uc."chatId" WHERE uc."userId" = $1';
                $params = [$userId];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
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
            throw new Exception("Erreur lors de la récupération des conversations par utilisateur : " . $e->getMessage());
        }
    }

    public function getByCharacterId($characterId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT c.* FROM chat c INNER JOIN character_chat cc ON c.id = cc.chatId WHERE cc.characterId = :characterId';
                $params = [':characterId' => $characterId];
                break;
            case 'pgsql':
                $sql = 'SELECT c.* FROM "chat" c INNER JOIN "character_chat" cc ON c.id = cc."chatId" WHERE cc."characterId" = $1';
                $params = [$characterId];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
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
            throw new Exception("Erreur lors de la récupération des conversations par personnage : " . $e->getMessage());
        }
    }

    public function getByUniverseId($universeId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = "SELECT c.* FROM chat c 
                        INNER JOIN character_chat cc ON c.id = cc.chatId
                        INNER JOIN `character` ch ON cc.characterId = ch.id
                        INNER JOIN universe_character uc ON ch.id = uc.characterId
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
                throw new Exception("Type de base de données non reconnu");
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
            throw new Exception("Erreur lors de la récupération des chats par univers : " . $e->getMessage());
        }
    }
    

    // public function update($chatId, Chat $chat) {
    //     switch (__DB_INFOS__['database_type']) {
    //         case 'mysql':
    //         case 'sqlite':
    //             $sql = 'UPDATE chat SET name = :name, description = :description WHERE id = :id';
    //             $params = [':name' => $chat->getName(), ':description' => $chat->getDescription(), ':id' => $chatId];
    //             break;
    //         case 'pgsql':
    //             $sql = 'UPDATE "chat" SET name = $1, description = $2 WHERE id = $3';
    //             $params = [$chat->getName(), $chat->getDescription(), $chatId];
    //             break;
    //         default:
    //             throw new Exception("Type de base de données non reconnu");
    //     }

    //     try {
    //         $this->dbConnector->execute($sql, $params);
    //         return true;
    //     } catch (Exception $e) {
    //         throw new Exception("Erreur lors de la mise à jour de la conversation: " . $e->getMessage());
    //     }
    // }

    public function delete($chatId) {
        try {
            $this->unlinkChatFromUser($chatId);
            $this->unlinkChatFromCharacter($chatId);
    
            switch (__DB_INFOS__['database_type']) {
                case 'mysql':
                case 'sqlite':
                    $sql = 'DELETE FROM chat WHERE id = :id';
                    break;
                case 'pgsql':
                    $sql = 'DELETE FROM "chat" WHERE id = $1';
                    break;
                default:
                    throw new Exception("Type de base de données non reconnu");
            }
            $params = ['id' => $chatId];
            $this->dbConnector->execute($sql, $params);
            return true;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la suppression de la conversation : " . $e->getMessage());
        }
    }    

    public function linkChatToUser($chatId, $userId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'INSERT INTO user_chat (userId, chatId) VALUES (:userId, :chatId)';
                $params = [':userId' => $userId, ':chatId' => $chatId];
                break;
            case 'pgsql':
                $sql = 'INSERT INTO "user_chat" ("userId", "chatId") VALUES ($1, $2)';
                $params = [$userId, $chatId];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }

        try {
            $this->dbConnector->execute($sql, $params);
            return true;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de l'association de la conversation à l'utilisateur : " . $e->getMessage());
        }
    }

    public function linkChatToCharacter($chatId, $characterId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'INSERT INTO character_chat (characterId, chatId) VALUES (:characterId, :chatId)';
                $params = [':characterId' => $characterId, ':chatId' => $chatId];
                break;
            case 'pgsql':
                $sql = 'INSERT INTO "character_chat" ("characterId", "chatId") VALUES ($1, $2)';
                $params = [$characterId, $chatId];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }

        try {
            $this->dbConnector->execute($sql, $params);
            return true;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de l'association de la conversation au personnage " . $e->getMessage());
        }
    }

    public function unlinkChatFromUser($chatId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'DELETE FROM user_chat WHERE chatId = :chatId';
                break;
            case 'pgsql':
                $sql = 'DELETE FROM "user_chat" WHERE "chatId" = $1';
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }
        $params = ['chatId' => $chatId];
        
        try {
            $this->dbConnector->execute($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la suppression de la liaison chat-utilisateur : " . $e->getMessage());
        }
    }
    
    public function unlinkChatFromCharacter($chatId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'DELETE FROM character_chat WHERE chatId = :chatId';
                break;
            case 'pgsql':
                $sql = 'DELETE FROM "character_chat" WHERE "chatId" = $1';
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }
        $params = ['chatId' => $chatId];
        
        try {
            $this->dbConnector->execute($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la suppression de la liaison chat-personnage : " . $e->getMessage());
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
                throw new Exception("Type de base de données non reconnu");
        }
    
        try {
            $result = $this->dbConnector->select($sql, $params);
            $count = $result[0]['COUNT(*)'] ?? 0;
            return $count > 0;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la vérification de l'existence du personnage : " . $e->getMessage());
        }
    }

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
                throw new Exception("Type de base de données non reconnu");
        }

        try {
            $characterData = $this->dbConnector->select($sql, $params);
            if (!empty($characterData)) {
                return Character::fromMap($characterData[0]);
            } else {
                throw new Exception("Personnage non trouvé pour le chatId spécifié.");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des détails du personnage : " . $e->getMessage());
        }
    }

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
                throw new Exception("Type de base de données non reconnu");
        }
    
        return $this->executeOwnershipQuery($sql, $params);
    }
}