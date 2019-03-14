<?php
include('includes/config.php');
include('includes/functions.php');
// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if(!empty($_GET['limit'])) { 
	$limit = $_GET['limit'];
} else {
	$limit = 0;
}


//Reset Sectional Start
$sqlseq = "UPDATE `tbl_hist_results` SET `sectional`='0' WHERE `sectional`='-'";
$resultseq = $mysqli->query($sqlseq);
	
//Reset Sectional End
//Sectional Start
if($limit == 0) {
	$getsec = $mysqli->query("SELECT `hist_id`, `sectional` FROM `tbl_hist_results` WHERE `sectional`!='0' AND `avgsec`=''");
}
else {
	$getsec = $mysqli->query("SELECT `hist_id`, `sectional` FROM `tbl_hist_results` WHERE `sectional`!='0' AND `avgsec`='' LIMIT $limit");
}
if($getsec->num_rows > 0) {
	while($getsc = $getsec->fetch_object()) {
		$sectional = explode("/", $getsc->sectional);
		if($sectional[0] < 651) {
			$avgvalue = $sectional[1];
			$sqlseq = $mysqli->query("UPDATE `tbl_hist_results` SET `avgsec`='$avgvalue' WHERE `hist_id`='$getsc->hist_id'");
		}
	}
}
else {
	echo '0 Results. <a href="./updatehptime.php">Click Here</a> to go back';
}

// Sectional End
// Reset Handicap
$sqlhand = "";
if($limit == 0) {
	$sqlhand = "UPDATE `tbl_hist_results` SET `handicap`='0' WHERE `handicap`!='0'";
} else {
	$sqlhand = "UPDATE `tbl_hist_results` SET `handicap`='0' WHERE `handicap`!='0' ORDER BY hist_id ASC LIMIT $limit";
}
$resulthand = $mysqli->query($sqlhand);

// Reset Handicap End
// Handicap Started

if($limit == "0") {
	$sqlnow = "SELECT * FROM `tbl_hist_results` WHERE `handicap`='0.00'";
} else { 
	$sqlnow = "SELECT * FROM `tbl_hist_results` WHERE `handicap`='0.00' LIMIT $limit";
}
$hadnires = $mysqli->query($sqlnow);
if ($hadnires->num_rows > 0) {
	// output data of each row
	while ($handi = $hadnires->fetch_object()) {
		$racedet = race_details($handi->race_id);
		$distance = round($racedet->race_distance / 100);
		$distance = $distance * 100;
		$newhandicap = newvalue($handi->length, $racedet->race_distance, $distance, $handi->horse_position, number_format($handi->race_time, 2));
		$newhandi = number_format($newhandicap, 3);

		$id = $handi->hist_id;
		// $newhandicap = newvalue($row["length"], $row["original_distance"], $row["distance"], $row["pos"], number_format($row["minimumtime"],2));
		$updatehptime = "UPDATE `tbl_hist_results` SET `handicap`=$newhandi WHERE hist_id = $id";
		echo $updatehptime . "<br>";
		echo "-------------------" . "<br>";
		$result2 = $mysqli->query($updatehptime);
	}
} else {
	echo '0 results. <a href="./updatehptime.php">Click Here</a> to go back';
}

//Handicap End
//Reset Rank
//Query to update the rank avg
if($limit == "0") {
	$sql7 = "Update `tbl_hist_results` SET `rank`='0.00' WHERE `rank`!='0.00'"; //Resetting Rank
} else { 
	$sql7 = "Update `tbl_hist_results` SET `rank`='0.00' WHERE `rank`!='0.00' LIMIT $limit";
}
$mysqli->query("UPDATE `tbl_races` SET `rank_status`='0' WHERE `rank_status`!='0'");
$result7 = $mysqli->query($sql7);

