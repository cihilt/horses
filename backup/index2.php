<?php
//var_dump($_REQUEST);
include('includes/config.php');
include('includes/functions.php');
// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
//get the timer and secpoint
$sql_formulas = "SELECT `secpoint`,`timer`,`position_percentage` FROM `tbl_formulas` WHERE id=1";
  $result_formulas = $mysqli->query($sql_formulas);
  $res = mysqli_fetch_all($result_formulas,MYSQLI_ASSOC);

  $secpoint = $res[0]['secpoint'];
  $timer = $res[0]['timer'];
  $position_percentage = $res[0]['position_percentage'];

if(isset($_POST['singlebutton'])){
    $secpoint = $_POST['secpoint'];
  $timer = $_POST['timer'];
  $position_percentage = $_POST['positionpercentage'];
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
  $sql = "UPDATE `tbl_formulas` SET `secpoint`=".$secpoint.",`timer`=".$timer.",`position_percentage`=".$position_percentage." WHERE id = 1";
$result = $mysqli->query($sql);
if($result){
  echo "Values Updated successfully";
}
}
$race_id = $_REQUEST['race_id'];
$race_id_var_get = $_REQUEST['race_id'];
$distance = $_REQUEST['distance'];
$gracedet = race_details($race_id);
$gmeetdet = meeting_details($gracedet->meeting_id);

?>
<form class="form-horizontal" method="post">
<fieldset>

<!-- Form Name -->
<legend>Formula Values</legend>

<!-- Text input-->
<div class="form-group">
  <label class="col-md-4 control-label" for="secpoint">secpoint</label>  
  <div class="col-md-4">
  <input id="secpoint" name="secpoint" type="text" placeholder="" class="form-control input-md" required="" value="<?php echo $secpoint; ?>">
    
  </div>
</div>

<!-- Text input-->
<div class="form-group">
  <label class="col-md-4 control-label" for="positionpercentage">Position  Percentage</label>  
  <div class="col-md-4">
  <input id="positionpercentage" name="positionpercentage" type="text" placeholder="" class="form-control input-md" required="" value="<?php echo $position_percentage; ?>">
    
  </div>
</div>

<!-- Text input-->
<div class="form-group">
  <label class="col-md-4 control-label" for="timer">timer</label>  
  <div class="col-md-4">
  <input id="timer" name="timer" type="text" placeholder="" class="form-control input-md" required="" value="<?php echo $timer; ?>">
    
  </div>
</div>

<!-- Button -->
<div class="form-group">
  <label class="col-md-4 control-label" for="singlebutton"></label>
  <div class="col-md-4">
    <button id="singlebutton" name="singlebutton" class="btn btn-primary" value="submit">Update Values</button>
  </div>
</div>

