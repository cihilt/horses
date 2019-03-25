<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once '../includes/config.php';
require_once APP_ROOT . '/parallel/Thread.php';
require_once APP_ROOT . '/includes/functions.php';
require_once APP_ROOT . '/includes/formula_functions.php';

$limit = 0;
$distance = 0;
$workersPool = [];
$execTime = null;
$resetHandicap = true;
$workersCount = WORKERS_COUNT;
// position percentage
$sqlFormulas = "SELECT `position_percentage` 
                FROM `tbl_formulas` WHERE id = 1";
$resultFormulas = $mysqli->query($sqlFormulas);
$posPercentage = $resultFormulas->fetch_row()[0];
// default algorithm
$algorithmId = 0;

if(isset($_POST['run'])) {
    $start = microtime(true);

    $limit = intval(arrayGet($_POST, 'limit', 0));
    $distance = intval(arrayGet($_POST, 'distance', $distance));
    $workersCount = intval(arrayGet($_POST, 'workers', WORKERS_COUNT));
    $algorithmId = intval(arrayGet($_POST, 'alg', $algorithmId));
    $posPercentage = intval(arrayGet($_POST, 'position_percentage', $posPercentage));
    // algorithm data
    $sqlAlg = "SELECT title FROM tbl_algorithm WHERE id = $algorithmId";
    $resultAlg = $mysqli->query($sqlAlg);
    $algData = $resultAlg->fetch_row();
    $algorithmTitle = $algData[0];

    $workersData = [
        "limit" => $limit,
        "distance" => $distance,
        "position_percentage" => $posPercentage,
        "algorithm_id" => $algorithmId,
        "algorithm_title" => $algorithmTitle,
        "offset_start" => 0,
        "offset_limit" => 0,
        "proc_id" => 0
    ];

    # Handicap workers
    if (isset($_POST['reset_handicap'])) {
        // distribute records for workers
        $q = "SELECT COUNT(hist_id) FROM tbl_hist_results";
        $totalResults = $mysqli->query($q);
        $totalRows = (int)$totalResults->fetch_row()[0];
        $chunk = floor($totalRows / $workersCount);
        $rawChunk = ceil($totalRows / $workersCount);
        // create handicap threads
        $offsetStart = $offsetLimit = 0;
        for ($i = 0; $i < $workersCount; $i++) {
            $offsetLimit = $chunk;
            if ($i + 1 >= $workersCount) {
                $offsetLimit = $rawChunk;
            }
            $data = base64_encode(json_encode([
                'proc_id'      => $i + 1,
                'offset_start' => $offsetStart,
                'offset_limit' => $offsetLimit,
            ]));

            $workersPool[] = 'php worker_handicap.php '.$data;
            $offsetStart += $chunk;
        }
        // run handicap threads
        $threads = new Multithread($workersPool);
        $threads->run();
        $workersPool = [];
    } else {
        $resetHandicap = false;
    }

    # Algorithm workers

    // distribute races for workers
    $q = "SELECT COUNT(race_id) FROM tbl_races";
    $totalResults = $mysqli->query($q);
    $totalRows = (int) $totalResults->fetch_row()[0];
    $chunk = floor($totalRows / $workersCount);
    $rawChunk = ceil($totalRows / $workersCount);

    // create races threads
    $offsetStart = $offsetLimit = 0;
    for ($i = 0; $i < $workersCount; $i++) {
        $offsetLimit = $chunk;
        if ($i + 1 >= $workersCount) {
            $offsetLimit = $rawChunk;
        }

        $workersData['proc_id'] = $i + 1;
        $workersData['offset_start'] = $offsetStart;
        $workersData['offset_limit'] = $offsetLimit;
        $data = base64_encode(json_encode($workersData));

        $workersPool[] = 'php worker.php ' . $data;
        $offsetStart += $chunk;
    }

    // run races threads
    $threads = new Multithread($workersPool);
    $threads->run();
    $execTime = microtime(true) - $start;
}

// algorithm list options
$q = "SELECT * FROM tbl_algorithm";
$algResults = $mysqli->query($q);
$algorithmOptions = '';
while ($alg = $algResults->fetch_object()) {
    $selected = ($algorithmId == $alg->id) ? ' selected' : '';
    $algorithmOptions .= '<option value="'.$alg->id.'"'.$selected.'>'.$alg->title.'</option>';
}
?>

<style>
    div.label {
        margin: 12px 0 3px 0;
        font-size: 10pt;
        font-family: Arial;
    }
</style>

<form action="index.php" method="post">
    <div class="label">Function:</div>
    <div>
        <select name="alg">
            <option></option>
            <?= $algorithmOptions ?>
        </select>
    </div>

    <div class="label">Handicap:</div>
    <div>
        <input type="checkbox" <?= ($resetHandicap) ? 'checked' : ''?> name="reset_handicap" id="reset_handicap"> <label for="reset_handicap">reset</label>
    </div>

    <div class="label">Distance</div>
    <div>
        <input type="text" name="distance" value="<?= $distance ?>">
    </div>

    <div class="label">Limit</div>
    <div>
        <input type="text" name="limit" value="<?= $limit ?>">
    </div>

    <div class="label">Position Percentage</div>
    <div>
        <input type="text" name="position_percentage" value="<?= $posPercentage ?>">
    </div>

    <div class="label">Workers count</div>
    <div>
        <input type="text" name="workers" value="<?= $workersCount ?>">
    </div>

    <div class="label">
        <input type="submit" value="Run" name="run">
    </div>
</form>

<?= ($execTime !== null) ? 'Execution time ' . $execTime : '' ?>