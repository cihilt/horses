<?php
include('constant.php');
// Check connection
if ($conn->connect_error) {
die("Connection failed: " . $conn->connect_error);
}
session_start();
if (!isset($_SESSION['mname'])) {
    $_SESSION['mname'] = $_REQUEST['mname'];
}
$s1 = "";
$s2 = "";
$raceid = $_REQUEST['raceid'];
$meetingid = $_REQUEST['meetingid'];
$avg = 0;
if(isset($_REQUEST['avg'])){
    $avg = $_REQUEST['avg'];
}

if($avg==1){
    
    $sql = "SELECT * , MIN(data.time) minimumtime,MIN(data.time2) minimumtime2,AVG(rating) AS rat FROM horses 
            LEFT JOIN data ON horses.horse_name = data.name
            LEFT JOIN rankavg ON horses.horse_name = rankavg.name 
            WHERE horses.race_id = $raceid  GROUP BY horse_name ORDER BY avgrank DESC"; //LEFT JOIN data ON horses.horse_name = data.name 
    
    $geting = $conn->query($sql);
    
    $ratin = array();
    while($gnow = $geting->fetch_object()) {
        $ratin[] = number_format($gnow->rat,2);
    }
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

}else{
    $sql = "SELECT * , MIN(data.time) minimumtime,MIN(data.time2) minimumtime2 FROM horses LEFT JOIN data ON horses.horse_name = data.name WHERE horses.race_id =" . $raceid . " GROUP BY id";
}
//var_dump($sql);
$result = $conn->query($sql);

$sql1 = "SELECT *  FROM races WHERE meeting_id =" . $meetingid . " ORDER by race_id";
$result1 = $conn->query($sql1);
//print_r($result1);

$race_id = $raceid;

$sql2 = "SELECT *  FROM results LEFT JOIN races ON races.race_id = results.race_id WHERE results.race_id = " . $race_id;
$result2 = $conn->query($sql2);

$current_file_name = basename($_SERVER['PHP_SELF']);

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Horses Data</title>
<script src=https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js></script>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<link rel="stylesheet" id="main-css" href="main.css" type="text/css" media="all">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.9/css/jquery.dataTables.min.css"/>

<script type="text/javascript" src="https://cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js"></script>

<ul> 
    <li><a href="meeting.php">Home</a></li>
    <li><a href="meeting.php">Meetings</a></li>
    <li><a href="result.php" >Results</a></li>
    <li><a class="active"><?php echo $_SESSION['mname']; ?></a></li>

<?php
    if ($result1->num_rows > 0) {
        // output data of each row
        while ($row = $result1->fetch_assoc()) {
?>
            <li><a href=<?php echo $current_file_name; ?>?raceid=<?php echo $row['race_id'] ?>&meetingid=<?php echo $meetingid; ?>&rd=<?php echo $row['race_distance'] ?>&avg=<?php echo $avg; ?> <?php if ($row['race_id'] == $_REQUEST['raceid']) { ?>class="active" <?php } ?>><?php echo $row['race_number'] ?></a></li>
<?php
        }
    }
?>
<?php 
    if($avg==1){ 
?>
        <li class="pull-right"><a href="horses.php?raceid=<?php echo $_REQUEST['raceid'] ?>&meetingid=<?php echo $meetingid; ?>&rd=<?php echo $_REQUEST['rd'] ?>&avg=0" class="dropdown-item active" >Show Horses</a></li>
        <li class="pull-right"><a href="rating.php?raceid=<?php echo $_REQUEST['raceid'] ?>&meetingid=<?php echo $meetingid; ?>&rd=<?php echo $_REQUEST['rd'] ?>&avg=0" class="dropdown-item active">Show All</a></li>
<?php 
    }else{ 
?>
        <li class="pull-right"><a href="horses.php?raceid=<?php echo $_REQUEST['raceid'] ?>&meetingid=<?php echo $meetingid; ?>&rd=<?php echo $_REQUEST['rd'] ?>&avg=0" class="dropdown-item active" >Show Horses</a></li>
        <li class="pull-right"><a href="rating.php?raceid=<?php echo $_REQUEST['raceid'] ?>&meetingid=<?php echo $meetingid; ?>&rd=<?php echo $_REQUEST['rd'] ?>&avg=1" class="dropdown-item active" >Show Average</a></li>
<?php 
    } 
