<?php

require_once __DIR__ . '/../Class/class.JWTKeySingleton.php';
require_once __DIR__ . '/../Class/class.JWTFactory.php';
require_once __DIR__ . '/../Repository/repo.UserRepository.php';

/**
 * Class AuthController
 * 
 * This class is the controller for the authentication.
 * 
 */
class AuthController {

    /**
     * Function to authenticate a user and to generate a JWT token
     *
     * @param array $request
     * @return string
     */
    public function authenticate($request) {

        // Get the request data
        $requestData = json_decode(file_get_contents('php://input'), true);

        $email = $requestData['email'] ?? null;
        $password = $requestData['password'] ?? null; 

        // Check if the email and the password are set. If not, return an error
        if ($email === null || $password === null) {
            http_response_code(400);
            return json_encode(['error' => 'Requete invalide, il manque un identifiant']);
        }

        $userRepository = new UserRepository();
        $user = $userRepository->getByEmail($email);

        // Check if the user exists and if the password is correct
        if ($user && password_verify($password, $user->getPassword())) {

            $date = new DateTimeImmutable();

            // Set the expiration time of the token (24 hours)
            $expire_at = $date->modify('+1440 minutes')->getTimestamp();

            // Create the payload for the token
            $payload = [
                'iat'  => $date->getTimestamp(),
                'aud' => __WEBSITE_URL__,
                'exp'  => $expire_at,
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername()
            ];

            // Create the token using the payload and the JWT key
            $jwtKey = JWTKeySingleton::getInstance()->getSecretKey();
            $token = JWTFactory::createToken($payload, $jwtKey);

            // Commented out for production
            // return json_encode(['token' => $token]);

            // Return the token and the expiration time
            // Used to make troobleshooting easier locally
            // Preferably, only return the token if used on production
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
            echo json_encode(['error' => 'Authentification echouee, veuillez verifier vos identifiants']);
            return;
        }
    }
}
