<?php // path: src/Class/class.Autoloader.php

class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($className) {
            $directories = [
                __DIR__ . '/',
                __DIR__ . '/../Controller/',
            ];

            $classFile = (strpos($className, 'Controller') !== false ? 'ctrl.' : 'class.') . $className . '.php';

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

Autoloader::register();