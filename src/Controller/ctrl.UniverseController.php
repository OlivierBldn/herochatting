<?php // path: src/Controller/ctrl.UniverseController.php

require_once __DIR__ . '/../Class/class.DBConnectorFactory.php';
require_once __DIR__ . '/../Repository/repo.UniverseRepository.php';
require_once __DIR__ . '/../Repository/repo.CharacterRepository.php';
require_once __DIR__ . '/../Repository/repo.ChatRepository.php';
require_once __DIR__ . '/../Repository/repo.MessageRepository.php';
require_once __DIR__ . '/../Repository/repo.UserRepository.php';
require_once __DIR__ . '/../Class/Service/srv.OpenAIService.php';
require_once __DIR__ . '/../Class/Service/srv.StableDiffusionService.php';
require_once __DIR__ . '/../Class/Middleware/mdw.OwnershipVerifierMiddleware.php';

/**
 * Class UniverseController
 * 
 * This class is the controller for the Universe.
 * 
 */
class UniverseController
{
    private $dbConnector;

    public function __construct()
    {
        $this->dbConnector = DBConnectorFactory::getConnector();
        $this->ownershipVerifier = new OwnershipVerifierMiddleware();
    }

    /**
     * Function to create a universe
     *
     * @param string $requestMethod
     * @return void
     */
    public function createUniverse($requestMethod)
    {
        // Check if the request method is POST
        if ($requestMethod !== 'POST') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisee']);
            return;
        }

        $requestUri = $_SERVER['REQUEST_URI'];
        $segments = explode('/', $requestUri);

        // Check if the user id is set in the URL
        if(!isset($segments[3])) {
            http_response_code(400);
            echo json_encode(['message' => 'Il manque l\'identifiant de l\'utilisateur dans l\'URL']);
            return;
        }

        $userId = (int) $segments[3];
        $universeRepository = new UniverseRepository();

        // Check if the user exists
        if(!$universeRepository->userExists($userId)) {
            http_response_code(404);
            echo json_encode(['message' => 'Utilisateur non trouve']);
            return;
        }

