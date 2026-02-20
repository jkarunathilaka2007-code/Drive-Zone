-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 10, 2026 at 07:12 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `drivezone_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'System Admin', 'admin@gmail.com', '$2y$10$TNqTjO5XFRyCWQ0u/EZHFeeWpOAhaQQVOswqP/4vjy2sVUd13djkS', '2026-01-07 01:23:32');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `bus_id` int(11) NOT NULL,
  `passenger_id` int(11) NOT NULL,
  `seat_number` varchar(10) NOT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `travel_date` date NOT NULL,
  `status` varchar(20) DEFAULT 'booked',
  `booking_ref` varchar(20) NOT NULL,
  `package_name` varchar(50) DEFAULT NULL,
  `pickup_point` varchar(100) DEFAULT NULL,
  `drop_point` varchar(100) DEFAULT NULL,
  `payment_method` varchar(20) DEFAULT 'cash',
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `bus_id`, `passenger_id`, `seat_number`, `gender`, `travel_date`, `status`, `booking_ref`, `package_name`, `pickup_point`, `drop_point`, `payment_method`, `is_verified`, `created_at`) VALUES
(17, 3, 1, 'A2', 'Male', '2026-01-11', 'booked', 'DZ-58ACBC', 'standard', 'Bandarawela', 'Badulla', 'cash', 0, '2026-01-10 03:19:42');

-- --------------------------------------------------------

--
-- Table structure for table `buses`
--

CREATE TABLE `buses` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  `gear_type` enum('Auto','Manual','Both') NOT NULL,
  `bus_number` varchar(20) NOT NULL,
  `bus_type` varchar(50) NOT NULL,
  `number_of_seats` int(11) NOT NULL,
  `facilities` text DEFAULT NULL,
  `img_front` varchar(255) DEFAULT NULL,
  `img_back` varchar(255) DEFAULT NULL,
  `img_left` varchar(255) DEFAULT NULL,
  `img_right` varchar(255) DEFAULT NULL,
  `img_interior` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `seats_per_row` int(11) DEFAULT 4,
  `total_rows` int(11) DEFAULT 10,
  `seats_per_column` int(11) DEFAULT 2,
  `has_conductor_seat` tinyint(1) DEFAULT 1,
  `last_row_seats` int(11) DEFAULT 5,
  `route_number` varchar(50) DEFAULT NULL,
  `route_start` varchar(100) DEFAULT NULL,
  `route_end` varchar(100) DEFAULT NULL,
  `route_path` text DEFAULT NULL,
  `driver_id` int(11) DEFAULT 0,
  `conductor_id` int(11) DEFAULT 0,
  `route_path_json` text DEFAULT NULL,
  `schedule_days` text DEFAULT NULL,
  `departure_time` time DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `destination_time` time DEFAULT NULL,
  `route_id` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buses`
--

INSERT INTO `buses` (`id`, `owner_id`, `brand`, `model`, `gear_type`, `bus_number`, `bus_type`, `number_of_seats`, `facilities`, `img_front`, `img_back`, `img_left`, `img_right`, `img_interior`, `status`, `created_at`, `seats_per_row`, `total_rows`, `seats_per_column`, `has_conductor_seat`, `last_row_seats`, `route_number`, `route_start`, `route_end`, `route_path`, `driver_id`, `conductor_id`, `route_path_json`, `schedule_days`, `departure_time`, `start_time`, `destination_time`, `route_id`) VALUES
(1, 1, 'Isuzu', 'Journey J', 'Manual', 'NB-3344', 'Semi-Luxury', 46, 'AC, Reclining Seats, TV, WiFi, USB Charging, Reading Lights, Curtains', 'images/buses/NB-3344/1767752225_front_download (2).webp', 'images/buses/NB-3344/1767752225_back_OIP (7).webp', 'images/buses/NB-3344/1767752225_left_OIP (2).webp', 'images/buses/NB-3344/1767752225_right_OIP (5).webp', 'images/buses/NB-3344/1767752225_interior_OIP (11).webp', 'active', '2026-01-07 02:17:05', 2, 10, 2, 1, 6, NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 1, 'Haylou', 'Cyber-shot DSC-RX10 IV', 'Auto', 'ND 4725', 'AC', 41, 'USB', 'images/buses/ND 4725/1767760354_front_OIP (3).webp', 'images/buses/ND 4725/1767760354_back_download (3).webp', 'images/buses/ND 4725/1767760354_left_OIP (4).webp', 'images/buses/ND 4725/1767760354_right_OIP (4).webp', 'images/buses/ND 4725/1767760354_interior_OIP (12).webp', 'active', '2026-01-07 04:32:34', 3, 7, 2, 1, 6, NULL, NULL, NULL, NULL, 2, 2, NULL, NULL, NULL, NULL, NULL, 2);

