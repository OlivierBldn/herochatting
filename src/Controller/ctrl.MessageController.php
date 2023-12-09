<?php // path: src/Controller/ctrl.MessageController.php

require_once __DIR__ . '/../Repository/repo.MessageRepository.php';
require_once __DIR__ . '/../Repository/repo.ChatRepository.php';
require_once __DIR__ . '/../Class/Service/srv.OpenAIService.php';
require_once __DIR__ . '/../Class/Middleware/mdw.OwnershipVerifierMiddleware.php';

/**
 * Class MessageController
 * 
 * This class is the controller for the messages.
 * 
 */
class MessageController {
    private $messageRepository;
    private $chatRepository;

    public function __construct() {
        $this->messageRepository = new MessageRepository();
        $this->chatRepository = new ChatRepository();
        $this->ownershipVerifier = new OwnershipVerifierMiddleware();
    }

    /**
     * Function to create a message
     *
     * @param string $requestMethod
     * @return void
     */
    public function createMessage($requestMethod)
    {
        // Check if the request method is POST
        if ($requestMethod !== 'POST') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisee']);
            return;
        }
    
        // Get the user ID and chat ID from the URL
        $requestUri = $_SERVER['REQUEST_URI'];
        $segments = explode('/', $requestUri);
    
        if (!isset($segments[3]) || !isset($segments[5])) {
            http_response_code(400);
            echo json_encode(['message' => 'Informations manquantes dans l\'URL']);
            return;
        }
    
        $userId = (int) $segments[3];

        // Check if the user exist
        if (!$this->messageRepository->userExists($userId)) {
            http_response_code(404);
            echo json_encode(['message' => 'Utilisateur non trouve']);
            return;
        }

