<?php // path: src/Controller/ctrl.CharacterController.php

require_once __DIR__ . '/../Class/class.DBConnectorFactory.php';
require_once __DIR__ . '/../Repository/repo.CharacterRepository.php';
require_once __DIR__ . '/../Repository/repo.ChatRepository.php';
require_once __DIR__ . '/../Repository/repo.MessageRepository.php';
require_once __DIR__ . '/../Repository/repo.UniverseRepository.php';
require_once __DIR__ . '/../Class/Service/srv.OpenAIService.php';
require_once __DIR__ . '/../Class/Service/srv.StableDiffusionService.php';
require_once __DIR__ . '/../Class/Middleware/mdw.OwnershipVerifierMiddleware.php';

class CharacterController
{
    private $dbConnector;

    public function __construct()
    {
        $this->dbConnector = DBConnectorFactory::getConnector();
        $this->ownershipVerifier = new OwnershipVerifierMiddleware();
    }

    public function createCharacter($requestMethod)
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
    
        $universeId = (int) $segments[3];
        $characterRepository = new CharacterRepository();
    
        // Vérification de l'existence de l'univers
        if (!$characterRepository->universeExists($universeId)) {
            http_response_code(400);
            echo json_encode(['message' => 'Univers invalide ou inexistant']);
            return;
        }
    
        try {
            $requestData = json_decode(file_get_contents('php://input'), true);

            if (!isset($requestData['name']) || empty($requestData['name'])) {
                http_response_code(400);
                echo json_encode(['message' => 'Nom du personnage manquant ou invalide']);
                return;
            }
    
            // Récupération d'un personnage existant ou création d'un nouveau
            $existingCharacter = $characterRepository->getByNameAndUniverseName($requestData['name'], $characterRepository->getUniverseNameById($universeId));
            $universeRepository = new UniverseRepository();
            $universe = $universeRepository->getById($universeId);

            $openAIService = OpenAIService::getInstance();
            $stableDiffusionService = StableDiffusionService::getInstance();

            switch($existingCharacter) {
                case null:
                    // Créer un nouveau personnage si aucun personnage existant n'a été trouvé
                    $newCharacter = new Character();

                    $prompt = "Fais moi une description du personnage {$requestData['name']} issu de l'univers {$universe->getName()}. Donne moi son histoire, sa personnalité et ses spécificités.";
                    $requestData['description'] = $openAIService->generateDescription($prompt);

                    $imagePrompt = "Ecris moi un prompt pour générer une image avec l'intelligence artificielle Text-to-image nommé StableDiffusion afin de représenter le personnage {$requestData['name']} issu de l'univers {$universe->getName()}. Le prompt doit etre en anglais, il doit décrire le personnage {$requestData['name']} d'un point de vue général et également d'un point de vue graphique. Le prompt ne doit pas dépasser 300 caractères.";
                    $imageDescription = $openAIService->generateDescription($imagePrompt);

                    $requestData['image'] = $stableDiffusionService->generateImage($imageDescription);

                    $this->setCharacterData($newCharacter, $requestData);
                break;
                case true:
                    // Cloner le personnage existant
                    $newCharacter = $existingCharacter->clone();
                    $this->setCharacterData($existingCharacter, $existingCharacter->toMap());
                break;
                default:
                    // Gérer les autres cas (si nécessaire)
                    $newCharacter = new Character();

                    $prompt = "Fais moi une description du personnage {$requestData['name']} issu de l'univers {$universe->getName()}. Donne moi son histoire, sa personnalité et ses spécificités.";
                    $requestData['description'] = $openAIService->generateDescription($prompt);

                    $imagePrompt = "Ecris moi un prompt pour générer une image avec l'intelligence artificielle Text-to-image nommé StableDiffusion afin de représenter le personnage {$requestData['name']} issu de l'univers {$universe->getName()}. Le prompt doit etre en anglais, il doit décrire le personnage {$requestData['name']} d'un point de vue général et également d'un point de vue graphique. Le prompt ne doit pas dépasser 300 caractères.";
                    $imageDescription = $openAIService->generateDescription($imagePrompt);

                    $requestData['image'] = $stableDiffusionService->generateImage($imageDescription);

                    $this->setCharacterData($newCharacter, $requestData);
                break;
            }
            
            $success = $characterRepository->create($newCharacter->toMap(), $universeId);
    
            if ($success) {

                $successResponse = [
                    'success' => true,
                    'message' => 'Personnage créé avec succès.'
                ];
                http_response_code(201);
                echo json_encode($successResponse);
            } else {
                throw new Exception("Erreur lors de la création du personnage");
            }
        } catch (Exception $e) {
            $errorResponse = [
                'success' => false,
                'message' => 'Erreur lors de la création du personnage : ' . $e->getMessage()
            ];
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }

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