-- --------------------------------------------------------

--
-- Table structure for table `bus_owners`
--

CREATE TABLE `bus_owners` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `owner_name` varchar(255) NOT NULL,
  `branch_address` text NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `nic_number` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `company_logo` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bus_owners`
--

INSERT INTO `bus_owners` (`id`, `company_name`, `owner_name`, `branch_address`, `contact_number`, `nic_number`, `email`, `profile_image`, `company_logo`, `password`, `status`, `created_at`) VALUES
(1, 'NCG', 'priyantha kumara', 'Bandarawela', '0758974562', '200465897852', 'priyanthakarunathilaka023@gmail.com', 'images/owners/profiles/1767751911_prof_OIP (8).webp', 'images/owners/logos/1767751911_logo_OIP (10).webp', '$2y$10$.uBVgZVzAHqJ2z6qcYCvh.P3xyznMLSdAmQFkCukpPgnDBhFfpvv2', 'approved', '2026-01-07 02:11:51'),
(2, 'Namali', 'Pasindu M.Soiza', 'Watagamuwa,Bandarawela', '075464324', '23424542434', 'soiza@gmail.com', 'images/owners/profiles/1767751973_prof_OIP (9).webp', 'images/owners/logos/1767751973_logo_download (4).webp', '$2y$10$47FdIAerMUEDjZxi38gqHujP2FTVlzzoPgBtrzeD3RPvT9UBSu1AK', 'approved', '2026-01-07 02:12:53');

-- --------------------------------------------------------

--
-- Table structure for table `conductors`
--

CREATE TABLE `conductors` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nic_number` varchar(20) NOT NULL,
  `dob` date DEFAULT NULL,
  `mobile_number` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `conductor_license_no` varchar(50) DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL,
  `license_photo` varchar(255) DEFAULT NULL,
  `license_expiry` date DEFAULT NULL,
  `emergency_contact` varchar(15) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `job_status` enum('active','deactive') DEFAULT 'deactive',
  `bus_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conductors`
--

INSERT INTO `conductors` (`id`, `owner_id`, `full_name`, `email`, `nic_number`, `dob`, `mobile_number`, `address`, `conductor_license_no`, `experience_years`, `license_photo`, `license_expiry`, `emergency_contact`, `password`, `status`, `created_at`, `job_status`, `bus_id`) VALUES
(1, 1, 'supun', 'supun@gmail.com', '19875643', '2022-06-07', '0447983454', 'Bandarawela', '24316464', 2031, 'uploads/conductors/1767755295_con_license.jpg', '2029-02-25', '0468764676', '$2y$10$OvRcMJHy0Ti5Wy1BTI036e2EMVJ2rXnRS6JPA9SvonS4nQgSvoUdC', 'approved', '2026-01-07 03:08:15', 'deactive', NULL),
(2, 1, 'Saman', 'saman@gmail.com', '27616865', '1978-12-06', '017343556', 'Bandarawela', '024343', 20155, 'uploads/conductors/1768012005_con_license.jpg', '3545-02-04', '365638656', '$2y$10$ixGr/W3Nwklz1FqwVGFlXOo9dVRiF289tJA/7HUrPE0YxUGUgKiee', 'approved', '2026-01-10 02:26:45', 'deactive', 3);

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `nic_number` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `dob` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_no` varchar(15) DEFAULT NULL,
  `emergency_contact` varchar(15) DEFAULT NULL,
  `license_no` varchar(50) DEFAULT NULL,
  `license_expiry` date DEFAULT NULL,
  `license_classes` varchar(100) DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL,
  `driver_photo` varchar(255) DEFAULT NULL,
  `license_photo` varchar(255) DEFAULT NULL,
  `police_report` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `job_status` enum('active','deactive') DEFAULT 'deactive',
  `bus_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`id`, `owner_id`, `full_name`, `nic_number`, `email`, `dob`, `address`, `contact_no`, `emergency_contact`, `license_no`, `license_expiry`, `license_classes`, `experience_years`, `driver_photo`, `license_photo`, `police_report`, `password`, `status`, `created_at`, `job_status`, `bus_id`) VALUES
