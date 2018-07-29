<?php

include('constant.php');

// Check connection

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// first update 

$sqlseq = "UPDATE `data` SET `sectional` = 0 WHERE `sectional`='-'"; //Resetting Rating 
$resultseq = $conn->query($sqlseq);

$sql = "SELECT * , MIN(data.time) minimumtime,MIN(data.time2) minimumtime2 FROM horses LEFT JOIN data ON horses.horse_name = data.name  GROUP BY id";
$result = $conn->query($sql);
$updatehptime = '';

if ($result->num_rows > 0) {
    // output data of each row
    while ($row = $result->fetch_assoc()) {

        $distance = round($row["original_distance"] / 100);
        $distance = $distance * 100;
        $newhandicap = newvalue($row["length"], $row["original_distance"], $distance, $row["pos"], number_format($row["minimumtime"], 2));
        $newhandi = number_format($newhandicap, 3);

        $rating = 0;
        if (strlen($row["horse_fixed_odds"]) > 0) {
            $rating = rating_system($newhandicap, $row["sectional"], $row["weight"], $row["horse_weight"]);
            $rating = number_format($rating, 0);
        } else {
            $rating = 0;
        }
        $id = $row['id'];
        $updatehptime .= "UPDATE `data` SET `handicap`=$newhandi WHERE id = $id;  ";
    }
    echo $updatehptime . "<br><br><br><br><br><br>";
    echo "--------------------------------------------------------------------------------------------";
    $result2 = $conn->query($updatehptime);
} else {
    echo "0 results";
}

//Query to update the rank avg

$numArray = array();
$numArray[0] = 0;
$sql20 = "SELECT COUNT(horses.race_id) as num, horses.race_id FROM horses LEFT JOIN races ON races.race_id = horses.race_id GROUP BY horses.race_id ORDER BY horses.race_id";

$result20 = $conn->query($sql20);
if ($result20->num_rows > 0) {
    while ($rownum = $result20->fetch_assoc()) {
        $numArray[$rownum[race_id]] = $rownum[num];
    }
}

$sql7 = "Update `data` SET rank = NULL"; //Resetting Rank
$result7 = $conn->query($sql7);

$sql2 = "SELECT *  FROM `minihand` LEFT JOIN rank_avg_data ON rank_avg_data.race_id = minihand.race_id AND rank_avg_data.distance = minihand.distance";
$updaterankavg = '';
$rank_avg = 0;

$result2 = $conn->query($sql2);
if ($result2->num_rows > 0) {
    // output data of each row
    while ($row = $result2->fetch_assoc()) {

        $countofhorses = intval($numArray[$row['race_id']]);
        //echo $countofhorses;
        $handicap = $row['minihandi'];
        $distance = $row['distance'];
        $horsename = str_replace("'", "\'", $row['horse_name']);
        $arr = explode(",", $row["handis"]);
        $cnt = count($arr);
        $per = ($cnt / $countofhorses) * 100;

        if ($per > 40) {
            $rank_avg = rank_avg($row["minihandi"], $arr, 0);
            $rank_avg = number_format($rank_avg, 2, '.', '');
            $updaterankavg = "UPDATE data SET rank = ".$rank_avg." WHERE  distance= '".$distance."' AND name= '".$horsename."';";
            $result4 = $conn->query($updaterankavg);
            echo $updaterankavg . "";
        }
    }
    echo "----------------------------------------------------------------------------------------------";
}

// third update

$sql6 = "Update `data` SET rating = NULL"; //Resetting Rating
$result6 = $conn->query($sql6);

$rank_avg_dataArray_id = array();
$rank_avg_dataArray = array();
$sql_rank_avg_data = "SELECT race_id, distance, handis  FROM rank_avg_data";
$result_rank_avg_data = $conn->query($sql_rank_avg_data);
if ($result_rank_avg_data->num_rows > 0) {
    while($row = $result_rank_avg_data->fetch_assoc()) {
        $rank_avg_dataArray_id[] = $row['handis'];
        $rank_avg_dataArray[] = array($row['race_id'], $row['distance']);
    }            
}
//print_r($rank_avg_dataArray);

