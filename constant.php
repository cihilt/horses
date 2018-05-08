<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "horse2";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

$menu = '
 <ul> <li><a href="meeting.php" class="active">Home</a></li>
      <li><a href="meeting.php">Meetings</a></li>
 </ul>';
?>
