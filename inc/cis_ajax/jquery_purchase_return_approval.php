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
	
	$_REQUEST['rtn_id'] = $update_id;
	$update_qry = "";   
	if ($task == 'PR_APP') {
			$sql = "UPDATE tbl_purchase_return SET rtn_approve_status = 1 ,rtn_approve_by='".$_SESSION['_user_id']."',rtn_approve_date_time='".date('Y-m-d H:i:s')."',rtn_approve_remarks='".$remarks."' WHERE rtn_id = ".$_REQUEST['rtn_id']." ";
			//echo $sql;exit;
			$conn->query($sql);
			
			$_SESSION['_msg'] = "Purchase Return Approved..!";
	}if ($task == 'PR_REJ') {
			$sql = "UPDATE tbl_purchase_return SET rtn_approve_status = 2 ,rtn_approve_by='".$_SESSION['_user_id']."',rtn_approve_date_time='".date('Y-m-d H:i:s')."',rtn_approve_remarks='".$remarks."' WHERE rtn_id = ".$_REQUEST['rtn_id']." ";
			//echo $sql;exit;
			$conn->query($sql);
			
			$_SESSION['_msg'] = "Purchase Return Rejected..!";
	}
	
?>