$rankavgArray = array();
$rankavgArray_id = array();
$sql_rankavg = "SELECT name, avgrank FROM rankavg";
$result_rankavg = $conn->query($sql_rankavg);
if ($result_rankavg->num_rows > 0) {
    while($row = $result_rankavg->fetch_assoc()) {
        $rankavgArray_id[] = $row['name'];
        $rankavgArray[] = $row['avgrank'];
    }            
}
//print_r($rankavgArray);

$sec_avg_data = array();
$sec_avg_data_id = array();
$sql_sec_avg_data = "SELECT * FROM `sec_avg_data`";
$result_sec_avg_data = $conn->query($sql_sec_avg_data);
if ($result_sec_avg_data->num_rows > 0) {
    while($row = $result_sec_avg_data->fetch_assoc()) {
        $sec_avg_data_id[] = $row['sectionals'];
        $sec_avg_data[] = array($row['race_id'], $row['distance']);
    }            
}
//print_r($sec_avg_data);

$sql5 = "SELECT * FROM `maxsectional`" ;
$result5 = $conn->query($sql5);

$updaterankavg1 = '';

if ($result5->num_rows > 0) {
    // output data of each row
    while ($row = $result5->fetch_assoc()) {

        $distance = $row['distance'];
        $countofhorses = intval($numArray[$row['race_id']]);
        $handicap = $row['maxsectional'];
        
        if(in_array(array($row['race_id'], $distance),$rank_avg_dataArray)){
        	$handis = $rank_avg_dataArray_id[ array_search( array($row['race_id'], $distance), $rank_avg_dataArray ) ];
        }else {$handis = '';}
        
        $arr2 = explode(",", $handis);
        $cnt1 = count($arr2); //Count of Handis
        $per1 = ($cnt1 / $countofhorses) * 100;
        $horsename = str_replace("'", "\'", $row['horse_name']);

        //Getting % of sectionals
        
        $sectionals = '';
        if(in_array(array($row['race_id'], $distance), $sec_avg_data)){
        	$sectionals = $sec_avg_data_id[ array_search( array($row['race_id'], $distance), $sec_avg_data ) ];
        }else {$sectionals = '';}  
        
        $arr = explode(",", $sectionals);
        $cnt = count($arr);
        $per = ($cnt / $countofhorses) * 100;
        //Checking rank value 
        if ($per1 > 40) {
            
            if(in_array($row['horse_name'], $rankavgArray_id)){
            	$rank = $rankavgArray[ array_search( $row['horse_name'], $rankavgArray_id ) ];
            }else {$rank = 0;}
            
        } else {
            $rank = 0;
        }
        
        //Checking sectional value 
        if ($per > 40) {
            $sectional_avg = sectional_avg($row["maxsectional"], $arr, 0);
        } else {
            $sectional_avg = 0;
        }
        $rating = rating_system_new($rank, $sectional_avg, $row["weight"], $row["horse_weight"]);
        $updaterankavg1 = "UPDATE `data` SET `rating` = $rating WHERE `distance`= '$distance' AND `name`= '$horsename';  ";
        echo $updaterankavg1 . " -";
        $result4 = $conn->query($updaterankavg1);
    }
}

function newvalue($length, $distance, $orgdistance, $pos, $time) {

    //Getting the postion of the horse
    $pos = explode('/', $pos);
    $position = intval($pos[0]);
    $modifier = MODIFIER;
    $remainder = get_remainder($distance);

    if ($position == 1) {

        if ($distance < $orgdistance) {

            $newtime = win_rounded_up($time, $length, $modifier, $remainder);
        } else {
            $newtime = win_rounded_down($time, $length, $modifier, $remainder);
        }
    } else {
        if ($distance < $orgdistance) {
            $newtime = loses_rounded_up($time, $length, $modifier, $remainder);
        } else if ($distance > $orgdistance) {
            $newtime = loses_rounded_down($time, $length, $modifier, $remainder);
        } else {
            $newtime = $time + ($length * $modifier);
        }
    }
    return $newtime;
}

