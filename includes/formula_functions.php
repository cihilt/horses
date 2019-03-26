<?php

if (!isset($logger)) {
    $logger = new logger();
}

function updatehptime($mysqli, $position_percentage, $limit = 0, $raceId = 0)
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
    $updateQuery = "";
    $updateQueryCount = 0;
    $updateQueryRaces = "";
    $updateQueryRacesCount = 0;

    // Rank
    if ($races->num_rows > 0) {

        $logger->log('Start calculation of the rank');
        if ($raceId) $logger->log('RaceID is ' . $raceId);

        while ($race = $races->fetch_object()) {
            $horsesCount = get_rows(
                "`tbl_temp_hraces` 
                WHERE `race_id`='$race->race_id' 
                AND `horse_fxodds`!='0'"
            );

            $qDistance = "SELECT DISTINCT CAST(race_distance AS UNSIGNED) AS racedist 
                          FROM tbl_hist_results 
                          WHERE `race_id`='$race->race_id' 
                          ORDER by racedist ASC";
            $distances = $mysqli->query($qDistance);

            $logger->log('Rank. All below results are for Race ID: '.$race->race_id, 'debug');
            $logger->log($qDistance, 'debug');

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

                    if ($oddsResult->num_rows === 0) continue;
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

                                    $updateQuery .= "UPDATE `tbl_hist_results` 
                                                    SET `rank`='$rank' 
                                                    WHERE `race_id`='$race->race_id' 
                                                    AND `race_distance`= '$distance->racedist' 
                                                    AND `horse_id`='$horse->horse_id';";
                                    $updateQueryCount++;
                                }
                            }
                        }
                        ++$i;
                    }
                }
            }
            if ($updateQuery) {
                $updateQueryRaces .= "UPDATE `tbl_races` 
                     SET `rank_status`='1' 
                     WHERE `race_id`='$race->race_id';";
                $updateQueryRacesCount++;
            }
        }

        $logger->log('Finish calculation of the rank');
    } else {
        $logger->log('Rank: 0 results');
    }

    // Sectional avg
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

        $logger->log('Start calculation of the Sectional AVG');
        if ($raceId) $logger->log('RaceID is ' . $raceId);

        while ($race = $races->fetch_object()) {
            $horsesCount = get_rows(
                "`tbl_temp_hraces` WHERE `race_id`='$race->race_id' AND `horse_fxodds`!='0'"
            );

            $qDistance = "SELECT DISTINCT CAST(race_distance AS UNSIGNED) AS racedist 
                          FROM tbl_hist_results 
                          WHERE `race_id`='$race->race_id' 
                          ORDER by racedist ASC";
            $distances = $mysqli->query($qDistance);

            $logger->log('Sectional AVG. All below results are for Race ID: '.$race->race_id, 'debug');
            $logger->log($qDistance, 'debug');

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
                    if ($oddsResult->num_rows === 0) continue;

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
                                $updateQuery .= "UPDATE `tbl_hist_results` 
                                                 SET `avgsectional`='$avgSectional' 
                                                 WHERE `race_id`='$race->race_id' 
                                                 AND `race_distance`= '$distance->racedist' 
                                                 AND `horse_id`='$horse->horse_id';";
                                $updateQueryCount++;
                            }
                        }
                        ++$i;
                    }
                }
            }
            if ($updateQuery) {
                $updateQueryRaces .= "UPDATE `tbl_races` 
                     SET `sec_status`='1' 
                     WHERE `race_id`='$race->race_id';";
                $updateQueryRacesCount++;
            }
        }

        $logger->log('Finish calculation of the Sectional AVG');
    } else {
        $logger->log('Sectional AVG: 0 results');
    }

    // Execute update queries
    if ($updateQuery) {
        runMultipleQuery(
            $mysqli,
            'tbl_hist_results',
            $updateQuery,
            $updateQueryCount,
            $logger
        );
        runMultipleQuery(
            $mysqli,
            'tbl_races',
            $updateQueryRaces,
            $updateQueryRacesCount,
            $logger
        );

        $updateQuery = "";
        $updateQueryCount = 0;
    } else {
        $logger->log('No updates for "tbl_hist_results"');
    }

    // Rating
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

        $logger->log('Start calculation of the rating');
        if ($raceId) $logger->log('RaceID is ' . $raceId);

        while ($row = $results->fetch_object()) {
            $logMessage = 'avgsectional: '.$row->avgsectional.PHP_EOL;
            $logMessage .= 'rank: '.$row->rank.PHP_EOL;
            $logMessage .= 'hist_id: '.$row->hist_id;
            $logger->log($logMessage, 'debug');

            if ($row->avgsectional != "0" || $row->rank != "0") {
                $ratePos = $row->avgsectional + $row->rank;
                $updateQuery .= "UPDATE `tbl_hist_results` 
                                 SET `rating` = '$ratePos' 
                                 WHERE `hist_id` = '$row->hist_id';";
                $updateQueryCount++;

                $logger->log('Rating done for: '.$row->hist_id, 'debug');
            }
        }

        if ($updateQuery) {
            runMultipleQuery(
                $mysqli,
                'tbl_hist_results',
                $updateQuery,
                $updateQueryCount,
                $logger
            );
        } else {
            $logger->log('No updates for "tbl_hist_results"');
        }

        $logger->log('Finish calculation of the rating');
    } else {
        $logger->log('Rating: 0 results');
    }

    $logger->log('Finished: '. __FUNCTION__);
}

