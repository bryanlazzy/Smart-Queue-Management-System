-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Feb 04, 2026 at 08:07 AM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u737260546_capstone`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `username` text NOT NULL,
  `password` varchar(255) NOT NULL,
  `service_table` varchar(255) NOT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`username`, `password`, `service_table`, `reset_token`, `reset_expires`) VALUES
('assessmentadmin', '$2y$10$vwTlJY/oLIQZmwlPRsHJYekaUm4WhngEVMJmy62yo.oYxDvgNzat.', 'assessment_window', '7199373e1d65bb3a43174e9d8accff210cdb40086f2b7a41e85563c7d92c0c64', '2026-01-11 13:16:54'),
('postingadmin', '$2y$10$ZMji45owxLeV/x9L5dlUOOHCsXAtR.07B9u2WlBmYsqHXinKkqmk.', 'posting_unholding_account', NULL, NULL),
('othersadmin', '$2y$10$iVI1ZvkquZdZyD4gjZ8Htu7B9A2H8pIv8/tE5NmYG6FAKjXvleD9S', 'other_service', NULL, NULL),
('cashieradmin', '$2y$10$iVI1ZvkquZdZyD4gjZ8Htu7B9A2H8pIv8/tE5NmYG6FAKjXvleD9S', 'cashier_service', NULL, NULL),
('registrationadmin', '$2y$10$zCJYBSohpO5GV4rdWtq32eIbwwQto95cKG3gCq6WYMdsX8SQBiga.', 'all_services', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `assessment_window`
--

CREATE TABLE `assessment_window` (
  `queuenumber` int(11) NOT NULL,
  `name` text NOT NULL,
  `contactnumber` varchar(20) NOT NULL,
  `sms_sent` tinyint(1) DEFAULT 0,
  `sms_sent_time` datetime DEFAULT NULL,
  `sms_registered_sent` tinyint(1) DEFAULT 0,
  `sms_near_sent` tinyint(1) DEFAULT 0,
  `sms_next_sent` tinyint(1) DEFAULT 0,
  `current_serving` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `assessment_window`
--

INSERT INTO `assessment_window` (`queuenumber`, `name`, `contactnumber`, `sms_sent`, `sms_sent_time`, `sms_registered_sent`, `sms_near_sent`, `sms_next_sent`, `current_serving`) VALUES
(1, 'Maria Leonora Teresa Penuliar', '09763248920', 0, '2026-02-04 07:52:18', 1, 0, 0, 1),
(2, 'Marie Kazser Z Bihag', '09167694623', 0, '2026-02-04 07:54:42', 1, 0, 0, 0),
(3, 'Isaac Kurt Rudy Garcia Ada', '09763037867', 0, '2026-02-04 07:56:50', 1, 0, 0, 0),
(4, 'Patrick Miguel Requita Agbon', '09561800496', 0, '2026-02-04 07:57:48', 1, 0, 0, 0),
(5, 'Ronald Bahia', '09198264248', 0, '2026-02-04 07:58:45', 1, 0, 0, 0),
(6, 'Charmaine Gonzales', '09568320399', 0, '2026-02-04 07:59:55', 1, 0, 0, 0),
(7, 'Jeramie Apple Beratio Digno', '09175892408', 0, '2026-02-04 08:01:09', 1, 0, 0, 0),
(8, 'Joanna Marie Beratio Penuliar', '09267422421', 0, '2026-02-04 08:01:38', 1, 0, 0, 0),
(9, 'Phoenix Bradley Rivera', '09606813386', 0, '2026-02-04 08:06:47', 1, 0, 0, 0),
(10, 'Juan Dela Cruz Random Testing', '09645959227', 0, '2026-02-04 08:07:06', 1, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `cashier_service`
--

CREATE TABLE `cashier_service` (
  `queuenumber` int(11) NOT NULL,
  `name` text NOT NULL,
  `contactnumber` varchar(20) NOT NULL,
  `sms_sent` tinyint(1) DEFAULT 0,
  `sms_sent_time` datetime DEFAULT NULL,
  `sms_registered_sent` tinyint(1) DEFAULT 0,
  `sms_near_sent` tinyint(1) DEFAULT 0,
  `sms_next_sent` tinyint(1) DEFAULT 0,
  `current_serving` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `cashier_service`
--

INSERT INTO `cashier_service` (`queuenumber`, `name`, `contactnumber`, `sms_sent`, `sms_sent_time`, `sms_registered_sent`, `sms_near_sent`, `sms_next_sent`, `current_serving`) VALUES
(1, 'Wednesday Trial', '09763248920', 0, '2026-02-04 07:50:00', 1, 0, 0, 1),
(2, 'hello', '09763108813', 0, '2026-02-04 08:03:41', 1, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `other_service`
--

CREATE TABLE `other_service` (
  `queuenumber` int(11) NOT NULL,
  `name` text NOT NULL,
  `contactnumber` varchar(20) NOT NULL,
  `sms_sent` tinyint(1) DEFAULT 0,
  `sms_sent_time` datetime DEFAULT NULL,
  `sms_registered_sent` tinyint(1) DEFAULT 0,
  `sms_near_sent` tinyint(1) DEFAULT 0,
  `sms_next_sent` tinyint(1) DEFAULT 0,
  `current_serving` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posting_unholding_account`
