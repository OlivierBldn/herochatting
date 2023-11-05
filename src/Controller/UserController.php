<?php // path: src/Controller/UserController.php

require_once __DIR__ . '/../Repository/UserRepository.php';

class UserController
{
    // Méthode pour créer un utilisateur
    public function createUser($requestMethod)
    {
        if ($requestMethod !== 'POST') {
            http_response_code(405); // Méthode non autorisée
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        // Récupérer les données de l'utilisateur depuis la requête POST
        $userData = json_decode(file_get_contents('php://input'));

        try {
            // Ajouter l'utilisateur à la base de données en utilisant le UserRepository
            $userRepository = new UserRepository();

            // Créer un nouvel utilisateur en utilisant les setters
            $user = new User();
            $user->setEmail($userData->email);
            $user->setPassword($userData->password);
            $user->setUsername($userData->username);
            $user->setFirstName($userData->firstName);
            $user->setLastName($userData->lastName);

            $userRepository->create($user);

            // Répondre avec un statut de succès
            http_response_code(201); // Créé avec succès
            echo json_encode(['message' => 'Utilisateur créé avec succès']);
        } catch (Exception $e) {
            // En cas d'erreur, répondre avec une erreur
            http_response_code(500); // Erreur interne du serveur
            echo json_encode(['message' => 'Erreur lors de la création de l\'utilisateur : ' . $e->getMessage()]);
        }
    }

    // Méthode pour récupérer un utilisateur par ID
    public function getUserById($requestMethod, $userId)
    {
        if ($requestMethod !== 'GET') {
            http_response_code(405); // Méthode non autorisée
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        $userId = (int) $userId;

        try {
            // Récupérer l'utilisateur par ID en utilisant le UserRepository
            $userRepository = new UserRepository();
            $user = $userRepository->getById($userId);

            if ($user) {
                // Répondre avec les données de l'utilisateur
                $responseData = [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'username' => $user->getUsername(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'password' => $user->getPassword()
                ];


                http_response_code(200); // OK
                echo json_encode($responseData);
            } else {
                // Répondre avec une erreur si l'utilisateur n'est pas trouvé
                http_response_code(404); // Non trouvé
                echo json_encode(['message' => 'Utilisateur non trouvé']);
            }
        } catch (Exception $e) {
            // En cas d'erreur, répondre avec une erreur
            http_response_code(500); // Erreur interne du serveur
            echo json_encode(['message' => 'Erreur lors de la récupération de l\'utilisateur : ' . $e->getMessage()]);
        }
    }
    
    // Méthode pour mettre à jour un utilisateur par ID
    public function updateUser($requestMethod, $userId)
    {
        if ($requestMethod !== 'PUT') {
            http_response_code(405); // Méthode non autorisée
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        // Récupérer l'ID de l'utilisateur depuis l'URI
        $userId = (int) $userId;

        // Récupérer les données de l'utilisateur depuis la requête PUT
        $userData = json_decode(file_get_contents('php://input'));

        try {
            // Récupérer l'utilisateur par ID en utilisant le UserRepository
            $userRepository = new UserRepository();
            $user = $userRepository->getById($userId);

            if ($user) {
                // Mettre à jour les propriétés en fonction des champs fournis
                foreach ($userData as $field => $value) {
                    switch ($field) {
                        case 'email':
                            $user->setEmail($value);
                            break;
                        case 'password':
                            $user->setPassword($value);
                            break;
                        case 'username':
                            $user->setUsername($value);
                            break;
                        case 'firstName':
                            $user->setFirstName($value);
                            break;
                        case 'lastName':
                            $user->setLastName($value);
                            break;
                        default:
                            // Ignorer les champs inconnus
                            break;
                    }
                }

                // Mettre à jour l'utilisateur dans la base de données
                $userRepository->update($user);

                // Répondre avec un statut de succès
                http_response_code(200); // OK
                echo json_encode(['message' => 'Utilisateur mis à jour avec succès']);
            } else {
                // Répondre avec une erreur si l'utilisateur n'est pas trouvé
                http_response_code(404); // Non trouvé
                echo json_encode(['message' => 'Utilisateur non trouvé']);
            }
        } catch (Exception $e) {
            // En cas d'erreur, répondre avec une erreur
            http_response_code(500); // Erreur interne du serveur
            echo json_encode(['message' => 'Erreur lors de la mise à jour de l\'utilisateur : ' . $e->getMessage()]);
        }
    }

    // Méthode pour supprimer un utilisateur par ID
    public function deleteUser($requestMethod, $userId)
    {
        if ($requestMethod !== 'DELETE') {
            http_response_code(405); // Méthode non autorisée
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        // Récupérer l'ID de l'utilisateur depuis l'URI
        $userId = (int) $userId;

        try {
            // Récupérer l'utilisateur par ID en utilisant le UserRepository
            $userRepository = new UserRepository();
            $user = $userRepository->getById($userId);

            if ($user) {
                // Supprimer l'utilisateur de la base de données
                $userRepository->delete($userId);

                // Répondre avec un statut de succès
                http_response_code(200); // OK
                echo json_encode(['message' => 'Utilisateur supprimé avec succès']);
            } else {
                // Répondre avec une erreur si l'utilisateur n'est pas trouvé
                http_response_code(404); // Non trouvé
                echo json_encode(['message' => 'Utilisateur non trouvé']);
            }
        } catch (Exception $e) {
            // En cas d'erreur, répondre avec une erreur
            http_response_code(500); // Erreur interne du serveur
            echo json_encode(['message' => 'Erreur lors de la suppression de l\'utilisateur : ' . $e->getMessage()]);
        }
    }

    // Méthode pour récupérer tous les utilisateurs
    public function getAllUsers($requestMethod)
    {
        if ($requestMethod !== 'GET') {
            http_response_code(405); // Méthode non autorisée
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        try {
            // Récupérer tous les utilisateurs en utilisant le UserRepository
            $userRepository = new UserRepository();
            $users = $userRepository->getAll();

            // Construire un tableau de données en utilisant les getters de User
            $responseData = [];
            foreach ($users as $user) {
                $responseData[] = [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'username' => $user->getUsername(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'password' => $user->getPassword()
                ];
            }

            // Répondre avec les données de tous les utilisateurs
            http_response_code(200); // OK
            echo json_encode($responseData);
        } catch (Exception $e) {
            // En cas d'erreur, répondre avec une erreur
            http_response_code(500); // Erreur interne du serveur
            echo json_encode(['message' => 'Erreur lors de la récupération des utilisateurs : ' . $e->getMessage()]);
        }
    }
}
