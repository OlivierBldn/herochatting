<?php // path: src/Class/class.Autoloader.php

/**
 * Class Autoloader
 * 
 * This class is the autoloader for the classes and controllers.
 * 
 */
class Autoloader
{
    /**
     * Function to register the autoloader
     *
     * @return void
     */
    public static function register()
    {
        // Register the autoloader for the classes and controllers
        spl_autoload_register(function ($className) {
            $directories = [
                __DIR__ . '/',
                __DIR__ . '/../Controller/',
            ];

            // Check if the class is a controller or a class
            $classFile = (strpos($className, 'Controller') !== false ? 'ctrl.' : 'class.') . $className . '.php';

            // Check if the class exists in the directories
            foreach ($directories as $directory) {
                $file = $directory . $classFile;

                // If the file exists, require it
                if (file_exists($file)) {
                    require $file;
                    return;
                }
            }
        });
    }
}

Autoloader::register();