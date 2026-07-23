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


if (isset($_POST['SAVE'])){
  		
	$update_id = $_REQUEST['txtHid'];
	try
	{
		
		$advance_amount = $dbconn->GetSingleReconrd("tbl_emp_advance","SUM(advance_amount)","is_current=1 AND advance_status != 3 AND emp_id",$update_id);

		$return_amount = $dbconn->GetSingleReconrd("tbl_emp_advance","SUM(return_amount)","is_current=1 AND advance_status != 3 AND emp_id",$update_id);

		if($advance_amount==0 || $advance_amount == ''){
		$advance_amount = $_REQUEST['advance_amount'];
		}

		if($return_amount==0 || $return_amount == ''){
		$return_amount = 0;
		}

		$balance_amount = $advance_amount - $return_amount;
		
		
	$_REQUEST['pay_finyr'] = $dbconn->GetSingleReconrd("mst_finyear","finyr","finyr_active",1);
	$_REQUEST['advance_slno'] = $dbconn->GetMaxValue('tbl_emp_advance','advance_slno','pay_finyr',$_REQUEST['pay_finyr'])+1;

	if($_REQUEST['pay_type'] == 'Q')
	{
		$chq_passed = "NO";
	}
	else
	{
		$chq_passed = "";
	}
    
		$stmt2 = null;
		$stmt2 = $conn->prepare("INSERT INTO tbl_emp_advance (emp_id,advance_slno, advance_date, advance_amount, balance_amount, pay_finyr,ledger_id,pay_type,pay_cardno,pay_refno,pay_chq_no,pay_chq_dt,chq_passed,advance_remarks, is_current, advance_status, update_by, update_dtm) VALUES 
											(:emp_id,:advance_slno, :advance_date, :advance_amount, :balance_amount,:pay_finyr,:ledger_id,:pay_type,:pay_cardno,:pay_refno,:pay_chq_no,:pay_chq_dt,:chq_passed, :advance_remarks, :is_current, :advance_status, :update_by, :update_dtm)");		
		$data2 = array(
			':emp_id' => $update_id,
			':advance_slno' => $_REQUEST['advance_slno'],
			':advance_date' => date('Y-m-d',strtotime($_REQUEST['advance_date'])),
			':advance_amount' => $_REQUEST['advance_amount'],
			':balance_amount' => $_REQUEST['advance_amount'],
			':pay_finyr' => $_REQUEST['pay_finyr'],
			':ledger_id' => $_REQUEST['ledger_id'],	
			':pay_type' => $_REQUEST['pay_type'],
			':pay_cardno' => $_REQUEST['pay_cardno'],
			':pay_refno' => strtoupper($_REQUEST['pay_refno']),
			':pay_chq_no' => $_REQUEST['pay_chq_no'],
			':pay_chq_dt' => $_REQUEST['pay_chq_dt'],
			':chq_passed' => $chq_passed,
			':advance_remarks' => $_REQUEST['advance_remarks'],
			':is_current' => 1,
			':advance_status' => 1,
			':update_by' => $_SESSION['_user_id'],
			':update_dtm' => date('Y-m-d H:i:s')
		);
        // print_r($data2);
		$stmt2->execute($data2);
		$last_id = $conn->lastInsertId();
		
		/* Account update */
		$emp_ledger_id = $dbconn->GetSingleReconrd("mst_employee","ledger_id","emp_id",$update_id);
		$acc_main_entry = $conn->prepare("INSERT INTO tbl_accounts (acc_date, emp_id, voucher_type, record_type, advance_id, acc_tran_value, dr_ledger_id, cr_ledger_id) VALUES (:acc_date, :emp_id, :voucher_type, :record_type, :advance_id, :acc_tran_value, :dr_ledger_id, :cr_ledger_id)");		
		
		$acc_main_data = array(				
			':acc_date' => date('Y-m-d'),		
			':emp_id' => $update_id,
			':voucher_type' => "Employee Advance",
			':record_type' => "M",
			':advance_id' => $last_id,
			':acc_tran_value' => $_REQUEST['advance_amount'],
			':dr_ledger_id' => $emp_ledger_id,
			':cr_ledger_id' => $_REQUEST['ledger_id']
		);
        // print_r($acc_main_data);die();
		$acc_main_entry->execute($acc_main_data);		
		/* Account update */		
					
		$_SESSION['_msg'] = "Employee Advance details has been succesfully Updated..!";
	}
	catch (Exception $e)
	{		
		$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
		$_SESSION['_msg_err'] = $str;			
	}
	header("location:employee_advance.php");	
	die();
}

if ($_REQUEST['emp_id'] != '') {
    $is_allow_fh = '2';
    // $result = $conn->query("SELECT * FROM mst_employee WHERE rec_del_status = '1' AND emp_id = " . $_REQUEST['emp_id']);
    $result = $conn->query("SELECT * FROM mst_employee  WHERE emp_id = " . $_REQUEST['emp_id']);
    if ($result->rowCount() > 0) {
        $obj = $result->fetch(PDO::FETCH_OBJ);

        $emp_id = $obj->emp_id;

        $emp_name = $obj->emp_name;
        $emp_fat_hus_name = $obj->emp_fat_hus_name;
        $emp_code = $obj->emp_code;
        $is_allow_fh = $obj->is_allow_fh;
    }
    $designation_name = $dbconn->GetSingleReconrd("mst_designation", "designation_name", "rec_del_status = 1  AND designation_id", $obj->designation_id);
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>
        <?php echo PAGE_TITLE; ?>- Employee Advance
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
                            <span class="breadcrumb-item active">Employee Advance</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>

            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">

                        <form name="thisForm" class="form-horizontal" method='POST' action="emp_advance_update.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                            <fieldset>
                                <div class="card">
                                    <div class="card-header bg-pgheader text-white header-elements-inline">
                                        <h6 class="card-title">Employee Advance</h6>
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
                                                            <h6 class="card-title">Advance Details</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="col-lg-12">
                                                                <div class="form-group row  pt-2">
                                                                    <label class="col-lg-5 col-form-label"><b>Date <span class="text-mandatory">*</span></b></label>
                                                                    <!-- <input class="col-lg-6 form-control" type="text" name="" id="" value=""/> -->
                                                                    <div class="col-lg-7">
                                                                        <input type="date" name="advance_date" id="advance_date" class="form-control " min="" maxlength="" value="" max="">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row  pt-2">
                                                                    <label class="col-lg-5 col-form-label"><b>Advance <span class="text-mandatory">*</span></b></label>
                                                                    <div class="col-lg-7">
                                                                        <input  class=" form-control" type="text" name="advance_amount" id="advance_amount"  onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="7" value="<?php echo $obj->advance_amount; ?>" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row  pt-2">
                                                                    <label class="col-lg-5 col-form-label"><b>Ledger <span class="text-mandatory">*</span></b></label>
                                                                    <div class="col-lg-7">
                                                                        <select name="ledger_id" id="ledger_id" data-placeholder="Select a Ledger .." class="select-search">
                                                                            <option value="">Select Ledger</option>
                                                                            <?php
                                                                            echo $dbconn->fnFillComboFromTable_Where("ledger_id", "ledger_name", "mst_ledger", "ledger_id", " WHERE ledger_status = 1");
                                                                            ?>
                                                                        </select>

                                                                    </div>
                                                                </div>
                                                                <div class="form-group row  pt-2">
                                                                    <label class="col-lg-5 col-form-label"><b>Pay Mode <span class="text-mandatory">*</span></b></label>
                                                                    <div class="col-lg-7">
                                                                        <select name="pay_type" id="pay_type" data-placeholder="Choose a Pay mode.." class="select-search">
                                                                            <option value="">--Select--</option>
                                                                            <option value="C">Cash</option>
                                                                            <option value="Q">Cheque</option>
                                                                            <option value="B">Card</option>
                                                                            <option value="N">Net Banking</option>
                                                                            <option value="A">Account Transfer</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row  pt-2" id="pay_chq_dt_div">
                                                                    <label class="col-lg-5 col-form-label"><b> Passed Date <span class="text-mandatory">*</span></b></label>
                                                                    <!-- <input class="col-lg-6 form-control" type="text" name="" id="" value=""/> -->
                                                                    <div class="col-lg-7">
                                                                        <input type="date" name="pay_chq_dt" id="pay_chq_dt" class="form-control " min="" maxlength="" value="" max="">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row  pt-2" id="pay_chq_no_div">
                                                                    <label class="col-lg-5 col-form-label"><b>Cheque No. <span class="text-mandatory">*</span></b></label>
                                                                    <div class="col-lg-7">
                                                                        <input class=" form-control" type="text" name="pay_chq_no" id="pay_chq_no" onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="15" placeholder="Cheque No." value="<?php echo $obj->advance_amount; ?>" />
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row  pt-2" id="pay_cardno_div">
                                                                    <label class="col-lg-5 col-form-label"><b>Card No. <span class="text-mandatory">*</span></b></label>
                                                                    <div class="col-lg-7">
                                                                        <input class=" form-control" type="text" name="pay_cardno" id="pay_cardno" placeholder="Card No." value="<?php echo $obj->advance_amount; ?>" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row  pt-2" id="pay_refno_div">
                                                                    <label class="col-lg-5 col-form-label"><b>Ref No. <span class="text-mandatory">*</span></b></label>
                                                                    <div class="col-lg-7">
                                                                        <input class=" form-control" type="text" name="pay_refno" id="pay_refno" placeholder="Ref No." value="<?php echo $obj->advance_amount; ?>" />
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row  pt-2">
                                                                    <label class="col-lg-5 col-form-label"><b>Remarks if</b></label>
                                                                    <div class="col-lg-7">
                                                                        <textarea class="form-control" name="advance_remarks" id="advance_remarks" value=""></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card-footer text-center">
                                                            <?php if ($_REQUEST["emp_id"] != '' && $obj->rec_del_status == 1) { ?>
                                                                <INPUT class="btn btn-info" type="submit" name="SAVE" id="submit" value="Save">
                                                                <INPUT class="btn btn-light" type="button" name="Cancel" value="Cancel" onClick="javascript:history.go(-1);">
                                                                <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['emp_id']; ?>">
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-lg-6">
                                                <?php
                                                    echo '<div class="card">
                                                        <div class="card-header bg-info text-white header-elements-inline">
                                                            <h6 class="card-title">Advance Update History</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="col-lg-12">
                                                                <div class="row">';
                                                            
                                                                $advance_amount = 0;
												
                                                                $SQL1 = "SELECT DISTINCT * FROM tbl_emp_advance WHERE emp_id = ".$emp_id." AND advance_status != 3 ORDER BY advance_date ASC";
                            
                                                                $result1 = $conn->query($SQL1);
                                                                     if ($result1->rowCount() > 0)
                                                                        {
                                                                            // echo "hi";
                                                                            $sno=1;
                                                                            while ($ut = $result1->fetch()){
                                                                                
                                                                               

                                                                             
                                                                                echo '  <div class="col-lg-2" style="  text-align:center; padding-top:5px;">
                                                                                            <b>'.$sno.'</b>
                                                                                        </div>
                                                                                        <div class="col-lg-10">
                                                                                        Advance <strong>Rs.'.$ut->advance_amount.'</strong> <span class="add-on" id="history"></span> is given by '.$dbconn->GetSingleReconrd("tbl_user","usr_name","usr_id",$ut->update_by).' on '.date("d-M-Y", strtotime($ut->update_dtm)).'';
                                                                                        if($ut->advance_remarks!=''){
                                                                                        echo '<br>Note : '.$ut->advance_remarks.'';
                                                                                        }
                                                                                       echo' </div><legend class="font-weight-semibold"></legend>';
                                                                                        // $emp_ctc = $ut->emp_ctc;
                                                                                        $sno++;
                                                                            }
                                                                            // echo'</div><legend class="font-weight-semibold"></legend>';
                                                                        }
                                                                
                                                            echo'</div>
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

function fnValidate(){	

if(isNull(document.thisForm.advance_date,"Advance Date..!")){ return false; }

if(isNull(document.thisForm.advance_amount,"Advance Amount..!")){ return false; }

if(notSelected(document.thisForm.ledger_id,"Ledger..!")){ return false; }

if(notSelected(document.thisForm.pay_type,"Payment Mode..!")){ return false; }

if (document.thisForm.pay_type.value =="Q")
{
    // alert();
    if(isNull(document.thisForm.pay_chq_dt," Passed Date. ..!")){ return false; }
    if(isNull(document.thisForm.pay_chq_no,"Cheque No. ..!")){ return false; }
    
}if (document.thisForm.pay_type.value =="B")
{
    if(isNull(document.thisForm.pay_chq_dt," Passed Date. ..!")){ return false; }
    if(isNull(document.thisForm.pay_cardno,"Card No. ..!")){ return false; }

}if (document.thisForm.pay_type.value =="N")
{
    if(isNull(document.thisForm.pay_chq_dt," Passed Date. ..!")){ return false; }
    if(isNull(document.thisForm.pay_refno,"Reference No. ..!")){ return false; }
}if (document.thisForm.pay_type.value =="A")
{
    if(isNull(document.thisForm.pay_chq_dt," Passed Date. ..!")){ return false; }
    if(isNull(document.thisForm.pay_refno,"Reference No. ..!")){ return false; }
}

document.thisForm.submit();

}


    $('#pay_cardno_div').hide();
    $('#pay_refno_div').hide();
    $('#pay_chq_no_div').hide();
    $('#pay_chq_dt_div').hide();

    $('#pay_type').change(function() {
        var pay_mode = $('#pay_type').val();
        $('#pay_cardno').val('');
            $('#pay_refno').val('');
            $('#pay_chq_no').val('');
            $('#pay_chq_dt').val('');
        if (pay_mode == "Q") {
            $('#pay_cardno_div').hide();
            $('#pay_refno_div').hide();
            $('#pay_chq_no_div').show();
            $('#pay_chq_dt_div').show();
        } else if (pay_mode == "N") {
            $('#pay_cardno_div').hide();
            $('#pay_refno_div').show();
            $('#pay_chq_no_div').hide();
            $('#pay_chq_dt_div').show();

        } else if (pay_mode == "B") {
            $('#pay_cardno_div').show();
            $('#pay_refno_div').hide();
            $('#pay_chq_no_div').hide();
            $('#pay_chq_dt_div').show();
        } else if (pay_mode == "A") {
            $('#pay_cardno_div').hide();
            $('#pay_refno_div').show();
            $('#pay_chq_no_div').hide();
            $('#pay_chq_dt_div').show();
        } else if (pay_mode == "C") {
            $('#pay_cardno_div').hide();
            $('#pay_refno_div').hide();
            $('#pay_chq_no_div').hide();
            $('#pay_chq_dt_div').hide();
        } else {
            $('#pay_cardno_div').hide();
            $('#pay_refno_div').hide();
            $('#pay_chq_no_div').hide();
            $('#pay_chq_dt_div').hide();
        }
    });
    // $('#advance_date').datepicker('setDate', 'today');
</script>
<!-- Footer -->