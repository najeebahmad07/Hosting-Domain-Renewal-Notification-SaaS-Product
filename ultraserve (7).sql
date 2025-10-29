-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 25, 2025 at 10:03 AM
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
-- Database: `ultraserve`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `admin_id`, `admin_name`, `action`, `table_name`, `record_id`, `is_read`, `created_at`) VALUES
(842, 11, 'mohideen', 'Logged In', 'super_admins', 11, 0, '2025-10-24 15:45:48'),
(843, 11, 'mohideen', 'Logged In', 'super_admins', 11, 0, '2025-10-24 15:47:07'),
(844, 11, 'mohideen', 'Logged In', 'super_admins', 11, 0, '2025-10-24 15:47:46'),
(845, 11, 'mohideen', 'Logged In', 'super_admins', 11, 0, '2025-10-24 15:48:38'),
(846, 11, 'mohideen', 'Logged In', 'super_admins', 11, 0, '2025-10-24 15:49:19'),
(847, 11, 'mohideen', 'Logged In', 'super_admins', 11, 0, '2025-10-24 15:53:51'),
(848, 11, 'mohideen', 'Logged In', 'super_admins', 11, 0, '2025-10-24 15:57:06'),
(849, 11, 'mohideen', 'Logged In', 'super_admins', 11, 0, '2025-10-24 16:06:28'),
(850, 11, 'mohideen', 'Logged In', 'super_admins', 11, 0, '2025-10-24 16:26:23'),
(851, 11, 'mohideen', 'Logged In', 'super_admins', 11, 0, '2025-10-24 16:35:41'),
(852, 11, 'mohideen', 'Logged In', 'super_admins', 11, 0, '2025-10-25 09:59:43'),
(853, 31, 'mohideen', 'Updated', 'admin', 31, 0, '2025-10-25 10:11:12'),
(854, 11, 'mohideen', 'Logged In', 'super_admins', 11, 0, '2025-10-25 10:12:23'),
(855, 11, 'mohideen', 'Logged In', 'super_admins', 11, 0, '2025-10-25 10:20:10'),
(856, 11, 'mohideen', 'Logged In', 'super_admins', 11, 0, '2025-10-25 10:26:07'),
(857, 11, 'mohideen', 'Logged In', 'super_admins', 11, 0, '2025-10-25 11:34:46'),
(858, 11, 'mohideen', 'Logged In', 'super_admins', 11, 0, '2025-10-25 13:23:59'),
(859, 32, 'mohideen', 'Updated', 'admin', 32, 0, '2025-10-25 13:24:16'),
(860, 0, 'jaffer', 'Created', 'hosting_domain', 779, 0, '2025-10-25 13:25:51'),
(861, 11, 'mohideen', 'Logged In', 'super_admins', 11, 0, '2025-10-25 13:26:17');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `super_admin_id` int(11) DEFAULT NULL,
  `email` varchar(256) NOT NULL,
  `status` enum('active','suspended','pending') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `super_admin_id`, `email`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(30, 'najeeb', '123', 11, 'n@gmail.com', 'active', NULL, '2025-10-24 09:22:06', '2025-10-24 09:22:53'),
(31, 'sufiyan', '1234', 11, 'sufiyan.ultragits@gmail.com', 'active', NULL, '2025-10-24 09:23:49', '2025-10-25 04:41:12'),
(33, 'jaffer', '123', 11, 'j@gmail.com', 'active', NULL, '2025-10-25 07:55:14', '2025-10-25 07:55:14');

-- --------------------------------------------------------

--
-- Table structure for table `admin_settings`
--

