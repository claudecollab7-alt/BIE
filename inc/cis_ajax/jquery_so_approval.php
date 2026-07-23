<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


$update_id = $_POST["so_id"];
$task = $_POST["task"];
$remarks = $_POST["remarks"];


$stmt = null;
$stmt = $conn->prepare("UPDATE tbl_sales_order set  so_approve_status = :so_approve_status, so_approve_by = :so_approve_by, so_approve_date_time = :so_approve_date_time,
	so_remarks = :so_remarks, so_status = :so_status WHERE so_id = :so_id");



if ($task == 'SO_APP') {
	$_REQUEST['created_dtm'] = date('Y-m-d H:i:s');

	$data = array(
		':so_approve_status' =>  "1",
		':so_approve_by' => $_SESSION['_user_id'],
		':so_approve_date_time' => $_REQUEST['created_dtm'],
		':so_remarks' => $remarks,
		':so_status' => "5",
		':so_id' => $update_id
	);

	$stmt->execute($data);

	$_SESSION['_msg'] = "Sales Approved..!";
}

if ($task == 'SO_REJ') {
	$data = array(
		':so_approve_status' =>  "2",
		':so_approve_by' => $_SESSION['_user_id'],
		':so_approve_date_time' => $_REQUEST['created_dtm'],
		':so_remarks' => $remarks,
		':so_status' => "4",
		':so_id' => $update_id
	);
	$stmt->execute($data);


	$_SESSION['_msg'] = "Sales Rejected..!";
}
