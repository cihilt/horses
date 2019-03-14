<?php
$servername = "localhost";
$username = "bettinga";
$password = "Newcar888!!";
$dbname = "bettinganewdb";

// Create connection
$mysqli = new mysqli($servername, $username, $password, $dbname);

// Turn on debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Logging
date_default_timezone_set("UTC");
require_once 'logging.php';
define('LOG_LEVEL', 'ERROR');
$logger = new logger('logs_default_algorithm.txt', LOG_LEVEL);

