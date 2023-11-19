<?php // path: src/Class/Builder/bldr.ChatBuilder.php

class ChatBuilder {
    private $chat;

    public function __construct() {
        $this->chat = new Chat();
    }

    // Définir l'ID du chat
    public function withId($id) {
        $this->chat->setId($id);
        return $this;
    }

    // Ajouter un participant au chat
    public function addParticipant($participant) {
        $this->chat->addParticipant($participant);
        return $this;
    }

    // Ajouter un message au chat
    public function addMessage($message) {
        $this->chat->addMessage($message);
        return $this;
    }

    // Ajouter une collection de messages au chat
    public function withMessages($messages) {
        foreach ($messages as $message) {
            $this->chat->addMessage($message);
        }
        return $this;
    }

    // Charger les messages depuis une base de données
    public function loadMessages($chatId) {
        $messages = Message::getMessagesByChatId($chatId);
        foreach ($messages as $message) {
            $this->chat->addMessage($message);
        }
        return $this;
    }

    // Construire et obtenir l'objet Chat
    public function build() {
        return $this->chat;
    }
}