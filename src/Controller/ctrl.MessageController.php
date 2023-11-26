<?php // path: src/Controller/ctrl.MessageController.php

require_once __DIR__ . '/../Repository/repo.MessageRepository.php';
require_once __DIR__ . '/../Repository/repo.ChatRepository.php';

class MessageController {
    private $messageRepository;
    private $chatRepository;

    public function __construct() {
        $this->messageRepository = new MessageRepository();
        $this->chatRepository = new ChatRepository();
    }

    public function createMessage($requestMethod)
    {
        if ($requestMethod !== 'POST') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        $requestUri = $_SERVER['REQUEST_URI'];
        $segments = explode('/', $requestUri);

        if(!isset($segments[3])) {
            http_response_code(400);
            echo json_encode(['message' => 'Identifiant de l\'utilisateur manquant']);
            return;
        }

        if(!isset($segments[5])) {
            http_response_code(400);
            echo json_encode(['message' => 'Identifiant du chat manquant']);
            return;
        }

        $userId = (int) $segments[3];
        $chatId = (int) $segments[5];

        if(!$this->messageRepository->userExists($userId)) {
            http_response_code(404);
            echo json_encode(['message' => 'Utilisateur non trouvé']);
            return;
        }

        if(!$this->messageRepository->chatExists($chatId)) {
            http_response_code(404);
            echo json_encode(['message' => 'Chat non trouvé']);
            return;
        }

        try {
            $requestData = json_decode(file_get_contents('php://input'), true);

            if (!isset($requestData['content'], $requestData['isHuman']) ||
                empty($requestData['content'])) {
                http_response_code(400);
                echo json_encode(['message' => 'Données manquantes ou invalides']);
                return;
            }

            $message = new Message(null, $requestData['content'], new DateTime(), $requestData['isHuman'] ?? true);

            $messageData = $message->toMap();

            $messageId = $this->messageRepository->create($messageData, $chatId);

            if ($messageId) {
                $successResponse = [
                    'success' => true,
                    'message' => 'Message créé avec succès.',
                    'messageId' => $messageId
                ];
                http_response_code(201);
                echo json_encode($successResponse);
            } else {
                throw new Exception("Erreur lors de la création du message");
            }
        } catch (Exception $e) {
            $errorResponse = [
                'success' => false,
                'message' => 'Erreur lors de la création du message : ' . $e->getMessage()
            ];
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }

    public function getAllMessages($requestMethod)
    {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        try {
            $messages = $this->messageRepository->getAll();

            if (empty($messages)) {
                $response = [
                    'success' => true,
                    'message' => 'Aucune conversation trouvée.',
                    'data' => []
                ];
            } else {
                $responseData = [];
                foreach ($messages as $message) {
                    $responseData[] = $message->toMap();
                }

                $response = [
                    'success' => true,
                    'data' => $responseData
                ];
            }

            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            $errorResponse = [
                'success' => false,
                'message' => 'Erreur lors de la récupération des messages : ' . $e->getMessage()
            ];

            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }

    public function getMessageById($requestMethod, $messageId)
    {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        try {
            $message = $this->messageRepository->getById($messageId);

            if ($message !== null) {
                $messageData = $message->toMap();

                http_response_code(200);
                echo json_encode($messageData);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Message non trouvé']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la récupération du message : ' . $e->getMessage()]);
        }
    }

    public function getMessagesByChatId($requestMethod, $chatId) {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Method Not Allowed']);
            return;
        }

        $requestUri = $_SERVER['REQUEST_URI'];
        $segments = explode('/', $requestUri);

        if(!isset($segments[3])) {
            http_response_code(400);
            echo json_encode(['message' => 'Identifiant de l\'utilisateur manquant']);
            return;
        }

        if(!isset($segments[5])) {
            http_response_code(400);
            echo json_encode(['message' => 'Identifiant du chat manquant']);
            return;
        }

        $userId = (int) $segments[3];
        $chatId = (int) $segments[5];

        if(!$this->messageRepository->userExists($userId)) {
            http_response_code(404);
            echo json_encode(['message' => 'Utilisateur non trouvé']);
            return;
        }

        try {
            // Vérifier si le chat existe
            if (!$this->chatRepository->getById($chatId)) {
                http_response_code(404);
                echo json_encode(['message' => 'Conversation non trouvée']);
                return;
            }

            // Récupérer les messages
            $messages = $this->messageRepository->getMessagesByChatId($chatId);
            $messageData = array_map(function($message) {
                return $message->toMap();
            }, $messages);

            http_response_code(200);
            echo json_encode(['messages' => $messageData]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la récupération des messages : ' . $e->getMessage()]);
        }
    }

    public function updateMessage($requestMethod, $messageId) {
        if ($requestMethod !== 'PUT') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        try {
            $requestData = json_decode(file_get_contents('php://input'), true);

            if (empty($requestData) || !isset($requestData['content'])) {
                http_response_code(400);
                echo json_encode(['message' => 'Données manquantes ou invalides pour la mise à jour']);
                return;
            }

            $updatedMessage = Message::fromMap([
                'id' => $messageId,
                'content' => $requestData['content']
            ]);

            $success = $this->messageRepository->update($messageId, $updatedMessage->toMap());

            if ($success) {
                http_response_code(200);
                echo json_encode(['message' => 'Message mis à jour avec succès']);
            } else {
                throw new Exception("Erreur lors de la mise à jour du message");
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la mise à jour du message : ' . $e->getMessage()]);
        }
    }

    public function deleteMessage($requestMethod, $messageId) {
        
        if ($requestMethod !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        $messageId = (int) $messageId;

        try {
            $message = $this->messageRepository->getById($messageId);
            
            if (!$message) {
                http_response_code(404);
                echo json_encode(['message' => 'Message non trouvée']);
                return;
            }

            if ($this->messageRepository->delete($messageId)) {
                http_response_code(200);
                echo json_encode(['message' => 'Message supprimé avec succès']);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Message non trouvé']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la suppression du message : ' . $e->getMessage()]);
        }
    }
}
