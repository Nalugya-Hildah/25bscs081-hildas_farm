-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 17, 2026 at 10:41 AM
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
-- Database: `hildas_poultry_farm`
--

-- --------------------------------------------------------

--
-- Table structure for table `breeds`
--

CREATE TABLE `breeds` (
  `breed_id` int(10) UNSIGNED NOT NULL,
  `breed_name` varchar(100) NOT NULL,
  `category` enum('broiler','layer','dual_purpose','turkey','duck','other') NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `breeds`
--

INSERT INTO `breeds` (`breed_id`, `breed_name`, `category`, `description`, `created_at`) VALUES
(1, 'Rhode Island Red', 'layer', NULL, '2026-03-12 13:13:31'),
(2, 'Ross 308', 'broiler', NULL, '2026-03-12 13:13:31'),
(3, 'Kuroiler', 'dual_purpose', NULL, '2026-03-12 13:13:31'),
(4, 'Black Australorp', 'layer', NULL, '2026-03-12 13:13:31'),
(5, 'Cornish Cross', 'broiler', NULL, '2026-03-12 13:13:31');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `customer_type` enum('individual','wholesale','retailer','restaurant','other') NOT NULL DEFAULT 'individual',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `egg_production`
--

CREATE TABLE `egg_production` (
  `production_id` int(10) UNSIGNED NOT NULL,
  `flock_id` int(10) UNSIGNED NOT NULL,
  `recorded_by` int(10) UNSIGNED NOT NULL,
  `record_date` date NOT NULL,
  `eggs_collected` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `cracked_eggs` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `dirty_eggs` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `expense_id` int(10) UNSIGNED NOT NULL,
  `expense_date` date NOT NULL,
  `category` enum('utilities','labor','equipment','maintenance','transport','other') NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `paid_to` varchar(150) DEFAULT NULL,
  `recorded_by` int(10) UNSIGNED NOT NULL,
  `receipt_no` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feed_inventory`
--

CREATE TABLE `feed_inventory` (
  `inventory_id` int(10) UNSIGNED NOT NULL,
  `feed_id` int(10) UNSIGNED NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `supplier` varchar(150) DEFAULT NULL,
  `purchase_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `batch_no` varchar(50) DEFAULT NULL,
  `added_by` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feed_inventory`
--

INSERT INTO `feed_inventory` (`inventory_id`, `feed_id`, `quantity`, `unit_cost`, `supplier`, `purchase_date`, `expiry_date`, `batch_no`, `added_by`, `created_at`) VALUES
(1, 3, 50.00, 1800.00, 'Kafiika', '2026-03-17', '2012-09-17', '', 1, '2026-03-17 08:51:10');

-- --------------------------------------------------------

--
-- Table structure for table `feed_types`
--

CREATE TABLE `feed_types` (
  `feed_id` int(10) UNSIGNED NOT NULL,
  `feed_name` varchar(100) NOT NULL,
  `category` enum('starter','grower','finisher','layer','breeder','supplement') NOT NULL,
  `unit` varchar(20) NOT NULL DEFAULT 'kg',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feed_types`
--

INSERT INTO `feed_types` (`feed_id`, `feed_name`, `category`, `unit`, `description`, `created_at`) VALUES
(1, 'Chick Starter', 'starter', 'kg', NULL, '2026-03-12 13:13:31'),
(2, 'Grower Mash', 'grower', 'kg', NULL, '2026-03-12 13:13:31'),
(3, 'Layer Mash', 'layer', 'kg', NULL, '2026-03-12 13:13:31'),
(4, 'Broiler Finisher', 'finisher', 'kg', NULL, '2026-03-12 13:13:31'),
(5, 'Vitamin Supplement', 'supplement', 'litre', NULL, '2026-03-12 13:13:31');

-- --------------------------------------------------------

--
-- Table structure for table `feed_usage`
--

CREATE TABLE `feed_usage` (
  `usage_id` int(10) UNSIGNED NOT NULL,
  `flock_id` int(10) UNSIGNED NOT NULL,
  `feed_id` int(10) UNSIGNED NOT NULL,
  `recorded_by` int(10) UNSIGNED NOT NULL,
  `usage_date` date NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `flocks`
--

CREATE TABLE `flocks` (
  `flock_id` int(10) UNSIGNED NOT NULL,
  `flock_name` varchar(100) NOT NULL,
  `breed_id` int(10) UNSIGNED NOT NULL,
  `pen_id` int(10) UNSIGNED NOT NULL,
  `date_acquired` date NOT NULL,
  `initial_count` int(10) UNSIGNED NOT NULL,
  `current_count` int(10) UNSIGNED NOT NULL,
  `source` varchar(150) DEFAULT NULL,
  `acquisition_cost` decimal(12,2) DEFAULT 0.00,
  `status` enum('active','sold','culled','transferred') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `flocks`
--

INSERT INTO `flocks` (`flock_id`, `flock_name`, `breed_id`, `pen_id`, `date_acquired`, `initial_count`, `current_count`, `source`, `acquisition_cost`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'LAYERS', 4, 1, '2012-09-17', 147, 147, 'UGACHICK', 4200.00, 'sold', '', '2026-03-17 08:28:41', '2026-03-17 08:28:41');

-- --------------------------------------------------------

--
-- Table structure for table `health_records`
--

CREATE TABLE `health_records` (
  `health_id` int(10) UNSIGNED NOT NULL,
  `flock_id` int(10) UNSIGNED NOT NULL,
  `medication_id` int(10) UNSIGNED NOT NULL,
  `recorded_by` int(10) UNSIGNED NOT NULL,
  `treatment_date` date NOT NULL,
  `quantity_used` decimal(10,2) NOT NULL,
  `cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reason` text DEFAULT NULL,
  `administered_by` varchar(100) DEFAULT NULL,
  `next_due_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medications`
--

CREATE TABLE `medications` (
  `medication_id` int(10) UNSIGNED NOT NULL,
  `med_name` varchar(150) NOT NULL,
  `med_type` enum('vaccine','antibiotic','vitamin','dewormer','supplement','other') NOT NULL,
  `unit` varchar(30) NOT NULL DEFAULT 'ml',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mortality`
--

CREATE TABLE `mortality` (
  `mortality_id` int(10) UNSIGNED NOT NULL,
  `flock_id` int(10) UNSIGNED NOT NULL,
  `recorded_by` int(10) UNSIGNED NOT NULL,
  `record_date` date NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `cause` enum('disease','injury','predator','unknown','culled') NOT NULL DEFAULT 'unknown',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pens`
--

CREATE TABLE `pens` (
  `pen_id` int(10) UNSIGNED NOT NULL,
  `pen_name` varchar(50) NOT NULL,
  `capacity` int(10) UNSIGNED NOT NULL,
  `pen_type` enum('broiler','layer','breeder','quarantine','other') NOT NULL,
  `status` enum('active','inactive','maintenance') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pens`
--

INSERT INTO `pens` (`pen_id`, `pen_name`, `capacity`, `pen_type`, `status`, `notes`, `created_at`) VALUES
(1, 'Pen A', 500, 'layer', 'active', NULL, '2026-03-12 13:13:31'),
(2, 'Pen B', 300, 'broiler', 'active', NULL, '2026-03-12 13:13:31'),
(3, 'Pen C', 200, 'layer', 'active', NULL, '2026-03-12 13:13:31'),
(4, 'Pen D', 100, 'quarantine', 'active', NULL, '2026-03-12 13:13:31');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `sold_by` int(10) UNSIGNED NOT NULL,
  `sale_date` date NOT NULL,
  `sale_type` enum('eggs','live_birds','dressed_birds','manure','other') NOT NULL,
  `quantity` decimal(12,2) NOT NULL,
  `unit` varchar(30) NOT NULL DEFAULT 'pieces',
  `unit_price` decimal(10,2) NOT NULL,
  `total_amount` decimal(12,2) GENERATED ALWAYS AS (`quantity` * `unit_price`) STORED,
  `payment_status` enum('paid','partial','pending') NOT NULL DEFAULT 'paid',
  `amount_paid` decimal(12,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `flock_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `job_title` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `status` enum('active','inactive','resigned') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','manager','staff') NOT NULL DEFAULT 'staff',
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `username`, `password_hash`, `role`, `email`, `phone`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Hilda (Farm Owner)', 'admin', '$2a$12$UG9fa9giJ.wG7wEAkT3ap.R7aU3Y.7RvC6rLnx67edaK2PxJc4XI.', 'admin', 'hilda@poultry.local', NULL, 1, '2026-03-12 13:13:31', '2026-03-12 13:13:31');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_daily_egg_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_daily_egg_summary` (
`record_date` date
,`flock_name` varchar(100)
,`breed_name` varchar(100)
,`pen_name` varchar(50)
,`eggs_collected` int(10) unsigned
,`cracked_eggs` int(10) unsigned
,`dirty_eggs` int(10) unsigned
,`saleable_eggs` bigint(12) unsigned
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_flock_overview`
-- (See below for the actual view)
--
CREATE TABLE `v_flock_overview` (
`flock_id` int(10) unsigned
,`flock_name` varchar(100)
,`breed_name` varchar(100)
,`bird_type` enum('broiler','layer','dual_purpose','turkey','duck','other')
,`pen_name` varchar(50)
,`date_acquired` date
,`initial_count` int(10) unsigned
,`current_count` int(10) unsigned
,`total_mortality` bigint(11) unsigned
,`status` enum('active','sold','culled','transferred')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_monthly_sales`
-- (See below for the actual view)
--
CREATE TABLE `v_monthly_sales` (
`month` varchar(7)
,`sale_type` enum('eggs','live_birds','dressed_birds','manure','other')
,`num_transactions` bigint(21)
,`total_qty` decimal(34,2)
,`gross_revenue` decimal(34,2)
,`amount_collected` decimal(34,2)
);

-- --------------------------------------------------------

--
-- Structure for view `v_daily_egg_summary`
--
DROP TABLE IF EXISTS `v_daily_egg_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_daily_egg_summary`  AS SELECT `ep`.`record_date` AS `record_date`, `f`.`flock_name` AS `flock_name`, `b`.`breed_name` AS `breed_name`, `p`.`pen_name` AS `pen_name`, `ep`.`eggs_collected` AS `eggs_collected`, `ep`.`cracked_eggs` AS `cracked_eggs`, `ep`.`dirty_eggs` AS `dirty_eggs`, `ep`.`eggs_collected`- `ep`.`cracked_eggs` - `ep`.`dirty_eggs` AS `saleable_eggs` FROM (((`egg_production` `ep` join `flocks` `f` on(`ep`.`flock_id` = `f`.`flock_id`)) join `breeds` `b` on(`f`.`breed_id` = `b`.`breed_id`)) join `pens` `p` on(`f`.`pen_id` = `p`.`pen_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_flock_overview`
--
DROP TABLE IF EXISTS `v_flock_overview`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_flock_overview`  AS SELECT `f`.`flock_id` AS `flock_id`, `f`.`flock_name` AS `flock_name`, `b`.`breed_name` AS `breed_name`, `b`.`category` AS `bird_type`, `p`.`pen_name` AS `pen_name`, `f`.`date_acquired` AS `date_acquired`, `f`.`initial_count` AS `initial_count`, `f`.`current_count` AS `current_count`, `f`.`initial_count`- `f`.`current_count` AS `total_mortality`, `f`.`status` AS `status` FROM ((`flocks` `f` join `breeds` `b` on(`f`.`breed_id` = `b`.`breed_id`)) join `pens` `p` on(`f`.`pen_id` = `p`.`pen_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_monthly_sales`
--
DROP TABLE IF EXISTS `v_monthly_sales`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_monthly_sales`  AS SELECT date_format(`sales`.`sale_date`,'%Y-%m') AS `month`, `sales`.`sale_type` AS `sale_type`, count(0) AS `num_transactions`, sum(`sales`.`quantity`) AS `total_qty`, sum(`sales`.`total_amount`) AS `gross_revenue`, sum(`sales`.`amount_paid`) AS `amount_collected` FROM `sales` GROUP BY date_format(`sales`.`sale_date`,'%Y-%m'), `sales`.`sale_type` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `breeds`
--
ALTER TABLE `breeds`
  ADD PRIMARY KEY (`breed_id`),
  ADD UNIQUE KEY `breed_name` (`breed_name`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `egg_production`
--
ALTER TABLE `egg_production`
  ADD PRIMARY KEY (`production_id`),
  ADD KEY `flock_id` (`flock_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`expense_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `feed_inventory`
--
ALTER TABLE `feed_inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `feed_id` (`feed_id`),
  ADD KEY `added_by` (`added_by`);

--
-- Indexes for table `feed_types`
--
ALTER TABLE `feed_types`
  ADD PRIMARY KEY (`feed_id`),
  ADD UNIQUE KEY `feed_name` (`feed_name`);

--
-- Indexes for table `feed_usage`
--
ALTER TABLE `feed_usage`
  ADD PRIMARY KEY (`usage_id`),
  ADD KEY `flock_id` (`flock_id`),
  ADD KEY `feed_id` (`feed_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `flocks`
--
ALTER TABLE `flocks`
  ADD PRIMARY KEY (`flock_id`),
  ADD KEY `breed_id` (`breed_id`),
  ADD KEY `pen_id` (`pen_id`);

--
-- Indexes for table `health_records`
--
ALTER TABLE `health_records`
  ADD PRIMARY KEY (`health_id`),
  ADD KEY `flock_id` (`flock_id`),
  ADD KEY `medication_id` (`medication_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `medications`
--
ALTER TABLE `medications`
  ADD PRIMARY KEY (`medication_id`);

--
-- Indexes for table `mortality`
--
ALTER TABLE `mortality`
  ADD PRIMARY KEY (`mortality_id`),
  ADD KEY `flock_id` (`flock_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `pens`
--
ALTER TABLE `pens`
  ADD PRIMARY KEY (`pen_id`),
  ADD UNIQUE KEY `pen_name` (`pen_name`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `sold_by` (`sold_by`),
  ADD KEY `flock_id` (`flock_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `breeds`
--
ALTER TABLE `breeds`
  MODIFY `breed_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `egg_production`
--
ALTER TABLE `egg_production`
  MODIFY `production_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `expense_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feed_inventory`
--
ALTER TABLE `feed_inventory`
  MODIFY `inventory_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `feed_types`
--
ALTER TABLE `feed_types`
  MODIFY `feed_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `feed_usage`
--
ALTER TABLE `feed_usage`
  MODIFY `usage_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `flocks`
--
ALTER TABLE `flocks`
  MODIFY `flock_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `health_records`
--
ALTER TABLE `health_records`
  MODIFY `health_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medications`
--
ALTER TABLE `medications`
  MODIFY `medication_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mortality`
--
ALTER TABLE `mortality`
  MODIFY `mortality_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pens`
--
ALTER TABLE `pens`
  MODIFY `pen_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `egg_production`
--
ALTER TABLE `egg_production`
  ADD CONSTRAINT `egg_production_ibfk_1` FOREIGN KEY (`flock_id`) REFERENCES `flocks` (`flock_id`),
  ADD CONSTRAINT `egg_production_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `feed_inventory`
--
ALTER TABLE `feed_inventory`
  ADD CONSTRAINT `feed_inventory_ibfk_1` FOREIGN KEY (`feed_id`) REFERENCES `feed_types` (`feed_id`),
  ADD CONSTRAINT `feed_inventory_ibfk_2` FOREIGN KEY (`added_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `feed_usage`
--
ALTER TABLE `feed_usage`
  ADD CONSTRAINT `feed_usage_ibfk_1` FOREIGN KEY (`flock_id`) REFERENCES `flocks` (`flock_id`),
  ADD CONSTRAINT `feed_usage_ibfk_2` FOREIGN KEY (`feed_id`) REFERENCES `feed_types` (`feed_id`),
  ADD CONSTRAINT `feed_usage_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `flocks`
--
ALTER TABLE `flocks`
  ADD CONSTRAINT `flocks_ibfk_1` FOREIGN KEY (`breed_id`) REFERENCES `breeds` (`breed_id`),
  ADD CONSTRAINT `flocks_ibfk_2` FOREIGN KEY (`pen_id`) REFERENCES `pens` (`pen_id`);

--
-- Constraints for table `health_records`
--
ALTER TABLE `health_records`
  ADD CONSTRAINT `health_records_ibfk_1` FOREIGN KEY (`flock_id`) REFERENCES `flocks` (`flock_id`),
  ADD CONSTRAINT `health_records_ibfk_2` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`medication_id`),
  ADD CONSTRAINT `health_records_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `mortality`
--
ALTER TABLE `mortality`
  ADD CONSTRAINT `mortality_ibfk_1` FOREIGN KEY (`flock_id`) REFERENCES `flocks` (`flock_id`),
  ADD CONSTRAINT `mortality_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`sold_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `sales_ibfk_3` FOREIGN KEY (`flock_id`) REFERENCES `flocks` (`flock_id`);

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
