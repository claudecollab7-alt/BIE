<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn= new dbhandler();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


	$update_id = $_POST["id"];
	$task = $_POST["task"];
	$remarks = $_POST["remarks"];
	
	$_REQUEST['po_id'] = $update_id;
	$update_qry = "";   
	if ($task == 'PO_APP') {
			$sql = "UPDATE tbl_purchase_order SET po_approve_status = 1 ,po_approve_by='".$_SESSION['_user_id']."',po_approve_date_time='".date('Y-m-d H:i:s')."',po_approve_remarks='".$remarks."',po_status = 5 WHERE po_id = ".$_REQUEST['po_id']." ";
			//echo $sql;exit;
			$conn->query($sql);
			
			$_SESSION['_msg'] = "PO Approved..!";
	}if ($task == 'PO_REJ') {
			$sql = "UPDATE tbl_purchase_order SET po_approve_status = 2 ,po_approve_by='".$_SESSION['_user_id']."',po_approve_date_time='".date('Y-m-d H:i:s')."',po_approve_remarks='".$remarks."',po_status = 4 WHERE po_id = ".$_REQUEST['po_id']." ";
			//echo $sql;exit;
			$conn->query($sql);
			
			$_SESSION['_msg'] = "PO Rejected..!";
	}
	
?>