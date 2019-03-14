Project Goal
############

My site tries to  predicts the position of a horse to real horse races in Australia.
The prediction is done by combining current and past data and applying algorithms onto this data.
Currently there is two algorithms.

The first one is located at:
/beta/updatehptime.php

My goal, is to be able to switch algorithms and apply them with a click of a button.

The issue is currently that only raceID can be selected.
I would like to add an additional option, where I can select all Races.



Details
######################

How to do this
-------------

Currently we are selecting one 



How The site works
###################
In order to do race predicions, we need to have data.
This data is historical data and current data.
This data collectively is used to give a Ranking and a Rating. 

This injected data needs to be organized, so we organise this data in the following order.

Meetings -> Race Name(Number) -> Race ID -> Horses.


First there is meetings, like location (randwick, flemington), 
these meetings are attached to a date: i.e (29th,30th, 1st, 2nd and so on.


Under the meeting date, is the race name, or race number.
1,2,3,4,5,6,7,8

In the db these are set at race id.
i.e 672 would be found as http://209.182.232.82/beta/race.php?race=672


For each raceid, we gather information about the horses past and present.
This information is contained in the db tables
--> tbl_hist_results (past races)
--> tbl_horses & tbl_temp_hraces (current race)

The order could be represented like this:

Meeting id (91)
 |
 -> Race id (667-675)
    |
     -> Race Id selected(672)(tbl_horses & tbl_temp_hraces)
        |
        -> Horse Id (692)->Winx
            |
             -> Distances
                (1000)
                (1100)
		(1200)
		(1300)
		(1400)
		(1500)
              **(1600)** (race id 672 is run at this distance)
		(1700)                  |
		(1800)                  --> tbl_hist_results (past races)
                (.../) and so on
                  |
                  -> tbl_hist_results (past races)


 
How does the Algorithm Work
###############################

We get the following data, such as  past Historical Data 
-> tbl_hist_results (past races) and race data (tbl_horses & tbl_temp_hraces)

But while retrieving this data, we add a formula to the field called handicap.
Handicap = Minimum Time + (extra functions)

These extra functions are (distance rounding)
                          (Length behind winner)

Distance Rounding
-----------------
Distance Rounding is done, when the horses (past races) are at unusual distance, such as 1205, 1330.
We round this off to the closest 1000 and apply either a time increase penalty, or time reduction)


Length behind Winner
-------------------
Its rare to be able to find individual horse time, so we use the time from the winner.
If the horse didn't win, we use length from the winner and apply a penalty.

(Using these two functions is how we get handicap value)


How is Average Rank Algorithm calculated?
##########################################
At this stage, AverageRank = fastest handicap time with a point allocation system.
More information about how this works can be found on distance.php with checking comments //gavri.

In summary, we retreive the historical data and group them into distances.
For instance: Horse can have previously raced at:
 1200,1300,1400,1600,1700

We combine horses that have raced at the same distance.
So these horses should all have similar times.

A good example of this is Raceid 672i, below I will describe this in the code.

############### Start Code BreakDown

We loop through a particular race id, where the odds are not 0.
Then we loop through each distance.

Starting from maybe 800, then 900, then 1000, then 1100, 1200.
We then count the number of unique entries in each of these historical distance.