    public function getAllCharacters($requestMethod)
    {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        try {
            $characterRepository = new CharacterRepository();
            $characters = $characterRepository->getAll();

            if (empty($characters)) {
                $response = [
                    'success' => true,
                    'message' => 'Aucun personnage trouvé.',
                ];
            } else {
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
                'message' => 'Erreur lors de la récupération des personnages : ' . $e->getMessage()
            ];

            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }

    public function getAllCharactersByUniverseId($requestMethod)
    {
        if ($requestMethod !== 'GET') {
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

        $universeId = (int) $segments[3];
        $characterRepository = new CharacterRepository();

        if (!$characterRepository->universeExists($universeId)) {
            http_response_code(400);
            echo json_encode(['message' => 'Univers invalide ou inexistant']);
            return;
        }

        try {
            $characters = $characterRepository->getAllByUniverseId($universeId);

            if (empty($characters)) {
                $response = [
                    'success' => true,
                    'message' => 'Aucun personnage trouvé.',
                ];
            } else {
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
                'message' => 'Erreur lors de la récupération des personnages : ' . $e->getMessage()
            ];

            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }

    public function getCharactersByUserId($requestMethod) {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Methode Non Autorisée']);
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

        $characterRepository = new CharacterRepository();

        try {
            $characters = $characterRepository->getByUserId($userId);
            $characterData = array_map(function($character) {
                return $character->toMap();
            }, $characters);

            http_response_code(200);
            echo json_encode(['characters' => $characterData]);
        } catch (Exception $e) {
            if ($e->getMessage() == "User not found") {
                http_response_code(404);
                echo json_encode(['message' => 'Utilisateur non trouvé']);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Erreur lors de la récupération des personnages : ' . $e->getMessage()]);
            }
        }
    }

    public function getCharacterById($requestMethod, $characterId)
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

        $characterId = (int) $characterId;

        try {
            $characterRepository = new CharacterRepository();
            $character = $characterRepository->getById($characterId);

            if ($character !== null) {
                $characterData = $character->toMap();

                http_response_code(200);
                echo json_encode($characterData);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Personnage non trouvé']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la récupération du personnage : ' . $e->getMessage()]);
        }
    }

    public function updateCharacter($requestMethod, $characterId)
    {
        if ($requestMethod !== 'PUT') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }
    
        $characterId = (int) $characterId;
    
        try {
            $requestData = json_decode(file_get_contents('php://input'), true);
    
            if ($characterId <= 0) {
                http_response_code(400);
                echo json_encode(['message' => 'L\'identifiant du personnage est invalide']);
                return;
            }
    
            if (empty($requestData)) {
                http_response_code(400);
                echo json_encode(['message' => 'Aucune donnée fournie pour la mise à jour']);
                return;
            }
    
            $characterRepository = new CharacterRepository();
            $existingCharacter = $characterRepository->getById($characterId);
    
            if (!$existingCharacter) {
                http_response_code(404);
                echo json_encode(['message' => 'Personnage non trouvé']);
                return;
            }
    
            // Mettre à jour les propriétés du personnage
            if (isset($requestData['name'])) {
                $existingCharacter->setName($requestData['name']);
            }
            if (isset($requestData['description'])) {
                $existingCharacter->setDescription($requestData['description']);
            }
            if (isset($requestData['image'])) {
                $existingCharacter->setImage($requestData['image']);
            }
    
            $success = $characterRepository->update($characterId, $existingCharacter->toMap());
    
            if ($success) {
                http_response_code(200);
                echo json_encode(['message' => 'Personnage mis à jour avec succès']);
            } else {
                throw new Exception("Erreur lors de la mise à jour du personnage");
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la mise à jour du personnage : ' . $e->getMessage()]);
        }
    }

    public function deleteCharacter($requestMethod, $characterId)
    {
        if ($requestMethod !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        $characterId = (int) $characterId;

        try {
            $chatRepository = new ChatRepository();
            $messageRepository = new MessageRepository();
            $characterRepository = new CharacterRepository();

            // Vérifier si le personnage existe
            $character = $characterRepository->getById($characterId);
            
            if (!$character) {
                http_response_code(404);
                echo json_encode(['message' => 'Personnage non trouvé']);
                return;
            }

            // Commencer une transaction
            $this->dbConnector->beginTransaction();

            // Supprimer les messages dans les chats du personnage
            $chats = $chatRepository->getByCharacterId($characterId);
            foreach ($chats as $chat) {
                $messages = $messageRepository->getMessagesByChatId($chat->getId());
                foreach ($messages as $message) {
                    $messageRepository->delete($message->getId());
                }
                $chatRepository->delete($chat->getId());
            }

            // Supprimer le personnage
            $characterRepository->delete($characterId);

            // Valider la transaction
            $this->dbConnector->commit();

            http_response_code(200);
            echo json_encode(['message' => 'Personnage supprimé avec succès']);
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->dbConnector->rollBack();
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la suppression du personnage : ' . $e->getMessage()]);
        }
    }
}