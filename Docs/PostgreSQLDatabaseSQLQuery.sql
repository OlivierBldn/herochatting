CREATE TABLE "user" (
  "id" SERIAL PRIMARY KEY,
  "firstName" TEXT NOT NULL,
  "lastName" TEXT NOT NULL,
  "username" TEXT NOT NULL,
  "password" TEXT NOT NULL,
  "email" TEXT NOT NULL
);

CREATE TABLE "universe" (
  "id" SERIAL PRIMARY KEY,
  "name" TEXT NOT NULL,
  "description" TEXT NOT NULL,
  "image" TEXT NOT NULL,
  "id_user" INTEGER NOT NULL,
  FOREIGN KEY ("id_user") REFERENCES "user" ("id") ON DELETE CASCADE
);

CREATE TABLE "character" (
  "id" SERIAL PRIMARY KEY,
  "name" TEXT NOT NULL,
  "description" TEXT NOT NULL,
  "image" TEXT NOT NULL,
  "id_universe" INTEGER NOT NULL,
  FOREIGN KEY ("id_universe") REFERENCES "universe" ("id") ON DELETE CASCADE
);

CREATE TABLE "message" (
  "id" SERIAL PRIMARY KEY,
  "description" TEXT,
  "dateMessage" DATE,
  "is_human" INTEGER,
  "id_user" INTEGER,
  "id_character" INTEGER,
  "id_universe" INTEGER,
  FOREIGN KEY ("id_user") REFERENCES "user" ("id") ON DELETE CASCADE,
  FOREIGN KEY ("id_character") REFERENCES "character" ("id") ON DELETE CASCADE,
  FOREIGN KEY ("id_universe") REFERENCES "universe" ("id") ON DELETE CASCADE
);