<?php // path: src/Repository/repo.MessageRepository.php

require_once __DIR__ . '/../Class/class.DBConnectorFactory.php';
require_once __DIR__ . '/../../config/cfg_dbConfig.php';

class MessageRepository {
    private $dbConnector;

    public function __construct() {
        $this->dbConnector = DBConnectorFactory::getConnector();
    }

    public function create($messageData, $chatId) {
        $content = $messageData['content'];
        $createdAt = $messageData['createdAt']->format('Y-m-d H:i:s');
        $isHuman = $messageData['isHuman'];

        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'INSERT INTO `message` (content, createdAt, is_human) VALUES (:content, :createdAt, :isHuman)';
                $params = [':content' => $content, ':createdAt' => $createdAt, ':isHuman' => $isHuman];
                break;
            case 'pgsql':
                $sql = 'INSERT INTO "message" (content, "createdAt", "is_human") VALUES ($1, $2, $3)';
                $params = [$content, $createdAt, $isHuman];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }

        try {
            $this->dbConnector->execute($sql, $params);
            $messageId = $this->dbConnector->lastInsertRowID();

            $this->linkMessageToChat($messageId, $chatId);

            return $messageId;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la création du message: " . $e->getMessage());
        }
    }

    private function linkMessageToChat($messageId, $chatId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'INSERT INTO `chat_message` (chatId, messageId) VALUES (:chatId, :messageId)';
                $params = [':chatId' => $chatId, ':messageId' => $messageId];
                break;
            case 'pgsql':
                $sql = 'INSERT INTO "chat_message" ("chatId", "messageId") VALUES ($1, $2)';
                $params = [$chatId, $messageId];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }

        try {
            $this->dbConnector->execute($sql, $params);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de l'association du message à la conversation: " . $e->getMessage());
        }
    }

    public function getAll() {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT * FROM `message`';
                break;
            case 'pgsql':
                $sql = 'SELECT * FROM "message"';
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }

        try {
            $allMessagesArraySql = $this->dbConnector->select($sql);

            $allMessagesArrayObject = [];

            foreach ($allMessagesArraySql as $message) {
                $allMessagesArrayObject[] = Message::fromMap($message);
            }

            return $allMessagesArrayObject;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération de tous les messages : " . $e->getMessage());
        }
    }

    public function getById($messageId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT * FROM `message` WHERE id = :id';
                $params = [':id' => $messageId];
                break;
            case 'pgsql':
                $sql = 'SELECT * FROM "message" WHERE id = $1';
                $params = [$messageId];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }

        try {
            $messageMap = $this->dbConnector->select($sql, $params);
            if (count($messageMap) === 1) {
                return Message::fromMap($messageMap[0]);
            } else {
                return null;
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération du message : " . $e->getMessage());
        }
    }

    public function getMessagesByChatId($chatId) {

        if (!$this->chatExists($chatId)) {
            throw new Exception("Chat not found");
        }

        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT m.* FROM message m 
                        INNER JOIN chat_message cm ON m.id = cm.messageId 
                        WHERE cm.chatId = :chatId';
                $params = [':chatId' => $chatId];
                break;
            case 'pgsql':
                $sql = 'SELECT m.* FROM "message" m 
                        INNER JOIN "chat_message" cm ON m.id = cm."messageId" 
                        WHERE cm."chatId" = $1';
                $params = [$chatId];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }

        try {
            $result = $this->dbConnector->select($sql, $params);
            $messages = [];
            foreach ($result as $row) {
                $messages[] = Message::fromMap($row);
            }
            return $messages;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des messages : " . $e->getMessage());
        }
    }

    public function update($messageId, $messageData) {
        $content = $messageData['content'];

        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'UPDATE `message` SET content = :content WHERE id = :messageId';
                $params = [':content' => $content, ':messageId' => $messageId];
                break;
            case 'pgsql':
                $sql = 'UPDATE "message" SET content = $1 WHERE id = $2';
                $params = [$content, $messageId];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }

        try {
            $success = $this->dbConnector->execute($sql, $params);
            return $success;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la mise à jour du message: " . $e->getMessage());
        }
    }

    public function delete($messageId) {
        try {
            $count = 0;
            switch (__DB_INFOS__['database_type']) {
                case 'mysql':
                case 'sqlite':
                    $sqlExists = 'SELECT COUNT(*) as count FROM `message` WHERE id = :messageId';
                    $paramsExists = [':messageId' => $messageId];
                    $result = $this->dbConnector->select($sqlExists, $paramsExists);
                    $count = $result[0]['count'] ?? 0;
                    break;
                case 'pgsql':
                    $sqlExists = 'SELECT COUNT(*) as count FROM "message" WHERE id = $1';
                    $paramsExists = [$messageId];
                    $result = $this->dbConnector->select($sqlExists, $paramsExists);
                    $count = $result[0]['count'] ?? 0;
                    break;
                default:
                    throw new Exception("Type de base de données non reconnu");
            }
    
            if ($count == 0) {
                return false; // Le message n'existe pas
            }
    
            // Commencer une transaction
            $this->dbConnector->beginTransaction();
    
            // Supprimer les enregistrements liés dans chat_message
            switch (__DB_INFOS__['database_type']) {
                case 'mysql':
                case 'sqlite':
                    $sqlChatMessage = 'DELETE FROM `chat_message` WHERE messageId = :messageId';
                    break;
                case 'pgsql':
                    $sqlChatMessage = 'DELETE FROM "chat_message" WHERE "messageId" = $1';
                    break;
            }
            $this->dbConnector->execute($sqlChatMessage, $paramsExists);
    
            // Supprimer le message
            switch (__DB_INFOS__['database_type']) {
                case 'mysql':
                case 'sqlite':
                    $sqlMessage = 'DELETE FROM `message` WHERE id = :messageId';
                    break;
                case 'pgsql':
                    $sqlMessage = 'DELETE FROM "message" WHERE id = $1';
                    break;
            }
            $this->dbConnector->execute($sqlMessage, $paramsExists);
    
            // Valider la transaction
            $this->dbConnector->commit();
    
            return true;
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->dbConnector->rollBack();
            throw new Exception("Erreur lors de la suppression du message : " . $e->getMessage());
        }
    }

    public function userExists($userId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = "SELECT COUNT(*) FROM `user` WHERE id = :userId";
                $params = [':userId' => $userId];
                break;
            case 'pgsql':
                $sql = "SELECT COUNT(*) FROM \"user\" WHERE id = $1";
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

    public function chatExists($chatId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = "SELECT COUNT(*) FROM `chat` WHERE id = :chatId";
                $params = [':chatId' => $chatId];
                break;
            case 'pgsql':
                $sql = "SELECT COUNT(*) FROM \"chat\" WHERE id = $1";
                $params = [$chatId];
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
            throw new Exception("Erreur lors de la vérification de l'existence de la conversation : " . $e->getMessage());
        }
    }
}