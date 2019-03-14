<?php
error_reporting(E_ALL);
include('includes/config.php');

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$totalprofit  = 0;
$totalloss    = 0;
$ratingprofit = 0;
$ratingloss   = 0;
$data_data    = array();
$data_id      = array();
$sql          = "SELECT horse_name, AVG(rating) as rating, AVG(rank) as rank, AVG(rank) as rank, horse_fixed_odds FROM tbl_hist_results hr INNER JOIN tbl_horses h ON hr.horse_id = h.horse_id WHERE 1 GROUP BY horse_name";
$result       = $mysqli->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data_data[$row['horse_name']] = array(
            'rating' => $row['rating'],
            'rank' => $row['rank'],
            'horse_fixed_odds' => $row['horse_fixed_odds']
        );
    }
}

$results_data = array();
$results_id   = array();
$sql          = "select * from `tbl_results`";
$result       = $mysqli->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $results_data[$row['horse_id']] = array('race_id' => $row['race_id'], 'position' => $row['position']);
    }
}

$horse_data = array();
$sql        = "SELECT * FROM `tbl_horses` ORDER BY horse_id ASC";
$result     = $mysqli->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        
        $rating      = '';
        $rank        = '';
		$odds        = '';

		if (isset($data_data[$row['horse_name']])) {
            $rating       = $data_data[$row['horse_name']]["rating"];
			$rank 		  = $data_data[$row['horse_name']]["rank"];
			$odds		  = $data_data[$row['horse_name']]['horse_fixed_odds'];
        }

        $position = '';
		$race_id = '';
        if (isset($results_data[$row['horse_id']])) {
            $race_id     = $results_data[$row['horse_id']]['race_id'];
            $position     = $results_data[$row['horse_id']]['position'];
        }

		$horse_data[] = array(
			$race_id,
			$row['horse_name'],
			$rating,
			$rank,
			$position,
			$odds,
		);
    }
}

$total_profit = 0;
$total_loss   = 0;

$sql_raceid    = "SELECT race_id  FROM tbl_races";
$result_raceid = $mysqli->query($sql_raceid);

//echo "<pre>";
//print_r($horse_data);
//echo "</pre>";
//exit;

echo "<h2>Profit and Loss for every Race based on Average Rank Field</h2> <br />";

if ($result_raceid->num_rows > 0) {

    // output data of each row
    while ($row_id = $result_raceid->fetch_assoc()) {

        $temp_array = array();
        $race_data  = array_column($horse_data, 0);
        
        foreach ($race_data as $k => $r) {
            if ($row_id['race_id'] == $r) {
                $temp_array[] = $horse_data[$k];
            }
        }
        usort($temp_array, function($a, $b)
        {
            if ($a[3] === $b[3])
                return 0;
            return ($a[3] > $b[3]) ? -1 : 1;
        });
		
        if (count($temp_array) > 0) {
            $real_result = array(
                $temp_array[0],
                $temp_array[1]
            );
            $ratin       = array();
            
            if (count($real_result) > 0) {
                foreach ($real_result as $row) {
                    $ratin[]  = number_format(floatval($row[2]), 0);
                    $avgrank  = number_format(floatval($row[3]), 2);
                    $odds     = str_replace("$", "", $row[5]);
                    $position = intval($row[4]);
                    $profit = $row[4] == "" ? 0 : (($position == 1) ? ((10 * $odds) - 10) : -10);
                    $total_loss += ($profit < 0) ? 10 : 0;
                    $total_profit += $profit;
                    echo "<strong>race id:</strong> " . $row_id['race_id']  . "&nbsp&nbsp <strong>horse:</strong> " . $row[1]  . "&nbsp&nbsp <strong>Revenue:</strong> \$" . $profit . "&nbsp&nbsp&nbsp <strong>Total:</strong> \$" . $total_profit;
                    echo "<br />";
                }
            }
        }
    }
}
echo "<br />Average Rank Total Profit: $" . $total_profit;
echo "<br/>";
echo "Avg Rank Current Winnings: " . ($total_loss );
echo "<br/>";
echo "Avg Rank Current Losses: " . ($total_loss - $total_profit);
echo "<br/><br/>";

