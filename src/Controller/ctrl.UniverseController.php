<?php // path: src/Controller/ctrl.UniverseController.php

require_once __DIR__ . '/../Repository/repo.UniverseRepository.php';

class UniverseController
{
    public function createUniverse($requestMethod)
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

            $universeRepository = new UniverseRepository();

            $existingUniverse = new Universe();
            
            if (!$existingUniverse) {
                http_response_code(404);
                echo json_encode(['message' => 'Univers non trouvé']);
                return;
            }

            $newUniverse = $existingUniverse->clone();

            if (isset($requestData['name'])) {
                $newUniverse->setName($requestData['name']);
            }
            if (isset($requestData['description'])) {
                $newUniverse->setDescription($requestData['description']);
            }
            if (isset($requestData['image'])) {
                $newUniverse->setImage($requestData['image']);
            }

            $success = $universeRepository->create($newUniverse->toMap());

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

    public function getAllUniversesByUserId($requestMethod)
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

        $userId = (int) $segments[3];

        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode(['message' => 'Utilisateur invalide']);
            return;
        }

        try {
            $universeRepository = new UniverseRepository();
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

        $universeId = (int) $universeId;

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
            $requestData = json_decode(file_get_contents('php://input'), true);

            if ($universeId <= 0) {
                http_response_code(400);
                echo json_encode(['message' => 'L\'identifiant de l\'univers est invalide']);
                return;
            }

            if (empty($requestData)) {
                http_response_code(400);
                echo json_encode(['message' => 'Aucune donnée fournie pour la mise à jour']);
                return;
            }

            $universeRepository = new UniverseRepository();

            $existingUniverse = new Universe();

            if (!$existingUniverse) {
                http_response_code(404);
                echo json_encode(['message' => 'Univers non trouvé']);
                return;
            }

            $updatedUniverse = $existingUniverse;

            if (isset($requestData['name'])) {
                $updatedUniverse->setName($requestData['name']);
            }
            if (isset($requestData['description'])) {
                $updatedUniverse->setDescription($requestData['description']);
            }
            if (isset($requestData['image'])) {
                $updatedUniverse->setImage($requestData['image']);
            }

            $success = $universeRepository->update($universeId, $updatedUniverse->toMap());

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
            $universeRepository = new UniverseRepository();
            $universe = $universeRepository->getById($universeId);

            if ($universe) {
                $universeRepository->delete($universeId);

                http_response_code(200);
                echo json_encode(['message' => 'Univers supprimé avec succès']);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Univers non trouvé']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la suppression de l\'univers : ' . $e->getMessage()]);
        }
    }
}