        // Check if the User that sends the request is the owner of the requested User
        if (!$this->ownershipVerifier->handle($userId, 'user')) {
            http_response_code(403);
            echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant de l\'utilisateur dans l\'URL']);
            return;
        }

        try {
            $requestData = json_decode(file_get_contents('php://input'), true);

            // Check if the request is valid
            if (!isset($requestData['name']) || empty($requestData['name'])) {
                http_response_code(400);
                echo json_encode(['message' => 'Nom de l\'univers manquant ou invalide']);
                return;
            }

            $universeName = $requestData['name'];

            // Check if the Universe name is not already used by the User
            if ($universeRepository->universeExistsForUser($userId, $universeName)) {
                http_response_code(409); // Code 409 Conflict
                echo json_encode(['message' => 'Vous possedez deja un univers avec ce nom']);
                return;
            }

            // Check if a Universe with the same name already exists
            $existingUniverse = $universeRepository->getByName($requestData['name']);

            $openAIService = OpenAIService::getInstance();
            $stableDiffusionService = StableDiffusionService::getInstance();

            switch($existingUniverse) {
                case null:
                    // Create a new Universe with the data from the request if no Universe with the same name exists
                    $newUniverse = new Universe();

                    // Generate a description for the Universe using the OpenAI API
                    $prompt = "Fais-moi une description de l'univers de {$requestData['name']}. Son epoque, son histoire et ses specificites.";
                    $requestData['description'] = $openAIService->generateDescription($prompt);
    
                    // Generate a description for the Universe image using the OpenAI API
                    $imagePrompt = "Ecris moi un prompt pour generer une image avec l'intelligence artificielle Text-to-image nomme StableDiffusion afin de representer l'univers {$requestData['name']}. Le prompt doit etre en anglais, il doit decrire l'univers {$requestData['name']} d'un point de vue general et egalement d'un point de vue graphique. Le prompt ne doit pas depasser 300 caracteres.";
                    $imageDescription = $openAIService->generateDescription($imagePrompt);

                    // Generate an image for the Universe using the StableDiffusion API
                    $requestData['image'] = $stableDiffusionService->generateImage($imageDescription);
    
                    $this->setUniverseData($newUniverse, $requestData);
                    break;
                case true:
                    // Clone the existing Universe if a Universe with the same name exists
                    $newUniverse = $existingUniverse->clone();
                    $this->setUniverseData($existingUniverse, $existingUniverse->toMap());
                    $requestData['image'] = $existingUniverse->getImage();
                    break;
                default:
                    // By default, create a new Universe
                    $newUniverse = new Universe();
                    $prompt = "Fais-moi une description de l'univers de {$requestData['name']}. Son epoque, son histoire et ses specificites.";
                    $requestData['description'] = $openAIService->generateDescription($prompt);

                    $imagePrompt = "Ecris moi un prompt pour generer une image avec l'intelligence artificielle Text-to-image nomme StableDiffusion afin de representer l'univers {$requestData['name']}. Le prompt doit etre en anglais, il doit decrire l'univers {$requestData['name']} d'un point de vue general et egalement d'un point de vue graphique. Le prompt ne doit pas depasser 300 caracteres.";
                    $imageDescription = $openAIService->generateDescription($imagePrompt);
                    $requestData['image'] = $stableDiffusionService->generateImage($imageDescription);

                    $this->setUniverseData($newUniverse, $requestData);
                    break;
            }

            $success = $universeRepository->create($newUniverse->toMap(), $userId);

            if ($success) {
                // Get the id from the newly created Universe
                $universeId = $success;
                $imageFileName = $requestData['image'];

                // Add the reference between the image and the Universe in the database
                if (!empty($imageFileName)) {
                    $universeRepository->addImageReference($imageFileName, $universeId, 'universe');
                }

                $successResponse = [
                    'success' => true,
                    'message' => 'Univers cree avec succes',
                    'universeId' => $universeId,
                    'imageFileName' => $imageFileName,
                ];
                http_response_code(201);
                echo json_encode($successResponse);
            } else {
                throw new Exception("Erreur lors de la creation de l'univers");
            }
        } catch (Exception $e) {
            $errorResponse = [
                'success' => false,
                'message' => 'Erreur lors de la creation de l\'univers : ' . $e->getMessage()
            ];
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }

    /**
     * Function to set the data of a Universe
     *
     * @param Universe $universe
     * @param array $requestData
     * @return void
     */
    private function setUniverseData(Universe $universe, array $requestData) {
        if (isset($requestData['name'])) {
            $universe->setName($requestData['name']);
        }
        if (isset($requestData['description'])) {
            $universe->setDescription($requestData['description']);
        }
        if (isset($requestData['image'])) {
            $universe->setImage($requestData['image']);
        }
    }

    /**
     * Function to get all the Universes of a User
     *
     * @param string $requestMethod
     * @return void
     */
    public function getAllUniversesByUserId($requestMethod)
    {
        // Check if the request method is GET
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisee']);
            return;
        }

        $universeRepository = new UniverseRepository();

        $requestUri = $_SERVER['REQUEST_URI'];

        $segments = explode('/', $requestUri);

        // Check if the User id is set in the URL
        if(!isset($segments[3])) {
            http_response_code(400);
            echo json_encode(['message' => 'Il manque l\'identifiant de l\'utilisateur dans l\'URL']);
            return;
        }

        $userId = (int) $segments[3];

        // Check if the User exists
        if (!$universeRepository->userExists($userId)) {
            http_response_code(400);
            echo json_encode(['message' => 'Utilisateur invalide']);
            return;
        }
    
        // Check if the User that sends the request is the owner of the requested User
        if (!$this->ownershipVerifier->handle($userId, 'user')) {
            http_response_code(403);
            echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant de l\'utilisateur dans l\'URL']);
            return;
        }
        
        try {
            $universes = $universeRepository->getAllByUserId($userId);

            // Return a success response if the query was executed successfully
            if (empty($universes)) {
                $response = [
                    'success' => true,
                    'message' => 'Aucun univers trouve.',
                ];
            } else {
                // Return an array of Universes if some Universes were found
                $responseData = [];
                foreach ($universes as $universe) {
                    $responseData[] = $universe->toMap();
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
                'message' => 'Erreur lors de la recuperation des univers : ' . $e->getMessage()
            ];

            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }

    /**
     * Function to get all the Universes
     *
     * @param string $requestMethod
     */
    public function getAllUniverses($requestMethod)
    {
        // Check if the request method is GET
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisee']);
            return;
        }

        try {
            $universeRepository = new UniverseRepository();

            // Get all the Universes using the UniverseRepository
            $universes = $universeRepository->getAll();

            // Return a success response if the query was executed successfully
            if (empty($universes)) {
                $response = [
                    'success' => true,
                    'message' => 'Aucun univers trouve.',
                    'data' => []
                ];
            } else {
                // Return an array of Universes if some Universes were found
                $responseData = [];
                foreach ($universes as $universe) {
                    $responseData[] = $universe->toMap();
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
                'message' => 'Erreur lors de la recuperation des univers : ' . $e->getMessage()
            ];

            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }

    /**
     * Function to get a Universe by its id
     *
     * @param string $requestMethod
     * @param int $universeId
     * @return void
     */
    public function getUniverseById($requestMethod, $universeId)
    {
        // Check if the request method is GET
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisee']);
            return;
        }

        try {
            $universeRepository = new UniverseRepository();
            $universe = $universeRepository->getById($universeId);

            // Return the Universe if it was found
            if ($universe !== null) {

                $userRepository = new UserRepository();
                $userId = $universeRepository->getUniverseUserId($universeId);
        
                // Check if the User that sends the request exists
                if (!$userRepository->getById($userId)) {
                    http_response_code(404);
                    echo json_encode(['message' => 'Utilisateur invalide']);
                    return;
                }
        
                // Check if the User that sent the request is the owner of the Universe
                if (!$this->ownershipVerifier->handle($userId, 'user')) {
                    http_response_code(403);
                    echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant de l\'utilisateur dans l\'URL']);
                    return;
                }

                $universeData = $universe->toMap();

                http_response_code(200);
                echo json_encode($universeData);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Univers non trouve']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la recuperation de l\'univers : ' . $e->getMessage()]);
        }
    }

    /**
     * Function to update a Universe
     *
     * @param string $requestMethod
     * @param int $universeId
     * @return void
     */
    public function updateUniverse($requestMethod, $universeId)
    {
        // Check if the request method is PUT
        if ($requestMethod !== 'PUT') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisee']);
            return;
        }
        
        try {    
            $universeRepository = new UniverseRepository();
            $existingUniverse = $universeRepository->getById($universeId);
    
            // Check if the Universe exists
            if (!$existingUniverse) {
                http_response_code(404);
                echo json_encode(['message' => 'Univers non trouve']);
                return;
            }

            // Check if the User that sends the request exists
            $userRepository = new UserRepository();
            $userId = $universeRepository->getUniverseUserId($universeId);

            if (!$userRepository->getById($userId)) {
                http_response_code(404);
                echo json_encode(['message' => 'Utilisateur non trouve']);
                return;
            }
    
            // Check if the User that sends the request is the owner of the requested User
            if (!$this->ownershipVerifier->handle($userId, 'user')) {
                http_response_code(403);
                echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant de l\'utilisateur dans l\'URL']);
                return;
            }

            $requestData = json_decode(file_get_contents('php://input'), true);
    
            if (empty($requestData)) {
                http_response_code(400);
                return;
            }
    
            // Update the Universe with the data from the request
            if (isset($requestData['name'])) {
                $existingUniverse->setName($requestData['name']);
            }
            if (isset($requestData['description'])) {
                $existingUniverse->setDescription($requestData['description']);
            }
            if (isset($requestData['image'])) {
                $existingUniverse->setImage($requestData['image']);
            }
    
            // Update the Universe in the database using the UniverseRepository
            $success = $universeRepository->update($universeId, $existingUniverse->toMap());
    
            if ($success) {
                http_response_code(200);
                echo json_encode(['message' => 'Univers mis a jour avec succes']);
            } else {
                throw new Exception("Erreur lors de la mise a jour de l'univers");
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la mise a jour de l\'univers : ' . $e->getMessage()]);
        }
    }

    /**
     * Function to delete a Universe
     *
     * @param string $requestMethod
     * @param int $universeId
     * @return void
     */
    public function deleteUniverse($requestMethod, $universeId)
    {
        // Check if the request method is DELETE
        if ($requestMethod !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode non autorisee']);
            return;
        }

        try {

            $universeRepository = new UniverseRepository();

            // Check if the Universe exists
            $universe = $universeRepository->getById($universeId);
            if (!$universe) {
                http_response_code(404);
                echo json_encode(['message' => 'Univers non trouve']);
                return;
            }

            // Check if the User that sends the request is the owner of the Universe
            $userId = $universeRepository->getUniverseUserId($universeId);

            $userRepository = new UserRepository();

            if (!$userRepository->getById($userId)) {
                http_response_code(404);
                echo json_encode(['message' => 'Utilisateur non trouve']);
                return;
            }
    
            // Check if the User that sends the request is the owner of the requested User
            if (!$this->ownershipVerifier->handle($userId, 'user')) {
                http_response_code(403);
                echo json_encode(['message' => 'Acces refuse, verifiez l\'identifiant de l\'utilisateur dans l\'URL']);
                return;
            }

            // Begin a transaction to execute multiple queries
            $this->dbConnector->beginTransaction();

            $characterRepository = new CharacterRepository();

            $characterController = new CharacterController();

            // Delete the image of the Universe if it is not used by other entities
            $universeImage = $universe->getImage();
            $stableDiffusionService = StableDiffusionService::getInstance();
            $stableDiffusionService->deleteImageIfUnused($universeImage, $universeId, 'universe');
            
            // Delete the Characters linked to the Universe
            $characters = $characterRepository->getAllByUniverseId($universeId);
            foreach ($characters as $character) {
                $characterController->deleteCharacter('DELETE', $character->getId());
            }

            // Delete the Universe
            $universeRepository->delete($universeId);

            // Commit the transaction
            $this->dbConnector->commit();

            http_response_code(200);
            echo json_encode(['message' => 'Univers supprime avec succes']);
        } catch (Exception $e) {
            // Cancel the transaction if an error occurs
            $this->dbConnector->rollBack();
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la suppression de l\'univers : ' . $e->getMessage()]);
        }
    }
}
