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


if (isset($_POST['UPDATE'])) {

    $update_id = $_REQUEST['txtHid'];
    //	print_r($_REQUEST);exit;
    try {
        $stmt = null;
        $stmt = $conn->prepare("UPDATE  tbl_emp_salary SET em_current = :em_current
				WHERE emp_id = :emp_id");
        $data = array(
            ':emp_id' => $update_id,
            ':em_current' => 0
        );
        $stmt->execute($data);
        if ($_REQUEST['emp_ctc'] == '' || $_REQUEST['emp_ctc'] == 0) {
            $_REQUEST['emp_ctc'] = $_REQUEST['emp_ctc_old'];
        }

        $stmt2 = null;
        $stmt2 = $conn->prepare("INSERT INTO tbl_emp_salary (emp_id, sal_id, emp_ctc, emp_ot_sal, emp_cl, em_from, em_current, em_remarks, is_allow_fh, em_update_by, em_date_time) VALUES 
											(:emp_id, :sal_id, :emp_ctc, :emp_ot_sal, :emp_cl, :em_from, :em_current, :em_remarks, :is_allow_fh, :em_update_by, :em_date_time)");
        $data2 = array(
            ':emp_id' => $update_id,
            ':sal_id' => $_REQUEST['sal_id'],
            ':emp_ctc' => $_REQUEST['emp_ctc'],
            ':emp_ot_sal' => $_REQUEST['emp_ot_sal'],
            ':emp_cl' => $_REQUEST['emp_cl'],
            ':em_from' => date('Y-m-d'),
            ':em_current' => 1,
            ':em_remarks' => $_REQUEST['em_remarks'],
            ':is_allow_fh' => $_REQUEST['is_allow_fh'],
            ':em_update_by' => $_SESSION['_user_id'],
            ':em_date_time' => date('Y-m-d H:i:s')
        );
        // print_r($data2);die();
        $stmt2->execute($data2);
        $last_id = $conn->lastInsertId();

        $stmt3 = null;
        $stmt3 = $conn->prepare("UPDATE  mst_employee SET emp_ctc = :emp_ctc, em_id = :em_id
				WHERE emp_id = :emp_id");
        $data3 = array(
            ':emp_id' => $update_id,
            ':emp_ctc' => $_REQUEST['emp_ctc'],
            ':em_id' => $last_id
        );
        $stmt3->execute($data3);

        $_SESSION['_msg'] = "Employee Salary details has been succesfully Updated..!";
    } catch (Exception $e) {
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
    }
    header("location:employee_salary.php");
    die();
}

if ($_REQUEST['emp_id'] != '') {
    $is_allow_fh = '2';
    // $result = $conn->query("SELECT * FROM mst_employee WHERE rec_del_status = '1' AND emp_id = " . $_REQUEST['emp_id']);
    $result = $conn->query("SELECT * FROM mst_employee as a LEFT JOIN tbl_emp_salary as b ON a.em_id = b.em_id WHERE a.emp_id = " . $_REQUEST['emp_id']);
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
        <?php echo PAGE_TITLE; ?>-Employee Salary
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
                            <span class="breadcrumb-item active">Employee Salary</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>

            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">

                        <form name="thisForm" class="form-horizontal" method='POST' action="emp_setsalary.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                            <fieldset>
                                <div class="card">
                                    <div class="card-header bg-pgheader text-white header-elements-inline">
                                        <h6 class="card-title">Employee Salary</h6>
                                        <div class="header-elements">
                                            <div class="list-icons">
                                                <a class="list-icons-item" href="employee_salary.php" title="Salary Package List"><i class="icon-arrow-left52 mr-2"></i></a>
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
                                                            <h6 class="card-title">Salary Details</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="col-lg-12">
                                                                <div class="form-group row  pt-2">
                                                                    <label class="col-lg-5 col-form-label"><b>Salary Package <span class="text-mandatory">*</span></b></label>
                                                                    <!-- <input class="col-lg-6 form-control" type="text" name="" id="" value=""/> -->
                                                                    <div class="col-lg-7">
                                                                        <select name="sal_id" id="sal_id" data-placeholder="Select a Salary Package.." class="select-search">
                                                                            <option value="">Select Package</option>
                                                                            <?php
                                                                            $dbconn = new dbhandler();
                                                                            echo $dbconn->fnFillComboFromTable_Where("sal_id", "CONCAT(sal_package_name,'/',IF(sal_period = 1,'Day','Month'))", "mst_salary_setting", "sal_id", " WHERE rec_del_status = 1")
                                                                            ?>
                                                                        </select>
                                                                        <script>
                                                                            document.thisForm.sal_id.value = "<?php echo $obj->sal_id; ?>";
                                                                        </script>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row pt-2" id="">
                                                                    <label class="col-lg-5 col-form-label"><b>Current CTC </b></label>
                                                                    <div class="col-lg-7" style="padding-top:10px;">
                                                                        <lable>Rs.<span name="curr" id="curr"></span></lable>
                                                                        <input type="hidden" name="emp_ctc_old" id="emp_ctc_old" value="<?php echo $obj->emp_ctc; ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row pt-2" id="">
                                                                    <label class="col-lg-5 col-form-label"><b>New CTC <span class=""></span></b></label>
                                                                    <div class="col-lg-7">
                                                                        <div class="input-group">
                                                                            <input class="col-lg-7 form-control" type="text" name="emp_ctc" id="emp_ctc" onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="6" value="<?php echo $obj->emp_ctc; ?>" />
                                                                            <span class="col-lg-5 input-group-prepend form-control" name="period" id="period">
                                                                                <!-- <input class=" " type="text" name="" id="" value=""/> -->
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row  pt-2">
                                                                    <label class="col-lg-5 col-form-label"><b>OT Salary / Hour <span class="text-mandatory">*</span></b></label>
                                                                    <div class="col-lg-7">
                                                                        <input class=" form-control" type="text" name="emp_ot_sal" id="emp_ot_sal" onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="5" value="<?php echo $obj->emp_ot_sal; ?>" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row  pt-2">
                                                                    <label class="col-lg-5 col-form-label"><b>Leave with Pay <span class="text-mandatory">*</span></b></label>
                                                                    <div class="col-lg-7">
                                                                        <input class=" form-control" type="text" name="emp_cl" id="emp_cl" onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="4" value="<?php echo $obj->emp_cl; ?>" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row  pt-2">
                                                                    <label class="col-lg-5 col-form-label"><b>Allow Function Holidays<span class="text-mandatory"> *</span></b></label>
                                                                    <div class="col-lg-7" style="padding-top:10px;">
                                                                        <input class="" type="radio" id="is_allow_fh" name="is_allow_fh" value="1" checked="" <?php if ($is_allow_fh == 1) {
                                                                                                                                                                    echo "checked";
                                                                                                                                                                } ?>>

                                                                        <label for="">&nbsp;Yes</label>&nbsp;&nbsp;&nbsp;

                                                                        <input type="radio" id="is_allow_fh" name="is_allow_fh" value="2" <?php if ($is_allow_fh == 2) {
                                                                                                                                                echo "checked";
                                                                                                                                            } ?> />
                                                                        <label for="">&nbsp;No</label>
                                                                        <!-- <input class=" form-control" type="text" name="" id="" value=""/> -->
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row  pt-2">
                                                                    <label class="col-lg-5 col-form-label"><b>Remarks if</b></label>
                                                                    <div class="col-lg-7">
                                                                        <textarea class="form-control" name="em_remarks" id="em_remarks"><?php echo $obj->em_remarks; ?></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card-footer text-center">
                                                            <?php if ($_REQUEST["emp_id"] != '' && $obj->rec_del_status == 1) { ?>
                                                                <INPUT class="btn btn-info" type="submit" name="UPDATE" id="submit" value="Update">
                                                                <INPUT class="btn btn-light" type="button" name="Cancel" value="Cancel" onClick="javascript:history.go(-1);">
                                                                <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['emp_id']; ?>">
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-lg-6">
                                                    <div class="card">
                                                        <div class="card-header bg-info text-white header-elements-inline">
                                                            <h6 class="card-title">Salary Details</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="col-lg-12">
                                                                <div class="form-group row  pt-2">
                                                                    <label class="col-lg-3 col-form-label"><b>Basic (<span class="" name="basic" id="basic"></span>%)</b></label>
                                                                    <div class="col-lg-3">
                                                                        <input class="form-control" Readonly type="text" name="sal_basic" id="sal_basic" value="" />
                                                                    </div>
                                                                    <label class="col-lg-3 col-form-label"><b>HRA (<span class="" name="hra" id="hra"></span>%)</b></label>
                                                                    <div class="col-lg-3">
                                                                        <input class="form-control" Readonly type="text" name="sal_hra" id="sal_hra" value="" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row  pt-2">
                                                                    <label class="col-lg-3 col-form-label"><b>DA (<span class="" name="da" id="da"></span>%)</b></label>
                                                                    <div class="col-lg-3">
                                                                        <input class="form-control" Readonly type="text" name="sal_da" id="sal_da" value="" />
                                                                    </div>
                                                                    <label class="col-lg-3 col-form-label"><b>Convey (<span class="" name="convey" id="convey"></span>%)</b></label>
                                                                    <div class="col-lg-3">
                                                                        <input class="form-control" Readonly type="text" name="sal_convey" id="sal_convey" value="" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row  pt-2">
                                                                    <label class="col-lg-3 col-form-label"><b>Basic + DA </b></label>
                                                                    <div class="col-lg-3">
                                                                        <input class="form-control" Readonly type="text" name="sum" id="sum" value="" />
                                                                    </div>
                                                                    <label class="col-lg-3 col-form-label"><b>PF (<span class="" name="pf" id="pf"></span>%)</b></label>
                                                                    <div class="col-lg-3">
                                                                        <input class="form-control" Readonly type="text" name="sal_pf" id="sal_pf" value="" />
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row  pt-2">
                                                                    <label class="col-lg-9 col-form-label" style="text-align: right;"><b>Net Salary</b></label>
                                                                    <div class="col-lg-3">
                                                                        <input class="form-control" Readonly name="sal_total" id="sal_total" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card-footer text-center">
                                                            <label class="col-lg-9 col-form-label" style="text-align: center;">
                                                                <h4><b>Rs.&nbsp;<span class="" name="cca_total" id="cca_total"></span>&nbsp;/-</b></h4>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <?php
                                                    echo '<div class="card">
                                                        <div class="card-header bg-info text-white header-elements-inline">
                                                            <h6 class="card-title">Salary Update History</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="col-lg-12">
                                                                <div class="row">';

                                                    $emp_ctc = 0;
                                                    $icon = '';
                                                    $SQL1 = "SELECT DISTINCT * FROM tbl_emp_salary WHERE emp_id = " . $_REQUEST['emp_id'] . " ORDER BY em_id DESC";

                                                    $result1 = $conn->query($SQL1);
                                                    if ($result1->rowCount() > 0) {
                                                        // echo "hi";

                                                        while ($ut = $result1->fetch()) {

                                                            if ($ut->emp_ctc > $emp_ctc) {
                                                                $icon = '<i class="fa fa-arrow-up fa-2x" style="color:green;"></i>';
                                                            } else {
                                                                $icon = '<i class="fa fa-arrow-down fa-2x" style="color:red;"></i>';
                                                            }

                                                            if ($ut->em_current == 1) {
                                                                $class_current = "info";
                                                            } else {
                                                                $class_current = "";
                                                            }

                                                            echo '  <div class="col-lg-2" style="  text-align:center; padding-top:12px;">
                                                                                            ' . $icon . '
                                                                                        </div>
                                                                                        <div class="col-lg-10">
                                                                                            Salary CTC <strong>Rs.' . $ut->emp_ctc . '</strong> <span class="add-on" id="history"></span> (' . $dbconn->GetSingleReconrd("mst_salary_setting", "sal_package_name", "sal_id", $ut->sal_id) . ') is updated by ' . $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id", $ut->em_update_by) . ' on ' . date("d-M-Y", strtotime($ut->em_date_time)) . '<br>Note : ' . $ut->em_remarks . '
                                                                                        </div><legend class="font-weight-semibold"></legend>';
                                                            $emp_ctc = $ut->emp_ctc;
                                                        }
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

        if (notSelected(document.thisForm.sal_id, "Salary Package..!")) {
            return false;
        }
        if (isNull(document.thisForm.emp_ot_sal, "OT Salary..!")) {
            return false;
        }
        if (isNull(document.thisForm.emp_cl, "Leave With Pay..!")) {
            return false;
        }


    }

    $('#sal_id').change(function() {
        var sal_id = $(this).val();
        $.ajax({
            type: "POST",
            url: "inc/cis_ajax/emp_sal_package_type.php",
            data: {
                'id': sal_id
            }
        }).done(function(msg) {
            // alert(msg);
            var dataSal = msg.split('~');
            $('#curr').html(dataSal[6]);
            $('#period').html(dataSal[0]);
            $('#history').html(dataSal[0]);
            $('#basic').html(dataSal[1]);
            $('#da').html(dataSal[2]);
            $('#hra').html(dataSal[3]);
            $('#convey').html(dataSal[4]);
            $('#pf').html(dataSal[5]);
            $('#emp_ctc_old').val(dataSal[6]).change();
        });
    });

    $('#emp_ctc').change(function() {
        var emp_id = $('#emp_id').val();
        var sal_id = $('#sal_id').val();
        var ctc = $('#emp_ctc').val();
        $.ajax({
            type: "POST",
            url: "inc/cis_ajax/emp_salary_cal.php",
            data: {
                'id': emp_id,
                'sal_id': sal_id,
                'ctc': ctc
            }
        }).done(function(msg) {
            // alert(msg);
            var dataSal = msg.split('~');
            $('#sal_basic').val(dataSal[0]);
            $('#sal_da').val(dataSal[1]);
            $('#sum').val(dataSal[2]);
            $('#sal_hra').val(dataSal[3]);
            $('#sal_convey').val(dataSal[4]);
            $('#sal_pf').val(dataSal[5]);
            $('#sal_total').val(dataSal[6]);
            $('#sal_cca').val(dataSal[7]);
            $('#cca_total').html(dataSal[8]);

        });
    });

    $(function() {
        $('#emp_ctc').trigger('change');
        $('#sal_id').trigger('change');
    });
</script>
<!-- Footer -->