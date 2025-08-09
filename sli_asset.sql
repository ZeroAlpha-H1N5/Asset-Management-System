-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2025 at 02:16 AM
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
-- Database: `sli_asset`
--

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `asset_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `site_id` int(11) DEFAULT NULL,
  `asset_brand` varchar(255) NOT NULL,
  `asset_model` varchar(255) NOT NULL,
  `asset_serial_num` varchar(255) NOT NULL,
  `asset_tag` varchar(255) DEFAULT NULL,
  `asset_register_date` date NOT NULL,
  `asset_purchase_date` date DEFAULT NULL,
  `asset_depreciation_period` varchar(50) DEFAULT NULL,
  `asset_purchase_cost` decimal(15,2) DEFAULT NULL,
  `asset_depreciated_cost` decimal(15,2) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT '/SLI_ASSET/assets/default.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`asset_id`, `type_id`, `status_id`, `owner_id`, `site_id`, `asset_brand`, `asset_model`, `asset_serial_num`, `asset_tag`, `asset_register_date`, `asset_purchase_date`, `asset_depreciation_period`, `asset_purchase_cost`, `asset_depreciated_cost`, `image_path`) VALUES
(1, 2, 1, 107, 4, 'Huawei', 'Y7', 'DUB-LXN', 'SLI-PHN-0001', '2025-05-30', '2025-05-30', '5', 1000000.00, 16666.67, '/SLI_ASSET/assets/681c046c04f18_tuna.jpg'),
(4, 2, 1, 110, 1, 'Test', 'Test', 'Test', 'SLI-PHN-0002', '2025-05-30', '2025-05-29', '12', 1000000.00, 6944.44, '/SLI_ASSET/assets/681c08092ed65_tuna.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `asset_status`
--

CREATE TABLE `asset_status` (
  `status_id` int(11) NOT NULL,
  `status_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asset_status`
--

INSERT INTO `asset_status` (`status_id`, `status_name`) VALUES
(1, 'Brand New'),
(2, 'Used'),
(3, 'Operational'),
(4, 'For Repair'),
(5, 'For Disposal');

-- --------------------------------------------------------

--
-- Table structure for table `asset_turnover_log`
--

CREATE TABLE `asset_turnover_log` (
  `turnover_log_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `previous_site_id` int(11) DEFAULT NULL,
  `previous_owner_id` int(11) DEFAULT NULL,
  `site_id` int(11) DEFAULT NULL,
  `turnover_date` date NOT NULL,
  `turnover_reason` varchar(255) NOT NULL,
  `turnover_details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `asset_type`
--

CREATE TABLE `asset_type` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(255) NOT NULL,
  `type_code` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asset_type`
--

INSERT INTO `asset_type` (`type_id`, `type_name`, `type_code`) VALUES
(1, 'Computers', 'COM'),
(2, 'Phones', 'PHN'),
(3, 'Devices', 'DEV'),
(4, 'Material Handling Equipment', 'MHE'),
(5, 'Tables', 'TAB'),
(6, 'Chairs', 'CHR'),
(7, 'Cabinets', 'CAB'),
(8, 'Others', 'ETC');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`) VALUES
(1, 'Business Operations'),
(2, 'Warehouse Operations'),
(3, 'Business Development Management'),
(4, 'Business Excellence & Quality Management System'),
(5, 'Finance and Accounting'),
(6, 'Purchasing Department'),
(7, 'Top Management'),
(8, 'Human Resources Department'),
(9, 'Project Support'),
(10, 'Preventive Maintenance'),
(11, 'IT and Facilities Management');

-- --------------------------------------------------------

--
-- Table structure for table `owners`
--

CREATE TABLE `owners` (
  `owner_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `owner_name` varchar(255) NOT NULL,
  `owner_position` varchar(255) DEFAULT NULL,
  `owner_date_hired` date DEFAULT NULL,
  `owner_phone_num` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owners`
--

INSERT INTO `owners` (`owner_id`, `department_id`, `owner_name`, `owner_position`, `owner_date_hired`, `owner_phone_num`) VALUES
(107, 11, 'Simon Quinzon', 'Intern', '2025-05-30', '0'),
(108, 6, 'Test', 'Test', '2025-06-07', '0'),
(109, 8, 'Test', 'Test', '2025-05-30', '0'),
(110, 9, 'Test', 'Test', '2025-05-30', '0');

-- --------------------------------------------------------

--
-- Table structure for table `site_locations`
--

CREATE TABLE `site_locations` (
  `site_id` int(11) NOT NULL,
  `site_name` varchar(255) NOT NULL,
  `site_region` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_locations`
