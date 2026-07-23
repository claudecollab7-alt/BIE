<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");

isAdmin();
$conn = new dbconnect();
$dbconn = new dbhandler();


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <title>
        <?php echo PAGE_TITLE; ?> - Shift Wise
    </title>
    <!-- <link href="css/main.css" rel="stylesheet" type="text/css" /> -->
    <?php include_once("inc/common/css-js.php"); ?>
</head>

<body>
    <!-- Main navbar -->
    <?php include("inc/common/header.php") ?>
    <!-- /main navbar -->
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
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Home</a>
                            <a href="#" class="breadcrumb-item"> HR Management</a>
                            <span class="breadcrumb-item active">Shift Wise</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>
            <!-- Content area -->
            <div class="content pt-0">
                <!-- Dashboard content -->
                <div class="row">
                    <div class="col-md-12">
                        <!-- This Form UI Starts here --->
                        <!-- Basic datatable -->
                        <div class="card">
                            <div class="card-header bg-pgheader text-white header-elements-inline">
                                <h6 class="card-title">Shift Wise</h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body ">
                                <table class="table table-xs table-hover table-bordered mt-0 " id="emp_sal">
                                    <thead>
                                        <tr class="bg-table-header">
                                            <th>#</th>
                                            <th>Shift Name</th>
                                            <th>Check In</th>
                                            <th>Check Out</th>
                                            <th>Check In Start</th>
                                            <th>Check Out End</th>
                                            <th>Check Out Start</th>
                                            <th>Check In End</th>
                                            <th>Duration</th>
                                            <th class="text-center" width="10%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $shift = $conn->query(" SELECT * FROM mst_shifts WHERE status=1");
                                        if ($shift->rowCount() > 0) {
                                            $sno = 1;
                                            while ($obj = $shift->fetch(PDO::FETCH_OBJ)) {
                                                echo '<tr>
                                                <td>' . $sno . '</td>
                                                <td>' . $obj->shift_name . '</td>
                                                <td>' . $obj->check_in . '</td>
                                                <td>' . $obj->check_out . '</td>
                                                <td>' . $obj->check_in_start . '</td>
                                                <td>' . $obj->check_in_end . '</td>
                                                <td>' . $obj->check_out_start . '</td>
                                                <td>' . $obj->check_out_end . '</td>
                                                <td>' . $obj->duration . " Minutes" . '</td>
                                                <td style=text-align:center;><a href="shift_wise_time_setting.php?shift_id=' . $obj->shift_id . '" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit mr-2"></i></a></td>
                                            </tr>';
                                                $sno++;
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <input type="hidden" name="iPageNum" value="<?php echo $iPageNum ?>">
                            </div>
                        </div>
                        <!-- /basic datatable -->
                        <!-- End of This Form UI  --->
                    </div>
                </div>
                <!-- /dashboard content -->
            </div>
            <!-- /content area -->
            <!-- Footer -->
            <?php include("inc/common/footer.php") ?>
            <!-- /footer -->
        </div>
        <!-- /main content -->
    </div>
    <!-- /page content -->
</body>

</html>
<script language="javascript" type="text/javascript">
    $(function() {
        <?php
        if (isset($_SESSION['_msg']) && $_SESSION['_msg'] != "") {
            echo "$.jGrowl('" . $_SESSION['_msg'] . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-success', position: 'top-right', life:'2000', header: 'Success!' });";
            $_SESSION['_msg'] = "";
        }
        if (isset($_SESSION['_msg_err']) && $_SESSION['_msg_err'] != "") {
            echo "$.jGrowl('" . $_SESSION['_msg_err'] . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-danger', position: 'top-right', shutdown:'3000', header: 'Error!' });";
            $_SESSION['_msg_err'] = "";
        }
        ?>
    });

      

</script>