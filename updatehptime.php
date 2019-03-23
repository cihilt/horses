<style>
    h2 {
        margin: 20px 0 10px 0;
    }
    h3 {
        margin: 25px 0 15px 0;
    }
    h4 {
        margin: 15px 0 10px 0;
    }
</style>

<?php
include('includes/config.php');
include('includes/functions.php');
include('includes/formula_functions.php');
// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
//get the timer and secpoint
$sql_formulas = "SELECT `secpoint`,`timer`,`position_percentage` 
                 FROM `tbl_formulas` WHERE id=1";
$result_formulas = $mysqli->query($sql_formulas);
$res = mysqli_fetch_all($result_formulas, MYSQLI_ASSOC);

$secpoint = $res[0]['secpoint'];
$timer = $res[0]['timer'];
$position_percentage = $res[0]['position_percentage'];

if ( ! empty($_GET['limit'])) {
    $limit = $_GET['limit'];
} else {
    $limit = 0;
}

$race_id_var_get = isset($_GET['race_id']) && $_GET['race_id']
    ? $_GET['race_id'] : 0;

if (empty($_GET['do'])) {
    echo '<center><h2>You need an action</h2><br />';
    echo 'This page allows you to either reset and apply the formulas. We need this page because after retreiving the horses, we need to apply our algorithm. <br />
Start by selecting the raceID. <br />If you want to reset all, beware that this can take up to 1 hour, this can be done by going to the console version<br /><br />';

    $race_id_var = '';
    $race_id_var_int = isset($_POST['race_id']) && $_POST['race_id']
        ? $_POST['race_id'] : 0;
    if ($race_id_var_int) {
        $race_id_var = '&race_id='.$_POST['race_id'];
    }
    $sql_races
        = "SELECT `race_id` FROM `tbl_races` WHERE 1=1 ORDER by `race_id` ASC";
    $result_races = $mysqli->query($sql_races);
    echo '<form method="post" id="race_form">';
    echo '<select name="race_id" onchange="document.getElementById(\'race_form\').submit();">';
    echo '<option value="">Select race_id</option>';

    if ($result_races->num_rows > 0) {
        while ($result_race = $result_races->fetch_object()) {
            echo '<option value="'.$result_race->race_id.'"'.($race_id_var_int
                && $race_id_var_int == $result_race->race_id ? ' selected' : '')
                .'>'.$result_race->race_id.'</option>';
        }
    }
    ?>
    </select>
    <a href="" style="color: black;">Select RaceID</a></br>
    </form>

    <h3>Default Algorithm</h3>
    <?php
    require_once 'includes/default_algorithm.php';
    ?>

    <h3>Remove meetings by date</h3>
    <?php
    require_once 'includes/remove_meeting.php';
    ?>

    <h3>Remove selected race</h3>
    <?php
    require_once 'includes/remove_race.php';

    echo '
        <h3>Reset</h3>
        <h4>Individual formulas</h4>
        <a href="./updatehptime.php?limit=50000&do=resethandicap'.$race_id_var.'">Reset Handicap</a> | 
        <a href="./updatehptime.php?limit=50000&do=resetsectional'.$race_id_var.'">Reset Sectional</a> |  
        <a href="./updatehptime.php?limit=50000&do=resetrank'.$race_id_var.'">Reset Rank</a> | 
        <a href="./updatehptime.php?limit=50000&do=resetrating'.$race_id_var.'">Reset Rating</a><br /> 
        
        <h4>Reset in one go</h4>
        <a href="./updatehptime.php?do=reset">Rank + Rating + Sectional Average + Sectional + avgsec</a><br>
        
        <h3>Apply Individual formulas</h3>
        <a href="./updatehptime.php?limit=50000&do=sectional'.$race_id_var.'">Sectional</a> | 
        <a href="./updatehptime.php?limit=50000&do=handicap'.$race_id_var.'">Handicap</a> | 
        <a href="./updatehptime.php?limit=50000&do=rank'.$race_id_var.'">Rank</a> | 
        <a href="./updatehptime.php?limit=50000&do=sectionalavg'.$race_id_var.'">Sectional Average</a> | 
        <a href="./updatehptime.php?limit=50000&do=datarating'.$race_id_var.'">Rating</a><br />
        
        <h4>Setup the Rankings in one go</h4>
        <a href="./updatehptime.php?limit=50000&do=ranksectionalavgrating'.$race_id_var.'">Rank + Sectional Average + Rating</a></center>';

    exit;
} else {
    $action = $_GET['do'];

    if ($action === 'reset') {
        if (resetRankings($mysqli, $logger)) {
            echo 'Rankings were reset. <a href="./updatehptime.php">Click Here</a> to go back';
        } else {
            echo 'Error while reset. Please review logs for details';
        }
    }

    if ($action == 'resetsectional') {
        $sqlseq
            = "UPDATE `tbl_hist_results` SET `sectional`='0' WHERE `sectional`='-'";
        $resultseq = $mysqli->query($sqlseq);
        echo 'Sectional Resetted. <a href="./updatehptime.php">Click Here</a> to go back';
    }
    if ($action == 'sectional') {
        if ($limit == 0) {
            $getsec
                = $mysqli->query("SELECT `hist_id`, `sectional` FROM `tbl_hist_results` WHERE `sectional`!='0' AND `avgsec`=''");
        } else {
            $getsec
                = $mysqli->query("SELECT `hist_id`, `sectional` FROM `tbl_hist_results` WHERE `sectional`!='0' AND `avgsec`='' LIMIT $limit");
        }
        if ($getsec->num_rows > 0) {
            while ($getsc = $getsec->fetch_object()) {
                $sectional = explode("/", $getsc->sectional);
                if ($sectional[0] < 651) {
                    $avgvalue = $sectional[1];
                    $sqlseq
                        = $mysqli->query("UPDATE `tbl_hist_results` SET `avgsec`='$avgvalue' WHERE `hist_id`='$getsc->hist_id'");
                }
            }
            echo 'Sectional Values Done. <a href="./updatehptime.php">Click Here</a> to go back';
        } else {
            echo '0 Results. <a href="./updatehptime.php">Click Here</a> to go back';
        }
    } else {
        if ($action == 'resethandicap') {
            $sqlhand = "";
            if ($limit == 0) {
                $sqlhand
                    = "UPDATE `tbl_hist_results` SET `handicap`='0' WHERE `handicap`!='0'";
            } else {
                $sqlhand
                    = "UPDATE `tbl_hist_results` SET `handicap`='0' WHERE `handicap`!='0' ORDER BY hist_id ASC LIMIT $limit";
            }
            $resulthand = $mysqli->query($sqlhand);
            echo 'Handicap Resetted for '.$limit
                .'. <a href="./updatehptime.php">Click Here</a> to go back';
        } else {
            if ($action == 'handicap') {
                if ($limit == "0") {
                    $sqlnow
                        = "SELECT * FROM `tbl_hist_results` WHERE `handicap`='0.00'";
                } else {
                    $sqlnow
                        = "SELECT * FROM `tbl_hist_results` WHERE `handicap`='0.00' LIMIT $limit";
                }
                $hadnires = $mysqli->query($sqlnow);
                if ($hadnires->num_rows > 0) {
                    // output data of each row
                    while ($handi = $hadnires->fetch_object()) {
                        $racedet = race_details($handi->race_id);
                        $distance = round($racedet->race_distance / 100);
                        $distance = $distance * 100;
                        $newhandicap = newvalue($handi->length,
                            $racedet->race_distance, $distance,
                            $handi->horse_position,
                            number_format($handi->race_time, 2));
                        $newhandi = number_format($newhandicap, 3);

                        $id = $handi->hist_id;
                        // $newhandicap = newvalue($row["length"], $row["original_distance"], $row["distance"], $row["pos"], number_format($row["minimumtime"],2));
                        $updatehptime
                            = "UPDATE `tbl_hist_results` SET `handicap`=$newhandi WHERE hist_id = $id";
                        echo $updatehptime."<br>";
                        echo "-------------------"."<br>";
                        $result2 = $mysqli->query($updatehptime);
                    }
                    echo 'Your Action has been completed. <a href="./updatehptime.php">Click Here</a> to go back';
                } else {
                    echo '0 results. <a href="./updatehptime.php">Click Here</a> to go back';
                }
            } else {
                if ($action == 'resetrank') {
                    //Query to update the rank avg
                    if ($limit == "0") {
                        $sql7
                            = "Update `tbl_hist_results` SET `rank`='0.00' WHERE `rank`!='0.00'"; //Resetting Rank
                    } else {
                        $sql7
                            = "Update `tbl_hist_results` SET `rank`='0.00' WHERE `rank`!='0.00' LIMIT $limit";
                    }
                    $mysqli->query("UPDATE `tbl_races` SET `rank_status`='0' WHERE `rank_status`!='0'");
                    $result7 = $mysqli->query($sql7);
                    echo 'Rank Resetted. <a href="./updatehptime.php">Click Here</a> to go back';
                } else {
                    if ($action == 'rank') {
                        if ($race_id_var_get) {
                            $sql2
                                = "SELECT `race_id` FROM `tbl_races` WHERE `race_id`='$race_id_var_get' ORDER by `race_id` ASC";
                        } else {
                            if ($limit == "0") {
                                $sql2
                                    = "SELECT `race_id` FROM `tbl_races` WHERE `rank_status`='0' ORDER by `race_id` ASC";
                            } else {
                                $sql2
                                    = "SELECT `race_id` FROM `tbl_races` WHERE `rank_status`='0' ORDER by `race_id` ASC LIMIT $limit";
                            }
                        }
                        $raceres = $mysqli->query($sql2);
                        if ($raceres->num_rows > 0) {
                            // output data of each row
                            while ($rowrc = $raceres->fetch_object()) {
                                $countofhorses
                                    = get_rows("`tbl_temp_hraces` WHERE `race_id`='$rowrc->race_id' AND `horse_fxodds`!='0'");
                                echo 'All below results are for Race ID: '
                                    .$rowrc->race_id.'<br /><br />';
                                $get_distances
                                    = $mysqli->query("SELECT DISTINCT CAST(race_distance AS UNSIGNED) AS racedist FROM tbl_hist_results WHERE `race_id`='$rowrc->race_id' ORDER by racedist ASC");
                                $updaterankavg = "";
                                while ($dists
                                    = $get_distances->fetch_object()) {
                                    // echo '<b>$dists->racedist</b>: '.$dists->racedist.'<br>';
                                    $handitotal = get_handisum($rowrc->race_id,
                                        $dists->racedist);
                                    //echo $dists->racedist . ' ( ' . $handitotal . ' )<br />';
                                    $numbersarray
                                        = get_array_of_handicap($rowrc->race_id,
                                        $dists->racedist);
                                    $cnt = count($numbersarray);

                                    $get_unique
                                        = $mysqli->query("SELECT DISTINCT `horse_id` FROM `tbl_hist_results` WHERE `race_id`='$rowrc->race_id' AND `race_distance`='$dists->racedist'");
                                    $i = 1;
                                    while ($ghorse
                                        = $get_unique->fetch_object()) {
                                        $checkodds
                                            = $mysqli->query("SELECT * FROM `tbl_temp_hraces` WHERE `race_id`='$rowrc->race_id' AND `horse_id`='$ghorse->horse_id'");
                                        $goddds = $checkodds->fetch_object();
                                        if (isset($goddds->horse_fxodds)
                                            && $goddds->horse_fxodds != "0"
                                        ) {
                                            $get_hist
                                                = $mysqli->query("SELECT MIN(handicap) as minihandi FROM `tbl_hist_results` WHERE `race_id`='$rowrc->race_id' AND `race_distance`='$dists->racedist' AND `horse_id`='$ghorse->horse_id'");
                                            // echo '<b>$get_hist</b>: ' . "SELECT MIN(handicap) as minihandi FROM `tbl_hist_results` WHERE `race_id`='$rowrc->race_id' AND `race_distance`='$dists->racedist' AND `horse_id`='$ghorse->horse_id'".'<br>';
                                            while ($shandi
                                                = $get_hist->fetch_object()) {
                                                // echo '<b>$shandi->minihandi</b>: '.$shandi->minihandi.'<br>';
                                                // echo '<b>$countofhorses</b>: '.$countofhorses.'<br>';
                                                if ($countofhorses > 0) {
                                                    $per = ($cnt
                                                            / $countofhorses)
                                                        * 100;
                                                    // echo '<b>$per</b>: '.$per.'<br><br>';
                                                    if ($per
                                                        > $position_percentage
                                                    ) {
                                                        $genrank
                                                            = generate_rank($shandi->minihandi,
                                                            $numbersarray);
                                                        // echo '<b>$genrank</b>: '.$genrank.'<br>';
                                                        $updaterankavg
                                                            = "UPDATE `tbl_hist_results` SET `rank`='$genrank' WHERE `race_id`='$rowrc->race_id' AND `race_distance`= '$dists->racedist' AND `horse_id`='$ghorse->horse_id'";
                                                        if ($mysqli->query($updaterankavg)) {
                                                            echo $updaterankavg
                                                                ."<br>";
                                                            echo "-------------------"
                                                                ."<br>";
                                                        }
                                                    }
                                                }
                                            }
                                            ++$i;
                                        }
                                    }
                                }
                                if ($updaterankavg) {
                                    $mysqli->query("UPDATE `tbl_races` SET `rank_status`='1' WHERE `race_id`='$rowrc->race_id'");
                                }
                            }
                            echo '<h3>Your Action has been completed for Races. <a href="./updatehptime.php">Click Here</a> to go back</h3>';
                        } else {
                            echo '0 Results. <a href="./updatehptime.php">Click Here</a> to go back';
                        }
                    } else {
                        if ($action == 'resetrating') {
                            //Update rating using the avg sectional formula
                            //$sql5 = "SELECT *  FROM `maxsectional` LEFT JOIN sec_avg_data ON sec_avg_data.race_id = maxsectional.race_id AND sec_avg_data.distance = maxsectional.distance";
                            if ($limit == 0) {
                                $sql6
                                    = "Update `tbl_hist_results` SET `rating`='0'"; //Resetting Rating
                            } else {
                                $sql6
                                    = "Update `tbl_hist_results` SET `rating`='0' LIMIT $limit";
                            }
                            $result6 = $mysqli->query($sql6);
                            echo 'Rating Resetted. <a href="./updatehptime.php">Click Here</a> to go back';
                        } else {
                            if ($action == 'sectionalavg') {
                                if ($race_id_var_get) {
                                    $sql2
                                        = "SELECT `race_id` FROM `tbl_races` WHERE `race_id`='$race_id_var_get'";
                                } else {
                                    if ($limit == "0") {
                                        $sql2
                                            = "SELECT `race_id` FROM `tbl_races` WHERE `sec_status`='0' OR `sec_status`='' ORDER by `race_id` ASC";
                                    } else {
                                        $sql2
                                            = "SELECT `race_id` FROM `tbl_races` WHERE `sec_status`='0' OR `sec_status`='' ORDER by `race_id` ASC LIMIT $limit";
                                    }
                                }
                                $raceres = $mysqli->query($sql2);
                                if ($raceres->num_rows > 0) {
                                    // output data of each row
                                    while ($rowrc = $raceres->fetch_object()) {
                                        $countofhorses
                                            = get_rows("`tbl_temp_hraces` WHERE `race_id`='$rowrc->race_id' AND `horse_fxodds`!='0'");
                                        echo 'All below results are for Race ID: '
                                            .$rowrc->race_id.'<br /><br />';
                                        $get_distances
                                            = $mysqli->query("SELECT DISTINCT CAST(race_distance AS UNSIGNED) AS racedist FROM tbl_hist_results WHERE `race_id`='$rowrc->race_id' ORDER by racedist ASC");
                                        $updateavgsec = "";
                                        while ($dists
                                            = $get_distances->fetch_object()) {
                                            $secttotal
                                                = get_sectionalsum($rowrc->race_id,
                                                $dists->racedist);
                                            //echo $dists->racedist . ' ( ' . $handitotal . ' )<br />';
                                            $numbersarray
                                                = get_array_of_avgsec($rowrc->race_id,
                                                $dists->racedist);
                                            $cnt = count($numbersarray);

                                            $get_unique
                                                = $mysqli->query("SELECT DISTINCT `horse_id` FROM `tbl_hist_results` WHERE `race_id`='$rowrc->race_id' AND `race_distance`='$dists->racedist'");
                                            $i = 1;
                                            while ($ghorse
                                                = $get_unique->fetch_object()) {
                                                $checkodds
                                                    = $mysqli->query("SELECT * FROM `tbl_temp_hraces` WHERE `race_id`='$rowrc->race_id' AND `horse_id`='$ghorse->horse_id'");
                                                $goddds
                                                    = $checkodds->fetch_object();
                                                if ($goddds->horse_fxodds
                                                    != "0"
                                                ) {
                                                    $get_hist
                                                        = $mysqli->query("SELECT MAX(avgsec) AS secavg FROM `tbl_hist_results` WHERE `race_id`='$rowrc->race_id' AND `race_distance`='$dists->racedist' AND `horse_id`='$ghorse->horse_id'");
                                                    while ($ssect
                                                        = $get_hist->fetch_object()) {
                                                        $per = ($cnt
                                                                / $countofhorses)
                                                            * 100;
                                                        if ($per
                                                            > $position_percentage
                                                        ) {
                                                            $genavgsec
                                                                = generate_avgsectional($ssect->secavg,
                                                                $numbersarray);
                                                            $updateavgsec
                                                                = "UPDATE `tbl_hist_results` SET `avgsectional`='$genavgsec' WHERE `race_id`='$rowrc->race_id' AND `race_distance`= '$dists->racedist' AND `horse_id`='$ghorse->horse_id'";
                                                            if ($mysqli->query($updateavgsec)) {
                                                                echo $updateavgsec
                                                                    ."<br>";
                                                                echo "-------------------"
                                                                    ."<br>";
                                                            }
                                                        }
                                                    }
                                                    ++$i;
                                                }
                                            }
                                        }
                                        if ($updateavgsec) {
                                            $mysqli->query("UPDATE `tbl_races` SET `sec_status`='1' WHERE `race_id`='$rowrc->race_id'");
                                        }
                                    }
                                    echo '<h3>Your Action has been completed for Sectional. <a href="./updatehptime.php">Click Here</a> to go back</h3>';
                                } else {
                                    echo '0 Results. <a href="./updatehptime.php">Click Here</a> to go back';
                                }
                            } else {
                                if ($action == 'datarating') {
                                    if ($race_id_var_get) {
                                        $sqldatarat
                                            = "SELECT * FROM `tbl_hist_results` WHERE `rating`='0' AND `race_id`='$race_id_var_get'";
                                    } else {
                                        if ($limit == "0") {
                                            $sqldatarat
                                                = "SELECT * FROM `tbl_hist_results` WHERE `rating`='0'";
                                        } else {
                                            $sqldatarat
                                                = "SELECT * FROM `tbl_hist_results` WHERE `rating`='0' LIMIT $limit";
                                        }
                                    }
                                    $datarat = $mysqli->query($sqldatarat);
                                    if ($datarat->num_rows > 0) {
                                        while ($ratin
                                            = $datarat->fetch_object()) {
                                            echo '<br>$ratin->avgsectional: '
                                                .$ratin->avgsectional.'<br>';
                                            echo '$ratin->rank: '.$ratin->rank
                                                .'<br>';
                                            echo '$ratin->hist_id: '
                                                .$ratin->hist_id.'<br>';
                                            if ($ratin->avgsectional != "0"
                                                || $ratin->rank != "0"
                                            ) {
                                                // echo $ratin->avgsectional;
                                                $ratepos = $ratin->avgsectional
                                                    + $ratin->rank;
                                                $updaterankavg1
                                                    = $mysqli->query("UPDATE `tbl_hist_results` SET `rating`='$ratepos' WHERE `hist_id`= '$ratin->hist_id'");
                                                echo 'Rating Done for: '
                                                    .$ratin->hist_id.'<br />';
                                            }
                                        }
                                        echo 'Your Action has been completed. <a href="./updatehptime.php">Click Here</a> to go back';
                                    } else {
                                        echo '0 Results. <a href="./updatehptime.php">Click Here</a> to go back';
                                    }
                                } else {
                                    if ($action == 'ranksectionalavgrating') {
                                        // rank
                                        if ($race_id_var_get) {
                                            $sql2 = "SELECT `race_id` FROM `tbl_races` WHERE `race_id`='$race_id_var_get' ORDER by `race_id` ASC";
                                        } else {
                                            if($limit == "0") {
                                                $sql2 = "SELECT `race_id` FROM `tbl_races` WHERE `rank_status`='0' ORDER by `race_id` ASC";
                                            } else {
                                                $sql2 = "SELECT `race_id` FROM `tbl_races` WHERE `rank_status`='0' ORDER by `race_id` ASC LIMIT $limit";
                                            }
                                        }
                                        $raceres = $mysqli->query($sql2);
                                        if ($raceres->num_rows > 0) {
                                            // output data of each row
                                            while ($rowrc = $raceres->fetch_object()) {
                                                $countofhorses = get_rows("`tbl_temp_hraces` WHERE `race_id`='$rowrc->race_id' AND `horse_fxodds`!='0'");
                                                echo 'All below results are for Race ID: ' . $rowrc->race_id . '<br /><br />';
                                                $get_distances = $mysqli->query("SELECT DISTINCT CAST(race_distance AS UNSIGNED) AS racedist FROM tbl_hist_results WHERE `race_id`='$rowrc->race_id' ORDER by racedist ASC");
                                                $updaterankavg = "";
                                                while($dists = $get_distances->fetch_object()) {
                                                    // echo '<b>$dists->racedist</b>: '.$dists->racedist.'<br>';
                                                    $handitotal = get_handisum($rowrc->race_id, $dists->racedist);
                                                    //echo $dists->racedist . ' ( ' . $handitotal . ' )<br />';
                                                    $numbersarray = get_array_of_handicap($rowrc->race_id, $dists->racedist);
                                                    $cnt = count($numbersarray);

                                                    $get_unique = $mysqli->query("SELECT DISTINCT `horse_id` FROM `tbl_hist_results` WHERE `race_id`='$rowrc->race_id' AND `race_distance`='$dists->racedist'");
                                                    $i = 1;
                                                    while($ghorse = $get_unique->fetch_object()) {
                                                        $checkodds = $mysqli->query("SELECT * FROM `tbl_temp_hraces` WHERE `race_id`='$rowrc->race_id' AND `horse_id`='$ghorse->horse_id'");
                                                        $goddds = $checkodds->fetch_object();
                                                        if(isset($goddds->horse_fxodds) && $goddds->horse_fxodds != "0") {
                                                            $get_hist = $mysqli->query("SELECT MIN(handicap) as minihandi FROM `tbl_hist_results` WHERE `race_id`='$rowrc->race_id' AND `race_distance`='$dists->racedist' AND `horse_id`='$ghorse->horse_id'");
                                                            // echo '<b>$get_hist</b>: ' . "SELECT MIN(handicap) as minihandi FROM `tbl_hist_results` WHERE `race_id`='$rowrc->race_id' AND `race_distance`='$dists->racedist' AND `horse_id`='$ghorse->horse_id'".'<br>';
                                                            while($shandi = $get_hist->fetch_object()) {
                                                                // echo '<b>$shandi->minihandi</b>: '.$shandi->minihandi.'<br>';
                                                                // echo '<b>$countofhorses</b>: '.$countofhorses.'<br>';
                                                                if($countofhorses > 0) {
                                                                    $per = ($cnt / $countofhorses) * 100;
                                                                    // echo '<b>$per</b>: '.$per.'<br><br>';
                                                                    if ($per > $position_percentage) {
                                                                        $genrank = generate_rank($shandi->minihandi, $numbersarray);
                                                                        // echo '<b>$genrank</b>: '.$genrank.'<br>';
                                                                        $updaterankavg = "UPDATE `tbl_hist_results` SET `rank`='$genrank' WHERE `race_id`='$rowrc->race_id' AND `race_distance`= '$dists->racedist' AND `horse_id`='$ghorse->horse_id'";
                                                                        if($mysqli->query($updaterankavg)) {
                                                                            echo $updaterankavg . "<br>";
                                                                            echo "-------------------" . "<br>";
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                            ++$i;
                                                        }
                                                    }
                                                }
                                                if($updaterankavg) {
                                                    $mysqli->query("UPDATE `tbl_races` SET `rank_status`='1' WHERE `race_id`='$rowrc->race_id'");
                                                }
                                            }
                                            echo '<h3>Your Action has been completed for Races. <a href="./updatehptime.php">Click Here</a> to go back</h3>';
                                        }
                                        else { echo '0 Results. <a href="./updatehptime.php">Click Here</a> to go back'; }
                                        // .rank
                                        // sectionalavg
                                        if ($race_id_var_get) {
                                            $sql2 = "SELECT `race_id` FROM `tbl_races` WHERE `race_id`='$race_id_var_get'";
                                        } else {
                                            if($limit == "0") {
                                                $sql2 = "SELECT `race_id` FROM `tbl_races` WHERE `sec_status`='0' OR `sec_status`='' ORDER by `race_id` ASC";
                                            } else {
                                                $sql2 = "SELECT `race_id` FROM `tbl_races` WHERE `sec_status`='0' OR `sec_status`='' ORDER by `race_id` ASC LIMIT $limit";
                                            }
                                        }
                                        $raceres = $mysqli->query($sql2);
                                        if ($raceres->num_rows > 0) {
                                            // output data of each row
                                            while ($rowrc = $raceres->fetch_object()) {
                                                $countofhorses = get_rows("`tbl_temp_hraces` WHERE `race_id`='$rowrc->race_id' AND `horse_fxodds`!='0'");
                                                echo 'All below results are for Race ID: ' . $rowrc->race_id . '<br /><br />';
                                                $get_distances = $mysqli->query("SELECT DISTINCT CAST(race_distance AS UNSIGNED) AS racedist FROM tbl_hist_results WHERE `race_id`='$rowrc->race_id' ORDER by racedist ASC");
                                                $updateavgsec = "";
                                                while($dists = $get_distances->fetch_object()) {
                                                    $secttotal = get_sectionalsum($rowrc->race_id, $dists->racedist);
                                                    //echo $dists->racedist . ' ( ' . $handitotal . ' )<br />';
                                                    $numbersarray = get_array_of_avgsec($rowrc->race_id, $dists->racedist);
                                                    $cnt = count($numbersarray);

                                                    $get_unique = $mysqli->query("SELECT DISTINCT `horse_id` FROM `tbl_hist_results` WHERE `race_id`='$rowrc->race_id' AND `race_distance`='$dists->racedist'");
                                                    $i = 1;
                                                    while($ghorse = $get_unique->fetch_object()) {
                                                        $checkodds = $mysqli->query("SELECT * FROM `tbl_temp_hraces` WHERE `race_id`='$rowrc->race_id' AND `horse_id`='$ghorse->horse_id'");
                                                        $goddds = $checkodds->fetch_object();
                                                        if($goddds->horse_fxodds != "0") {
                                                            $get_hist = $mysqli->query("SELECT MAX(avgsec) AS secavg FROM `tbl_hist_results` WHERE `race_id`='$rowrc->race_id' AND `race_distance`='$dists->racedist' AND `horse_id`='$ghorse->horse_id'");
                                                            while($ssect = $get_hist->fetch_object()) {
                                                                $per = ($cnt / $countofhorses) * 100;
                                                                if ($per > $position_percentage) {
                                                                    $genavgsec = generate_avgsectional($ssect->secavg, $numbersarray);
                                                                    $updateavgsec = "UPDATE `tbl_hist_results` SET `avgsectional`='$genavgsec' WHERE `race_id`='$rowrc->race_id' AND `race_distance`= '$dists->racedist' AND `horse_id`='$ghorse->horse_id'";
                                                                    if($mysqli->query($updateavgsec)) {
                                                                        echo $updateavgsec . "<br>";
                                                                        echo "-------------------" . "<br>";
                                                                    }
                                                                }
                                                            }
                                                            ++$i;
                                                        }
                                                    }
                                                }
                                                if($updateavgsec) {
                                                    $mysqli->query("UPDATE `tbl_races` SET `sec_status`='1' WHERE `race_id`='$rowrc->race_id'");
                                                }
                                            }
                                            echo '<h3>Your Action has been completed for Sectional. <a href="./updatehptime.php">Click Here</a> to go back</h3>';
                                        }
                                        else { echo '0 Results. <a href="./updatehptime.php">Click Here</a> to go back'; }
                                        // .sectionalavg
                                        // rating
                                        if ($race_id_var_get) {
                                            $sqldatarat = "SELECT * FROM `tbl_hist_results` WHERE `rating`='0' AND `race_id`='$race_id_var_get'";
                                        } else {
                                            if($limit == "0") {
                                                $sqldatarat = "SELECT * FROM `tbl_hist_results` WHERE `rating`='0'";
                                            }
                                            else {
                                                $sqldatarat = "SELECT * FROM `tbl_hist_results` WHERE `rating`='0' LIMIT $limit";
                                            }
                                        }
                                        $datarat = $mysqli->query($sqldatarat);
                                        if($datarat->num_rows > 0) {
                                            while($ratin = $datarat->fetch_object()) {
                                                echo '<br>$ratin->avgsectional: ' . $ratin->avgsectional.'<br>';
                                                echo '$ratin->rank: ' . $ratin->rank.'<br>';
                                                echo '$ratin->hist_id: ' . $ratin->hist_id.'<br>';
                                                if($ratin->avgsectional != "0" || $ratin->rank != "0") {
                                                    // echo $ratin->avgsectional;
                                                    $ratepos = $ratin->avgsectional + $ratin->rank;
                                                    $updaterankavg1 = $mysqli->query("UPDATE `tbl_hist_results` SET `rating`='$ratepos' WHERE `hist_id`= '$ratin->hist_id'");
                                                    echo 'Rating Done for: ' . $ratin->hist_id . '<br />';
                                                }
                                            }
                                            echo 'Your Action has been completed. <a href="./updatehptime.php">Click Here</a> to go back';
                                        }
                                        else {
                                            echo '0 Results. <a href="./updatehptime.php">Click Here</a> to go back';
                                        }
                                        // .rating
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
