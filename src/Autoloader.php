<?php // path: src/Autoloader.php

class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($className) {
            // Définissez un tableau de répertoires où chercher les classes
            $directories = [
                __DIR__ . '/Class/', // Répertoire des classes
                __DIR__ . '/Controller/', // Répertoire des contrôleurs
                // Ajoutez d'autres répertoires au besoin
            ];

            // Convertissez le nom de classe en un nom de fichier en remplaçant les \ par des /
            $classFile = str_replace('\\', '/', $className) . '.php';

            // Parcourez les répertoires et cherchez la classe
            foreach ($directories as $directory) {
                $file = $directory . $classFile;
                if (file_exists($file)) {
                    require $file;
                    return;
                }
            }
        });
    }
}