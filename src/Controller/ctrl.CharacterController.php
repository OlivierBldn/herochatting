<?php // path: src/Controller/ctrl.CharacterController.php

require_once __DIR__ . '/../Class/class.DBConnectorFactory.php';
require_once __DIR__ . '/../Repository/repo.CharacterRepository.php';
require_once __DIR__ . '/../Repository/repo.ChatRepository.php';
require_once __DIR__ . '/../Repository/repo.MessageRepository.php';
require_once __DIR__ . '/../Repository/repo.UniverseRepository.php';
require_once __DIR__ . '/../Class/Service/srv.OpenAIService.php';
require_once __DIR__ . '/../Class/Service/srv.StableDiffusionService.php';
require_once __DIR__ . '/../Class/Middleware/mdw.OwnershipVerifierMiddleware.php';

/**
 * Class CharacterController
 * 
 * This class is the controller for the Character.
 * It allows to create, get, update and delete characters.
 * 
 */
class CharacterController
{
    private $dbConnector;

    public function __construct()
    {
        $this->dbConnector = DBConnectorFactory::getConnector();
        $this->ownershipVerifier = new OwnershipVerifierMiddleware();
        $this->chatRepository = new ChatRepository();
    }

    /**
     * Function to create a Character
     *
     * @param string $requestMethod
     * @param string $characterId
     * @return void
     */
    public function createCharacter($requestMethod)
    {
        // Check if the request method is POST
        if ($requestMethod !== 'POST') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisee']);
            return;
        }
    
        // Get the universe ID from the request URI
        $requestUri = $_SERVER['REQUEST_URI'];
        $segments = explode('/', $requestUri);
    
        if(!isset($segments[3])) {
            http_response_code(400);
            echo json_encode(['message' => 'Il manque l\'identifiant de l\'univers dans l\'URL']);
            return;
        }
    
        $universeId = (int) $segments[3];

        $universeRepository = new UniverseRepository();
        $universe = $universeRepository->getById($universeId);
        if (!$universe) {
            http_response_code(404);
            echo json_encode(['message' => 'Univers invalide ou inexistant']);
            return;
        }

