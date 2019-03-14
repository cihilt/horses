<?php
include('includes/config.php');
include('includes/functions.php');
// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if(empty($_REQUEST['meeting'])) {
	echo "You must select a meeting";
	exit();
}
else {
	$meetdet = meeting_details($_REQUEST['meeting']);
	session_start();
	$_SESSION['mname'] = $meetdet->meeting_name;
	//$sql = "SELECT *, MIN(time) minimumtime,AVG(time) avgtime FROM data WHERE `name` IN (";
	$races = $mysqli->query("SELECT * FROM `tbl_races` WHERE `meeting_id`=" . $meetdet->meeting_id . " ORDER by race_order ASC");
	$races1 = $mysqli->query("SELECT * FROM `tbl_races` WHERE `meeting_id`=" . $meetdet->meeting_id . " ORDER by race_order ASC");
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Horses Data</title>
       <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

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

    	<ul> <li><a href="meeting.php">Home</a></li>
        <li><a href="horses.php">Horses</a></li>
        <li><a href="meeting.php">Meetings</a></li>
        <li><a href="races.php?meeting=<?=$_GET['meeting']?>">Races</a></li>
        <li><a class="active"><?=$meetdet->meeting_name?></a></li>

        <?php
        if ($races->num_rows > 0) {
            while ($racdet = $races->fetch_object()) {
                ?>
                <li><a href="race.php?race=<?=$racdet->race_id?>&avg=1"><?=$racdet->race_order?></a></li>
             <?php
            }
        }
        ?>

    </ul>
    <div class="container-fluid">
        <div class="">
            <h1>Race Data</h1>
            <div class="">
                <table id="employee_grid" class="display" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Race</th>
                            <th>Race No.</th>
                            <th>Race Schedule</th>
                        </tr>
                    </thead>
                    <tbody>
					<?php
                    if ($races1->num_rows > 0) {
                        // output data of each row
                        while ($racedet = $races1->fetch_object()) {
                            echo '<tr>'
                            . '<td>'.$racedet->race_order.'</td>'
                            . '<td><a href="race.php?race='.$racedet->race_id.'">'.$racedet->race_title.'</a></td>'
                            . '<td>'.$meetdet->meeting_date . ' ' . $racedet->race_schedule_time.'</td>'
                            . '</tr>';
                        }
                    } else {
                        echo "0 results";
                    }
                    $mysqli->close();
                    ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Race</th>
                            <th>Race No.</th>
                            <th>Race Schedule</th>
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
<?php } ?>