<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
require_once("../common/csrf.php");
require_once("../common/access.php");

$conn = new dbconnect();
$dbconn= new dbhandler();

// 2026-10-13 login + csrf + approver check - the list holding this page is an admin-only menu, values bound
// 2026-10-13 approver from Task Settings (PURCHASE_RETURN A)
// 2026-10-13 only a return sent for approval and not yet decided

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

fnAjaxGuard();
$rtn_branch = $dbconn->GetSingleReconrd("tbl_purchase_return", "branch_id", "rtn_id", (int)$_POST["id"]);
if (!fnTaskCan('PURCHASE_RETURN', 'A', $rtn_branch)) {
	fnAccessDenied(true);
}

$update_id = isset($_POST["id"]) ? $_POST["id"] : 0;
$task = isset($_POST["task"]) ? $_POST["task"] : '';
$remarks = isset($_POST["remarks"]) ? $_POST["remarks"] : '';

$_REQUEST['rtn_id'] = $update_id;
$stmt = $conn->prepare("UPDATE tbl_purchase_return SET rtn_approve_status = :st, rtn_approve_by = :by, rtn_approve_date_time = :dtm, rtn_approve_remarks = :rem WHERE rtn_id = :rtn_id AND rtn_verify_status = 1 AND rtn_approve_status = 0");
if ($task == 'PR_APP') {
	$stmt->execute(array(':st' => 1, ':by' => $_SESSION['_user_id'], ':dtm' => date('Y-m-d H:i:s'), ':rem' => $remarks, ':rtn_id' => $_REQUEST['rtn_id']));

	$_SESSION['_msg'] = "Purchase Return Approved..!";
}if ($task == 'PR_REJ') {
	$stmt->execute(array(':st' => 2, ':by' => $_SESSION['_user_id'], ':dtm' => date('Y-m-d H:i:s'), ':rem' => $remarks, ':rtn_id' => $_REQUEST['rtn_id']));

	$_SESSION['_msg'] = "Purchase Return Rejected..!";
}

// nothing changed: the document was already approved / rejected or is not waiting for this step
if (isset($stmt) && $stmt instanceof PDOStatement && $stmt->rowCount() == 0) {
	$_SESSION['_msg'] = '';
	$_SESSION['_msg_err'] = "Not waiting for approval - it may already be approved or rejected..!";
	http_response_code(409);
}

?>
