-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2025 at 06:48 AM
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
('laravel-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:69:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:12:\"view reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:16:\"manage inventory\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:15:\"manage channels\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:15:\"manage packages\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:18:\"manage allocations\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:18:\"manage subscribers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:17:\"permissions.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:18:\"permissions.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:11:\"roles.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:12:\"roles.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:11:\"roles.store\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:13:\"roles.destroy\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:13:\"clients.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:14:\"clients.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:13:\"clients.store\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:12:\"clients.show\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:12:\"clients.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:14:\"clients.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:15:\"clients.destroy\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:15:\"locations.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:16:\"locations.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:15:\"locations.store\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:14:\"locations.show\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:14:\"locations.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:16:\"locations.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:25;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:17:\"locations.destroy\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:26;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:14:\"channels.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:27;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:15:\"channels.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:28;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:14:\"channels.store\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:29;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:13:\"channels.show\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:30;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:13:\"channels.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:31;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:15:\"channels.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:32;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:16:\"channels.destroy\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:33;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:17:\"inventories.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:34;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:18:\"inventories.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:35;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:17:\"inventories.store\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:36;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:16:\"inventories.show\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:37;a:4:{s:1:\"a\";i:38;s:1:\"b\";s:16:\"inventories.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:38;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:18:\"inventories.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:39;a:4:{s:1:\"a\";i:40;s:1:\"b\";s:19:\"inventories.destroy\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:40;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:16:\"inventories.ping\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:41;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:18:\"inventories.reboot\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:42;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:14:\"packages.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:43;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:15:\"packages.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:44;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:14:\"packages.store\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:45;a:4:{s:1:\"a\";i:46;s:1:\"b\";s:13:\"packages.show\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:46;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:13:\"packages.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:47;a:4:{s:1:\"a\";i:48;s:1:\"b\";s:15:\"packages.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:48;a:4:{s:1:\"a\";i:49;s:1:\"b\";s:16:\"packages.destroy\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:49;a:4:{s:1:\"a\";i:50;s:1:\"b\";s:24:\"inventory-packages.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:50;a:4:{s:1:\"a\";i:51;s:1:\"b\";s:25:\"inventory-packages.assign\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:51;a:4:{s:1:\"a\";i:52;s:1:\"b\";s:14:\"utility.online\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:52;a:4:{s:1:\"a\";i:53;s:1:\"b\";s:13:\"reports.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:53;a:4:{s:1:\"a\";i:54;s:1:\"b\";s:15:\"reports.preview\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:54;a:4:{s:1:\"a\";i:55;s:1:\"b\";s:16:\"reports.download\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:55;a:4:{s:1:\"a\";i:56;s:1:\"b\";s:10:\"help.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:56;a:4:{s:1:\"a\";i:57;s:1:\"b\";s:17:\"permissions.store\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:57;a:4:{s:1:\"a\";i:58;s:1:\"b\";s:18:\"live-reports.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:58;a:4:{s:1:\"a\";i:59;s:1:\"b\";s:20:\"live-reports.preview\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:59;a:4:{s:1:\"a\";i:60;s:1:\"b\";s:21:\"live-reports.download\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:60;a:4:{s:1:\"a\";i:61;s:1:\"b\";s:23:\"installed-reports.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:61;a:4:{s:1:\"a\";i:62;s:1:\"b\";s:25:\"installed-reports.preview\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:62;a:4:{s:1:\"a\";i:63;s:1:\"b\";s:26:\"installed-reports.download\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:63;a:4:{s:1:\"a\";i:64;s:1:\"b\";s:21:\"channel-reports.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:64;a:4:{s:1:\"a\";i:65;s:1:\"b\";s:23:\"channel-reports.preview\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:65;a:4:{s:1:\"a\";i:66;s:1:\"b\";s:24:\"channel-reports.download\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:66;a:4:{s:1:\"a\";i:67;s:1:\"b\";s:21:\"package-reports.index\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:67;a:4:{s:1:\"a\";i:68;s:1:\"b\";s:23:\"package-reports.preview\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:68;a:4:{s:1:\"a\";i:69;s:1:\"b\";s:24:\"package-reports.download\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:3:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:5:\"Admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:7:\"Manager\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:6:\"Client\";s:1:\"c\";s:3:\"web\";}}}', 1761549872);

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
  `channel_id` bigint(20) UNSIGNED DEFAULT NULL,
  `channel_name` varchar(255) NOT NULL,
  `broadcast` longtext DEFAULT NULL,
  `channel_source_in` varchar(255) DEFAULT NULL,
  `channel_source_details` varchar(255) DEFAULT NULL,
  `channel_stream_type_out` varchar(255) DEFAULT NULL,
  `channel_url` varchar(255) DEFAULT NULL,
  `channel_genre` varchar(255) DEFAULT NULL,
  `channel_resolution` varchar(255) DEFAULT NULL,
  `channel_type` varchar(255) DEFAULT NULL,
  `language` varchar(255) DEFAULT NULL,
  `encryption` tinyint(1) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `channels`
