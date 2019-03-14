<?php
include('includes/config.php');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if(isset($_REQUEST['date'])){ 
    $date = $_POST['date'];
    $sql = "SELECT * FROM `tbl_meetings` WHERE `meeting_date`='$date'";
}
else {
	$sql = "SELECT * FROM `tbl_meetings`";
}
$result = $mysqli->query($sql);
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
        
<ul>
  <li><a href="#">Home</a></li>
  <li><a href="horses.php">Horses</a></li>
  <li><a href="meeting.php" class="active">Meetings</a></li>
  <li><a href="races.php?meeting=1" >Races</a></li>
</ul>

    <div class="container">
         <form method="post">
        <div class="form-group  col-sm-4">
          <label for="date">Select date</label>
          <input type="date" name="date" class="form-control" id="date" aria-describedby="dateHelp" placeholder="Select date" required>
          </div>
              <div class="form-group  col-sm-4">
        <button type="submit" class="btn btn-primary">Show Meetings</button>
              </div>
             
      </form>
        <div class="clearfix"></div>
        <div class="">
            <h1>Meeting Data</h1>
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
$mysqli->close();
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

    </div>

    <script type="text/javascript">
        $(document).ready(function () {
            $('#employee_grid').DataTable({

                "responsive": true,
            });
        });
    </script>