//Rank Started
if($limit == "0") {
	$sql2 = "SELECT `race_id` FROM `tbl_races` WHERE `rank_status`='0' ORDER by `race_id` ASC";
} else { 
	$sql2 = "SELECT `race_id` FROM `tbl_races` WHERE `rank_status`='0' ORDER by `race_id` ASC LIMIT $limit";
}
$raceres = $mysqli->query($sql2);
if ($raceres->num_rows > 0) {
	// output data of each row
	while ($rowrc = $raceres->fetch_object()) {
		$countofhorses = get_rows("`tbl_temp_hraces` WHERE `race_id`='$rowrc->race_id' AND `horse_fxodds`!='0'");
		echo 'All below results are for Race ID: ' . $rowrc->race_id . '<br /><br />';
		$get_distances = $mysqli->query("SELECT DISTINCT CAST(race_distance AS UNSIGNED) AS racedist FROM tbl_hist_results WHERE `race_id`='$rowrc->race_id' ORDER by racedist ASC");
		$updaterankavg = "";
		while($dists = $get_distances->fetch_object()) {
			$handitotal = get_handisum($rowrc->race_id, $dists->racedist);
			//echo $dists->racedist . ' ( ' . $handitotal . ' )<br />';
			$numbersarray = get_array_of_handicap($rowrc->race_id, $dists->racedist);
			$cnt = count($numbersarray);
			
			$get_unique = $mysqli->query("SELECT DISTINCT `horse_id` FROM `tbl_hist_results` WHERE `race_id`='$rowrc->race_id' AND `race_distance`='$dists->racedist'");
			$i = 1;				
			while($ghorse = $get_unique->fetch_object()) {
				$checkodds = $mysqli->query("SELECT * FROM `tbl_temp_hraces` WHERE `race_id`='$rowrc->race_id' AND `horse_id`='$ghorse->horse_id'");
				$goddds = $checkodds->fetch_object();
				if($goddds->horse_fxodds != "0") {
					$get_hist = $mysqli->query("SELECT MIN(handicap) as minihandi FROM `tbl_hist_results` WHERE `race_id`='$rowrc->race_id' AND `race_distance`='$dists->racedist' AND `horse_id`='$ghorse->horse_id'");
					while($shandi = $get_hist->fetch_object()) {
						if($countofhorses > 0) {
							$per = ($cnt / $countofhorses) * 100;
							if ($per > 40) {
								$genrank = generate_rank($shandi->minihandi, $numbersarray);
								$updaterankavg = "UPDATE `tbl_hist_results` SET `rank`='$genrank' WHERE `race_id`='$rowrc->race_id' AND `race_distance`= '$dists->racedist' AND `horse_id`='$ghorse->horse_id'";
								if($mysqli->query($updaterankavg)) {
									echo $updaterankavg . "<br>";
									echo "-------------------" . "<br>";
								}
							}
						}
					}
					++$i;
				}
			}
		}
		if($updaterankavg) { 
			$mysqli->query("UPDATE `tbl_races` SET `rank_status`='1' WHERE `race_id`='$rowrc->race_id'");
		}
	}
}
else { echo '0 Results. <a href="./updatehptime.php">Click Here</a> to go back'; }

//Reset Rating
if($limit == 0) {
	$sql6 = "Update `tbl_hist_results` SET `rating`='0'"; //Resetting Rating
} else {
	$sql6 = "Update `tbl_hist_results` SET `rating`='0' LIMIT $limit";
}
$result6 = $mysqli->query($sql6);

// Do Sectional Average

if($limit == "0") {
	$sql2 = "SELECT `race_id` FROM `tbl_races` WHERE `sec_status`='0' OR `sec_status`='' ORDER by `race_id` ASC";
} else { 
	$sql2 = "SELECT `race_id` FROM `tbl_races` WHERE `sec_status`='0' OR `sec_status`='' ORDER by `race_id` ASC LIMIT $limit";
}
$raceres = $mysqli->query($sql2);
if ($raceres->num_rows > 0) {
	// output data of each row
	while ($rowrc = $raceres->fetch_object()) {
		$countofhorses = get_rows("`tbl_temp_hraces` WHERE `race_id`='$rowrc->race_id' AND `horse_fxodds`!='0'");
		echo 'All below results are for Race ID: ' . $rowrc->race_id . '<br /><br />';
		$get_distances = $mysqli->query("SELECT DISTINCT CAST(race_distance AS UNSIGNED) AS racedist FROM tbl_hist_results WHERE `race_id`='$rowrc->race_id' ORDER by racedist ASC");
		$updateavgsec = "";
		while($dists = $get_distances->fetch_object()) {
			$secttotal = get_sectionalsum($rowrc->race_id, $dists->racedist);
			//echo $dists->racedist . ' ( ' . $handitotal . ' )<br />';
			$numbersarray = get_array_of_avgsec($rowrc->race_id, $dists->racedist);
			$cnt = count($numbersarray);
			
			$get_unique = $mysqli->query("SELECT DISTINCT `horse_id` FROM `tbl_hist_results` WHERE `race_id`='$rowrc->race_id' AND `race_distance`='$dists->racedist'");
			$i = 1;
			while($ghorse = $get_unique->fetch_object()) {
				$checkodds = $mysqli->query("SELECT * FROM `tbl_temp_hraces` WHERE `race_id`='$rowrc->race_id' AND `horse_id`='$ghorse->horse_id'");
				$goddds = $checkodds->fetch_object();
				if($goddds->horse_fxodds != "0") {
					$get_hist = $mysqli->query("SELECT MAX(avgsec) AS secavg FROM `tbl_hist_results` WHERE `race_id`='$rowrc->race_id' AND `race_distance`='$dists->racedist' AND `horse_id`='$ghorse->horse_id'");
					while($ssect = $get_hist->fetch_object()) {
						$per = ($cnt / $countofhorses) * 100;
						if ($per > 40) {
							$genavgsec = generate_avgsectional($ssect->secavg, $numbersarray);
							$updateavgsec = "UPDATE `tbl_hist_results` SET `avgsectional`='$genavgsec' WHERE `race_id`='$rowrc->race_id' AND `race_distance`= '$dists->racedist' AND `horse_id`='$ghorse->horse_id'";
							if($mysqli->query($updateavgsec)) {
								echo $updateavgsec . "<br>";
								echo "-------------------" . "<br>";
							}
						}
					}
					++$i;
				}
			}
		}
		if($updateavgsec) {
			$mysqli->query("UPDATE `tbl_races` SET `sec_status`='1' WHERE `race_id`='$rowrc->race_id'");
		}
	}
	echo '<h3>Your Action has been completed for Sectional. <a href="./updatehptime.php">Click Here</a> to go back</h3>';
}
else { echo '0 Results. <a href="./updatehptime.php">Click Here</a> to go back'; }

