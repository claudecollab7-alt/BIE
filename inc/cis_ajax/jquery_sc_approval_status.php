<?php
// 2026-11-16 new - service call: send for approval (V) and approve (A) from the print, Task Settings SERVICE_CALL
// send for approval only when every complaint is completed and not sent yet; approve only when sent and not approved
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
require_once("../common/csrf.php");
require_once("../common/access.php");
session_start();
$conn = new dbconnect();
$dbconn = new dbhandler();
fnAjaxGuard();

$sc_id = (int)(isset($_POST['id']) ? $_POST['id'] : 0);
$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$branch = $dbconn->GetSingleReconrd("tbl_service_call_register", "branch_id", "sc_id", $sc_id);
if (!in_array($mode, array('verify', 'approve')) || !fnTaskCan('SERVICE_CALL', $mode == 'verify' ? 'V' : 'A', $branch)) {
	fnAccessDenied(true);
}
try {
	if ($mode == 'verify') {
		$st = $conn->prepare("UPDATE tbl_service_call_register SET sc_verify_status = 1, sc_verify_by = :by, sc_verify_date_time = :dt
			WHERE sc_id = :id AND sc_status = 2 AND sc_verify_status = 0");
	} else {
		$st = $conn->prepare("UPDATE tbl_service_call_register SET sc_approve_status = 1, sc_status = 3, sc_approve_by = :by, sc_approve_date_time = :dt
			WHERE sc_id = :id AND sc_status = 2 AND sc_verify_status = 1 AND sc_approve_status = 0");
	}
	$st->execute(array(':by' => $_SESSION['_user_id'], ':dt' => date('Y-m-d H:i:s'), ':id' => $sc_id));
	if ($st->rowCount() == 0) {
		http_response_code(409);
		echo $mode == 'verify' ? "Not ready - every complaint must be completed, and it must not be sent already..!" : "Not waiting for approval - it may already be approved..!";
		exit;
	}
	echo 'OK';
} catch (Exception $e) {
	fnLogError($e);
	http_response_code(500);
	echo 'Not saved - please try again..!';
}
