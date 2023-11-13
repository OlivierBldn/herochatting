<?php // path: src/Controller/ctrl.CharacterController.php

require_once __DIR__ . '/../Repository/repo.CharacterRepository.php';

class CharacterController
{
    public function createCharacter($requestMethod)
    {
        if ($requestMethod !== 'POST') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }
    
        $requestUri = $_SERVER['REQUEST_URI'];
        $segments = explode('/', $requestUri);
    
        if (!isset($segments[3])) {
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
    
            if (!isset($requestData['name'], $requestData['description'], $requestData['image']) ||
                empty($requestData['name']) || empty($requestData['description']) || empty($requestData['image'])) {
                http_response_code(400);
                echo json_encode(['message' => 'Données manquantes ou invalides']);
                return;
            }
    
            // Récupération d'un personnage existant ou création d'un nouveau
            $existingCharacter = $characterRepository->getByName($requestData['name']);
            $newCharacter = $existingCharacter ? $existingCharacter->clone() : new Character();
    
            // Mise à jour des propriétés du personnage
            $requestData['universeId'] = $universeId;

            if (isset($requestData['name'])) {
                $newCharacter->setName($requestData['name']);
            }
            if (isset($requestData['description'])) {
                $newCharacter->setDescription($requestData['description']);
            }
            if (isset($requestData['image'])) {
                $newCharacter->setImage($requestData['image']);
            }
            
            $success = $characterRepository->create($requestData);
    
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

        if ($universeId <= 0) {
            http_response_code(400);
            echo json_encode(['message' => 'Univers invalide']);
            return;
        }

        try {
            $characterRepository = new CharacterRepository();
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

    public function getCharacterById($requestMethod, $characterId)
    {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
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
            $characterRepository = new CharacterRepository();
            $character = $characterRepository->getById($characterId);

            if ($character) {
                $characterRepository->delete($characterId);

                http_response_code(200);
                echo json_encode(['message' => 'Personnage supprimé avec succès']);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Personnage non trouvé']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la suppression du personnage : ' . $e->getMessage()]);
        }
    }
}