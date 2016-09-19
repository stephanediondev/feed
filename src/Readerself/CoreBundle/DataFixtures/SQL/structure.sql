-- phpMyAdmin SQL Dump
-- version 4.6.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 19, 2016 at 05:52 AM
-- Server version: 5.7.14
-- PHP Version: 7.0.9

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `readerself_symfony`
--

-- --------------------------------------------------------

--
-- Table structure for table `action`
--

DROP TABLE IF EXISTS `action`;
CREATE TABLE `action` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `action_category`
--

DROP TABLE IF EXISTS `action_category`;
CREATE TABLE `action_category` (
  `id` int(10) UNSIGNED NOT NULL,
  `action_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `action_category_member`
--

DROP TABLE IF EXISTS `action_category_member`;
CREATE TABLE `action_category_member` (
  `id` int(10) UNSIGNED NOT NULL,
  `action_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `member_id` int(10) UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `action_feed_member`
--

DROP TABLE IF EXISTS `action_feed_member`;
CREATE TABLE `action_feed_member` (
  `id` int(10) UNSIGNED NOT NULL,
  `feed_id` int(10) UNSIGNED NOT NULL,
  `member_id` int(10) UNSIGNED NOT NULL,
  `action_id` int(10) UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `action_item`
--

DROP TABLE IF EXISTS `action_item`;
CREATE TABLE `action_item` (
  `id` int(10) UNSIGNED NOT NULL,
  `action_id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `action_item_member`
--

DROP TABLE IF EXISTS `action_item_member`;
CREATE TABLE `action_item_member` (
  `id` int(10) UNSIGNED NOT NULL,
  `action_id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `member_id` int(10) UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `author`
--

DROP TABLE IF EXISTS `author`;
CREATE TABLE `author` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `collection`
--

DROP TABLE IF EXISTS `collection`;
CREATE TABLE `collection` (
  `id` int(10) UNSIGNED NOT NULL,
  `feeds` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `errors` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `time` double UNSIGNED NOT NULL DEFAULT '0',
  `memory` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL,
  `date_modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `collection_feed`
--

DROP TABLE IF EXISTS `collection_feed`;
CREATE TABLE `collection_feed` (
  `id` int(10) UNSIGNED NOT NULL,
  `collection_id` int(10) UNSIGNED NOT NULL,
  `feed_id` int(10) UNSIGNED NOT NULL,
  `error` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `connection`
--

DROP TABLE IF EXISTS `connection`;
CREATE TABLE `connection` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enclosure`
--

DROP TABLE IF EXISTS `enclosure`;
CREATE TABLE `enclosure` (
  `id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED DEFAULT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `length` int(10) UNSIGNED DEFAULT NULL,
  `width` int(10) UNSIGNED DEFAULT NULL,
  `height` int(10) UNSIGNED DEFAULT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feed`
--

DROP TABLE IF EXISTS `feed`;
CREATE TABLE `feed` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hostname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `direction` char(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `next_collection` datetime DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  `language` char(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

DROP TABLE IF EXISTS `item`;
CREATE TABLE `item` (
  `id` int(10) UNSIGNED NOT NULL,
  `feed_id` int(10) UNSIGNED NOT NULL,
  `author_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `content_full` longtext COLLATE utf8mb4_unicode_ci,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `item_category`
--

DROP TABLE IF EXISTS `item_category`;
CREATE TABLE `item_category` (
  `id` int(10) UNSIGNED NOT NULL,
  `item_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

DROP TABLE IF EXISTS `member`;
CREATE TABLE `member` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nickname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `administrator` tinyint(1) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL,
  `date_modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `push`
--

DROP TABLE IF EXISTS `push`;
CREATE TABLE `push` (
  `id` int(10) UNSIGNED NOT NULL,
  `member_id` int(10) UNSIGNED NOT NULL,
  `endpoint` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `public_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `authentication_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `date_modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `action`
--
ALTER TABLE `action`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `title` (`title`);

--
-- Indexes for table `action_category`
--
ALTER TABLE `action_category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `action_id_category_id` (`action_id`,`category_id`) USING BTREE,
  ADD KEY `category_id` (`category_id`),
  ADD KEY `action_id` (`action_id`);

--
-- Indexes for table `action_category_member`
--
ALTER TABLE `action_category_member`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `action_id_category_id_member_id` (`action_id`,`category_id`,`member_id`) USING BTREE,
  ADD KEY `member_id` (`member_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `action_id` (`action_id`);

--
-- Indexes for table `action_feed_member`
--
ALTER TABLE `action_feed_member`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `action_id_feed_id_member_id` (`action_id`,`feed_id`,`member_id`),
  ADD KEY `feed_id` (`feed_id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `action_id` (`action_id`);

--
-- Indexes for table `action_item`
--
ALTER TABLE `action_item`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `action_id_item_id` (`action_id`,`item_id`) USING BTREE,
  ADD KEY `item_id` (`item_id`),
  ADD KEY `action_id` (`action_id`);

--
-- Indexes for table `action_item_member`
--
ALTER TABLE `action_item_member`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `action_id_item_id_member_id` (`action_id`,`item_id`,`member_id`) USING BTREE,
  ADD KEY `item_id` (`item_id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `action_id` (`action_id`);

--
-- Indexes for table `author`
--
ALTER TABLE `author`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `title` (`title`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `title` (`title`);

--
-- Indexes for table `collection`
--
ALTER TABLE `collection`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `collection_feed`
--
ALTER TABLE `collection_feed`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `collection_id_feed_id` (`collection_id`,`feed_id`) USING BTREE,
  ADD KEY `collection_id` (`collection_id`),
  ADD KEY `feed_id` (`feed_id`);

--
-- Indexes for table `connection`
--
ALTER TABLE `connection`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_token` (`type`,`token`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `enclosure`
--
ALTER TABLE `enclosure`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `type` (`type`);

--
-- Indexes for table `feed`
--
ALTER TABLE `feed`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `link` (`link`),
  ADD KEY `next_collection` (`next_collection`) USING BTREE;

--
-- Indexes for table `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `link` (`link`),
  ADD KEY `feed_id` (`feed_id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `item_category`
--
ALTER TABLE `item_category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_id_category_id` (`item_id`,`category_id`) USING BTREE,
  ADD KEY `item_id` (`item_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nickname` (`nickname`);

--
-- Indexes for table `push`
--
ALTER TABLE `push`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `endpoint` (`endpoint`),
  ADD KEY `member_id` (`member_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `action`
--
ALTER TABLE `action`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `action_category`
--
ALTER TABLE `action_category`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `action_category_member`
--
ALTER TABLE `action_category_member`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `action_feed_member`
--
ALTER TABLE `action_feed_member`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `action_item`
--
ALTER TABLE `action_item`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `action_item_member`
--
ALTER TABLE `action_item_member`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `author`
--
ALTER TABLE `author`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `collection`
--
ALTER TABLE `collection`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `collection_feed`
--
ALTER TABLE `collection_feed`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `connection`
--
ALTER TABLE `connection`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `enclosure`
--
ALTER TABLE `enclosure`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `feed`
--
ALTER TABLE `feed`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `item`
--
ALTER TABLE `item`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `item_category`
--
ALTER TABLE `item_category`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `push`
--
ALTER TABLE `push`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `action_category`
--
ALTER TABLE `action_category`
  ADD CONSTRAINT `FK_D19B69A712469DE2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_D19B69A79D32F035` FOREIGN KEY (`action_id`) REFERENCES `action` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `action_category_member`
--
ALTER TABLE `action_category_member`
  ADD CONSTRAINT `FK_1C1E667412469DE2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_1C1E66747597D3FE` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_1C1E66749D32F035` FOREIGN KEY (`action_id`) REFERENCES `action` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `action_feed_member`
--
ALTER TABLE `action_feed_member`
  ADD CONSTRAINT `FK_C914034851A5BC03` FOREIGN KEY (`feed_id`) REFERENCES `feed` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_C91403487597D3FE` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_C91403489D32F035` FOREIGN KEY (`action_id`) REFERENCES `action` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `action_item`
--
ALTER TABLE `action_item`
  ADD CONSTRAINT `FK_69FA9E10126F525E` FOREIGN KEY (`item_id`) REFERENCES `item` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_69FA9E109D32F035` FOREIGN KEY (`action_id`) REFERENCES `action` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `action_item_member`
--
ALTER TABLE `action_item_member`
  ADD CONSTRAINT `FK_8DCF0673126F525E` FOREIGN KEY (`item_id`) REFERENCES `item` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_8DCF06737597D3FE` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_8DCF06739D32F035` FOREIGN KEY (`action_id`) REFERENCES `action` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `collection_feed`
--
ALTER TABLE `collection_feed`
  ADD CONSTRAINT `FK_69376845514956FD` FOREIGN KEY (`collection_id`) REFERENCES `collection` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_6937684551A5BC03` FOREIGN KEY (`feed_id`) REFERENCES `feed` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `connection`
--
ALTER TABLE `connection`
  ADD CONSTRAINT `FK_29F773667597D3FE` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enclosure`
--
ALTER TABLE `enclosure`
  ADD CONSTRAINT `FK_E0F73063126F525E` FOREIGN KEY (`item_id`) REFERENCES `item` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `item`
--
ALTER TABLE `item`
  ADD CONSTRAINT `FK_1F1B251E51A5BC03` FOREIGN KEY (`feed_id`) REFERENCES `feed` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_1F1B251EF675F31B` FOREIGN KEY (`author_id`) REFERENCES `author` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `item_category`
--
ALTER TABLE `item_category`
  ADD CONSTRAINT `FK_6A41D10A12469DE2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_6A41D10A126F525E` FOREIGN KEY (`item_id`) REFERENCES `item` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `push`
--
ALTER TABLE `push`
  ADD CONSTRAINT `FK_5F3A16647597D3FE` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE;
SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
