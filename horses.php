<?php
include('constant.php');
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$raceid = $_REQUEST['raceid'];
//$sql = "SELECT *, MIN(time) minimumtime,AVG(time) avgtime FROM data WHERE `name` IN (";
$sql = "SELECT * , MIN(data.time) minimumtime,MIN(data.time2) minimumtime2 FROM horses LEFT JOIN data ON horses.horse_name = data.name WHERE horses.race_id =" . $raceid;

$sql .=  " GROUP BY name,`distance`";

$result = $conn->query($sql);

session_start();
$meetingid = $_REQUEST['meetingid'];
//$sql = "SELECT *, MIN(time) minimumtime,AVG(time) avgtime FROM data WHERE `name` IN (";
$sql1 = "SELECT *  FROM races WHERE meeting_id =" . $meetingid." ORDER by race_id";
$result1 = $conn->query($sql1);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Horses Data</title>
        <link rel="stylesheet" id="font-awesome-style-css" href="http://phpflow.com/code/css/bootstrap3.min.css" type="text/css" media="all">
        <script type="text/javascript" charset="utf8" src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.11.1.min.js"></script>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.9/css/jquery.dataTables.min.css"/>

        <script type="text/javascript" src="https://cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js"></script>
  

<link rel="stylesheet" id="main-css" href="main.css" type="text/css" media="all">

<ul> <li><a href="meeting.php">Home</a></li>
      <li><a href="meeting.php">Meetings</a></li>
      <li><a class="active"><?php echo $_SESSION['mname']; ?></a></li>

      <?php
if ($result1->num_rows > 0) {
    // output data of each row
    while ($row = $result1->fetch_assoc()) {
        ?>
<li><a href=horses.php?raceid=<?php echo $row['race_id'] ?>&meetingid=<?php echo $meetingid; ?> <?php if($row['race_id']==$_REQUEST['raceid']){ ?>class="active" <?php } ?>><?php echo $row['race_number'] ?></a></li>
  

     
 <?php
      
}}
    ?>
</ul>
    <div class="container-fluid">
        <div class="">
            <h1>Horses Data</h1>
            <div class="">
                <table id="employee_grid" class="display" width="100%" cellspacing="0">
                    <thead>
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            // output data of each row
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>"
                                . "<td>" . $row["horse_number"] . "</td>"
                                . "<td>" . $row["horse_name"] . "</td>"
                                . "<td>" . $row["horse_fixed_odds"] . "</td>"
                                         . "<td>" . $row["horse_h2h"] . "</td>"
                                         . "<td>" . $row["pos"] . "</td>"
                                         . "<td>" . $row["length"] . "</td>"
                                         . "<td>" . $row["condition"] . "</td>"
                                         . "<td>" . $row["original_distance"] . "</td>"
                                         . "<td>" . $row["distance"] . "</td>"
                                         . "<td>" . $row["weight"] . "</td>"
                                          . "<td>" . $row["horse_weight"] . "</td>"
                                         . "<td>" . $row["sectional"] . "</td>"
                                         . "<td>" . $row["minimumtime"] . "</td>"
                                         . "<td>" . number_format($row["time2"],2) . "</td>"
                                . "</tr>";
                            }
                        } else {
                            echo "0 results";
                        }
                        $conn->close();
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
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>

    <script type="text/javascript">
        $(document).ready(function () {
            $('#employee_grid').DataTable({

                "responsive": true,
            });
        });
    </script>


<?php
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
	    	$modifier = 0.5;
	    }
	    elseif ($distance >= 1100 AND $distance <= 4000)
	    {
	    	$modifier = 0.7;
	    }
            $remainder = get_remainder($distance);
        //if horse wins    
            if($position==1){
                if($distance>$orgdistance){
                    win_rounded_up($time, $length, $modifier, $remainder);
                }else{ 
                    win_rounded_down($time, $length, $modifier, $remainder);
                }
            }else{
                 if($distance>$orgdistance){
                    loses_rounded_up($time, $length, $modifier, $remainder);
                }else{ 
                    loses_rounded_down($time, $length, $modifier, $remainder);
                }
            }
            
            
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
                $reminder = $reminder_distance*0.01;
                return $reminder;
}


function win_rounded_up($time,$length,$modifier,$remainder){
    
}
function win_rounded_down($time,$length,$modifier,$remainder){
    
    
}
function loses_rounded_up($time,$length,$modifier,$remainder){
    
}

function loses_rounded_down($time,$length,$modifier,$remainder){
    
}

?>