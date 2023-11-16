<?php // path: src/Class/class.JWTKeySingleton.php

class JWTKeySingleton {
    private static $instance = null;
    private $secretKey;

    private function __construct() {
        // Vous pouvez charger cette clé depuis un fichier de configuration ou une variable d'environnement
        $this->secretKey = 'your_secret_key'; 
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