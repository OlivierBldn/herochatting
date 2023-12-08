<?php // path: src/Controller/ctrl.UserController.php

require_once __DIR__ . '/../Class/class.DBConnectorFactory.php';
require_once __DIR__ . '/../Repository/repo.UserRepository.php';
require_once __DIR__ . '/../Repository/repo.ChatRepository.php';
require_once __DIR__ . '/../Repository/repo.MessageRepository.php';
require_once __DIR__ . '/../Repository/repo.UniverseRepository.php';
require_once __DIR__ . '/../Repository/repo.CharacterRepository.php';
require_once __DIR__ . '/../Class/Middleware/mdw.OwnershipVerifierMiddleware.php';
require_once __DIR__ . '/../Class/Service/srv.StableDiffusionService.php';

/**
 * Class UserController
 * 
 * This class is the controller for the User.
 * 
 */
class UserController
{
    private $dbConnector;

    public function __construct()
    {
        $this->dbConnector = DBConnectorFactory::getConnector();
        $this->ownershipVerifier = new OwnershipVerifierMiddleware();
    }

    /**
     * Function to create a User
     * 
     * @param string $requestMethod
     * 
     * @return void
     * 
     */
    public function createUser($requestMethod)
    {
        // Check if the request method is POST
        if ($requestMethod !== 'POST') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }
    
        try {
            $requestData = json_decode(file_get_contents('php://input'), true);
    
            // Check if the request data is set and valid
            if (!isset($requestData['email'], $requestData['password'], $requestData['username'], $requestData['firstName'], $requestData['lastName']) ||
                empty($requestData['email']) || empty($requestData['password']) || empty($requestData['username']) ||
                empty($requestData['firstName']) || empty($requestData['lastName'])) {
                http_response_code(400);
                echo json_encode(['message' => 'Données manquantes ou invalides']);
                return;
            }
    
            $userRepository = new UserRepository();

            // Create the user and store it in the database using the repository
            $success = $userRepository->create($requestData);
    
            if ($success) {
                $successResponse = [
                    'success' => true,
                    'message' => 'Utilisateur créé avec succès.'
                ];
                http_response_code(201);
                echo json_encode($successResponse);
            } else {
                throw new Exception("Erreur lors de la création de l'utilisateur");
            }
        } catch (Exception $e) {
            $errorResponse = [
                'success' => false,
                'message' => 'Erreur lors de la création de l\'utilisateur : ' . $e->getMessage()
            ];
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }

    /**
     * Function to get all Users
     * 
     * @param string $requestMethod
     * 
     * @return void
     * 
     */
    public function getAllUsers($requestMethod)
    {
        // Check if the request method is GET
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }
    
        try {
            $userRepository = new UserRepository();

            // Get all users from the database using the repository
            $users = $userRepository->getAll();
    
            // Return a success response if the query was executed successfully
            if (empty($users)) {
                $response = [
                    'success' => true,
                    'message' => 'Aucun utilisateur trouvé.',
                    'data' => []
                ];
            } else {
                // Map the Users to an array of data to return if some were found
                $responseData = [];
                foreach ($users as $user) {
                    $responseData[] = $user->toMap();
                }
    
                $response = [
                    'success' => true,
                    'data' => $responseData
                ];
            }
    
            header('Content-Type: application/json');
            http_response_code(200); // OK
            echo json_encode($response);
        } catch (Exception $e) {
            $errorResponse = [
                'success' => false,
                'message' => 'Erreur lors de la récupération des utilisateurs : ' . $e->getMessage()
            ];
    
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }

    /**
     * Function to get a User by its ID
     * 
     * @param string $requestMethod
     * @param int $userId
     * 
     * @return void
     * 
     */
    public function getUserById($requestMethod, $userId)
    {
        // Check if the request method is GET
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        // Check if the User that sends the request is the owner of the requested User
        $ownershipVerifier = new OwnershipVerifierMiddleware();
        if (!$ownershipVerifier->handle($userId)) {
            http_response_code(403);
            echo json_encode(['message' => 'Accès refusé']);
            return;
        }

        try {
            $userRepository = new UserRepository();
            $user = $userRepository->getById($userId);

            // If a User was found, return a success response with the User data
            if ($user !== null) {
                $userData = $user->toMap();

                http_response_code(200);
                echo json_encode($userData);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Utilisateur non trouvé']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la récupération de l\'utilisateur : ' . $e->getMessage()]);
        }
    }

    /**
     * Function to update a User
     * 
     * @param string $requestMethod
     * @param int $userId
     * 
     * @return void
     * 
     */
    public function updateUser($requestMethod, $userId)
    {
        // Check if the request method is PUT
        if ($requestMethod !== 'PUT') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }
        
        try {
            $requestData = json_decode(file_get_contents('php://input'), true);
    
            // Check if the request data is set and valid
            if ($userId <= 0) {
                http_response_code(400);
                echo json_encode(['message' => 'L\'identifiant de l\'utilisateur est invalide']);
                return;
            }
    
            if (empty($requestData)) {
                http_response_code(400);
                echo json_encode(['message' => 'Aucune donnée fournie pour la mise à jour']);
                return;
            }
    
            $userRepository = new UserRepository();

            // Update the user in the database using the repository
            $success = $userRepository->update($userId, $requestData);
    
            if ($success) {
                http_response_code(200);
                echo json_encode(['message' => 'Utilisateur mis à jour avec succès']);
            } else {
                throw new Exception("Erreur lors de la mise à jour de l'utilisateur");
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la mise à jour de l\'utilisateur : ' . $e->getMessage()]);
        }
    }

    /**
     * Function to delete a User
     * 
     * @param string $requestMethod
     * @param int $userId
     * 
     * @return void
     * 
     */
    public function deleteUser($requestMethod, $userId)
    {
        // Check if the request method is DELETE
        if ($requestMethod !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        try {
            $chatRepository = new ChatRepository();
            $messageRepository = new MessageRepository();
            $universeRepository = new UniverseRepository();
            $userRepository = new UserRepository();
            $characterRepository = new CharacterRepository();

            // Begin a transaction to execute multiple queries
            $this->dbConnector->beginTransaction();

            // Delete the User's Chats and Messages
            $chats = $chatRepository->getByUserId($userId);
            foreach ($chats as $chat) {
                $messages = $messageRepository->getMessagesByChatId($chat->getId());
                foreach ($messages as $message) {
                    $messageRepository->delete($message->getId());
                }
                $chatRepository->delete($chat->getId());
            }

            
            $stableDiffusionService = StableDiffusionService::getInstance();
            
            // Delete the User's Characters
            $characters = $characterRepository->getByUserId($userId);
            foreach ($characters as $character) {
                $characterId = $character->getId();
                $characterImage = $character->getImage();
                $stableDiffusionService->deleteImageIfUnused($characterImage, $characterId, 'character');
                $characterRepository->delete($character->getId());
            }

            // Delete the User's Universes
            $universes = $universeRepository->getAllByUserId($userId);
            foreach ($universes as $universe) {
                $universeId = $universe->getId();
                $universeImage = $universe->getImage();
                $stableDiffusionService->deleteImageIfUnused($universeImage, $universed, 'universe');
                $universeRepository->delete($universe->getId());
            }

            // Delete the User
            $userRepository->delete($userId);

            // Commit the transaction
            $this->dbConnector->commit();

            http_response_code(200);
            echo json_encode(['message' => 'Utilisateur supprimé avec succès']);
        } catch (Exception $e) {
            // Cancel the transaction if an error occurs
            $this->dbConnector->rollBack();
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la suppression de l\'utilisateur : ' . $e->getMessage()]);
        }
    }
}
