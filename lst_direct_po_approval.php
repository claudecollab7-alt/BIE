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

$po_date = date("Y-m-d");


if (isset($_REQUEST['id'])) {

    $_REQUEST['po_id'] = $_REQUEST['id'];


    $result = $conn->query("SELECT * FROM  tbl_purchase_order WHERE po_id = '" . $_REQUEST['po_id'] . "'");
    if ($result->rowCount() > 0) {
        $obj = $result->fetch(PDO::FETCH_OBJ);
        $po_date = $obj->po_date;

        $po_status = '';
        if ($obj->po_status < 4) {
            if ($obj->po_status == 1) $po_status = "Draft";
            if ($obj->po_status == 2) $po_status = "In Approval";
            if ($obj->po_status == 3) $po_status = "PO Rejected";
        }
        $dis = 0;
        $dis = $dbconn->GetSingleReconrd(" tbl_purchase_order_details", "sum(tax_value)", "po_id", $_REQUEST['po_id']);



        $resSupp = $conn->query("SELECT * FROM mst_supplier_new WHERE supp_id = '" . $obj->supp_id . "'");
        if ($resSupp->rowCount() > 0) {
            $sup = $resSupp->fetch(PDO::FETCH_OBJ);
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
    <title><?php echo PAGE_TITLE; ?> - Direct Purchase Order</title>
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
                            <span class="breadcrumb-item active">Direct Purchase Order</span>
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
                                <h6 class="card-title">Purchase Order for Approval</h6>
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
                                            <th width="10px">#</th>
                                            <th>PO Code</th>
                                            <th>PO Date</th>
                                            <th>Supplier</th>
                                            <th>Items</th>
                                            <th>PO Value</th>
                                            <th width="30px" class="text-center">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                            $sql = "SELECT * FROM tbl_purchase_order WHERE po_status IN (3)";
                                            $searchRes1 = $conn->query($sql);
                                            $iSno = 1;

                                            if ($searchRes1->rowCount() > 0) {
                                                while ($rs = $searchRes1->fetch()) {
                                                    echo '<tr>';
                                                    echo '<td>' . $iSno . ' </td>';
                                                    echo '<td>' . $rs->po_refno . '</td>';
                                                    echo '<td>' . date("d-m-Y", strtotime($rs->po_date)) . '</td>';
                                                    echo '<td>' . $dbconn->GetSingleReconrd("mst_supplier_new","supp_name","supp_id",$rs->supp_id).'</td>';
                                                    echo '<td>' . $dbconn->GetCount("tbl_purchase_order_details","po_id",$rs->po_id).'</td>';
                                                    echo '<td class="text-right">' . $rs->po_value . '</td>';
                                                    echo '<td><a href="po_print.php?po_id=' . $rs->po_id . '" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit mr-2"></i></a></td>';
    
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