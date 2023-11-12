<?php // path: src/Class/class.UniversePrototype.php

require __DIR__ . '/Interface/iface.UniversePrototypeInterface.php';

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

    public function clone(): UniversePrototype {
        $universeClone = new Universe();
        $universeClone->setId($this->id);
        $universeClone->setName($this->name);
        $universeClone->setDescription($this->description);
        $universeClone->setImage($this->image);
        return $universeClone;
    }

    // Getter pour l'ID de l'univers
    public function getId()
    {
        return $this->id;
    }

    // Setter pour l'ID de l'univers
    public function setId($id)
    {
        $this->id = $id;
    }

    // Getter pour le nom de l'univers
    public function getName()
    {
        return $this->name;
    }

    // Setter pour le nom de l'univers
    public function setName($name)
    {
        $this->name = $name;
    }

    // Getter pour la description de l'univers
    public function getDescription()
    {
        return $this->description;
    }

    // Setter pour la description de l'univers
    public function setDescription($description)
    {
        $this->description = $description;
    }

    // Getter pour l'image de l'univers
    public function getImage()
    {
        return $this->image;
    }

    // Setter pour l'image de l'univers
    public function setImage($image)
    {
        $this->image = $image;
    }

    public static function fromMap($map): Universe {
        $universe = new Universe();
        $universe->setId($map['id'] ?? null);
        $universe->setName($map['name'] ?? null);
        $universe->setDescription($map['description'] ?? null);
        $universe->setImage($map['image'] ?? null);
        return $universe;
    }

    public function toMap(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image
        ];
    }
}