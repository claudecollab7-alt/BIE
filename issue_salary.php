<?PHP

ob_start();

session_start();

require_once("inc/common/userclass.php");

isAdmin();

$conn = new dbconnect();
$dbconn= new dbhandler();


ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

/*ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);*/

//print_r($_REQUEST);exit;
/*
$_REQUEST['emp_id'];
$_REQUEST['salary_for'];
$_REQUEST['total_working_days'];
$_REQUEST['paid_days'];
$_REQUEST['work_days'];
$_REQUEST['emp_ctc'];
$_REQUEST['earned_salary'];
$_REQUEST['basic_salary'];
$_REQUEST['da_salary'];
$_REQUEST['basic_da'];
$_REQUEST['hra_salary'];
$_REQUEST['convay_salary'];
$_REQUEST['epf_amount'];
$_REQUEST['advance_amount'];
$_REQUEST['deduction_amount'];
$_REQUEST['balance_amount'];
$_REQUEST['net_amount'];
$_REQUEST['salary_type'];*/

$_REQUEST['bio_id'] = $dbconn->GetSingleReconrd("mst_employee","bio_id","rec_del_status = 1 AND emp_id",$_REQUEST['emp_id']);
//echo $_REQUEST['bio_id'];exit;
try {
	if($_REQUEST['salary_type'] == 'WOPF' || $_REQUEST['salary_type'] == 'WPF'){
		$stmt = null;				
		$stmt = $conn->prepare("INSERT INTO tbl_emp_monthly_salary (emp_id, bio_id, branch_id, salary_for, total_working_days, paid_days, work_days, fh_days, emp_lwp,  emp_ctc, earned_salary, basic_salary, da_salary, basic_da, hra_salary, convay_salary, epf_amount, advance_amount, deduction_amount, balance_amount,debit_amount, debit_deduction_payment, balance_debit_amount, net_amount, salary_type, salary_status, issued_by,issued_dtm) VALUES (:emp_id, :bio_id, :branch_id, :salary_for, :total_working_days, :paid_days, :work_days, :fh_days, :emp_lwp, :emp_ctc, :earned_salary, :basic_salary, :da_salary, :basic_da, :hra_salary, :convay_salary, :epf_amount, :advance_amount, :deduction_amount, :balance_amount,:debit_amount, :debit_deduction_payment, :balance_debit_amount, :net_amount, :salary_type, :salary_status, :issued_by, :issued_dtm)");		
		$data = array(				
			':emp_id' => $_REQUEST['emp_id'],
			':bio_id' => $_REQUEST['bio_id'],
			':branch_id' => $_SESSION['_user_branch'],
			':salary_for' => $_REQUEST['salary_for'],
			':total_working_days' => $_REQUEST['total_working_days'],
			':paid_days' => $_REQUEST['paid_days'],
			':work_days' => $_REQUEST['work_days'],
			':fh_days' => $_REQUEST['fh_days'],
			':emp_lwp' => $_REQUEST['emp_lwp'],
			':emp_ctc' => $_REQUEST['emp_ctc'],
			':earned_salary' => $_REQUEST['earned_salary'],
			':basic_salary' => $_REQUEST['basic_salary'],
			':da_salary' => $_REQUEST['da_salary'],
			':basic_da' => $_REQUEST['basic_da'],
			':hra_salary' => $_REQUEST['hra_salary'],
			':convay_salary' => $_REQUEST['convay_salary'],
			':epf_amount' => $_REQUEST['epf_amount'],
			':advance_amount' => $_REQUEST['advance_amount'],
			':deduction_amount' => $_REQUEST['deduction_amount'],
			':balance_amount' => $_REQUEST['balance_amount'],
			':debit_amount' => $_REQUEST['debit_amount'],
			':debit_deduction_payment' => $_REQUEST['debit_deduction_payment'],
			':balance_debit_amount' => $_REQUEST['balance_debit_amount'],
			':net_amount' => $_REQUEST['net_amount'],
			':salary_type' => $_REQUEST['salary_type'],
			':salary_status' => 1,
			':issued_by' => $_SESSION['_user_id'],
			':issued_dtm' => date('Y-m-d H:i:s')	
		);
		$stmt->execute($data);
		$last_id = $conn->lastInsertId();
		
		/* Account update */
		$emp_ledger_id = $dbconn->GetSingleReconrd("mst_employee","ledger_id","emp_id",$_REQUEST['emp_id']);
		$acc_main_entry = $conn->prepare("INSERT INTO tbl_accounts (acc_date, emp_id, voucher_type, record_type, emp_sal_id, acc_tran_value, dr_ledger_id, cr_ledger_id) VALUES (:acc_date, :emp_id, :voucher_type, :record_type, :emp_sal_id, :acc_tran_value, :dr_ledger_id, :cr_ledger_id)");
		$acc_main_data = array(				
			':acc_date' => date('Y-m-d'),		
			':emp_id' => $_REQUEST['emp_id'],
			':voucher_type' => "Employee Salary",
			':record_type' => "M",
			':emp_sal_id' => $last_id,
			':acc_tran_value' => $_REQUEST['net_amount'],
			':dr_ledger_id' => 1,
			':cr_ledger_id' => $emp_ledger_id
		);
		$acc_main_entry->execute($acc_main_data);		
		/* Account update */
		 $_SESSION['_msg'] = "Salary issued successfully..!";
	}elseif($_REQUEST['salary_type'] == 'OT'){
		$stmt = null;				
		$stmt = $conn->prepare("INSERT INTO tbl_emp_monthly_salary (emp_id, bio_id, branch_id, salary_for, total_ot_hours, amount_per_hour, ot_amount, tiffen_allow_days, tiffen_amount, total_ot_amount, salary_type, salary_status, issued_by, 	issued_dtm) VALUES (:emp_id, :bio_id, :branch_id, :salary_for,:total_ot_hours, :amount_per_hour, :ot_amount, :tiffen_allow_days, :tiffen_amount, :total_ot_amount,  :salary_type, :salary_status, :issued_by, :issued_dtm)");		
		$data = array(				
			':emp_id' => $_REQUEST['emp_id'],
			':bio_id' => $_REQUEST['bio_id'],
			':branch_id' => $_SESSION['_user_branch'],
			':salary_for' => $_REQUEST['salary_for'],
			':total_ot_hours' => $_REQUEST['total_ot_hours'],
			':amount_per_hour' => $_REQUEST['amount_per_hour'],
			':ot_amount' => $_REQUEST['ot_amount'],
			':tiffen_allow_days' => $_REQUEST['tiffen_allow_days'],
			':tiffen_amount' => $_REQUEST['tiffen_amount'],
			':total_ot_amount' => $_REQUEST['total_ot_amount'],
			':salary_type' => $_REQUEST['salary_type'],
			':salary_status' => 1,
			':issued_by' => $_SESSION['_user_id'],
			':issued_dtm' => date('Y-m-d H:i:s')	
		);
		$stmt->execute($data);
		$last_id = $conn->lastInsertId();
		
		/* Account update */
		$emp_ledger_id = $dbconn->GetSingleReconrd("mst_employee","ledger_id","emp_id",$_REQUEST['emp_id']);
		$acc_main_entry = $conn->prepare("INSERT INTO tbl_accounts (acc_date, emp_id, voucher_type, record_type, emp_sal_id, acc_tran_value, dr_ledger_id, cr_ledger_id) VALUES (:acc_date, :emp_id, :voucher_type, :record_type, :emp_sal_id, :acc_tran_value, :dr_ledger_id, :cr_ledger_id)");
		$acc_main_data = array(				
			':acc_date' => date('Y-m-d'),		
			':emp_id' => $_REQUEST['emp_id'],
			':voucher_type' => "Employee OT Salary",
			':record_type' => "M",
			':emp_sal_id' => $last_id,
			':acc_tran_value' => $_REQUEST['total_ot_amount'],
			':dr_ledger_id' => 1,
			':cr_ledger_id' => $emp_ledger_id,
		);
		$acc_main_entry->execute($acc_main_data);

		$_SESSION['_msg'] = "Salary issued successfully..!";
	}
}catch(Exception $e){
		$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
		 $_SESSION['_msg_err'] = $str;		
}
		
?>
