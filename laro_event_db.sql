-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 24, 2026 at 09:29 AM
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
-- Database: `laro_event_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `actor_type` enum('Admin','Registrant','System') NOT NULL,
  `actor_name` varchar(100) NOT NULL,
  `action_description` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `actor_type`, `actor_name`, `action_description`, `ip_address`, `created_at`) VALUES
(1, 'Admin', 'superadmin', 'Verified payment for Player ID: 7', '138.84.137.22', '2026-07-23 13:15:41'),
(2, 'Admin', 'ajretuya', 'Scanned QR and granted entry to Player ID: 7', '138.84.137.22', '2026-07-23 13:16:16'),
(3, 'Registrant', 'Jessica Jones', 'Submitted a new event registration.', '138.84.137.22', '2026-07-23 13:23:12'),
(4, 'Admin', 'superadmin', 'Added new event module: Sorbetes (Food)', '138.84.137.22', '2026-07-23 13:33:47'),
(5, 'Admin', 'superadmin', 'Initiated Direct Print for physical tickets.', '138.84.137.22', '2026-07-23 13:34:06'),
(6, 'Admin', 'superadmin', 'Exported physical tickets as PDF (A4).', '138.84.137.22', '2026-07-23 13:34:25'),
(7, 'Admin', 'superadmin', 'Updated maximum registrant limit to: 75', '138.84.137.22', '2026-07-23 13:45:58'),
(8, 'Admin', 'superadmin', 'Updated maximum registrant limit to: 10', '138.84.137.22', '2026-07-23 13:47:17'),
(9, 'System', 'Limit Limit', 'Attempted to register but event was full.', '138.84.137.22', '2026-07-23 13:48:14'),
(10, 'System', 'Limit Limit', 'Attempted to register but event was full.', '138.84.137.22', '2026-07-23 13:48:19'),
(11, 'System', 'Limit Limit', 'Attempted to register but event was full.', '138.84.137.22', '2026-07-23 13:49:46'),
(12, 'System', 'Limit Limit', 'Attempted to register but event was full.', '138.84.137.22', '2026-07-23 13:56:06'),
(13, 'Admin', 'superadmin', 'Updated maximum registrant limit to: 11', '138.84.137.22', '2026-07-23 14:07:28'),
(14, 'Admin', 'superadmin', 'Updated maximum registrant limit to: 75', '138.84.137.22', '2026-07-23 14:07:45'),
(15, 'Admin', 'ajretuya', 'Added new event module: Basketball (Arcade)', '138.84.137.22', '2026-07-23 14:23:31'),
(16, 'Admin', 'ajretuya', 'Added new event module: Hotdog Buns (Food)', '138.84.137.22', '2026-07-23 14:24:08'),
(17, 'Registrant', 'Juan Dela Cruz', 'Submitted a new event registration.', '138.84.137.22', '2026-07-23 14:25:57'),
(18, 'Admin', 'ajretuya', 'Flagged Player ID: 1 for issue: Short', '138.84.137.22', '2026-07-23 14:26:34'),
(19, 'Admin', 'ajretuya', 'Flagged Player ID: 1 for issue: Invalid', '138.84.137.22', '2026-07-23 14:27:57'),
(20, 'Admin', 'ajretuya', 'Verified payment for Player ID: 1', '138.84.137.22', '2026-07-23 14:28:32'),
(21, 'Admin', 'ajretuya', 'Scanned QR and granted entry to Player ID: 1', '138.84.137.22', '2026-07-23 14:31:23'),
(22, 'Admin', 'ajretuya', 'Initiated Direct Print for physical tickets.', '138.84.137.22', '2026-07-23 14:32:52'),
(23, 'Admin', 'ajretuya', 'Exported physical tickets as PDF (FOLIO).', '138.84.137.22', '2026-07-23 14:33:07'),
(24, 'Admin', 'ajretuya', 'Initiated Direct Print for physical tickets.', '138.84.137.22', '2026-07-23 14:33:42'),
(25, 'Admin', 'ajretuya', 'Initiated Direct Print for physical tickets.', '138.84.137.22', '2026-07-23 14:34:19'),
(26, 'Admin', 'superadmin', 'Updated maximum registrant limit to: 1', '138.84.137.22', '2026-07-23 14:35:52'),
(27, 'Admin', 'superadmin', 'Updated maximum registrant limit to: 75', '138.84.137.22', '2026-07-23 14:36:15'),
(28, 'Admin', 'superadmin', 'Deleted event module: ', '138.84.137.22', '2026-07-23 14:50:45'),
(29, 'Admin', 'superadmin', 'Deleted event module: ', '138.84.137.22', '2026-07-23 14:51:08'),
(30, 'Admin', 'superadmin', 'Deleted event module: ', '138.84.137.22', '2026-07-23 14:51:10'),
(31, 'Admin', 'superadmin', 'Deleted event module: ', '138.84.137.22', '2026-07-23 14:51:12'),
(32, 'Admin', 'superadmin', 'Deleted event module: ', '138.84.137.22', '2026-07-23 14:51:14'),
(33, 'Admin', 'superadmin', 'Deleted event module: ', '138.84.137.22', '2026-07-23 14:51:16'),
(34, 'Admin', 'superadmin', 'Added new event module: Claw Machine (Arcade)', '138.84.137.22', '2026-07-23 14:52:22'),
(35, 'Admin', 'superadmin', 'Added new event module: Basketball (Arcade)', '138.84.137.22', '2026-07-23 14:52:34'),
(36, 'Admin', 'superadmin', 'Added new event module: Falling Sticks (Arcade)', '138.84.137.22', '2026-07-23 14:52:47'),
(37, 'Admin', 'superadmin', 'Added new event module: Buzz Wire (Arcade)', '138.84.137.22', '2026-07-23 14:52:58'),
(38, 'Admin', 'superadmin', 'Deleted event module: ', '138.84.137.22', '2026-07-23 14:57:49'),
(39, 'Admin', 'superadmin', 'Deleted event module: Claw Machine', '138.84.137.22', '2026-07-23 15:04:33'),
(40, 'Admin', 'superadmin', 'Added new event module: Buzz Wire (Arcade)', '138.84.137.22', '2026-07-23 15:06:32'),
(41, 'Admin', 'superadmin', 'Added new event module: 10 Second Challenge (Arcade)', '138.84.137.22', '2026-07-23 15:06:56'),
(42, 'Admin', 'superadmin', 'Added new event module: Claw Machine (Arcade)', '138.84.137.22', '2026-07-23 15:08:00'),
(43, 'Admin', 'ajretuya', 'Added new event module: Cover The Spot (Arcade)', '138.84.137.22', '2026-07-23 15:18:52'),
(44, 'Admin', 'ajretuya', 'Added new event module: Sorbates (Food)', '138.84.137.22', '2026-07-23 15:19:54'),
(45, 'Admin', 'ajretuya', 'Added new event module: Hotdog Buns (Food)', '138.84.137.22', '2026-07-23 15:20:09'),
(46, 'Admin', 'ajretuya', 'Added new event module: Juice Bar (Food)', '138.84.137.22', '2026-07-23 15:20:21'),
(47, 'Admin', 'ajretuya', 'Initiated Direct Print for physical tickets.', '138.84.137.22', '2026-07-23 15:21:33'),
(48, 'Registrant', 'Juan  Dela Cruz', 'Submitted a new event registration.', '138.84.137.22', '2026-07-24 07:11:05'),
(49, 'Admin', 'superadmin', 'Verified payment for Player ID: 1', '138.84.137.22', '2026-07-24 07:11:20'),
(50, 'Admin', 'ajretuya', 'Scanned QR and granted entry to Player ID: 1', '138.84.137.22', '2026-07-24 07:17:43');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Super Admin','Admin') NOT NULL DEFAULT 'Admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `require_password_change` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `role`, `created_at`, `require_password_change`) VALUES
(2, 'superadmin', '$2y$10$XqMYjXGLqLs3EDCFcleNNOUg7v.18qu6075Dd1CShElGzPxq00imS', 'Super Admin', '2026-07-21 13:25:59', 0),
(3, 'ajretuya', '$2y$10$n1Xt6ALiPYbS9EAKtPEm6enWPM1Poqz0ba.CvUDep8iOundsRVape', 'Admin', '2026-07-21 13:36:53', 0),
(4, 'piamorales', '$2y$10$RptHKQULPWHy4HgKcWbFMu2gjrWTurM4ohD7deU7A.TArirDKIneC', 'Admin', '2026-07-23 14:37:02', 0),
(5, 'lhenrivera', '$2y$10$hrO78IyA0OQKTlz3XQPmD.U.0q05LgNiC6jpzQdxIFBGTFuX8ka8y', 'Admin', '2026-07-23 14:37:31', 0),
(6, 'christiajonieca', '$2y$10$PiSiRi9TQyHxjhAJCatrxe6lLa4cJUOLj7V/UqKgWkju0.W3muEOa', 'Admin', '2026-07-23 14:41:50', 0);

-- --------------------------------------------------------

--
-- Table structure for table `event_modules`
--

CREATE TABLE `event_modules` (
  `id` int(11) NOT NULL,
  `module_name` varchar(100) NOT NULL,
  `module_type` varchar(50) NOT NULL,
  `default_tickets` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_modules`
