<?php

$today_date = date('Y-m-d', $str_date);
$today_date_for_db = date('d/n/Y', $str_date);

ignore_user_abort(true);
set_time_limit(0);
ini_set('max_execution_time', 0);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

chdir(dirname(__FILE__));
include_once('simple_html_dom.php');

include('constant.php');
try {
    $dbh = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);    
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}

$base_url = 'http://www.racingzone.com.au';
$part_url = '/results/'.$today_date.'/';
$parse_url = $base_url.$part_url;

$html = file_get_html($parse_url);

$insert_counter = 0;

$msg = [];

$msg['info'][] = "[" . date("Y-m-d H:i:s") . "] Main parse url is <a href='$parse_url' class='alert-link'>$parse_url</a>";

$tables = $html->find('table.meeting');
foreach ($tables as $table) {
    $rows = $table->find('tr');
    foreach ($rows as $row) {
        // select meeting_id
        $meeting_date = $today_date;
        $meeting_name = $row->find('td', 0)->find('a', 0)->plaintext;
        $stmt = $dbh->prepare("SELECT meeting_id FROM meetings WHERE meeting_date = ? AND meeting_name = ?");
        if ($stmt->execute(array($meeting_date, $meeting_name))) {
            $meeting_id = $stmt->fetchColumn();
        } else {
            $msg['danger'][] = "[" . date("Y-m-d H:i:s") . "] Find meeting_id error: " . $stmt->error;
        }
        // .select meeting_id        
        $race_number = 1;
        $tds = $row->find('td.popup-race');
        foreach ($tds as $td) {
            if (empty($td->title)) {
                continue;
            }            
            // get race_date
            $race_date = date('d/m/y', $str_date);
            // .get race_date
            // get race_name
            $race_name = '';
            $td_html = str_get_html( $td->title );
            if ($td_html) {
                $race_name = $td_html->find('th', 0)->plaintext;
            }
            // .get race_name
            $link = $td->find('a', 0)->href;
            $race_link = $base_url.$link;
            $race_html = file_get_html($race_link);
            // get race_distance
            $race_distance_spans = $race_html->find('div#container > div > h1 > span');
            $race_distance_span = end($race_distance_spans);
            $race_distance = $race_distance_span->plaintext;
            $race_distance = str_ireplace('m', '', $race_distance);
            // .get race_distance            
            // select race_id
            $stmt = $dbh->prepare("SELECT race_id FROM races WHERE meeting_id = ? AND race_number = ? AND race_distance = ?");
            if ($stmt->execute(array($meeting_id, $race_number, $race_distance))) {
                $race_id = $stmt->fetchColumn();
            } else {
                $msg['danger'][] = "[" . date("Y-m-d H:i:s") . "] Find race_id error: " . $stmt->error;
            }
            $race_number++;
            // .select race_id            
            $race_table = $race_html->find('table.formguide', 0);
            $race_table_rows = $race_table->find('tr');
            $i = 1;
            if ($race_id) {
                foreach ($race_table_rows as $race_table_row) {
                    if (strpos($race_table_row->class, 'scratch') == false) {
                        $horse_position = $i;
                        $horse_name = $race_table_row->find('td.horse a', 0)->plaintext;
                        $i++;
                        // select data_id
                        $data_id = null;                        
                        $stmt = $dbh->prepare("SELECT id FROM data WHERE race_date = ? AND race_name = ? AND name = ? AND track_name = ? LIMIT 1");
                        if ($stmt->execute(array($race_date, $race_name, $horse_name, $meeting_name))) {
                            $data_id = $stmt->fetchColumn();
                        } else {
                            $msg['danger'][] = "[" . date("Y-m-d H:i:s") . "] Find data_id error: " . $stmt->error;
                        }
                        // select data_id
                        $stmt = $dbh->prepare('INSERT IGNORE INTO results (race_id, data_id, position, horse, date, event, distance) VALUE(:race_id, :data_id, :position, :horse, :date, :event, :distance)');
                        $data = array(
                            ':race_id' => $race_id,
                            ':data_id' => $data_id,
                            ':position' => $horse_position,
                            ':horse' => $horse_name,
                            ':date' => $today_date_for_db,
                            ':event' => $meeting_name,
                            ':distance' => $race_distance
                        );
                        if (!$stmt->execute($data)) {
                            $msg['danger'][] = "[" . date("Y-m-d H:i:s") . "] Insert failed: " . $stmt->error;
                        } else {
                            $insert_counter++;
                        }                    
                    }
                }
            } else {
                $msg['danger'][] = "[" . date("Y-m-d H:i:s") . "] Insert failed - race_id is empty or not found at other table for url <a href='$race_link' class='alert-link'>$race_link</a>";
            }
        }
    }
}
    
$dbh = null;

$msg['success'][] = 'Successfully found '.$insert_counter.' items.';
