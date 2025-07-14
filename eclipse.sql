-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 14, 2025 at 04:30 PM
-- Server version: 8.0.42-0ubuntu0.20.04.1
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `eclipse`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `dentist_id` int NOT NULL,
  `scheduled_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `price` int NOT NULL,
  `duration` int NOT NULL,
  `note` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment`
--

INSERT INTO `appointment` (`id`, `user_id`, `dentist_id`, `scheduled_at`, `price`, `duration`, `note`) VALUES
(11, 8, 1, '2025-07-21 15:00:00', 2312, 120, 'peri zube'),
(13, 1, 5, '2025-07-17 08:30:00', 15, 30, NULL),
(14, 1, 4, '2025-07-24 08:45:00', 250, 120, NULL),
(16, 8, 5, '2025-07-14 14:30:00', 200, 60, NULL),
(18, 10, 5, '2025-07-15 08:00:00', 215, 90, NULL),
(19, 10, 4, '2025-07-15 08:00:00', 10, 15, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `appointment_codes`
--

CREATE TABLE `appointment_codes` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `appointment_id` int NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment_codes`
--

INSERT INTO `appointment_codes` (`id`, `user_id`, `appointment_id`, `code`, `created_at`) VALUES
(5, 8, 11, 'fd6941586ee35cb6', '2025-07-11 11:12:35'),
(7, 1, 13, 'ba7e57e121accd6d', '2025-07-10 19:44:12'),
(8, 1, 14, '655cb56e3a60ab9e', '2025-07-10 20:13:49'),
(10, 8, 16, '3cddd72c9e58e643', '2025-07-12 15:34:32'),
(12, 10, 18, 'fcf4276a489bc576', '2025-07-14 11:02:23'),
(13, 10, 19, 'f8461e4e8fcca5fa', '2025-07-14 14:59:04');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_service`
--

CREATE TABLE `appointment_service` (
  `id` int NOT NULL,
  `appointment_id` int NOT NULL,
  `service_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment_service`
--

INSERT INTO `appointment_service` (`id`, `appointment_id`, `service_id`) VALUES
(37, 13, 2),
(38, 14, 5),
(40, 16, 3),
(42, 11, 1),
(43, 11, 7),
(45, 18, 3),
(46, 18, 2),
(47, 19, 4);

-- --------------------------------------------------------

--
-- Table structure for table `dentist`
--

CREATE TABLE `dentist` (
  `id` int NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `photo` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'dentist',
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dentist`
--

INSERT INTO `dentist` (`id`, `first_name`, `last_name`, `email`, `photo`, `role`, `password`) VALUES
(1, 'Will', 'Green', 'will.green@gmail.com', '/public/img/will_green.jpg', 'dentist', '$2y$10$04/XO.pQQm7uheY2n13VNOZbMCrITo8XDkg9QTUSanNzfayIX8ab6'),
(2, 'John', 'Smith', 'john@gmail.com', '/public/img/john_smith.jpg', 'dentist', NULL),
(3, 'Mary', 'Jones', 'mary.jones@gmail.com', '/public/img/mary_jones.jpg', 'dentist', NULL),
(4, 'Samantha', 'Wales', 'samantha.wales@gmail.com', '/public/img/samantha_wales.jpg', 'dentist', NULL),
(5, 'Mike', 'Malone', 'mike.malone@gmail.com', '/public/img/mike_malone.jpg', 'dentist', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `dentist_service`
--

CREATE TABLE `dentist_service` (
  `id` int NOT NULL,
  `dentist_id` int NOT NULL,
  `service_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dentist_service`
--

INSERT INTO `dentist_service` (`id`, `dentist_id`, `service_id`) VALUES
(1, 1, 1),
(2, 1, 4),
(3, 1, 6),
(4, 2, 3),
(5, 2, 4),
(6, 2, 1),
(7, 3, 4),
(8, 3, 3),
(9, 3, 6),
(10, 3, 1),
(11, 4, 5),
(12, 4, 4),
(13, 5, 3),
(14, 5, 7),
(15, 5, 2);

-- --------------------------------------------------------

--
-- Table structure for table `dentist_specialization`
--

CREATE TABLE `dentist_specialization` (
  `id` int NOT NULL,
  `dentist_id` int NOT NULL,
  `specialization_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dentist_specialization`
--

INSERT INTO `dentist_specialization` (`id`, `dentist_id`, `specialization_id`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 3, 3),
(4, 4, 4),
(5, 5, 2);

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

CREATE TABLE `service` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `duration` int NOT NULL,
  `price` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service`
--

INSERT INTO `service` (`id`, `name`, `duration`, `price`) VALUES
(1, 'tooth extraction', 30, 10),
(2, 'root canal', 30, 15),
(3, 'braces', 60, 150),
(4, 'cleaning', 15, 10),
(5, 'veneers', 120, 250),
(6, 'dental filling', 45, 25),
(7, 'wisdom teeth removal', 180, 300);

-- --------------------------------------------------------

--
-- Table structure for table `specialization`
--

CREATE TABLE `specialization` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `specialization`
--

INSERT INTO `specialization` (`id`, `name`) VALUES
(1, 'general'),
(2, 'orthodontics'),
(3, 'pedodontics'),
(4, 'cosmetic dentistry');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `activation_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `activation_token_expires_at` datetime NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `is_banned` tinyint(1) NOT NULL DEFAULT '0',
  `password_reset_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user',
  `password_reset_token_expires_at` datetime DEFAULT NULL,
  `num_missed_appointments` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `first_name`, `last_name`, `email`, `phone`, `password`, `activation_token`, `activation_token_expires_at`, `is_active`, `is_banned`, `password_reset_token`, `role`, `password_reset_token_expires_at`, `num_missed_appointments`) VALUES
(1, 'Will', 'Greenn', 'test@gmail.com', '1232344214', '$2y$10$45aM21IC/wE4liWSrIJ5cey1fV/onl25OHJ1fMZdZO1mWsI.k1.FK', NULL, '0000-00-00 00:00:00', 0, 0, NULL, 'user', NULL, 2),
(8, 'User', 'Test', 'user@gmail.com', '06412343212', '$2y$10$r3sHXs/UzzvFOCk2mWY4RuPSPuzYc18pXZiSCvQDRYpYp8HYPIW.m', '820f97abc79a62a58487447238568a58b5dfb8d8bcb29703f2db344096d269a8', '2025-07-12 16:23:07', 0, 0, 'acd18c34c8bcd311d36ee04f93d0fa71', 'user', '2025-07-13 01:27:25', 0),
(9, 'admin', 'test', 'admin@gmail.com', '06412343212', '$2y$10$O8Pp4gPnCLE60D2NSjwm9ueJ7lzcx3bqPtzSrV8tjbC2duaf5pxXe', '4ad3a47c0fa2a73d4bf4ccf0e2d437ba835d32cbd7ffb3f10e9d0c55b979c52a', '2025-07-13 13:14:02', 0, 0, NULL, 'admin', NULL, 0),
(10, 'test', 'user', 'test1@gmail.com', '1231231232', '$2y$10$KZg4g62qxNWmFp9irsoMweIFpFDwV27T.0HY20n6MLQAZZfwshX06', 'b3e4aad868b5b29ef7604917ca61ad7f746063d38875361230f96c43b6762024', '2025-07-14 11:59:25', 0, 0, NULL, 'user', NULL, 0),
(11, 'user', 'name', 'example@gmail.com', '1231231231', '$2y$10$EWR.QYwrEkBpA7B0FdvSnuwAktWBMuNNh7H.QPuGKJapUIyaunB2C', '3b83c65d253e12caba6aeba38e8a6176136385e688b14a5ca0477d13ddbaad19', '2025-07-14 16:01:27', 0, 0, NULL, 'user', NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `appointment_codes`
--
ALTER TABLE `appointment_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_code_user` (`user_id`),
  ADD KEY `fk_code_appointment` (`appointment_id`);

--
-- Indexes for table `appointment_service`
--
ALTER TABLE `appointment_service`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_appointment_appointment` (`appointment_id`),
  ADD KEY `fk_appointment_service` (`service_id`);

--
-- Indexes for table `dentist`
--
ALTER TABLE `dentist`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dentist_service`
--
ALTER TABLE `dentist_service`
  ADD PRIMARY KEY (`id`),
  ADD KEY `offer_dentist_id` (`dentist_id`),
  ADD KEY `offer_offer_id` (`service_id`);

--
-- Indexes for table `dentist_specialization`
--
ALTER TABLE `dentist_specialization`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dentist_specialization` (`dentist_id`),
  ADD KEY `fk_specialization` (`specialization_id`);

--
-- Indexes for table `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `specialization`
--
ALTER TABLE `specialization`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `appointment_codes`
--
ALTER TABLE `appointment_codes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `appointment_service`
--
ALTER TABLE `appointment_service`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `dentist`
--
ALTER TABLE `dentist`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `dentist_service`
--
ALTER TABLE `dentist_service`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `dentist_specialization`
--
ALTER TABLE `dentist_specialization`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `specialization`
--
ALTER TABLE `specialization`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointment_codes`
--
ALTER TABLE `appointment_codes`
  ADD CONSTRAINT `fk_code_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointment` (`id`),
  ADD CONSTRAINT `fk_code_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `appointment_service`
--
ALTER TABLE `appointment_service`
  ADD CONSTRAINT `fk_appointment_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointment` (`id`),
  ADD CONSTRAINT `fk_appointment_service` FOREIGN KEY (`service_id`) REFERENCES `service` (`id`);

--
-- Constraints for table `dentist_service`
--
ALTER TABLE `dentist_service`
  ADD CONSTRAINT `offer_dentist_id` FOREIGN KEY (`dentist_id`) REFERENCES `dentist` (`id`),
  ADD CONSTRAINT `offer_offer_id` FOREIGN KEY (`service_id`) REFERENCES `service` (`id`);

--
-- Constraints for table `dentist_specialization`
--
ALTER TABLE `dentist_specialization`
  ADD CONSTRAINT `fk_dentist_specialization` FOREIGN KEY (`dentist_id`) REFERENCES `dentist` (`id`),
  ADD CONSTRAINT `fk_specialization` FOREIGN KEY (`specialization_id`) REFERENCES `specialization` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