--

INSERT INTO `site_locations` (`site_id`, `site_name`, `site_region`) VALUES
(1, 'JSU - Cabuyao', 'Luzon'),
(2, 'GMC - Canlubang', 'Luzon'),
(3, 'Multi-Client - Cabuyao', 'Luzon'),
(4, 'Multi-Client - Taguig Dry', 'Luzon'),
(5, 'SLMC Operations', 'Luzon'),
(6, 'Maynilad Operations', 'Luzon'),
(7, 'Sportfit - Santa Mesa', 'Luzon'),
(8, 'SYSU - Pangasinan', 'Luzon'),
(9, 'Multi-Client Balagtas Cold', 'Luzon'),
(10, 'Multi-Client Balagtas Dry', 'Luzon'),
(11, 'GMC - Meycauayan', 'Luzon'),
(12, 'SYSU - Iloilo', 'Visayas'),
(13, 'JSU - Looc', 'Luzon'),
(14, 'JSU - Umapad', 'Visayas'),
(15, 'SYSU - Cebu', 'Visayas'),
(16, '3G - Cebu', 'Visayas'),
(17, 'Multi-Client Cebu', 'Visayas'),
(18, 'Multi-Client Davao', 'Mindanao'),
(19, 'Multi-Client CDO', 'Mindanao');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `role` enum('Admin','User') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `user_email`, `role`) VALUES
(4, 'ASSET_ADMIN', '$2y$10$4.Q5ka6klpeKp4AvNBRZEOhO2fS8ey8CeQFE40BOCtw5t2K2EXjiK', 'zeroalpha415@gmail.com', 'Admin'),
(5, 'ASSET_USER', '$2y$10$MI/BXkpWMvVanCkcFFWnOuh.DTWD25gCGTx/vikMC3aIvT2ClQoz6', 'quinzonsimon@gmail.com', 'User');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`asset_id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `status_id` (`status_id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `assets_ibfk_5` (`site_id`);

--
-- Indexes for table `asset_status`
--
ALTER TABLE `asset_status`
  ADD PRIMARY KEY (`status_id`);

--
-- Indexes for table `asset_turnover_log`
--
ALTER TABLE `asset_turnover_log`
  ADD PRIMARY KEY (`turnover_log_id`),
  ADD KEY `asset_id` (`asset_id`),
  ADD KEY `site_id` (`site_id`),
  ADD KEY `previous_site_id` (`previous_site_id`),
  ADD KEY `previous_owner_id` (`previous_owner_id`);

--
-- Indexes for table `asset_type`
--
ALTER TABLE `asset_type`
  ADD PRIMARY KEY (`type_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `owners`
--
ALTER TABLE `owners`
  ADD PRIMARY KEY (`owner_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `site_locations`
--
ALTER TABLE `site_locations`
  ADD PRIMARY KEY (`site_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `asset_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `asset_turnover_log`
--
ALTER TABLE `asset_turnover_log`
  MODIFY `turnover_log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `owners`
--
ALTER TABLE `owners`
  MODIFY `owner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `asset_type` (`type_id`),
  ADD CONSTRAINT `assets_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `asset_status` (`status_id`),
  ADD CONSTRAINT `assets_ibfk_3` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`owner_id`),
  ADD CONSTRAINT `assets_ibfk_5` FOREIGN KEY (`site_id`) REFERENCES `site_locations` (`site_id`);

--
-- Constraints for table `asset_turnover_log`
--
ALTER TABLE `asset_turnover_log`
  ADD CONSTRAINT `asset_turnover_log_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`asset_id`),
  ADD CONSTRAINT `previous_owner_id` FOREIGN KEY (`previous_owner_id`) REFERENCES `owners` (`owner_id`),
  ADD CONSTRAINT `previous_site_id` FOREIGN KEY (`previous_site_id`) REFERENCES `site_locations` (`site_id`),
  ADD CONSTRAINT `site_id` FOREIGN KEY (`site_id`) REFERENCES `site_locations` (`site_id`);

--
-- Constraints for table `owners`
--
ALTER TABLE `owners`
  ADD CONSTRAINT `owners_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
