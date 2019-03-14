<?php
if (isset($_POST['date']) && !empty($_POST['date'])) {
    ignore_user_abort(true);
    set_time_limit(0);
    ini_set('max_execution_time', 0);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
	date_default_timezone_set('Europe/London');

    chdir(dirname(__FILE__));
    include_once('simple_html_dom.php');

    include('includes/config.php');
    try {
        $dbh = new PDO('mysql:host='.$servername.';dbname='.$dbname, $username, $password);
    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . "<br/>";
        die();
    }

    $str_date = strtotime($_POST['date']);

    $today_date = date('Y-m-d', $str_date);
    $today_date_for_db = date('d/n/Y', $str_date);

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
                $link = $td->find('a', 0)->href;
                $race_link = $base_url.$link;
                $race_html = file_get_html($race_link);
                // get race_distance
                $race_distance_spans = $race_html->find('div#container > div > h1 > span');
                foreach ($race_distance_spans as $key => $race_distance_span_el) {
                    $race_distance_span_el_class = $race_distance_span_el->class;
                    if (strpos($race_distance_span_el_class, 'popup') !== false) {
                        unset($race_distance_spans[$key]);
                    }
                }
                $race_distance_span = end($race_distance_spans);
                $race_distance = $race_distance_span->plaintext;
                $race_distance = str_ireplace('m', '', $race_distance);
                $race_distance = trim($race_distance);
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
}
?>

<!-- Select date form -->
<!-- jquery -->
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="/resources/demos/style.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
$( function() {
  $( "#date" ).datepicker({maxDate: '0'}).datepicker("setDate", new Date());
} );
</script>
<!-- .jquery -->
<!-- bootstrap -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" crossorigin="anonymous">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" crossorigin="anonymous">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" crossorigin="anonymous"></script>
<!-- .bootstrap -->
<div class="container">
  <div class="row">
    <div class="col-md-8 col-md-offset-2">
      <h1>Scrapping for `results` table</h1>
      <form method="post">
        <div class="form-group">
          <label for="date">Select date</label>
          <input type="text" name="date" class="form-control" id="date" placeholder="Select date" required>
        </div>
        <button type="submit" class="btn btn-primary">Scrape</button>
      </form>
      <?php if (isset($msg['success']) && !empty($msg['success'])) : ?>
      <?php foreach($msg['success'] as $message) : ?>
      <div class="alert alert-success" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <?php echo $message; ?>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
      <?php if (isset($msg['info']) && !empty($msg['info'])) : ?>
      <?php foreach($msg['info'] as $message) : ?>
      <div class="alert alert-info" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <?php echo $message; ?>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
      <?php if (isset($msg['danger']) && !empty($msg['danger'])) : ?>
      <?php foreach($msg['danger'] as $message) : ?>
      <div class="alert alert-danger" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <?php echo $message; ?>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
<!-- .Select date form -->