function distance_new($mysqli, $position_percentage, $distance = 0, $raceId = 0)
{
    global $logger;
    $logger->log('Started: '. __FUNCTION__);

    if ($raceId == 0) {
        $logger->log("Race id is $raceId. Exit");
        return;
    }

    $q = "SELECT `race_id` FROM `tbl_races` 
             WHERE `race_id`='$raceId' ORDER by `race_id` ASC";
    $races = $mysqli->query($q);
    if (!$races) {
        $logger->log($mysqli->error, 'error');
    }

    // Rank
    if ($races->num_rows > 0) {
        while ($race = $races->fetch_object()) {
            $horsesCount = get_rows(
                "`tbl_temp_hraces` 
                 WHERE `race_id`='$race->race_id' 
                 AND `horse_fxodds`!='0'"
            );

            $logger->log('Start calculation of the Rank');
            if ($raceId) $logger->log('RaceID is ' . $raceId);

            $distancesResult = $mysqli->query(
                "SELECT DISTINCT CAST(race_distance AS UNSIGNED) AS racedist 
                 FROM tbl_hist_results 
                 WHERE `race_id`='$race->race_id' 
                 AND `race_distance`='$distance' 
                 ORDER by racedist ASC"
            );

            $updateQuery = "";
            $updateQueriesCount = 0;
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
                                    $rank = distanceNewRank(
                                        $handicap->minihandi,
                                        $numsArray
                                    );

                                    if($rank) {
                                        $horseDetails = horse_details($horseHist->horse_id);
                                        $logger->log($horseDetails->horse_name." rank: $rank");
                                    }

                                    $updateQuery .=
                                        "UPDATE `tbl_hist_results` 
                                         SET `rank`='$rank' 
                                         WHERE `race_id`='$race->race_id' 
                                         AND `race_distance`= '$distance->racedist' 
                                         AND `horse_id`='$horseHist->horse_id';";
                                    $updateQueriesCount++;
                                }
                            }
                        }
                        ++$i;
                    }
                }
            }
            if($updateQuery) {
                runMultipleQuery(
                    $mysqli,
                    'tbl_hist_results',
                    $updateQuery,
                    $updateQueriesCount,
                    $logger
                );

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
        $logger->log('Finish calculation of the Rank');
    } else {
        $logger->log('Rating: 0 results');
    }

    $logger->log('Finished: '. __FUNCTION__);
}

/**
 * Update handicap
 *
 * @param resource $mysqli
 * @param string   $offset  Example: "0, 100", or "5", or just "0"
 * @param array    $histIds Array of hist_results ids
 *
 * @return bool False in case of an error
 */
