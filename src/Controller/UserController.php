<?php // path: src/Controller/UserController.php

require_once __DIR__ . '/../Repository/UserRepository.php';

class UserController
{
    public function createUser($requestMethod, $id)
    {
        if ($requestMethod !== 'POST') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }
    
        try {
            $requestData = json_decode(file_get_contents('php://input'), true);
    
            if (!isset($requestData['email'], $requestData['password'], $requestData['username'], $requestData['firstName'], $requestData['lastName']) ||
                empty($requestData['email']) || empty($requestData['password']) || empty($requestData['username']) ||
                empty($requestData['firstName']) || empty($requestData['lastName'])) {
                http_response_code(400);
                echo json_encode(['message' => 'Données manquantes ou invalides']);
                return;
            }
    
            $userRepository = new UserRepository();
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


    public function getAllUsers($requestMethod)
    {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }
    
        try {
            $userRepository = new UserRepository();
            $users = $userRepository->getAll();
    
            if (empty($users)) {
                $response = [
                    'success' => true,
                    'message' => 'Aucun utilisateur trouvé.',
                    'data' => []
                ];
            } else {
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


    public function getUserById($requestMethod, $userId)
    {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        $userId = (int) $userId;

        try {
            $userRepository = new UserRepository();
            $user = $userRepository->getById($userId);

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

    public function updateUser($requestMethod, $userId)
    {
        if ($requestMethod !== 'PUT') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }
    
        $userId = (int) $userId;
    
        try {
            $requestData = json_decode(file_get_contents('php://input'), true);
    
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


    public function deleteUser($requestMethod, $userId)
    {
        if ($requestMethod !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        $userId = (int) $userId;

        try {
            $userRepository = new UserRepository();
            $user = $userRepository->getById($userId);

            if ($user) {
                $userRepository->delete($userId);

                http_response_code(200);
                echo json_encode(['message' => 'Utilisateur supprimé avec succès']);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Utilisateur non trouvé']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la suppression de l\'utilisateur : ' . $e->getMessage()]);
        }
    }
}