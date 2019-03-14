<?php
date_default_timezone_set("UTC");

$servername = "localhost";
$username = "bettinga";
$password = "Newcar888!!";
$dbname = "bettinganewdb";

// Create connection
$mysqli = new mysqli($servername, $username, $password, $dbname);

// Logging
require_once 'logging.php';
define('LOG_LEVEL', 'error');
$logger = new logger('logs.txt', LOG_LEVEL);

define('APP_ROOT', realpath(__DIR__ . '/..'));
define('WORKERS_COUNT', 2);
define('ERROR_REPORTING', E_ALL);
define('DISPLAY_ERRORS', 1);
define('MODIFIER', '0.025');

// Turn on debugging
error_reporting(ERROR_REPORTING);
ini_set('display_errors', DISPLAY_ERRORS);
