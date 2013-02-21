-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 25, 2012 at 09:31 PM
-- Server version: 5.5.24-log
-- PHP Version: 5.4.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `teamspeak`
--

-- --------------------------------------------------------

--
-- Table structure for table `banned`
--

CREATE TABLE IF NOT EXISTS `banned` (
  `banID` mediumint(9) NOT NULL AUTO_INCREMENT,
  `banCriteria` varchar(60) NOT NULL,
  PRIMARY KEY (`banID`),
  UNIQUE KEY `banCriteria` (`banCriteria`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `banned`
--

INSERT INTO `banned` (`banID`, `banCriteria`) VALUES
(2, 'unique=FHao8rL3hZvwhZ6Ix4pWucTq+gYs');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE IF NOT EXISTS `members` (
  `memberID` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(80) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `password` varchar(80) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`memberID`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=980235 ;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`memberID`, `username`, `name`, `password`, `status`) VALUES
(980234, '980234', 'Test User', '156743', 1);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
  `memberID` bigint(20) NOT NULL,
  `permission` varchar(50) NOT NULL,
  `value` text NOT NULL,
  `extra` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`memberID`, `permission`, `value`, `extra`) VALUES
(980234, 'ignore_rules', '1', '');

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE IF NOT EXISTS `registrations` (
  `registrationID` int(11) NOT NULL AUTO_INCREMENT,
  `memberID` int(7) NOT NULL,
  `expectedName` varchar(120) NOT NULL,
  `uniqueID` varchar(45) NOT NULL,
  `token` varchar(40) NOT NULL,
  `registeredIP` varchar(15) DEFAULT NULL,
  `registeredTimestamp` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `deletedReason` varchar(150) DEFAULT NULL,
  `deletedIP` varchar(15) DEFAULT NULL,
  `deletedTimestamp` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`registrationID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1120 ;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`registrationID`, `memberID`, `expectedName`, `uniqueID`, `token`, `registeredIP`, `registeredTimestamp`, `deleted`, `deletedReason`, `deletedIP`, `deletedTimestamp`) VALUES
(1119, 980234, 'Test User', 'FHao8rL3hZvwhZ6Ix4pWucTq+gY=', 'KFvS6ZMSUdudtHyD38r0JcDHq0mqPacNxoSAfMsP', '127.0.0.1', '2012-11-25 19:55:11', 0, NULL, NULL, NULL),
(1117, 980234, 'Test User', 'si53xXYBgnW41YYMeohMKO9d1WU=', 'YbN5JgUfkVm29iu7RWRkEnfiJTsivRJUFLRr2v5e', '127.0.0.1', '2012-11-25 19:45:33', 1, 'user_request_web', '127.0.0.1', '2012-11-25 19:53:03'),
(1118, 980234, 'Test User', 'FHao8rL3hZvwhZ6Ix4pWucTq+gY=', 'WpyUOi4WYgUeZC6zSRdDhJDYiImf7ZRcszTE03v0', '127.0.0.1', '2012-11-25 19:53:17', 1, 'user_request_web', '127.0.0.1', '2012-11-25 19:53:59');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
