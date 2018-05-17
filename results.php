<?php
ignore_user_abort(true);
set_time_limit(0);
ini_set('max_execution_time', 0);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

chdir(dirname(__FILE__));
include_once('simple_html_dom.php');

$db_host = 'localhost';
$db_name = 'horse2';
$db_user = 'root';
$db_pass = '';

try {
    $dbh = new PDO('mysql:host='.$db_host.';dbname='.$db_name, $db_user, $db_pass);    
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}

$today_date = date('Y-m-d');
$today_date_for_db = date('d/n/Y');

$base_url = 'http://www.racingzone.com.au';
$part_url = '/results/'.$today_date.'/';
$parse_url = $base_url.$part_url;

$html = file_get_html($parse_url);

$insert_counter = 0;

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
            $msg = "[" . date("Y-m-d H:i:s") . "] Find meeting_id error: " . $stmt->error;
            echo $msg . "\n";
        }
        // .select meeting_id        
        $race_number = 1;
        $tds = $row->find('td.popup-race');
        foreach ($tds as $td) {
            if (empty($td->title)) {
                continue;
            }
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
                $msg = "[" . date("Y-m-d H:i:s") . "] Find race_id error: " . $stmt->error;
                echo $msg . "\n";
            }
            $race_number++;
            // .select race_id            
            $race_table = $race_html->find('table.formguide', 0);
            $race_table_rows = $race_table->find('tr');
            $i = 1;
            foreach ($race_table_rows as $race_table_row) {
                if (strpos($race_table_row->class, 'scratch') == false) {
                    $horse_position = $i;
                    $horse_name = $race_table_row->find('td.horse a', 0)->plaintext;
                    $i++;
                    $stmt = $dbh->prepare('INSERT INTO results (race_id, position, horse, date, event, distance) VALUE(:race_id, :position, :horse, :date, :event, :distance)');
                    $data = array(
                        ':race_id' => $race_id,
                        ':position' => $horse_position,
                        ':horse' => $horse_name,
                        ':date' => $today_date_for_db,
                        ':event' => $meeting_name,
                        ':distance' => $race_distance
                    );
                    if (!$stmt->execute($data)) {
                        $msg = "[" . date("Y-m-d H:i:s") . "] Insert failed: " . $stmt->error;
                        echo $msg . "\n";
                    } else {
                        $insert_counter++;
                    }
                }
            }
        }
    }
}
    
$dbh = null;

echo 'Successfully inserted '.$insert_counter.' records.';