<?php // path: src/Class/Interface/iface.AuthHandlerInterface.php

/**
 * AuthHandlerInterface
 * Interface for the authentication handler
 * Defines the methods that must be implemented by the authentication handler
 */
interface AuthHandlerInterface {

    /**
     * Function to set the next handler in the chain
     *
     * @param AuthHandlerInterface $handler
     * @return AuthHandlerInterface
     */
    public function setNext(AuthHandlerInterface $handler): AuthHandlerInterface;

    /**
     * Function to handle the request submitted to the handler
     *
     * @param Request $request
     * @param string $entityType
     */
    public function handle($request, $entityType = null);
}