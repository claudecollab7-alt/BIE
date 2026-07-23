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

$_REQUEST['sal_cca'] = 0;

if (isset($_REQUEST['sal_period']) && $_REQUEST['sal_period'] == 1) {
    $_REQUEST['sal_type']  = "D";
}
if (isset($_REQUEST['sal_period']) && $_REQUEST['sal_period'] == 2) {
    $_REQUEST['sal_type']  = "M";
}

$_REQUEST['created_dtm'] = date('Y-m-d H:i:s');
if (isset($_POST['SAVE'])) {

     $mst_exist = $dbconn->GetSingleReconrd("mst_salary_setting","sal_package_name","rec_del_status = 1 AND sal_period = $_REQUEST[sal_period]  AND sal_package_name",$_REQUEST['sal_package_name']);
			
    if($mst_exist != ""){
        $_SESSION['_msg_err'] = "Salary Package Name Already Exist..!";
        header("location:lst_salary_package.php");
        die();
    }

    try {
        $stmt = null;
        $stmt = $conn->prepare("INSERT INTO mst_salary_setting (sal_package_name, sal_type, sal_period, sal_basic, sal_da, sal_hra,sal_convey, sal_pf, sal_cca, created_by,created_dtm) VALUES (:sal_package_name, :sal_type, :sal_period, :sal_basic, :sal_da, :sal_hra, :sal_convey,:sal_pf, :sal_cca, :created_by,:created_dtm)");

        $data = array(
            ':sal_package_name' => $_REQUEST['sal_package_name'],
            ':sal_type' => $_REQUEST['sal_type'],
            ':sal_period' => $_REQUEST['sal_period'],
            ':sal_basic' => $_REQUEST['sal_basic'],
            ':sal_da' => $_REQUEST['sal_da'],
            ':sal_hra' => $_REQUEST['sal_hra'],
            ':sal_convey' => $_REQUEST['sal_convey'],
            ':sal_pf' => $_REQUEST['sal_pf'],
            ':sal_cca' => $_REQUEST['sal_cca'],
            ':created_by' =>  $_SESSION['_user_id'],
            ':created_dtm' => $_REQUEST['created_dtm']
        );
        // print_r($data);die();
        $stmt->execute($data);
        $_SESSION['_msg'] = "Salary Package succesfully saved..!";
    } catch (Exception $e) {
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
    }

    header("location:lst_salary_package.php");
    die();
}

