<?php // path: src/Controller/ctrl.ChatController.php
require_once __DIR__ . '/../Repository/repo.ChatRepository.php';
require_once __DIR__ . '/../Class/Chat.php';

class ChatController {
    private $chatRepository;

    public function __construct() {
        $this->chatRepository = new ChatRepository();
    }

    // public function createChat($requestMethod) {
    //     if ($requestMethod !== 'POST') {
    //         http_response_code(405);
    //         echo json_encode(['message' => 'Method Not Allowed']);
    //         return;
    //     }

    //     try {
    //         $requestData = json_decode(file_get_contents('php://input'), true);
    //         $chat = Chat::fromMap($requestData);

    //         $userId = $requestData['userId'];
    //         $characterId = $requestData['characterId'];

    //         $chatId = $this->chatRepository->create($chat, $userId, $characterId);

    //         http_response_code(201);
    //         echo json_encode(['message' => 'Chat created successfully', 'chatId' => $chatId]);
    //     } catch (Exception $e) {
    //         http_response_code(500);
    //         echo json_encode(['message' => 'Error creating chat: ' . $e->getMessage()]);
    //     }
    // }

    public function createChat($requestMethod) {
        if ($requestMethod !== 'POST') {
            http_response_code(405);
            echo json_encode(['message' => 'Method Not Allowed']);
            return;
        }

        try {
            // Récupérer les données du chat depuis la requête
            $requestData = json_decode(file_get_contents('php://input'), true);

            // Utiliser ChatBuilder pour construire l'objet Chat
            $chatBuilder = new ChatBuilder();
            $chat = $chatBuilder->build();

            // Enregistrer le chat dans la base de données
            $chatId = $this->chatRepository->create($chat);

            http_response_code(201);
            echo json_encode(['message' => 'Chat created successfully', 'chatId' => $chatId]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Error creating chat: ' . $e->getMessage()]);
        }
    }

    public function getAllChats($requestMethod) {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Method Not Allowed']);
            return;
        }

        try {
            $chats = $this->chatRepository->getAll();
            $chatData = array_map(function($chat) { return $chat->toMap(); }, $chats);

            http_response_code(200);
            echo json_encode(['chats' => $chatData]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Error fetching chats: ' . $e->getMessage()]);
        }
    }

    public function getChatById($requestMethod, $chatId) {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Method Not Allowed']);
            return;
        }

        try {
            $chat = $this->chatRepository->getById($chatId);
            if ($chat) {
                http_response_code(200);
                echo json_encode($chat->toMap());
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Chat not found']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Error fetching chat: ' . $e->getMessage()]);
        }
    }

    // public function updateChat($requestMethod, $chatId) {
    //     if ($requestMethod !== 'PUT') {
    //         http_response_code(405);
    //         echo json_encode(['message' => 'Method Not Allowed']);
    //         return;
    //     }

    //     try {
    //         $requestData = json_decode(file_get_contents('php://input'), true);
    //         $chatData = Chat::fromMap($requestData)->toMap();
    //         $success = $this->chatRepository->update($chatId, $chatData);

    //         if ($success) {
    //             http_response_code(200);
    //             echo json_encode(['message' => 'Chat updated successfully']);
    //         } else {
    //             throw new Exception('Update failed');
    //         }
    //     } catch (Exception $e) {
    //         http_response_code(500);
    //         echo json_encode(['message' => 'Error updating chat: ' . $e->getMessage()]);
    //     }
    // }

    public function deleteChat($requestMethod, $chatId) {
        if ($requestMethod !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['message' => 'Method Not Allowed']);
            return;
        }

        try {
            $success = $this->chatRepository->delete($chatId);
            if ($success) {
                http_response_code(200);
                echo json_encode(['message' => 'Chat deleted successfully']);
            } else {
                throw new Exception('Delete failed');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Error deleting chat: ' . $e->getMessage()]);
        }
    }

    // public function getChatByUserId($requestMethod, $userId) {
    //     if ($requestMethod !== 'GET') {
    //         http_response_code(405);
    //         echo json_encode(['message' => 'Method Not Allowed']);
    //         return;
    //     }

    //     try {
    //         $chats = $this->chatRepository->getByUserId($userId);
    //         $chatData = array_map(function($chat) { return $chat->toMap(); }, $chats);

    //         http_response_code(200);
    //         echo json_encode(['chats' => $chatData]);
    //     } catch (Exception $e) {
    //         http_response_code(500);
    //         echo json_encode(['message' => 'Error fetching chats for user: ' . $e->getMessage()]);
    //     }
    // }

    public function getChatByUserId($requestMethod, $userId) {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Method Not Allowed']);
            return;
        }

        try {
            $chatRows = $this->chatRepository->getByUserId($userId);
            $chats = [];

            foreach ($chatRows as $chatRow) {
                $chat = (new ChatBuilder())
                    ->setId($chatRow['id'])
                    ->build();

                array_push($chats, $chat->toMap());
            }

            http_response_code(200);
            echo json_encode(['chats' => $chats]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Error fetching chats for user: ' . $e->getMessage()]);
        }
    }

    public function getChatByCharacterId($requestMethod, $characterId) {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Method Not Allowed']);
            return;
        }

        try {
            $chats = $this->chatRepository->getByCharacterId($characterId);
            $chatData = array_map(function($chat) { return $chat->toMap(); }, $chats);

            http_response_code(200);
            echo json_encode(['chats' => $chatData]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Error fetching chats for character: ' . $e->getMessage()]);
        }
    }
}