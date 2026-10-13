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

// 2026-10-13 login + csrf + approver check - same rule as the approve button (admin or the quotation's branch)

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

fnAjaxGuard();
$quo_branch = $dbconn->GetSingleReconrd("tbl_quotation", "bie_branch_id", "quo_id", (int)$_POST["id"]);
if (!($_SESSION['_user_id'] == 1 || ($quo_branch != '' && $quo_branch == $_SESSION['_user_branch']))) {
	fnAccessDenied(true);
}


	$update_id = $_POST["id"];
	$task = $_POST["task"];
	$remarks = $_POST["remarks"];
	
		
	$stmt = null;
	$stmt = $conn->prepare("UPDATE tbl_quotation set  quo_approve_status = :quo_approve_status, quo_approve_id = :quo_approve_id, quo_approve_date_time= :quo_approve_date_time,
		quo_approve_remarks= :quo_approve_remarks WHERE quo_id = :quo_id");

	if ($task == 'QUO_APP') 
	{
		$data = array(
			':quo_approve_status' =>  "1",
			':quo_approve_id' =>$_SESSION['_user_id'],
			':quo_approve_date_time' =>date('Y-m-d H:i:s'),
			':quo_approve_remarks' =>$remarks,
			':quo_id' => $update_id			
		);
		
		$stmt->execute($data);
		$_SESSION['_msg'] = "Quotation Approved..!";
	}

	if ($task == 'QUO_REJ') {		
		$data = array(
			':quo_approve_status' =>  "2",
			':quo_approve_id' =>$_SESSION['_user_id'],
			':quo_approve_date_time' =>date('Y-m-d H:i:s'),
			':quo_approve_remarks' =>$remarks,
			':quo_id' => $update_id			
		);
		$stmt->execute($data);

		$_SESSION['_msg'] = "Quotation Rejected..!";
	}
	
?>