-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 16, 2026 at 11:05 AM
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
-- Table structure for table `laptop_pc_specs`
--

CREATE TABLE `laptop_pc_specs` (
  `spec_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `cpu` varchar(255) DEFAULT NULL,
  `ram` varchar(255) DEFAULT NULL,
  `rom` varchar(255) DEFAULT NULL,
  `motherboard` varchar(255) DEFAULT NULL,
  `os` varchar(255) DEFAULT NULL,
  `key` varchar(255) DEFAULT NULL,
  `antivirus` varchar(255) DEFAULT NULL,
  `comp_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laptop_pc_specs`
--

INSERT INTO `laptop_pc_specs` (`spec_id`, `item_id`, `cpu`, `ram`, `rom`, `motherboard`, `os`, `key`, `antivirus`, `comp_name`) VALUES
(7, 210, 'Intel(R) Core(TM) i5-10310u CPU@1.70GHz', '16GB DDR4 2667 MT/s', 'NVMe 512GB SSD', '-', ' WIN 11 PRO 64-bit (10.0, Build26100)', '-', '-', '-');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `laptop_pc_specs`
--
ALTER TABLE `laptop_pc_specs`
  ADD PRIMARY KEY (`spec_id`),
  ADD KEY `item_id` (`item_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `laptop_pc_specs`
--
ALTER TABLE `laptop_pc_specs`
  MODIFY `spec_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `laptop_pc_specs`
--
ALTER TABLE `laptop_pc_specs`
  ADD CONSTRAINT `laptop_pc_specs_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `item_tb` (`item_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