function resetHandicap($mysqli, $offset = null, array $histIds = [])
{
    global $logger;
    $logger->log('Started: '. __FUNCTION__);

    // Handicap Started
    if ($histIds) {
        $histIdStr = implode(', ', $histIds);
        $sql = "SELECT * FROM `tbl_hist_results` WHERE `hist_id` in ($histIdStr)";
    } elseif ($offset === null) {
        $sql = "UPDATE `tbl_hist_results` SET `handicap`='0'";
        $res = $mysqli->query($sql);
        if (!$res) {
            $logger->log('MySQL error: ' . $mysqli->error, 'error');
        }

        $sql = "SELECT * FROM `tbl_hist_results` WHERE `handicap`='0'";
    } else {
        $sql = "SELECT * FROM `tbl_hist_results` 
                ORDER BY hist_id ASC LIMIT $offset";
    }
    $logger->log('Gather data for update: ' . $sql, 'debug');
    $resHandicap = $mysqli->query($sql);

    if ($resHandicap->num_rows > 0) {
        $qHandicapHist = '';
        $qHandicapHistCount = '';
        while ($handicap = $resHandicap->fetch_object()) {
            $raceDetails = race_details($handicap->race_id);
            $distance = round($raceDetails->race_distance / 100);
            $distance = $distance * 100;
            $newHandicap = newvalue(
                $handicap->length,
                $raceDetails->race_distance,
                $distance,
                $handicap->horse_position,
                number_format($handicap->race_time, 2)
            );
            $newHandicap = number_format($newHandicap, 3);

            $id = $handicap->hist_id;
            $qHandicapHist .= "UPDATE `tbl_hist_results` 
                               SET `handicap`='$newHandicap' 
                               WHERE hist_id = '$id';";
            $qHandicapHistCount++;

            if ($qHandicapHistCount >= 500) {
                runMultipleQuery(
                    $mysqli,
                    'tbl_hist_results',
                    $qHandicapHist,
                    $qHandicapHistCount,
                    $logger
                );
                $qHandicapHist = '';
                $qHandicapHistCount = 0;
            }
        }
        if ($qHandicapHist) {
            $updated = runMultipleQuery(
                $mysqli,
                'tbl_hist_results',
                $qHandicapHist,
                $qHandicapHistCount,
                $logger
            );
            if (!$updated) {
                return false;
            }
        }
    } else {
        $logger->log('No any data for update', 'debug');
    }
    $logger->log('Finished: '. __FUNCTION__);

    return true;
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
        //Getting the position of the horse
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
            $newtime = $newtime(
                $time,
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
        global $logger;
        if ($timer === null) {
            $logger->log(__FUNCTION__.': $timer variable was not imported. The result will be calculated wrong', 'error');
        }
        return $time + ($timer * $remainder);
    }
}

if (!function_exists('win_rounded_down')) {
    // if horse wins
    function win_rounded_down($time, $remainder)
    {
        global $timer;
        global $logger;
        if ($timer === null) {
            $logger->log(__FUNCTION__.': $timer variable was not imported. The result will be calculated wrong', 'error');
        }
        return $time - ($timer * $remainder);
    }
}

if (!function_exists('loses_rounded_up')) {
    // if horse loses
    function loses_rounded_up($time, $length, $modifier, $remainder)
    {
        global $timer;
        global $logger;
        if ($timer === null) {
            $logger->log(__FUNCTION__.': $timer variable was not imported. The result will be calculated wrong', 'error');
        }
        return $time + ($length * $modifier) + ($timer * $remainder);
    }
}

if (!function_exists('loses_rounded_down')) {
    // if horse loses
    function loses_rounded_down($time, $length, $modifier, $remainder)
    {
        global $timer;
        global $logger;
        if ($timer === null) {
            $logger->log(__FUNCTION__.': $timer variable was not imported. The result will be calculated wrong', 'error');
        }
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

if (!function_exists('distanceRank')) {
    function distanceRank($value, $array, $order = 0)
    {
        if ($order) {
            sort($array);
        } else {
            rsort($array);
        }

        array_unshift($array, $value + 1);
        $keys = array_keys($array, $value);

        if (count($keys) == 0) {
            return null;
        }

        return count($keys);
    }
}

if (!function_exists('distanceNewRank')) {
    function distanceNewRank($value, $array, $order = 0)
    {
        array_unique($array);

        if ($order) {
            sort($array);
        } else {
            rsort($array);
        }

        return array_search($value, $array) + 1;
    }
}

/**
 * Reset rankings: rank, rating, sectional, avgsectional, avgsec
 *
 * @param resource $mysqli
 * @param bool $rank
 * @param bool $rating
 * @param bool $sectional
 * @param bool $avgSectional
 * @param bool $avgSec
 * @param logger $logger
 *
 * @return bool False in a case if something went wrong
 */
function resetRankings(
    $mysqli,
    $rank = true,
    $rating = true,
    $sectional = true,
    $avgSectional = true,
    $avgSec = true,
    $logger = null
)
{
    $query = "UPDATE tbl_hist_results SET ";
    $expressions = [
        ((bool)$rank) ? 'rank = 0, ' : '',
        ((bool)$rating) ? 'rating = 0, ' : '',
        ((bool)$avgSectional) ? 'avgsectional = 0, ' : '',
        ((bool)$avgSec) ? 'avgsec = 0, ' : '',
        ((bool)$sectional) ? 'sectional = 0, ': '',
    ];

    foreach ($expressions as $exp) {
        $query .= $exp;
    }

    $query = substr($query, 0, - 2);
    $res = $mysqli->query($query);
    if (!$res) {
        if ($logger !== null) $logger->log(
            'Error on reset ranking: ' . $mysqli->error);
        return false;
    }

    return true;
}

