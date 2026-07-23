<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");
require_once("inc/common/css-js.php");



isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

//ini_set('display_errors', '1');
//ini_set('display_startup_errors', '1');
//error_reporting(E_ALL);

if ($_REQUEST['atten_year'] == '') {
    $atten_year = date('Y');
} else {
    $atten_year = $_REQUEST['atten_year'];
}
if ($_REQUEST['month_id'] == '') {
    $month_id = date('m');
} else {
    $month_id = $_REQUEST['month_id'];
}
$emp_id = $_REQUEST['emp_id'];

$dateObj   = DateTime::createFromFormat('!m', $month_id);
$monthName = $dateObj->format('F');
$emp_det = $dbconn->GetSingleReconrd("mst_employee", "CONCAT(emp_code,'~',emp_name)", "emp_id", $emp_id);
$emp = explode('~', $emp_det);

if (isset($_POST['UPDATE'])) {

    $work_date = $dbconn->GetSingleReconrd("tbl_attendance", "work_date", "attn_id", $_REQUEST['attn_id']);

    $shift_id = $dbconn->GetSingleReconrd("tbl_attendance", "shift_id", "attn_id", $_REQUEST['attn_id']);


    if ($_REQUEST['check_in_time'] != '00:00:00' && $_REQUEST['check_out_time'] != '00:00:00') {
        $shift_det = $dbconn->GetSingleReconrd("mst_shifts", "CONCAT(check_in,'~',check_out,'~',check_in_start,'~',check_in_end,'~',check_out_start,'~',check_out_end)", "shift_id", $shift_id);

        $shift = explode('~', $shift_det);

        $check_in = date('H:i:s', strtotime($_REQUEST['check_in_time']));

        $check_out = date('H:i:s', strtotime($_REQUEST['check_out_time']));

        if ($check_in > $shift[0]) {
            $late_in = abs(strtotime($check_in) - strtotime($shift[0])) / 60;
        }
        if ($check_out > $shift[1]) {
            $late_out = abs(strtotime($check_out) - strtotime($shift[1])) / 60;
        }
        if ($check_in < $shift[0]) {
            $early_in = abs(strtotime($shift[0]) - strtotime($check_in)) / 60;
        }
        if ($check_out < $shift[1]) {
            $early_out = abs(strtotime($shift[1]) - strtotime($check_out)) / 60;
        }
        $workhours = abs(strtotime($work_date . ' ' . $_REQUEST['check_out_time']) - strtotime($work_date . ' ' . $_REQUEST['check_in_time'])) / 60;

        if (isset($_REQUEST['is_ot'])) {
            $is_ot = 1;
        } else {
            $is_ot = 0;
        }

        $update_attn = $conn->prepare("UPDATE tbl_attendance SET check_in = :check_in, check_in_dtm = :check_in_dtm, check_out = :check_out,check_out_dtm=:check_out_dtm,late_in=:late_in,late_out=:late_out,early_in=:early_in,early_out=:early_out,work_time=:work_time,is_ot=:is_ot,manual_update_by=:manual_update_by,manual_update_dtm=:manual_update_dtm WHERE attn_id = :attn_id");

        $check_data = array(
            ':attn_id' => $_REQUEST['attn_id'],
            ':check_in' => date('H:i:s', strtotime($_REQUEST['check_in_time'])),
            ':check_in_dtm' => date('Y-m-d H:i:s', strtotime($work_date . ' ' . $_REQUEST['check_in_time'])),
            ':check_out' => date('H:i:s', strtotime($_REQUEST['check_out_time'])),
            ':check_out_dtm' => date('Y-m-d H:i:s', strtotime($work_date . ' ' . $_REQUEST['check_out_time'])),
            ':late_in' => $late_in,
            ':late_out' => $late_out,
            ':early_in' => $early_in,
            ':early_out' => $early_out,
            ':work_time' => $workhours,
            ':is_ot' => $is_ot,
            ':manual_update_by' => $_SESSION['_user_id'],
            ':manual_update_dtm' => date('Y-m-d H:i:s')
        );

        $update_attn->execute($check_data);
    }

    $_SESSION['_msg'] = "Attendance Updated";

    header("location:checkattendance.php?month_id=" . $_REQUEST['month_id'] . "&atten_year=" . $_REQUEST['atten_year'] . "&emp_id=" . $_REQUEST['emp_id'] . " ");
    die();
}

