-- Création de la table user
CREATE TABLE "user" (
    id SERIAL PRIMARY KEY,
    "firstName" VARCHAR(255),
    "lastName" VARCHAR(255),
    username VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    email VARCHAR(255) UNIQUE
);

-- Création de la table universe
CREATE TABLE "universe" (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255),
    description TEXT,
    image VARCHAR(255)
);

-- Création de la table character
CREATE TABLE "character" (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255),
    description TEXT,
    image VARCHAR(255)
);

-- Création de la table message
CREATE TABLE "message" (
    id SERIAL PRIMARY KEY,
    content TEXT,
    createdAt TIMESTAMP,
    is_human BOOLEAN
);

-- Création de la table chat
CREATE TABLE "chat" (
    id SERIAL PRIMARY KEY
);

-- Création de la table user_universe
CREATE TABLE "user_universe" (
    "userId" INT,
    "universeId" INT,
    FOREIGN KEY ("userId") REFERENCES "user"(id),
    FOREIGN KEY ("universeId") REFERENCES "universe"(id)
);

-- Création de la table user_chat
CREATE TABLE "user_chat" (
    "userId" INT,
    "chatId" INT,
    FOREIGN KEY ("userId") REFERENCES "user"(id),
    FOREIGN KEY ("chatId") REFERENCES "chat"(id)
);

-- Création de la table universe_character
CREATE TABLE "universe_character" (
    "universeId" INT,
    "characterId" INT,
    FOREIGN KEY ("universeId") REFERENCES "universe"(id),
    FOREIGN KEY ("characterId") REFERENCES "character"(id)
);

-- Création de la table chat_message
CREATE TABLE "chat_message" (
    "chatId" INT,
    "messageId" INT,
    FOREIGN KEY ("chatId") REFERENCES "chat"(id),
    FOREIGN KEY ("messageId") REFERENCES "message"(id)
);

-- Création de la table character_chat
CREATE TABLE "character_chat" (
  "characterId" int NOT NULL,
  "chatId" int NOT NULL,
  FOREIGN KEY ("characterId") REFERENCES "character" ("id"),
  FOREIGN KEY ("chatId") REFERENCES "chat" ("id")
);