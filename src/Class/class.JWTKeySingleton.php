<?php // path: src/Class/class.JWTKeySingleton.php

require __DIR__ . '/../../config/cfg_apiConfig.php';

/**
 * Class JWTKeySingleton
 * 
 * This class is the singleton for the JWT key.
 * it is used to get the JWT key and to avoid multiple instances of the key.
 * 
 */
class JWTKeySingleton {
    private static $instance = null;
    private $secretKey;

    private function __construct() {
        $this->secretKey = __API_KEY__;
    }

    /**
     * Function to get the instance of the JWTKeySingleton
     *
     * @return JWTKeySingleton
     */
    public static function getInstance() {
        
        if (self::$instance == null) {
            self::$instance = new JWTKeySingleton();
        }
        return self::$instance;
    }

    /**
     * Function to get the JWT key
     *
     * @return string
     */
    public function getSecretKey() {
        return $this->secretKey;
    }
}