</fieldset>
</form>
 <div class="container-fluid">
        <h1>Horses Rating- Distance <?php echo $gracedet->round_distance; ?> </h1>
        <table id="employee_grid" class="display" width="100%" cellspacing="0">
            <thead>
            <tr>
                
             
                    <th>No</th>
                    <th>Name</th>
                    <th>Form</th>
                    <th>Odds</th>
                    <th>Distance</th>
                    <th>Sectional</th>
                    <th>Minimum Time</th>
			<th>Race Pos</th>
                	<th>Orig Weight</th>
			<th>Current Weight</th>	
		    <th>Handicap</th>
                    <th>Rating</th>
                    <th>Rank</th>
                      <th>Formula</th>
               
            </tr>
            </thead>
            <tbody>
            <?php
            $sqlfavg = "SELECT *, AVG(rating) rat,AVG(rank) as avgrank FROM `tbl_hist_results` WHERE `race_id`='".$race_id."' GROUP BY horse_id";
            $max_1 = $max_2 = 0;
            $geting = $mysqli->query($sqlfavg);
            $ratin = array();
            while($gnow = $geting->fetch_object()) {
                $ratin[] = number_format($gnow->rat,2);
            }
            if(count($ratin) > '0') {
                $ismaxrat = max($ratin);

                $max_1 = $max_2 = -1;
                $maxused = 0;

                for($i=0; $i<count($ratin); $i++) {
                    if($ratin[$i] > $max_1) {
                        $max_2 = $max_1;
                        $max_1 = $ratin[$i];
                    } else if($ratin[$i] > $max_2) {
                        $max_2 = $ratin[$i];
                    }
                }
            }

            echo $max_1." == ".$max_2."<br>";

            $getrnum = $mysqli->query("SELECT * FROM `tbl_temp_hraces` WHERE `race_id`='$race_id'");
            $temp = [];
            while($ghorse = $getrnum->fetch_object()) {
                $sqlfavg = "SELECT *, AVG(rating) rat,AVG(rank) as avgrank FROM `tbl_hist_results` WHERE `race_id`='".$race_id."' AND `horse_id`='$ghorse->horse_id' GROUP BY horse_id";
                $sqlavg = $mysqli->query($sqlfavg);

                while($resavg = $sqlavg->fetch_assoc()) {
                    $temp[] = $resavg;
                }

            }

            $getrnum = $mysqli->query("SELECT * FROM `tbl_temp_hraces` WHERE `race_id`='$race_id'");
            function method1($a,$b)
            {
                return ($a["avgrank"] <= $b["avgrank"]) ? -1 : 1;
            }
            usort($temp, "method1");

            $temp = array_reverse($temp);
            $table = array_slice($temp, 0, 2);
            $top_ids = [];
            foreach ($table as $arr){
                $top_ids[] = $arr['horse_id'];
            }
            while($ghorse = $getrnum->fetch_object()) {
                $horsedet = horse_details($ghorse->horse_id);
                $aracedet = race_details($ghorse->race_id);

// This if condition shows from the homepage, without entering average or showing all

                if(isset($_GET['avg']) == "") {
                    $sqlnow = $mysqli->query("SELECT *  FROM `tbl_hist_results` WHERE `race_id`='".$race_id."' AND `horse_id`='$ghorse->horse_id' AND `race_distance`='$distance'");
                    if($sqlnow->num_rows > 0) {
                        while($resnow = $sqlnow->fetch_object()) {
                            $ratingcal = rating_system(number_format($resnow->handicap, 3),$resnow->sectional , $resnow->horse_weight,$ghorse->horse_weight,$secpoint);
                            
                            echo "<tr>"
                                . "<td>" . $ghorse->horse_num . "</td>"
                                . "<td>" . $horsedet->horse_name . "</td>"
                                . "<td>" . $horsedet->horse_latest_results . "</td>"
                                . "<td>" . $resnow->horse_fixed_odds . "</td>"
                                . "<td>" . $resnow->race_distance . "</td>"
                                . "<td>" . $resnow->sectional . "</td>"
                                . "<td>" . $resnow->race_time . "</td>"
				. "<td>" . $resnow->horse_position . "</td>"
                                . "<td>" . $resnow->horse_weight . "</td>"
				. "<td>" . $ghorse->horse_weight . "</td>"
				. "<td>" . number_format($resnow->handicap, 3) . "</td>"
                                . "<td>" . $resnow->rating . "</td>"
                                . "<td>" . $resnow->rank . "</td>"
                                     . "<td>".$ratingcal."</td>"
                                . "</tr>";
                        }
                    }
                    else {
                        echo "<tr>"
                            . "<td>" . $ghorse->horse_num . "</td>"
                            . "<td>" . $horsedet->horse_name . "</td>"
                            . "<td>" . $horsedet->horse_latest_results . "</td>"
                            . "<td>" . $ghorse->horse_fxodds . "</td>"
                            . "<td></td>"
                            . "<td></td>"
                            . "<td></td>"
                            . "<td>0.00</td>"
                            . "<td>0.00</td>"
                            . "<td></td>"
                            . "<td></td>"
                            . "<td></td>"
                            . "<td></td>"
                            . "</tr>";
                    }
                }
                else {
                    $sqlfavg = "SELECT *, AVG(rating) rat,AVG(rank) as avgrank FROM `tbl_hist_results` WHERE `race_id`='".$race_id."' AND `horse_id`='$ghorse->horse_id' GROUP BY horse_id";
                    //$sqlfavg = "SELECT *, AVG(rating) rat,AVG(rank) as avgrank FROM `tbl_hist_results` WHERE `race_id`='".$race_id."' GROUP BY horse_id";

                    $geting = $mysqli->query($sqlfavg);
                    $ratin = array();
                    while($gnow = $geting->fetch_object()) {
                        $ratin[] = number_format($gnow->rat,2);
                    }




                    /*
                    if(count($ratin) > '0') {
                        $ismaxrat = max($ratin);

                        $max_1 = $max_2 = -1;
                        $maxused = 0;

                        for($i=0; $i<count($ratin); $i++) {
                            if($ratin[$i] > $max_1) {
                                $max_2 = $max_1;
                                $max_1 = $ratin[$i];
                            } else if($ratin[$i] > $max_2) {
                                $max_2 = $ratin[$i];
                            }
                        }
                    }
                    */

                    //here from
                    $cnt = 1;
                    $ratenow = array();

                    $sqlavg = $mysqli->query($sqlfavg);
                    if($sqlavg->num_rows > 0) {
                        while($resavg = $sqlavg->fetch_object()) {
                            $rating = number_format($resavg->rat,2);
                            $ratenow[] = $rating;
                            $avgrank =  number_format($resavg->avgrank,2);
                            $odds = str_replace("$","" , $resavg->horse_fixed_odds);

                            $posres = $mysqli->query("SELECT position FROM `tbl_results` WHERE `race_id`='".$race_id."' AND `horse_id`='$ghorse->horse_id' LIMIT 1");
                            while($prow = $posres->fetch_object()) {
                                $position = $prow->position;
                            }

                            if($cnt<3){
                                //$position = $resavg->horse_position;
                                //var_dump($position);

                                if(!empty($position)){
                                    if($position<2){
                                        $profit = 10*$odds-10;
                                    }else{
                                        $profit = -10;
                                    }
                                }else{
                                    $profit = "";
                                }
                            } else{
                                $profit = "";
                            }

                            $profitloss = "";
                            //$position = $resavg->horse_position;

                            if(!empty($position)){
                                if($rating && $position > 2) {
                                    if($rating > 0) {
                                        if($rating == $max_1 || $rating == $max_2){
                                            $profitloss = 10*0-10;
                                        } else {
                                            $profitloss = "";
                                        }
                                    }
                                }
                                else {
                                    if($rating > 0) {
                                        if($rating == $max_1 || $rating == $max_2) {
                                            //  $pos =  explode('/', $resavg->horse_position);
                                            //  $position =  intval($pos[0]);

                                            if($position != 1) {
                                                $profitloss = 10*0-10;
                                            } else {
                                                $profitloss = 10*$odds-10;
                                            }
                                        } else {
                                            $profitloss = "";
                                        }
                                    }
                                }
                            } else {
                                $profitloss ="";
                            }

                            echo "<tr>"
                                . "<td>" . $ghorse->horse_num . "</td>"
                                . "<td>" . $horsedet->horse_name . "</td>"
                                . "<td>" . $horsedet->horse_latest_results . "</td>"
                                . "<td>" . $resavg->horse_fixed_odds . "</td>"
                                . "<td>" . $resavg->horse_weight . "</td>"
                                . "<td>" . $resavg->horse_weight . "</td>"
                                . "<td>" . $rating . "</td>"
                                . "<td>" . $profitloss . "</td>"
                                . "<td>" . $avgrank . "</td>"
                                . "<td>" . ((!in_array($horsedet->horse_id, $top_ids)) ? null : $profit). "</td>" //  && $profit < 0
                                . "</tr>";
                            ++$cnt;
                        }
                    }
                    else {
                        echo "<tr>"
                            . "<td>" . $ghorse->horse_num . "</td>"
                            . "<td>" . $horsedet->horse_name . "</td>"
                            . "<td>" . $horsedet->horse_latest_results . "</td>"
                            . "<td>" . $ghorse->horse_fxodds . "</td>"
                            . "<td></td>"
                            . "<td></td>"
                            . "<td>0.00</td>"
                            . "<td></td>"
                            . "<td></td>"
                            . "<td></td>"
                            . "</tr>";
                    }
                }
            }
            ?>
            </tbody>
            <tfoot>
            <tr>
                <?php
                if(isset($_GET['avg'])==1){
                    ?>
                    <th>No</th>
                    <th>Name</th>
                    <th>Form</th>
                    <th>Odds</th>
                    <th>Weight</th>
                    <th>Current Weight</th>
                    <th>Rating</th>
                    <th>Profit & Loss</th>
                    <th>AVG Rank</th>
                    <th>Profit</th>
                    <?php
                }else{
                    ?>
                    <th>No</th>
                    <th>Name</th>
                    <th>Form</th>
                    <th>Odds</th>
                    <th>Distance</th>
                    <th>Sectional</th>
                    <th>Minimum Time</th>
			<th>Race Pos</th>
                	<th>Orig Weight</th>
			<th>Current Weight</th>	
		    <th>Handicap</th>
                    <th>Rating</th>
                    <th>Rank</th>
                    <?php
                }
                ?>
            </tr>
            </tfoot>
        </table>
    </div>

