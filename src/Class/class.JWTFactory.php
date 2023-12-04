<?php // path: src/Class/class.JWTFactory.php

require __DIR__ . '/class.JWTKeySingleton.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTFactory {
    
    public static function createToken($payload) {
        $key = JWTKeySingleton::getInstance()->getSecretKey();
        return JWT::encode($payload, $key, 'HS256');
    }

    public static function validateToken($token) {
        $key = JWTKeySingleton::getInstance()->getSecretKey();
        try {
            return JWT::decode($token, new Key($key, 'HS256'));
        } catch (Exception $e) {
            // Gérer l'exception ou retourner false si le token n'est pas valide
            return false;
        }
    }

    public static function getAuthorizationToken() {
        $headers = apache_request_headers();
        if (isset($headers["Authorization"])) {
            $matches = [];
            if (preg_match('/Bearer\s(\S+)/', $headers["Authorization"], $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}