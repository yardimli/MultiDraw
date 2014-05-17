-- --------------------------------------------------------
-- Host:                         localhost
-- Server version:               5.5.36 - MySQL Community Server (GPL)
-- Server OS:                    Win32
-- HeidiSQL Version:             8.1.0.4545
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping database structure for cloudofvoice
CREATE DATABASE IF NOT EXISTS `cloudofvoice` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `cloudofvoice`;


-- Dumping structure for table cloudofvoice.canvas
DROP TABLE IF EXISTS `canvas`;
CREATE TABLE IF NOT EXISTS `canvas` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CanvasName` varchar(50) DEFAULT NULL,
  `CanvasDate` datetime DEFAULT NULL,
  `CanvasWidth` int(11) NOT NULL DEFAULT '2000',
  `CanvasHeight` int(11) NOT NULL DEFAULT '3000',
  `LastID` int(11) NOT NULL DEFAULT '0',
  `Active` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `ID` (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table cloudofvoice.drawings
DROP TABLE IF EXISTS `drawings`;
CREATE TABLE IF NOT EXISTS `drawings` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SenderID` varchar(50) DEFAULT NULL,
  `X1` int(11) NOT NULL DEFAULT '0',
  `Y1` int(11) NOT NULL DEFAULT '0',
  `X2` int(11) NOT NULL DEFAULT '0',
  `Y2` int(11) NOT NULL DEFAULT '0',
  `DrawingID` int(11) NOT NULL DEFAULT '0',
  `xTime` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ID` (`ID`),
  KEY `SenderID` (`SenderID`),
  KEY `DrawingID` (`DrawingID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table cloudofvoice.snapshots
DROP TABLE IF EXISTS `snapshots`;
CREATE TABLE IF NOT EXISTS `snapshots` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LastID` int(11) NOT NULL DEFAULT '0',
  `Width` int(11) NOT NULL DEFAULT '0',
  `Height` int(11) NOT NULL DEFAULT '0',
  `DrawingID` int(11) NOT NULL DEFAULT '0',
  `SnapFile` varchar(255) DEFAULT '0',
  `xDate` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ID` (`ID`),
  KEY `LastID` (`LastID`),
  KEY `DrawingID` (`DrawingID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
