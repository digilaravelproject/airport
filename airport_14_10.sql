-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 14, 2025 at 01:22 PM
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
-- Database: `airport`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:69:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:12:\"view reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:16:\"manage inventory\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:15:\"manage channels\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:15:\"manage packages\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:18:\"manage allocations\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:18:\"manage subscribers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:17:\"permissions.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:18:\"permissions.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:11:\"roles.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:12:\"roles.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:11:\"roles.store\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:13:\"roles.destroy\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:13:\"clients.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:14:\"clients.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:13:\"clients.store\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:12:\"clients.show\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:12:\"clients.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:14:\"clients.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:15:\"clients.destroy\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:15:\"locations.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:16:\"locations.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:15:\"locations.store\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:14:\"locations.show\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:14:\"locations.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:16:\"locations.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:25;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:17:\"locations.destroy\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:26;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:14:\"channels.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:27;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:15:\"channels.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:28;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:14:\"channels.store\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:29;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:13:\"channels.show\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:30;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:13:\"channels.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:31;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:15:\"channels.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:32;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:16:\"channels.destroy\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:33;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:17:\"inventories.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:34;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:18:\"inventories.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:35;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:17:\"inventories.store\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:36;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:16:\"inventories.show\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:37;a:4:{s:1:\"a\";i:38;s:1:\"b\";s:16:\"inventories.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:38;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:18:\"inventories.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:39;a:4:{s:1:\"a\";i:40;s:1:\"b\";s:19:\"inventories.destroy\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:40;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:16:\"inventories.ping\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:41;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:18:\"inventories.reboot\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:42;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:14:\"packages.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:43;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:15:\"packages.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:44;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:14:\"packages.store\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:45;a:4:{s:1:\"a\";i:46;s:1:\"b\";s:13:\"packages.show\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:46;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:13:\"packages.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:47;a:4:{s:1:\"a\";i:48;s:1:\"b\";s:15:\"packages.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:48;a:4:{s:1:\"a\";i:49;s:1:\"b\";s:16:\"packages.destroy\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:49;a:4:{s:1:\"a\";i:50;s:1:\"b\";s:24:\"inventory-packages.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:50;a:4:{s:1:\"a\";i:51;s:1:\"b\";s:25:\"inventory-packages.assign\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:51;a:4:{s:1:\"a\";i:52;s:1:\"b\";s:14:\"utility.online\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:52;a:4:{s:1:\"a\";i:53;s:1:\"b\";s:13:\"reports.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:53;a:4:{s:1:\"a\";i:54;s:1:\"b\";s:15:\"reports.preview\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:54;a:4:{s:1:\"a\";i:55;s:1:\"b\";s:16:\"reports.download\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:55;a:4:{s:1:\"a\";i:56;s:1:\"b\";s:10:\"help.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:56;a:4:{s:1:\"a\";i:57;s:1:\"b\";s:17:\"permissions.store\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:57;a:4:{s:1:\"a\";i:58;s:1:\"b\";s:18:\"live-reports.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:58;a:4:{s:1:\"a\";i:59;s:1:\"b\";s:20:\"live-reports.preview\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:59;a:4:{s:1:\"a\";i:60;s:1:\"b\";s:21:\"live-reports.download\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:60;a:4:{s:1:\"a\";i:61;s:1:\"b\";s:23:\"installed-reports.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:61;a:4:{s:1:\"a\";i:62;s:1:\"b\";s:25:\"installed-reports.preview\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:62;a:4:{s:1:\"a\";i:63;s:1:\"b\";s:26:\"installed-reports.download\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:63;a:4:{s:1:\"a\";i:64;s:1:\"b\";s:21:\"channel-reports.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:64;a:4:{s:1:\"a\";i:65;s:1:\"b\";s:23:\"channel-reports.preview\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:65;a:4:{s:1:\"a\";i:66;s:1:\"b\";s:24:\"channel-reports.download\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:66;a:4:{s:1:\"a\";i:67;s:1:\"b\";s:21:\"package-reports.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:67;a:4:{s:1:\"a\";i:68;s:1:\"b\";s:23:\"package-reports.preview\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:68;a:4:{s:1:\"a\";i:69;s:1:\"b\";s:24:\"package-reports.download\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:3:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:5:\"Admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:7:\"Manager\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:6:\"Client\";s:1:\"c\";s:3:\"web\";}}}', 1760445193);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `channels`
--

