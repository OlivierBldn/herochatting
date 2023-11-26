<?php

require_once __DIR__ . '/../Class/class.JWTKeySingleton.php';
require_once __DIR__ . '/../Class/class.JWTFactory.php';
require_once __DIR__ . '/../Repository/repo.UserRepository.php';

class AuthController {

    public function authenticate($request) {
        $requestData = json_decode(file_get_contents('php://input'), true);

        $email = $requestData['email'] ?? null;
        $password = $requestData['password'] ?? null; 

        if ($email === null || $password === null) {
            http_response_code(400);
            return json_encode(['error' => 'Requête invalide, il manque un identifiant']);
        }

        $userRepository = new UserRepository();
        $user = $userRepository->getByEmail($email);

        if ($user && password_verify($password, $user->getPassword())) {

            $date = new DateTimeImmutable();

            // Set the expiration time of the token (24 hours)
            $expire_at = $date->modify('+1440 minutes')->getTimestamp();

            $payload = [
                'iat'  => $date->getTimestamp(),
                'aud' => __WEBSITE_URL__,
                'exp'  => $expire_at,
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername()
            ];

            $jwtKey = JWTKeySingleton::getInstance()->getSecretKey();
            $token = JWTFactory::createToken($payload, $jwtKey);

            // return json_encode(['token' => $token]);

            echo json_encode([
                'token' => $token,
                'expire_at' => $expire_at,
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'username' => $user->getUsername()
                ]
            ]);
        } else {
            http_response_code(401);
            return json_encode(['error' => 'Authentification échouée']);
        }
    }
}