        // Check if the User that made the request is the owner of the corresponding Universe
        if (!$this->ownershipVerifier->handle($universeId, 'universe')) {
            http_response_code(403);
            echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant de l\'univers']);
            return;
        }
    
        try {
            $requestData = json_decode(file_get_contents('php://input'), true);

            // Check if the name is set and not empty
            if (!isset($requestData['name']) || empty($requestData['name'])) {
                http_response_code(400);
                echo json_encode(['message' => 'Nom du personnage manquant ou invalide']);
                return;
            }
    
            $characterRepository = new CharacterRepository();
            $characterName = $requestData['name'];

            // Check if the character already exists in the universe
            if ($characterRepository->characterExistsInUniverse($characterName, $universeId)) {
                http_response_code(409);
                echo json_encode(['message' => 'Un personnage avec ce nom existe deja dans cet univers']);
                return;
            }

            // Get the existing character if it exists with the same name and the same universe
            $existingCharacter = $characterRepository->getByNameAndUniverseName($requestData['name'], $characterRepository->getUniverseNameById($universeId));
            $universe = $universeRepository->getById($universeId);

            $openAIService = OpenAIService::getInstance();
            $stableDiffusionService = StableDiffusionService::getInstance();

            switch($existingCharacter) {
                case null:
                    // Create a new character if no existing character was found
                    $newCharacter = new Character();

                    // Generate a description for the character using the OpenAI API
                    $prompt = "Fais moi une description du personnage {$requestData['name']} issu de l'univers {$universe->getName()}. Donne moi son histoire, sa personnalite et ses specificites.";
                    $requestData['description'] = $openAIService->generateDescription($prompt);

                    // Generate a prompt for the character image using the OpenAI API
                    $imagePrompt = "Ecris moi un prompt pour generer une image avec l'intelligence artificielle Text-to-image nomme StableDiffusion afin de representer le personnage {$requestData['name']} issu de l'univers {$universe->getName()}. Le prompt doit etre en anglais, il doit decrire le personnage {$requestData['name']} d'un point de vue general et egalement d'un point de vue graphique. Le prompt ne doit pas depasser 300 caracteres.";
                    $imageDescription = $openAIService->generateDescription($imagePrompt);

                    // Generate an image using the StableDiffusion API using the prompt generated by the OpenAI API
                    $requestData['image'] = $stableDiffusionService->generateImage($imageDescription);

                    $this->setCharacterData($newCharacter, $requestData);
                break;
                case true:
                    // Clone the existing character if it exists
                    $newCharacter = $existingCharacter->clone();
                    $this->setCharacterData($existingCharacter, $existingCharacter->toMap());
                    $requestData['image'] = $existingCharacter->getImage();
                break;
                default:
                    // By default, create a new character
                    $newCharacter = new Character();

                    $prompt = "Fais moi une description du personnage {$requestData['name']} issu de l'univers {$universe->getName()}. Donne moi son histoire, sa personnalite et ses specificites.";
                    $requestData['description'] = $openAIService->generateDescription($prompt);

                    $imagePrompt = "Ecris moi un prompt pour generer une image avec l'intelligence artificielle Text-to-image nomme StableDiffusion afin de representer le personnage {$requestData['name']} issu de l'univers {$universe->getName()}. Le prompt doit etre en anglais, il doit decrire le personnage {$requestData['name']} d'un point de vue general et egalement d'un point de vue graphique. Le prompt ne doit pas depasser 300 caracteres.";
                    $imageDescription = $openAIService->generateDescription($imagePrompt);

                    $requestData['image'] = $stableDiffusionService->generateImage($imageDescription);

                    $this->setCharacterData($newCharacter, $requestData);
                break;
            }
            
            $success = $characterRepository->create($newCharacter->toMap(), $universeId);
    
            // Return a success response if the character was created successfully
            if ($success) {
                $characterId = $success;
                $imageFileName = $requestData['image'];

                // Add an image reference to the character if an image was generated
                if (!empty($imageFileName)) {
                    $characterRepository->addImageReference($imageFileName, $characterId, 'character');
                }

                $successResponse = [
                    'success' => true,
                    'message' => 'Personnage cree avec succes',
                    'characterId' => $characterId,
                    'imageFileName' => $imageFileName,
                ];
                http_response_code(201);
                echo json_encode($successResponse);
            } else {
                throw new Exception("Erreur lors de la creation du personnage");
            }
        } catch (Exception $e) {
            $errorResponse = [
                'success' => false,
                'message' => 'Erreur lors de la creation du personnage : ' . $e->getMessage()
            ];
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }

    /**
     * Function to set the Character data
     *
     * @param Character $character
     * @param array $requestData
     * @return void
     */
    private function setCharacterData(Character $character, array $requestData) {
        if (isset($requestData['name'])) {
            $character->setName($requestData['name']);
        }
        if (isset($requestData['description'])) {
            $character->setDescription($requestData['description']);
        }
        if (isset($requestData['image'])) {
            $character->setImage($requestData['image']);
        }
    }

    /**
     * Function to get all the Characters from the database
     *
     * @param string $requestMethod
     * @return void
     */
    public function getAllCharacters($requestMethod)
    {
        // Check if the request method is GET
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisee']);
            return;
        }

        try {
            $characterRepository = new CharacterRepository();

            // Use the getAll method from the CharacterRepository to get all the characters
            $characters = $characterRepository->getAll();

            // Return a success response if the query was executed successfully
            if (empty($characters)) {
                $response = [
                    'success' => true,
                    'message' => 'Aucun personnage trouve',
                ];
            } else {

                // Map the characters to an array of associative arrays if characters were found
                $responseData = [];
                foreach ($characters as $character) {
                    $responseData[] = $character->toMap();
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
                'message' => 'Erreur lors de la recuperation des personnages : ' . $e->getMessage()
            ];

            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }

    /**
     * Function to get all the Characters from the database given a Universe ID
     *
     * @param string $requestMethod
     * @return void
     */
    public function getAllCharactersByUniverseId($requestMethod)
    {
        // Check if the request method is GET
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisee']);
            return;
        }

        // Get the universe ID from the request URI
        $requestUri = $_SERVER['REQUEST_URI'];

        $segments = explode('/', $requestUri);

        if(!isset($segments[3])) {
            http_response_code(400);
            echo json_encode(['message' => 'Il manque l\'identifiant de l\'univers dans l\'URL']);
            return;
        }

        $universeId = (int) $segments[3];
        $characterRepository = new CharacterRepository();

        // Check if the universe exists
        if (!$characterRepository->universeExists($universeId)) {
            http_response_code(400);
            echo json_encode(['message' => 'Univers invalide ou inexistant']);
            return;
        }

        // Check if the User that made the request is the owner of the corresponding Universe
        if (!$this->ownershipVerifier->handle($universeId, 'universe')) {
            http_response_code(403);
            echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant de l\'univers']);
            return;
        }

        try {
            $characters = $characterRepository->getAllByUniverseId($universeId);

            // Return a success response if the query was executed successfully
            if (empty($characters)) {
                $response = [
                    'success' => true,
                    'message' => 'Aucun personnage trouve',
                ];
            } else {
                // Map the characters to an array of associative arrays if characters were found
                $responseData = [];
                foreach ($characters as $character) {
                    $responseData[] = $character->toMap();
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
                'message' => 'Erreur lors de la recuperation des personnages : ' . $e->getMessage()
            ];

            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }

    /**
     * Function to get all the Characters from the database given a User ID
     *
     * @param string $requestMethod
     * @return void
     */
    public function getCharactersByUserId($requestMethod) {
        // Check if the request method is GET
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode Non Autorisee']);
            return;
        }

        // Get the User ID from the request URI
        $requestUri = $_SERVER['REQUEST_URI'];

        $segments = explode('/', $requestUri);

        if(!isset($segments[3])) {
            http_response_code(400);
            echo json_encode(['message' => 'Il manque l\'identifiant de l\'utilisateur dans l\'URL']);
            return;
        }

        $userId = (int) $segments[3];

        // Check if the User that made the request is the owner of the Characters
        if (!$this->ownershipVerifier->handle($userId, 'user')) {
            http_response_code(403);
            echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant de l\'utlisateur']);
            return;
        }

        $characterRepository = new CharacterRepository();

        try {
            $characters = $characterRepository->getByUserId($userId);

            // Return an array of associative arrays if characters were found
            $characterData = array_map(function($character) {
                return $character->toMap();
            }, $characters);

            http_response_code(200);
            echo json_encode(['characters' => $characterData]);
        } catch (Exception $e) {
            if ($e->getMessage() == "User not found") {
                http_response_code(404);
                echo json_encode(['message' => 'Utilisateur non trouve']);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Erreur lors de la recuperation des personnages : ' . $e->getMessage()]);
            }
        }
    }

    /**
     * Function to get a Character by its ID
     *
     * @param string $requestMethod
     * @param string $characterId
     * @return void
     */
    public function getCharacterById($requestMethod, $characterId)
    {
        // Check if the request method is GET
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisee']);
            return;
        }

        // Check if the User that made the request is the owner of the Character
        if (!$this->ownershipVerifier->handle($characterId, 'character')) {
            http_response_code(403);
            echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant du personnage']);
            return;
        }

        try {
            $characterRepository = new CharacterRepository();
            $character = $characterRepository->getById($characterId);

            // Return an array if the character was found
            if ($character !== null) {
                $characterData = $character->toMap();

                http_response_code(200);
                echo json_encode($characterData);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Personnage non trouve']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la recuperation du personnage : ' . $e->getMessage()]);
        }
    }

    /**
     * Function to update a Character
     *
     * @param string $requestMethod
     * @param string $characterId
     * @return void
     */
    public function updateCharacter($requestMethod, $characterId)
    {
        // Check if the request method is PUT
        if ($requestMethod !== 'PUT') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisee']);
            return;
        }
        
        try {
            $requestData = json_decode(file_get_contents('php://input'), true);
    
            // Check if the character ID is valid
            if ($characterId <= 0) {
                http_response_code(400);
                echo json_encode(['message' => 'L\'identifiant du personnage est invalide']);
                return;
            }

            // Check if the User that made the request is the owner of the Character
            if (!$this->ownershipVerifier->handle($characterId, 'character')) {
                http_response_code(403);
                echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant du personnage']);
                return;
            }
    
            // Check if the request data is empty or not
            if (empty($requestData)) {
                http_response_code(400);
                echo json_encode(['message' => 'Aucune donnee fournie pour la mise a jour']);
                return;
            }
    
            $characterRepository = new CharacterRepository();
            $existingCharacter = $characterRepository->getById($characterId);
    
            // Check if the character exists
            if (!$existingCharacter) {
                http_response_code(404);
                echo json_encode(['message' => 'Personnage non trouve']);
                return;
            }
    
            // Update the character data if the data is set in the query
            if (isset($requestData['name'])) {
                $existingCharacter->setName($requestData['name']);
            }
            if (isset($requestData['description'])) {
                $existingCharacter->setDescription($requestData['description']);
            }
            if (isset($requestData['image'])) {
                $existingCharacter->setImage($requestData['image']);
            }
    
            // Update the character in the database using the CharacterRepository
            $success = $characterRepository->update($characterId, $existingCharacter->toMap());
    
            if ($success) {
                http_response_code(200);
                echo json_encode(['message' => 'Personnage mis a jour avec succes']);
            } else {
                throw new Exception("Erreur lors de la mise a jour du personnage");
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la mise a jour du personnage : ' . $e->getMessage()]);
        }
    }

    /**
     * Function to delete a Character
     *
     * @param string $requestMethod
     * @param string $characterId
     * @return void
     */
    public function deleteCharacter($requestMethod, $characterId)
    {
        // Check if the request method is DELETE
        if ($requestMethod !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisee']);
            return;
        }

        try {

            $characterRepository = new CharacterRepository();

            // Check if the character exists
            $character = $characterRepository->getById($characterId);
            
            if (!$character) {
                http_response_code(404);
                echo json_encode(['message' => 'Personnage non trouve']);
                return;
            }

            // Check if the User that made the request is the owner of the Character
            if (!$this->ownershipVerifier->handle($characterId, 'character')) {
                http_response_code(403);
                echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant du personnage']);
                return;
            }

            // Begin transaction to execute multiple queries
            $this->dbConnector->beginTransaction();

            // Delete the character image if it is not used by other entities
            $characterImage = $character->getImage();
            $stableDiffusionService = StableDiffusionService::getInstance();
            $stableDiffusionService->deleteImageIfUnused($characterImage, $characterId, 'character');

            // Suppression des chats liés à ce personnage
            $chatController = new ChatController();
            $chats = $this->chatRepository->getByCharacterId($characterId);
            foreach ($chats as $chat) {
                $chatController->deleteChat('DELETE', $chat->getId());
            }

            // Suppression du personnage
            $characterRepository->delete($characterId);

            // $chatRepository = new ChatRepository();
            // // $messageRepository = new MessageRepository();

            // $chatController = new ChatController();
            // // $messageController = new MessageController();

            // // Delete the character chats and messages
            // $chats = $chatRepository->getByCharacterId($characterId);
            // foreach ($chats as $chat) {
            //     // $messages = $messageRepository->getMessagesByChatId($chat->getId());
            //     // foreach ($messages as $message) {
            //     //     $messageController->deleteMessage('DELETE', $message->getId());
            //     // }
            //     $chatController->deleteChat('DELETE', $chat->getId());
            // }

            // // Delete the character
            // $characterRepository->delete($characterId);

            // Commit the transaction if all the queries were executed successfully
            $this->dbConnector->commit();

            http_response_code(200);
            echo json_encode(['message' => 'Personnage supprime avec succes']);

        } catch (Exception $e) {
            // Cancel the transaction if an error occured
            $this->dbConnector->rollBack();
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la suppression du personnage : ' . $e->getMessage()]);
        }
    }
}