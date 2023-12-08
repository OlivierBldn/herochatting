<?php // path: src/Controller/ctrl.ChatController.php
require_once __DIR__ . '/../Repository/repo.ChatRepository.php';
require_once __DIR__ . '/../Class/class.Chat.php';
require_once __DIR__ . '/../Class/Builder/bldr.ChatBuilder.php';
require_once __DIR__ . '/../Class/Middleware/mdw.OwnershipVerifierMiddleware.php';

/**
 * Class ChatController
 * 
 * This class is the Chat controller.
 * It is used to handle the Chat routes.
 * 
 */
class ChatController {
    private $dbConnector;
    private $chatRepository;

    public function __construct() {
        $this->dbConnector = DBConnectorFactory::getConnector();
        $this->chatRepository = new ChatRepository();
        $this->ownershipVerifier = new OwnershipVerifierMiddleware();
    }

    /**
     * Function to create a Chat
     * 
     * @param string $requestMethod
     * @return void
     */
    public function createChat($requestMethod)
    {
        // Check if the request method is POST
        if ($requestMethod !== 'POST') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisée']);
            return;
        }

        try {
            $requestData = json_decode(file_get_contents('php://input'), true);

            // Use the ChatBuilder to create a Chat
            $chatBuilder = new ChatBuilder();
            $chat = $chatBuilder->build();

            // Create the Chat in the database and get the Chat id
            $chatId = $this->chatRepository->create($requestData['userId'], $requestData['characterId']);

            http_response_code(201);
            echo json_encode(['message' => 'Conversation créée avec succès', 'chatId' => $chatId]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la création de la conversation : ' . $e->getMessage()]);
        }
    }

    /**
     * Function to get all Chats from the database
     * 
     * @param string $requestMethod
     * @return void
     */
    public function getAllChats($requestMethod) {
        // Check if the request method is GET
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisée']);
            return;
        }

        try {
            // Get all the Chats using the ChatRepository
            $chats = $this->chatRepository->getAll();

            // Map the Chats to an array
            $chatData = array_map(function($chat) { return $chat->toMap(); }, $chats);

            http_response_code(200);
            echo json_encode(['chats' => $chatData]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la récupération des conversations : ' . $e->getMessage()]);
        }
    }

    /**
     * Function to get a Chat by its id
     * 
     * @param string $requestMethod
     * @param int $chatId
     * @return void
     */
    public function getChatById($requestMethod, $chatId)
    {
        // Check if the request method is GET
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisée']);
            return;
        }

        // Check if the User is the owner of the requested Chat
        $ownershipVerifier = new OwnershipVerifierMiddleware();
        if (!$ownershipVerifier->handle($chatId)) {
            http_response_code(403);
            echo json_encode(['message' => 'Accès refusé']);
            return;
        }

        try {
            $chat = $this->chatRepository->getById($chatId);

            // If the Chat exists, return it
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

    /**
     * Function to get all Chats from a User
     * 
     * @param string $requestMethod
     * @param int $userId
     * @return void
     */
    public function getChatsByUserId($requestMethod, $userId)
    {
        // Check if the request method is GET
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisée']);
            return;
        }

        // Check if the url is well formed
        $requestUri = $_SERVER['REQUEST_URI'];

        $segments = explode('/', $requestUri);

        if(!isset($segments[3])) {
            http_response_code(400);
            echo json_encode(['message' => 'URL malformée']);
            return;
        }
    
        // Check if the User exists
        if (!$this->chatRepository->userExists($userId)) {
            http_response_code(404);
            echo json_encode(['message' => 'Utilisateur non trouvé']);
            return;
        }

        try {
            // Get all the Chats from the User using the ChatRepository
            $chatRows = $this->chatRepository->getByUserId($userId);
            $chats = [];
    
            // Map the Chats to an array using the ChatBuilder
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

    /**
     * Function to get all Messages from a Chat
     * 
     * @param string $requestMethod
     * @param int $chatId
     * 
     * @return void
     */
    public function getMessagesByChatId($requestMethod, $chatId)
    {
        // Check if the request method is GET
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisée']);
            return;
        }

        // Check if the url is well formed
        $requestUri = $_SERVER['REQUEST_URI'];

        $segments = explode('/', $requestUri);

        if(!isset($segments[3])) {
            http_response_code(400);
            echo json_encode(['message' => 'URL malformée']);
            return;
        }

        $messageRepository = new MessageRepository();

        try {
            // Get all the Messages from the Chat using the MessageRepository
            $messages = $messageRepository->getMessagesByChatId($chatId);

            // Map the Messages to an array
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

    /**
     * Function to get the Chat from a Character
     * 
     * @param string $requestMethod
     * @param int $characterId
     * 
     * @return void
     */
    public function getChatByCharacterId($requestMethod, $characterId) {
        // Check if the request method is GET
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Method Not Allowed']);
            return;
        }

        // Check if the url is well formed
        $requestUri = $_SERVER['REQUEST_URI'];

        $segments = explode('/', $requestUri);

        if(!isset($segments[3])) {
            http_response_code(400);
            echo json_encode(['message' => 'URL malformée']);
            return;
        }
    
        // Check if the Character exists
        if (!$this->chatRepository->characterExists($characterId)) {
            http_response_code(404);
            echo json_encode(['message' => 'Personnage non trouvé']);
            return;
        }
    
        try {
            // Get the Chat from the Character using the ChatRepository
            $chatRows = $this->chatRepository->getByCharacterId($characterId);
            $chatsData = [];
    
            // Map the Chats to an array using the ChatBuilder
            foreach ($chatRows as $chatRow) {
                $builder = new ChatBuilder();
                $chat = $builder->withId($chatRow['id'])
                                ->loadMessages($chatRow['id'])
                                ->build();
                array_push($chatsData, $chat->toMap());
            }
    
            http_response_code(200);
            echo json_encode(['chats' => $chatsData]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la récupération des conversations pour le personnage : ' . $e->getMessage()]);
        }
    }
    
    /**
     * Function to delete a Chat
     * 
     * @param string $requestMethod
     * @param int $chatId
     * 
     * @return void
     */
    public function deleteChat($requestMethod, $chatId)
    {
        // Check if the request method is DELETE
        if ($requestMethod !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        try {
            $messageRepository = new MessageRepository();

            // Check if the Chat exists
            $chat = $this->chatRepository->getById($chatId);

            if (!$chat) {
                http_response_code(404);
                echo json_encode(['message' => 'Conversation non trouvée']);
                return;
            }

            // Begin a transaction to execute multiple queries
            $this->dbConnector->beginTransaction();

            // Delete all the Messages from the Chat
            $messages = $messageRepository->getMessagesByChatId($chatId);
            foreach ($messages as $message) {
                $messageRepository->delete($message->getId());
            }

            // Delete the Chat
            $this->chatRepository->delete($chatId);

            // Commit the transaction
            $this->dbConnector->commit();

            http_response_code(200);
            echo json_encode(['message' => 'Conversation supprimée avec succès']);
        } catch (Exception $e) {
            // Cancel the transaction if an error occurs
            $this->dbConnector->rollBack();
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la suppression de la conversation : ' . $e->getMessage()]);
        }
    }
}