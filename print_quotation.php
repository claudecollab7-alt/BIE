<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");

isAdmin();


$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

if (isset($_REQUEST['quo_id'])) {
    $result = $conn->query("SELECT * FROM tbl_quotation WHERE quo_id = '" . $_REQUEST['quo_id'] . "'");
    if ($result->rowCount() > 0) {
        $obj = $result->fetch(PDO::FETCH_OBJ);
        $quo_value = $obj->quo_value;
    }

    $company_add = $conn->query("SELECT * FROM  mst_branch WHERE branch_id = " . $obj->bie_branch_id);
    if ($company_add->rowCount() > 0) {
        $res = $company_add->fetch(PDO::FETCH_OBJ);
        // print_r($obj1);
        $address = $res->company_address;
        $address .= '<br><b>Ph : </b> +91' .$res->company_ph_no1 .' / '.$res->company_ph_no2;
        $address .= '<br><b>E-Mail : </b>'.$res->company_mail;
        $address .= '<br><b>Web : </b>'.$res->company_web;

        $gst_no = $res->company_gst;
        $pan_no = $res->company_pan;
        $branch_state_code = $res->branch_state_code;

    }
}


$dis_count = $dbconn->GetCount("tbl_quotation_details", "quo_discount>0 AND quo_id", $_REQUEST['quo_id']);
$dis_count_val = $dbconn->GetSingleReconrd("tbl_quotation_details", "quo_discount", "quo_id", $_REQUEST['quo_id']);
if ($obj->quo_id > 0) {

    $cus_sql = $conn->query("SELECT * FROM mst_supplier_new WHERE supp_id = " . $obj->supp_id);

    if ($cus_sql->rowCount() > 0) {
        $supp = $cus_sql->fetch(PDO::FETCH_OBJ);
		
        $ref_phone_no = $obj->ref_phone_no;
        $ref_email = $obj->ref_email;
        $enq_gst = $supp->supp_gst;
        $enq_pan = $supp->supp_pan;
        $state_id = $supp->state_id;
        $supp_id = $supp->supp_id;
        $branch_id = $obj->branch_id;


        $supp_sql = $conn->query("SELECT * FROM mst_supplier_new WHERE supp_id = " . $obj->supp_id);
        $supp_dtl = $supp_sql->fetch(PDO::FETCH_OBJ);

        $city_name = $dbconn->GetSingleReconrd("mst_city", "city_name", "city_id",  $supp_dtl->city_id);

        $district_name = $dbconn->GetSingleReconrd("mst_district", "district_name", "district_id", $supp_dtl->district_id);

        $state_name = $dbconn->GetSingleReconrd("mst_state", "state_name", "state_status = 1 AND state_id", $supp_dtl->state_id);

        $pincode = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_pincode", "supp_id", $obj->supp_id);

        $state_code = $dbconn->GetSingleReconrd("mst_state", "state_code", "state_status = 1 AND state_id", $supp_dtl->state_id);

        $pay_content = $dbconn->GetSingleReconrd("mst_terms_condition", "terms_con_content", "terms_con_id", $obj->terms_con_id1);
        $customer_scope =  $dbconn->GetSingleReconrd("mst_terms_condition", "terms_con_content", "terms_con_id", $obj->terms_con_id2);
        $delivery_period = $dbconn->GetSingleReconrd("mst_terms_condition", "terms_con_content", "terms_con_id", $obj->terms_con_id3);
        $delivery_terms = $dbconn->GetSingleReconrd("mst_terms_condition", "terms_con_content", "terms_con_id", $obj->terms_con_id4);
        $installation = $dbconn->GetSingleReconrd("mst_terms_condition", "terms_con_content", "terms_con_id", $obj->terms_con_id5);
        $warranty = $dbconn->GetSingleReconrd("mst_terms_condition", "terms_con_content", "terms_con_id", $obj->terms_con_id6);
        $validity = $dbconn->GetSingleReconrd("mst_terms_condition", "terms_con_content", "terms_con_id", $obj->terms_con_id7);
        $transportation = $dbconn->GetSingleReconrd("mst_terms_condition", "terms_con_content", "terms_con_id", $obj->terms_con_id8);

        $branch_add =  $conn->query("SELECT * FROM mst_customer_branch WHERE branch_id = " . $obj->branch_id);
        $bch = $branch_add->fetch(PDO::FETCH_OBJ);

        $state_code1 = $dbconn->GetSingleReconrd("mst_state", "state_code", "state_status = 1 AND state_id ", $bch->state_id);
        $state_name1 = $dbconn->GetSingleReconrd("mst_state", "state_name", "state_status = 1 AND state_id ", $bch->state_id);



        if ($obj->branch_id > 0) {
            $add .= $bch->branch_add1;
            if ($bch->branch_add2 != "") {
                $add .= ',' . $bch->branch_add2;
            }

            if ($state_name1 != '') {
                $add .= '<br>' . $state_name1 . ' - ' . $bch->branch_pincode;
            }

            if ($ref_phone_no != '') {
                $add .= '<br><b>Ph : </b>' . $ref_phone_no;
            }

            if ($ref_email != '') {
                $add .= '<br><b>E-Mail : </b> ' . $ref_email;
            }

            if ($supp->supp_website != '') {
                $add .= '<br><b>Web : </b> ' . $supp->supp_website;
            }
        } else {
            $add .= $supp_dtl->supp_add1;
            if ($supp_dtl->supp_add2 != "") {
                $add .= $supp_dtl->supp_add2;
            }

            if ($city_name != "") {
                $add .= '<br> ' . $city_name;
            }
            if ($district_name != "") {
                $add .=  ' '.$district_name .' Dist.';
            }



            if ($state_name != '') {
                $add .= '<br>' . $state_name . ' - ' . $pincode;
            }


            if ($ref_phone_no != '') {
                $add .= '<br><b>Ph : </b> ' . $ref_phone_no;
            }

            if ($ref_email != '') {
                $add .= '<br><b>E-Mail : </b> ' . $ref_email;
            }

            if ($supp->supp_website != '') {
                $add .= '<br><b>Web : </b> ' . $supp->supp_website;
            } else {
                $add .= '<br><b>Web : </b> ' . "";
            }
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
    <title><?php echo PAGE_TITLE; ?> - Direct Quotation Order</title>
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
                margin: 0.3,
                filename: '<?php echo $obj->quo_refno; ?>' + '.pdf',
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

<body>
    <?php include("inc/common/header.php") ?>
    <div class="page-content">
        <?php include("inc/common/sidebar.php") ?>
        <div class="content-wrapper">
            <div class="page-header">
                <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                    <div class="d-flex">
                        <div class="breadcrumb">
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
                            <a href="#" class="breadcrumb-item"> Work Area</a>
                            <span class="breadcrumb-item active">Quotation</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>
            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-pgheader text-white header-elements-inline">
                                <h6 class="card-title">Quotation Order Details - <?php echo  $obj->quo_refno; ?></h6>


                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" onClick="javascript:history.go(-1)" title="Quotation List"><i class="icon-arrow-left52 mr-2"></i></a>

                                        <?php
                                        if ($obj->quo_approve_status == 1) {
                                        ?>
                                            <a class="list-icons-item" href="javascript:PrintPartsNew(new Array('print_content1'),'<?php echo $obj->quo_refno; ?>');" id="print_page" title="Print PO"><i class="icon-printer2 mr-1"></i></a>
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
                                        <?php 
                                        $pro_id = $dbconn->GetSingleReconrd("tbl_proforma", "pro_id", "quo_id ", $obj->quo_id);
                                        if ($obj->quo_approve_status == 1 && $pro_id == '') { ?>
                                            <a class="list-icons-item mb-2" id="cmd" href="quo_proforma.php?quo_id=<?php echo $obj->quo_id; ?>" title="PDF"><span class="badge bg-success" style="font-size: 16px;">Generate Proforma</span></a>
                                        <?php } else { ?>
                                            <!-- <span style="font-size: 16px;color: green ;font-weight:bold; ">Proforma Generated</span> -->
                                        <?php } ?>
                                        <table class="table table-xs table-bordered po_print_table">
                                            <thead>
                                                <tr>
                                                    <td colspan="14">
                                                        <span style="float: right;"><img src="img/BIE_logo.png" alt="" width="75px" height="auto"></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center" colspan="14" style="font-size: 20px; font-weight: bold; background-color: #bebebe;">QUOTATION</td>
                                                </tr>
                                                <tr>
                                                    <?php if ($dis_count > 0) {
                                                        $col_ref = 6;
                                                        $col_gst = 2;
                                                        $col_pan = 4;
                                                        $col_enref = 6;
                                                        $col_dte = 7;
                                                        $col_sp_rate = 9;
                                                        $pre_itmcode = '8%';
                                                        $pre_qty = '5%';
                                                        $pre_rte = '5%';
                                                        $pre_hsn = '8%';
                                                        $pre_unit = '4%';
                                                        $pre_dis = '10%';
                                                        $pre_tax = '10%';
                                                    } else {
                                                        $col_ref = 5;
                                                        $col_gst = 2;
                                                        $col_pan = 3;
                                                        $col_enref = 5;
                                                        $col_dte = 8;
                                                        $col_sp_rate = 8;
                                                        $pre_itmcode = '12%';
                                                        $pre_hsn = '9%';
                                                        $pre_qty = '5%';
                                                        $pre_rte = '5%';
                                                        $pre_unit = '8%';
                                                        $pre_tax = '12%';
                                                    } ?>
                                                    <td colspan="<?php echo $col_ref; ?>"><b>Ref. No : </b><?php echo  $obj->quo_refno; ?></td>
                                                    <td colspan="8"><b>Date : </b><?php echo date('d-m-Y', strtotime($obj->quo_date)); ?></td>

                                                </tr>
                                                <tr>
                                                    <td colspan="<?php echo $col_ref; ?>" style="font-weight:bold; background-color: #bebebe;">FROM :</td>
                                                    <td colspan="8" style="font-weight:bold; background-color: #bebebe;">TO :</td>

                                                </tr>
                                                <tr>

                                                    <td colspan="<?php echo $col_ref; ?>"><?php echo $address; ?>
                                                    </td>
                                                    <td colspan="8" style="vertical-align: top;"><?php echo '<b>M/S.' . $supp_dtl->supp_name . '</b><br/>' . $add . ""; ?><br />

                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td colspan="<?php echo $col_gst; ?>" style="background-color: #bebebe; font-size: 9px; text-align: center;"><b>GSTIN NO.</b></td>
                                                    <td colspan="<?php echo $col_pan; ?>" style="text-align:center;"><?php echo $gst_no; ?></td>
                                                    <td colspan="2" style="background-color: #bebebe; font-size: 9px; text-align: center;"><b>GSTIN NO.</b></td>
                                                    <td colspan="6" style="text-align:center;"><?php echo $enq_gst; ?></td>
                                                </tr>

                                                <tr>
                                                    <td colspan="<?php echo $col_gst; ?>" style="background-color: #bebebe; font-size: 9px; text-align: center;"><b>PAN NO.</b></td>
                                                    <td colspan="<?php echo $col_pan; ?>" style="text-align:center;"><?php echo $pan_no; ?></td>
                                                    <td colspan="2" style="background-color: #bebebe; font-size: 9px; text-align: center;"><b>PAN NO.</b></td>
                                                    <td colspan="6" style="text-align:center;"><?php echo $enq_pan; ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="<?php echo $col_gst; ?>" style="background-color: #bebebe; font-size: 9px; text-align: center;"><b>STATE CODE</b></td>
                                                    <td colspan="<?php echo $col_pan; ?>" style="text-align:center;"><?php echo $branch_state_code; ?></td>
                                                    <td colspan="2" style="background-color: #bebebe; font-size: 9px; text-align: center;"><b>STATE CODE</b></td>
                                                    <td colspan="6" style="text-align:center;">
                                                        <?php
                                                        if ($obj->branch_id > 0) {
                                                            echo $state_code1;
                                                            $common_state = $state_code1;
                                                        } else {
                                                            echo $state_code;
                                                            $common_state = $state_code;
                                                        }
                                                        ?></td>
                                                </tr>

                                                <tr>
                                                    <td colspan="<?php echo $col_enref; ?>" align="left"><b>YOUR ENQUIRY REF : </b><?php echo ""; ?></td>
                                                    <td colspan="<?php echo $col_dte; ?>" align="left"><b>DATE : </b><?php echo date('d-m-Y', strtotime($obj->quo_date)); ?></td>
                                                </tr>

                                                <tr style="font-weight:bold; background-color: #bebebe; text-align:center;">
                                                    <td style="border-bottom: none !important; " width="2%">SL</td>

                                                    <td width="16%">Description of Goods</td>
                                                    <td width="<?php echo $pre_itmcode; ?>">Item Code</td>
                                                    <td width="<?php echo $pre_hsn; ?>">HSN Code</td>
                                                    <td width="<?php echo $pre_rte; ?>">Rate</td>
                                                    <td width="<?php echo $pre_qty; ?>">Qty</td>
                                                    <td colspan="" width="<?php echo $pre_unit; ?>">Unit</td>
                                                    <?php
                                                    if ($dis_count > 0) { ?>
                                                        <td colspan="" width="<?php echo $pre_dis; ?>">Discount</td>
                                                    <?php } ?>
                                                    <td colspan="" width="<?php echo $pre_tax; ?>">Taxable Value</td>
                                                    <?php if ($common_state == $branch_state_code) { ?>
                                                        <td colspan="2" width="">CGST</td>
                                                        <td width="" colspan="3">SGST</td>
                                                    <?php } else { ?>
                                                        <td colspan="5">IGST</td>
                                                    <?php } ?>
                                                </tr>
                                                <tr style="font-weight:bold; background-color: #bebebe; text-align:center;">


                                                    <?php if ($common_state == $branch_state_code) { ?>

                                                        <?php
                                                        if ($dis_count > 0) { ?>
                                                        
                                                            <td colspan="1" width=""></td>
                                                        <?php } ?>

                                                        <td colspan="8"></td>
                                                        <td width="6%" colspan="">Rate</td>
                                                        <td width="6%" colspan="">Amount</td>
                                                        <td width="6%" colspan="2">Rate</td>
                                                        <td width="6%" colspan="">Amount</td>
                                                    <?php } else { ?>
                                                        <td colspan="<?php echo $col_sp_rate; ?>"></td>
                                                        <td colspan="3">Rate</td>
                                                        <td colspan="2">Amount</td>
                                                    <?php } ?>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?PHP
                                                $quo_sql = "SELECT * FROM tbl_quotation as a LEFT JOIN tbl_quotation_details as b ON a.quo_id = b.quo_id WHERE a.quo_id = '" . $_REQUEST['quo_id'] . "' ORDER BY b.details_id ASC";

                                                $quo_result = $conn->query($quo_sql);
                                                if ($quo_result->rowCount() > 0) {

                                                    $iSno = 1;
                                                    $netTotal = 0;
                                                    $item_val_no_tax = 0;
                                                    $tax_val1 = 0;
                                                    $tax_val2 = 0;
                                                    $tax_val3 = 0;
                                                    while ($quo = $quo_result->fetch()) {
                                                        $item_sql = "SELECT * FROM tbl_item_details  WHERE item_id ='" . $quo->item_id . "'";
                                                        $item_result = $conn->query($item_sql);
                                                        $itm = $item_result->fetch(PDO::FETCH_OBJ);

                                                        $uom_name = $dbconn->GetSingleReconrd("mst_uom", "uom_name", "uom_status = '1' AND uom_id", $itm->item_uom);
                                                        $hsn_sql = $conn->query("SELECT * FROM mst_hsn WHERE hsn_id = '" . $itm->item_hsn . "'");
                                                        $hsn = $hsn_sql->fetch(PDO::FETCH_OBJ);


                                                        $tax = $hsn->cgst + $hsn->sgst + $hsn->igst;
                                                        $item_value = $quo->quo_value;
                                                        $item_taxval = (($item_value * $tax) / 100);
                                                        $item_total = $item_value + $item_taxval;
                                                        $igst = $quo->vat;

                                                        if ($common_state == $branch_state_code) {
                                                            $hsn->cgst = $hsn->sgst = $hsn->igst / 2;

                                                            $gst1 = $hsn->cgst;
                                                            $val1 = (($item_value * $gst1) / 100);
                                                            $gst2 = $hsn->sgst;
                                                            $val2 = (($item_value * $gst2) / 100);
                                                        } else {
                                                            $gst3 = $hsn->igst;
                                                            $val3 = (($item_value * $gst3) / 100);
                                                        }

                                                        $d_gst1 =    ($gst1 > 0) ? roundofnum($gst1) . ' %' : '';

                                                        $d_val1 =    ($val1 > 0) ? number_format($val1, 2) : '';

                                                        $d_gst2 =    ($gst2 > 0) ? roundofnum($gst2) . ' %' : '';

                                                        $d_val2 =    ($val2 > 0) ? number_format($val2, 2) : '';

                                                        $d_gst3 =    ($gst3 > 0) ? roundofnum($gst3) . ' %' : '';
                                                        $d_val3 =    ($val3 > 0) ? number_format($val3, 2) : '';



                                                        echo '<tr>
                                                                <td colspan = "" align="center" >' . $iSno . '</td>
                                                                <td colspan = "" align="left" >' . $itm->item_desciption . '</td>
                                                                <td colspan = "" align="center">' . $itm->item_code . '</td>
                                                                <td colspan = "" align="center" >' . $hsn->hsn_code . '</td>
                                                                <td colspan = ""align="right">' . $quo->selling_price . '</td>
                                                                <td colspan = "" align="center">' . $quo->quo_qty . '</td>
                                                                <td colspan = "" align="center">' . $uom_name . '</td>';
                                                        if ($dis_count > 0) {
                                                            echo '<td align="center">' . number_format($quo->quo_discount, 0) . " %" . '
                                                                    </td>';
                                                        }

                                                        echo ' <td colspan = "" align="right">' . number_format($quo->quo_value, 2) . '</td>';


                                                        if ($common_state == $branch_state_code) {
                                                            echo '  <td  align="center" colspan="">' . $d_gst1 . '</td>
                                                                <td align="right" colspan="">' . $d_val1 . '</td>
                                                                <td align="center" colspan="2">' . $d_gst2 . '</td>
                                                                <td align="right" colspan="">' . $d_val2 . '</td>';
                                                        } else {

                                                            echo '<td width="10%" align="center" colspan="3">' . $d_gst3 . '</td>
																	<td width="10%" align="right" colspan="3">' . $d_val3 . '</td>';
                                                        }
                                                        echo '<tr>';




                                                        $iSno++;
                                                        $item_val_no_tax = $item_val_no_tax + $item_value;

                                                        $tax_val1 += $val1;
                                                        $tax_val2 += $val2;
                                                        $tax_val3 += $val3;
                                                    }
                                                }

                                                ?>
                                                <?php
                                                $quo_details_sql = "SELECT * FROM tbl_quotation_details  WHERE quo_id =$obj->quo_id";
                                                $quo_details_result = $conn->query($quo_details_sql);
                                                $det = $quo_details_result->fetch(PDO::FETCH_OBJ);
                                                ?>


                                                <?php if ($dis_count > 0) { ?>
                                                    <tr>
                                                        <?php
                                                        if ($common_state == $branch_state_code) {  ?>

                                                            <td colspan="8" align="right"><b>SUB TOTAL</b></td>
                                                        <?php } else {  ?>
                                                            <td colspan="8" align="right"><b>SUB TOTAL</b></td>
                                                        <?php } ?>

                                                        <td align="right"><b><?php echo ($item_val_no_tax > 0) ? number_format($item_val_no_tax, 2) : ''; ?></b></td>
                                                        <?php
                                                        if ($common_state == $branch_state_code) {  ?>

                                                            <td colspan="2" align="right"><b><?php echo ($tax_val1 > 0) ? number_format($tax_val1, 2) : ''; ?></b></td>

                                                            <td colspan="3" align="right" colspan=""><b><?php echo ($tax_val2 > 0) ? number_format($tax_val2, 2) : ''; ?></b></td>

                                                        <?php } else {  ?>
                                                            <td align="right" colspan="4"><b><?php echo ($tax_val3 > 0) ? number_format($tax_val3, 2) : ''; ?></b></td>
                                                        <?php } ?>
                                                    </tr>



                                                <?php } else { ?>
                                                    <tr>
                                                        <?php
                                                        if ($common_state == $branch_state_code) {  ?>


                                                            <?php
                                                            if ($dis_count > 0) { ?>
                                                                <td colspan="1" width=""></td>
                                                            <?php } ?>

                                                            <td colspan="7" align="right"><b>SUB TOTAL</b></td>
                                                        <?php } else {  ?>
                                                            <td colspan="7" align="right"><b>SUB TOTAL</b></td>
                                                        <?php } ?>

                                                        <td align="right"><b><?php echo ($item_val_no_tax > 0) ? number_format($item_val_no_tax, 2) : ''; ?></b></td>
                                                        <?php
                                                        if ($common_state == $branch_state_code) {  ?>

                                                            <td colspan="2" align="right"><b><?php echo ($tax_val1 > 0) ? number_format($tax_val1, 2) : ''; ?></b></td>

                                                            <td colspan="3" align="right" colspan=""><b><?php echo ($tax_val2 > 0) ? number_format($tax_val2, 2) : ''; ?></b></td>

                                                        <?php } else {  ?>
                                                            <td align="right" colspan="5"><b><?php echo ($tax_val3 > 0) ? number_format($tax_val3, 2) : ''; ?></b></td>
                                                        <?php } ?>

                                                    </tr>

                                                <?php } ?>





                                                <?php

                                                $pack_sql = "SELECT * FROM tbl_quo_pack_details WHERE quo_id='" . $obj->quo_id . "'";
                                                $pack_res = $conn->query($pack_sql);

                                                if ($pack_res->rowCount() > 0) {

                                                    $sno = 1;
                                                    $pack_total = 0;
                                                    $pack_cgst_tot = 0;
                                                    $pack_sgst_tot = 0;
                                                    $pack_igst_tot = 0;

                                                    while ($pack = $pack_res->fetch()) {
                                                        $desc = $dbconn->GetSingleReconrd("mst_quo_details", "quo_pack_decp", "quo_id", $pack->quo_pack_decp);


                                                        if ($pack->quo_pack_percent == 1) {

                                                            $description = '<b>' . $desc . ' (' . roundofnum($pack->quo_pack_text) . ' %)</b>';
                                                        } elseif ($pack->quo_pack_percent == 2) {
                                                            $description = '<b>' . $desc . '</b>';
                                                        }

                                                        if ($pack->quo_pack_percent == 1) {
                                                            $quo_pack_val = $pack->quo_pack_taxable_val;
                                                        } elseif ($pack->quo_pack_percent == 2) {
                                                            $quo_pack_val = $pack->quo_pack_taxable_val;
                                                        }

                                                        if ($common_state == $branch_state_code) {
                                                            $cgst_vat = $hsn->cgst;
                                                            $cgst_val = (($quo_pack_val * $cgst_vat) / 100);
                                                            $sgst_vat = $hsn->sgst;
                                                            $sgst_val = (($quo_pack_val * $sgst_vat) / 100);
                                                        } else {
                                                            $igst_vat = $igst;
                                                            $igst_val = (($quo_pack_val * $igst_vat) / 100);
                                                        }

                                                        echo "<tr>
                                                                <td colspan='2' align='right'>" . strtoupper($description) . "</td>
                                                                <td></td>
                                                                <td align='center'>" . $pack->gst_id . "</td>";
                                                        if ($dis_count > 0) {
                                                            echo "<td></td>";
                                                        }
                                                        echo "<td></td>
                                                        <td></td>
                                                        <td></td>
                                                                <td align ='right'>";
                                                        echo ($quo_pack_val > 0)  ? number_format($quo_pack_val, 2) : '';
                                                        echo "</td>";

                                                        if ($common_state == $branch_state_code) {
                                                            echo "<td colspan='' align ='center'>";
                                                            echo ($cgst_vat > 0)  ? roundofnum($cgst_vat) . '%' : '';
                                                            echo "</td>";
                                                            echo "<td colspan='' align ='right'>";
                                                            echo ($cgst_val > 0)  ? number_format($cgst_val, 2) : '';
                                                            echo "</td>";
                                                            echo "<td align = 'center' colspan='2'>";
                                                            echo ($sgst_vat > 0)  ? roundofnum($sgst_vat) . '%' : '';
                                                            echo "</td>";
                                                            echo "<td align = 'right' colspan=''>";
                                                            echo ($sgst_val > 0)  ? number_format($sgst_val, 2) : '';
                                                            echo "</td>";
                                                        } else {
                                                            echo "<td align = 'center' colspan='3'> ";
                                                            echo ($igst_vat > 0)  ? roundofnum($igst_vat) . '%' : '';
                                                            echo "</td>";
                                                            echo "<td align = 'right' colspan='3'>";
                                                            echo ($igst_val > 0)  ? number_format($igst_val, 2) : '';
                                                            echo "</td>";
                                                        }
                                                        $pack_total = $pack_total + $quo_pack_val;
                                                        $pack_cgst_tot = $pack_cgst_tot + $cgst_val;
                                                        $pack_sgst_tot = $pack_sgst_tot + $sgst_val;
                                                        $pack_igst_tot = $pack_igst_tot + $igst_val;
                                                    }
                                                }
                                                $taxable_tot = $item_val_no_tax + $pack_total;

                                                $cgst_tot = $tax_val1 + $pack_cgst_tot;
                                                $sgst_tot = $tax_val2 + $pack_sgst_tot;
                                                $igst_tot = $tax_val3 + $pack_igst_tot;
                                                ?>


                                                <?php if ($dis_count > 0) { ?>
                                                    <tr>
                                                        <?php
                                                        if ($common_state == $branch_state_code) {  ?>

                                                            <td colspan="8" align="right"><b>TOTAL</b></td>

                                                        <?php } else {  ?>
                                                            <td colspan="8" align="right"><b>TOTAL</b></td>
                                                        <?php } ?>


                                                        <td align="right"><b><?php echo  number_format($taxable_tot, 2); ?></b></td>
                                                        <?php
                                                        if ($common_state == $branch_state_code) {  ?>

                                                            <td colspan="2" align="right"><b><?php echo ($cgst_tot > 0) ? '<i class="fa fa-inr"></i>' . number_format($cgst_tot, 2) : ''; ?></b></td>

                                                            <td colspan="3" align="right" colspan=""><b><?php echo ($sgst_tot > 0) ? number_format($sgst_tot, 2) : ''; ?></b></td>

                                                        <?php } else {  ?>
                                                            <td align="right" colspan="4"><b><?php echo ($igst_tot > 0) ? '<i class="fa fa-inr"></i>' . number_format($igst_tot, 2) : ''; ?></b></td>
                                                        <?php } ?>
                                                    </tr>

                                                <?php } else { ?>

                                                    <tr>
                                                        <?php
                                                        if ($common_state == $branch_state_code) {  ?>

                                                            <td colspan="7" align="right"><b>TOTAL</b></td>

                                                        <?php } else {  ?>
                                                            <td colspan="7" align="right"><b>TOTAL</b></td>
                                                        <?php } ?>


                                                        <td align="right"><b><?php echo  number_format($taxable_tot, 2); ?></b></td>
                                                        <?php
                                                        if ($common_state == $branch_state_code) {  ?>

                                                            <td colspan="2" align="right"><b><?php echo ($cgst_tot > 0) ? '<i class="fa fa-inr"></i>' . number_format($cgst_tot, 2) : ''; ?></b></td>

                                                            <td colspan="3" align="right" colspan=""><b><?php echo ($sgst_tot > 0) ? number_format($sgst_tot, 2) : ''; ?></b></td>

                                                        <?php } else {  ?>
                                                            <td align="right" colspan="5"><b><?php echo ($igst_tot > 0) ? '<i class="fa fa-inr"></i>' . number_format($igst_tot, 2) : ''; ?></b></td>
                                                        <?php } ?>
                                                    </tr>
                                                <?php } ?>

                                                <?php

                                                $net_total = $taxable_tot + $cgst_tot + $sgst_tot + $igst_tot;

                                                $total_in_words = ucwords(number_to_words(number_format($net_total, 0, ".", ""))) . " ";
                                                $net_tax_total = $cgst_tot + $sgst_tot + $igst_tot;

                                                $number = $net_total;
                                                $precision = 2;
                                                $net_total = substr(number_format($number, $precision + 1, '.', ''), 0, -1);


                                                $round_amt = $net_total;

                                                $round_val = round($round_amt) - $net_total;
                                                ?>
                                                <tr>

                                                    <?php if ($common_state == $branch_state_code) { ?>

                                                        <?php
                                                        if ($dis_count > 0) { ?>

                                                        <?php } ?>

                                                        <td height="" colspan="12" align="right"><b>Round Off (+/-)</b></td>
                                                        <td align="right" colspan="2"><?php echo number_format($round_val, 2); ?></td>
                                                    <?php } else { ?>
                                                        <td height="" colspan="12" align="right"><b>Round Off (+/-)</b></td>
                                                        <td align="right" colspan=" "><?php echo number_format($round_val, 2); ?></td>
                                                    <?php } ?>
                                                </tr>

                                                <tr>

                                                    <?php if ($common_state == $branch_state_code) { ?>

                                                        <?php
                                                        if ($dis_count > 0) { ?>

                                                        <?php } ?>

                                                        <td colspan="12" align="right"><b>GRAND TOTAL VALUE (In Figure)</b></td>
                                                        <td colspan="2" align="right"><b><?php echo number_format(round($net_total), 2) ?></b></td>
                                                    <?php } else { ?>
                                                        <td colspan="12" align="right"><b>GRAND TOTAL VALUE (In Figure)</b></td>
                                                        <td align="right"><b><?php echo number_format(round($net_total), 2) ?></b></td>
                                                    <?php } ?>
                                                </tr>
                                                <tr>



                                                    <?php if ($common_state == $branch_state_code) { ?>

                                                        <?php
                                                        if ($dis_count > 0) { ?>
                                                        <?php } ?>

                                                        <td colspan="14" align="center"><b>Total Value (In Words) : <?php echo $total_in_words; ?>Rupees Only</b></td>

                                                    <?php } else { ?>

                                                        <td colspan="13" align="center"><b>Total Value (In Words) : <?php echo $total_in_words; ?>Rupees Only</b></td>
                                                    <?php } ?>
                                                </tr>
                                                <tr>

                                                    <?php if ($common_state == $branch_state_code) { ?>
                                                        <?php
                                                        if ($dis_count > 0) { ?>

                                                        <?php } ?>

                                                        <td colspan="14" style="font-weight:bold; text-align:center; background-color: #bebebe;">TERMS & CONDITIONS</td>
                                                    <?php } else { ?>


                                                        <td colspan="13" style="font-weight:bold; text-align:center; background-color: #bebebe;">TERMS & CONDITIONS</td>
                                                    <?php } ?>

                                                </tr>
                                                <?php $sno = 1;
                                                if ($obj->terms_con_id1 != '') { ?>
                                                    <tr>
                                                        <td style="vertical-align:top;"><?php echo $sno; ?></td>
                                                        <td style="vertical-align: top; text-align: left; font-weight: bold;">PAYMENT </td>
                                                        <td colspan="13" align="left"><?php echo $pay_content; ?></td>
                                                    </tr>
                                                <?php $sno++;
                                                } ?>

                                                <?php if ($obj->terms_con_id2 != '') { ?>
                                                    <tr>
                                                        <td style="vertical-align:top;"><?php echo $sno; ?></td>
                                                        <td style="vertical-align: top; text-align: left; font-weight: bold;">CUSTOMER SCOPE</td>
                                                        <td colspan="13" align="left"><?php echo $customer_scope; ?></td>
                                                    </tr>
                                                <?php $sno++;
                                                } ?>

                                                <?php if ($obj->terms_con_id3 != '') { ?>
                                                    <tr>
                                                        <td style="vertical-align:top;"><?php echo $sno; ?></td>
                                                        <td style="vertical-align: top; text-align: left; font-weight: bold;">DELIVERY PERIOD</td>
                                                        <td colspan="13" align="left"><?php echo $delivery_period; ?></td>
                                                    </tr>
                                                <?php $sno++;
                                                } ?>

                                                <?php if ($obj->terms_con_id4 != '') { ?>
                                                    <tr>
                                                        <td style="vertical-align:top;"><?php echo $sno; ?></td>
                                                        <td style="vertical-align: top; text-align: left; font-weight: bold;">DELIVERY TERMS</td>
                                                        <td colspan="13" align="left"><?php echo $delivery_terms; ?></td>
                                                    </tr>
                                                <?php $sno++;
                                                } ?>

                                                <?php if ($obj->terms_con_id5 != '') { ?>
                                                    <tr>
                                                        <td style="vertical-align:top;"><?php echo $sno; ?></td>
                                                        <td style="vertical-align: top; text-align: left; font-weight: bold;">INSTALLATION</td>
                                                        <td colspan="13" align="left"><?php echo $installation; ?></td>
                                                    </tr>
                                                <?php $sno++;
                                                } ?>

                                                <?php if ($obj->terms_con_id6 != '') { ?>
                                                    <tr>
                                                        <td style="vertical-align:top;"><?php echo $sno; ?></td>
                                                        <td style="vertical-align: top; text-align: left; font-weight: bold;">WARRANTY</td>
                                                        <td colspan="13" align="left"><?php echo $warranty; ?></td>
                                                    </tr>
                                                <?php $sno++;
                                                } ?>
                                                <?php if ($obj->terms_con_id7 != '') { ?>
                                                    <tr>
                                                        <td style="vertical-align:top;"><?php echo $sno; ?></td>
                                                        <td style="vertical-align: top; text-align: left; font-weight: bold;">VALIDITY</td>
                                                        <td colspan="13>" align="left"><?php echo $validity; ?></td>
                                                    </tr>
                                                <?php $sno++;
                                                } ?>
                                                <?php if ($obj->terms_con_id8 != '') { ?>
                                                    <tr>
                                                        <td style="vertical-align:top;"><?php echo $sno; ?></td>
                                                        <td style="vertical-align: top; text-align: left; font-weight: bold;">TRANSPORTATION</td>
                                                        <td colspan="13" align="left"><?php echo $transportation; ?></td>
                                                    </tr>
                                                <?php $sno++;
                                                } ?>
                                                <tr>
                                                    <td colspan="14" align="right"><b>FOR BENZEAR INDUSTRIAL ENTERPRISES<br /><br /><br /><br /></b>Authorised Signature</td>
                                                </tr>

                                                <tr>
                                                    <td colspan="15" style="font-weight:bold; background-color: #bebebe; text-align:center;">www.benzear-bie.com</td>
                                                </tr>

                                            </tbody>
                                           <!-- <tfoot>
                                                <tr>
                                                    <td colspan="13">
                                                        <table width="100%" style="border: none !important;table-layout: fixed;" cellspacing="0" cellpadding="0">
                                                            <tr>

                                                                <td style="padding-top: 100px; border-top: none !important;border-bottom: none !important; border-left: none !important;" class="text-center">
                                                                    <b>PREPARED BY</b>
                                                                </td>
                                                                <td style="padding-top: 100px; border-top: none !important;border-bottom: none !important; border-left: none !important;" class="text-center">
                                                                    <b>STORE INCHARGE SIGN</b>
                                                                </td>
                                                                <td style="padding-top: 100px; border-top: none !important;border-bottom: none !important; border-left: none !important;" class="text-center">
                                                                    <b>PURCHASE INCHARGE SIGN</b>
                                                                </td>
                                                                <td style="padding-top: 100px; border-top: none !important;border-bottom: none !important; border-left: none !important; border-right: none !important;" class=" text-center">
                                                                    <b>C.E.O SIGN</b>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </tfoot>-->
                                        </table>
                                    </div>
                                </div>
                            
                                <?php 
                                    if ($obj->quo_verify_status == 1 && $obj->quo_approve_status == 0) {
                                        if ($_SESSION['_user_id'] == 1 || ($obj->bie_branch_id==$_SESSION['_user_branch'])) {
                                ?>
                                       
                                        <br>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Remarks </label>
                                                    <textarea name="quo_approve_remarks" id="quo_approve_remarks" class="form-control" rows="2" maxlength="250"><?php echo $obj->quo_approve_remarks; ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                 <?php
                                        }
                                    } 
                                ?>
                            </div>
                                <?php 
                                    if ($obj->quo_verify_status == 1 && $obj->quo_approve_status == 0) {
                                        if ($_SESSION['_user_id'] == 1 || ($obj->bie_branch_id==$_SESSION['_user_branch'])) {
                                ?>
                                        <div class="card-footer text-center">
                                            <input type="hidden" name="quo_id" id="quo_id" value="<?php echo $_REQUEST['quo_id']; ?>" />
                                            <INPUT class="btn btn-custom" type="button" id="APPROVE" name="APPROVE" value="Approve">
                                            <INPUT class="btn btn-danger" type="button" id="REJECT" name="REJECT" value="Reject">
                                        </div>
                                <?php
                                        }
                                    } 
                                ?>
                        </div>
                        <div class="row">
                            <div class="text-left col-lg-6">
                                <div class="rec_create_dets"><b>Created by : </b><?php echo $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id ", $obj->created_by) . ' on ' . date('d-M-y @ H:i', strtotime($obj->created_dtm)); ?></b><br>
                                <b>Remarks : </b> <?php echo $obj->quo_remarks; ?>
                                </div>
                            </div>
                            <div class="text-right col-lg-6">
                                <?php
                                if ($obj->quo_approve_status != 0) {
                                ?>
                                    <div class="rec_create_dets"><b>Approved by : </b><?php echo $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id ", $obj->quo_approve_id) . ' on ' . date('d-M-y @ H:i', strtotime($obj->quo_approve_date_time)); ?></b><br><b>Remarks : </b> <?php echo $obj->quo_approve_remarks; ?>
                                    </div>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
             <?php include("inc/common/footer.php") ?>
        </div>
    </div>
</body>
<script>
    $('#APPROVE').click(function() {

        var quo_id = $('#quo_id').val();
        var remarks = $('#quo_approve_remarks').val();
        var task = "QUO_APP";

        $.ajax({
            type: 'post',
            url: 'inc/cis_ajax/jquery_quo_approval.php',
            data: {
                "id": quo_id,
                "task": task,
                "remarks": remarks
            },
            beforeSend: function() {
                if (confirm('Are you sure to Approve this Quotation Order..?')) {} else {
                    return false;
                }
            },
            complete: function() {},
            success: function(result) {

                window.location.href = "javascript:history.go(-1)";
            }
        });
        return false;
    });

    $('#REJECT').click(function() {

        if ($('#quo_approve_remarks').val() == '') {
            alert("Please enter the  Rejection Remarks..!");
            $('#quo_approve_remarks').focus();
            return false;
        }

        var quo_id = $('#quo_id').val();
        var remarks = $('#quo_approve_remarks').val();
        var task = "QUO_REJ";
        var quo_slno = $('#quo_id').val();
        $.ajax({
            type: 'post',
            url: 'inc/cis_ajax/jquery_quo_approval.php',
            data: {
                "id": quo_id,
                "task": task,
                "remarks": remarks,
                "quo_slno": quo_slno
            },
            beforeSend: function() {
                if (confirm('Are you sure to Reject this Quotation Order..?')) {} else {
                    return false;
                }
            },
            complete: function() {},
            success: function(result) {

                window.location.href = "javascript:history.go(-1)";
            }
        });
        return false;
    });
</script>

</html>