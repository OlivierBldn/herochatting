<?php // path: src/Class/class.Message.php

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

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getContent() {
        return $this->content;
    }

    public function setContent($content) {
        $this->content = $content;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    public function isHuman() {
        return $this->isHuman;
    }

    public function setIsHuman($isHuman) {
        $this->isHuman = $isHuman;
    }

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