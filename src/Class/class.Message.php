<?php // path: src/Class/class.Message.php

/**
 * Class Message
 * 
 * This class is the message class.
 * It represents a message in the chat.
 * 
 */
class Message
{
    private $id;
    private $content;
    private $createdAt;
    private $isHuman;

    public function __construct($id = null, $content = null, $createdAt = null, $isHuman = null)
    {
        $this->id = $id;
        $this->content = $content;
        $this->createdAt = $createdAt;
        $this->isHuman = $isHuman;
    }

    // Getter for the message ID
    public function getId() {
        return $this->id;
    }

    // Setter for the message ID
    public function setId($id) {
        $this->id = $id;
    }

    // Getter for the message content
    public function getContent() {
        return $this->content;
    }

    // Setter for the message content
    public function setContent($content) {
        $this->content = $content;
    }

    // Getter for the message creation date
    public function getCreatedAt() {
        return $this->createdAt;
    }

    // Setter for the message creation date
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    /**
     * Getter for the message isHuman
     * Used to know if the message is from a user or from the api
     * 
     * @return bool
     */
    public function isHuman() {
        return $this->isHuman;
    }

    /**
     * Setter for the message isHuman
     * Used to know if the message is from a user or from the api
     * 
     * @param bool $isHuman
     * 
     * @return void
     */
    public function setIsHuman($isHuman) {
        $this->isHuman = $isHuman;
    }

    /**
     * Function to convert a map to a message
     * 
     * @param array $map
     * 
     * @return Message
     */
    public static function fromMap($map): Message
    {
        $isHuman = null;
        if (isset($map['is_human'])) {
            $isHumanValue = $map['is_human'];
            $isHuman = $isHumanValue == 1 ? true : ($isHumanValue == 0 ? false : null);
        }
    
        return new self(
            $map['id'] ?? null,
            $map['content'] ?? null,
            $map['createdAt'] ?? null,
            $isHuman
        );
    }

    /**
     * Function to convert the message to a map
     * 
     * @return array
     */
    public function toMap(): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'createdAt' => $this->createdAt,
            'isHuman' => $this->isHuman
        ];
    }
}