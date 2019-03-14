<?php
define("MODIFIER",'0.025');
function get_rows($table_and_query) {
	global $mysqli;
	$total = $mysqli->query("SELECT * FROM $table_and_query");
	$total = $total->num_rows;
	return $total;
}

function meeting_details($mid) {
	global $mysqli;
	$meetingdet = $mysqli->query("SELECT * FROM `tbl_meetings` WHERE `meeting_id`='$mid'");
	$detail = $meetingdet->fetch_object();
	return $detail;
}
function horse_details($hid) {
	global $mysqli;
	$horsedet = $mysqli->query("SELECT * FROM `tbl_horses` WHERE `horse_id`='$hid'");
	$detail = $horsedet->fetch_object();
	return $detail;
}
function race_details($rid) {
	global $mysqli;
	$racedet = $mysqli->query("SELECT * FROM `tbl_races` WHERE `race_id`='$rid'");
	$detail = $racedet->fetch_object();
	return $detail;
}
function get_horses_in_race($rid) {
	global $mysqli;
	$racedet = $mysqli->query("SELECT * FROM `tbl_races` WHERE `race_id`='$rid'");
	$detail = $racedet->fetch_object();
	return $detail;
}
function result_details($rid, $hid) {
	global $mysqli;
	$resdet = $mysqli->query("SELECT * FROM `tbl_results` WHERE `race_id`='$rid' AND `horse_id`='$hid'");
	$detail = $resdet->fetch_object();
	return $detail;
}

/*	
	function get_user_details($user_name) {
		global $mysqli;
		$log_user = $mysqli->query("SELECT * FROM `tbl_user` WHERE `user_name`='$user_name'");
		$detail = $log_user->fetch_object();
		return $detail;
	}
	
	function get_user_by_id($user_id) {
		global $mysqli;
		$log_user = $mysqli->query("SELECT * FROM `tbl_user` WHERE `id`='$user_id'");
		$detail = $log_user->fetch_object();
		return $detail;
	}
	
	
	
	function get_sub_details($sub_id) {
		global $mysqli;
		$sub_user = $mysqli->query("SELECT * FROM `tbl_staff_subscribers` WHERE `id`='$sub_id'");
		$detail = $sub_user->fetch_object();
		return $detail;
	}
	
	
	function get_staff_by_id($staff_id) {
		global $mysqli;
		$staff_user = $mysqli->query("SELECT * FROM tbl_staff WHERE id='$staff_id'");
		$detail = $staff_user->fetch_object();
		return $detail;
	}
	
	function get_msg_by_id($msg_id) {
		global $mysqli;
		$msg_det = $mysqli->query("SELECT * FROM `tbl_recv_messages` WHERE id='$msg_id'");
		$detail = $msg_det->fetch_object();
		return $detail;
	}
	
	function get_last_user_id($lastid = 1)
	{
		global $mysqli;
		$get_last_user = $mysqli->query("SELECT * FROM `tbl_user` ORDER by `id` DESC LIMIT 1");
		$lastuser = $get_last_user->fetch_object();
		return $lastuser->id;
	}
	*/
?>