<?php

    $race_id_var     = '';
    $race_id_var_int = isset($_POST['race_id']) && $_POST['race_id'] ? $_POST['race_id'] : 0;
    $distance = $_POST['distance'];
    if ($race_id_var_int) {
        $race_id_var = '&race_id=' . $_POST['race_id'];
    }
    $sql_races = "SELECT `race_id` FROM `tbl_races` WHERE 1=1 ORDER by `race_id` ASC";
	$result_races = $mysqli->query($sql_races);
    echo '<form method="post" id="race_form">';
    echo '<select name="race_id">';
    echo '<option value="">Select race_id</option>';
	if ($result_races->num_rows > 0) {
        while($result_race = $result_races->fetch_object()) {
            echo '<option value="' . $result_race->race_id . '"' . ($race_id_var_int && $race_id_var_int ==$result_race->race_id ? ' selected' : '') . '>' . $result_race->race_id . '</option>';
        }
    }
    
//    echo '<a href="" style="color: black;">Select RaceID</a>';
    echo '</select>';
    echo '<input type="text" name="distance" value="'.$distance.'"><input type="submit" value="getdetails">';
    echo '</form>';

    
     if ($race_id_var_get) {
            $sql2 = "SELECT `race_id` FROM `tbl_races` WHERE `race_id`='$race_id_var_get' ORDER by `race_id` ASC";
        } 
		$raceres = $mysqli->query($sql2);
		if ($raceres->num_rows > 0) {
			// output data of each row
			while ($rowrc = $raceres->fetch_object()) {
				$countofhorses = get_rows("`tbl_temp_hraces` WHERE `race_id`='$rowrc->race_id' AND `horse_fxodds`!='0'");
				echo 'All below results are for Race ID: ' . $rowrc->race_id . '<br /><br />';
				$get_distances = $mysqli->query("SELECT DISTINCT CAST(race_distance AS UNSIGNED) AS racedist FROM tbl_hist_results WHERE `race_id`='$rowrc->race_id' AND `race_distance`='$distance' ORDER by racedist ASC");
				$updaterankavg = "";
                                
				while($dists = $get_distances->fetch_object()) {
                    // echo '<b>$dists->racedist</b>: '.$dists->racedist.'<br>';
					$handitotal = get_handisum($rowrc->race_id, $dists->racedist);
					//echo $dists->racedist . ' ( ' . $handitotal . ' )<br />';
					$numbersarray = get_array_of_handicap($rowrc->race_id, $dists->racedist);
					$cnt = count($numbersarray);
					
					$get_unique = $mysqli->query("SELECT DISTINCT `horse_id` FROM `tbl_hist_results` WHERE `race_id`='$rowrc->race_id' AND `race_distance`='$dists->racedist'");
                    $i = 1;				
					while($ghorse = $get_unique->fetch_object()) {
						$checkodds = $mysqli->query("SELECT * FROM `tbl_temp_hraces` WHERE `race_id`='$rowrc->race_id' AND `horse_id`='$ghorse->horse_id'");
						$goddds = $checkodds->fetch_object();
						if(isset($goddds->horse_fxodds) && $goddds->horse_fxodds != "0") {
							$get_hist = $mysqli->query("SELECT MIN(handicap) as minihandi FROM `tbl_hist_results` WHERE `race_id`='$rowrc->race_id' AND `race_distance`='$dists->racedist' AND `horse_id`='$ghorse->horse_id'");
							// echo '<b>$get_hist</b>: ' . "SELECT MIN(handicap) as minihandi FROM `tbl_hist_results` WHERE `race_id`='$rowrc->race_id' AND `race_distance`='$dists->racedist' AND `horse_id`='$ghorse->horse_id'".'<br>';
                            while($shandi = $get_hist->fetch_object()) {
                                // echo '<b>$shandi->minihandi</b>: '.$shandi->minihandi.'<br>';
                                // echo '<b>$countofhorses</b>: '.$countofhorses.'<br>';
								if($countofhorses > 0) {
									$per = ($cnt / $countofhorses) * 100;
                                    // echo '<b>$per</b>: '.$per.'<br><br>';
									if ($per > $position_percentage) {
										$genrank = generate_rank($shandi->minihandi, $numbersarray);
                                        // echo '<b>$genrank</b>: '.$genrank.'<br>';
                                         $horsedet = horse_details($ghorse->horse_id);
										//$updaterankavg = "UPDATE `tbl_hist_results` SET `rank`='$genrank' WHERE `race_id`='$rowrc->race_id' AND `race_distance`= '$dists->racedist' AND `horse_id`='$ghorse->horse_id'";
										if($genrank) {
											echo $horsedet->horse_name." Rank:".$genrank . "<br>";
                                            echo "-------------------" . "<br>";
                                            echo $shandi->minihandi."aaaaaaaaaaaaa".$numbersarray[0].":"$numbersarray[1].":"$numbersarray[2].":"$numbersarray[3]"<br>";
										}
									}
								}
							}
							++$i;
						}
					}
				}
				if($updaterankavg) { 
					//$mysqli->query("UPDATE `tbl_races` SET `rank_status`='1' WHERE `race_id`='$rowrc->race_id'");
				}
			}
			
		}
		else { echo '0 Results';}
                
