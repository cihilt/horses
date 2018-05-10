<?php
include('constant.php');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT *  FROM meetings";


$result = $conn->query($sql);
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

<link rel="stylesheet" id="main-css" href="main.css" type="text/css" media="all">
<ul>
  <li><a href="meeting.php" class="active">Home</a></li>
  <li><a href="meeting.php">Meetings</a></li>
  
</ul>

    <div class="container">
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
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" .
        $row["meeting_date"] . "</td><td><a href=race.php?meetingid=" . $row['meeting_id'] . ">" . $row["meeting_name"] . "</a></td></tr>";
    }
} else {
    echo "0 results";
}
$conn->close();
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

