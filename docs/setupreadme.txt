Name,	Description,	mysqlTable
No,The horse number allocated to the horse.,	?
,This is unique only to the race and can change 
Name,The horses name its a unique field,	?

Form,The past performance of horse in previous races.,	?
,1 = 1st 8 = 8th and so on.
Odds,The odds of the horses which indicates how much you can win.,	?
Distance,The distance ran at a particular past race,	?
Sectional,The sectional time that the horse ran at past race,	?
,For example: last 600m what time was shown
,Lower times = Faster,
Minimum Time,The converted time before we apply formulas to reach handicap value.,	?
Race Pos,The position of the horse at past race .,	?	
Orig Weight,The weight of the horse at past race.,	?
Current Weight,The weight of the horse at this race.,	?
Handicap,The minimum time with handicap:see above for more info.,	?
Rating,The rating value applied to horse for this race.,	?
Rank,The ranking value applied to horse for this race.,	?







Name,	Description,	mysqlTable
No,The horse number allocated to the horse.,`tbl_temp_hraces`.`horse_num`
,This is unique only to the race and not past races 
Name,The horses name its a unique field,`tbl_horses`.`horse_name`

Form,The past performance of horse in previous races.,tbl_horses`.`horse_latest_results`
,1 = 1st 8 = 8th and so on.
Odds,The odds of the horses which indicates how much you can win in this race.,tbl_temp_hraces`.`horse_fxodds`
Distance,The distance ran at a particular past race,tbl_hist_results`.`race_distance`
Sectional,The sectional time that the horse ran at past race,`tbl_hist_results`.`sectional`
,For example: last 600m what time was shown
,Lower times = Faster,
Minimum Time,The converted time before we apply formulas to reach handicap value.,`tbl_hist_results`.`race_time`
Race Pos,The position of the horse at past race .,`tbl_hist_results`.`horse_position`	
Orig Weight,The weight of the horse at past race.,`tbl_hist_results`.`horse_weight`
Current Weight,The weight of the horse at this race.,tbl_temp_hraces`.`horse_weight`
Handicap,The minimum time with handicap:see above for more info.,`tbl_hist_results`.`handicap`
Rating,The rating value applied to horse for this race.,`tbl_hist_results`.`rating`
Rank,The ranking value applied to horse for this race.,`tbl_hist_results`.`rank`
