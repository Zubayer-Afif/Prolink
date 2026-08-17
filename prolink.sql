-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 08, 2026 at 06:11 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `prolink`
--

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `contract_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `freelancer_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `payment_status` enum('pending','paid') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contracts`
--

INSERT INTO `contracts` (`contract_id`, `job_id`, `client_id`, `freelancer_id`, `title`, `description`, `amount`, `due_date`, `payment_status`, `created_at`) VALUES
(1, 1, 1, 2, 'Web designing', 'Hello', 20.00, '2026-05-30', 'paid', '2026-05-01 15:37:25'),
(2, 2, 1, 2, 'Translation', 'Translate chinese', 15.00, '2026-06-01', 'paid', '2026-05-01 15:52:10'),
(3, 3, 1, 3, 'Graphics', 'Draw a House', 10.00, '2026-06-04', 'paid', '2026-05-02 05:24:09'),
(4, 4, 1, 3, 'Roasting 2.0', 'Roast Ridwan', 5.00, '2026-05-04', 'paid', '2026-05-02 05:46:38'),
(5, 5, 1, 2, 'ux design', 'design a unique', 20.00, '2026-05-02', 'paid', '2026-05-02 06:32:43'),
(6, 11, 1, 7, 'Draw Nashita', 'Draw a very bad picture of nashita', 25.00, '2026-05-09', 'paid', '2026-05-07 16:44:34'),
(7, 12, 1, 2, 'build a site', 'build a good site', 50.00, '2026-05-09', 'pending', '2026-05-08 02:51:50');

-- --------------------------------------------------------

--
-- Table structure for table `freelancer_skills`
--

CREATE TABLE `freelancer_skills` (
  `user_id` int(11) NOT NULL,
  `skill` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `freelancer_skills`
--

INSERT INTO `freelancer_skills` (`user_id`, `skill`) VALUES
(2, 'CSS'),
(2, 'HTML'),
(2, 'PHP'),
(3, 'CSS'),
(3, 'PHP'),
(3, 'Roasting'),
(6, 'java'),
(6, 'Python'),
(6, 'Roasting'),
(7, 'Design'),
(7, 'Graphics Design'),
(8, 'CSS'),
(8, 'HTML'),
(8, 'PHP');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `job_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `budget` decimal(10,2) DEFAULT NULL,
  `job_status` enum('open','in_progress','completed') DEFAULT 'open',
  `skills` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`job_id`, `user_id`, `title`, `description`, `budget`, `job_status`, `skills`) VALUES
(1, 1, 'Web designing', 'Hello', 50.00, 'completed', 'PHP, CSS, HTML'),
(2, 1, 'Translation', 'Translate chinese', 20.00, 'completed', 'PHP, CSS, HTML'),
(3, 1, 'Graphics', 'Draw a House', 40.00, 'completed', ' HTML'),
(4, 1, 'Roasting 2.0', 'Roast Ridwan', 20.00, 'completed', 'PHP, CSS, HTML, Roasting'),
(5, 1, 'ux design', 'design a unique', 20.00, 'completed', 'PHP, CSS, HTML'),
(6, 1, 'E-commerce Website Development', 'Build a modern e-commerce site with shopping cart and payment integration.', 1500.00, 'open', 'PHP, HTML, CSS'),
(7, 1, 'Mobile App UI Design', 'Design beautiful UI screens for an iOS fitness app.', 800.00, 'open', 'Figma, UI Design, Photoshop'),
(8, 4, 'WordPress Blog Setup', 'Set up a professional WordPress blog with custom theme.', 300.00, 'open', 'WordPress, PHP, CSS'),
(9, 4, 'Python Data Analysis Script', 'Create a Python script to analyze sales data and generate reports.', 600.00, 'open', 'Python, Data Analysis'),
(10, 1, 'Logo and Brand Identity', 'Design a complete brand identity including logo and color palette.', 450.00, 'open', 'Graphic Design, Photoshop, Illustrator'),
(11, 1, 'Draw Nashita', 'Draw a very bad picture of nashita', 50.00, 'completed', 'Design'),
(12, 1, 'build a site', 'build a good site', 100.00, 'in_progress', 'PHP, CSS, HTML');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `content`, `timestamp`) VALUES
(1, 'Hello', '2026-05-02 11:46:49'),
(2, 'Hola', '2026-05-02 11:47:03'),
(3, 'Hello', '2026-05-07 22:46:46'),
(4, 'Hello', '2026-05-07 22:46:57'),
(5, 'mistake naki.', '2026-05-07 22:51:45'),
(6, 'I am not mistake', '2026-05-07 22:51:57');