CREATE TABLE `channels` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `channel_name` varchar(255) NOT NULL,
  `channel_source_in` varchar(255) DEFAULT NULL,
  `channel_source_details` varchar(255) DEFAULT NULL,
  `channel_stream_type_out` varchar(255) DEFAULT NULL,
  `channel_url` varchar(255) DEFAULT NULL,
  `channel_genre` varchar(255) DEFAULT NULL,
  `channel_resolution` varchar(255) DEFAULT NULL,
  `channel_type` varchar(255) DEFAULT NULL,
  `language` varchar(255) DEFAULT NULL,
  `encryption` tinyint(1) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `channels`
--

INSERT INTO `channels` (`id`, `channel_name`, `channel_source_in`, `channel_source_details`, `channel_stream_type_out`, `channel_url`, `channel_genre`, `channel_resolution`, `channel_type`, `language`, `encryption`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Star Sports', 'DTH', '25', 'UDP', 'udp://239.15.0.1:1234', 'Sports', 'HD1', 'Paid', 'Urdu', 1, 1, '2025-09-20 05:29:22', '2025-10-08 04:25:43'),
(2, 'TV10', 'Internet', 'URL', 'UDP', 'udp://239.15.0.1:1234', 'News', 'HD', 'Free', 'Marathi', 1, 1, '2025-09-20 05:31:01', '2025-10-08 04:25:33'),
(3, 'ABP Maza', 'Internet', 'URL', 'UDP', 'udp://239.15.0.1:1234', 'News', 'HD', 'Free', 'English', 1, 1, '2025-09-22 01:03:43', '2025-10-08 04:25:13'),
(4, 'Sony Sport1', 'DTH', '25', 'UDP', 'udp://239.15.0.1:1234', 'Sports', 'HD', 'Free', 'Hindi', 1, 1, '2025-09-22 01:05:01', '2025-10-08 04:25:23');

-- --------------------------------------------------------

--
-- Table structure for table `channel_package`
--

CREATE TABLE `channel_package` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `package_id` bigint(20) UNSIGNED NOT NULL,
  `channel_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `channel_package`
--

INSERT INTO `channel_package` (`id`, `package_id`, `channel_id`, `created_at`, `updated_at`) VALUES
(5, 1, 1, NULL, NULL),
(6, 1, 4, NULL, NULL),
(7, 2, 2, NULL, NULL),
(8, 2, 3, NULL, NULL),
(10, 3, 1, NULL, NULL),
(12, 3, 3, NULL, NULL),
(13, 3, 4, NULL, NULL),
(14, 3, 2, NULL, NULL),
(15, 4, 1, NULL, NULL),
(16, 4, 2, NULL, NULL),
(22, 6, 3, NULL, NULL),
(23, 6, 2, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_no` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `type` enum('Paid','Free') NOT NULL DEFAULT 'Free',
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `pin` varchar(255) DEFAULT NULL,
  `gst_no` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `name`, `address`, `contact_no`, `email`, `contact_person`, `type`, `city`, `state`, `pin`, `gst_no`, `created_at`, `updated_at`) VALUES
