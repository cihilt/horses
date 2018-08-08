<?php
error_reporting( E_ALL );
include('constant.php');
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//echo $menu;

$sql1 = "SELECT *  FROM races";
$result1 = $conn->query($sql1);
$totalprofit = 0;
$totalloss = 0;
$ratingprofit = 0;
$ratingloss = 0;

if ($result1->num_rows > 0) {
    // output data of each row
    while ($row = $result1->fetch_assoc()) {
        $raceid = $row['race_id'];

        $sql = "select *, MIN(data.time) minimumtime,MIN(data.time2) minimumtime2,rating rat 
                from `horses` a
                
                
                LEFT JOIN data ON a.horse_name = data.name 
                
                
                LEFT JOIN rankavg ON a.horse_name = rankavg.name 
                LEFT JOIN results ON results.horse = a.horse_name
                WHERE a.race_id = $raceid
                GROUP BY horse_name ORDER BY avgrank DESC
                LIMIT 0,2";
        $result = $conn->query($sql);
        $ratin = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $ratin[] = number_format($row['rating'], 0);    //
                $avgrank = number_format($row['avgrank'], 2);
                $odds = str_replace("$", "", $row["horse_fixed_odds"]);
                // echo $row['horse_name'] . " " . $raceid . " ";
                $pos =  explode('/', $row['pos']);
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
              //  echo "<br/>";
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
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $rating = number_format($row["rating"],0);
                    $odds = str_replace("$","" , $row["horse_fixed_odds"]);
                    
                    $profitloss = "";
                        $pos =  explode('/', $row['pos']);
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
                                $pos =  explode('/', $row['pos']);
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
