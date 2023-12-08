<?php // path: src/Class/Interface/iface.UniversePrototypeInterface.php

/**
 * UniversePrototype
 * Interface for the UniversePrototype
 * Defines the methods that must be implemented by the UniversePrototype
 * The UniversePrototype is used to clone a Universe object
 * Allows to create a Universe object without making new requests to external APIs
 */
interface UniversePrototype
{
    /**
     * Declare the function to clone the UniversePrototype
     *
     * @return UniversePrototype
     */
    public function clone(): UniversePrototype;

    /**
     * Declare the function to set the name of the UniversePrototype
     *
     * @param string $name
     * @return void
     */
    public function setName($name);

    /**
     * Declare the function to set the image of the UniversePrototype
     *
     * @param string $image
     * @return void
     */
    public function setImage($image);

    /**
     * Declare the function to set the description of the UniversePrototype
     *
     * @param string $description
     * @return void
     */
    public function setDescription($description);

    /**
     * Declare the function to create a Universe object from the UniversePrototype
     * 
     * @return array
     */
    public function toMap(): array;
}