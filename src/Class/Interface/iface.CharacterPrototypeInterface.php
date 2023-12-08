<?php // path: src/Class/Interface/iface.CharacterPrototypeInterface.php

/**
 * CharacterPrototype
 * Interface for the CharacterPrototype
 * Defines the methods that must be implemented by the CharacterPrototype
 * The CharacterPrototype is used to clone a Character object
 * Allows to create a Character object without making new requests to external APIs
 */
interface CharacterPrototype
{
    /**
     * Declare the function to clone the CharacterPrototype
     *
     * @return CharacterPrototype
     */
    public function clone(): CharacterPrototype;

    /**
     * Declare the function to set the name of the CharacterPrototype
     *
     * @param string $name
     * @return void
     */
    public function setName($name): void;

    /**
     * Declare the function to set the image of the CharacterPrototype
     *
     * @param string $image
     * @return void
     */
    public function setImage($image): void;

    /**
     * Declare the function to set the description of the CharacterPrototype
     *
     * @param string $description
     * @return void
     */
    public function setDescription($description): void;

    /**
     * Declare the function to create a Character object from the CharacterPrototype
     * 
     * @return array
     */
    public function toMap(): array;
}