/*
$distance = newvalue(2.0, 1100, 1100, 3/9, 1.53);
//echo $distance;
$distance = newvalue(1.6, 1100, 1100, 3/11, 1.2);


        
        function rank_avg($value, $array, $order = 0) {
// sort  
  if ($order) sort ($array); else rsort($array);
// add item for counting from 1 but 0
  array_unshift($array, $value+1); 
// select all indexes vith the value
  $keys = array_keys($array, $value);
  if (count($keys) == 0) return NULL;
// calculate the rank
  
  return array_sum($keys) / count($keys);
  
}
$val = "1.25,1.37,1.34,1.29,1.24,1.55,1.46,1.31,1.34";
     $val1 =   explode(",",$val);
echo rank_avg(1.37, $val1, 1);

function newvalue($length,$distance,$orgdistance,$pos,$time){
    $modifier = 0;
     //Getting the postion of the horse
$pos =  explode('/', $pos);
    		$position =  intval($pos[0]);
               
          //Getting the value of the modifier      
if ($distance >= 800 AND $distance <= 999)
	    {
	    	$modifier = 1;
	    }
	    elseif ($distance >= 1000 AND $distance <= 1099)
	    {
	    	$modifier = 0.05;
	    }
	    elseif ($distance >= 1100 AND $distance <= 4000)
	    {
	    	$modifier = 0.07;
	    }
            $modifier = 0.03;
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
            } else if($distance>$orgdistance) {
                $newtime = loses_rounded_down($time, $length, $modifier, $remainder);
            }else{
               $newtime =   $time + ($length*$modifier);
            }
        }
        return $newtime;
            
            
}
function get_remainder($distance){

    if ($distance % 10 < 5)
		{
			$distance -= $distance % 10;
                       
		}
		else
		{
			$distance += (10 - ($distance % 10));
                       
		}
	       
		if ($distance % 100 < 50)
		{
			$reminder_distance = $distance % 100;
			
                       
		}
		else
		{
			$reminder_distance = (100 - ($distance % 100));
			
                        
		}
                $reminder = $reminder_distance;
                return $reminder;
}

 //if horse wins   
function win_rounded_up($time,$length,$modifier,$remainder){
  //  echo $remainder;
 echo "win rounded up";
  echo "<pre>";
   echo $time."+(0.0007*".$remainder.")";

    echo "</pre>";
    $newtime =  $time+(0.0007*$remainder);
    return $newtime;
}
 //if horse wins  
function win_rounded_down($time,$length,$modifier,$remainder){
    echo "win rounded down";
      echo "<pre>";
   echo $time."-(0.0007*".$remainder.")";

    echo "</pre>";
        $newtime =  $time-(0.0007*$remainder);
    return $newtime;
    
}
 //if horse loses  
function loses_rounded_up($time,$length,$modifier,$remainder){
    //time+(length*modifier)-(0.0007*$remainder);
   echo "loses rounded up";
   echo "<pre>";
   echo $time."+(".$length."*".$modifier.")+(0.0007*".$remainder.")";

    echo "</pre>";
        $newtime =  $time+($length*$modifier)+(0.0007*$remainder);
    return $newtime;
}
 //if horse loses  
function loses_rounded_down($time,$length,$modifier,$remainder){
 echo "loses rounded down";
   echo "<pre>";
   echo $time."+(".$length."*".$modifier.")-(0.0007*".$remainder.")";

    echo "</pre>";
     $newtime =  $time+($length*$modifier)-(0.0007*$remainder);
    return $newtime;
}*/
    
    
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

    $newtime = $time + ($timer * $remainder);
    return $newtime;
}

