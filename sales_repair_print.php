<?php
ob_start();
session_start();
require_once("inc/common/userclass.php");
isAdmin();

$conn  = new dbconnect();
$dbconn = new dbhandler();

if (isset($_REQUEST['sal_repair_id']) && $_REQUEST['sal_repair_id'] != '') {

    $result = $conn->query("SELECT * FROM tbl_sales_repair WHERE sal_repair_id = " . $_REQUEST['sal_repair_id']);
    if ($result->rowCount() > 0) {
        $obj = $result->fetch(PDO::FETCH_OBJ);

        $supp_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_status = 1 AND supp_id", $obj->supp_id);
        $supp_code = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_code", "supp_status = 1 AND supp_id", $obj->supp_id);

        if ($obj->sal_repair_date != "0000-00-00" && $obj->sal_repair_date != "") {
            $sal_repair_date = date("d-m-Y", strtotime($obj->sal_repair_date));
        }
    }

    $get_add = $conn->query("SELECT * FROM mst_supplier_new WHERE supp_id = " . $obj->supp_id);
    if ($get_add->rowCount() > 0) {
        $obj1 = $get_add->fetch(PDO::FETCH_OBJ);
        $add  = $obj1->supp_add1;
        if ($obj1->supp_add2 != "") {
            $add .= ', ' . $obj1->supp_add2;
        }
        $add .= ', <br/>' . $dbconn->GetSingleReconrd("mst_city",     "city_name",     "city_status = 1 AND city_id",         $obj1->city_id);
        $add .= ', '      . $dbconn->GetSingleReconrd("mst_district",  "district_name", "district_status = 1 AND district_id", $obj1->district_id);
        $add .= ', <br/>' . $dbconn->GetSingleReconrd("mst_state",     "state_name",    "state_status = 1 AND state_id",       $obj1->state_id);
        $add .= ' - '     . $obj1->supp_pincode . '.';
    }

    // $approve_dept = $dbconn->GetSingleReconrd("mst_task_setting", "approve_department_id", "task_id", 12);
    // $created_dept = $dbconn->GetSingleReconrd("mst_task_setting", "department_id",         "task_id", 12);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Sales Repair</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <?php include_once("inc/common/css-js.php"); ?>
</head>

<script type="text/javascript" src="print_me.js"></script>
<script src="js/html2pdf.bundle.min.js"></script>

<script language="javascript">
$(function() {
    $("body").on("click", "#cmd", function() {
        var element = document.getElementById('print_content1');
        var opt = {
            margin:     0.5,
            filename:   '<?php echo leadingZeros($obj->sal_repair_slno, 3); ?>.pdf',
            image:      { type: 'jpeg', quality: 1 },
            html2canvas:{ scale: 2, logging: true },
            jsPDF:      { unit: 'cm', format: 'A4', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save();
    });
});
</script>

<script type="text/javascript">
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

    $('#sal_repair_verify').on('click', function(e) {
        e.preventDefault();
        if (confirm("Are you sure, you want to Send this Sales Repair to Approval?")) {
            var id = $(this).attr('data-id');
            $.ajax({
                type: 'post',
                url: 'inc/cis_ajax/jquery_sales_repair_status.php',
                data: { "id": id, "mode": "verify" },
                complete: function() {
                    $.jGrowl('Sales Repair details has been verified..!', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-success', position: 'bottom-right', life: '2000', header: 'Success!' });
                },
                success: function(result) { location.reload(); }
            });
        }
    });

    $('#sal_repair_approve').on('click', function(e) {
        e.preventDefault();
        if (confirm("Are you sure, you want to approve this Sales Repair?")) {
            var id = $(this).attr('data-id');
            $.ajax({
                type: 'post',
                url: 'inc/cis_ajax/jquery_sales_repair_status.php',
                data: { "id": id, "mode": "approve" },
                complete: function() {
                    $.jGrowl('Sales Repair details has been approved..!', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-success', position: 'bottom-right', life: '2000', header: 'Success!' });
                },
                success: function(result) { location.reload(); }
            });
        }
    });

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
                            <a href="#" class="breadcrumb-item">Sales</a>
                            <a href="repair_indent_list.php" class="breadcrumb-item">Sales Repair</a>
                            <span class="breadcrumb-item active"><?php echo leadingZeros($obj->sal_repair_slno, 3); ?></span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>
            <!-- /page header -->

            <!-- Content area -->
            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">

                        <div class="card">
                            <div class="card-header bg-pgheader text-white header-elements-inline">
                                <h6 class="card-title">Sales Repair Details - <?php echo leadingZeros($obj->sal_repair_slno, 3); ?></h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" href="repair_indent_list.php" title="Sales Repair List"><i class="icon-arrow-left52 mr-2"></i></a>
                                        <a class="list-icons-item" href="javascript:PrintPartsNew(new Array('print_content1'),'<?php echo leadingZeros($obj->sal_repair_slno, 3); ?>');" title="Print"><i class="icon-printer2 mr-1"></i></a>
                                        <a class="list-icons-item" id="cmd" href="javascript:;" title="PDF"><i class="icon-file-pdf mr-2"></i></a>
                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="invoice" id="print_content1" style="width:100%;">

                                        <table class="table table-xs table-bordered po_print_table mt-0 pt-0">
                                            <thead>
                                                <tr>
                                                    <td colspan="10">
                                                        <span style="float:right;"><img src="img/BIE_logo.png" alt="" width="50px" height="auto"></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="center" colspan="10" style="font-size:20px; font-weight:bold; background-color:#bebebe;">Sales Repair</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" align="left"><b>Repair No. : </b><?php echo leadingZeros($obj->sal_repair_slno, 3); ?></td>
                                                    <td colspan="2"></td>
                                                    <td colspan="4" align="right"><b>Repair Date : </b><?php echo $sal_repair_date; ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" align="left"><b>Supplier : </b><?php echo $supp_name; ?></td>
                                                    <td colspan="3" align="left"><b>Reference No : </b><?php echo $obj->indent_po_refno; ?></td>
                                                    <td colspan="4" align="left"><b>Address : </b><?php echo $add; ?></td>
                                                </tr>
                                                <tr style="font-weight:bold; background-color:#bebebe;" class="align-center uppercase">
                                                    <td align="center" width="3%">#</td>
                                                    <td align="left"  width="20%">Repair Item</td>
                                                    <td align="left"  width="20%">Item Code</td>
                                                    <td align="left"  width="10%">Remarks</td>
                                                    <td align="center" width="8%">Qty</td>
                                                    <td align="center" width="7%">Unit</td>
                                                    <td align="right"  width="10%">Unit Price</td>
                                                    <td align="right"  width="10%">Value</td>
                                                    <td align="right"  width="7%">Tax (%)</td>
                                                    <td align="right"  width="5%">Total</td>
                                                </tr>
                                            </thead>

                                            <tbody>
                                            <?php
                                                $posql = "SELECT * FROM tbl_sales_repair
                                                          LEFT JOIN tbl_sales_repair_details
                                                            ON tbl_sales_repair.sal_repair_id = tbl_sales_repair_details.sal_repair_id
                                                          WHERE tbl_sales_repair.sal_repair_id = '" . $_REQUEST['sal_repair_id'] . "'";

                                                $result  = $conn->query($posql);
                                                $iSno    = 1;
                                                $netTotal = 0;
                                                $tr_class = '';

                                                if ($result->rowCount() > 0) {
                                                    while ($pr = $result->fetch()) {

                                                        $repair_item_name = $dbconn->GetSingleReconrd("tbl_item_details",
                                                            "item_desciption",
                                                            "item_status = '1' AND item_id", $pr->repair_item_id);

                                                        $spare_item_name = $dbconn->GetSingleReconrd("tbl_item_details",
                                                            "item_code",
                                                            "item_status = '1' AND item_id", $pr->repair_item_id);

                                                        $repair_remarks = "";

                                                        echo '<tr ' . $tr_class . ' valign="top">
                                                                <td class="text-center">'  . $iSno . '</td>
                                                                <td class="text-left">'    . $repair_item_name . '</td>
                                                                <td class="text-left">'    . $spare_item_name . '</td>
                                                                <td class="text-left">'    . $repair_remarks . '</td>
                                                                <td class="text-center">'  . $pr->repair_qty . '</td>
                                                                <td class="text-center">'  . $pr->repair_unit . '</td>
                                                                <td class="text-right">'   . number_format($pr->repair_selling_price, 2) . '</td>
                                                                <td class="text-right">'   . number_format($pr->repair_value, 2) . '</td>
                                                                <td class="text-right">'   . number_format($pr->repair_tax, 2) . '</td>
                                                                <td class="text-right">'   . number_format($pr->repair_net_val, 2) . '</td>
                                                              </tr>';

                                                        $netTotal += $pr->repair_net_val;
                                                        $tr_class  = '';
                                                        $iSno++;
                                                    }
                                                }

                                                $no_items     = $iSno;
                                                $items_height = $no_items * 250;
                                                $height       = ($items_height < 500) ? (500 - $items_height) : 10;

                                                echo '<tr valign="top">
                                                        <td><p style="min-height:' . $height . 'px;">&nbsp;</p></td>
                                                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                                                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                                                      </tr>';

                                                $netTotal      = number_format($netTotal, 0, ".", "");
                                                $rupees_words  = 'Rupees ' . ucwords(number_to_words($netTotal)) . ' Only';

                                                echo '<tr>
                                                        <td colspan="8" align="left"><strong>Amount in Words : </strong>' . $rupees_words . '</td>
                                                        <td align="right"><strong>Grand Total</strong></td>
                                                        <td align="right"><strong>' . number_format($netTotal, 2) . '</strong></td>
                                                      </tr>';
                                            ?>
                                            </tbody>

                                            <tfoot>
                                                <tr>
                                                    <td class="text-center" colspan="2">
                                                        <p>Prepared By</p>
                                                        <p>&nbsp;</p><p>&nbsp;</p>
                                                        <p>
                                                            <?php
                                                            echo $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id", $obj->modify_by)
                                                                 . '<br/> On ' . date("d-m-Y", strtotime($obj->modify_date_time));
                                                            ?>
                                                        </p>
                                                    </td>
                                                    <td class="text-center" colspan="4">
                                                        <p>&nbsp;</p><p>&nbsp;</p>
                                                        <p>
                                                        <?php
                                                        if ($obj->sal_repair_verify_status == 1) {
                                                            echo '<p>Send for Approval By</p>';
                                                            echo $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id", $obj->sal_repair_verify_by)
                                                                 . '<br/> On ' . date("d-m-Y", strtotime($obj->sal_repair_verify_date_time));
                                                        } elseif ($_SESSION['_user_id'] == 1) { ?>
                                                            <a href="javascript:void(0)" id="sal_repair_verify"
                                                               data-id="<?php echo $obj->sal_repair_id; ?>">
                                                                <span class="badge badge-warning">Send for Approval</span>
                                                            </a>
                                                        <?php } ?>
                                                        </p>
                                                    </td>
                                                    <td class="text-center" colspan="4">
                                                        <p>&nbsp;</p><p>&nbsp;</p>
                                                        <p>
                                                        <?php
                                                        if ($obj->sal_repair_approve_status == 1) {
                                                            echo '<p>Approved By</p>';
                                                            echo $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id", $obj->sal_repair_approve_by)
                                                                 . '<br/> On ' . date("d-m-Y", strtotime($obj->sal_repair_approve_date_time));
                                                        } elseif ($obj->sal_repair_verify_status == 1
                                                               && $obj->sal_repair_approve_status == 0
                                                               && ($_SESSION['_user_id'] == 1)) { ?>
                                                            <a href="javascript:void(0)" id="sal_repair_approve"
                                                               data-id="<?php echo $obj->sal_repair_id; ?>">
                                                                <span class="badge badge-warning">Approve Now</span>
                                                            </a>
                                                        <?php } else { echo '&nbsp;'; } ?>
                                                        </p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="center" colspan="10" style="padding-top:80px;">
                                                        <b>For Benzear., &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Authorized Signatory</b>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="center" style="background-color:#bebebe;" colspan="10">
                                                        <b>www.benzear-bie.com</b>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>

                                    </div><!-- /invoice -->
                                </div>
                            </div><!-- /card-body -->
                        </div><!-- /card -->

                        <!-- Created / Modified row -->
                        <div class="row">
                            <div class="text-left col-lg-6">
                                <div class="rec_create_dets">
                                    <b>Created by : </b>
                                    <?php echo $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id", $obj->modify_by)
                                              . ' on ' . date('d-M-y @ H:i', strtotime($obj->modify_date_time)); ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div><!-- /content -->

            <?php include("inc/common/footer.php") ?>
        </div><!-- /content-wrapper -->
    </div><!-- /page-content -->

</body>
</html>