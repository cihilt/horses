<?php
date_default_timezone_set('Europe/London');
class RacingZoneScraper {
    protected $_base_url = "http://www.racingzone.com.au/form-guide/";
    protected $_db_host = "localhost";
    protected $_db_post = 3306;
    protected $_db_user = "root";
    protected $_db_password = "";
    protected $_db_name = "horses";
    protected $_mysqli;
    protected $_stmt_data;
    protected $_stmt_meetings;
    protected $_stmt_races;
    protected $_stmt_horses;
    protected $_delay = 2;
    protected $_cookiefile = "";
    protected $_ch;
    public function __construct() {
        if (!$this->_mysqli) {
            $this->mysql_connect();
        }
        $this->_cookiefile = dirname(__FILE__) . 'cookies.txt';
        if (file_exists($this->_cookiefile)) {
            unlink($this->_cookiefile);
        }
        $this->init();
    }
    private function mysql_connect() {
        $mysqli = new mysqli($this->_db_host, $this->_db_user, $this->_db_password, $this->_db_name);
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
            exit;
        }
        $mysqli->set_charset("utf8");
        $this->_mysqli = $mysqli;
    }
    private function init() {
        $sql = "INSERT INTO `data` ( `race_date`, `horse_id`, `name`, `track`, `length`, `condition`, `distance`, `original_distance`, `pos`, `weight`, `prize`, `time`, `sectional`, `time2` ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )  ON DUPLICATE KEY UPDATE `time2`=`time2`;";
        $stmt_data;
        if (!($stmt_data = $this->_mysqli->prepare($sql))) {
            echo "Prepare failed: (" . $this->_mysqli->errno . ") " . $this->_mysqli->error;
        }
        $this->_stmt_data = $stmt_data;

        $sql = "INSERT INTO `meetings` ( `meeting_date`, `meeting_name`, `meeting_url` ) VALUES ( ?, ?, ? );";
        $stmt_meetings;
        if (!($stmt_meetings = $this->_mysqli->prepare($sql))) {
            echo "Prepare failed: (" . $this->_mysqli->errno . ") " . $this->_mysqli->error;
        }
        $this->_stmt_meetings = $stmt_meetings;

        $sql = "INSERT INTO `races` ( `meeting_id`, `race_number`, `race_schedule_time`, `race_title` ) VALUES ( ?, ?, ?, ? );";
        $stmt_races;
        if (!($stmt_races = $this->_mysqli->prepare($sql))) {
            echo "Prepare failed: (" . $this->_mysqli->errno . ") " . $this->_mysqli->error;
        }
        $this->_stmt_races = $stmt_races;

        $sql = "INSERT INTO `horses` ( `race_id`, `horse_number`, `horse_name`, `horse_weight`, `horse_fixed_odds`, `horse_h2h` ) VALUES ( ?, ?, ?, ?, ?, ? );";
        $stmt_horses;
      
        if (!($stmt_horses = $this->_mysqli->prepare($sql))) {
            echo "Prepare failed: (" . $this->_mysqli->errno . ") " . $this->_mysqli->error;
        }
        $this->_stmt_horses = $stmt_horses;
    }
    public function get_data($start_date = "", $end_date = "") {
        $current_date = new DateTime('today');
        $current_date = $current_date->format("Y-m-d");
        if (!$start_date) {
            $start_date = $current_date;
        }
        if (!$end_date) {
            $end_date = $current_date;
        }
        $dates = $this->createDatesRange($start_date, $end_date);
        foreach ($dates as $date) {
            $this->process_date($date);
        }
    }
    private function parse_meetings($content, $date) {
        //file_put_contents("Meetings.htm", $content);
        $race_meetings = array();
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($content, LIBXML_NOWARNING);
        $xpath = new DOMXPath($doc);
        foreach ($xpath->query('//table[contains(@class, "meeting")]//tr') as $row) {
            $meeting = array();
            $meeting["date"] = $date;
            $meeting["place"] = $xpath->evaluate('string(./td[1]/a/text())', $row);
            $meeting["url"] = $xpath->evaluate('string(./td[1]/a/@href)', $row);
            $meeting["url"] = "http://www.racingzone.com.au" . $meeting["url"];
            array_push($race_meetings, $meeting);
        }
        return $race_meetings;
    }
    private function parse_races($content, $meeting_id) {
        $races = array();
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($content, LIBXML_NOWARNING);
        $xpath = new DOMXPath($doc);
        foreach ($xpath->query('(//h3[.="Race Schedule"]/following-sibling::table[contains(@class, "info")])[1]//tr') as $row) {
            $race = array();
            $race["meeting_id"] = $meeting_id;
            $race["title"] = $xpath->evaluate('string(./td[3]/a/text())', $row);
            $race["number"] = $xpath->evaluate('string(./td[1]/text())', $row);
            $race["schedule_time"] = $xpath->evaluate('string(./td[2]/text())', $row);
            $race["url"] = $xpath->evaluate('string(./td[3]/a/@href)', $row);
            $race["url"] = "http://www.racingzone.com.au" . $race["url"];
            array_push($races, $race);
        }
        return $races;
    }
    private function parse_horses($content, $race, $race_id) {
        $horses = array();
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($content, LIBXML_NOWARNING);
        $xpath = new DOMXPath($doc);
        foreach ($xpath->query('//table[contains(@class, "formguide")][1]//tr[contains(@class, "row-vm")]') as $row) {
            $horse = array();
            $horse["race_id"] = $race_id;
            $horse["name"] = $xpath->evaluate('string(./td[4]/a/text())', $row);
            $horse["horse_name"] = $horse["name"];
            $horse["horse_number"] = $xpath->evaluate('string(./td[1]/text())', $row);
            $horse["horse_weight"] = $xpath->evaluate('string(./td[6]/span/text())', $row);
            $horse["horse_fixed_odds"] = $xpath->evaluate('string(./td[11]/span/a/text())', $row);
            //$horse["horse_h2h"] = $xpath->evaluate('string(./td[4]/span[contains(@class, "h2h")]/text())', $row);

            $class = $xpath->evaluate('string(./@class)', $row);
            if (preg_match('/^(\d+)/', $class, $matches)) {
                $horse["id"] = $matches[1];
            }

            if ( preg_match( '/\$\("span.horse' . $horse["id"] . '"\)\.text\("([^"]*)"\)/', $content, $matches ) ) {

                $horse["horse_h2h"] = $matches[1];
            }
            $horse["field_id"] = $xpath->evaluate('string(./@rel)', $row);
            array_push($horses, $horse);
        }
        return $horses;
    }
    private function process_date($date) {
        echo ($date . "\n");
        $content = $this->CallPage($this->_base_url . $date . "/", null, null, $this->_cookiefile);
        $race_meetings = $this->parse_meetings( $content, $date );
        foreach ($race_meetings as $meeting) {
            $this->process_meeting($meeting);
        }
        sleep(1);
    }
    private function process_meeting($meeting) {
        echo ("\t" . $meeting["place"] . "\t" . $meeting["url"] . "\n");

        $meeting_id = $this->save_meeting($meeting);

        $content = $this->CallPage($meeting["url"], null, null, $this->_cookiefile);
        $races = $this->parse_races( $content, $meeting_id );
        foreach ($races as $race) {
            $this->process_race($race);
        }
    }
    private function process_race($race) {
        echo ("\t\t" . $race["title"] . "\t" . $race["url"] . "\n");

        $race_id = $this->save_race($race);

        if (preg_match('/\/(\d+)-[^\/]+\/\s*$/', $race["url"], $matches)) {
            $race_site_id = $matches[1];
        }
        $content = $this->CallPage("http://www.racingzone.com.au/formguide-detail.php?race_id=" . $race_site_id, null, null, $this->_cookiefile);
        $horses = $this->parse_horses($content, $race, $race_id);
        foreach ($horses as $horse) {
            $this->process_horse($horse, $race_site_id);
        }
    }
    private function process_horse($horse, $race_site_id) {
        echo ("\t\t\t\t" . $horse["id"] . "\n");

        $this->save_horse($horse);

        $content = $this->CallPage("http://www.racingzone.com.au/past-form-from-results2.php?horse=" . $horse["id"] . "&race_id=" . $race_site_id . "&field_id=" . $horse["field_id"], null, null, $this->_cookiefile);
        $records = $this->parse_horse($content, $horse);
        foreach ($records as $record) {
            $this->save_record($record);
        }
    }
    private function parse_horse($content, $meta) {
        //file_put_contents("Horse.htm", $content);
        $records = array();
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($content, LIBXML_NOWARNING);
        $xpath = new DOMXPath($doc);
        foreach ($xpath->query('//table[@class="pastform"]//tr[starts-with(@class, "result")]') as $row) {
            $record = array();
            $record["horse_id"] = $meta["id"];
            $record["name"] = $meta["name"];
            $record["race_date"] = $xpath->evaluate('string(./td[1]/text())', $row);
            $record["track"] = $xpath->evaluate('string(./td[2]/text())', $row);
            $record["distance"] = $xpath->evaluate('string(./td[3]/text()[1])', $row);
            $record["pos"] = $xpath->evaluate('string(./td[4]/strong/text())', $row);
            $record["mrg"] = $xpath->evaluate('string(./td[5]/text())', $row);
            $record["condition"] = $xpath->evaluate('string(./td[6]/text())', $row);
            $record["weight"] = $xpath->evaluate('string(./td[9]/text())', $row);
            $record["prize"] = $xpath->evaluate('string(./td[12]/text()[1])', $row);
            $record["time"] = $this->convert_to_minutes($xpath->evaluate('string(./td[13]/text())', $row));
//            print_r($record);die();
            if ( $record["time"] != 0 ) {
                $record["sectional"] = $xpath->evaluate('string(./td[14]/text())', $row);
                $record["time2"] = $this->calculate_modified_time($record["time"], $record["mrg"]);
                array_push($records, $record);
            }
        }
        return $records;
    }
    private function convert_to_minutes($time) {
//    	die($time);
        if (preg_match('/(\d+):/', $time, $matches)) {
            $minutes = $matches[1];
        } else {
            $minutes = 0;
        }
        if (preg_match('/([\d.]+)$/', $time, $matches)) {
            $seconds = $matches[1];
        } else {
            $seconds = 0;
        }
//        die('--'.$seconds);
        $result = $minutes + $seconds / 60;
        return number_format((float)$result, 2, '.', '');
    }

    private function __get_offset($val) {
//    	800 -> 899 = 0.77 (10 sec)
//		900 -> 999 = 0.87 (10 sec)
//		1000 -> 1099 = 0.97 (5 sec)
//		1100 -> 1199 = 1.02 (7 sec)
//		10/ 10 - 1 sec per 10 metres)
//		5 / 10 = 0.5 sec per 10 metres)
//		7 / 10 = 0.7 sec per 10 metres)
	    if ($val >= 800 AND $val <= 999)
	    {
	    	return 1;
	    }
	    elseif ($val >= 1000 AND $val <= 1099)
	    {
	    	return 0.5;
	    }
	    elseif ($val >= 1100 AND $val <= 4000)
	    {
	    	return 0.7;
	    }
	    return 0;
    }

    private function __process_time2($record) {
//    	First round up distance to nearest 10
//    	i.e 833 = 830
//		Then we round off again to nearest 100.
//		830-800 (with a remainder of 30)
//		Then we will use use an algorithm below to determine how much time to
//		add to the final time.
		$distance = $record["distance"];
		if ($distance % 10 < 5)
		{
			$distance -= $distance % 10;
		}
		else
		{
			$distance += (10 - ($distance % 10));
		}
	    	$sign = 1.0;
		if ($distance % 100 < 50)
		{
			$reminder_distance = $distance % 100;
			$distance -= $reminder_distance;
			$sign = -1.0;
		}
		else
		{
			$reminder_distance = (100 - ($distance % 100));
			$distance += $reminder_distance;
		}
		$offset = $this->__get_offset($distance);
//		echo '^^'.$offset.'^^';
//		echo '^^'.$reminder_distance.'^^';
	    $record['distance'] = $distance;
		$record['time2'] = $sign * $reminder_distance * 0.01 * $offset + $record['time2'];
		return $record;
    }

    private function __get_place($record)
    {
    	try {
    		$pos = explode('/', $record['pos']);
    		return intval($pos[0]);
	    } catch (Exception $e) {

	    }
    	return 0;
    }

    private function save_record($record) {

    	// Modify time2 to suit our needs
	    $record['original_distance'] = $record['distance'];
	    $place = $this->__get_place($record);
	    if ($place != 1)
	    {
	    	$record = $this->__process_time2($record);
	    }
	    else
	    {
	    	// First place, leave it as it is
	    	$record['time2'] = $record['time'];
	    }


        $initialDistance = $record["distance"];

        $thousands =  intval($initialDistance/1000);


        $thousandsModule = $initialDistance%1000;


        $hundrends = intval($thousandsModule/100);
        $tens = $initialDistance/10;

        if($thousands < 1)
        {
            $record["distance"] = ($hundrends * 100);
        }
        else
        {
            $record["distance"] = ($thousands * 1000) + ($hundrends * 100);
        }


        //$record["original_distance"] = $record["distance"];


//	    echo '----'.$place.'---';
//	    if ($record['time'] != $record['time2'])
//	    {
//	    	print_r($record);die();
//	    }
        $this->_stmt_data->bind_param("ssssssssssssss", $record["race_date"], $record["horse_id"], $record["name"], $record["track"], $record["mrg"], $record["condition"], $record["distance"], $record["original_distance"], $record["pos"], $record["weight"], $record["prize"], $record["time"], $record["sectional"], $record["time2"]);
        if (!$this->_stmt_data->execute()) {
            $msg = "[" . date("Y-m-d H:i:s") . "] Insert failed: " . $this->_stmt_data->error;
            echo $msg . "\n";
            $myfile = file_put_contents('logs.txt', $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }

    private function save_meeting($meeting) {
        $this->_stmt_meetings->bind_param("sss", $meeting["date"], $meeting["place"], $meeting["url"]);
        if (!$this->_stmt_meetings->execute()) {
            $msg = "[" . date("Y-m-d H:i:s") . "] Insert failed: " . $this->_stmt_meetings->error;
            echo $msg . "\n";
            $myfile = file_put_contents('logs.txt', $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
        }

        return $this->_mysqli->insert_id;
    }

    private function save_race($race) {
        $this->_stmt_races->bind_param("ssss", $race["meeting_id"], $race["number"], $race["schedule_time"], $race["title"]);
        if (!$this->_stmt_races->execute()) {
            $msg = "[" . date("Y-m-d H:i:s") . "] Insert failed: " . $this->_stmt_races->error;
            echo $msg . "\n";
            $myfile = file_put_contents('logs.txt', $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
        }

        return $this->_mysqli->insert_id;
    }

    private function save_horse($horse) {
        $this->_stmt_horses->bind_param("ssssss", $horse["race_id"], $horse["horse_number"], $horse["horse_name"], $horse["horse_weight"], $horse["horse_fixed_odds"], $horse["horse_h2h"]);
        if (!$this->_stmt_horses->execute()) {
            $msg = "[" . date("Y-m-d H:i:s") . "] Insert failed: " . $this->_stmt_horses->error;
            echo $msg . "\n";
            $myfile = file_put_contents('logs.txt', $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
        }

        return $this->_mysqli->insert_id;
    }


    private function calculate_modified_time($original_time, $length) {
        $modified_time = "";
        if ($original_time <= 1.19) {
            $modified_time = $original_time + ($length * 0.04);
        } else {
            $modified_time = $original_time + ($length * 0.03);
        }
        return number_format((float)$modified_time, 2, '.', '');;
    }
    private function createDatesRange($start, $end, $format = 'Y-m-d') {
        $start = new DateTime($start);
        $end = new DateTime($end);
        $invert = $start > $end;
        $dates = array();
        $dates[] = $start->format($format);
        while ($start != $end) {
            $start->modify(($invert ? '-' : '+') . '1 day');
            $dates[] = $start->format($format);
        }
        return $dates;
    }
    private function get_post_fields($arrPostFields) {
        $strPostFields = "";
        $postFieldValues = array();
        foreach ($arrPostFields as $key => $value) {
            array_push($postFieldValues, urlencode($key) . "=" . urlencode($value));
        }
        $strPostFields = join("&", $postFieldValues);
        return $strPostFields;
    }
    private function get_by_xpath($root, $xpath, $elem = null) {
        $result = "";
        $nodeList = $root->query($xpath, $elem);
        if ($nodeList->length > 0) {
            $result = trim($nodeList->item(0)->nodeValue);
        }
        return $result;
    }
    private function CallPage($strSubmitURL, $arrPostFields = null, $strReferrer = "", $strCookieFile = "", $strProxy = "", $arrCustomHeaders = null) {
        $header[0] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        //$header[] = "Accept-Encoding: gzip, deflate, br";
        $header[] = "Cache-Control: no-cache";
        $header[] = "Connection: keep-alive";
        $header[] = "Accept-Language: en-us,en;q=0.5";
        $header[] = "User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36";
        if ($arrCustomHeaders) {
            foreach ($arrCustomHeaders as $customHeader) {
                $header[] = $customHeader;
            }
        }
        $cookie_jar = $strCookieFile;
        if (!$this->_ch) {
            $this->_ch = curl_init();
        }
        curl_setopt($this->_ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($this->_ch, CURLOPT_VERBOSE, false);
        if ($strProxy != "") {
            curl_setopt($this->_ch, CURLOPT_PROXY, $strProxy);
        }
        if ($cookie_jar != "") {
            curl_setopt($this->_ch, CURLOPT_COOKIEJAR, $cookie_jar);
            curl_setopt($this->_ch, CURLOPT_COOKIEFILE, $cookie_jar);
        }
        if ($strReferrer != "") {
            curl_setopt($this->_ch, CURLOPT_REFERER, "$strReferrer");
        }
        //curl_setopt($this->_ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($this->_ch, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($this->_ch, CURLOPT_TIMEOUT, 140);
        curl_setopt($this->_ch, CURLOPT_URL, $strSubmitURL);
        if ($arrPostFields != null) {
            //set type as an post
            //curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($this->_ch, CURLOPT_HEADER, true);
            curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, 'POST');
            $strPostFields = $this->get_post_fields($arrPostFields);
            //field name
            $header[] = "Content-length: " . strlen($strPostFields);
            $header[] = 'Content-Type: application/x-www-form-urlencoded';
            $header[] = "method:POST";
            //$header[] = 'X-Requested-With: XMLHttpRequest';
            curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $strPostFields);
            //echo "posting $strPostFields";

        } else {
            curl_setopt($this->_ch, CURLOPT_HEADER, false);
        }
        // don' verify ssl host
        curl_setopt($this->_ch, CURLOPT_SSL_VERIFYPEER, true);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $header);
        //curl_setopt($this->_ch, CURLOPT_VERBOSE, true);
        $strData = curl_exec($this->_ch);
        if (!$strData) {
            die("cURL error: " . curl_error($this->_ch) . "\n");
            return '';
        }
        //curl_close($ch);
        //unset($ch);
        return $strData;
    }
}
$scraper = new RacingZoneScraper();

//echo $scraper->__process_time2([
//	'distance' => '833',
//	'time' => '87.5',
//]);
//echo '<hr>';
//echo $scraper->__process_time2([
//	'distance' => '846',
//	'time' => '87.5',
//]);
//die();

$scraper->get_data();
//$scraper->get_data("2018-05-01", "2018-05-04");
echo ("Done!");
?>
