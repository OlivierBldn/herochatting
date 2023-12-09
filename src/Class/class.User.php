<?php // path: src/Class/class.User.php

/**
 * Class User
 * 
 * This class is the User class.
 * 
 */
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

    // Getter for the User ID
    public function getId()
    {
        return $this->id;
    }

    // Setter for the User ID
    public function setId($id)
    {
        $this->id = $id;
    }

    // Getter for the User e-mail
    public function getEmail()
    {
        return $this->email;
    }

    // Setter for the User e-mail
    public function setEmail($email)
    {
        $this->email = $email;
    }

    // Getter for the User password
    public function getPassword()
    {
        return $this->password;
    }

    // Setter for the User password
    public function setPassword($password)
    {
        // Hash password before storing it
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $this->password = $hashedPassword;
    }

    // Getter for the User username
    public function getUsername()
    {
        return $this->username;
    }

    // Setter for the User username
    public function setUsername($username)
    {
        $this->username = $username;
    }

    // Getter for the User first name
    public function getFirstName()
    {
        return $this->firstName;
    }

    // Setter for the User first name
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    // Getter for the User last name
    public function getLastName()
    {
        return $this->lastName;
    }

    // Setter for the User last name
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * Function to check if the password is valid
     * 
     * @param string $password
     * 
     * @return bool
     */
    public function validatePassword($password)
    {
        return password_verify($password, $this->password);
    }

    /**
     * Function to convert a map to a User
     *
     * @param array $map
     * 
     * @return User
     */
    public static function fromMap($map): ?User
    {
        if (!$map) {
            return null;
        }
    
        $user = new User();
        $user->setId($map['id'] ?? null);
        $user->setEmail($map['email'] ?? null);
        $user->setUsername($map['username'] ?? null);
        $user->setFirstName($map['firstName'] ?? null);
        $user->setLastName($map['lastName'] ?? null);
        $user->password = $map['password'] ?? null;
    
        return $user;
    }

    /**
     * Function to convert a User to a map
     *
     * @return array
     */
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