CREATE TABLE `admin_settings` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_settings`
--

INSERT INTO `admin_settings` (`id`, `admin_id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 26, 'timezone', 'Asia/Kolkata', '2025-10-22 06:57:09', '2025-10-22 06:57:14'),
(2, 26, 'currency', 'INR', '2025-10-22 06:57:09', '2025-10-23 05:51:28'),
(3, 26, 'date_format', 'Y-m-d', '2025-10-22 06:57:09', '2025-10-22 06:57:09'),
(7, 26, 'company_name', 'UltraServe', '2025-10-22 06:57:26', '2025-10-22 07:15:10'),
(8, 26, 'primary_color', '#6366f1', '2025-10-22 06:57:26', '2025-10-23 05:01:40'),
(9, 26, 'secondary_color', '#8b5cf6', '2025-10-22 06:57:26', '2025-10-23 05:01:40'),
(22, 26, 'enable_backup', '1', '2025-10-22 07:08:40', '2025-10-22 07:08:40'),
(23, 26, 'backup_frequency', 'daily', '2025-10-22 07:08:40', '2025-10-22 07:08:40'),
(24, 26, 'backup_time', '02:00', '2025-10-22 07:08:40', '2025-10-22 07:08:40'),
(25, 26, 'backup_location', '/backups/', '2025-10-22 07:08:40', '2025-10-22 07:08:40'),
(26, 26, 'backup_retention', '30', '2025-10-22 07:08:40', '2025-10-22 07:08:40'),
(27, 26, 'retention_period', 'never', '2025-10-22 07:10:07', '2025-10-23 07:37:52'),
(28, 26, 'logs_retention', '90', '2025-10-22 07:10:07', '2025-10-22 07:10:07'),
(29, 26, 'auto_delete', '1', '2025-10-22 07:10:07', '2025-10-22 07:10:07'),
(30, 26, 'delete_expired', '0', '2025-10-22 07:10:07', '2025-10-22 07:10:07'),
(56, 26, 'logo_path', 'uploads/logos/logo_26_1761195700.jpg', '2025-10-23 05:01:40', '2025-10-23 05:01:40');

-- --------------------------------------------------------

--
-- Table structure for table `hosting_domain`
--

