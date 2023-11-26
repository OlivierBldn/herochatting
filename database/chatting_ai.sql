-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 19, 2023 at 11:58 PM
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

--
-- Dumping data for table `character`
--

INSERT INTO `character` (`id`, `name`, `description`, `image`) VALUES
(21, 'Yoda', 'Maître', 'yoda.jpeg'),
(26, 'Yoda', 'Maître', 'yoda.jpeg'),
(27, 'Yoda', 'Maître', 'yoda.jpeg'),
(28, 'Test', 'zertgfrfdeg', 'fdsgfdg'),
(29, 'Yoda', 'Maître', 'yoda.jpeg'),
(30, 'Yoda', 'zertgfrfdeg', 'fdsgfdg');

-- --------------------------------------------------------

--
-- Table structure for table `character_chat`
--

CREATE TABLE `character_chat` (
  `characterId` int(11) NOT NULL,
  `chatId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `character_chat`
--

INSERT INTO `character_chat` (`characterId`, `chatId`) VALUES
(21, 1);

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

CREATE TABLE `chat` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat`
--

INSERT INTO `chat` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `chat_message`
--

CREATE TABLE `chat_message` (
  `chatId` int(11) DEFAULT NULL,
  `messageId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_message`
--

INSERT INTO `chat_message` (`chatId`, `messageId`) VALUES
(1, 3);

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

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`id`, `content`, `createdAt`, `is_human`) VALUES
(3, 'Test message 1', '2023-11-19 20:42:37', 1);

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

--
-- Dumping data for table `universe`
--

INSERT INTO `universe` (`id`, `name`, `description`, `image`) VALUES
(42, 'rujhjtrj', 'blabla', 'test'),
(43, 'Star Wars', 'ok', 'test'),
(44, 'rujhjtrj', 'blabla', 'test'),
(45, 'rokokoko', 'tetetete', 'eheheh'),
(46, 'ryghfghgf', 'uhyfgyjg', 'eheheh'),
(47, 'piouty', 'uhyfgyjg', 'eheheh'),
(48, 'piouty', 'uhyfgyjg', 'eheheh'),
(49, 'piouty', 'uhyfgyjg', 'eheheh'),
(50, 'piouty', 'uhyfgyjg', 'eheheh'),
(51, 'piouty', 'uhyfgyjg', 'eheheh'),
(52, 'piouty', 'uhyfgyjg', 'eheheh'),
(53, 'piouty', 'uhyfgyjg', 'eheheh'),
(54, 'piouty', 'uhyfgyjg', 'eheheh'),
(55, 'piouty', 'uhyfgyjg', 'eheheh'),
(56, 'dfsfsdf', 'uhyfgyjg', 'eheheh'),
(57, 'dfsfsdf', 'uhyfgyjg', 'eheheh'),
(58, 'dfsfsdf', 'uhyfgyjg', 'eheheh'),
(59, 'dfsfsdf', 'uhyfgyjg', 'eheheh'),
(60, 'dfsfsdf', 'uiouioiu', 'frghdgf'),
(61, 'dfsfsdf', 'uiouioiu', 'frghdgf'),
(62, 'dfsfsdf', 'iîi', 'frghdgf'),
(63, 'dfsfsdf', 'iîi', 'frghdgf'),
(64, 'dfsfsdf', 'uhyfgyjg', 'eheheh'),
(65, 'dfsfsdf', 'uhyfgyjg', 'eheheh'),
(66, 'dfsfsdf', 'uhyfgyjg', 'eheheh'),
(67, 'dfsfsdf', 'uhyfgyjg', 'eheheh'),
(68, 'dfsfsdf', 'uhyfgyjg', 'eheheh'),
(69, 'test', 'ytutyuiyu', 'frghdgf'),
(70, 'test', 'ytutyuiyu', 'frghdgf'),
(71, 'test', 'ytutyuiyu', 'frghdgf');

-- --------------------------------------------------------

--
-- Table structure for table `universe_character`
--

CREATE TABLE `universe_character` (
  `universeId` int(11) DEFAULT NULL,
  `characterId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `universe_character`
--

INSERT INTO `universe_character` (`universeId`, `characterId`) VALUES
(44, 21),
(44, 26),
(44, 27),
(44, 28),
(44, 29),
(43, 30);

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
(10, 'yyyy', 'kkkk', 'ttttt', '$2y$10$H2A3p/HsWm2xHhX5a4Fqv.FineaHncpbULwKmBQNt9FSrYmtiiqm.', 'mil@mail.com'),
(13, 'olivier', 'kkkk', 'tpojp', '$2y$10$vTCGba4eHMw/CosBVfeVAuydonlpXQp70/SbZrRodvHfTNagIt04S', 'test@mail.com'),
(16, 'firstname', 'lastname', 'username', '$2y$10$X7dcG3RaXs1J3kgkfxwW6OV4Cd7VUMijbnpp.AD47RU4x/UrYDa0e', 'mail@mail.com'),
(17, 'firstname', 'lastname', 'polo', '$2y$10$MwRukE0zgBLo8Sj6u6glA.etlnMaAN7kCdPdQcvL5Tm29audIxBjy', 'polo@mail.com'),
(19, 'firstname', 'lastname', 'mlml', '$2y$10$X4FNpizm7oFM.5OddWRIHed4apjE3NfjWpSjCyriC61Jp2ViHl0xe', 'mlml@mail.com'),
(23, 'firstname', 'lastname', 'test', '$2y$10$Tjdff0dKucj8NRiZPKy0Auxj65AyH1NWaJ6TAggMhDHY7h3WySZnC', '6546341@mail.com');

-- --------------------------------------------------------

--
-- Table structure for table `user_chat`
--

CREATE TABLE `user_chat` (
  `userId` int(11) DEFAULT NULL,
  `chatId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_chat`
--

INSERT INTO `user_chat` (`userId`, `chatId`) VALUES
(13, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_universe`
--

CREATE TABLE `user_universe` (
  `userId` int(11) DEFAULT NULL,
  `universeId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_universe`
--

INSERT INTO `user_universe` (`userId`, `universeId`) VALUES
(10, 43),
(NULL, 44),
(NULL, 45),
(NULL, 46),
(NULL, 47),
(NULL, 48),
(NULL, 49),
(NULL, 50),
(NULL, 51),
(NULL, 52),
(10, 53),
(10, 54),
(10, 55),
(10, 56),
(10, 57),
(10, 58),
(10, 59),
(10, 60),
(10, 61),
(10, 62),
(10, 63),
(10, 64),
(10, 65),
(10, 66),
(10, 67),
(10, 68),
(10, 69),
(10, 70),
(10, 71);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `chat`
--
ALTER TABLE `chat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `universe`
--
ALTER TABLE `universe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

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
