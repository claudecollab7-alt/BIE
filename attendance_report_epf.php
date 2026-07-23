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

if (isset($_POST['GET_ATTN'])) {
    header("location:attendance_report_epf.php?month_id=" . $_REQUEST['month_id'] . "&atten_year=" . $_REQUEST['atten_year'] . "&emp_id=" . $_REQUEST['emp_id']);
    die();
}
if(isset($_POST['GET_TXT']))
{
	header("location:attendance_report_epf_txt.php?month_id=".$_REQUEST['month_id']."&atten_year=".$_REQUEST['atten_year']);
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
        <?php echo PAGE_TITLE; ?> - Employee EPF Report
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

                        <form name="thisform" class="form-horizontal" method='POST' action="attendance_report_epf.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                            <?php
                            $dateObj   = DateTime::createFromFormat('!m', $month_id);
                            $monthName = $dateObj->format('F');
                            ?>
                            <fieldset>
                                <div class="card">
                                    <div class="card-header bg-pgheader text-white header-elements-inline">
                                        <h6 class="card-title">Employee's EPF Report</h6>
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
                                                            <INPUT class="btn btn-success" type="submit" name="GET_ATTN" id="GET_ATTN" value="GENERATE REPORT">
                                                        </div>

                                                    </div>

                                                </div>

                                            </div>
                                        </div>




                                        <?php if (isset($_REQUEST['month_id']) && $_REQUEST['month_id'] != '') { ?>
                                            <div class="table-responsive">
                                                <?php

                                                $srch = $_REQUEST['atten_year'] . "-" . $_REQUEST['month_id'];

                                                $SQL = "SELECT * FROM tbl_emp_monthly_salary WHERE salary_for= '" . $srch . "' AND salary_type = 'WPF' AND salary_status = 1 ORDER BY salary_for ASC";

                                                $result = $conn->query($SQL);

                                                if ($result->rowCount() > 0) {

                                                    echo '
                                                        <div class="col-md-12 text-right pb-2" >
                                                            <a href="javascript:PrintPartsNew(new Array(\'stock_division\'),\'Day   Book\');" class="rpt_print">
                                                                <button type="button" class="buttons-html5 btn btn-light" ><i class="icon-printer mr-1"></i> Print</button>
                                                            </a>
                                                        </div>';
                                                ?>
                                                    <!-- <div class="card"> -->
                                                    <!-- <div class="card-body"> -->
                                                    <div class="table-responsive">
                                                        <!-- <div class="card">
                                                    <div class="card-body"> -->
                                                        <div id="stock_division">

                                                            <table class="table table-xs invoice_tbl ">

                                                                <tbody>

                                                                    <H6>
                                                                        <center> EPF Report for <?php echo $monthName . ' - ' . $atten_year; ?></center>
                                                                    </H6>

                                                                    <tr class="table table-xs invoice_tbl rpt_heading">
                                                                        <th width="50px">Sl. No.</th>
                                                                        <th>EPF No.</th>
                                                                        <th>UAN No.</th>
                                                                        <th>Name</th>
                                                                        <th>T days</th>
                                                                        <th>W days</th>
                                                                        <th>Salary</th>
                                                                        <th>Act. Salary</th>
                                                                        <th>Basic 40%</th>
                                                                        <th>DA 25%</th>
                                                                        <th>GROSS/EPF/EPS/EDLI Wages</th>
                                                                        <th>EPF Contrib.</th>
                                                                        <th>EPS Contrib.</th>
                                                                        <th>EDLI Contrib.</th>
                                                                        <th>NCP days</th>
                                                                    </tr>

                                                                    <?php $Sno = 1;
                                                                    while ($obj = $result->fetch()) {

                                                                        $emp_query = "SELECT emp_name,emp_epf_no,emp_uan_no FROM mst_employee WHERE emp_id = " . $obj->emp_id . " ";

                                                                        $res = $conn->query($emp_query);

                                                                        if ($res->rowCount() > 0) {

                                                                            $emp = $res->fetch();
                                                                        }

                                                                        $eps = ($obj->basic_da / 100) * 8.33;

                                                                        $edlf = $obj->epf_amount - $eps;

                                                                        $ncp_days = $obj->total_working_days - $obj->work_days;

                                                                    ?>

                                                                        <tr>
                                                                            <td><?php echo $Sno ?></td>
                                                                            <td><?php echo $emp->emp_epf_no ?></td>
                                                                            <td><?php echo $emp->emp_uan_no ?></td>
                                                                            <td><?php echo $emp->emp_name ?></td>
                                                                            <td><?php echo $obj->total_working_days ?></td>
                                                                            <td><?php echo $obj->work_days ?></td>
                                                                            <td><?php echo $obj->emp_ctc ?></td>
                                                                            <td><?php echo $obj->earned_salary ?></td>
                                                                            <td><?php echo $obj->basic_salary ?></td>
                                                                            <td><?php echo $obj->da_salary ?></td>
                                                                            <td align="right"><?php echo number_format(round($obj->basic_da), 2) ?></td>
                                                                            <td align="right"><?php echo number_format(round($obj->epf_amount), 2) ?></td>
                                                                            <td align="right"><?php echo number_format(round($eps), 2) ?></td>
                                                                            <td align="right"><?php echo number_format(round($edlf), 2) ?></td>
                                                                            <td align="right"><?php echo number_format(round($ncp_days), 2) ?></td>
                                                                        </tr>
                                                                    <?php
                                                                        $Sno++;

                                                                        $tot_working += $obj->total_working_days;
                                                                        $tot_work_days += $obj->work_days;
                                                                        $tot_act_salary += $obj->earned_salary;
                                                                        $tot_basic += $obj->basic_salary;
                                                                        $tot_da += $obj->da_salary;
                                                                        $tot_wages += $obj->basic_da;
                                                                        $tot_epf += $obj->epf_amount;
                                                                        $tot_eps += $eps;
                                                                        $tot_edlf += $edlf;
                                                                        $tot_ncp_days += $ncp_days;


                                                                        $total_epfa = round($tot_epf) + round($tot_edlf);
                                                                        $total_eps = $tot_act_salary - $tot_epf;
                                                                        $total_edl = $tot_act_salary - $tot_wages;

                                                                        $amt_charges = round($tot_wages / 100 * 1.1);
                                                                        $edlis_charges = round($tot_wages / 100 * 0.5);
                                                                        $amt_charges_ac_22 = round($tot_wages / 100 * 0.01);

                                                                        $net_amt = $total_epfa + $tot_eps + $amt_charges + $edlis_charges + $amt_charges_ac_22;

                                                                        $obj = null;
                                                                    } ?>

                                                                    <tr valign="top">
                                                                        <td colspan="4" align="left"><strong></strong></td>
                                                                        <td><strong><?php echo number_format($tot_working, 2) ?></strong></td>
                                                                        <td><strong><?php echo number_format($tot_work_days, 2) ?></strong></td>
                                                                        <td></td>
                                                                        <td align="right"><strong><?php echo number_format(round($tot_act_salary), 2) ?></strong></td>
                                                                        <td align="right"><strong><?php echo number_format(round($tot_basic), 2) ?></strong></td>
                                                                        <td align="right"><strong><?php echo number_format(round($tot_da), 2) ?></strong></td>
                                                                        <td align="right"><strong><?php echo number_format(round($tot_wages), 2) ?></strong></td>
                                                                        <td align="right"><strong><?php echo number_format(round($tot_epf), 2) ?></strong></td>
                                                                        <td align="right"><strong><?php echo number_format(round($tot_eps), 2) ?></strong></td>
                                                                        <td align="right"><strong><?php echo number_format(round($tot_edlf), 2) ?></strong></td>
                                                                        <td align="right"><strong><?php echo number_format(round($tot_ncp_days), 2) ?></strong></td>
                                                                    </tr>
                                                                    <tr valign="top">
                                                                        <td colspan="10" align="right"><strong>E.P.F.A/c No.1</strong></td>
                                                                        <td><strong></strong></td>
                                                                        <td align="right"><strong><?php echo number_format(round($total_epfa), 2) ?></strong></td>
                                                                        <td align="right"><strong><?php echo number_format(round($total_eps), 2) ?></strong></td>
                                                                        <td align="right"><strong><?php echo number_format(round($total_edl), 2) ?></strong></td>
                                                                        <td><strong></strong></td>
                                                                    </tr>
                                                                    <tr valign="top">
                                                                        <td colspan="10" align="right"><strong> E.P.S.A/c No.10 </strong></td>
                                                                        <td><strong></strong></td>
                                                                        <td align="right"><strong><?php echo number_format(round($tot_eps), 2) ?></strong></td>
                                                                        <td><strong></strong></td>
                                                                        <td><strong></strong></td>
                                                                        <td><strong></strong></td>
                                                                    </tr>
                                                                    <tr valign="top">
                                                                        <td colspan="10" align="right"><strong>Admn. Charges A/c No. 2</strong></td>
                                                                        <td><strong></strong></td>
                                                                        <td align="right"><strong><?php echo number_format(round($amt_charges), 2) ?></strong></td>
                                                                        <td><strong></strong></td>
                                                                        <td><strong></strong></td>
                                                                        <td><strong></strong></td>
                                                                    </tr>
                                                                    <tr valign="top">
                                                                        <td colspan="10" align="right"><strong>EDLIS A/c No. 21</strong></td>
                                                                        <td><strong></strong></td>
                                                                        <td align="right"><strong><?php echo number_format(round($edlis_charges), 2) ?></strong></td>
                                                                        <td><strong></strong></td>
                                                                        <td><strong></strong></td>
                                                                        <td><strong></strong></td>
                                                                    </tr>
                                                                    <tr valign="top">
                                                                        <td colspan="10" align="right"><strong>Admn. Charges A/c No. 22</strong></td>
                                                                        <td><strong></strong></td>
                                                                        <td align="right"><strong><?php echo number_format(round($amt_charges_ac_22), 2) ?></strong></td>
                                                                        <td><strong></strong></td>
                                                                        <td><strong></strong></td>
                                                                        <td><strong></strong></td>
                                                                    </tr>
                                                                    <tr valign="top">
                                                                        <td colspan="10" align="right"><strong>TOTAL AMOUNT</strong></td>
                                                                        <td><strong></strong></td>
                                                                        <td align="right"><strong><?php echo number_format(round($net_amt), 2) ?></strong></td>
                                                                        <td><strong></strong></td>
                                                                        <td><strong></strong></td>
                                                                        <td><strong></strong></td>
                                                                    </tr>
                                                                    

                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <tr valign="top" class="card-footer">
                                                                        <td colspan="20" align="right">
                                                                            <strong>
                                                                                <div class=" text-center p-2">
                                                                                    <INPUT class="btn btn-custom mr-2" type="submit" name="GET_TXT" value="GENERATE TEXT FILE">
                                                                                    <!-- <INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
                                                                                    <input type="hidden" name="txtHid" value="0"> -->
                                                                                </div>
                                                                            </strong>
                                                                        </td>
                                                                    </tr>
                                                    </div>
                                            </div>


                                    </div>


                            <?php

                                                }
                                            }
                            ?>
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