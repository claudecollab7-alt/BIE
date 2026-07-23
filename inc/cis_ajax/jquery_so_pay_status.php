<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
session_start();
$conn = new dbconnect();
$dbconn= new dbhandler();

$so_id = $_POST["so_id"];
$remarks = $_POST["remarks"];
if (isset($_REQUEST['so_id'])) 
{ 
	if($_REQUEST['mode'] == 'admin')
	{
		try
		{
			$stmt = null;				
			$stmt = $conn->prepare("UPDATE tbl_sales_order SET pay_status = :pay_status, so_user_approve_by = :so_user_approve_by, pay_remarks = :pay_remarks, so_verify_status = :so_verify_status, so_verify_by = :so_verify_by, so_verify_date_time = :so_verify_date_time WHERE so_id = :so_id");		
			$data = array(				
				':so_id' => $so_id,
				':pay_status' => 3,
				':so_user_approve_by' => 'A',
				':pay_remarks' => $remarks,
				':so_verify_status' => 1,
				':so_verify_by' => $_SESSION['_userid'],
				':so_verify_date_time' => date('Y-m-d H:i:s')
			);
			
			$stmt->execute($data);

		}
		catch (Exception $e)
		{		
			$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
			$_SESSION['_msg_err'] = $str;			
		}	
	}

	if($_REQUEST['mode'] == 'employee')
	{
		try
		{
			$stmt = null;				
			$stmt = $conn->prepare("UPDATE tbl_sales_order SET pay_status = :pay_status, so_user_approve_by = :so_user_approve_by, accounts_verify_status = :accounts_verify_status, accounts_by = :accounts_by, accounts_date_time = :accounts_date_time WHERE so_id = :so_id");		
			$data = array(				
				':so_id' => $so_id,
				':pay_status' => 3,
				':so_user_approve_by' => 'E',
				':accounts_verify_status' => 1,
				':accounts_by' => $_SESSION['_userid'],
				':accounts_date_time' => date('Y-m-d H:i:s')
			);
			$stmt->execute($data);
			

		}
		catch (Exception $e)
		{		
			$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
			$_SESSION['_msg_err'] = $str;			
		}	
	}		
}


?>