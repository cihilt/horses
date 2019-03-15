<?php
define('APP_ROOT', realpath(__DIR__ . '/..'));
require_once APP_ROOT . '/vendor/autoload.php';

// Load environment variables
$env = Dotenv\Dotenv::create(APP_ROOT);
$env->load();

// App constants
define('WORKERS_COUNT', getenv('WORKERS_COUNT'));
define('ERROR_REPORTING', getenv('ERROR_REPORTING'));
define('DISPLAY_ERRORS', getenv('DISPLAY_ERRORS'));
define('MODIFIER', getenv('MODIFIER'));
define('TIMEZONE', getenv('TIMEZONE'));
define('LOG_LEVEL', getenv('LOG_LEVEL'));

// Error reporting
error_reporting(ERROR_REPORTING);
ini_set('display_errors', DISPLAY_ERRORS);

// Logging
require_once 'logging.php';
$logger = new logger('logs/main.log', LOG_LEVEL);

// Database connection
$dbServer = getenv('DB_SERVER');
$dbName = getenv('DB_NAME');
$dbUser = getenv('DB_USER');
$dbPassword = getenv('DB_PASSWORD');
$mysqli = new mysqli($dbServer, $dbUser, $dbPassword, $dbName);
if ($mysqli->connect_error) {
    $logger->log("Database connection error ({$mysqli->connect_errno}): {$mysqli->connect_error}", 'error');
    die('Database connection error (' . $mysqli->connect_errno . ')');
}

date_default_timezone_set(TIMEZONE);
