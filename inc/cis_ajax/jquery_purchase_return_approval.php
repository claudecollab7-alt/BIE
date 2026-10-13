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

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

fnAjaxGuard();
if ($_SESSION['_user_id'] != 1) {
	fnAccessDenied(true);
}

$update_id = isset($_POST["id"]) ? $_POST["id"] : 0;
$task = isset($_POST["task"]) ? $_POST["task"] : '';
$remarks = isset($_POST["remarks"]) ? $_POST["remarks"] : '';

$_REQUEST['rtn_id'] = $update_id;
$stmt = $conn->prepare("UPDATE tbl_purchase_return SET rtn_approve_status = :st, rtn_approve_by = :by, rtn_approve_date_time = :dtm, rtn_approve_remarks = :rem WHERE rtn_id = :rtn_id");
if ($task == 'PR_APP') {
	$stmt->execute(array(':st' => 1, ':by' => $_SESSION['_user_id'], ':dtm' => date('Y-m-d H:i:s'), ':rem' => $remarks, ':rtn_id' => $_REQUEST['rtn_id']));

	$_SESSION['_msg'] = "Purchase Return Approved..!";
}if ($task == 'PR_REJ') {
	$stmt->execute(array(':st' => 2, ':by' => $_SESSION['_user_id'], ':dtm' => date('Y-m-d H:i:s'), ':rem' => $remarks, ':rtn_id' => $_REQUEST['rtn_id']));

	$_SESSION['_msg'] = "Purchase Return Rejected..!";
}

?>
