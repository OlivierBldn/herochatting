-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 12, 2023 at 10:46 PM
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
-- Database: `chatting_ai`
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
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `date` datetime DEFAULT NULL,
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

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `firstName`, `lastName`, `username`, `password`, `email`) VALUES
(4, 'yyyy', 'kkkk', 'okokoko', '$2y$10$9bIm3M1jR8XhsnY6yka7x.lCC9zUE6/saCjJDBT/4rMP4X2B6XAlK', 'okokok@mail.com'),
(10, 'yyyy', 'kkkk', 'ttttt', '$2y$10$H2A3p/HsWm2xHhX5a4Fqv.FineaHncpbULwKmBQNt9FSrYmtiiqm.', 'mil@mail.com');

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
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `universe`
--
ALTER TABLE `universe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

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
