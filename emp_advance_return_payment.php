<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");
require_once("inc/common/css-js.php");

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();


if (isset($_POST['SAVE'])) {
    // die();

    $update_id = $_REQUEST['txtHid'];
    try {
        // echo "!!";

         $advance_amount = $dbconn->GetSingleReconrd("tbl_emp_advance","SUM(advance_amount)","is_current=1 AND advance_status != 3 AND advance_id = ".$_REQUEST['advance_id']." AND emp_id",$update_id);

		$return_amount = $dbconn->GetSingleReconrd("tbl_emp_advance","SUM(return_amount)","is_current=1 AND advance_status != 3  AND advance_id = ".$_REQUEST['advance_id']." AND  emp_id",$update_id); 

		$ledger_id = $dbconn->GetSingleReconrd("tbl_emp_advance","ledger_id","is_current=1 AND advance_status != 3 AND  emp_id",$update_id);

		// if($advance_amount==0 || $advance_amount == ''){
		// $advance_amount = $_REQUEST['advance_amount'];
		// }

		if($return_amount==0 || $return_amount == ''){
		$return_amount = 0;
		}

		$balance_amount = $advance_amount - $return_amount;

		$atten_month =0;

		$atten_year = '';

		if(isset($_REQUEST['atten_month']) && $_REQUEST['atten_month']!=''){
			$atten_month =$_REQUEST['atten_month'];
		}
		if(isset($_REQUEST['atten_year']) && $_REQUEST['atten_year']!=''){
			$atten_year =$_REQUEST['atten_year'];
		}

		$_REQUEST['pay_finyr'] = $dbconn->GetSingleReconrd("mst_finyear","finyr","finyr_active",1);

		$_REQUEST['payment_slno'] = $dbconn->GetMaxValue('tbl_emp_advance_return_payment','payment_slno','pay_finyr',$_REQUEST['pay_finyr'])+1;

		$stmt2 = null;

		$stmt2 = $conn->prepare("INSERT INTO tbl_emp_advance_return_payment (emp_id,payment_slno,pay_finyr, advance_id, payment_date, payment_amount, payment_remarks, atten_month, atten_year, payment_status, update_by, update_dtm) VALUES 
											(:emp_id,:payment_slno,:pay_finyr,:advance_id, :payment_date, :payment_amount,:payment_remarks, :atten_month, :atten_year,:payment_status, :update_by, :update_dtm)");		
		$data2 = array(
			':emp_id' => $update_id,
			':payment_slno' => $_REQUEST['payment_slno'],
			':pay_finyr' => $_REQUEST['pay_finyr'],
			':advance_id' => $_REQUEST['advance_id'],
			':payment_date' => date('Y-m-d',strtotime($_REQUEST['payment_date'])),
			':payment_amount' => $_REQUEST['payment_amount'],
			':payment_remarks' => $_REQUEST['payment_remarks'],
			':atten_month' => $atten_month,
			':atten_year' => $atten_year,
			':payment_status' => 1,
			':update_by' => $_SESSION['_user_id'],
			':update_dtm' => date('Y-m-d H:i:s')
		);
        // print_r($data2); die();
		$stmt2->execute($data2);
		$last_id = $conn->lastInsertId();
		
		echo $total_paid_amount = $dbconn->GetSingleReconrd("tbl_emp_advance_return_payment","SUM(payment_amount)","advance_id = ".$_REQUEST['advance_id']." AND emp_id",$update_id);
		
		if($total_paid_amount>=$advance_amount){

		$sql =  "UPDATE tbl_emp_advance SET return_amount = return_amount +".$_REQUEST['payment_amount'].", balance_amount = balance_amount - ".$_REQUEST['payment_amount'].", update_by = ".$_SESSION['_user_id'].", update_dtm = '".date('Y-m-d H:i:s')."',advance_status = 3 WHERE advance_id  = ".$_REQUEST['advance_id']." ";

		$result = $conn->prepare($sql);
		$result->execute();
		}else{
			$sql =  "UPDATE tbl_emp_advance SET return_amount = return_amount +".$_REQUEST['payment_amount'].", balance_amount = balance_amount - ".$_REQUEST['payment_amount'].", update_by = ".$_SESSION['_user_id'].", update_dtm = '".date('Y-m-d H:i:s')."' WHERE advance_id  = ".$_REQUEST['advance_id']." ";
            // print_r($sql); die();
			$result = $conn->prepare($sql);
			$result->execute();
		}
		/* Account update */
		$emp_ledger_id = $dbconn->GetSingleReconrd("mst_employee","ledger_id","emp_id",$update_id);

		$acc_main_entry = $conn->prepare("INSERT INTO tbl_accounts (acc_date, emp_id, voucher_type, record_type, advance_deduction_id, acc_tran_value, dr_ledger_id, cr_ledger_id) VALUES (:acc_date, :emp_id, :voucher_type, :record_type, :advance_deduction_id, :acc_tran_value, :dr_ledger_id, :cr_ledger_id)");

		$acc_main_data = array(				
			':acc_date' => date('Y-m-d'),		
			':emp_id' => $update_id,
			':voucher_type' => "Employee Advance Deduction",
			':record_type' => "M",
			':advance_deduction_id' => $last_id,    
			':acc_tran_value' => $_REQUEST['payment_amount'],
			':dr_ledger_id' => $ledger_id,
			':cr_ledger_id' => $emp_ledger_id,
		);
		$acc_main_entry->execute($acc_main_data);		
		
		/* Account update */
		
		$_SESSION['_msg'] = "Employee Advance details has been succesfully Updated..!";
    } catch (Exception $e) {
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
    }
    if(isset($_REQUEST['atten_month']) && $_REQUEST['atten_month']!='' && $_REQUEST['redirect_page']=='pf'){

        header("location:attendance_report_pf.php?month_id=".$_REQUEST['atten_month']."&atten_year=".$_REQUEST['atten_year']." ");	

        }else if(isset($_REQUEST['atten_month']) && $_REQUEST['atten_month']!='' && $_REQUEST['redirect_page']=='wpf'){

        header("location:attendance_report_withoutpf.php?month_id=".$_REQUEST['atten_month']."&atten_year=".$_REQUEST['atten_year']." ");	

        }else{

         header("location:employee_advance.php");

    }
}

if ($_REQUEST['id'] != '') {
    // $result = $conn->query("SELECT * FROM mst_employee WHERE rec_del_status = '1' AND emp_id = " . $_REQUEST['emp_id']);
    $result = $conn->query("SELECT * FROM mst_employee  WHERE emp_id = " . $_REQUEST['id']);
    if ($result->rowCount() > 0) {
        $obj = $result->fetch(PDO::FETCH_OBJ);

        $emp_id = $obj->emp_id;

        $emp_name = $obj->emp_name;
        $emp_fat_hus_name = $obj->emp_fat_hus_name;
        $emp_code = $obj->emp_code;
        // $is_allow_fh = $obj->is_allow_fh;
    }
    $designation_name = $dbconn->GetSingleReconrd("mst_designation", "designation_name", "rec_del_status = 1  AND designation_id", $obj->designation_id);

    $balance_amount = $dbconn->GetSingleReconrd("tbl_emp_advance", "balance_amount", "advance_status != 3 AND emp_id",$emp_id); 
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>
        <?php echo PAGE_TITLE; ?>- Employee Advance Deduction
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
                            <a href="#" class="breadcrumb-item">Masters</a>
                            <span class="breadcrumb-item active">Employee Advance Deduction</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>

            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">

                        <form name="thisForm" class="form-horizontal" method='POST' action="emp_advance_return_payment.php" onSubmit="return fnValidate();" enctype="multipart/form-data">

                            <input type="hidden" name="emp_id" id="emp_id" value="<?php echo $emp_id; ?>">
                            <input type="hidden" name="atten_year" id="atten_year" value="<?php echo $_REQUEST['atten_year']; ?>">
                            <input type="hidden" name="atten_month" id="atten_month" value="<?php echo $_REQUEST['atten_month']; ?>">
                            <input type="hidden" name="net_amount" id="net_amount" value="<?php echo $_REQUEST['net']; ?>">
                            <input type="hidden" name="redirect_page" id="redirect_page" value="<?php echo $_REQUEST['page']; ?>">
                            <input type="hidden" name="balance_amount" id="balance_amount" value="<?php echo $balance_amount; ?>">
                            <fieldset>
                                <div class="card">  
                                    <div class="card-header bg-pgheader text-white header-elements-inline">
                                        <h6 class="card-title">Employee Advance Deduction</h6>
                                        <div class="header-elements">
                                            <div class="list-icons">
                                                <a class="list-icons-item" href="employee_advance.php" title="Employee Advance List"><i class="icon-arrow-left52 mr-2"></i></a>
                                                <a class="list-icons-item" data-action="fullscreen"></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body" id="">
                                        <div class="form-group col-lg-12 p-2">
                                            <div class="row">
                                                <div class="col-lg-10">
                                                    <div class="form-group col-lg-12">
                                                        <div class="row">
                                                            <label class="col-lg-6 col-form-label"><b>Employee Name
                                                                    <span style="padding-left:60px; color: blue; font-size: 15px; font-weight: bold;"><?php echo $obj->prefix . "&nbsp;" . $emp_name ?></span></b>
                                                            </label>
                                                            <label class="col-lg-6 col-form-label"><b>Father/Husband Name<span style="padding-left:60px; color: blue; font-size: 15px; font-weight: bold;"><?php echo $emp_fat_hus_name ?></span></b></label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-lg-12    ">
                                                        <div class="row">
                                                            <label class="col-lg-6 col-form-label"><b>Employee Code<span style="padding-left:70px; color: blue; font-size: 15px; font-weight: bold;"><?php echo $emp_code ?></span></b></label>
                                                            <label class="col-lg-6 col-form-label"><b>Designation<span style="padding-left:122px; color: blue; font-size: 15px; font-weight: bold;"><?php echo $designation_name ?></span></b></label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-lg-12">
                                                        <div class="row">
                                                            <label class="col-lg-6 col-form-label"><b>Mobile Number<span style="padding-left:70px; color: blue; font-size: 15px; font-weight: bold;"><?php echo $obj->emp_mobile ?></span></b></label>
                                                            <label class="col-lg-6 col-form-label"><b>Date of Joining<span style="padding-left:103px; color: blue; font-size: 15px; font-weight: bold;"><?php echo date("d-m-Y", strtotime($obj->emp_date_join)) ?></span></b></label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-2">
                                                    <img src="project_img/emp_photo/<?php if ($obj->emp_photo != '') {
                                                                                        echo $obj->emp_photo;
                                                                                    } else { ?>usravatar.png <?php } ?>" class="img-fluid">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-lg-12 p-2">
                                            <div class="row">
                                                <div class=" col-lg-6">
                                                    <div class="card">
                                                        <div class="card-header bg-info text-white header-elements-inline">
                                                            <h6 class="card-title">Advance Deduction Details</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="col-lg-12">
                                                                <div class="form-group row  pt-2">
                                                                    <label class="col-lg-5 col-form-label"><b>Date <span class="text-mandatory">*</span></b></label>
                                                                    <!-- <input class="col-lg-6 form-control" type="text" name="" id="" value=""/> -->
                                                                    <div class="col-lg-7">
                                                                        <input type="date" name="payment_date" id="payment_date" class="form-control " min="" maxlength="" value="" max="">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row  pt-2">
                                                                    <label class="col-lg-5 col-form-label"><b>Deduction for <span class="text-mandatory">*</span></b></label>
                                                                    <div class="col-lg-7">
                                                                        <select name="advance_id" id="advance_id" data-placeholder="Choose" class="select-search">
                                                                            <option value="">Select Ledger</option>
                                                                            <?php
                                                                            echo $dbconn->fnFillComboFromTable_Where("advance_id", "CONCAT(advance_date,'~',balance_amount)", "tbl_emp_advance", "advance_id", " WHERE advance_status != 3 AND emp_id = '" . $emp_id . "'");
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row  pt-2">
                                                                    <label class="col-lg-5 col-form-label"><b>Advance <span class="text-mandatory">*</span></b></label>
                                                                    <div class="col-lg-7">
                                                                        <input class=" form-control" type="text" name="payment_amount" id="payment_amount" onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="7" value="2000" />
                                                                    </div>
                                                                </div>


                                                                <div class="form-group row  pt-2">
                                                                    <label class="col-lg-5 col-form-label"><b>Remarks if</b></label>
                                                                    <div class="col-lg-7">
                                                                        <textarea class="form-control" name="payment_remarks" id="payment_remarks" value=""></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card-footer text-center">
                                                            <INPUT class="btn btn-info" type="submit" name="SAVE" id="submit" value="Save">
                                                            <INPUT class="btn btn-light" type="button" name="Cancel" value="Cancel" onClick="javascript:history.go(-1);">
                                                            <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $emp_id;?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-lg-6">
                                                    <?php
                                                    echo '<div class="card">
                                                        <div class="card-header bg-info text-white header-elements-inline">
                                                            <h6 class="card-title">Advance Deduction History</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="col-lg-12">
                                                                <div class="row">';

                                                    $advance_amount = 0;

                                                    $SQL1 = "SELECT DISTINCT * FROM tbl_emp_advance_return_payment WHERE emp_id = " . $emp_id . " ORDER BY payment_date ASC";

                                                    $result1 = $conn->query($SQL1);
                                                    if ($result1->rowCount() > 0) {
                                                        // echo "hi";
                                                        $sno = 1;
                                                        while ($ut = $result1->fetch()) {




                                                            echo '  <div class="col-lg-2" style="  text-align:center; padding-top:5px;">
                                                                                            <b>' . $sno . '</b>
                                                                                        </div>
                                                                                        <div class="col-lg-10">
                                                                                        Payment  <strong>Rs.' . $ut->payment_amount . '</strong> <span class="add-on" id="history"></span> is taken by ' . $dbconn->GetSingleReconrd("tbl_user","usr_name","usr_id",$ut->update_by) . ' on ' . date("d-M-Y", strtotime($ut->update_dtm)) . '';
                                                            if ($ut->advance_remarks != '') {
                                                                echo '<br>Note : ' . $ut->advance_remarks . '';
                                                            }
                                                            echo ' </div><legend class="font-weight-semibold"></legend>';
                                                            // $emp_ctc = $ut->emp_ctc;
                                                            $sno++;
                                                        }
                                                        // echo'</div><legend class="font-weight-semibold"></legend>';
                                                    }

                                                    echo '</div>
                                                        </div>
                                                    </div>';
                                                    ?>
                                                </div>

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

<script language="javascript" type="text/javascript">
    function fnValidate() {

        if(isNull(document.thisForm.payment_date,"Date..!")){ return false; }

        if(notSelected(document.thisForm.advance_id,"Deduction..!")){ return false; }

        if(isNull(document.thisForm.payment_amount,"Amount..!")){ return false; }

        // if(isNull(document.thisForm.payment_remarks," Remarks..!")){ return false; }

	var net = parseFloat($('#net_amount').val());

    // var balance_amount = parseFloat($('#balance_amount').val());
    var balance_amount = $('#balance_amount').val();

    // alert(balance_amount);

    

	var pay_amount = parseFloat($('#payment_amount').val());
	//alert(net+'---'+pay_amount);return false;


	if(net > 0){
		if(pay_amount > balance_amount){
			alert("Advance Deduction is Higher than Select Deduction for !");
			$('#payment_amount').val('').focus();
			return false;
		}
	}
    if(net > 0){
		if(pay_amount > net){
			alert("Advance Deduction is Higher than salary!");
			$('#payment_amount').val('').focus();
			return false;
		}
	}



	document.thisForm.submit();

    }




   
    // $('#advance_date').datepicker('setDate', 'today');
</script>
<!-- Footer -->