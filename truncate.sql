ALTER TABLE `races` ADD `race_distance` INT(7) NOT NULL DEFAULT '0' AFTER `race_title`;
ALTER TABLE `horses` ADD `horse_latest_results` VARCHAR(45) NULL DEFAULT NULL AFTER `horse_h2h`;
