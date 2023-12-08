<?php // path: src/Class/class.Chat.php

/**
 * Class Chat
 * 
 * This class is the Chat class.
 * 
 */
class Chat {
    private $id;
    private $participants;
    private $messages;

    public function __construct() {
        $this->participants = [];
        $this->messages = [];
    }

    // Getter for the Chat ID
    public function getId() {
        return $this->id;
    }

    // Setter for the Chat ID
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    // Function to add a participant to a Chat
    public function addParticipant($participant) {
        $this->participants[] = $participant;
        return $this;
    }

    // Getter for all the participants of a Chat
    public function getParticipants() {
        return $this->participants;
    }

    // Function to add a message to a Chat
    public function addMessage($message) {
        $this->messages[] = $message;
        return $this;
    }

    // Getter for all the messages of a Chat
    public function getMessages() {
        return $this->messages;
    }

    // Function to convert a Chat to a map
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
    }
}