//if horse wins  
function win_rounded_down($time, $length, $modifier, $remainder) {

    $newtime = $time - ($timer * $remainder);
    return $newtime;
}

//if horse loses  
function loses_rounded_up($time, $length, $modifier, $remainder) {
    //time+(length*modifier)-(0.0007*$remainder);

    $newtime = $time + ($length * $modifier) + ($timer* $remainder);
    return $newtime;
}

//if horse loses  
function loses_rounded_down($time, $length, $modifier, $remainder) {

    $newtime = $time + ($length * $modifier) - ($timer * $remainder);
    return $newtime;
}

function rating_system($handicap, $section, $oldweight, $newweight,$secpoint) {
    
    if(strlen($section)>0){
    $pos = explode('/', $section);
    }else{
        $sectiontime = 0;
    }
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
        $sectionpoints = ($secpoint / $sectiontime) * 100;
    }
    $sectionpoints = number_format($sectionpoints,2);
    $rating = $handicappoints + $sectionpoints + ($weight / 100);
    return $handicappoints."+".$sectionpoints."+(".$weight."/100) = ".$rating;
    //return $rating;
}

function weight_points($oldweight, $newweight) {

    $weight = $newweight - $oldweight;
    //echo $weight."<br>";
    if ($weight > 3) {
        $wgt = 1.5;
        return $wgt;
    }
    if ($weight >= 2 && $weight <= 2.5) {
        $wgt = 1;
        return $wgt;
    }
    if ($weight >= 1 && $weight <= 1.5) {
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
?>
