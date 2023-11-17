<?php // path: src/Class/class.JWTKeySingleton.php

class JWTKeySingleton {
    private static $instance = null;
    private $secretKey;

    private function __construct() {
        $this->secretKey = __API_KEY__;
    }

    public static function getInstance() {
        
        if (self::$instance == null) {
            self::$instance = new JWTKeySingleton();
        }
        return self::$instance;
    }

    public function getSecretKey() {
        return $this->secretKey;
    }
}