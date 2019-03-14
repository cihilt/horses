<?php
$algorithms = [];
$distance = 0;
$errorMessage = '';
$successMessage = '';
$algorithmOptions = '';
$raceId = $race_id_var_get;
$updateLimit = (!empty($_POST['limit'])) ? intval($_POST['limit']) : $limit;

// Save default algorithm
if (isset($_POST['default_algorithm'])) {
    if (!empty($_POST['race_id'])) {
        $raceId = intval($_POST['race_id']);
    }
    if (!empty($_POST['distance'])) {
        $distance = intval($_POST['distance']);
    }
    $defaultAlgId = intval($_POST['default_algorithm']);

    $q = "UPDATE tbl_algorithm SET is_default = '0';
          UPDATE tbl_algorithm SET is_default = '1' WHERE id = '$defaultAlgId'";
    $saveAlg = $mysqli->multi_query($q);

    if ( ! $saveAlg) {
        echo 'Error while saving a default algorithm.';
        $logger->log($mysqli->error, 'error');
    } else {
        while ($mysqli->next_result()) // flush multi_queries
        {
            if ( ! $mysqli->more_results()) {
                break;
            }
        }
    }
}

// Options for Default Algorithm web form
$q = "SELECT * FROM tbl_algorithm";
$algs = $mysqli->query($q);
while ($alg = $algs->fetch_object()) {
    $algorithms[$alg->id] = $alg->title;

    $selected = ((bool) $alg->is_default) ? ' selected' : '';
    $algorithmOptions .= '<option value="'.$alg->id.'"'.$selected.'>'.$alg->title.'</option>';
}

// Applying the algorithm
if (isset($_POST['default_algorithm'])) {
    $logger->setLevel('debug');

    $selectedAlgorithm = $algorithms[$defaultAlgId];
    if ($selectedAlgorithm == 'udpatehptime') {
        udpatehptime($mysqli, $position_percentage, $raceId, $updateLimit);
    } elseif ($selectedAlgorithm == 'distance_new') {
        distance_new($mysqli, $position_percentage, $raceId, $distance);
    }

    $logger->setLevel();
    $successMessage = 'Succeed';
}
?>

<?php if ($successMessage): ?>
<div align="center" style="background: #5cb85c; color: #fff; padding: 2px 0; margin: -5px 0 10px 0">
    <b>Result:</b> <?= $successMessage ?>
</div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div align="center" style="background: #c9302c; color: #fff; padding: 2px 0; margin: -5px 0 10px 0">
        <b>Error:</b> <?= $errorMessage ?>
    </div>
<?php endif; ?>

<form action="updatehptime.php" method="post">

    <div>
        Update Limit: <input type="text" name="limit" value="<?= $updateLimit ?>"> (required by "udpatehptime")
    </div>

    <div>
        Distance: <input type="text" name="distance" value="<?= $distance ?>"> (required by "distance_new")
    </div>

    <select name="default_algorithm">
        <option></option>
        <?= $algorithmOptions ?>
    </select>
    <input type="submit" value="Save">
</form>