(1, 1, 'Theekshana', '1869543215v', 'theekshanajanith4@gmail.com', '2018-06-14', 'No6,Colombo Road , Kandy', '0745698247', '0710410333', '32164134646vs', '2026-01-20', 'd', 5, 'uploads/drivers/1767755201_photo.jpg', 'uploads/drivers/1767755201_license.jpg', NULL, '$2y$10$LkMJAazwchUcKagHrM4jL.WTz5Tq.FsjgKTYmNrw3UxbMgAU/mKNm', 'approved', '2026-01-07 03:06:41', 'deactive', NULL),
(2, 1, 'Anura', '2468435467', 'anura@gmail.com', '1998-06-16', 'Thanthiriya,Bandarawela', '075754676', '0710410333', '04644654', '2030-10-22', 'G1', 2, 'uploads/drivers/1768011918_photo.jpg', 'uploads/drivers/1768011918_license.jpg', NULL, '$2y$10$hUAazP.NukHGRE0qNS2igOvqvuPZnWFiuo1Cxns9KSdjca78EKCle', 'approved', '2026-01-10 02:25:18', 'deactive', 3);

-- --------------------------------------------------------

--
-- Table structure for table `passengers`
--

CREATE TABLE `passengers` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `nic_number` varchar(20) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `emergency_contact` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `passengers`
--

INSERT INTO `passengers` (`id`, `full_name`, `contact_number`, `email`, `gender`, `nic_number`, `profile_image`, `emergency_contact`, `password`, `created_at`) VALUES
(1, 'janith', '0705267767', 'jkarunathilaka2007@gmail.com', 'Male', '200707401798', 'images/passenger/1767757949_IMG_20240610_133230_244.jpg', '0710410333', '$2y$10$oZRKCA3.Rc49APm7rDQzAe8b2cYIP/A6pu9MO.M//jtwtjrLAfEJC', '2026-01-07 03:52:30'),
(2, 'Kavithra Karunathilaka', '0758974562', 'ksenethmi@gmail.com', 'Female', '23424542434', 'images/passenger/1767966167_20240805_154145@-1150038662.jpg', '0468764676', '$2y$10$hOq96l/DV03R4tZlmBc2A.hvjQu0Kx4RFC453GfxKcAnFC0VpHuCS', '2026-01-09 13:42:47');

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE `routes` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `bus_id` int(11) DEFAULT NULL,
  `route_number` varchar(50) DEFAULT NULL,
  `start_point` varchar(100) DEFAULT NULL,
  `end_point` varchar(100) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `destination_time` time DEFAULT NULL,
  `route_path_json` text DEFAULT NULL,
  `schedule_days` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`id`, `owner_id`, `bus_id`, `route_number`, `start_point`, `end_point`, `start_time`, `destination_time`, `route_path_json`, `schedule_days`, `created_at`) VALUES