CREATE TABLE `hosting_domain` (
  `id` int(11) NOT NULL,
  `client_name` varchar(256) NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `domain_name` varchar(150) NOT NULL,
  `purchased_from` varchar(100) NOT NULL,
  `registration_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `hosting_purchased_from` varchar(255) DEFAULT NULL,
  `hosting_registration_date` date DEFAULT NULL,
  `hosting_expiry_date` date DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `notes` varchar(250) NOT NULL,
  `admin_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hosting_domain`
--

INSERT INTO `hosting_domain` (`id`, `client_name`, `company_name`, `domain_name`, `purchased_from`, `registration_date`, `expiry_date`, `hosting_purchased_from`, `hosting_registration_date`, `hosting_expiry_date`, `email`, `notes`, `admin_id`) VALUES
(729, 'Fun At Fit Sports', 'Fun At Fit Sports', 'funatfitsports.com', 'Sammamishdomains', '2025-03-25', '2026-03-25', 'afternic', '2025-03-25', '2026-03-25', 'funatfitsports@gmail.com', '', 30),
(730, 'KUJEMW', 'KUJEMW', 'kujemw.com', 'Go Daddy', '2024-02-28', '2030-02-28', 'Go Daddy', '2024-02-28', '2030-02-28', 'projects@kujemw.com', '', 30),
(731, 'Avoor Medd Plus', 'Avoor Medd Plus', 'avoormeddplus.com', 'Hostinger', '2022-04-22', '2027-04-22', 'Hostinger', '2022-04-22', '2027-04-22', 'mubarak@avoormeddplus.com', '', 30),
(732, 'Bollinee Towers', 'Bollinee Towers', 'bollineetowers.com', 'Go Daddy', '2023-08-17', '2025-08-17', 'Domains control', '2023-08-17', '2025-08-17', 'sufiyan.ultragits@gmail.com', '', 30),
(733, 'IT AM solutions', 'IT AM solutions', 'itamsolutions.in', 'Go Daddy', '2024-08-23', '2025-08-23', 'Hostinger', '2024-08-23', '2025-08-23', 'sufiyan.ultragits@gmail.com', '', 30),
(734, 'Metaroll Steels', 'Metaroll Steels', 'metarollsteels.com', 'Go Daddy', '2024-08-26', '2025-08-26', 'Hostinger', '2024-08-26', '2025-08-26', 'sufiyan.ultragits@gmail.com', '', 30),
(735, 'Maharastra Steels', 'Maharastra Steels', 'maharashtrasteels.com', 'Go Daddy', '2024-08-30', '2025-08-30', 'Hostinger', '2024-08-30', '2025-08-30', 'sufiyan.ultragits@gmail.com', '', 30),
(736, 'Mujib Briyani', 'Mujib Briyani', 'mujibbriyani.in', 'Go Daddy', '2019-10-10', '2026-10-10', 'Hostinger', '2019-10-10', '2026-10-10', 'bilaladhil@gmail.com', '', 30),
(737, 'Arakkonam', 'Arakkonam', 'arakkonam.in', 'Go Daddy', '2020-10-19', '2025-10-19', 'Hostinger', '2020-10-19', '2025-10-19', 'sufiyan.ultragits@gmail.com', '', 30),
(738, 'VR Mahal', 'VR Mahal', 'vrmahal.in', 'Go Daddy', '2019-11-10', '2025-11-10', 'Hostinger', '2019-11-10', '2025-11-10', 'vrmahal@gmail.com', '', 30),
(739, 'Advatrix', 'Advatrix', 'advatrix.com', 'Go Daddy', '2021-12-14', '2027-12-14', 'Hostinger', '2021-12-14', '2027-12-14', 'support@advatrix.com', '', 30),
(740, 'MSME Chain X', 'MSME Chain X', 'msmechanix.com', 'Go Daddy', '2021-12-26', '2025-12-26', 'Hostinger', '2021-12-26', '2025-12-26', 'jobs@msmechanix.com', '', 30),
(741, 'RFR consultants', 'RFR consultants', 'rfrconsultants.in', 'Go Daddy', '2022-12-26', '2025-12-26', 'Hostinger', '2022-12-26', '2025-12-26', 'info@rfrconsultants.in', '', 30),
(742, 'AlMinar International', 'AlMinar International', 'alminarinternational.com', 'Go Daddy', '2025-01-12', '2026-01-12', 'Hostinger', '2025-01-12', '2026-01-12', 'info@alminarinternational.com', '', 30),
(743, 'Future Tek Services', 'Future Tek Services', 'futuretekservices.com', 'Go Daddy', '2020-01-25', '2026-01-25', 'Hostinger', '2020-01-25', '2026-01-25', 'projects@futuretekservices.com', '', 30),
(744, 'Metal Man ltd', 'Metal Man ltd', 'metalmanltd.ca', 'Go Daddy', '2023-12-26', '2026-12-26', 'Hostinger', '2023-12-26', '2026-12-26', 'info@metalmanltd.ca', '', 30),
(745, 'Xray welders', 'Xray welders', 'xraywelders.ca', 'Go Daddy', '2024-03-07', '2027-03-07', 'Go Daddy', '2024-03-07', '2027-03-07', 'sufiyan.ultragits@gmail.com', '', 30),
(746, 'AIADSWT', 'AIADSWT', 'aiadswt.org', 'Go Daddy', '2021-03-17', '2027-03-17', 'Hostinger', '2021-03-17', '2027-03-17', 'mail@aiadswt.org', '', 30),
(747, 'Crisp Systems', 'Crisp Systems', 'crispsystems.in', 'Go Daddy', '2024-06-25', '2027-06-25', 'Hostinger', '2024-06-25', '2027-06-25', 'salemcrisp@gmail.com', '', 30),
(748, 'Halal cuts meat', 'Halal cuts meat', 'halalcutsmeat.com', 'Name SRS', '2024-11-13', '2025-11-13', 'Hostinger', '2024-11-13', '2025-11-13', 'halalcutmeats@gmail.com', '', 30),
(749, 'Palm Import Export', 'Palm Import Export', 'palmimportexport.com', 'Go Daddy', '2025-02-21', '2026-02-21', 'Hostinger', '2025-02-21', '2026-02-21', 'sufiyan.ultragits@gmail.com', '', 30),
(750, 'Brilliant Fire Safety', 'Brilliant Fire Safety', 'www.brilliantfiresafety.com', 'Good Domain', '2024-03-14', '2026-03-14', 'Hostinger', '2024-03-14', '2026-03-14', 'project@brilliantfiresafety.com', '', 30),
(751, 'Byteksa', 'Byteksa', 'byteksa.com', 'Go Daddy', '2023-04-15', '2027-04-15', 'Hostinger', '2023-04-15', '2027-04-15', 'info@byteksa.com', '', 30),
(752, 'Wet market', 'Wet market', 'wetmarket.in', 'Go Daddy', '2023-07-11', '2026-07-11', 'Hostinger', '2023-07-11', '2026-07-11', 'support@Wetmarket.in.', '', 30),
(753, 'Nidi Tech solutions', 'Nidi Tech solutions', 'niditechsolutions.in', 'Good Domain', '2022-12-28', '2026-12-28', 'Hostinger', '2022-12-28', '2026-12-28', 'sufiyan.ultragits@gmail.com', '', 30),
(754, 'Abfrefractory', 'Abfrefractory', 'abfrefractory.com', 'Hostinger', '2024-12-10', '2025-12-10', 'Hostinger', '2024-12-10', '2025-12-10', 'sufiyan.ultragits@gmail.com', '', 30),
(755, 'NetherLands 3DCP', 'NetherLands 3DCP', 'NETHERLANDS3DCP.COM', 'Domainshype', '2024-12-12', '2025-12-12', 'Hostinger', '2024-12-12', '2025-12-12', 'Info@netherlands3dcp.com', '', 30),
(756, 'Jaft Biotech', 'Jaft Biotech', 'jaftbiotech.com', 'Go Daddy', '2023-09-19', '2025-09-19', 'Hostinger', '2023-09-19', '2025-09-19', 'jaftbiotech@gmail.com', '', 30),
(757, 'Shafi Institute', 'Shafi Institute', 'shafiinstitute.com', 'Go Daddy', '2024-12-28', '2025-12-28', 'Hostinger', '2024-12-28', '2025-12-28', 'info@shafiinstitute.com', '', 30),
(758, 'AI Minar International', 'AI Minar International', 'https://alminarinternational.com/', 'Go Daddy', '2025-01-12', '2026-01-12', 'Hostinger', '2025-01-12', '2026-01-12', 'info@alminarinternational.com', '', 30),
(759, 'Go Green Charging India', 'Go Green Charging India', 'gogreenchargingindia.com', 'Go Daddy', '2025-02-07', '2026-02-07', 'Hostinger', '2025-02-07', '2026-02-07', 'info@gogreenchargingindia.com', '', 30),
(760, 'KingInkjet', 'KingInkjet', 'kinginkjet.in', 'Go Daddy', '2023-02-25', '2026-02-25', 'Go Daddy', '2023-02-25', '2026-02-25', 'Kijprinters2020@gmail.com', '', 30),
(761, 'Siyakhams', 'Siyakhams', 'siyakhams.com', 'Go Daddy', '2023-09-23', '2027-09-23', 'Go Daddy', '2023-09-23', '2027-09-23', 'info@siyakhams.com', '', 30),
(762, 'Crisps Chool projects', 'Crisps Chool projects', 'crispschoolprojects.in', 'Go Daddy', '2024-10-03', '2027-10-03', 'Hostinger', '2024-10-03', '2027-10-03', 'salemcrisp@gmail.com', '', 30),
(763, 'Kitd-Verein', 'Kitd-Verein', 'kitd-verein.de', 'Hostinger', NULL, NULL, 'Hostinger', NULL, NULL, 'sufiyan.ultragits@gmail.com', '', 30),
(764, 'Quality Roofs', 'Quality Roofs', 'https://qualityroof.in/', 'Go Daddy', '2017-03-23', '2027-03-23', 'Hostinger', '2017-03-23', '2027-03-23', 'sales@qualityroof.in', '', 30),
(765, 'KM Computers', 'KM Computers', 'http://kmcomputers.co.in/', 'Good Domain', '2025-01-12', '2026-01-12', 'Hostinger', '2025-01-12', '2026-01-12', 'info@kmcomputers.com', '', 30),
(766, 'EWasco', 'EWasco', 'http://ewasco.com/', 'Good Domain', '2018-03-06', '2027-03-06', 'Hostinger', '2018-03-06', '2027-03-06', 'info@ewasco.com', '', 30),
(767, 'MSZ Consultancy', 'MSZ Consultancy', 'MSZ.ae', 'AE Server', NULL, NULL, 'Hostinger', NULL, NULL, 'info@msz.ae', '', 30),
(768, 'SOlveTechMe', 'SOlveTechMe', 'Solvetechme.com', 'Go Daddy', '2023-01-10', '2026-01-10', 'Hostinger', '2023-01-10', '2026-01-10', 'info@solvetechme.com', '', 30),
(769, 'SKY tech Me', 'SKY tech Me', 'SkyTechMe.com', 'Go Daddy', '2024-09-18', '2026-09-18', 'Hostinger', '2024-09-18', '2026-09-18', 'info@skytechme.com', '', 30),
(770, 'ReTrrac', 'ReTrrac', 'retrrac.org', 'PDR LTD', '2022-02-12', '2027-02-12', 'Hostinger', '2022-02-12', '2027-02-12', 'info@retrrac.org', '', 30),
(771, 'Bollinee Towers', 'Bollinee Towers', 'https://bollineetowers.com/', 'Hostinger', '2023-08-17', '2025-08-17', 'Hostinger', '2023-08-17', '2025-08-17', 'sufiyan.ultragits@gmail.com', '', 30),
(772, 'ComfortLiv', 'ComfortLiv', 'comfortliv.com', 'Go Daddy', '2022-08-02', '2026-08-02', 'Hostinger', '2022-08-02', '2026-08-02', 'info@comfortliv.com', '', 30),
(773, 'IZEWELT', 'IZEWELT', 'IZIWELT.de', 'Hostinger', NULL, NULL, 'Hostinger', NULL, NULL, ' kontakt@iziwelt.de', '', 30),
(774, 'IZEWELT', 'IZEWELT', 'https://iziwelt.com/', 'Cronon Gmbh', '2023-07-05', '2026-07-05', 'Hostinger', '2023-07-05', '2026-07-05', 'Kontakt@iziwelt.de', '', 30),
(775, 'Fazo academy', 'Fazo academy', 'https://www.fazoacademy.com/', 'Hostinger', '2022-03-09', '2026-03-09', 'Hostinger', '2022-03-09', '2026-03-09', 'info@fazoacademy.com', '', 30),
(776, 'Falco Fusion', 'Falco Fusion', 'falcofusion.com', 'Go Daddy', '2023-05-10', '2026-05-10', 'Hostinger', '2023-05-10', '2026-05-10', 'haroonrasheed724@gmail.com', '', 30),
(777, 'Indian Cafe restaurant', 'Indian Cafe restaurant', 'https://indiancaferestaurant.in/', 'Hostinger', '2025-01-08', '2026-01-08', 'Hostinger', '2025-01-08', '2026-01-08', 'mdibrahim@indiancaferestaurant.in', '', 30),
(778, 'Muthu Mushkir', 'Raj Kumar Pvt. Ltd.', 'Riyaz Suthan', 'Namecheap', '2002-09-17', '2019-07-02', 'Riyaz Raj', '2025-08-06', '2022-08-10', 'zibyko@example.com', 'Qui ullam aut laudan', 31),
(779, 'Hema Raj', 'Kumari Raj Pvt. Ltd.', 'Muthu Sharif', 'Bluehost', '2013-01-11', '2022-06-15', 'Jeba Dhayalan', '1998-01-30', '2014-06-07', 'xolum@example.com', 'Minus possimus aut ', 33);

-- --------------------------------------------------------

--
-- Table structure for table `pricing_plans`
--

CREATE TABLE `pricing_plans` (
  `id` int(11) NOT NULL,
  `plan_name` varchar(50) NOT NULL,
  `plan_type` varchar(20) NOT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `billing_cycle` varchar(20) DEFAULT 'year',
  `ideal_for` varchar(255) DEFAULT NULL,
  `domains_limit` varchar(50) DEFAULT NULL,
  `notifications` varchar(255) DEFAULT NULL,
  `reminder_scheduling` varchar(255) DEFAULT NULL,
  `dashboard_access` varchar(255) DEFAULT NULL,
  `data_import` varchar(255) DEFAULT NULL,
  `users_limit` varchar(100) DEFAULT NULL,
  `analytics_reports` varchar(255) DEFAULT NULL,
  `support_type` varchar(255) DEFAULT NULL,
  `white_label` varchar(50) DEFAULT NULL,
  `is_popular` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `border_color` varchar(20) DEFAULT '#2563eb',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pricing_plans`
--

INSERT INTO `pricing_plans` (`id`, `plan_name`, `plan_type`, `price`, `billing_cycle`, `ideal_for`, `domains_limit`, `notifications`, `reminder_scheduling`, `dashboard_access`, `data_import`, `users_limit`, `analytics_reports`, `support_type`, `white_label`, `is_popular`, `is_active`, `border_color`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Basic (Free)', 'basic', 0.00, 'lifetime', 'Freelancers & Individuals', 'Up to 30', 'Email Alerts', 'Default (7 days before expiry)', 'Basic Overview', 'Manual Upload (Excel/CSV)', '1 Admin', 'Basic Renewal Insights', 'Email Support', 'No', 0, 1, '#60a5fa', 1, '2025-10-23 06:54:17', '2025-10-25 04:43:40'),
(2, 'Standard', 'standard', 3000.00, 'year', 'Small & Growing Agencies', 'Up to 1,000', 'Email + WhatsApp', 'Custom (30/15/7 days)', 'Advanced Dashboard', 'Auto-Sync from Registrars', 'Up to 5 Users', 'Renewal Trends & Filters', 'Priority Email & Chat Support', 'No', 0, 1, '#2563eb', 2, '2025-10-23 06:54:17', '2025-10-23 06:54:17'),
(3, 'Premium', 'premium', 10000.00, 'year', 'Enterprises & Hosting Providers', 'Unlimited', 'Email, WhatsApp, SMS & Push', 'Fully Custom + Smart Scheduling', 'Smart Dashboard + Reports', 'Auto-Sync + API Integration', 'Unlimited Users & Roles', 'Advanced Analytics + Export', '24/7 Premium Support', 'Yes (Your Branding)', 0, 1, '#1e3a8a', 3, '2025-10-23 06:54:17', '2025-10-23 06:54:17');

-- --------------------------------------------------------

--
-- Table structure for table `profile`
--

CREATE TABLE `profile` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `super_admins`
--

CREATE TABLE `super_admins` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `super_admins`
--

INSERT INTO `super_admins` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(11, 'mohideen', 'm@gmail.com', '1234', '2025-10-15 09:24:43');

-- --------------------------------------------------------

--
-- Table structure for table `user_subscriptions`
--

CREATE TABLE `user_subscriptions` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `payment_status` varchar(20) DEFAULT 'pending',
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_subscriptions`
--

INSERT INTO `user_subscriptions` (`id`, `admin_id`, `plan_id`, `start_date`, `end_date`, `status`, `payment_status`, `amount_paid`, `created_at`) VALUES
(6, 27, 1, '2025-10-24', '2026-10-24', 'active', 'paid', 0.00, '2025-10-24 09:14:05'),
(7, 28, 1, '2025-10-24', '2026-10-24', 'active', 'paid', 0.00, '2025-10-24 09:15:40'),
(8, 29, 1, '2025-10-24', '2026-10-24', 'active', 'paid', 0.00, '2025-10-24 09:19:16'),
(9, 31, 3, '2025-10-24', '2026-10-24', 'active', 'paid', 10000.00, '2025-10-24 09:23:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_super_admin` (`super_admin_id`);

--
-- Indexes for table `admin_settings`
--
ALTER TABLE `admin_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_admin_setting` (`admin_id`,`setting_key`);

--
-- Indexes for table `hosting_domain`
--
ALTER TABLE `hosting_domain`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_expiry_date` (`expiry_date`),
  ADD KEY `idx_hosting_expiry` (`hosting_expiry_date`),
  ADD KEY `idx_admin_expiry` (`admin_id`,`expiry_date`);

--
-- Indexes for table `pricing_plans`
--
ALTER TABLE `pricing_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `super_admins`
--
ALTER TABLE `super_admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=862;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `admin_settings`
--
ALTER TABLE `admin_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `hosting_domain`
--
ALTER TABLE `hosting_domain`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=780;

--
-- AUTO_INCREMENT for table `pricing_plans`
--
ALTER TABLE `pricing_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `profile`
--
ALTER TABLE `profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `super_admins`
--
ALTER TABLE `super_admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `fk_super_admin` FOREIGN KEY (`super_admin_id`) REFERENCES `super_admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `hosting_domain`
--
ALTER TABLE `hosting_domain`
  ADD CONSTRAINT `hosting_domain_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `profile`
--
ALTER TABLE `profile`
  ADD CONSTRAINT `profile_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
