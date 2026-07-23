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


$dis_count_val = $dbconn->GetSingleReconrd("tbl_sales_order_details", "so_discount", "so_id", $_REQUEST['so_id']);
$dis_count = $dbconn->GetCount("tbl_sales_order_details", "so_discount>0 AND so_id", $_REQUEST['so_id']);
if (isset($_REQUEST['so_id'])) {
    $result = $conn->query("SELECT * FROM tbl_sales_order WHERE so_id = '" . $_REQUEST['so_id'] . "'");
    if ($result->rowCount() > 0) {
        $obj = $result->fetch(PDO::FETCH_OBJ);

        $quo_value = $obj->quo_value;
        $dispatch_date1 = $obj->despatch_date;
        $sales_date = $obj->so_date;


        $branch_state_code = $dbconn->GetSingleReconrd("mst_branch", "branch_state_code", "branch_status = 1 AND branch_id ", $obj->bie_branch_id);

        if ($obj->quo_id != 0) {
            $quo_no = $dbconn->GetSingleReconrd("tbl_quotation", "quo_refno", "quo_id", $obj->quo_id);
            $quo_date = $dbconn->GetSingleReconrd("tbl_quotation", "quo_date", "quo_id", $obj->quo_id);



            if ($quo_date != "0000-00-00" && $quo_date != "") {
                $quodate = date("d-m-Y", strtotime($quo_date));
            }

            $quo_no_date = $quo_no . ' / ' . $quodate;
        }
    }

    //----------3rd Table----mst supplier--------------//
    $branch_add = $conn->query("SELECT * FROM mst_supplier_new WHERE supp_id = " . $obj->branch_id);
    $bch = $branch_add->fetch(PDO::FETCH_OBJ);

    $get_add = $conn->query("SELECT * FROM mst_supplier_new WHERE supp_id = " . $obj->supp_id);
    if ($get_add->rowCount() > 0) {
        $obj1 = $get_add->fetch(PDO::FETCH_OBJ);
        $add = "";
        $add .= $obj1->supp_add1;

        if ($obj1->supp_add2 != "") {
            $add .= '' . $obj1->supp_add2;
        }
		$state_id = $obj1->state_id;

		$city_name = $dbconn->GetSingleReconrd("mst_city", "city_name", "city_id",  $obj1->city_id);

        $district_name = $dbconn->GetSingleReconrd("mst_district", "district_name", "district_id", $obj1->district_id);

        $state_name = $dbconn->GetSingleReconrd("mst_state", "state_name", "state_status = 1 AND state_id", $state_id);

       
		$pincode = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_pincode", "supp_id", $obj1->supp_id);


        $branch_add =  $conn->query("SELECT * FROM mst_customer_branch WHERE branch_id = " . $obj->branch_id);
        $bch = $branch_add->fetch(PDO::FETCH_OBJ);

        $ref_phone_no =  $dbconn->GetSingleReconrd("tbl_quotation", "ref_phone_no", "quo_id", $obj->quo_id);
        $ref_email = $dbconn->GetSingleReconrd("tbl_quotation", "ref_email", "quo_id", $obj->quo_id);
        $state_code = $dbconn->GetSingleReconrd("mst_state", "state_code", "state_status = 1 AND state_id ", $obj1->state_id);
        $state_name1 = $dbconn->GetSingleReconrd("mst_state", "state_name", "state_status = 1 AND state_id ", $bch->state_id);

        // $receipt_dt = $dbconn->GetSingleReconrd("tbl_receipt", "pay_date", "state_status = 1 AND state_id ", $bch->state_id);

        $state_code1 = $dbconn->GetSingleReconrd("mst_state", "state_code", "state_status = 1 AND state_id ", $bch->state_id);

        if ($obj->branch_id > 0) {
            $add .= '<br>' . $bch->branch_add1;
            if ($bch->branch_add2 != "") {
                $add .= '<br>' . $bch->branch_add2;
            }

            if ($state_name1 != '') {
                $add .= '<br>' . $state_name1 . ' - ' . $bch->branch_pincode;
            }

            if ($ref_phone_no != '') {
                $add .= '<br><b>Ph: </b>' . $ref_phone_no;
            }

            if ($ref_email != '') {
                $add .= '<br><b>E-Mail: </b> ' . $ref_email;
            }

            if ($supp->supp_website != '') {
                $add .= '<br><b>Web: </b> ' . $supp->supp_website;
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
                $add .= '<br>' . $district_name;
            }



            if ($state_name != '') {
                $add .= '<br>' . $state_name . ' - ' . $pincode;
            }


            if ($ref_phone_no != '') {
                $add .= '<br><b>Ph: </b> ' . $ref_phone_no;
            }

            if ($ref_email != '') {
                $add .= '<br><b>E-Mail: </b> ' . $ref_email;
            }

            if ($supp->supp_website != '') {
                $add .= '<br><b>Web: </b> ' . $supp->supp_website;
            } else {
                $add .= '<br><b>Web: </b> ' . "";
            }
        }



        $gstin = $obj1->supp_gst;
        $pan = $obj1->supp_pan;

        if ($ref_phone_no != '') {
            $mobile = $ref_phone_no;
        } elseif ($supp_arr[0] != '') {
            $mobile = $obj1->supp_mobile1;
        }

        if ($ref_email != '') {
            $email = $ref_email;
        } elseif ($supp_arr[1] != '') {
            $email = $obj1->supp_email;
        }
        $contact_person = $obj1->supp_contact_person1;
    }
}

