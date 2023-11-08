<?php // path: src/Class/class.RouteHandler.php

class RouteHandler
{
    // Fonction de routage
    function routeRequest($uri, $routes, $requestMethod)
    {
        // Validation de la méthode de requête HTTP
        if (!in_array($requestMethod, ['GET', 'POST', 'PUT', 'DELETE'])) {
            http_response_code(405); // Méthode non autorisée
            echo json_encode(['message' => 'Méthode de requête non autorisée']);

            return;
        }

        // Parcourir les routes pour trouver une correspondance
        foreach ($routes as $pattern => $route) {

            if (preg_match($pattern, $uri, $matches)) {
                $className = $route['class'];
                $controllerName = $route['controller'];
                $methodName = $route['methods'][$requestMethod];

                if (!class_exists($className)) {
                    // Classe non trouvée
                    http_response_code(404);
                    echo json_encode(['message' => 'Classe non trouvée']);
                    return;
                }
                $controller = new $controllerName();

                if (!method_exists($controller, $methodName)) {
                    // Méthode non trouvée
                    http_response_code(404);
                    echo json_encode(['message' => 'Méthode non trouvée']);
                    return;
                }

                // Récupation les segments de l'URI
                $uriSegments = explode('/', $uri);
                
                // Suppression des segments vides
                $uriSegments = array_filter($uriSegments);

                // Suppression du premier segment (nom de la classe)
                array_shift($uriSegments);

                // L'ID est le dernier segment de l'URI
                $entityId = (int) end($uriSegments);

                // Appel de la méthode du contrôleur avec l'ID
                $controller->$methodName($requestMethod, $entityId);

                return;
            }
        }

        // Si aucune correspondance n'a été trouvée, renvoyer une réponse 404
        http_response_code(404);
        echo json_encode(['message' => 'Route non trouvée']);
    }
}