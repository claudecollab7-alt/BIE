<?php
ob_start();
session_start();
require_once("inc/common/userclass.php");
isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();


// 2026-10-14 customer branch read from cus_branch_id - it used branch_id (our branch, 1 = head office), so every head office invoice showed customer branch #1
// 2026-10-26 customer names and invoice / pack totals read once for the report, not 6-8 queries per invoice
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);


$from_dt = $dbconn->GetSingleReconrd("mst_finyear", "finyr_startdt", "finyr_active", 1);
if (isset($_REQUEST['from_dt']) && $_REQUEST['from_dt'] != '') {
    $from_date = $_REQUEST['from_dt'];
}
$to_dt   = $dbconn->GetSingleReconrd("mst_finyear", "finyr_enddt", "finyr_active", 1);
if (isset($_REQUEST['to_dt']) && $_REQUEST['to_dt'] != '') {
    $to_date = $_REQUEST['to_dt'];
}

if ($_REQUEST['branch_id'] == '')
    $branch = 1;
else
    $branch = $_REQUEST['branch_id'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Invoice - Report</title>

    <?php include_once("inc/common/css-js.php"); ?>

    <script type="text/javascript" src="print_me.js"></script>
    <script src="js/jquery.table2excel.min.js"></script>
    <script src="js/html2pdf.bundle.min.js"></script>



    <script>
        $(function() {

            $(".rpt_export").click(function(e) {
                var table = $('#lst_table');
                if (table && table.length) {
                    $(table).table2excel({
                        exclude: ".noExl",
                        name: "SR",
                        filename: "SR" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xls",
                        fileext: ".xls",
                        exclude_img: true,
                        exclude_links: true,
                        exclude_inputs: true,
                        preserveColors: true,
                    });
                }
            });

            $(".rpt_pdf").click(function(e) {
                var element = document.getElementById('rpt_division');
                var opt = {
                    margin: 1,
                    filename: '<?php echo "SR" . date("dMY"); ?>' + '.pdf',
                    image: {
                        type: 'jpeg',
                        quality: 1
                    },
                    html2canvas: {
                        scale: 2,
                        logging: true
                    },
                    jsPDF: {
                        unit: 'cm',
                        format: 'A4',
                        orientation: 'portrait'
                    }
                };
                html2pdf().set(opt).from(element).save();
            });
        });
    </script>

</head>

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
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Home</a>
                            <a href="#" class="breadcrumb-item"> Report</a>
                            <span class="breadcrumb-item active">Invoice</span>
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
                        <!-- Basic datatable -->
                        <div class="card">

                            <div class="card-header bg-pgheader text-white header-elements-inline">
                                <h6 class="card-title">Invoice - Report </h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>

                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>


                            <form name='thisForm' id="validate" method='post' action="" onSubmit="return fnValidate();">

                                <div class="card-body pt-2 pb-5">
                                    <div class="form-group row">


                                        <div class="form-group col-md-2">
                                            <label>From Date</label>
                                            <input type="date" class="form-control" name='from_dt' id='from_dt' value="<?php echo  $from_dt; ?>">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>To Date</label>
                                            <input type="date" class="form-control" name='to_dt' id='to_dt' value="<?php echo  $to_dt; ?>">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Customer</label>
                                            <select name="supp_id" id="supp_id" class="form-control select-search">
                                                <option value="">-- All Customer --</option>
                                                <?php
                                                $dbconn = new dbhandler();
                                                echo $dbconn->fnFillComboFromTable_Where("supp_id", "CONCAT(supp_name,' - ',supp_add2)", "mst_supplier_new", "supp_name", " WHERE supp_status = '1' AND supp_type = 'C'");
                                                ?>
                                            </select>
                                            <script>
                                                document.getElementById('supp_id').value = "<?php echo $_REQUEST['supp_id']; ?>";
                                            </script>
                                        </div>
                                        <div class="form-group col-md-1">
                                            <label>Invoice No</label>
                                            <input type="text" class="form-control" name='keyword' id='keyword' placeholder="Invoice No" value="<?php echo $_REQUEST['keyword']; ?>" autocomplete="off">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>All Branches</label>
                                            <select name="branch_id" id="branch_id" class="form-control select-search">

                                                <?php
                                                echo $dbconn->fnFillComboFromTable_Where("branch_id", "branch_name", "mst_branch", "branch_id", " WHERE branch_status = 1");
                                                ?>
                                            </select>
                                            <script>
                                                document.getElementById('branch_id').value = "<?php echo $branch; ?>";
                                            </script>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <button class="btn btn-info mt-4" name="Report" value="Report" type="submit">
                                                <i class="icon-statistics mr-1"></i>Generate Report</button>
                                        </div>
                                    </div>
                                    <hr>


                                    <?php

                                    if (isset($_REQUEST['Report'])) {

                                        $branch_name =  $dbconn->GetSingleReconrd("mst_branch", "branch_name", "branch_id", $_REQUEST['branch_id']);


                                        if ($_REQUEST['supp_id'] != '')
                                            $customer = "<span style='color:green;'> | " . $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_id", $_REQUEST['supp_id']) . '</span>';

                                        else
                                            $customer = '';
                                        if ($_REQUEST['keyword'] != '')
                                            $keyword = " | " . $_REQUEST['keyword'];
                                        else
                                            $keyword = '';


                                        //------  PRINT  ------//

                                        echo '<div class="col-md-12 text-right">	
                                        <a href="javascript:" class="rpt_export">
                                        <button type="button" class="btn btn-sm btn-light" ><i class="icon-file-excel mr-s"></i> Excel</button></a>

                                        <a href="javascript:" class="rpt_pdf">
                                        <button type="button" class="buttons-html5 btn btn-light" >
                                        <i class="icon-file-pdf mr-1"></i> PDF</button>
                                        </a>
                                                          
                                        <a href="javascript:PrintPartsNew(new Array(\'rpt_division\'),\'Invoice - Report\');" class="rpt_print">
                                        <button type="button" class="btn btn-sm btn-light" ><i class="fas fa-print mr-2""></i> Print</button></a>
                                        </div>';

                                        //------  PRINT  ------//

                                        echo ' 
                                    <div class="invoice" id="print_content1" style="width:100%;">
                                    <div class="table-responsive" style="width:100%" id="payment_tbl"><br>';

                                        echo '<table class="table table-xs invoice_tbl" id="lst_table">
                                           
                                    <thead>
                                    <tr>
                                        <th colspan="7" style="text-align: center;">
                                        <span style="font-size: 18px; color: #463ece;">Invoice Details</span><br/>' . date("d-M-Y", strtotime($_REQUEST['from_dt'])) . ' - ' . date("d-M-Y", strtotime($_REQUEST['to_dt'])) . '' . $customer . ' ' . $keyword . '
                                        </th>
                                    </tr>

                                    <tr  class="rpt_heading">
                                        <th width="5px">#</th>
                                        <th style="text-align:center;">Invoice No.</th>
                                        <th style="text-align:center;">Invoice Date</th>
                                        <th>Customer Name</th>
                                        <th style="text-align:center;">Grand Total</th> 
                                    </tr>

                                </thead>
                                <tbody>';

                                        $SQL = "SELECT *,a.supp_id as supp_id, b.supp_name FROM tbl_invoice AS a LEFT JOIN mst_supplier_new AS b ON b.supp_id = a.supp_id  WHERE a.inv_status = '1'";

                                        if ($_REQUEST['keyword'] != "") {
                                            $SQL .= " AND (a.inv_slno = '" . $_REQUEST['keyword'] . "' )";
                                        }

                                        if ($_REQUEST['supp_id'] != "") {
                                            $SQL .= " AND (a.supp_id ='" . $_REQUEST['supp_id'] . "') ";
                                        }
                                        if (($_REQUEST['branch_id']) != '') {
    
                                            $SQL .= " AND (a.branch_id =" . $_REQUEST['branch_id'].") ";
                                        }


                                        if ($_REQUEST['from_dt'] != '' || $_REQUEST['from_dt'] == '0000-00-00' && $_REQUEST['to_dt'] != "" || $_REQUEST['to_date'] == '0000-00-00') {

                                            $SQL .= "AND a.inv_date BETWEEN '" . date('Y-m-d', strtotime($_REQUEST['from_dt'])) . "' AND '" . date('Y-m-d', strtotime($_REQUEST['to_dt'])) . "' ";
                                        }
                                        $SQL .= " ORDER BY a.inv_date DESC";



                                        $result = $conn->query($SQL);
                                        $inv_rows = $result->fetchAll();

                                        // names and invoice / pack totals for all the invoices listed, one query each
                                        $inv_ids = $supp_ids = $cbr_ids = array(0);
                                        foreach ($inv_rows as $r) {
                                            $inv_ids[] = (int)$r->inv_id;
                                            $supp_ids[] = (int)$r->supp_id;
                                            $cbr_ids[] = (int)$r->cus_branch_id;
                                        }
                                        $in = implode(',', array_unique($supp_ids));
                                        $m_supp_name = $dbconn->GetMap("SELECT supp_id, supp_name FROM mst_supplier_new WHERE supp_id IN ($in)");
                                        $m_supp_add2 = $dbconn->GetMap("SELECT supp_id, supp_add2 FROM mst_supplier_new WHERE supp_id IN ($in)");
                                        $in = implode(',', array_unique($cbr_ids));
                                        $m_cbr_name = $dbconn->GetMap("SELECT branch_id, branch_name FROM mst_customer_branch WHERE branch_id IN ($in)");
                                        $m_cbr_add2 = $dbconn->GetMap("SELECT branch_id, branch_add2 FROM mst_customer_branch WHERE branch_id IN ($in)");
                                        $in = implode(',', array_unique($inv_ids));
                                        $m_net = $dbconn->GetMap("SELECT inv_id, SUM(net_value) FROM tbl_invoice_details WHERE inv_id IN ($in) GROUP BY inv_id");
                                        $m_pack = $dbconn->GetMap("SELECT inv_id, SUM(inv_pack_total) FROM tbl_invoice_pack_details WHERE inv_id IN ($in) GROUP BY inv_id");

                                        if ($result->rowCount() > 0) {
                                            $Sno = 1;
                                            foreach ($inv_rows as $obj) {

                                                if ($obj->supp_id != '0') {
                                                    $buss_name = $dbconn->MapValue($m_supp_name, $obj->supp_id);
                                                    $add2 = ' - ' . $dbconn->MapValue($m_supp_add2, $obj->supp_id);
                                                } else {
                                                    // invoice without a customer - left as it was (rare)
                                                    $buss_name = $dbconn->GetSingleReconrd("tbl_enquiry", "enq_buss_name", "enq_id", $obj->enq_id);
                                                }

                                                if ($obj->cus_branch_id > 0) {
                                                    $branch_name = $dbconn->MapValue($m_cbr_name, $obj->cus_branch_id);
                                                    $add2 = ' - ' . $dbconn->MapValue($m_cbr_add2, $obj->cus_branch_id);
                                                    $cus_details = $buss_name . '<br/><b>Branch Name: </b>' . $branch_name . $add2;
                                                } else {
                                                    $cus_details = '<b>' . $buss_name . '</b>';
                                                }


                                                $net_value = $dbconn->MapValue($m_net, $obj->inv_id, null);
                                                $inv_pack_val = $dbconn->MapValue($m_pack, $obj->inv_id, null);



                                                if ($obj->inv_id > 0) {

                                                    $invoice_link = '<a href="invoice_print.php?inv_id=' . $obj->inv_id . '" target="_blank">' .
                                                        $obj->inv_refno . '</a>';
                                                } else {
                                                    $invoice_link = '';
                                                }

                                                $inv_grand_tot = $net_value + $inv_pack_val;
                                                echo '<tr style="background-color:#bcedc88f">

                                                             <td>' . $Sno . '</td>
                                                             <td align="center">' . $invoice_link . '</td>
                                                             <td align="center">' . date("d-m-Y", strtotime($obj->inv_date)) . '</td>
                                                             <td align="left">' .  strtoupper($cus_details)  . '</td>
                                                             <td align="right">' . number_format(round($inv_grand_tot), 2, ".", "") . '</td>
                                                     
                                                        </tr>';

                                                $total_inv_value += $inv_grand_tot;
                                                $Sno++;
                                            }

                                                echo '<tr style="color: #463ece; font-size: 15px; background-color:#bcedc88f;">
                                                                    
                                                <td colspan="4" align="right"><b>Total</B></td>
                                                <td align="right"><b>' . number_format(round($total_inv_value), 2, ".", "") . '<b></td>
                                                
                                                </tr>';
                                                $obj = null;
                                        } else {

                                            echo ' <tr class="font-weight-semibold rpt_footer ">
                                                             <td colspan="8" align="center">No History found..!</td>
                                                      </tr>';
                                        }

                                        echo     '</tbody>
                                        </table>
                                        </div></div></div>';
                                    }

                                    ?>

                            </form>
                        </div>
                        <!-- End of This Form UI  --->
                    </div>
                </div>
                <!-- /dashboard content -->
            </div>
            <!-- /content area -->
        <?php include("inc/common/footer.php") ?>
            <!-- Footer -->
            <!-- /footer -->
        </div>

        <!-- /main content -->
    </div>
    <!-- /page content -->
</body>

</html>

<script type="text/javascript">
    function fnValidate() {

        if (isNull(document.thisForm.from_dt, "From date..!")) {
            return false;
        }
        if (isNull(document.thisForm.to_dt, "To date..!")) {
            return false;
        }
        // if (isNull(document.thisForm.supp_id, "Customer Name..!")) {
        //     return false;
        // }
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