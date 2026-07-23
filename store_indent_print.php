<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
isAdmin();

//ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);

$conn = new dbconnect();
$dbconn = new dbhandler();

$po_date = date("Y-m-d");



if (isset($_REQUEST['si_id'])) {

    $result = $conn->query("SELECT * FROM tbl_store_indent WHERE si_id = '" . $_REQUEST['si_id'] . "'");
    if ($result->rowCount() > 0) {
        $obj = $result->fetch(PDO::FETCH_OBJ);
        $si_date = $obj->si_date;

        // $po_status = '';
        // if ($obj->po_status < 4) {
        //     if ($obj->po_status == 1) $po_status = "Draft";
        //     if ($obj->po_status == 2) $po_status = "In Approval";
        //     if ($obj->po_status == 3) $po_status = "PO Rejected";
        // }
        // $dis = 0;
        // $dis = $dbconn->GetSingleReconrd(" tbl_purchase_order_details", "sum(tax_value)", "po_id", $_REQUEST['po_id']);

        // $resSup = $conn->query("SELECT * FROM mst_supplier_new WHERE supp_id = '" . $obj->supp_id . "'");
        // if ($resSup->rowCount() > 0) {
        //     $sup = $resSup->fetch(PDO::FETCH_OBJ);
        // }
    }

    $company_add = $conn->query("SELECT * FROM  mst_branch WHERE branch_id = " . $obj->branch_id);
    if ($company_add->rowCount() > 0) {
        $res = $company_add->fetch(PDO::FETCH_OBJ);
        // print_r($obj1);
        $address = $res->company_address;
        $address .= '<br>PH : +91 ' .$res->company_ph_no1 .' / '.$res->company_ph_no2;
        $address .= '<br>E-Mail : '.$res->company_mail;
        $address .= '<br>Web : '.$res->company_web;

        $gst_no = $res->company_gst;
        $pan_no = $res->company_pan;
        $state_code = $res->company_state_code;

    }  

}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Store Indent</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <?php include_once("inc/common/css-js.php"); ?>

</head>

<script type="text/javascript" src="print_me.js"></script>

<script src="js/html2pdf.bundle.min.js"></script>

