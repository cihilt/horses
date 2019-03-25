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

// request data
$data = (isset($argv[1])) ? $argv[1] : [];
$data = json_decode(base64_decode($data), true);
if (empty($data)) {
    $wLogger->log('No data passed. Exit', 'error');
}
$procId = arrayGet($data, 'proc_id', 0);

// offset
$offsetStart = arrayGet($data, 'offset_start', '');
$offsetLimit = arrayGet($data, 'offset_limit', '');
$offset = ($offsetStart || $offsetLimit) ? "$offsetStart, $offsetLimit" : '';

// Run
$wLogger->log('Worker started: ' . $procId);
$wLogger->log("Worker $procId: run the handicap function with params: mysqli, \"$offset\"");
resetHandicap($mysqli, $offset);
$wLogger->log('Worker finished: ' . $procId);
