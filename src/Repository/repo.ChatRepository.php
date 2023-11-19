<?php // path: src/Repository/repo.ChatRepository.php

require_once __DIR__ . '/../Class/class.DBConnectorFactory.php';
require_once __DIR__ . '/../../config/cfg_dbConfig.php';

class ChatRepository {
    private $dbConnector;

    public function __construct() {
        $this->dbConnector = DBConnectorFactory::getConnector();
    }

    public function create(Chat $chat, $userId, $characterId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'INSERT INTO chat (name, description) VALUES (:name, :description)';
                $params = [':name' => $chat->getName(), ':description' => $chat->getDescription()];
                break;
            case 'pgsql':
                $sql = 'INSERT INTO "chat" (name, description) VALUES ($1, $2)';
                $params = [$chat->getName(), $chat->getDescription()];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }

        try {
            $this->dbConnector->execute($sql, $params);
            $chatId = $this->dbConnector->lastInsertRowID();

            $this->llinkChatToUser($chatId, $userId);
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
                $chats[] = Chat::fromMap($chatData);
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
                return Chat::fromMap($result[0]);
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
                $chats[] = Chat::fromMap($chatData);
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
                $chats[] = Chat::fromMap($chatData);
            }
            return $chats;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des conversations par personnage : " . $e->getMessage());
        }
    }

    public function update($chatId, Chat $chat) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'UPDATE chat SET name = :name, description = :description WHERE id = :id';
                $params = [':name' => $chat->getName(), ':description' => $chat->getDescription(), ':id' => $chatId];
                break;
            case 'pgsql':
                $sql = 'UPDATE "chat" SET name = $1, description = $2 WHERE id = $3';
                $params = [$chat->getName(), $chat->getDescription(), $chatId];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }

        try {
            $this->dbConnector->execute($sql, $params);
            return true;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la mise à jour de la conversation: " . $e->getMessage());
        }
    }

    public function delete($chatId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'DELETE FROM chat WHERE id = :id';
                $params = [':id' => $chatId];
                break;
            case 'pgsql':
                $sql = 'DELETE FROM "chat" WHERE id = $1';
                $params = [$chatId];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }

        try {
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
}