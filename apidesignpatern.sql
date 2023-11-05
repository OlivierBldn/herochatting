-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 05, 2023 at 10:10 PM
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
-- Database: `apidesignpatern`
--

-- --------------------------------------------------------

--
-- Table structure for table `character`
--

CREATE TABLE `character` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `image` text NOT NULL,
  `id_universe` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `dateMessage` date DEFAULT NULL,
  `is_human` tinyint(1) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_character` int(11) DEFAULT NULL,
  `id_universe` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `universe`
--

CREATE TABLE `universe` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `image` text NOT NULL,
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `firstName`, `lastName`, `username`, `password`, `email`) VALUES
(3, 'Arthur', 'Delarue', 'Surnom', '123456', 'hello@mail.com'),
(4, 'Paul', 'Miraut', 'Polo', '$2y$10$0Rt7WBUq5TejJuXWHcBNtOBlE1ZUbGYSSmtlr7H.bBuXVnyJy/MVu', 'testo@mail.com'),
(5, 'Lolo', 'lala', 'sqgsdh', '$2y$10$09PjGAGOd0jBL/63kXFb6eCUWTqrfiEaENdWglfArsjZ7VdWF7QAm', 'magic@mail.com'),
(6, '', 'sgnfn', 'sfngvbn', 'mlkdsghj', 'tes@mail.com'),
(7, 'hfdgjhg', 'sgnfn', 'sfnfhgdjgvbn', '$2y$10$dh..OejEX1275/xjHVDBDOipgcscX.Orx5kTs/rEceIw74lg0thvO', 'tesfdhbdf@mail.com'),
(8, 'opdfhsxhgfd', 'fsdhgoksdfhiot', '63o', '$2y$10$A1p9FEr9IAgZaAchGCBeI.Nk4HKjhE3nt/1EKHIJR1wZMivXYKEQO', 'hey@mail.com'),
(10, 'pfdghu', 'gfhgdfh', 'tololo', '$2y$10$1UisWuUX8/qkQe0CE7S.TecqC93yI.MBafmjaXYQxSq7oiA.cx8AO', 'poiu@mail.com'),
(12, 'llllllll', 'glllll', 'okokok', '$2y$10$3cAKNSoKpVw/sX0Ky9Gk8.VqcbBoNdWhDHRTzH.Z/EDzk47bqopn2', 'pu@mail.com'),
(13, 'aaaaall', 'aaaall', 'j', '$2y$10$dIsQOfq46wvUeU18saMcd.yOfkGacp0W2FUkKQps841UZKAHYy612', 'pu@mail.com'),
(14, 'oooooo', 'momomomo', 'iuiuiuiu', '$2y$10$mkMfMvA3VbpUJ3YOpKnW5e5.qS2fpZCfpwdFa491IKIL9Jedqmibi', 'pu@mail.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `character`
--
ALTER TABLE `character`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_universe_id` (`id_universe`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_message_user` (`id_user`),
  ADD KEY `FK_message_character` (`id_character`),
  ADD KEY `FK_message_universe` (`id_universe`);

--
-- Indexes for table `universe`
--
ALTER TABLE `universe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_user_id` (`id_user`) USING BTREE;

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `character`
--
ALTER TABLE `character`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `universe`
--
ALTER TABLE `universe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `character`
--
ALTER TABLE `character`
  ADD CONSTRAINT `FK_universe_id` FOREIGN KEY (`id_universe`) REFERENCES `universe` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `FK_message_character` FOREIGN KEY (`id_character`) REFERENCES `character` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_message_universe` FOREIGN KEY (`id_universe`) REFERENCES `universe` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_message_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `universe`
--
ALTER TABLE `universe`
  ADD CONSTRAINT `FK_user_id` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
