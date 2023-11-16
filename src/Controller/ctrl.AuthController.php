<?php

require_once __DIR__ . '/../Class/class.JWTKeySingleton.php';
require_once __DIR__ . '/../Class/class.JWTFactory.php';
require __DIR__ . '/../Class/class.UserRepository.php';

class AuthController {

    public function authenticate($request) {
        $email = $request['email'];
        $password = $request['password'];

        // Logique pour vérifier l'utilisateur
        $userRepository = new UserRepository();
        $user = $userRepository->findByEmail($email);

        if ($user && password_verify($password, $user->getPassword())) {
            // Création du token
            $jwtKey = JWTKeySingleton::getInstance()->getKey();
            $token = JWTFactory::createToken($user, $jwtKey);

            return json_encode(['token' => $token]);
        } else {
            http_response_code(401);
            return json_encode(['error' => 'Authentification échouée']);
        }
    }
}
