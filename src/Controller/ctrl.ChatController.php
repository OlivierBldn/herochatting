<?php // path: src/Controller/ctrl.ChatController.php
require_once __DIR__ . '/../Repository/repo.ChatRepository.php';
require_once __DIR__ . '/../Class/class.Chat.php';
require_once __DIR__ . '/../Class/Builder/bldr.ChatBuilder.php';

class ChatController {
    private $dbConnector;
    private $chatRepository;

    public function __construct() {
        $this->dbConnector = DBConnectorFactory::getConnector();
        $this->chatRepository = new ChatRepository();
    }

    public function createChat($requestMethod) {
        if ($requestMethod !== 'POST') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisée']);
            return;
        }

        try {
            $requestData = json_decode(file_get_contents('php://input'), true);

            $chatBuilder = new ChatBuilder();
            $chat = $chatBuilder->build();

            $chatId = $this->chatRepository->create($requestData['userId'], $requestData['characterId']);

            http_response_code(201);
            echo json_encode(['message' => 'Conversation créée avec succès', 'chatId' => $chatId]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la création de la conversation : ' . $e->getMessage()]);
        }
    }

    public function getAllChats($requestMethod) {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisée']);
            return;
        }

        try {
            $chats = $this->chatRepository->getAll();
            $chatData = array_map(function($chat) { return $chat->toMap(); }, $chats);

            http_response_code(200);
            echo json_encode(['chats' => $chatData]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la récupération des conversations : ' . $e->getMessage()]);
        }
    }

    public function getChatById($requestMethod, $chatId) {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisée']);
            return;
        }

        try {
            $chat = $this->chatRepository->getById($chatId);
            if ($chat) {
                http_response_code(200);
                echo json_encode($chat->toMap());
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Conversation non trouvée']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la récupération de la conversation : ' . $e->getMessage()]);
        }
    }

    public function getChatsByUserId($requestMethod, $userId) {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisée']);
            return;
        }

        $requestUri = $_SERVER['REQUEST_URI'];

        $segments = explode('/', $requestUri);

        if(!isset($segments[3])) {
            http_response_code(400);
            echo json_encode(['message' => 'URL malformée']);
            return;
        }

        $userId = (int) $segments[3];
    
        if (!$this->chatRepository->userExists($userId)) {
            http_response_code(404);
            echo json_encode(['message' => 'Utilisateur non trouvé']);
            return;
        }

        try {
            $chatRows = $this->chatRepository->getByUserId($userId);
            $chats = [];
    
            foreach ($chatRows as $chatRow) {
                $builder = new ChatBuilder();
                $chat = $builder->withId($chatRow->getId())
                                ->loadMessages($chatRow->getId())
                                ->build();
    
                array_push($chats, $chat->toMap());
            }
    
            http_response_code(200);
            echo json_encode(['chats' => $chats]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la récupération des conversations pour l\'utilisateur : ' . $e->getMessage()]);
        }
    }

    public function getMessagesByChatId($requestMethod, $chatId) {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisée']);
            return;
        }

        $requestUri = $_SERVER['REQUEST_URI'];

        $segments = explode('/', $requestUri);

        if(!isset($segments[3])) {
            http_response_code(400);
            echo json_encode(['message' => 'URL malformée']);
            return;
        }

        $chatId = (int) $segments[5];

        $messageRepository = new MessageRepository();

        try {
            $messages = $messageRepository->getMessagesByChatId($chatId);
            $messageData = array_map(function($message) {
                return $message->toMap();
            }, $messages);

            http_response_code(200);
            echo json_encode(['messages' => $messageData]);
        } catch (Exception $e) {
            if ($e->getMessage() == "Chat not found") {
                http_response_code(404);
                echo json_encode(['message' => 'Chat non trouvé']);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Erreur lors de la récupération des messages : ' . $e->getMessage()]);
            }
        }
    }

    public function getChatByCharacterId($requestMethod, $characterId) {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Method Not Allowed']);
            return;
        }

        $requestUri = $_SERVER['REQUEST_URI'];

        $segments = explode('/', $requestUri);

        if(!isset($segments[3])) {
            http_response_code(400);
            echo json_encode(['message' => 'URL malformée']);
            return;
        }

        $characterId = (int) $segments[3];
    
        if (!$this->chatRepository->characterExists($characterId)) {
            http_response_code(404);
            echo json_encode(['message' => 'Personnage non trouvé']);
            return;
        }
    
        try {
            $chatRows = $this->chatRepository->getByCharacterId($characterId);
            $chatsData = [];
    
            foreach ($chatRows as $chatRow) {
                $builder = new ChatBuilder();
                $chat = $builder->withId($chatRow['id'])
                                ->loadMessages($chatRow['id'])
                                ->build();
                array_push($chatsData, $chat->toMap()); // Utiliser toMap() pour convertir l'objet Chat en tableau
            }
    
            http_response_code(200);
            echo json_encode(['chats' => $chatsData]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la récupération des conversations pour le personnage : ' . $e->getMessage()]);
        }
    }
    
    public function deleteChat($requestMethod, $chatId)
    {
        if ($requestMethod !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        $chatId = (int) $chatId;

        try {
            $messageRepository = new MessageRepository();

            // Vérifier si la conversation existe
            $chat = $this->chatRepository->getById($chatId);

            if (!$chat) {
                http_response_code(404);
                echo json_encode(['message' => 'Conversation non trouvée']);
                return;
            }

            // Commencer une transaction
            $this->dbConnector->beginTransaction();

            // Supprimer les messages dans les chats du personnage
            $messages = $messageRepository->getMessagesByChatId($chatId);
            foreach ($messages as $message) {
                $messageRepository->delete($message->getId());
            }

            // Supprimer la conversation
            $this->chatRepository->delete($chatId);

            // Valider la transaction
            $this->dbConnector->commit();

            http_response_code(200);
            echo json_encode(['message' => 'Conversation supprimée avec succès']);
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->dbConnector->rollBack();
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la suppression de la conversation : ' . $e->getMessage()]);
        }
    }
}