--

INSERT INTO `event_modules` (`id`, `module_name`, `module_type`, `default_tickets`) VALUES
(8, 'Basketball', 'Arcade', 1),
(9, 'Falling Sticks', 'Arcade', 1),
(11, 'Buzz Wire', 'Arcade', 1),
(12, '10 Second Challenge', 'Arcade', 1),
(13, 'Claw Machine', 'Arcade', 1),
(14, 'Cover The Spot', 'Arcade', 1),
(15, 'Sorbates', 'Food', 1),
(16, 'Hotdog Buns', 'Food', 1),
(17, 'Juice Bar', 'Food', 1);

-- --------------------------------------------------------

--
-- Table structure for table `event_settings`
--

CREATE TABLE `event_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_settings`
--

INSERT INTO `event_settings` (`setting_key`, `setting_value`) VALUES
('max_registrants', 75);

-- --------------------------------------------------------

--
-- Table structure for table `registrants`
--

CREATE TABLE `registrants` (
  `id` int(11) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_initial` varchar(5) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `social_link` varchar(255) DEFAULT NULL,
  `age` int(11) NOT NULL,
  `payment_proof` varchar(255) NOT NULL,
  `unique_qr_code` varchar(100) DEFAULT NULL,
  `payment_status` enum('Pending','Verified','Short','Invalid') DEFAULT 'Pending',
  `attendance_status` enum('Not Arrived','Arrived') DEFAULT 'Not Arrived',
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `qr_token` varchar(100) DEFAULT NULL,
  `is_scanned` tinyint(1) DEFAULT 0,
  `scan_timestamp` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `event_modules`
--
ALTER TABLE `event_modules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_settings`
--
ALTER TABLE `event_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `registrants`
--
ALTER TABLE `registrants`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `event_modules`
--
ALTER TABLE `event_modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `registrants`
--
ALTER TABLE `registrants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