--

INSERT INTO `channels` (`id`, `channel_id`, `channel_name`, `broadcast`, `channel_source_in`, `channel_source_details`, `channel_stream_type_out`, `channel_url`, `channel_genre`, `channel_resolution`, `channel_type`, `language`, `encryption`, `active`, `created_at`, `updated_at`) VALUES
(1, 1, 'CNBC Prime HD', '', '239.15.0.11:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(2, 2, 'Times Now World HD', '', '239.15.0.12:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(3, 3, 'CNN INTL', '', '239.15.0.13:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(4, 4, 'CNN News 18', '', '239.15.0.14:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(5, 5, 'ET Now', '', '239.15.0.15:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(6, 6, 'BBC World News', '', '239.15.0.16:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(7, 7, 'NDTV 24 x 7', '', '239.15.0.17:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(8, 8, 'France 24', '', '239.15.0.18:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(9, 9, 'WION', '', '239.15.0.19:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(10, 10, 'Republic Bharat', '', '239.15.0.20:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(11, 11, 'Zee News', '', '239.15.0.21:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(12, 12, 'TV9 Bharatvrarsh', '', '239.15.0.22:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(13, 13, 'Zee Hindustan', '', '239.15.0.23:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(14, 14, 'TV9 Maharashtra', '', '239.15.0.24:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(15, 15, 'Zee 24 Taas', '', '239.15.0.25:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(16, 16, '&flix HD', '', '239.15.0.26:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(17, 17, '&prive HD', '', '239.15.0.27:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(18, 18, 'MN+ HD', '', '239.15.0.28:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(19, 19, 'MNX HD', '', '239.15.0.29:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(20, 20, 'MOVIES NOW HD', '', '239.15.0.30:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(21, 21, 'Romedy Now', '', '239.15.0.31:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(22, 22, 'SONY PIX HD', '', '239.15.0.32:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(23, 23, 'Star Movies HD', '', '239.15.0.33:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(24, 24, 'Star Movies Select HD', '', '239.15.0.34:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(25, 25, 'SONY MAX HD', '', '239.15.0.35:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(26, 26, 'Star Gold HD', '', '239.15.0.36:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(27, 27, 'Star Gold 2', '', '239.15.0.37:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(28, 28, 'Star Gold Select HD', '', '239.15.0.38:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(29, 29, 'Zee Cinema HD', '', '239.15.0.39:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(30, 30, 'Zee Action', '', '239.15.0.40:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(31, 31, 'Zee Anmol Cinema', '', '239.15.0.41:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(32, 32, 'Zee Bollywood', '', '239.15.0.42:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(33, 33, 'Zee Talkies HD', '', '239.15.0.43:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(34, 34, 'Sports', '', '239.15.0.44:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(35, 35, 'SONY SPORTS TEN 1 HD', '', '239.15.0.45:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(36, 36, 'SONY SPORTS TEN 2 HD', '', '239.15.0.46:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(37, 37, 'SONY SPORTS TEN 3 HD', '', '239.15.0.47:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(38, 38, 'SONY SPORTS TEN 5 HD', '', '239.15.0.48:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(39, 39, 'Cartoon Network', '', '239.15.0.49:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(40, 40, 'POGO', '', '239.15.0.50:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(41, 41, 'DD News', '', '239.15.0.101:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(42, 42, 'DD National', '', '239.15.0.102:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(43, 43, 'DD Retro', '', '239.15.0.103:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(44, 44, 'DD Kisan', '', '239.15.0.104:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(45, 45, 'DD India', '', '239.15.0.105:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(46, 46, 'Sun Marathi', '', '239.15.0.106:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(47, 47, 'Showbox', '', '239.15.0.107:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(48, 48, 'DD Girnar', '', '239.15.0.108:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(49, 49, 'Filamchi Bhojpuri', '', '239.15.0.109:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(50, 50, 'Abzy Movie', '', '239.15.0.110:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(51, 51, 'Vedic', '', '239.15.0.111:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(52, 52, 'B4u Movies', '', '239.15.0.112:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(53, 53, 'Good News Today', '', '239.15.0.113:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(54, 54, 'Dhamaka Movie', '', '239.15.0.114:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(55, 55, 'Shemaroo Umang', '', '239.15.0.115:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(56, 56, 'Zee ganga', '', '239.15.0.116:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(57, 57, 'Goldmine Bhojouri', '', '239.15.0.117:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(58, 58, 'Manoranjan Prime', '', '239.15.0.118:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(59, 59, 'Manoranjan Grand', '', '239.15.0.119:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(60, 60, 'Shemaroo Marathibana', '', '239.15.0.120:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(61, 61, 'DD Podhigai', '', '239.15.0.121:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(62, 62, 'DD Punjabi', '', '239.15.0.122:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(63, 63, 'DD Sahyadri', '', '239.15.0.123:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(64, 64, 'Fact Marathi', '', '239.15.0.124:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(65, 65, 'Sports 18', '', '239.15.0.125:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(66, 66, 'Sansad Tv', '', '239.15.0.126:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(67, 67, 'Sansad Tv Rajya Sabha', '', '239.15.0.127:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(68, 68, 'Shemaroo', '', '239.15.0.128:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(69, 69, 'Dangal', '', '239.15.0.129:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(70, 70, 'Bhojpuri Cinema', '', '239.15.0.130:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(71, 71, 'Zee Biskope', '', '239.15.0.131:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(72, 72, 'ABZY Cool', '', '239.15.0.132:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(73, 73, 'Goldmine', '', '239.15.0.133:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(74, 74, 'The Q', '', '239.15.0.134:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(75, 75, 'Cineplex Bollywood', '', '239.15.0.135:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(76, 76, 'Goldmine Bollywood', '', '239.15.0.136:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(77, 77, 'Dnagal 2', '', '239.15.0.137:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(78, 78, 'Rishtey Cineplex', '', '239.15.0.138:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(79, 79, 'Movie Plus', '', '239.15.0.139:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(80, 80, 'Manoranjan Movies', '', '239.15.0.140:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(81, 81, 'Big Magic', '', '239.15.0.141:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(82, 82, 'B4U KADAK', '', '239.15.0.142:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(83, 83, 'Manoranjan TV', '', '239.15.0.143:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(84, 84, 'TV9 Bharatvarsh', '', '239.15.0.144:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(85, 85, 'AASTHA', '', '239.15.0.145:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(86, 86, 'Ishara TV', '', '239.15.0.146:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(87, 87, 'Zing', '', '239.15.0.147:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(88, 88, 'ZEE ANMOL CINEMA', '', '239.15.0.148:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(89, 89, 'NDTV India', '', '239.15.0.149:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(90, 90, 'News 24 Think First', '', '239.15.0.150:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(91, 91, 'Enter10', '', '239.15.0.151:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(92, 92, 'Dhinchaak 2', '', '239.15.0.152:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(93, 93, 'Popcorn Movies', '', '239.15.0.153:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(94, 94, 'Sanskar TV', '', '239.15.0.154:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(95, 95, 'Star Utsav Movies', '', '239.15.0.155:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(96, 96, 'News 18 India', '', '239.15.0.156:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(97, 97, '9XM', '', '239.15.0.157:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(98, 98, 'Sony Wah', '', '239.15.0.158:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(99, 99, 'ZEE Hindustan', '', '239.15.0.159:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(100, 100, 'India News', '', '239.15.0.160:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(101, 101, 'Test Gate International', '', '239.15.0.1:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(102, 102, 'Test Gate Domestic', '', '239.15.0.2:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(103, 103, 'MTV Beats', '', '239.15.0.161:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(104, 104, 'Masti', '', '239.15.0.162:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(105, 105, 'B4U Music', '', '239.15.0.163:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(106, 106, 'India TV', '', '239.15.0.164:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(107, 107, 'News Nation', '', '239.15.0.165:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(108, 108, 'Times Now Navbhara', '', '239.15.0.166:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(109, 109, 'Republic Bharat', '', '239.15.0.167:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(110, 110, 'Aaj Tak', '', '239.15.0.168:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(111, 111, 'ABP News', '', '239.15.0.169:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(112, 112, 'ZEE News', '', '239.15.0.170:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(113, 113, 'ZEE Chitra Mandir', '', '239.15.0.171:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(114, 114, 'ZEE Punjabi', '', '239.15.0.172:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(115, 115, 'B4U Bhojpuri', '', '239.15.0.173:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(116, 116, 'Bharat 24 Vision of New India', '', '239.15.0.174:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(117, 117, 'DD Yadagiri', '', '239.15.0.175:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(118, 118, 'DD UP', '', '239.15.0.176:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(119, 119, 'DD Bharati', '', '239.15.0.177:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(120, 120, 'DD Rajasthan', '', '239.15.0.178:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(121, 121, 'DD Sports', '', '239.15.0.179:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(122, 122, 'DD Bihar', '', '239.15.0.180:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(123, 123, 'DD Jharkhand', '', '239.15.0.181:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(124, 124, 'DD MP', '', '239.15.0.182:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(125, 125, 'DD Tripura', '', '239.15.0.183:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(126, 126, 'DD CHHATTISGARH', '', '239.15.0.184:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(127, 127, 'DD Kashir', '', '239.15.0.185:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(128, 128, 'DD Chandana', '', '239.15.0.186:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(129, 129, 'DD UTTARAKHAND', '', '239.15.0.187:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(130, 130, 'DD Saptagiri', '', '239.15.0.188:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(131, 131, 'DD Malayalam', '', '239.15.0.189:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(132, 132, 'DD Assam', '', '239.15.0.190:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(133, 133, 'DD Oriya', '', '239.15.0.191:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(134, 134, 'DD Arunprabha', '', '239.15.0.192:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(135, 135, 'DD Bangla', '', '239.15.0.193:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(136, 136, 'Aastha Bhajan', '', '239.15.0.194:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(137, 137, 'Chardikla Time TV', '', '239.15.0.195:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(138, 138, 'DD GOA', '', '239.15.0.196:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(139, 139, 'DD Haryana', '', '239.15.0.197:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(140, 140, 'DD Himachal Pradesh', '', '239.15.0.198:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(141, 141, 'MH1 DilSe', '', '239.15.0.199:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14'),
(142, 142, 'Bansal News', '', '239.15.0.200:1234', '', '', '', '', '', 'Paid', '', NULL, 1, '2025-10-24 05:26:14', '2025-10-24 05:26:14');

--
-- Triggers `channels`
--
DELIMITER $$
CREATE TRIGGER `trg_channels_bi_channel_id` BEFORE INSERT ON `channels` FOR EACH ROW BEGIN
                IF NEW.channel_id IS NULL OR NEW.channel_id = 0 THEN
                    INSERT INTO channel_sequences (stub) VALUES ('a');
                    SET NEW.channel_id = LAST_INSERT_ID();
                END IF;
            END
$$
DELIMITER ;

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
-- Table structure for table `channel_sequences`
--

CREATE TABLE `channel_sequences` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `stub` char(1) NOT NULL DEFAULT 'a'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `box_subnet` varchar(255) DEFAULT NULL,
  `gateway` varchar(255) DEFAULT NULL,
  `box_os` varchar(255) DEFAULT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `box_fw` varchar(255) DEFAULT NULL,
  `box_remote_model` varchar(255) DEFAULT NULL,
  `warranty_date` date DEFAULT NULL,
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `terminal` varchar(255) DEFAULT NULL,
  `level` varchar(255) DEFAULT NULL,
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

INSERT INTO `inventories` (`id`, `box_id`, `box_model`, `box_serial_no`, `box_mac`, `box_subnet`, `gateway`, `box_os`, `supplier_name`, `box_fw`, `box_remote_model`, `warranty_date`, `client_id`, `location`, `terminal`, `level`, `photo`, `box_ip`, `mgmt_url`, `mgmt_token`, `status`, `created_at`, `updated_at`) VALUES
(1, '104', 'H201', '107-1088400', '5C:0F:FB:1A:56:F0', NULL, NULL, NULL, NULL, '25.6.2520.6R', 'Willow', '2025-09-15', 1, 'Mumbai', NULL, NULL, 'inventories/IOdpCe200OtMFvY0UzHnOBWoiwWAgGji0x48012T.jpg', '192.168.1.50', 'http://192.168.1.50:8090/api/v2', NULL, 1, '2025-09-20 05:58:36', '2025-10-08 06:27:43'),
(2, '103', 'H202', '107-1088401', '5C:0F:FB:1A:56:F1', NULL, NULL, NULL, NULL, '25.6.2520.6R', 'Willow', '2025-10-23', 2, 'Mumbai', NULL, NULL, 'inventories/LRAWLItTzWCdl6sxOAleGZ96nTRhf7OfDqOLenTD.jpg', '192.168.1.51', NULL, NULL, 1, '2025-09-23 06:59:07', '2025-10-08 06:28:07'),
(3, '102', 'H203', '107-1088403', '5C:0F:FB:1A:56:F3', NULL, NULL, NULL, NULL, '25.6.2520.6R', 'Willow', '2025-09-30', 1, 'Mumbai', NULL, NULL, 'inventories/V0q5Hna5pSHy0vjN5CKDirosX1heCcUBzYhqNopg.jpg', '192.168.1.53', 'http://192.168.1.50:8090/api/v2', NULL, 1, '2025-09-26 07:40:54', '2025-10-08 06:28:25'),
(4, '101', 'H204', '107-1088404', '5C:0F:FB:1A:56:F4', NULL, NULL, NULL, NULL, '25.6.2520.6R', 'Willow', '2025-10-30', 4, 'Mumbai', NULL, NULL, 'inventories/4Fa7zc5dcy6xFQyKbADgQsctEXwJCHVJvHo5Pa20.jpg', '192.168.1.52', NULL, NULL, 1, '2025-09-30 05:56:37', '2025-10-08 06:28:17'),
(5, '105', 'H205', '107-1088405', '5C:0F:FB:1A:56:F5', NULL, NULL, NULL, NULL, '25.6.2520.6R', 'Willow', '2025-10-30', 4, 'Mumbai', NULL, NULL, 'inventories/4Fa7zc5dcy6xFQyKbADgQsctEXwJCHVJvHo5Pa20.jpg', '192.168.1.52', NULL, NULL, 1, '2025-09-30 05:56:37', '2025-10-08 06:28:17'),
(6, '106', 'H206', '107-1088406', '5C:0F:FB:1A:56:F6', NULL, NULL, NULL, NULL, '25.6.2520.6R', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2025-10-24 00:37:31', '2025-10-24 00:37:31'),
(7, '107', 'H200', '107-10877558', '5C:0F:FB:1A:C7:EC', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.150', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(8, '108', 'H200', '107-10877096', '5C:0F:FB:1A:C4:50', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.151', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(9, '109', 'H200', '107-10877515', '5C:0F:FB:1A:C7:96', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.152', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(10, '110', 'H200', '107-10877849', '5C:0F:FB:1A:CA:32', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.153', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(11, '111', 'H200', '107-10877563', '5C:0F:FB:1A:C7:F6', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.154', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(12, '112', 'H200', '107-10878359', '5C:0F:FB:1A:CE:2E', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.155', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(13, '113', 'H200', '107-10878337', '5C:0F:FB:1A:CE:02', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.156', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(14, '114', 'H200', '107-10877947', '5C:0F:FB:1A:CA:F6', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.157', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(15, '115', 'H200', '107-10877556', '5C:0F:FB:1A:C7:E8', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.158', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(16, '116', 'H200', '107-10877274', '5C:0F:FB:1A:C5:B4', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.159', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(17, '117', 'H200', '107-10878084', '5C:0F:FB:1A:CC:08', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.160', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(18, '118', 'H200', '107-10877567', '5C:0F:FB:1A:C7:FE', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.161', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(19, '119', 'H200', '107-10878275', '5C:0F:FB:1A:CD:86', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.162', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(20, '120', 'H200', '107-10878352', '5C:0F:FB:1A:CE:20', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.163', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(21, '121', 'H200', '107-10877552', '5C:0F:FB:1A:C7:E0', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.164', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(22, '122', 'H200', '107-10878377', '5C:0F:FB:1A:CE:52', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.165', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(23, '123', 'H200', '107-10877281', '5C:0F:FB:1A:C5:C2', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.166', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(24, '124', 'H200', '107-10877254', '5C:0F:FB:1A:C5:8C', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.167', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(25, '125', 'H200', '107-10877442', '5C:0F:FB:1A:C7:04', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.168', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(26, '126', 'H200', '107-10877387', '5C:0F:FB:1A:C6:96', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.169', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(27, '127', 'H200', '107-10877282', '5C:0F:FB:1A:C5:C4', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.170', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(28, '128', 'H200', '107-10877559', '5C:0F:FB:1A:C7:EE', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.171', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(29, '129', 'H200', '107-10877561', '5C:0F:FB:1A:C7:F2', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.172', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(30, '130', 'H200', '107-10878498', '5C:0F:FB:1A:CF:44', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.173', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(31, '131', 'H200', '107-10877253', '5C:0F:FB:1A:C5:8A', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.174', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(32, '132', 'H200', '107-10877275', '5C:0F:FB:1A:C5:B6', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.175', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(33, '133', 'H200', '107-10877581', '5C:0F:FB:1A:C8:1A', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.176', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(34, '134', 'H200', '107-10877722', '5C:0F:FB:1A:C9:34', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.177', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(35, '135', 'H200', '107-10878521', '5C:0F:FB:1A:CF:72', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.178', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(36, '136', 'H200', '107-10878134', '5C:0F:FB:1A:CC:6C', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.179', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(37, '137', 'H200', '107-10877726', '5C:0F:FB:1A:C9:3C', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.180', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(38, '138', 'H200', '107-10877595', '5C:0F:FB:1A:C8:36', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.181', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(39, '139', 'H200', '107-10878278', '5C:0F:FB:1A:CD:8C', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.182', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(40, '140', 'H200', '107-10877499', '5C:0F:FB:1A:C7:76', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.183', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(41, '141', 'H200', '107-10877587', '5C:0F:FB:1A:C8:26', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.184', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(42, '142', 'H200', '107-10877577', '5C:0F:FB:1A:C8:12', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.185', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(43, '143', 'H200', '107-10877707', '5C:0F:FB:1A:C9:16', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.186', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(44, '144', 'H200', '107-10878188', '5C:0F:FB:1A:CC:D8', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.187', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(45, '145', 'H200', '107-10877441', '5C:0F:FB:1A:C7:02', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.188', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(46, '146', 'H200', '107-10877684', '5C:0F:FB:1A:C8:E8', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.189', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(47, '147', 'H200', '107-10877548', '5C:0F:FB:1A:C7:D8', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.190', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(48, '148', 'H200', '107-10877438', '5C:0F:FB:1A:C6:FC', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.191', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(49, '149', 'H200', '107-10877462', '5C:0F:FB:1A:C7:2C', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.192', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(50, '150', 'H200', '107-10877544', '5C:0F:FB:1A:C7:D0', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.193', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(51, '151', 'H200', '107-10877443', '5C:0F:FB:1A:C7:06', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.194', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(52, '152', 'H200', '107-10877696', '5C:0F:FB:1A:C9:00', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.195', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(53, '153', 'H200', '107-10878524', '5C:0F:FB:1A:CF:78', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.196', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(54, '154', 'H200', '107-10877697', '5C:0F:FB:1A:C9:02', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.197', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(55, '155', 'H200', '107-10878536', '5C:0F:FB:1A:CF:90', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.198', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(56, '156', 'H200', '107-10877574', '5C:0F:FB:1A:C8:0C', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.199', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(57, '157', 'H200', '107-10878497', '5C:0F:FB:1A:CF:42', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.200', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(58, '158', 'H200', '107-10877573', '5C:0F:FB:1A:C8:0A', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.201', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(59, '159', 'H200', '107-10878513', '5C:0F:FB:1A:CF:62', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.202', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(60, '160', 'H200', '107-10877720', '5C:0F:FB:1A:C9:30', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.203', NULL, NULL, 0, NULL, '2025-10-24 03:52:12'),
(61, '161', 'H200', '107-10877309', '5C:0F:FB:1A:C5:FA', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '192.168.1.204', NULL, NULL, 0, NULL, '2025-10-24 03:52:12');

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
(16, 3, 3, NULL, NULL),
(28, 4, 2, NULL, NULL),
(32, 1, 3, NULL, NULL),
(34, 2, 4, NULL, NULL),
(35, 6, 4, NULL, NULL),
(36, 5, 3, NULL, NULL);

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
(16, '2025_10_01_061144_add_box_ip_mgmt_fields_to_inventories_table', 11),
(17, '2025_10_13_050057_add_fields_to_inventories_table', 12),
(18, '2025_10_13_054657_add_unique_inventory_no_in_inventories_table', 12),
(19, '2025_10_22_074252_add_terminal_level_to_inventories_table', 12),
(20, '2025_10_24_063934_add_broadcast_to_channels_table', 13),
(21, '2025_10_27_042043_add_channel_id_to_channels_table', 14);

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
('fOzMGHvpIAIaesiiKQQQ52OYgfSzSLv4iEHnTml0', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWkpScWJtWHNyb21IZTduaHZJNUdkNTZzRk1XOHRrZTNMNmJDUjl5eiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbi9waG9uZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1760424348),
('kE53bJTzx6nbRaooVcKqOTl6eU0MZPKkGztWzz2y', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoieGtqTGRURnU4d3JoMjVsY0ZBeHpCQjltVG02bDJoZDRIME5yNHNtSCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9jbGllbnRzIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1760440976),
('uA34YkMWdeFVqQWmzRnD1Jz7BZuiIsAwCW4cFvaB', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWnVYeFp2S0lXMjNXTmN1U1I5b1lZQUFmZ2Z4UnhZcnZuaWxCbkFjOCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9jbGllbnRzIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1761543993),
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
(1, 'Admin', 'admin01@gmail.com', NULL, '$2y$12$WfChrlkOXOObEVmz01HgQOKOMTVnsrebGKG/VHiX01ZFaRE.ICHye', 'Ddf0ElP3boRBbqYKA60UmBXj9kzgF3Vl9AEuefOcFdrFS1XuKeLpjJEFaq5p', '2025-09-19 05:49:53', '2025-09-19 06:49:37'),
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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `channels_channel_id_unique` (`channel_id`);

--
-- Indexes for table `channel_package`
--
ALTER TABLE `channel_package`
  ADD PRIMARY KEY (`id`),
  ADD KEY `channel_package_package_id_foreign` (`package_id`),
  ADD KEY `channel_package_channel_id_foreign` (`channel_id`);

--
-- Indexes for table `channel_sequences`
--
ALTER TABLE `channel_sequences`
  ADD PRIMARY KEY (`id`);

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
  ADD UNIQUE KEY `inventories_box_serial_no_unique` (`box_serial_no`),
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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT for table `channel_package`
--
ALTER TABLE `channel_package`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `channel_sequences`
--
ALTER TABLE `channel_sequences`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `inventory_package`
--
ALTER TABLE `inventory_package`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

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
