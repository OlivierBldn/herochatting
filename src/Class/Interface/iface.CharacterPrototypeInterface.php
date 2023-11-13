<?php // path: src/Class/Interface/iface.CharacterPrototypeInterface.php

interface CharacterPrototype
{
    public function clone(): CharacterPrototype;
    public function setName($name);
    public function setImage($image);
    public function setDescription($description);
    public function toMap(): array;
}