?>   
</ul>

<div class="container-fluid">
    <h1>Horses Rating- Distance <?php echo $_REQUEST['rd']; ?> </h1>
    <table id="employee_grid" class="display" width="100%" cellspacing="0">
        <thead>
            <tr>
<?php      
                if($avg==1 && $result2->num_rows > 0){
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
                    <th>Original Distance</th>
                    <th>Sectional</th>
                    <th>Minimum Time</th>
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
            if ($result->num_rows > 0) {
                
                // output data of each row
                $cnt = 1;
                $ratenow = array();
                
                while ($row = $result->fetch_assoc()) {
                    //var_dump($row);
                    if($avg==1){
                        $rating = number_format($row["rat"],2);
                        $ratenow[] = $rating;
                    }else{
                        $rating = $row["rating"];
                    }
                    
                    if($avg==1 && $result2->num_rows > 0){
                        
                        $avgrank =  number_format($row['avgrank'],2);
                        $odds = str_replace("$","" , $row["horse_fixed_odds"]);
                        
                        if($cnt<3){
                            // $pos =  explode('/', );
                            $position = $row['pos'];
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
                        //$pos =  explode('/', $row['pos']);
                        //$position =  intval($pos[0]);
                        $position = $row['pos'];
                        //var_dump($position);
                        
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
                                        $pos =  explode('/', $row['pos']);
                                        $position =  intval($pos[0]);
                                        
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
                        } else{
                            $profitloss ="";
                        }
                        echo "<tr>"
                        . "<td>" . $row["horse_number"] . "</td>"
                        . "<td>" . $row["horse_name"] . "</td>"
                        . "<td>" . $row["horse_latest_results"] . "</td>"
                        . "<td>" . $row["horse_fixed_odds"] . "</td>"
                        . "<td>" . $row["weight"] . "</td>"
                        . "<td>" . $row['horse_weight'] . "</td>"
                        . "<td>" .$rating. "</td>"
                        . "<td>" . $profitloss . "</td>"
                        . "<td>" . $avgrank . "</td>"
                        . "<td>" . $profit. "</td>"
                        . "</tr>";
                        //$row["horse_id"];
                    } else{
                        echo "<tr>"
                        . "<td>" . $row["horse_number"] . "</td>"
                        . "<td>" . $row["horse_name"] . "</td>"
                        
                        . "<td>" . $row["horse_latest_results"] . "</td>"
                        . "<td>" . $row["horse_fixed_odds"] . "</td>"
                        . "<td>" . $row["original_distance"] . "</td>"
                        . "<td>" . $row['distance'] . "</td>"
                        
                        . "<td>" . $row["sectional"] . "</td>"
                        . "<td>" . $row["minimumtime"] . "</td>"
                        . "<td>" . number_format($row['handicap'], 3) . "</td>"
                        
                        . "<td>" .$rating. "</td>"
                        . "<td>" .$row['rank']. "</td>"   
                        . "</tr>";
                    }
                    $cnt++;
                }
            } else {
                echo "0 results";
            }
?>
        </tbody>
        <tfoot>
            <tr>
<?php      
                if($avg==1 && $result2->num_rows > 0){
                    
?>
                    <th>No</th>
                    <th>Name</th>
                    
                    <th>Form</th>
                    <th>Odds</th>
                    
                    <th>Minimum Time</th>
                    <th>Handicap</th>
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
                    <th>Original Distance</th>
                    <th>Sectional</th>
                    <th>Minimum Time</th>
                    <th>Handicap</th>
                    <th>Rating</th>
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
                if ($result2->num_rows > 0) {
                // output data of each row
                while ($row = $result2->fetch_assoc()) {
                echo "<tr>"
                . "<td>" . $row["position"] . "</td>"
                . "<td>" . $row["horse"] . "</td>"
                . "<td>" . $row["distance"] . "</td>"
                . "<td>" . $row["event"] . "</td>"
                . "<td><a href='horses.php?rd=" . $row['distance'] . "&raceid=" . $row['race_id'] . "&meetingid=" . $row['meeting_id'] . "&mname=" . $row['race_title'] . "'>" . $row["race_title"] . "</a></td>"
                . "</tr>";
                }
                } else {
                echo "0 results";
                }
                $conn->close();
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
?>
