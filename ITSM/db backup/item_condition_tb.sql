-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 16, 2026 at 11:03 AM
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
-- Database: `inventory_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `item_condition_tb`
--

CREATE TABLE `item_condition_tb` (
  `condition_id` int(11) NOT NULL,
  `cond` varchar(30) NOT NULL,
  `description` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_condition_tb`
--

INSERT INTO `item_condition_tb` (`condition_id`, `cond`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Good', 'All Working', '2026-03-14 15:41:51', '2026-03-14 16:40:46'),
(2, 'Slightly Damaged', 'Slightly Damaged', '2026-03-14 15:41:51', '2026-03-14 16:40:46'),
(3, 'Defective', 'Defective', '2026-03-14 15:42:15', '2026-03-14 16:41:54'),
(4, 'For Disposal', 'For Disposal', '2026-03-14 15:42:15', '2026-03-14 16:41:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `item_condition_tb`
--
ALTER TABLE `item_condition_tb`
  ADD PRIMARY KEY (`condition_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `item_condition_tb`
--
ALTER TABLE `item_condition_tb`
  MODIFY `condition_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
