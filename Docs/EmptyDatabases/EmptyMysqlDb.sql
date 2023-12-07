-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 07, 2023 at 11:48 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hero_chatting`
--

-- --------------------------------------------------------

--
-- Table structure for table `character`
--

CREATE TABLE `character` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `character_chat`
--

CREATE TABLE `character_chat` (
  `characterId` int(11) NOT NULL,
  `chatId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

CREATE TABLE `chat` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_message`
--

CREATE TABLE `chat_message` (
  `chatId` int(11) DEFAULT NULL,
  `messageId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `image_references`
--

CREATE TABLE `image_references` (
  `id` int(11) NOT NULL,
  `image_file_name` varchar(255) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `entity_type` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `is_human` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `universe`
--

CREATE TABLE `universe` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `universe_character`
--

CREATE TABLE `universe_character` (
  `universeId` int(11) DEFAULT NULL,
  `characterId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `lastName` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_chat`
--

CREATE TABLE `user_chat` (
  `userId` int(11) DEFAULT NULL,
  `chatId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_universe`
--

CREATE TABLE `user_universe` (
  `userId` int(11) DEFAULT NULL,
  `universeId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `character`
--
ALTER TABLE `character`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `character_chat`
--
ALTER TABLE `character_chat`
  ADD KEY `characterId` (`characterId`),
  ADD KEY `chatId` (`chatId`);

--
-- Indexes for table `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chat_message`
--
ALTER TABLE `chat_message`
  ADD KEY `chatId` (`chatId`),
  ADD KEY `messageId` (`messageId`);

--
-- Indexes for table `image_references`
--
ALTER TABLE `image_references`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `universe`
--
ALTER TABLE `universe`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `universe_character`
--
ALTER TABLE `universe_character`
  ADD KEY `universeId` (`universeId`),
  ADD KEY `characterId` (`characterId`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_chat`
--
ALTER TABLE `user_chat`
  ADD KEY `userId` (`userId`),
  ADD KEY `chatId` (`chatId`);

--
-- Indexes for table `user_universe`
--
ALTER TABLE `user_universe`
  ADD KEY `userId` (`userId`),
  ADD KEY `universeId` (`universeId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `character`
--
ALTER TABLE `character`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat`
--
ALTER TABLE `chat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `image_references`
--
ALTER TABLE `image_references`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `universe`
--
ALTER TABLE `universe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `character_chat`
--
ALTER TABLE `character_chat`
  ADD CONSTRAINT `character_chat_ibfk_1` FOREIGN KEY (`characterId`) REFERENCES `character` (`id`),
  ADD CONSTRAINT `character_chat_ibfk_2` FOREIGN KEY (`chatId`) REFERENCES `chat` (`id`);

--
-- Constraints for table `chat_message`
--
ALTER TABLE `chat_message`
  ADD CONSTRAINT `chat_message_ibfk_1` FOREIGN KEY (`chatId`) REFERENCES `chat` (`id`),
  ADD CONSTRAINT `chat_message_ibfk_2` FOREIGN KEY (`messageId`) REFERENCES `message` (`id`);

--
-- Constraints for table `universe_character`
--
ALTER TABLE `universe_character`
  ADD CONSTRAINT `universe_character_ibfk_1` FOREIGN KEY (`universeId`) REFERENCES `universe` (`id`),
  ADD CONSTRAINT `universe_character_ibfk_2` FOREIGN KEY (`characterId`) REFERENCES `character` (`id`);

--
-- Constraints for table `user_chat`
--
ALTER TABLE `user_chat`
  ADD CONSTRAINT `user_chat_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `user_chat_ibfk_2` FOREIGN KEY (`chatId`) REFERENCES `chat` (`id`);

--
-- Constraints for table `user_universe`
--
ALTER TABLE `user_universe`
  ADD CONSTRAINT `user_universe_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `user_universe_ibfk_2` FOREIGN KEY (`universeId`) REFERENCES `universe` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
