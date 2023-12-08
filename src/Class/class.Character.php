<?php // path: src/Class/class.Character.php

require __DIR__ . '/Interface/iface.CharacterPrototypeInterface.php';

/**
 * Class Character
 * 
 * This class is the Character class.
 * Implements the interface CharacterPrototypeInterface.
 * 
 */
class Character implements CharacterPrototype
{
    private $id;
    private $name;
    private $description;
    private $image;

    public function __construct($id = null, $name = null, $description = null, $image = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->image = $image ?? "placeholder.png";
    }

    /**
     * Function to clone a Character
     * Used to optimize the token usage
     *
     * @return CharacterPrototype
     */
    public function clone(): CharacterPrototype
    {
        $characterClone = new Character();
        $characterClone->setId($this->id);
        $characterClone->setName($this->name);
        $characterClone->setDescription($this->description);
        $characterClone->setImage($this->image);
        return $characterClone;
    }

    // Getter for the Character ID
    public function getId()
    {
        return $this->id;
    }

    // Setter for the Character ID
    public function setId($id)
    {
        $this->id = $id;
    }

    // Getter for the Character name
    public function getName()
    {
        return $this->name;
    }

    // Setter for the Character name
    public function setName($name)
    {
        $this->name = $name;
    }

    // Getter for the Character description
    public function getDescription()
    {
        return $this->description;
    }

    // Setter for the Character description
    public function setDescription($description)
    {
        $this->description = $description;
    }

    // Getter for the Character image
    public function getImage()
    {
        return $this->image;
    }

    // Setter for the Character image
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * Function to convert a map to a Character
     *
     * @param array $map
     * 
     * @return Character
     */
    public static function fromMap($map): Character
    {
        $character = new Character();
        $character->setId($map['id'] ?? null);
        $character->setName($map['name'] ?? null);
        $character->setDescription($map['description'] ?? null);
        $character->setImage($map['image'] ?? null);
        return $character;
    }

    /**
     * Function to convert a Character to a map
     *
     * @return array
     */
    public function toMap(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
        ];
    }
}