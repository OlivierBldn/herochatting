<?php // path: src/Middleware/mdw.AuthHandlerMiddleware.php

require_once __DIR__ . '/../Interface/iface.AuthHandlerInterface.php';
require_once __DIR__ . '/../class.JWTFactory.php';

/**
 * AuthHandlerMiddleware
 * Class to handle the authentication
 * Implements the AuthHandlerInterface
 * Used to check if the user is authenticated
 * Used to check if the user has the right to access the requested resource
 */
class AuthHandlerMiddleware implements AuthHandlerInterface {
    private $nextHandler;

    /**
     * Function to set the next handler in the chain
     *
     * @param AuthHandlerInterface $handler
     * @return AuthHandlerInterface
     */
    public function setNext(AuthHandlerInterface $handler): AuthHandlerInterface {
        $this->nextHandler = $handler;
        return $handler;
    }

    /**
     * Function to handle the request submitted to the handler
     *
     * @param Request $request
     * @return mixed
     */
    public function handle($request) {
        $headers = apache_request_headers();

        // Check if the request contains an Authorization header
        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);

            // Check if the token is valid using the JWTFactory
            $isValid = JWTFactory::validateToken($token);
            if ($isValid) {
                return $this->nextHandler ? $this->nextHandler->handle($request) : true;

                // To use instead in case middlewares chain setted up
                // return $this->nextHandler ? $this->nextHandler->handle($request) : null;
            }
        }

        http_response_code(401);
        echo json_encode(['error' => 'Accès non autorisé']);
        return null;
    }
}