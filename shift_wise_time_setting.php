<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");
require_once("inc/common/css-js.php");

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

if (isset($_POST['UPDATE'])) {
    try {
        $update_id = $_REQUEST['txtHid'];
        $stmt = null;
        $stmt = $conn->prepare("UPDATE  mst_shifts SET shift_name = :shift_name, check_in= :check_in,check_out=:check_out, check_in_start=:check_in_start, check_in_end=:check_in_end, check_out_start = :check_out_start,check_out_end = :check_out_end, duration = :duration
					WHERE shift_id = :shift_id");
        $data = array(
            ':shift_id' => $update_id,
            ':shift_name' => $_REQUEST['shift_name'],
            ':check_in' => $_REQUEST['check_in'],
            ':check_out' => $_REQUEST['check_out'],
            ':check_in_start' => $_REQUEST['check_in_start'],
            ':check_in_end' => $_REQUEST['check_in_end'],
            ':check_out_start' => $_REQUEST['check_out_start'],
            ':check_out_end' => $_REQUEST['check_out_end'],
            ':duration' => $_REQUEST['duration'],
            // ':modify_by' => $_SESSION['_user_id'],
            // ':modify_dtm' => date('Y-m-d H:i:s')
        );
        // print_r($data);die();
        $stmt->execute($data);
        // echo $stmt->fullQuery;

        $_SESSION['_msg'] = "Shift succesfully Updated..!";
    } catch (Exception $e) {
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
    }

    header("location:shift_wise.php");
    die();
}

$shift_id = "";
$year = "";
if (isset($_REQUEST['shift_id'])) {
    $result = $conn->query("SELECT * FROM mst_shifts WHERE status = 1 AND shift_id ='" . $_REQUEST['shift_id'] . "'");

    // print_r($result );die();
    if ($result->rowCount() > 0) {
        $obj = $result->fetch(PDO::FETCH_OBJ);
        $shift_id = $obj->shift_id;
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
        <?php echo PAGE_TITLE; ?>-Shift Wise
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
                            <a href="#" class="breadcrumb-item">HR Management</a>
                            <span class="breadcrumb-item active">Shift Wise</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>

            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">

                        <form name="thisform" class="form-horizontal" method='POST' action="shift_wise_time_setting.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                            <fieldset>
                                <div class="card">
                                    <div class="card-header bg-pgheader text-white header-elements-inline">
                                        <h6 class="card-title">Shfit Wise</h6>
                                        <div class="header-elements">
                                            <div class="list-icons">
                                                <a class="list-icons-item" href="shift_wise.php" title="Shift Wise List"><i class="icon-arrow-left52 mr-2"></i></a>
                                                <a class="list-icons-item" data-action="fullscreen"></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body " id="">
                                        <div class="form-group row pt-2">
                                            <label class="col-lg-2 col-form-label"><b>Shift <span class="text-mandatory">*<span></b></label>
                                            <div class="col-lg-4">
                                                <input type="text" class="form-control" readonly id="shift_name" name="shift_name" tabindex="-1" value="<?php echo $obj->shift_name; ?>" />
                                            </div>
                                            <label class="col-lg-2 col-form-label"><b>Duration </b></label>
                                            <div class="col-lg-4">
                                                <input type="text" readonly class="form-control" id="duration" name="duration" tabindex="-1" value="" />
                                            </div>
                                            <script>
                                                document.thisform.duration.value = "<?php echo $obj->duration; ?>";
                                            </script>
                                        </div>
                                        <div class="form-group row pt-2">
                                            <label class="col-lg-2 col-form-label"><b>Check In Time <span class="text-mandatory">*</span></b></label>
                                            <div class="col-lg-4">
                                                <input type="time" class="form-control" id="check_in" name="check_in" tabindex="-1" value="" />
                                            </div>
                                            <script>
                                                document.thisform.check_in.value = "<?php echo $obj->check_in; ?>";
                                            </script>
                                            <label class="col-lg-2 col-form-label"><b>Check Out Time <span class="text-mandatory">*</span></b></label>
                                            <div class="col-lg-4">
                                                <input type="time" class="form-control" id="check_out" name="check_out" tabindex="-1" value="" />
                                            </div>
                                            <script>
                                                document.thisform.check_out.value = "<?php echo $obj->check_out; ?>";
                                            </script>
                                        </div>
                                        <div class=" form-group row pt-2">
                                            <label class="col-lg-2 col-form-label"><b>Check In Start <span class="text-mandatory">*</span></b></label>
                                            <div class="col-lg-4">
                                                <input type="time" class="form-control" id="check_in_start" name="check_in_start" tabindex="-1" value="" />
                                                <script>
                                                    document.thisform.check_in_start.value = "<?php echo $obj->check_in_start; ?>";
                                                </script>
                                            </div>
                                            <label class="col-lg-2 col-form-label"><b>Check In End <span class="text-mandatory">*</span></b></label>
                                            <div class="col-lg-4">
                                                <input type="time" class="form-control" id="check_in_end" name="check_in_end" tabindex="-1" value="" />
                                                <script>
                                                    document.thisform.check_in_end.value = "<?php echo $obj->check_in_end; ?>";
                                                </script>
                                            </div>
                                        </div>
                                        <div class=" form-group row pt-2">
                                            <label class="col-lg-2 col-form-label"><b>Check Out Start <span class="text-mandatory">*</span></b></label>
                                            <div class="col-lg-4">
                                                <input type="time" class="form-control" id="check_out_start" name="check_out_start" tabindex="-1" value="" />
                                                <script>
                                                    document.thisform.check_out_start.value = "<?php echo $obj->check_out_start; ?>";
                                                </script>
                                            </div>
                                            <label class="col-lg-2 col-form-label"><b>Check Out End <span class="text-mandatory">*</span></b></label>
                                            <div class="col-lg-4">
                                                <input type="time" class="form-control" id="check_out_end" name="check_out_end" tabindex="-1" value="" />
                                                <script>
                                                    document.thisform.check_out_end.value = "<?php echo $obj->check_out_end; ?>";
                                                </script>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer text-center">
                                        <INPUT class="btn btn-info" type="submit" name="UPDATE" id="submit" value="Update">
                                        <INPUT class="btn btn-light" type="button" name="Cancel" value="Cancel" onClick="javascript:window.location.href='shift_wise.php'">
                                        <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['shift_id']; ?>">
                                        <!--  -->
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

<!-- <script language="javascript" type="text/javascript">

$('#check_in').change (function(){
    var check_in = $('#check_in').val();
    var check_in_start = $('#check_in_start').val();
    // alert(check_in);
    if(check_in < check_in_start ){
        alert("Check In Time Should be Less  then Check In Start Time... ! ");
    }

    })

</script> -->
<!-- Footer -->