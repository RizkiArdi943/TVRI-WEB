-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql109.infinityfree.com
-- Generation Time: Oct 29, 2025 at 08:18 AM
-- Server version: 11.4.7-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_40253179_sipetra`
--

-- --------------------------------------------------------

--
-- Table structure for table `cases`
--

CREATE TABLE `cases` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `equipment_name` varchar(255) NOT NULL,
  `model` varchar(255) DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `damage_date` date NOT NULL,
  `location` varchar(100) NOT NULL,
  `damage_condition` enum('light','moderate','severe') DEFAULT 'light',
  `category_id` int(11) NOT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `reported_by` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `id_laporan` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cases`
--

INSERT INTO `cases` (`id`, `title`, `description`, `equipment_name`, `model`, `serial_number`, `damage_date`, `location`, `damage_condition`, `category_id`, `status`, `priority`, `reported_by`, `assigned_to`, `image_path`, `created_at`, `updated_at`, `id_laporan`) VALUES
(2, 'Pemancar Ambruk', 'Di daerah Kalimantan Tengah Sampit Pemancar Ambruk', 'Antena', 'BT-001', '21678DAS', '2025-10-26', 'Transmisi Sampit', 'severe', 1, 'pending', 'medium', 1, NULL, 'case_68fd10b9037a01.15456469.jpg', '2025-10-26 08:02:33', '2025-10-26 08:02:33', 'SIPETRA-SMT-20251026-001'),
(3, 'Kerusakan pada IRD', 'Terjadi kerusakan pada IRD di transmisi', 'IRD', 'Thomson', '-', '2025-10-29', 'Transmisi Palangkaraya', 'light', 1, 'pending', 'medium', 1, NULL, 'case_690182436f7353.14843152.png', '2025-10-29 16:56:03', '2025-10-29 16:56:03', 'SIPETRA-PLK-20251029-001'),
(4, 'Kerusakan IRD', 'IRD Terjadi Blank', 'IRD', 'Thomson', '-', '2025-10-29', 'Transmisi Palangkaraya', 'light', 1, 'pending', 'medium', 1, NULL, 'case_690188023209b0.35306373.jpg', '2025-10-29 17:20:34', '2025-10-29 17:20:34', 'SIPETRA-PLK-20251029-002');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#3B82F6',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `color`, `created_at`, `updated_at`) VALUES
(1, 'Transmisi', '#3B82F6', '2025-08-30 21:42:58', '2025-08-30 21:42:58'),
(2, 'Studio', '#10B981', '2025-08-30 21:42:58', '2025-08-30 21:42:58'),
(3, 'Perangkat', '#F59E0B', '2025-08-30 21:42:58', '2025-08-30 21:42:58'),
(4, 'Jaringan', '#EF4444', '2025-08-30 21:42:58', '2025-08-30 21:42:58'),
(5, 'Lainnya', '#8B5CF6', '2025-08-30 21:42:58', '2025-08-30 21:42:58');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'system_name', 'TVRI Kalimantan Tengah', 'Nama sistem', '2025-08-30 21:42:58', '2025-08-30 21:42:58'),
(2, 'system_version', '1.0.0', 'Versi aplikasi', '2025-08-30 21:42:58', '2025-08-30 21:42:58');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `employee_id` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `employee_id`, `department`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$ckvqdHe6Re8l5z3N/gdN7u/Cqoa/uRfLli9xixIwfXb0LzKHqPecO', 'Administrator', 'admin@tvri.id', 'ADMIN001', 'Administrasi', 'admin', '2025-08-30 21:42:58', '2025-08-30 23:26:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cases`
--
ALTER TABLE `cases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_reported_by` (`reported_by`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cases`
--
ALTER TABLE `cases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cases`
--
ALTER TABLE `cases`
  ADD CONSTRAINT `cases_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cases_ibfk_2` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cases_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