if (isset($_POST['ADD'])) {

    $work_date = $_REQUEST['attn_date'];

    $is_exist = $dbconn->GetSingleReconrd("tbl_attendance", "attn_id", "work_date = '" . $work_date . "' AND emp_id ", $_REQUEST['emp_id']);

    if ($is_exist > 0) {

        $_SESSION['_msg_err'] = 'Attendance already exists..!';

        header("location:checkattendance.php?month_id=" . $_REQUEST['month_id'] . "&atten_year=" . $_REQUEST['atten_year'] . "&emp_id=" . $_REQUEST['emp_id'] . " ");

        die();
    }

    $shift_id = 1;

    $bio_id = $dbconn->GetSingleReconrd("mst_employee", "bio_id", "emp_id", $_REQUEST['emp_id']);

    if ($_REQUEST['check_in_time'] != '00:00:00' && $_REQUEST['check_out_time'] != '00:00:00') {

        $late_in = 0;
        $late_out = 0;
        $early_in = 0;
        $early_out = 0;
        $shift_det = $dbconn->GetSingleReconrd("mst_shifts", "CONCAT(check_in,'~',check_out,'~',check_in_start,'~',check_in_end,'~',check_out_start,'~',check_out_end)", "shift_id", $shift_id);
        $shift = explode('~', $shift_det);
        $check_in = date('H:i:s', strtotime($_REQUEST['check_in_time']));
        $check_out = date('H:i:s', strtotime($_REQUEST['check_out_time']));
        if ($check_in > $shift[0]) {
            $late_in = abs(strtotime($check_in) - strtotime($shift[0])) / 60;
        }
        if ($check_out > $shift[1]) {
            $late_out = abs(strtotime($check_out) - strtotime($shift[1])) / 60;
        }
        if ($check_in < $shift[0]) {
            $early_in = abs(strtotime($shift[0]) - strtotime($check_in)) / 60;
        }
        if ($check_out < $shift[1]) {
            $early_out = abs(strtotime($shift[1]) - strtotime($check_out)) / 60;
        }
        $workhours = abs(strtotime($work_date . ' ' . $_REQUEST['check_out_time']) - strtotime($work_date . ' ' . $_REQUEST['check_in_time'])) / 60;

        if (isset($_REQUEST['is_ot'])) {
            $is_ot = 1;
        } else {
            $is_ot = 0;
        }
        $update_attn = $conn->prepare("INSERT INTO tbl_attendance (emp_id,bio_id,is_ot,work_date,work_time,check_in,check_out,check_in_dtm,check_out_dtm,late_in,late_out,early_in,early_out,shift_id,manual_update_by,manual_update_dtm) VALUES (:emp_id,:bio_id,:is_ot,:work_date,:work_time,:check_in,:check_out,:check_in_dtm,:check_out_dtm,:late_in,:late_out,:early_in,:early_out,:shift_id,:manual_update_by,:manual_update_dtm) ");
        $check_data = array(
            ':emp_id' => $_REQUEST['emp_id'],
            ':bio_id' => $bio_id,
            ':work_date' => $work_date,
            ':check_in' => date('H:i:s', strtotime($_REQUEST['check_in_time'])),
            ':check_in_dtm' => date('Y-m-d H:i:s', strtotime($work_date . ' ' . $_REQUEST['check_in_time'])),
            ':check_out' => date('H:i:s', strtotime($_REQUEST['check_out_time'])),
            ':check_out_dtm' => date('Y-m-d H:i:s', strtotime($work_date . ' ' . $_REQUEST['check_out_time'])),
            ':late_in' => $late_in,
            ':late_out' => $late_out,
            ':early_in' => $early_in,
            ':early_out' => $early_out,
            ':work_time' => $workhours,
            ':shift_id' => $shift_id,
            ':is_ot' => $is_ot,
            ':manual_update_by' => $_SESSION['_user_id'],
            ':manual_update_dtm' => date('Y-m-d H:i:s')
        );

        $update_attn->execute($check_data);
    }

    $_SESSION['_msg'] = "Attendance Added";

    header("location:checkattendance.php?month_id=" . $_REQUEST['month_id'] . "&atten_year=" . $_REQUEST['atten_year'] . "&emp_id=" . $_REQUEST['emp_id'] . " ");
    die();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>
        <?php echo PAGE_TITLE; ?>- Check Attendance
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
                            <span class="breadcrumb-item active">Check Attendance</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>

            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">

                        <form name="thisForm" class="form-horizontal" method='POST' action="checkattendance.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                            <fieldset>
                                <div class="card">
                                    <div class="card-header bg-pgheader text-white header-elements-inline">
                                        <h6 class="card-title">Check Employee's Attendance</h6>
                                        <div class="header-elements">
                                            <div class="list-icons">
                                                <a class="list-icons-item" href="attendance_report_pf.php" title="Attendance Report PF"><i class="icon-arrow-left52 mr-2"></i></a>
                                                <a class="list-icons-item" data-action="fullscreen"></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body" id="">



                                        <div class="col-lg-12 ">
                                            <div class="row p-2 ">
                                                <div class="col-lg-8" style="text-align:right;">
                                                    <H6>Attendance for <?php echo $monthName . ' - ' . $atten_year . ' (' . $emp[1] . ') '; ?></H6>
                                                </div>
                                                <div class="col-lg-4" style="text-align:right;">
                                                    <a data-toggle="modal" data-target="#modalEmplyeeAttendanceAdd" href="" data-id="<?php echo $emp_id ?>" data-attn-id="" data-month="<?php echo $month_id ?>" data-year="<?php echo  $atten_year ?>" data-popup="tooltip" title="Add Attendance"><INPUT class="btn btn-info" type="button" name="" id="" value="Add Attendance"></a>
                                                </div>
                                            </div>
                                        </div>

                                        <table class="table table-xs invoice_tbl" id="stock_db_table">
                                            <thead>

                                                <tr class="rpt_heading" style="text-align:center;">
                                                    <th><b>#</b></th>
                                                    <th><b> Date</b></th>
                                                    <th><b>Check in</b></th>
                                                    <th><b> Check out</th>
                                                    <th style="width:18%; text-align:center;"><b> Action</b></th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <?php
                                                $SQL = "SELECT * FROM tbl_attendance WHERE MONTH(work_date) = '" . $month_id . "' AND YEAR(work_date) = '" . $atten_year . "' AND emp_id = '" . $emp_id . "' ORDER BY work_date ASC";
                                                

                                                $result = $conn->query($SQL);

                                                // print_r( $result);

                                                if ($result->rowCount() > 0) {
                                                    $Sno = 1;

                                                    while ($obj = $result->fetch()) {
                                                        $shift = $dbconn->GetSingleReconrd("mst_shifts", "shift_name", "shift_id", $obj->shift_id);



                                                        if ($obj->check_out_dtm != '0000-00-00 00:00:00') {
                                                            $check_out_dtm = date('H:i:s', strtotime($obj->check_out_dtm));
                                                        } else {
                                                            $check_out_dtm = '00:00:00';
                                                        }


                                                        $check_in_out_dets = '<a data-toggle="modal" data-target="#modalEmplyeeAttaDets" href="" data-id="' . $obj->emp_id . '" data-att-id="' . $obj->attn_id . '" data-atta_date="' . $obj->work_date . '" data-popup="tooltip" title="Employee Attendance Details">View Details</a>';
                                                        $att_update = '<a data-toggle="modal" data-target="#modalEmplyeeAttendanceupdate" href="" data-id="' . $obj->emp_id . '" data-attn-id="' . $obj->attn_id . '" data-month="' . $month_id . '" data-year="' . $atten_year . '" data-date="' . date('Y-m-d', strtotime($obj->work_date)) . '"data-popup="tooltip" title="Attendance Update">Update</a>';

                                                        echo '<tr style = " text-align:center;">
                                                                    <td>' . $Sno . '</td>
                                                                    <td>' . date('d-m-Y', strtotime($obj->work_date)) . '</td>
                                                                    <td>' . date('H:i:s', strtotime($obj->check_in_dtm)) . '</td>
                                                                    <td>' . $check_out_dtm . '</td>
                                                                    <td style = "width:18%; text-align:center;"> ' . $check_in_out_dets . ' | ' . $att_update . '
                                                                    </td>
                                                                </tr>';
                                                        /*<hr>
                                                                <li><a class="fancybox fancybox.ajax" href="inc/popup/fancybox_attendance_report.php?emp_id='.$obj->emp_id.'&dt='.date('Y-m-d',strtotime($obj->work_date)).'">View Report</a></li>	*/
                                                        $Sno++;
                                                    }
                                                    $obj = null;
                                                }
                                                ?>

                                            </tbody>
                                        </table>

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
    <?php include("modal_emp_atta_update.php") ?>
    <?php include("modal_employee_atta_det.php") ?>
    <?php include("modal_emp_atta_add.php") ?>
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

    $('#modalEmplyeeAttendanceupdate').on('show.bs.modal', function(e) {
        var id = $(e.relatedTarget).data('id');
        var attn_id = $(e.relatedTarget).data('attn-id');
        var date = $(e.relatedTarget).data('date');
        var month = $(e.relatedTarget).data('month');
        var year = $(e.relatedTarget).data('year');


        if (id != '') {
            $.ajax({
                type: 'post',
                url: 'inc/cis_ajax/jquery_modal_employee_atte_update.php',
                data: {
                    "id": id,
                    "date": date,
                    'attn_id': attn_id,
                    "month": month,
                    'year': year
                },
                success: function(data) {
                    // alert(data);
                    string = data.split("~");
                    //  $('#m_sales_amt').html(string[2]);
                    // $('#m_sales_code').html(data);
                    $('#m_sales_cod').html(string[1]);
                    $('#m_sales_re').html(string[0]);

                }
            });
        }
    });
    $('#modalEmplyeeAttaDets').on('show.bs.modal', function(e) {
        var id = $(e.relatedTarget).data('id');
        // var att_id = $(e.relatedTarget).data('att_id');
        var atta_date = $(e.relatedTarget).data('atta_date');
        // alert(atta_date);
        // alert(id);
        if (id != '') {
            $.ajax({
                type: 'post',
                url: 'inc/cis_ajax/jquery_modal_employee_atta_det.php',
                data: {
                    'id': id,
                    'atta_date': atta_date
                    // 'att_id':att_id
                },
                success: function(data) {
                    // alert(data);
                    string = data.split("~");
                    //  $('#m_sales_amt').html(string[2]);
                    // $('#m_sales_code').html(data);
                    $('#m_sales_code').html(string[1]);
                    $('#m_sales_rec').html(string[0]);

                }
            });
        }
    });

    $('#modalEmplyeeAttendanceAdd').on('show.bs.modal', function(e) {
        // alert();
        var id = $(e.relatedTarget).data('id');
        // var attn_id = $(e.relatedTarget).data('attn-id');
        // var date = $(e.relatedTarget).data('date');
        var month = $(e.relatedTarget).data('month');
        var year = $(e.relatedTarget).data('year');


        if (id != '') {
            $.ajax({
                type: 'post',
                url: 'inc/cis_ajax/jquery_modal_employee_atte_add.php',
                data: {
                    "id": id,
                    // "date": date,
                    // 'attn_id': attn_id,
                    "month": month,
                    'year': year
                },
                success: function(data) {
                    string = data.split("~");
                    $('#emp_det').html(string[1]);
                    $('#emp_atte_form').html(string[0]);
                }
            });
        }
    });
</script>
<!-- Footer -->