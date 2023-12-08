<?php // path: src/Class/class.UniversePrototype.php

require __DIR__ . '/Interface/iface.UniversePrototypeInterface.php';

/**
 * Class Universe
 * 
 * This class is the Universe class.
 * Implements the interface UniversePrototypeInterface.
 * 
 */
class Universe implements UniversePrototype {
    private $id;
    private $name;
    private $description;
    private $image;

    public function __construct($id = null, $name = null, $description = null, $image = null) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->image = $image ?? "placeholder.png";
    }

    /**
     * Function to clone a Universe
     * Used to optimize the token usage
     *
     * @return UniversePrototype
     */
    public function clone(): UniversePrototype
    {
        $universeClone = new Universe();
        $universeClone->setId($this->id);
        $universeClone->setName($this->name);
        $universeClone->setDescription($this->description);
        $universeClone->setImage($this->image);
        return $universeClone;
    }

    // Getter for the Universe ID
    public function getId()
    {
        return $this->id;
    }

    // Setter for the Universe ID
    public function setId($id)
    {
        $this->id = $id;
    }

    // Getter for the Universe name
    public function getName()
    {
        return $this->name;
    }

    // Setter for the Universe name
    public function setName($name)
    {
        $this->name = $name;
    }

    // Getter for the Universe description
    public function getDescription()
    {
        return $this->description;
    }

    // Setter for the Universe description
    public function setDescription($description)
    {
        $this->description = $description;
    }

    // Getter for the Universe image
    public function getImage()
    {
        return $this->image;
    }

    // Setter for the Universe image
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * Function to convert a map to a Universe
     *
     * @return array
     */
    public static function fromMap($map): Universe {
        $universe = new Universe();
        $universe->setId($map['id'] ?? null);
        $universe->setName($map['name'] ?? null);
        $universe->setDescription($map['description'] ?? null);
        $universe->setImage($map['image'] ?? null);
        return $universe;
    }

    /**
     * Function to convert a Universe to a map
     *
     * @return array
     */
    public function toMap(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image
        ];
    }
}