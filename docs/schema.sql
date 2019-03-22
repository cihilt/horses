/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table finaldata
# ------------------------------------------------------------

DROP VIEW IF EXISTS `finaldata`;

CREATE TABLE `finaldata` (
   `race_id` INT(11) UNSIGNED NOT NULL,
   `horse_name` VARCHAR(255) NULL DEFAULT NULL,
   `position` INT(11) NOT NULL,
   `horse_fixed_odds` VARCHAR(45) NULL DEFAULT NULL,
   `handicap` DECIMAL(3) NULL DEFAULT NULL,
   `sectional` VARCHAR(45) NULL DEFAULT NULL,
   `race_distance` VARCHAR(45) NULL DEFAULT NULL
) ENGINE=MyISAM;



# Dump of table jnik_view
# ------------------------------------------------------------

DROP VIEW IF EXISTS `jnik_view`;

CREATE TABLE `jnik_view` (
   `raceid` INT(11) UNSIGNED NULL DEFAULT NULL,
   `race_number` INT(11) NULL DEFAULT NULL,
   `horse_name` VARCHAR(255) NOT NULL,
   `position` INT(11) NULL DEFAULT NULL,
   `horse_fixed_odds` VARCHAR(45) NULL DEFAULT NULL,
   `distance` VARCHAR(45) NULL DEFAULT NULL,
   `handicap` DECIMAL(3) NULL DEFAULT NULL
) ENGINE=MyISAM;



# Dump of table tbl_algorithm
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tbl_algorithm`;

CREATE TABLE `tbl_algorithm` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL DEFAULT '',
  `is_default` tinyint(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table tbl_formulas
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tbl_formulas`;

CREATE TABLE `tbl_formulas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `secpoint` varchar(20) DEFAULT NULL,
  `timer` varchar(20) DEFAULT NULL,
  `position_percentage` varchar(3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table tbl_hist_results
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tbl_hist_results`;

