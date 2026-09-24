-- GWTTT complete fresh-install database initialization
-- Generated from the production GWTTT schema and static/reference catalog data.
-- Source snapshot: 2026-09-23, MariaDB 10.11.14.
--
-- IMPORTANT: Run this against an EMPTY database. Do not run it against the
-- existing production gwst database. It intentionally contains no user,
-- account, character, stats, inventory, history, authentication, reminder,
-- preference, or production mail-settings rows.

/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: gwst
-- ------------------------------------------------------
-- Server version	10.11.14-MariaDB-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `auth_throttle`
--

DROP TABLE IF EXISTS `auth_throttle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `auth_throttle` (
  `throttle_key` varbinary(191) NOT NULL,
  `action_type` varchar(16) NOT NULL,
  `failure_count` int(10) unsigned NOT NULL DEFAULT 0,
  `window_started_at` datetime NOT NULL,
  `blocked_until` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`throttle_key`,`action_type`),
  KEY `idx_auth_throttle_updated` (`updated_at`),
  KEY `idx_auth_throttle_blocked` (`blocked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwaccounts`
--

DROP TABLE IF EXISTS `gwaccounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwaccounts` (
  `accid` int(11) NOT NULL AUTO_INCREMENT COMMENT 'this key will be bound by charid in table gwchars',
  `userid` int(11) NOT NULL,
  `accemail` varchar(50) NOT NULL,
  PRIMARY KEY (`accid`),
  KEY `idx_gwaccounts_userid` (`userid`),
  CONSTRAINT `fk_gwaccounts_user` FOREIGN KEY (`userid`) REFERENCES `userinfo` (`userid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwchars`
--

DROP TABLE IF EXISTS `gwchars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwchars` (
  `charid` int(11) NOT NULL AUTO_INCREMENT,
  `accid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `charname` varchar(19) NOT NULL,
  `birthdate` date DEFAULT NULL,
  `profid` int(11) NOT NULL,
  `profcolor` char(7) NOT NULL DEFAULT '#45b39d',
  PRIMARY KEY (`charid`),
  KEY `idx_gwchars_userid` (`userid`),
  KEY `idx_gwchars_accid` (`accid`),
  KEY `idx_gwchars_profid` (`profid`),
  CONSTRAINT `fk_gwchars_account` FOREIGN KEY (`accid`) REFERENCES `gwaccounts` (`accid`) ON DELETE CASCADE,
  CONSTRAINT `fk_gwchars_profession` FOREIGN KEY (`profid`) REFERENCES `gwprofessions` (`profid`),
  CONSTRAINT `fk_gwchars_user` FOREIGN KEY (`userid`) REFERENCES `userinfo` (`userid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwminiature_group_members`
--

DROP TABLE IF EXISTS `gwminiature_group_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwminiature_group_members` (
  `groupid` smallint(5) unsigned NOT NULL,
  `miniid` int(10) unsigned NOT NULL,
  `display_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`groupid`,`miniid`),
  KEY `idx_gwminiature_group_members_miniid` (`miniid`),
  KEY `idx_gwminiature_group_members_order` (`groupid`,`display_order`,`miniid`),
  CONSTRAINT `fk_gwminiature_group_members_group` FOREIGN KEY (`groupid`) REFERENCES `gwminiature_groups` (`groupid`) ON DELETE CASCADE,
  CONSTRAINT `fk_gwminiature_group_members_mini` FOREIGN KEY (`miniid`) REFERENCES `gwminiatures` (`miniid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwminiature_groups`
--

DROP TABLE IF EXISTS `gwminiature_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwminiature_groups` (
  `groupid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `groupkey` varchar(32) NOT NULL,
  `groupname` varchar(64) NOT NULL,
  `display_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `group_note` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`groupid`),
  UNIQUE KEY `uq_gwminiature_groups_key` (`groupkey`),
  KEY `idx_gwminiature_groups_order` (`display_order`,`groupname`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwminiature_inventory`
--

DROP TABLE IF EXISTS `gwminiature_inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwminiature_inventory` (
  `userid` int(11) NOT NULL,
  `accid` int(11) NOT NULL,
  `miniid` int(10) unsigned NOT NULL,
  `dedicated` tinyint(1) NOT NULL DEFAULT 0,
  `quantity` int(10) unsigned NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`userid`,`accid`,`miniid`),
  KEY `idx_gwminiature_inventory_accid` (`accid`),
  KEY `idx_gwminiature_inventory_miniid` (`miniid`),
  CONSTRAINT `fk_gwminiature_inventory_mini` FOREIGN KEY (`miniid`) REFERENCES `gwminiatures` (`miniid`) ON DELETE CASCADE,
  CONSTRAINT `chk_gwminiature_inventory_dedicated` CHECK (`dedicated` in (0,1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwminiatures`
--

DROP TABLE IF EXISTS `gwminiatures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwminiatures` (
  `miniid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mininame` varchar(100) NOT NULL,
  `rarity` varchar(16) NOT NULL DEFAULT 'White',
  `wiki_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`miniid`),
  UNIQUE KEY `uq_gwminiatures_name` (`mininame`)
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwprofessions`
--

DROP TABLE IF EXISTS `gwprofessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwprofessions` (
  `profid` int(2) NOT NULL AUTO_INCREMENT,
  `profession` varchar(12) NOT NULL,
  `profcolor` char(4) NOT NULL,
  PRIMARY KEY (`profid`),
  UNIQUE KEY `uq_gwprofessions_profession` (`profession`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwstats`
--

DROP TABLE IF EXISTS `gwstats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwstats` (
  `titlenameid` int(11) NOT NULL,
  `stnameid` int(11) DEFAULT NULL,
  `titlepoints` int(11) DEFAULT NULL,
  `currentstrankname` varchar(37) DEFAULT NULL,
  `currentstrank` int(11) DEFAULT NULL,
  `percent` int(11) DEFAULT NULL,
  `gwamm` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `charid` int(11) NOT NULL DEFAULT 0 COMMENT '0 denotes an account-wide title',
  `accid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  PRIMARY KEY (`userid`,`accid`,`charid`,`titlenameid`),
  KEY `idx_gwstats_accid` (`accid`),
  KEY `idx_gwstats_titlenameid` (`titlenameid`),
  KEY `idx_gwstats_stnameid` (`stnameid`),
  CONSTRAINT `fk_gwstats_account` FOREIGN KEY (`accid`) REFERENCES `gwaccounts` (`accid`) ON DELETE CASCADE,
  CONSTRAINT `fk_gwstats_subtitle` FOREIGN KEY (`stnameid`) REFERENCES `gwsubtitles` (`stnameid`) ON DELETE SET NULL,
  CONSTRAINT `fk_gwstats_title` FOREIGN KEY (`titlenameid`) REFERENCES `gwtitles` (`titlenameid`) ON DELETE CASCADE,
  CONSTRAINT `fk_gwstats_user` FOREIGN KEY (`userid`) REFERENCES `userinfo` (`userid`) ON DELETE CASCADE,
  CONSTRAINT `chk_gwstats_gwamm` CHECK (`gwamm` in (0,1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwsubtitles`
--

DROP TABLE IF EXISTS `gwsubtitles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwsubtitles` (
  `stnameid` int(11) NOT NULL AUTO_INCREMENT,
  `titlenameid` int(11) NOT NULL,
  `stname` varchar(50) NOT NULL,
  `stpoints` int(11) NOT NULL,
  `strank` int(11) NOT NULL,
  PRIMARY KEY (`stnameid`),
  UNIQUE KEY `uq_gwsubtitles_title_rank` (`titlenameid`,`strank`),
  KEY `idx_gwsubtitles_titlenameid` (`titlenameid`),
  CONSTRAINT `fk_gwsubtitles_title` FOREIGN KEY (`titlenameid`) REFERENCES `gwtitles` (`titlenameid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=322 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwtitles`
--

DROP TABLE IF EXISTS `gwtitles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwtitles` (
  `titlenameid` int(2) NOT NULL AUTO_INCREMENT,
  `titlename` varchar(40) NOT NULL,
  `titletype` tinyint(3) unsigned NOT NULL COMMENT '0 = account, 1 = character',
  `titlemaxrank` int(11) NOT NULL,
  `autofilled` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '0 = no, 1 = yes',
  `gwamm` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '0 = no, 1 = yes',
  PRIMARY KEY (`titlenameid`),
  UNIQUE KEY `uq_gwtitles_titlename` (`titlename`),
  CONSTRAINT `chk_gwtitles_titletype` CHECK (`titletype` in (0,1)),
  CONSTRAINT `chk_gwtitles_autofilled` CHECK (`autofilled` in (0,1)),
  CONSTRAINT `chk_gwtitles_gwamm` CHECK (`gwamm` in (0,1))
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwtonic_inventory`
--

DROP TABLE IF EXISTS `gwtonic_inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwtonic_inventory` (
  `userid` int(11) NOT NULL,
  `accid` int(11) NOT NULL,
  `tonicid` int(10) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`userid`,`accid`,`tonicid`),
  KEY `idx_gwtonic_inventory_accid` (`accid`),
  KEY `idx_gwtonic_inventory_tonicid` (`tonicid`),
  CONSTRAINT `fk_gwtonic_inventory_tonic` FOREIGN KEY (`tonicid`) REFERENCES `gwtonics` (`tonicid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwtonics`
--

DROP TABLE IF EXISTS `gwtonics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwtonics` (
  `tonicid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tonicname` varchar(120) NOT NULL,
  `rarity` enum('White','Purple','Gold','Green') NOT NULL,
  `display_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`tonicid`),
  UNIQUE KEY `uq_gwtonics_name` (`tonicname`),
  KEY `idx_gwtonics_order` (`rarity`,`display_order`,`tonicname`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwtreasure_attributes`
--

DROP TABLE IF EXISTS `gwtreasure_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwtreasure_attributes` (
  `attribute_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `attribute_name` varchar(50) NOT NULL,
  PRIMARY KEY (`attribute_id`),
  UNIQUE KEY `uq_gwtreasure_attribute` (`attribute_name`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwtreasure_history`
--

DROP TABLE IF EXISTS `gwtreasure_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwtreasure_history` (
  `treasure_history_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `accid` int(11) NOT NULL,
  `charid` int(11) NOT NULL,
  `location_id` smallint(5) unsigned NOT NULL,
  `collected_on` date NOT NULL,
  `gold_received` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `drop_type` varchar(20) NOT NULL DEFAULT 'nothing',
  `drop_description` varchar(255) DEFAULT NULL,
  `rarity_id` tinyint(3) unsigned DEFAULT NULL,
  `requirement` tinyint(3) unsigned DEFAULT NULL,
  `weapon_type_id` tinyint(3) unsigned DEFAULT NULL,
  `attribute_id` tinyint(3) unsigned DEFAULT NULL,
  `item_name` varchar(150) DEFAULT NULL,
  `material_id` tinyint(3) unsigned DEFAULT NULL,
  `rune_id` tinyint(3) unsigned DEFAULT NULL,
  `insignia_id` tinyint(3) unsigned DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`treasure_history_id`),
  KEY `idx_gwtreasure_character_location_date` (`userid`,`charid`,`location_id`,`collected_on`),
  KEY `idx_gwtreasure_account_character` (`accid`,`charid`),
  KEY `idx_gwtreasure_location` (`location_id`),
  KEY `fk_gwtreasure_history_character` (`charid`),
  KEY `idx_gwtreasure_history_structured_drop` (`drop_type`,`weapon_type_id`,`attribute_id`),
  KEY `fk_gwtreasure_history_rarity` (`rarity_id`),
  KEY `fk_gwtreasure_history_requirement` (`requirement`),
  KEY `fk_gwtreasure_history_weapon_type` (`weapon_type_id`),
  KEY `fk_gwtreasure_history_attribute` (`attribute_id`),
  KEY `fk_gwtreasure_history_material` (`material_id`),
  KEY `fk_gwtreasure_history_rune` (`rune_id`),
  KEY `fk_gwtreasure_history_insignia` (`insignia_id`),
  CONSTRAINT `fk_gwtreasure_history_account` FOREIGN KEY (`accid`) REFERENCES `gwaccounts` (`accid`) ON DELETE CASCADE,
  CONSTRAINT `fk_gwtreasure_history_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `gwtreasure_attributes` (`attribute_id`),
  CONSTRAINT `fk_gwtreasure_history_character` FOREIGN KEY (`charid`) REFERENCES `gwchars` (`charid`) ON DELETE CASCADE,
  CONSTRAINT `fk_gwtreasure_history_insignia` FOREIGN KEY (`insignia_id`) REFERENCES `gwtreasure_insignias` (`insignia_id`),
  CONSTRAINT `fk_gwtreasure_history_location` FOREIGN KEY (`location_id`) REFERENCES `gwtreasure_locations` (`location_id`),
  CONSTRAINT `fk_gwtreasure_history_material` FOREIGN KEY (`material_id`) REFERENCES `gwtreasure_materials` (`material_id`),
  CONSTRAINT `fk_gwtreasure_history_rarity` FOREIGN KEY (`rarity_id`) REFERENCES `gwtreasure_rarities` (`rarity_id`),
  CONSTRAINT `fk_gwtreasure_history_requirement` FOREIGN KEY (`requirement`) REFERENCES `gwtreasure_requirements` (`requirement`),
  CONSTRAINT `fk_gwtreasure_history_rune` FOREIGN KEY (`rune_id`) REFERENCES `gwtreasure_runes` (`rune_id`),
  CONSTRAINT `fk_gwtreasure_history_user` FOREIGN KEY (`userid`) REFERENCES `userinfo` (`userid`) ON DELETE CASCADE,
  CONSTRAINT `fk_gwtreasure_history_weapon_type` FOREIGN KEY (`weapon_type_id`) REFERENCES `gwtreasure_weapon_types` (`weapon_type_id`),
  CONSTRAINT `chk_gwtreasure_drop_type` CHECK (`drop_type` in ('weapon','material','rune_insignia','nothing'))
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwtreasure_insignias`
--

DROP TABLE IF EXISTS `gwtreasure_insignias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwtreasure_insignias` (
  `insignia_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `profession_id` tinyint(3) unsigned NOT NULL,
  `insignia_name` varchar(60) NOT NULL,
  PRIMARY KEY (`insignia_id`),
  UNIQUE KEY `uq_gwtreasure_insignia` (`insignia_name`),
  KEY `fk_gwtreasure_insignia_profession` (`profession_id`),
  CONSTRAINT `fk_gwtreasure_insignia_profession` FOREIGN KEY (`profession_id`) REFERENCES `gwtreasure_professions` (`profession_id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwtreasure_locations`
--

DROP TABLE IF EXISTS `gwtreasure_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwtreasure_locations` (
  `location_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `location_name` varchar(100) NOT NULL,
  `wiki_url` varchar(255) DEFAULT NULL,
  `reset_days` smallint(5) unsigned NOT NULL DEFAULT 30,
  `display_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`location_id`),
  UNIQUE KEY `uq_gwtreasure_location_name` (`location_name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwtreasure_materials`
--

DROP TABLE IF EXISTS `gwtreasure_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwtreasure_materials` (
  `material_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `material_name` varchar(50) NOT NULL,
  PRIMARY KEY (`material_id`),
  UNIQUE KEY `uq_gwtreasure_material` (`material_name`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwtreasure_professions`
--

DROP TABLE IF EXISTS `gwtreasure_professions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwtreasure_professions` (
  `profession_id` tinyint(3) unsigned NOT NULL,
  `profession_name` varchar(30) NOT NULL,
  PRIMARY KEY (`profession_id`),
  UNIQUE KEY `uq_gwtreasure_profession` (`profession_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwtreasure_rarities`
--

DROP TABLE IF EXISTS `gwtreasure_rarities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwtreasure_rarities` (
  `rarity_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `rarity_name` varchar(20) NOT NULL,
  PRIMARY KEY (`rarity_id`),
  UNIQUE KEY `uq_gwtreasure_rarity` (`rarity_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwtreasure_requirements`
--

DROP TABLE IF EXISTS `gwtreasure_requirements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwtreasure_requirements` (
  `requirement` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`requirement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwtreasure_runes`
--

DROP TABLE IF EXISTS `gwtreasure_runes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwtreasure_runes` (
  `rune_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `profession_id` tinyint(3) unsigned NOT NULL,
  `rune_name` varchar(50) NOT NULL,
  PRIMARY KEY (`rune_id`),
  UNIQUE KEY `uq_gwtreasure_rune` (`profession_id`,`rune_name`),
  CONSTRAINT `fk_gwtreasure_rune_profession` FOREIGN KEY (`profession_id`) REFERENCES `gwtreasure_professions` (`profession_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwtreasure_weapon_attribute_map`
--

DROP TABLE IF EXISTS `gwtreasure_weapon_attribute_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwtreasure_weapon_attribute_map` (
  `weapon_type_id` tinyint(3) unsigned NOT NULL,
  `attribute_id` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`weapon_type_id`,`attribute_id`),
  KEY `idx_gwtreasure_weapon_attr_attribute` (`attribute_id`),
  CONSTRAINT `fk_gwtreasure_weapon_attr_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `gwtreasure_attributes` (`attribute_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_gwtreasure_weapon_attr_type` FOREIGN KEY (`weapon_type_id`) REFERENCES `gwtreasure_weapon_types` (`weapon_type_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gwtreasure_weapon_types`
--

DROP TABLE IF EXISTS `gwtreasure_weapon_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `gwtreasure_weapon_types` (
  `weapon_type_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `weapon_type_name` varchar(40) NOT NULL,
  PRIMARY KEY (`weapon_type_id`),
  UNIQUE KEY `uq_gwtreasure_weapon_type` (`weapon_type_name`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mail_settings`
--

DROP TABLE IF EXISTS `mail_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mail_settings` (
  `settings_id` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `enabled` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `smtp_host` varchar(255) NOT NULL DEFAULT '',
  `smtp_port` smallint(5) unsigned NOT NULL DEFAULT 587,
  `smtp_encryption` varchar(10) NOT NULL DEFAULT 'tls',
  `smtp_username` varchar(255) NOT NULL DEFAULT '',
  `from_address` varchar(255) NOT NULL DEFAULT '',
  `from_name` varchar(100) NOT NULL DEFAULT 'Guild Wars Stats Tracker',
  `reply_to_address` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`settings_id`),
  CONSTRAINT `chk_mail_settings_singleton` CHECK (`settings_id` = 1),
  CONSTRAINT `chk_mail_settings_enabled` CHECK (`enabled` in (0,1)),
  CONSTRAINT `chk_mail_settings_encryption` CHECK (`smtp_encryption` in ('none','tls','ssl'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `token_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`token_id`),
  UNIQUE KEY `uq_password_reset_token_hash` (`token_hash`),
  KEY `idx_password_reset_user` (`userid`),
  KEY `idx_password_reset_expires` (`expires_at`),
  CONSTRAINT `fk_password_reset_user` FOREIGN KEY (`userid`) REFERENCES `userinfo` (`userid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reminder_notifications`
--

DROP TABLE IF EXISTS `reminder_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reminder_notifications` (
  `notification_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `reminder_type` varchar(30) NOT NULL,
  `reference_key` varchar(191) NOT NULL,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`notification_id`),
  UNIQUE KEY `uq_reminder_notification` (`userid`,`reminder_type`,`reference_key`),
  KEY `idx_reminder_notifications_sent_at` (`sent_at`),
  CONSTRAINT `fk_reminder_notification_user` FOREIGN KEY (`userid`) REFERENCES `userinfo` (`userid`) ON DELETE CASCADE,
  CONSTRAINT `chk_reminder_notification_type` CHECK (`reminder_type` in ('treasure','birthday'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_preferences`
--

DROP TABLE IF EXISTS `user_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_preferences` (
  `userid` int(11) NOT NULL,
  `birthday_email_enabled` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `birthday_reminder_days` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `treasure_email_enabled` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`userid`),
  CONSTRAINT `fk_user_preferences_user` FOREIGN KEY (`userid`) REFERENCES `userinfo` (`userid`) ON DELETE CASCADE,
  CONSTRAINT `chk_user_preferences_birthday_email` CHECK (`birthday_email_enabled` in (0,1)),
  CONSTRAINT `chk_user_preferences_treasure_email` CHECK (`treasure_email_enabled` in (0,1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `userinfo`
--

DROP TABLE IF EXISTS `userinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `userinfo` (
  `userid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `userpass` varchar(255) NOT NULL,
  `usermail` varchar(50) NOT NULL,
  `admin` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '0 = normal user, 1 = administrator',
  `prefaccid` int(11) NOT NULL DEFAULT 0 COMMENT 'preferred Guild Wars account; 0 = none selected',
  `prefaccname` varchar(50) NOT NULL DEFAULT 'No default selected',
  `prefcharid` int(11) NOT NULL DEFAULT 0 COMMENT 'preferred character; 0 = none selected',
  `prefcharname` varchar(19) NOT NULL DEFAULT 'No default selected',
  PRIMARY KEY (`userid`),
  UNIQUE KEY `uq_userinfo_username` (`username`),
  UNIQUE KEY `uq_userinfo_usermail` (`usermail`),
  CONSTRAINT `chk_userinfo_admin` CHECK (`admin` in (0,1))
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'gwst'
--

--
-- Dumping routines for database 'gwst'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-23 23:14:30


-- ============================================================================
-- STATIC / REFERENCE / CATALOG DATA
-- ============================================================================

/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: gwst
-- ------------------------------------------------------
-- Server version	10.11.14-MariaDB-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `gwprofessions`
--

LOCK TABLES `gwprofessions` WRITE;
/*!40000 ALTER TABLE `gwprofessions` DISABLE KEYS */;
INSERT INTO `gwprofessions` (`profid`, `profession`, `profcolor`) VALUES (1,'Warrior','#FF8');
INSERT INTO `gwprofessions` (`profid`, `profession`, `profcolor`) VALUES (2,'Ranger','#CF9');
INSERT INTO `gwprofessions` (`profid`, `profession`, `profcolor`) VALUES (3,'Monk','#ACF');
INSERT INTO `gwprofessions` (`profid`, `profession`, `profcolor`) VALUES (4,'Necromancer','#9FC');
INSERT INTO `gwprofessions` (`profid`, `profession`, `profcolor`) VALUES (5,'Mesmer','#DAF');
INSERT INTO `gwprofessions` (`profid`, `profession`, `profcolor`) VALUES (6,'Elementalist','#FBB');
INSERT INTO `gwprofessions` (`profid`, `profession`, `profcolor`) VALUES (7,'Assassin','#FCE');
INSERT INTO `gwprofessions` (`profid`, `profession`, `profcolor`) VALUES (8,'Ritualist','#BFF');
INSERT INTO `gwprofessions` (`profid`, `profession`, `profcolor`) VALUES (9,'Paragon','#FC9');
INSERT INTO `gwprofessions` (`profid`, `profession`, `profcolor`) VALUES (10,'Dervish','#DDF');
/*!40000 ALTER TABLE `gwprofessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `gwtitles`
--

LOCK TABLES `gwtitles` WRITE;
/*!40000 ALTER TABLE `gwtitles` DISABLE KEYS */;
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (1,'Friend of the Kurzicks',0,12,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (2,'Friend of the Luxons',0,12,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (3,'Asura',1,10,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (4,'Deldrimor (Delver)',1,10,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (5,'Drunkard',1,2,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (6,'Lightbringer',1,8,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (7,'Sweet Tooth',1,2,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (8,'Treasure Hunter',0,7,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (9,'Wisdom',0,7,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (10,'Party Animal',1,2,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (16,'Champion',0,12,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (17,'Codex',0,12,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (18,'Gamer',0,12,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (19,'Gladiator',0,12,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (20,'Hero',0,15,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (21,'Lucky',0,6,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (22,'Unlucky',0,7,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (23,'Zaishen',0,12,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (24,'Commander',0,12,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (25,'Tyrian Cartographer (Prophecies)',1,6,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (26,'Tyrian Guardian (Prophecies)',1,1,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (27,'Kind of a Big Deal',1,6,1,1);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (28,'Tyrian Protector (Prophecies)',1,1,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (29,'Tyrian Skill Hunter (Prophecies)',1,1,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (30,'Survivor',1,3,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (31,'Tyrian Vanquisher (Prophecies)',1,1,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (32,'Defender of Ascalon',1,1,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (33,'Sunspear',1,10,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (34,'Ebon Vanguard (Agent)',1,10,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (35,'Master of the North',1,6,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (36,'Norn (Slayer)',1,10,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (37,'Legendary Cartographer',1,1,1,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (38,'Legendary Guardian',1,1,1,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (39,'Legendary Skill Hunter',1,1,1,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (40,'Legendary Vanquisher',1,1,1,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (41,'Canthan Cartographer (Factions)',1,6,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (42,'Elonian Cartographer (Nightfall)',1,6,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (45,'Canthan Guardian (Factions)',1,1,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (46,'Elonian Guardian (Nightfall)',1,1,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (47,'Canthan Protector (Factions)',1,1,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (48,'Elonian Protector (Nightfall)',1,1,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (49,'Canthan Vanquisher (Factions)',1,1,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (50,'Elonian Vanquisher (Nightfall)',1,1,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (51,'Canthan Skill Hunter (Factions)',1,1,0,0);
INSERT INTO `gwtitles` (`titlenameid`, `titlename`, `titletype`, `titlemaxrank`, `autofilled`, `gwamm`) VALUES (52,'Elonian Skill Hunter (Nightfall)',1,1,0,0);
/*!40000 ALTER TABLE `gwtitles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `gwsubtitles`
--

LOCK TABLES `gwsubtitles` WRITE;
/*!40000 ALTER TABLE `gwsubtitles` DISABLE KEYS */;
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (1,3,'Not Too Smelly',1000,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (2,3,'Not Too Dopey',4000,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (3,3,'Not Too Clumsy',8000,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (4,25,'Tyrian Explorer',60,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (5,25,'Tyrian Pathfinder',70,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (6,25,'Tyrian Trailblazer',80,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (7,25,'Tyrian Cartographer',90,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (8,25,'Tyrian Master Cartographer',95,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (9,25,'Tyrian Grandmaster Cartographer',100,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (10,3,'Not Too Boring',16000,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (11,3,'Not Too Annoying',26000,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (12,3,'Not Too Grumpy',40000,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (13,3,'Not Too Silly',56000,7);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (14,3,'Not Too Lazy',80000,8);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (15,3,'Not Too Foolish',110000,9);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (16,3,'Not Too Shabby',160000,10);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (17,16,'Champion',25,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (18,16,'Fierce Champion',50,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (19,16,'Mighty Champion',100,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (20,16,'Deadly Champion',168,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (21,16,'Terrifying Champion',280,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (22,16,'Conquering Champion',466,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (23,16,'Subjugating Champion',775,7);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (40,16,'Vanquishing Champion',1296,8);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (41,16,'King\'s Champion',2160,9);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (42,16,'Emperor\'s Champion',3600,10);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (43,16,'Balthazar\'s Champion',6000,11);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (44,16,'Legendary Champion',10000,12);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (46,41,'Canthan Explorer',60,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (47,41,'Canthan Pathfinder',70,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (48,41,'Canthan Trailblazer',80,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (49,41,'Canthan Cartographer',90,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (50,41,'Canthan Master Cartographer',95,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (51,41,'Canthan Grandmaster Cartographer',100,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (52,32,'Legendary Defender of Ascalon',20,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (53,5,'Drunkard',1000,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (54,5,'Incorrigible Ale-Hound',10000,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (55,30,'Survivor',140600,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (56,30,'Indomitable Survivor',587500,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (57,30,'Legendary Survivor',1337500,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (58,46,'Guardian of Elona',20,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (59,45,'Guardian of Cantha',13,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (60,26,'Guardian of Tyria',25,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (61,24,'Commander',125,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (62,24,'Victorious Commander',250,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (63,24,'Triumphant Commander',500,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (64,24,'Keen Commander',840,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (65,24,'Battle Commander',1400,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (66,24,'Field Commander',2330,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (67,24,'Lieutenant Commander',3875,7);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (68,24,'Wing Commander',6480,8);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (69,24,'Cobra Commander',10800,9);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (70,24,'Supreme Commander',18000,10);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (71,24,'Master and Commander',30000,11);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (72,24,'Legendary Commander',50000,12);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (73,42,'Elonian Explorer',60,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (74,42,'Elonian Pathfinder',70,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (75,42,'Elonian Trailblazer',80,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (76,42,'Elonian Cartographer',90,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (77,42,'Elonian Master Cartographer',95,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (78,42,'Elonian Grandmaster Cartographer',100,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (79,37,'Legendary Cartographer',3,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (80,38,'Legendary Guardian',6,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (81,10,'Party Animal',1000,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (82,10,'Life of the Party',10000,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (83,7,'Sweet Tooth',1000,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (84,7,'Connoisseur of Confectionaries',10000,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (85,49,'Canthan Vanquisher',33,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (86,50,'Elonian Vanquisher',34,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (87,31,'Tyrian Vanquisher',54,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (88,17,'Codex Initiate',500,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (89,17,'Codex Acolyte',1000,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (90,17,'Codex Disciple',2000,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (91,17,'Codex Zealot',3360,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (92,17,'Codex Stalwart',5600,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (93,17,'Codex Adept',9320,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (94,17,'Codex Exemplar',15500,7);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (95,17,'Codex Prodigy',25920,8);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (96,17,'Codex Champion',43200,9);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (97,17,'Codex Paragon',72000,10);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (98,17,'Codex Master',120000,11);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (99,17,'Codex Grandmaster',200000,12);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (100,4,'Delver',1000,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (101,4,'Stout Delver',4000,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (102,4,'Gutsy Delver',8000,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (103,4,'Risky Delver',16000,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (104,4,'Bold Delver',26000,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (105,4,'Daring Delver',40000,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (106,4,'Adventurous Delver',56000,7);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (107,4,'Courageous Delver',80000,8);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (108,4,'Epic Delver',110000,9);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (109,4,'Legendary Delver',160000,10);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (110,34,'Agent',1000,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (111,34,'Covert Agent',4000,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (112,34,'Stealth Agent',8000,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (113,34,'Mysterious Agent',16000,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (114,34,'Shadow Agent',26000,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (115,34,'Underground Agent',40000,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (116,34,'Special Agent',56000,7);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (117,34,'Valued Agent',80000,8);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (118,34,'Superior Agent',110000,9);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (119,34,'Secret Agent',160000,10);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (120,1,'Kurzick Supporter',100000,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (121,1,'Friend of the Kurzicks',250000,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (122,1,'Companion of the kurzicks',400000,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (123,1,'Ally of the Kurzicks',550000,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (124,1,'Sentinel of the Kurzicks',875000,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (125,1,'Steward of the Kurzicks',1200000,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (126,1,'Defender of the Kurzicks',1850000,7);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (127,1,'Warden of the Kurzicks',2500000,8);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (128,1,'Bastion of the Kurzicks',3750000,9);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (129,1,'Champion of the Kurzicks',5000000,10);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (130,1,'Hero of the Kurzicks',7500000,11);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (131,1,'Savior of the Kurzicks',10000000,12);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (132,2,'Luxon Supporter',100000,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (133,2,'Friend of the Luxons',250000,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (134,2,'Companion of the Luxons',400000,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (135,2,'Ally of the Luxons',550000,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (136,2,'Sentinel of the Luxons',875000,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (137,2,'Steward of the Luxons',1200000,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (138,2,'Defender of the Luxons',1850000,7);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (139,2,'Warden of the Luxons',2500000,8);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (140,2,'Bastion of the Luxons',3750000,9);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (141,2,'Champion of the Luxons',5000000,10);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (142,2,'Hero of the Luxons',7500000,11);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (143,2,'Savior of the Luxons',10000000,12);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (144,18,'Skillz',1000,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (145,18,'Pro Skillz',2000,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (146,18,'Numchuck Skillz',4000,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (147,18,'Mad Skillz',7000,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (148,18,'Ãœber Micro Skillz',12000,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (176,18,'Gosu Skillz',20000,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (177,18,'1337 Skillz',32500,7);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (178,18,'iddqd Skillz',50000,8);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (179,18,'T3h Haxz0rz Skillz',70000,9);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (180,18,'Pure Pwnage Skillz',90000,10);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (181,18,'These skillz go to',110000,11);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (182,18,'Real Ultimate Power Skillz',135000,12);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (183,19,'Gladiator',500,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (184,19,'Fierce Gladiator',1000,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (185,19,'Mighty Gladiator',2000,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (186,19,'Deadly Gladiator',3360,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (187,19,'Terrifying Gladiator',5600,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (188,19,'Conquering Gladiator',9320,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (189,19,'Subjugating Gladiator',15500,7);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (190,19,'Vanquishing Gladiator',25920,8);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (191,19,'King\'s Gladiator',43200,9);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (192,19,'Emperor\'s Gladiator',72000,10);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (193,19,'Balthazar\'s Gladiator',120000,11);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (194,19,'Legendary Gladiator',200000,12);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (195,20,'Hero',25,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (196,20,'Fierce Hero',75,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (197,20,'Mighty Hero',180,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (198,20,'Deadly Hero',360,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (199,20,'Terrifying Hero',600,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (200,20,'Conquering Hero',1000,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (201,20,'Subjugating Hero',1680,7);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (202,20,'Vanquishing Hero',2800,8);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (203,20,'Renowned Hero',4665,9);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (204,20,'Illustrious Hero',7750,10);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (205,20,'Eminent Hero',12960,11);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (206,20,'King\'s Hero',21600,12);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (207,20,'Emperor\'s Hero',36000,13);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (208,20,'Balthazar\'s Hero',60000,14);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (209,20,'Legendary Hero',100000,15);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (210,27,'Kind Of A Big Deal',5,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (211,27,'People Know Me',10,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (212,27,'I\'m Very Important',15,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (213,27,'I Have Many Leather-Bound Books',20,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (214,27,'My Guild Hall Smells of Rich Mahogany',25,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (215,27,'God Walking Amongst Mere Mortals',30,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (223,39,'Legendary Skill Hunter',3,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (224,40,'Legendary Vanquisher',3,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (225,6,'Lightbringer',100,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (226,6,'Adept Lightbringer',300,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (227,6,'Brave Lightbringer',1000,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (228,6,'Mighty Lightbringer',2500,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (229,6,'Conquering Lightbringer',7500,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (230,6,'Vanquishing Lightbringer',15000,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (231,6,'Revered Lightbringer',25000,7);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (232,6,'Holy Lightbringer',50000,8);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (233,21,'Charmed',50000,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (234,21,'Lucky',100000,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (235,21,'Favored',250000,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (236,21,'Prosperous',500000,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (237,21,'Golden',1000000,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (238,21,'Blessed By Fate',2500000,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (239,22,'Hapless',5000,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (240,22,'Unlucky',10000,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (241,22,'Unfavored',25000,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (242,22,'Tragic',50000,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (243,22,'Wretched',100000,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (244,22,'Jinxed',250000,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (245,22,'Cursed By fate',500000,7);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (246,35,'Adventurer of the North',100,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (247,35,'Pioneer of the North',200,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (248,35,'Veteran of the North',350,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (249,35,'Conqueror of the North',550,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (250,35,'Master of the North',750,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (251,35,'Legendary Master of the North',1000,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (252,36,'Slayer of Imps',1000,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (253,36,'Slayer of Beasts',4000,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (254,36,'Slayer of Nightmares',8000,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (255,36,'Slayer of Giants',16000,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (256,36,'Slayer of Wurms',26000,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (257,36,'Slayer of Demons',40000,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (258,36,'Slayer of Heroes',56000,7);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (259,36,'Slayer of Champions',80000,8);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (260,36,'Slayer of Hordes',110000,9);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (261,36,'Slayer of All',160000,10);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (262,47,'Protector of Cantha',13,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (263,48,'Protector of Elona',20,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (264,28,'Protector of Tyria',25,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (265,51,'Canthan Elite Skill Hunter',120,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (266,52,'Elonian Elite Skill Hunter',140,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (267,29,'Tyrian Elite Skill Hunter',90,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (268,33,'Sunspear Sergeant',50,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (269,33,'Sunspear Master Sergeant',100,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (270,33,'Second Spear',175,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (271,33,'First Spear',300,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (272,33,'Sunspear Captain',500,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (273,33,'Sunspear Commander',1000,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (274,33,'Sunspear General',2500,7);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (275,33,'Sunspear Castellan',7500,8);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (276,33,'Spearmarshal',15000,9);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (277,33,'Legendary Spearmarshal',50000,10);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (278,8,'Treasure Hunter',100,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (279,8,'Adept Treasure Hunter',250,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (280,8,'Advanced Treasure Hunter',550,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (281,8,'Expert Treasure Hunter',1200,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (282,8,'Elite Treasure Hunter',2500,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (283,8,'Master Treasure Hunter',5000,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (284,8,'Grandmaster Treasure Hunter',10000,7);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (285,9,'Seeker of Wisdom',100,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (286,9,'Collector of Wisdom',250,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (287,9,'Devotee of Wisdom',550,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (288,9,'Devourer of Wisdom',1200,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (289,9,'Font of Wisdom',2500,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (290,9,'Oracle of Wisdom',5000,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (291,9,'Source of Wisdom',10000,7);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (292,23,'Zaishen Support',250,1);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (293,23,'Friend of the Zaishen',500,2);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (294,23,'Companion of the Zaishen',1000,3);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (295,23,'Ally of the Zaishen',1680,4);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (296,23,'Sentinel of the Zaishen',2800,5);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (297,23,'Steward of the Zaishen',4660,6);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (298,23,'Defender of the Zaishen',7750,7);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (299,23,'Warden of the Zaishen',12960,8);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (300,23,'Bastion of the Zaishen',21600,9);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (301,23,'Champion of the Zaishen',36000,10);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (302,23,'Hero of the Zaishen',60000,11);
INSERT INTO `gwsubtitles` (`stnameid`, `titlenameid`, `stname`, `stpoints`, `strank`) VALUES (303,23,'Legendary Hero of the Zaishen',100000,12);
/*!40000 ALTER TABLE `gwsubtitles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `gwminiature_groups`
--

LOCK TABLES `gwminiature_groups` WRITE;
/*!40000 ALTER TABLE `gwminiature_groups` DISABLE KEYS */;
INSERT INTO `gwminiature_groups` (`groupid`, `groupkey`, `groupname`, `display_order`, `group_note`) VALUES (1,'year1','Year 1',10,NULL);
INSERT INTO `gwminiature_groups` (`groupid`, `groupkey`, `groupname`, `display_order`, `group_note`) VALUES (2,'year2','Year 2',20,NULL);
INSERT INTO `gwminiature_groups` (`groupid`, `groupkey`, `groupname`, `display_order`, `group_note`) VALUES (3,'year3','Year 3',30,NULL);
INSERT INTO `gwminiature_groups` (`groupid`, `groupkey`, `groupname`, `display_order`, `group_note`) VALUES (4,'year4','Year 4',40,NULL);
INSERT INTO `gwminiature_groups` (`groupid`, `groupkey`, `groupname`, `display_order`, `group_note`) VALUES (5,'year5','Year 5',50,NULL);
INSERT INTO `gwminiature_groups` (`groupid`, `groupkey`, `groupname`, `display_order`, `group_note`) VALUES (6,'year6','Year 6',60,'Sixth-year Birthday Presents contain a tonic rather than a miniature.');
INSERT INTO `gwminiature_groups` (`groupid`, `groupkey`, `groupname`, `display_order`, `group_note`) VALUES (7,'year7','Year 7',70,'Seventh-year Birthday Presents can contain Purple, Gold, or Green miniatures from years 1-5 plus several previously exclusive miniatures.');
INSERT INTO `gwminiature_groups` (`groupid`, `groupkey`, `groupname`, `display_order`, `group_note`) VALUES (8,'historical','Historical / Unobtainable',80,NULL);
INSERT INTO `gwminiature_groups` (`groupid`, `groupkey`, `groupname`, `display_order`, `group_note`) VALUES (9,'ingame','In-game Rewards',90,'Miniatures obtained from in-game rewards, events, PvP rewards, and anniversary quests. These miniatures can be dedicated in the Hall of Monuments.');
/*!40000 ALTER TABLE `gwminiature_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `gwminiature_group_members`
--

LOCK TABLES `gwminiature_group_members` WRITE;
/*!40000 ALTER TABLE `gwminiature_group_members` DISABLE KEYS */;
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (1,1,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (1,2,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (1,3,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (1,4,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (1,5,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (1,6,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (1,7,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (1,8,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (1,9,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (1,10,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (1,11,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (1,12,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (1,13,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (1,14,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (2,15,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (2,16,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (2,17,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (2,18,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (2,19,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (2,20,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (2,21,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (2,22,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (2,23,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (2,24,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (2,25,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (2,26,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (2,27,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (2,28,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (3,29,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (3,30,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (3,31,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (3,32,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (3,33,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (3,34,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (3,35,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (3,36,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (3,37,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (3,38,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (3,39,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (3,40,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (3,41,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (3,42,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (4,43,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (4,44,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (4,45,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (4,46,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (4,47,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (4,48,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (4,49,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (4,50,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (4,51,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (4,52,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (4,53,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (4,54,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (4,55,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (4,56,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (5,57,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (5,58,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (5,59,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (5,60,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (5,61,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (5,62,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (5,63,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (5,64,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (5,65,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (5,66,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (5,67,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (5,68,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (5,69,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (5,70,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,9,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,10,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,11,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,12,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,13,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,14,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,23,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,24,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,25,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,26,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,27,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,28,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,37,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,38,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,39,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,40,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,41,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,42,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,48,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,49,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,50,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,51,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,52,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,53,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,65,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,66,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,67,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,68,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,69,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,70,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,71,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,72,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,73,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,74,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (7,75,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (8,71,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (8,72,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (8,73,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (8,74,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (8,75,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (8,76,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (8,77,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (8,78,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (8,79,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (8,80,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (8,81,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (8,82,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (8,83,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (8,84,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (8,85,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (8,86,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (8,87,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (8,88,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,78,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,79,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,80,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,81,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,82,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,83,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,84,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,85,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,86,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,87,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,89,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,90,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,91,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,92,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,93,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,94,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,95,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,96,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,97,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,98,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,99,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,100,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,101,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,102,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,103,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,104,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,105,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,106,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,107,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,108,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,109,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,110,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,111,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,112,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,113,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,114,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,115,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,116,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,117,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,118,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,119,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,120,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,121,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,122,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,123,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,124,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,125,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,126,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,127,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,128,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,129,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,130,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,131,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,132,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,133,0);
INSERT INTO `gwminiature_group_members` (`groupid`, `miniid`, `display_order`) VALUES (9,134,0);
/*!40000 ALTER TABLE `gwminiature_group_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `gwminiatures`
--

LOCK TABLES `gwminiatures` WRITE;
/*!40000 ALTER TABLE `gwminiatures` DISABLE KEYS */;
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (1,'Fungal Wallow','White','https://wiki.guildwars.com/wiki/Miniature_Fungal_Wallow');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (2,'Hydra','White','https://wiki.guildwars.com/wiki/Miniature_Hydra');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (3,'Jade Armor','White','https://wiki.guildwars.com/wiki/Miniature_Jade_Armor');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (4,'Jungle Troll','White','https://wiki.guildwars.com/wiki/Miniature_Jungle_Troll');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (5,'Necrid Horseman','White','https://wiki.guildwars.com/wiki/Miniature_Necrid_Horseman');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (6,'Siege Turtle','White','https://wiki.guildwars.com/wiki/Miniature_Siege_Turtle');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (7,'Temple Guardian','White','https://wiki.guildwars.com/wiki/Miniature_Temple_Guardian');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (8,'Whiptail Devourer','White','https://wiki.guildwars.com/wiki/Miniature_Whiptail_Devourer');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (9,'Burning Titan','White','https://wiki.guildwars.com/wiki/Miniature_Burning_Titan');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (10,'Charr Shaman','White','https://wiki.guildwars.com/wiki/Miniature_Charr_Shaman');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (11,'Kirin','White','https://wiki.guildwars.com/wiki/Miniature_Kirin');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (12,'Prince Rurik','White','https://wiki.guildwars.com/wiki/Miniature_Prince_Rurik');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (13,'Shiro','White','https://wiki.guildwars.com/wiki/Miniature_Shiro');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (14,'Bone Dragon','White','https://wiki.guildwars.com/wiki/Miniature_Bone_Dragon');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (15,'Aatxe','White','https://wiki.guildwars.com/wiki/Miniature_Aatxe');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (16,'Fire Imp','White','https://wiki.guildwars.com/wiki/Miniature_Fire_Imp');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (17,'Harpy Ranger','White','https://wiki.guildwars.com/wiki/Miniature_Harpy_Ranger');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (18,'Heket Warrior','White','https://wiki.guildwars.com/wiki/Miniature_Heket_Warrior');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (19,'Juggernaut','White','https://wiki.guildwars.com/wiki/Miniature_Juggernaut');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (20,'Mandragor Imp','White','https://wiki.guildwars.com/wiki/Miniature_Mandragor_Imp');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (21,'Thorn Wolf','White','https://wiki.guildwars.com/wiki/Miniature_Thorn_Wolf');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (22,'Wind Rider','White','https://wiki.guildwars.com/wiki/Miniature_Wind_Rider');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (23,'Elf','White','https://wiki.guildwars.com/wiki/Miniature_Elf');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (24,'Koss','White','https://wiki.guildwars.com/wiki/Miniature_Koss');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (25,'Palawa Joko','White','https://wiki.guildwars.com/wiki/Miniature_Palawa_Joko');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (26,'Lich','White','https://wiki.guildwars.com/wiki/Miniature_Lich');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (27,'Water Djinn','White','https://wiki.guildwars.com/wiki/Miniature_Water_Djinn');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (28,'Gwen','White','https://wiki.guildwars.com/wiki/Miniature_Gwen');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (29,'Abyssal','White','https://wiki.guildwars.com/wiki/Miniature_Abyssal');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (30,'Cave Spider','White','https://wiki.guildwars.com/wiki/Miniature_Cave_Spider');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (31,'Cloudtouched Simian','White','https://wiki.guildwars.com/wiki/Miniature_Cloudtouched_Simian');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (32,'Forest Minotaur','White','https://wiki.guildwars.com/wiki/Miniature_Forest_Minotaur');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (33,'Irukandji','White','https://wiki.guildwars.com/wiki/Miniature_Irukandji');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (34,'Mursaat','White','https://wiki.guildwars.com/wiki/Miniature_Mursaat');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (35,'Raptor','White','https://wiki.guildwars.com/wiki/Miniature_Raptor');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (36,'Roaring Ether','White','https://wiki.guildwars.com/wiki/Miniature_Roaring_Ether');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (37,'Freezie','White','https://wiki.guildwars.com/wiki/Miniature_Freezie');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (38,'Nornbear','White','https://wiki.guildwars.com/wiki/Miniature_Nornbear');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (39,'Ooze','White','https://wiki.guildwars.com/wiki/Miniature_Ooze');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (40,'Black Beast of Aaaaarrrrrrggghhh','White','https://wiki.guildwars.com/wiki/Miniature_Black_Beast_of_Aaaaarrrrrrggghhh');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (41,'White Rabbit','White','https://wiki.guildwars.com/wiki/Miniature_White_Rabbit');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (42,'Mad King Thorn','White','https://wiki.guildwars.com/wiki/Miniature_Mad_King_Thorn');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (43,'Abomination','White','https://wiki.guildwars.com/wiki/Miniature_Abomination');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (44,'Desert Griffon','White','https://wiki.guildwars.com/wiki/Miniature_Desert_Griffon');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (45,'Dredge Brute','White','https://wiki.guildwars.com/wiki/Miniature_Dredge_Brute');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (46,'Krait Neoss','White','https://wiki.guildwars.com/wiki/Miniature_Krait_Neoss');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (47,'Kveldulf','White','https://wiki.guildwars.com/wiki/Miniature_Kveldulf');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (48,'Flowstone Elemental','White','https://wiki.guildwars.com/wiki/Miniature_Flowstone_Elemental');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (49,'Jora','White','https://wiki.guildwars.com/wiki/Miniature_Jora');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (50,'Nian','White','https://wiki.guildwars.com/wiki/Miniature_Nian');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (51,'Dagnar Stonepate','White','https://wiki.guildwars.com/wiki/Miniature_Dagnar_Stonepate');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (52,'Flame Djinn','White','https://wiki.guildwars.com/wiki/Miniature_Flame_Djinn');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (53,'Eye of Janthir','White','https://wiki.guildwars.com/wiki/Miniature_Eye_of_Janthir');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (54,'Quetzal Sly','White','https://wiki.guildwars.com/wiki/Miniature_Quetzal_Sly');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (55,'Terrorweb Dryder','White','https://wiki.guildwars.com/wiki/Miniature_Terrorweb_Dryder');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (56,'Word of Madness','White','https://wiki.guildwars.com/wiki/Miniature_Word_of_Madness');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (57,'Cobalt Scabara','White','https://wiki.guildwars.com/wiki/Miniature_Cobalt_Scabara');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (58,'Fire Drake','White','https://wiki.guildwars.com/wiki/Miniature_Fire_Drake');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (59,'Ophil Nahualli','White','https://wiki.guildwars.com/wiki/Miniature_Ophil_Nahualli');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (60,'Scourge Manta','White','https://wiki.guildwars.com/wiki/Miniature_Scourge_Manta');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (61,'Seer','White','https://wiki.guildwars.com/wiki/Miniature_Seer');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (62,'Shard Wolf','White','https://wiki.guildwars.com/wiki/Miniature_Shard_Wolf');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (63,'Siege Devourer','White','https://wiki.guildwars.com/wiki/Miniature_Siege_Devourer');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (64,'Summit Giant Herder','White','https://wiki.guildwars.com/wiki/Miniature_Summit_Giant_Herder');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (65,'Candysmith Marley','White','https://wiki.guildwars.com/wiki/Miniature_Candysmith_Marley');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (66,'Oola','White','https://wiki.guildwars.com/wiki/Miniature_Oola');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (67,'Ventari','White','https://wiki.guildwars.com/wiki/Miniature_Ventari');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (68,'King Adelbern','White','https://wiki.guildwars.com/wiki/Miniature_King_Adelbern');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (69,'Zhu Hanuku','White','https://wiki.guildwars.com/wiki/Miniature_Zhu_Hanuku');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (70,'M.O.X.','White','https://wiki.guildwars.com/wiki/Miniature_M.O.X.');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (71,'Naga Raincaller','White','https://wiki.guildwars.com/wiki/Miniature_Naga_Raincaller');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (72,'Oni','White','https://wiki.guildwars.com/wiki/Miniature_Oni');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (73,'Shiro\'ken Assassin','White','https://wiki.guildwars.com/wiki/Miniature_Shiro\'ken_Assassin');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (74,'Zhed Shadowhoof','White','https://wiki.guildwars.com/wiki/Miniature_Zhed_Shadowhoof');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (75,'Vizu','White','https://wiki.guildwars.com/wiki/Miniature_Vizu');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (76,'Kuunavang','White','https://wiki.guildwars.com/wiki/Miniature_Kuunavang');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (77,'Varesh Ossa','White','https://wiki.guildwars.com/wiki/Miniature_Varesh_Ossa');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (78,'Asura','White','https://wiki.guildwars.com/wiki/Miniature_Asura');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (79,'Destroyer of Flesh','White','https://wiki.guildwars.com/wiki/Miniature_Destroyer_of_Flesh');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (80,'Gray Giant','White','https://wiki.guildwars.com/wiki/Miniature_Gray_Giant');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (81,'Grawl','White','https://wiki.guildwars.com/wiki/Miniature_Grawl');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (82,'Ceratadon','White','https://wiki.guildwars.com/wiki/Miniature_Ceratadon');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (83,'Longhair Yeti','White','https://wiki.guildwars.com/wiki/Miniature_Longhair_Yeti');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (84,'Island Guardian','White','https://wiki.guildwars.com/wiki/Miniature_Island_Guardian');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (85,'Mad King\'s Guard','White','https://wiki.guildwars.com/wiki/Miniature_Mad_King\'s_Guard');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (86,'Panda','White','https://wiki.guildwars.com/wiki/Miniature_Panda');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (87,'Kanaxai','White','https://wiki.guildwars.com/wiki/Miniature_Kanaxai');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (88,'Rickalis Tu Rivers','Green','https://wiki.guildwars.com/wiki/Miniature_Rickalis_Tu_Rivers');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (89,'Black Moa Chick','White','https://wiki.guildwars.com/wiki/Miniature_Black_Moa_Chick');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (90,'Brown Rabbit','White','https://wiki.guildwars.com/wiki/Miniature_Brown_Rabbit');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (91,'Dhuum','White','https://wiki.guildwars.com/wiki/Miniature_Dhuum');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (92,'Forest Griffon','White','https://wiki.guildwars.com/wiki/Miniature_Forest_Griffon');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (93,'Forgemaster','White','https://wiki.guildwars.com/wiki/Miniature_Forgemaster');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (94,'Gwen Doll','White','https://wiki.guildwars.com/wiki/Miniature_Gwen_Doll');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (95,'Ghozer Dhuum','White','https://wiki.guildwars.com/wiki/Miniature_Ghozer_Dhuum');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (96,'Kazhad Dhuum','White','https://wiki.guildwars.com/wiki/Miniature_Kazhad_Dhuum');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (97,'Madruk Dhuum','White','https://wiki.guildwars.com/wiki/Miniature_Madruk_Dhuum');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (98,'Thul Za Dhuum','White','https://wiki.guildwars.com/wiki/Miniature_Thul_Za_Dhuum');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (99,'Smite Crawler','White','https://wiki.guildwars.com/wiki/Miniature_Smite_Crawler');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (100,'Wailing Lord','White','https://wiki.guildwars.com/wiki/Miniature_Wailing_Lord');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (101,'Yakkington','White','https://wiki.guildwars.com/wiki/Miniature_Yakkington');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (102,'Ghost of Althea','White','https://wiki.guildwars.com/wiki/Miniature_Ghost_of_Althea');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (103,'Undead Prince','White','https://wiki.guildwars.com/wiki/Miniature_Undead_Prince');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (104,'Mallyx','White','https://wiki.guildwars.com/wiki/Miniature_Mallyx');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (105,'Confessor Dorian','White','https://wiki.guildwars.com/wiki/Miniature_Confessor_Dorian');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (106,'Confessor Isaiah','White','https://wiki.guildwars.com/wiki/Miniature_Confessor_Isaiah');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (107,'Ecclesiate Xun Rao','White','https://wiki.guildwars.com/wiki/Miniature_Ecclesiate_Xun_Rao');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (108,'Evennia','White','https://wiki.guildwars.com/wiki/Miniature_Evennia');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (109,'Livia','White','https://wiki.guildwars.com/wiki/Miniature_Livia');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (110,'Peacekeeper Enforcer','White','https://wiki.guildwars.com/wiki/Miniature_Peacekeeper_Enforcer');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (111,'Princess Salma','White','https://wiki.guildwars.com/wiki/Miniature_Princess_Salma');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (112,'Minister Reiko','White','https://wiki.guildwars.com/wiki/Miniature_Minister_Reiko');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (113,'Celestial Dog','White','https://wiki.guildwars.com/wiki/Miniature_Celestial_Dog');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (114,'Celestial Dragon','White','https://wiki.guildwars.com/wiki/Miniature_Celestial_Dragon');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (115,'Celestial Horse','White','https://wiki.guildwars.com/wiki/Miniature_Celestial_Horse');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (116,'Celestial Monkey','White','https://wiki.guildwars.com/wiki/Miniature_Celestial_Monkey');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (117,'Celestial Ox','White','https://wiki.guildwars.com/wiki/Miniature_Celestial_Ox');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (118,'Celestial Pig','White','https://wiki.guildwars.com/wiki/Miniature_Celestial_Pig');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (119,'Celestial Rabbit','White','https://wiki.guildwars.com/wiki/Miniature_Celestial_Rabbit');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (120,'Celestial Rat','White','https://wiki.guildwars.com/wiki/Miniature_Celestial_Rat');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (121,'Celestial Rooster','White','https://wiki.guildwars.com/wiki/Miniature_Celestial_Rooster');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (122,'Celestial Sheep','White','https://wiki.guildwars.com/wiki/Miniature_Celestial_Sheep');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (123,'Celestial Snake','White','https://wiki.guildwars.com/wiki/Miniature_Celestial_Snake');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (124,'Celestial Tiger','White','https://wiki.guildwars.com/wiki/Miniature_Celestial_Tiger');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (125,'Legionnaire','White','https://wiki.guildwars.com/wiki/Miniature_Legionnaire');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (126,'Polar Bear','White','https://wiki.guildwars.com/wiki/Miniature_Polar_Bear');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (127,'World-Famous Racing Beetle','White','https://wiki.guildwars.com/wiki/Miniature_World-Famous_Racing_Beetle');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (128,'Guild Lord','White','https://wiki.guildwars.com/wiki/Miniature_Guild_Lord');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (129,'Ghostly Hero','White','https://wiki.guildwars.com/wiki/Miniature_Ghostly_Hero');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (130,'Ghostly Priest','White','https://wiki.guildwars.com/wiki/Miniature_Ghostly_Priest');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (131,'High Priest Zhang','White','https://wiki.guildwars.com/wiki/Miniature_High_Priest_Zhang');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (132,'Rift Warden','White','https://wiki.guildwars.com/wiki/Miniature_Rift_Warden');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (133,'Greased Lightning','White','https://wiki.guildwars.com/wiki/Miniature_Greased_Lightning');
INSERT INTO `gwminiatures` (`miniid`, `mininame`, `rarity`, `wiki_url`) VALUES (134,'Pig','White','https://wiki.guildwars.com/wiki/Miniature_Pig');
/*!40000 ALTER TABLE `gwminiatures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `gwtonics`
--

LOCK TABLES `gwtonics` WRITE;
/*!40000 ALTER TABLE `gwtonics` DISABLE KEYS */;
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (1,'Everlasting Acolyte Jin Tonic','White',10);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (2,'Everlasting Acolyte Sousuke Tonic','White',20);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (3,'Everlasting Dunkoro Tonic','White',30);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (4,'Everlasting Goren Tonic','White',40);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (5,'Everlasting Hayda Tonic','White',50);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (6,'Everlasting Kahmu Tonic','White',60);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (7,'Everlasting Livia Tonic','White',70);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (8,'Everlasting Margrid the Sly Tonic','White',80);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (9,'Everlasting Melonni Tonic','White',90);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (10,'Everlasting Morgahn Tonic','White',100);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (11,'Everlasting Norgu Tonic','White',110);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (12,'Everlasting Olias Tonic','White',120);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (13,'Everlasting Tahlkora Tonic','White',130);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (14,'Everlasting Vekk Tonic','White',140);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (15,'Everlasting Xandra Tonic','White',150);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (16,'Everlasting Zenmai Tonic','White',160);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (17,'Everlasting Anton Tonic','Purple',170);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (18,'Everlasting Jora Tonic','Purple',180);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (19,'Everlasting Koss Tonic','Purple',190);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (20,'Everlasting M.O.X. Tonic','Purple',200);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (21,'Everlasting Master of Whispers Tonic','Purple',210);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (22,'Everlasting Ogden Stonehealer Tonic','Purple',220);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (23,'Everlasting Pyre Fierceshot Tonic','Purple',230);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (24,'Everlasting Queen Salma Tonic','Purple',240);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (25,'Everlasting Razah Tonic','Purple',250);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (26,'Everlasting Zhed Shadowhoof Tonic','Purple',260);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (27,'Everlasting Gwen Tonic','Gold',270);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (28,'Everlasting Keiran Thackeray Tonic','Gold',280);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (29,'Everlasting Miku Tonic','Gold',290);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (30,'Everlasting Shiro Tonic','Gold',300);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (31,'Everlasting Prince Rurik Tonic','Gold',310);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (32,'Everlasting Destroyer Tonic','Green',320);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (33,'Everlasting Kuunavang Tonic','Green',330);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (34,'Everlasting Margonite Tonic','Green',340);
INSERT INTO `gwtonics` (`tonicid`, `tonicname`, `rarity`, `display_order`) VALUES (35,'Everlasting Slightly Mad King Tonic','Green',350);
/*!40000 ALTER TABLE `gwtonics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `gwtreasure_attributes`
--

LOCK TABLES `gwtreasure_attributes` WRITE;
/*!40000 ALTER TABLE `gwtreasure_attributes` DISABLE KEYS */;
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (25,'Air Magic');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (3,'Axe Mastery');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (9,'Beast Mastery');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (15,'Blood Magic');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (32,'Channeling Magic');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (37,'Command');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (33,'Communing');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (27,'Critical Strikes');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (16,'Curses');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (28,'Dagger Mastery');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (29,'Deadly Arts');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (17,'Death Magic');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (10,'Divine Favor');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (20,'Domination Magic');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (26,'Earth Magic');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (42,'Earth Prayers');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (22,'Energy Storage');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (6,'Expertise');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (18,'Fast Casting');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (23,'Fire Magic');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (4,'Hammer Mastery');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (11,'Healing Prayers');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (21,'Illusion Magic');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (19,'Inspiration Magic');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (35,'Leadership');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (7,'Marksmanship');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (38,'Motivation');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (39,'Mysticism');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (12,'Protection Prayers');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (34,'Restoration Magic');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (40,'Scythe Mastery');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (30,'Shadow Arts');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (13,'Smiting Prayers');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (14,'Soul Reaping');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (31,'Spawning Power');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (36,'Spear Mastery');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (1,'Strength');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (2,'Swordsmanship');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (5,'Tactics');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (24,'Water Magic');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (8,'Wilderness Survival');
INSERT INTO `gwtreasure_attributes` (`attribute_id`, `attribute_name`) VALUES (41,'Wind Prayers');
/*!40000 ALTER TABLE `gwtreasure_attributes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `gwtreasure_insignias`
--

LOCK TABLES `gwtreasure_insignias` WRITE;
/*!40000 ALTER TABLE `gwtreasure_insignias` DISABLE KEYS */;
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (1,1,'Survivor Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (2,1,'Radiant Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (3,1,'Stalwart Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (4,1,'Brawler\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (5,1,'Blessed Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (6,1,'Herald\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (7,1,'Sentry\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (8,2,'Knight\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (9,2,'Stonefist Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (10,2,'Dreadnought Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (11,2,'Sentinel\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (12,2,'Lieutenant\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (13,3,'Frostbound Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (14,3,'Pyrebound Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (15,3,'Stormbound Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (16,3,'Scout\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (17,3,'Earthbound Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (18,3,'Beastmaster\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (19,4,'Wanderer\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (20,4,'Disciple\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (21,4,'Anchorite\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (22,5,'Bloodstained Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (23,5,'Tormentor\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (24,5,'Bonelace Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (25,5,'Minion Master\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (26,5,'Blighter\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (27,5,'Undertaker\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (28,6,'Virtuoso\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (29,6,'Artificer\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (30,6,'Prodigy\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (31,7,'Hydromancer Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (32,7,'Geomancer Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (33,7,'Pyromancer Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (34,7,'Aeromancer Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (35,7,'Prismatic Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (36,8,'Vanguard\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (37,8,'Infiltrator\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (38,8,'Saboteur\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (39,8,'Nightstalker\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (40,9,'Shaman\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (41,9,'Ghost Forge Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (42,9,'Mystic\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (43,10,'Centurion\'s Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (44,11,'Windwalker Insignia');
INSERT INTO `gwtreasure_insignias` (`insignia_id`, `profession_id`, `insignia_name`) VALUES (45,11,'Forsaken Insignia');
/*!40000 ALTER TABLE `gwtreasure_insignias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `gwtreasure_locations`
--

LOCK TABLES `gwtreasure_locations` WRITE;
/*!40000 ALTER TABLE `gwtreasure_locations` DISABLE KEYS */;
INSERT INTO `gwtreasure_locations` (`location_id`, `location_name`, `wiki_url`, `reset_days`, `display_order`, `is_active`) VALUES (1,'Issnur Isles','https://wiki.guildwars.com/wiki/Issnur_Isles',30,1,1);
INSERT INTO `gwtreasure_locations` (`location_id`, `location_name`, `wiki_url`, `reset_days`, `display_order`, `is_active`) VALUES (2,'Mehtani Keys','https://wiki.guildwars.com/wiki/Mehtani_Keys',30,2,1);
INSERT INTO `gwtreasure_locations` (`location_id`, `location_name`, `wiki_url`, `reset_days`, `display_order`, `is_active`) VALUES (3,'Arkjok Ward','https://wiki.guildwars.com/wiki/Arkjok_Ward',30,3,1);
INSERT INTO `gwtreasure_locations` (`location_id`, `location_name`, `wiki_url`, `reset_days`, `display_order`, `is_active`) VALUES (4,'Jahai Bluffs','https://wiki.guildwars.com/wiki/Jahai_Bluffs',30,4,1);
INSERT INTO `gwtreasure_locations` (`location_id`, `location_name`, `wiki_url`, `reset_days`, `display_order`, `is_active`) VALUES (5,'Bahdok Caverns','https://wiki.guildwars.com/wiki/Bahdok_Caverns',30,5,1);
INSERT INTO `gwtreasure_locations` (`location_id`, `location_name`, `wiki_url`, `reset_days`, `display_order`, `is_active`) VALUES (6,'The Mirror of Lyss','https://wiki.guildwars.com/wiki/The_Mirror_of_Lyss',30,6,1);
INSERT INTO `gwtreasure_locations` (`location_id`, `location_name`, `wiki_url`, `reset_days`, `display_order`, `is_active`) VALUES (7,'The Hidden City of Ahdashim','https://wiki.guildwars.com/wiki/The_Hidden_City_of_Ahdashim',30,7,1);
INSERT INTO `gwtreasure_locations` (`location_id`, `location_name`, `wiki_url`, `reset_days`, `display_order`, `is_active`) VALUES (8,'Forum Highlands','https://wiki.guildwars.com/wiki/Forum_Highlands',30,8,1);
INSERT INTO `gwtreasure_locations` (`location_id`, `location_name`, `wiki_url`, `reset_days`, `display_order`, `is_active`) VALUES (9,'The Sulfurous Wastes','https://wiki.guildwars.com/wiki/The_Sulfurous_Wastes',30,9,1);
INSERT INTO `gwtreasure_locations` (`location_id`, `location_name`, `wiki_url`, `reset_days`, `display_order`, `is_active`) VALUES (10,'The Ruptured Heart','https://wiki.guildwars.com/wiki/The_Ruptured_Heart',30,10,1);
INSERT INTO `gwtreasure_locations` (`location_id`, `location_name`, `wiki_url`, `reset_days`, `display_order`, `is_active`) VALUES (11,'Nightfallen Jahai','https://wiki.guildwars.com/wiki/Nightfallen_Jahai',30,11,1);
INSERT INTO `gwtreasure_locations` (`location_id`, `location_name`, `wiki_url`, `reset_days`, `display_order`, `is_active`) VALUES (12,'Domain of Pain','https://wiki.guildwars.com/wiki/Domain_of_Pain',30,12,1);
/*!40000 ALTER TABLE `gwtreasure_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `gwtreasure_materials`
--

LOCK TABLES `gwtreasure_materials` WRITE;
/*!40000 ALTER TABLE `gwtreasure_materials` DISABLE KEYS */;
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (1,'Amber Chunk');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (2,'Bolt of Damask');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (3,'Bolt of Linen');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (4,'Bolt of Silk');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (5,'Deldrimor Steel Ingot');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (6,'Diamond');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (7,'Elonian Leather Square');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (8,'Fur Square');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (9,'Glob of Ectoplasm');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (10,'Jadeite Shard');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (11,'Leather Square');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (12,'Lump of Charcoal');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (13,'Monstrous Claw');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (14,'Monstrous Eye');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (15,'Monstrous Fang');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (16,'Obsidian Shard');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (17,'Onyx Gemstone');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (18,'Roll of Parchment');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (19,'Roll of Vellum');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (20,'Ruby');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (21,'Sapphire');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (22,'Spiritwood Plank');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (23,'Steel Ingot');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (24,'Tempered Glass Vial');
INSERT INTO `gwtreasure_materials` (`material_id`, `material_name`) VALUES (25,'Vial of Ink');
/*!40000 ALTER TABLE `gwtreasure_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `gwtreasure_professions`
--

LOCK TABLES `gwtreasure_professions` WRITE;
/*!40000 ALTER TABLE `gwtreasure_professions` DISABLE KEYS */;
INSERT INTO `gwtreasure_professions` (`profession_id`, `profession_name`) VALUES (8,'Assassin');
INSERT INTO `gwtreasure_professions` (`profession_id`, `profession_name`) VALUES (11,'Dervish');
INSERT INTO `gwtreasure_professions` (`profession_id`, `profession_name`) VALUES (7,'Elementalist');
INSERT INTO `gwtreasure_professions` (`profession_id`, `profession_name`) VALUES (6,'Mesmer');
INSERT INTO `gwtreasure_professions` (`profession_id`, `profession_name`) VALUES (4,'Monk');
INSERT INTO `gwtreasure_professions` (`profession_id`, `profession_name`) VALUES (5,'Necromancer');
INSERT INTO `gwtreasure_professions` (`profession_id`, `profession_name`) VALUES (1,'None');
INSERT INTO `gwtreasure_professions` (`profession_id`, `profession_name`) VALUES (10,'Paragon');
INSERT INTO `gwtreasure_professions` (`profession_id`, `profession_name`) VALUES (3,'Ranger');
INSERT INTO `gwtreasure_professions` (`profession_id`, `profession_name`) VALUES (9,'Ritualist');
INSERT INTO `gwtreasure_professions` (`profession_id`, `profession_name`) VALUES (2,'Warrior');
/*!40000 ALTER TABLE `gwtreasure_professions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `gwtreasure_rarities`
--

LOCK TABLES `gwtreasure_rarities` WRITE;
/*!40000 ALTER TABLE `gwtreasure_rarities` DISABLE KEYS */;
INSERT INTO `gwtreasure_rarities` (`rarity_id`, `rarity_name`) VALUES (2,'Blue');
INSERT INTO `gwtreasure_rarities` (`rarity_id`, `rarity_name`) VALUES (4,'Gold');
INSERT INTO `gwtreasure_rarities` (`rarity_id`, `rarity_name`) VALUES (5,'Green');
INSERT INTO `gwtreasure_rarities` (`rarity_id`, `rarity_name`) VALUES (3,'Purple');
INSERT INTO `gwtreasure_rarities` (`rarity_id`, `rarity_name`) VALUES (1,'White');
/*!40000 ALTER TABLE `gwtreasure_rarities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `gwtreasure_requirements`
--

LOCK TABLES `gwtreasure_requirements` WRITE;
/*!40000 ALTER TABLE `gwtreasure_requirements` DISABLE KEYS */;
INSERT INTO `gwtreasure_requirements` (`requirement`) VALUES (0);
INSERT INTO `gwtreasure_requirements` (`requirement`) VALUES (1);
INSERT INTO `gwtreasure_requirements` (`requirement`) VALUES (2);
INSERT INTO `gwtreasure_requirements` (`requirement`) VALUES (3);
INSERT INTO `gwtreasure_requirements` (`requirement`) VALUES (4);
INSERT INTO `gwtreasure_requirements` (`requirement`) VALUES (5);
INSERT INTO `gwtreasure_requirements` (`requirement`) VALUES (6);
INSERT INTO `gwtreasure_requirements` (`requirement`) VALUES (7);
INSERT INTO `gwtreasure_requirements` (`requirement`) VALUES (8);
INSERT INTO `gwtreasure_requirements` (`requirement`) VALUES (9);
INSERT INTO `gwtreasure_requirements` (`requirement`) VALUES (10);
INSERT INTO `gwtreasure_requirements` (`requirement`) VALUES (11);
INSERT INTO `gwtreasure_requirements` (`requirement`) VALUES (12);
INSERT INTO `gwtreasure_requirements` (`requirement`) VALUES (13);
/*!40000 ALTER TABLE `gwtreasure_requirements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `gwtreasure_runes`
--

LOCK TABLES `gwtreasure_runes` WRITE;
/*!40000 ALTER TABLE `gwtreasure_runes` DISABLE KEYS */;
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (1,1,'Attunement');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (2,1,'Clarity');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (3,1,'Purity');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (4,1,'Recovery');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (5,1,'Restoration');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (6,1,'Vigor');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (7,1,'Vitae');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (8,2,'Absorption');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (9,2,'Axe Mastery');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (10,2,'Hammer Mastery');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (11,2,'Strength');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (12,2,'Swordsmanship');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (13,2,'Tactics');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (14,3,'Beast Mastery');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (15,3,'Expertise');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (16,3,'Marksmanship');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (17,3,'Wilderness Survival');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (18,4,'Divine Favor');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (19,4,'Healing Prayers');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (20,4,'Protection Prayers');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (21,4,'Smiting Prayers');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (22,5,'Blood Magic');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (23,5,'Curses');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (24,5,'Death Magic');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (25,5,'Soul Reaping');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (26,6,'Domination Magic');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (27,6,'Fast Casting');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (28,6,'Illusion Magic');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (29,6,'Inspiration Magic');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (30,7,'Air Magic');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (31,7,'Earth Magic');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (32,7,'Energy Storage');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (33,7,'Fire Magic');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (34,7,'Water Magic');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (35,8,'Critical Strikes');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (36,8,'Dagger Mastery');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (37,8,'Deadly Arts');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (38,8,'Shadow Arts');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (39,9,'Channeling Magic');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (40,9,'Communing');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (41,9,'Restoration Magic');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (42,9,'Spawning Power');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (43,10,'Command');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (44,10,'Leadership');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (45,10,'Motivation');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (46,10,'Spear Mastery');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (47,11,'Earth Prayers');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (48,11,'Mysticism');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (49,11,'Scythe Mastery');
INSERT INTO `gwtreasure_runes` (`rune_id`, `profession_id`, `rune_name`) VALUES (50,11,'Wind Prayers');
/*!40000 ALTER TABLE `gwtreasure_runes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `gwtreasure_weapon_attribute_map`
--

LOCK TABLES `gwtreasure_weapon_attribute_map` WRITE;
/*!40000 ALTER TABLE `gwtreasure_weapon_attribute_map` DISABLE KEYS */;
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (1,3);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (2,4);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (3,2);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (4,28);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (5,40);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (6,7);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (7,7);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (8,7);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (9,7);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (10,7);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (11,36);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (12,10);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (12,11);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (12,12);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (12,13);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (12,14);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (12,15);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (12,16);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (12,17);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (12,18);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (12,19);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (12,20);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (12,21);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (12,22);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (12,23);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (12,24);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (12,25);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (12,26);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (12,31);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (12,32);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (12,33);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (12,34);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (13,10);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (13,11);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (13,12);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (13,13);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (13,14);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (13,15);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (13,16);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (13,17);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (13,18);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (13,19);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (13,20);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (13,21);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (13,22);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (13,23);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (13,24);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (13,25);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (13,26);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (13,31);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (13,32);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (13,33);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (13,34);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (14,10);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (14,11);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (14,12);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (14,13);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (14,14);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (14,15);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (14,16);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (14,17);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (14,18);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (14,19);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (14,20);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (14,21);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (14,22);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (14,23);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (14,24);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (14,25);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (14,26);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (14,31);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (14,32);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (14,33);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (14,34);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (15,1);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (15,5);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (15,35);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (15,37);
INSERT INTO `gwtreasure_weapon_attribute_map` (`weapon_type_id`, `attribute_id`) VALUES (15,38);
/*!40000 ALTER TABLE `gwtreasure_weapon_attribute_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `gwtreasure_weapon_types`
--

LOCK TABLES `gwtreasure_weapon_types` WRITE;
/*!40000 ALTER TABLE `gwtreasure_weapon_types` DISABLE KEYS */;
INSERT INTO `gwtreasure_weapon_types` (`weapon_type_id`, `weapon_type_name`) VALUES (1,'Axe');
INSERT INTO `gwtreasure_weapon_types` (`weapon_type_id`, `weapon_type_name`) VALUES (6,'Bow (Flatbow)');
INSERT INTO `gwtreasure_weapon_types` (`weapon_type_id`, `weapon_type_name`) VALUES (7,'Bow (Hornbow)');
INSERT INTO `gwtreasure_weapon_types` (`weapon_type_id`, `weapon_type_name`) VALUES (8,'Bow (Longbow)');
INSERT INTO `gwtreasure_weapon_types` (`weapon_type_id`, `weapon_type_name`) VALUES (9,'Bow (Recurve)');
INSERT INTO `gwtreasure_weapon_types` (`weapon_type_id`, `weapon_type_name`) VALUES (10,'Bow (Shortbow)');
INSERT INTO `gwtreasure_weapon_types` (`weapon_type_id`, `weapon_type_name`) VALUES (4,'Dagger');
INSERT INTO `gwtreasure_weapon_types` (`weapon_type_id`, `weapon_type_name`) VALUES (14,'Focus');
INSERT INTO `gwtreasure_weapon_types` (`weapon_type_id`, `weapon_type_name`) VALUES (2,'Hammer');
INSERT INTO `gwtreasure_weapon_types` (`weapon_type_id`, `weapon_type_name`) VALUES (5,'Scythe');
INSERT INTO `gwtreasure_weapon_types` (`weapon_type_id`, `weapon_type_name`) VALUES (15,'Shield');
INSERT INTO `gwtreasure_weapon_types` (`weapon_type_id`, `weapon_type_name`) VALUES (11,'Spear');
INSERT INTO `gwtreasure_weapon_types` (`weapon_type_id`, `weapon_type_name`) VALUES (12,'Staff');
INSERT INTO `gwtreasure_weapon_types` (`weapon_type_id`, `weapon_type_name`) VALUES (3,'Sword');
INSERT INTO `gwtreasure_weapon_types` (`weapon_type_id`, `weapon_type_name`) VALUES (13,'Wand');
/*!40000 ALTER TABLE `gwtreasure_weapon_types` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-23 23:16:08
