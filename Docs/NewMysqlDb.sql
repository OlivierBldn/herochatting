CREATE TABLE `user` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstName VARCHAR(255),
    lastName VARCHAR(255),
    username VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    email VARCHAR(255) UNIQUE
);

CREATE TABLE `universe` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    description TEXT,
    image VARCHAR(255)
);

CREATE TABLE `character` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    description TEXT,
    image VARCHAR(255)
);

CREATE TABLE `message` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description TEXT,
    date DATETIME,
    is_human BOOLEAN
);

CREATE TABLE `chat` (
    id INT AUTO_INCREMENT PRIMARY KEY
);

CREATE TABLE `user_universe` (
    userId INT,
    universeId INT,
    FOREIGN KEY (userId) REFERENCES user(id),
    FOREIGN KEY (universeId) REFERENCES universe(id)
);

CREATE TABLE `user_chat` (
    userId INT,
    chatId INT,
    FOREIGN KEY (userId) REFERENCES user(id),
    FOREIGN KEY (chatId) REFERENCES chat(id)
);

CREATE TABLE `universe_character` (
    universeId INT,
    characterId INT,
    FOREIGN KEY (universeId) REFERENCES universe(id),
    FOREIGN KEY (characterId) REFERENCES `character`(id)
);

CREATE TABLE `chat_message` (
    chatId INT,
    messageId INT,
    FOREIGN KEY (chatId) REFERENCES chat(id),
    FOREIGN KEY (messageId) REFERENCES `message`(id)
);