<?php // path: src/Middleware/mdw.AuthHandlerMiddleware.php

require_once __DIR__ . '/../Interface/iface.AuthHandlerInterface.php';
require_once __DIR__ . '/../class.JWTFactory.php';

class AuthHandlerMiddleware implements AuthHandlerInterface {
    private $nextHandler;

    public function setNext(AuthHandlerInterface $handler): AuthHandlerInterface {
        $this->nextHandler = $handler;
        return $handler;
    }

    public function handle($request) {
        $headers = apache_request_headers();

        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);
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