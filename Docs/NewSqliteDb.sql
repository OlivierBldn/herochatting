-- Création de la table user
CREATE TABLE user (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firstName TEXT,
    lastName TEXT,
    username TEXT UNIQUE,
    password TEXT,
    email TEXT UNIQUE
);

-- Création de la table universe
CREATE TABLE universe (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    description TEXT,
    image TEXT
);

-- Création de la table character
CREATE TABLE character (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    description TEXT,
    image TEXT
);

-- Création de la table message
CREATE TABLE message (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    content TEXT,
    createdAt DATETIME,
    is_human BOOLEAN
);

-- Création de la table chat
CREATE TABLE chat (
    id INTEGER PRIMARY KEY AUTOINCREMENT
);

-- Création de la table user_universe
CREATE TABLE user_universe (
    userId INT,
    universeId INT,
    FOREIGN KEY (userId) REFERENCES user(id),
    FOREIGN KEY (universeId) REFERENCES universe(id)
);

-- Création de la table user_chat
CREATE TABLE user_chat (
    userId INT,
    chatId INT,
    FOREIGN KEY (userId) REFERENCES user(id),
    FOREIGN KEY (chatId) REFERENCES chat(id)
);

-- Création de la table universe_character
CREATE TABLE universe_character (
    universeId INT,
    characterId INT,
    FOREIGN KEY (universeId) REFERENCES universe(id),
    FOREIGN KEY (characterId) REFERENCES character(id)
);

-- Création de la table chat_message
CREATE TABLE chat_message (
    chatId INT,
    messageId INT,
    FOREIGN KEY (chatId) REFERENCES chat(id),
    FOREIGN KEY (messageId) REFERENCES message(id)
);

-- Création de la table character_chat
CREATE TABLE character_chat (
  characterId int NOT NULL,
  chatId int NOT NULL,
  FOREIGN KEY (characterId) REFERENCES character (id),
  FOREIGN KEY (chatId) REFERENCES chat (id)
);