        // Check if the User is the owner of the requested User id
        if (!$this->ownershipVerifier->handle($userId, 'user')) {
            http_response_code(403);
            echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant de l\'utilisateur']);
            return;
        }

        $chatId = (int) $segments[5];

        // Check if the chat exist
        if (!$this->messageRepository->chatExists($chatId)) {
            http_response_code(404);
            echo json_encode(['message' => 'Chat non trouve']);
            return;
        }

        // Check if the User is the owner of the requested Chat
        if (!$this->ownershipVerifier->handle($chatId, 'chat')) {
            http_response_code(403);
            echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant de la conversation']);
            return;
        }
    
        try {
            $requestData = json_decode(file_get_contents('php://input'), true);

            // Check if the content is set and not empty
            if (!isset($requestData['content']) || empty($requestData['content'])) {
                http_response_code(400);
                echo json_encode(['message' => 'Contenu du message manquant ou invalide']);
                return;
            }
    
            $userMessageContent = $requestData['content'];
            $isHuman = $requestData['isHuman'] ?? true;

            // Create the message using the MessageRepository
            $userMessageId = $this->messageRepository->create(['content' => $userMessageContent, 'isHuman' => $isHuman], $chatId);
    
            // If the message is from a human, get the response from the OpenAI API
            if ($isHuman) {
                $characterResponse = $this->getCharacterResponse($chatId, $userMessageContent);
                if ($characterResponse !== 'Description non disponible') {
                    $this->messageRepository->create(['content' => $characterResponse, 'isHuman' => false], $chatId);
                }
            }
    
            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'Message cree avec succes.', 'messageId' => $userMessageId]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la creation du message : ' . $e->getMessage()]);
        }
    }
    
    /**
     * Function to get the response from the OpenAI API
     *
     * @param int $chatId
     * @param string $userMessage
     * @return string
     */
    private function getCharacterResponse($chatId, $userMessage)
    {
        // Get the character informations from the chat ID
        $characterDetails = $this->chatRepository->getCharacterDetailsByChatId($chatId);
        if (!$characterDetails) {
            return 'Description non disponible';
        }
    
        $openAIService = OpenAIService::getInstance();
    
        // Generate the response from the OpenAI API
        return $openAIService->generateResponse($userMessage, $characterDetails);
    }

    /**
     * Function to get all the messages
     *
     * @param string $requestMethod
     * @return void
     */
    public function getAllMessages($requestMethod)
    {
        // Check if the request method is GET
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisee']);
            return;
        }

        try {
            $messages = $this->messageRepository->getAll();

            // Return a success response if the query was executed successfully
            if (empty($messages)) {
                $response = [
                    'success' => true,
                    'message' => 'Aucune conversation trouvee.',
                    'data' => []
                ];
            } else {
                // Return the messages if some were found
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
                'message' => 'Erreur lors de la recuperation des messages : ' . $e->getMessage()
            ];

            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }

    /**
     * Function to get a message by its ID
     *
     * @param string $requestMethod
     * @param int $messageId
     * @return void
     */
    public function getMessageById($requestMethod, $messageId)
    {
        // Check if the request method is GET
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisee']);
            return;
        }

        // Check if the User that sent the request is the owner of the message
        if (!$this->ownershipVerifier->handle($messageId, 'message')) {
            http_response_code(403);
            echo json_encode(['message' => 'Acces refuse']);
            return;
        }

        try {
            $message = $this->messageRepository->getById($messageId);

            // If the request succeeded, return the message
            if ($message !== null) {
                $messageData = $message->toMap();

                http_response_code(200);
                echo json_encode($messageData);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Message non trouve']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la recuperation du message : ' . $e->getMessage()]);
        }
    }

    /**
     * Function to get all the messages by a Chat ID
     *
     * @param string $requestMethod
     * @param int $chatId
     * @return void
     */
    public function getMessagesByChatId($requestMethod)
    {
        // Check if the request method is GET
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Method Not Allowed']);
            return;
        }

        $requestUri = $_SERVER['REQUEST_URI'];
        $segments = explode('/', $requestUri);

        // Check if the UserID is set
        if(!isset($segments[3])) {
            http_response_code(400);
            echo json_encode(['message' => 'Identifiant de l\'utilisateur manquant']);
            return;
        }

        // Check if the Chat Id is set
        if(!isset($segments[5])) {
            http_response_code(400);
            echo json_encode(['message' => 'Identifiant du chat manquant']);
            return;
        }

        $userId = (int) $segments[3];

        // Check if the user exist
        if(!$this->messageRepository->userExists($userId)) {
            http_response_code(404);
            echo json_encode(['message' => 'Utilisateur non trouve']);
            return;
        }

        // Check if the User is the owner of the requested User id
        if (!$this->ownershipVerifier->handle($userId, 'user')) {
            http_response_code(403);
            echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant de l\'utilisateur']);
            return;
        }

        $chatId = (int) $segments[5];

        try {
            // Check if the chat exist
            if (!$this->chatRepository->getById($chatId)) {
                http_response_code(404);
                echo json_encode(['message' => 'Conversation non trouvee']);
                return;
            }

            // Check if the User is the owner of the requested Chat id
            if (!$this->ownershipVerifier->handle($chatId, 'chat')) {
                http_response_code(403);
                echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant de la conversation']);
                return;
            }

            $messages = $this->messageRepository->getMessagesByChatId($chatId);

            // Return an array of messages if some were found
            $messageData = array_map(function($message) {
                return $message->toMap();
            }, $messages);

            http_response_code(200);
            echo json_encode(['messages' => $messageData]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la recuperation des messages : ' . $e->getMessage()]);
        }
    }

    /**
     * Function to delete a message
     *
     * @param string $requestMethod
     * @param int $messageId
     * @return void
     */
    public function deleteMessage($requestMethod, $messageId)
    {
        // Check if the request method is DELETE
        if ($requestMethod !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisee']);
            return;
        }

        // Check if the User is the owner of the requested User id
        if (!$this->ownershipVerifier->handle($messageId, 'message')) {
            http_response_code(403);
            echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant de l\'utilisateur']);
            return;
        }

        try {
            // Get the message by its ID
            $message = $this->messageRepository->getById($messageId);
            
            if (!$message) {
                http_response_code(404);
                echo json_encode(['message' => 'Message non trouvee']);
                return;
            }

            // Delete the message using the MessageRepository
            if ($this->messageRepository->delete($messageId)) {
                http_response_code(200);
                echo json_encode(['message' => 'Message supprime avec succes']);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Message non trouve']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la suppression du message : ' . $e->getMessage()]);
        }
    }
}