if ($obj->branch_id > 0) {
    // echo $state_code1;
    $common_state = $state_code1;
} else {
    // echo $state_code;
    $common_state = $state_code;
}

?>
<?php if ($dis_count > 0) {
    $col_ref = 6;
    $col_gst = 2;
    $col_gstin = 4;
    $col_con = 3;
    $col_quo_dt = 6;
    $col_so_sl = '2%';
    $col_so_dec = '18%';
    $pre_itmcode = '10%';
    $pre_hsn = '9%';
    $pre_rte = '2%';
    $pre_qty = '6%';
    $pre_unit = '6%';
    $pre_dis = '8%';
    $pre_tax = '12%';
    // $pre_cgs = '10%';
    // $pre_sgs = '10%';
    $col_sp_rate = 9;
    $col_su_tot = 8;
    $col_tot = 8;
    $col_rou = 12;
    $col_igst = "10%";
    $col_ig_rate = 3;
    $col_ig_amount = '';
    $col_cgst = 2;
    $col_sgst = 2;
    $col_cg_rate = "6%";
    $col_cg_amount = "6%";
    $col_sg_rate = "6%";
    $col_sg_amount = "6%";
} else {
    $col_ref = 6;
    $col_gst = 3;
    $col_gstin = 3;
    $col_con = 2;
    $col_quo_dt = 6;
    $col_so_sl = '2%';
    $col_so_dec = '22%';
    $pre_itmcode = '14%';
    $pre_hsn = '13%';
    $pre_rte = '2%';
    $pre_qty = '6%';
    $pre_unit = '6%';
    $pre_tax = '20%';
    $col_sp_rate = 9;
    $col_su_tot = 7;
    $col_tot = 7;
    $col_rou = 12;
    $col_igst = "15%";
    $col_ig_rate = '11%';
    $col_ig_amount = '10%';
    $col_cg_rate = "6%";
    $col_cg_amount = "6%";
    $col_sg_rate = "6%";
    $col_sg_amount = "6%";
    $col_cgst = 2;
    $col_sgst = 2;
    $col_taxx = 2;
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Direct Sales Order</title>
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
                margin: 0.5,
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
                            <span class="breadcrumb-item active">Sales</span>
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
                                <h6 class="card-title">Sales Order Details - <?php echo  $obj->so_refno; ?></h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" href="lst_sales_order.php" title="SO List"><i class="icon-arrow-left52 mr-2"></i></a>
                                        <?php
                                        if ($obj->so_verify_status == 1 && $obj->so_status == 5) {
                                        ?>
                                            <a class="list-icons-item" href="javascript:PrintPartsNew(new Array('print_content1'),'<?php echo $obj->so_refno; ?>');" id="print_page" title="Print SO"><i class="icon-printer2 mr-1"></i></a>
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
                                                    <td colspan="14">
                                                        <span style="float: right;"><img src="img/BIE_logo.png" alt="" width="50px" height="auto"></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center" colspan="14" style="font-size: 20px; font-weight: bold; ">SALES ORDER</td>
                                                </tr>

                                                <tr>
                                                    <td colspan="14" style="font-weight:bold; background-color: #bebebe;">Dealer Address</td>
                                                </tr>

                                                <tr>
                                                    <td colspan="<?php echo $col_ref; ?>" rowspan="3">
                                                        <p align="left"><?php echo '<b>' . $obj1->supp_name . '</b>' . '<br/>' . $add; ?></p>
                                                    </td>

                                                <tr>
                                                    <td colspan="3"><b>SO NO.</b></td>
                                                    <td colspan="5"><?php echo $obj->so_refno;  ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3"><b>SO DATE</b></td>
                                                    <td colspan="5"><?php echo date('d-m-Y', strtotime($sales_date)); ?></td>
                                                </tr>
                                                </tr>
                                                <tr>
                                                    <td colspan="<?php echo $col_gst; ?>" style="background-color: #bebebe;"><b>GSTIN NO.</b></td>
                                                    <td colspan="<?php echo $col_gstin; ?>"><?php echo $gstin; ?></td>
                                                    <td colspan=<?php echo $col_con; ?> style="background-color: #bebebe;"><b>CONTACT NO.</b></td>
                                                    <td colspan="6"><?php echo $mobile; ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan=<?php echo $col_gst; ?> style="background-color: #bebebe;"><b>PAN No.</b></td>
                                                    <td colspan="<?php echo $col_gstin; ?>"><?php echo $pan; ?></td>
                                                    <td colspan=<?php echo $col_con; ?> style="background-color: #bebebe;"><b>CONTACT PERSON NAME</b></td>
                                                    <td colspan="6"><?php echo $contact_person; ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan=<?php echo $col_gst; ?> style="background-color: #bebebe;"><b>STATE CODE</b></td>
                                                    <td colspan="<?php echo $col_gstin; ?>"><?php echo $common_state; ?></td>

                                                    <td colspan=<?php echo $col_con; ?> style="background-color: #bebebe;"><b>E-MAIL ID</b></td>
                                                    <td colspan="6"><?php echo $email; ?></td>
                                                </tr>

                                                <tr>
                                                    <td colspan=<?php echo $col_quo_dt; ?> align="left"><b>QUOTATION NO & DT : </b><?php echo $quo_no_date; ?></td>

                                                    <td colspan="10" align="left"><b>DISPATCH DATE : </b><?php echo date('d-m-Y', strtotime($dispatch_date1)); ?></td>
                                                </tr>





                                                <tr style="font-weight:bold; background-color: #bebebe; text-align:center;">
                                                    <td style="border-bottom: none !important; " width="<?php echo $col_so_sl; ?>">SL</td>

                                                    <td colspan="" width="<?php echo $col_so_dec; ?>">Description of Goods</td>
                                                    <td width="<?php echo $pre_itmcode; ?>">Item Code</td>
                                                    <td width="<?php echo $pre_hsn; ?>">HSN Code</td>
                                                    <td width="<?php echo $pre_rte; ?>">Rate</td>
                                                    <td width="<?php echo $pre_qty; ?>">Qty</td>
                                                    <td colspan="" width="<?php echo $pre_unit; ?>"">Unit</td>
                                                   
                                                   
                                                   <?php
                                                    if ($dis_count > 0) { ?>
                                                        <td colspan=" 2" width=" <?php echo $pre_dis; ?>">Discount</td>
                                                <?php } ?>


                                                <td colspan="<?php echo $col_taxx; ?>" width="<?php echo $pre_tax; ?>">Taxable Value</td>
                                                <?php if ($common_state == $branch_state_code) { ?>
                                                    <td colspan="<?php echo $col_cgst; ?>" width="">CGST</td>
                                                    <td width="" colspan="<?php echo $col_sgst; ?>">SGST</td>
                                                <?php } else { ?>
                                                    <td width="<?php echo $col_igst; ?>" colspan="5">IGST</td>
                                                <?php } ?>
                                                </tr>

                                                <tr style="font-weight:bold; background-color: #bebebe; text-align:center;">

                                                    <?php if ($common_state == $branch_state_code) { ?>
                                                        <td colspan="<?php echo $col_sp_rate; ?>"></td>


                                                        <?php
                                                        if ($dis_count > 0) { ?>
                                                            <td colspan="1" width=""></td>
                                                        <?php } ?>
                                                        <td width="<?php echo $col_cg_rate; ?>" colspan="">Rate</td>
                                                        <td width="<?php echo $col_cg_amount; ?>" colspan="">Amount</td>
                                                        <td width="<?php echo $col_sg_rate; ?>" colspan="">Rate</td>
                                                        <td width="<?php echo $col_sg_amount; ?>" colspan="">Amount</td>
                                                    <?php } else { ?>
                                                        <td colspan="<?php echo $col_sp_rate; ?>"></td>
                                                        <td width="<?php echo $col_ig_rate; ?>" colspan="3">Rate</td>
                                                        <td width="<?php echo $col_ig_amount; ?>" colspan="4">Amount</td>
                                                    <?php } ?>

                                                </tr>
                                            </thead>
                                            <tbody>

                                                <?PHP
                                                $quo_sql = "SELECT * FROM tbl_sales_order as a LEFT JOIN tbl_sales_order_details as b ON a.so_id = b.so_id WHERE a.so_id = '" . $_REQUEST['so_id'] . "'";

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

                                                        $sos_discount = $dbconn->GetSingleReconrd("tbl_sales_order_details", "so_discount", "so_id", $obj->so_id);

                                                        $hsn_sql = $conn->query("SELECT * FROM mst_hsn WHERE hsn_id = '" . $itm->item_hsn . "'");
                                                        $hsn = $hsn_sql->fetch(PDO::FETCH_OBJ);


                                                        $tax = $hsn->cgst + $hsn->sgst + $hsn->igst;
                                                        $item_value = $quo->so_value;
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
                                                                <td colspan = ""align="right">' . $quo->so_selling_price . '</td>
                                                                <td colspan = "" align="center">' . $quo->so_qty . '</td>
                                                                <td colspan = "" align="center">' . $uom_name . '</td>';

                                                        if ($dis_count > 0) {

                                                            echo ' <td colspan = "1"  align="center">' . number_format($quo->so_discount, 0) . "%" . '</td>';
                                                        }

                                                        echo ' <td colspan = "2" align="right">' . number_format($quo->so_value, 2) . '</td>';

                                                        if ($common_state == $branch_state_code) {
                                                            echo '  <td  align="center" colspan="1">' . $d_gst1 . '</td>
                                                                <td align="right" colspan="1">' . $d_val1 . '</td>
                                                                <td align="center" colspan="1">' . $d_gst2 . '</td>
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

                                                $quo_details_sql = "SELECT * FROM tbl_sales_order_details  WHERE so_id =$obj->so_id";
                                                $quo_details_result = $conn->query($quo_details_sql);
                                                $det = $quo_details_result->fetch(PDO::FETCH_OBJ);

                                                ?>

                                                <?php if ($dis_count > 0) { ?>


                                                    <tr>
                                                        <td colspan="9" <?php echo $col_su_tot; ?>" align="right"><b>SUB TOTAL</b></td>
                                                        <td align="right"><b><?php echo ($item_val_no_tax > 0) ? number_format($item_val_no_tax, 2) : ''; ?></b></td>
                                                        <?php
                                                        if ($common_state == $branch_state_code) {  ?>

                                                            <td colspan="2" align="right"><b><?php echo ($tax_val1 > 0) ? number_format($tax_val1, 2) : ''; ?></b></td>

                                                            <td colspan="3" align="right" colspan=""><b><?php echo ($tax_val2 > 0) ? number_format($tax_val2, 2) : ''; ?></b></td>

                                                        <?php } else {  ?>
                                                            <td align="right" colspan="5"><b><?php echo ($tax_val3 > 0) ? number_format($tax_val3, 2) : ''; ?></b></td>
                                                        <?php } ?>

                                                    </tr>
                                                <?php } else { ?>
                                                    <tr>
                                                        <td colspan="<?php echo $col_su_tot; ?>" align="right"><b>SUB TOTAL</b></td>
                                                        <td align="right"><b><?php echo ($item_val_no_tax > 0) ? number_format($item_val_no_tax, 2) : ''; ?></b></td>
                                                        <?php
                                                        if ($common_state == $branch_state_code) {  ?>

                                                            <td colspan="3" align="right"><b><?php echo ($tax_val1 > 0) ? number_format($tax_val1, 2) : ''; ?></b></td>

                                                            <td colspan="3" align="right" colspan=""><b><?php echo ($tax_val2 > 0) ? number_format($tax_val2, 2) : ''; ?></b></td>

                                                        <?php } else {  ?>
                                                            <td align="right" colspan="5"><b><?php echo ($tax_val3 > 0) ? number_format($tax_val3, 2) : ''; ?></b></td>
                                                        <?php } ?>

                                                    </tr>
                                                <?php } ?>


                                                <!--------------Package Details--------------------->
                                                <?php

                                                $pack_sql = "SELECT * FROM tbl_sales_order_pack_dts WHERE so_id='" . $obj->so_id . "'";
                                                $pack_res = $conn->query($pack_sql);

                                                if ($pack_res->rowCount() > 0) {

                                                    $sno = 1;
                                                    $pack_total = 0;
                                                    $pack_cgst_tot = 0;
                                                    $pack_sgst_tot = 0;
                                                    $pack_igst_tot = 0;


                                                    while ($pack = $pack_res->fetch()) {
                                                        $desc = $dbconn->GetSingleReconrd("mst_quo_details", "quo_pack_decp", "quo_id", $pack->quo_pack_decp);

                                                        $desc = $dbconn->GetSingleReconrd("mst_quo_details", "quo_pack_decp", "quo_id", $pack->pack_decp);
                                                        if ($pack->pack_percent == 1) {
                                                            $description = '<b>' . $desc . ' (' . $pack->pack_text . ' %)</b>';

                                                            $taxable_charge =  $pack->pack_taxable_val;
                                                        } elseif ($pack->pack_percent == 2) {
                                                            $description = '<b>' . $desc . '</b>';

                                                            $taxable_charge = $pack->pack_taxable_val;
                                                        }


                                                        $pack_hsn_code = $dbconn->GetSingleReconrd("mst_hsn", "hsn_code", "hsn_id", $pack->gst_id);
                                                        $cgst = $dbconn->GetSingleReconrd("mst_hsn", "cgst", "hsn_status = '1' AND hsn_id", $pack->gst_id);
                                                        $sgst = $dbconn->GetSingleReconrd("mst_hsn", "sgst", "hsn_status = '1' AND hsn_id", $pack->gst_id);


                                                        if ($common_state == $branch_state_code) {
                                                            $cgst_vat = $hsn->cgst;
                                                            $cgst_val = (($taxable_charge * $cgst_vat) / 100);
                                                            $sgst_vat = $hsn->sgst;
                                                            $sgst_val = (($taxable_charge * $sgst_vat) / 100);
                                                        } else {
                                                            $igst_vat = $hsn->igst;
                                                            $igst_val = (($taxable_charge * $igst_vat) / 100);
                                                        }



                                                        if ($dis_count > 0) {

                                                            echo "<tr>
                                                            <td colspan='2' align='right'>" . strtoupper($description) . "</td>
                                                            <td></td>
                                                            <td align='center'>" . $pack->gst_id . "</td>";

                                                            if ($dis_count > 0) {
                                                                echo "<td colspan='1'></td>";
                                                            }

                                                            echo "<td></td>
                                                            <td colspan='1'></td>
                                                            
                                                            <td colspan='1'align ='right'>";
                                                            echo "</td>";





                                                            if ($common_state == $branch_state_code) {

                                                                echo "
                                                                <td colspan='2'align = 'right'>" . number_format($taxable_charge, 2) . "</td>

                                                                <td colspan='1' align = 'center'>" . number_format($cgst_vat) . "%</td>

                                                                   <td colspan='1' align = 'right'>" . number_format($cgst_val, 2) . "</td>
                                                                   <td colspan='1' align = 'center'>" . number_format($sgst_vat) . "%</td>
                                                                   <td colspan='2' align = 'right'>" . number_format($sgst_val, 2) . "</td>";
                                                            } else {

                                                                echo "<td  colspan='3' align = 'right'>" . number_format($taxable_charge, 2) . "</td>

                                                                

                                                                <td colspan='2' align = 'center'>" . number_format($igst_vat) . "%</td>

                                                                  <td  colspan='3' align = 'right'>" . number_format($igst_val, 2) . "</td>";
                                                            }
                                                            echo "</tr>";
                                                        } else {


                                                            echo "<tr>
                                                            <td colspan='2' align='right'>" . strtoupper($description) . "</td>
                                                            <td></td>
                                                          
                                                            <td align='center'>" . $pack->gst_id . "</td>";

                                                            if ($dis_count > 0) {
                                                                echo "<td colspan='1'></td>";
                                                            }

                                                            echo "<td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td align ='right'>";
                                                            echo ($taxable_charge > 0)  ? number_format($taxable_charge, 2) : '';
                                                            echo "</td>";


                                                            if ($common_state == $branch_state_code) {

                                                                echo " <td colspan='2' align = 'right'>" . number_format($cgst_vat) . "%</td>
                                                                   <td colspan='1' align = 'right'>" . number_format($cgst_val, 2) . "</td>
                                                                   <td colspan='1' align = 'right'>" . number_format($sgst_vat) . "%</td>
                                                                   <td colspan='2' align = 'right'>" . number_format($sgst_val, 2) . "</td>";
                                                            } else {

                                                                echo "<td  colspan='3' align = 'center'>" . number_format($igst_vat) . "%</td>

                                                                  <td  colspan='3' align = 'right'>" . number_format($igst_val, 2) . "</td>";
                                                            }
                                                            echo "</tr>";
                                                        }



                                                        $pack_total = $pack_total + $taxable_charge;
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

                                                        <td colspan="9" <?php echo $col_tot; ?>" align="right"><b>TOTAL</b></td>
                                                        <td align="right"><b><?php echo  number_format($taxable_tot, 2); ?></b></td>
                                                        <?php
                                                        if ($common_state == $branch_state_code) {  ?>

                                                            <td colspan="2" align="right"><b><?php echo ($cgst_tot > 0) ? '<i class="fa fa-inr"></i>' . number_format($cgst_tot, 2) : ''; ?></b></td>
                                                            <td colspan="2" align="right" colspan=""><b><?php echo ($sgst_tot > 0) ? number_format($sgst_tot, 2) : ''; ?></b></td>

                                                        <?php } else {  ?>

                                                            <td align="right" colspan="5"><b><?php echo ($igst_tot > 0) ? '<i class="fa fa-inr"></i>' . number_format($igst_tot, 2) : ''; ?></b></td>
                                                        <?php } ?>

                                                    </tr>

                                                <?php } else { ?>
                                                    <tr>

                                                        <td colspan="<?php echo $col_tot; ?>" align="right"><b>TOTAL</b></td>
                                                        <td align="right"><b><?php echo  number_format($taxable_tot, 2); ?></b></td>
                                                        <?php
                                                        if ($common_state == $branch_state_code) {  ?>

                                                            <td colspan="3" align="right"><b><?php echo ($cgst_tot > 0) ? '<i class="fa fa-inr"></i>' . number_format($cgst_tot, 2) : ''; ?></b></td>
                                                            <td colspan="2" align="right" colspan=""><b><?php echo ($sgst_tot > 0) ? number_format($sgst_tot, 2) : ''; ?></b></td>

                                                        <?php } else {  ?>

                                                            <td align="right" colspan="5"><b><?php echo ($igst_tot > 0) ? '<i class="fa fa-inr"></i>' . number_format($igst_tot, 2) : ''; ?></b></td>
                                                        <?php } ?>

                                                    </tr>
                                                <?php } ?>

                                                <?php

                                                $net_total = $taxable_tot + $cgst_tot + $sgst_tot + $igst_tot;
                                                $total_in_words = 'RUPEES ' . ucwords(number_to_words(number_format($net_total, 0, ".", ""))) . " Rupees ONLY Only";
                                                $net_tax_total = $cgst_tot + $sgst_tot + $igst_tot;

                                                $tax_in_word = 'RUPEES ' . ucwords(number_to_words(number_format($net_tax_total, 0, ".", ""))) . "Rupees ONLY Only";

                                                $number = $net_total;
                                                $precision = 2;
                                                $net_total = substr(number_format($number, $precision + 1, '.', ''), 0, -1);

                                                $round_amt = round($net_total);
                                                $round_val = $round_amt - $net_total;

                                                ?>

                                                <?php if ($dis_count > 0) { ?>
                                                    <tr>
                                                        <?php if ($common_state == $branch_state_code) { ?>
                                                            <td height="" colspan="13" <?php echo $col_rou; ?>" align="right"><b>Round Off (+/-)</b></td>
                                                            <td align="right" colspan=""><?php echo number_format($round_val, 2); ?></td>


                                                        <?php } else { ?>


                                                            <td colspan="13" <?php echo $col_rou; ?>" align="right"><b>Round Off (+/-)</b></td>
                                                            <td align="right" colspan=" "><?php echo number_format($round_val, 2); ?></td>


                                                        <?php } ?>
                                                    </tr>
                                                <?php } else { ?>

                                                    <tr>
                                                        <?php if ($common_state == $branch_state_code) { ?>
                                                            <td height="" colspan="<?php echo $col_rou; ?>" align="right"><b>Round Off (+/-)</b></td>
                                                            <td align="right" colspan=""><?php echo number_format($round_val, 2); ?></td>

                                                        <?php } else { ?>
                                                            <td height="" colspan="<?php echo $col_rou; ?>" align="right"><b>Round Off (+/-)</b></td>
                                                            <td align="right" colspan=" "><?php echo number_format($round_val, 2); ?></td>

                                                        <?php } ?>
                                                    </tr>



                                                <?php } ?>
                                                <tr>
                                                    <?php if ($dis_count > 0) { ?>
                                                        <td colspan="13" <?php echo $col_rou; ?>" align="right"><b>GRAND TOTAL VALUE (In Figure)</b></td>
                                                        <td align="right" colspan="2"><b><?php echo number_format(round($net_total), 2) ?></b></td>
                                                    <?php } else { ?>
                                                        <td colspan="<?php echo $col_rou; ?>" align="right"><b>GRAND TOTAL VALUE (In Figure)</b></td>
                                                        <td align="right"><b><?php echo number_format(round($net_total), 2) ?></b></td>
                                                    <?php } ?>

                                                </tr>

                                                <tr>
                                                    <td colspan="2" align="left"><b>Total Value (In Words) :</b></b></td>
                                                    <td colspan="12"> <?php echo $total_in_words; ?></td>
                                                </tr>


                                                <tr>
                                                    <td colspan="2" align="left"><b>Amount of Tax Subject to Reverse Charges :</b></td>
                                                    <td colspan="12" align="left"><?php echo $tax_in_word; ?></td>
                                                </tr>

                                                <tr>
                                                    <td colspan="14" align="left"><b>Transport Details:</b></td>
                                                </tr>

                                                <tr style="font-weight:bold; background-color: #bebebe;">
                                                    <td colspan="7">Mode of Payment</td>
                                                    <td colspan="4">Amount</td>
                                                    <td colspan="3">Date</td>
                                                </tr>
                                                <?php

                                                $pay_sql = "SELECT * FROM tbl_receipt WHERE pay_status = 0 AND so_id='" . $obj->so_id . "'";

                                                $result = $conn->query($pay_sql);

                                                //echo mysql_error();

                                                if ($result->rowCount() > 0) {
                                                    $sno = 1;
                                                    $netTotal = 0;

                                                    while ($pay = $result->fetch()) {
                                                        if ($pay->pay_type == 'C') {
                                                            $pay_mode = 'Cash';
                                                        } elseif ($pay->pay_type == 'Q') {
                                                            $pay_mode = 'Cheque - No: ' . $pay->pay_chq_no;
                                                        } elseif ($pay->pay_type == 'N') {
                                                            $pay_mode = 'Net Banking - Ref No: ' . $pay->pay_refno;
                                                        } elseif ($pay->pay_type == 'B') {
                                                            $pay_mode = 'Card - No: ' . $pay->pay_cardno;
                                                        } elseif ($pay->pay_type == 'A') {
                                                            $pay_mode = 'Account Transfer - Ref No: ' . $pay->pay_refno;
                                                        }

                                                        echo "<tr>

                                                            <td colspan='7'><b>" . $pay_mode . "</b></td>
                                                            <td colspan='4'>" . $pay->pay_amount . "</td>
                                                            <td colspan='3'>" . MyFormatDate($pay->pay_date) . "</td>

                                                            </tr>";
                                                    }
                                                }

                                                ?>

                                                <tr>
                                                    <td colspan="14" align="right"><b>FOR BENZEAR INDUSTRIAL ENTERPRISES<br /><br /><br /><br /></b>Authorised Signature</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="15" style="font-weight:bold; background-color: #bebebe; text-align:center;">www.benzear-bie.com</td>
                                                </tr>

                                                <?php
                                                // $pay_sql = "SELECT * FROM tbl_invoice WHERE inv_status = 1 AND so_id='" . $obj->so_id . "'";

                                                // $result = $conn->query($pay_sql);

                                                // //echo mysql_error();

                                                // if ($result->rowCount() > 0) {    
                                                ?>
                                                <!-- <tr style="font-weight:bold; background-color: #bebebe;">
                                                        <td colspan="2">Invoice No</td>
                                                        <td colspan="2">Invoice Date</td>
                                                        <td colspan="2">DC. No.</td>
                                                        <td colspan="4">DC. Date.</td>
                                                        <td colspan="2">Mode of Transport</td>
                                                        <td colspan="2">Vehicle No.</td>
                                                    </tr> -->

                                                <?php
                                                //     $sno = 1;
                                                //     $netTotal = 0;

                                                //     while ($inv = $result->fetch()) {

                                                //         $dc_slno = $dbconn->GetSingleReconrd("tbl_dc", "dc_slno", "dc_id", $inv->dc_id);
                                                //         $dc_date = $dbconn->GetSingleReconrd("tbl_dc", "dc_date", "dc_id", $inv->dc_id);

                                                //         echo "<tr>
                                                //             <td colspan='2'><b>" . leadingZeros($inv->inv_slno, 3) . "</b></td>
                                                //             <td colspan='2'>" . date("d-m-Y", strtotime($inv->inv_date)) . "</td>
                                                //             <td colspan='2'>" . leadingZeros($dc_slno, 3) . "</td>
                                                //             <td colspan='4'>" . date("d-m-Y", strtotime($dc_date)) . "</td>
                                                //             <td colspan='2'>" . $inv->inv_transport . "</td>
                                                //             <td colspan='2'>" . $inv->inv_vehicle_no . "</td>
                                                //         </tr>";
                                                //     }
                                                // }
                                                ?>


                                            </tbody>
                                           <!-- <tfoot>
                                                <tr>
                                                    <td colspan="14">
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
                                                                <td style="padding-top: 100px; border-top: none !important;border-bottom: none !important; border-left: none !important; border-right: none !important;"" class=" text-center">
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
                                <?php if (($obj->so_verify_status == 1) && $obj->so_status == 1) {

                                    $approve_user_dat = $dbconn->GetSingleReconrd("tbl_task_user", "user_id", "task_id", 3);

                                    if ($_SESSION['_user_id'] == 1) {
                                ?>
                                        
                                        <br>
                                        <div class="row">
                                            <?php if ($obj->pay_status != 3 && $obj->pay_status != 4 && $obj->so_credit_days <= 0) { ?>

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>Remarks </label>
                                                        <textarea name="so_remarks" id="so_remarks" class="form-control" rows="2" maxlength="250"><?php echo $obj->so_remarks; ?></textarea>
                                                    </div>
                                                </div>
                                        </div>
                                        <?php } ?>
                                        <?php
                                    }
                                } ?>
                            </div>
                            <?php if (($obj->so_verify_status == 1) && $obj->so_status == 1) { ?>
                            <div class="card-footer text-center">

                                <input type="hidden" name="so_id" id="so_id" value="<?php echo $_REQUEST['so_id']; ?>" />
                                <INPUT class="btn btn-custom" type="button" id="APPROVE" name="APPROVE" value="Approve">
                                <INPUT class="btn btn-danger" type="button" id="REJECT" name="REJECT" value="Reject">
                                

                            </div>
                            <?php } ?>
                             

                        </div>
                        <div class="row">
                            <div class="text-left col-lg-6">
                                <div class="rec_create_dets"><b>Created by : </b><?php echo $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id ", $obj->modify_by) . ' on ' . date('d-M-y @ H:i', strtotime($obj->modify_date_time)); ?></b><br><b>Remarks : </b> <?php echo $obj->so_remarks; ?>
                                </div>
                            </div>
                            <div class="text-right col-lg-6">
                                <?php
                                if ($obj->so_approve_status != 0) {
                                ?>
                                    <div class="rec_create_dets"><b>Approved by : </b><?php echo $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id ", $obj->so_id) . ' on ' . date('d-M-y @ H:i', strtotime($obj->so_approve_date_time)); ?></b>
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
</body>

</html>



<script>
    $('#APPROVE').click(function() {

        var so_id = $('#so_id').val();
        var remarks = $('#so_remarks').val();
        var task = "SO_APP";


        $.ajax({
            type: 'post',
            url: 'inc/cis_ajax/jquery_so_approval.php',
            data: {
                "so_id": so_id,
                "task": task,
                "remarks": remarks

            },

            beforeSend: function() {

                if (confirm('Are you sure to Approve this Sales Order..?')) {} else {
                    return false;
                }
            },
            complete: function() {

            },
            success: function(result) {

                window.location.href = "javascript:history.go(-1)";
            }
        });
        return false;
    });




    $('#REJECT').click(function() {

        if ($('#so_remarks').val() == '') {
            alert("Please enter the  Rejection Remarks..!");
            $('#so_remarks').focus();
            return false;
        }

        var so_id = $('#so_id').val();
        var remarks = $('#so_remarks').val();
        var task = "SO_REJ";
        var so_slno = $('#so_id').val();
        $.ajax({
            type: 'post',
            url: 'inc/cis_ajax/jquery_so_approval.php',
            data: {
                "so_id": so_id,
                "task": task,
                "remarks": remarks,
                "quo_slno": so_slno
            },
            beforeSend: function() {
                if (confirm('Are you sure to Reject this Sales Order..?')) {} else {
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