<?php
if($argv === null) {
    die('A worker should not be run directly from a browser');
}

require_once '../includes/config.php';
require_once APP_ROOT . '/includes/functions.php';
require_once APP_ROOT . '/includes/formula_functions.php';

error_reporting(ERROR_REPORTING);
ini_set('display_errors', DISPLAY_ERRORS);

// logger
$wLogger = new logger(APP_ROOT . '/logs/'. basename(__FILE__) .'.log', 'debug');
$logPrefix = 'ProcID ' . $procId . ' ---> ';

// request data
$data = (isset($argv[1])) ? $argv[1] : [];
$data = json_decode(base64_decode($data), true);
if (empty($data)) {
    $wLogger->log('No data passed. Exit', 'error');
}
$limit = arrayGet($data, 'limit');
$distance = arrayGet($data, 'distance');
$procId = arrayGet($data, 'proc_id', 0);
$positionPercentage = arrayGet($data, 'position_percentage', 0);
$algId = arrayGet($data, 'algorithm_id');
$algTitle = arrayGet($data, 'algorithm_title');

// offset
$offsetStart = arrayGet($data, 'offset_start', '');
$offsetLimit = arrayGet($data, 'offset_limit', '');

// Prepare the selected function
$algArgs = [$mysqli, $positionPercentage];
if ($algTitle == 'udpatehptime') {
    $algArgs[] = $limit;
} elseif ($algTitle == 'distance_new') {
    $algArgs[] = $distance;
}

// Run the algorithm
$qLimit = ($offsetStart || $offsetLimit) ? "LIMIT $offsetStart, $offsetLimit" : '';
$q = "SELECT * FROM tbl_races ORDER BY race_id ASC $qLimit ";
$races = $mysqli->query($q);
$i = 0;

$wLogger->log('Worker started: ' . $procId);
$wLogger->log('Offset: ' . $qLimit);
while ($race = $races->fetch_object()) {
    $algArgs['raceId'] = $race->race_id;
    $wLogger->log("Run the function with params: mysqli, $algArgs[1], $algArgs[2], {$algArgs['raceId']}");
    call_user_func_array($algTitle, $algArgs);
    $i++;
}
$wLogger->log('Worker finished: ' . $procId);
$wLogger->log('Races count: ' . $i);
