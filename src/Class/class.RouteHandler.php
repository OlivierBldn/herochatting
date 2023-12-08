<?php // path: src/Class/class.RouteHandler.php

require __DIR__ . '/Middleware/mdw.AuthHandlerMiddleware.php';

/**
 * Class RouteHandler
 * 
 * This class is the route handler.
 * It is used to route the requests to the right controller and method.
 * 
 */
class RouteHandler
{
    private $authMiddleware;

    public function __construct()
    {
        $this->authMiddleware = new AuthHandlerMiddleware();
    }

    /**
     * Function to route the request to the right controller and method
     *
     * @param string $uri
     * @param array $routes
     * @param string $requestMethod
     * @return void
     */
    function routeRequest($uri, $routes, $requestMethod)
    {
        // Validation de la méthode de requête HTTP
        if (!in_array($requestMethod, ['GET', 'POST', 'PUT', 'DELETE'])) {
            http_response_code(405); // Méthode non autorisée
            echo json_encode(['message' => 'Méthode de requête non autorisée']);

            return;
        }

        // Loop through the routes and find a match for the requested URI
        foreach ($routes as $pattern => $route) {

            // If the route pattern matches the requested URI create the controller and call the method
            if (preg_match($pattern, $uri, $matches)) {
                $className = $route['class'];
                $controllerName = $route['controller'];
                $methodName = $route['methods'][$requestMethod];

                // If the route is protected by the auth middleware, call the middleware
                if (!in_array($uri, __UNPROTECTED_ROUTES__)) {
                    $response = $this->authMiddleware->handle($requestMethod);
                    if ($response === null) {
                        return;
                    }
                }

                // If the class does not exist return a 404 response
                if (!class_exists($className)) {
                    http_response_code(404);
                    echo json_encode(['message' => 'Classe non trouvée']);
                    return;
                }
                $controller = new $controllerName();

                // If the method does not exist return a 404 response
                if (!method_exists($controller, $methodName)) {
                    http_response_code(404);
                    echo json_encode(['message' => 'Méthode non trouvée']);
                    return;
                }

                // Get the URI segments
                $uriSegments = explode('/', $uri);
                
                // Remove empty segments
                $uriSegments = array_filter($uriSegments);

                // Delete the first segment (the base URI)
                array_shift($uriSegments);

                // The last segment is the entity ID
                $entityId = (int) end($uriSegments);

                // Call the method with the entity ID
                $controller->$methodName($requestMethod, $entityId);

                return;
            }
        }

        http_response_code(404);
        echo json_encode(['message' => 'Route non trouvée']);
    }
}