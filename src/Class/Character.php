<?php // path: src/Class/Character.php

class Character
{
    private $id;
    private $name;
    private $description;
    private $image;
    private $universeId;

    public function __construct($id = null, $name = null, $description = null, $image = null, $universeId = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->image = $image;
        $this->universeId = $universeId;
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

    // Getter pour l'ID de l'univers associé au personnage (clé étrangère)
    public function getUniverseId()
    {
        return $this->universeId;
    }

    // Setter pour l'ID de l'univers associé au personnage (clé étrangère)
    public function setUniverseId($universeId)
    {
        $this->universeId = $universeId;
    }

    public static function fromMap($map): Character
    {
        $character = new Character();
        $character->setId($map['id'] ?? null);
        $character->setName($map['name'] ?? null);
        $character->setDescription($map['description'] ?? null);
        $character->setImage($map['image'] ?? null);
        $character->setUniverseId($map['id_universe'] ?? null);
        return $character;
    }

    public function toMap(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
            'id_universe' => $this->universeId
        ];
    }
}