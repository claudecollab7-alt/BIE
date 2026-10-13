<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
require_once("../common/csrf.php");
require_once("../common/access.php");

$conn = new dbconnect();
$dbconn = new dbhandler();

// 2026-10-13 login + csrf + approver check - same rule as the approve button (admin or the dc's branch)
// 2026-10-13 approver from Task Settings (DC A, per branch)

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

fnAjaxGuard();
$dc_branch = $dbconn->GetSingleReconrd("tbl_dc", "branch_id", "dc_id", (int)$_POST["dc_id"]);
if (!fnTaskCan('DC', 'A', $dc_branch)) {
	fnAccessDenied(true);
}


$update_id = $_POST["dc_id"];
$task = $_POST["task"];
$remarks = $_POST["remarks"];


$stmt = null;
$stmt = $conn->prepare("UPDATE tbl_dc set  dc_approve_status = :dc_approve_status, dc_approve_by = :dc_approve_by, dc_approve_date_time = :dc_approve_date_time,
dc_remarks = :dc_remarks WHERE dc_id = :dc_id");


if ($task == 'DC_APP') {
    $_REQUEST['created_dtm'] = date('Y-m-d H:i:s');

    $data = array(
        ':dc_approve_status' =>  "1",
        ':dc_approve_by' => $_SESSION['_user_id'],
        ':dc_approve_date_time' => $_REQUEST['created_dtm'],
        ':dc_remarks' => $remarks,
        ':dc_id' => $update_id
    );

    $stmt->execute($data);
    $_SESSION['_msg'] = "Delivery Challan Approved..!";
}



if ($task == 'DC_REJ') {
    $data = array(
        ':dc_approve_status' =>  "2",
        ':dc_approve_by' => $_SESSION['_user_id'],
        ':dc_approve_date_time' => $_REQUEST['created_dtm'],
        ':dc_remarks' => $remarks,
        ':dc_id' => $update_id
    );

    $stmt->execute($data);
    $_SESSION['_msg'] = "Delivery Challan Rejected..!";
}