(2, 1, NULL, '23127', 'Badulla', 'Bandarawela', '06:00:00', '07:10:00', '[{\"name\":\"Hali Ela\",\"duration\":10},{\"name\":\"Halpe\",\"duration\":30},{\"name\":\"kumbalwela\",\"duration\":10},{\"name\":\"Dova\",\"duration\":10},{\"name\":\"Thanthiriya\",\"duration\":10}]', 'Tuesday,Wednesday,Friday,Sunday', '2026-01-10 02:29:51'),
(3, 1, NULL, '23127', 'Bandarawela', 'Badulla', '14:00:00', '15:10:00', '[{\"name\":\"Thanthiriya\",\"duration\":10},{\"name\":\"Dova\",\"duration\":10},{\"name\":\"kumbalwela\",\"duration\":10},{\"name\":\"Halpe\",\"duration\":30},{\"name\":\"Hali Ela\",\"duration\":10}]', 'Tuesday,Wednesday,Friday,Sunday', '2026-01-10 02:29:51');

-- --------------------------------------------------------

--
-- Table structure for table `route_packs`
--

CREATE TABLE `route_packs` (
  `id` int(11) NOT NULL,
  `route_number` varchar(10) DEFAULT NULL,
  `start_point` varchar(100) DEFAULT NULL,
  `end_point` varchar(100) DEFAULT NULL,
  `route_path_json` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `route_packs`
--

INSERT INTO `route_packs` (`id`, `route_number`, `start_point`, `end_point`, `route_path_json`) VALUES
(1, '23127', 'Badulla', 'Bandarawela', '[{\"name\":\"Hali Ela\",\"duration\":10},{\"name\":\"Halpe\",\"duration\":30},{\"name\":\"kumbalwela\",\"duration\":10},{\"name\":\"Dova\",\"duration\":10},{\"name\":\"Thanthiriya\",\"duration\":10}]');

-- --------------------------------------------------------

--
-- Table structure for table `sos_alerts`
--

CREATE TABLE `sos_alerts` (
  `id` int(11) NOT NULL,
  `passenger_id` int(11) NOT NULL,
  `current_location` varchar(255) DEFAULT NULL,
  `status` enum('active','attended','resolved') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sos_alerts`
--

INSERT INTO `sos_alerts` (`id`, `passenger_id`, `current_location`, `status`, `created_at`) VALUES
(1, 1, 'Location Shared via Mobile', 'active', '2026-01-07 08:04:01');

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `passenger_id` int(11) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('open','pending','resolved') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `support_tickets`
--

INSERT INTO `support_tickets` (`id`, `passenger_id`, `category`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 1, 'Payment Problem', 'Cake Class', 'sdcx', 'open', '2026-01-07 07:45:02');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_prices`
--

CREATE TABLE `ticket_prices` (
  `id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `from_town` varchar(255) NOT NULL,
  `to_town` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_prices`
--

INSERT INTO `ticket_prices` (`id`, `route_id`, `from_town`, `to_town`, `price`, `updated_at`) VALUES
(121, 2, 'Badulla', 'Hali Ela', 30.00, '2026-01-10 02:49:52'),
(122, 2, 'Badulla', 'Halpe', 60.00, '2026-01-10 02:49:52'),
(123, 2, 'Badulla', 'kumbalwela', 40.00, '2026-01-10 02:49:52'),
(124, 2, 'Badulla', 'Dova', 70.00, '2026-01-10 02:49:52'),
(125, 2, 'Badulla', 'Thanthiriya', 50.00, '2026-01-10 02:49:52'),
(126, 2, 'Badulla', 'Bandarawela', 80.00, '2026-01-10 02:49:52'),
(127, 2, 'Hali Ela', 'Halpe', 90.00, '2026-01-10 02:49:52'),
(128, 2, 'Hali Ela', 'kumbalwela', 120.00, '2026-01-10 02:49:52'),
(129, 2, 'Hali Ela', 'Dova', 100.00, '2026-01-10 02:49:52'),
(130, 2, 'Hali Ela', 'Thanthiriya', 130.00, '2026-01-10 02:49:52'),
(131, 2, 'Hali Ela', 'Bandarawela', 110.00, '2026-01-10 02:49:52'),
(132, 2, 'Halpe', 'kumbalwela', 140.00, '2026-01-10 02:49:52'),
(133, 2, 'Halpe', 'Dova', 160.00, '2026-01-10 02:49:52'),
(134, 2, 'Halpe', 'Thanthiriya', 150.00, '2026-01-10 02:49:52'),
(135, 2, 'Halpe', 'Bandarawela', 170.00, '2026-01-10 02:49:52'),
(136, 2, 'kumbalwela', 'Dova', 180.00, '2026-01-10 02:49:52'),
(137, 2, 'kumbalwela', 'Thanthiriya', 200.00, '2026-01-10 02:49:52'),
(138, 2, 'kumbalwela', 'Bandarawela', 190.00, '2026-01-10 02:49:52'),
(139, 2, 'Dova', 'Thanthiriya', 210.00, '2026-01-10 02:49:52'),
(140, 2, 'Dova', 'Bandarawela', 220.00, '2026-01-10 02:49:52'),
(141, 2, 'Thanthiriya', 'Bandarawela', 230.00, '2026-01-10 02:49:52');

-- --------------------------------------------------------

--
-- Table structure for table `trip_bookings`
--

CREATE TABLE `trip_bookings` (
  `id` int(10) UNSIGNED NOT NULL,
  `bus_id` int(11) NOT NULL,
  `passenger_id` int(10) UNSIGNED NOT NULL,
  `trip_type` varchar(100) DEFAULT NULL,
  `passenger_count` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `pickup_location` varchar(255) DEFAULT NULL,
  `final_destination` varchar(255) DEFAULT NULL,
  `visit_places` text DEFAULT NULL,
  `extra_facilities` text DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trip_bookings`
--

INSERT INTO `trip_bookings` (`id`, `bus_id`, `passenger_id`, `trip_type`, `passenger_count`, `start_date`, `end_date`, `start_time`, `pickup_location`, `final_destination`, `visit_places`, `extra_facilities`, `total_amount`, `status`, `created_at`) VALUES
(1, 1, 1, 'Family', 25, '2026-01-31', '2026-02-03', '08:00:00', 'Bandarawela', 'Colombo', 'Haputhale View point , Beragala , Lotus tower , Gall Face , One Galle fave', 'Cool box AND Party box [EST. PRICE: RS. 250000]', 250000.00, '', '2026-01-08 09:12:28'),
(2, 3, 1, 'Wedding', 15, '2026-01-15', '2026-01-16', '10:00:00', 'Bandarawela', 'Colombo', 'nothing', 'cool box', 35000.00, '', '2026-01-08 09:51:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `buses`
--
ALTER TABLE `buses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bus_number` (`bus_number`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `bus_owners`
--
ALTER TABLE `bus_owners`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nic_number` (`nic_number`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `conductors`
--
ALTER TABLE `conductors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nic_number` (`nic_number`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nic_number` (`nic_number`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `passengers`
--
ALTER TABLE `passengers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nic_number` (`nic_number`);

--
-- Indexes for table `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `route_packs`
--
ALTER TABLE `route_packs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `route_number` (`route_number`);

--
-- Indexes for table `sos_alerts`
--
ALTER TABLE `sos_alerts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ticket_prices`
--
ALTER TABLE `ticket_prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `route_id` (`route_id`);

--
-- Indexes for table `trip_bookings`
--
ALTER TABLE `trip_bookings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `buses`
--
ALTER TABLE `buses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bus_owners`
--
ALTER TABLE `bus_owners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `conductors`
--
ALTER TABLE `conductors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `passengers`
--
ALTER TABLE `passengers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `route_packs`
--
ALTER TABLE `route_packs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sos_alerts`
--
ALTER TABLE `sos_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ticket_prices`
--
ALTER TABLE `ticket_prices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- AUTO_INCREMENT for table `trip_bookings`
--
ALTER TABLE `trip_bookings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `buses`
--
ALTER TABLE `buses`
  ADD CONSTRAINT `buses_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `bus_owners` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conductors`
--
ALTER TABLE `conductors`
  ADD CONSTRAINT `conductors_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `bus_owners` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `drivers`
--
ALTER TABLE `drivers`
  ADD CONSTRAINT `drivers_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `bus_owners` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ticket_prices`
--
ALTER TABLE `ticket_prices`
  ADD CONSTRAINT `ticket_prices_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
