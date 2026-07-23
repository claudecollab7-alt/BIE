<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
// require_once ("inc/common/dbrowiterator.php");	

isAdmin();


// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$conn = new dbconnect();
$dbconn = new dbhandler();

//---------------------Approval - Reject list ---------------------//

$quo_date = date("Y-m-d");

if (isset($_REQUEST['id'])) {

    $_REQUEST['quo_id'] = $_REQUEST['id'];

    $result = $conn->query("SELECT * FROM  tbl_quotation WHERE quo_id = '" . $_REQUEST['quo_id'] . "'");
    if ($result->rowCount() > 0) {
        $obj = $result->fetch(PDO::FETCH_OBJ);
        $quo_date = $obj->quo_date;

        $quo_status = '';
        if ($obj->quo_status < 4) {
            if ($obj->quo_status == 1) $quo_status = "Draft";
            if ($obj->quo_status == 2) $quo_status = "In Approval";
            if ($obj->quo_status == 3) $quo_status = "PO Rejected";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Quotation </title>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <?php include_once("inc/common/css-js.php"); ?>
</head>

<script type="text/javascript">
    function fnValidate() {

        if (notSelected(document.thisForm.supp_id, "Supplier..!")) {
            return false;
        }
        if (isNull(document.thisForm.po_date, "Order date..!")) {
            return false;
        }

        document.thisForm.submit();
    }

    $(function() {

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
    });

</script>

<body>
    <!-- Main navbar -->
    <?php include("inc/common/header.php") ?>
    <!-- /main navbar -->

    <!-- Page content -->
    <div class="page-content">

        <!-- Main sidebar -->
        <?php include("inc/common/sidebar.php") ?>
        <!-- /main sidebar -->

        <!-- Main content -->
        <div class="content-wrapper">

            <!-- Page header -->
            <div class="page-header">
                <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                    <div class="d-flex">
                        <div class="breadcrumb">
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
                            <a href="#" class="breadcrumb-item"> Work Area</a>
                            <span class="breadcrumb-item active">Quotation Order</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>
            <!-- /page header -->

            <!-- Content area -->
            <div class="content pt-0">
                <!-- Dashboard content -->
                <div class="row">
                    <div class="col-md-12">
                        <!-- This Form UI Starts here --->

                        <div class="card">
                            <div class="card-header bg-pgheader text-white header-elements-inline">
                                <h6 class="card-title">Quotation for Approval </h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                    <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                            <table class="datatable-col7 table table-xs table-hover table-bordered" id="divisionTable">
                                    <thead>
                                    <tr class="bg-table-header">
                                            <th width="2%">#</th>
                                            <th>Ref No.</th>
                                            <th>Quotation Date</th>
                                            <th>Customer Name</th>
                                            <th>Total Items</th>
                                            <th>Quotation Value</th>
                                            <!-- <th>Follow Status</th> -->
                                            <th class="text-center" width="15%">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php

                                        $sql = "SELECT * FROM tbl_quotation WHERE quo_verify_status=1 AND quo_approve_status=0";
                                        $searchRes1 = $conn->query($sql);
                                        $iSno = 1;

                                        if ($searchRes1->rowCount() > 0) {
                                            while ($rs = $searchRes1->fetch()) {
                                                echo '<tr>';
                                                echo '<td>' . $iSno . ' </td>';
                                                echo '<td>' . $rs->quo_refno . '</td>';
                                                echo '<td>' . date("d-m-Y", strtotime($rs->quo_date)) . '</td>';
                                                echo '<td>' . $dbconn->GetSingleReconrd("mst_supplier_new","supp_name","supp_id",$rs->supp_id).'</td>';
                                                echo '<td class="text-center">' . $dbconn->GetCount("tbl_quotation_details","quo_id",$rs->quo_id).'</td>';
                                                echo '<td class="text-right">' . $rs->quo_value . '</td>';
                                                echo '<td><a href="print_quotation.php?quo_id=' . $rs->quo_id . '" data-popup="tooltip" title="Print" data-original-title="Print" ><i class="icon-printer bg-edit mr-2"></i></a></td>';

                                                echo '</tr>';
                                                $iSno++;
                                            }
                                        } 
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- End of This Form UI  --->
                    </div>
                </div>
                <!-- /dashboard content -->
            </div>
            <?php include("inc/common/footer.php") ?>
        </div>
    </div>
</body>

</html>