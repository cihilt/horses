<?php
date_default_timezone_set('Europe/London');
$str_date = strtotime('now');

include('results_core.php');

if (isset($msg['success']) && !empty($msg['success'])) :
    foreach($msg['success'] as $message) :
        echo "success: " . $message . PHP_EOL . PHP_EOL;
    endforeach;
endif;

if (isset($msg['info']) && !empty($msg['info'])) :
    foreach($msg['info'] as $message) :
        echo "info: " . $message . PHP_EOL . PHP_EOL;
    endforeach;
endif;

if (isset($msg['danger']) && !empty($msg['danger'])) :
    foreach($msg['danger'] as $message) :
        echo "danger: " . $message . PHP_EOL . PHP_EOL;
    endforeach;
endif;

exit();