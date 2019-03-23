<?php
$errorMessage = '';
$successMessage = '';
$raceId = (isset($_POST['race_id'])) ? $_POST['race_id'] : 0;

if (isset($_POST['remove_race'])) {
    $q = "DELETE FROM tbl_races WHERE race_id = '$raceId' LIMIT 1";
    $qResult = $mysqli->query($q);
    if (!$qResult) {
        $errorMessage = "Error: {$mysqli->error} ($mysqli->errno)";
    } else {
        $successMessage = "Race $raceId was removed. Affected rows: {$mysqli->affected_rows}";
    }
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
        RaceID: <input type="text" name="race_id" value="<?= $raceId ?>">
        <input type="submit" value="Remove" name="remove_race">
    </div>
</form>