function get_remainder($distance) {

    if ($distance % 10 < 5) {
        $distance -= $distance % 10;
    } else {
        $distance += (10 - ($distance % 10));
    }

    if ($distance % 100 < 50) {
        $reminder_distance = $distance % 100;
    } else {
        $reminder_distance = (100 - ($distance % 100));
    }
    $reminder = $reminder_distance;
    return $reminder;
}

//if horse wins   
function win_rounded_up($time, $length, $modifier, $remainder) {

    $newtime = $time + (0.0007 * $remainder);
    return $newtime;
}

//if horse wins  
function win_rounded_down($time, $length, $modifier, $remainder) {

    $newtime = $time - (0.0007 * $remainder);
    return $newtime;
}

//if horse loses  
function loses_rounded_up($time, $length, $modifier, $remainder) {
    //time+(length*modifier)-(0.0007*$remainder);

    $newtime = $time + ($length * $modifier) + (0.0007 * $remainder);
    return $newtime;
}

//if horse loses  
function loses_rounded_down($time, $length, $modifier, $remainder) {

    $newtime = $time + ($length * $modifier) - (0.0007 * $remainder);
    return $newtime;
}

function rating_system($handicap, $section, $oldweight, $newweight) {
    $pos = explode('/', $section);

    if (isset($pos[1])) {
        $sectiontime = $pos[1];
    } else {
        $sectiontime = 0;
    }

    $weight = weight_points($oldweight, $newweight);
    $handicappoints = $handicap;
    // $handicappoints = $rankavg;
    //$sectionpoints = sectional avgvalue from  forumla;
    if ($sectiontime == 0) {
        $sectionpoints = 0;
    } else {
        $sectionpoints = (9 / $sectiontime) * 100;
    }
    $rating = $handicappoints + $sectionpoints + ($weight / 100);
    return $rating;
}

function weight_points($oldweight, $newweight) {

    $weight = $newweight - $oldweight;

    if ($weight > 3) {
        $wgt = 1.5;
        return $wgt;
    }
    if ($weight > 2 && $weight <= 2.5) {
        $wgt = 1;
        return $wgt;
    }
    if ($weight > 1 && $weight <= 1.5) {
        $wgt = 0.5;
        return $wgt;
    }
    if ($weight > 0 && $weight <= 0.5) {
        $wgt = 1;
        return $wgt;
    }
    if ($weight > -0.5 && $weight <= 0) {
        $wgt = -1.5;
        return $wgt;
    }
    if ($weight > -1 && $weight <= -1.5) {
        $wgt = -2;
        return $wgt;
    }
    if ($weight > -1 && $weight <= -2.5) {
        $wgt = -2;
        return $wgt;
    }
    if ($weight > -3 && $weight < -2.5) {
        $wgt = -3;
        return $wgt;
    }
}

function rank_avg($value, $array, $order = 0) {
// sort  
    if ($order)
        sort($array);
    else
        rsort($array);
// add item for counting from 1 but 0
    array_unshift($array, $value + 1);
// select all indexes vith the value
    $keys = array_keys($array, $value);
    if (count($keys) == 0)
        return NULL;
// calculate the rank

    $res = array_sum($keys) / count($keys);
        return $res / 2;
}

function sectional_avg($value, $array, $order = 0) {

// sort  
    if ($order)
        sort($array);
    else
        rsort($array);
// add item for counting from 1 but 0
    array_unshift($array, $value + 1);
// select all indexes vith the value
    $keys = array_keys($array, $value);
    if (count($keys) == 0)
        return NULL;
// calculate the rank

    $res = array_sum($keys) / count($keys);

    return $res / 2;
}

function rating_system_new($rankavg, $avgsectional, $oldweight, $newweight) {
    //$rating = $rankavg;
    //$rating = $rankavg+$avgsectional;
    //$weight = weight_points($oldweight, $newweight);
    //$handicappoints = $handicap;

    $rating = $rankavg + $avgsectional;

    return $rating;
}

?>
