<?php // path: src/Class/Interface/iface.UniversePrototypeInterface.php

interface UniversePrototype
{
    public function clone(): UniversePrototype;
    public function setName($name);
    public function setImage($image);
    public function setDescription($description);
    public function setUserId($userId);
    public function toMap(): array;
}