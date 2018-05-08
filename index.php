<?php

include('constant.php');
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if(isset($_REQUEST['searchsn'])){
   // var_dump($_REQUEST);
    $dtype = $_REQUEST['dtype'];
    $distance = $_REQUEST['distance'];
     $distance1 = $_REQUEST['distance1'];
    switch ($dtype) {
        case "Equals":

            $sql1 = "distance = ".$distance;


            break;
  case "GreaterThan":

            $sql1 = "distance > ".$distance;


            break;
         case "LessThan":

            $sql1 = "distance < ".$distance;


            break;
         case "LessThanEquals":

            $sql1 = "distance <= ".$distance;


            break;
         case "GreaterThanEquals":

            $sql1 = "distance >= ".$distance;


            break;
         case "Between":

            $sql1 = "distance BETWEEN ".$distance." AND ".$distance1;


            break;
        default:
            echo "Default";
            break;
    }
    //echo $sql1;
   // exit();
$file_handle = fopen("horses.txt", "rb");

$distance = "800";


//$sql = "SELECT *, MIN(time) minimumtime,AVG(time) avgtime FROM data WHERE `name` IN (";
$sql = "SELECT *, MIN(time) minimumtime,MIN(time2) minimumtime2 FROM data WHERE `name` IN (";

$file_handle = fopen("horses.txt", "rb");
/*
while (!feof($file_handle) ) {

$line_of_text = fgets($file_handle);
$parts = explode(',', $line_of_text);
//print_r($parts);
$str = join('","', $parts);
$sql .= "\"".$str."\"";
//$sql .= $str1;
}*/


while (!feof($file_handle) ) {

$line_of_text = fgets($file_handle);
$parts = explode(',', $line_of_text);
//print_r($parts);
$str = join('","', $parts);
$sql  .= "\"".$str."\"";


}$sql .= ") AND ";
$sql .= $sql1;
$sql .=  " GROUP BY name,`distance`";



$result = $conn->query($sql);


?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Horses Data</title>
<!--  <link rel="stylesheet" id="font-awesome-style-css" href="http://phpflow.com/code/css/bootstrap3.min.css" type="text/css" media="all"> -->
<script type="text/javascript" charset="utf8" src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.11.1.min.js"></script>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.9/css/jquery.dataTables.min.css"/>

<script type="text/javascript" src="https://cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/1.0.3/css/dataTables.responsive.css">
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/responsive/1.0.3/js/dataTables.responsive.js"></script>

<? echo $menu ?>

	<div class="container">
      <div class="">
        <h1>Horse Data</h1>
        <div class="">
		<table id="employee_grid" class="display" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>Number</th>
                <th>name</th>
                <th>length</th>
				<th>condition</th>
                <th>distance</th>
                <th>weight</th>
                <th>Sectional</th>
                <th>Minimum Time</th>

                     <th>Handicap</th>
            </tr>
        </thead>
 <tbody>
   <?php
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>" . 
        $row["id"]. "</td><td>" .
        $row["name"]. "</td><td>" . 
        $row["length"]. "</td><td>" . 
        $row["condition"]. "</td><td>" . 
        $row["distance"]. "</td><td>" . 
        $row["weight"]. "</td><td>" . 
        $row["sectional"]. "</td><td>" .
        $row["minimumtime"]. "</td><td>" . number_format($row["time2"],2). "</td></tr>";
    }
} else {
    echo "0 results";
}
$conn->close();

?>
 </tbody>
        <tfoot>
            <tr>
                <th>name</th>
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
$( document ).ready(function() {
$('#employee_grid').DataTable({

		 "responsive": true,
             });
});
</script>
<?php } ?>
<form method="POST" name="searchvanita" class="searchvanita" id="searchvanita" >
<div class="clear"></div>
<div class="c_one"> <label>Distance</label>
    <input name="distance" type="text" id="sn" tabindex="1" class="sm-form-c" placeholder="distance" autocomplete="off" />  </div>
  
<div class="c_one"> <label>Between Distance</label>
    <input name="distance1" type="text" id="sn" tabindex="1" class="sm-form-c" placeholder="distance to" autocomplete="off" />  </div>
 
    
    <select name="dtype" id="type">
            <option value="Equals">Equals</option>
            <option value="GreaterThan">Greater Than</option>
            <option value="LessThan">Less Than</option>
            <option value="GreaterThanEquals">Greater Than Equals</option>
               <option value="LessThanEquals">Less Than Equals</option>
                <option value="Between">Between</option>
        </select>
<div class="col_one_fifth"> <br/> <input type="submit" name="searchsn" value="Search Horses"  tabindex="9" class="button button-3d nomargin" />
</div>
</form>

