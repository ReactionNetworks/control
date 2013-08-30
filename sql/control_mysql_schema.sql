-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 28, 2013 at 06:40 PM
-- Server version: 5.5.31
-- PHP Version: 5.3.10-1ubuntu3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `control`
--

-- --------------------------------------------------------

--
-- Table structure for table `batch_jobs`
--

CREATE TABLE IF NOT EXISTS `batch_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `file_format` tinyint(3) unsigned NOT NULL COMMENT '0 = human, 1 = net stoichiometry, 2 = net stoichiometry + V, 3 = source + target + V, 4 = s + v, 5 = SBML, 6 = sauro',
  `email` varchar(255) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0 = not started, 1 = in progress, 2 = complete, 3 = output file downloaded, 4 = output file removed',
  `detailed_output` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `mass_action_only` tinyint(3) unsigned NOT NULL,
  `tests_enabled` varchar(2047) NOT NULL,
  `filekey` varchar(14) NOT NULL,
  `remote_ip` varchar(40) NOT NULL COMMENT 'Length 40 to allow IPv6',
  `remote_user_agent` varchar(2047) NOT NULL,
  `creation_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `filename` (`filename`),
  UNIQUE KEY `filekey` (`filekey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
