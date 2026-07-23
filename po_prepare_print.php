<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
isAdmin();


$conn = new dbconnect();
$dbconn = new dbhandler();

//ini_set('display_errors', '1');
//ini_set('display_startup_errors', '1');
//error_reporting(E_ALL);


if ($_REQUEST['po_prepare_id'] != "") {
    $dbconn = new dbhandler();
    $result = $conn->query("SELECT * FROM tbl_po_prepare WHERE po_prepare_id = " . $_REQUEST['po_prepare_id']);
    if ($result->rowCount() > 0) {
        $obj = $result->fetch(PDO::FETCH_OBJ);
        $si_refno = $dbconn->GetSingleReconrd("tbl_store_indent", "si_refno", "si_id", $obj->si_id);
        $branch_id = $dbconn->GetSingleReconrd("tbl_store_indent", "branch_id", "si_id", $obj->si_id);
    }
    $company_add = $conn->query("SELECT * FROM  mst_branch WHERE branch_id = " . $branch_id);
    if ($company_add->rowCount() > 0) {
        $res = $company_add->fetch(PDO::FETCH_OBJ);
        // print_r($obj1);
        $address = $res->company_address;
        $address .= '<br>Ph : +91 ' .$res->company_ph_no1 .' / '.$res->company_ph_no2;
        $address .= '<br>E-Mail : '.$res->company_mail;
        $address .= '<br>Web : '.$res->company_web;

        $gst_no = $res->company_gst;
        $pan_no = $res->company_pan;
        $company_state_code = $res->company_state_code;

    }
}
if (isset($_REQUEST['GEN_PO'])) {

    try {

        $posql = "SELECT * FROM tbl_po_prepare
					LEFT JOIN tbl_po_prepare_dets ON tbl_po_prepare.po_prepare_id = tbl_po_prepare_dets.po_prepare_id	
					WHERE tbl_po_prepare.po_prepare_id	= " . $_REQUEST['po_prepare_id'] . " AND tbl_po_prepare_dets.gen_po_status = 0 GROUP BY tbl_po_prepare_dets.supp_id ";
        $result = $conn->query($posql);

        if ($result->rowCount() > 0) {
            while ($suppObj = $result->fetch()) {
                if ($suppObj->item_prepare_status == 3) {


                    if ($suppObj->si_qty > 0) {

                        $_REQUEST['po_date'] = date("Y-m-d");

                        $_REQUEST['po_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
                        // $_REQUEST['po_slno'] = $dbconn->GetMaxValue('tbl_purchase_order', 'po_slno', 'po_finyr = "' . $_REQUEST['po_finyr'] . '" AND company_id', $_SESSION['company_id']) + 1;
		                $_REQUEST['branch'] = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id='".$_SESSION['_user_branch']."' AND branch_status", 1);

                        $_REQUEST['po_slno'] = $dbconn->GetMaxValue('tbl_purchase_order', 'po_slno',' po_finyr',$_REQUEST['po_finyr']) +1;


                        // $_REQUEST['po_refno'] = 'PO/' . leadingZeros($_REQUEST['po_slno'], 4) . '/BEN/' . $_REQUEST['po_finyr'];

		                $_REQUEST['po_refno'] = 'PO/' . leadingZeros($_REQUEST['po_slno'], 4) . '/BIE/'.$_REQUEST['branch'].'/' . $_REQUEST['po_finyr'];


                        $_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
                        $_REQUEST['modify_by'] = $_SESSION['_userid'];

                        $stmt = null;
                        $stmt = $conn->prepare("INSERT INTO tbl_purchase_order (po_finyr, po_slno, po_refno, po_date,po_prepare_id, branch_id, supp_id, po_type, po_remarks, modify_date_time, modify_by, po_status) VALUES (:po_finyr, :po_slno, :po_refno, :po_date, :po_prepare_id, :branch_id, :supp_id, :po_type, :po_remarks,  :modify_date_time, :modify_by, :po_status)");
                        $data = array(
                            ':po_finyr' => $_REQUEST['po_finyr'],
                            ':po_slno' => $_REQUEST['po_slno'],
                            ':po_refno' => $_REQUEST['po_refno'],
                            ':po_date' => $_REQUEST['po_date'],
                            ':po_prepare_id' => $_REQUEST['po_prepare_id'],
			                ':branch_id' => $_SESSION['_user_branch'],
                            ':supp_id' => $suppObj->supp_id,
                            ':po_type' => 'PI-PO',
                            ':po_remarks' => 'Purchase indent based',
                            ':modify_date_time' => '',
                            ':modify_by' => '',
                            ':po_status' => '0'

                        );
                        $stmt->execute($data);
                        $po_last_id = $conn->lastInsertId();
                        /*po head*/

                        /* po other details */
                        $delete_details1 =  "DELETE FROM tbl_purchase_order_others WHERE po_id = '" . $po_last_id . "'";
                        $result1 = $conn->prepare($delete_details1);
                        $result1->execute();
                        $otherSql = " SELECT * FROM mst_po_settings WHERE status = 1 ORDER BY auto_id ASC ";
                        $other_result = $conn->query($otherSql);
                        if ($other_result->rowCount() > 0) {
                            while ($other = $other_result->fetch()) {
                                if ($_REQUEST['po_desc_' . $other->po_setting_id . '_' . $suppObj->supp_id] == '') {
                                    $_REQUEST['po_desc_' . $other->po_setting_id . '_' . $suppObj->supp_id] = '';
                                }
                                $stmt = null;
                                $stmt = $conn->prepare("INSERT INTO tbl_purchase_order_others (po_id, po_setting_id, other_val) VALUES (:po_id, :po_setting_id, :other_val)");
                                $data = array(
                                    ':po_id' => $po_last_id,
                                    ':po_setting_id' => $other->auto_id,
                                    ':other_val' => $_REQUEST['po_desc_' . $other->po_setting_id . '_' . $suppObj->supp_id]
                                );
                                $stmt->execute($data);
                            }
                        }

                        /* po other details */


                        /* po details */
                        $delete_details =  "DELETE FROM tbl_purchase_order_details WHERE po_id = '" . $po_last_id . "'";
                        $result1 = $conn->prepare($delete_details);
                        $result1->execute();
                        $detsSql = " SELECT * FROM tbl_po_prepare
											LEFT JOIN tbl_po_prepare_dets ON tbl_po_prepare.po_prepare_id = tbl_po_prepare_dets.po_prepare_id	
											WHERE tbl_po_prepare.po_prepare_id	= " . $_REQUEST['po_prepare_id'] . " AND supp_id = " . $suppObj->supp_id . " ORDER BY tbl_po_prepare_dets.item_id ASC ";
                        $dets_result = $conn->query($detsSql);
                        if ($dets_result->rowCount() > 0) {
                            $po_value = 0;
                            $tax_value = 0;
                            while ($pod = $dets_result->fetch()) {
                                $wx_po_id = $dbconn->GetSingleReconrd("tbl_purchase_order", "po_id", "po_prepare_id", $_REQUEST['po_prepare_id']);
                                $details_id = $dbconn->GetSingleReconrd("tbl_purchase_order_details", "details_id", "item_id ='" . $pod->item_id . "' AND po_prepare_id ", $_REQUEST['po_prepare_id']);
                                //if($details_id == ''){	
                                // $uom = $dbconn->GetSingleReconrd("tbl_item_details", "item_uom", "item_status = '1' AND item_id", $pod->item_id);
                                // $item_uom = $dbconn->GetSingleReconrd("mst_uom", "uom_code", "uom_status='1' AND uom_id", $uom);

                                $si_unit = $dbconn->GetSingleReconrd("tbl_store_indent_details","si_unit","item_id = '".$pod->item_id."' AND si_id",$pod->si_id);


                                if($si_unit == 0 || $si_unit == '' || $si_unit == 'NULL'){
                                    $uom = $dbconn->GetSingleReconrd("tbl_item_details", "item_uom", "item_status = '1' AND item_id", $pod->item_id);
                                    $item_uom = $dbconn->GetSingleReconrd("mst_uom", "uom_code", "uom_status='1' AND uom_id", $uom);
                                }else{
                                    $item_uom = $dbconn->GetSingleReconrd("mst_uom","uom_name","uom_id",$si_unit);

                                }
                                //$unit_price = $dbconn->GetSingleReconrd("tbl_item_details","item_selling_price","item_status = '1' AND item_id",$pod->item_id);
                                // $branch_item_cost_price = $dbconn->GetSingleReconrd("mst_branch","branch_item_cost_price","branch_id",$_SESSION['_user_branch']);
                                // $unit_price =  $dbconn->GetSingleReconrd("tbl_item_stock","$branch_item_cost_price","item_id",$pod->item_id);

                                $branch_item_discount = $dbconn->GetSingleReconrd("mst_branch","branch_item_discount","branch_id",$_SESSION['_user_branch']);
                                $item_discount =  $dbconn->GetSingleReconrd("tbl_item_stock","$branch_item_discount","item_id",$pod->item_id);

                                $branch_item_price = $dbconn->GetSingleReconrd("mst_branch","branch_item_price","branch_id",$_SESSION['_user_branch']);
                                $item_price =  $dbconn->GetSingleReconrd("tbl_item_stock","$branch_item_price","item_id",$pod->item_id);


                                // $unit_price = $dbconn->GetSingleReconrd("tbl_item_details", "item_cost_price", "item_status = '1' AND item_id", $pod->item_id);
                                $price = $pod->unit_price * $pod->si_qty;

                                $item_hsn = $dbconn->GetSingleReconrd("tbl_item_details", "item_hsn", "item_status = '1' AND item_id", $pod->item_id);
                                $vat  = $dbconn->GetSingleReconrd("mst_hsn", "igst", "hsn_status = '1' AND hsn_id", $item_hsn);

                                $tax = $price * $vat / 100;
                                $net_amt = $price + $tax;
                                $stmt = null;
                                $stmt = $conn->prepare("INSERT INTO tbl_purchase_order_details (po_id, po_prepare_id, item_id, po_qty, po_unit, cost_price, discount_per, item_price, vat, po_value,
                                 tax_value, net_value) VALUES (:po_id, :po_prepare_id, :item_id, :po_qty, :po_unit, :cost_price, :discount_per, :item_price, :vat, :po_value, :tax_value, :net_value)");
                                $data = array(
                                    ':po_id' => $po_last_id,
                                    ':po_prepare_id' => $_REQUEST['po_prepare_id'],
                                    ':item_id' => $pod->item_id,
                                    ':po_qty' => $pod->si_qty,
                                    ':po_unit' => $item_uom,
                                    ':cost_price' => $pod->unit_price,
                                    ':discount_per' => $pod->item_discount,
                                    ':item_price' => $item_price,
                                    ':vat' => $vat,
                                    ':po_value' => $price,
                                    ':tax_value' => $tax,
                                    ':net_value' => $net_amt
                                );
                                $stmt->execute($data);
                                $po_value = $po_value + $net_amt;

                                $update_po = $conn->prepare("UPDATE  tbl_po_prepare_dets SET gen_po_status = :gen_po_status WHERE po_prepare_id = :po_prepare_id  AND item_id = :item_id ");
                                $data1 = array(
                                    ':gen_po_status' => 1,
                                    ':po_prepare_id' => $_REQUEST['po_prepare_id'],
                                    ':item_id' => $pod->item_id
                                );
                                $update_po->execute($data1);
                                // }
                            }
                            /* po details */

                            $update_po = $conn->prepare("UPDATE  tbl_purchase_order SET po_value = :po_value WHERE po_id = :po_id");
                            $data1 = array(
                                ':po_id' => $po_last_id,
                                ':po_value' => $po_value
                            );
                            $update_po->execute($data1);
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
    }

    header("Location:lst_direct_po.php");
    die();

    //exit;
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Direct Store Indent</title>
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
                filename: 'echo $obj->si_refno; ' + '.pdf',
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

        // if (notSelected(document.thisForm.supp_id, "Supplier..!")) {
        //     return false;
        // }
        // if (isNull(document.thisForm.po_date, "Order date..!")) {
        //     return false;
        // }

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
                            <span class="breadcrumb-item active">PO Prepare</span>
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
                        <form name='thisForm' id="validate" class="form-horizontal" method='post' action="po_prepare_print.php" onSubmit="return fnValidate();" enctype="multipart/form-data">

                        <div class="card">
                            <div class="card-header bg-pgheader text-white header-elements-inline">
                                <h6 class="card-title">PO Prepare Print</h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" href="po_prepare_list.php" title="Prepare Purchase Order List"><i class="icon-arrow-left52 mr-2"></i></a>
                                        <?php
                                        // if ($obj->po_prepare_status == 1) { ?>
                                            
                                        <!--   <a class="list-icons-item" href="javascript:PrintPartsNew(new Array('print_content1'),'<?php echo $obj->si_refno; ?>');" id="print_page" title="Print Store Indent"><i class="icon-printer2 mr-1"></i></a>
                                           <a class="list-icons-item" id="cmd" href="javascript:;" title="PDF"><i class="icon-file-pdf  mr-2"></i></a>-->
                                         <?php
                                        // }
                                        // ?>
                                        <a class="list-icons-item" data-action="fullscreen"></a>

                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="invoice" id="print_content1" style="width:100%;">
                                        <table class="table table-xs table-bordered po_print_table mt-0 pt-0">
                                            <thead>
                                                <!-- <tr class="hidden-print"> -->
                                                    <td>

                                                        <table class="align-center " width="100%">
                                                        
                                                            <span style="float: left;" class="col-md-5"><img src="img/BIE_logo.png" alt=""  width="50px"><span style="font-size:20px;float:right;font-weight: bold;margin-right:-260px;">PREPARE PURCHASE ORDER</span></span>

                                                            <span style="float:center;margin-left:600px;" class="col-md-4 mt-3 pt-4 pb-5"> PO Indent: <a target="_blank" href="store_indent_print.php?si_id=<?php echo $obj->si_id; ?>"><span style="font-weight:600;font-size:12px;"><strong><?php echo $si_refno; ?></strong></span></a></span>
                                                            
                                                            <span style="float:right; margin-right:-130px;" class="col-md-3"> PO Prepare Dt. : <strong><?php echo date('d-m-Y', strtotime($obj->po_prepare_date)); ?></strong></span>
                                                            
                                                             
                                                        </table>

                                                    </td>
                                                <!-- </tr> -->

                                            </thead>

                                            <tbody>


                                                <tr>
                                                    <td>
                                                        <?php
                                                        $posql = "SELECT * FROM tbl_po_prepare
                                                            LEFT JOIN tbl_po_prepare_dets ON tbl_po_prepare.po_prepare_id = tbl_po_prepare_dets.po_prepare_id	
                                                            WHERE tbl_po_prepare.po_prepare_id	= " . $_REQUEST['po_prepare_id'] . " AND tbl_po_prepare_dets.gen_po_status != 1 GROUP BY tbl_po_prepare_dets.supp_id ";
                                                        $result = $conn->query($posql);
                                                        if ($result->rowCount() > 0) 
                                                        {

                                                            $sno = 1;
                                                            while ($suppObj = $result->fetch()) 
                                                            {
                                                                $supp_name ="";
                                                                $supp_gst =  $supp_pan = "";
                                                                $add = "";


                                                                //$sum_po_qty = $dbconn->GetSingleReconrd("tbl_purchase_indent_details","SUM(si_qty)","si_id",$suppObj->si_id);
                                                                $po_id = $dbconn->GetSingleReconrd("tbl_purchase_order", "po_id", "po_prepare_id", $suppObj->po_prepare_id);
                                                                //$sum_po_qty = $dbconn->GetSingleReconrd("tbl_purchase_indent_details","si_qty","item_id = ".$suppObj->item_id." AND si_id",$suppObj->si_id);
                                                                $po_qty = $dbconn->GetSingleReconrd("tbl_purchase_order_details", "po_qty", "item_id = " . $suppObj->item_id . " AND po_id", $po_id);
                                                                if ($suppObj->si_qty > 0 && $suppObj->item_prepare_status > 2 && $suppObj->gen_po_status == 0) 
                                                                {
                                                                   

                                                                    $supp_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_status = 1 AND supp_id", $suppObj->supp_id);

                                                                    $get_add = $conn->query("SELECT * FROM mst_supplier_new WHERE supp_id = " . $suppObj->supp_id . " AND company_branch_id= '".$_SESSION['_user_branch']."' ");
                                                                    
                                                                    if ($get_add->rowCount() > 0) 
                                                                    {
                                                                        $obj1 = $get_add->fetch(PDO::FETCH_OBJ);
                                                                        $supp_gst = $obj1->supp_gst;
                                                                        $supp_pan = $obj1->supp_pan;
                                                                        $add .= $obj1->supp_add1;
                                                                        if ($obj1->supp_add2 != "") {
                                                                            $add .= ', ' . $obj1->supp_add2;
                                                                        }
                                                                        $add .= ', <br/>' . $dbconn->GetSingleReconrd("mst_city", "city_name", "city_status = 1 AND city_id ", $obj1->city_id);

                                                                        $add .= ', ' . $dbconn->GetSingleReconrd("mst_district", "district_name", "district_status = 1 AND district_id ", $obj1->district_id);

                                                                        $add .= ', <br/>' . $dbconn->GetSingleReconrd("mst_state", "state_name", "state_status = 1 AND state_id ", $obj1->state_id);

                                                                        $add .= ' - ' . $obj1->supp_pincode . '.';
                                                                    }
                                                               
                                                                
                                                                     ?>
                                                                     
                                                                    <table class="mystyle" cellpadding="7px;" style="margin-bottom:10px;">
                                                                        <thead>
                                                                          
                                                                            <tr>
                                                                                <td align="center" colspan="11" style="font-size: 20px; font-weight: bold; background-color: #bebebe;">PURCHASE ORDER</td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td colspan="3" align="left" style="font-weight:bold; background-color: #bebebe;">TO :</td>
                                                                                <td colspan="8" align="left" style="font-weight:bold; background-color: #bebebe;">FROM :</td>
                                                                            </tr>

                                                                            <tr>
                                                                               
                                                                                <td colspan="3" align="left" style="vertical-align: top;"><?php echo '<b>' . $supp_name . '</b><br/>' . $add; ?><br />
                                                                                    GSTIN NO : <b><?php echo $supp_gst; ?></b><br />
                                                                                    PAN NO : <b><?php echo $supp_pan; ?></b></td>
                                                                                <td colspan="9" align="left"  style="vertical-align: top;"><?php echo   $address; ?>
                                                                                    
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td colspan="12" align="center" style="background-color: #bebebe;"><span style="text-align:center;">GSTIN NO : <b><?php echo $gst_no; ?></b></span></td>
                                                                            </tr>
                                                                        
                                                                        </thead>
                                                                    
                                                                        <tbody>
                                                                            <tr style="font-weight:bold;" class="align-center uppercase">
                                                                                <td width="3%">#</td>
                                                                                <td width="20%">Material Code</td>
                                                                                <td width="30%">Description</td>
                                                                                <td width="10%">Item Code</td>
                                                                                <td width="10%">Model</td>
                                                                                <td width="10%">Qty</td>
                                                                                <td width="10%">UOM</td>
                                                                                <td width="10%">Unit Price</td>
                                                                                <td width="10%">Amount</td>
                                                                                <td width="10%">GST</td>
                                                                                <td width="10%">Total</td>
                                                                            </tr>
                                                                        
                                                                        
                                                                        <?php
                                                                        $detsSql = " SELECT * FROM tbl_po_prepare
                                                                        LEFT JOIN tbl_po_prepare_dets ON tbl_po_prepare.po_prepare_id = tbl_po_prepare_dets.po_prepare_id	
                                                                        WHERE tbl_po_prepare.po_prepare_id	= " . $_REQUEST['po_prepare_id'] . " AND supp_id = " . $suppObj->supp_id . "  ORDER BY tbl_po_prepare_dets.item_id ASC ";

                                                                            $dets_result = $conn->query($detsSql);

                                                                            if ($dets_result->rowCount() > 0) 
                                                                            {
                                                                                $iSno = 1;
                                                                                $netTotal = 0;
                                                                                $supp_id = 0;
                                                                                $total_net_amount = 0;
                                                                                while ($pod = $dets_result->fetch()) 
                                                                                {
                                                                                    $send_to_admin = $pod->send_to_admin_status;
                                                                                    $send_admin_status = $pod->send_admin_status;

                                                                                    $supp_item_code = $dbconn->GetSingleReconrd("tbl_item_details", "supp_item_code", "item_status = '1' AND item_id", $pod->item_id);

                                                                                    $item_code = $dbconn->GetSingleReconrd("tbl_item_details", "item_code", "item_status = '1' AND item_id", $pod->item_id);

                                                                                    $item_name = $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_status = '1' AND item_id", $pod->item_id);

                                                                                    $item_model = $dbconn->GetSingleReconrd("tbl_item_details", "item_model_manufac", "item_status = '1' AND item_id", $pod->item_id);
                                                                                    $po_qty = $pod->si_qty;
                                                                                    //if($po_qty > 0){
                                                                                    $branch_item_cost_price = $dbconn->GetSingleReconrd("mst_branch","branch_item_cost_price","branch_id",$_SESSION['_user_branch']);
                                                                                    $unit_price =  $dbconn->GetSingleReconrd("tbl_item_stock","$branch_item_cost_price","item_id",$pod->item_id);
                                                                                    
                                                                                    // $unit_price = $dbconn->GetSingleReconrd("tbl_item_details", "item_cost_price", "item_status = '1' AND item_id", $pod->item_id);
                                                                                    $price = $pod->unit_price * $po_qty;

															                       $si_unit = $dbconn->GetSingleReconrd("tbl_store_indent_details","si_unit","item_id = '".$pod->item_id."' AND si_id",$pod->si_id);


                                                                                    if($si_unit == 0 || $si_unit == '' || $si_unit == 'NULL'){
                                                                                        $uom = $dbconn->GetSingleReconrd("tbl_item_details", "item_uom", "item_status = '1' AND item_id", $pod->item_id);
                                                                                        $item_uom = $dbconn->GetSingleReconrd("mst_uom", "uom_code", "uom_status='1' AND uom_id", $uom);
                                                                                    }else{
                                                                                        $item_uom = $dbconn->GetSingleReconrd("mst_uom","uom_name","uom_id",$si_unit);
                            
                                                                                    }
                                                                                    // $uom = $dbconn->GetSingleReconrd("tbl_item_details", "item_uom", "item_status = '1' AND item_id", $pod->item_id);
                                                                                    // $item_uom = $dbconn->GetSingleReconrd("mst_uom", "uom_code", "uom_status='1' AND uom_id", $uom);

                                                                                    $item_hsn = $dbconn->GetSingleReconrd("tbl_item_details", "item_hsn", "item_status = '1' AND item_id", $pod->item_id);
                                                                                    $gst  = $dbconn->GetSingleReconrd("mst_hsn", "igst", "hsn_status = '1' AND hsn_id", $item_hsn);

                                                                                    $tax = $price * $gst / 100;
                                                                                    $net_amt = $price + $tax;

                                                                                    echo '<tr  valign="top">
                                                                                         <td class="align-center">' . $iSno . '</td>';
                                                                                    echo '<td class="align-left">' . $supp_item_code . '</td>';
                                                                                    echo '<td class="align-left">' . $item_name . '</td>';
                                                                                    echo '<td class="align-center">' . $item_code . '</td>';
                                                                                    echo '<td class="align-center">' . $item_model . '</td>';

                                                                                    echo '<td class="align-right">' . $po_qty . '</td>';
                                                                                    echo '<td class="align-center">' . $item_uom . '</td>';
                                                                                    echo '<td class="align-right">' . $pod->unit_price . '</td>';
                                                                                    echo '<td class="align-right">' . number_format($price, 2, ".", "") . '</td>';
                                                                                    echo '<td class="align-center">' . $gst . '</td>';
                                                                                    echo '<td class="align-right">' . number_format($net_amt, 2, ".", "") . '</td>';
                                                                                    //number_format($net_val,2,".","")
                                                                                    echo '</tr>';

                                                                                    $total_net_amount += $net_amt;
                                                                                    $iSno++;
                                                                                    //}
                                                                                }
                                                                                $rupees_words = 'Rupees ' . ucwords(number_to_words(number_format($total_net_amount, 0, ".", ""))) . " Only";

                                                                                echo '<tr>';
                                                                                echo '<td colspan="8" class="align-left"><p><strong> Amount in words : </strong>' . $rupees_words . '</p></td>';
                                                                                echo '<td colspan="2" class="align-right"><strong>Grand Total</strong></td>';
                                                                                echo '<td class="align-right">' . number_format($total_net_amount, 2) . '</td>';
                                                                                echo '</tr>';
                                                                          
                                                                                ?>
                                                                                <?php 
                                                                                    $exSql = " SELECT * FROM mst_po_settings WHERE status = 1 ";

                                                                                    $ex_result = $conn->query($exSql);
                                                                                    
                                                                                    if ($ex_result->rowCount() > 0)
                                                                                        {
                                                                                            $sno=1;
                                                                                            while ($rs = $ex_result->fetch())
                                                                                            {
                                                                                                ?>
                                                                                            <tr>
                                                                                                <td width="3%"><?php echo $sno; ?></td>
                                                                                                <td align="left"><b><?php echo $rs->po_desc; ?></b></td>
                                                                                                <td colspan="10" align="left"><input class="span12 noshadow" style="width: 70%;" type="text" name="po_other_<?php echo $rs->auto_id;?>_<?php echo $suppObj->supp_id;?>" maxlength="100" value = "" /></td>
                                                                                            </tr>
                                                                                    <?php $sno++;
                                                                                            }
                                                                                        }
                                                                                ?>
                                                                            <?php }
                                                                            $sno++; ?>
                                                                        </tbody>
                                                                    </table>
                                                                
                                                                <?php echo $pagebreak = '<div class="pagebreak" style="page-break-before:always">&nbsp;</div>' . PHP_EOL;
                                                             }
                                                                ?>

                                                            <?php    }    ?>
                                                        <?php }

                                                        ?>
                                                    </td>
                                                </tr>


                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                                    <div class="card-footer text-center pt-2">
                                        <!--<a href="javascript:void(0)" id="gen_po" data-id="//$pod->po_prepare_id; "><span class="btn btn-info">GENERATE PO</span></a>-->
                                         <?php if (($_SESSION['_user_type'] == 'A') || ($_SESSION['_user_type'] == 'S')) { ?>
                                            
                                            <?php   ?>
                                            <?php   ?>
                                            <?php $gen_po = $dbconn->GetSingleReconrd("tbl_purchase_order", "po_id", " po_prepare_id", $_REQUEST['po_prepare_id']);
                                            $sum_gen_po_status = $dbconn->GetSingleReconrd("tbl_po_prepare_dets", "SUM(gen_po_status)", " po_prepare_id", $_REQUEST['po_prepare_id']);
                                            $sum_auto_id = $dbconn->GetSingleReconrd("tbl_po_prepare_dets", "COUNT(auto_id)", " po_prepare_id", $_REQUEST['po_prepare_id']);

                                            if ($send_to_admin == 1) { ?>

                                                <input type="hidden" name="po_prepare_id" id="po_prepare_id" value="<?php echo $obj->po_prepare_id; ?>" />
                                                <INPUT class="btn btn-info" type="submit" name="GEN_PO" value="GENERATE PO" onclick="return fnValidate();">
                                            <?php }
                                        } else { ?>
                                            <span style="color:red !important;"> In Admin Approvel &nbsp;</span>
                                        <?php } ?>
                                        <INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
                                       
                                    </div>

                                    <!-- End of This Form UI  --->
                             
                            <!-- /dashboard content -->
                        </div>
                        </form>
                        
                    </div>
                                            
                </div>

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