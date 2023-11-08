<?php // path: src/Class/Universe.php

class Universe
{
    private $id;
    private $name;
    private $description;
    private $image;
    private $userId;

    public function __construct($id = null, $name = null, $description = null, $image = null, $userId = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->image = $image;
        $this->userId = $userId;
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

    // Getter pour l'ID de l'utilisateur associé à l'univers (clé étrangère)
    public function getUserId()
    {
        return $this->userId;
    }

    // Setter pour l'ID de l'utilisateur associé à l'univers (clé étrangère)
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    // Ajoutez ici d'autres méthodes spécifiques à la classe Universe, si nécessaire.

    public static function fromMap($map): Universe
    {
        $universe = new Universe();
        $universe->setId($map['id']);
        $universe->setName($map['name']);
        $universe->setDescription($map['description']);
        $universe->setImage($map['image']);
        $universe->setUserId($map['user_id']); // Assurez-vous que le nom de la colonne est correct
        return $universe;
    }

    public function toMap(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
            'user_id' => $this->userId // Assurez-vous que le nom de la colonne est correct
        ];
    }
}