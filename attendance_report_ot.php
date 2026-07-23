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
    header("location:attendance_report_ot.php?month_id=" . $_REQUEST['month_id'] . "&atten_year=" . $_REQUEST['atten_year'] . "&emp_id=" . $_REQUEST['emp_id']);
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
        <?php echo PAGE_TITLE; ?>-OT Salary for Month
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
                            <span class="breadcrumb-item active">Attendance</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>

            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">

                        <form name="thisform" class="form-horizontal" method='POST' action="attendance_report_ot.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                            <?php
                            $dateObj   = DateTime::createFromFormat('!m', $month_id);
                            $monthName = $dateObj->format('F');
                            ?>
                            <fieldset>
                                <div class="card">
                                    <div class="card-header bg-pgheader text-white header-elements-inline">
                                            <h6 class="card-title">Employee's OT Salary Report</h6>
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
                                            <div class="table-responsive">
                                                <?php

                                                $SQL = "SELECT * FROM mst_employee WHERE bio_id > 0 AND rec_del_status = 1 AND (emp_type = 2 OR emp_id IN (SELECT DISTINCT emp_id FROM tbl_emp_salary WHERE emp_ot_sal > 0 AND em_current = 1 )) AND emp_id IN (SELECT emp_id FROM tbl_attendance WHERE  work_date LIKE '%" . date('Y-m', strtotime($atten_year . "-" . $_REQUEST['month_id'])) . "%' AND status =  1)  ORDER BY bio_id ASC ";

                                                $result = $conn->query($SQL);

                                                if ($result->rowCount() > 0) {

                                                    $Sno = 1;
                                                    $emp_id = 0;
                                                    $present = 0;
                                                    $total_earned_sal = 0;
                                                    $total_net_sal = 0;
                                                    $total_adv = 0;
                                                    $total_adv_dedic = 0;
                                                    $total_adv_bal = 0;
                                                    $final_hours = 0;
                                                    $ot_hrs = 0;
                                                    $late_mins = 0;
                                                    $total_late_mins = 0;

                                                    echo '<div class="col-md-12 text-right pb-2" >
                                          
                                               
                                                <a href="javascript:PrintPartsNew(new Array(\'stock_division\'),\'Day   Book\');" class="rpt_print">
                                                    <button type="button" class="buttons-html5 btn btn-light" ><i class="icon-printer mr-1"></i> Print</button>
                                                </a>
                                            </div>';


                                                ?>
                                                    <div id="stock_division">
                                                        <table class="table table-xs invoice_tbl" >

                                                            <tbody>
                                                                <tr>
                                                                    <td colspan="10" style="text-align:right;"><img src="project_img\usr_avatar\Benzear BIE logo.jpg" width="5%" hight="5%" alt=""></td>
                                                                </tr>
                                                                <tr class="text-center">

                                                                    <th colspan="10">SALARY REGISTER for OVER TIME <?php echo $monthName . ' - ' . $atten_year; ?></th>

                                                                </tr>
                                                                <tr class="rpt_heading">
                                                                    <th width="50px">#</th>
                                                                    <th>Emp. Name</th>
                                                                    <th>Total Over Time</th>
                                                                    <th>Amount per Hour</th>
                                                                    <th>OT Amount</th>
                                                                    <th>Tiffen Allowed Days</th>
                                                                    <th>Tiffen Amount</th>
                                                                    <th>Total</th>
                                                                    <th>Signature</th>
                                                                    <th class="actionbtns">Action</th>
                                                                </tr>
                                                                <?php

                                                                while ($obj = $result->fetch()) {
                                                                    $emp_det = $dbconn->GetSingleReconrd("mst_employee", "CONCAT(emp_code,'~',emp_name)", "emp_id", $obj->emp_id);

                                                                    $emp = explode('~', $emp_det);

                                                                    $emp_id = $obj->emp_id;

                                                                    $working_days = $dbconn->GetSingleReconrd("tbl_month_master", "working_days", "year = '" . date('Y', strtotime($atten_year . "-" . $_REQUEST['month_id'])) . "' AND month", date('m', strtotime($atten_year . "-" . $_REQUEST['month_id'])));

                                                                    $ot_hrs = 0;
                                                                    $late_mins = 0;
                                                                    $total_late_mins = 0;
                                                                    $final_hours = 0;
                                                                    $ot_chk_out = 0;
                                                                    $finalOTMins = 0;
                                                                    $OThours = 0;
                                                                    $OTmins = 0;
                                                                    $finalhours = 0;
                                                                    $finalmins = 0;
                                                                    // $total_ot_amount = 0;
                                                                    // $total_ot_hours = 0;

                                                                    $SQL1 = "SELECT attn_id,emp_id,bio_id,late_in,work_date,work_time,check_in,check_out,check_in_dtm,check_out_dtm,is_ot FROM tbl_attendance WHERE emp_id='" . $emp_id . "' AND work_date LIKE '%" . date('Y-m', strtotime($atten_year . "-" . $_REQUEST['month_id'])) . "%' AND status =  1 ";

                                                                    $result1 = $conn->query($SQL1);

                                                                    if ($result1->rowCount() > 0) {
                                                                        while ($obj1 = $result1->fetch(PDO::FETCH_OBJ)) {

                                                                            $ot_chk_out = 0;

                                                                            if ($obj1->check_in != '0:00:00' || $obj1->check_in != '') {

                                                                                $check_out_dtm = $obj1->check_out_dtm;

                                                                                if ($check_out_dtm == '0000-00-00 00:00:00') {

                                                                                    $check_out_dtm = $obj1->check_in_dtm;
                                                                                }

                                                                                $chk_in = strtotime($obj1->check_in_dtm);

                                                                                $act_chk_out = strtotime($obj1->work_date . ' 17:30:00');

                                                                                $chk_out = strtotime($check_out_dtm);

                                                                                $day_name = date('D', strtotime($obj1->work_date));

                                                                                if ($day_name != 'Sun' || $day_name != 'SUN') {

                                                                                    if ($obj1->is_ot == 0) {

                                                                                        if ($chk_out > $act_chk_out) {

                                                                                            $OTmins += round(abs($chk_out - $act_chk_out) / 60, 2);
                                                                                        }
                                                                                    } else {

                                                                                        if ($obj1->work_time <= 230) {

                                                                                            $ot_chk_out = $obj1->work_time;

                                                                                            $OTmins += $ot_chk_out;
                                                                                        } else {

                                                                                            $chk_in = strtotime($obj1->check_in_dtm);

                                                                                            $act_chk_out = strtotime($obj1->work_date . ' 17:30:00');

                                                                                            $chk_out = strtotime($check_out_dtm);

                                                                                            if ($chk_out > $act_chk_out) {

                                                                                                $OTmins += round(abs($chk_out - $act_chk_out) / 60, 2);
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                } else {

                                                                                    $OTmins += $OTmins;
                                                                                }

                                                                                $late_mins = 0;

                                                                                //    echo  $late_mins =  (0.06666666666);
                                                                                //    echo $intdiv = intval($obj1->late_in/60);

                                                                                $late_mins = intval($obj1->late_in / 60) . '.' . leadingZeros(($obj1->late_in % 60), 2); 

                                                                                $total_late_mins += $late_mins;
                                                                            }
                                                                        }
                                                                    }

                                                                    $finalOTMins = $OTmins - $total_late_mins;

                                                                    $amount_per_hour = $dbconn->GetSingleReconrd("tbl_emp_salary", "emp_ot_sal", "em_id = " . $obj->em_id . " AND em_current", 1);

                                                                    $amount_per_minutes = ($amount_per_hour / 60);

                                                                    $ot_amount = ($finalOTMins * $amount_per_minutes);

                                                                    $OThours = floor($finalOTMins / 60);

                                                                    $OTmins = $finalOTMins - ($OThours * 60);

                                                                    (float)$total_ot_amount += (float)$ot_amount;

                                                                    (float)$total_ot_hours += (float)$finalOTMins;

                                                                    $sign = '';
                                                                    if ($_SESSION['_user_id'] == 1) {
                                                                        $issueBtn = '<a href="#" name="issue_salary"  class="  issue_salary badge btn  bg-warning" title="" data-original-title="Salary Issue">Issue</a>';
                                                                    }

                                                                    $issued_salary = $dbconn->GetSingleReconrd("tbl_emp_monthly_salary", "emp_sal_id", "salary_status = 1 AND salary_for = '" . $_REQUEST['atten_year'] . '-' . $_REQUEST['month_id'] . "' AND salary_type = 'OT' AND  emp_id", $obj->emp_id);

                                                                    if ($issued_salary > 0) {

                                                                        $sign = '<span class="badge bg-grey">Signed</span>';

                                                                        $issueBtn = '<span style="font-size: 16px;color: blue ;font-weight:bold; ">Issued</span>';
                                                                    }


                                                                ?>
                                                                    <tr class="rpt_content">
                                                                        <td><?php echo $Sno ?></td>
                                                                        <td><?php echo $emp[1] ?></td>
                                                                        <td align="right"><?php echo $OThours ?>:<?php echo number_format($OTmins, 2) ?></td>
                                                                        <td align="right"><?php echo number_format($amount_per_hour, 2) ?></td>
                                                                        <td align="right"><?php echo number_format($ot_amount, 2) ?></td>
                                                                        <td align="right">0</td>
                                                                        <td align="right">0</td>
                                                                        <td align="right">
                                                                            <h6><?php echo number_format($ot_amount, 2) ?></h6>
                                                                        </td>
                                                                        <td style="padding-left:5px; overflow:hidden; height: 50px;"><?php echo $sign ?></td>
                                                                        <td class="actionbtns"><?php echo $issueBtn ?></td>
                                                                        <input type="hidden" class="employee_id" name="employee_id" value="<?php echo $emp_id; ?>" />
                                                                        <input type="hidden" class="salary_for" name="salary_for" value="<?php echo $_REQUEST['atten_year'] . '-' . $_REQUEST['month_id']; ?>" />
                                                                        <input type="hidden" class="total_ot_hours" name="total_ot_hours" value="<?php echo $finalOTMins; ?>" />
                                                                        <input type="hidden" class="amount_per_hour" name="amount_per_hour" value="<?php echo $amount_per_hour; ?>" />
                                                                        <input type="hidden" class="ot_amount" name="ot_amount" value="<?php echo $ot_amount; ?>" />
                                                                        <input type="hidden" class="tiffen_allow_days" name="tiffen_allow_days" value="0" />
                                                                        <input type="hidden" class="tiffen_amount" name="tiffen_amount" value="0" />
                                                                        <input type="hidden" class="total_ot_amount" name="total_ot_amount" value="<?php echo $ot_amount; ?>" />
                                                                    </tr>



                                                                <?php
                                                                    $Sno++;
                                                                }

                                                                $finalhours = floor($total_ot_hours / 60);
                                                                $finalmins = $total_ot_hours - ($finalhours * 60);

                                                                ?>

                                                                <tr>
                                                                    <td></td>
                                                                    <td><b>TOTAL OVER TIME</b></td>
                                                                    <td align="right"><b>Time : <?php echo $finalhours . ':' . $finalmins ?></b></td>
                                                                    <td></td>
                                                                    <td align="right"><b>Rs.<?php echo number_format($total_ot_amount, 2) ?></b></td>
                                                                    <td align="right"><b>0</b></td>
                                                                    <td align="right"><b>Rs. 0.00</b></td>
                                                                    <td align="right"><b>Rs.<?php echo number_format($total_ot_amount, 2) ?></b></td>
                                                                    <td style="padding-left:5px; overflow:hidden; height: 50px;"></td>
                                                                    <td class="actionbtns"></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                            </div>
                                    <?php
                                                    $obj = null;
                                                }
                                            }

                                    ?>
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

    $('.issue_salary').click(function() {

        var emp_id = $(this).closest('tr').find('.employee_id').val();
        var salary_for = $(this).closest('tr').find('.salary_for').val();
        var total_ot_hours = $(this).closest('tr').find('.total_ot_hours').val();
        var amount_per_hour = $(this).closest('tr').find('.amount_per_hour').val();
        var ot_amount = $(this).closest('tr').find('.ot_amount').val();
        var tiffen_allow_days = $(this).closest('tr').find('.tiffen_allow_days').val();
        var tiffen_amount = $(this).closest('tr').find('.tiffen_amount').val();
        var total_ot_amount = $(this).closest('tr').find('.total_ot_amount').val();
        var salary_type = 'OT';
        // alert(emp_id);
        $.ajax({
            type: "POST",
            url: "issue_salary.php",
            data: {
                "emp_id": emp_id,
                "salary_for": salary_for,
                "total_ot_hours": total_ot_hours,
                "amount_per_hour": amount_per_hour,
                "ot_amount": ot_amount,
                "tiffen_allow_days": tiffen_allow_days,
                "tiffen_amount": tiffen_amount,
                "total_ot_amount": total_ot_amount,
                "salary_type": salary_type
            }
        }).done(function(msg) {
            // alert(msg);
            location.reload();
            return false;
        });
    });
</script>
<!-- Footer -->