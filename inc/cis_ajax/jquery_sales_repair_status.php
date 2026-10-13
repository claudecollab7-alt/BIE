<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
require_once("../common/csrf.php");
require_once("../common/access.php");
session_start();
$conn = new dbconnect();
$dbconn= new dbhandler();

// 2026-10-13 login + csrf + check - send for approval and approve are admin-only buttons
// 2026-10-13 send for approval / approve from Task Settings (SALES_REPAIR V / A)
// 2026-10-13 repair's branch (company_id) passed - a branch-only verifier / approver was always refused
// 2026-10-13 send for approval only when not sent yet, approve only when sent and not approved
fnAjaxGuard();
$repair_branch = $dbconn->GetSingleReconrd("tbl_sales_repair", "company_id", "sal_repair_id", (int)(isset($_POST["id"]) ? $_POST["id"] : 0));
if (!fnTaskCan('SALES_REPAIR', (isset($_POST['mode']) && $_POST['mode'] == 'verify') ? 'V' : 'A', $repair_branch)) {
	fnAccessDenied(true);
}

$sal_repair_id = $_POST["id"];
$mode = $_POST["mode"];
if ($sal_repair_id && $mode == 'verify') 
{		
	try
	{
		$stmt = null;				
		$stmt = $conn->prepare("UPDATE  tbl_sales_repair SET sal_repair_verify_status = :sal_repair_verify_status, sal_repair_verify_by = :sal_repair_verify_by, sal_repair_verify_date_time = :sal_repair_verify_date_time
				WHERE sal_repair_id = :sal_repair_id AND sal_repair_verify_status = 0");		
		$data = array(				
			':sal_repair_id' => $sal_repair_id,
			':sal_repair_verify_status' => 1,
			':sal_repair_verify_by' => $_SESSION['_user_id'],
			':sal_repair_verify_date_time' => date('Y-m-d H:i:s')
		);
		
		$stmt->execute($data);
	}
	catch (Exception $e)
	{
		fnLogError($e);		
		$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
		$_SESSION['_msg_err'] = $str;			
	}	
}

if ($sal_repair_id && $mode == 'approve') 
{		
	try
	{
		$stmt = null;				
		$stmt = $conn->prepare("UPDATE  tbl_sales_repair SET sal_repair_approve_status = :sal_repair_approve_status, sal_repair_approve_by = :sal_repair_approve_by, sal_repair_approve_date_time = :sal_repair_approve_date_time
				WHERE sal_repair_id = :sal_repair_id AND sal_repair_verify_status = 1 AND sal_repair_approve_status = 0");		
		$data = array(				
			':sal_repair_id' => $sal_repair_id,
			':sal_repair_approve_status' => 1,
			':sal_repair_approve_by' => $_SESSION['_user_id'],
			':sal_repair_approve_date_time' => date('Y-m-d H:i:s')
		);
		$stmt->execute($data);


		//****Accounts****


		//[**Main Entry**]
		// $select_so_main = $conn->query("SELECT * FROM tbl_sales_repair WHERE sal_repair_id='".$sal_repair_id."'");
		// if ($select_so_main->rowCount()>0)
		// {
		// 	$acc_main_entry = $conn->prepare("INSERT INTO tbl_accounts (acc_date, supp_id, sal_repair_id, acc_tran_value, record_type, voucher_type, cr_ledger_id, dr_ledger_id, tax_value) VALUES (:acc_date, :supp_id, :sal_repair_id, :acc_tran_value, :record_type, :voucher_type, :cr_ledger_id, :dr_ledger_id, :tax_value)");		
		// 	while ($main = $select_so_main->fetch()) 
		// 	{
		// 		$item_tax_val = $dbconn->GetSingleReconrd("tbl_sales_repair_details","SUM(repair_tax_val)","sal_repair_id",$sal_repair_id);


		// 		$total_tax_value = $item_tax_val;

		// 		$supp_ledger_id = $dbconn->GetSingleReconrd("mst_supplier","ledger_id","supp_id",$main->supp_id);


		// 		$sales_ledger_id = $dbconn->GetSingleReconrd("mst_accounts_setting","ledger_id","acc_task_status='1' AND acc_task_id",4);


				

		// 		$acc_main_data = array(				
		// 			':acc_date' => date('Y-m-d'),		
		// 			':supp_id' => $main->supp_id,
		// 			':sal_repair_id' => $sal_repair_id,
		// 			':acc_tran_value' => $main->sal_repair_value,
		// 			':record_type' => 'M',
		// 			':voucher_type' => 'Credit Note',
		// 			':dr_ledger_id' => $sales_ledger_id,
		// 			':cr_ledger_id' => $supp_ledger_id,
		// 			':tax_value' => $total_tax_value
		// 		);
		// 		$acc_main_entry->execute($acc_main_data);
					
				
		// 	}

		// }

	}
	catch (Exception $e)
	{
		fnLogError($e);		
		$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
		$_SESSION['_msg_err'] = $str;			
	}	
}	
// nothing changed: the document was already approved / rejected or is not waiting for this step
if (isset($stmt) && $stmt instanceof PDOStatement && $stmt->rowCount() == 0) {
	$_SESSION['_msg'] = '';
	$_SESSION['_msg_err'] = "Not waiting for approval - it may already be approved or rejected..!";
	http_response_code(409);
}

?>