//Sectional Average End
// Do Rating

if($limit == "0") {
	$sqldatarat = "SELECT * FROM `tbl_hist_results` WHERE `rating`='0'";
}
else {
	$sqldatarat = "SELECT * FROM `tbl_hist_results` WHERE `rating`='0' LIMIT $limit";
}
$datarat = $mysqli->query($sqldatarat);
if($datarat->num_rows > 0) {
	while($ratin = $datarat->fetch_object()) {
		if($ratin->avgsectional != "0" || $ratin->rank != "0") {
			echo $ratin->avgsectional;
			$ratepos = $ratin->avgsectional + $ratin->rank;
			$updaterankavg1 = $mysqli->query("UPDATE `tbl_hist_results` SET `rating`='$ratepos' WHERE `hist_id`= '$ratin->hist_id'");
			echo 'Rating Done for: ' . $ratin->hist_id . '<br />';
		}
	}
	echo 'Your Action has been completed. <a href="./updatehptime.php">Click Here</a> to go back';
}
else {
	echo '0 Results. <a href="./updatehptime.php">Click Here</a> to go back';
}
//Rating End
	
function get_handisum($race_id, $race_dist) {
	global $mysqli;
	$get_hists = $mysqli->query("SELECT MIN(handicap) AS minhandi FROM `tbl_hist_results` WHERE `race_id`='$race_id' AND `race_distance`='$race_dist' GROUP by horse_id");
	$totalhandi = 0;
	while($gethand = $get_hists->fetch_object()) {
		$totalhandi += $gethand->minhandi;
	}
	return $totalhandi;
}

function get_array_of_handicap($raceid, $racedis) {
	global $mysqli;
	$get_array = $mysqli->query("SELECT DISTINCT `horse_id` FROM `tbl_hist_results` WHERE `race_id`='$raceid' AND `race_distance`='$racedis'");
	$arr = array();
	while($arhorse = $get_array->fetch_object()) {
		$get_histar = $mysqli->query("SELECT MIN(handicap) as minihandi FROM `tbl_hist_results` WHERE `race_id`='$raceid' AND `race_distance`='$racedis' AND `horse_id`='$arhorse->horse_id'");
		while($ahandi = $get_histar->fetch_object()) {
			$arr[] = $ahandi->minihandi;
		}
	}
	return $arr;
}

function get_sectionalsum($race_id, $race_dist) {
	global $mysqli;
	$get_histsec = $mysqli->query("SELECT MAX(avgsec) AS secavg FROM `tbl_hist_results` WHERE `race_id`='$race_id' AND `race_distance`='$race_dist' GROUP by horse_id");
	$totalsec = 0;
	while($getsect = $get_histsec->fetch_object()) {
		$totalsec += $getsect->secavg;
	}
	return $totalsec;
}

function get_array_of_avgsec($raceid, $racedis) {
	global $mysqli;
	$get_array = $mysqli->query("SELECT DISTINCT `horse_id` FROM `tbl_hist_results` WHERE `race_id`='$raceid' AND `race_distance`='$racedis'");
	$arr = array();
	while($arhorse = $get_array->fetch_object()) {
		$get_histar = $mysqli->query("SELECT MAX(avgsec) AS secavg FROM `tbl_hist_results` WHERE `race_id`='$raceid' AND `race_distance`='$racedis' AND `horse_id`='$arhorse->horse_id'");
		while($asec = $get_histar->fetch_object()) {
			$arr[] = $asec->secavg;
		}
	}
	return $arr;
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
// adjust the distance for each race, to be able to compare the handicap time
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

function generate_rank($value, $array, $order = 0) {
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

function generate_avgsectional($value, $array, $order = 0) {
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

function rating_system_new($rankavg, $avgsectional) {
    //	, $oldweight, $newweight
    //$rating = $rankavg;
    //$rating = $rankavg+$avgsectional;
    //$weight = weight_points($oldweight, $newweight);
    //$handicappoints = $handicap;
    $rating = $rankavg + $avgsectional;
    return $rating;
}
?>