<script language="javascript">
    $(function() {
        $("body").on("click", "#cmd", function() {

            var element = document.getElementById('print_content1');
            //html2pdf(element);
            var opt = {
                margin: 0.5,
                filename: '<?php echo $obj->si_refno; ?>' + '.pdf',
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

            // New Promise-based usage:
            html2pdf().set(opt).from(element).save();
            // Old monolithic-style usage:
            //html2pdf(element, opt);
        });


    });
</script>

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
                            <span class="breadcrumb-item active">Store Indent</span>
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
                                <h6 class="card-title">Store Indent Details - <?php echo $obj->si_refno; ?></h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" href="store_indent_list.php" title="Store Indent List"><i class="icon-arrow-left52 mr-2"></i></a>
                                        <?php
                                        if ($obj->send_purchase_status == 1) {
                                        ?>
                                            <a class="list-icons-item" href="javascript:PrintPartsNew(new Array('print_content1'),'<?php echo $obj->si_refno; ?>');" id="print_page" title="Print Store Indent"><i class="icon-printer2 mr-1"></i></a>
                                            <a class="list-icons-item" id="cmd" href="javascript:;" title="PDF"><i class="icon-file-pdf  mr-2"></i></a>
                                        <?php
                                        }
                                        ?>
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
                                                    <td colspan="12">
                                                        <span style="float: right;"><img src="img/BIE_logo.png" alt="" width="50px" height="auto"></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="center" colspan="12" style="font-size: 20px; font-weight: bold; background-color: #bebebe;">Store Indent</td>
                                                </tr>

                                                <tr>
                                                    <td colspan="4" align="left"><b>Ref. No. : </b><?php echo $obj->si_refno; ?></td>
                                                    <td colspan="4"></td>
                                                    <td colspan="4" align="right"><b>Date : </b><?php echo date('d-m-Y', strtotime($si_date)); ?></td>
                                                </tr>

                                                <tr style="font-weight:bold; background-color: #bebebe;" class="align-center uppercase">
                                                    <td colspan="1" align="center" width="5%">#</td>
                                                    <td colspan="2" align="left">Item Code</td>
                                                    <td colspan="5">Description</td>
                                                    <td colspan="2" align="center">UOM</td>
                                                    <td colspan="2" align="right">Store Indent Quantity</td>


                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php

                                                $posql = "SELECT * FROM tbl_store_indent
                                                                    LEFT JOIN tbl_store_indent_details ON tbl_store_indent.si_id = tbl_store_indent_details.si_id
                                                                    WHERE tbl_store_indent.si_id = '" . $_REQUEST['si_id'] . "'";

                                                $result = $conn->query($posql);



                                                if ($result->rowCount() > 0) {

                                                    $iSno = 1;

                                                    while ($pod = $result->fetch()) {

                                                        $supp_item_code = $dbconn->GetSingleReconrd("tbl_item_details", "supp_item_code", "item_id", $pod->item_id);

                                                        $item_pur_code = $dbconn->GetSingleReconrd("tbl_item_details", "item_purchase_code", "item_id", $pod->item_id);

                                                        $item_name = $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_id", $pod->item_id);
                                                        $uom_no = $dbconn->GetSingleReconrd("tbl_item_details", "item_uom", "item_id", $pod->item_id);

                                                        if($pod->si_unit == 0 || $pod->si_unit == '' || $pod->si_unit == 'NULL'){
                                                            $si_uom = $dbconn->GetSingleReconrd("mst_uom","uom_name","uom_id",$uom_no);
                                                        }else{
                                                            $si_uom = $dbconn->GetSingleReconrd("mst_uom","uom_name","uom_id",$pod->si_unit);

                                                        }


                                                        echo '<tr>
                                                                        <td colspan="1" align="center">' . $iSno . '</td>
                                                                        <td colspan="2" align="left">' . $item_pur_code . '</td>
                                                                        <td colspan="5" align="left">' . $item_name . '</td>
                                                                        <td colspan="2" align="center">' . $si_uom . '</td>
                                                                        <td colspan="2" align="right">' . $pod->si_qty . '</td>

                                                                       
                                                                    </tr>';

                                                        //$netTotal = $netTotal + $pod->item_total;		
                                                        $iSno++;
                                                    }
                                                }
                                                ?>
                                                <tr>
                                                    <!-- <td align="right" colspan="12"><b>BENZEAR INDUSTRIAL ENTERPRISES</b><br />
                                                        5/113-3, VELLANAIPATTY<br />
                                                        KALAPATTI POST<br />
                                                        COIMBATORE - 641 048<br />
                                                        <b>Ph: </b>+91 95009 60202 / 95009 68923<br />
                                                        <b>E-Mail : </b>info@benzear-bie.com<br />
                                                        <b>Web : </b>www.benzear-bie.com</p>
                                                    </td> -->
                                                    <td colspan="12" align="right"  style="vertical-align: top;"><?php echo  $address;?>
                                                </tr>

                                                <tr>
                                                    <td align="center" style="background-color: #bebebe;" colspan="12"><b>www.benzear-bie.com</b></td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td class="align-center" colspan="2">
                                                        <p align="center">Prepared By</p>
                                                        <p>&nbsp;</p>
                                                        <p>&nbsp;</p>
                                                        <p align="center">
                                                            <?php
                                                            echo $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id", $obj->modify_by) . '<br/> On ' . date("d-m-Y", strtotime($obj->modify_date_time));
                                                            ?>&nbsp;
                                                        </p>
                                                    </td>
                                                    <td class="align-center" colspan="6">
                                                        <?php
                                                        if ($obj->po_approve_status == 1) {
                                                            echo '<p align="center">Approved By</p>
                                                            <p>&nbsp;</p><p align="center">&nbsp;<br><br>';
                                                            echo $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id", $obj->po_approve_by) . '<br/> On ' . date("d-m-Y", strtotime($obj->po_approve_date_time)) . '</p>';
                                                        }  ?>
                                                    </td>
                                                    <td align="center" colspan="4" style="padding-top: 100px;"><b>C.E.O SIGN</b></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                        <label><b>Remarks: </b></label>
                                        <?php echo $obj->si_remarks; ?> 
                                        </div>
                                    </div>
                                </div>  
                            </div>
                        </div>

                        <div class="row">
                            <div class="text-left col-lg-6">
                                <div class="rec_create_dets"><b>Created by : </b><?php echo $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id ", $obj->modify_by) . ' on ' . date('d-M-y @ H:i', strtotime($obj->modify_date_time)); ?></b>
                                </div>
                            </div>
                            <div class="text-right col-lg-6">
                                <?php
                                if ($obj->send_purchase_status == 1) {
                                ?>
                                    <div class="rec_create_dets"><b>Send to Purchase by : </b><?php echo $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id ", $obj->si_approve_id) . ' on ' . date('d-M-y @ H:i', strtotime($obj->send_purchase_dt)); ?></b>
                                    </div>
                                <?php
                                }
                                ?>
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

<script type="text/javascript">
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
</script>

</html>