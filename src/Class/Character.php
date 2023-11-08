<?php // path: src/Class/Character.php

class Character
{
    private $id;
    private $name;
    private $description;
    private $image;
    private $id_universe;
    
    public function __construct($id = null, $name = null, $description = null, $image = null, $id_universe = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->image = $image;
        $this->id_universe = $id_universe;
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

     // Getter pour l'ID de l'univers lié au personnage
    public function getIdUniverse()
    {
        return $this->id_universe;
    }

    // Setter pour l'ID de l'univers lié au personnage
    public function setIdUniverse($id_universe)
    {
        $this->id_universe = $id_universe;
    }


    public static function fromMap($map): Character
    {
        $character = new Character();
        $character->setId($map['id']);
        $character->setName($map['name']);
        $character->setDescription($map['description']);
        $character->setImage($map['image']);
        $character->setIdUniverse($map['id_universe']);
        
        return $character;
    }



    public function toMap(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
            'id_universe' => $this->id_universe
        ];
    }
}