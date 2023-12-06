<?php // path: src/Controller/ctrl.UniverseController.php

require_once __DIR__ . '/../Class/class.DBConnectorFactory.php';
require_once __DIR__ . '/../Repository/repo.UniverseRepository.php';
require_once __DIR__ . '/../Repository/repo.CharacterRepository.php';
require_once __DIR__ . '/../Repository/repo.ChatRepository.php';
require_once __DIR__ . '/../Repository/repo.MessageRepository.php';
require_once __DIR__ . '/../Class/Service/srv.OpenAIService.php';
require_once __DIR__ . '/../Class/Service/srv.StableDiffusionService.php';
require_once __DIR__ . '/../Class/Middleware/mdw.OwnershipVerifierMiddleware.php';

class UniverseController
{
    private $dbConnector;

    public function __construct()
    {
        $this->dbConnector = DBConnectorFactory::getConnector();
        $this->ownershipVerifier = new OwnershipVerifierMiddleware();
    }

    public function createUniverse($requestMethod)
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
            echo json_encode(['message' => 'URL malformée']);
            return;
        }

        $userId = (int) $segments[3];
        $universeRepository = new UniverseRepository();

        if(!$universeRepository->userExists($userId)) {
            http_response_code(404);
            echo json_encode(['message' => 'Utilisateur non trouvé']);
            return;
        }

        try {
            $requestData = json_decode(file_get_contents('php://input'), true);

            if (!isset($requestData['name']) || empty($requestData['name'])) {
                http_response_code(400);
                echo json_encode(['message' => 'Nom de l\'univers manquant ou invalide']);
                return;
            }

            $existingUniverse = $universeRepository->getByName($requestData['name']);
            $openAIService = OpenAIService::getInstance();
            $stableDiffusionService = StableDiffusionService::getInstance();

            switch($existingUniverse) {
                case null:
                    $newUniverse = new Universe();
                    $prompt = "Fais-moi une description de l'univers de {$requestData['name']}. Son époque, son histoire et ses spécificités.";
                    $requestData['description'] = $openAIService->generateDescription($prompt);
    
                    $imagePrompt = "Ecris moi un prompt pour générer une image avec l'intelligence artificielle Text-to-image nommé StableDiffusion afin de représenter l'univers {$requestData['name']}. Le prompt doit etre en anglais, il doit décrire l'univers {$requestData['name']} d'un point de vue général et également d'un point de vue graphique. Le prompt ne doit pas dépasser 300 caractères.";
                    $imageDescription = $openAIService->generateDescription($imagePrompt);

                    $requestData['image'] = $stableDiffusionService->generateImage($imageDescription);
    
                    $this->setUniverseData($newUniverse, $requestData);
                    break;
                case true:
                    $newUniverse = $existingUniverse->clone();
                    $this->setUniverseData($existingUniverse, $existingUniverse->toMap());
                    $requestData['image'] = $existingUniverse->getImage();
                    break;
                default:
                    $newUniverse = new Universe();
                    $prompt = "Fais-moi une description de l'univers de {$requestData['name']}. Son époque, son histoire et ses spécificités.";
                    $requestData['description'] = $openAIService->generateDescription($prompt);

                    $imagePrompt = "Ecris moi un prompt pour générer une image avec l'intelligence artificielle Text-to-image nommé StableDiffusion afin de représenter l'univers {$requestData['name']}. Le prompt doit etre en anglais, il doit décrire l'univers {$requestData['name']} d'un point de vue général et également d'un point de vue graphique. Le prompt ne doit pas dépasser 300 caractères.";
                    $imageDescription = $openAIService->generateDescription($imagePrompt);
                    $requestData['image'] = $stableDiffusionService->generateImage($imageDescription);

                    $this->setUniverseData($newUniverse, $requestData);
                    break;
            }

            $success = $universeRepository->create($newUniverse->toMap(), $userId);

            if ($success) {
                $universeId = $success;
                $imageFileName = $requestData['image'];

                if (!empty($imageFileName)) {
                    $universeRepository->addImageReference($imageFileName, $universeId, 'universe');
                }

                $successResponse = [
                    'success' => true,
                    'message' => 'Univers créé avec succès.',
                    'universeId' => $universeId,
                    'imageFileName' => $imageFileName,
                ];
                http_response_code(201);
                echo json_encode($successResponse);
            } else {
                throw new Exception("Erreur lors de la création de l'univers");
            }
        } catch (Exception $e) {
            $errorResponse = [
                'success' => false,
                'message' => 'Erreur lors de la création de l\'univers : ' . $e->getMessage()
            ];
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }

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

    public function getAllUniversesByUserId($requestMethod)
    {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        $universeRepository = new UniverseRepository();

        $requestUri = $_SERVER['REQUEST_URI'];

        $segments = explode('/', $requestUri);

        if(!isset($segments[3])) {
            http_response_code(400);
            echo json_encode(['message' => 'URL malformée']);
            return;
        }

        $userId = (int) $segments[3];

        if (!$universeRepository->userExists($userId)) {
            http_response_code(400);
            echo json_encode(['message' => 'Utilisateur invalide']);
            return;
        }
        
        try {
            $universes = $universeRepository->getAllByUserId($userId);

            if (empty($universes)) {
                $response = [
                    'success' => true,
                    'message' => 'Aucun univers trouvé.',
                ];
            } else {
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
                'message' => 'Erreur lors de la récupération des univers : ' . $e->getMessage()
            ];

            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }


    public function getAllUniverses($requestMethod)
    {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        try {
            $universeRepository = new UniverseRepository();
            $universes = $universeRepository->getAll();

            if (empty($universes)) {
                $response = [
                    'success' => true,
                    'message' => 'Aucun univers trouvé.',
                    'data' => []
                ];
            } else {
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
                'message' => 'Erreur lors de la récupération des univers : ' . $e->getMessage()
            ];

            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }

    public function getUniverseById($requestMethod, $universeId)
    {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        $ownershipVerifier = new OwnershipVerifierMiddleware();
        if (!$ownershipVerifier->handle($userId, $messageId)) {
            http_response_code(403);
            echo json_encode(['message' => 'Accès refusé']);
            return;
        }

        try {
            $universeRepository = new UniverseRepository();
            $universe = $universeRepository->getById($universeId);

            if ($universe !== null) {
                $universeData = $universe->toMap();

                http_response_code(200);
                echo json_encode($universeData);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Univers non trouvé']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la récupération de l\'univers : ' . $e->getMessage()]);
        }
    }

    public function updateUniverse($requestMethod, $universeId)
    {
        if ($requestMethod !== 'PUT') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }
    
        $universeId = (int) $universeId;
    
        try {    
            $universeRepository = new UniverseRepository();
            $existingUniverse = $universeRepository->getById($universeId);
    
            if (!$existingUniverse) {
                http_response_code(404);
                echo json_encode(['message' => 'Univers non trouvé']);
                return;
            }

            $requestData = json_decode(file_get_contents('php://input'), true);
    
            if (empty($requestData)) {
                http_response_code(400);
                return;
            }
    
            // Mettre à jour les propriétés de l'univers
            if (isset($requestData['name'])) {
                $existingUniverse->setName($requestData['name']);
            }
            if (isset($requestData['description'])) {
                $existingUniverse->setDescription($requestData['description']);
            }
            if (isset($requestData['image'])) {
                $existingUniverse->setImage($requestData['image']);
            }
    
            $success = $universeRepository->update($universeId, $existingUniverse->toMap());
    
            if ($success) {
                http_response_code(200);
                echo json_encode(['message' => 'Univers mis à jour avec succès']);
            } else {
                throw new Exception("Erreur lors de la mise à jour de l'univers");
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la mise à jour de l\'univers : ' . $e->getMessage()]);
        }
    }

    public function deleteUniverse($requestMethod, $universeId)
    {
        if ($requestMethod !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        $universeId = (int) $universeId;

        try {
            $chatRepository = new ChatRepository();
            $messageRepository = new MessageRepository();
            $universeRepository = new UniverseRepository();
            $characterRepository = new CharacterRepository();

            // Vérifier si l'univers existe
            $universe = $universeRepository->getById($universeId);
            if (!$universe) {
                http_response_code(404);
                echo json_encode(['message' => 'Univers non trouvé']);
                return;
            }

            // Commencer une transaction
            $this->dbConnector->beginTransaction();


            // Supprimer l'image de l'univers
            $universeImage = $universe->getImage();
            $stableDiffusionService = StableDiffusionService::getInstance();
            $stableDiffusionService->deleteImageIfUnused($universeImage, $universeId, 'universe');
            

            // Supprimer les messages dans les chats de l'univers
            $chats = $chatRepository->getByUniverseId($universeId);
            foreach ($chats as $chat) {
                $messages = $messageRepository->getMessagesByChatId($chat->getId());
                foreach ($messages as $message) {
                    $messageRepository->delete($message->getId());
                }
                $chatRepository->delete($chat->getId());
            }

            // Supprimer les personnages liés à l'univers
            $characters = $characterRepository->getAllByUniverseId($universeId);
            foreach ($characters as $character) {
                $characterRepository->delete($character->getId());
            }

            // Supprimer l'univers
            $universeRepository->delete($universeId);

            // Valider la transaction
            $this->dbConnector->commit();

            http_response_code(200);
            echo json_encode(['message' => 'Univers supprimé avec succès']);
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->dbConnector->rollBack();
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la suppression de l\'univers : ' . $e->getMessage()]);
        }
    }
}
