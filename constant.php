<?php

$servername = "localhost";
$username = "bettinga_horsy";
$password = "newcar123";
$dbname = "bettinga_horse2";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

$menu = '
 <ul> <li><a href="meeting.php" class="active">Home</a></li>
      <li><a href="meeting.php">Meetings</a></li>
 </ul>';
?>
