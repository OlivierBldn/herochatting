<?php // path: src/Class/class.ErrorHandler.php

/**
 * Class ErrorHandler
 * 
 * This class is the error handler class.
 * 
 */
class ErrorHandler {

    public static function handleException($exception) {
        // Logger l'exception
        error_log("Exception: " . $exception->getMessage());

        // Renvoyer une réponse d'erreur standardisée
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Une erreur interne s'est produite."
        ]);
    }

    public static function handleError($errno, $errstr, $errfile, $errline) {
        // Logger l'erreur
        error_log("Erreur : [$errno] $errstr dans le fichier $errfile à la ligne $errline");

        // Renvoyer une réponse d'erreur standardisée
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Une erreur interne s'est produite."
        ]);

        // Ne pas exécuter le gestionnaire d'erreur PHP interne
        return true;
    }

    public static function register() {
        // Enregistrer les fonctions de gestion des erreurs et des exceptions
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
    }
}

// Enregistrer le gestionnaire d'erreurs
ErrorHandler::register();