-- --------------------------------------------------------

--
-- Table structure for table `proposals`
--

CREATE TABLE `proposals` (
  `proposal_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `freelancer_id` int(11) NOT NULL,
  `bid_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `cover_letter` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proposals`
--

INSERT INTO `proposals` (`proposal_id`, `job_id`, `freelancer_id`, `bid_amount`, `status`, `cover_letter`, `submitted_at`) VALUES
(1, 1, 2, 20.00, 'accepted', 'hello', '2026-05-01 15:36:56'),
(2, 2, 2, 15.00, 'accepted', 'Hello', '2026-05-01 15:51:37'),
(3, 3, 2, 20.00, 'rejected', 'hello', '2026-05-02 05:23:24'),
(4, 3, 3, 10.00, 'accepted', 'I am pro', '2026-05-02 05:23:38'),
(5, 4, 2, 10.00, 'rejected', 'hola', '2026-05-02 05:46:01'),
(6, 4, 3, 5.00, 'accepted', 'I am the pro', '2026-05-02 05:46:12'),
(7, 5, 2, 20.00, 'accepted', 'interested', '2026-05-02 06:30:30'),
(8, 11, 7, 25.00, 'accepted', 'I can draw', '2026-05-07 16:44:03'),
(9, 12, 2, 50.00, 'accepted', 'I am good', '2026-05-07 16:55:02'),
(10, 12, 8, 25.00, 'rejected', 'good', '2026-05-07 16:55:26');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `reviewee_id` int(11) NOT NULL,
  `reviewer_type` enum('client','freelancer') NOT NULL,
  `rating_score` tinyint(4) NOT NULL,
  `comments` text DEFAULT NULL,
  `review_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `job_id`, `reviewer_id`, `reviewee_id`, `reviewer_type`, `rating_score`, `comments`, `review_date`) VALUES
(1, 1, 1, 2, 'client', 3, 'good', '2026-05-01 15:38:26'),
(2, 1, 2, 1, 'freelancer', 5, 'best', '2026-05-01 15:38:39'),
(3, 2, 2, 1, 'freelancer', 4, 'Good', '2026-05-01 15:53:17'),
(4, 2, 1, 2, 'client', 5, 'Best', '2026-05-01 15:53:25'),
(5, 3, 1, 3, 'client', 5, 'He is the pro', '2026-05-02 05:25:03'),
(6, 3, 3, 1, 'freelancer', 3, 'Not so pro', '2026-05-02 05:25:19'),
(7, 4, 1, 3, 'client', 5, 'He is pro', '2026-05-02 05:47:36'),
(8, 4, 3, 1, 'freelancer', 4, 'Almost Pro', '2026-05-02 05:47:55'),
(9, 5, 1, 2, 'client', 5, 'pro', '2026-05-02 06:43:04'),
(10, 5, 2, 1, 'freelancer', 3, 'good', '2026-05-02 06:43:17'),
(11, 11, 1, 7, 'client', 5, 'Actually bad', '2026-05-07 16:52:44'),
(12, 11, 7, 1, 'freelancer', 5, 'good money', '2026-05-07 16:52:55');

-- --------------------------------------------------------

--
-- Table structure for table `send_message`
--

CREATE TABLE `send_message` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `send_message`
--

INSERT INTO `send_message` (`message_id`, `sender_id`, `receiver_id`, `job_id`) VALUES
(1, 1, 3, 4),
(2, 3, 1, 4),
(3, 7, 1, 11),
(4, 1, 7, 11),
(5, 1, 7, 11),
(6, 7, 1, 11);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `contract_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  `payment_method` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `contract_id`, `amount`, `date`, `payment_method`, `created_at`) VALUES
(1, 1, 20.00, '2026-05-01', 'BRAC Bank', '2026-05-01 15:38:08'),
(2, 2, 15.00, '2026-05-01', 'Standard Chartered Bank', '2026-05-01 15:52:54'),
(3, 3, 10.00, '2026-05-02', 'Dutch Bangla Bank', '2026-05-02 05:24:45'),
(4, 4, 5.00, '2026-05-02', 'BRAC Bank', '2026-05-02 05:47:24'),
(5, 5, 20.00, '2026-05-02', 'BRAC Bank', '2026-05-02 06:41:29'),
(6, 6, 25.00, '2026-05-07', 'BRAC Bank', '2026-05-07 16:52:22');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `type` enum('client','freelancer') NOT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `total_spent` decimal(12,2) DEFAULT 0.00,
  `bio` text DEFAULT NULL,
  `hourly_rate` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password_hash`, `type`, `company_name`, `total_spent`, `bio`, `hourly_rate`) VALUES
