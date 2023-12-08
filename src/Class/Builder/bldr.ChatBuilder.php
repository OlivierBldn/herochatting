<?php // path: src/Class/Builder/bldr.ChatBuilder.php

require_once __DIR__ . '/../../Repository/repo.MessageRepository.php';

/**
 * ChatBuilder
 * Class to build a Chat object
 * Builds a Chat object, gathering a User, a Character and a Message object or a collection of Message objects
 */
class ChatBuilder {
    private $chat;

    public function __construct() {
        $this->chat = new Chat();
    }

    /** 
     * Set the id of the chat
     * 
     * @param int $id
     * @return ChatBuilder
     */
    public function withId($id) {
        $this->chat->setId($id);
        return $this;
    }

    /** 
     * Add a participant to the chat
     * 
     * @param $participant
     * @return ChatBuilder
     */
    public function addParticipant($participant) {
        $this->chat->addParticipant($participant);
        return $this;
    }

    /** 
     * Add a message to the chat
     * 
     * @param Message $message
     * @return ChatBuilder
     */
    public function addMessage($message) {
        $this->chat->addMessage($message);
        return $this;
    }

    /**
     * Function to add a collection of messages to the chat
     *
     * @param Collection $messages
     * @return ChatBuilder
     */
    public function withMessages($messages) {
        foreach ($messages as $message) {
            $this->chat->addMessage($message);
        }
        return $this;
    }

    /**
     * Function to load the messages of a chat
     *
     * @param int $chatId
     * @return ChatBuilder
     */
    public function loadMessages($chatId) {
        $messageRepository = new MessageRepository();
        $messages = $messageRepository->getMessagesByChatId($chatId);

        foreach ($messages as $message) {
            $this->chat->addMessage($message);
        }

        return $this;
    }

    /**
     * Build the Chat object
     *
     * @return Chat
     */
    public function build() {
        return $this->chat;
    }
}