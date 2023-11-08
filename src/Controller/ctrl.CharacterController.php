<?php // path: src/Controller/ctrl.CharacterController.php

require_once __DIR__ . '/../Repository/repo.CharacterRepository.php';

class CharacterController
{
    public function createCharacter($requestMethod, $universeId)
    {
        if ($requestMethod !== 'POST') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
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

            $requestData['universeId'] = $universeId;

            $characterRepository = new CharacterRepository();
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

    public function getAllCharactersByUniverseId($requestMethod, $universeId)
    {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
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
            $success = $characterRepository->update($characterId, $requestData);

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