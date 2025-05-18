-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 18, 2025 at 12:11 PM
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
-- Database: `production_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_batches`
--

CREATE TABLE `tbl_batches` (
  `batch_id` int(6) NOT NULL,
  `recipe_id` int(6) NOT NULL,
  `schedule_id` int(6) NOT NULL,
  `batch_startTime` datetime NOT NULL DEFAULT current_timestamp(),
  `batch_endTime` datetime NOT NULL,
  `batch_status` enum('Pending','In Progress','Completed','') NOT NULL DEFAULT 'Pending',
  `batch_remarks` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_batches`
--

INSERT INTO `tbl_batches` (`batch_id`, `recipe_id`, `schedule_id`, `batch_startTime`, `batch_endTime`, `batch_status`, `batch_remarks`) VALUES
(4, 4, 8, '2025-01-13 16:43:00', '2025-01-13 19:43:00', 'Completed', ''),
(5, 4, 8, '2025-01-14 17:11:00', '2025-01-14 19:11:00', 'Completed', ''),
(6, 4, 8, '2025-01-15 19:13:00', '2025-01-15 20:13:00', 'Completed', ''),
(8, 4, 8, '2025-01-13 09:08:00', '2025-01-13 09:08:00', 'Completed', ''),
(9, 5, 9, '2025-04-21 22:14:00', '2025-04-22 22:14:00', 'Pending', 'blackout'),
(10, 15, 12, '2025-05-17 19:43:00', '2025-05-21 19:43:00', 'Pending', ''),
(11, 2, 7, '2025-05-17 22:40:00', '2025-05-20 22:40:00', 'Pending', ''),
(12, 5, 9, '2025-05-23 22:42:00', '2025-05-24 22:42:00', 'Completed', '');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_batch_assignments`
--

CREATE TABLE `tbl_batch_assignments` (
  `ba_id` int(6) NOT NULL,
  `batch_id` int(6) NOT NULL,
  `user_id` int(6) NOT NULL,
  `ba_task` varchar(255) NOT NULL,
  `ba_status` enum('Pending','In Progress','Completed','') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_batch_assignments`
--

INSERT INTO `tbl_batch_assignments` (`ba_id`, `batch_id`, `user_id`, `ba_task`, `ba_status`) VALUES
(15, 6, 17, 'Mixing', 'Pending'),
(16, 5, 15, 'Mixing', 'Pending'),
(17, 5, 16, 'Baking', 'Pending'),
(18, 5, 20, 'Decorating', 'Pending'),
(21, 4, 15, 'Mixing', 'Pending'),
(22, 4, 18, 'Baking', 'Pending'),
(26, 8, 18, 'Mixing', 'Pending'),
(33, 9, 15, 'Mixing', 'Pending'),
(34, 9, 16, 'Baking', 'Pending'),
(35, 10, 23, 'Mixing', 'Pending'),
(36, 10, 18, 'Baking', 'Pending'),
(37, 11, 20, 'Decorating', 'Pending'),
(40, 12, 21, 'Decorating', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_equipments`
--

CREATE TABLE `tbl_equipments` (
  `equipment_id` int(6) NOT NULL,
  `equipment_name` varchar(100) NOT NULL,
  `equipment_description` text NOT NULL,
  `equipment_status` enum('Available','Maintenance','Out of Order') NOT NULL DEFAULT 'Available',
  `equipment_dateAdded` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_equipments`
--

INSERT INTO `tbl_equipments` (`equipment_id`, `equipment_name`, `equipment_description`, `equipment_status`, `equipment_dateAdded`) VALUES
(1, 'Mixer A', 'Industrial dough mixer - 50L capacity', '', '2025-01-10 13:37:17'),
(2, 'Mixer B', 'Industrial dough mixer - 100L capacity', 'Available', '2025-01-10 13:37:17'),
(3, 'Oven 1', 'Deck oven - 3 decks', '', '2025-01-10 13:38:05'),
(4, 'Oven 2', 'Convection oven', 'Available', '2025-01-10 13:38:05'),
(5, 'Proofer 1', 'Walk-in proofer', '', '2025-01-10 13:39:53'),
(6, 'Sheeter 1', 'Dough sheeter - medium capacity', 'Available', '2025-01-10 13:39:53');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_ingredients`
--

CREATE TABLE `tbl_ingredients` (
  `ingredient_id` int(6) NOT NULL,
  `recipe_id` int(6) NOT NULL,
  `ingredient_name` varchar(200) NOT NULL,
  `ingredient_quantity` int(6) NOT NULL,
  `ingredient_unitOfMeasure` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_ingredients`
--

INSERT INTO `tbl_ingredients` (`ingredient_id`, `recipe_id`, `ingredient_name`, `ingredient_quantity`, `ingredient_unitOfMeasure`) VALUES
(17, 9, 'Sugar', 200, 'g'),
(18, 9, 'Cocoa Powder', 100, 'g'),
(19, 9, 'Eggs', 4, 'pcs'),
(20, 9, 'Butter', 250, 'g'),
(62, 1, 'Bread Flour', 500, 'g'),
(63, 1, 'Active Dry Yeast', 7, 'g'),
(64, 1, 'Salt', 10, 'g'),
(65, 1, 'Warm Water', 350, 'ml'),
(66, 2, 'All-Purpose Flour', 500, 'g'),
(67, 2, 'Butter', 250, 'g'),
(68, 2, 'Sugar', 50, 'g'),
(69, 2, 'Salt', 10, 'g'),
(70, 2, 'Active Dry Yeast', 7, 'g'),
(71, 2, 'Milk', 200, 'ml'),
(72, 2, 'Dark Chocolate', 200, 'g'),
(73, 3, 'Cake Flour', 150, 'g'),
(74, 3, 'Eggs', 6, 'pcs'),
(75, 3, 'Sugar', 150, 'g'),
(76, 3, 'Vegetable Oil', 80, 'ml'),
(77, 3, 'Vanilla Extract', 10, 'ml'),
(78, 4, 'Bread Flour', 500, 'g'),
(79, 4, 'Whole Wheat Flour', 100, 'g'),
(80, 4, 'Sourdough Starter', 150, 'g'),
(81, 4, 'Salt', 12, 'g'),
(82, 4, 'Water', 350, 'ml'),
(83, 5, 'All-Purpose Flour', 280, 'g'),
(84, 5, 'Butter', 230, 'g'),
(85, 5, 'Brown Sugar', 200, 'g'),
(86, 5, 'White Sugar', 100, 'g'),
(87, 5, 'Eggs', 2, 'pcs'),
(88, 5, 'Vanilla Extract', 5, 'ml'),
(89, 5, 'Chocolate Chips', 300, 'g'),
(90, 6, 'All-Purpose Flour', 400, 'g'),
(91, 6, 'Butter', 250, 'g'),
(92, 6, 'Sugar', 50, 'g'),
(93, 6, 'Active Dry Yeast', 7, 'g'),
(94, 6, 'Milk', 180, 'ml'),
(95, 6, 'Eggs', 2, 'pcs'),
(96, 6, 'Mixed Fruits', 300, 'g'),
(97, 7, 'Cake Flour', 300, 'g'),
(98, 7, 'Cocoa Powder', 20, 'g'),
(99, 7, 'Butter', 120, 'g'),
(100, 7, 'Sugar', 300, 'g'),
(101, 7, 'Eggs', 3, 'pcs'),
(102, 7, 'Buttermilk', 240, 'ml'),
(103, 7, 'Red Food Coloring', 30, 'ml'),
(104, 7, 'Cream Cheese', 500, 'g'),
(105, 8, 'Whole Wheat Flour', 300, 'g'),
(106, 8, 'Bread Flour', 200, 'g'),
(107, 8, 'Active Dry Yeast', 7, 'g'),
(108, 8, 'Honey', 30, 'ml'),
(109, 8, 'Salt', 10, 'g'),
(110, 8, 'Warm Water', 300, 'ml'),
(111, 9, 'Almond Flour', 200, 'g'),
(112, 9, 'Powdered Sugar', 200, 'g'),
(113, 9, 'Egg Whites', 70, 'g'),
(114, 9, 'Granulated Sugar', 90, 'g'),
(115, 9, 'Food Coloring', 1, 'g'),
(116, 10, 'Almond Flour', 150, 'g'),
(117, 10, 'Powdered Sugar', 150, 'g'),
(118, 10, 'Eggs', 5, 'pcs'),
(119, 10, 'Dark Chocolate', 200, 'g'),
(120, 10, 'Coffee Extract', 30, 'ml'),
(121, 10, 'Butter', 250, 'g'),
(122, 10, 'Heavy Cream', 200, 'ml'),
(127, 13, 'yis', 43, 'g'),
(128, 14, 'yis', 43, 'g'),
(134, 15, 'yis', 43, 'pcs'),
(145, 16, 'TEPUNG ROTI', 500, 'g'),
(146, 16, 'GULA CASTER', 4, 'g'),
(147, 16, 'SUSU TEPUNG', 2, 'g'),
(148, 16, 'AIR', 1, 'l'),
(149, 16, 'BUTTER', 2, 'g'),
(150, 16, 'KRIM JAGUNG', 2, 'l'),
(151, 16, 'TELUR', 1, 'pcs'),
(152, 16, 'GARAM', 12, 'g'),
(153, 16, 'PEWARNA KUNING', 1, 'ml'),
(154, 16, 'YIS', 11, 'g');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_quality_checks`
--

CREATE TABLE `tbl_quality_checks` (
  `qc_id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `production_stage` enum('Mixing','Baking','Cooling','Packaging') NOT NULL,
  `appearance` varchar(255) DEFAULT NULL,
  `texture` varchar(255) DEFAULT NULL,
  `taste_flavour` varchar(255) DEFAULT NULL,
  `shape_size` varchar(255) DEFAULT NULL,
  `packaging` varchar(255) DEFAULT NULL,
  `qc_comments` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_quality_checks`
--

INSERT INTO `tbl_quality_checks` (`qc_id`, `batch_id`, `user_id`, `production_stage`, `appearance`, `texture`, `taste_flavour`, `shape_size`, `packaging`, `qc_comments`, `created_at`) VALUES
(8, 9, 24, 'Mixing', 'Good', 'Soft & Fluffy', 'Excellent Flavour', 'Uniform Shape', 'Properly Packaged', NULL, '2025-04-23 05:50:08'),
(9, 9, 24, 'Mixing', 'Good', 'Soft & Fluffy', 'Excellent Flavour', 'Uniform Shape', 'Damaged Packaged', NULL, '2025-04-24 03:16:40'),
(10, 9, 24, 'Mixing', 'Good', 'Soft & Fluffy', 'Excellent Flavour', 'Uniform Shape', 'Properly Packaged', NULL, '2025-05-17 10:41:05'),
(11, 12, 24, 'Mixing', 'Good', 'Soft & Fluffy', 'Excellent Flavour', 'Uniform Shape', 'Properly Packaged', NULL, '2025-05-17 14:44:44'),
(12, 12, 24, 'Mixing', NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-18 02:43:07');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_recipe`
--

CREATE TABLE `tbl_recipe` (
  `recipe_id` int(6) NOT NULL,
  `recipe_name` varchar(100) NOT NULL,
  `recipe_category` varchar(100) NOT NULL,
  `recipe_batchSize` int(6) NOT NULL,
  `recipe_unitOfMeasure` varchar(50) NOT NULL,
  `recipe_instructions` text NOT NULL,
  `recipe_dateCreated` timestamp NOT NULL DEFAULT current_timestamp(),
  `recipe_dateUpdated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_recipe`
--

INSERT INTO `tbl_recipe` (`recipe_id`, `recipe_name`, `recipe_category`, `recipe_batchSize`, `recipe_unitOfMeasure`, `recipe_instructions`, `recipe_dateCreated`, `recipe_dateUpdated`, `image_path`) VALUES
(1, 'Classic Baguette', 'Bread', 4, 'pcs', '1. Mix flour, yeast, and salt\n2. Add water gradually and knead for 10 minutes\n3. First rise: 1 hour\n4. Shape into baguettes\n5. Second rise: 30 minutes\n6. Score and bake at 230°C for 25 minutes', '2024-12-25 15:00:26', '2024-12-25 15:06:00', NULL),
(2, 'Chocolate Croissant', 'Pastry', 12, 'pcs', '1. Prepare laminated dough\n2. Roll and cut into triangles\n3. Add chocolate batons\n4. Shape and proof for 2 hours\n5. Egg wash and bake at 190°C for 18 minutes', '2024-12-25 15:00:26', '2024-12-25 15:06:00', NULL),
(3, 'Vanilla Chiffon Cake', 'Cake', 1, 'pcs', '1. Separate eggs\n2. Whip egg whites with sugar\n3. Mix wet and dry ingredients\n4. Fold in meringue\n5. Bake in tube pan at 170°C for 45 minutes', '2024-12-25 15:00:26', '2024-12-25 15:06:00', NULL),
(4, 'Sourdough Bread', 'Bread', 2, 'pcs', '1. Feed starter 12 hours before\n2. Mix dough and autolyse\n3. Stretch and fold every 30 minutes\n4. Bulk ferment 4-6 hours\n5. Shape and cold proof overnight\n6. Bake in Dutch oven', '2024-12-25 15:00:26', '2024-12-25 15:06:00', NULL),
(5, 'Chocolate Chip Cookies', 'Cookie', 24, 'pcs', '1. Cream butter and sugars\n2. Add eggs and vanilla\n3. Mix in dry ingredients\n4. Fold in chocolate chips\n5. Scoop and bake at 180°C for 12 minutes', '2024-12-25 15:00:26', '2024-12-25 15:06:00', NULL),
(6, 'Fruit Danish', 'Pastry', 8, 'pcs', '1. Prepare Danish dough\n2. Roll and cut squares\n3. Add pastry cream and fruits\n4. Proof for 1 hour\n5. Bake at 200°C for 15 minutes\n6. Glaze while warm', '2024-12-25 15:00:26', '2024-12-25 15:06:00', NULL),
(7, 'Red Velvet Cake', 'Cake', 1, 'pcs', '1. Mix wet ingredients\n2. Combine dry ingredients\n3. Add red food coloring\n4. Bake layers at 175°C\n5. Prepare cream cheese frosting\n6. Assemble and frost', '2024-12-25 15:00:26', '2024-12-25 15:06:00', NULL),
(8, 'Whole Wheat Bread', 'Bread', 2, 'pcs', '1. Mix flours and yeast\n2. Knead for 15 minutes\n3. First rise: 90 minutes\n4. Shape loaves\n5. Second rise: 45 minutes\n6. Bake at 200°C', '2024-12-25 15:00:26', '2024-12-25 15:06:00', NULL),
(9, 'Macarons', 'Cookie', 24, 'pcs', '1. Age egg whites\n2. Make almond flour mixture\n3. Prepare meringue\n4. Macaronage\n5. Pipe and rest\n6. Bake at 150°C\n7. Fill and mature', '2024-12-25 15:00:26', '2024-12-25 15:06:00', NULL),
(10, 'Opera Cake', 'Cake', 1, 'pcs', '1. Bake Joconde layers\n2. Prepare coffee syrup\n3. Make chocolate ganache\n4. Prepare coffee buttercream\n5. Layer and refrigerate\n6. Glaze with chocolate', '2024-12-25 15:00:26', '2024-12-25 15:06:00', NULL),
(13, 'roti susu', 'Bread', 57, 'pcs', '1.2333', '2025-05-17 06:52:07', '2025-05-17 06:52:07', 'images/recipes/68283217094db_BPME1013 BMC GROUP 2.png'),
(14, 'KEK MARBLE', 'Cake', 40, 'kg', '1. MASUKKAN YIS\r\n2. LETAK DALAM OVEN', '2025-05-17 07:51:19', '2025-05-17 07:51:19', 'images/recipes/68283ff7c8d6c_PROFILE PIC YT BOT.png'),
(15, 'KEK labu', 'Pastry', 40, 'pcs', '1. AIR\r\n2. GARAM', '2025-05-17 07:54:17', '2025-05-17 10:07:28', 'images/recipes/682840a912d17_use case Diagram-USE CASE DIAGRAM.drawio.png'),
(16, 'ROTI JAGUNG', 'Bread', 50, 'pcs', '1. Menggunakan breadmaker:\r\n- Masukkan bahan B terlebih dahulu.\r\n- Masukkan bahan A dan akhirnya yeast (C).\r\n- Pilih fungsi untuk roti manis.\r\n\r\n2. Selepas 2 jam, keluarkan doh dan bulat-bulatkan mengikut saiz yang diingini. Rehatkan selama 1 jam 30 minit sebelum dipanaskan oven. Atau, biarkan di dalam breadmaker sehingga masak.\r\n\r\n3. Bakar pada suhu 150°C selama 20 minit. Angkat dan sapu butter ke atasnya untuk mengekalkan lembutnya roti dan warna yang berkilat.', '2025-05-18 06:11:09', '2025-05-18 06:11:09', 'images/recipes/682979fd618e9_roti jagung.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_schedule`
--

CREATE TABLE `tbl_schedule` (
  `schedule_id` int(6) NOT NULL,
  `recipe_id` int(6) NOT NULL,
  `schedule_date` date NOT NULL,
  `schedule_quantityToProduce` int(6) NOT NULL,
  `schedule_orderVolumn` int(6) NOT NULL,
  `schedule_status` enum('Pending','In Progress','Completed','') NOT NULL DEFAULT 'Pending',
  `schedule_batchNum` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_schedule`
--

INSERT INTO `tbl_schedule` (`schedule_id`, `recipe_id`, `schedule_date`, `schedule_quantityToProduce`, `schedule_orderVolumn`, `schedule_status`, `schedule_batchNum`) VALUES
(6, 1, '2025-01-14', 32, 30, 'Pending', 8),
(7, 2, '2025-01-15', 36, 30, 'In Progress', 3),
(8, 4, '2025-01-16', 10, 10, 'Completed', 5),
(9, 5, '2025-01-13', 48, 40, 'Pending', 2),
(10, 3, '2025-04-25', 32, 32, 'Pending', 32),
(11, 6, '2025-04-25', 32, 30, 'Pending', 4),
(12, 15, '2025-05-17', 80, 78, 'Pending', 2);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_schedule_assignments`
--

CREATE TABLE `tbl_schedule_assignments` (
  `sa_id` int(6) NOT NULL,
  `schedule_id` int(6) NOT NULL,
  `user_id` int(6) NOT NULL,
  `sa_dateAssigned` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_schedule_assignments`
--

INSERT INTO `tbl_schedule_assignments` (`sa_id`, `schedule_id`, `user_id`, `sa_dateAssigned`) VALUES
(22, 7, 14, '2025-01-13 07:31:27'),
(29, 9, 18, '2025-01-13 09:45:56'),
(30, 6, 17, '2025-01-13 12:01:17'),
(31, 6, 14, '2025-01-13 12:01:17'),
(32, 8, 19, '2025-01-13 12:09:58'),
(33, 8, 14, '2025-01-13 12:09:58'),
(34, 10, 15, '2025-04-24 01:40:04'),
(35, 10, 18, '2025-04-24 01:40:04'),
(36, 11, 20, '2025-04-24 03:15:31'),
(37, 11, 14, '2025-04-24 03:15:31'),
(38, 12, 16, '2025-05-17 10:20:49'),
(39, 12, 20, '2025-05-17 10:20:49'),
(40, 12, 21, '2025-05-17 10:20:49');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_schedule_equipment`
--

CREATE TABLE `tbl_schedule_equipment` (
  `se_id` int(6) NOT NULL,
  `schedule_id` int(6) NOT NULL,
  `equipment_id` int(6) NOT NULL,
  `se_dateAssigned` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_schedule_equipment`
--

INSERT INTO `tbl_schedule_equipment` (`se_id`, `schedule_id`, `equipment_id`, `se_dateAssigned`) VALUES
(18, 7, 3, '2025-01-13 15:31:27.236068'),
(19, 7, 5, '2025-01-13 15:31:27.236899'),
(26, 9, 1, '2025-01-13 17:45:56.211112'),
(27, 6, 1, '2025-01-13 20:01:17.208562'),
(28, 6, 3, '2025-01-13 20:01:17.208747'),
(29, 8, 1, '2025-01-13 20:09:58.507312'),
(30, 8, 3, '2025-01-13 20:09:58.507424'),
(31, 10, 1, '2025-04-24 09:40:04.884444'),
(32, 11, 2, '2025-04-24 11:15:31.268948'),
(33, 11, 4, '2025-04-24 11:15:31.269600'),
(34, 11, 5, '2025-04-24 11:15:31.275615'),
(35, 12, 3, '2025-05-17 18:20:49.689435'),
(36, 12, 4, '2025-05-17 18:20:49.689694'),
(37, 12, 5, '2025-05-17 18:20:49.689898');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `user_id` int(6) NOT NULL,
  `user_fullName` varchar(200) NOT NULL,
  `user_contact` varchar(100) NOT NULL,
  `user_address` text NOT NULL,
  `user_email` varchar(200) NOT NULL,
  `user_password` varchar(200) NOT NULL,
  `user_dateRegister` datetime NOT NULL DEFAULT current_timestamp(),
  `user_role` varchar(10) NOT NULL DEFAULT 'Baker',
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`user_id`, `user_fullName`, `user_contact`, `user_address`, `user_email`, `user_password`, `user_dateRegister`, `user_role`, `reset_token`, `token_expiry`) VALUES
(13, 'Admin', '0123456789', 'Roti Sri Bakery', 'Admin@gmail.com', '$2y$10$ydcLcf5duwhxgjXK.k/mBu0ikQTz14zXVDzmcx25BOhUsNifs5QB.', '2024-12-29 16:36:59', 'Admin', NULL, NULL),
(14, 'Alicia', '0123456789', 'Kedah', 'alicia@gmail.com', '$2y$10$7jFzWeYpO8gz8e0Z2qVpkeE6co5taUmyOzqflvvx4g1wldBhV8T8e', '2024-12-29 16:38:47', 'Supervisor', NULL, NULL),
(15, 'Abby', '0123456789', 'Kedah', 'abby@gmail.com', '$2y$10$wHP8eBIK2iFIPznx4cop5ue5wlEBw4HYxU0is3ogQw5DeSLrvA8Re', '2024-12-29 16:39:21', 'Baker', NULL, NULL),
(16, 'Aurora', '0123456789', 'Kedah', 'aurora@gmail.com', '$2y$10$EfeC4D4e6C2XFTUMbJjxXO.MJ2OvHtSwpYGztV.Hh5WxSxwcXdbA.', '2024-12-29 16:40:12', 'Baker', NULL, NULL),
(17, 'Irdina', '0123456789', 'Kedah', 'irdina@gmail.com', '$2y$10$NpmQZqhTTp4YOGvN4DGNkeoKdZ2oiB6UJGrI4f/cCStUpNt2CWbVK', '2024-12-29 16:41:00', 'Baker', NULL, NULL),
(18, 'Alia', '0123456789', 'Kedah', 'alia@gmail.com', '$2y$10$px1H5SauACBIugoCce/aPehQol8A7tS9w6qza4W/LB7OUQv4apSBO', '2024-12-29 16:42:41', 'Baker', NULL, NULL),
(19, 'Yungjie', '0123456789', '1234567890', 'yungjielee@gmail.com', '$2y$10$7kI7/iEFumEnHNWqVdYoHeBlNZjwML.kDhDQ2aAY6mqeb2E3oGvka', '2025-01-13 04:25:43', 'Baker', NULL, NULL),
(20, 'Jaslyn', '0123456789', 'Kedah', 'mylittlepony000101@gmail.com', '$2y$10$XO8Tdo9NeLIafi3nH257/.O1jhHoXel0Mrecfl7Xn/rzCDvp92gyC', '2025-01-13 09:02:27', 'Baker', '39bda9c939952785bec958e4792385ab8905a93f5914298b3b4448da06e8576de1d5112bd1524676528b4144b17b3d49175d', '2025-01-13 09:33:38'),
(21, 'MAYA BINTI ALI', '0123456789', 'JALAN TERUSAN, PARIT BUNTAR', 'maya@gmail.com', '$2y$10$AY23NqhpxzRw59yW1E5DD.XiXMmWlSy.Bcr/JpdAHNULaV9nOnIZy', '2025-03-18 10:10:24', 'Baker', NULL, NULL),
(23, 'Dummy Baker', '0000000000', 'dummy baker', 'dummy@baker.com', '$2y$10$rwKIvhDADIft3QuZ19gZdOUr7Ov9UyCMwRvnuuCNAn78qecyZHFUy', '2025-04-20 12:23:18', 'Baker', NULL, NULL),
(24, 'Dummy Sv', '00000000000', 'Dummy Sv', 'dummy@sv.com', '$2y$10$S0JBBnadQsTz63yqbvEhtuCNL5og9algN4MqpIvL5lDccT0/RdX52', '2025-04-20 12:24:03', 'Supervisor', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_batches`
--
ALTER TABLE `tbl_batches`
  ADD PRIMARY KEY (`batch_id`),
  ADD KEY `recipe_id` (`recipe_id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `tbl_batch_assignments`
--
ALTER TABLE `tbl_batch_assignments`
  ADD PRIMARY KEY (`ba_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `batch_id` (`batch_id`);

--
-- Indexes for table `tbl_equipments`
--
ALTER TABLE `tbl_equipments`
  ADD PRIMARY KEY (`equipment_id`);

--
-- Indexes for table `tbl_ingredients`
--
ALTER TABLE `tbl_ingredients`
  ADD PRIMARY KEY (`ingredient_id`),
  ADD KEY `receipt_id` (`recipe_id`);

--
-- Indexes for table `tbl_quality_checks`
--
ALTER TABLE `tbl_quality_checks`
  ADD PRIMARY KEY (`qc_id`),
  ADD KEY `batch_id` (`batch_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_recipe`
--
ALTER TABLE `tbl_recipe`
  ADD PRIMARY KEY (`recipe_id`);

--
-- Indexes for table `tbl_schedule`
--
ALTER TABLE `tbl_schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `tbl_schedule_assignments`
--
ALTER TABLE `tbl_schedule_assignments`
  ADD PRIMARY KEY (`sa_id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_schedule_equipment`
--
ALTER TABLE `tbl_schedule_equipment`
  ADD PRIMARY KEY (`se_id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_batches`
--
ALTER TABLE `tbl_batches`
  MODIFY `batch_id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_batch_assignments`
--
ALTER TABLE `tbl_batch_assignments`
  MODIFY `ba_id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `tbl_equipments`
--
ALTER TABLE `tbl_equipments`
  MODIFY `equipment_id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_ingredients`
--
ALTER TABLE `tbl_ingredients`
  MODIFY `ingredient_id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- AUTO_INCREMENT for table `tbl_quality_checks`
--
ALTER TABLE `tbl_quality_checks`
  MODIFY `qc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `tbl_recipe`
--
ALTER TABLE `tbl_recipe`
  MODIFY `recipe_id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tbl_schedule`
--
ALTER TABLE `tbl_schedule`
  MODIFY `schedule_id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_schedule_assignments`
--
ALTER TABLE `tbl_schedule_assignments`
  MODIFY `sa_id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `tbl_schedule_equipment`
--
ALTER TABLE `tbl_schedule_equipment`
  MODIFY `se_id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `user_id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_batches`
--
ALTER TABLE `tbl_batches`
  ADD CONSTRAINT `tbl_batches_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `tbl_recipe` (`recipe_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_batches_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `tbl_schedule` (`schedule_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_batch_assignments`
--
ALTER TABLE `tbl_batch_assignments`
  ADD CONSTRAINT `tbl_batch_assignments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_batch_assignments_ibfk_2` FOREIGN KEY (`batch_id`) REFERENCES `tbl_batches` (`batch_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_ingredients`
--
ALTER TABLE `tbl_ingredients`
  ADD CONSTRAINT `tbl_ingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `tbl_recipe` (`recipe_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_quality_checks`
--
ALTER TABLE `tbl_quality_checks`
  ADD CONSTRAINT `tbl_quality_checks_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `tbl_batches` (`batch_id`),
  ADD CONSTRAINT `tbl_quality_checks_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`);

--
-- Constraints for table `tbl_schedule`
--
ALTER TABLE `tbl_schedule`
  ADD CONSTRAINT `tbl_schedule_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `tbl_recipe` (`recipe_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_schedule_assignments`
--
ALTER TABLE `tbl_schedule_assignments`
  ADD CONSTRAINT `tbl_schedule_assignments_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `tbl_schedule` (`schedule_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_schedule_assignments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_schedule_equipment`
--
ALTER TABLE `tbl_schedule_equipment`
  ADD CONSTRAINT `tbl_schedule_equipment_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `tbl_equipments` (`equipment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_schedule_equipment_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `tbl_schedule` (`schedule_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
