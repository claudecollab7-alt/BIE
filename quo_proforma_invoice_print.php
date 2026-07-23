<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);

$pro_date = date("Y-m-d");

if ($_REQUEST['pro_id'] != "") {
    $dbconn = new dbhandler();
    $result = $conn->query("SELECT * FROM tbl_proforma WHERE pro_id = " . $_REQUEST['pro_id']);
    if ($result->rowCount() > 0) {
        $obj = $result->fetch(PDO::FETCH_OBJ);

        $supp_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_status = 1 AND supp_id", $obj->supp_id);
        $supp_gst  = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_gst",  "supp_status = 1 AND supp_id", $obj->supp_id);
        $supp_pan  = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_pan",  "supp_status = 1 AND supp_id", $obj->supp_id);
        $so_refno  = $dbconn->GetSingleReconrd("tbl_sales_order", "so_refno", "so_status = 5 AND so_id", $obj->so_id);
        $so_date   = $dbconn->GetSingleReconrd("tbl_sales_order", "so_date",  "so_status = 5 AND so_id", $obj->so_id);

        if ($obj->pro_date != "0000-00-00" && $obj->pro_date != "") {
            $pro_date = date("d-m-Y", strtotime($obj->pro_date));
        }
        if ($so_date != "0000-00-00" && $so_date != "") {
            $so_dates = date("d-m-Y", strtotime($so_date));
        }

        $dis_count = $dbconn->GetCount("tbl_proforma_details", "pro_discount>0 AND pro_id", $obj->pro_id);
    }

    // Consignee address (billing)
    $get_add = $conn->query("SELECT * FROM mst_supplier_new WHERE supp_id = " . $obj->supp_id);
    if ($get_add->rowCount() > 0) {
        $obj1  = $get_add->fetch(PDO::FETCH_OBJ);
        $add   = $obj1->supp_add1;
        if ($obj1->supp_add2 != "") $add .= ', ' . $obj1->supp_add2;
        $add  .= ', <br/>' . $dbconn->GetSingleReconrd("mst_city",     "city_name",     "city_status = 1 AND city_id",         $obj1->city_id);
        $add  .= ', '      . $dbconn->GetSingleReconrd("mst_district", "district_name", "district_status = 1 AND district_id", $obj1->district_id);
        $add  .= ', <br/>' . $dbconn->GetSingleReconrd("mst_state",    "state_name",    "state_status = 1 AND state_id",       $obj1->state_id);
        $add  .= ' - ' . $obj1->supp_pincode . '.';
        $state_code = $dbconn->GetSingleReconrd("mst_state", "state_code", "state_status = 1 AND state_id", $obj1->state_id);
    }

    // Delivery address
    if ($obj->cus_branch_id > 0) {
        $get_add2 = $conn->query("SELECT * FROM mst_customer_branch WHERE branch_id = " . $obj->cus_branch_id);
        if ($get_add2->rowCount() > 0) {
            $obj2       = $get_add2->fetch(PDO::FETCH_OBJ);
            $supp_name2 = $supp_name . ' - ' . $obj2->branch_name;
            $add2       = $obj2->branch_add1;
            if ($obj2->branch_add2 != "") $add2 .= ', ' . $obj2->branch_add2;
            $add2 .= ', <br/>' . $dbconn->GetSingleReconrd("mst_city",     "city_name",     "city_status = 1 AND city_id",         $obj2->city_id);
            $add2 .= ', '      . $dbconn->GetSingleReconrd("mst_district", "district_name", "district_status = 1 AND district_id", $obj2->district_id);
            $add2 .= ', <br/>' . $dbconn->GetSingleReconrd("mst_state",    "state_name",    "state_status = 1 AND state_id",       $obj2->state_id);
            $add2 .= ' - ' . $obj2->branch_pincode . '.';
            $state_code = $dbconn->GetSingleReconrd("mst_state", "state_code", "state_status = 1 AND state_id", $obj2->state_id);
        }
    } else {
        $supp_name2 = $supp_name;
        $add2       = $obj1->delivery_add1;
        if ($obj1->delivery_add2 != "") $add2 .= ', ' . $obj1->delivery_add2;
        $add2 .= ', <br/>' . $dbconn->GetSingleReconrd("mst_city",     "city_name",     "city_status = 1 AND city_id",         $obj1->delivery_city_id);
        $add2 .= ', '      . $dbconn->GetSingleReconrd("mst_district", "district_name", "district_status = 1 AND district_id", $obj1->delivery_district_id);
        $add2 .= ', <br/>' . $dbconn->GetSingleReconrd("mst_state",    "state_name",    "state_status = 1 AND state_id",       $obj1->delivery_state_id);
        $add2 .= ' - ' . $obj1->delivery_pincode . '.';
        $state_code = $dbconn->GetSingleReconrd("mst_state", "state_code", "state_status = 1 AND state_id", $obj1->delivery_state_id);
    }

    // Company / branch details
    $company_add = $conn->query("SELECT * FROM mst_branch WHERE branch_id = " . $obj->branch_id);
    if ($company_add->rowCount() > 0) {
        $res     = $company_add->fetch(PDO::FETCH_OBJ);
        $address = $res->company_address;
        $address .= '<br><b>PH : </b> +91' . $res->company_ph_no1 . ' / ' . $res->company_ph_no2;
        $address .= '<br><b>E-Mail : </b>' . $res->company_mail;
        $address .= '<br><b>Web : </b>'    . $res->company_web;
        $gst_no           = $res->company_gst;
        $pan_no           = $res->company_pan;
        $branch_state_code = $res->branch_state_code;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Proforma</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <?php include_once("inc/common/css-js.php"); ?>
    <script type="text/javascript" src="print_me.js"></script>
    <script src="js/html2pdf.bundle.min.js"></script>

    <style>
        /* Screen */
        .po_print_table { font-size: 12px; }

        /* @page declared here so it applies when printing directly from page too */
        @page { size: A4 portrait; margin: 1cm; }

        @media print {
            html, body {
                overflow: visible !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            /* Wrapper: 1px right padding guarantees the table border
               is never flush with the Chrome printable-area edge */
            #print_content1 {
                padding-right: 2px !important;
                box-sizing: border-box !important;
            }
            .po_print_table {
                font-size: 9px !important;
                width: 100% !important;
                table-layout: fixed !important;
                border-collapse: collapse !important;
                box-sizing: border-box !important;
            }
            .po_print_table td,
            .po_print_table th {
                padding: 2px 3px !important;
                word-break: break-word !important;
                overflow: hidden !important;
                box-sizing: border-box !important;
            }
        }
    </style>

    <script>
        $(function () {
            /* PDF export via html2pdf */
            $("body").on("click", "#cmd", function () {
                var element = document.getElementById('print_content1');
                var opt = {
                    margin: 0.5,
                    filename: '<?php echo $obj->pro_refno; ?>.pdf',
                    image:    { type: 'jpeg', quality: 1 },
                    html2canvas: { scale: 2, logging: true },
                    jsPDF:    { unit: 'cm', format: 'A4', orientation: 'portrait' }
                };
                html2pdf().set(opt).from(element).save();
            });

            <?php
            if (!empty($_SESSION['_msg'])) {
                echo "$.jGrowl('" . $_SESSION['_msg'] . "', { sticky:false, theme:'alert-styled-left alert-arrow-left alert-success', position:'bottom-right', life:'2000', header:'Success!' });";
                $_SESSION['_msg'] = "";
            }
            if (!empty($_SESSION['_msg_err'])) {
                echo "$.jGrowl('" . $_SESSION['_msg_err'] . "', { sticky:false, theme:'alert-styled-left alert-arrow-left alert-danger', position:'top-right', shutdown:'3000', header:'Error!' });";
                $_SESSION['_msg_err'] = "";
            }
            ?>
        });
    </script>
</head>

<body>
    <!-- Main navbar -->
    <?php include("inc/common/header.php"); ?>

    <div class="page-content">
        <?php include("inc/common/sidebar.php"); ?>

        <div class="content-wrapper">
            <!-- Page header / breadcrumb -->
            <div class="page-header">
                <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                    <div class="d-flex">
                        <div class="breadcrumb">
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
                            <a href="#" class="breadcrumb-item">Work Area</a>
                            <span class="breadcrumb-item active">Proforma</span>
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
                                <h6 class="card-title">Proforma - <?php echo $obj->pro_refno; ?></h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" href="lst_quotation.php" title="Proforma List"><i class="icon-arrow-left52 mr-2"></i></a>
                                        <a class="list-icons-item" href="javascript:PrintPartsNew(['print_content1'],'<?php echo $obj->pro_refno; ?>');" title="Print Proforma"><i class="icon-printer2 mr-1"></i></a>
                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12 pdf_page" id="print_content1">

                                        <table class="table table-xs table-bordered po_print_table">
                                            <!-- 15 cols summing to 100% — with table-layout:fixed Chrome cannot overflow -->
                                            <colgroup>
                                                <col style="width:3%">   <!-- # -->
                                                <col style="width:18%">  <!-- Description -->
                                                <col style="width:9%">   <!-- Model -->
                                                <col style="width:6%">   <!-- HSN Code -->
                                                <col style="width:6%">   <!-- Rate -->
                                                <col style="width:4%">   <!-- Qty -->
                                                <col style="width:5%">   <!-- Unit -->
                                                <col style="width:6%">   <!-- Discount % -->
                                                <col style="width:8%">   <!-- Taxable Value -->
                                                <col style="width:5%">   <!-- CGST Rate -->
                                                <col style="width:6%">   <!-- CGST Amount -->
                                                <col style="width:5%">   <!-- SGST Rate -->
                                                <col style="width:6%">   <!-- SGST Amount -->
                                                <col style="width:5%">   <!-- IGST Rate -->
                                                <col style="width:8%">   <!-- IGST Amount -->
                                            </colgroup>
                                            <thead>
                                                <!-- Title -->
                                                <tr>
                                                    <td colspan="15" align="center" style="font-size:20px; font-weight:bold; background-color:#bebebe;">PROFORMA INVOICE</td>
                                                </tr>

                                                <!-- Logo + Address -->
                                                <tr>
                                                    <td colspan="6" class="text-center" style="vertical-align:middle;">
                                                        <img src="img/BIE_logo.png" alt="" width="100px" height="auto">
                                                    </td>
                                                    <td colspan="9" align="left"><?php echo $address; ?></td>
                                                </tr>

                                                <!-- GSTIN / Proforma No / Mode of Transport -->
                                                <tr>
                                                    <td colspan="2" style="background-color:#D3D3D3;"><b>GSTIN NO.</b></td>
                                                    <td colspan="4"><?php echo $gst_no; ?></td>
                                                    <td colspan="2" style="background-color:#D3D3D3;"><b>PROFORMA NO</b></td>
                                                    <td colspan="2"><?php echo $obj->pro_refno; ?></td>
                                                    <td colspan="2" style="background-color:#D3D3D3;"><b>MODE OF TRANSPORT</b></td>
                                                    <td colspan="3"><?php echo $obj->pro_mode_of_trans; ?></td>
                                                </tr>

                                                <!-- PAN / Proforma Date / Vehicle No -->
                                                <tr>
                                                    <td colspan="2" style="background-color:#D3D3D3;"><b>PAN NO.</b></td>
                                                    <td colspan="4"><?php echo $pan_no; ?></td>
                                                    <td colspan="2" style="background-color:#D3D3D3;"><b>PROFORMA DATE</b></td>
                                                    <td colspan="2"><?php echo $pro_date; ?></td>
                                                    <td colspan="2" style="background-color:#D3D3D3;"><b>VEHICLE NO.</b></td>
                                                    <td colspan="3"><?php echo $obj->pro_vechicle_no; ?></td>
                                                </tr>

                                                <!-- State Code / Time / Transport Charges -->
                                                <tr>
                                                    <td colspan="2" style="background-color:#D3D3D3;"><b>STATE CODE</b></td>
                                                    <td colspan="4"><?php echo $state_code; ?></td>
                                                    <td colspan="2" style="background-color:#D3D3D3;"><b>TIME OF PROFORMA</b></td>
                                                    <td colspan="2"><?php echo date('h:i:s', strtotime($obj->modify_date_time)); ?></td>
                                                    <td colspan="2" style="background-color:#D3D3D3;"><b>TRANSPORT CHARGES</b></td>
                                                    <td colspan="3"><?php echo ($obj->pro_trans_charge == 1) ? 'To Pay' : 'Paid'; ?></td>
                                                </tr>

                                                <!-- (blank) / SO No / SO Date -->
                                                <tr>
                                                    <td colspan="2" style="background-color:#D3D3D3;"></td>
                                                    <td colspan="4"></td>
                                                    <td colspan="2" style="background-color:#D3D3D3;"><b>SO.NO.</b></td>
                                                    <td colspan="2"><?php echo $so_refno; ?></td>
                                                    <td colspan="2" style="background-color:#D3D3D3;"><b>SO DATE</b></td>
                                                    <td colspan="3"><?php echo $so_dates; ?></td>
                                                </tr>

                                                <!-- Consignee / Delivery Address -->
                                                <tr>
                                                    <td colspan="6" align="left">
                                                        <p align="center"><b>CONSIGNEE ADDRESS</b></p>
                                                        <p><?php echo '<b>' . $supp_name . '</b><br/>' . $add; ?></p>
                                                    </td>
                                                    <td colspan="9" align="left">
                                                        <p align="center"><b>DELIVERY ADDRESS</b></p>
                                                        <p><?php echo '<b>' . $supp_name2 . '</b><br/>' . $add2; ?></p>
                                                    </td>
                                                </tr>

                                                <!-- Customer GSTIN -->
                                                <tr>
                                                    <td colspan="2" style="background-color:#D3D3D3;"><b>GSTIN NO.</b></td>
                                                    <td colspan="4"><?php echo $supp_gst; ?></td>
                                                    <td colspan="4" style="background-color:#D3D3D3;"><b>GSTIN NO.</b></td>
                                                    <td colspan="5"><?php echo $supp_gst; ?></td>
                                                </tr>

                                                <!-- Customer PAN -->
                                                <tr>
                                                    <td colspan="2" style="background-color:#D3D3D3;"><b>PAN NO.</b></td>
                                                    <td colspan="4"><?php echo $supp_pan; ?></td>
                                                    <td colspan="4" style="background-color:#D3D3D3;"><b>PAN NO.</b></td>
                                                    <td colspan="5"><?php echo $supp_pan; ?></td>
                                                </tr>

                                                <!-- Customer State Code -->
                                                <tr>
                                                    <td colspan="2" style="background-color:#D3D3D3;"><b>STATE CODE</b></td>
                                                    <td colspan="4"><?php echo $state_code; ?></td>
                                                    <td colspan="4" style="background-color:#D3D3D3;"><b>STATE CODE</b></td>
                                                    <td colspan="5"><?php echo $state_code; ?></td>
                                                </tr>

                                                <!-- Column headers: row 1 — # only (rowspan covers all 4 header rows) -->
                                                <tr style="font-weight:bold; background-color:#D3D3D3; text-align:center;">
                                                    <td rowspan="4">#</td>
                                                    <td rowspan="4">Description of Goods</td>
                                                    <td rowspan="4">Model</td>
                                                    <td rowspan="4">HSN Code</td>
                                                    <td rowspan="4">Rate</td>
                                                    <td rowspan="4">Qty</td>
                                                    <td rowspan="4">Unit</td>
                                                    <td rowspan="4">Discount %</td>
                                                    <td rowspan="4">Taxable Value</td>
                                                    <td colspan="2">CGST</td>
                                                    <td colspan="2">SGST</td>
                                                    <td colspan="2">IGST</td>
                                                </tr>
                                                <!-- Column headers: row 2 — Rate/Amount sub-headers -->
                                                <tr style="font-weight:bold; background-color:#D3D3D3; text-align:center;">
                                                    <td>Rate</td>
                                                    <td>Amount</td>
                                                    <td>Rate</td>
                                                    <td>Amount</td>
                                                    <td>Rate</td>
                                                    <td>Amount</td>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php
                                                $posql  = "SELECT * FROM tbl_proforma AS a
                                                            LEFT JOIN tbl_proforma_details AS b ON a.pro_id = b.pro_id
                                                            WHERE a.pro_id = '" . $_REQUEST['pro_id'] . "'";
                                                $result = $conn->query($posql);

                                                $iSno            = 1;
                                                $item_val_no_tax = 0;
                                                $tax_val1        = 0; // cgst total
                                                $tax_val2        = 0; // sgst total
                                                $tax_va3         = 0; // igst total

                                                if ($result->rowCount() > 0) {
                                                    while ($inv = $result->fetch()) {
                                                        $igst_vat = $cgst_vat = $sgst_vat = 0;
                                                        $igst_val = $cgst_val = $sgst_val = 0;

                                                        $item_code  = $dbconn->GetSingleReconrd("tbl_item_details", "item_code",            "item_status='1' AND item_id", $inv->item_id);
                                                        $item_name  = $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption",       "item_status='1' AND item_id", $inv->item_id);
                                                        $gst_id     = $dbconn->GetSingleReconrd("tbl_item_details", "item_hsn",              "item_status='1' AND item_id", $inv->item_id);
                                                        $hsn        = $dbconn->GetSingleReconrd("mst_hsn", "hsn_code", "hsn_status='1' AND hsn_id", $gst_id);
                                                        $cgst       = $dbconn->GetSingleReconrd("mst_hsn", "cgst",     "hsn_status='1' AND hsn_id", $gst_id);
                                                        $sgst       = $dbconn->GetSingleReconrd("mst_hsn", "sgst",     "hsn_status='1' AND hsn_id", $gst_id);
                                                        $igst       = $dbconn->GetSingleReconrd("mst_hsn", "igst",     "hsn_status='1' AND hsn_id", $gst_id);

                                                        $taxable_val = $inv->unit_price * $inv->pro_qty;
                                                        if ($dis_count > 0) {
                                                            $dis_amt     = ($taxable_val * $inv->pro_discount) / 100;
                                                            $taxable_val = (float)$taxable_val - (float)$dis_amt;
                                                        }

                                                        if ($state_code == $branch_state_code) {
                                                            $cgst_vat = $cgst;
                                                            $cgst_val = ((float)$taxable_val * (float)$cgst_vat) / 100;
                                                            $sgst_vat = $sgst;
                                                            $sgst_val = ((float)$taxable_val * (float)$sgst_vat) / 100;
                                                        } else {
                                                            $igst_vat = $igst;
                                                            $igst_val = ((float)$taxable_val * (float)$igst_vat) / 100;
                                                        }

                                                        echo '<tr valign="top">
                                                            <td class="text-center">'  . $iSno . '</td>
                                                            <td class="text-left">'    . $item_name . '</td>
                                                            <td class="text-center"><b>' . $item_code . '</b></td>
                                                            <td class="text-center">'  . $hsn . '</td>
                                                            <td class="text-right">'   . $inv->unit_price . '</td>
                                                            <td class="text-center">'  . $inv->pro_qty . '</td>
                                                            <td class="text-center">'  . $inv->pro_unit . '</td>
                                                            <td class="text-right">'   . number_format($inv->pro_discount, 2) . '</td>
                                                            <td class="text-right">'   . number_format($taxable_val, 2) . '</td>
                                                            <td class="text-right">'   . number_format($cgst_vat, 2) . '</td>
                                                            <td class="text-right">'   . number_format($cgst_val, 2) . '</td>
                                                            <td class="text-right">'   . number_format($sgst_vat, 2) . '</td>
                                                            <td class="text-right">'   . number_format($sgst_val, 2) . '</td>
                                                            <td class="text-right">'   . number_format($igst_vat, 2) . '</td>
                                                            <td class="text-right">'   . number_format($igst_val, 2) . '</td>
                                                        </tr>';

                                                        $item_val_no_tax += $taxable_val;
                                                        $tax_val1        += $cgst_val;
                                                        $tax_val2        += $sgst_val;
                                                        $tax_va3         += $igst_val;
                                                        $iSno++;
                                                    }
                                                }

                                                // SUB TOTAL row
                                                echo '<tr>
                                                    <td colspan="8" align="right" style="background-color:#D3D3D3;"><b>SUB TOTAL</b></td>
                                                    <td align="right"><b>' . number_format($item_val_no_tax, 2) . '</b></td>
                                                    <td></td>
                                                    <td align="right"><b>' . number_format($tax_val1, 2) . '</b></td>
                                                    <td></td>
                                                    <td align="right"><b>' . number_format($tax_val2, 2) . '</b></td>
                                                    <td></td>
                                                    <td align="right"><b>' . number_format($tax_va3, 2) . '</b></td>
                                                </tr>';

                                                // Pack / additional charges
                                                $pack_sql = "SELECT * FROM tbl_proforma_pack_details WHERE pro_id='" . $_REQUEST['pro_id'] . "'";
                                                $pack_res = $conn->query($pack_sql);

                                                $pack_total    = 0;
                                                $pack_cgst_tot = 0;
                                                $pack_sgst_tot = 0;
                                                $pack_igst_tot = 0;

                                                if ($pack_res->rowCount() > 0) {
                                                    while ($pack = $pack_res->fetch()) {
                                                        $desc = $dbconn->GetSingleReconrd("mst_quo_details", "quo_pack_decp", "quo_id", $pack->pro_pack_decp);

                                                        if ($pack->pro_pack_percent == 1) {
                                                            $description = '<b>' . $desc . ' (' . roundofnum($pack->pro_pack_text) . ' %)</b>';
                                                        } else {
                                                            $description = '<b>' . $desc . '</b>';
                                                        }
                                                        $pro_pack_val = $pack->pro_pack_taxable_val;

                                                        $p_cgst_vat = $p_sgst_vat = $p_igst_vat = 0;
                                                        $p_cgst_val = $p_sgst_val = $p_igst_val = 0;

                                                        $hsn_sql = $conn->query("SELECT * FROM mst_hsn WHERE hsn_code = '" . $pack->gst_id . "'");
                                                        $hsn_obj = $hsn_sql->fetch(PDO::FETCH_OBJ);

                                                        if ($state_code == $branch_state_code) {
                                                            $p_cgst_vat = $hsn_obj->cgst;
                                                            $p_cgst_val = ((float)$pro_pack_val * (float)$p_cgst_vat) / 100;
                                                            $p_sgst_vat = $hsn_obj->sgst;
                                                            $p_sgst_val = ((float)$pro_pack_val * (float)$p_sgst_vat) / 100;
                                                        } else {
                                                            $p_igst_vat = $hsn_obj->igst;
                                                            $p_igst_val = ((float)$pro_pack_val * (float)$p_igst_vat) / 100;
                                                        }

                                                        echo '<tr>
                                                            <td colspan="3" align="right">' . strtoupper($description) . '</td>
                                                            <td></td>
                                                            <td align="center">' . $pack->gst_id . '</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td align="right">' . ($pro_pack_val > 0 ? number_format($pro_pack_val, 2) : '') . '</td>
                                                            <td class="text-right">' . number_format($p_cgst_vat, 2) . '</td>
                                                            <td class="text-right">' . number_format($p_cgst_val, 2) . '</td>
                                                            <td class="text-right">' . number_format($p_sgst_vat, 2) . '</td>
                                                            <td class="text-right">' . number_format($p_sgst_val, 2) . '</td>
                                                            <td class="text-right">' . number_format($p_igst_vat, 2) . '</td>
                                                            <td class="text-right">' . number_format($p_igst_val, 2) . '</td>
                                                        </tr>';

                                                        $pack_total    += $pro_pack_val;
                                                        $pack_cgst_tot += $p_cgst_val;
                                                        $pack_sgst_tot += $p_sgst_val;
                                                        $pack_igst_tot += $p_igst_val;
                                                    }
                                                }

                                                // TOTAL row
                                                $over_all_tot  = $item_val_no_tax + $pack_total;
                                                $over_all_tot1 = $tax_val1 + $pack_cgst_tot;
                                                $over_all_tot2 = $tax_val2 + $pack_sgst_tot;
                                                $over_all_tot3 = $tax_va3  + $pack_igst_tot;

                                                echo '<tr>
                                                    <td colspan="8" align="right" style="background-color:#D3D3D3;"><b>TOTAL</b></td>
                                                    <td align="right"><b>' . number_format($over_all_tot, 2) . '</b></td>
                                                    <td></td>
                                                    <td align="right"><b>' . number_format($over_all_tot1, 2) . '</b></td>
                                                    <td></td>
                                                    <td align="right"><b>' . number_format($over_all_tot2, 2) . '</b></td>
                                                    <td></td>
                                                    <td align="right"><b>' . number_format($over_all_tot3, 2) . '</b></td>
                                                </tr>';

                                                // Grand total calculations
                                                $final_pack_tot = $pack_total + $pack_cgst_tot + $pack_sgst_tot + $pack_igst_tot;
                                                $net_total      = $item_val_no_tax + $tax_val1 + $tax_val2 + $tax_va3 + $final_pack_tot;
                                                $round_amt      = round($net_total);
                                                $round_val      = $net_total - $round_amt;
                                                $total_in_words = 'RUPEES ' . ucwords(number_to_words(number_format($net_total, 0, '.', '')));
                                                $tax_amt        = $tax_val1 + $tax_val2 + $tax_va3;
                                                $total_tax_amt  = '<b>Total Tax Value : Rs.' . $tax_amt . '</b>&nbsp;&nbsp;RUPEES ' . ucwords(number_to_words($tax_amt));

                                                echo '
                                                <tr>
                                                    <td colspan="14" align="right" style="background-color:#D3D3D3;"><b>ROUND OFF (+/-)</b></td>
                                                    <td align="right">' . number_format($round_val, 2) . '</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="14" align="right" style="background-color:#D3D3D3;"><b>GRAND TOTAL VALUE (In Figure)</b></td>
                                                    <td align="right"><b>' . number_format($round_amt, 2) . '</b></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" style="background-color:#D3D3D3;"><b>Total Value (In Words)</b></td>
                                                    <td colspan="13">' . $total_in_words . '</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" style="background-color:#D3D3D3;"><b>Amount of Tax Subjected to Reverse Charges</b></td>
                                                    <td colspan="13">' . $total_tax_amt . '</td>
                                                </tr>

                                                <!-- Footer header row: labels -->
                                                <tr style="background-color:#D3D3D3; font-weight:bold;">
                                                    <td colspan="6">Payment Terms &amp; Bank Details</td>
                                                    <td colspan="3" align="center">Prepared By</td>
                                                    <td colspan="2" align="center">Checked By</td>
                                                    <td colspan="4" align="center">FOR BENZEAR INDUSTRIAL ENTERPRISES</td>
                                                </tr>

                                                <!-- Footer body: interest note | empty signature cells -->
                                                <tr>
                                                    <td colspan="6" style="background-color:#D3D3D3;">
                                                        <b>Interest @24% p.a. will be charged on overdue payments.<br>
                                                        All disputes are subject to Coimbatore Jurisdiction.</b>
                                                    </td>
                                                    <td colspan="3" rowspan="2" style="vertical-align:bottom; height:80px;"></td>
                                                    <td colspan="2" rowspan="2" style="vertical-align:bottom; height:80px;"></td>
                                                    <td colspan="4" rowspan="2" style="vertical-align:bottom; text-align:center; height:80px; padding-bottom:6px;">
                                                        <b>Authorised Signature</b>
                                                    </td>
                                                </tr>

                                                <!-- Footer body: bank details -->
                                                <tr>
                                                    <td colspan="6" style="background-color:#D3D3D3;">
                                                        <b>Bank Details :</b><br>
                                                        <b>Acc. Name : BENZEAR INDUSTRIAL ENTERPRISES</b><br>
                                                        <b>Bank Name : Indian Bank</b><br>
                                                        <b>Account No. : 6176803403</b><br>
                                                        <b>IFS Code : IDIB000C062</b><br>
                                                        <b>Branch : 1/202, Avinashi Road, Chinniampalayam, Coimbatore.</b>
                                                    </td>
                                                </tr>

                                                <!-- Toll Free -->
                                                <tr>
                                                    <td colspan="15" style="background-color:#D3D3D3;">
                                                        <b>Toll Free No: 1800 599 2323</b>
                                                    </td>
                                                </tr>';
                                                ?>
                                            </tbody>
                                        </table>

                                    </div><!-- /#print_content1 -->
                                </div>
                            </div>
                        </div><!-- /.card -->

                        <div class="row mt-2">
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
            </div><!-- /.content -->

            <?php include("inc/common/footer.php"); ?>
        </div><!-- /.content-wrapper -->
    </div><!-- /.page-content -->
</body>
</html>