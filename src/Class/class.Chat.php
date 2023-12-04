<?php // path: src/Class/class.Chat.php

class Chat {
    private $id;
    private $participants;
    private $messages;

    public function __construct() {
        $this->participants = [];
        $this->messages = [];
    }

    // Getter et Setter pour l'ID
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    // Ajouter un participant
    public function addParticipant($participant) {
        $this->participants[] = $participant;
        return $this;
    }

    // Obtenir tous les participants
    public function getParticipants() {
        return $this->participants;
    }

    // Ajouter un message
    public function addMessage($message) {
        $this->messages[] = $message;
        return $this;
    }

    // Obtenir tous les messages
    public function getMessages() {
        return $this->messages;
    }

    // Méthode pour obtenir une représentation sous forme de tableau
    public function toMap() {

        $participants = array_map(function($participant) {
            return $participant->toMap();
        }, $this->participants);

        $messages = array_map(function($message) {
            return $message->toMap();
        }, $this->messages);

        return [
            'id' => $this->id,
            'participants' => $participants,
            'messages' => $messages
        ];
        // return [
        //     'id' => $this->id,
        //     'participants' => array_map(function($participant) {
        //         return $participant->toMap();
        //     }, $this->participants),
        //     'messages' => array_map(function($message) {
        //         return $message->toMap();
        //     }, $this->messages)
        // ];
    }
}