if($countofhorses > 0) {
                                                                        $per = ($cnt / $countofhorses) * 100;

If this condition is passed we then loop through the distances (DISTINCT) then order in ascending.

 while ($rowrc = $raceres->fetch_object()) {
                                $countofhorses = get_rows("`tbl_temp_hraces` WHERE `race_id`='$rowrc->race_id' AND `horse_fxodds`!='0'");
                                echo 'All below results are for Race ID: ' . $rowrc->race_id . '<br /><br />';
                                $get_distances = $mysqli->query("SELECT DISTINCT CAST(race_distance AS UNSIGNED) AS racedist FROM tbl_hist_results WHERE `race_id`='$rowrc->race_id' AND `race_distance`='$distance' ORDER by racedist ASC");

After looping through the required distances, we put these into an array called $numbersarray

while($dists = $get_distances->fetch_object()) {
                    // echo '<b>$dists->racedist</b>: '.$dists->racedist.'<br>';
                                        $handitotal = get_handisum($rowrc->race_id, $dists->racedist);
                                        //echo $dists->racedist . ' ( ' . $handitotal . ' )<br />';
                                        $numbersarray = get_array_of_handicap($rowrc->race_id, $dists->racedist);
                                        $cnt = count($numbersarray);

Say for a particular race id  = 672 and race distance of 1200, you would end up with the following array.
Noticed the minimum, this is the one we select

                    // handicap of Happy Clapper 1.18, 1.24,1.22. minimum is 1.18
                    // handicap of Patrick Erin 1.3, 1.3, 1.34, 1.27, 1.26, 1.25, 1.24. minimum is 1.24
                    // handicap of  Winx 1.24. minimum is 1.24
                    // handicap of Unforgotten 1.24, 1.26. minimum is 1.24


Then we use this minimum time and allocate a points system

function generate_rank($value, $array, $order = 0) {
// sort  
    //$value = 1.18, $array = [1.18,1.24,1.24,1.24], $order = 0
    //$order = 0 so go to rsort($array)
    if ($order)
        sort($array);
    else
        rsort($array);
    //rsort function is function that sorts an indexed array in descending order
    // so $array = [1.24,1.24,1.24,1.18]
// add item for counting from 1 but 0
    array_unshift($array, $value + 1);
    //array_unshift is function that adds one or more elements to the beginning of an array
    // so $array = [2.18, 1.24, 1.24, 1.24, 1.18]
// select all indexes vith the value
    $keys = array_keys($array, $value);
    //array_keys(a, b) Returns all the keys of an array that value is $value
    // $value = 1.18 so $keys = [4]
    if (count($keys) == 0)
        return NULL;
// calculate the rank

    // $res = array_sum($keys) / count($keys);

    $me = count($keys) ;
    //array_sum returns the sum of the values in an array
    //so $res = 4 / 1
    //result this function return 2
    // go to previous function Line: 463
    // return $res / 2;
    return $me;





About race.php
#####################

Race combines the current race data such as:
No, Weight, Form, Odds

Race also combines a  past history of the horse such as:
Distance, sectional,Race Pos, Rating, Rank

So If you entered:
http://209.182.232.82/beta/race.php?race=672
You will see the combination of two tables gathered to display details.


The following fields mean the following: 

+----------------+---------------------------------------------------------------------------+-----------------------------------------+
|      Name      |                                  Description                              |                 mysqlTable              |
+----------------+---------------------------------------------------------------------------+-----------------------------------------+
| No             | The horse number allocated to the horse.                                  | `tbl_temp_hraces`.`horse_num`           |
|                | This is unique only to the race and not past races                        |                                         |
| Name           | The horses name its a unique field                                        | `tbl_horses`.`horse_name`               |
| Form           | The past performance of horse in previous races.                          | tbl_horses`.`horse_latest_results`      |
|                | 1 = 1st 8 = 8th and so on.                                                |                                         |
| Odds           | The odds of the horses which indicates how much you can win in this race. | tbl_temp_hraces`.`horse_fxodds`         |
| Distance       | The distance ran at a particular past race                                | tbl_hist_results`.`race_distance`       |
| Sectional      | The sectional time that the horse ran at past race                        | `tbl_hist_results`.`sectional`          |
|                | For example: last 600m what time was shown                                |                                         |
|                | Lower times = Faster                                                      |                                         |
| Minimum Time   | The converted time before we apply formulas to reach handicap value.      | `tbl_hist_results`.`race_time`          |
| Race Pos       | The position of the horse at past race .                                  | `tbl_hist_results`.`horse_position`     |
| Orig Weight    | The weight of the horse at past race.                                     | `tbl_hist_results`.`horse_weight`       |
| Current Weight | The weight of the horse at this race.                                     | tbl_temp_hraces`.`horse_weight`         |
| Handicap       | The minimum time with handicap:see above for more info.                   | `tbl_hist_results`.`handicap`           |
| Rating         | The rating value applied to horse for this race.                          | `tbl_hist_results`.`rating`             |
| Rank           | The ranking value applied to horse for this race.                         | `tbl_hist_results`.`rank`               |
+----------------+---------------------------------------------------------------------------+-----------------------------------------+

 

Important note: 
ID is not shown, but its how we identify the horses between different races such as in:
SELECT * FROM `tbl_temp_hraces` WHERE horse_id = 692


+-----------+----------+-----------+--------------+-----------+--------------+-----------+-----------+-----------+
| race_id h | horse_id | horse_num | horse_fxodds | horse_h2h | horse_weight | horse_win | horse_plc | horse_avg |
+-----------+----------+-----------+--------------+-----------+--------------+-----------+-----------+-----------+
|        61 |      692 |        10 | $1.24        | 7-0       | NULL         | NULL      | NULL      | NULL      |
|       219 |      692 |         5 | $1.1         | 11-0      | NULL         | NULL      | NULL      | NULL      |
|       672 |      692 |         5 | $1.09        | 15-0      | 57           | 85%       | 93%       | 577k      |
+-----------+----------+-----------+--------------+-----------+--------------+-----------+-----------+-----------+


SQL query: SELECT * FROM `tbl_hist_results` WHERE horse_id = 692 LIMIT 0, 25 ;
*I've taken out some columsn at the end to fit, that store the formulas, this is just to show that horseID 692 is called from two parts.


  hist_id   race_id   race_date    race_distance   horse_id   h_num   horse_position   horse_weight   horse_fixed_odds   horse_h2h     prize     race_time   length   sectional   avgsec  
 --------- --------- ------------ --------------- ---------- ------- ---------------- -------------- ------------------ ----------- ----------- ----------- -------- ----------- -------- 
    11245        61   2018-04-14            2000        692      10                1           57.0   $1.24              7-0         2M/4M            2.03      3.8   600/34.32    34.32  
    11246        61   2018-03-24            1500        692      10                1           57.0   $1.24              7-0         580k/1M          1.52      0.8   600/34.06    34.06    
    11247        61   2018-03-03            1600        692      10                1           57.0   $1.24              7-0         344k/600k        1.58      7.0   600/35.41    35.41   
    11248        61   2017-10-28            2000        692      10                1           57.0   $1.24              7-0         1.8M/3M          2.05      0.4   600/34.93    34.93  
    11249        61   2017-10-07            2000        692      10                1           57.0   $1.24              7-0         300k/500k        2.03      6.5   600/34.92    34.92 



+--------------------------+----------------------------------------------------------------------------------------------------+-------------------------------------------------+
|          files           |                                            Description                                             |                     Weblink                     |
+--------------------------+----------------------------------------------------------------------------------------------------+-------------------------------------------------+
| distance_new.php         | Beta testing of new formula that should be implemented with new algorithm switcher                 |                                                 |
|                          |                                                                                                    |                                                 |
| distance.php             | Allow Debugging of updatehptime.php so that we know how we're getting the correct values.          |                                                 |
|                          | Currently this page needs more work to be able to exactly show how we got there.                   |                                                 |
|                          |                                                                                                    |                                                 |
| horse.php                | New Page that will display information about a horse:                                              | http://209.182.232.82/beta/horse.php?horse=1    |
|                          |                                                                                                    |                                                 |
| horses.php               | Alphabetical listing of horses,                                                                    |                                                 |
|                          | this page should be hidden if the page ever goes into production due to load:                      | http://209.182.232.82/beta/horses.php           |
| includes (folder)        |                                                                                                    |                                                 |
| include.config.php       |                                                                                                    |                                                 |
| functions.php            | Contains the functions that retriever data using sql queries connected to db,                      |                                                 |
|                          | some popular ones are $horsedet,$racedet & $resdet which are used by updatehptime.php              |                                                 |
|                          |                                                                                                    |                                                 |
| index2_console.php       | This is the console version of the scraper,                                                        |                                                 |
|                          | which can be run via cron or via terminal, this connects to a racing webpage and retrieves data,   |                                                 |
|                          | about the current races and historical data for the horses.                                        |                                                 |
| index2.php               | This is the online version of the scraper, this can be run via a web browser                       |                                                 |
|                          | and allows you to select past dates.                                                               |                                                 |
|                          | Data is retreived via a racing webpage that collects:                                              |                                                 |
|                          | current races and historical data for the horses.                                                  |                                                 |
|                          |                                                                                                    |                                                 |
| index.php                | This is the homepage, this shows how are algorithm is performing.                                  |                                                 |
|                          | Currently with all races, averageRank is giving: AVG Rank Total Profit: -$6509.                    |                                                 |
|                          | This is worked out by having a stake of $10 and choosing two horses with the fastest average rank. |                                                 |
|                          |                                                                                                    |                                                 |
| logs.txt                 | Log file to debug scraping, or formula updates.                                                    |                                                 |
|                          |                                                                                                    |                                                 |
| meeting.php              | We can see the locations of racing venues in Australia:                                            | http://209.182.232.82/beta/meeting.php          |
| php.ini                  | Access PHP server configuration                                                                    |                                                 |
|                          |                                                                                                    |                                                 |
| race.php                 | The actual race, each race has its own race id, for instance:                                      | http://209.182.232.82/beta/race.php?race=672    |
|                          |                                                                                                    |                                                 |
| races.php                | This page shows the race meeting with the race names and number of races, as well as the timing.   |                                                 |
|                          | Typically this can be seen at:                                                                     | http://209.182.232.82/beta/races.php?meeting=91 |
|                          |                                                                                                    |                                                 |
| results_console.php      | This is the console version to retreive the race results, which can be run from a console.         |                                                 |
|                          | This retreives the current race data.                                                              |                                                 |
|                          |                                                                                                    |                                                 |
| results_core.php         | Used by both results.php and results_console.php,                                                  |                                                 |
|                          | this plugs in the db details sets details and organizes the printing.                              |                                                 |
|                          |                                                                                                    |                                                 |
| results.php              | This is the web version to retreive the race results, it cannot be run from console,               |                                                 |
|                          | but allows you to select previous dates.                                                           |                                                 |
|                          |                                                                                                    |                                                 |
| simple_html_dom.php      | Provides a set of classes used by results_core.php                                                 |                                                 |
|                          |                                                                                                    |                                                 |
| updatehptime_console.php | The console version of updatehptime, that will update every single race id,                        |                                                 |
|                          | this can be run by console and can take more than 1 hour to finish.                                |                                                 |
|                          |                                                                                                    |                                                 |
| updatehptime.php         | The page allows you to apply the algorithms.                                                       |                                                 |
|                          | Currently this is controlled by an algorithm switcher.                                             |                                                 |
|                          | In order to add new formulas, you will need to wrap these into a function.                         |                                                 |
+--------------------------+----------------------------------------------------------------------------------------------------+-------------------------------------------------+
