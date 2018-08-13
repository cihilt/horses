<?php
error_reporting(E_ALL);
include('constant.php');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//echo $menu;

$totalprofit  = 0;
$totalloss    = 0;
$ratingprofit = 0;
$ratingloss   = 0;
$data_data    = array();
$data_id      = array();
$sql          = "select name, rating, pos, MIN(data.time) minimumtime,MIN(data.time2) minimumtime2 from `data` WHERE sectional = 0 GROUP BY name ";
$result       = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data_data[] = array(
            $row['rating'],
            $row['minimumtime'],
            $row['minimumtime2'],
            $row['pos']
        );
        $data_id[]   = $row['name'];
    }
}

$rankavg_data = array();
$rankavg_id   = array();
$sql          = "select name, avgrank from `rankavg` GROUP BY name ORDER BY avgrank DESC";
$result       = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rankavg_data[] = $row['avgrank'];
        $rankavg_id[]   = $row['name'];
    }
}

$results_data = array();
$results_id   = array();
$sql          = "select * from `results`";
$result       = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $results_data[] = $row['position'];
        $results_id[]   = $row['horse'];
    }
}

$horse_data = array();
$sql        = "select * from `horses` order by horse_id asc";
$result     = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        
        $rating       = '';
        $minimumtime  = '';
        $minimumtime2 = '';
        $pos          = '';
        
        if (in_array($row['horse_name'], $data_id)) {
            $index_array  = $data_data[array_search($row['horse_name'], $data_id)];
            $rating       = $index_array[0];
            $minimumtime  = $index_array[1];
            $minimumtime2 = $index_array[2];
            $pos          = $index_array[3];
        } else {
            $rating       = '';
            $minimumtime  = '';
            $minimumtime2 = '';
            $pos          = '';
        }
        
        $avg_rank = '';
        if (in_array($row['horse_name'], $rankavg_id)) {
            $avg_rank = $rankavg_data[array_search($row['horse_name'], $rankavg_id)];
        } else {
            $avg_rank = '';
        }
        
        $position = '';
        if (in_array($row['horse_name'], $results_id)) {
            $position     = $results_data[array_search($row['horse_name'], $results_id)];
            $horse_data[] = array(
                $row['race_id'],
                $row['horse_name'],
                $minimumtime,
                $minimumtime2,
                $rating,
                $avg_rank,
                $position,
                $row['horse_fixed_odds'],
                $pos
            );
        } else {
            $position = '';
        }
    }
}

$total_profit = 0;
$total_loss   = 0;

$sql_raceid    = "SELECT race_id  FROM races";
$result_raceid = $conn->query($sql_raceid);

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
            if ($a[5] === $b[5])
                return 0;
            return ($a[5] > $b[5]) ? -1 : 1;
        });

        
        if (count($temp_array) > 0) {
            $real_result = array(
                $temp_array[0],
                $temp_array[1]
            );
            $ratin       = array();
            
            if (count($real_result) > 0) {
                foreach ($real_result as $row) {
                    $ratin[]  = number_format(floatval($row[4]), 0);
                    $avgrank  = number_format(floatval($row[5]), 2);
                    $odds     = str_replace("$", "", $row[7]);
                    $position = intval($row[6]);
                    $profit = ($position == 1) ? ((10 * $odds) - 10) : -10;
                    $total_loss += ($profit < 0) ? 10 : 0;
                    $total_profit += $profit;
                    echo "race id " . $row_id['race_id']  . "&nbsp&nbsp Revenue \$" . $profit . "&nbsp&nbsp&nbsp Total \$" . $total_profit;
                    echo "<br />";
                }
            }
        }
    }
}
echo "<br />AVG Rank Without Sectional Total Profit: " . $total_profit;
echo "<br/>";
echo "Avg Rank Current Winnings: " . ($total_loss );
echo "<br/>";
echo "Avg Rank Current Losses: " . ($total_loss - $total_profit);
echo "<br/><br/>";
?>
