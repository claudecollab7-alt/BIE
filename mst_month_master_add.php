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

$_REQUEST['created_dtm'] = date('Y-m-d H:i:s');
if (isset($_POST['SAVE'])) {
    $mst_exist = $dbconn->GetSingleReconrd("tbl_month_master", "auto_id", "month_master_status = 1 AND month='" . $_REQUEST['month'] . "' AND year='" . $_REQUEST['year'] . "' AND 1", 1);

    if ($mst_exist != "") {
        $_SESSION['_msg_err'] = "Month Already Exist..!";
        header("location:mst_month_master.php");
        die();
    }

    try {
        $stmt = null;
        $stmt = $conn->prepare("INSERT INTO tbl_month_master (month, year, total_days, working_days, holidays,function_holidays,created_by,created_dtm) VALUES (:month, :year, :total_days, :working_days, :holidays, :function_holidays,:created_by,:created_dtm)");
        $data = array(
            ':month' => $_REQUEST['month'],
            ':year' => $_REQUEST['year'],
            ':total_days' => $_REQUEST['total_days'],
            ':working_days' => $_REQUEST['working_days'],
            ':holidays' => $_REQUEST['holidays'],
            ':function_holidays' => $_REQUEST['function_holidays'],
            ':created_by' =>  $_SESSION['_user_id'],
            ':created_dtm' => $_REQUEST['created_dtm']
        );
        // print_r($data);die();
        $stmt->execute($data);
        $_SESSION['_msg'] = "Month succesfully saved..!";
    } catch (Exception $e) {
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
    }

    header("location:mst_month_master.php");
    die();
}

