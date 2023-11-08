<?php // path: index.php

// Appel du fichier de l'autoloader
require __DIR__ . '/src/Class/class.Autoloader.php';
require __DIR__ . '/config/api_config.php';
require __DIR__ . '/src/Class/class.RouteHandler.php';
$userRoutes = require __DIR__ . '/config/routes/user-routes.php';
$universeRoutes = require __DIR__ . '/config/routes/universe-routes.php';
$characterRoutes = require __DIR__ . '/config/routes/character-routes.php';

// Enregistrement de l'autoloader
Autoloader::register();

// Récupérer la méthode de requête et l'URI de la demande
$requestMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$basePath = '/'.__WEBSITE_URL__;
$uri = str_replace($basePath, '', $uri);

// Chargement de configuration de routage
$routes = array_merge($userRoutes, $universeRoutes, $characterRoutes);

// Création d'une instance de RouterController
$routeHandler = new RouteHandler();

// Appel de la fonction de routage
$routeHandler->routeRequest($uri, $routes, $requestMethod);