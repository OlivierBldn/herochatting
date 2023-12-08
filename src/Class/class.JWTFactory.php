<?php // path: src/Class/class.JWTFactory.php

require __DIR__ . '/class.JWTKeySingleton.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Class JWTFactory
 * 
 * This class is the JWT factory class.
 * It is used to create and validate JWT tokens.
 * 
 */
class JWTFactory {
    
    /**
     * Function to create a JWT token
     *
     * @param array $payload
     * @return string
     */
    public static function createToken($payload) {
        // Get the JWT key from the singleton
        $key = JWTKeySingleton::getInstance()->getSecretKey();

        // Encode the payload with the key
        return JWT::encode($payload, $key, 'HS256');
    }

    /**
     * Function to validate a JWT token
     *
     * @param string $token
     * @return mixed
     */
    public static function validateToken($token) {
        // Get the JWT key from the singleton
        $key = JWTKeySingleton::getInstance()->getSecretKey();

        // Try to decode the token with the key
        try {
            return JWT::decode($token, new Key($key, 'HS256'));
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Function to get the authorization token from the request headers
     *
     * @return string|null
     */
    public static function getAuthorizationToken() {
        // Get the authorization header
        $headers = apache_request_headers();

        // If the authorization header is set, get the token from the header
        if (isset($headers["Authorization"])) {
            $matches = [];

            // Check if the authorization header is a bearer token and if so return the token
            if (preg_match('/Bearer\s(\S+)/', $headers["Authorization"], $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}