CREATE TABLE `tbl_hist_results` (
  `hist_id` int(11) NOT NULL AUTO_INCREMENT,
  `race_id` int(11) unsigned NOT NULL,
  `race_date` varchar(45) NOT NULL,
  `race_distance` varchar(45) NOT NULL,
  `horse_id` int(11) DEFAULT NULL,
  `h_num` varchar(5) DEFAULT NULL,
  `horse_position` int(11) DEFAULT NULL,
  `horse_weight` varchar(45) NOT NULL,
  `horse_fixed_odds` varchar(45) DEFAULT NULL,
  `horse_h2h` varchar(45) DEFAULT NULL,
  `prize` varchar(45) NOT NULL,
  `race_time` varchar(45) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `length` decimal(3,1) NOT NULL,
  `sectional` varchar(45) NOT NULL,
  `avgsec` double NOT NULL DEFAULT '0',
  `avgsectional` decimal(3,2) NOT NULL DEFAULT '0.00',
  `handicap` decimal(3,2) NOT NULL,
  `rating` float NOT NULL,
  `rank` decimal(3,2) NOT NULL,
  PRIMARY KEY (`hist_id`),
  KEY `horse_id` (`horse_id`),
  KEY `race_id` (`race_id`),
  CONSTRAINT `tbl_hist_results_ibfk_2` FOREIGN KEY (`race_id`) REFERENCES `tbl_races` (`race_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tbl_hist_results_ibfk_1` FOREIGN KEY (`horse_id`) REFERENCES `tbl_horses` (`horse_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table tbl_horses
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tbl_horses`;

CREATE TABLE `tbl_horses` (
  `horse_id` int(11) NOT NULL AUTO_INCREMENT,
  `horse_name` varchar(255) NOT NULL,
  `horse_slug` varchar(255) NOT NULL,
  `horse_latest_results` varchar(45) DEFAULT NULL,
  `added_on` varchar(25) NOT NULL,
  PRIMARY KEY (`horse_id`),
  UNIQUE KEY `horse_name` (`horse_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table tbl_meetings
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tbl_meetings`;

CREATE TABLE `tbl_meetings` (
  `meeting_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `meeting_date` varchar(45) DEFAULT NULL,
  `meeting_name` varchar(45) DEFAULT NULL,
  `meeting_url` varchar(255) DEFAULT NULL,
  `added_on` varchar(25) NOT NULL,
  PRIMARY KEY (`meeting_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table tbl_races
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tbl_races`;

CREATE TABLE `tbl_races` (
  `race_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `old_race_id` int(11) NOT NULL,
  `meeting_id` int(11) unsigned DEFAULT NULL,
  `race_order` int(11) DEFAULT NULL,
  `race_schedule_time` varchar(45) DEFAULT NULL,
  `race_title` varchar(45) DEFAULT NULL,
  `race_slug` varchar(45) NOT NULL,
  `race_distance` int(7) NOT NULL DEFAULT '0',
  `round_distance` int(11) NOT NULL,
  `race_url` varchar(255) NOT NULL,
  `rank_status` int(11) NOT NULL,
  `sec_status` int(11) DEFAULT NULL,
  PRIMARY KEY (`race_id`),
  KEY `meeting_id` (`meeting_id`),
  CONSTRAINT `tbl_races_ibfk_1` FOREIGN KEY (`meeting_id`) REFERENCES `tbl_meetings` (`meeting_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table tbl_results
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tbl_results`;

CREATE TABLE `tbl_results` (
  `result_id` int(11) NOT NULL AUTO_INCREMENT,
  `race_id` int(11) unsigned NOT NULL,
  `horse_id` int(11) DEFAULT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`result_id`),
  KEY `race_id` (`race_id`),
  KEY `horse_id` (`horse_id`),
  CONSTRAINT `tbl_results_ibfk_2` FOREIGN KEY (`horse_id`) REFERENCES `tbl_horses` (`horse_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tbl_results_ibfk_1` FOREIGN KEY (`race_id`) REFERENCES `tbl_races` (`race_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table tbl_temp_hraces
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tbl_temp_hraces`;

CREATE TABLE `tbl_temp_hraces` (
  `race_id` int(11) unsigned NOT NULL COMMENT 'h',
  `horse_id` int(11) NOT NULL,
  `horse_num` int(11) NOT NULL,
  `horse_fxodds` varchar(45) NOT NULL,
  `horse_h2h` varchar(45) NOT NULL,
  `horse_weight` varchar(45) DEFAULT NULL,
  `horse_win` varchar(45) DEFAULT NULL,
  `horse_plc` varchar(45) DEFAULT NULL,
  `horse_avg` varchar(45) DEFAULT NULL,
  KEY `race_id` (`race_id`),
  KEY `horse_id` (`horse_id`),
  CONSTRAINT `tbl_temp_hraces_ibfk_2` FOREIGN KEY (`horse_id`) REFERENCES `tbl_horses` (`horse_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tbl_temp_hraces_ibfk_1` FOREIGN KEY (`race_id`) REFERENCES `tbl_races` (`race_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Replace placeholder table for finaldata with correct view syntax
# ------------------------------------------------------------

DROP TABLE `finaldata`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `finaldata`
AS SELECT
   `tbl_results`.`race_id` AS `race_id`,
   `tbl_horses`.`horse_name` AS `horse_name`,
   `tbl_results`.`position` AS `position`,
   `tbl_hist_results`.`horse_fixed_odds` AS `horse_fixed_odds`,
   `tbl_hist_results`.`handicap` AS `handicap`,
   `tbl_hist_results`.`sectional` AS `sectional`,
   `tbl_hist_results`.`race_distance` AS `race_distance`
FROM ((`tbl_results` left join `tbl_hist_results` on((`tbl_results`.`race_id` = `tbl_hist_results`.`race_id`))) left join `tbl_horses` on((`tbl_results`.`horse_id` = `tbl_horses`.`horse_id`)));



# Replace placeholder table for jnik_view with correct view syntax
# ------------------------------------------------------------

DROP TABLE `jnik_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `jnik_view`
AS SELECT
   `tbl_results`.`race_id` AS `raceid`,
   `tbl_races`.`race_order` AS `race_number`,
   `tbl_horses`.`horse_name` AS `horse_name`,
   `tbl_results`.`position` AS `position`,
   `tbl_hist_results`.`horse_fixed_odds` AS `horse_fixed_odds`,
   `tbl_hist_results`.`race_distance` AS `distance`,
   `tbl_hist_results`.`handicap` AS `handicap`
FROM (((`tbl_horses` left join `tbl_hist_results` on((`tbl_horses`.`horse_id` = `tbl_hist_results`.`horse_id`))) left join `tbl_results` on((`tbl_horses`.`horse_id` = `tbl_results`.`horse_id`))) left join `tbl_races` on((`tbl_races`.`race_id` = `tbl_results`.`race_id`))) where (`tbl_results`.`race_id` is not null) group by `tbl_hist_results`.`hist_id` order by `tbl_hist_results`.`hist_id` desc;

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