if (isset($_POST['UPDATE'])) {
    $update_id = $_REQUEST['txtHid'];

     $mst_exist = $dbconn->GetSingleReconrd("mst_salary_setting", "sal_id", "sal_id <> " . $update_id . " AND rec_del_status = 1  AND sal_period = $_REQUEST[sal_period] AND sal_package_name",$_REQUEST['sal_package_name']);

    if ($mst_exist != "") {
        $_SESSION['_msg_err'] = "Salary Package Name Already Exist..!";
        header("location:lst_salary_package.php");
        die();
    }

    try {
        $stmt = null;

        $stmt = $conn->prepare("UPDATE  mst_salary_setting SET sal_package_name = :sal_package_name, sal_type= :sal_type, sal_period=:sal_period, sal_basic= :sal_basic, sal_da=:sal_da, sal_hra=:sal_hra, sal_convey = :sal_convey, sal_pf= :sal_pf, modify_by = :modify_by, modify_dtm = :modify_dtm
					WHERE sal_id = :sal_id");
        $data = array(
            ':sal_id' => $update_id,
            ':sal_package_name' => $_REQUEST['sal_package_name'],
            ':sal_type' => $_REQUEST['sal_type'],
            ':sal_period' => $_REQUEST['sal_period'],
            ':sal_basic' => $_REQUEST['sal_basic'],
            ':sal_da' => $_REQUEST['sal_da'],
            ':sal_hra' => $_REQUEST['sal_hra'],
            ':sal_convey' => $_REQUEST['sal_convey'],
            ':sal_pf' => $_REQUEST['sal_pf'],
            // ':sal_cca' => $_REQUEST['sal_cca'],
            ':modify_by' => $_SESSION['_user_id'],
            ':modify_dtm' => date('Y-m-d H:i:s')
        );
        // print_r($data);die();
        $stmt->execute($data);
        // echo $stmt->fullQuery;

        $_SESSION['_msg'] = "Salary Package succesfully Updated..!";
    } catch (Exception $e) {
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
    }

    header("location:lst_salary_package.php");
    die();
}

$sal_basic = 40;
$sal_da = 25;
$sal_hra = 25;
$sal_convey = 10;
$sal_pf = 12;

$sal_id = "";
$sal_type = "";
if (isset($_REQUEST['sal_id'])) {
    $result = $conn->query("SELECT * FROM mst_salary_setting WHERE rec_del_status = '1' AND sal_id = " . $_REQUEST['sal_id']);
    if ($result->rowCount() > 0) {
        $obj = $result->fetch(PDO::FETCH_OBJ);

        $sal_id = $obj->sal_id;

        $sal_basic = $obj->sal_basic;
        $sal_da = $obj->sal_da;
        $sal_hra = $obj->sal_hra;
        $sal_convey = $obj->sal_convey;
        $sal_pf = $obj->sal_pf;

        $sal_type = $obj->sal_type;
    }
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>
        <?php echo PAGE_TITLE; ?>-Salary Package
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
                            <span class="breadcrumb-item active">Salary Package</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>

            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">

                        <form name="thisform" class="form-horizontal" method='POST' action="mst_salary_package_add.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                            <fieldset>
                                <div class="card">
                                    <div class="card-header bg-pgheader text-white header-elements-inline">
                                        <h6 class="card-title">Salary Package</h6>
                                        <div class="header-elements">
                                            <div class="list-icons">
                                                <a class="list-icons-item" href="lst_salary_package.php" title="Salary Package List"><i class="icon-arrow-left52 mr-2"></i></a>
                                                <a class="list-icons-item" data-action="fullscreen"></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body" id="">
                                        <div class="form-group row p-2">
                                            <label class="col-lg-2 col-form-label"><b>Salary Package <span class="text-mandatory">*</span></b></label>
                                            <div class="col-lg-4">
                                                <input type="text" class="form-control" name="sal_package_name" id="sal_package_name" onkeypress='return event.charCode >= 48 && event.charCode <= 57' maxlength="10" value="<?php echo $obj->sal_package_name; ?>" />
                                            </div>
                                            <label class="col-lg-2 col-form-label"><b>CTC Per Day / Month<span class="text-mandatory"> *</span></b></label>
                                            <div class="col-lg-4">
                                                <select name="sal_period" data-placeholder="Choose a Period.." class="select">
                                                    <option value="">Choose a Period..</option>
                                                    <option value="1">Per Day</option>
                                                    <option value="2">Per Month</option>
                                                </select>
                                                <script>
                                                    document.thisform.sal_period.value = "<?php echo $obj->sal_period; ?>";
                                                </script>
                                            </div>
                                        </div>
                                        <div class="form-group row p-2">
                                            <label class="col-lg-2 col-form-label"><b>Basic from CTC <span class="text-mandatory">*</span></b></label>
                                            <div class="col-lg-4">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" style="text-align:center;"  onkeypress='return event.charCode >= 48 && event.charCode <= 57' maxlength="2" id="sal_basic" name="sal_basic" tabindex="-1" value="<?php echo $sal_basic; ?>" />
                                                    <span class="input-group-prepend">
                                                        <input class="form-control input-group-text" readonly maxlength="" value="%"></input>
                                                    </span>
                                                </div>
                                            </div>
                                            <label class="col-lg-2 col-form-label"><b>DA from CTC <span class="text-mandatory">*</span></b></label>
                                            <div class="col-lg-4">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" style="text-align:center;"   onkeypress='return event.charCode >= 48 && event.charCode <= 57' maxlength="2" id="sal_da" name="sal_da" tabindex="-1" value="<?php echo $sal_da ?>" />
                                                    <span class="input-group-prepend">
                                                        <input class="form-control input-group-text" readonly maxlength="" value="%"></input>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class=" form-group row p-2">
                                            <label class="col-lg-2 col-form-label"><b>HRA from CTC  <span class="text-mandatory">*</span></b></label>
                                            <div class="col-lg-4">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" style="text-align:center;"   onkeypress='return event.charCode >= 48 && event.charCode <= 57' maxlength="2" id="sal_hra" name="sal_hra" tabindex="-1" value="<?php echo $sal_hra ?>" />
                                                    <span class="input-group-prepend">
                                                    <input class="form-control input-group-text" readonly maxlength="" value="%"></input>
                                                    </span>
                                                </div>
                                            </div>
                                            <label class="col-lg-2 col-form-label"><b>Conveyance from CTC <span class="text-mandatory">*</span></b></label>
                                            <div class="col-lg-4">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" style="text-align:center;"   onkeypress='return event.charCode >= 48 && event.charCode <= 57' maxlength="2" id="sal_convey" name="sal_convey" tabindex="-1" value="<?php echo $sal_convey ?>" />
                                                    <span class="input-group-prepend">
                                                    <input class="form-control input-group-text" readonly maxlength="" value="%"></input>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class=" form-group row p-2">
                                            <label class="col-lg-2 col-form-label"><b>PF deduction from Basic <span class="text-mandatory">*</span></b></label>
                                            <div class="col-lg-4">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" style="text-align:center;"   onkeypress='return event.charCode >= 48 && event.charCode <= 57' maxlength="2" id="sal_pf" name="sal_pf" tabindex="-1" value="<?php echo $sal_pf ?>" />
                                                    <span class="input-group-prepend">
                                                    <input class="form-control input-group-text" readonly maxlength="" value="%"></input>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer text-center">
                                        <?php if ($_REQUEST["sal_id"] != '') { ?>
                                            <INPUT class="btn btn-info" type="submit" name="UPDATE" id="submit" value="Update">
                                            <INPUT class="btn btn-light" type="button" name="Cancel" onclick="javascript:history.go(-1);" value="Cancel" >
                                            <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['sal_id']; ?>">
                                        <?php } else { ?>
                                            <INPUT class="btn btn-custom" type="submit" name="SAVE" id="submit" value="Save">
                                            <INPUT class="btn btn-light" type="button" onclick="window.location='lst_salary_package.php';" name="Cancel" value="Cancel" >
                                            <input type="hidden" name="txtHid" value="">
                                        <?php } ?>
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
        var hra_value = $('#sal_hra').val();
        var sal_convey = $('#sal_convey').val();
        var sal_pf = $('#sal_pf').val();
        if (isNull(document.thisform.sal_package_name, "Salary Package...!")) {return false;}
        if (notSelected(document.thisform.sal_period, "CTC Per Day / Month...!")) {return false;}
        if (isNull(document.thisform.sal_basic, "Basic Salary value..")) {return false;}
        if (isNull(document.thisform.sal_da, "DA value..")) {return false;}
        if(hra_value==''){alert("Please Enter the HRA value...!");$('#sal_hra').focus();return false;}
        if(sal_convey ==''){alert("Please Enter the Conveyance value...!");$('#sal_convey').focus();return false;}
        if(sal_pf ==''){alert("Please Enter the PF value...!");$('#sal_pf').focus();return false;}
    }
</script>
<!-- Footer -->