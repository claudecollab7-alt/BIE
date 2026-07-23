<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
	

isAdmin();


//ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);

$conn = new dbconnect();
$dbconn = new dbhandler();

$po_date = date("Y-m-d");


if (isset($_REQUEST['po_id'])) {

    $result = $conn->query("SELECT * FROM tbl_purchase_order WHERE po_id = '" . $_REQUEST['po_id'] . "'");
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
        $dis = $dbconn->GetSingleReconrd("tbl_purchase_order_details", "sum(tax_value)", "po_id", $_REQUEST['po_id']);

        $resSup = $conn->query("SELECT * FROM mst_supplier_new WHERE supp_id = '" . $obj->supp_id . "'");
        if ($resSup->rowCount() > 0) {
            $obj1 = $resSup->fetch(PDO::FETCH_OBJ);
            $supp_gst = $obj1->supp_gst;
            $supp_pan = $obj1->supp_pan;
            $add = "";
            $add .= $obj1->supp_add1;
            if($obj1->supp_add2 != "")
            {
                $add .= ', '.$obj1->supp_add2;
            }
            $add .= ', <br/>'.$dbconn->GetSingleReconrd("mst_city","city_name","city_status = 1 AND city_id ",$obj1->city_id);
    
            $add .=', '.$dbconn->GetSingleReconrd("mst_district","district_name","district_status = 1 AND district_id ",$obj1->district_id);
    
            $add .=', <br/>'.$dbconn->GetSingleReconrd("mst_state","state_name","state_status = 1 AND state_id ",$obj1->state_id);
    
            $add .=' - '.$obj1->supp_pincode.'.';
			
			$add .='<br/><b>GSTIN :  </b> '.$obj1->supp_gst;
			
			$add .='<br/><b>PAN :  </b> '.$obj1->supp_pan;
        }
    }

    $company_add = $conn->query("SELECT * FROM  mst_branch WHERE branch_id = " . $obj->branch_id);
    if ($company_add->rowCount() > 0) {
        $res = $company_add->fetch(PDO::FETCH_OBJ);
        // print_r($obj1);
        $address = $res->company_address;
        $address .= '<br><b>PH : </b> +91 ' .$res->company_ph_no1 .' / '.$res->company_ph_no2;
        $address .= '<br><b>E-Mail : </b>'.$res->company_mail;
        $address .= '<br><b>Web : </b>'.$res->company_web;

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
    <title><?php echo PAGE_TITLE; ?> - Direct Purchase Order</title>
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
                filename: '<?php echo $obj->po_refno; ?>' + '.pdf',
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
                                <h6 class="card-title">Purchase Order Details - <?php echo $obj->po_refno; ?></h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" href="lst_direct_po.php" title="Purchase Order List"><i class="icon-arrow-left52 mr-2"></i></a>
                                        <?php
                                        if ($obj->po_status == 5) {
                                        ?>
                                            <a class="list-icons-item" href="javascript:PrintPartsNew(new Array('print_content1'),'<?php echo $obj->po_refno; ?>');" id="print_page" title="Print PO"><i class="icon-printer2 mr-1"></i></a>
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
                                        <table class="table table-xs table-bordered po_print_table">
                                            <thead>
                                                <tr>
                                                    <td colspan="12">
                                                        <span style="float: right;"><img src="img/BIE_logo.png" alt="" width="50px" height="auto"></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="center" colspan="12" style="font-size: 20px; font-weight: bold; background-color: #bebebe;">PURCHASE ORDER</td>
                                                </tr>

                                                <tr>
                                                    <td colspan="5" align="left"><b>Ref. No. : </b><?php echo $obj->po_refno; ?></td>
                                                    <td colspan="7" align="left"><b>Date : </b><?php echo date('d-m-Y', strtotime($po_date)); ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="5" align="left" style="font-weight:bold; background-color: #bebebe;">TO :</td>
                                                    <td colspan="7" align="left" style="font-weight:bold; background-color: #bebebe;">FROM :</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="5" align="left" style="vertical-align: top;"><?php echo '<b>' . $obj1->supp_name . '</b><br/> '.$add; ?></td>
                                                    <td colspan="7" align="left"><?php echo $address; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="center" colspan="12" style="background-color: #bebebe;"><b>GSTIN NO : <?php echo $gst_no; ?> </b></td>
                                                </tr>
                                                <tr style="font-weight:bold; background-color: #bebebe;" class="align-center uppercase">
                                                    <td>#</td>
                                                    <td>Material Code</td>
                                                    <td>Description</td>
                                                    <td>Item Code</td>
                                                    <td>Quantity</td>
                                                    <td>Unit</td>
                                                    <td>Unit Price</td>
                                                    <td>Dis. (%)</td>
                                                    <td>Amount</td>
                                                    <td>Tax (%)</td>
                                                    <td>Total</td>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php

                                                $posql = "SELECT * FROM tbl_purchase_order
                                                                    LEFT JOIN tbl_purchase_order_details ON tbl_purchase_order.po_id = tbl_purchase_order_details.po_id
                                                                    WHERE tbl_purchase_order.po_id = '" . $_REQUEST['po_id'] . "'";

                                                $result = $conn->query($posql);

                                              

                                                if ($result->rowCount() > 0) {

                                                    $iSno = 1;
                                                    $netTotal = 0;
                                                    while ($pod = $result->fetch()) {

                                                        $supp_item_code = $dbconn->GetSingleReconrd("tbl_item_details", "supp_item_code", "item_id", $pod->item_id);

                                                        $item_pur_code = $dbconn->GetSingleReconrd("tbl_item_details", "item_purchase_code", "item_id", $pod->item_id);

                                                        $item_name = $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_id", $pod->item_id);
                                                        $item_model = $dbconn->GetSingleReconrd("tbl_item_details", "item_model_manufac", "item_id", $pod->item_id);

                                                        echo '<tr valign="top">
                                                                        <td class="align-center">' . $iSno . '</td>
                                                                        <td class="align-left">' . $supp_item_code . '</td>
                                                                        <td class="align-left">' . $item_name . '</td>
                                                                        <td class="align-left">' . $item_pur_code . '</td>
                                                                        <td class="align-center">' . $pod->po_qty . '</td>
                                                                        <td class="align-center">' . $pod->po_unit . '</td>
                                                                        <td class="text-right">' . number_format($pod->cost_price, 2, ".", "") . '</td>
                                                                        <td class="text-right">' . number_format($pod->discount_per, 0) . '</td>
                                                                        <td class="text-right">' . number_format($pod->po_value, 2) . '</td>
                                                                        <td class="text-right">' . number_format($pod->vat, 2) . '</td>
                                                                        <td class="text-right">' . number_format($pod->net_value, 2) . '</td>
                                                                    </tr>';

                                                        //$netTotal = $netTotal + $pod->item_total;		
                                                        $tr_class = 'class="topborderzero"';
                                                        $iSno++;
                                                    }
                                                }

                                                $no_items = $iSno;
                                                $items_height = $no_items * 180;
                                                if ($items_height < 500) {
                                                    $height = 500 - $items_height;
                                                } else {
                                                    $height = 10;
                                                }

                                                $netTotal = number_format($netTotal, 0, ".", "");
                                                $rupees_words = 'Rupees ' . ucwords(number_to_words(number_format($obj->po_value, 0, ".", ""))) . " Only";

                                                echo '<tr><td colspan="8" align="left"><p><strong> Amount in Words : </strong>' . $rupees_words . '</p></td>
                                                                <td colspan="2" align="right"><strong>Grand Total</strong></td>
                                                                <td align="right"><strong>' . number_format(round($obj->po_value), 2) . '</strong></td>
                                                                </tr>';

                                                ?>

                                                <?php if ($_REQUEST['po_id'] != '') {

                                                    $dets_sql = "SELECT * FROM tbl_po_print_details WHERE po_id = " . $_REQUEST['po_id'] . " ORDER BY pr_sort";
                                                    $result_dets = $conn->query($dets_sql);
                                                    $rowCnt = $result_dets->rowCount();
                                                    if ($result_dets->rowCount() > 0) {
                                                        $sno = 1;
                                                        while ($itm = $result_dets->fetch()) {

                                                            echo '
                                                            <tr>
                                                                <td>' . $sno . '</td>
                                                                <td><b>' . $itm->pr_name . '</b></td>
                                                                <td colspan="10">' . $itm->pr_desc . '</td>
                                                            </tr>';
                                                            $sno++;
                                                        }
                                                    }
                                                }
                                                ?>
                                                
                                                <tr>
                                                    <td align="right" colspan="12"><b>BENZEAR INDUSTRIAL ENTERPRISES</b><br />
                                                       M.MUTHUMANI<br />
														PURCHASE MANAGER<br />
														9894360204
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td align="center" style="background-color: #bebebe;" colspan="12"><b>www.benzear-bie.com</b></td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td class="align-center" colspan="2 ">
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
                                        <label><b>Purchase Order Note: </b></label>
                                        <?php echo $obj->po_remarks; ?> 
                                        </div>
                                    </div>
                                </div>  
                            
                                <?php if (($obj->po_approve_status == 0 || $obj->po_approve_status == 2) && $obj->po_status == 3) { ?> 
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Remarks </label>
                                            <textarea name="po_approve_remarks" id="po_approve_remarks" class="form-control" rows="2" maxlength="250"><?php echo $obj->po_approve_remarks; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                            
                            <?php if (($obj->po_approve_status == 0 || $obj->po_approve_status == 2) && $obj->po_status == 3 && $_SESSION['_user_id'] == 1) { ?> 
                            <div class="card-footer text-center">
                                <input type="hidden" name="po_id" id="po_id" value="<?php echo $_REQUEST['po_id']; ?>" />
                                <INPUT class="btn btn-custom" type="button" id="APPROVE" name="APPROVE" value="Approve">
                                <INPUT class="btn btn-danger" type="button" id="REJECT" name="REJECT" value="Reject">
                            </div>
                            <?php } ?>
                             
                        </div>
                        
                         <div class="row">
                            <div class="text-left col-lg-6">
                                <div class="rec_create_dets"><b>Created by : </b><?php echo $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id ", $obj->modify_by) . ' on ' . date('d-M-y @ H:i', strtotime($obj->modify_date_time)); ?></b>
                                </div>
                            </div>
                            <div class="text-right col-lg-6">
                                <?php
                                if ($obj->po_approve_status != 0) {
                                ?>
                                    <div class="rec_create_dets"><b>Approved by : </b><?php echo $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id ", $obj->po_approve_id) . ' on ' . date('d-M-y @ H:i', strtotime($obj->po_approve_date_time)); ?></b><br><b>Remarks : </b> <?php echo $obj->po_approve_remarks; ?>
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
    $(document).ready(function() {
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


        $('#APPROVE').click(function() {

            var po_id = $('#po_id').val();
            var remarks = $('#po_approve_remarks').val();
            var task = "PO_APP";

            $.ajax({
                type: 'post',
                url: 'inc/cis_ajax/jquery_po_approval.php',
                data: {
                    "id": po_id,
                    "task": task,
                    "remarks": remarks
                },
                beforeSend: function() {
                    if (confirm('Are you sure to Approve this Purchase Order..?')) {} else {
                        return false;
                    }
                },
                complete: function() {},
                success: function(result) {
                    //location.reload();
                    window.location.href = "lst_direct_po.php";
                }
            });
            return false;
        });

        $('#REJECT').click(function() {

            if ($('#po_approve_remarks').val() == '') {
                alert("Please enter the PO Rejection Remarks..!");
                $('#po_approve_remarks').focus();
                return false;
            }

            var po_id = $('#po_id').val();
            var remarks = $('#po_approve_remarks').val();
            var task = "PO_REJ";
            $.ajax({
                type: 'post',
                url: 'inc/cis_ajax/jquery_po_approval.php',
                data: {
                    "id": po_id,
                    "task": task,
                    "remarks": remarks
                },
                beforeSend: function() {
                    if (confirm('Are you sure to Reject this Purchase Order..?')) {} else {
                        return false;
                    }
                },
                complete: function() {},
                success: function(result) {
                    //location.reload();
                    window.location.href = "lst_direct_po.php";
                }
            });
            return false;
        });

    });
</script>

</html>