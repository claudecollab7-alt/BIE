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

// 2026-10-13 login + csrf + approver check (same rule as the approve button), values bound

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

$_REQUEST['po_id'] = $update_id;
$stmt = $conn->prepare("UPDATE tbl_purchase_order SET po_approve_status = :st, po_approve_by = :by, po_approve_date_time = :dtm, po_approve_remarks = :rem, po_status = :po_status WHERE po_id = :po_id");
if ($task == 'PO_APP') {
	$stmt->execute(array(':st' => 1, ':by' => $_SESSION['_user_id'], ':dtm' => date('Y-m-d H:i:s'), ':rem' => $remarks, ':po_status' => 5, ':po_id' => $_REQUEST['po_id']));

	$_SESSION['_msg'] = "PO Approved..!";
}if ($task == 'PO_REJ') {
	$stmt->execute(array(':st' => 2, ':by' => $_SESSION['_user_id'], ':dtm' => date('Y-m-d H:i:s'), ':rem' => $remarks, ':po_status' => 4, ':po_id' => $_REQUEST['po_id']));

	$_SESSION['_msg'] = "PO Rejected..!";
}

?>
