<?php // path: src/Class/class.Character.php

require __DIR__ . '/Interface/iface.CharacterPrototypeInterface.php';

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

    public function clone(): CharacterPrototype
    {
        $characterClone = new Character();
        $characterClone->setId($this->id);
        $characterClone->setName($this->name);
        $characterClone->setDescription($this->description);
        $characterClone->setImage($this->image);
        return $characterClone;
    }

    // Getter pour l'ID du personnage
    public function getId()
    {
        return $this->id;
    }

    // Setter pour l'ID du personnage
    public function setId($id)
    {
        $this->id = $id;
    }

    // Getter pour le nom du personnage
    public function getName()
    {
        return $this->name;
    }

    // Setter pour le nom du personnage
    public function setName($name)
    {
        $this->name = $name;
    }

    // Getter pour la description du personnage
    public function getDescription()
    {
        return $this->description;
    }

    // Setter pour la description du personnage
    public function setDescription($description)
    {
        $this->description = $description;
    }

    // Getter pour l'image du personnage
    public function getImage()
    {
        return $this->image;
    }

    // Setter pour l'image du personnage
    public function setImage($image)
    {
        $this->image = $image;
    }

    public static function fromMap($map): Character
    {
        $character = new Character();
        $character->setId($map['id'] ?? null);
        $character->setName($map['name'] ?? null);
        $character->setDescription($map['description'] ?? null);
        $character->setImage($map['image'] ?? null);
        return $character;
    }

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