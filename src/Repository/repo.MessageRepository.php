<?php // path: src/Repository/repo.MessageRepository.php

/**
 * Class MessageRepository
 * 
 * This class is the repository for the Message class.
 * It contains all the queries to the database regarding the Message class.
 * 
 */
class MessageRepository extends AbstractRepository
{
    /**
     * Function to create a message
     *
     * @param array $messageData
     * @param int $chatId
     * @return int
     */
    public function create($messageData, $chatId) {
        $content = $messageData['content'];
        $createdAt = (new DateTime())->format('Y-m-d H:i:s');
        $isHuman = isset($messageData['isHuman']) && $messageData['isHuman'] ? 1 : 0;
    
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

    /**
     * Function to link a Message to a Chat
     *
     * @param int $messageId
     * @param int $chatId
     * @return void
     */
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

    /**
     * Function to get all the messages
     *
     * @return array
     */
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

    /**
     * Function to get a message by its id
     *
     * @param int $messageId
     * @return Message|null
     */
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

    /**
     * Function to get all the messages by a Chat id
     *
     * @param int $chatId
     * @return array
     */
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

    // /**
    //  * Function to update a Message
    //  * 
    //  * @param int $messageId
    //  * @param array $messageData
    //  * 
    //  * @return bool
    //  */
    // public function update($messageId, $messageData) {
    //     $content = $messageData['content'];

    //     switch (__DB_INFOS__['database_type']) {
    //         case 'mysql':
    //         case 'sqlite':
    //             $sql = 'UPDATE `message` SET content = :content WHERE id = :messageId';
    //             $params = [':content' => $content, ':messageId' => $messageId];
    //             break;
    //         case 'pgsql':
    //             $sql = 'UPDATE "message" SET content = $1 WHERE id = $2';
    //             $params = [$content, $messageId];
    //             break;
    //         default:
    //             throw new Exception("Type de base de données non reconnu");
    //     }

    //     try {
    //         $success = $this->dbConnector->execute($sql, $params);
    //         return $success;
    //     } catch (Exception $e) {
    //         throw new Exception("Erreur lors de la mise à jour du message: " . $e->getMessage());
    //     }
    // }

    /**
     * Function to delete a Message
     *
     * @param int $messageId
     * @return bool
     */
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
                return false;
            }
    
            // Begin transaction to execute multiple queries
            $this->dbConnector->beginTransaction();
    
            // Delete the references to the message in the relation table chat_message
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
    
            // Delete the message
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
    
            // Commit the transaction
            $this->dbConnector->commit();
    
            return true;
        } catch (Exception $e) {
            // Cancel the transaction if an error occurs
            $this->dbConnector->rollBack();
            throw new Exception("Erreur lors de la suppression du message : " . $e->getMessage());
        }
    }

    /**
     * Function to check if a User exists
     *
     * @param int $userId
     * @return bool
     */
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

    /**
     * Function to check if a Chat exists
     *
     * @param int $chatId
     * @return bool
     */
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

    /**
     * Function to check if a User is the owner of a Message
     * 
     * @param int $messageId
     * @param int $userId
     * 
     * @return bool
     */
    public function isUserMessageOwner($messageId, $userId) {
        switch (__DB_INFOS__['database_type']) {
            case 'mysql':
            case 'sqlite':
                $sql = 'SELECT COUNT(*) FROM `chat_message` AS cm
                        JOIN `user_chat` AS uc ON cm.chatId = uc.chatId
                        WHERE cm.messageId = :messageId AND uc.userId = :userId';
                $params = [':messageId' => $messageId, ':userId' => $userId];
                break;
            case 'pgsql':
                $sql = 'SELECT COUNT(*) FROM "chat_message" AS cm
                        JOIN "user_chat" AS uc ON cm."chatId" = uc."chatId"
                        WHERE cm."messageId" = $1 AND uc."userId" = $2';
                $params = [$messageId, $userId];
                break;
            default:
                throw new Exception("Type de base de données non reconnu");
        }

        return $this->executeOwnershipQuery($sql, $params);
    }
}