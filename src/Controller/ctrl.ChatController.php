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
        $this->userRepository = new UserRepository();
        $this->characterRepository = new CharacterRepository();
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
            echo json_encode(['message' => 'Methode non autorisee']);
            return;
        }

        try {
            $requestData = json_decode(file_get_contents('php://input'), true);

            $userId = $requestData['userId'];

            // Check if the User exists
            if (!$this->userRepository->getById($userId)) {
                http_response_code(404);
                echo json_encode(['message' => 'Utilisateur non trouve']);
                return;
            }

            // Check if the User is the owner of the Character
            if (!$this->ownershipVerifier->handle($userId, 'user')) {
                http_response_code(403);
                echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant de l\'utilisateur']);
                return;
            }

            $characterId = $requestData['characterId'];

            // Check if the Character exists
            if (!$this->characterRepository->getById($characterId)) {
                http_response_code(404);
                echo json_encode(['message' => 'Personnage non trouve']);
                return;
            }

            // Check if the User is the owner of the Character
            if (!$this->ownershipVerifier->handle($characterId, 'character')) {
                http_response_code(403);
                echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant du personnage']);
                return;
            }

            // Check if a Chat already exists for the Character
            if ($this->chatRepository->chatExistsForCharacter($characterId)) {
                http_response_code(409);
                echo json_encode(['message' => 'Une conversation existe deja pour ce personnage']);
                return;
            }

            // Use the ChatBuilder to create a Chat
            $chatBuilder = new ChatBuilder();
            
            $user = $this->userRepository->getById($userId);
            if ($user) {
                $chatBuilder->addParticipant($user);
            }

            $character = $this->characterRepository->getById($characterId);
            if ($character) {
                $chatBuilder->addParticipant($character);
            }

            $chat = $chatBuilder->build();

            // Create the Chat in the database and get the Chat id
            $chatId = $this->chatRepository->create($chat);

            http_response_code(201);
            echo json_encode(['message' => 'Conversation creee avec succes', 'chatId' => $chatId]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la creation de la conversation : ' . $e->getMessage()]);
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
            echo json_encode(['message' => 'Methode non autorisee']);
            return;
        }

        try {
            // Get all the Chats using the ChatRepository
            $chats = $this->chatRepository->getAll();
            $chatsArray = [];
    
            foreach ($chats as $chat) {
                $chatBuilder = new ChatBuilder();
                $chatBuilder->withId($chat->getId())
                            ->loadParticipants($chat->getId())
                            ->loadMessages($chat->getId());
    
                $chatsArray[] = $chatBuilder->build()->toMap();
            }
    
            http_response_code(200);
            echo json_encode(['chats' => $chatsArray]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la recuperation des conversations : ' . $e->getMessage()]);
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
            echo json_encode(['message' => 'Methode non autorisee']);
            return;
        }

        // Check if the User is the owner of the requested Chat
        if (!$this->ownershipVerifier->handle($chatId, 'chat')) {
            http_response_code(403);
            echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant de la conversation']);
            return;
        }

        try {
            $chat = $this->chatRepository->getById($chatId);

            if ($chat) {
                $chatBuilder = new ChatBuilder();
                $chatBuilder->withId($chat->getId())
                            ->loadParticipants($chat->getId())
                            ->loadMessages($chat->getId());
    
                $chat = $chatBuilder->build();
    
                http_response_code(200);
                echo json_encode($chat->toMap());
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Conversation non trouvee']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la recuperation de la conversation : ' . $e->getMessage()]);
        }
    }

    /**
     * Function to get all Chats from a User
     * 
     * @param string $requestMethod
     * @param int $userId
     * @return void
     */
    public function getChatsByUserId($requestMethod)
    {
        // Check if the request method is GET
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisee']);
            return;
        }

        // Check if the url is well formed
        $requestUri = $_SERVER['REQUEST_URI'];

        $segments = explode('/', $requestUri);

        if(!isset($segments[3])) {
            http_response_code(400);
            echo json_encode(['message' => 'Il manque l\'identifiant de l\'utilisateur dans l\'URL']);
            return;
        }

        $userId = $segments[3];
    
        // Check if the User exists
        if (!$this->userRepository->getById($userId)) {
            http_response_code(404);
            echo json_encode(['message' => 'Utilisateur non trouve']);
            return;
        }

        // Check if the User is the owner of the requested Chats
        if (!$this->ownershipVerifier->handle($userId, 'user')) {
            http_response_code(403);
            echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant de l\'utilisateur']);
            return;
        }

        try {
            $chats = $this->chatRepository->getByUserId($userId);
            $chatsArray = [];
    
            foreach ($chats as $chat) {
                $chatBuilder = new ChatBuilder();
                $chatBuilder->withId($chat->getId())
                            ->loadParticipants($chat->getId())
                            ->loadMessages($chat->getId());
    
                $chatsArray[] = $chatBuilder->build()->toMap();
            }
    
            http_response_code(200);
            echo json_encode(['chats' => $chatsArray]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la recuperation des conversations pour l\'utilisateur : ' . $e->getMessage()]);
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
            echo json_encode(['message' => 'Methode non autorisee']);
            return;
        }

        // Check if the url is well formed
        $requestUri = $_SERVER['REQUEST_URI'];

        $segments = explode('/', $requestUri);

        if(!isset($segments[3])) {
            http_response_code(400);
            echo json_encode(['message' => 'Verifiez l\'identifiant de l\'utilisateur dans l\'URL']);
            return;
        }

        $userId = $segments[3];

        // Check if the User is the owner of the User id he is requesting
        if (!$this->ownershipVerifier->handle($userId, 'user')) {
            http_response_code(403);
            echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant de l\'utilisateur']);
            return;
        }

        // Check if the User is the owner of the requested Chat
        if (!$this->ownershipVerifier->handle($chatId, 'chat')) {
            http_response_code(403);
            echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant de la conversation']);
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
                echo json_encode(['message' => 'Chat non trouve']);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Erreur lors de la recuperation des messages : ' . $e->getMessage()]);
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
    public function getChatByCharacterId($requestMethod) {
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
            echo json_encode(['message' => 'Verifiez l\'identifiant du personnage dans l\'URL']);
            return;
        }

        $characterId = $segments[3];
    
        // Check if the Character exists
        if (!$this->characterRepository->getById($characterId)) {
            http_response_code(404);
            echo json_encode(['message' => 'Personnage non trouve']);
            return;
        }

        // Check if the User is the owner of the requested Characters Chats
        if (!$this->ownershipVerifier->handle($characterId, 'character')) {
            http_response_code(403);
            echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant de l\'utilisateur']);
            return;
        }
    
        try {
            // Get the Chat from the Character using the ChatRepository
            $chats = $this->chatRepository->getByCharacterId($characterId);
            $chatsArray = [];
    
            foreach ($chats as $chat) {
                $chatBuilder = new ChatBuilder();
                $chatBuilder->withId($chat->getId())
                            ->loadParticipants($chat->getId())
                            ->loadMessages($chat->getId());
    
                $chatsArray[] = $chatBuilder->build()->toMap();
            }
    
            http_response_code(200);
            echo json_encode(['chats' => $chatsArray]);      
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la recuperation des conversations pour le personnage : ' . $e->getMessage()]);
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
    public function deleteChat($requestMethod, $chatId = null)
    {
        // Check if the request method is DELETE
        if ($requestMethod !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisee']);
            return;
        }

        if ($chatId === null) {
            // Récupérer $chatId de l'URL si non fourni en paramètre
            $requestUri = $_SERVER['REQUEST_URI'];
            $segments = explode('/', $requestUri);
            $chatId = $segments[3] ?? null;
        }

        // // Check if the url is well formed
        // $requestUri = $_SERVER['REQUEST_URI'];

        // $segments = explode('/', $requestUri);

        // if(!isset($segments[3])) {
        //     http_response_code(400);
        //     echo json_encode(['message' => 'Verifiez l\'identifiant de la conversation dans l\'URL']);
        //     return;
        // }

        // $chatId = $segments[3];

        // Check if the Chat exists
        $chat = $this->chatRepository->getById($chatId);

        if (!$chat) {
            http_response_code(404);
            echo json_encode(['message' => 'Conversation non trouvee']);
            return;
        }

        // Check if the User is the owner of the requested Chat
        if (!$this->ownershipVerifier->handle($chatId, 'chat')) {
            http_response_code(403);
            echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant de l\'utilisateur']);
            return;
        }

        $messageRepository = new MessageRepository();

        try {
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
            echo json_encode(['message' => 'Conversation supprimee avec succes']);
        } catch (Exception $e) {
            // Cancel the transaction if an error occurs
            $this->dbConnector->rollBack();
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la suppression de la conversation : ' . $e->getMessage()]);
        }
    }
}