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
$raceid = $_REQUEST['raceid'];
$avg = 0;
if (isset($_REQUEST['avg'])) {
    $avg = $_REQUEST['avg'];
}


//$sql = "SELECT *, MIN(time) minimumtime,AVG(time) avgtime FROM data WHERE `name` IN (";
//$sql = "SELECT * , MIN(data.time) minimumtime FROM horses LEFT JOIN data ON horses.horse_name = data.name WHERE horses.race_id =" . $raceid;
$sql = "SELECT *  FROM `minihand` LEFT JOIN rank_avg_data ON rank_avg_data.race_id = minihand.race_id AND rank_avg_data.distance = minihand.distance WHERE minihand.race_id =" . $raceid;


$result = $conn->query($sql);


$meetingid = $_REQUEST['meetingid'];
//$sql = "SELECT *, MIN(time) minimumtime,AVG(time) avgtime FROM data WHERE `name` IN (";
$sql1 = "SELECT *  FROM races WHERE meeting_id =" . $meetingid . " ORDER by race_id";
$result1 = $conn->query($sql1);


$race_id = $raceid;
$sql2 = "SELECT *  FROM results LEFT JOIN races ON races.race_id = results.race_id WHERE results.race_id = " . $race_id;

$result2 = $conn->query($sql2);
    $countofhorses = $result2->num_rows;
   
        function rank_avg($value, $array, $order = 0) {
// sort  
  if ($order) sort ($array); else rsort($array);
// add item for counting from 1 but 0
  array_unshift($array, $value+1); 
// select all indexes vith the value
  $keys = array_keys($array, $value);
  if (count($keys) == 0) return NULL;
// calculate the rank
  
 $res =  array_sum($keys) / count($keys);
 return $res/2;
  
}
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


    <ul> <li><a href="meeting.php">Home</a></li>
        <li><a href="meeting.php">Meetings</a></li>
        <li><a href="result.php" >Results</a></li>
        <li><a class="active"><?php echo $_SESSION['mname']; ?></a></li>

        <?php
        if ($result1->num_rows > 0) {
            // output data of each row
            while ($row = $result1->fetch_assoc()) {
                ?>
                <li><a href=horses.php?raceid=<?php echo $row['race_id'] ?>&meetingid=<?php echo $meetingid; ?>&rd=<?php echo $row['race_distance'] ?> <?php if ($row['race_id'] == $_REQUEST['raceid']) { ?>class="active" <?php } ?>><?php echo $row['race_number'] ?></a></li>



                <?php
            }
        }
        ?>
        <li class="pull-right">   <a href="rating.php?raceid=<?php echo $_REQUEST['raceid'] ?>&meetingid=<?php echo $meetingid; ?>&rd=<?php echo $_REQUEST['rd'] ?>&avg=0" class="dropdown-item active" >Show Rating</a></li>
        <li class="pull-right"> <a href="rating.php?raceid=<?php echo $_REQUEST['raceid'] ?>&meetingid=<?php echo $meetingid; ?>&rd=<?php echo $_REQUEST['rd'] ?>&avg=1" class="dropdown-item active" >Show Average</a></li>

    </ul>
    <div class="container-fluid">

        <h1>Horses Data - Distance <?php echo $_REQUEST['rd']; ?> </h1>
        <table id="employee_grid" class="display" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>horse_fixed_odds</th>
                    <th>distance</th>
                    <th> pos</th>
                    <th> time</th>
                    <th> minihandi</th>
                    <th> handis</th>
                    <th>Rank Avg</th>

                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {

                    while ($row = $result->fetch_assoc()) {
                        //print_r($row);
                        $arr = explode(",", $row["handis"]);
                        $cnt = count($arr);
                        $per = ($cnt/$countofhorses)*100;
                    if($per>40){
                        $rank_avg = rank_avg($row["minihandi"], $arr,0);

                        echo "<tr>"
                        . "<td>" . $row["horse_name"] . "</td>"
                        . "<td>" . $row["horse_fixed_odds"] . "</td>"
                        . "<td>" . $row["distance"] . "</td>"
                        . "<td>" . $row["pos"] . "</td>"
                        . "<td>" . $row["time"] . "</td>"
                        . "<td>" . $row["minihandi"] . "</td>"
                        . "<td>" . $row["handis"] . "</td>"
                        . "<td>" . $rank_avg . "</td>"
                        
                        . "</tr>";
                    }
                    
                    }
                } else {
                    echo "0 results";
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Horse No.</th>
                    <th>Horse Name</th>
                    <th>Odds</th>
                    <th>H2H</th>
                    <th>Position</th>
                    <th>Length</th>
                    <th>Condition</th>
                    <th>Orig Dist</th>
                    <th>Distance</th>
                    <th>Weight</th>
                    <th>Last Weight</th>
                    <th>Sectional</th>
                    <th>Minimum Time</th>
                    <th>Handicap</th>
                    <th>NewTime</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>


<?php
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

</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('#employee_grid').DataTable({
            "pageLength": 25,

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
    $modifier = 0.05;
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


?>