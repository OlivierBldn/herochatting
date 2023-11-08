<?php // path: src/Class/iface.dbconnector.php

interface DBConnectorInterface
{
    public function select($query, $params = []) : array;
    public function execute($query, $params = []) : bool;
}