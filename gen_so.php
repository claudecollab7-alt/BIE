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

// -------------------------------------------- SAVE DATABASE -------------------------------//

$_REQUEST['so_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);


if (isset($_POST['SAVE'])) {
    $_REQUEST['select_branch_id'] = (isset($_REQUEST['select_branch_id'])) ? ($_REQUEST['select_branch_id']) : '';
    // try {

    $_REQUEST['so_date'] = date("Y-m-d", strtotime($_REQUEST['so_date']));
    $_REQUEST['despatch_date'] = date("Y-m-d", strtotime($_REQUEST['despatch_date']));

    $_REQUEST['so_slno'] = $_REQUEST['so_no'];
	// $_REQUEST['so_slno'] = $dbconn->GetMaxValue('tbl_sales_order', 'so_slno', 'branch_id="'.$_SESSION['_user_branch'].'" AND 1 ', 1) + 1;


    $_REQUEST['so_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
	$_REQUEST['branch'] = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id='".$_SESSION['_user_branch']."' AND branch_status", 1);

	$_REQUEST['so_refno'] = 'SO/' . leadingZeros($_REQUEST['so_slno'], 4) . '/BIE/'.$_REQUEST['branch'].'/' . $_REQUEST['so_finyr'];


    $_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
    $_REQUEST['modify_by'] = $_SESSION['_user_id'];


    $stmt = null;
    $stmt = $conn->prepare("INSERT INTO tbl_sales_order (so_finyr, so_slno, so_refno, so_ref, so_date, bie_branch_id, despatch_date, quo_id, supp_id, branch_id, item_net_val,bal_value, so_remarks, modify_date_time, modify_by ) VALUES (:so_finyr, :so_slno, :so_refno, :so_ref, :so_date, :bie_branch_id, :despatch_date, :quo_id, :supp_id, :branch_id, :item_net_val,  :bal_value, :so_remarks,  :modify_date_time, :modify_by)");
    $data = array(
        ':so_finyr' => $_REQUEST['so_finyr'],
        ':so_slno' => $_REQUEST['so_slno'],
        ':so_refno' => $_REQUEST['so_refno'],
        ':so_ref' => $_REQUEST['so_ref'],
        ':so_date' => $_REQUEST['so_date'],
		':bie_branch_id' => $_SESSION['_user_branch'],
        ':despatch_date' => $_REQUEST['despatch_date'],
        ':quo_id' => $_REQUEST['quo_id'],
        ':supp_id' => $_REQUEST['supp_id'],
        ':branch_id' => $_REQUEST['select_branch_id'],
        ':item_net_val' => $_REQUEST['sales_order_total'],
        ':bal_value' => $_REQUEST['sales_order_total'],
        ':so_remarks' => $_REQUEST['so_remarks'],
        ':modify_date_time' => $_REQUEST['modify_date_time'],
        ':modify_by' => $_REQUEST['modify_by']
    );
    // print_r($data);
    $stmt->execute($data);
    $last_id = $conn->lastInsertId();
    if($last_id>0)
    {
        $conn->query("UPDATE tbl_quotation SET so_gen_status = 1, quo_so_id='".$last_id."' WHERE quo_id=" . $_REQUEST['quo_id']);
    }
   
    // Individual item ...

    $stmt = null;
    $stmt = $conn->prepare("INSERT INTO tbl_sales_order_details (so_id,  item_id, so_qty, so_unit, so_selling_price, so_discount, so_discount_amt, so_vat, so_value, so_tax_value, item_total) 
	VALUES (:so_id, :item_id, :so_qty,  :so_unit, :so_selling_price, :so_discount, :so_discount_amt, :so_vat, :so_value, :so_tax_value, :item_total)");
    $row_count = count($_REQUEST['temp_item_id']);

    for ($n = 0; $n < $row_count; $n++) {
        $data = array(
            ':so_id' => $last_id,
            ':item_id' => $_REQUEST['temp_item_id'][$n],
            ':so_qty' => $_REQUEST['temp_qty'][$n],
            ':so_unit' => $_REQUEST['temp_unit'][$n],
            ':so_selling_price' => $_REQUEST['temp_selling_price'][$n],
            ':so_discount' => $_REQUEST['temp_discount_per'][$n],
            ':so_discount_amt' => $_REQUEST['temp_discount_val'][$n],
            ':so_vat' => $_REQUEST['temp_vat'][$n],
            ':so_value' => $_REQUEST['temp_quo_price'][$n],
            ':so_tax_value' => $_REQUEST['quo_pack_taxable_value'][$n],
            ':item_total' => $_REQUEST['temp_net_amt'][$n]
        );
        // print_r($data);die();
        $stmt->execute($data);
    }

    // package charges details...

    $stmt = null;
    $stmt = $conn->prepare("INSERT INTO tbl_sales_order_pack_dts (so_id, pack_decp, pack_percent, pack_text, pack_taxable_val, gst_id, pack_vat, pack_value, pack_total)
		                    VALUES (:so_id, :pack_decp, :pack_percent, :pack_text, :pack_taxable_val, :gst_id, :pack_vat, :pack_value, :pack_total)");
    if(isset($_REQUEST['pack_id'])){
        $row_count = (count($_REQUEST['pack_id']));
        if ($row_count > 0) {
            for ($n = 0; $n < $row_count; $n++) {
                $quo_pack_total = isset($_REQUEST['pack_total'][$n]) ? $_REQUEST['pack_total'][$n] : '';
                $data = array(
                    ':so_id' => $last_id,
                    ':pack_decp' => $_REQUEST['pack_id'][$n],
                    ':pack_percent' => $_REQUEST['quo_pack_per_fa'][$n],
                    ':pack_text' => $_REQUEST['quo_pack_per_fa_value'][$n],
                    ':pack_taxable_val' => $_REQUEST['quo_pack_taxable_val'][$n],
                    ':gst_id' => $_REQUEST['quo_pack_gst_id'][$n],
                    ':pack_vat' => $_REQUEST['quo_pack_gst_per'][$n],
                    ':pack_value' => $_REQUEST['quo_pack_gst_amt'][$n],
                    ':pack_total' => $_REQUEST['quo_pack_total'][$n]
                );
                $stmt->execute($data);
            }
        }
    }
    header("location:lst_sales_order.php");
    die();
}
if (isset($_POST['UPDATE'])) {

    $update_id = $_REQUEST['txtHid'];
    $_REQUEST['modify_by'] = $_SESSION['_user_id'];
    $_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
    $_REQUEST['so_slno'] = $_REQUEST['so_no'];
    // $_REQUEST['so_slno'] = $dbconn->GetMaxValue('tbl_sales_order', 'so_slno', 'branch_id="'.$_SESSION['_user_branch'].'" AND 1 ', 1) + 1;
    $_REQUEST['branch'] = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id='".$_SESSION['_user_branch']."' AND branch_status", 1);

	$_REQUEST['so_refno'] = 'SO/' . leadingZeros($_REQUEST['so_slno'], 4) . '/BIE/'.$_REQUEST['branch'].'/' . $_REQUEST['so_finyr'];

    $stmt = null;
    $stmt = $conn->prepare("UPDATE tbl_sales_order SET so_finyr = :so_finyr, so_slno = :so_slno, so_refno = :so_refno, so_ref = :so_ref, so_date = :so_date, despatch_date = :despatch_date, quo_id = :quo_id, supp_id = :supp_id, branch_id = :branch_id, item_net_val = :item_net_val, so_remarks = :so_remarks, modify_date_time = :modify_date_time, modify_by = :modify_by  WHERE  so_id = :so_id");

    $data = array(
        ':so_id' => $update_id,
        ':so_finyr' => $_REQUEST['so_finyr'],
        ':so_slno' => $_REQUEST['so_slno'],
        ':so_refno' => $_REQUEST['so_refno'],
        ':so_ref' => $_REQUEST['so_ref'],
        ':so_date' => $_REQUEST['so_date'],
        ':despatch_date' => $_REQUEST['despatch_date'],
        ':quo_id' => $_REQUEST['quo_id'],
        ':supp_id' => $_REQUEST['supp_id'],
        ':branch_id' => $_REQUEST['select_branch_id'],
        ':item_net_val' => $_REQUEST['sales_order_total'],
        ':so_remarks' => $_REQUEST['so_remarks'],
        ':modify_date_time' => $_REQUEST['modify_date_time'],
        ':modify_by' => $_REQUEST['modify_by']
    );
    $stmt->execute($data);
    // print_r($data);die();
    // $conn->query("UPDATE tbl_quotation SET so_des_gen = 1 WHERE quo_id=" . $_REQUEST['txtHid']);
    // $conn->query("UPDATE tbl_sales_order SET accounts_status = 1 WHERE quo_id=" . $_REQUEST['txtHid']);

    $sql = "DELETE FROM tbl_sales_order_details WHERE so_id = '" . $update_id . "'";
    $result = $conn->prepare($sql);
    $result->execute();

    $stmt = null;
    $stmt = $conn->prepare("INSERT INTO tbl_sales_order_details (so_id,  item_id, so_qty, so_unit, so_selling_price, so_discount, so_discount_amt, so_vat, so_value, so_tax_value, item_total) 
VALUES (:so_id, :item_id, :so_qty,  :so_unit, :so_selling_price, :so_discount, :so_discount_amt, :so_vat, :so_value, :so_tax_value, :item_total)");
    $row_count = count($_REQUEST['temp_item_id']);

    for ($n = 0; $n < $row_count; $n++) {
        $data = array(
            ':so_id' => $update_id,
            ':item_id' => $_REQUEST['temp_item_id'][$n],
            ':so_qty' => $_REQUEST['temp_qty'][$n],
            ':so_unit' => $_REQUEST['temp_unit'][$n],
            ':so_selling_price' => $_REQUEST['temp_selling_price'][$n],
            ':so_discount' => $_REQUEST['temp_discount_per'][$n],
            ':so_discount_amt' => $_REQUEST['temp_discount_val'][$n],
            ':so_vat' => $_REQUEST['temp_vat'][$n],
            ':so_value' => $_REQUEST['temp_quo_price'][$n],
            ':so_tax_value' => $_REQUEST['quo_pack_taxable_value'][$n],
            ':item_total' => $_REQUEST['temp_net_amt'][$n]
        );
        // print_r($data);
        $stmt->execute($data);
    }
    $sql = "DELETE FROM tbl_sales_order_pack_dts WHERE so_id = '" . $update_id . "'";
    $result = $conn->prepare($sql);
    $result->execute();

    $stmt = null;
    $stmt = $conn->prepare("INSERT INTO tbl_sales_order_pack_dts (so_id, pack_decp, pack_percent, pack_text, pack_taxable_val, gst_id, pack_vat, pack_value, pack_total)
		                    VALUES (:so_id, :pack_decp, :pack_percent, :pack_text, :pack_taxable_val, :gst_id, :pack_vat, :pack_value, :pack_total)");

    if(isset($_REQUEST['pack_id'])){

        $row_count = count($_REQUEST['pack_id']);
        if ($row_count > 0) {
            for ($n = 0; $n < $row_count; $n++) {
                $quo_pack_total = isset($_REQUEST['pack_total'][$n]) ? $_REQUEST['pack_total'][$n] : '';
                $data = array(
                    ':so_id' => $update_id,
                    ':pack_decp' => $_REQUEST['pack_id'][$n],
                    ':pack_percent' => $_REQUEST['quo_pack_per_fa'][$n],
                    ':pack_text' => $_REQUEST['quo_pack_per_fa_value'][$n],
                    ':pack_taxable_val' => $_REQUEST['quo_pack_taxable_val'][$n],
                    ':gst_id' => $_REQUEST['quo_pack_gst_id'][$n],
                    ':pack_vat' => $_REQUEST['quo_pack_gst_per'][$n],
                    ':pack_value' => $_REQUEST['quo_pack_gst_amt'][$n],
                    ':pack_total' => $_REQUEST['quo_pack_total'][$n]
                );
                $stmt->execute($data);
            }
        }

    }
    // print_r($data);die();
    header("location:lst_sales_order.php");
    die();
}

if (isset($_POST['ACCOUNTS'])) {
    
    $conn->query("UPDATE tbl_sales_order SET accounts_status = 1 WHERE so_id=" . $_REQUEST['txtHid']);

    header("location:lst_sales_order.php");
    die();
}

//  Edit -----View----- Fetch data ...

if (isset($_REQUEST['so_id'])) {
    $get_val = $conn->query("SELECT * FROM tbl_sales_order WHERE  so_id =' " . $_REQUEST['so_id'] . "'");

    if ($get_val->rowCount() > 0) {
        $obj = $get_val->fetch(PDO::FETCH_OBJ);

        $supp_id = $obj->supp_id;
        $pieces = explode(" ", $pizza);
        $branch_id = $obj->branch_id;
        $ref_no = $obj->so_refno;
        $so_date = $obj->so_date;
        $despatch_date = $obj->despatch_date;
        $so_ref = $obj->so_ref;
        $so_remarks = $obj->so_remarks;
        $sales_order_total = $obj->item_net_val;
        $branch_name = $obj->branch_id;
       
        $so_no = leadingZeros($dbconn->GetMaxValue('tbl_sales_order', 'so_slno', 'so_finyr', $_REQUEST['so_finyr']), 3);
    }
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
        $so_no = leadingZeros($dbconn->GetMaxValue('tbl_sales_order', 'so_slno', 'so_finyr', $_REQUEST['so_finyr']) + 1, 3);
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <title><?php echo PAGE_TITLE; ?> - Sales Order</title>
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
                            <a href="#" class="breadcrumb-item"> Sales</a>
                            <span class="breadcrumb-item active">Sales Order</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>
            <!-- This Form UI Starts here --->
            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">
                        <form name='thisForm' id="validate" class="form-horizontal" method='post' action="gen_so.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                            <input type="hidden" name="quo_id" id="quo_id[]" value="<?php echo $obj->quo_id; ?>">
                            <fieldset>
                                <div class="card">
                                    <div class="card-header bg-pgheader text-white header-elements-inline">
                                        <h6 class="card-title">Sales Order</h6>
                                        <div class="header-elements">
                                            <div class="list-icons">
                                                <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                                <a class="list-icons-item" href="lst_sales_order.php" title="Sales Order List"><i class="icon-arrow-left52 mr-2"></i></a>
                                                <a class="list-icons-item" data-action="fullscreen"></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <label class="col-lg-2 col-form-label">Sales Order No <span class="text-mandatory"></span></label>
                                            <div class="col-lg-4">
                                                <input type="text" class="form-control" name="so_no" id="so_no" style="font-size: 16px; color: blue; font-weight: bold;" value="<?php echo $so_no; ?>" readonly />
                                            </div>
                                            <label class="col-lg-2 col-form-label">Date <span class="text-mandatory"> *</span></label>
                                            <div class="col-lg-4">
                                                <input type="date" name="so_date" id="so_date" class="form-control" maxlength="75" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $so_date; ?>" placeholder="Date" />
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-lg-2 col-form-label">Customer<span class="text-mandatory"> *</span></label>
                                            <div class="col-lg-4">
                                                <select name="supp_id" id="supp_id" data-placeholder="Choose a Customer.." class="select">
                                                    <option value="">-- Select Customer --</option>
                                                    <?php
                                                    $dbconn = new dbhandler();
                                                    echo $dbconn->fnFillComboFromTable_Where("supp_id", "CONCAT(supp_name,' - ',supp_add2)", "mst_supplier_new", "supp_id", " WHERE supp_status = '1' AND supp_type = 'C'");
                                                    ?>
                                                </select>
                                            </div>

                                            <script>
                                                document.thisForm.supp_id.value = "<?php echo $obj->supp_id; ?>";
                                            </script>

                                            <label class="col-lg-2 col-form-label">Branch<span class="text-mandatory"></span></label>
                                            <div class="col-lg-4">
                                                <select name="select_branch_id" id="select_branch_id" data-placeholder="Choose a Branch.." class="select select-search">
                                                    <option value="0">-- Select Branch --</option>
                                                    <?php
                                                    $dbconn = new dbhandler();
                                                    echo $dbconn->fnFillComboFromTable_Where("branch_id", "branch_name", "mst_customer_branch", "branch_id ", " WHERE branch_status ='1'");
                                                    ?>
                                                </select>
                                            </div>

                                        </div>
                                        <script>
                                            document.thisForm.select_branch_id.value = "<?php echo $branch_id ?>";
                                           
                                        </script>
                                        <div class="form-group row">

                                            <label class="col-lg-2 col-form-label">Sales Order Ref <span class="text-mandatory"></span></label>
                                            <div class="col-lg-4">
                                                <input type="text" class="form-control" id="so_ref" name="so_ref" maxlength="35" value="<?php $obj->so_ref ?> " />
                                            </div>

                                            <label class="col-lg-2 col-form-label">Despatch Date<span class="text-mandatory"> *</span></label>
                                            <div class="col-lg-4">
                                                <input type="date" name="despatch_date" id="despatch_date" class="form-control " maxlength="75" value="<?php echo $despatch_date; ?>" />
                                            </div>
                                        </div>
                                        <legend class="font-weight-semibold "><i class="icon-address-book  mr-2"></i>Sales Order Details</legend>

                                        <!-----individual description table--------->

                                        <div class="form-group row">
                                            <div id="quo_table" class="col-md-12">
                                                <table class="table table-xs table-bordered" style="font-size: small !important;">
                                                    <thead>
                                                        <tr class="bg-teal">
                                                            <th>Description</th>
                                                            <th>Item Code</th>
                                                            <th>Avl Qty</th>
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
                                                        <?php


                                                        if (isset($_REQUEST['quo_id'])) {
                                                            $get_quo_dets =  $conn->query("SELECT * FROM tbl_quotation_details WHERE  quo_id = '" . $_REQUEST['quo_id'] . "'");
                                                            if ($get_quo_dets->rowCount() > 0) {
                                                                while ($obj = $get_quo_dets->fetch(PDO::FETCH_OBJ)) 
                                                                {
                                                                    $field_name = $dbconn->GetSingleReconrd("mst_branch","branch_stock_field","branch_id",$_SESSION['_user_branch']);

                                                                    $item_avl_qty = $dbconn->GetSingleReconrd("tbl_item_stock","$field_name","item_id",$obj->item_id);
                                                                    if($item_avl_qty<$obj->quo_qty)
                                                                    {
                                                                        $in_stock='<span style="color:red;"><b>'.round($item_avl_qty).'</b></span>';
                                                                    }
                                                                    else
                                                                    {
                                                                        $in_stock='<span style="color:green;">'.round($item_avl_qty).'</span>';
                                                                    }

                                                                    echo '<tr id="' . $obj->item_id . '" class="g' . $obj->group_id . '">
																	<td>' . $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_id", $obj->item_id) . '</td>
																	<input type="hidden" class="item_desciption" name="item_desciption[]" value="' . $obj->item_id . '" />

																	<td>' . $dbconn->GetSingleReconrd("tbl_item_details", "item_code", "item_id", $obj->item_id) . '</td>
																	<input type="hidden" class="temp_item_id" name="temp_item_id[]" value="' . $obj->item_id . '" />

                                                                    <td class="text-right">'.$in_stock.'</td>

																	<td class="text-right">' . $obj->quo_qty . '</td>
																	<input type="hidden" class="temp_qty" name="temp_qty[]" value="' . $obj->quo_qty . '" />

																	<td class="text-right">' . $obj->quo_unit . '</td>
																	<input type="hidden" class="temp_unit" name="temp_unit[]" value="' . $obj->quo_unit . '" />

																	<td class="text-right">' . $obj->selling_price . '</td>
																	<input type="hidden" class="temp_selling_price" name="temp_selling_price[]" value="' . $obj->selling_price . '" />

																	<td class="text-right">' . $obj->quo_discount . '</td>
																	<input type="hidden" class="temp_discount_per" name="temp_discount_per[]" value="' . $obj->quo_discount . '" />
                                                                    <input type="hidden" class="temp_discount_val" name="temp_discount_val[]" value="' .  $obj->quo_discount_amt . '">
																	<td class="text-right">' . $obj->quo_value . '</td>
																	<input type="hidden" class="temp_quo_price" name="temp_quo_price[]" value="' . $obj->quo_value . '" />
																	
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
                                                        if (isset($_REQUEST['so_id'])) {
                                                            $get_sales_order_dets =  $conn->query("SELECT * FROM tbl_sales_order_details WHERE  so_id = '" . $_REQUEST['so_id'] . "'");
                                                            if ($get_sales_order_dets->rowCount() > 0) {
                                                                while ($obj = $get_sales_order_dets->fetch(PDO::FETCH_OBJ)) 
                                                                {
                                                                    $field_name = $dbconn->GetSingleReconrd("mst_branch","branch_stock_field","branch_id",$_SESSION['_user_branch']);

                                                                    $item_avl_qty = $dbconn->GetSingleReconrd("tbl_item_stock","$field_name","item_id",$obj->item_id);
                                                                    if($item_avl_qty<$obj->quo_qty)
                                                                    {
                                                                        $in_stock='<span style="color:red;"><b>'.$item_avl_qty.'</b></span>';
                                                                    }
                                                                    else
                                                                    {
                                                                        $in_stock='<span style="color:green;">'.$item_avl_qty.'</span>';
                                                                    }

                                                                    echo '<tr id="' . $obj->item_id . '" class="g' . $obj->group_id . '">
																	<td>' . $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_id", $obj->item_id) . '</td>
																	<input type="hidden" class="item_desciption" name="item_desciption[]" value="' . $obj->item_id . '" />

																	<td>' . $dbconn->GetSingleReconrd("tbl_item_details", "item_code", "item_id", $obj->item_id) . '</td>
																	<input type="hidden" class="temp_item_id" name="temp_item_id[]" value="' . $obj->item_id . '" />

                                                                    <td class="text-right">'.$in_stock.'</td>

																	<td class="text-right">' . $obj->so_qty . '</td>
																	<input type="hidden" class="temp_qty" name="temp_qty[]" value="' . $obj->so_qty . '" />

																	<td class="text-right">' . $obj->so_unit . '</td>
																	<input type="hidden" class="temp_unit" name="temp_unit[]" value="' . $obj->so_unit . '" />

																	<td class="text-right">' . $obj->so_selling_price . '</td>
																	<input type="hidden" class="temp_selling_price" name="temp_selling_price[]" value="' . $obj->so_selling_price . '" />

																	<td class="text-right">' . $obj->so_discount . '</td>
																	<input type="hidden" class="temp_discount_per" name="temp_discount_per[]" value="' . $obj->so_discount . '" />
                                                                    <input type="hidden" class="temp_discount_val" name="temp_discount_val[]" value="' .  $obj->so_discount_amt . '">
																
																	<td class="text-right">' . $obj->so_value . '</td>
																	<input type="hidden" class="temp_quo_price" name="temp_quo_price[]" value="' . $obj->so_value . '" />
																	
																	<td class="text-right">' . $obj->so_vat . '</td>
																	<input type="hidden" class="temp_vat" name="temp_vat[]" value="' . $obj->so_vat . '" />
                                                                    <input type="hidden" class="quo_pack_taxable_value" name="quo_pack_taxable_value[]" value="' . $obj->so_tax_value . '"/>

																	<td class="text-right">' . $obj->item_total . '</td>
																	<input type="hidden" class="temp_net_amt" name="temp_net_amt[]"  id="net_total" value="' . $obj->item_total . '" />

																	
																</tr>';
                                                                }
                                                            }

                                                            $tot_qty = $dbconn->GetSingleReconrd("tbl_sales_order_details", "SUM(so_qty)", "so_id", $_REQUEST['so_id']);
                                                            $tot_amt = $dbconn->GetSingleReconrd("tbl_sales_order_details", "SUM(so_value)", "so_id", $_REQUEST['so_id']);
                                                            $tot_netamt = $dbconn->GetSingleReconrd("tbl_sales_order_details", "SUM(item_total)", "so_id", $_REQUEST['so_id']);
                                                        }

                                                        ?>

                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <th colspan="3" class="text-right">Total </th>
                                                            <th id="quo_total_qty" class="text-right"><?php echo $tot_qty; ?></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th id="quo_total_amt" class="text-right"><?php echo $tot_amt; ?></th>
                                                            <th></th>
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
                                                                while ($obj = $get_quo_pack_dets->fetch(PDO::FETCH_OBJ)) 
                                                                {



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



                                                        if (isset($_REQUEST['so_id'])) {
                                                            $get_pack_charge_dets =  $conn->query("SELECT * FROM  tbl_sales_order_pack_dts WHERE  so_id = '" . $_REQUEST['so_id'] . "'");

                                                            if ($get_pack_charge_dets->rowCount() > 0) {
                                                                while ($obj = $get_pack_charge_dets->fetch(PDO::FETCH_OBJ)) {

                                                                    $percent = $obj->pack_percent;
                                                                    if ($percent == 1) {
                                                                        $percent_val =   $obj->pack_text . " %";
                                                                    } else {
                                                                        $percent_val = number_format($obj->pack_text, 0) . " - FA";
                                                                    }
                                                                    echo '<tr id="PK_' . $obj->pack_details_id . '"  class="p' . $obj->so_id . '">																

																		<td>' . $dbconn->GetSingleReconrd("mst_quo_details", "quo_pack_decp", "quo_id",  $obj->pack_decp) . '
																		<input type="hidden" class="pack_id" name="pack_id[]" value="'  . $obj->pack_decp . '" />
																		
																		</td>
																		
																		<td>' . $percent_val . '
																		<input type="hidden" class="quo_pack_per_fa_value" name="quo_pack_per_fa_value[]" value="' . $percent_val . '" />
																		<input type="hidden" class="quo_pack_taxable_val" name="quo_pack_taxable_val[]" value="' . $obj->pack_taxable_val . '" />
																		<input type="hidden" class="quo_pack_gst_amt" name="quo_pack_gst_amt[]" value="' . $obj->pack_value . '" >
																		</td>

																		<td class="text-right disp_tax_val">' . number_format($obj->pack_taxable_val, 2)  . '
																		
																		</td>

																		<td class="text-right">' . $obj->gst_id . '
																		<input type="hidden" class="quo_pack_gst_id" name="quo_pack_gst_id[]" value="' . $obj->gst_id . '" >
																		<input type="hidden" class="quo_pack_per_fa" name="quo_pack_per_fa[]" value="' . $percent . '" >
																		</td>

																		<td class="text-right">' . $obj->pack_vat . '	
																		<input type="hidden" class="quo_pack_gst_per" name="quo_pack_gst_per[]" value="' . $obj->pack_vat . '" />
																		<input type="hidden" class="quo_pack_total" name="quo_pack_total[]" id="quo_pack_total" value="' . $obj->pack_total . '" />
																		</td>

																		<td class="text-right disp_gst_amt">' . number_format($obj->pack_value, 2) . '
																		
																		</td>

																		<td class="text-right disp_pack_total">' . number_format($obj->pack_total, 2) . '
																		
																		</td>

																		

																	</tr>';
                                                                }
                                                            }
                                                            $pack_total = $dbconn->GetSingleReconrd("tbl_sales_order_pack_dts", "SUM(pack_total)", "so_id", $_REQUEST['so_id']);
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
                                            <span style="font-size: 15px;">Sales Order Value: </span>
                                            <input type="text" style="font-size:15px; border: none; text-align: left; color: blue; font-weight: bold;" name="sales_order_total" id="sales_order_total" value="<?php echo $sales_order_total; ?>" />
                                        </div>


                                        <legend class="font-weight-semibold"></legend>

                                        <div class="control-group">
                                            <div class="span12">
                                                <label class="col-lg-2 col-form-label">Remarks if any :</label>
                                                <textarea name="so_remarks" id="so_remarks" maxlength="250" class="form-control"></textarea>
                                            </div>
                                        </div><br><br>
                                    </div>

                                    <div class="card-footer text-center">

                                        <?php if ($_REQUEST["so_id"] != '') { ?>
                                            <INPUT class="btn btn-custom mr-2" type="submit" name="UPDATE" value="UPDATE">
                                            <INPUT class="btn btn-light mr-2" type="button" name="cancel" value="Cancel" onClick="javascript:window.location.href='quotation_list.php'">
                                            <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['so_id']; ?>">
                                            <INPUT class="btn btn-warning mr-2" type="submit" name="ACCOUNTS" value="Send to Accounts">
                                        <?php } else { ?>
                                            <INPUT class="btn btn-custom mr-2" type="submit" name="SAVE" value="SAVE">
                                            <INPUT class="btn btn-light mr-2" type="button" name="cancel" value="Cancel" onClick="javascript:window.location.href='quotation_list.php'">
                                            <!-- <input type="hidden" name="txtHid" value="0"> -->
                                            <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['quo_id']; ?>">
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
<!---------Script-------->

<!-- AUTO COMPLETE -->
<script type='text/javascript' src='js/auto/jquery.autocomplete.js'></script>
<link rel="stylesheet" type="text/css" href="js/auto/jquery.autocomplete.css" />

<script type="text/javascript">
    function fnValidate() {

        if ($('#supp_id').val() == '' || $('#supp_id').val() == null) {
            alert('Please Select the Customer..!');
            return false;
        }
        if (notSelected(document.thisForm.supp_id, "Customer..!")) {
            return false;
        }
        if (isNull(document.thisForm.so_date, "Order date..!")) {
            return false;
        }
        if (isNull(document.thisForm.despatch_date, "Despatch date..!")) {
            return false;
        }
        document.thisForm.submit();
    }
</script>
<script type="text/javascript">
    $(function() {
        var today = new Date().toISOString().split('T')[0];
        document.getElementById("despatch_date").setAttribute('min', today);
    });

   


	$(function() {
        $("#supp_id").on('change', function() {
			var supp_id = $(this).val();
			
			if (supp_id > 0) {

				$.ajax({
					type: "POST",
					url: "inc/cis_ajax/jquery_select_branch.php",
					async: false,
					data: {
						supp_id: supp_id

					}
				}).done(function(msg) {

					$('#select_branch_id option').remove();
					var dataArr = msg.split('#');

					$.each(dataArr, function(i, element) {
						if (dataArr[i] != "") {
							var dataArr2 = dataArr[i].split('~');

							$('#select_branch_id').append("<option value='" + dataArr2[0] + "'>" + dataArr2[1] + "</option>");
						}
					});

				});
			}
			$("#select_branch_id").val('<?php echo $branch_id; ?>').change();
            // $('#select_branch_id option:not(:selected)').attr('disabled', true).change();
		}).change();

    });
</script>