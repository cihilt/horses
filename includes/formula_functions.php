<?php

if (!isset($logger)) {
    $logger = new $logger->log(); 
}

function udpatehptime($mysqli, $position_percentage, $raceId = 0, $limit = 0)
{
    global $logger;
    $logger->log('Started: '. __FUNCTION__);

    if ($raceId) {
        $q = "SELECT `race_id` 
              FROM `tbl_races` 
              WHERE `race_id`='$raceId' 
              ORDER by `race_id` ASC";
    } else {
        if ($limit == "0") {
            $q = "SELECT `race_id` 
                  FROM `tbl_races` 
                  WHERE `rank_status`='0' 
                  ORDER by `race_id` ASC";
        } else {
            $q = "SELECT `race_id` 
                  FROM `tbl_races` 
                  WHERE `rank_status`='0' 
                  ORDER by `race_id` ASC LIMIT $limit";
        }
    }
    $races = $mysqli->query($q);

    // rank
    if ($races->num_rows > 0) {
        while ($race = $races->fetch_object()) {
            $horsesCount = get_rows(
                "`tbl_temp_hraces` 
                WHERE `race_id`='$race->race_id' 
                AND `horse_fxodds`!='0'"
            );

            $logger->log('All below results are for Race ID: '
                .$race->race_id);

            $distances = $mysqli->query(
                "SELECT DISTINCT CAST(race_distance AS UNSIGNED) AS racedist 
                 FROM tbl_hist_results 
                 WHERE `race_id`='$race->race_id' 
                 ORDER by racedist ASC");

            $updateQuery = "";
            while ($distance = $distances->fetch_object()) {
                $numsArray = get_array_of_handicap(
                    $race->race_id,
                    $distance->racedist
                );
                $cnt = count($numsArray);

                $horsesHistResult = $mysqli->query(
                    "SELECT DISTINCT `horse_id` 
                     FROM `tbl_hist_results` 
                     WHERE `race_id`='$race->race_id' 
                     AND `race_distance`='$distance->racedist'"
                );

                $i = 1;
                while ($horse = $horsesHistResult->fetch_object()) {
                    $oddsResult = $mysqli->query(
                        "SELECT * FROM `tbl_temp_hraces` 
                         WHERE `race_id`='$race->race_id' 
                         AND `horse_id`='$horse->horse_id'"
                    );
                    $odds = $oddsResult->fetch_object();

                    if (isset($odds->horse_fxodds)
                        && $odds->horse_fxodds != "0"
                    ) {
                        $handicapResults = $mysqli->query(
                            "SELECT MIN(handicap) as minihandi 
                             FROM `tbl_hist_results` 
                             WHERE `race_id`='$race->race_id' 
                             AND `race_distance`='$distance->racedist' 
                             AND `horse_id`='$horse->horse_id'"
                        );

                        while ($row = $handicapResults->fetch_object()) {
                            if ($horsesCount > 0) {
                                $per = ($cnt / $horsesCount) * 100;

                                if ($per > $position_percentage) {
                                    $rank = generate_rank(
                                        $row->minihandi,
                                        $numsArray
                                    );

                                    $updateQuery = "UPDATE `tbl_hist_results` 
                                                    SET `rank`='$rank' 
                                                    WHERE `race_id`='$race->race_id' 
                                                    AND `race_distance`= '$distance->racedist' 
                                                    AND `horse_id`='$horse->horse_id'";

                                    if ($mysqli->query($updateQuery)) {
                                        $logger->log($updateQuery, 'debug');
                                    } else {
                                        $logger->log($mysqli->error, 'error');
                                    }
                                }
                            }
                        }
                        ++$i;
                    }
                }
            }
            if ($updateQuery) {
                $q = "UPDATE `tbl_races` 
                     SET `rank_status`='1' 
                     WHERE `race_id`='$race->race_id'";

                if($mysqli->query($q)) {
                    $logger->log($q, 'debug');
                } else {
                    $logger->log($mysqli->error, 'error');
                }
            }
        }
        $logger->log('Action has been completed for Rank');
    } else {
        $logger->log('Rank: 0 results');
    }

    // sectional avg
    if ($raceId) {
        $q = "SELECT `race_id` FROM `tbl_races` WHERE `race_id`='$raceId'";
    } else {
        if ($limit == "0") {
            $q = "SELECT `race_id` 
                  FROM `tbl_races` 
                  WHERE `sec_status`='0' 
                  OR `sec_status`='' 
                  ORDER by `race_id` ASC";
        } else {
            $q = "SELECT `race_id` 
                  FROM `tbl_races` 
                  WHERE `sec_status`='0' 
                  OR `sec_status`='' 
                  ORDER by `race_id` 
                  ASC LIMIT $limit";
        }
    }
    $races = $mysqli->query($q);
    if ($races->num_rows > 0) {
        while ($race = $races->fetch_object()) {
            $horsesCount = get_rows(
                "`tbl_temp_hraces` WHERE `race_id`='$race->race_id' AND `horse_fxodds`!='0'"
            );

            $logger->log('All below results are for Race ID: '.$race->race_id);

            $distances = $mysqli->query(
                "SELECT DISTINCT CAST(race_distance AS UNSIGNED) AS racedist 
                 FROM tbl_hist_results 
                 WHERE `race_id`='$race->race_id' 
                 ORDER by racedist ASC"
            );

            $updateQuery = "";
            while ($distance = $distances->fetch_object()) {
                $numsArray = get_array_of_avgsec($race->race_id,
                    $distance->racedist);
                $cnt = count($numsArray);

                $horsesHistResult = $mysqli->query(
                    "SELECT DISTINCT `horse_id` 
                     FROM `tbl_hist_results` 
                     WHERE `race_id`='$race->race_id' 
                     AND `race_distance`='$distance->racedist'"
                );

                $i = 1;
                while ($horse = $horsesHistResult->fetch_object()) {
                    $oddsResult = $mysqli->query(
                        "SELECT * FROM `tbl_temp_hraces` 
                         WHERE `race_id`='$race->race_id' 
                         AND `horse_id`='$horse->horse_id'"
                    );
                    $odds = $oddsResult->fetch_object();

                    if ($odds->horse_fxodds != "0") {
                        $handicapResults = $mysqli->query(
                            "SELECT MAX(avgsec) AS secavg 
                             FROM `tbl_hist_results` 
                             WHERE `race_id`='$race->race_id' 
                             AND `race_distance`='$distance->racedist' 
                             AND `horse_id`='$horse->horse_id'"
                        );

                        while ($row = $handicapResults->fetch_object()) {
                            $per = ($cnt / $horsesCount) * 100;

                            if ($per > $position_percentage) {
                                $avgSectional = generate_avgsectional(
                                    $row->secavg,
                                    $numsArray
                                );
                                $updateQuery = "UPDATE `tbl_hist_results` 
                                                 SET `avgsectional`='$avgSectional' 
                                                 WHERE `race_id`='$race->race_id' 
                                                 AND `race_distance`= '$distance->racedist' 
                                                 AND `horse_id`='$horse->horse_id'";

                                if ($mysqli->query($updateQuery)) {
                                    $logger->log($updateQuery, 'debug');
                                } else {
                                    $logger->log($mysqli->error, 'error');
                                }
                            }
                        }
                        ++$i;
                    }
                }
            }
            if ($updateQuery) {
                $q = "UPDATE `tbl_races` 
                     SET `sec_status`='1' 
                     WHERE `race_id`='$race->race_id'";

                if($mysqli->query($q)) {
                    $logger->log($q, 'debug');
                } else {
                    $logger->log($mysqli->error, 'error');
                }
            }
        }
        $logger->log('Action has been completed for Sectional AVG');
    } else {
        $logger->log('Sectional AVG: 0 results');
    }

    // rating
    if ($raceId) {
        $q = "SELECT * FROM `tbl_hist_results` 
              WHERE `rating`='0' AND `race_id`='$raceId'";
    } else {
        if ($limit == "0") {
            $q = "SELECT * FROM `tbl_hist_results` 
                  WHERE `rating`='0'";
        } else {
            $q = "SELECT * FROM `tbl_hist_results` 
                  WHERE `rating`='0' LIMIT $limit";
        }
    }

    $results = $mysqli->query($q);
    if ($results->num_rows > 0) {
        while ($row = $results->fetch_object()) {
            $logMessage = 'avgsectional: '.$row->avgsectional.PHP_EOL;
            $logMessage .= 'rank: '.$row->rank.PHP_EOL;
            $logMessage .= 'hist_id: '.$row->hist_id;
            $logger->log($logMessage);

            if ($row->avgsectional != "0" || $row->rank != "0") {
                $ratePos = $row->avgsectional + $row->rank;
                $q = "UPDATE `tbl_hist_results` 
                     SET `rating`='$ratePos' 
                     WHERE `hist_id`= '$row->hist_id'";
                if ($mysqli->query($q)) {
                    $logger->log($q, 'debug');
                } else {
                    $logger->log($mysqli->error, 'error');
                }
                $logger->log('Rating Done for: '.$row->hist_id);
            }
        }
        $logger->log('Action has been completed for Rating');
    } else {
        $logger->log('Rating: 0 results');
    }

    $logger->log('Finished: '. __FUNCTION__);
}

function distance_new($mysqli, $position_percentage, $raceId, $distance = 0)
{
    global $logger;
    $logger->log('Started: '. __FUNCTION__);

    function rank($value, $array, $order = 0)
    {
        array_unique($array);

        if ($order) {
            sort($array);
        } else {
            rsort($array);
        }

        return array_search($value, $array) + 1;
    }

    $q = "SELECT `race_id` FROM `tbl_races` 
             WHERE `race_id`='$raceId' ORDER by `race_id` ASC";
    $races = $mysqli->query($q);

    if ($races->num_rows > 0) {
        while ($race = $races->fetch_object()) {
            $horsesCount = get_rows(
                "`tbl_temp_hraces` 
                 WHERE `race_id`='$race->race_id' 
                 AND `horse_fxodds`!='0'"
            );

            $logger->log('All below results are for Race ID: ' . $race->race_id);

            $distancesResult = $mysqli->query(
                "SELECT DISTINCT CAST(race_distance AS UNSIGNED) AS racedist 
                 FROM tbl_hist_results 
                 WHERE `race_id`='$race->race_id' 
                 AND `race_distance`='$distance' 
                 ORDER by racedist ASC"
            );

            $updateQuery = "";
            while($distance = $distancesResult->fetch_object()) {
                $numsArray = get_array_of_handicap($race->race_id, $distance->racedist);
                $cnt = count($numsArray);
                $horsesHistResult = $mysqli->query(
                    "SELECT DISTINCT `horse_id` 
                     FROM `tbl_hist_results` 
                     WHERE `race_id`='$race->race_id' 
                     AND `race_distance`='$distance->racedist'"
                );

                $i = 1;
                while($horseHist = $horsesHistResult->fetch_object()) {
                    $odds = $mysqli->query(
                        "SELECT * FROM `tbl_temp_hraces` 
                         WHERE `race_id`='$race->race_id' 
                         AND `horse_id`='$horseHist->horse_id'"
                    );
                    $oddsResult = $odds->fetch_object();

                    if(isset($oddsResult->horse_fxodds)
                        && $oddsResult->horse_fxodds != "0") {
                        $handicapResult = $mysqli->query(
                            "SELECT MIN(handicap) as minihandi 
                             FROM `tbl_hist_results` 
                             WHERE `race_id`='$race->race_id' 
                             AND `race_distance`='$distance->racedist' 
                             AND `horse_id`='$horseHist->horse_id'"
                        );

                        while($handicap = $handicapResult->fetch_object()) {
                            if($horsesCount > 0) {
                                $per = ($cnt / $horsesCount) * 100;

                                if ($per > $position_percentage) {
                                    // get rank
                                    $rank = rank(
                                        $handicap->minihandi,
                                        $numsArray
                                    );

                                    if($rank) {
                                        $horseDetails = horse_details($horseHist->horse_id);
                                        $logger->log($horseDetails->horse_name." rank: $rank");
                                    }

                                    $updateQuery =
                                        "UPDATE `tbl_hist_results` 
                                         SET `rank`='$rank' 
                                         WHERE `race_id`='$race->race_id' 
                                         AND `race_distance`= '$distance->racedist' 
                                         AND `horse_id`='$horseHist->horse_id'";

                                    if ($mysqli->query($updateQuery)) {
                                        $logger->log($updateQuery, 'debug');
                                    } else {
                                        $logger->log($mysqli->error, 'error');
                                    }
                                }
                            }
                        }
                        ++$i;
                    }
                }
            }
            if($updateQuery) {
                $q = "UPDATE `tbl_races` 
                      SET `rank_status`='1' 
                      WHERE `race_id`='$race->race_id'";

                if($mysqli->query($q)) {
                    $logger->log($q, 'debug');
                } else {
                    $logger->log($mysqli->error, 'error');
                }
            }
        }
        $logger->log('Action has been completed for distance_new');
    } else {
        $logger->log('Rating: 0 results');
    }
    $logger->log('Finished: '. __FUNCTION__);
}

if (!function_exists('get_handisum')) {
    function get_handisum($race_id, $race_dist)
    {
        global $mysqli;
        $get_hists
            = $mysqli->query("SELECT MIN(handicap) AS minhandi FROM `tbl_hist_results` WHERE `race_id`='$race_id' AND `race_distance`='$race_dist' GROUP by horse_id");
        $totalhandi = 0;
        while ($gethand = $get_hists->fetch_object()) {
            $totalhandi += $gethand->minhandi;
        }

        return $totalhandi;
    }
}

if (!function_exists('get_array_of_handicap')) {
    function get_array_of_handicap($raceid, $racedis)
    {
        global $mysqli;
        $get_array
            = $mysqli->query("SELECT DISTINCT `horse_id` FROM `tbl_hist_results` WHERE `race_id`='$raceid' AND `race_distance`='$racedis'");
        $arr = array();
        while ($arhorse = $get_array->fetch_object()) {
            $get_histar
                = $mysqli->query("SELECT MIN(handicap) as minihandi FROM `tbl_hist_results` WHERE `race_id`='$raceid' AND `race_distance`='$racedis' AND `horse_id`='$arhorse->horse_id'");
            while ($ahandi = $get_histar->fetch_object()) {
                $arr[] = $ahandi->minihandi;
            }
        }

        return $arr;
    }
}

if (!function_exists('get_sectionalsum')) {
    function get_sectionalsum($race_id, $race_dist)
    {
        global $mysqli;
        $get_histsec
            = $mysqli->query("SELECT MAX(avgsec) AS secavg FROM `tbl_hist_results` WHERE `race_id`='$race_id' AND `race_distance`='$race_dist' GROUP by horse_id");
        $totalsec = 0;
        while ($getsect = $get_histsec->fetch_object()) {
            $totalsec += $getsect->secavg;
        }

        return $totalsec;
    }
}

if (!function_exists('get_array_of_avgsec')) {
    function get_array_of_avgsec($raceid, $racedis)
    {
        global $mysqli;
        $get_array
            = $mysqli->query("SELECT DISTINCT `horse_id` FROM `tbl_hist_results` WHERE `race_id`='$raceid' AND `race_distance`='$racedis'");
        $arr = array();
        while ($arhorse = $get_array->fetch_object()) {
            $get_histar
                = $mysqli->query("SELECT MAX(avgsec) AS secavg FROM `tbl_hist_results` WHERE `race_id`='$raceid' AND `race_distance`='$racedis' AND `horse_id`='$arhorse->horse_id'");
            while ($asec = $get_histar->fetch_object()) {
                $arr[] = $asec->secavg;
            }
        }

        return $arr;
    }
}

if (!function_exists('newvalue')) {
    function newvalue($length, $distance, $orgdistance, $pos, $time)
    {

        //Getting the postion of the horse
        $pos = explode('/', $pos);
        $position = intval($pos[0]);
        $modifier = MODIFIER;
        $remainder = get_remainder($distance);

        if ($position == 1) {
            if ($distance < $orgdistance) {
                $newtime = 'win_rounded_up';
            } else {
                $newtime = 'win_rounded_down';
            }
            $newtime = $$newtime(
                $time,
                $length,
                $modifier,
                $remainder
            );
        } else {
            if ($distance < $orgdistance) {
                $newtime = loses_rounded_up(
                    $time,
                    $length,
                    $modifier,
                    $remainder
                );
            } else {
                if ($distance > $orgdistance) {
                    $newtime = loses_rounded_down(
                        $time,
                        $length,
                        $modifier,
                        $remainder
                    );
                } else {
                    $newtime = $time + ($length * $modifier);
                }
            }
        }

        return $newtime;
    }
}

if (!function_exists('get_remainder')) {
    function get_remainder($distance)
    {

        if ($distance % 10 < 5) {
            $distance -= $distance % 10;
        } else {
            $distance += (10 - ($distance % 10));
        }

        if ($distance % 100 < 50) {
            $reminder_distance = $distance % 100;
        } else {
            $reminder_distance = (100 - ($distance % 100));
        }
        $reminder = $reminder_distance;

        return $reminder;
    }
}

if (!function_exists('win_rounded_up')) {
    // if horse wins
    function win_rounded_up($time, $remainder)
    {
        global $timer;
        return $time + ($timer * $remainder);
    }
}

if (!function_exists('win_rounded_down')) {
    // if horse wins
    function win_rounded_down($time, $remainder)
    {
        global $timer;
        return $time - ($timer * $remainder);
    }
}

if (!function_exists('loses_rounded_up')) {
    // if horse loses
    function loses_rounded_up($time, $length, $modifier, $remainder)
    {
        global $timer;
        return $time + ($length * $modifier) + ($timer * $remainder);
    }
}

if (!function_exists('loses_rounded_down')) {
    // if horse loses
    function loses_rounded_down($time, $length, $modifier, $remainder)
    {
        global $timer;
        return $time + ($length * $modifier) - ($timer * $remainder);
    }
}

if (!function_exists('rating_system')) {
    function rating_system($handicap, $section, $oldweight, $newweight)
    {
        global $secpoint;
        $pos = explode('/', $section);

        if (isset($pos[1])) {
            $sectiontime = $pos[1];
        } else {
            $sectiontime = 0;
        }

        $weight = weight_points($oldweight, $newweight);
        $handicappoints = $handicap;

        if ($sectiontime == 0) {
            $sectionpoints = 0;
        } else {
            $sectionpoints = ($secpoint / $sectiontime) * 100;
        }
        $rating = $handicappoints + $sectionpoints + ($weight / 100);

        return $rating;
    }
}

if (!function_exists('weight_points')) {
    function weight_points($oldweight, $newweight)
    {
        $weight = $newweight - $oldweight;
        $wgt = null;

        $weights = [
            ($weight > 3) => 1.5,
            ($weight > 2 && $weight <= 2.5) => 1,
            ($weight > 1 && $weight <= 1.5) => 0.5,
            ($weight > 0 && $weight <= 0.5) => 1,
            ($weight > -0.5 && $weight <= 0) => -1.5,
            ($weight > -1 && $weight <= -1.5) => -2,
            ($weight > -1 && $weight <= -2.5) => -2,
            ($weight > -3 && $weight < -2.5) => -3
        ];

        foreach ($weights as $w => $value) {
            if ($w) {
                $wgt = $value;
            }
        }

        return $wgt;
    }
}

if (!function_exists('generate_rank')) {
    function generate_rank($value, $array, $order = 0)
    {
        // sort
        if ($order) {
            sort($array);
        } else {
            rsort($array);
        }

        // add item for counting from 1 but 0
        array_unshift($array, $value + 1);

        // select all indexes with the value
        $keys = array_keys($array, $value);
        if (count($keys) == 0) {
            return null;
        }

        // calculate the rank
        $res = array_sum($keys) / count($keys);

        return $res / 2;
    }
}

if (!function_exists('generate_avgsectional')) {
    function generate_avgsectional($value, $array, $order = 0)
    {
        // sort
        if ($order) {
            sort($array);
        } else {
            rsort($array);
        }

        // add item for counting from 1 but 0
        array_unshift($array, $value + 1);

        // select all indexes with the value
        $keys = array_keys($array, $value);
        if (count($keys) == 0) {
            return null;
        }

        // calculate the rank
        $res = array_sum($keys) / count($keys);

        return $res / 2;
    }
}

if (!function_exists('rating_system_new')) {
    function rating_system_new($rankavg, $avgsectional)
    {
        $rating = $rankavg + $avgsectional;

        return $rating;
    }
}