(1, 'John Marker2', 'Pune', '89874875758', 'john@gmail.com', 'mike', 'Free', 'Pune', 'MH', '411060', 'GST-232', '2025-09-20 02:55:45', '2025-09-22 00:29:19'),
(2, 'Mark Datson', 'New York, NY, USA', '8986756575', 'darshankondekar01@gmail.com', 'Dev', 'Paid', 'New York', 'NY', '411060', NULL, '2025-09-20 04:28:54', '2025-09-20 04:28:54'),
(3, 'Mark Datson', 'New York, NY, USA', '8986756575', 'darshankondekar01@gmail.com', 'Dev', 'Paid', 'New York', 'NY', '411060', NULL, '2025-09-20 04:30:10', '2025-09-20 04:30:10'),
(4, 'Mark Zaa', 'Mumbai', '8975647565', 'jay@gmail.com', 'Jay', 'Paid', 'Mumbai', 'Maharashtra', '411060', 'GST-232', '2025-09-26 07:37:55', '2025-09-26 07:37:55');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventories`
--

CREATE TABLE `inventories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `box_id` varchar(255) DEFAULT NULL,
  `box_model` varchar(255) NOT NULL,
  `box_serial_no` varchar(255) NOT NULL,
  `box_mac` varchar(255) NOT NULL,
  `box_fw` varchar(255) DEFAULT NULL,
  `box_remote_model` varchar(255) DEFAULT NULL,
  `warranty_date` date DEFAULT NULL,
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `box_ip` varchar(255) DEFAULT NULL,
  `mgmt_url` varchar(255) DEFAULT NULL,
  `mgmt_token` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventories`
--

INSERT INTO `inventories` (`id`, `box_id`, `box_model`, `box_serial_no`, `box_mac`, `box_fw`, `box_remote_model`, `warranty_date`, `client_id`, `location`, `photo`, `box_ip`, `mgmt_url`, `mgmt_token`, `status`, `created_at`, `updated_at`) VALUES
(1, '104', 'H201', '107-1088400', '5C:0F:FB:1A:56:F0', '25.6.2520.6R', 'Willow', '2025-09-15', 1, 'Mumbai', 'inventories/IOdpCe200OtMFvY0UzHnOBWoiwWAgGji0x48012T.jpg', '192.168.1.50', 'http://192.168.1.50:8090/api/v2', NULL, 1, '2025-09-20 05:58:36', '2025-10-08 06:27:43'),
(2, '103', 'H202', '107-1088401', '5C:0F:FB:1A:56:F1', '25.6.2520.6R', 'Willow', '2025-10-23', 2, 'Mumbai', 'inventories/LRAWLItTzWCdl6sxOAleGZ96nTRhf7OfDqOLenTD.jpg', '192.168.1.51', NULL, NULL, 1, '2025-09-23 06:59:07', '2025-10-08 06:28:07'),
(3, '102', 'H203', '107-1088403', '5C:0F:FB:1A:56:F3', '25.6.2520.6R', 'Willow', '2025-09-30', 1, 'Mumbai', 'inventories/V0q5Hna5pSHy0vjN5CKDirosX1heCcUBzYhqNopg.jpg', '192.168.1.53', 'http://192.168.1.50:8090/api/v2', NULL, 1, '2025-09-26 07:40:54', '2025-10-08 06:28:25'),
(4, '101', 'H204', '107-1088404', '5C:0F:FB:1A:56:F4', '25.6.2520.6R', 'Willow', '2025-10-30', 4, 'Mumbai', 'inventories/4Fa7zc5dcy6xFQyKbADgQsctEXwJCHVJvHo5Pa20.jpg', '192.168.1.52', NULL, NULL, 1, '2025-09-30 05:56:37', '2025-10-08 06:28:17');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_package`
--

CREATE TABLE `inventory_package` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `inventory_id` bigint(20) UNSIGNED NOT NULL,
  `package_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_package`
--

INSERT INTO `inventory_package` (`id`, `inventory_id`, `package_id`, `created_at`, `updated_at`) VALUES
(2, 1, 2, NULL, NULL),
(16, 3, 3, NULL, NULL),
(17, 2, 6, NULL, NULL),
(19, 4, 6, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `location_name` varchar(255) NOT NULL,
  `terminal` varchar(255) DEFAULT NULL,
  `area` varchar(255) DEFAULT NULL,
  `level` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `client_id`, `location_name`, `terminal`, `area`, `level`, `description`, `image_path`, `created_at`, `updated_at`) VALUES
