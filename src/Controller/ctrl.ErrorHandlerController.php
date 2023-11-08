<?php // path: src/Controller/ctrl.ErrorHandlerController.php

class CustomErrorHandler
{
    public static function handleException($exception)
    {
        // Log the exception
        error_log("Exception: " . $exception->getMessage());

        // You can customize how you handle different types of exceptions here
        if ($exception instanceof ApiException) {
            // Handle API-specific exceptions
            self::respondWithError($exception->getMessage(), $exception->getCode());
        } elseif ($exception instanceof DatabaseException) {
            // Handle database-related exceptions
            self::respondWithError("Une erreur de base de données s'est produite.", 500);
        } else {
            // Handle other unanticipated exceptions
            self::respondWithError("Une erreur interne s'est produite.", 500);
        }
    }

    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        // Log the error
        error_log("Erreur : [$errno] $errstr dans le fichier $errfile à la ligne $errline");

        // You can customize how you handle different types of errors here
        self::respondWithError("Une erreur s'est produite.", 500);
    }

    public static function respondWithError($message, $statusCode)
    {
        http_response_code($statusCode);
        echo json_encode(array('error' => $message));
        exit();
    }
}