--

CREATE TABLE `posting_unholding_account` (
  `queuenumber` int(11) NOT NULL,
  `name` text NOT NULL,
  `contactnumber` varchar(20) NOT NULL,
  `sms_sent` tinyint(1) DEFAULT 0,
  `sms_sent_time` datetime DEFAULT NULL,
  `sms_registered_sent` tinyint(1) DEFAULT 0,
  `sms_near_sent` tinyint(1) DEFAULT 0,
  `sms_next_sent` tinyint(1) DEFAULT 0,
  `current_serving` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `posting_unholding_account`
--

INSERT INTO `posting_unholding_account` (`queuenumber`, `name`, `contactnumber`, `sms_sent`, `sms_sent_time`, `sms_registered_sent`, `sms_near_sent`, `sms_next_sent`, `current_serving`) VALUES
(1, 'Pepito Lucio Juanito', '09064250493', 0, '2026-02-04 07:51:29', 1, 0, 0, 1),
(2, 'Marie Bihag', '09771057265', 0, '2026-02-04 07:56:36', 1, 0, 0, 0),
(3, 'cath reyes', '09763108813', 0, '2026-02-04 07:59:29', 1, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_queue_tracking`
--

CREATE TABLE `user_queue_tracking` (
  `id` int(11) NOT NULL,
  `contactnumber` varchar(20) NOT NULL,
  `service_table` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `user_queue_tracking`
--

INSERT INTO `user_queue_tracking` (`id`, `contactnumber`, `service_table`, `created_at`) VALUES
(202, '09763248920', 'cashier_service', '2026-02-04 07:49:48'),
(203, '09064250493', 'posting_unholding_account', '2026-02-04 07:51:17'),
(204, '09763248920', 'assessment_window', '2026-02-04 07:52:08'),
(205, '09167694623', 'assessment_window', '2026-02-04 07:54:30'),
(206, '09771057265', 'posting_unholding_account', '2026-02-04 07:56:24'),
(207, '09763037867', 'assessment_window', '2026-02-04 07:56:37'),
(208, '09561800496', 'assessment_window', '2026-02-04 07:57:35'),
(209, '09198264248', 'assessment_window', '2026-02-04 07:58:34'),
(210, '09763108813', 'posting_unholding_account', '2026-02-04 07:59:14'),
(211, '09568320399', 'assessment_window', '2026-02-04 07:59:45'),
(212, '09175892408', 'assessment_window', '2026-02-04 08:00:57'),
(213, '09267422421', 'assessment_window', '2026-02-04 08:01:27'),
(214, '09763108813', 'cashier_service', '2026-02-04 08:03:30'),
(215, '09606813386', 'assessment_window', '2026-02-04 08:06:35'),
(216, '09645959227', 'assessment_window', '2026-02-04 08:06:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assessment_window`
--
ALTER TABLE `assessment_window`
  ADD PRIMARY KEY (`queuenumber`);

--
-- Indexes for table `cashier_service`
--
ALTER TABLE `cashier_service`
  ADD PRIMARY KEY (`queuenumber`);

--
-- Indexes for table `other_service`
--
ALTER TABLE `other_service`
  ADD PRIMARY KEY (`queuenumber`);

--
-- Indexes for table `posting_unholding_account`
--
ALTER TABLE `posting_unholding_account`
  ADD PRIMARY KEY (`queuenumber`);

--
-- Indexes for table `user_queue_tracking`
--
ALTER TABLE `user_queue_tracking`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_contact_service` (`contactnumber`,`service_table`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assessment_window`
--
ALTER TABLE `assessment_window`
  MODIFY `queuenumber` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `cashier_service`
--
ALTER TABLE `cashier_service`
  MODIFY `queuenumber` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `other_service`
--
ALTER TABLE `other_service`
  MODIFY `queuenumber` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `posting_unholding_account`
--
ALTER TABLE `posting_unholding_account`
  MODIFY `queuenumber` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_queue_tracking`
--
ALTER TABLE `user_queue_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=217;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
