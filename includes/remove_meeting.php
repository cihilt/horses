<?php
$errorMessage = '';
$successMessage = '';
$meetingDates = (isset($_POST['meeting_dates'])) ? $_POST['meeting_dates'] : '';
$meetingDatesAr = explode(',', $meetingDates);

if (isset($_POST['remove_meeting'])) {
    $q = "DELETE FROM tbl_meetings WHERE meeting_date IN ";
    $qMeetingDates = '';

    if ($meetingDatesAr) {
        foreach ($meetingDatesAr as $meetingDate) {
            $meetingDate = trim(str_replace('/', '-', $meetingDate));
            $meetingDate = substr($meetingDate, 0, 10);
            $time = strtotime($meetingDate);
            if (!$time) {
                $logger->log('Invalid date "'.$meetingDate.'"', 'warn');
                continue;
            }
            $meetingDate = date('Y-m-d', $time);
            $qMeetingDates .= "'$meetingDate', ";
        }

        $qMeetingDates = substr($qMeetingDates, 0, -2);
        $q .= "($qMeetingDates)";
    }
    $qResult = $mysqli->query($q);
    if (!$qResult) {
        $errorMessage = "Error: {$mysqli->error} ($mysqli->errno)";
    } else {
        $successMessage = "{$mysqli->affected_rows} meetings were removed";
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
        Date (dd/mm/yyyy): <br>
        <textarea cols="40" rows="3" name="meeting_dates"><?= $meetingDates ?></textarea>
        <div style="font-size: 12px; font-family: Arial;">Several dates should be separated by commas</div>
        <input type="submit" value="Remove" name="remove_meeting">
    </div>
</form>

