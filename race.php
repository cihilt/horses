<?php
include('includes/config.php');
include('includes/functions.php');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$race_id = $_REQUEST['race'];
$gracedet = race_details($race_id);
$gmeetdet = meeting_details($gracedet->meeting_id);
// var_dump($gracedet,$gmeetdet); exit();
?>
    <!DOCTYPE html>
    <html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Horses Data</title>
    <link rel="stylesheet" id="font-awesome-style-css" href="http://phpflow.com/code/css/bootstrap3.min.css" type="text/css" media="all">
    <script type="text/javascript" charset="utf8" src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.11.1.min.js"></script>

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.9/css/jquery.dataTables.min.css"/>

    <script type="text/javascript" src="https://cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/1.0.3/css/dataTables.responsive.css">
    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/responsive/1.0.3/js/dataTables.responsive.js"></script>

    <link rel="stylesheet" id="main-css" href="assests/main.css" type="text/css" media="all">
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

    <ul>
        <li><a href="meeting.php" >Home</a></li>
        <li><a href="horses.php">Horses</a></li>
        <li><a href="meeting.php">Meetings</a></li>
        <li><a href="races.php?meeting=1" >Races</a></li>
        <li><a class="active"><?=$gmeetdet->meeting_name?></a></li>

        <?php
        $races = $mysqli->query("SELECT * FROM `tbl_races` WHERE `meeting_id`=" . $gmeetdet->meeting_id . " ORDER by race_order ASC");
        if ($races->num_rows > 0) {
            while ($racdet = $races->fetch_object()) {
                ?>
                <li><a href="race.php?race=<?=$racdet->race_id?>&avg=1" <?php if($racdet->race_id == $_REQUEST['race']) { ?>class="active"<?php } ?>><?=$racdet->race_order?></a></li>
                <?php
            }
        }
        ?>
        <?php
        if(isset($_GET['avg'])==1){
            ?>
            <li class="pull-right"><a href="horses.php?raceid=<?php echo $race_id; ?>&meetingid=<?php echo $gmeetdet->meeting_id; ?>&rd=<?php echo isset($_REQUEST['rd']) ? $_REQUEST['rd'] : 0; ?>&avg=0" class="dropdown-item active" >Show Horses</a></li>
            <li class="pull-right"><a href="race.php?race=<?php echo $_REQUEST['race'] ?>" class="dropdown-item active">Show All</a></li>
            <?php
        }else{
            ?>
            <li class="pull-right"><a href="horses.php?raceid=<?php echo $race_id; ?>&meetingid=<?php echo $gmeetdet->meeting_id; ?>&rd=<?php echo isset($_REQUEST['rd']) ? $_REQUEST['rd'] : 0; ?>&avg=0" class="dropdown-item active" >Show Horses</a></li>
            <li class="pull-right"><a href="race.php?race=<?php echo $_REQUEST['race'] ?>&avg=1" class="dropdown-item active" >Show Average</a></li>
            <?php
        }
        ?>
    </ul>


    <div class="container-fluid">
        <h1>Horses Rating- Distance <?php echo $gracedet->round_distance; ?> </h1>
        <table id="employee_grid" class="display" width="100%" cellspacing="0">
            <thead>
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
                    $sqlnow = $mysqli->query("SELECT *  FROM `tbl_hist_results` WHERE `race_id`='".$race_id."' AND `horse_id`='$ghorse->horse_id'");
                    if($sqlnow->num_rows > 0) {
                        while($resnow = $sqlnow->fetch_object()) {
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
////////////////////////////////////////////////////////
?>

    <div class="">
        <h1>Race Results</h1>
        <div class="">
            <table id="employee_grid1" class="display" width="100%" cellspacing="0">
                <thead>
                <tr>
                    <th>Position</th>
                    <th>Horse Name</th>
                    <th>Distance</th>
                    <th>Event</th>
                    <th>Race Name</th>

                </tr>
                </thead>
                <tbody>
                <?php
                $raceres = $mysqli->query("SELECT * FROM `tbl_results` WHERE `race_id`='".$race_id."' ORDER BY position ASC");
                if ($raceres->num_rows > 0) {
                    // output data of each row
                    while ($racres = $raceres->fetch_object()) {
                        $horsedet = horse_details($racres->horse_id);
                        $racedet = race_details($racres->race_id);
                        $meetdet = meeting_details($racedet->meeting_id);

                        echo '<tr>'
                            . '<td>' . $racres->position. '</td>'
                            . '<td>' . $horsedet->horse_name . '</td>'
                            . '<td>' . $racedet->round_distance . '</td>'
                            . '<td>' . $meetdet->meeting_name . '</td>'
                            . '<td><a href="race.php?race='.$race_id.'">' . $racedet->race_title . '</a></td>'
                            . '</tr>';
                    }
                } else {
                    echo "0 results";
                }
                $mysqli->close();
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <script type="text/javascript">
        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                var min = parseInt( $('#min').val(), 10 );
                var max = parseInt( $('#max').val(), 10 );
                var age = parseFloat( data[6] ) || 0; // use data for the age column

                if ( ( isNaN( min ) && isNaN( max ) ) ||
                    ( isNaN( min ) && age <= max ) ||
                    ( min <= age   && isNaN( max ) ) ||
                    ( min <= age   && age <= max ) ){
                    return true;
                }
                return false;
            }
        );
        $(document).ready(function () {
            $('#employee_grid').DataTable({
                "pageLength": 25,
                "order": [[ 7, "desc" ]]
            });
            $('#employee_grid1').DataTable({
                "responsive": true,
            });
        });
    </script>
<?php

function newvalue($length, $distance, $orgdistance, $pos, $time) {
    $modifier = 0;
    //Getting the postion of the horse
    $pos = explode('/', $pos);
    $position = intval($pos[0]);

    //Getting the value of the modifier
    /* if ($distance >= 800 AND $distance <= 999)
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
    } */
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
        } else {
            $newtime = loses_rounded_down($time, $length, $modifier, $remainder);
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


function rating_system($handicap,$section,$oldweight,$newweight){
    $pos = explode('/', $section);

    if(isset($pos[1])){
        $sectiontime = $pos[1];
    }else{
        $sectiontime = 0;
    }

    $weight = weight_points($oldweight, $newweight);
    $handicappoints = 1/$handicap;
    if($sectiontime==0){
        $sectionpoints = 0;
    }else{
        $sectionpoints = (9/$sectiontime)*100;
    }
    $rating = $handicappoints+$sectionpoints+($weight/100);
    return $rating;
}

function weight_points($oldweight,$newweight){

    $weight =  $newweight-$oldweight;

    if($weight>3){
        $wgt = 1.5;
        return $wgt;

    }
    if($weight>2&&$weight<=2.5){
        $wgt = 1;
        return $wgt;

    }
    if($weight>1&&$weight<=1.5){
        $wgt = 0.5;
        return $wgt;
    }
    if($weight>0&&$weight<=0.5){
        $wgt = 1;
        return $wgt;
    }
    if($weight>-0.5&&$weight<=0){
        $wgt = -1.5;
        return $wgt;
    }
    if($weight>-1&&$weight<=-1.5){
        $wgt = -2;
        return $wgt;
    }
    if($weight>-1&&$weight<=-2.5){
        $wgt = -2;
        return $wgt;
    }
    if($weight>-3&&$weight<-2.5){
        $wgt = -3;
        return $wgt;
    }
}
