<?php
error_reporting( E_ALL );
include('constant.php');
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//echo $menu;

$totalprofit = 0;
$totalloss = 0;
$ratingprofit = 0;
$ratingloss = 0;

$data_data = array();
$data_id = array();
$sql = "select name, rating, pos, MIN(data.time) minimumtime,MIN(data.time2) minimumtime2 from `data` GROUP BY name ";//WHERE sectional = 0
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data_data[] = array($row['rating'], $row['minimumtime'], $row['minimumtime2'], $row['pos']);
        $data_id[] = $row['name'];
    }
}

$rankavg_data = array();
$rankavg_id = array();
$sql = "select name, avgrank from `rankavg` GROUP BY name ORDER BY avgrank DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rankavg_data[] = $row['avgrank'];
        $rankavg_id[] = $row['name'];
    }
}

$results_data = array();
$results_id = array();
$sql = "select * from `results`";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $results_data[] = $row['position'];
        $results_id[] = $row['horse'];
    }
}

$horse_data = array();
$sql = "select * from `horses` order by horse_id asc";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        
        $rating = '';
        $minimumtime = '';
        $minimumtime2 = '';
        $pos = '';
        if(in_array($row['horse_name'], $data_id)){
             $index_array = $data_data[array_search($row['horse_name'], $data_id)];
        	     $rating = $index_array[0];
             $minimumtime = $index_array[1];
             $minimumtime2 = $index_array[2];
             $pos = $index_array[3];
        }else {
             $rating = '';
             $minimumtime = '';
             $minimumtime2 = '';
             $pos = '';
        }
        
        $avg_rank = '';
        if(in_array($row['horse_name'], $rankavg_id)){
             $avg_rank = $rankavg_data[array_search($row['horse_name'], $rankavg_id)];
        }else {
             $avg_rank = '';
        }
        
        $position = '';
        if(in_array($row['horse_name'], $results_id)){
            $position = $results_data[array_search($row['horse_name'], $results_id)];
            $horse_data[] = array($row['race_id'], $row['horse_name'], $minimumtime, $minimumtime2, $rating, $avg_rank, $position, $row['horse_fixed_odds'], $pos);
        }else {
            $position = '';
        }
    }
}

$sql_raceid = "SELECT race_id  FROM races";
$result_raceid = $conn->query($sql_raceid);

if ($result_raceid->num_rows > 0) {
    // output data of each row
    while ($row_id = $result_raceid->fetch_assoc()) {
        
        $temp_array = $horse_data;
        
        
        $first_id = array_search($row_id['race_id'], array_column($horse_data, 0));
        unset($temp_array[$first_id]);
        $second_id = array_search($row_id['race_id'], array_column($temp_array, 0));
        
        $real_result = array($horse_data[$first_id], $horse_data[$second_id]);

        $ratin = array();

        if (count($real_result) > 0) {
            foreach ($real_result as $row)  {
                $ratin[] = number_format(floatval($row[5]), 0);    
                $avgrank = number_format(floatval($row[6]), 2);
                $odds = str_replace("$", "", $row[7]);
                $pos =  explode('/', $row[8]);
                $position =  intval($pos[0]);
           
                if ($position < 2) {
                    $profit = 10 * $odds - 10;
                  //  echo $profit . " Profit";
                    $totalprofit += $profit;
                } else {
                    $loss = -10;
                  //  echo $loss . " Loss";
                   
                    $totalloss += $loss;
                }
                echo "race id ".$row_id['race_id'] ."&nbsp&nbsp&nbsp won \$".  $totalprofit ."&nbsp&nbsp&nbsp Revenue " . $totalloss;
		echo "<br />";
            }
            $max_1 = $max_2 = -1;
            for($i=0; $i<count($ratin); $i++) {
                if($ratin[$i] > $max_1)
                {
                  $max_2 = $max_1;
                  $max_1 = $ratin[$i];
                }
                else if($ratin[$i] > $max_2)
                {
                  $max_2 = $ratin[$i];
                }
            }
            foreach ($real_result as $row) {
                $rating = number_format(floatval($row[4]),0);
                $odds = str_replace("$","" , $row[7]);
                
                $profitloss = "";
                $pos =  explode('/', $row[8]);
                $position =  intval($pos[0]);
                if($rating && $position > 2) {
                    if($rating > 0) {
                        
                        if($rating == $max_1 || $rating == $max_2)
                        {
                            $profitloss = 10*0-10;
			}
                        else
                        {
                            $profitloss = "";
                        }
                    }
                }
                else {
                    if($rating > 0) {
                        if($rating == $max_1 || $rating == $max_2) {
                                $pos =  explode('/', $row[8]);
                                $position =  intval($pos[0]);
                            if($position != 1) {
                                $profitloss = 10*0-10;
                            }
                            else {
                                $profitloss = 10*$odds-10;
                            }
                        }
                        else {
                            $profitloss = "";
                        }
                    }
                }
                
                if($profitloss != "") {
                    if($profitloss > 0)
                    {
                        $ratingprofit += $profitloss;
                    }
                    else {
                        $ratingloss += $profitloss;
                    }
                }
            }
        }
    }
}
echo "<br />AVG Rank Without Sectional Total Profit: " . $totalprofit;
echo "<br/>";
echo "Avg Rank Total Loss: " . $totalloss;
echo "<br/>";
echo "Avg Rank Revenue: " . ($totalprofit - -$totalloss);
echo "<br/><br/>";

echo "AVG Rank with Sectional Total Profit: " . $ratingprofit;
echo "<br/>";
echo "Rating Total Loss: " . $ratingloss;
echo "<br/>";
echo "Rating Revenue: " . ($ratingprofit - -$ratingloss);
?>
