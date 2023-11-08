<?php // path: src/Class/interface/iface.DBConnectorInterface.php

interface DBConnectorInterface
{
    public function select($query, $params = []): array;
    public function execute($query, $params = []): bool;
    public function lastInsertRowID(): int;
}