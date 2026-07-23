<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");
require_once("inc/common/css-js.php");



isAdmin();
ini_set('max_execution_time', '0'); // for infinite time of execution 


$conn = new dbconnect();
$dbconn = new dbhandler();

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

if (isset($_POST['IMPORT'])) {

    try {

        $file_pre = date("m-Y");


        if ($_FILES['imp_att_file']['name'] != "") {
            $ext = pathinfo($_FILES['imp_att_file']['name'], PATHINFO_EXTENSION);
            $customfilename = ($file_pre . '_attendance_sheet.') . $ext;
            $_REQUEST['imp_att_file'] = post_img($customfilename, $_FILES['imp_att_file']['tmp_name'], "project_img/import_atten/");
        }

        require_once 'excel_reader2.php';
       $fileName = 'project_img/import_atten/'.$_REQUEST['imp_att_file'];
        $data = new Spreadsheet_Excel_Reader();
        $data->read($fileName);
		$cnt = 0;

        for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {

            if ($data->sheets[0]['cells'][$i][1] != '') {
                // print_r($data->sheets[0]['cells'][$i]);
                // exit;
                $_REQUEST['bio_id'] = $data->sheets[0]['cells'][$i][3];

                $_REQUEST['emp_id'] = $dbconn->GetSingleReconrd("mst_employee", "emp_id", "bio_id > 0 AND bio_id", $_REQUEST['bio_id']);

                $_REQUEST['biometric_time'] = date('Y-m-d H:i:s', strtotime($data->sheets[0]['cells'][$i][1]));

                $_REQUEST['biometric_name'] = $data->sheets[0]['cells'][$i][5];

                $stmt = $conn->prepare("DELETE FROM tbl_attendance_import_new WHERE biometric_time = '" . date('Y-m-d H:i:s', strtotime($_REQUEST['biometric_time'])) . "' AND bio_id = " . $_REQUEST['bio_id'] . " ");
                $stmt->execute();


                $stmt = $conn->prepare("DELETE FROM tbl_attendance WHERE work_date = '" . date('Y-m-d', strtotime($_REQUEST['biometric_time'])) . "' AND bio_id = " . $_REQUEST['bio_id'] . " ");
                $stmt->execute();

                $attn_exist = $dbconn->GetSingleReconrd("tbl_attendance_import_new", "auto_id", "emp_id = '" . $_REQUEST['emp_id'] . "' AND biometric_time = '" . $_REQUEST['biometric_time'] . "' AND bio_id", $_REQUEST['bio_id']);



                if ($attn_exist > 0) {
                } else {

                    $stmt = null;

                    $stmt = $conn->prepare("INSERT INTO tbl_attendance_import_new (emp_id, bio_id, biometric_time,biometric_name) VALUES ('" . $_REQUEST['emp_id'] . "', '" . $_REQUEST['bio_id'] . "', '" . $_REQUEST['biometric_time'] . "', '" . $_REQUEST['biometric_name'] . "')");
                    // print_r($stmt);
                    $stmt->execute();
                }
            }
        }


        $SQL = "SELECT * FROM tbl_attendance_import_new WHERE sync_status = 0 ";

        $result = $conn->query($SQL);

        if ($result->rowCount() > 0) {
            $update_import = $conn->prepare("UPDATE tbl_attendance_import_new SET sync_status = :sync_status,check_in_out = :check_in_out,shift_id = :shift_id WHERE auto_id = :auto_id");
            // 
            // print_r($update_import);
            // $last_dt = '0000-00-00';

            while ($obj = $result->fetch()) {

                $_REQUEST['req_emp_id'] = $obj->emp_id;
                $_REQUEST['req_bio_id'] = $obj->bio_id;
                $_REQUEST['req_work_date'] = date('Y-m-d', strtotime($obj->biometric_time));
                $_REQUEST['req_shift_id'] = 1;

                $SQL2 = "SELECT check_in_out FROM tbl_attendance_import_new WHERE emp_id = " . $obj->emp_id . " AND sync_status = 1 ORDER BY auto_id DESC LIMIT 1";

                // print_r($SQL2);

                $result3 = $conn->query($SQL2);

                $last_type = "OUT";



                if ($result3->rowCount() > 0) {



                    $in = $result3->fetch();

                    $last_type1 = $in->check_in_out;

                    // echo $last_type1;
                    // echo '<br>';

                    if ($last_type == 'OUT' && $last_type1 == 'IN') {

                        $last_type = "OUT";

                        $last_type_final = "OUT";
                        //    echo'<br>';

                    } else {

                        $last_type = "OUT";

                        $last_type_final = "IN";
                    }
                }

                if ($last_type == 'IN') {

                    $type = "OUT";

                    // echo"if $type";
                    // echo '<br>';

                } else {

                    $type = "IN";
                    // echo"else $type";
                    //     echo '<br>';
                }

                //   echo($type);

                $sql_tot_punch = "SELECT count(*) as cnt FROM tbl_attendance_import_new WHERE emp_id = " . $obj->emp_id . " AND sync_status = 1 AND DATE(biometric_time) = '" . date('Y-m-d', strtotime($obj->biometric_time)) . "' ";
                // print_r($sql_tot_punch);

                $result2 = $conn->query($sql_tot_punch);

                if ($result2->rowCount() > 0) {

                    $tot_punch = $result2->fetch();

                    $tot_punch = $tot_punch->cnt;

                    if ($tot_punch % 2 == 0) {

                        if ($type == "IN") {

                            $SQL2 = "SELECT * FROM tbl_attendance WHERE emp_id = " . $obj->emp_id . " AND work_date = '" . $_REQUEST['req_work_date'] . "' AND shift_id = '" . $_REQUEST['req_shift_id'] . "' ";

                            //  print_r($SQL2); 

                            $result2 = $conn->query($SQL2);

                            if ($result2->rowCount() > 0) {

                                $attn = $result2->fetch();
                            } else {

                                $stmt = null;

                                $stmt = $conn->prepare("INSERT INTO tbl_attendance (emp_id, bio_id, work_date, check_in,check_in_dtm,shift_id) VALUES ('" . $_REQUEST['req_emp_id'] . "', '" . $_REQUEST['req_bio_id'] . "', '" . $_REQUEST['req_work_date'] . "', '" . date('H:i:s', strtotime($obj->biometric_time)) . "', '" . date('Y-m-d H:i:s', strtotime($obj->biometric_time)) . "', '" . $_REQUEST['req_shift_id'] . "')");

                                // print_r($stmt);

                                $stmt->execute();
                            }
                        } else if ($type == "OUT") {

                            $SQL2 = "SELECT * FROM tbl_attendance WHERE emp_id = " . $obj->emp_id . " AND shift_id = '" . $_REQUEST['req_shift_id'] . "' ORDER BY attn_id DESC LIMIT 1";

                            // print_r($SQL2);die();

                            $result2 = $conn->query($SQL2);

                            if ($result2->rowCount() > 0) {

                                $attn = $result2->fetch();

                                $last_chkin =  $attn->check_in_dtm;

                                $last_chkout = $obj->biometric_time;

                                $starttimestamp = strtotime($last_chkin);

                                $endtimestamp = strtotime($last_chkout);

                                $difference = abs($endtimestamp - $starttimestamp) / 3600;

                                if ($difference > 10) {

                                    $lastpunch_qry = "SELECT MAX(biometric_time) From tbl_attendance_import_new WHERE biometric_time < ( SELECT Max(biometric_time) FROM tbl_attendance_import_new)";

                                    $last_result = $conn->query($lastpunch_qry);

                                    if ($last_result->rowCount() > 0) {

                                        $lastobj = $last_result->fetch();
                                        $_REQUEST['req_emp_id'] = $lastobj->emp_id;
                                        $_REQUEST['req_bio_id'] = $lastobj->bio_id;
                                        $_REQUEST['req_work_date'] = date('Y-m-d', strtotime($lastobj->biometric_time));

                                        // $type == "OUT";

                                        $update_attn = $conn->prepare("UPDATE tbl_attendance SET check_out = :check_out,check_out_dtm=:check_out_dtm WHERE attn_id = :attn_id");

                                        $check_data = array(

                                            ':attn_id' => $attn->attn_id,
                                            ':check_out' => date('H:i:s', strtotime($lastobj->biometric_time)),
                                            ':check_out_dtm' => date('Y-m-d H:i:s', strtotime($lastobj->biometric_time))
                                        );
                                        $update_attn->execute($check_data);

                                        // $type = "--";

                                    }
                                } else {

                                    $update_attn = $conn->prepare("UPDATE tbl_attendance SET check_out = :check_out,check_out_dtm=:check_out_dtm WHERE attn_id = :attn_id");
                                    $check_data = array(
                                        ':attn_id' => $attn->attn_id,
                                        ':check_out' => date('H:i:s', strtotime($obj->biometric_time)),
                                        ':check_out_dtm' => date('Y-m-d H:i:s', strtotime($obj->biometric_time))
                                    );
                                    $update_attn->execute($check_data);
                                }
                            }
                        }
                    } else {
                        $firstpunch = "SELECT * FROM tbl_attendance_import_new WHERE emp_id = " . $obj->emp_id . " AND sync_status = 1 AND DATE(biometric_time) = '" . date('Y-m-d', strtotime($obj->biometric_time)) . "' ORDER BY auto_id ASC LIMIT 1";
                        // print_r($firstpunch);die();
                        $first_result = $conn->query($firstpunch);

                        if ($first_result->rowCount() > 0) {

                            $firobj = $first_result->fetch();
                            $_REQUEST['req_emp_id'] = $firobj->emp_id;
                            $_REQUEST['req_bio_id'] = $firobj->bio_id;
                            $_REQUEST['req_work_date'] = date('Y-m-d', strtotime($firobj->biometric_time));

                            $type = $last_type_final;

                            if ($type == 'OUT') {

                                $SQL2 = "SELECT * FROM tbl_attendance WHERE emp_id = " . $obj->emp_id . " AND shift_id = '" . $_REQUEST['req_shift_id'] . "' ORDER BY attn_id DESC LIMIT 1";

                                $result2 = $conn->query($SQL2);

                                if ($result2->rowCount() > 0) {
                                    $attn = $result2->fetch();

                                    // $type == "OUT"; 

                                    $update_attn = $conn->prepare("UPDATE tbl_attendance SET check_out = :check_out,check_out_dtm=:check_out_dtm WHERE attn_id = :attn_id");

                                    $check_data = array(
                                        ':attn_id' => $attn->attn_id,
                                        ':check_out' => date('H:i:s', strtotime($obj->biometric_time)),
                                        ':check_out_dtm' => date('Y-m-d H:i:s', strtotime($obj->biometric_time))
                                    );

                                    $update_attn->execute($check_data);
                                }
                            }
                            $_REQUEST['req_shift_id'] = 1;

                            $SQL2 = "SELECT * FROM tbl_attendance WHERE emp_id = " . $firobj->emp_id . " AND work_date = '" . $_REQUEST['req_work_date'] . "' AND shift_id = '" . $_REQUEST['req_shift_id'] . "' ";

                            $result2 = $conn->query($SQL2);

                            if ($result2->rowCount() > 0) {

                                $attn = $result2->fetch();
                            } else {

                                $stmt = null;

                                $stmt = $conn->prepare("INSERT INTO tbl_attendance (emp_id, bio_id, work_date, check_in,check_in_dtm,shift_id) VALUES ('" . $_REQUEST['req_emp_id'] . "', '" . $_REQUEST['req_bio_id'] . "', '" . $_REQUEST['req_work_date'] . "', '" . date('H:i:s', strtotime($firobj->biometric_time)) . "', '" . date('Y-m-d H:i:s', strtotime($firobj->biometric_time)) . "', '" . $_REQUEST['req_shift_id'] . "')");

                                $stmt->execute();
                            }
                        }
                    }
                }

                $data1 = array(
                    ':auto_id' => $obj->auto_id,
                    ':sync_status' => 1,
                    ':check_in_out' => $type,
                    ':shift_id' => $_REQUEST['req_shift_id']
                );

                $update_import->execute($data1);

                if ($last_dt != date('Y-m-d', strtotime($obj->biometric_time))) {

                    $t_tot_punch = "SELECT count(*) as cnt FROM tbl_attendance_import_new WHERE emp_id = " . $obj->emp_id . " AND sync_status = 1 AND DATE(biometric_time) = '" . date('Y-m-d', strtotime($obj->biometric_time)) . "' ";

                    // print_r($t_tot_punch);

                    $result3 = $conn->query($t_tot_punch);

                    if ($result3->rowCount() > 0) {

                        $tot_punch_cnt = $result3->fetch();

                        $tot_punch1 = $tot_punch_cnt->cnt;

                        if ($tot_punch1 % 2 != 0) {

                            if ($tot_punch1 == 1) {

                                if ($last_type == 'IN') {

                                    $last_type = "IN";
                                } else {

                                    $last_type = "OUT";
                                }
                            } else {

                                if ($last_type == 'IN') {

                                    $last_type = "OUT";
                                } else {

                                    $last_type = "IN";
                                }
                            }
                        } else {

                            if ($last_type == 'IN') {

                                $last_type = "OUT";
                            } else {

                                $last_type = "IN";
                            }
                        }
                    }
                } else {

                    if ($last_type == 'IN') {

                        $last_type = "OUT";
                    } else {

                        $last_type = "IN";
                    }
                }

                //  echo ''."$obj->biometric_time".' <br>';

                $last_dt = date('Y-m-d', strtotime($obj->biometric_time));
            }
            // die();
        }

        $attn_sql = "SELECT * FROM tbl_attendance WHERE (check_in != '00:00:00' AND check_out != '00:00:00') AND work_time = 0 ";
        $result2 = $conn->query($attn_sql);

        if ($result2->rowCount() > 0) {
            while ($attnObj = $result2->fetch()) {

                $imp_sql = "SELECT * FROM tbl_attendance_import_new WHERE emp_id = " . $attnObj->emp_id . " AND biometric_time > '" . $attnObj->check_in_dtm . "' AND biometric_time < '" . $attnObj->check_out_dtm . "' AND shift_id = '" . $attnObj->shift_id . "' ";
                // print_r($imp_sql );die();
                $res = $conn->query($imp_sql);

                $breakhours = 0;
                $workhours = 0;
                $intime = $attnObj->check_in_dtm;
                $outtime = 0;
                $lasttype = '';
                $late_in = 0;
                $late_out = 0;
                $early_in = 0;
                $early_out = 0;

                if ($res->rowCount() > 0) {
                    while ($ob = $res->fetch()) {
                        if ($ob->check_in_out == 'OUT') {

                            $outtime = $ob->biometric_time;
                            // echo'<br>';

                            $workhours += abs(strtotime($ob->biometric_time) - strtotime($intime)) / 60;

                            $lasttype = 'OUT';
                            // die();

                        }

                        if ($ob->check_in_out == 'IN') {

                            // echo $ob->biometric_time;echo'<br>';
                            // echo $outtime;echo'<br>';
                            // echo (strtotime($ob->biometric_time));echo'<br>';
                            // echo $outtime;echo'<br>';

                            $breakhours = abs(strtotime($ob->biometric_time) - strtotime($outtime)) / 60;
                            //  echo'<br>';

                            $intime = $ob->biometric_time;

                            $lasttype = 'IN';
                            // die();
                        }
                    }
                    if ($lasttype == 'IN') {

                        $workhours += abs(strtotime($attnObj->check_out_dtm) - strtotime($intime)) / 60;
                    }
                    if ($lasttype == 'OUT') {
                    }
                } else {

                    $workhours += abs(strtotime($attnObj->check_out_dtm) - strtotime($attnObj->check_in_dtm)) / 60;
                }

                $shift_det = $dbconn->GetSingleReconrd("mst_shifts", "CONCAT(check_in,'~',check_out,'~',check_in_start,'~',check_in_end,'~',check_out_start,'~',check_out_end)", "shift_id", $attnObj->shift_id);

                $shift = explode('~', $shift_det);

                if ($attnObj->check_in > $shift[0]) {
                    $late_in = abs(strtotime($attnObj->check_in) - strtotime($shift[0])) / 60;
                }

                if ($attnObj->check_out > $shift[1]) {
                    $late_out = abs(strtotime($attnObj->check_out) - strtotime($shift[1])) / 60;
                }

                if ($attnObj->check_in < $shift[0]) {
                    $early_in = abs(strtotime($shift[0]) - strtotime($attnObj->check_in)) / 60;
                }

                if ($attnObj->check_out < $shift[1]) {
                    $early_out = abs(strtotime($shift[1]) - strtotime($attnObj->check_out)) / 60;
                }

                $update_attn = $conn->prepare("UPDATE tbl_attendance SET work_time = :work_time,break_time=:break_time,late_in=:late_in,late_out=:late_out,early_in=:early_in,early_out=:early_out WHERE attn_id = :attn_id");

                $check_data = array(
                    ':attn_id' => $attnObj->attn_id,
                    ':work_time' => $workhours,
                    ':break_time' => $breakhours,
                    ':late_in' => $late_in,
                    ':late_out' => $late_out,
                    ':early_in' => $early_in,
                    ':early_out' => $early_out
                );
                $update_attn->execute($check_data);
            }
        }
        $_SESSION['_msg'] = "Attendance imported successfully";
    } catch (Exception $e) {
        echo $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
    }
    header("location:attendance_new.php");
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
        <?php echo PAGE_TITLE; ?>-Import Attendance
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
                            <a href="#" class="breadcrumb-item">Attendance and Salary</a>
                            <span class="breadcrumb-item active">Import Attendance Log</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>

            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">

                        <form name="thisform" class="form-horizontal" method='POST' action="import_attendance.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                            <fieldset>
                                <div class="card">
                                    <div class="card-header bg-pgheader text-white header-elements-inline">
                                        <h6 class="card-title">Import Attendance Log</h6>
                                        <div class="header-elements">
                                            <div class="list-icons">
                                                <a class="list-icons-item" href="attendance_new.php" title="Import Attendance List"><i class="icon-arrow-left52 mr-2"></i></a>
                                                <a class="list-icons-item" data-action="fullscreen"></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body " id="">

                                        <div class="form-group row p-2">
                                            <label class="col-lg-2 col-form-label"><b>Import Attendance 
                                                    <span class="text-mandatory">*<span></b></label>
                                            <div class="col-lg-4">
                                                <div class="col-lg-12 row">
                                                    <div class="col-lg-10" style="padding-top:5px;">
                                                        <input type="file" class="form" id="imp_att_file" name="imp_att_file" tabindex="-1" value="" />
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <INPUT class="btn btn-info" type="submit" name="IMPORT" id="submit" value="IMPORT">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 ">
                                                <div class="row ">
                                                    <div class="col-lg-8" style="text-align:right;">
                                                        <a href="attendance_report.php"><INPUT class="btn btn-info" type="button" name="" id="" value="Attendance Report"></a>
                                                    </div>
                                                    <div class="col-lg-4 " style="text-align:right;">
                                                    <a href="attendance_new.php"><INPUT class="btn btn-info" type="button" name="" id="" value="Attendance Log Report"></a>
                                                    </div>
                                                    
                                                </div>
                                            </div>
                                            <div class="col-lg-4" style="padding-top:5px;">

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
        var imp_att_file = $('#imp_att_file').val();

        if (imp_att_file == '') {
            alert("Please Import Attendance xls Sheet...!");
            return false;
        }

        document.thisform.submit();
    }

    $(document).on("change", "#imp_att_file", function() {


        myfile = $(this).val();
        var ext = myfile.split('.').pop();
        if (ext == "xls") {

        } else {
            alert("Please check the file type of Import Attendance.\nAllowed File Type: .xls Sheet Only...!");
            $(this).val('');
        }



    })
</script>
<!-- Footer -->