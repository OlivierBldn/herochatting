<?php // path: src/Class/iface.dbconnector.php

interface DBConnectorInterface
{
    public function select($query) : array;
    public function execute($query) : bool;
}