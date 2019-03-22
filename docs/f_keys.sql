ALTER TABLE `tbl_results`
  MODIFY `race_id` int(11) UNSIGNED,
  MODIFY `horse_id` int(11) NULL;

UPDATE `tbl_results` SET `horse_id` = null WHERE horse_id = '0';

ALTER TABLE `tbl_results`
  ADD CONSTRAINT `tbl_results_ibfk_1` FOREIGN KEY (`race_id`) REFERENCES `tbl_races` (`race_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_results_ibfk_2` FOREIGN KEY (`horse_id`) REFERENCES `tbl_horses` (`horse_id`) ON DELETE CASCADE ON UPDATE CASCADE;


# ------------------------------------------------------------


ALTER TABLE `tbl_hist_results`
  MODIFY `race_id` int(11) UNSIGNED;

ALTER TABLE `tbl_hist_results`
  ADD CONSTRAINT `tbl_hist_results_ibfk_1` FOREIGN KEY (`horse_id`) REFERENCES `tbl_horses` (`horse_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_hist_results_ibfk_2` FOREIGN KEY (`race_id`) REFERENCES `tbl_races` (`race_id`) ON DELETE CASCADE ON UPDATE CASCADE;


# ------------------------------------------------------------


ALTER TABLE `tbl_temp_hraces`
  MODIFY `race_id` int(11) UNSIGNED;

ALTER TABLE `tbl_temp_hraces`
  ADD CONSTRAINT `tbl_temp_hraces_ibfk_2` FOREIGN KEY (`horse_id`) REFERENCES `tbl_horses` (`horse_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_temp_hraces_ibfk_1` FOREIGN KEY (`race_id`) REFERENCES `tbl_races` (`race_id`) ON DELETE CASCADE ON UPDATE CASCADE;


# ------------------------------------------------------------


ALTER TABLE `tbl_races`
  MODIFY `meeting_id` int(11) UNSIGNED;

ALTER TABLE `tbl_races`
  ADD CONSTRAINT `tbl_races_ibfk_1` FOREIGN KEY (`meeting_id`) REFERENCES `tbl_meetings` (`meeting_id`) ON DELETE CASCADE ON UPDATE CASCADE;


