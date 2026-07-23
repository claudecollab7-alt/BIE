<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");
require_once("inc/common/css-js.php");



isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

if (isset($_POST['GET_ATTN'])) {
    print_r($_POST['GET_ATTN']);
    header("location:attendance_report_withoutpf.php?month_id=" . $_REQUEST['month_id'] . "&atten_year=" . $_REQUEST['atten_year'] . "&emp_id=" . $_REQUEST['emp_id']);
    die();
}



if ($_REQUEST['atten_year'] == '') {
    $atten_year = date('Y');
} else {
    $atten_year = $_REQUEST['atten_year'];
}
if ($_REQUEST['emp_id'] != '') {
    $emp_id1 = $_REQUEST['emp_id'];
}

if ($_REQUEST['month_id'] == '') {
    $month_id = date('m');
} else {
    $month_id = $_REQUEST['month_id'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>
        <?php echo PAGE_TITLE; ?>-Attendance for Month
    </title>
	<?php include_once("inc/common/css-js.php"); ?>
    <script type="text/javascript" src="print_me.js"></script>
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
                            <a href="#" class="breadcrumb-item">Attendance and Salary</a>
                            <span class="breadcrumb-item active">Salary</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>

            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">

                        <form name="thisform" class="form-horizontal" method='POST' action="attendance_report_withoutpf.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                            <?php
                            $dateObj   = DateTime::createFromFormat('!m', $month_id);
                            $monthName = $dateObj->format('F');
							
							$total_days = $dbconn->GetSingleReconrd("tbl_month_master","total_days","year = ".$atten_year." AND month",$_REQUEST['month_id']);

							$total_working_days = $dbconn->GetSingleReconrd("tbl_month_master", "working_days", "year = " . $atten_year . " AND month", $_REQUEST['month_id']);

							$emp_type3 = $total_days;

							$total_working_days1 = $total_working_days;

							$function_holidays = $dbconn->GetSingleReconrd("tbl_month_master", "function_holidays", "year = " . $atten_year . " AND month", $_REQUEST['month_id']);

							

							$function_holidays = $dbconn->GetSingleReconrd("tbl_month_master","function_holidays","year = ".$atten_year." AND month",$_REQUEST['month_id']);

                            ?>
                            <fieldset>
                                <div class="card">
                                    <div class="card-header bg-pgheader text-white header-elements-inline">
                                        <h6 class="card-title">Employee's Salary Report</h6>
                                        <div class="header-elements">
                                            <div class="list-icons">
                                                <a class="list-icons-item" href="#" title=""><i class="icon-arrow-left52 mr-2"></i></a>
                                                <a class="list-icons-item" data-action="fullscreen"></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <div class="col-lg-12 row">
                                                <div class="col-lg-8 p-2">
                                                    <div class="row">
                                                        <div class="col-lg-3">
                                                            <select name="atten_year" id="atten_year" class="select">
                                                                <?php for ($y = date('Y') - 1; $y < (date('Y') + 1); $y++) {
                                                                    echo '<option value="' . $y . '">' . $y . '</option>';
                                                                } ?>
                                                            </select>
                                                            <script>
                                                                document.thisform.atten_year.value = "<?php echo $atten_year ?>";
                                                            </script>
                                                        </div>

                                                        <div class="col-lg-3">
                                                            <select name="month_id" id="month_id" class="select">
                                                                <option value="">--Select Month--</option>
                                                                <?php
                                                                $dbconn = new dbhandler();
                                                                echo $dbconn->fnFillComboFromTable_Where("month_id", "month_name", "mst_atten_month", "month_id", "WHERE month_status = 1") ?>
                                                            </select>

                                                        </div>
                                                        <script>
                                                            document.thisform.month_id.value = "<?php echo $_REQUEST['month_id']; ?>";
                                                        </script>

                                                        <div class="col-lg-2">
                                                            <INPUT class="btn btn-success" type="submit" name="GET_ATTN" id="GET_ATTN" value="GET">
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>
                                        </div>



                                        <?php if (isset($_REQUEST['month_id']) && $_REQUEST['month_id'] != '') { ?>

                                            <div class="table-responsive" >
                                                <?php

                                                //echo "SELECT * FROM mst_employee WHERE bio_id > 0 AND  rec_del_status = 1 AND (emp_pf = 1  OR emp_pf = 0) ORDER BY bio_id ASC";

                                                $SQL = "SELECT * FROM mst_employee WHERE bio_id > 0 AND  rec_del_status = 1 AND (emp_pf = 1  OR emp_pf = 0) ORDER BY bio_id ASC";

                                                $result = $conn->query($SQL);

                                                if ($result->rowCount() > 0) {

                                                    $days= 0;

                                                    $Sno = 1;
                                                    $emp_id = 0;
                                                    $present = 0;
                                                    $total_paid_days = '';
                                                    $w_minutes = 0;
                                                    $ot_hrs = "0:00";
                                                    $paid_minutes = 0;
													$is_allow_fh=0;
													echo '<div class="col-md-12 text-right pb-2" >
                                          
                                               
														<a href="javascript:PrintPartsNew(new Array(\'stock_division\'),\'Day   Book\');" class="rpt_print">
															<button type="button" class="buttons-html5 btn btn-light" ><i class="icon-printer mr-1"></i> Print</button>
														</a>
                                                    </div>';

                                                    while ($obj = $result->fetch()) {

                                                        $days = 0;

                                                        $halfday = 0;

                                                        $work_days = 0;

                                                        $ot_mins = 0;

                                                        $emp_id = $obj->emp_id;

                                                        $working_days = (int)$dbconn->GetSingleReconrd("tbl_month_master","working_days","year = '" . date('Y', strtotime($atten_year . "-" . $_REQUEST['month_id'])) . "' AND month", date('m', strtotime($atten_year . "-" . $_REQUEST['month_id'])));

                                                        $SQL1 = "SELECT attn_id,emp_id,bio_id,work_date,work_time,check_in,check_out,check_in_dtm,check_out_dtm FROM tbl_attendance WHERE emp_id=" . $emp_id . " AND work_date LIKE '%" . date('Y-m', strtotime($atten_year . "-" . $_REQUEST['month_id'])) . "%' AND status =  1";
                                                        //echo '<br>';
                                                        $result1 = $conn->query($SQL1);

                                                        if ($result1->rowCount() > 0) {

                                                       

                                                            while ($obj1 = $result1->fetch(PDO::FETCH_OBJ)){

                                                                if ($obj1->check_in != '0:00:00' || $obj1->check_in != '') {

                                                                    $check_out_dtm = $obj1->check_out_dtm;

                                                                    if ($check_out_dtm == '0000-00-00 00:00:00') {

                                                                        $check_out_dtm = $obj1->check_in_dtm;
                                                                        
                                                                    }

                                                                    $chk_in = strtotime($obj1->check_in_dtm);

                                                                    $act_chk_out = strtotime($obj1->work_date.' 17:30:00');

                                                                    if ($obj->emp_type != 3) { //staff and labour

                                                                        $act_chk_out = strtotime($obj1->work_date . ' 17:30:00');

                                                                    } else {

                                                                        if ($obj->emp_type == 3) { //others

                                                                            $act_chk_out = strtotime($check_out_dtm);

                                                                            $total_working_days = $emp_type3;

                                                                        } else {

                                                                            $total_working_days = $total_working_days1;

                                                                        }
                                                                    }

                                                                    $chk_out = strtotime($check_out_dtm);
                                                                    if ($chk_out > $act_chk_out) {

                                                                        $chk_out = $act_chk_out;
                                                                    }


                                                                    $minutes = round(abs($chk_out - $chk_in) / 60, 2);


                                                                    $hours = floor($minutes / 60);

                                                                    $min = $minutes - ($hours * 60); /* 480 is 8 hours  & 510 - for 8.30 hours */

                                                                    if ($min > 50) {
                                                                        $hours = $hours + 1;
                                                                    }

                                                                    $day_name = date('D', strtotime($obj1->work_date));

                                                                    if ($day_name != 'Sun' || $day_name != 'SUN') {

                                                                        if ($minutes >= 460) {

                                                                            $days = $days + 1;

                                                                        } else if ($minutes >= 230) {

                                                                            $days = $days + 0.5;

                                                                        } else {

                                                                            $ot_mins += $minutes;
                                                                        }
                                                                    } else {

                                                                        $ot_mins += $minutes;

                                                                    }
                                                                }
                                                            }
														}

                                                            $emp_cl= $dbconn->GetSingleReconrd("tbl_emp_salary","emp_cl","em_id = ".$obj->em_id." AND em_current",1);
															$is_allow_fh = $dbconn->GetSingleReconrd("tbl_emp_salary","is_allow_fh","em_id = ".$obj->em_id." AND em_current",1);
																									
                                                            if($days < 10){

                                                                $emp_cl= 0;

                                                            }

                                                            if($is_allow_fh==2){

                                                                $fun_holi = 0;

                                                            }else{ $fun_holi=$function_holidays; }



                                                            if($days < $total_working_days){

                                                                if($obj->emp_type == 3){

                                                                    $days = $emp_type3;

                                                                }else{
																	//Modified  On 07-02-2025 as per the request of accounts mam
																	//$total_paid_days = ($days+$emp_cl+$function_holidays);
                                                                    $total_paid_days = ($days+$emp_cl+$fun_holi);

                                                                }
                                                                
                                                            }else if($days== 0){
                    
                                                                $total_paid_days = ($days+$emp_cl+$fun_holi);
                    
                                                            }else{
                    
                                                                $days += ($fun_holi);
                    
                                                                $total_paid_days = $days;
                    
                                                            }

                                                            $earned_salary=0;

                                                            $basic_salary=0;

                                                            $da_salary=0;

                                                            $basic_da=0;

                                                            $hra_salary=0;

                                                            $convay_salary=0;

                                                            $epf_amount=0;

                                                            $advance_amount=0;

                                                            $return_payment_link=0;

                                                            $balance_amount=0;

                                                            $debit_amount=0;

                                                            $net_amount=0;

                                                            $emp_ctc=0;

                                                            $emp_ctc = $dbconn->GetSingleReconrd("tbl_emp_salary","emp_ctc","em_id = ".$obj->em_id." AND em_current",1);

                                                            $salary_id = $dbconn->GetSingleReconrd("tbl_emp_salary","sal_id","em_id = ".$obj->em_id." AND em_current",1);

                                                            $salary_dets = $dbconn->GetSingleReconrd("mst_salary_setting","CONCAT(sal_basic,'~',sal_da,'~',sal_hra,'~',sal_convey,'~',sal_pf,'~',sal_period)","sal_id",$salary_id);

                                                            $salary_calc = explode('~',$salary_dets);

                                                            if(isset($days)) 
                                                            { 
                                                                $days = $days;
                                                            }
                                                            if($obj->emp_type != 3)
                                                            {
                                                                if($total_paid_days > $working_days)
                                                                {
                                                                    $total_paid_days = $working_days;
                                                                }
                                                            }
                                                            else
                                                            {
                                                                    $total_paid_days = $emp_type3;
                                                            }
                                                            $paid_days = $total_paid_days;
                                                            $paid_days_show = $total_paid_days;
                                                            $paid_days_save = $total_paid_days;
                                                            
                                                            if($salary_calc[5] == 2){
                                                            $earned_salary  = ($emp_ctc/$total_working_days)*$paid_days;
                                                            }else{
                                                            $earned_salary  = ((float)$emp_ctc * (float)$paid_days);
                                                            }
                
                                                            $basic_salary = (((float)$earned_salary * (float)$salary_calc[0])/100); //40
                                                            $da_salary = (((float)$earned_salary * (float)$salary_calc[1])/100); //25
                                                            $hra_salary = (((float)$earned_salary * (float)$salary_calc[2])/100); //25
                                                            $convay_salary = (((float)$earned_salary * (float)$salary_calc[3])/100);//10
                                                            $basic_da = ((float)$basic_salary + (float)$da_salary);
                                                            $epf_amount = (((float)$basic_da * (float)$salary_calc[4])/100);//12

                                                            $payment_amount  = 0;

                                                            $debit_payment_amount  = 0;

                                                             $payment_amount = $dbconn->GetSingleReconrd("tbl_emp_advance_return_payment","SUM(payment_amount)","atten_month = ".$_REQUEST['month_id']." AND atten_year = '".$_REQUEST['atten_year']."' AND emp_id",$obj->emp_id); 

                                                             $advance_amount = $dbconn->GetSingleReconrd("tbl_emp_advance","SUM(advance_amount)","is_current=1  AND advance_status != 3 AND emp_id",$obj->emp_id);
                                                            
                                                            $balance_amount = $dbconn->GetSingleReconrd("tbl_emp_advance","SUM(balance_amount)","is_current=1  AND advance_status != 3 AND emp_id",$obj->emp_id);


                                                          
                                                           

                                                            $debit_payment_amount = $dbconn->GetSingleReconrd("tbl_emp_debit_note_return_payment","SUM(payment_amount)","atten_month = ".$_REQUEST['month_id']." AND atten_year = '".$_REQUEST['atten_year']."' AND emp_id",$obj->emp_id);
                                                            
                                                            $balance_debit_amount = $dbconn->GetSingleReconrd("tbl_emp_debit_note","SUM(balance_amount)","is_current=1 AND emp_id",$obj->emp_id);



                                                            if($balance_debit_amount>0){

                                                                $debit_amount = $dbconn->GetSingleReconrd("tbl_emp_debit_note","SUM(debit_amount)","is_current=1 AND emp_id",$obj->emp_id);

                                                                }else{

                                                                    $debit_amount =0;
                                                                }

                                                                $net_amount =($earned_salary-$payment_amount-$debit_payment_amount);
                                                                $return_payment_link = '';
                                                                $debit_payment_link = '';
                                                                $return_payment = 0;
                                                                $denit_return_payment = 0;
                                                                $issued_salary = 0;

                                                                if($balance_amount > 0){

                                                                    if($payment_amount > 0){

                                                                        $return_payment_link = $payment_amount;

                                                                    }else{

                                                                    $return_payment_link = '<a href="emp_advance_return_payment.php?id='.$obj->emp_id.'&atten_month='.$_REQUEST['month_id'].'&atten_year='.$_REQUEST['atten_year'].'&net='.$net_amount.'&page=wpf" class="tip label label-success" title="Advance Deduction">+</a>';
                                                                    }
                                                                }else{

                                                                    $return_payment_link = $payment_amount;

                                                                }
                                                                /*debit*/
                                                                if($balance_debit_amount > 0){
                                                                    if($debit_payment_amount > 0){
                                                                        $debit_payment_link = $debit_payment_amount;
                                                                        $denit_return_payment = $debit_payment_amount;
                                                                    }else{
                                                                    $debit_payment_link = '<a href="emp_debit_return_payment.php?id='.$obj->emp_id.'&atten_month='.$_REQUEST['month_id'].'&atten_year='.$_REQUEST['atten_year'].'&net='.$net_amount.'&page=wpf" class="tip label label-success" title="Advance Deduction">+</a>';
                                                                    }
                                                                }else{
                                                                        $debit_payment_link = $debit_payment_amount;
                                                                }

                                                                $sign = '';

                                                                $issueBtn = '';

                                                                if($_SESSION['_user_id'] == 1){
                                                             
                                                                $issueBtn = '<a href="#" name="issue_salary"  class="  issue_salary badge btn  bg-warning" title="" data-original-title="Salary Issue">Issue</a>';
                                                                }

                                                                $checkAttendance = '<a href="checkattendance.php?month_id=' .$_REQUEST['month_id'] . '&atten_year=' . $_REQUEST['atten_year'] . '&emp_id=' . $obj->emp_id . '" class="badge bg-info" title="" data-original-title="Check Attendance">Check Attendance</a>';
                                                                
                                                                $issued_salary = $dbconn->GetSingleReconrd("tbl_emp_monthly_salary", "emp_sal_id", "salary_status = 1 AND salary_for = '" . $_REQUEST['atten_year'] . '-' . $_REQUEST['month_id'] . "' AND salary_type = 'WOPF' AND emp_id", $obj->emp_id);

                                                                if ($issued_salary > 0) {

                                                                    $sign = '<span class="badge bg-grey">Signed</span>';

                                                                    $issueBtn = '<span style="font-size: 16px;color: blue ;font-weight:bold; ">Issued</span>';

                                                                }

                                                             ?>
															<div id="stock_division">
                                                            <table class="table table-xs invoice_tbl">
                                                                <tbody>
                                                                    <tr>
                                                                        <td colspan="22" style="text-align:right;"><img src="project_img\usr_avatar\Benzear BIE logo.jpg" width="8%" hight="8%" alt=""></td>
                                                                    </tr>
                                                                    <tr>

                                                                        <td>S.No</td>
                                                                        <td>Name</td>
                                                                        <td>Designation</td>
                                                                        <td>EPF.No</td>
                                                                        <td colspan="18">
                                                                            <p align="right">
                                                                                <b>BENZEAR INDUSTRIAL ENTERPRISES</b><br />
                                                                                COIMBATORE - 35<br />
                                                                                EPF No:TN\CBE\72625<br />
                                                                                Salary Register for the month of <?php echo $monthName . ' - ' . $atten_year; ?>
                                                                            </p><br />
                                                                        </td>
                                                                    </tr>
																	<?php
 
																	$emp_det = $dbconn->GetSingleReconrd("mst_employee", "CONCAT(emp_code,'~',emp_name)", "emp_id", $obj->emp_id);
																	$emp = explode('~', $emp_det);

																	$emp_desig_det = $dbconn->GetSingleReconrd("mst_employee", "CONCAT(designation_id,'~',emp_epf_no,'~',emp_uan_no)", "emp_id", $obj->emp_id);
																	$emp_desig_dets = explode('~', $emp_desig_det);

																	$emp_desig = $dbconn->GetSingleReconrd("mst_designation", "designation_name", "designation_id", $emp_desig_dets[0]); 
																	?>

                                                                    <tr>
                                                                        <td><?php echo $Sno ?></td>
                                                                        <td><?php echo $emp[1] ?></td>
                                                                        <td><?php echo $emp_desig ?></td>
                                                                        <td><?php echo $emp_desig_dets[1] ?></td>
                                                                        <td colspan="18"><b>UAN: <?php echo $emp_desig_dets[2] ?></b></td>
                                                                    </tr>
                                                                    <tr style="font-weight: bold;">
                                                                        <td>TWD</td>
                                                                        <td>FH</td>
                                                                        <td>WD</td>
                                                                        <td>LWP</td>
                                                                        <td>TOTAL PDYS</td>
                                                                        <td>Actual Salary</td>
                                                                        <td>Earned Salary</td>
                                                                        <td>Basic</td>
                                                                        <td>DA</td>
                                                                        <td>Basic & DA</td>
                                                                        <td>HRA</td>
                                                                        <td>CONV</td>
                                                                        <td>EPF</td>
                                                                        <td>Total Advance</td>
                                                                        <td>Advan Dedic</td>
                                                                        <td>Balance Advan</td>
                                                                        <td>Total Debit</td>
                                                                        <td>Debit Dedic</td>
                                                                        <td>Balance Debit</td>
                                                                        <td>Net Pay</td>
                                                                        <td width="8%">Sign</td>
                                                                        <td class="text-center">Action</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <input type="hidden" class="employee_id" name="employee_id" value="<?php echo $obj->emp_id; ?>" />
                                                                        <input type="hidden" class="salary_for" name="salary_for" value="<?php echo $_REQUEST['atten_year'].'-'.$_REQUEST['month_id']; ?>" />
                                                                        <input type="hidden" class="total_working_days" name="total_working_days" value="<?php echo $total_working_days; ?>" />
                                                                        <input type="hidden" class="paid_days" name="paid_days" value="<?php echo $paid_days_save; ?>" />
                                                                        <input type="hidden" class="work_days" name="work_days" value="<?php echo $days; ?>" />
                                                                        <input type="hidden" class="fh_days" name="fh_days" value="<?php echo $fun_holi; ?>" />
                                                                        <input type="hidden" class="emp_lwp" name="emp_lwp" value="<?php echo $emp_cl; ?>" />
                                                                        <input type="hidden" class="emp_ctc" name="emp_ctc" value="<?php echo $emp_ctc; ?>" />
                                                                        <input type="hidden" class="earned_salary" name="earned_salary" value="<?php echo $earned_salary; ?>" />
                                                                        <input type="hidden" class="basic_salary" name="basic_salary" value="<?php echo $basic_salary; ?>" />
                                                                        <input type="hidden" class="da_salary" name="da_salary" value="<?php echo $da_salary; ?>" />
                                                                        <input type="hidden" class="basic_da" name="basic_da" value="<?php echo $basic_da; ?>" />
                                                                        <input type="hidden" class="hra_salary" name="hra_salary" value="<?php echo $hra_salary; ?>" />
                                                                        <input type="hidden" class="convay_salary" name="convay_salary" value="<?php echo $convay_salary; ?>" />
                                                                        <input type="hidden" class="epf_amount" name="epf_amount" value="0" />
                                                                        <input type="hidden" class="advance_amount" name="advance_amount" value="0" />
                                                                        <input type="hidden" class="deduction_amount" name="deduction_amount" value="<?php echo $return_payment; ?>" />
                                                                        <input type="hidden" class="balance_amount" name="balance_amount" value="<?php echo $balance_amount; ?>" />
                                                                        <input type="hidden" class="debit_amount" name="debit_amount" value="<?php echo $debit_amount; ?>" />
                                                                        <input type="hidden" class="debit_deduction_payment" name="debit_deduction_payment" value="<?php echo $denit_return_payment; ?>" />
                                                                        <input type="hidden" class="balance_debit_amount" name="balance_debit_amount" value="<?php echo $balance_debit_amount; ?>" />
                                                                        <input type="hidden" class="net_amount" name="net_amount" value="<?php echo $net_amount; ?>" />

                                                                        <td><?php echo $total_working_days ?></td>
                                                                        <td><?php echo $fun_holi ?></td> 
                                                                        <td><?php echo $days ?></td>
                                                                        <td><?php echo $emp_cl ?></td>
                                                                        <td><?php echo $total_paid_days ?></td>
                                                                        <td><?php echo $emp_ctc ?></td>
                                                                        <td><?php echo number_format($earned_salary,2)?></td>
                                                                        <td><?php echo number_format($basic_salary,2)?></td>
                                                                        <td><?php echo number_format($da_salary,2)?></td>
                                                                        <td><?php echo number_format($basic_da,2)?> </td>
                                                                        <td><?php echo number_format($hra_salary,2)?></td>
                                                                        <td><?php echo number_format($convay_salary,2)?></td>
                                                                        <td>Nill</td>
                                                                        <td><?php echo $advance_amount ?></td>
                                                                        <td><?php echo $return_payment_link ?></td>
                                                                        <td><?php echo $balance_amount ?></td>
                                                                        <td><?php echo $debit_amount ?></td>
                                                                        <td><?php echo $debit_payment_link ?></td>
                                                                        <td><?php echo $balance_debit_amount ?></td>
                                                                        <td><?php echo number_format($net_amount,2) ?></td>
                                                                        <td width="8%"><?php echo $sign ?></td>
                                                                        <td class="text-center"><?php echo $issueBtn ?><?php echo $checkAttendance ?></td>
                                                                    </tr>

                                                                </tbody>
                                                            </table>
															
                                                            <hr>
                                                           
                                                        <?php

                                                        $Sno++;
														
                                                    }
													
                                                }?>
                                               
                                            </div>
                                        <?php  } ?>

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

<?php
    if (isset($_SESSION['_msg']) && $_SESSION['_msg'] != "") {
        echo "$.jGrowl('" . $_SESSION['_msg'] . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-success', position: 'bottom-right', life:'2000', header: 'Success!' });";
        $_SESSION['_msg'] = "";
    }
    if (isset($_SESSION['_msg_err']) && $_SESSION['_msg_err'] != "") {
        echo "$.jGrowl('" . $_SESSION['_msg_err'] . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-danger', position: 'top-right', shutdown:'3000', header: 'Error!' });";
        $_SESSION['_msg_err'] = "";
    }
    ?>

    function fnValidate() {
        if (notSelected(document.thisform.month_id, "Month..!")) {
            return false;
        }
    }

    $('.issue_salary').click(function(){
		var emp_id = $(this).closest('tr').find('.employee_id').val();
		var salary_for = $(this).closest('tr').find('.salary_for').val();
		var total_working_days = $(this).closest('tr').find('.total_working_days').val();
		var paid_days = $(this).closest('tr').find('.paid_days').val();
		var work_days = $(this).closest('tr').find('.work_days').val();
		var fh_days = $(this).closest('tr').find('.fh_days').val();
		var emp_lwp = $(this).closest('tr').find('.emp_lwp').val();
		var emp_ctc = $(this).closest('tr').find('.emp_ctc').val();
		var earned_salary = $(this).closest('tr').find('.earned_salary').val();
		var basic_salary = $(this).closest('tr').find('.basic_salary').val();
		var da_salary = $(this).closest('tr').find('.da_salary').val();
		var basic_da = $(this).closest('tr').find('.basic_da').val();
		var hra_salary = $(this).closest('tr').find('.hra_salary').val();
		var convay_salary = $(this).closest('tr').find('.convay_salary').val();
		var epf_amount = $(this).closest('tr').find('.epf_amount').val();		
		var advance_amount = $(this).closest('tr').find('.advance_amount').val();
		var deduction_amount = $(this).closest('tr').find('.deduction_amount').val();
		var balance_amount = $(this).closest('tr').find('.balance_amount').val();
		
		var debit_amount = $(this).closest('tr').find('.debit_amount').val();
		var debit_deduction_payment = $(this).closest('tr').find('.debit_deduction_payment').val();
		var balance_debit_amount = $(this).closest('tr').find('.balance_debit_amount').val();
		
		var net_amount = $(this).closest('tr').find('.net_amount').val();
		var salary_type = 'WOPF';
	 	$.ajax({
	 		type: "POST",
	 		url: "issue_salary.php",
	 		data: {"emp_id":emp_id,"salary_for":salary_for,"total_working_days":total_working_days,
			"paid_days":paid_days,
			"work_days":work_days,
			"fh_days":fh_days,
			"emp_lwp":emp_lwp,
			"emp_ctc":emp_ctc,
			"earned_salary":earned_salary,
			"basic_salary":basic_salary,
			"da_salary":da_salary,
			"basic_da":basic_da,
			"hra_salary":hra_salary,
			"convay_salary":convay_salary,
			"epf_amount":epf_amount,
			"advance_amount":advance_amount,
			"deduction_amount":deduction_amount,
			"balance_amount":balance_amount,
			"debit_amount":debit_amount,
			"debit_deduction_payment":debit_deduction_payment,
			"balance_debit_amount":balance_debit_amount,
			"net_amount":net_amount,
			"salary_type":salary_type
			}
	 		}).done(function( msg ) {
				location.reload();				
				return false;
	 	});
	});

</script>
<!-- Footer -->