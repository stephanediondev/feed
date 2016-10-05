-- phpMyAdmin SQL Dump
-- version 4.6.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 25, 2016 at 03:52 AM
-- Server version: 5.7.14
-- PHP Version: 7.0.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `readerself`
--

--
-- Dumping data for table `action`
--

INSERT INTO `action` VALUES(1, NULL, 'read', '2016-09-28 12:25:57');
INSERT INTO `action` VALUES(2, NULL, 'star', '2016-09-28 12:25:57');
INSERT INTO `action` VALUES(3, NULL, 'subscribe', '2016-09-28 12:25:57');
INSERT INTO `action` VALUES(4, NULL, 'read_all', '2016-09-28 12:25:57');
INSERT INTO `action` VALUES(5, NULL, 'exclude', '2016-09-28 12:25:57');
INSERT INTO `action` VALUES(6, NULL, 'evernote', '2016-09-28 12:25:57');
INSERT INTO `action` VALUES(7, NULL, 'email', '2016-09-28 12:25:57');
INSERT INTO `action` VALUES(8, NULL, 'purge', '2016-09-28 12:25:57');
INSERT INTO `action` VALUES(11, NULL, 'elasticsearch', '2016-09-28 12:25:57');
INSERT INTO `action` VALUES(12, NULL, 'unread', '2016-09-28 12:25:57');
INSERT INTO `action` VALUES(13, NULL, 'unstar', '2016-09-28 00:00:00');
INSERT INTO `action` VALUES(14, NULL, 'include', '2016-09-28 00:00:00');
INSERT INTO `action` VALUES(15, NULL, 'unsubscribe', '2016-09-28 00:00:00');

UPDATE `action` SET reverse = 1 WHERE id = 12;
UPDATE `action` SET reverse = 2 WHERE id = 13;
UPDATE `action` SET reverse = 3 WHERE id = 15;
UPDATE `action` SET reverse = 5 WHERE id = 14;

UPDATE `action` SET reverse = 12 WHERE id = 1;
UPDATE `action` SET reverse = 13 WHERE id = 2;
UPDATE `action` SET reverse = 15 WHERE id = 3;
UPDATE `action` SET reverse = 14 WHERE id = 5;


--
-- Dumping data for table `member`
--

INSERT INTO `member` VALUES(1, 'example@example.com', '$2y$13$A7u4u3TkyKB8AY0o.KJs4.P495uSbI74ECIjuboy7h5mf8OZwetCu', 1, '2016-09-09 20:46:23', '2016-09-09 20:46:23');
