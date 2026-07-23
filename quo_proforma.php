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

//-------------------------------------------- SAVE DATABASE -------------------------------//

if (isset($_POST['SAVE'])) {

    $_REQUEST['select_branch_id'] = (isset($_REQUEST['select_branch_id'])) ? ($_REQUEST['select_branch_id']) : '';
    // try {

    $_REQUEST['pro_date'] = date("Y-m-d", strtotime($_REQUEST['pro_date']));
    $_REQUEST['branch'] = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id='" . $_SESSION['_user_branch'] . "' AND branch_status", 1);
    $_REQUEST['pro_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
    $_REQUEST['pro_slno'] = $dbconn->GetMaxValue('tbl_proforma', 'pro_slno', 'branch_id="' . $_SESSION['_user_branch'] . '" AND pro_finyr="' . $_REQUEST['pro_finyr'] . '" AND 1', 1) + 1;

    $_REQUEST['pro_refno'] = 'PI/' . leadingZeros($_REQUEST['pro_slno'], 4) . '/BIE/' . $_REQUEST['branch'] . '/'  . $_REQUEST['pro_finyr'];


    $_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
    $_REQUEST['modify_by'] = $_SESSION['_user_id'];
    if ($_REQUEST['branch_id'] > 0) {
        $_REQUEST['cus_branch_id'] = $_REQUEST['branch_id'];
    } else {
        $_REQUEST['cus_branch_id'] = '0';
    }

    $stmt = null;
    $stmt = $conn->prepare("INSERT INTO tbl_proforma (pro_finyr, pro_slno, pro_refno, pro_date, supp_id, cus_branch_id, quo_id, pro_mode_of_trans, pro_vechicle_no, pro_trans_charge,pro_tot_value,pro_bal_value,pro_remarks,  invoice_type, modify_by, modify_date_time, branch_id) VALUES (:pro_finyr, :pro_slno, :pro_refno, :pro_date, :supp_id, :cus_branch_id, :quo_id, :pro_mode_of_trans, :pro_vechicle_no, :pro_trans_charge, :pro_tot_value, :pro_bal_value, :pro_remarks, :invoice_type, :modify_by, :modify_date_time, :branch_id)");
    $data = array(
        ':pro_finyr' => $_REQUEST['pro_finyr'],
        ':pro_slno' => $_REQUEST['pro_slno'],
        ':pro_refno' => $_REQUEST['pro_refno'],
        ':pro_date' => $_REQUEST['pro_date'],
        ':supp_id' => $_REQUEST['supp_id'],
        ':cus_branch_id' => $_REQUEST['cus_branch_id'],
        ':quo_id' => $_REQUEST['quo_id'],
        ':pro_mode_of_trans' => $_REQUEST['pro_mode_of_trans'],
        ':pro_vechicle_no' => $_REQUEST['pro_vechicle_no'],
        ':pro_trans_charge' => $_REQUEST['pro_trans_charge'],
        ':pro_tot_value' => $_REQUEST['pro_tot_value'],
        ':pro_bal_value' => $_REQUEST['pro_tot_value'],
        ':pro_remarks' => $_REQUEST['pro_remarks'],
        ':invoice_type' => 'Q',
        ':modify_by' => $_REQUEST['modify_by'],
        ':modify_date_time' => $_REQUEST['modify_date_time'],
        ':branch_id' => $_SESSION['_user_branch']
    );
    // print_r($data);
    $stmt->execute($data);
    $last_id = $conn->lastInsertId();

    // Individual item ...

    $delete_details =  "DELETE FROM  tbl_proforma_details WHERE pro_id = '" . $last_id . "'";
    $result = $conn->prepare($delete_details);
    $result->execute();

    $stmt1 = null;
    $stmt1 = $conn->prepare("INSERT INTO tbl_proforma_details (pro_id, item_id, pro_qty, pro_unit, unit_price, pro_discount, pro_discount_amt, vat, pro_value, tax_value, net_value) 
	VALUES (:pro_id, :item_id, :pro_qty, :pro_unit, :unit_price, :pro_discount, :pro_discount_amt, :vat, :pro_value, :tax_value, :net_value)");

    if (isset($_REQUEST['temp_item_id'])) {
        $row_count = count($_REQUEST['temp_item_id']);

        for ($n = 0; $n < $row_count; $n++) {
            $data1 = array(
                ':pro_id' => $last_id,
                ':item_id' => $_REQUEST['temp_item_id'][$n],
                ':pro_qty' => $_REQUEST['temp_qty'][$n],
                ':pro_unit' => $_REQUEST['temp_unit'][$n],
                ':unit_price' => $_REQUEST['temp_selling_price'][$n],
                ':pro_discount' => $_REQUEST['temp_discount_per'][$n],
                ':pro_discount_amt' => $_REQUEST['temp_discount_val'][$n],
                ':vat' => $_REQUEST['temp_vat'][$n],
                ':pro_value' => $_REQUEST['temp_quo_price'][$n],
                ':tax_value' => $_REQUEST['quo_pack_taxable_value'][$n],
                ':net_value' => $_REQUEST['temp_net_amt'][$n]
            );
            $stmt1->execute($data1);
        }
    }

    $stmt = null;
    $stmt = $conn->prepare("INSERT INTO tbl_proforma_pack_details (pro_id, pro_pack_decp, pro_pack_percent, pro_pack_text, pro_pack_taxable_val, gst_id, pro_pack_vat, pro_pack_value, pro_pack_total)
		                    VALUES (:pro_id, :pro_pack_decp, :pro_pack_percent, :pro_pack_text, :pro_pack_taxable_val, :gst_id, :pro_pack_vat, :pro_pack_value, :pro_pack_total)");

    if (isset($_REQUEST['pack_id'])) {
        $row_count = (count($_REQUEST['pack_id']));
        if ($row_count > 0) {
            for ($n = 0; $n < $row_count; $n++) {
                $quo_pack_total = isset($_REQUEST['quo_pack_total'][$n]) ? $_REQUEST['quo_pack_total'][$n] : '';
                $data = array(
                    ':pro_id' => $last_id,
                    ':pro_pack_decp' => $_REQUEST['pack_id'][$n],
                    ':pro_pack_percent' => $_REQUEST['quo_pack_per_fa'][$n],
                    ':pro_pack_text' => $_REQUEST['quo_pack_per_fa_value'][$n],
                    ':pro_pack_taxable_val' => $_REQUEST['quo_pack_taxable_val'][$n],
                    ':gst_id' => $_REQUEST['quo_pack_gst_id'][$n],
                    ':pro_pack_vat' => $_REQUEST['quo_pack_gst_per'][$n],
                    ':pro_pack_value' => $_REQUEST['quo_pack_gst_amt'][$n],
                    ':pro_pack_total' => $_REQUEST['quo_pack_total'][$n]
                );
                $stmt->execute($data);
            }
        }
    }

    $_SESSION['_msg'] = "Proforma succesfully Saved..!";
    header("location:quo_proforma_invoice_print.php?pro_id=".$last_id);
    die();
}

if (isset($_REQUEST['quo_id'])) {
    $get_val = $conn->query("SELECT * FROM tbl_quotation WHERE  quo_id =' " . $_REQUEST['quo_id'] . "'");

    if ($get_val->rowCount() > 0) {
        $obj = $get_val->fetch(PDO::FETCH_OBJ);
        $supp_id = $obj->supp_id;
        $so_date = $obj->quo_date;
        $branch_id = $obj->branch_id;
        $branch_name = $obj->branch_id;
        $sales_order_total = $obj->quo_value;
        //$so_no = leadingZeros($dbconn->GetMaxValue('tbl_sales_order', 'so_slno', 'so_finyr', $_REQUEST['so_finyr']) + 1, 3);
    }
}

$pro_date = date('Y-m-d');
if (isset($_REQUEST['pro_id'])) {
    $get_pro_val = $conn->query("SELECT * FROM tbl_proforma WHERE  pro_id =' " . $_REQUEST['pro_id'] . "'");

    if ($get_pro_val->rowCount() > 0) {
        $obj = $get_pro_val->fetch(PDO::FETCH_OBJ);
        $supp_id = $obj->supp_id;
        $so_date = $obj->pro_date;
        $branch_id = $obj->cus_branch_id;
        $branch_name = $obj->cus_branch_id;
        $sales_order_total = $obj->pro_tot_value;
        $pro_remarks = $obj->pro_remarks;
        $pro_date = $obj->pro_date;
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <title><?php echo PAGE_TITLE; ?> - Proforma</title>
    <link href="css/main.css" rel="stylesheet" type="text/css" />

    <?php include_once("inc/common/css-js.php"); ?>

    <!-- AUTO COMPLETE -->
    <script type='text/javascript' src='js/auto/jquery.autocomplete.js'></script>
    <link rel="stylesheet" type="text/css" href="js/auto/jquery.autocomplete.css" />
</head>

<body>
    <?php include("modal_supp_dets.php") ?>
    <?php include("inc/common/header.php") ?>
    <!-- /main navbar -->
    <!-- Page content -->
    <div class="page-content">
        <!-- Main sidebar -->
        <?php include("inc/common/sidebar.php") ?>
        <!-- Main content -->
        <div class="content-wrapper">
            <!-- Page header -->
            <div class="page-header">
                <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                    <div class="d-flex">
                        <div class="breadcrumb">
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Home</a>
                            <a href="#" class="breadcrumb-item">Work Area</a>
                            <span class="breadcrumb-item active">Proforma</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>
            <!-- This Form UI Starts here --->
            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">
                        <form name='thisForm' id="validate" class="form-horizontal" method='post' action="quo_proforma.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                            <input type="hidden" name="quo_id" id="quo_id" value="<?php echo $_REQUEST['quo_id']; ?>">
                            <fieldset>
                                <div class="card">
                                    <div class="card-header bg-pgheader text-white header-elements-inline">
                                        <h6 class="card-title">Quotation Proforma</h6>
                                        <div class="header-elements">
                                            <div class="list-icons">
                                                <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                                <a class="list-icons-item" href="lst_quotation.php" title="Quotation List"><i class="icon-arrow-left52 mr-2"></i></a>
                                                <a class="list-icons-item" data-action="fullscreen"></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <div class="row">
                                                <input type="hidden" name="pro_no" id="pro_no" class="form-control" value="" />
                                                <label class="col-lg-1 col-form-label">Customer <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3">
                                                    <select name="supp_id" id="supp_id" data-placeholder="Choose a Customer.." class="form-control select-search">
                                                        <option value=""></option>
                                                        <?php
                                                        $dbconn = new dbhandler();
                                                        echo $dbconn->fnFillComboFromTable_Where("supp_id", "supp_name", "mst_supplier_new", "supp_id", " WHERE supp_status = '1' AND supp_type = 'C'") ?>
                                                    </select>
                                                    <script>
                                                        document.thisForm.supp_id.value = "<?php echo $supp_id; ?>";
                                                    </script>
                                                </div>
                                                <label class="col-lg-1 col-form-label">Customer Branch / Delivery Address </label>
                                                <div class="col-lg-3">
                                                    <select name="branch_id" id="branch_id" data-placeholder="Choose a Customer Branch.." class="form-control select-search">
                                                        <option value=""></option>
                                                        <?php
                                                        $dbconn = new dbhandler();
                                                        if (isset($_REQUEST['pro_id']) && $_REQUEST['pro_id'] != "") {
                                                            echo $dbconn->fnFillComboFromTable_Where("branch_id", "CONCAT(branch_name,' ~ ',branch_add1,' ~ ',branch_add2)", "mst_customer_branch", "branch_id", " WHERE branch_status = '1' AND supp_id = '" . $supp_id . "'");
                                                        }
                                                        ?>
                                                    </select>
                                                    <script>
                                                        document.thisForm.branch_id.value = "<?php echo $branch_id; ?>";
                                                    </script>
                                                </div>


                                                <label class="col-lg-1 col-form-label">Date <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3">
                                                    <input type="date" name="pro_date" id="pro_date" class="form-control" readonly maxlength="75" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $pro_date; ?>" placeholder="Date" />
                                                </div>
                                            </div>

                                            <div class="row pt-2">
                                                <label class="col-lg-1 col-form-label">Mode of Transport <span class="text-mandatory"></span></label>
                                                <div class="col-lg-3">
                                                    <input type="text" name="pro_mode_of_trans" id="pro_mode_of_trans" class="form-control" maxlength="100" placeholder="Mode of Transport" value="<?php echo $obj->pro_mode_of_trans; ?>" />
                                                </div>
                                                <label class="col-lg-1 col-form-label">Vehicle No. <span class="text-mandatory"></span></label>
                                                <div class="col-lg-3">
                                                    <input type="text" name="pro_vechicle_no" id="pro_vechicle_no" class="form-control" maxlength="75" placeholder="Vehicle No." value="<?php echo $obj->pro_vechicle_no; ?>" />
                                                </div>
                                                <label class="col-lg-1 col-form-label">Transport Charges Type <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3">
                                                    <select name="pro_trans_charge" id="pro_trans_charge" data-placeholder="Choose a Transport Charges Type.." class="form-control select-search">
                                                        <option value=""></option>
                                                        <option value="1">To Pay</option>
                                                        <option value="2">Paid</option>
                                                    </select>
                                                    <script>
                                                        document.thisForm.pro_trans_charge.value = "<?php echo $obj->pro_trans_charge; ?>";
                                                    </script>
                                                </div>
                                            </div>

                                        </div>


                                        <legend class="font-weight-semibold"><i class="icon-cart mr-2"></i>Proforma Details</legend>

                                        <!-----individual description table --------->

                                        <div class="form-group row">
                                            <div id="quo_table" class="col-md-12">
                                                <table class="table table-xs table-bordered" style="font-size: small !important;">
                                                    <thead>
                                                        <tr class="bg-teal">
                                                            <th>Description</th>
                                                            <th>Item Code</th>
                                                            <th>In Stock</th>
                                                            <th>Qty</th>
                                                            <th>Unit</th>
                                                            <th>Unit Price</th>
                                                            <th>Disc %</th>
                                                            <th>Amount</th>
                                                            <th>GST %</th>
                                                            <th>Net Amount</th>

                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tbody>
                                                        <?php
                                                        if (isset($_REQUEST['quo_id'])) {
                                                            $get_quo_dets =  $conn->query("SELECT * FROM tbl_quotation_details WHERE  quo_id = '" . $_REQUEST['quo_id'] . "'");
                                                            if ($get_quo_dets->rowCount() > 0) {
                                                                while ($obj = $get_quo_dets->fetch(PDO::FETCH_OBJ)) {

                                                                    $field_name = $dbconn->GetSingleReconrd("mst_branch", "branch_stock_field", "branch_id", $_SESSION['_user_branch']);
                                                                    $curr_stock = $dbconn->GetSingleReconrd("tbl_item_stock", "$field_name", "item_id", $obj->item_id);

                                                                    $item_type = $dbconn->GetSingleReconrd("tbl_item_details", "item_type", "item_status = '1' AND item_id", $obj->item_id);

                                                                    if ($curr_stock <= 0 && $item_type != 8) {
                                                                        echo '<tr id="' . $obj->item_id . '" style="color:red;" class="g' . $obj->group_id . '">';
                                                                    } else {
                                                                        echo '<tr id="' . $obj->item_id . '" >';
                                                                    }


                                                                    echo '<td>' . $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_id", $obj->item_id) . '</td>
																	<input type="hidden" class="item_desciption" name="item_desciption[]" value="' . $obj->item_id . '" />

																	<td>' . $dbconn->GetSingleReconrd("tbl_item_details", "item_code", "item_id", $obj->item_id) . '</td>
																	<input type="hidden" class="temp_item_id" name="temp_item_id[]" value="' . $obj->item_id . '" />
																	<td class="text-right">' . $curr_stock . '</td>
																	<input type="hidden" class="temp_item_qty" name="temp_item_qty[]" value="' . $curr_stock . '" />

																	<td class="text-right">' . $obj->quo_qty . '</td>
																	<input type="hidden" class="temp_qty" name="temp_qty[]" value="' . $obj->quo_qty . '" />

																	<td class="text-right">' . $obj->quo_unit . '</td>
																	<input type="hidden" class="temp_unit" name="temp_unit[]" value="' . $obj->quo_unit . '" />

																	<td class="text-right">' . $obj->selling_price . '</td>
																	<input type="hidden" class="temp_selling_price" name="temp_selling_price[]" value="' . $obj->selling_price . '" />

																	<td class="text-right">' . $obj->quo_discount . '</td>
																	<input type="hidden" class="temp_discount_per" name="temp_discount_per[]" value="' . $obj->quo_discount . '" />
																
																	<td class="text-right">' . $obj->quo_value . '</td>
																	<input type="hidden" class="temp_quo_price" name="temp_quo_price[]" value="' . $obj->quo_value . '" />
																	<input type="hidden" class="temp_discount_val" name="temp_discount_val[]" value="' .  $obj->quo_discount_amt . '">

																	<td class="text-right">' . $obj->vat . '</td>
																	<input type="hidden" class="temp_vat" name="temp_vat[]" value="' . $obj->vat . '" />
																	<input type="hidden" class="quo_pack_taxable_value" name="quo_pack_taxable_value[]" value="' . $obj->tax_value . '"/>

																	<td class="text-right">' . $obj->net_value . '</td>
																	<input type="hidden" class="temp_net_amt" name="temp_net_amt[]"  id="net_total" value="' . $obj->net_value . '" />
																</tr>';
                                                                }
                                                            }
                                                            $tot_qty = $dbconn->GetSingleReconrd("tbl_quotation_details", "SUM(quo_qty)", "quo_id", $_REQUEST['quo_id']);
                                                            $tot_amt = $dbconn->GetSingleReconrd("tbl_quotation_details", "SUM(quo_value)", "quo_id", $_REQUEST['quo_id']);
                                                            $tot_netamt = $dbconn->GetSingleReconrd("tbl_quotation_details", "SUM(net_value)", "quo_id", $_REQUEST['quo_id']);
                                                        }

                                                        //Proforma Details
                                                        if (isset($_REQUEST['pro_id'])) {
                                                            $get_pro_dets =  $conn->query("SELECT * FROM tbl_proforma_details WHERE  pro_id = '" . $_REQUEST['pro_id'] . "'");
                                                            if ($get_pro_dets->rowCount() > 0) {
                                                                while ($obj = $get_pro_dets->fetch(PDO::FETCH_OBJ)) {

                                                                    $field_name = $dbconn->GetSingleReconrd("mst_branch", "branch_stock_field", "branch_id", $_SESSION['_user_branch']);
                                                                    $curr_stock = $dbconn->GetSingleReconrd("tbl_item_stock", "$field_name", "item_id", $obj->item_id);
                                                                    echo '<tr id="' . $obj->item_id . '" class="g' . $obj->group_id . '">
                                                                    <td>' . $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_id", $obj->item_id) . '</td>
                                                                    <input type="hidden" class="item_desciption" name="item_desciption[]" value="' . $obj->item_id . '" />

                                                                    <td>' . $dbconn->GetSingleReconrd("tbl_item_details", "item_code", "item_id", $obj->item_id) . '</td>
                                                                    <input type="hidden" class="temp_item_id" name="temp_item_id[]" value="' . $obj->item_id . '" />
                                                                    <td class="text-left">' . $curr_stock . '</td>
																	<input type="hidden" class="temp_item_qty" name="temp_item_qty[]" value="' . $curr_stock . '" />
                                                                    <td class="text-right">' . $obj->pro_qty . '</td>
                                                                    <input type="hidden" class="temp_qty" name="temp_qty[]" value="' . $obj->pro_qty . '" />

                                                                    <td class="text-right">' . $obj->pro_unit . '</td>
                                                                    <input type="hidden" class="temp_unit" name="temp_unit[]" value="' . $obj->pro_unit . '" />

                                                                    <td class="text-right">' . $obj->unit_price . '</td>
                                                                    <input type="hidden" class="temp_selling_price" name="temp_selling_price[]" value="' . $obj->unit_price . '" />

                                                                    <td class="text-right">' . $obj->pro_discount . '</td>
                                                                    <input type="hidden" class="temp_discount_per" name="temp_discount_per[]" value="' . $obj->pro_discount . '" />
                                                                
                                                                    <td class="text-right">' . $obj->pro_value . '</td>
                                                                    <input type="hidden" class="temp_quo_price" name="temp_quo_price[]" value="' . $obj->quo_value . '" />
                                                                    <input type="hidden" class="temp_discount_val" name="temp_discount_val[]" value="' .  $obj->pro_discount_amt . '">

                                                                    <td class="text-right">' . $obj->vat . '</td>
                                                                    <input type="hidden" class="temp_vat" name="temp_vat[]" value="' . $obj->vat . '" />
                                                                    <input type="hidden" class="quo_pack_taxable_value" name="quo_pack_taxable_value[]" value="' . $obj->tax_value . '"/>

                                                                    <td class="text-right">' . $obj->net_value . '</td>
                                                                    <input type="hidden" class="temp_net_amt" name="temp_net_amt[]"  id="net_total" value="' . $obj->net_value . '" />
                                                                </tr>';
                                                                }
                                                            }
                                                            $tot_qty = $dbconn->GetSingleReconrd("tbl_proforma_details", "SUM(pro_qty)", "pro_id", $_REQUEST['pro_id']);
                                                            $tot_amt = $dbconn->GetSingleReconrd("tbl_proforma_details", "SUM(pro_value)", "pro_id", $_REQUEST['pro_id']);
                                                            $tot_netamt = $dbconn->GetSingleReconrd("tbl_proforma_details", "SUM(net_value)", "pro_id", $_REQUEST['pro_id']);
                                                        }

                                                        ?>
                                                    </tbody>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <th colspan="2" class="text-right">Total </th>
                                                            <th id="quo_total_qty" class="text-right"><?php echo $tot_qty; ?></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th id="quo_total_amt" class="text-right"><?php echo $tot_amt; ?></th>
                                                            <th class="text-right"></th>
                                                            <th id="quo_total_netamt" name="quo_total_netamt" class="text-right"><?php echo $tot_netamt; ?></th>
                                                            <input type="hidden" id="txt_quo_total_amt" value="<?php echo $tot_amt; ?>">
                                                            <input type="hidden" id="txt_quo_total_netamt" name="txt_quo_total_netamt" value="<?php echo $tot_netamt; ?>">
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>

                                        <legend class="font-weight-semibold"></legend>

                                        <!----------------------------package table---------------------------->
                                        <div class="form-group row">
                                            <div id="package_table" class="col-md-12">

                                                <table class="table table-xs table-bordered" style="font-size: small !important;">
                                                    <thead>
                                                        <tr class="bg-teal">
                                                            <th>Description</th>
                                                            <th>%/Fixed Amt</th>
                                                            <th>Taxable Value</th>
                                                            <th>HSN</th>
                                                            <th>GST %</th>
                                                            <th>GST Amount</th>
                                                            <th>Total Value</th>

                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        if (isset($_REQUEST['quo_id'])) {
                                                            $get_quo_pack_dets =  $conn->query("SELECT * FROM tbl_quo_pack_details WHERE  quo_id = '" . $_REQUEST['quo_id'] . "'");

                                                            if ($get_quo_pack_dets->rowCount() > 0) {
                                                                while ($obj = $get_quo_pack_dets->fetch(PDO::FETCH_OBJ)) {

                                                                    $percent = $obj->quo_pack_percent;
                                                                    if ($percent == 1) {
                                                                        $percent_val =   $obj->quo_pack_text . " %";
                                                                    } else {
                                                                        $percent_val = number_format($obj->quo_pack_text, 0) . " - FA";
                                                                    }
                                                                    echo '<tr id="PK_' . $obj->pack_quo_details_id . '"  class="p' . $obj->quo_id . '">																

																		<td>' . $dbconn->GetSingleReconrd("mst_quo_details", "quo_pack_decp", "quo_id",  $obj->quo_pack_decp) . '
																		<input type="hidden" class="pack_id" name="pack_id[]" value="'  . $obj->quo_pack_decp . '" />
																		
																		</td>
																		
																		<td>' . $percent_val . '
																		<input type="hidden" class="quo_pack_per_fa_value" name="quo_pack_per_fa_value[]" value="' . $percent_val . '" />
																		<input type="hidden" class="quo_pack_taxable_val" name="quo_pack_taxable_val[]" value="' . $obj->quo_pack_taxable_val . '" />
																		<input type="hidden" class="quo_pack_gst_amt" name="quo_pack_gst_amt[]" value="' . $obj->quo_pack_value . '" >
																		</td>

																		<td class="text-right disp_tax_val">' . number_format($obj->quo_pack_taxable_val, 2)  . '
																		
																		</td>

																		<td class="text-right">' . $obj->gst_id . '
																		<input type="hidden" class="quo_pack_gst_id" name="quo_pack_gst_id[]" value="' . $obj->gst_id . '" >
																		<input type="hidden" class="quo_pack_per_fa" name="quo_pack_per_fa[]" value="' . $percent . '" >
																		</td>

																		<td class="text-right">' . $obj->quo_pack_vat . '	
																		<input type="hidden" class="quo_pack_gst_per" name="quo_pack_gst_per[]" value="' . $obj->quo_pack_vat . '" />
																		<input type="hidden" class="quo_pack_total" name="quo_pack_total[]" id="quo_pack_total" value="' . $obj->quo_pack_total . '" />
																		</td>

																		<td class="text-right disp_gst_amt">' . number_format($obj->quo_pack_value, 2) . '
																		
																		</td>

																		<td class="text-right disp_pack_total">' . number_format($obj->quo_pack_total, 2) . '
																		
																		</td>

																		

																	</tr>';
                                                                }
                                                            }
                                                            $pack_total = $dbconn->GetSingleReconrd("tbl_quo_pack_details", "SUM(quo_pack_total)", "quo_id", $_REQUEST['quo_id']);
                                                        }

                                                        //Proforma Pack Details
                                                        if (isset($_REQUEST['pro_id'])) {
                                                            $get_pro_pack_dets =  $conn->query("SELECT * FROM tbl_proforma_pack_details WHERE  pro_id = '" . $_REQUEST['pro_id'] . "'");

                                                            if ($get_pro_pack_dets->rowCount() > 0) {
                                                                while ($obj = $get_pro_pack_dets->fetch(PDO::FETCH_OBJ)) {

                                                                    $percent = $obj->pro_pack_percent;
                                                                    if ($percent == 1) {
                                                                        $percent_val =   $obj->pro_pack_text . " %";
                                                                    } else {
                                                                        $percent_val = number_format($obj->pro_pack_text, 0) . " - FA";
                                                                    }
                                                                    echo '<tr id="PK_' . $obj->pack_pro_details_id . '"  class="p' . $obj->pro_id . '">                                                             

                                                                        <td>' . $dbconn->GetSingleReconrd("mst_quo_details", "quo_pack_decp", "quo_id",  $obj->pro_pack_decp) . '
                                                                        <input type="hidden" class="pack_id" name="pack_id[]" value="'  . $obj->pro_pack_decp . '" />
                                                                        
                                                                        </td>
                                                                        
                                                                        <td>' . $percent_val . '
                                                                        <input type="hidden" class="quo_pack_per_fa_value" name="quo_pack_per_fa_value[]" value="' . $percent_val . '" />
                                                                        <input type="hidden" class="quo_pack_taxable_val" name="quo_pack_taxable_val[]" value="' . $obj->pro_pack_taxable_val . '" />
                                                                        <input type="hidden" class="quo_pack_gst_amt" name="quo_pack_gst_amt[]" value="' . $obj->pro_pack_value . '" >
                                                                        </td>

                                                                        <td class="text-right disp_tax_val">' . number_format($obj->pro_pack_taxable_val, 2)  . '
                                                                        
                                                                        </td>

                                                                        <td class="text-right">' . $obj->gst_id . '
                                                                        <input type="hidden" class="quo_pack_gst_id" name="quo_pack_gst_id[]" value="' . $obj->gst_id . '" >
                                                                        <input type="hidden" class="quo_pack_per_fa" name="quo_pack_per_fa[]" value="' . $percent . '" >
                                                                        </td>

                                                                        <td class="text-right">' . $obj->pro_pack_vat . '   
                                                                        <input type="hidden" class="quo_pack_gst_per" name="quo_pack_gst_per[]" value="' . $obj->pro_pack_vat . '" />
                                                                        <input type="hidden" class="quo_pack_total" name="quo_pack_total[]" id="quo_pack_total" value="' . $obj->pro_pack_total . '" />
                                                                        </td>

                                                                        <td class="text-right disp_gst_amt">' . number_format($obj->pro_pack_value, 2) . '
                                                                        
                                                                        </td>

                                                                        <td class="text-right disp_pack_total">' . number_format($obj->pro_pack_total, 2) . '
                                                                        
                                                                        </td>

                                                                        

                                                                    </tr>';
                                                                }
                                                            }
                                                            $pack_total = $dbconn->GetSingleReconrd("tbl_proforma_pack_details", "SUM(pro_pack_total)", "pro_id", $_REQUEST['pro_id']);
                                                        }


                                                        ?>
                                                    </tbody>
                                                    <tfoot>
                                                        <th colspan="2" class="text-right">Total</th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th id="quo_total_pack_netamt" class="text-right"><?php echo $pack_total; ?></th>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>

                                        <div align="right">
                                            <span style="font-size: 15px;">Proforma Value: </span>
                                            <input type="text" readonly style="font-size:15px; border: none; text-align: left; color: blue; font-weight: bold;" name="pro_tot_value" id="pro_tot_value" value="<?php echo $sales_order_total; ?>" />
                                        </div>

                                        <legend class="font-weight-semibold"></legend>

                                        <div class="control-group">
                                            <div class="span12">
                                                <label class="col-lg-2 col-form-label">Remarks if any :</label>
                                                <textarea name="pro_remarks" id="pro_remarks" maxlength="250" class="form-control"><?php echo $pro_remarks; ?></textarea>
                                            </div>
                                        </div><br><br>
                                    </div>

                                    <div class="card-footer text-center">

                                        <?php if ((isset($_REQUEST['quo_id']) && $_REQUEST['quo_id'] > 0)) { ?>
                                            <INPUT class="btn btn-custom mr-2" type="submit" name="SAVE" value="Generate Proforma">
                                            <INPUT class="btn btn-light mr-2" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
                                        <?php } else { ?>
                                            <input type="hidden" name="txtHid" value="<?php echo $_REQUEST['pro_id']; ?>">
                                            <INPUT class="btn btn-light mr-2" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
                                        <?php } ?>

                                    </div>
                                </div>
                    </div>
                </div>

                </fieldset>
                </form>

            </div>
            <!-- Footer -->
            <?php include("inc/common/footer.php") ?>
            <!-- /footer -->
        </div>
    </div>
    </div>
    </div>
</body>

</html>


<script type="text/javascript">
    var wasSubmitted = false;

    function fnValidate() {

        if (notSelected(document.thisForm.supp_id, "Customer..!")) {
            return false;
        }
        if (isNull(document.thisForm.pro_date, "Proforma Date..!")) {
            return false;
        }
        if (isNull(document.thisForm.pro_trans_charge, "Transport Charges..!")) {
            return false;
        }


        //cah amount and net amount should be equal
        var total_val5 = parseFloat($('#pro_tot_value').val());
        //end
        if (!wasSubmitted) {
            wasSubmitted = true;
            document.thisForm.submit();
            return true;
        }
        return false;
    }

    $(function() {

        $('.cash_denomination').hide();

        $('#supp_id').change(function() {
            var supp_id = $('#supp_id').val();

            $.ajax({
                type: "POST",
                url: "inc/cis_ajax/jquery_select_item.php",
                data: {
                    supp_id: supp_id,
                    mode: 'cus_branch'
                }
            }).done(function(msg) {
                $('#branch_id option').remove();
                var dataArr = msg.split('#');
                $.each(dataArr, function(i, element) {
                    if (dataArr[i] != "") {
                        var dataArr2 = dataArr[i].split('~');
                        $('#branch_id').append("<option value='" + dataArr2[0] + "'>" + dataArr2[1] + "</option>");
                    }
                });
                $("#s2id_branch_id").select2('val', '');
                $("#branch_id").trigger("liszt:updated");
            });
        });
    });


    function remove_item(auto_id) {
        $('#tbl' + auto_id).remove();
        findReciptTotal();
    }

    function findReciptTotal() {
        var sales_total_cost = 0;
        $("#show_table2 tr").each(function() {
            sales_order_reci = parseFloat($(this).closest('tr').find('.temp_total').val());
            if (isNaN(sales_order_reci)) sales_order_reci = 0;
            sales_total_cost += sales_order_reci;
            $('#show_table2 tfoot th#lastRow01').html(sales_total_cost.toFixed(2));
        });

    }
</script>