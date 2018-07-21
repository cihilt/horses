<?php

include('constant.php');
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sqlseq = "UPDATE `data` SET `sectional` = 0 WHERE `sectional`='-'"; //Resetting Rating 
$resultseq = $conn->query($sqlseq);
//$sql = "SELECT *, MIN(time) minimumtime,AVG(time) avgtime FROM data WHERE `name` IN (";
$sql = "SELECT * , MIN(data.time) minimumtime,MIN(data.time2) minimumtime2 FROM horses LEFT JOIN data ON horses.horse_name = data.name  GROUP BY id";


$result = $conn->query($sql);

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
        // $newhandicap = newvalue($row["length"], $row["original_distance"], $row["distance"], $row["pos"], number_format($row["minimumtime"],2));
        $updatehptime = "UPDATE `data` SET `handicap`=$newhandi WHERE id = $id";
        echo $updatehptime . "<br>";
        echo "-------------------";
        $result2 = $conn->query($updatehptime);
    }
} else {
    echo "0 results";
}
//Query to update the rank avg
$sql7 = "Update `data` SET rank = NULL"; //Resetting Rank
$result7 = $conn->query($sql7);
$sql2 = "SELECT *  FROM `minihand` LEFT JOIN rank_avg_data ON rank_avg_data.race_id = minihand.race_id AND rank_avg_data.distance = minihand.distance";

$result2 = $conn->query($sql2);
if ($result2->num_rows > 0) {
    // output data of each row
    while ($row = $result2->fetch_assoc()) {

        $sql2 = "SELECT *  FROM horses LEFT JOIN races ON races.race_id = horses.race_id WHERE horses.race_id = " . $row['race_id'];

        $result3 = $conn->query($sql2);
        $countofhorses = $result3->num_rows;

        $handicap = $row['minihandi'];
        $distance = $row['distance'];
        $horsename = str_replace("'", "\'", $row['horse_name']);
        $arr = explode(",", $row["handis"]);
        $cnt = count($arr);
        $per = ($cnt / $countofhorses) * 100;
        if ($per > 40) {
            $rank_avg = rank_avg($row["minihandi"], $arr, 0);
            $updaterankavg = "UPDATE `data` SET `rank` = '$rank_avg' WHERE  `distance`= '$distance' AND `name`= '$horsename'";
            echo $updaterankavg . "<br>";
            echo "-------------------";
        }

        $result4 = $conn->query($updaterankavg);
    }
}
/*
  $sql3 = "SELECT * , MIN(data.time) minimumtime,MIN(data.time2) minimumtime2 FROM horses
  LEFT JOIN data ON horses.horse_name = data.name
  LEFT JOIN rankavg ON horses.horse_name = rankavg.name GROUP BY id";
  $result3 = $conn->query($sql3);


  if ($result3->num_rows > 0) {
  // output data of each row
  while ($row = $result3->fetch_assoc()) {

  $rating = 0;
  if (strlen($row["horse_fixed_odds"]) > 0) {
  $rating = rating_system($row['avgrank'], $row["sectional"], $row["weight"], $row["horse_weight"]);
  $rating = number_format($rating, 0);
  } else {
  $rating = 0;
  }
  $id = $row['id'];
  if($id>0){
  // $newhandicap = newvalue($row["length"], $row["original_distance"], $row["distance"], $row["pos"], number_format($row["minimumtime"],2));
  $updatehptime1 = "UPDATE `data` SET `rating`=$rating WHERE id = $id";
  echo $updatehptime1 . "<br>";
  echo "-------------------";
  $result2 = $conn->query($updatehptime);
  }
  }
  } else {
  echo "0 results";
  }
 */
//Update rating using the avg sectional formula
//$sql5 = "SELECT *  FROM `maxsectional` LEFT JOIN sec_avg_data ON sec_avg_data.race_id = maxsectional.race_id AND sec_avg_data.distance = maxsectional.distance";


$sql6 = "Update `data` SET rating = NULL"; //Resetting Rating 
$result6 = $conn->query($sql6);


//$sql5 = "SELECT *  FROM `maxsectional` LEFT JOIN sec_avg_data ON sec_avg_data.race_id = maxsectional.race_id AND sec_avg_data.distance = maxsectional.distance";

$sql5 = "SELECT *  FROM `maxsectional` LEFT JOIN sec_avg_data ON sec_avg_data.race_id = maxsectional.race_id AND sec_avg_data.distance = maxsectional.distance
LEFT JOIN rank_avg_data ON rank_avg_data.race_id = maxsectional.race_id AND rank_avg_data.distance = maxsectional.distance
LEFT JOIN rankavg ON maxsectional.horse_name = rankavg.name";
$result5 = $conn->query($sql5);
if ($result5->num_rows > 0) {
    // output data of each row
    while ($row = $result5->fetch_assoc()) {


        $distance = $row['distance'];

        $sql2 = "SELECT *  FROM horses LEFT JOIN races ON races.race_id = horses.race_id WHERE horses.race_id  = " . $row['race_id'];

        $result3 = $conn->query($sql2);
        $countofhorses = $result3->num_rows;

        $handicap = $row['maxsectional'];



        $handis = $row['handis'];
        $arr2 = explode(",", $handis);
        $cnt1 = count($arr2); //Count of Handis
        $per1 = ($cnt1 / $countofhorses) * 100;


        $horsename = str_replace("'", "\'", $row['horse_name']);

        //Getting % of sectionals
        $arr = explode(",", $row["sectionals"]);

        $cnt = count($arr);
        $per = ($cnt / $countofhorses) * 100;
        //Checking rank value 
        if ($per1 > 40) {
            $rank = $row['avgrank'];
        } else {
            $rank = 0;
        }
        //Checking sectional value 
        if ($per > 40) {
            $sectional_avg = sectional_avg($row["maxsectional"], $arr, 0);
        } else {
            $sectional_avg = 0;
        }

      


        //echo $sectional_avg."<br/>";
        $rating = rating_system_new($rank, $sectional_avg, $row["weight"], $row["horse_weight"]);
        // echo $rating."<br/>";
        $updaterankavg1 = "UPDATE `data` SET `rating` = '$rating' WHERE `distance`= '$distance' AND `name`= '$horsename'";
        $result4 = $conn->query($updaterankavg1);

        echo $updaterankavg1 . "<br>";

        echo "-------------------";
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
