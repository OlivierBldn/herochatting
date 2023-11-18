<?php // path: src/Class/class.User.php

class User
{
    private $id;
    private $email;
    private $password;
    private $username;
    private $firstName;
    private $lastName;
    
    public function __construct($id = null, $email = null, $password = null, $username = null, $firstName = null, $lastName = null)
    {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->username = $username;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    // Getter pour l'ID de l'utilisateur
    public function getId()
    {
        return $this->id;
    }

    // Setter pour l'ID de l'utilisateur
    public function setId($id)
    {
        $this->id = $id;
    }

    // Getter pour l'e-mail de l'utilisateur
    public function getEmail()
    {
        return $this->email;
    }

    // Setter pour l'e-mail de l'utilisateur
    public function setEmail($email)
    {
        $this->email = $email;
    }

    // Getter pour le mot de passe de l'utilisateur
    public function getPassword()
    {
        return $this->password;
    }

    // Setter pour le mot de passe de l'utilisateur
    public function setPassword($password)
    {
        // Hasher le mot de passe avant de le stocker
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $this->password = $hashedPassword;
    }

    // Getter pour le nom d'utilisateur de l'utilisateur
    public function getUsername()
    {
        return $this->username;
    }

    // Setter pour le nom d'utilisateur de l'utilisateur
    public function setUsername($username)
    {
        $this->username = $username;
    }

    // Getter pour le prénom de l'utilisateur
    public function getFirstName()
    {
        return $this->firstName;
    }

    // Setter pour le prénom de l'utilisateur
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    // Getter pour le nom de famille de l'utilisateur
    public function getLastName()
    {
        return $this->lastName;
    }

    // Setter pour le nom de famille de l'utilisateur
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function validatePassword($password)
    {
        // Vérification du mot de passe haché
        return password_verify($password, $this->password);
    }

    public static function fromMap($map): ?User
    {
        if (!$map) {
            return null; // Gestion d'une entrée vide ou invalide
        }
    
        $user = new User();
        $user->setId($map['id'] ?? null);
        $user->setEmail($map['email'] ?? null);
        // $user->setPassword($map['password'] ?? null);
        $user->setUsername($map['username'] ?? null);
        $user->setFirstName($map['firstName'] ?? null);
        $user->setLastName($map['lastName'] ?? null);
        $user->password = $map['password'] ?? null;
    
        return $user;
    }

    public function toMap(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'password' => $this->password,
            'username' => $this->username,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName
        ];
    }
}