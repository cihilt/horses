<?php
include('includes/config.php');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$horse = null;
if(isset($_REQUEST['horse'])){
    $horse = $_REQUEST['horse'];
}
$sql = "SELECT * FROM `tbl_horses` WHERE `horse_id`='$horse'";
$result = $mysqli->query($sql);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Horse Data</title>
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

<ul>
  <li><a href="#">Home</a></li>
  <li><a href="horses.php" class="active">Horses</a></li>
  <li><a href="meeting.php">Meetings</a></li>
  <li><a href="races.php?meeting=1" >Races</a></li>
</ul>

    <div class="container">

    <?php
    if ($result->num_rows > 0) {
        // output data of each row
        while ($row = $result->fetch_object()) {
    ?>

        <div class="panel-group">

          <div class="panel panel-primary">
            <div class="panel-heading"><?php echo $row->horse_name; ?> # <?php echo $row->horse_id; ?></div>
            <div class="panel-body">

            <table id="employee_grid_2" class="display" width="100%" cellspacing="0">
                    <tbody>

                            <td>Horse Name: <?php echo $row->horse_name; ?></td>
                            <td>Horse Slug: <?php echo $row->horse_slug; ?></td>
                            <td>Horse Latest Results: <?php echo $row->horse_latest_results; ?></td>
                            <td>Added On: <?php echo $row->added_on; ?></td>

                    </tbody>
                </table>

            </div>
          </div>

        </div>

    <?php
        }

        $sql = "SELECT * FROM tbl_meetings
                LEFT JOIN tbl_races ON tbl_meetings.meeting_id = tbl_races.meeting_id
                LEFT JOIN tbl_results ON tbl_races.race_id = tbl_results.race_id
                WHERE tbl_results.horse_id = " . $horse . " GROUP BY tbl_meetings.meeting_id";
        $result = $mysqli->query($sql);
    ?>

        <div class="">
            <h1>Meetings Data</h1>
            <div class="">
                <table id="employee_grid" class="display" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Meeting Date</th>
                            <th>Meeting Name</th>

                        </tr>
                    </thead>
                    <tbody>
<?php
if ($result->num_rows > 0) {
    // output data of each row
    while ($row = $result->fetch_object()) {
        echo "<tr><td>" .
        $row->meeting_date . "</td><td><a href='races.php?meeting=" . $row->meeting_id . "'>" . $row->meeting_name. "</a></td></tr>";
    }
} else {
    echo "0 results";
}
?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Meeting Date</th>
                            <th>Meeting Name</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    <?php
    } else {
        echo "0 results";
    }
    $mysqli->close();
    ?>

    </div>

    <script type="text/javascript">
        $(document).ready(function () {
            $('#employee_grid').DataTable({

                "responsive": true,
            });
        });
    </script>
