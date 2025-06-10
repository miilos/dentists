-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 10, 2025 at 03:28 PM
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
-- Database: `dentist`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `dentist_id` int(11) NOT NULL,
  `scheduled_at` datetime NOT NULL DEFAULT current_timestamp(),
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointment_codes`
--

CREATE TABLE `appointment_codes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `code` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointment_service`
--

CREATE TABLE `appointment_service` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dentist`
--

CREATE TABLE `dentist` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `specialization` varchar(255) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'dentist'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dentist`
--

INSERT INTO `dentist` (`id`, `first_name`, `last_name`, `email`, `specialization`, `photo`, `role`) VALUES
(1, 'Will', 'Green', 'will.green@gmail.com', 'general', '/img/will_green.jpg', 'dentist'),
(2, 'John', 'Smith', 'john@gmail.com', 'orthodontics', '/img/john_smith.jpg', 'dentist'),
(3, 'Mary', 'Jones', 'mary.jones@gmail.com', 'pedodontics', '/img/mary_jones.jpg', 'dentist'),
(4, 'Samantha', 'Wales', 'samantha.wales@gmail.com', 'cosmetic dentistry', '/img/samantha_wales.jpg', 'dentist'),
(5, 'Mike', 'Malone', 'mike.malone@gmail.com', 'orthodontics', '/img/mike_malone.jpg', 'dentist');

-- --------------------------------------------------------

--
-- Table structure for table `dentist_service`
--

CREATE TABLE `dentist_service` (
  `id` int(11) NOT NULL,
  `dentist_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL
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
-- Table structure for table `service`
--

CREATE TABLE `service` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `duration` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service`
--

INSERT INTO `service` (`id`, `name`, `duration`, `price`) VALUES
(1, 'tooth extraction', 20, 9.99),
(2, 'root canal', 30, 14.99),
(3, 'braces', 60, 199.99),
(4, 'cleaning', 15, 9.99),
(5, 'veneers', 120, 249.99),
(6, 'dental filling', 45, 24.99),
(7, 'wisdom teeth removal', 180, 299.99);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `activation_token` varchar(255) DEFAULT NULL,
  `activation_token_expires_at` datetime NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `is_banned` tinyint(1) NOT NULL DEFAULT 0,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `first_name`, `last_name`, `email`, `phone`, `password`, `activation_token`, `activation_token_expires_at`, `is_active`, `is_banned`, `password_reset_token`, `role`) VALUES
(1, 'test', 'user', 'test@gmail.com', '064123432', '$2y$10$sKCCQJhcQdMIIlmuVwxmNuvPdGZEsO23mCTvTmmKEO0KR1OVb5RzO', NULL, '0000-00-00 00:00:00', 0, 0, NULL, 'user'),
(2, 'test', 'user', 'test2@gmail.com', '064123432', '$2y$10$tvlT982XHYexh2O/FYEScekEz9mcY.yn.cCWvfhp2XnB6cMNWUlNi', NULL, '0000-00-00 00:00:00', 0, 0, NULL, 'user'),
(4, 'test', 'user', 'test3@gmail.com', '064123432', '$2y$10$PIuPiQGT5nhLksvgNYM1LO33w.qN2IbOFz6ADOXkn.oZ7VJkFCNJW', '977e75260726221d748c9585eaa9b418e1478daf8b401ca52bdd14c6e4fa500a', '2025-06-05 18:43:20', 0, 0, NULL, 'user'),
(5, 'test', 'user', 'test4@gmail.com', '064123432', '$2y$10$B8KZwXCleQ7rjZe6Thso7emgMxzPA9DYfKslUfp3aGDzbdsMcfFfS', NULL, '2025-06-05 19:25:02', 1, 0, NULL, 'user'),
(6, 'test', 'user', 'test5@gmail.com', '064123432', '$2y$10$oR9kS1Kyz1mBpMrIZAGSoeWxfAek6dMRcs.jTi/QEMf9zP.n8yVJ.', NULL, '2025-06-05 19:29:13', 1, 0, NULL, 'user'),
(7, 'test', 'user', 't@gmail.com', '064123432', '$2y$10$R8B5IfblovgYSNUdNZ5Lsu3CsDWC1aOAgrfuUPp2wc40YhwqGKmGO', '9d1a020f5c41a6eeede058c93f19d503cf97a899f12b3a3a80ab04cd39929b99', '2025-06-05 20:13:47', 0, 0, NULL, 'user');

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
-- Indexes for table `service`
--
ALTER TABLE `service`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointment_codes`
--
ALTER TABLE `appointment_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointment_service`
--
ALTER TABLE `appointment_service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dentist`
--
ALTER TABLE `dentist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `dentist_service`
--
ALTER TABLE `dentist_service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