if (isset($_POST['UPDATE'])) {
    $update_id = $_REQUEST['txtHid'];
    $mst_exist = $dbconn->GetSingleReconrd("tbl_month_master", "auto_id", "auto_id <> " . $update_id . " AND month_master_status = 1 AND month='" . $_REQUEST['month'] . "' AND year='" . $_REQUEST['year'] . "' AND 1", 1);

    if ($mst_exist != "") {
        $_SESSION['_msg_err'] = "Month Already Exist..!";
        header("location:mst_month_master.php");
        die();
    }

    try {
        $stmt = null;
        $stmt = $conn->prepare("UPDATE  tbl_month_master SET month = :month, year= :year,total_days=:total_days, working_days=:working_days, holidays=:holidays, function_holidays = :function_holidays, modify_by = :modify_by, modify_dtm = :modify_dtm
					WHERE auto_id = :auto_id");
        $data = array(
            ':auto_id' => $update_id,
            ':month' => $_REQUEST['month'],
            ':year' => $_REQUEST['year'],
            ':total_days' => $_REQUEST['total_days'],
            ':working_days' => $_REQUEST['working_days'],
            ':holidays' => $_REQUEST['holidays'],
            ':function_holidays' => $_REQUEST['function_holidays'],
            ':modify_by' => $_SESSION['_user_id'],
            ':modify_dtm' => date('Y-m-d H:i:s')
        );
        // print_r($data);die();
        $stmt->execute($data);
        // echo $stmt->fullQuery;

        $_SESSION['_msg'] = "Month succesfully Updated..!";
    } catch (Exception $e) {
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
    }

    header("location:mst_month_master.php");
    die();
}

$auto_id = "";
$month = "";
$year = "";
if (isset($_REQUEST['auto_id'])) {
    $result = $conn->query("SELECT * FROM tbl_month_master WHERE month_master_status = '1' AND auto_id = " . $_REQUEST['auto_id']);
    if ($result->rowCount() > 0) {
        $obj = $result->fetch(PDO::FETCH_OBJ);
        $auto_id = $obj->auto_id;
        $month = $obj->month;
        $year = $obj->year;
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
        <?php echo PAGE_TITLE; ?>-Month Master
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
                            <span class="breadcrumb-item active">Month Master</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>

            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">

                        <form name="thisform" class="form-horizontal" method='POST' action="mst_month_master_add.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                            <fieldset>
                                <div class="card">
                                    <div class="card-header bg-pgheader text-white header-elements-inline">
                                        <h6 class="card-title">Months</h6>
                                        <div class="header-elements">
                                            <div class="list-icons">
                                                <a class="list-icons-item" href="mst_month_master.php" title="Month Master List"><i class="icon-arrow-left52 mr-2"></i></a>
                                                <a class="list-icons-item" data-action="fullscreen"></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body" id="">
                                        <div class="form-group row">
                                            <label class="col-lg-2 col-form-label"><b>Year <span class="text-mandatory">*</span></b></label>
                                            <div class="col-lg-4">
                                                <select name="year" id="year" class="select">
                                                    <option value="">--Select Year--</option>
                                                    <?php for ($y = date('Y') - 1; $y < (date('Y') + 10); $y++) {
                                                        echo '<option value="' . $y . '">' . $y . '</option>';
                                                    } ?>
                                                </select>
                                                <script>
                                                    document.thisform.year.value = "<?php echo $year; ?>";
                                                </script>
                                            </div>
                                            <label class="col-lg-2 col-form-label"><b>Month <span class="text-mandatory">*</span></b></label>
                                            <div class="col-lg-4">
                                                <select name="month" id="month" class="select">
                                                    <option value="">--Select Month--</option>
                                                    <?php
                                                    $dbconn = new dbhandler();
                                                    echo $dbconn->fnFillComboFromTable_Where("month_id", "month_name", "mst_atten_month", "month_id", " WHERE month_status = 1") ?>
                                                </select>
                                                <script>
                                                    document.thisform.month.value = "<?php echo $month; ?>";
                                                </script>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-lg-2 col-form-label"><b>Total Days <span class="text-mandatory">*</span></b></label>
                                            <div class="col-lg-4">
                                                <input type="text" class="form-control"  onkeypress='return event.charCode >= 48 && event.charCode <= 57' maxlength="2" id="total_days" name="total_days" tabindex="-1" value="" />
                                                <script>
                                                    document.thisform.total_days.value = "<?php echo $obj->total_days; ?>";
                                                </script>
                                            </div>
                                            <label class="col-lg-2 col-form-label"><b>Working Days <span class="text-mandatory">*</span></b></label>
                                            <div class="col-lg-4">
                                                <input type="text" onkeypress='return event.charCode >= 48 && event.charCode <= 57' maxlength="2" class="form-control" id="working_days" name="working_days" tabindex="-1" value="" />
                                                <script>
                                                    document.thisform.working_days.value = "<?php echo $obj->working_days; ?>";
                                                </script>
                                            </div>
                                        </div>
                                        <div class=" form-group row">
                                            <label class="col-lg-2 col-form-label"><b>Holidays <span class="text-mandatory">*</span></b></label>
                                            <div class="col-lg-4">
                                                <input type="text" onkeypress='return event.charCode >= 48 && event.charCode <= 57' maxlength="2" class="form-control" id="holidays" name="holidays" tabindex="-1" value="" />
                                                <script>
                                                    document.thisform.holidays.value = "<?php echo $obj->holidays; ?>";
                                                </script>
                                            </div>
                                            <label class="col-lg-2 col-form-label"><b>Function Days <span class="text-mandatory">*</span></b></label>
                                            <div class="col-lg-4">
                                                <input type="text" onkeypress='return event.charCode >= 48 && event.charCode <= 57' maxlength="2" class="form-control" id="function_holidays" name="function_holidays" tabindex="-1" value="" />
                                            </div>
                                            <script>
                                                document.thisform.function_holidays.value = "<?php echo $obj->function_holidays; ?>";
                                            </script>
                                        </div>
                                    </div>
                                    <div class="card-footer text-center">
                                        <?php if ($_REQUEST["auto_id"] != '') { ?>
                                            <INPUT class="btn btn-custom" type="submit" name="UPDATE" id="submit" value="Update">
                                            <INPUT class="btn btn-light" type="button" name="Cancel" value="Cancel" onClick="javascript:window.location.href='mst_month_master.php'">
                                            <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['auto_id']; ?>">
                                        <?php } else { ?>
                                            <INPUT class="btn btn-custom" type="submit" name="SAVE" id="submit" value="Save">
                                            <INPUT class="btn btn-light" type="button" name="Cancel" value="Cancel" onClick="javascript:window.location.href='mst_month_master.php'">
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
// $().click(function)
   

    // 
    $(document).ready(function() {

        // var year = $("#year").val();
        // var month = $("#month").val();

        // 
        // Selectors for year and month
        var year = $("#year");
        //   alert(year);
        var month = $("#month");

        // Event handler for when the year or month selection changes
        year.on("change", updateNumberOfDays);

        // $('#month').trigger("select");

        month.on("change", updateNumberOfDays);

        // Function to update the number of days
        function updateNumberOfDays() {
           
            // Get the selected year and month
            var updateyear = year.val();
            var updatemonth = month.val();

            if(updateyear!='' && updatemonth!=''){
            // $('#month').trigger("select");
            // Create a new Date object for the selected year and month
            var date = new Date(updateyear, updatemonth, 1);

            // Set the date to the last day of the month
            date.setMonth(date.getMonth());
            date.setDate(date.getDate() - 1);

            // Get the number of days in the month
            var numberOfDays = date.getDate();

            // Update the UI with the number of days
            $("#total_days").val(numberOfDays);
            };
        }
    // };
    });
// };


    /*$("#submit").click(function() {
        let totaldays = $('#total_days').val();

        let workingdays = parseFloat($('#working_days').val());
        if (isNaN(workingdays)) workingdays = 0;

        var holidays = parseFloat($('#holidays').val());
        if (isNaN(holidays)) holidays = 0;

        var functiondays = parseFloat($('#function_holidays').val());
        if (isNaN(functiondays)) functiondays = 0;

        var totalcal = workingdays + holidays + functiondays;

        if (totaldays != totalcal) {
            alert("(Working Days + Holidays + Function Holidays) Should Be Same Total days");

            $("#function_holidays").val('').trigger('change')
            $("#holidays").val('').trigger('change')
            $("#working_days").val('').trigger('change')

            return false;

        }
    });*/



    function fnValidate() {
        if (notSelected(document.thisform.year, "Year..")) {
            return false;
        }
        if (notSelected(document.thisform.month, "Month..")) {
            return false;
        }
        if (isNull(document.thisform.total_days, "Total Days..")) {
            return false;
        }
        if (isNull(document.thisform.working_days, "Working Days..")) {
            return false;
        }
        if (isNull(document.thisform.holidays, "Holidays..")) {
            return false;
        }
        /*if (isNull(document.thisform.function_holidays, "Function Holidays..")) {
            return false;
        }*/
        document.thisform.submit();
    }
</script>
<!-- Footer -->