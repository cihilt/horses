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
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Horses Data</title>
 <!--       <link rel="stylesheet" id="font-awesome-style-css" href="http://phpflow.com/code/css/bootstrap3.min.css" type="text/css" media="all">-->
        <script type="text/javascript" charset="utf8" src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.11.1.min.js"></script>

        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.9/css/jquery.dataTables.min.css"/>

        <script type="text/javascript" src="https://cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js"></script>
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/1.0.3/css/dataTables.responsive.css">
        <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/responsive/1.0.3/js/dataTables.responsive.js"></script>

<link rel="stylesheet" id="main-css" href="main.css" type="text/css" media="all">
<? echo $menu ?>
    <div class="container">
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
                            <th>length</th>
                            <th>condition</th>
                            <th>distance</th>
                            <th>weight</th>
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

