-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 08, 2025 at 01:31 PM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `capstone`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`username`, `password`, `service_table`, `reset_token`, `reset_expires`) VALUES
('assessmentadmin', '$2y$10$9KsXC5Pphl6iB1bx7RlGxObSvYg/4NDJcmx9i6LLOCzKkjHvG3XGm', 'assessment_window', NULL, NULL),
('postingadmin', '$2y$10$ZMji45owxLeV/x9L5dlUOOHCsXAtR.07B9u2WlBmYsqHXinKkqmk.', 'posting_unholding_account', NULL, NULL),
('othersadmin', '$2y$10$iVI1ZvkquZdZyD4gjZ8Htu7B9A2H8pIv8/tE5NmYG6FAKjXvleD9S', 'other_service', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `assessment_window`
--

CREATE TABLE `assessment_window` (
  `queuenumber` int(11) NOT NULL,
  `name` text NOT NULL,
  `contactnumber` varchar(20) NOT NULL,
  `sms_sent` tinyint(1) DEFAULT 0,
  `sms_sent_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `assessment_window`
--

INSERT INTO `assessment_window` (`queuenumber`, `name`, `contactnumber`, `sms_sent`, `sms_sent_time`) VALUES
(1, 'Bryan Lazaro', '09167694623', 0, NULL),
(2, 'Marie Bihag', '09771057265', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `other_service`
--

CREATE TABLE `other_service` (
  `queuenumber` int(11) NOT NULL,
  `name` text NOT NULL,
  `contactnumber` varchar(20) NOT NULL,
  `sms_sent` tinyint(1) DEFAULT 0,
  `sms_sent_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `posting_unholding_account`
--

CREATE TABLE `posting_unholding_account` (
  `queuenumber` int(11) NOT NULL,
  `name` text NOT NULL,
  `contactnumber` varchar(20) NOT NULL,
  `sms_sent` tinyint(1) DEFAULT 0,
  `sms_sent_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assessment_window`
--
ALTER TABLE `assessment_window`
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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assessment_window`
--
ALTER TABLE `assessment_window`
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
  MODIFY `queuenumber` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