(1, 'ZZA095', 'zubayerafif@gmail.com', '$2y$10$3uyFRDIxA9GV83MuoJfd0ep.SoMYibY80nylNPX1wC.fK0/tGt3tm', 'client', 'Asteroid Cast', 95.00, NULL, NULL),
(2, 'ZA09', 'afifzawad@gmail.com', '$2y$10$.MJzm7ENsPV7.lAyklAj8utnJ8MRB63JMFKKC0ZHk3dWL/jTdtKvq', 'freelancer', NULL, 0.00, 'Hello', 10.00),
(3, 'Demo', 'freelancer123@gmail.com', '$2y$10$SIK0VrsYonBqjABWsy95TOWe9.86ZD4qfKNs3ArZfLsLdFvFc6Cd.', 'freelancer', NULL, 0.00, 'I am PRO. The one and only.', 5.00),
(4, NULL, 'client@gmail.com', '$2y$10$SzGll.S14yKHLH7Ny5uFCOqCh8YKgASSxwIqEu4d9Xkl03s/sk1Wm', 'client', NULL, 0.00, NULL, NULL),
(5, NULL, 'freelancer@gmail.com', '$2y$10$k/BppbDqqXd/674Uq7yk3uMJ82KdwqRfPnnVxHQN2eH0g2AagMBW2', 'freelancer', NULL, 0.00, NULL, NULL),
(6, 'Honolulu', 'zubayerstorage03@gmail.com', '$2y$10$hMuI0CcOOJd6sBSfYTly3.R1gT4kQVIaG39gdnvEcoOu2K9qFVT0m', 'freelancer', NULL, 0.00, 'Hello. I am pro as Hell.', 10.00),
(7, 'Astha', 'familyvibes1129@gmail.com', '$2y$10$M0hek5zNpy2Cxa6nE.wB3O4YPqdG2GnzJzN8BVuNcTbDXNHlYtVZy', 'freelancer', NULL, 0.00, 'I am the Mistake', 20.00),
(8, 'Iron Clad', 'ironclad095@gmail.com', '$2y$10$dlIspfeoc1zlwDkFBXQr.e8Nsyg6EATN5uTSXGFIfzZmVAnWHreVy', 'freelancer', NULL, 0.00, 'Hola', 50.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`contract_id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `freelancer_id` (`freelancer_id`);

--
-- Indexes for table `freelancer_skills`
--
ALTER TABLE `freelancer_skills`
  ADD PRIMARY KEY (`user_id`,`skill`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`job_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `proposals`
--
ALTER TABLE `proposals`
  ADD PRIMARY KEY (`proposal_id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `freelancer_id` (`freelancer_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `unique_review` (`job_id`,`reviewer_id`,`reviewee_id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `reviewee_id` (`reviewee_id`);

--
-- Indexes for table `send_message`
--
ALTER TABLE `send_message`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `contract_id` (`contract_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `contract_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `proposals`
--
ALTER TABLE `proposals`
  MODIFY `proposal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contracts`
--
ALTER TABLE `contracts`
  ADD CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`job_id`),
  ADD CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `contracts_ibfk_3` FOREIGN KEY (`freelancer_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `freelancer_skills`
--
ALTER TABLE `freelancer_skills`
  ADD CONSTRAINT `freelancer_skills_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `proposals`
--
ALTER TABLE `proposals`
  ADD CONSTRAINT `proposals_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`job_id`),
  ADD CONSTRAINT `proposals_ibfk_2` FOREIGN KEY (`freelancer_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`job_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`reviewee_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `send_message`
--
ALTER TABLE `send_message`
  ADD CONSTRAINT `send_message_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`message_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `send_message_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `send_message_ibfk_3` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `send_message_ibfk_4` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`job_id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`contract_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
