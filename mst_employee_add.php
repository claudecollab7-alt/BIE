<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");
require_once("inc/common/css-js.php");



isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('isplay_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$_REQUEST['created_dtm'] = date('Y-m-d H:i:s');


if (isset($_POST['SAVE'])) {

    try {

        $file_pre = str_replace('/', '', $_REQUEST['emp_code']);

        if ($_FILES['emp_photo']['name'] != "") {
            $ext = pathinfo($_FILES['emp_photo']['name'], PATHINFO_EXTENSION);
            $customfilename = $file_pre . '_employee_photo.' . $ext;
            $_REQUEST['emp_photo'] = post_img($customfilename, $_FILES['emp_photo']['tmp_name'], "project_img/emp_photo/");
        }

        if ($_FILES['emp_pan_copy']['name'] != "") {
            $ext = pathinfo($_FILES['emp_pan_copy']['name'], PATHINFO_EXTENSION);
            $customfilename = $file_pre . '_employee_pan.' . $ext;
            $_REQUEST['emp_pan_copy'] = post_img($customfilename, $_FILES['emp_pan_copy']['tmp_name'], "project_img/emp_proofs/");
        }

        if ($_FILES['emp_aadhar_copy']['name'] != "") {
            $ext = pathinfo($_FILES['emp_aadhar_copy']['name'], PATHINFO_EXTENSION);
            $customfilename = $file_pre . '_employee_aadhar.' . $ext;
            $_REQUEST['emp_aadhar_copy'] = post_img($customfilename, $_FILES['emp_aadhar_copy']['tmp_name'], "project_img/emp_proofs/");
        }
        if ($_FILES['emp_add_proof_copy']['name'] != "") {
            $ext = pathinfo($_FILES['emp_add_proof_copy']['name'], PATHINFO_EXTENSION);
            $customfilename = $file_pre . '_employee_address.' . $ext;
            $_REQUEST['emp_add_proof_copy'] = post_img($customfilename, $_FILES['emp_add_proof_copy']['tmp_name'], "project_img/emp_proofs/");
        }
        if ($_FILES['emp_agreement_order']['name'] != "") {
            $ext = pathinfo($_FILES['emp_agreement_order']['name'], PATHINFO_EXTENSION);
            $customfilename = $file_pre . '_employee_agreement.' . $ext;
            $_REQUEST['emp_agreement_order'] = post_img($customfilename, $_FILES['emp_agreement_order']['tmp_name'], "project_img/emp_agreement_order/");
        }
        if ($_FILES['emp_appointment_order']['name'] != "") {
            $ext = pathinfo($_FILES['emp_appointment_order']['name'], PATHINFO_EXTENSION);
            $customfilename = $file_pre . '_employee_appointment.' . $ext;
            $_REQUEST['emp_appointment_order'] = post_img($customfilename, $_FILES['emp_appointment_order']['tmp_name'], "project_img/emp_appointment_order/");
        }

        if ($_REQUEST['copy_address'] == 1) {
            $_REQUEST['emp_pr_add1'] =  $_REQUEST['emp_cr_add1'];
            $_REQUEST['emp_pr_add2'] =  $_REQUEST['emp_cr_add2'];
            $_REQUEST['pr_state_id'] =  $_REQUEST['cr_state_id'];
            $_REQUEST['pr_district_id'] =  $_REQUEST['cr_district_id'];
            $_REQUEST['pr_city_id'] =  $_REQUEST['cr_city_id'];
            $_REQUEST['pr_pincode'] =  $_REQUEST['cr_pincode'];
        } else {
            $_REQUEST['copy_address'] = '';
        }


        $ledger_type = $dbconn->GetSingleReconrd("mst_accounts_group", "group_type", "group_id", $_REQUEST['group_id']);    
        $credit_ledger = null;
        $credit_ledger = $conn->prepare("INSERT INTO mst_ledger (group_id, ledger_name, ledger_type, open_bal, open_bal_type) VALUES (:group_id, :ledger_name, :ledger_type, :open_bal, :open_bal_type)");
        $credit_data = array(
            ':group_id' => $_REQUEST['group_id'],
            ':ledger_name' => ucwords($_REQUEST['ledger_name']),
            ':ledger_type' => $ledger_type,
            ':open_bal' => $_REQUEST['open_bal'],
            ':open_bal_type' => $_REQUEST['open_bal_type']
        );
        $credit_ledger->execute($credit_data);
        $ledger_last_id = $conn->lastInsertId();



    if($_REQUEST['emp_pf'] == '1' || $_REQUEST['emp_pf'] == "" )
	{
		$_REQUEST['emp_epf_no'] = "";
		$_REQUEST['emp_uan_no'] = "";
	}

	if($_REQUEST['staff_status'] == '1')
	{
		$_REQUEST['emp_epf_no'] = "";
		$_REQUEST['emp_uan_no'] = "";
	}

        $stmt = null;

        $stmt = $conn->prepare("INSERT INTO mst_employee ( branch_id, prefix, emp_name, emp_fat_hus_name, emp_slno, emp_code, bio_id, emp_dob, emp_type, staff_status, labour_status, emp_pf, labour_id, department_id, designation_id, emp_mobile, emp_phone, emp_email,emp_blood,emp_photo,emp_cr_add1, emp_cr_add2, cr_state_id, cr_district_id, cr_city_id, cr_pincode, emp_pr_add1, emp_pr_add2, pr_state_id, pr_district_id, pr_city_id, pr_pincode, copy_address, bank_acc_no, bank_acc_name, bank_name, bank_branch, ifsc_code, branch_pincode, emp_pan_no, emp_aadhar_no, emp_add_proof, emp_pan_copy, emp_aadhar_copy, emp_add_proof_copy, emp_marital_status, anniversary_date, spouse_name, children_no, spouse_cont_no, spouse_dob, emp_date_join, login_access, emp_login_name, emp_login_password, emp_epf_no, emp_uan_no, emp_agreement_order, emp_appointment_order, emp_nominee, emp_nominee_relation, emp_advance_bal, ledger_id,emp_credit_days,emp_pay_mode, created_by, created_dtm) VALUES(:branch_id, :prefix, :emp_name, :emp_fat_hus_name, :emp_slno, :emp_code, :bio_id, :emp_dob, :emp_type, :staff_status, :labour_status, :emp_pf, :labour_id, :department_id, :designation_id,:emp_mobile, :emp_phone, :emp_email, :emp_blood, :emp_photo,:emp_cr_add1, :emp_cr_add2, :cr_state_id, :cr_district_id, :cr_city_id, :cr_pincode, :emp_pr_add1, :emp_pr_add2, :pr_state_id, :pr_district_id, :pr_city_id, :pr_pincode, :copy_address, :bank_acc_no, :bank_acc_name, :bank_name, :bank_branch, :ifsc_code, :branch_pincode, :emp_pan_no, :emp_aadhar_no, :emp_add_proof, :emp_pan_copy, :emp_aadhar_copy, :emp_add_proof_copy, :emp_marital_status, :anniversary_date, :spouse_name, :children_no, :spouse_cont_no, :spouse_dob, :emp_date_join, :login_access, :emp_login_name, :emp_login_password, :emp_epf_no, :emp_uan_no, :emp_agreement_order, :emp_appointment_order, :emp_nominee, :emp_nominee_relation, :emp_advance_bal, :ledger_id, :emp_credit_days, :emp_pay_mode, :created_by, :created_dtm)");

        $data = array(
            ':branch_id' => $_REQUEST['branch_id'],
            ':prefix' => $_REQUEST['prefix'],
            ':emp_name' => $_REQUEST['emp_name'],
            ':emp_fat_hus_name' => ucwords($_REQUEST['emp_fat_hus_name']),
            ':emp_slno' => trim($_REQUEST['emp_slno']),
            ':emp_code' => $_REQUEST['emp_code'],
            ':bio_id' => $_REQUEST['bio_id'],
            ':emp_dob' => $_REQUEST['emp_dob'],
            ':emp_type' => $_REQUEST['emp_type'],
            ':staff_status' => $_REQUEST['staff_status'],
            ':labour_status' => $_REQUEST['labour_status'],
            ':emp_pf' => $_REQUEST['emp_pf'],
            ':labour_id' => $_REQUEST['labour_id'],
            ':department_id' => $_REQUEST['department_id'],
            ':designation_id' => $_REQUEST['designation_id'],
            ':emp_mobile' => $_REQUEST['emp_mobile1'],
            ':emp_phone' => $_REQUEST['emp_mobile2'],
            ':emp_email' => $_REQUEST['emp_email'],
            ':emp_blood' => $_REQUEST['emp_blood'],
            ':emp_photo' => $_REQUEST['emp_photo'],
            ':emp_cr_add1' => $_REQUEST['emp_cr_add1'],
            ':emp_cr_add2' => $_REQUEST['emp_cr_add2'],
            ':cr_state_id' => $_REQUEST['cr_state_id'],
            ':cr_district_id' => $_REQUEST['cr_district_id'],
            ':cr_city_id' => $_REQUEST['cr_city_id'],
            ':cr_pincode' => $_REQUEST['cr_pincode'],
            ':emp_pr_add1' => $_REQUEST['emp_pr_add1'],
            ':emp_pr_add2' => $_REQUEST['emp_pr_add2'],
            ':pr_state_id' => $_REQUEST['pr_state_id'],
            ':pr_district_id' => $_REQUEST['pr_district_id'],
            ':pr_city_id' => $_REQUEST['pr_city_id'],
            ':pr_pincode' => $_REQUEST['pr_pincode'],
            ':copy_address' => $_REQUEST['copy_address'],
            ':bank_acc_no' => $_REQUEST['bank_acc_no'],
            ':bank_acc_name' => $_REQUEST['bank_acc_name'],
            ':bank_name' => $_REQUEST['bank_name'],
            ':bank_branch' => $_REQUEST['bank_branch'],
            ':ifsc_code' => $_REQUEST['ifsc_code'],
            ':branch_pincode' => $_REQUEST['branch_pincode'],
            ':emp_pan_no' => $_REQUEST['emp_pan_no'],
            ':emp_aadhar_no' => $_REQUEST['emp_aadhar_no'],
            ':emp_add_proof' => $_REQUEST['emp_add_proof'],
            ':emp_pan_copy' => $_REQUEST['emp_pan_copy'],
            ':emp_aadhar_copy' => $_REQUEST['emp_aadhar_copy'],
            ':emp_add_proof_copy' => $_REQUEST['emp_add_proof_copy'],
            ':emp_marital_status' => $_REQUEST['emp_marital_status'],
            ':anniversary_date' => $_REQUEST['anniversary_date'],
            ':spouse_name' => $_REQUEST['spouse_name'],
            ':children_no' => $_REQUEST['children_no'],
            ':spouse_cont_no' => $_REQUEST['spouse_cont_no'],
            ':spouse_dob' => $_REQUEST['spouse_dob'],
            ':emp_date_join' => $_REQUEST['emp_date_join'],
            ':login_access' => $_REQUEST['login_access'],
            ':emp_login_name' => $_REQUEST['emp_login_name'],
            ':emp_login_password' => $_REQUEST['emp_login_password'],
            ':emp_epf_no' => $_REQUEST['emp_epf_no'],
            ':emp_uan_no' => $_REQUEST['emp_uan_no'],
            ':emp_agreement_order' => $_REQUEST['emp_agreement_order'],
            ':emp_appointment_order' => $_REQUEST['emp_appointment_order'],
            ':emp_nominee' => $_REQUEST['emp_nominee'],
            ':emp_nominee_relation' => $_REQUEST['emp_nominee_relation'],
            ':emp_advance_bal' => $_REQUEST['emp_advance_bal'],
            ':ledger_id' => $ledger_last_id,
            ':emp_credit_days' => $_REQUEST['emp_credit_days'],
            ':emp_pay_mode' => $_REQUEST['emp_pay_mode'],
            ':created_by' =>  $_SESSION['_user_id'],
		    ':created_dtm' => $_REQUEST['created_dtm']

        );
            
        // print_r($data);
        $stmt->execute($data);
        $last_id = $conn->lastInsertId();

        $stmt = null;
        $stmt = $conn->prepare("INSERT INTO tbl_asset_details (emp_id, asset_id, issue_date, asset_qty, asset_value, sim_no, mobile_no, sim_limit) VALUES (:emp_id, :asset_id, :issue_date, :asset_qty, :asset_value, :sim_no, :mobile_no,:sim_limit)");
        if(isset($_REQUEST['hidd_asset_id'])){
            $row_count = count($_REQUEST['hidd_asset_id']);
            for ($n = 0; $n < $row_count; $n++) {
                $data = array(
                    ':emp_id' => $last_id,
                    ':asset_id' => $_REQUEST['hidd_asset_id'][$n],
                    ':issue_date' => $_REQUEST['hidd_asset_date'][$n],
                    ':asset_qty' => $_REQUEST['hidd_asset_qty'][$n],
                    ':asset_value' => $_REQUEST['hidd_asset_val'][$n],
                    ':sim_no' => $_REQUEST['hidd_sim_no'][$n],
                    ':mobile_no' => $_REQUEST['hidd_mobile_no'][$n],
                    ':sim_limit' => $_REQUEST['hidd_sim_limit'][$n]
                );
                // print_r($data);
                $stmt->execute($data);
            }
        }
        

if ($_REQUEST['emp_type'] == 1) {
        $stmt = null;
        $stmt = $conn->prepare("INSERT INTO mst_employee_certificate (emp_id, emp_cert_name, emp_cert_copy) VALUES (:emp_id, :emp_cert_name,:emp_cert_copy)");

        if(isset($_REQUEST['emp_certname']) && $_REQUEST['emp_certcopy'] !=''){
            $row_count = count($_REQUEST['emp_certname']);

            for ($n = 0; $n < $row_count; $n++) {

                if ($_FILES['emp_certcopy']['name'][$n] != "") {
                    $ext = pathinfo($_FILES['emp_certcopy']['name'][$n], PATHINFO_EXTENSION);
                    $customfilename = $file_pre . '_employee_Certificate.' . $ext;
                    $_REQUEST['emp_certcopy'] = post_img($customfilename, $_FILES['emp_certcopy']['tmp_name'][$n], "project_img/emp_certificates/");
                }

                $data = array(
                    ':emp_id' => $last_id,
                    ':emp_cert_name' => $_REQUEST['emp_certname'][$n],
                    ':emp_cert_copy' => $_REQUEST['emp_certcopy']
                );
                // print_r($data); 
                $stmt->execute($data);
            }
        }
    }
    //echo"fsdsff";die();

        // die();


        if ($_REQUEST['login_access'] == 1) {
            if ($_REQUEST['branch_code'] == "H") {
                $usr_group = "H";
            } else {
                $usr_group = "B";
            }
            $login_detail = null;
            $login_detail =  $conn->prepare("INSERT INTO tbl_user(emp_id,usr_group,usr_name, usr_email, usr_mobile, usr_logname, usr_logpwd, usr_type, usr_access, pw_hint, branch_id, usr_status) VALUES(:emp_id, :usr_group, :usr_name, :usr_email, :usr_mobile, :usr_logname, :usr_logpwd, :usr_type, :usr_access, :pw_hint, :branch_id, :usr_status)");

            $login = array(
                // 
                ':usr_group' => $usr_group,
                ':emp_id' => $last_id,
                ':usr_name' => $_REQUEST['emp_name'],
                ':usr_email' => $_REQUEST['emp_email'],
                ':usr_mobile' => $_REQUEST['emp_mobile1'],
                ':usr_logname' => $_REQUEST['emp_login_name'],
                ':usr_logpwd' => StandardHash($_REQUEST['emp_login_password']),
                ':usr_type' => 'S',
                ':usr_access' => 1,
                ':pw_hint' => $_REQUEST['emp_login_password'],
                ':branch_id' => $_REQUEST['branch_id'],
                ':usr_status' => 1
            );
            $login_detail->execute($login);
        }


        if($_REQUEST['emp_type'] == 1)
	{
		header("location:lst_staff.php");	
	}
	else if($_REQUEST['emp_type'] == 2)
	{
		header("location:lst_labour.php");	
	}
	else
	{
		header("location:lst_employee.php");	
        }
        die();
    } catch (Exception $e) {
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
        header("location:lst_employee.php");	
	}

   
}

if (isset($_POST['UPDATE'])) {
    try {

    $file_pre = str_replace('/', '', $_REQUEST['emp_code']);

    $update_id = $_REQUEST['txtHid'];

    $sql =  "DELETE FROM mst_employee_certificate WHERE emp_id = '" . $update_id . "'";
    $result = $conn->prepare($sql);
    $result->execute();

    $stmt = null;
    $stmt = $conn->prepare("INSERT INTO mst_employee_certificate (emp_id, emp_cert_name, emp_cert_copy) VALUES (:emp_id, :emp_cert_name,:emp_cert_copy)");
 

if(isset($_REQUEST['emp_certname']) && $_REQUEST['emp_certname'] !=''){
    $row_count = count($_REQUEST['emp_certname']);
    for ($n = 0; $n < $row_count; $n++) {

        if ($_FILES['emp_certcopy']['name'][$n] != "") {

            removeFile("project_img/emp_certificates/" . $_REQUEST["hide_emp_certcopy"][$n]);

            $ext = pathinfo($_FILES['emp_certcopy']['name'][$n], PATHINFO_EXTENSION);
            $customfilename = $file_pre . '_employee_Certificate.' . $ext;
            $_REQUEST['emp_certcopy'] = post_img($customfilename, $_FILES['emp_certcopy']['tmp_name'][$n], "project_img/emp_certificates/");
        } else {

            $_REQUEST['emp_certcopy'] = $_REQUEST["hide_emp_certcopy"][$n];
        }

        $data = array(
            ':emp_id' => $update_id,
            ':emp_cert_name' => $_REQUEST['emp_certname'][$n],
            ':emp_cert_copy' =>  $_REQUEST['emp_certcopy']
        );
        // print_r($data);
        $stmt->execute($data);
    }
}
    // die();

    if ($_FILES['emp_photo']['name'] != "") {
        if ($_REQUEST["hide_emp_photo"] != "") {
            removeFile("project_img/emp_photo/" . $_REQUEST["hide_emp_photo"]);
        }

        $ext = pathinfo($_FILES['emp_photo']['name'], PATHINFO_EXTENSION);

        $customfilename = $file_pre . '_employee_photo.' . $ext;
        $_REQUEST['emp_photo'] =  post_img($customfilename, $_FILES['emp_photo']['tmp_name'], "project_img/emp_photo/");
    } else {
        $_REQUEST['emp_photo'] = $_REQUEST["hide_emp_photo"];
    }

    if ($_FILES['emp_pan_copy']['name'] != "") {
        if ($_REQUEST["hide_emp_pan_copy"] != "") {
            removeFile("project_img/emp_proofs/" . $_REQUEST["hide_emp_pan_copy"]);
        }

        $ext = pathinfo($_FILES['emp_pan_copy']['name'], PATHINFO_EXTENSION);
        $customfilename = $file_pre . '_employee_pan.' . $ext;
        $_REQUEST['emp_pan_copy'] = post_img($customfilename, $_FILES['emp_pan_copy']['tmp_name'], "project_img/emp_proofs/");
    } else {
        $_REQUEST['emp_pan_copy'] = $_REQUEST["hide_emp_pan_copy"];
    }


    if ($_FILES['emp_aadhar_copy']['name'] != "") {
        removeFile("project_img/emp_proofs/" . $_REQUEST["hide_emp_aadhar_copy"]);
        $ext = pathinfo($_FILES['emp_aadhar_copy']['name'], PATHINFO_EXTENSION);
        $customfilename = $file_pre . '_employee_aadhar.' . $ext;
        $_REQUEST['emp_aadhar_copy'] = post_img($customfilename, $_FILES['emp_aadhar_copy']['tmp_name'], "project_img/emp_proofs/");
    } else {
        $_REQUEST['emp_aadhar_copy'] = $_REQUEST["hide_emp_aadhar_copy"];
    }

    if ($_FILES['emp_add_proof_copy']['name'] != "") {
        removeFile("project_img/emp_proofs/" . $_REQUEST["hide_emp_add_proof_copy"]);
        $ext = pathinfo($_FILES['emp_add_proof_copy']['name'], PATHINFO_EXTENSION);
        $customfilename = $file_pre . '_employee_address.' . $ext;
        $_REQUEST['emp_add_proof_copy'] = post_img($customfilename, $_FILES['emp_add_proof_copy']['tmp_name'], "project_img/emp_proofs/");
    } else {
        $_REQUEST['emp_add_proof_copy'] = $_REQUEST["hide_emp_add_proof_copy"];
    }

    if ($_FILES['emp_agreement_order']['name'] != "") {
        removeFile("project_img/emp_agreement_order/" . $_REQUEST["hide_emp_agreement_order"]);

        $ext = pathinfo($_FILES['emp_agreement_order']['name'], PATHINFO_EXTENSION);
        $customfilename = $file_pre . '_employee_agreement.' . $ext;
        $_REQUEST['emp_agreement_order'] = post_img($customfilename, $_FILES['emp_agreement_order']['tmp_name'], "project_img/emp_agreement_order/");
    } else {
        $_REQUEST['emp_agreement_order'] = $_REQUEST["hide_emp_agreement_order"];
    }

    if ($_FILES['emp_appointment_order']['name'] != "") {
        removeFile("project_img/emp_appointment_order/" . $_REQUEST["hide_emp_appointment_order"]);

        $ext = pathinfo($_FILES['emp_appointment_order']['name'], PATHINFO_EXTENSION);
        $customfilename = $file_pre . '_employee_appointment.' . $ext;
        $_REQUEST['emp_appointment_order'] = post_img($customfilename, $_FILES['emp_appointment_order']['tmp_name'], "project_img/emp_appointment_order/");
    } else {
        $_REQUEST['emp_appointment_order'] = $_REQUEST["hide_emp_appointment_order"];
    }


    if ($_REQUEST['emp_dob'] != "") {
        $_REQUEST['emp_dob'] = date("Y-m-d", strtotime($_REQUEST['emp_dob']));
    }
    if ($_REQUEST['anniversary_date'] != "") {
        $_REQUEST['anniversary_date'] = date("Y-m-d", strtotime($_REQUEST['anniversary_date']));
    }
    if ($_REQUEST['spouse_dob'] != "") {
        $_REQUEST['spouse_dob'] = date("Y-m-d", strtotime($_REQUEST['spouse_dob']));
    }
    if ($_REQUEST['emp_date_join'] != "") {
        $_REQUEST['emp_date_join'] = date("Y-m-d", strtotime($_REQUEST['emp_date_join']));
    }


    $_REQUEST['emp_pan_no'] = strtoupper($_REQUEST['emp_pan_no']);
    $_REQUEST['ifsc_code'] = strtoupper($_REQUEST['ifsc_code']);


    // $_REQUEST['usr_logname'] = $_REQUEST['usr_name'];
    // $_REQUEST['usr_logpwd'] = StandardHash($_REQUEST['pw_hint']);

    if($_REQUEST['emp_pf'] == '1')
    {
    	$_REQUEST['emp_epf_no'] = "";
    	$_REQUEST['emp_uan_no'] = "";
    }
    if($_REQUEST['staff_status'] == '1')
    {
    	$_REQUEST['emp_epf_no'] = "";
    	$_REQUEST['emp_uan_no'] = "";
    }


    if ($_REQUEST['emp_marital_status'] == '0') {
        $_REQUEST['anniversary_date'] = "";
        $_REQUEST['children_no'] = "";
        $_REQUEST['spouse_name'] = "";
        $_REQUEST['spouse_cont_no'] = "";
        $_REQUEST['spouse_dob'] = "";
    }


    if ($_REQUEST['copy_address'] == 1) {
        $_REQUEST['emp_pr_add1'] =  $_REQUEST['emp_cr_add1'];
        $_REQUEST['emp_pr_add2'] =  $_REQUEST['emp_cr_add2'];
        $_REQUEST['pr_state_id'] =  $_REQUEST['cr_state_id'];
        $_REQUEST['pr_district_id'] =  $_REQUEST['cr_district_id'];
        $_REQUEST['pr_city_id'] =  $_REQUEST['cr_city_id'];
        $_REQUEST['pr_pincode'] =  $_REQUEST['cr_pincode'];
    } else {
        $_REQUEST['copy_address'] = '';
    }

    $ledger_id = $dbconn->GetSingleReconrd("mst_employee", "ledger_id", "emp_id", $update_id);
    if ($ledger_id > 0) {
        $ledger_type = $dbconn->GetSingleReconrd("mst_accounts_group", "group_type", "group_id", $_REQUEST['group_id']);
        $update_ledger = $conn->prepare("UPDATE  mst_ledger SET group_id = :group_id, ledger_name = :ledger_name, ledger_type = :ledger_type, open_bal = :open_bal, open_bal_type = :open_bal_type WHERE ledger_id = :ledger_id");
        $ledger_data = array(
            ':ledger_id' => $ledger_id,
            ':group_id' => $_REQUEST['group_id'],
            ':ledger_name' => ucwords($_REQUEST['ledger_name']),
            ':ledger_type' => $ledger_type,
            ':open_bal' => $_REQUEST['open_bal'],
            ':open_bal_type' => $_REQUEST['open_bal_type']
        );
        $update_ledger->execute($ledger_data);
    } else {
        $ledger_type = $dbconn->GetSingleReconrd("mst_accounts_group", "group_type", "group_id", $_REQUEST['group_id']);

        $credit_ledger = $conn->prepare("INSERT INTO mst_ledger (group_id, ledger_name, ledger_type, open_bal, open_bal_type) VALUES (:group_id, :ledger_name, :ledger_type, :open_bal, :open_bal_type)");
        $credit_data = array(
            ':group_id' => $_REQUEST['group_id'],
            ':ledger_name' => ucwords($_REQUEST['ledger_name']),
            ':ledger_type' => $ledger_type,
            ':open_bal' => $_REQUEST['open_bal'],
            ':open_bal_type' => $_REQUEST['open_bal_type']
        );
        $credit_ledger->execute($credit_data);
        $ledger_id = $conn->lastInsertId();
    }

    $stmt = null;
    $stmt = $conn->prepare("UPDATE  mst_employee SET branch_id = :branch_id, prefix = :prefix, emp_name = :emp_name, emp_fat_hus_name = :emp_fat_hus_name, emp_code = :emp_code, bio_id = :bio_id, emp_dob = :emp_dob, emp_type = :emp_type, staff_status = :staff_status, labour_status = :labour_status, emp_pf = :emp_pf, labour_id = :labour_id, department_id = :department_id, designation_id = :designation_id, emp_blood = :emp_blood, emp_mobile = :emp_mobile, emp_email = :emp_email, emp_phone = :emp_phone, emp_photo = :emp_photo, emp_cr_add1 = :emp_cr_add1, emp_cr_add2 = :emp_cr_add2, cr_state_id = :cr_state_id, cr_district_id = :cr_district_id, cr_city_id = :cr_city_id, cr_pincode = :cr_pincode, emp_pr_add1 = :emp_pr_add1, emp_pr_add2 = :emp_pr_add2, pr_state_id = :pr_state_id, pr_district_id = :pr_district_id, pr_city_id = :pr_city_id, pr_pincode = :pr_pincode, bank_acc_no = :bank_acc_no, bank_acc_name = :bank_acc_name, bank_name = :bank_name, bank_branch = :bank_branch, ifsc_code = :ifsc_code, branch_pincode = :branch_pincode, emp_pan_no = :emp_pan_no, emp_aadhar_no = :emp_aadhar_no, emp_add_proof = :emp_add_proof, emp_pan_copy = :emp_pan_copy, emp_aadhar_copy = :emp_aadhar_copy, emp_add_proof_copy = :emp_add_proof_copy, emp_marital_status = :emp_marital_status, anniversary_date = :anniversary_date, spouse_name = :spouse_name, children_no = :children_no, spouse_cont_no = :spouse_cont_no, spouse_dob = :spouse_dob, emp_date_join = :emp_date_join, login_access = :login_access, emp_login_name = :emp_login_name, emp_login_password = :emp_login_password, emp_epf_no = :emp_epf_no, emp_uan_no = :emp_uan_no, emp_agreement_order = :emp_agreement_order, emp_appointment_order = :emp_appointment_order, emp_nominee = :emp_nominee, emp_nominee_relation = :emp_nominee_relation, emp_advance_bal = :emp_advance_bal,ledger_id = :ledger_id,emp_credit_days = :emp_credit_days, emp_pay_mode =:emp_pay_mode, updated_by =:updated_by, updated_dtm =:updated_dtm WHERE emp_id = :emp_id");
    $data = array(
        ':emp_id' => $update_id,
        ':branch_id' => $_REQUEST['branch_id'],
        ':prefix' => $_REQUEST['prefix'],
        ':emp_name' => ucwords($_REQUEST['emp_name']),
        ':emp_fat_hus_name' => ucwords($_REQUEST['emp_fat_hus_name']),
        ':emp_code' => $_REQUEST['emp_code'],
        ':bio_id' => $_REQUEST['bio_id'],
        ':emp_dob' => $_REQUEST['emp_dob'],
        ':emp_type' => $_REQUEST['emp_type'],
        ':staff_status' => $_REQUEST['staff_status'],
        ':labour_status' => $_REQUEST['labour_status'],
        ':emp_pf' => $_REQUEST['emp_pf'],
        ':labour_id' => $_REQUEST['labour_id'],
        ':department_id' => $_REQUEST['department_id'],
        ':designation_id' => $_REQUEST['designation_id'],
        ':emp_mobile' => $_REQUEST['emp_mobile1'],
        ':emp_phone' => $_REQUEST['emp_mobile2'],
        ':emp_email' => $_REQUEST['emp_email'],
        ':emp_blood' => $_REQUEST['emp_blood'],
        ':emp_photo' => $_REQUEST['emp_photo'],
        ':emp_cr_add1' => $_REQUEST['emp_cr_add1'],
        ':emp_cr_add2' => $_REQUEST['emp_cr_add2'],
        ':cr_state_id' => $_REQUEST['cr_state_id'],
        ':cr_district_id' => $_REQUEST['cr_district_id'],
        ':cr_city_id' => $_REQUEST['cr_city_id'],
        ':cr_pincode' => $_REQUEST['cr_pincode'],
        ':emp_pr_add1' => $_REQUEST['emp_pr_add1'],
        ':emp_pr_add2' => $_REQUEST['emp_pr_add2'],
        ':pr_state_id' => $_REQUEST['pr_state_id'],
        ':pr_district_id' => $_REQUEST['pr_district_id'],
        ':pr_city_id' => $_REQUEST['pr_city_id'],
        ':pr_pincode' => $_REQUEST['pr_pincode'],
        ':bank_acc_no' => $_REQUEST['bank_acc_no'],
        ':bank_acc_name' => $_REQUEST['bank_acc_name'],
        ':bank_name' => $_REQUEST['bank_name'],
        ':bank_branch' => $_REQUEST['bank_branch'],
        ':ifsc_code' => $_REQUEST['ifsc_code'],
        ':branch_pincode' => $_REQUEST['branch_pincode'],
        ':emp_pan_no' => $_REQUEST['emp_pan_no'],
        ':emp_aadhar_no' => $_REQUEST['emp_aadhar_no'],
        ':emp_add_proof' => $_REQUEST['emp_add_proof'],
        ':emp_pan_copy' => $_REQUEST['emp_pan_copy'],
        ':emp_aadhar_copy' => $_REQUEST['emp_aadhar_copy'],
        ':emp_add_proof_copy' => $_REQUEST['emp_add_proof_copy'],
        ':emp_marital_status' => $_REQUEST['emp_marital_status'],
        ':anniversary_date' => $_REQUEST['anniversary_date'],
        ':spouse_name' => $_REQUEST['spouse_name'],
        ':children_no' => $_REQUEST['children_no'],
        ':spouse_cont_no' => $_REQUEST['spouse_cont_no'],
        ':spouse_dob' => $_REQUEST['spouse_dob'],
        ':emp_date_join' => $_REQUEST['emp_date_join'],
        ':login_access' => $_REQUEST['login_access'],
        ':emp_login_name' => $_REQUEST['emp_login_name'],
        ':emp_login_password' => $_REQUEST['emp_login_password'],
        ':emp_epf_no' => $_REQUEST['emp_epf_no'],
        ':emp_uan_no' => $_REQUEST['emp_uan_no'],
        ':emp_agreement_order' => $_REQUEST['emp_agreement_order'],
        ':emp_appointment_order' => $_REQUEST['emp_appointment_order'],
        ':emp_nominee' => $_REQUEST['emp_nominee'],
        ':emp_nominee_relation' => $_REQUEST['emp_nominee_relation'],
        ':emp_advance_bal' => $_REQUEST['emp_advance_bal'],
        ':ledger_id' => $ledger_id,
        ':emp_credit_days' => $_REQUEST['emp_credit_days'],
        ':emp_pay_mode' => $_REQUEST['emp_pay_mode'],
        ':updated_by' => $_SESSION['_user_id'],
        ':updated_dtm' =>$_REQUEST['created_dtm']
    );
    $stmt->execute($data);

    $sql =  "DELETE FROM tbl_asset_details WHERE emp_id = '" . $update_id . "'";
    $result = $conn->prepare($sql);
    $result->execute();


    $stmt = null;
    $stmt = $conn->prepare("INSERT INTO tbl_asset_details (emp_id, asset_id, issue_date, asset_qty, asset_value, sim_no, mobile_no, sim_limit) VALUES (:emp_id, :asset_id, :issue_date, :asset_qty, :asset_value, :sim_no, :mobile_no,:sim_limit)");


if(isset($_REQUEST['hidd_asset_id'])){

    $row_count = count($_REQUEST['hidd_asset_id']);
    for ($n = 0; $n < $row_count; $n++) {
        $data_asset = array(
            ':emp_id' => $update_id,
            ':asset_id' => $_REQUEST['hidd_asset_id'][$n],
            ':issue_date' => $_REQUEST['hidd_asset_date'][$n],
            ':asset_qty' => $_REQUEST['hidd_asset_qty'][$n],
            ':asset_value' => $_REQUEST['hidd_asset_val'][$n],
            ':sim_no' => $_REQUEST['hidd_sim_no'][$n],
            ':mobile_no' => $_REQUEST['hidd_mobile_no'][$n],
            ':sim_limit' => $_REQUEST['hidd_sim_limit'][$n]
        );
        $stmt->execute($data_asset);
    }

}
    $usr_emp_id = $dbconn->GetSingleReconrd("tbl_user", "usr_id", "emp_id", $update_id);


    if ($usr_emp_id != '' && $_REQUEST['login_access'] == 1) {

        if ($_REQUEST['branch_code'] == "H") {
            $usr_group = "H";
        } else {
            $usr_group = "B";
        }

        $login_detail = $conn->prepare("UPDATE  tbl_user SET usr_group = :usr_group, usr_name = :usr_name,  usr_email = :usr_email, usr_mobile = :usr_mobile, usr_logname = :usr_logname, usr_logpwd = :usr_logpwd, usr_type = :usr_type,usr_access = :usr_access, pw_hint = :pw_hint, branch_id = :branch_id WHERE emp_id = :emp_id");
        $login = array(
            ':emp_id' => $update_id,
            ':usr_group' => $usr_group,
            ':usr_name' => $_REQUEST['emp_name'],
            ':usr_email' => $_REQUEST['emp_email'],
            ':usr_mobile' => $_REQUEST['emp_mobile1'],
            ':usr_logname' => $_REQUEST['emp_login_name'],
            ':usr_logpwd' => StandardHash($_REQUEST['emp_login_password']),
            ':usr_type' => 'S',
            ':usr_access' => 1,
            ':pw_hint' => $_REQUEST['emp_login_password'],
            ':branch_id' => $_REQUEST['branch_id']

        );
        // print_r($login);die();
        $login_detail->execute($login);
    } elseif ($usr_emp_id != '' && $_REQUEST['login_access'] == 0) {
        $login_detail = $conn->prepare("UPDATE  tbl_user SET usr_status = :usr_status WHERE emp_id = :emp_id");
        $login = array(
            ':emp_id' => $update_id,
            ':usr_status' => 0
        );
        $login_detail->execute($login);
    } elseif ($usr_emp_id == '' && $_REQUEST['login_access'] == 1) {
        $login_detail =  $conn->prepare("INSERT INTO tbl_user(usr_group,usr_name, usr_email, usr_mobile, usr_logname, usr_logpwd, usr_type, usr_access, pw_hint, branch_id, usr_status) VALUES(:usr_group, :usr_name, :usr_email, :usr_mobile, :usr_logname, :usr_logpwd, :usr_type, :usr_access, :pw_hint, :branch_id, :usr_status)");

        $login = array(
            ':usr_group' => $usr_group,
            ':usr_name' => $_REQUEST['emp_name'],
            ':usr_email' => $_REQUEST['emp_email'],
            ':usr_mobile' => $_REQUEST['emp_mobile1'],
            ':usr_logname' => $_REQUEST['emp_login_name'],
            ':usr_logpwd' => StandardHash($_REQUEST['emp_login_password']),
            ':usr_type' => 'S',
            ':usr_access' => 1,
            ':pw_hint' => $_REQUEST['emp_login_password'],
            ':branch_id' => $_REQUEST['branch_id'],
            ':usr_status' => 1
        );
        $login_detail->execute($login);
    }
    
    if($_REQUEST['staffhid'] == 1)
	{
		header("location:lst_staff.php");	
	}
	else if($_REQUEST['labourhid'] == 2)
	{
		header("location:lst_labour.php");	
	}
	else
	{
		header("location:lst_employee.php");	
	}
    die();
} catch (Exception $e) {
    $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
    $_SESSION['_msg_err'] = $str;
    header("location:lst_employee.php");	

}


}


if ($_REQUEST['emp_id'] != '') {
    $result = $conn->query("SELECT * FROM mst_employee WHERE rec_del_status = '1' AND emp_id = " . $_REQUEST['emp_id']);
    if ($result->rowCount() > 0) {
        $obj = $result->fetch(PDO::FETCH_OBJ);

         $emp_id = $obj->emp_id;
        $prefix = $obj->prefix;
        $emp_name = $obj->emp_name;
        $emp_fat_hus_name = $obj->emp_fat_hus_name;
        $emp_code = $obj->emp_code;
        $bio_id = $obj->bio_id;
        $emp_slno = $obj->emp_slno;
        $emp_dob = $obj->emp_dob;
        $emp_pf = $obj->emp_pf;
        $labour_id = $obj->labour_id;
        $department_id = $obj->department_id;
        $designation_id = $obj->designation_id;
        $emp_blood = $obj->emp_blood;
        $emp_mobile = $obj->emp_mobile;
        $emp_email = $obj->emp_email;
        $emp_phone = $obj->emp_phone;
        $emp_photo = $obj->emp_photo;
        $emp_cr_add1 = $obj->emp_cr_add1;
        $emp_cr_add2 = $obj->emp_cr_add2;
        $cr_state_id = $obj->cr_state_id;
        $cr_district_id = $obj->cr_district_id;
        $cr_city_id = $obj->cr_city_id;
        $cr_pincode = $obj->cr_pincode;
        $emp_pr_add1 = $obj->emp_pr_add1;
        $emp_pr_add2 = $obj->emp_pr_add2;
        $pr_state_id = $obj->pr_state_id;
        $pr_district_id = $obj->pr_district_id;
        $pr_city_id = $obj->pr_city_id;
        $pr_pincode = $obj->pr_pincode;
        $copy_address = $obj->copy_address;
        $bank_acc_no = $obj->bank_acc_no;
        $bank_acc_name = $obj->bank_acc_name;
        $bank_name = $obj->bank_name;
        $bank_branch = $obj->bank_branch;
        $ifsc_code = $obj->ifsc_code;
        $branch_pincode = $obj->branch_pincode;
        $emp_pan_no = $obj->emp_pan_no;
        $emp_aadhar_no = $obj->emp_aadhar_no;
        $emp_add_proof = $obj->emp_add_proof;
        $emp_pan_copy = $obj->emp_pan_copy;
        $emp_aadhar_copy = $obj->emp_aadhar_copy;
        $emp_add_proof_copy = $obj->emp_add_proof_copy;
        $emp_marital_status = $obj->emp_marital_status;
        $anniversary_date = $obj->anniversary_date;
        $spouse_name = $obj->spouse_name;
        $children_no = $obj->children_no;
        $spouse_cont_no = $obj->spouse_cont_no;
        $spouse_dob = $obj->spouse_dob;
        $emp_date_join = $obj->emp_date_join;
        $login_access = $obj->login_access;
        $emp_epf_no = $obj->emp_epf_no;
        $emp_uan_no = $obj->emp_uan_no;
        $emp_agreement_order = $obj->emp_agreement_order;
        $emp_appointment_order = $obj->emp_appointment_order;
        $emp_advance_bal = $obj->emp_advance_bal;
        $emp_nominee = $obj->emp_nominee;
        $emp_nominee_relation = $obj->emp_nominee_relation;

        $ledger_name = $dbconn->GetSingleReconrd("mst_ledger", "ledger_name", "ledger_id", $obj->ledger_id);
        $open_bal_type = $dbconn->GetSingleReconrd("mst_ledger", "open_bal_type", "ledger_id", $obj->ledger_id);
        $open_bal = $dbconn->GetSingleReconrd("mst_ledger", "open_bal", "ledger_id", $obj->ledger_id);
        $group_id = $dbconn->GetSingleReconrd("mst_ledger", "group_id", "ledger_id", $obj->ledger_id);
        $usr_group = $dbconn->GetSingleReconrd("tbl_user", "usr_group", "emp_id", $emp_id );
        
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <style>
        .tab {
            display: none;
        }
    </style>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>
        <?php echo PAGE_TITLE; ?>-Employee
    </title>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <?php include_once("inc/common/css-js.php"); ?>
</head>

<body>
    <?php include("inc/common/header.php") ?>
    <!-- Page content -->
    <div class="page-content">
        <!-- Main sidebar -->
        <?php include("inc/common/sidebar.php") ?>
        <!-- Main content -->
        <div class="content-wrapper">

            <!-- Page header -->
            <div class="page-header">
                <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                    <div class="d-flex">
                        <div class="breadcrumb">
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
                            <a href="#" class="breadcrumb-item">HR Management</a>
                            <span class="breadcrumb-item active">Employee Master</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>

            </div>

            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">
                        <form name="thisForm" class="form-horizontal" method='POST' action="mst_employee_add.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                            <input type="hidden" name="emp_slno" id="emp_slno" value="<?php echo $emp_slno; ?>">
                            <input type="hidden" name="branch_code" id="branch_code" value="<?php echo $usr_group; ?>">
                            <input type="hidden" name="hide_emp_agreement_order" value="<?php echo $obj->emp_agreement_order; ?>">

                            <input type="hidden" name="hide_emp_photo" value="<?php echo $obj->emp_photo; ?>">
                            <input type="hidden" name="hide_emp_aadhar_copy" value="<?php echo $obj->emp_aadhar_copy; ?>">
                            <input type="hidden" name="hide_emp_add_proof_copy" value="<?php echo $obj->emp_add_proof_copy; ?>">
                            <input type="hidden" name="hide_emp_pan_copy" value="<?php echo $obj->emp_pan_copy; ?>">
                            <input type="hidden" name="hide_emp_appointment_order" value="<?php echo $obj->emp_appointment_order; ?>">
                            <input type="hidden" name="branch_hidd" id='branch_hidd' value="<?php echo $_SESSION['_user_branch']; ?>">
                            <fieldset>
                                <div class="card">
                                    <div class="card-header bg-pgheader text-white header-elements-inline">
                                        <h6 class="card-title">New Employee</h6>
                                        <div class="header-elements">
                                            <div class="list-icons">
                                                <!-- <a class="list-icons-item" id="toogle" href="javascript:;" data-toggle="collapse" data-target="#toogleform" title="New Employee"><i class="icon-plus-circle2 mr-2"></i></a>
                                                <a class="list-icons-item" data-action="fullscreen"></a> -->
                                                <a class="list-icons-item" href="lst_employee.php" title="Employee List"><i class="icon-arrow-left52 mr-2"></i></a>
                                                <a class="list-icons-item" data-action="fullscreen"></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body ">
                                        <div class="card">

                                            <div class=" tabbable ">

                                                <div class="" align="center;">
                                                    <ul class="nav nav-tabs-solid ">
                                                        <li class="">
                                                            <a class=" active nav-link" href="#tab1" data-toggle="tab">Personal Details</a>
                                                        </li>
                                                        <li>
                                                            <a class="nav-link" href="#tab2" data-toggle="tab">Communication Details</a>
                                                        </li>
                                                        <li>
                                                            <a class="nav-link" href="#tab3" data-toggle="tab">Bank Details</a>
                                                        </li>
                                                        <li>
                                                            <a class="nav-link" href="#tab4" data-toggle="tab">Documents</a>
                                                        </li>
                                                        <li>
                                                            <a class="nav-link" href="#tab5" data-toggle="tab">Family Details</a>
                                                        </li>
                                                        <li>
                                                            <a class="nav-link" href="#tab6" data-toggle="tab">Official Details</a>
                                                        </li>
                                                        <li>
                                                            <a class="nav-link" href="#tab7" data-toggle="tab">Asset Issue</a>
                                                        </li>
                                                        <li>
                                                            <a class="nav-link" href="#tab8" data-toggle="tab">Accounts</a>
                                                        </li>
                                                    </ul>
                                                </div>

                                            </div>
                                            <div class="tab-content p-2">
                                                <div class="tab-pane active" id="tab1">
                                                    <div class="form-group pt-2">
                                                        <div class="row">
                                                            <label class="col-lg-2  col-form-label">Branch <span class="text-mandatory"> *</span> </label>
                                                            <div class="col-lg-4 ">
                                                                <select name="branch_id"  id="branch_id" data-placeholder="Choose a Branch.." class="form-control select-search" data-fouc>
                                                                    <option value="">-- Select Branch --</option>
                                                                    <?php
                                                                    // if ($_SESSION['_user_id'] == 1) {
                                                                    //     echo $dbconn->fnFillComboFromTable_Where("branch_id", "branch_name", "mst_branch", "branch_id", " WHERE branch_status = 1");
                                                                    // } else {
                                                                        echo $dbconn->fnFillComboFromTable_Where("branch_id", "branch_name", "mst_branch", "branch_id", " WHERE (branch_status = 1 AND branch_id = " . $_SESSION['_user_branch'] . ")");
                                                                    // }
                                                                    // ?>
                                                                </select>
                                                                <script>
                                                                    document.thisForm.branch_id.value = "<?php echo $obj->branch_id; ?>";
                                                                </script>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row pt-2">
                                                        <label class="col-lg-2 col-form-label">Employee Name <span class="text-mandatory"> *</span></label>
                                                        <div class="col-lg-4">
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <select name="prefix" id="prefix" class="select">
                                                                        <option value="">Prefix</option>
                                                                        <option value="Mr.">Mr.</option>
                                                                        <option value="Mrs.">Mrs.</option>
                                                                        <option value="Ms.">Ms.</option>
                                                                        <option value="Dr.">Dr.</option>
                                                                    </select>
                                                                    <script>
                                                                        document.thisForm.prefix.value = "<?php echo $prefix; ?>";
                                                                    </script>
                                                                </span>
                                                                <input type="text" name="emp_name" id="emp_name" class="form-control" autocomplete="off" onkeypress="return event.charCode == 46 || event.charCode > 64 && event.charCode < 91 || event.charCode > 96 && event.charCode < 123 " maxlength="75"  value="<?php echo $emp_name; ?>" />
                                                            </div>
                                                        </div>
                                                        <label class="col-lg-2 col-form-label">Father / Husband Name </label>
                                                        <div class="col-lg-4">
                                                            <input type="text" name="emp_fat_hus_name" id="emp_fat_hus_name" class="form-control" maxlength="75" value="<?php echo $emp_fat_hus_name; ?>" placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="form-group row pt-2">
                                                        <label class="col-lg-2 col-form-label">Emp.Code & Bio Id </label>
                                                        <div class="col-lg-4">
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <input class=" col-lg-12 input-group-text" name="emp_code" id="emp_code" readonly maxlength="" value="<?php echo $emp_code; ?>"></input>
                                                                </span>
                                                                <input type="" name="bio_id" id="bio_id" class="form-control" maxlength="10" value="<?php echo $bio_id ?> " />
                                                            </div>
                                                        </div>
                                                        <label class="col-lg-2 col-form-label">Date of Birth</label>
                                                        <div class="col-lg-4">
                                                            <div class="input-group">
                                                                <input type="date" name="emp_dob" id="emp_dob" class="form-control "  maxlength="" value="<?php echo $emp_dob; ?>" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row pt-2">
                                                        <label class="col-lg-2 col-form-label">Employee Type <span class="text-mandatory"> *</span></label>
                                                        <div class="col-lg-4">
                                                            <div class="input-group">
                                                                <select name="emp_type" id="emp_type" data-placeholder="Choose a Employee Type.." class="select">
                                                                    <option value="">Choose a Employee Type</option>
                                                                    <option value="1">Staff</option>
                                                                    <option value="2">Labour</option>
                                                                    <option value="3">Others</option>
                                                                </select>
                                                                <script>
                                                                    document.thisForm.emp_type.value = "<?php echo $obj->emp_type; ?>";
                                                                </script>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6" id="labour">
                                                            <div class="row">
                                                                <label class="col-lg-4 col-form-label ">Labour Status <span class="text-mandatory"> *</span></label>
                                                                <div class="col-lg-8">
                                                                    <div class="input-group">
                                                                        <select name="labour_status" id="labour_status" data-placeholder="Choose a Labour Status.." class="select-search">
                                                                            <option value="">Choose a Labour Status..</option>
                                                                            <option value="1">Temporary</option>
                                                                            <option value="2">Permanent</option>
                                                                        </select>
                                                                        <script>
                                                                            document.thisForm.labour_status.value = "<?php echo $obj->labour_status; ?>";
                                                                        </script>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6" id="staff">
                                                            <div class="row">
                                                                <label class="col-lg-4 col-form-label">Staff Status <span class="text-mandatory">*</span></label>
                                                                <div class="col-lg-8">
                                                                    <div class="input-group">
                                                                        <select name="staff_status" id="staff_status" data-placeholder="Choose a Staff Status.." class="select-search">
                                                                            <option value="">Choose a Staff Status..</option>
                                                                            <option value="1">Temporary</option>
                                                                            <option value="2">Permanent</option>
                                                                        </select>
                                                                        <script>
                                                                            document.thisForm.staff_status.value = "<?php echo $obj->staff_status; ?>";
                                                                        </script>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="group" class="form-group row pt-2">
                                                        <label class="col-lg-2 col-form-label">Provident Fund <span class="text-mandatory" id="valdate"> *</span></label>
                                                        <div class="col-lg-4">
                                                            <div class="input-group">
                                                                <select name="emp_pf" id="emp_pf" data-placeholder="Choose a PF Status.." class="select-search">
                                                                    <option value="">--Select PF Status--</option>
                                                                    <option value="1">No</option>
                                                                    <option value="2">Yes</option>
                                                                </select>
                                                                <script>
                                                                    document.thisForm.emp_pf.value = "<?php echo $obj->emp_pf; ?>";
                                                                </script>
                                                            </div>
                                                        </div>
                                                        <label class="col-lg-2 col-form-label">Labour Group <span class="text-mandatory" id="valdate1"> *</span></label>
                                                        <div class="col-lg-4">
                                                            <div class="input-group">
                                                                <select name="labour_id" id="labour_id" data-placeholder="Choose a Labour Group.." class="select-search">
                                                                    <option value="">--Select Labour--</option>
                                                                    <?php
                                                                    echo $dbconn->fnFillComboFromTable_Where("labour_id", "labour_name", "mst_labour", "labour_id", " WHERE rec_del_status = 1") ?>
                                                                </select>
                                                                <script>
                                                                    document.thisForm.labour_id.value = "<?php echo $obj->labour_id; ?>";
                                                                </script>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row pt-2">
                                                        <label class="col-lg-2 col-form-label">Department <span class="text-mandatory"> *</span></label>
                                                        <div class="col-lg-4">
                                                            <div class="input-group">
                                                                <select name="department_id" id="department_id" data-placeholder="Choose a Department.." class="select-search">
                                                                    <option value=""></option>
                                                                    <?php
                                                                    echo $dbconn->fnFillComboFromTable_Where("department_id", "department_name", "mst_department", "department_id", " WHERE rec_del_status = 1 ") ?>
                                                                </select>
                                                                <script>
                                                                    document.thisForm.department_id.value = "<?php echo $obj->department_id; ?>";
                                                                </script>
                                                            </div>
                                                        </div>
                                                        <label class="col-lg-2 col-form-label">Designation <span class="text-mandatory"> *</span></label>
                                                        <div class="col-lg-4">
                                                            <div class="input-group">
                                                                <select name="designation_id" id="designation_id" data-placeholder="Choose a Department.." class="select-search">
                                                                    <option value=""></option>
                                                                    <?php
                                                                    if ($obj->department_id != '') {
                                                                        echo $dbconn->fnFillComboFromTable_Where("designation_id", "designation_name", "mst_designation", "designation_id", " WHERE rec_del_status = 1 and department_id=" . $obj->department_id);
                                                                    } ?>
                                                                </select>
                                                                <script>
                                                                    document.thisForm.designation_id.value = "<?php echo $obj->designation_id; ?>";
                                                                </script>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row pt-2">
                                                        <label class="col-lg-2 col-form-label">Mobile No. 1 <span class="text-mandatory"> *</span></label>
                                                        <div class="col-lg-4">
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <span class="input-group-text">+91</span>
                                                                </span>
                                                                <input onkeypress="return event.charCode >= 48 && event.charCode <= 57" type="tel" name="emp_mobile1" id="emp_mobile1" class="form-control" maxlength="10" value="<?php echo $obj->emp_mobile; ?>" />
                                                            </div>
                                                        </div>
                                                        <label class="col-lg-2 col-form-label">Mobile No. 2</label>
                                                        <div class="col-lg-4">
                                                            <div class="input-group">
                                                                <span class="input-group-prepend">
                                                                    <span class="input-group-text">+91</span>
                                                                </span>
                                                                <input onkeypress="return event.charCode >= 48 && event.charCode <= 57" type="tel" name="emp_mobile2" id="emp_mobile2" class="form-control" maxlength="10" value="<?php echo $obj->emp_phone; ?>" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row pt-2">
                                                        <label class="col-lg-2 col-form-label">Email ID</label>
                                                        <div class="col-lg-4">
                                                            <div class="input-group">
                                                                <input type="text" name="emp_email" id="emp_email" class="form-control text-lowercase" maxlength="100" autocomplete="off" value="<?php echo $obj->emp_email; ?>" />
                                                            </div>
                                                        </div>
                                                        <label class="col-lg-2 col-form-label">Blood Group</label>
                                                        <div class="col-lg-4">
                                                            <div class="input-group">
                                                                <select name="emp_blood" id="emp_blood" data-placeholder="Choose a Blood group.." class="select-search">
                                                                    <option value="">Choose a Blood group..</option>
                                                                    <option value="A+">A+</option>
                                                                    <option value="O+">O+</option>
                                                                    <option value="B+">B+</option>
                                                                    <option value="AB+">AB+</option>
                                                                    <option value="A-">A-</option>
                                                                    <option value="O-">O-</option>
                                                                    <option value="B-">B-</option>
                                                                    <option value="AB-">AB-</option>
                                                                </select>
                                                                <script>
                                                                    document.thisForm.emp_blood.value = "<?php echo $obj->emp_blood; ?>";
                                                                </script>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row  pt-2">
                                                        <label class="col-lg-2 col-form-label">Employee Photo </label>
                                                        <div class="col-lg-4">
                                                            <?php if ($obj->emp_photo != "") {
                                                                echo '<a href="javascript:void(0)" onClick="window.open(\'project_img/emp_photo/' . $obj->emp_photo . '\',\'' . $obj->emp_photo . '\',\'width=700,height=505,menubar=no,status=no,scrollbars=no\')" class=\'sub\' title=\'' . $obj->emp_name . '\' >' . $obj->emp_photo . '</a>';
                                                            } ?>
                                                            <input type="file" id="emp_photo" name="emp_photo" accept="image/gif, image/jpeg, image/png" class="form" value="">
                                                            <input type="hidden" name="hide_emp_photo" value="<?php echo $obj->emp_photo; ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane " id="tab2">
                                                    <div class="row ml-0 mr-0 pt-1 pb-1" style="background-color:#f9f6f6;">
                                                        <div class="col-md-6 font-weight-semibold">
                                                            Current Address
                                                        </div>

                                                    </div>
                                                    <div class="form-group row  pt-2">
                                                        <label class="col-lg-2 col-form-label">Address Line 1 <span class="text-mandatory"> *</span></label>
                                                        <div class="col-lg-4">
                                                            <input type="tel" name="emp_cr_add1" id="emp_cr_add1" class="form-control" maxlength="100" value="<?php echo $obj->emp_cr_add1; ?>" />
                                                        </div>
                                                        <label class="col-lg-2 col-form-label">Address Line 2</label>
                                                        <div class="col-lg-4">
                                                            <input type="tel" name="emp_cr_add2" id="emp_cr_add2" class="form-control" maxlength="100" value="<?php echo $obj->emp_cr_add2; ?>" />
                                                        </div>
                                                    </div>
                                                    <div class="form-group row  pt-2">
                                                        <label class="col-lg-2 col-form-label">State <span class="text-mandatory"> *</span></label>
                                                        <div class="col-lg-4">
                                                            <select data-placeholder="Choose a State.." name="cr_state_id" id="cr_state_id" class="select-search">
                                                                <option value="">--Select State--</option>
                                                                <?php
                                                                echo $dbconn->fnFillComboFromTable_Where("state_id", "state_name", "mst_state", "state_id", " WHERE state_status = 1");
                                                                ?>
                                                            </select>
                                                            <script>
                                                                document.thisForm.cr_state_id.value = "<?php echo $cr_state_id; ?>";
                                                            </script>
                                                        </div>
                                                        <label class="col-lg-2 col-form-label">District <span class="text-mandatory"> *</span></label>
                                                        <div class="col-lg-4">
                                                            <select name="cr_district_id" id="district_id" data-placeholder="Choose a district.." class="select-search">
                                                                <option value=""></option>
                                                                <?php
                                                                if ($cr_state_id != "") {
                                                                    echo  $dbconn->fnFillComboFromTable_Where("district_id", "district_name", "mst_district", "district_name", " WHERE district_status = 1 AND state_id = " . $cr_state_id);
                                                                } ?>
                                                            </select>
                                                            <script>
                                                                document.thisForm.cr_district_id.value = "<?php echo $cr_district_id; ?>";
                                                            </script>
                                                        </div>

                                                    </div>
                                                    <div class="form-group row  pt-2">
                                                        <label class="col-lg-2 col-form-label">City <span class="text-mandatory"> *</span></label>
                                                        <div class="col-lg-4">
                                                            <select name="cr_city_id" id="city_id" data-placeholder="Choose a city.." class="select-search">
                                                                <option value=""></option>
                                                                <?php if ($cr_district_id != "") {
                                                                    echo  $dbconn->fnFillComboFromTable_Where("city_id", "city_name", "mst_city", "city_name", " WHERE city_status = 1 AND district_id = " . $cr_district_id);
                                                                } ?>
                                                            </select>
                                                            <script>
                                                                document.thisForm.cr_city_id.value = "<?php echo $cr_city_id; ?>"
                                                            </script>
                                                        </div>
                                                        <label class="col-lg-2 col-form-label">Pincode <span class="text-mandatory"> *</span></label>
                                                        <div class="col-lg-4">
                                                            <input onkeypress="return event.charCode >= 48 && event.charCode <= 57" type="text" name="cr_pincode" id="cr_pincode" class="form-control" maxlength="6" value="<?php echo $obj->cr_pincode; ?>" />
                                                        </div>
                                                    </div>
                                                    <div class="row ml-0 mr-0 pt-1 pb-1" style="background-color:#f9f6f6;">
                                                        <div class="col-md-2 font-weight-semibold">
                                                            Permanent Address </div>
                                                        <div class="col-md-6 font-weight-semibold"> <input class="" type="checkbox" name="copy_address" id="copy_address" value="1" /> Permanent Address is same as Current Address..</div>
                                                    </div>
                                                    <div class="form-group row  pt-2" id="pr_emp_add">
                                                        <label class="col-lg-2 col-form-label">Address Line 1 <span class="text-mandatory"> *</span></label>
                                                        <div class="col-lg-4">
                                                            <input type="tel" name="emp_pr_add1" id="emp_pr_add1" class="form-control" maxlength="100" value="<?php echo $obj->emp_pr_add1; ?>" />
                                                        </div>
                                                        <label class="col-lg-2 col-form-label">Address Line 2</label>
                                                        <div class="col-lg-4">
                                                            <input type="tel" name="emp_pr_add2" id="emp_pr_add2" class="form-control" maxlength="100" value="<?php echo $obj->emp_pr_add2; ?>" />
                                                        </div>
                                                    </div>
                                                    <div class="form-group row  pt-2" id="pr_emp_sd">
                                                        <label class="col-lg-2 col-form-label">State <span class="text-mandatory"> *</span></label>
                                                        <div class="col-lg-4">
                                                            <select data-placeholder="Choose a State.." name="pr_state_id" id="pr_state_id" class="select-search">
                                                                <?php
                                                                echo $dbconn->fnFillComboFromTable_Where("state_id", "state_name", "mst_state", "state_id", " WHERE state_status = 1");
                                                                ?>
                                                            </select>
                                                            <script>
                                                                document.thisForm.pr_state_id.value = "<?php echo $pr_state_id; ?>";
                                                            </script>
                                                        </div>
                                                        <label class="col-lg-2 col-form-label">District <span class="text-mandatory"> *</span></label>
                                                        <div class="col-lg-4">
                                                            <select name="pr_district_id" id="pr_district_id" data-placeholder="Choose a district.." class="select-search">
                                                                <option value=""></option>
                                                                <?php if ($pr_state_id != "") {
                                                                    echo  $dbconn->fnFillComboFromTable_Where("district_id", "district_name", "mst_district", "district_name", " WHERE district_status = 1 AND state_id = " . $pr_state_id);
                                                                } ?>
                                                            </select>
                                                            <script>
                                                                document.thisForm.pr_district_id.value = "<?php echo $pr_district_id; ?>";
                                                            </script>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row  pt-2" id="pr_emp_cp">
                                                        <label class="col-lg-2 col-form-label">City <span class="text-mandatory"> *</span></label>
                                                        <div class="col-lg-4">
                                                            <select name="pr_city_id" id="pr_city_id" data-placeholder="Choose a city.." class="select-search">
                                                                <option value=""></option>
                                                                <?php if ($pr_district_id != "") {
                                                                    echo  $dbconn->fnFillComboFromTable_Where("city_id", "city_name", "mst_city", "city_name", " WHERE city_status = 1 AND district_id = " . $pr_district_id);
                                                                } ?>
                                                            </select>
                                                            <script>
                                                                document.thisForm.pr_city_id.value = "<?php echo $pr_city_id; ?>";
                                                            </script>
                                                        </div>
                                                        <label class="col-lg-2 col-form-label">Pincode <span class="text-mandatory"> *</span></label>
                                                        <div class="col-lg-4">
                                                            <input onkeypress="return event.charCode >= 48 && event.charCode <= 57" type="tel" name="pr_pincode" id="pr_pincode" class="form-control" maxlength="6" value="<?php echo $obj->pr_pincode; ?>"" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class=" tab-pane " id="tab3">
                                                            <div class="form-group row  pt-2">
                                                                <label class="col-lg-2 col-form-label">Bank Account No. </label>
                                                                <div class="col-lg-4">
                                                                    <input type="text" onkeypress="return event.charCode >= 48 && event.charCode <= 57" name="bank_acc_no" id="bank_acc_no" class="form-control" maxlength="20" value="<?php echo $obj->bank_acc_no; ?>" />
                                                                </div>
                                                                <label class="col-lg-2 col-form-label">Account Name</label>
                                                                <div class="col-lg-4">
                                                                    <input type="text" name="bank_acc_name" id="bank_acc_name" class="form-control" maxlength="100" value="<?php echo $obj->bank_acc_name; ?>" />
                                                                </div>
                                                            </div>
                                                            <div class="form-group row  pt-2">
                                                                <label class="col-lg-2 col-form-label">Bank Name </label>
                                                                <div class="col-lg-4">
                                                                    <input type="text" name="bank_name" id="bank_name" class="form-control" maxlength="100" value="<?php echo $obj->bank_name; ?>" />
                                                                </div>
                                                                <label class="col-lg-2 col-form-label">Bank Branch</label>
                                                                <div class="col-lg-4">
                                                                    <input type="text" name="bank_branch" id="bank_branch" class="form-control" maxlength="100" value="<?php echo $obj->bank_branch; ?>" />
                                                                </div>
                                                            </div>
                                                            <div class="form-group row  pt-2">
                                                                <label class="col-lg-2 col-form-label">IFSC Code </label>
                                                                <div class="col-lg-4">
                                                                    <input type="text" name="ifsc_code" id="ifsc_code" class="form-control" maxlength="15" value="<?php echo $obj->ifsc_code; ?>" />
                                                                </div>
                                                                <label class="col-lg-2 col-form-label">Branch Pincode</label>
                                                                <div class="col-lg-4">
                                                                    <input onkeypress="return event.charCode >= 48 && event.charCode <= 57" type="text" name="branch_pincode" id="branch_pincode" class="form-control" maxlength="6" value="<?php echo $obj->branch_pincode; ?>" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane " id="tab4">
                                                            <div class="form-group row  pt-2">
                                                                <label class="col-lg-2 col-form-label">PAN Number </label>
                                                                <div class="col-lg-4">
                                                                    <input type="text" name="emp_pan_no" id="emp_pan_no" class="form-control" maxlength="10" value="<?php echo $obj->emp_pan_no; ?>" />
                                                                </div>
                                                                <label class="col-lg-2 col-form-label">PAN Copy </label>
                                                                <div class="col-lg-4">
                                                                    <?php if ($obj->emp_pan_copy != "") {
                                                                        echo '<a href="javascript:void(0)" onClick="window.open(\'project_img/emp_proofs/' . $obj->emp_pan_copy . '\',\'' . $obj->emp_pan_copy . '\',\'width=700,height=505,menubar=no,status=no,scrollbars=no\')" class=\'sub\' title=\'' . $obj->emp_name . '\' >' . $obj->emp_pan_copy . '</a>';
                                                                    } ?>
                                                                    <input type="file" name="emp_pan_copy" id="emp_pan_copy" class="form" maxlength="100" accept="application/pdf,.doc,.docx" value="" />
                                                                </div>
                                                            </div>
                                                            <div class="form-group row  pt-2">
                                                                <label class="col-lg-2 col-form-label">Aadhar No. </label>
                                                                <div class="col-lg-4">

                                                                    <input type="text" name="emp_aadhar_no" id="emp_aadhar_no" onkeypress="return event.charCode >= 48 && event.charCode <= 57" class="form-control" maxlength="12" value="<?php echo $obj->emp_aadhar_no; ?>" />
                                                                </div>
                                                                <label class="col-lg-2 col-form-label">Aadhar Copy </label>
                                                                <div class="col-lg-4">
                                                                    <?php if ($obj->emp_aadhar_copy != "") {
                                                                        echo '<a href="javascript:void(0)" onClick="window.open(\'project_img/emp_proofs/' . $obj->emp_aadhar_copy . '\',\'' . $obj->emp_aadhar_copy . '\',\'width=700,height=505,menubar=no,status=no,scrollbars=no\')" class=\'sub\' title=\'' . $obj->emp_name . '\' >' . $obj->emp_aadhar_copy . '</a>';
                                                                    } ?>
                                                                    <input type="file" accept="application/pdf,.doc,.docx" name="emp_aadhar_copy" id="emp_aadhar_copy" class="form" maxlength="100" value="" />
                                                                </div>
                                                            </div>
                                                            <div class="form-group row  pt-2">
                                                                <label class="col-lg-2 col-form-label">Address Proof</label>
                                                                <div class="col-lg-4">

                                                                    <input type="text" name="emp_add_proof" id="emp_add_proof" class="form-control" maxlength="100" value="<?php echo $obj->emp_add_proof; ?>" />
                                                                </div>
                                                                <label class="col-lg-2 col-form-label">Address Proof Copy</label>
                                                                <div class="col-lg-4">
                                                                    <?php if ($obj->emp_add_proof_copy != "") {
                                                                        echo '<a href="javascript:void(0)" onClick="window.open(\'project_img/emp_proofs/' . $obj->emp_add_proof_copy . '\',\'' . $obj->emp_add_proof_copy . '\',\'width=700,height=505,menubar=no,status=no,scrollbars=no\')" class=\'sub\' title=\'' . $obj->emp_name . '\' >' . $obj->emp_add_proof_copy . '</a>';
                                                                    } ?>
                                                                    <input type="file" accept="application/pdf,.doc,.docx" name="emp_add_proof_copy" id="emp_add_proof_copy" class="form" maxlength="100" value="" />
                                                                </div>
                                                            </div>
                                                            <div class="row ml-0 mr-0 pt-1 pb-1" id="table_cert1" style="background-color:#f9f6f6;">
                                                                <div class="col-md-6 font-weight-semibold">
                                                                    Staff Certificate
                                                                </div>
                                                            </div>
                                                            <input type="hidden" name="cert_count" id="cert_count" value="<?php if ($_REQUEST['emp_id'] != '') { echo $dbconn->GetSingleReconrd("mst_employee_certificate", "COUNT(emp_id)", "emp_id", $_REQUEST['emp_id']); } else { ?> 0 <?php } ?>">

                                                            <div class="form-group row  pt-2" id="table_cert">
                                                                <div class="col-lg-12 pt-2">
                                                                    <div class="row">
                                                                        <label class="col-lg-4 col-form-label">Certificate Name <span class="text-mandatory"> *</span></label>
                                                                        <label class="col-lg-3 col-form-label" style="text-align:left ;">Certificate Copy <span class="text-mandatory"> *</span></label>
                                                                        <button class=" btn btn-success " id="addRow" type="button">+</button>
                                                                    </div>
                                                                </div>
                                                                <?php

                                                                if ($_REQUEST['emp_id'] != '') {
                                                                    $emp_certificate = "SELECT * FROM mst_employee_certificate WHERE emp_id =" . $_REQUEST['emp_id'];

                                                                    $emp_certificate_conn = $conn->query($emp_certificate);


                                                                    while ($row = $emp_certificate_conn->fetch(PDO::FETCH_OBJ)) {

                                                                        echo '<input type="hidden" name="hide_emp_certcopy[]" id = "hide_emp_certcopy' . $row->auto_id . '"  value=" ' . $row->emp_cert_copy . '">';
                                                                        echo '<input type="hidden" name="id_no[]" id = "id_no' . $row->auto_id . '"  value="' . $row->auto_id . '">';
                                                                        echo '<div class="col-lg-12 pt-2" id="more' . $row->auto_id . '">
                                                                        <div class="row">
                                                                            <div class="col-lg-4"><input type="text" accept="application/pdf,.doc,.docx"  name="emp_certname[]"  class="form-control emp_certname" maxlength="30" value="' . $row->emp_cert_name . '"></div>';
                                                                        echo ' <div class="col-lg-3">';
                                                                        echo '<a href="javascript:void(0)" onClick="window.open(\'project_img/emp_certificates/' . $row->emp_cert_copy . '\',\'' . $row->emp_cert_copy . '\',\'width=700,height=505,menubar=no,status=no,scrollbars=no\')" class=\'sub\' title=\'' . $row->emp_cert_name . '\' >' . $row->emp_cert_copy . '</a>';
                                                                        echo '<input type="file" name="emp_certcopy[]"  class="emp_certificate" src="" value="'.$row->emp_cert_copy.'">
                                                                        </div>
                                                                        <a href="javascript:;" onClick="removeElement1(' . $row->auto_id . ');"><i class="icon-bin bg-delete mr-2"></i></a>
                                                                    </div>
                                                                </div>';
                                                                    }
                                                                }
                                                            ?>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane " id="tab5">
                                                            <div class="form-group row  pt-2">
                                                                <label class="col-lg-2 col-form-label">Marital Status</label>
                                                                <div class="col-lg-4">
                                                                    <select name="emp_marital_status" id="emp_marital_status" class="select-search">
                                                                        <option value="1">Married</option>
                                                                        <option value="0">Un-Married</option>
                                                                    </select>
                                                                    <script>
                                                                        document.thisForm.emp_marital_status.value = "<?php echo $emp_marital_status; ?>";
                                                                    </script>
                                                                </div>
                                                                <div class="col-lg-6" name="" id="anvis_hide">
                                                                    <div class="row">
                                                                        <label class="col-lg-4 col-form-label">Anniversary Date</label>
                                                                        <div class="col-lg-8">
                                                                            <input type="date" name="anniversary_date" id="anniversary_date" class="form-control" value="<?php echo $anniversary_date; ?>" />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row  pt-2" id="sup_ch_hide">
                                                                <label class="col-lg-2 col-form-label">Spouse Name </label>
                                                                <div class="col-lg-4">
                                                                    <input type="text" name="spouse_name" id="spouse_name" class="form-control" maxlength="" value="<?php echo $obj->spouse_name; ?>" />
                                                                </div>
                                                                <label class="col-lg-2 col-form-label">No of children's</label>
                                                                <div class="col-lg-4">
                                                                    <input type="text" onkeypress="return event.charCode >= 48 && event.charCode <= 57" name="children_no" id="children_no" class="form-control" maxlength="1" value="<?php echo $obj->children_no; ?>" />
                                                                </div>
                                                            </div>
                                                            <div class="form-group row  pt-2" id="sup_dob_hide">
                                                                <label class="col-lg-2 col-form-label">Spouse Contact No.</label>
                                                                <div class="col-lg-4">
                                                                    <input type="text" onkeypress="return event.charCode >= 48 && event.charCode <= 57" name="spouse_cont_no" id="spouse_cont_no" class="form-control" maxlength="10" value="<?php echo $obj->spouse_cont_no; ?>" />
                                                                </div>
                                                                <label class="col-lg-2 col-form-label">Spouse DOB</label>
                                                                <div class="col-lg-4">
                                                                    <input type="date" name="spouse_dob" id="spouse_dob" class="form-control " maxlength="" value="<?php echo $spouse_dob; ?>" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane " id="tab6">
                                                            <div class="form-group row  pt-2">
                                                                <label class="col-lg-2 col-form-label">Date of Joining <span class="text-mandatory"> *</span></label>
                                                                <div class="col-lg-4">
                                                                    <input type="date" name="emp_date_join" id="emp_date_join" class="form-control " maxlength="" value="<?php echo $obj->emp_date_join; ?>" />
                                                                </div>
                                                                <label class="col-lg-2 col-form-label">Login Access</label>
                                                                <div class="col-lg-4">
                                                                    <select name="login_access" id="login_access" class="select-search">
                                                                        <option value="1">ENABLE - Default</option>
                                                                        <option value="0">DISABLE</option>
                                                                    </select>
                                                                    <script>
                                                                        document.thisForm.login_access.value = "<?php echo $login_access; ?>";
                                                                    </script>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row  pt-2" id="emp_usr_pass">
                                                                <label class="col-lg-2 col-form-label">User Name<span class="text-mandatory"> *</span></label>
                                                                <div class="col-lg-4">
                                                                    <input type="text" name="emp_login_name" id="emp_login_name" class="form-control" maxlength="" value="<?php echo $obj->emp_login_name; ?>" />
                                                                </div>
                                                                <label class="col-lg-2 col-form-label">Password<span class="text-mandatory"> *</span></label>
                                                                <div class="col-lg-4">
                                                                    <input type="password" name="emp_login_password" id="emp_login_password" class="form-control" maxlength="25" value="<?php echo $obj->emp_login_password; ?>" />
                                                                </div>
                                                            </div>
                                                            <div class="form-group row  pt-2 " id="epf_no">
                                                                <label class="col-lg-2 col-form-label">EPF No.</label>
                                                                <div class="col-lg-4">
                                                                    <input type="text" onkeypress="return event.charCode >= 48 && event.charCode <= 57" name="emp_epf_no" id="emp_epf_no" class="form-control" maxlength="100" value="<?php echo $obj->emp_epf_no; ?>" />
                                                                </div>
                                                                <label class="col-lg-2 col-form-label">UAN No.</label>
                                                                <div class="col-lg-4">
                                                                    <input type="text" onkeypress="return event.charCode >= 48 && event.charCode <= 57" name="emp_uan_no" id="emp_uan_no" class="form-control" maxlength="20" value="<?php echo $obj->emp_uan_no; ?>" />
                                                                </div>
                                                            </div>
                                                            <div class="form-group row  pt-2">
                                                                <label class="col-lg-2 col-form-label">Agreement Order </label>
                                                                <div class="col-lg-4">
                                                                    <?php if ($obj->emp_agreement_order != "") {
                                                                        echo '<a href="javascript:void(0)" onClick="window.open(\'project_img/emp_agreement_order/' . $obj->emp_agreement_order . '\',\'' . $obj->emp_agreement_order . '\',\'width=700,height=505,menubar=no,status=no,scrollbars=no\')" class=\'sub\' title=\'' . $obj->emp_name . '\' >' . $obj->emp_agreement_order . '</a>';
                                                                    } ?>
                                                                    <input type="file" accept="application/pdf,.doc,.docx" name="emp_agreement_order" id="emp_agreement_order" class="form" value="" />
                                                                </div>
                                                                <div class="col-lg-6" id="appo_hide">
                                                                    <div class="row">
                                                                        <label class="col-lg-4 col-form-label">Appointment Order</label>
                                                                        <div class="col-lg-8">
                                                                            <?php if ($obj->emp_appointment_order != "") {
                                                                                echo '<a href="javascript:void(0)" onClick="window.open(\'project_img/emp_appointment_order/' . $obj->emp_appointment_order . '\',\'' . $obj->emp_appointment_order . '\',\'width=700,height=505,menubar=no,status=no,scrollbars=no\')" class=\'sub\' title=\'' . $obj->emp_name . '\' >' . $obj->emp_appointment_order . '</a>';
                                                                            } ?>
                                                                            <input type="hidden" name="hide_emp_appointment_order" value="<?php echo $obj->emp_appointment_order; ?>">
                                                                            <input type="file" accept="application/pdf,.doc,.docx" name="emp_appointment_order" id="emp_appointment_order" class="form" value="" />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row  pt-2">
                                                                <label class="col-lg-2 col-form-label">Nominee </label>
                                                                <div class="col-lg-4">
                                                                    <input type="text" name="emp_nominee" id="emp_nominee" class="form-control" maxlength="100" value="<?php echo $obj->emp_nominee; ?>" />
                                                                </div>
                                                                <label class="col-lg-2 col-form-label">Relation</label>
                                                                <div class="col-lg-4">
                                                                    <input type="text" name="emp_nominee_relation" id="emp_nominee_relation" class="form-control" maxlength="20" value="<?php echo $obj->emp_nominee_relation; ?>" />
                                                                </div>
                                                            </div>
                                                            <div class="form-group row  pt-2">
                                                                <label class="col-lg-2 col-form-label">Advance Balance </label>
                                                                <div class="col-lg-4">
                                                                    <input type="text" name="emp_advance_bal" id="emp_advance_bal" class="form-control" maxlength="100" value="<?php echo $obj->emp_advance_bal; ?>" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane " id="tab7">
                                                            <div class="form-group row  pt-2">
                                                                <label class="col-lg-2 col-form-label">Asset Type <span class="text-mandatory"> *</span></label>
                                                                <div class="col-lg-4">
                                                                    <select name="asset_id" id="asset_id" data-placeholder="Choose a Asset Type.." class="select-search">
                                                                        <option value="">--Select Asset Type--</option>
                                                                        <?php
                                                                        echo $dbconn->fnFillComboFromTable_Where("asset_id", "asset_name", "mst_company_asset", "asset_id", " WHERE asset_status = 1") ?>
                                                                    </select>
                                                                    <script>
                                                                        document.thisForm.asset_id.value = "<?php echo $obj->asset_id; ?>";
                                                                    </script>
                                                                </div>
                                                                <label class="col-lg-2 col-form-label">Issue Date<span class="text-mandatory"> *</span></label>
                                                                <div class="col-lg-4">
                                                                    <input type="date" name="issue_date" id="issue_date" class="form-control " maxlength="" value="<?php echo $issue_date; ?>" />
                                                                </div>
                                                            </div>
                                                            <div class="form-group row  pt-2">
                                                                <label class="col-lg-2 col-form-label">Quantity<span class="text-mandatory"> *</span></label>
                                                                <div class="col-lg-4">
                                                                    <input type="text" onkeypress="return event.charCode >= 48 && event.charCode <= 57" name="asset_qty" id="asset_qty" class="form-control" maxlength="10" value="<?php echo $obj->asset_qty; ?>" />
                                                                </div>
                                                                <label class="col-lg-2 col-form-label">Value <span class="text-mandatory"> *</span></label>
                                                                <div class="col-lg-4">
                                                                    <input type="text" onkeypress="return event.charCode >= 48 && event.charCode <= 57" name="asset_value" id="asset_value" class="form-control" maxlength="10" value="<?php echo $obj->asset_qty; ?>" />
                                                                </div>
                                                            </div>
                                                            <div class="form-group row  pt-2" id="sim_mobile">
                                                                <label class="col-lg-2 col-form-label">Sim Number <span class="text-mandatory"> *</span></label>
                                                                <div class="col-lg-4">
                                                                    <input type="text" name="sim_no" id="sim_no" class="form-control" maxlength="22" value="" />
                                                                </div>
                                                                <label class="col-lg-2 col-form-label">Mobile Number <span class="text-mandatory"> *</span></label>
                                                                <div class="col-lg-4">
                                                                    <input type="text" onkeypress="return event.charCode >= 48 && event.charCode <= 57" name="mobile_no" id="mobile_no" class="form-control" maxlength="10" value="" />
                                                                </div>
                                                            </div>
                                                            <div class="form-group row  pt-2" id="usage_limt">
                                                                <label class="col-lg-2 col-form-label">Usage Limit <span class="text-mandatory"> *</span></label>
                                                                <div class="col-lg-4">
                                                                    <input type="text" onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="4" name="sim_limit" id="sim_limit" class="form-control" value="" />
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-12 text-center" algin="center" id="">
                                                                <button class="btn btn-success" id="add_items" name="add_items" type="button"> +
                                                                </button>
                                                            </div>
                                                            <div class="form-group row pt-2 ">
                                                                <div id="show_table" class="col-lg-12">
                                                                    <table class="table table-xs table-hover table-bordered" style="font-size: small !important;">
                                                                        <thead>
                                                                            <tr class=" bg-table-header">
                                                                                <th width="20%">Asset Type</th>
                                                                                <th width="20%">Issue date</th>
                                                                                <th width="20%">Quantity</th>
                                                                                <th width="20%">Value</th>
                                                                                <th width="20%" class="text-center">Action</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php
                                                                            if ($_REQUEST['emp_id'] != '') {
                                                                                $ass_dtes = "SELECT * FROM  tbl_asset_details WHERE emp_id=" . $_REQUEST['emp_id'];
                                                                                $ass_dtes_conn = $conn->query($ass_dtes);

                                                                                while ($row = $ass_dtes_conn->fetch(PDO::FETCH_OBJ)) {

                                                                                    $asset_name = $dbconn->GetSingleReconrd("mst_company_asset", "asset_name", "asset_id", $row->asset_id);
                                                                                    echo '<tr id=' . $row->id . '>
                                                                                        <td>' . $asset_name . '<input type="hidden" class="hidd_asset_id" name="hidd_asset_id[]" value="' . $row->asset_id . '" /> </td>
                                                                                        <td>' . $row->issue_date . '<input type="hidden" class="hidd_asset_date" name="hidd_asset_date[]" value="' . $row->issue_date . '" /></td>
                                                                                        <td>' . $row->asset_qty . '<input type="hidden" class="hidd_asset_qty" name="hidd_asset_qty[]" value="' . $row->asset_qty . '" /> </td>
                                                                                        <td class="text-right">' .  number_format($row->asset_value,2) . '<input type="hidden" class="hidd_asset_val" name="hidd_asset_val[]" value="' . $row->asset_value . '" /></td>
                                                                                        <td class="text-center"><input type="hidden" class="hidd_sim_no" name="hidd_sim_no[]" value="' . $row->sim_no . '" /><input type="hidden" class="hidd_mobile_no" name="hidd_mobile_no[]" value="' . $row->mobile_no . '" /><input type="hidden" class="hidd_sim_limit " name="hidd_sim_limit[]" value="' . $row->sim_limit . '" /><a href="javascript:remove_item(' . $row->id . ');" class="" rel="' . $row->id . '"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a></td>
                                                                                        </tr>';
                                                                                }
                                                                            }
                                                                            ?>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane " id="tab8">
                                                            <div class="form-group row  pt-2">
                                                                <label class="col-lg-2 col-form-label">Ledger Name </label>
                                                                <div class="col-lg-4">
                                                                    <input type="text" name="ledger_name" id="ledger_name" placeholder="Ledger Name" class="form-control" maxlength="100" value="<?php echo $ledger_name; ?>" />
                                                                </div>
                                                                <label class="col-lg-2 col-form-label">Under Group</label>
                                                                <div class="col-lg-4">
                                                                    <select name="group_id" id="group_id" data-placeholder="Choose a  Group.." class="select-search">
                                                                        <option value="">-- Select Group --</option>
                                                                        <?php
                                                                        echo $dbconn->fnFillComboFromTable_Where("group_id", "group_name", "mst_accounts_group", "group_id", " WHERE group_status = 1 "); ?>
                                                                    </select>
                                                                    <script>
                                                                        document.thisForm.group_id.value = "<?php echo $group_id; ?>";
                                                                    </script>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row  pt-2">
                                                                <label class="col-lg-2 col-form-label">Credit Days </label>
                                                                <div class="col-lg-4">
                                                                    <input type="text" onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="4" name="emp_credit_days" id="emp_credit_days" class="form-control" maxlength="3" value="<?php echo $obj->emp_credit_days; ?>" />
                                                                </div>
                                                                <label class="col-lg-2 col-form-label">Pay Mode</label>
                                                                <div class="col-lg-4">
                                                                    <select name="emp_pay_mode" id="emp_pay_mode" data-placeholder="Choose a  Pay Mode.." class="select">
                                                                        <option value="">Select Pay Mode</option>
                                                                        <option value="Cash">Cash</option>
                                                                        <option value="Cheque">Cheque</option>
                                                                        <option value="NEFT">NEFT/RTGS</option>
                                                                        <option value="IMPS">IMPS</option>
                                                                    </select>
                                                                    <script>
                                                                        document.thisForm.emp_pay_mode.value = "<?php echo $obj->emp_pay_mode; ?>";
                                                                    </script>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row  pt-2">
                                                                <label class="col-lg-2 col-form-label">Opening Balance </label>
                                                                <div class="col-lg-4">
                                                                    <div class="input-group">
                                                                        <span class="input-group-prepend">
                                                                            <select name="open_bal_type" id="open_bal_type" class="select">
                                                                                <option value="">Type</option>
                                                                                <option value="DR">DR</option>
                                                                                <option value="CR">CR</option>
                                                                            </select>
                                                                            <script>
                                                                                document.thisForm.open_bal_type.value = "<?php echo $open_bal_type; ?>";
                                                                            </script>
                                                                        </span>
                                                                        <input onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="5" type="text" name="open_bal" id="open_bal" class="form-control" value="<?php echo $open_bal; ?>" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer text-center pt-2">
                                                <div class="form-group ">
                                                    <div class="">
                                                        <?php if ($_REQUEST["emp_id"] != '') { ?>
                                                            <INPUT class="btn btn-info" onclick="return fnValidate();"  type="submit" name="UPDATE" id="UPDATE" value="UPDATE">
                                                            <INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);">
                                                            <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['emp_id']; ?>">
                                                            <input type="hidden" name="labourhid" id="labourhid" value="<?php echo $obj->emp_type;?>">
                                                            <input type="hidden" name="staffhid" id="staffhid" value="<?php echo $obj->emp_type;?>">
                                                        <?php } else { ?>
                                                            <INPUT class="btn btn-custom " type="submit" id="SAVE" name="SAVE" value="Save">
                                                            <INPUT class="btn btn-light" type="button" name="cancel" value="Cancel"  onclick="window.location ='lst_employee.php';" >
                                                            <input type="hidden" name="txtHid_rec" id="txtHid_rec" value="1">
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
            <?php include("inc/common/footer.php") ?>
        </div>

    </div>

</body>

</html>

<script type="text/javascript">
    var txtHid_rec = $('#txtHid_rec').val();
    var txtHid = $('#txtHid').val();
    var emp_marital_status = $('#emp_marital_status').val();

    function fnValidate (){
        var emp_mobile1 = $('#emp_mobile1').val();
        var branch_id = $('#branch_id').val();
        var emp_name = $('#emp_name').val();
        var emp_type = $('#emp_type').val();
        var staff_status = $('#staff_status').val();
        var labour_status = $('#labour_status').val();
        var emp_pf = $('#emp_pf').val();
        var labour_id = $('#labour_id').val();
        var department_id = $('#department_id').val();
        var designation_id = $('#designation_id').val();
        var emp_date_join = $('#emp_date_join').val();
        var emp_login_name = $('#emp_login_name').val();
        var emp_login_password = $('#emp_login_password').val();
        var login_access = $('#login_access').val();
    

       

        if (branch_id == '' || branch_id == null ) {
            alert("Please Select Branch in Persnol Details Tab ..!");
            return false;
        }


        if (emp_name == '') {
            alert("Please Enter Employee Name in Persnol Details Tab ..!");
            $('#emp_name').focus();
            return false;
        }

        if (emp_type == '') {
            alert("Please Select Employee Type in Persnol Details Tab ..!");
            return false;
        }
        if (emp_type == 1) {
            // alert();
            if (staff_status == '') {
                alert("Please Select Staff Status in Persnol Details Tab ..!");
                return false;
            }
        }
        if (emp_type == 2 ) {
            // alert();
            if (labour_status == 0 || '') {
                alert("Please Select Labour Status in Persnol Details Tab ..!");
                return false;
            }
            if (emp_pf == '') {
                alert("Please Select Provident Fund in Persnol Details Tab ..!");
                return false;
            }
            if (labour_id == '') {
                alert("Please Select Labour Group on in Persnol Details Tab ..!");
                return false;
            }
        }
        if(emp_type == 3 ){ 
            if (labour_status == null || labour_status == 0 || labour_status == '') {
                alert("Please Select Labour Status in Persnol Details Tab ..!");
                return false;
            }

        }

        if (department_id == '') {  
            alert("Please Select Department in Persnol Details Tab ..!");
            return false;
        }
        if (designation_id == 0 || '') {
            alert("Please Select Designation in Persnol Details Tab ..!");
            return false;
        }
        if (emp_mobile1 == '') {
            alert("Please Enter Employee Mobile Number in Persnol Details Tab ..!");
            $('#emp_mobile1').focus();
            return false;
        }

        if (document.thisForm.emp_mobile1.value != '') {
            if ((document.thisForm.emp_mobile1.value.length) < 10) {
                alert("Please Enter 10 Digit Mobile No. ");
                $('#emp_mobile1').focus();
                return false;   
            }
        }

        if (document.thisForm.emp_email.value != '') {
            const emailValue = emp_email.value;
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (emailPattern.test(emailValue)) {} else {
                alert('Invalid email format. Please enter a valid email address.');
                $('#emp_email').val('');
                $('#emp_email').focus();
                return false;
            }
        }


        var emp_cr_add1 = $('#emp_cr_add1').val();
        var cr_state_id = $('#cr_state_id').val();
        var district_id = $('#district_id').val();
        var city_id = $('#city_id').val();
        var cr_pincode = $('#cr_pincode').val();

        if (emp_cr_add1 == '') {
            alert("Please Enter Current Address in Communication Details Tab ..!");
            $('#emp_cr_add1').focus();
            return false;
        }
        if (cr_state_id == '') {
            alert("Please  Select Current State in Communication Details Tab ..!");
            return false;
        }
        if (district_id == 0 || '') {
            alert("Please Select Current District in Communication Details Tab ..!");
            return false;
        }
        if (city_id == 0 || '') {
            alert("Please Select Current City in Communication Details Tab ..!");
            return false;
        }
        if (cr_pincode == 0 || '') {
            alert("Please Enter Current Pincode in Communication Details Tab ..!");
            $('#cr_pincode').focus();
            return false;
        }

        if (document.thisForm.cr_pincode.value != '') {
            if ((document.thisForm.cr_pincode.value.length) < 6) {
                alert("Please Enter Current Pincode No. Should be 6 Digit...! ");
                $('#cr_pincode').focus();
                return false;
            }
        }


        var copy_address = $('#copy_address').val();


        if (document.getElementById('copy_address').checked == false) {
            var emp_pr_add1 = $('#emp_pr_add1').val();
            var pr_state_id = $('#pr_state_id').val();
            var pr_district_id = $('#pr_district_id').val();
            var pr_city_id = $('#pr_city_id').val();
            var pr_pincode = $('#pr_pincode').val();

            // alert(pr_state_id);

            if (emp_pr_add1 == '') {
                alert("Please Enter Permanant Address in Communication Details Tab ..!");
                $('#emp_pr_add1').focus();
                return false;
            }
            if (pr_state_id == null || 0 || '')  {
                alert("Please Select Permanant State in Communication Details Tab ..!");
                return false;
            }
            if (pr_district_id == 0 || '') {
                alert("Please Select Permanant District in Communication Details Tab ..!");
                return false;
            }
            if (pr_city_id == 0 || '') {
                alert("Please Select Permanant City in Communication Details Tab ..!");
                return false;
            }
            if (pr_pincode == 0 || '') {
                alert("Please Enter Permanant Pincode in Communication Details Tab ..!");
                $('#pr_pincode').focus();
                return false;
            }

            if (emp_type == 1 ) {
                var emp_certname = $('.emp_certname');
                var emp_certificate = $('.emp_certificate');

                for(var i = 0; i < emp_certname.length; i++){ 
                    var emp_certname_val = $(emp_certname[i]).val();
                    if(emp_certname_val == ''){
                        alert("Please Enter Certificate Name in Documents Tab ..!");
                        $(emp_certname[i]).focus(); 
                        return false;
                    }
                }
                for(var i = 0; i < emp_certificate.length; i++){ 
                    var emp_certname_copy = ($(emp_certificate[i]).attr('value'));

                    var emp_certname_copy2 = ($(emp_certificate[i]).val());

                    if((trim(emp_certname_copy).length) == 0 && (trim(emp_certname_copy2).length) == 0){
                        alert("Please Enter Certificate Copy in Documents Tab ..!");
                        return false;
                    }
                }
            }

            if (document.thisForm.pr_pincode.value != '') {
            if ((document.thisForm.pr_pincode.value.length) < 6) {
                alert("Please Enter Permanant Pincode No. Should be 6 Digit...!  ");
                $('#pr_pincode').focus();
                return false;
            }
        }
        }
        if (emp_date_join == 0 || '') {
                alert("Please Select Date of Join in Official Details Tab ..!");
                $('#emp_date_join').focus();
                return false;
        }
        if(login_access == 1){
                if (emp_login_name == '') {
                        alert("Please Enter User Name  in Official Details Tab ..!");
                        $('#emp_login_name').focus();
                        return false;
                }
                if (emp_login_password == '') {
                        alert("Please Enter Password in Official Details Tab ..!");
                        $('#emp_login_password').focus();
                        return false;
                }
            }
    }

    

    // alert(txtHid);
    $(function() {
        var dtToday = new Date();
        var month = dtToday.getMonth() + 1; // jan=0; feb=1 .......
        var day = dtToday.getDate();
        var year = dtToday.getFullYear() - 18;
        var year1 = dtToday.getFullYear();
        if (month < 10)
            month = '0' + month.toString();
        if (day < 10)
            day = '0' + day.toString();
        var minDate = year + '-' + month + '-' + day;
        var maxDate = year + '-' + month + '-' + day;
        var maxDate1 =  year1 + '-' + month + '-' + day;
        $('#emp_dob').attr('max', maxDate);
        $('#anniversary_date').attr('max', maxDate1);
        $('#spouse_dob').attr('max', maxDate1);
        $('#issue_date').attr('max', maxDate1);
    });

    if (txtHid_rec == 1) {
        var branch_hidd = $('#branch_hidd').val();
        $("#login_access").val("0").select();
        $("#cr_state_id").val("30").select();
        $("#branch_id").val(branch_hidd).select();
        $("#prefix").val("Mr.").select();
        $("#open_bal_type").val("DR").select();
        $("#emp_marital_status").val("1").select();
    }
    

  

    $(document).ready(function() {
        if (txtHid_rec == 1) {
            $('#cr_state_id').trigger('change');
        }
        if (txtHid > 0) {
            $('#branch_id').trigger('change');

            var emp_type = $("#emp_type").val();
            if (emp_type == 1) {
            $("#staff").show();
            $("#labour").hide();
            $("#group").show();
            $("#valdate").hide();
            $("#valdate1").hide();
            $("#table_cert").show();
            $("#table_cert1").show();
            $("#appo_hide").show();
            
        } else if (emp_type == 2) {
            $("#staff").hide();
            $("#group").show();
            $("#labour").show();
            $("#valdate").show();
            $("#valdate1").show();
            $("#table_cert").hide();
            $("#table_cert1").hide();
            $("#appo_hide").hide();
            $('#emp_appointment_order').val('');
        }else if ( emp_type == 3) {
            $("#staff").hide();
            $("#group").show();
            $("#labour").show();
            $("#valdate").hide();
            $("#valdate1").hide();
            $("#table_cert").hide();
            $("#table_cert1").hide();
            $("#appo_hide").hide();
            $('#emp_appointment_order').val('');
        }
         else {
            $("#valdate").hide();
            $("#valdate1").hide();
            $("#table_cert").hide();
            $("#table_cert1").hide();
            $("#staff").hide();
            $("#group").hide();
            $("#labour").hide();
            $("#appo_hide").hide();
            $('#emp_appointment_order').val('');
        }
            $("#login_access").val();
            $('#login_access').trigger('change');
        }
        if (emp_marital_status == 0) {
            $('#emp_marital_status').trigger('change');
        }
    });

    $('#copy_address').click(function() {
        if ($(this).is(':checked')) {
            $("#pr_emp_add").hide();
            $("#pr_emp_sd").hide();
            $("#pr_emp_cp").hide();
        } else {
            $("#pr_emp_add").show();
            $("#pr_emp_sd").show();
            $("#pr_emp_cp").show();

        }
    });

    $("#staff").hide();

    $("#labour").hide();

    $("#group").hide();

    $("#sim_mobile").hide();

    $("#usage_limt").hide();

    $("#emp_usr_pass").hide();

    $("#table_cert").hide();

    $("#table_cert1").hide();

    $("#appo_hide").hide();

    // $("#epf_no").hide();

    // id="cert_count"

    $('#login_access').change(function() {
        var login_access = $('#login_access').val();
        if (login_access == 1) {
            $("#emp_usr_pass").show();
        } else {
            $("#emp_usr_pass").hide();
        }
    });

    $('#emp_name').change(function(){
        var emp_name = $('#emp_name').val();
        $('#ledger_name').val(emp_name);
    });

    $('#department_id').change(function() {

        var department_id = $('#department_id').val();

        $.ajax({
            type: "POST",
            url: "inc/cis_ajax/jquery_select_designation.php",
            data: {
                department_id: department_id
            }
        }).done(function(msg) {

            $('#designation_id option').remove();
            var dataArr = msg.split('#');
            $.each(dataArr, function(i, element) {
                if (dataArr[i] != "") {
                    var dataArr2 = dataArr[i].split('~');
                    $('#designation_id').append("<option value='" + dataArr2[0] + "'>" + dataArr2[1] + "</option>");
                }
            });
            $("#s2id_designation_id").select2('val', '');
            $("#designation_id").trigger("liszt:updated");
        });
    });


    $('#emp_marital_status').change(function() {
        var emp_marital_status = $("#emp_marital_status").val();
        if (emp_marital_status == 0) {
            $("#anvis_hide").hide();
            $("#sup_ch_hide").hide();
            $("#sup_dob_hide").hide();
        } else {
            $("#anvis_hide").show();
            $("#sup_ch_hide").show();
            $("#sup_dob_hide").show();
        }

    });

    $('#emp_type').change(function() {
        var staff_status = $("#staff_status").val();
        var labour_status = $("#labour_status").val();
        $("#staff_status").val('').change();
        $("#labour_status").val('').change();
        $("#staff").hide();
        $("#group").hide();
        $("#labour").hide();
        $("#valdate").hide();
        $("#valdate1").hide();

        var emp_type = $("#emp_type").val();
        if (emp_type == 1) {
            $("#staff").show();
            $("#labour").hide();
            $("#group").show();
            $("#valdate").hide();
            $("#valdate1").hide();
            $("#table_cert").show();
            $("#table_cert1").show();
            $("#appo_hide").show();
        } else if (emp_type == 2) {
            $("#staff").hide();
            $("#group").show();
            $("#labour").show();
            $("#valdate").show();
            $("#valdate1").show();
            $("#table_cert").hide();
            $("#table_cert1").hide();
            $("#appo_hide").hide();
        }else if (emp_type == 3) {
            $("#staff").hide();
            $("#group").show();
            $("#labour").show();
            $("#valdate").hide();
            $("#valdate1").hide();
            $("#table_cert").hide();
            $("#table_cert1").hide();
            $("#appo_hide").hide();
        } else {
            $("#valdate").hide();
            $("#valdate1").hide();
            $("#table_cert").hide();
            $("#table_cert1").hide();
            $("#staff").hide();
            $("#group").hide();
            $("#labour").hide();
            $("#appo_hide").hide();
        }
        employee_code();
    });

    $("#staff_status").change(function() {
        var per = $("#staff_status").val();

        if (per == 2) {
            $("#epf_no").show();
        } else {
            $("#epf_no").hide();
        }
        employee_code();
    });


    $("#emp_pf").change(function() {
        var pf = $("#emp_pf").val();
        if (pf == 2) {
            $("#epf_no").show();
        } else if (pf == 1) {
            $("#epf_no").hide();
            $("#emp_epf_no").val('');
            $("#emp_uan_no").val('');
        }
    });


    $("#asset_id").change(function() {
        // $(".sim_hide").hide();
        var sim_prop = $("#asset_id").val();

        if (sim_prop == 1) {
            $("#sim_mobile").show();
            $("#usage_limt").show();
        } else {
            $("#sim_mobile").hide();
            $("#usage_limt").hide();
            $("#sim_no").val('');
            $("#mobile_no").val('');
            $("#sim_limit").val('');
        }
        $("#asset_qty").val('');
        $("#asset_value").val('');
    });

    $('#cr_state_id').change(function() {

        var state_id = $('#cr_state_id').val();
        // alert(state_id);
        $.ajax({
            type: "POST",
            url: "inc/cis_ajax/jquery_select_district.php",
            data: {
                state_id: state_id
            }
        }).done(function(msg) {

            $('#district_id option').remove();
            var dataArr = msg.split('#');
            $.each(dataArr, function(i, element) {
                if (dataArr[i] != "") {
                    var dataArr2 = dataArr[i].split('~');
                    $('#district_id').append("<option value='" + dataArr2[0] + "'>" + dataArr2[1] + "</option>");
                }
            });
            $("#s2id_district_id").select2('val', '<?php echo $cr_district_id; ?>');
            $("#district_id").trigger("liszt:updated");
        });
    });

    $('#district_id').change(function() {

        var district_id = $('#district_id').val();
        $.ajax({
            type: "POST",
            url: "inc/cis_ajax/jquery_select_city.php",
            data: {
                district_id: district_id
            }
        }).done(function(msg) {

            $('#city_id option').remove();
            var dataArr = msg.split('#');
            $.each(dataArr, function(i, element) {
                if (dataArr[i] != "") {
                    var dataArr2 = dataArr[i].split('~');
                    $('#city_id').append("<option value='" + dataArr2[0] + "'>" + dataArr2[1] + "</option>");
                }
            });
            $("#s2id_city_id").select2('val', '<?php echo $cr_city_id; ?>');
            $("#city_id").trigger("liszt:updated");
        });
    });
    $('#pr_state_id').change(function() {

        var state_id = $('#pr_state_id').val();
        // alert(state_id);
        $.ajax({
            type: "POST",
            url: "inc/cis_ajax/jquery_select_district.php",
            data: {
                state_id: state_id
            }
        }).done(function(msg) {

            $('#pr_district_id option').remove();
            var dataArr = msg.split('#');
            $.each(dataArr, function(i, element) {
                if (dataArr[i] != "") {
                    var dataArr2 = dataArr[i].split('~');
                    $('#pr_district_id').append("<option value='" + dataArr2[0] + "'>" + dataArr2[1] + "</option>");
                }
            });
            $("#s2id_pr_district").select2('val', '<?php echo $pr_district_id; ?>');
            $("#pr_district_id").trigger("liszt:updated");
        });
    });
    $('#pr_district_id').change(function() {

        var district_id = $('#pr_district_id').val();
        $.ajax({
            type: "POST",
            url: "inc/cis_ajax/jquery_select_city.php",
            data: {
                district_id: district_id
            }
        }).done(function(msg) {

            $('#pr_city_id option').remove();
            var dataArr = msg.split('#');
            $.each(dataArr, function(i, element) {
                if (dataArr[i] != "") {
                    var dataArr2 = dataArr[i].split('~');
                    $('#pr_city_id').append("<option value='" + dataArr2[0] + "'>" + dataArr2[1] + "</option>");
                }
            });
            $("#s2id_pr_city").select2('val', '<?php echo $pr_city_id; ?>');
            $("#pr_city_id").trigger("liszt:updated");
        });
    });

    $("#add_items").click(function() {
        if (notSelected(document.thisForm.asset_id, "Asset Type..!")) {
            return false;
        }
        if (document.thisForm.issue_date.value == "") {
            alert("Please Select Issue Date..!");
            return false;
        }
        if (isNull(document.thisForm.asset_qty, "Quantity...!")) {
            return false;
        }
        if (isNull(document.thisForm.asset_value, "Value...!")) {
            return false;
        }

        if ((document.thisForm.asset_id.value) == 1) {
            if (isNull(document.thisForm.sim_no, "Sim Number...!")) {
                return false;
            }
            if (isNull(document.thisForm.mobile_no, "Mobile Number...!")) {
                return false;
            }

            if (document.thisForm.mobile_no.value != '') {
            if ((document.thisForm.mobile_no.value.length) < 10) {
                alert("Please Enter 10 Digit Mobile No. 1 ");
                $('#mobile_no').focus();
                return false;
            }

            }
            if (isNull(document.thisForm.sim_limit, "Usage Limit...!")) {
                return false;
            }
        }




        var asset_id = $('#asset_id').val();
        // alert(asset_id);
        var issue_date = $('#issue_date').val();
        var asset_qty = $('#asset_qty').val();
        var asset_value = $('#asset_value').val();
        var sim_no = $('#sim_no').val();
        var mobile_no = $('#mobile_no').val();
        var sim_limit = $('#sim_limit').val();
        $.ajax({
            type: "POST",
            url: "inc/cis_ajax/jquery_asset_details.php",
            data: {
                "asset_id": asset_id,
                "issue_date": issue_date,
                "asset_qty": asset_qty,
                "asset_value": asset_value,
                "sim_no": sim_no,
                "mobile_no": mobile_no,
                "sim_limit": sim_limit,
                'mode': 'save'
            }
        }).done(function(msg) {
            $('#show_table tbody').append(msg);
            $("#asset_id").val('').trigger('change');
            $("#issue_date").val('');
            $("#asset_qty").val('');
            $("#asset_value").val('');
            $("#sim_no").val('');
            $("#mobile_no").val('');
            $('#sim_limit').val('');
        });

    });

    $('#labour_status,#Branch_id').change(function() {
        employee_code();
    });

    function employee_code() {
        // alert();
        var rec_type = $("#txtHid_rec").val();
        var emp_slno = $("#emp_slno").val();
        var emp_type = $("#emp_type").val();
        var staff_status = $("#staff_status").val();
        var labour_status = $("#labour_status").val();
        var branch_id = $("#branch_id").val();
        var e_status = '';

        // alert(e_status);

        if (staff_status > 0) {
            e_status = staff_status;
        }
        if (labour_status > 0) {
            e_status = labour_status;
        }


        $.ajax({
            type: "POST",
            url: "inc/cis_ajax/jquery_select_emp_code.php",
            data: {
                "emp_type": emp_type,
                "e_status": e_status,
                "rec_type": rec_type,
                "emp_slno": emp_slno,
                "branch_id": branch_id,
            }
        }).done(function(msg) {
            var dataSal = msg.split('~');
            $('#emp_slno').val(dataSal[0]);
            $('#emp_code').val(dataSal[1]);
            $('#branch_code').val(dataSal[2]);
        });
    }

    function removeElement(id) {
        id = parseInt(id);
        if (id > 1) {
            $('#more' + id).remove();
            var num = $('#cert_count').val();
            num--;
            $('#cert_count').val(num);
        }
        

    }

    function removeElement1(id) {
        id = parseInt(id);
        if (id > 0) {
            $('#more' + id).remove();
            var num = $('#cert_count').val();
            num--;
            $('#cert_count').val(num);
        }
        var hide_emp_certcopy = $("#hide_emp_certcopy"+id+"").val();
        var no = $("#id_no"+id+"").val();
        $.ajax({
            type: 'post',
            url: 'inc/cis_ajax/remove_emp_certificate.php',
            data: {
                "no": no,
                "hide_emp_certcopy": hide_emp_certcopy
            },
            success: function(result) {
                $("#hide_emp_certcopy"+id+"").val('');
            }
        });

    }

    function remove_item(auto_id) {
		$('#' + auto_id).remove();
	}


    $('#addRow').click(function() {
        // alert();

        var cert_count = $('#cert_count').val();
        if (isNaN(cert_count)) cert_count = 1;
        $('#cert_count').val(cert_count);

        if ($('#cert_count').val() >= 5) {
            alert("You can add only 5 Documents at a time...");
            return;
        } else { //num= parseInt(num)+1;

            num = parseInt($('#cert_count').val()) + 1;
            str = '<div class="col-lg-12 pt-2" id="more' + num + '">' +
                '<div class="row">' +
                '<div class="col-lg-4"><input type="text" accept="application/pdf,.doc,.docx"  name="emp_certname[]"  class="form-control emp_certname" maxlength="30" value=""></div>' +
                '<div class="col-lg-3"><input type="file" name="emp_certcopy[]"  class="emp_certificate" src="" value=""></div>' +
                '<a href="javascript:;" onClick="removeElement(' + num + ');"><i class="icon-bin bg-delete mr-2"></i></a>' +
                '</div>' +
                '</div>';
            $('#table_cert').append(str);
            $('#cert_count').val(num);
        }
    });

    $("#asset_id").val('').trigger('change');

    $("#emp_login_name").change(function() {
        // alert();
        var emp_login_name = $('#emp_login_name').val();
        var txtHid = $('#txtHid').val();
        // alert(txtHid);
        $.ajax({
            type: "POST",
            url: "inc/cis_ajax/jquery_check_login_user_name.php",
            data: {
                "emp_login_name": emp_login_name,
                "txtHid": txtHid,
                'mode': 'check'
            }
        }).done(function(msg) {
            var msg_array = msg.split("~");
            if (msg_array[1] == 1) {
                alert("This User Name Already Exit ...!");
                $("#emp_login_name").val('');
                return false;
            }

        });


    });

    $(document).on("change", "#emp_photo", function() {


    myfile = $(this).val();
    var ext = myfile.split('.').pop();
    if (ext == "pdf" || ext == "jpg" || ext == "png" || ext == "jpeg" ) {

    } else {
        alert("Please check the file type of Employee Photo.\nAllowed File Type: .JPG, .PNG, .PDF, .JPEG.\nMaximum File Size : 500 kb...!");
        $(this).val('');
    }
    if (this.files[0].size > 500000) {
        alert("Maximum File Size Should be 500kb...!");
        $(this).val('');
    }
    })
    $(document).on("change", "#emp_pan_copy", function() {


    myfile = $(this).val();
    var ext = myfile.split('.').pop();
    if (ext == "pdf" || ext == "jpg" || ext == "png" || ext == "jpeg") {

    } else {
        alert("Please check the file type of Employee Pan copy.\nAllowed File Type: .JPG, .PNG, .PDF, .JPEG.\nMaximum File Size : 500 kb...!");
        $(this).val('');
    }
    if (this.files[0].size > 500000) {
        alert("Maximum File Size Should be 500kb...!");
        $(this).val('');
    }
    })
    $(document).on("change", "#emp_aadhar_copy", function() {


    myfile = $(this).val();
    var ext = myfile.split('.').pop();
    if (ext == "pdf" || ext == "jpg" || ext == "png" || ext == "jpeg") {

    } else {
        alert("Please check the file type of Employee Aadhar Copy.\nAllowed File Type: .JPG, .PNG, .PDF, .JPEG.\nMaximum File Size : 500 kb...!");
        $(this).val('');
    }
    if (this.files[0].size > 500000) {
        alert("Maximum File Size Should be 500kb...!");
        $(this).val('');
    }
    })
    $(document).on("change", "#emp_add_proof_copy", function() {


    myfile = $(this).val();
    // alert(myfile);
    var ext = myfile.split('.').pop();
    if (ext == "pdf" || ext == "jpg" || ext == "png" || ext == "jpeg") {

    } else {
        alert("Please check the file type of Employee Address Proof Copy.\nAllowed File Type: .JPG, .PNG, .PDF, .JPEG.\nMaximum File Size : 500 kb...!");
        $(this).val('');
    }
    if (this.files[0].size > 500000) {
        alert("Maximum File Size Should be 500kb...!");
        $(this).val('');
    }
    })
    $(document).on("change", "#emp_agreement_order", function() {


    myfile = $(this).val();
    var ext = myfile.split('.').pop();
    if (ext == "pdf" || ext == "jpg" || ext == "png" || ext == "jpeg") {

    } else {
        alert("Please check the file type of Employee Agreement Order.\nAllowed File Type: .JPG, .PNG, .PDF, .JPEG.\nMaximum File Size : 500 kb...!");
        $(this).val('');
    }
    if (this.files[0].size > 500000) {
        alert("Maximum File Size Should be 500kb...!");
        $(this).val('');
    }
    })
    $(document).on("change", "#emp_appointment_order", function() {


    myfile = $(this).val();
    var ext = myfile.split('.').pop();
    if (ext == "pdf" || ext == "jpg" || ext == "png" || ext == "jpeg") {

    } else {
        alert("Please check the file type of employee Appoinment order.\nAllowed File Type: .JPG, .PNG, .PDF, .JPEG.\nMaximum File Size : 500 kb...!");
        $(this).val('');
    }
    if (this.files[0].size > 500000) {
        alert("Maximum File Size Should be 500kb...!");
        $(this).val('');
    }
    })

    $(document).on("change", ".emp_certificate", function() {

    myfile = $(this).val();
    var ext = myfile.split('.').pop();
    if (ext == "pdf" || ext == "jpg" || ext == "png" || ext == "jpeg") {

    } else {
        alert("Please check the file type of Employee Certificate.\nAllowed File Type: .JPG, .PNG, .PDF, .JPEG.\nMaximum File Size : 500 kb...!");
        $(this).val('');
    }
    if (this.files[0].size > 500000) {
        alert("\nMaximum File Size Should be 500kb...!");
        $(this).val('');
    }
    })
</script>