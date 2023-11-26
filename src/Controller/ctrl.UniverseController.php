<?php // path: src/Controller/ctrl.UniverseController.php

require_once __DIR__ . '/../Class/class.DBConnectorFactory.php';
require_once __DIR__ . '/../Repository/repo.UniverseRepository.php';
require_once __DIR__ . '/../Repository/repo.CharacterRepository.php';
require_once __DIR__ . '/../Repository/repo.ChatRepository.php';
require_once __DIR__ . '/../Repository/repo.MessageRepository.php';

class UniverseController
{
    private $dbConnector;

    public function __construct()
    {
        $this->dbConnector = DBConnectorFactory::getConnector();
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

            if (!isset($requestData['name'], $requestData['description'], $requestData['image']) ||
                empty($requestData['name']) || empty($requestData['description']) || empty($requestData['image'])) {
                http_response_code(400);
                echo json_encode(['message' => 'Données manquantes ou invalides']);
                return;
            }

            $existingUniverse = $universeRepository->getByName($requestData['name']);

            switch($existingUniverse) {
                case null:
                    $newUniverse = new Universe();
                    $this->setUniverseData($newUniverse, $requestData);
                    break;
                case true:
                    echo json_encode(['message' => 'Un univers avec ce nom existe déjà']);
                    $newUniverse = $existingUniverse->clone();
                    $this->setUniverseData($existingUniverse, $existingUniverse->toMap());
                    break;
                default:
                    $newUniverse = new Universe();
                    $this->setUniverseData($newUniverse, $requestData);
                    return;
            }
            
            $success = $universeRepository->create($newUniverse->toMap(), $userId);

            if ($success) {
                $successResponse = [
                    'success' => true,
                    'message' => 'Univers créé avec succès.'
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
