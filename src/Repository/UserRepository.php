<?php // path: src/Repository/UserRepository.php

class UserRepository
{
    public function create(User $user)
    {
        // Insertion dans la base de données
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO user (email, password, username, firstName, lastName) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);

        if (!$stmt) {
            // Gérer l'erreur de préparation de la requête
            throw new Exception("Erreur de préparation de la requête SQL : " . $db->error);
        }

        // // Liaison des paramètres
        $email = $user->getEmail();
        $password = $user->getPassword();
        $username = $user->getUsername();
        $firstName = $user->getFirstName();
        $lastName = $user->getLastName();

        $stmt->bind_param("sssss", $email, $password, $username, $firstName, $lastName);

        if (!$stmt->execute()) {
            // Gérer l'erreur d'exécution de la requête
            throw new Exception("Erreur lors de l'exécution de la requête SQL : " . $stmt->error);
        }

        // Récupération de l'ID généré pour le nouvel utilisateur
        $id = $db->insert_id;

        // Création et retour de l'instance User correspondante
        return new User($id, $email, $password, $username, $firstName, $lastName);
    }

    public function getById($id)
    {
        // Récupération de la connexion à la base de données
        $db = Database::getInstance()->getConnection();

        // Préparation de la requête SQL
        $sql = "SELECT * FROM user WHERE id = ?";
        $stmt = $db->prepare($sql);

        if (!$stmt) {
            // Gérer l'erreur de préparation de la requête
            throw new Exception("Erreur de préparation de la requête SQL : " . $db->error);
        }

        // Liaison des paramètres
        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            // Gérer l'erreur d'exécution de la requête
            throw new Exception("Erreur lors de l'exécution de la requête SQL : " . $stmt->error);
        }

        // Récupération du résultat de la requête
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Aucun utilisateur trouvé avec cet ID
            return null;
        }

        // Récupération des données de l'utilisateur depuis le résultat
        $row = $result->fetch_assoc();

        // Création et retour de l'instance User correspondante
        return new User(
            $row['id'],
            $row['email'],
            $row['password'],
            $row['username'],
            $row['firstName'],
            $row['lastName']
        );
    }

    public function update($user)
    {
        // Mettre à jour les données de l'utilisateur dans la base de données
        $db = Database::getInstance()->getConnection();
        $sql = "UPDATE user
                SET email = ?, password = ?, username = ?, firstName = ?, lastName = ?
                WHERE id = ?";
        $stmt = $db->prepare($sql);

        if (!$stmt) {
            throw new Exception("Erreur de préparation de la requête SQL : " . $db->error);
        }


        // Stocker les valeurs dans des variables distinctes
        $email = $user->getEmail();
        $password = $user->getPassword();
        $username = $user->getUsername();
        $firstName = $user->getFirstName();
        $lastName = $user->getLastName();
        $id = $user->getId();

        $stmt->bind_param("sssssi", $email, $password, $username, $firstName, $lastName, $id);

        if (!$stmt->execute()) {
            throw new Exception("Erreur lors de l'exécution de la requête SQL : " . $stmt->error);
        }
    }

    public function delete($id)
    {
        // Supprimer l'utilisateur de la base de données par ID
        $db = Database::getInstance()->getConnection();
        $sql = "DELETE FROM user WHERE id = ?";
        $stmt = $db->prepare($sql);
    
        if (!$stmt) {
            throw new Exception("Erreur de préparation de la requête SQL : " . $db->error);
        }
    
        $stmt->bind_param("i", $id);
    
        if (!$stmt->execute()) {
            throw new Exception("Erreur lors de l'exécution de la requête SQL : " . $stmt->error);
        }
    }

    public function getAll()
    {
        // Récupérer tous les utilisateurs depuis la base de données
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM user";
        $stmt = $db->prepare($sql);

        if (!$stmt) {
            throw new Exception("Erreur de préparation de la requête SQL : " . $db->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Erreur lors de l'exécution de la requête SQL : " . $stmt->error);
        }

        // Récupération du résultat de la requête
        $result = $stmt->get_result();

        // Création d'un tableau vide pour stocker les utilisateurs
        $users = [];

        // Récupération des données de chaque utilisateur depuis le résultat
        while ($row = $result->fetch_assoc()) {
            $users[] = new User(
                $row['id'],
                $row['email'],
                $row['password'],
                $row['username'],
                $row['firstName'],
                $row['lastName']
            );
        }

        // Retourner le tableau d'utilisateurs
        return $users;
    }
}