//////////////////////////////////////////////////////////////////////////////
$totalprofit  = 0;
$totalloss    = 0;
$ratingprofit = 0;
$ratingloss   = 0;
$data_data    = array();
$data_id      = array();
$sql          = "SELECT horse_name, AVG(rating) as rating, AVG(rank) as rank, AVG(rank) as rank, horse_fixed_odds FROM tbl_hist_results hr INNER JOIN tbl_horses h ON hr.horse_id = h.horse_id WHERE 1 GROUP BY horse_name";
$result       = $mysqli->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data_data[$row['horse_name']] = array(
            'rating' => $row['rating'],
            'rank' => $row['rank'],
            'horse_fixed_odds' => $row['horse_fixed_odds']
        );
    }
}

$results_data = array();
$results_id   = array();
$sql          = "select * from `tbl_results`";
$result       = $mysqli->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $results_data[$row['horse_id']] = array('race_id' => $row['race_id'], 'position' => $row['position']);
    }
}

$horse_data = array();
$sql        = "SELECT * FROM `tbl_horses` ORDER BY horse_id ASC";
$result     = $mysqli->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        
        $rating      = '';
        $rank        = '';
		$odds        = '';

		if (isset($data_data[$row['horse_name']])) {
            $rating       = $data_data[$row['horse_name']]["rating"];
			$rank 		  = $data_data[$row['horse_name']]["rank"];
			$odds		  = $data_data[$row['horse_name']]['horse_fixed_odds'];
        }

        $position = '';
		$race_id = '';
        if (isset($results_data[$row['horse_id']])) {
            $race_id     = $results_data[$row['horse_id']]['race_id'];
            $position     = $results_data[$row['horse_id']]['position'];
        }

		$horse_data[] = array(
			$race_id,
			$row['horse_name'],
			$rating,
			$rank,
			$position,
			$odds,
		);
    }
}

$total_profit = 0;
$total_loss   = 0;

$sql_raceid    = "SELECT race_id  FROM tbl_races";
$result_raceid = $mysqli->query($sql_raceid);

echo "<h2>Profit and Loss for every Race based on Rating Field</h2> <br />";


if ($result_raceid->num_rows > 0) {

    // output data of each row
    while ($row_id = $result_raceid->fetch_assoc()) {

        $temp_array = array();
        $race_data  = array_column($horse_data, 0);
        
        foreach ($race_data as $k => $r) {
            if ($row_id['race_id'] == $r) {
                $temp_array[] = $horse_data[$k];
            }
        }
        usort($temp_array, function($a, $b)
        {
            if ($a[2] === $b[2])
                return 0;
            return ($a[2] > $b[2]) ? -1 : 1;
        });
		
        if (count($temp_array) > 0) {
            $real_result = array(
                $temp_array[0],
                $temp_array[1]
            );
            $ratin       = array();
            
            if (count($real_result) > 0) {
                foreach ($real_result as $row) {
                    $ratin[]  = number_format(floatval($row[2]), 0);
                    $avgrank  = number_format(floatval($row[3]), 2);
                    $odds     = str_replace("$", "", $row[5]);
                    $position = intval($row[4]);
                    $profit = $row[4] == "" ? 0 : (($position == 1) ? ((10 * $odds) - 10) : -10);
                    $total_loss += ($profit < 0) ? 10 : 0;
                    $total_profit += $profit;
                    echo "<strong>race id:</strong> " . $row_id['race_id']  . "&nbsp&nbsp <strong>horse:</strong> " . $row[1]  . "&nbsp&nbsp <strong>Revenue:</strong> \$" . $profit . "&nbsp&nbsp&nbsp <strong>Total:</strong> \$" . $total_profit;
                    echo "<br />";
                }
            }
        }
    }
}
echo "<br />AVG Rank With Sectional Total Profit: " . $total_profit;
echo "<br/>";
echo "Avg Rank Current Winnings: " . ($total_loss );
echo "<br/>";
echo "Avg Rank Current Losses: " . ($total_loss - $total_profit);
echo "<br/><br/>";