(1, 1, '1st Class Lounge', 'Gate 1', 'Pawane', 'T2', 'Domestic Arrival', NULL, '2025-09-20 08:51:34', '2025-09-20 08:51:34'),
(2, 3, 'Chicago', 'T2', NULL, 'L3', NULL, NULL, '2025-09-20 04:30:10', '2025-09-20 04:30:10'),
(3, 4, 'Thane', 'T2', NULL, 'L3', NULL, NULL, '2025-09-26 07:37:55', '2025-09-26 07:37:55');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_09_19_110854_create_permission_tables', 2),
(5, '2025_09_20_081040_create_clients_table', 3),
(6, '2025_09_20_081206_create_locations_table', 4),
(7, '2025_09_20_102412_create_channels_table', 5),
(9, '2025_09_20_110557_create_inventories_table', 6),
(11, '2025_09_22_060811_create_packages_table', 7),
(12, '2025_09_22_062607_create_channel_package_table', 8),
(14, '2025_09_22_102552_create_inventory_package_table', 9),
(15, '2025_09_22_105101_add_status_to_inventories_table', 10),
(16, '2025_10_01_061144_add_box_ip_mgmt_fields_to_inventories_table', 11);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(3, 'App\\Models\\User', 3);

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `active` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `name`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Package(sports) 1', 'Yes', '2025-09-22 01:05:32', '2025-09-23 05:17:25'),
(2, 'Package(news) 2', 'Yes', '2025-09-22 01:19:05', '2025-09-22 01:19:05'),
(3, 'Package(all) 3', 'Yes', '2025-09-22 01:23:01', '2025-09-22 01:23:18'),
(4, 'Sports VIP', 'Yes', '2025-09-26 07:43:55', '2025-09-26 07:43:55'),
(6, 'Package v1', 'Yes', '2025-09-30 06:49:57', '2025-09-30 06:49:57');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('darshankondekar01@gmail.com', '$2y$12$k8qFGY0rlA4q2j/NIJlyV.nULkGUcgCLuZC059CiBX1SQCrx6PC4O', '2025-09-19 07:26:17');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'view reports', 'web', '2025-09-19 05:47:19', '2025-09-19 05:47:19'),
(2, 'manage inventory', 'web', '2025-09-19 05:47:19', '2025-09-19 05:47:19'),
(3, 'manage channels', 'web', '2025-09-19 05:47:19', '2025-09-19 05:47:19'),
(4, 'manage packages', 'web', '2025-09-19 05:47:19', '2025-09-19 05:47:19'),
(5, 'manage allocations', 'web', '2025-09-19 05:47:19', '2025-09-19 05:47:19'),
(6, 'manage subscribers', 'web', '2025-09-19 05:47:19', '2025-09-19 05:47:19'),
(7, 'permissions.index', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(8, 'permissions.update', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(9, 'roles.index', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(10, 'roles.create', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(11, 'roles.store', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(12, 'roles.destroy', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(13, 'clients.index', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(14, 'clients.create', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(15, 'clients.store', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(16, 'clients.show', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(17, 'clients.edit', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(18, 'clients.update', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(19, 'clients.destroy', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(20, 'locations.index', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(21, 'locations.create', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(22, 'locations.store', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(23, 'locations.show', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(24, 'locations.edit', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(25, 'locations.update', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(26, 'locations.destroy', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(27, 'channels.index', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(28, 'channels.create', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(29, 'channels.store', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(30, 'channels.show', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(31, 'channels.edit', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(32, 'channels.update', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(33, 'channels.destroy', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(34, 'inventories.index', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(35, 'inventories.create', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(36, 'inventories.store', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(37, 'inventories.show', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(38, 'inventories.edit', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(39, 'inventories.update', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(40, 'inventories.destroy', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(41, 'inventories.ping', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(42, 'inventories.reboot', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(43, 'packages.index', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(44, 'packages.create', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(45, 'packages.store', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(46, 'packages.show', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(47, 'packages.edit', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(48, 'packages.update', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(49, 'packages.destroy', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(50, 'inventory-packages.index', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(51, 'inventory-packages.assign', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(52, 'utility.online', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(53, 'reports.index', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(54, 'reports.preview', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(55, 'reports.download', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(56, 'help.index', 'web', '2025-10-01 04:49:55', '2025-10-01 04:49:55'),
(57, 'permissions.store', 'web', '2025-10-01 05:33:34', '2025-10-01 05:33:34'),
(58, 'live-reports.index', 'web', '2025-10-13 07:01:13', '2025-10-13 07:01:13'),
(59, 'live-reports.preview', 'web', '2025-10-13 07:01:32', '2025-10-13 07:01:32'),
(60, 'live-reports.download', 'web', '2025-10-13 07:01:42', '2025-10-13 07:01:42'),
(61, 'installed-reports.index', 'web', '2025-10-13 07:02:02', '2025-10-13 07:02:02'),
(62, 'installed-reports.preview', 'web', '2025-10-13 07:02:10', '2025-10-13 07:02:10'),
(63, 'installed-reports.download', 'web', '2025-10-13 07:02:17', '2025-10-13 07:02:17'),
(64, 'channel-reports.index', 'web', '2025-10-13 07:02:27', '2025-10-13 07:02:27'),
(65, 'channel-reports.preview', 'web', '2025-10-13 07:02:35', '2025-10-13 07:02:35'),
(66, 'channel-reports.download', 'web', '2025-10-13 07:02:41', '2025-10-13 07:02:41'),
(67, 'package-reports.index', 'web', '2025-10-13 07:02:50', '2025-10-13 07:02:50'),
(68, 'package-reports.preview', 'web', '2025-10-13 07:02:58', '2025-10-13 07:02:58'),
(69, 'package-reports.download', 'web', '2025-10-13 07:03:05', '2025-10-13 07:03:05');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'web', '2025-09-19 05:47:19', '2025-09-19 05:47:19'),
(2, 'Manager', 'web', '2025-09-19 05:47:19', '2025-09-19 05:47:19'),
(3, 'Client', 'web', '2025-09-19 05:47:19', '2025-09-19 05:47:19');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(6, 2),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(13, 2),
(14, 1),
(14, 2),
(15, 1),
(15, 2),
(16, 1),
(16, 2),
(17, 1),
(17, 2),
(18, 1),
(18, 2),
(19, 1),
(19, 2),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(34, 2),
(35, 1),
(35, 2),
(36, 1),
(36, 2),
(37, 1),
(37, 2),
(38, 1),
(38, 2),
(39, 1),
(39, 2),
(40, 1),
(40, 2),
(41, 1),
(41, 2),
(42, 1),
(42, 2),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(53, 2),
(53, 3),
(54, 1),
(54, 2),
(54, 3),
(55, 1),
(55, 2),
(55, 3),
(56, 1),
(56, 2),
(56, 3),
(57, 1),
(58, 1),
(59, 1),
(60, 1),
(61, 1),
(62, 1),
(63, 1),
(64, 1),
(65, 1),
(66, 1),
(67, 1),
(68, 1),
(69, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('77Ul1dSbrhnMvrHSiATXxyeXhJeTHuJMIwaEYBZi', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoibHBwQmlzZnEyRWxJTmpFVFhkWkR2elRyR1JEelBhaW1yaFZoWFhyRCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1758518324),
('fOzMGHvpIAIaesiiKQQQ52OYgfSzSLv4iEHnTml0', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWkpScWJtWHNyb21IZTduaHZJNUdkNTZzRk1XOHRrZTNMNmJDUjl5eiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbi9waG9uZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1760424348),
('kE53bJTzx6nbRaooVcKqOTl6eU0MZPKkGztWzz2y', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoieGtqTGRURnU4d3JoMjVsY0ZBeHpCQjltVG02bDJoZDRIME5yNHNtSCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9jbGllbnRzIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1760440896),
('vyhXSKuPT1gewrhhPnqpY6No8YcoGJKGprDiTLKr', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUW9LNmRLREJZNWx0QzRxWlBBclJPWWY1R0dxNnRWRVE0aFIzNllyeCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9pbnZlbnRvcmllcyI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7fQ==', 1759317119);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin01@gmail.com', NULL, '$2y$12$WfChrlkOXOObEVmz01HgQOKOMTVnsrebGKG/VHiX01ZFaRE.ICHye', '6T0fazYRUOiwPaeC4fVgkyolNwYq4sTjOw8k0Ah6OxzOE4DWg3SdZjp2bcOa', '2025-09-19 05:49:53', '2025-09-19 06:49:37'),
(2, 'Saurabh Patel', 'saurabh@gmail.com', NULL, '$2y$12$4cI79qWkNsjFz0N2KhS4wuoI..Am3sM2U9FAkRnxyhQK6dWMAysUu', 'vwo5RbYnNsBiwAWvXO6dc6cZDwDptafMtOipkV7d5wiXyK2Rg0SpYlBOtAKX', '2025-09-22 00:15:36', '2025-09-22 00:15:36'),
(3, 'Rush Tambe', 'rush@gmail.com', NULL, '$2y$12$emvIDkbxlcVmd9UTrrYPXe9.O9Wy03C1P5Jn6nHXIybzZIdz8cx6.', NULL, '2025-10-01 05:44:27', '2025-10-01 05:44:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `channels`
--
ALTER TABLE `channels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `channel_package`
--
ALTER TABLE `channel_package`
  ADD PRIMARY KEY (`id`),
  ADD KEY `channel_package_package_id_foreign` (`package_id`),
  ADD KEY `channel_package_channel_id_foreign` (`channel_id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `inventories`
--
ALTER TABLE `inventories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inventories_box_mac_unique` (`box_mac`),
  ADD KEY `inventories_client_id_foreign` (`client_id`);

--
-- Indexes for table `inventory_package`
--
ALTER TABLE `inventory_package`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_package_inventory_id_foreign` (`inventory_id`),
  ADD KEY `inventory_package_package_id_foreign` (`package_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `locations_client_id_foreign` (`client_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `channels`
--
ALTER TABLE `channels`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `channel_package`
--
ALTER TABLE `channel_package`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventories`
--
ALTER TABLE `inventories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventory_package`
--
ALTER TABLE `inventory_package`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `channel_package`
--
ALTER TABLE `channel_package`
  ADD CONSTRAINT `channel_package_channel_id_foreign` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `channel_package_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventories`
--
ALTER TABLE `inventories`
  ADD CONSTRAINT `inventories_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_package`
--
ALTER TABLE `inventory_package`
  ADD CONSTRAINT `inventory_package_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `inventories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_package_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `locations_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
