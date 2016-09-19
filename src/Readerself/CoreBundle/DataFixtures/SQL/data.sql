-- phpMyAdmin SQL Dump
-- version 4.6.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 19, 2016 at 05:54 AM
-- Server version: 5.7.14
-- PHP Version: 7.0.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `readerself_symfony`
--

--
-- Dumping data for table `action`
--

INSERT INTO `action` VALUES(1, 'read', '2016-09-09 20:47:09');
INSERT INTO `action` VALUES(2, 'star', '2016-09-09 20:46:23');
INSERT INTO `action` VALUES(3, 'subscribe', '2016-09-09 20:46:23');
INSERT INTO `action` VALUES(4, 'read_auto', '2016-09-09 20:46:23');
INSERT INTO `action` VALUES(5, 'exclude', '2016-09-09 20:46:23');
INSERT INTO `action` VALUES(6, 'evernote', '0000-00-00 00:00:00');
INSERT INTO `action` VALUES(7, 'email', '0000-00-00 00:00:00');
INSERT INTO `action` VALUES(8, 'purge', '2016-08-30 00:00:00');
INSERT INTO `action` VALUES(9, 'readability', '2016-08-31 00:00:00');
INSERT INTO `action` VALUES(10, 'pinboard', '2016-09-09 00:00:00');
INSERT INTO `action` VALUES(11, 'elasticsearch', '2016-09-17 00:00:00');

--
-- Dumping data for table `member`
--

INSERT INTO `member` VALUES(1, 'example@example.com', '$2y$13$3hj3qF1GOQcJokOfB5jcqOnUiUnH7QF3D/X8wqwwrNc3oVesyutiC', NULL, 0, '2014-12-30 20:39:18', '2016-09-11 06:52:43');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
