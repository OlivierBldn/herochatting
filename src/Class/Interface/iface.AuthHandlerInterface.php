<?php // path: src/Class/Interface/iface.AuthHandlerInterface.php

interface AuthHandlerInterface {
    public function setNext(AuthHandlerInterface $handler): AuthHandlerInterface;
    public function handle($request);
}