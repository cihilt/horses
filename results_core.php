<?php
date_default_timezone_set('Europe/London');
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

include('includes/config.php');
try {
    $dbh = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}

$base_url = 'https://www.racingzone.com.au';
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
        $stmt = $dbh->prepare("SELECT meeting_id FROM tbl_meetings WHERE meeting_date = ? AND meeting_name = ?");
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
            $stmt = $dbh->prepare("SELECT race_id FROM tbl_races WHERE meeting_id = ? AND race_order = ? AND race_distance = ?");
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
						$horseslug = preg_replace('/[^A-Za-z0-9\-]/', '', strtolower($horse_name));

						$stmt = $dbh->prepare("SELECT horse_id FROM tbl_horses WHERE horse_slug = ?");
						if ($stmt->execute(array($horseslug))) {
							$horse_id = $stmt->fetchColumn();
						}

                        $i++;

                        $stmt = $dbh->prepare('INSERT IGNORE INTO tbl_results (race_id, horse_id, position) VALUE(:race_id, :horse_id, :position)');
                        $data = array(
							':race_id' => $race_id,
							':horse_id' => $horse_id,
							':position' => $horse_position,
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
