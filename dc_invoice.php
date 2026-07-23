<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");

isAdmin();
$conn = new dbconnect();
$dbconn = new dbhandler();

/* ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL); */

//-------------------------------------------- SAVE DATABASE -------------------------------//

if (isset($_POST['SAVE'])) {
        
    $_REQUEST['select_branch_id'] = (isset($_REQUEST['select_branch_id'])) ? ($_REQUEST['select_branch_id']) : '';
    // try {

    $_REQUEST['inv_date'] = date("Y-m-d", strtotime($_REQUEST['inv_date']));
    $_REQUEST['branch'] = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id='".$_SESSION['_user_branch']."' AND branch_status", 1);
    $_REQUEST['inv_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
    $_REQUEST['inv_slno'] = $dbconn->GetMaxValue('tbl_invoice', 'inv_slno', 'branch_id="'.$_SESSION['_user_branch'].'" AND inv_finyr="'.$_REQUEST['inv_finyr'].'" AND 1', 1) + 1;

    $_REQUEST['inv_refno'] = 'INV/'. leadingZeros($_REQUEST['inv_slno'], 4) .'/BIE/' .$_REQUEST['branch'].'/'  . $_REQUEST['inv_finyr'];

    $_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
    $_REQUEST['modify_by'] = $_SESSION['_user_id'];
    if($_REQUEST['branch_id']>0)
    {
        $_REQUEST['cus_branch_id']=$_REQUEST['branch_id'];    
    }
    else
    {
        $_REQUEST['cus_branch_id']='0';    
    }

    $stmt = null;
    $stmt = $conn->prepare("INSERT INTO tbl_invoice (inv_finyr, inv_slno, inv_refno, inv_date, supp_id, cus_branch_id, dc_id, so_id, inv_mode_of_trans, inv_vechicle_no, inv_trans_charge,inv_tot_value,inv_bal_value,inv_remarks, invoice_type, modify_by, modify_date_time, branch_id) VALUES (:inv_finyr, :inv_slno, :inv_refno, :inv_date, :supp_id, :cus_branch_id, :dc_id, :so_id, :inv_mode_of_trans, :inv_vechicle_no, :inv_trans_charge, :inv_tot_value, :inv_bal_value, :inv_remarks, :invoice_type, :modify_by, :modify_date_time, :branch_id)");
    $data = array(
        ':inv_finyr' => $_REQUEST['inv_finyr'],      
        ':inv_slno' => $_REQUEST['inv_slno'],
        ':inv_refno' => $_REQUEST['inv_refno'],
        ':inv_date' => $_REQUEST['inv_date'],
        ':supp_id' => $_REQUEST['supp_id'],
        ':cus_branch_id' => $_REQUEST['cus_branch_id'],
        ':dc_id' => $_REQUEST['dc_id'],
        ':so_id' => $_REQUEST['so_id'],
        ':inv_mode_of_trans' => $_REQUEST['inv_mode_of_trans'],
        ':inv_vechicle_no' => $_REQUEST['inv_vechicle_no'],
        ':inv_trans_charge' => $_REQUEST['inv_trans_charge'],
        ':inv_tot_value' => $_REQUEST['txt_final_total'],
        ':inv_bal_value' => $_REQUEST['txt_final_total'],
        ':inv_remarks' => $_REQUEST['inv_remarks'],
        ':invoice_type' => 'D',
        ':modify_by' => $_REQUEST['modify_by'],
        ':modify_date_time' => $_REQUEST['modify_date_time'],
        ':branch_id' => $_SESSION['_user_branch']
    );
    
    $stmt->execute($data);
    $last_id = $conn->lastInsertId();
    if($last_id>0)
    {
        $conn->query("UPDATE tbl_dc SET dc_inv_status = 1, dc_inv_id='".$last_id."' WHERE dc_id=" . $_REQUEST['dc_id']);
    }

    // Individual item ...

    $delete_details =  "DELETE FROM  tbl_invoice_details WHERE inv_id = '" . $last_id . "'";
    $result = $conn->prepare($delete_details);
    $result->execute();

    $stmt1 = null;
    $stmt1 = $conn->prepare("INSERT INTO tbl_invoice_details (inv_id, item_id, inv_qty, inv_unit, unit_price, inv_discount, inv_discount_amt, vat, inv_value, tax_value, net_value) 
	VALUES (:inv_id, :item_id, :inv_qty, :inv_unit, :unit_price, :inv_discount, :inv_discount_amt, :vat, :inv_value, :tax_value, :net_value)");

    $row_count = count($_REQUEST['temp_item_id']);

    for ($n = 0; $n < $row_count; $n++) {
        $data1 = array(
            ':inv_id' => $last_id,
            ':item_id' => $_REQUEST['temp_item_id'][$n],
            ':inv_qty' => $_REQUEST['temp_qty'][$n],
            ':inv_unit' => $_REQUEST['temp_unit'][$n],
            ':unit_price' => $_REQUEST['temp_selling_price'][$n],
            ':inv_discount' => $_REQUEST['temp_discount_per'][$n],
            ':inv_discount_amt' => $_REQUEST['temp_discount_val'][$n],
            ':vat' => $_REQUEST['temp_vat'][$n],
            ':inv_value' => $_REQUEST['temp_quo_price'][$n],
            ':tax_value' => $_REQUEST['quo_pack_taxable_value'][$n],
            ':net_value' => $_REQUEST['temp_net_amt'][$n]
        );
        $stmt1->execute($data1);
        // print_r($data1);die();
    }

    if($_REQUEST['pack_id']!='')
    {
        
        $stmt = null;
        $stmt = $conn->prepare("INSERT INTO tbl_invoice_pack_details (inv_id, inv_pack_decp, inv_pack_percent, inv_pack_text, inv_pack_taxable_val, gst_id, inv_pack_vat, inv_pack_value, inv_pack_total)
    		                    VALUES (:inv_id, :inv_pack_decp, :inv_pack_percent, :inv_pack_text, :inv_pack_taxable_val, :gst_id, :inv_pack_vat, :inv_pack_value, :inv_pack_total)");

        $row_count = (count($_REQUEST['pack_id']));
        if ($row_count > 0) {
            for ($n = 0; $n < $row_count; $n++) {
                $quo_pack_total = isset($_REQUEST['quo_pack_total'][$n]) ? $_REQUEST['quo_pack_total'][$n] : '';
                $data = array(
                    ':inv_id' => $last_id,
                    ':inv_pack_decp' => $_REQUEST['pack_id'][$n],
                    ':inv_pack_percent' => $_REQUEST['quo_pack_per_fa'][$n],
                    ':inv_pack_text' => $_REQUEST['quo_pack_per_fa_value'][$n],
                    ':inv_pack_taxable_val' => $_REQUEST['quo_pack_taxable_val'][$n],
                    ':gst_id' => $_REQUEST['quo_pack_gst_id'][$n],
                    ':inv_pack_vat' => $_REQUEST['quo_pack_gst_per'][$n],
                    ':inv_pack_value' => $_REQUEST['quo_pack_gst_amt'][$n],
                    ':inv_pack_total' => $_REQUEST['quo_pack_total'][$n]
                );
                $stmt->execute($data);
            }
        }
    }

    
    $_SESSION['_msg'] = "Invoice succesfully Saved..!";
    header("location:invoice_list.php");
    die();
}

if (isset($_POST['UPDATE'])) 
{

    $update_id = $_REQUEST['txtHid'];
    $_REQUEST['modify_by'] = $_SESSION['_user_id'];
    $_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
     if($_REQUEST['branch_id']>0)
    {
        $_REQUEST['cus_branch_id']=$_REQUEST['branch_id'];    
    }
    else
    {
        $_REQUEST['cus_branch_id']='0';    
    }

    $stmt = null;
    $stmt = $conn->prepare("UPDATE tbl_invoice SET inv_date = :inv_date, supp_id = :supp_id, cus_branch_id = :cus_branch_id, inv_mode_of_trans = :inv_mode_of_trans, inv_vechicle_no = :inv_vechicle_no, inv_trans_charge = :inv_trans_charge, inv_tot_value = :inv_tot_value, inv_bal_value = :inv_bal_value, inv_remarks = :inv_remarks, modify_date_time = :modify_date_time, modify_by = :modify_by  WHERE  inv_id = :inv_id");

    $data = array(
        ':inv_id' => $update_id,
        ':inv_date' => $_REQUEST['inv_date'],       
        ':supp_id' => $_REQUEST['supp_id'],
        ':cus_branch_id' => $_REQUEST['cus_branch_id'],
        ':inv_mode_of_trans' => $_REQUEST['inv_mode_of_trans'],
        ':inv_vechicle_no' => $_REQUEST['inv_vechicle_no'],
        ':inv_trans_charge' => $_REQUEST['inv_trans_charge'],
        ':inv_tot_value' => $_REQUEST['txt_final_total'],
        ':inv_bal_value' => $_REQUEST['txt_final_total'],
        ':inv_remarks' => $_REQUEST['inv_remarks'],
        ':modify_date_time' => $_REQUEST['modify_date_time'],
        ':modify_by' => $_REQUEST['modify_by']
    );
    $stmt->execute($data);
    // print_r($data);die();
    // $conn->query("UPDATE tbl_quotation SET so_des_gen = 1 WHERE dc_id=" . $_REQUEST['txtHid']);
    // $conn->query("UPDATE tbl_sales_order SET accounts_status = 1 WHERE dc_id=" . $_REQUEST['txtHid']);


    $sql = "DELETE FROM tbl_invoice_details WHERE inv_id = '" . $update_id . "'";
    $result = $conn->prepare($sql);
    $result->execute();

    $stmt1 = null;
    $stmt1 = $conn->prepare("INSERT INTO tbl_invoice_details (inv_id, item_id, inv_qty, inv_unit, unit_price, inv_discount, inv_discount_amt, vat, inv_value, tax_value, net_value) 
    VALUES (:inv_id, :item_id, :inv_qty, :inv_unit, :unit_price, :inv_discount, :inv_discount_amt, :vat, :inv_value, :tax_value, :net_value)");

    $row_count = count($_REQUEST['temp_item_id']);

    for ($n = 0; $n < $row_count; $n++) {
        $data1 = array(
            ':inv_id' => $update_id,
            ':item_id' => $_REQUEST['temp_item_id'][$n],
            ':inv_qty' => $_REQUEST['temp_qty'][$n],
            ':inv_unit' => $_REQUEST['temp_unit'][$n],
            ':unit_price' => $_REQUEST['temp_selling_price'][$n],
            ':inv_discount' => $_REQUEST['temp_discount_per'][$n],
            ':inv_discount_amt' => $_REQUEST['temp_discount_val'][$n],
            ':vat' => $_REQUEST['temp_vat'][$n],
            ':inv_value' => $_REQUEST['temp_quo_price'][$n],
            ':tax_value' => $_REQUEST['quo_pack_taxable_value'][$n],
            ':net_value' => $_REQUEST['temp_net_amt'][$n]
        );
        $stmt1->execute($data1);
        // print_r($data1);die();
    }

    if($_REQUEST['pack_id']!='')
    {
        
        $sql = "DELETE FROM tbl_invoice_pack_details WHERE inv_id = '" . $update_id . "'";
        $result = $conn->prepare($sql);
        $result->execute();

        $stmt = null;
        $stmt = $conn->prepare("INSERT INTO tbl_invoice_pack_details (inv_id, inv_pack_decp, inv_pack_percent, inv_pack_text, inv_pack_taxable_val, gst_id, inv_pack_vat, inv_pack_value, inv_pack_total)
                                VALUES (:inv_id, :inv_pack_decp, :inv_pack_percent, :inv_pack_text, :inv_pack_taxable_val, :gst_id, :inv_pack_vat, :inv_pack_value, :inv_pack_total)");

        $row_count = (count($_REQUEST['pack_id']));
        if ($row_count > 0) {
            for ($n = 0; $n < $row_count; $n++) {
                $quo_pack_total = isset($_REQUEST['quo_pack_total'][$n]) ? $_REQUEST['quo_pack_total'][$n] : '';
                $data = array(
                    ':inv_id' => $update_id,
                    ':inv_pack_decp' => $_REQUEST['pack_id'][$n],
                    ':inv_pack_percent' => $_REQUEST['quo_pack_per_fa'][$n],
                    ':inv_pack_text' => $_REQUEST['quo_pack_per_fa_value'][$n],
                    ':inv_pack_taxable_val' => $_REQUEST['quo_pack_taxable_val'][$n],
                    ':gst_id' => $_REQUEST['quo_pack_gst_id'][$n],
                    ':inv_pack_vat' => $_REQUEST['quo_pack_gst_per'][$n],
                    ':inv_pack_value' => $_REQUEST['quo_pack_gst_amt'][$n],
                    ':inv_pack_total' => $_REQUEST['quo_pack_total'][$n]
                );
                $stmt->execute($data);
            }
        }
    }

    
    // print_r($data);die();
    $_SESSION['_msg'] = "Invoice succesfully Updated..!";
    header("location:invoice_list.php");
    die();
}

if (isset($_POST['FINALIZE'])) 
{

    $update_id = $_REQUEST['txtHid'];
    $_REQUEST['modify_by'] = $_SESSION['_user_id'];
    $_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
     if($_REQUEST['branch_id']>0)
    {
        $_REQUEST['cus_branch_id']=$_REQUEST['branch_id'];    
    }
    else
    {
        $_REQUEST['cus_branch_id']='0';    
    }

    $stmt = null;
    $stmt = $conn->prepare("UPDATE tbl_invoice SET inv_date = :inv_date, supp_id = :supp_id, cus_branch_id = :cus_branch_id, inv_mode_of_trans = :inv_mode_of_trans, inv_vechicle_no = :inv_vechicle_no, inv_trans_charge = :inv_trans_charge, inv_tot_value = :inv_tot_value, inv_bal_value = :inv_bal_value, inv_remarks = :inv_remarks, modify_date_time = :modify_date_time, modify_by = :modify_by, inv_status = :inv_status  WHERE  inv_id = :inv_id");

    $data = array(
        ':inv_id' => $update_id,
        ':inv_date' => $_REQUEST['inv_date'],       
        ':supp_id' => $_REQUEST['supp_id'],
        ':cus_branch_id' => $_REQUEST['cus_branch_id'],
        ':inv_mode_of_trans' => $_REQUEST['inv_mode_of_trans'],
        ':inv_vechicle_no' => $_REQUEST['inv_vechicle_no'],
        ':inv_trans_charge' => $_REQUEST['inv_trans_charge'],
        ':inv_tot_value' => $_REQUEST['txt_final_total'],
        ':inv_bal_value' => $_REQUEST['txt_final_total'],
        ':inv_remarks' => $_REQUEST['inv_remarks'],
        ':modify_date_time' => $_REQUEST['modify_date_time'],
        ':modify_by' => $_REQUEST['modify_by'],
        ':inv_status' => 1
    );
    $stmt->execute($data);
    // print_r($data);die();
    // $conn->query("UPDATE tbl_quotation SET so_des_gen = 1 WHERE dc_id=" . $_REQUEST['txtHid']);
    // $conn->query("UPDATE tbl_sales_order SET accounts_status = 1 WHERE dc_id=" . $_REQUEST['txtHid']);

    $sql = "DELETE FROM tbl_invoice_details WHERE inv_id = '" . $update_id . "'";
    $result = $conn->prepare($sql);
    $result->execute();

    $stmt1 = null;
    $stmt1 = $conn->prepare("INSERT INTO tbl_invoice_details (inv_id, item_id, inv_qty, inv_unit, unit_price, inv_discount, inv_discount_amt, vat, inv_value, tax_value, net_value) 
    VALUES (:inv_id, :item_id, :inv_qty, :inv_unit, :unit_price, :inv_discount, :inv_discount_amt, :vat, :inv_value, :tax_value, :net_value)");

    $row_count = count($_REQUEST['temp_item_id']);

    for ($n = 0; $n < $row_count; $n++) {
        $data1 = array(
            ':inv_id' => $update_id,
            ':item_id' => $_REQUEST['temp_item_id'][$n],
            ':inv_qty' => $_REQUEST['temp_qty'][$n],
            ':inv_unit' => $_REQUEST['temp_unit'][$n],
            ':unit_price' => $_REQUEST['temp_selling_price'][$n],
            ':inv_discount' => $_REQUEST['temp_discount_per'][$n],
            ':inv_discount_amt' => $_REQUEST['temp_discount_val'][$n],
            ':vat' => $_REQUEST['temp_vat'][$n],
            ':inv_value' => $_REQUEST['temp_quo_price'][$n],
            ':tax_value' => $_REQUEST['quo_pack_taxable_value'][$n],
            ':net_value' => $_REQUEST['temp_net_amt'][$n]
        );
        $stmt1->execute($data1);
        // print_r($data1);die();
    }

    if($_REQUEST['pack_id']!='')
    {
        
        $sql = "DELETE FROM tbl_invoice_pack_details WHERE inv_id = '" . $update_id . "'";
        $result = $conn->prepare($sql);
        $result->execute();

        $stmt = null;
        $stmt = $conn->prepare("INSERT INTO tbl_invoice_pack_details (inv_id, inv_pack_decp, inv_pack_percent, inv_pack_text, inv_pack_taxable_val, gst_id, inv_pack_vat, inv_pack_value, inv_pack_total)
                                VALUES (:inv_id, :inv_pack_decp, :inv_pack_percent, :inv_pack_text, :inv_pack_taxable_val, :gst_id, :inv_pack_vat, :inv_pack_value, :inv_pack_total)");

        $row_count = (count($_REQUEST['pack_id']));
        if ($row_count > 0) {
            for ($n = 0; $n < $row_count; $n++) {
                $quo_pack_total = isset($_REQUEST['quo_pack_total'][$n]) ? $_REQUEST['quo_pack_total'][$n] : '';
                $data = array(
                    ':inv_id' => $update_id,
                    ':inv_pack_decp' => $_REQUEST['pack_id'][$n],
                    ':inv_pack_percent' => $_REQUEST['quo_pack_per_fa'][$n],
                    ':inv_pack_text' => $_REQUEST['quo_pack_per_fa_value'][$n],
                    ':inv_pack_taxable_val' => $_REQUEST['quo_pack_taxable_val'][$n],
                    ':gst_id' => $_REQUEST['quo_pack_gst_id'][$n],
                    ':inv_pack_vat' => $_REQUEST['quo_pack_gst_per'][$n],
                    ':inv_pack_value' => $_REQUEST['quo_pack_gst_amt'][$n],
                    ':inv_pack_total' => $_REQUEST['quo_pack_total'][$n]
                );
                $stmt->execute($data);
            }
        }
    }



    /* STOCK DETAILS */

    $stmt1 = null;
    $stmt1 = $conn->prepare("INSERT INTO tbl_stock_flow 
                (trans_type, trans_id, branch_id, trans_date, item_id, item_price, before_qty, rcvd_qty, trans_qty, reje_qty, pend_qty, after_qty, modify_by, modify_date_time) 
                VALUES
                (:trans_type, :trans_id, :branch_id, :trans_date, :item_id, :item_price, :before_qty, :rcvd_qty, :trans_qty, :reje_qty, :pend_qty, :after_qty, :modify_by, :modify_date_time)");

    /* New Current Stock Update Branch */
    $field_name = $dbconn->GetSingleReconrd("mst_branch","branch_stock_field","branch_id",$_SESSION['_user_branch']);
    $stmt2 = null;
    $stmt2 = $conn->prepare("UPDATE tbl_item_stock SET ".$field_name." = :branch_stock WHERE item_id = :item_id ");

    for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) 
    {
       
       // $item_curr_stock = $dbconn->GetSingleReconrd("tbl_item_details", "item_curr_stock", "item_id", $_REQUEST['temp_item_id'][$x]);
       $item_curr_stock = $dbconn->GetSingleReconrd("tbl_item_stock", "$field_name", "item_id", $_REQUEST['temp_item_id'][$x]);

       $after_qty =  (int)$item_curr_stock - (int)$_REQUEST['temp_qty'][$x];
       $price = $dbconn->GetSingleReconrd("tbl_item_details", "item_selling_price", "item_id", $_REQUEST['temp_item_id'][$x]);

        $data = array(
            ':trans_type' => 'INV',
            ':trans_id' => $update_id,
            ':branch_id' => $_SESSION['_user_branch'],
            ':trans_date' => date('Y-m-d'),
            ':item_id' => $_REQUEST['temp_item_id'][$x],
            ':item_price' => $price,
            ':before_qty' => $item_curr_stock,
            ':rcvd_qty' => 0,
            ':trans_qty' => $_REQUEST['temp_qty'][$x],
            ':reje_qty' => 0,
            ':pend_qty' => 0,
            ':after_qty' => $after_qty,
            ':modify_by' => $_SESSION['_user_id'],
            ':modify_date_time' => date('Y-m-d H:i:s')
        );
        $stmt1->execute($data);

        $branch_item_curr_stock = $dbconn->GetSingleReconrd("tbl_item_stock", "$field_name", "item_id", $_REQUEST['temp_item_id'][$x]);
        $after_qty2 =  (int)$branch_item_curr_stock - (int)$_REQUEST['temp_qty'][$x];

        $data2 = array(
            ':item_id' => $_REQUEST['temp_item_id'][$x],
            ':branch_stock' => $after_qty2,
        );
        $stmt2->execute($data2);
    }

    /* STOCK DETAILS */


   /* ITEM DETAILS */

    // $_REQUEST['modify_by'] = $_SESSION['_user_id'];
    // $_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');

    // for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) {
    //     $stmt3 = null;
    //     $stmt3 = $conn->prepare("UPDATE tbl_item_details SET item_curr_stock = :item_curr_stock, modify_date_time=:modify_date_time, modify_by=:modify_by WHERE item_id = :item_id ");

    //     $item_curr_stock = $dbconn->GetSingleReconrd("tbl_item_details", "item_curr_stock", "item_id", $_REQUEST['temp_item_id'][$x]);

    //     $data = array(
    //         ':item_id' => $_REQUEST['temp_item_id'][$x],
    //         ':item_curr_stock' => $after_qty,
    //         ':modify_date_time' => $_REQUEST['modify_date_time'],
    //         ':modify_by' => $_REQUEST['modify_by'],
    //     );
    //     $stmt3->execute($data);
    // }

    /* ITEM DETAILS */
    // print_r($data);die();
    $_SESSION['_msg'] = "Invoice succesfully Updated..!";
    header("location:invoice_list.php");
    die();
}





if (isset($_REQUEST['dc_id'])) {
    $get_val = $conn->query("SELECT * FROM tbl_dc WHERE  dc_id =' " . $_REQUEST['dc_id'] . "'");

    if ($get_val->rowCount() > 0) {
        $obj = $get_val->fetch(PDO::FETCH_OBJ);
        $supp_id = $obj->supp_id;
        $so_date = $obj->quo_date;
        $branch_id = $obj->branch_id;
        $branch_name = $obj->branch_id;
        
        $so_id=$obj->so_id;
        $dc_no=$obj->dc_refno;
        $dc_date=$obj->dc_date;
        $cus_branch_id=$obj->cus_branch_id;
        //$so_no = leadingZeros($dbconn->GetMaxValue('tbl_sales_order', 'so_slno', 'so_finyr', $_REQUEST['so_finyr']) + 1, 3);
    }
}

$inv_date=date('Y-m-d');
if (isset($_REQUEST['inv_id'])) {
    $get_inv_val = $conn->query("SELECT * FROM tbl_invoice WHERE  inv_id =' " . $_REQUEST['inv_id'] . "'");

    if ($get_inv_val->rowCount() > 0) {
        $obj = $get_inv_val->fetch(PDO::FETCH_OBJ);
        $supp_id = $obj->supp_id;
        $so_date = $obj->inv_date;
        $branch_id = $obj->cus_branch_id;
        $branch_name = $obj->cus_branch_id;

        $inv_remarks=$obj->inv_remarks;
        
        $inv_date=$obj->inv_date;
        
        $so_id=$obj->so_id;
        $dc_no = $dbconn->GetSingleReconrd("tbl_dc","dc_refno","dc_id",$obj->dc_id);
        $dc_date = $dbconn->GetSingleReconrd("tbl_dc","dc_date","dc_id",$obj->dc_id);
        $cus_branch_id=$obj->cus_branch_id;
        //$so_no = leadingZeros($dbconn->GetMaxValue('tbl_sales_order', 'so_slno', 'so_finyr', $_REQUEST['so_finyr']) + 1, 3);
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <title><?php echo PAGE_TITLE; ?> - invoice</title>
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
                            <span class="breadcrumb-item active">invoice</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>
            <!-- This Form UI Starts here --->
            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">
                        <form name='thisForm' id="validate" class="form-horizontal" method='post' action="dc_invoice.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                            <input type="hidden" name="dc_id" id="dc_id" value="<?php echo $_REQUEST['dc_id']; ?>">
                            <input type="hidden" name="so_id" id="so_id" value="<?php echo $so_id; ?>">
                            <fieldset>
                                <div class="card">
                                    <div class="card-header bg-pgheader text-white header-elements-inline">
                                        <h6 class="card-title">DC Invoice</h6>
                                        <div class="header-elements">
                                            <div class="list-icons">
                                                <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                                <a class="list-icons-item" href="invoice_list.php" title="Invoice List"><i class="icon-arrow-left52 mr-2"></i></a>
                                                <a class="list-icons-item" data-action="fullscreen"></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <div class="row">
                                                <input type="hidden" name="inv_no" id="inv_no" class="form-control" value="<?php echo $inv_no; ?>" />
                                                <label class="col-lg-1 col-form-label">Customer <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3">
                                                    <select name="supp_id" id="supp_id" data-placeholder="Choose a Customer.." class="form-control select-search">
                                                        <option value=""></option>
                                                        <?php
                                                        $dbconn = new dbhandler();
                                                        echo $dbconn->fnFillComboFromTable_Where("supp_id", "supp_name", "mst_supplier_new", "supp_id", " WHERE supp_status = '1' AND supp_type = 'C'"); ?>
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
                                                        if (isset($_REQUEST['inv_id']) && $_REQUEST['inv_id'] != "") {
                                                            echo $dbconn->fnFillComboFromTable_Where("branch_id", "CONCAT(branch_name,' ~ ',branch_add1,' ~ ',branch_add2)", "mst_customer_branch", "branch_id", " WHERE branch_status = '1' AND supp_id = '" . $supp_id . "'");
                                                        }
                                                        //echo $dbconn->fnFillComboFromTable_Where("branch_id", "CONCAT(branch_name,' ~ ',branch_add1,' ~ ',branch_add2)", "mst_customer_branch", "branch_id", " WHERE branch_status = '1'") 
                                                        ?>
                                                    </select>
                                                    <script>
                                                        document.thisForm.branch_id.value = "<?php echo $cus_branch_id; ?>";
                                                    </script>
                                                </div>


                                                <label class="col-lg-1 col-form-label">Date <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3">
                                                    <input type="date" name="inv_date" id="inv_date" class="form-control" readonly maxlength="75" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $inv_date; ?>" placeholder="Date" />
                                                </div>
                                            </div>

                                            <div class="row pt-2">
                                                <label class="col-lg-1 col-form-label">Mode of Transport <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3">
                                                    <input type="text" name="inv_mode_of_trans" id="inv_mode_of_trans" class="form-control" maxlength="100" placeholder="Mode of Transport" value="<?php echo $obj->inv_mode_of_trans; ?>" />
                                                </div>
                                                <label class="col-lg-1 col-form-label">Vehicle No. <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3">
                                                    <input type="text" name="inv_vechicle_no" id="inv_vechicle_no" class="form-control" maxlength="75" placeholder="Vehicle No." value="<?php echo $obj->inv_vechicle_no; ?>" />
                                                </div>
                                                <label class="col-lg-1 col-form-label">Transport Charges <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3">
                                                    <input type="text" name="inv_trans_charge" id="inv_trans_charge" class="form-control number_only_dot" maxlength="7" placeholder="Transport Charges" value="<?php echo $obj->inv_trans_charge; ?>" />
                                                </div>
                                            </div>
                                            
                                            <div class="row pt-2">
                                                <label class="col-lg-1 col-form-label">DC No. <span class="text-mandatory"> </span></label>
                                                <div class="col-lg-3">
                                                    <input type="text" name="inv_dc_no" id="inv_dc_no" class="form-control" maxlength="100" placeholder="DC No." readonly value="<?php echo $dc_no; ?>" />
                                                </div>
                                                <label class="col-lg-1 col-form-label">DC Date <span class="text-mandatory"> </span></label>
                                                <div class="col-lg-3">
                                                    <input type="date" name="inv_dc_date" id="inv_dc_date" class="form-control" readonly maxlength="75" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $dc_date; ?>" placeholder="Date" />
                                                </div>
                                            </div>
                                        </div>


                                        <legend class="font-weight-semibold"><i class="icon-cart mr-2"></i>Invoice Details</legend>

                                        <!-----individual description table --------->

                                        <div class="form-group row">
                                            <div id="quo_table" class="col-md-12">
                                                <table class="table table-xs table-bordered" style="font-size: small !important;">
                                                    <thead>
                                                        <tr class="bg-teal">
                                                            <th>Description</th>
                                                            <th>Item Code</th>
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
                                                        if (isset($_REQUEST['dc_id'])) {
                                                            $get_quo_dets =  $conn->query("SELECT * FROM tbl_dc_details WHERE dc_dispatch_qty>0 AND dc_id = '" . $_REQUEST['dc_id'] . "'");
                                                            if ($get_quo_dets->rowCount() > 0) {
                                                                $tot_amt=0;
                                                                $tot_netamt=0;
                                                                while ($obj = $get_quo_dets->fetch(PDO::FETCH_OBJ)) 
                                                                {
                                                                    $so_id= $dbconn->GetSingleReconrd("tbl_dc","so_id","dc_id",$_REQUEST['dc_id']);

                                                                    /*$selling_price = $dbconn->GetSingleReconrd("tbl_item_details","item_selling_price","item_id",$obj->dc_item_id);*/
																	
																	$selling_price = $dbconn->GetSingleReconrd("tbl_sales_order_details","so_selling_price","item_id='".$obj->dc_item_id."' AND so_id",$so_id);
																	
                                                                    $discount = $dbconn->GetSingleReconrd("tbl_sales_order_details","so_discount","item_id='".$obj->dc_item_id."' AND so_id",$so_id);
                                                                   
                                                                    $item_tax = $dbconn->GetSingleReconrd("tbl_sales_order_details","so_vat","item_id='".$obj->dc_item_id."' AND so_id",$so_id);
                                                                    
                                                                    $tot_item_value=((float)$obj->dc_dispatch_qty*(float)$selling_price);
                                                                    
                                                                    if($discount>0)
                                                                    {
                                                                        $item_value=((float)$tot_item_value-(((float)$tot_item_value*(float)$discount)/100));
                                                                    }
                                                                    else
                                                                    {
                                                                        $item_value=$tot_item_value;
                                                                    }


                                                                    $item_tax_value=(((float)$item_value*(float)$item_tax)/100);
                                                                    
                                                                    $item_tot_value=((float)$item_value+(float)$item_tax_value);
                                                                    

                                                                    echo '<tr id="' . $obj->dc_item_id . '" class="g">
																	<td>' . $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_id", $obj->dc_item_id) . '</td>
																	<input type="hidden" class="item_desciption" name="item_desciption[]" value="' . $obj->dc_item_id . '" />

																	<td>' . $dbconn->GetSingleReconrd("tbl_item_details", "item_code", "item_id", $obj->dc_item_id) . '</td>
																	<input type="hidden" class="temp_item_id" name="temp_item_id[]" value="' . $obj->dc_item_id . '" />

																	<td class="text-right">' . $obj->dc_dispatch_qty . '</td>
																	<input type="hidden" class="temp_qty" name="temp_qty[]" value="' . $obj->dc_dispatch_qty . '" />

																	<td class="text-right">' . $obj->dc_unit . '</td>
																	<input type="hidden" class="temp_unit" name="temp_unit[]" value="' . $obj->dc_unit . '" />

																	<td class="text-right">' . $selling_price . '</td>
																	<input type="hidden" class="temp_selling_price" name="temp_selling_price[]" value="' . $selling_price . '" />

																	<td class="text-right">' . $discount . '</td>
																	<input type="hidden" class="temp_discount_per" name="temp_discount_per[]" value="' . $discount . '" />
																
																	<td class="text-right">' . number_format($item_value,2) . '</td>
																	<input type="hidden" class="temp_quo_price" name="temp_quo_price[]" value="' . $item_value . '" />
																	<input type="hidden" class="temp_discount_val" name="temp_discount_val[]" value="' .  $obj->quo_discount_amt . '">

																	<td class="text-right">' . $item_tax . '</td>
																	<input type="hidden" class="temp_vat" name="temp_vat[]" value="' . $item_tax . '" />
																	<input type="hidden" class="quo_pack_taxable_value" name="quo_pack_taxable_value[]" value="' . $item_tax_value . '"/>

																	<td class="text-right">' . number_format($item_tot_value,2) . '</td>
																	<input type="hidden" class="temp_net_amt" name="temp_net_amt[]"  id="net_total" value="' . $item_tot_value . '" />
																</tr>';
                                                                    
                                                                    $tot_amt +=$item_value; 
                                                                    $tot_netamt += $item_tot_value;
                                                                }
                                                            }
                                                            $tot_qty = $dbconn->GetSingleReconrd("tbl_dc_details", "SUM(dc_dispatch_qty)", "dc_id", $_REQUEST['dc_id']);
                                                        }

                                                        //Invoice Details
                                                        if (isset($_REQUEST['inv_id'])) {
                                                            $get_inv_dets =  $conn->query("SELECT * FROM tbl_invoice_details WHERE  inv_id = '" . $_REQUEST['inv_id'] . "'");
                                                            if ($get_inv_dets->rowCount() > 0) {
                                                                while ($obj = $get_inv_dets->fetch(PDO::FETCH_OBJ)) {

                                                                    echo '<tr id="' . $obj->item_id . '" class="g' . $obj->group_id . '">
                                                                    <td>' . $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_id", $obj->item_id) . '</td>
                                                                    <input type="hidden" class="item_desciption" name="item_desciption[]" value="' . $obj->item_id . '" />

                                                                    <td>' . $dbconn->GetSingleReconrd("tbl_item_details", "item_code", "item_id", $obj->item_id) . '</td>
                                                                    <input type="hidden" class="temp_item_id" name="temp_item_id[]" value="' . $obj->item_id . '" />

                                                                    <td class="text-right">' . $obj->inv_qty . '</td>
                                                                    <input type="hidden" class="temp_qty" name="temp_qty[]" value="' . $obj->inv_qty . '" />

                                                                    <td class="text-right">' . $obj->inv_unit . '</td>
                                                                    <input type="hidden" class="temp_unit" name="temp_unit[]" value="' . $obj->inv_unit . '" />

                                                                    <td class="text-right">' . $obj->unit_price . '</td>
                                                                    <input type="hidden" class="temp_selling_price" name="temp_selling_price[]" value="' . $obj->unit_price . '" />

                                                                    <td class="text-right">' . $obj->inv_discount . '</td>
                                                                    <input type="hidden" class="temp_discount_per" name="temp_discount_per[]" value="' . $obj->inv_discount . '" />
                                                                
                                                                    <td class="text-right">' . $obj->inv_value . '</td>
                                                                    <input type="hidden" class="temp_quo_price" name="temp_quo_price[]" value="' . $obj->quo_value . '" />
                                                                    <input type="hidden" class="temp_discount_val" name="temp_discount_val[]" value="' .  $obj->inv_discount_amt . '">

                                                                    <td class="text-right">' . $obj->vat . '</td>
                                                                    <input type="hidden" class="temp_vat" name="temp_vat[]" value="' . $obj->vat . '" />
                                                                    <input type="hidden" class="quo_pack_taxable_value" name="quo_pack_taxable_value[]" value="' . $obj->tax_value . '"/>

                                                                    <td class="text-right">' . $obj->net_value . '</td>
                                                                    <input type="hidden" class="temp_net_amt" name="temp_net_amt[]"  id="net_total" value="' . $obj->net_value . '" />
                                                                </tr>';
                                                                }
                                                            }
                                                            $tot_qty = $dbconn->GetSingleReconrd("tbl_invoice_details", "SUM(inv_qty)", "inv_id", $_REQUEST['inv_id']);
                                                            $tot_amt = $dbconn->GetSingleReconrd("tbl_invoice_details", "SUM(inv_value)", "inv_id", $_REQUEST['inv_id']);
                                                            $tot_netamt = $dbconn->GetSingleReconrd("tbl_invoice_details", "SUM(net_value)", "inv_id", $_REQUEST['inv_id']);
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
															<th id="quo_total_amt" class="text-right"><?php echo number_format(round($tot_amt),2); ?></th>
															<th class="text-right"></th>
															<th id="quo_total_netamt" name="quo_total_netamt" class="text-right"><?php echo $tot_netamt; ?></th>
															<input type="hidden" id="txt_quo_total_amt" value="<?php echo $tot_amt; ?>">
															<input type="hidden" id="txt_quo_total_netamt" name="txt_quo_total_netamt" value="<?php echo $tot_netamt; ?>">
														</tr>
													</tfoot>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- <legend class="font-weight-semibold"></legend> -->
                                        <legend class="font-weight-semibold"></legend>
                                        <div class="form-group row">
                                            <label class="col-lg-2 col-form-label "><i class="icon-address-book  mr-2"></i>Other Details</label>
                                            <div class="col-lg-4">
                                                <select name="otdets_group" id="otdets_group" class="select">
                                                    <option value="">Customise</option>
                                                    <?php
                                                    echo $dbconn->fnFillComboFromTable_Where(
                                                        "principal_id",
                                                        "principal_name",
                                                        "mst_principal",
                                                        "principal_id",
                                                        "WHERE principal_status= 1"
                                                    );
                                                    ?>
                                                </select>
                                                <script>
                                                    document.thisForm.otdets_group.value = "<?php echo $otdets_principal_id; ?>";
                                                </script>
                                            </div>
                                        </div>
                                        <legend class="font-weight-semibold"></legend>
                                        <!----------------------------package table---------------------------->
                                        <div class="row pt-1">
                                            <div class="col-md-12">
                                                <fieldset>
                                                    <div class="form-group row">
                                                        <div class="form-group col-md-3">
                                                            <p><b>Description</b></p>
                                                            <div>
                                                                <select name="pack_id" id="pack_id" class="select">
                                                                    <option value="">-- Select --</option>
                                                                    <?php
                                                                    if ($otdets_principal_id > 0) {
                                                                        echo $dbconn->fnFillComboFromTable_Where("quo_id", "quo_pack_decp", "mst_quo_details", "quo_id", " WHERE quo_status = '1' ANd principal_id=" . $otdets_principal_id);
                                                                    } else {
                                                                        echo $dbconn->fnFillComboFromTable_Where("quo_id", "quo_pack_decp", "mst_quo_details", "quo_id", " WHERE quo_status = '1'");
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-md-2">
                                                            <p><b>% / Fixed Amount</b></p>
                                                            <select name="quo_pack_per_fa" id="quo_pack_per_fa" data-placeholder="Choose .." class="select">
                                                                <option value="">Select</option>
                                                                <option value="1">% Percent</option>
                                                                <option value="2">Fixed Amount</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group pl-0 col-md-1">
                                                            <p id='quo_per'><b> % Percent</b></p>
                                                            <p id='quo_val'><b>Fixed Amt</b></p>
                                                            <input type="text" class="form-control" name="quo_pack_per_fa_value" id="quo_pack_per_fa_value" onkeypress="return isNumberKey_With_Dot(event)" maxlength="9" value="" />
                                                        </div>
                                                        <div class="form-group pl-2 col-md-2">
                                                            <p><b>HSN</b></p>
                                                            <select name="quo_pack_gst_id" id="quo_pack_gst_id" data-placeholder="Choose a HSN.." class="select-search">
                                                                <option value="">Select HSN</option>
                                                                <?php
                                                                $dbconn = new dbhandler();
                                                                echo $dbconn->fnFillComboFromTable_Where("hsn_id", "CONCAT(hsn_code,' - ',igst,'%')", "mst_hsn", "hsn_id", " WHERE hsn_status = '1'") ?>
                                                            </select>
                                                        </div>
                                                        <div class="form-group pl-0 col-md-1">
                                                            <p><b>GST</b></p>
                                                            <div class="input-append">
                                                                <input type="text" class="form-control" name="quo_pack_gst_per" id="quo_pack_gst_per" maxlength="7" tabIndex="-1" readonly value="" />
                                                                <input type="hidden" name="quo_pack_taxable_val" id="quo_pack_taxable_val" value="">
                                                            </div>
                                                        </div>
                                                        <div class="form-group pl-1 col-md-2">
                                                            <p><b>GST Amount</b></p>
                                                            <div class="input-append">
                                                                <input type="text" class="form-control" name="quo_pack_gst_amt" id="quo_pack_gst_amt" maxlength="7" tabIndex="-1" readonly value="" />
                                                            </div>
                                                        </div>
                                                        <div class="form-group pl-0" id="item_indv_add_btn">
                                                            <button class="btn btn-success mr-2 mt-4 pt-1" id="add_pack" name="add_pack" type="button"> +</button>
                                                        </div>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>

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
                                                            <th><i class=" icon-cog6 mr-2"></i></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        if (isset($_REQUEST['inv_id'])) {
                                                            $get_quo_pack_dets =  $conn->query("SELECT * FROM tbl_invoice_pack_details WHERE  inv_id = '" . $_REQUEST['inv_id'] . "'");

                                                            if ($get_quo_pack_dets->rowCount() > 0) {
                                                                while ($obj = $get_quo_pack_dets->fetch(PDO::FETCH_OBJ)) {

                                                                    $percent = $obj->inv_pack_percent;
                                                                    if ($percent == 1) {
                                                                        $percent_val =   $obj->inv_pack_text . " %";
                                                                    } else {
                                                                        $percent_val = number_format($obj->inv_pack_text, 0) . " - FA";
                                                                    }
                                                                    echo '<tr id="PK_' . $obj->pack_inv_details_id . '"  class="p' . $obj->inv_id . '">                                                             
                                                                        <td>' . $dbconn->GetSingleReconrd("mst_quo_details", "quo_pack_decp", "quo_id",  $obj->inv_pack_decp) . '
                                                                            <input type="hidden" class="pack_id" name="pack_id[]" value="'  . $obj->inv_pack_decp . '" />
                                                                            <input type="hidden" class="inv_id" name="inv_id[]" value="'  . $obj->inv_id . '" /></td>
                                                                        <td>' . $percent_val . '
                                                                            <input type="hidden" class="quo_pack_per_fa_value" name="quo_pack_per_fa_value[]" value="' . $percent_val . '" />
                                                                            <input type="hidden" class="quo_pack_taxable_val" name="quo_pack_taxable_val[]" value="' . $obj->inv_pack_taxable_val . '" />
                                                                            <input type="hidden" class="quo_pack_gst_amt" name="quo_pack_gst_amt[]" value="' . $obj->inv_pack_value . '" >
                                                                        </td>
                                                                        <td class="text-right disp_tax_val">' . number_format($obj->inv_pack_taxable_val, 2)  . '</td>
                                                                        <td class="text-right">' . $obj->gst_id . '
                                                                            <input type="hidden" class="quo_pack_gst_id" name="quo_pack_gst_id[]" value="' . $obj->gst_id . '" >
                                                                            <input type="hidden" class="quo_pack_per_fa" name="quo_pack_per_fa[]" value="' . $percent . '" ></td>
                                                                        <td class="text-right">' . $obj->inv_pack_vat . '   
                                                                            <input type="hidden" class="quo_pack_gst_per" name="quo_pack_gst_per[]" value="' . $obj->inv_pack_vat . '" />
                                                                            <input type="hidden" class="quo_pack_total" name="quo_pack_total[]" id="quo_pack_total" value="' . $obj->inv_pack_total . '" />
                                                                        </td>
                                                                        <td class="text-right disp_gst_amt">' . number_format($obj->inv_pack_value, 2) . '</td>
                                                                        <td class="text-right disp_pack_total">' . number_format($obj->inv_pack_total, 2) . '</td>
                                                                        <td class="text-center">
                                                                            <a href="javascript:remove_item1(' . $obj->pack_inv_details_id . ');" class="" rel="' . $obj->pack_inv_details_id . '"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a>
                                                                        </td>
                                                                    </tr>';
                                                                }
                                                            }
                                                        }
                                                        $pack_total = $dbconn->GetSingleReconrd("tbl_invoice_pack_details", "SUM(inv_pack_total)", "inv_id", $_REQUEST['inv_id']);
                                                        ?>
                                                    </tbody>
                                                    <tfoot>
                                                        <th colspan="2" class="text-right">Total</th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th id="quo_total_pack_netamt" class="text-right"><?php echo $pack_total; ?></th>
                                                        <th></th>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                        <legend class="font-weight-semibold"></legend>

                                        <div align="right">
                                            <span style="font-size: 15px;">Invoice Value: </span>
                                            <input type="text" readonly style="font-size:15px; border: none; text-align: left; color: blue; font-weight: bold;" name="txt_final_total" id="txt_final_total" value="<?php echo $sales_order_total; ?>" />
                                        </div>
                                        

                                        


                                        <legend class="font-weight-semibold"></legend>

                                        <div class="control-group">
                                            <div class="span12">
                                                <label class="col-lg-2 col-form-label">Remarks if any :</label>
                                                <textarea name="inv_remarks" id="inv_remarks" maxlength="250" class="form-control"><?php echo $inv_remarks; ?></textarea>
                                            </div>
                                        </div><br><br>
                                    </div>

                                    <div class="card-footer text-center">
                                        <?php if((isset($_REQUEST['dc_id']) && $_REQUEST['dc_id']>0)){ ?>
                                        <INPUT class="btn btn-custom mr-2" type="submit" name="SAVE" value="Draft">
                                        <INPUT class="btn btn-light mr-2" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
                                    <?php }else{?>
                                        <input type="hidden" name="txtHid" value="<?php echo $_REQUEST['inv_id']; ?>">
                                        <INPUT class="btn btn-custom mr-2" type="submit" name="UPDATE" value="Update">
                                        <INPUT class="btn btn-success mr-2" type="submit" name="FINALIZE" value="Save Invoice">
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

        /* if (notSelected(document.thisForm.supp_id, "Customer..!")) {
            return false;
        } */
		if($("#supp_id").val()=="" || $("#supp_id").val()==null){
			alert("Please Select the Customer..!");
			return false;
		}
        if (isNull(document.thisForm.inv_date, "Invoice Date..!")) {
            return false;
        }
        if (isNull(document.thisForm.inv_mode_of_trans, "Mode of Transport..!")) {
            return false;
        }
        if (isNull(document.thisForm.inv_vechicle_no, "Vehicle No..!")) {
            return false;
        }
        if (isNull(document.thisForm.inv_trans_charge, "Transport Charges..!")) {
            return false;
        }
        

        
        
      
        if (!wasSubmitted) {
            wasSubmitted = true;
            document.thisForm.submit();
            return true;
        }
        return false;
    }

    $(function() {

        $('#cash_id').change(function() {
            var cash_id = $('#cash_id').val();
            $('#cash_id_no').val(cash_id);
        });

        $('.pay_chq_div').hide();
        $('.pay_refno_div').hide();
        $('.pay_chq_dt').hide();
        $('.pay_cardno_div').hide();
        $('.cred_remark_div').hide();
        $('.cash_denomination').hide();
        $('.pay_id').trigger("change");

        $('#pay_id').change(function() {
            var pay_mode = $('#pay_id').val();
            if (pay_mode == "2") {
                $('.pay_chq_div').show();
                $('.pay_refno_div').hide();
                $('#pay_refno').val('');
                $('.pay_chq_dt').show();
                $('.pay_cardno_div').hide();
                $('#pay_cardno').val('');
                $('.cred_remark_div').hide();
                $('#credit_remarks').val('');
                $('.cash_denomination').hide();

            } else if (pay_mode == "4") {
                $('.pay_refno_div').show();
                $('.pay_chq_dt').show();
                $('.pay_chq_div').hide();
                $('#pay_chq_no').val('');
                $('.pay_cardno_div').hide();
                $('#pay_cardno').val('');
                $('.cred_remark_div').hide();
                $('#credit_remarks').val('');
                $('.cash_denomination').hide();

            } else if (pay_mode == "3") {
                $('.pay_cardno_div').show();
                $('.pay_chq_dt').show();
                $('.pay_chq_div').hide();
                $('#pay_chq_no').val('');
                $('.pay_refno_div').hide();
                $('#pay_refno').val('');
                $('.cred_remark_div').hide();
                $('#credit_remarks').val('');
                $('.cash_denomination').hide();

            } else if (pay_mode == "5") {
                $('.pay_refno_div').show();
                $('.pay_chq_dt').show();
                $('.pay_chq_div').hide();
                $('#pay_chq_no').val('');
                $('.pay_cardno_div').hide();
                $('#pay_cardno').val('');
                $('.cred_remark_div').hide();
                $('#credit_remarks').val('');
                $('.cash_denomination').hide();

            } else if (pay_mode == "1") {
                $('.pay_refno_div').hide();
                $('.pay_chq_dt').hide();
                $('.pay_chq_div').hide();
                $('#pay_chq_no').val('');
                $('.pay_cardno_div').hide();
                $('#pay_cardno').val('');
                $('.cred_remark_div').hide();
                $('#credit_remarks').val('');
                $('.cash_denomination').show();

            } else if (pay_mode == "6") {
                $('.pay_refno_div').show();
                $('.pay_chq_dt').show();
                $('.pay_chq_div').hide();
                $('#pay_chq_no').val('');
                $('.pay_cardno_div').hide();
                $('#pay_cardno').val('');
                $('.cred_remark_div').hide();
                $('#credit_remarks').val('');
                $('.cash_denomination').hide();

            } else if (pay_mode == "7") {
                $('.cred_remark_div').show();
                $('.pay_chq_dt').show();
                $('.pay_chq_div').hide();
                $('#pay_chq_no').val('');
                $('.pay_refno_div').hide();
                $('.pay_cardno_div').hide();
                $('#pay_cardno').val('');
                // $('#credit_remarks').val('');
                $('.cash_denomination').hide();
            } else {
                $('.pay_chq_div').hide();
                $('#pay_chq_no').val('');
                $('.pay_refno_div').hide();
                $('#pay_refno').val('');
                $('.pay_chq_dt').hide();
                $('.pay_cardno_div').hide();
                $('#pay_cardno').val('');
                $('.cred_remark_div').hide();
                $('#credit_remarks').val('');
                $('.cash_denomination').hide();

            }
        }).trigger('change');

        $('#add_pay_items').click(function() {

            if ($('#cash_id').val() == "") {
                alert("Please select the Cash Denomination ...!");
                $('#cash_id').focus();
                return false;
            }

            if ($('#cash_count').val() == "") {
                alert("Please enter the Count ...!");
                $('#cash_count').focus();
                return false;
            }
            var table = document.getElementById("show_table2");
            var cash_id = $("#cash_id").val();
            var cash_count = $("#cash_count").val();

            var arr = [];
            var cash_id = $('#cash_id_no').val();
            $("#show_table2 tr").each(function() {
                arr.push(this.id);
            });
            if (jQuery.inArray(cash_id, arr) != -1) {
                $('#' + cash_id).remove();
            }

            $.ajax({
                type: "POST",
                url: "inc/cis_ajax/jquery_po_details.php",
                data: {
                    "cash_id": cash_id,
                    "cash_count": cash_count,
                    'mode': 'save_pay'
                }
            }).done(function(msg) {
                $("#show_table2 tbody").append(msg);
                $("#cash_id").val('').trigger('change');
                $("#item_code").val('');
                $('#cash_count').val('');
                findReciptTotal();
                // var total_val = 0;
                // $("#show_table2 tr").each(function() {
                //     var temp_total = $(this).closest('tr').find('.temp_total').val();
                //     if (temp_total > 0) {
                //         total_val = parseFloat(total_val) + parseFloat(temp_total);

                //     }
                // });
                // $('#show_table2 tfoot th#lastRow01').html(total_val.toFixed(2));

            });

        });


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


    function remove_item(auto_id) 
    {
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


    //------Package total function--------//

    function findQuotationPackageTotal() {

        var quo_pack_total = 0;
        var quo_total_amt = parseFloat($('#txt_quo_total_amt').val());

        if (isNaN(quo_total_amt)) quo_total_amt = 0;
        $("#package_table tr").each(function() {
            var per_fa = parseFloat($(this).closest('tr').find('.quo_pack_per_fa').val());

            if (per_fa == 1) {
                per_fa_value = parseFloat($(this).closest('tr').find('.quo_pack_per_fa_value').val());
                if (isNaN(per_fa_value)) per_fa_value = 0;

                if (per_fa_value > 0)
                    taxable_value = quo_total_amt * per_fa_value / 100;
                else
                    taxable_value = 0;
                $(this).closest('tr').find('.quo_pack_taxable_val').val(taxable_value);
                $(this).closest('tr').find('.disp_tax_val').html(taxable_value.toFixed(2));

                gst_per = parseFloat($(this).closest('tr').find('.quo_pack_gst_per').val());
                if (isNaN(gst_per)) gst_per = 0;
                if (gst_per > 0)
                    gst_amt = taxable_value * gst_per / 100;
                else
                    gst_amt = 0;
                $(this).closest('tr').find('.disp_gst_amt').html(gst_amt.toFixed(2));
                $(this).closest('tr').find('.quo_pack_gst_amt').val(gst_amt.toFixed(2));

                total = taxable_value + gst_amt;
                $(this).closest('tr').find('.quo_pack_total').val(total);
                $(this).closest('tr').find('.disp_pack_total').html(total.toFixed(2));
                quo_pack_total += total;
            } else {
                inv_quo_pack_total = parseFloat($(this).closest('tr').find('.quo_pack_total').val());
                if (isNaN(inv_quo_pack_total)) inv_quo_pack_total = 0;
                quo_pack_total += inv_quo_pack_total;
            }
        });
        $('#quo_total_pack_netamt').html(quo_pack_total.toFixed(2));
        GrandTotal();
    }

    //------Grand total function--------//

    function GrandTotal() {

        var quo_total_quo_netamt = parseFloat($('#quo_total_netamt').text());
        if (isNaN(quo_total_quo_netamt)) quo_total_quo_netamt = 0;

        var quo_total_pack_netamt = parseFloat($('#quo_total_pack_netamt').text());
        if (isNaN(quo_total_pack_netamt)) quo_total_pack_netamt = 0;

        var grand_total = quo_total_quo_netamt + quo_total_pack_netamt;
        
        $('.final_total').html(Math.round(grand_total).toFixed(2));
        $('#txt_final_total').val(Math.round(grand_total).toFixed(2));

    }

    //---- Individual remove----//

    function remove_item(auto_id) {
        $('#' + auto_id).remove();
        findQuotationItemTotal();
    }

    function remove_item1(auto_id) {
        $('#PK_' + auto_id).remove();
        findQuotationPackageTotal();
    }

    //---- Get value package ---//

    function get_value() {
        var quo_pack_gst_id = $("#quo_pack_gst_id").val();
        var quo_total_amt = $("#quo_total_amt").text();
        var quo_pack_per_fa_value = $("#quo_pack_per_fa_value").val();
        var quo_pack_per_fa = $("#quo_pack_per_fa").val();

        $.ajax({
            type: "POST",
            url: "inc/cis_ajax/jquery_quotation_package_cal.php",
            data: {
                "quo_total_amt": quo_total_amt,
                "quo_pack_per_fa": quo_pack_per_fa,
                "quo_pack_per_fa_value": quo_pack_per_fa_value,
                "quo_pack_gst_id": quo_pack_gst_id
            }
        }).done(function(msg) {
            var data = msg.split('~');
            $("#quo_pack_gst_per").val(data[0]);
            $("#quo_pack_taxable_value").val(data[1]);
            $("#quo_pack_gst_amt").val(data[2]);
            GrandTotal();
        });
    }

    $('#quo_pack_per_fa_value').change(function() {
        get_value();
    });

    $('#quo_pack_gst_id').change(function() {
        get_value();
    });

    $("#quo_val").show();
    $("#quo_per").hide();
    $('#quo_pack_per_fa').change(function() {
        get_value();
        change_heading();
    });

    function change_heading() {

        $("#quo_per").hide();
        $("#quo_val").show();
        var per_val = $("#quo_pack_per_fa").val();
        if (per_val == 1) {
            $("#quo_per").show();
            $("#quo_val").hide();
        } else if (per_val == 2) {
            $("#quo_per").hide();
            $("#quo_val").show();
        }
    }

    //---------------------------------------//

    $("#pack_id").change(function() {
        var desc_id = $(this).val();

        $("#quo_pack_per_fa").val('');
        $("#quo_pack_per_fa_value").val('');
        $("#quo_pack_gst_id").val('');

        $.ajax({
            type: "POST",
            url: "inc/cis_ajax/jquery_quotation_other_dets.php",
            data: {
                "desc_id": desc_id
            }
        }).done(function(msg) {
            // alert(msg);
            var data = msg.split('~');
            $("#quo_pack_per_fa option[value=" + data[0] + "]").attr('selected', 'selected').trigger('change');
            $("#quo_pack_per_fa_value").val(data[2]);
            setTimeout(function() {
                $("#quo_pack_gst_id option[value=" + data[1] + "]").attr('selected', 'selected').trigger('change');
            }, 300);
        });
    });



    //----- Add Package Details -----//

    $('#add_pack').click(function() {

        if (isNull(document.thisForm.pack_id, "Description..!")) {
            return false;
        }
        if (notSelected(document.thisForm.quo_pack_per_fa, "% / Fixed Amount...!")) {
            return false;
        }
        if (isNull(document.thisForm.quo_pack_per_fa_value, "% / Fixed Amount...!")) {
            return false;
        }
        var quo_pack_per_fa_value = document.thisForm.quo_pack_per_fa_value.value;
        if (quo_pack_per_fa_value <= 0) {
            alert("Please add % / Fixed Amount...!");
            return false;
        }
        if (notSelected(document.thisForm.quo_pack_gst_id, "HSN...!")) {
            return false;
        }

        var table = document.getElementById("package_table");
        var rowCount = 1;
        var pack_id = $("#pack_id").val();
        var quo_pack_per_fa = $("#quo_pack_per_fa").val();
        var quo_pack_per_fa_value = $("#quo_pack_per_fa_value").val();
        var quo_pack_gst_id = $("#quo_pack_gst_id").val();
        var quo_pack_gst_per = $("#quo_pack_gst_per").val();
        var quo_pack_gst_amt = $("#quo_pack_gst_amt").val();
        var quo_pack_taxable_value = $("#quo_pack_taxable_value").val();

        var arr = [];
        var is_pack = 0;

        $("#package_table tr").each(function() {
            arr.push(this.id);
        });
        if (jQuery.inArray(pack_id, arr) != -1) {

            var is_pack = 1;
        }

        $.ajax({
            type: "POST",
            url: "inc/cis_ajax/jquery_quotation_package_details.php",
            data: {

                "pack_id": pack_id,
                "quo_pack_per_fa": quo_pack_per_fa,
                "quo_pack_per_fa_value": quo_pack_per_fa_value,
                "quo_pack_gst_id": quo_pack_gst_id,
                "quo_pack_gst_per": quo_pack_gst_per,
                "quo_pack_gst_amt": quo_pack_gst_amt,
                "quo_pack_taxable_value": quo_pack_taxable_value,
                'mode': 'save'
            }
        }).done(function(msg) {

            if (is_pack == 0) {
                $("#package_table tbody").append(msg);
            } else {
                $("#" + pack_id).replaceWith(msg);
            }

            $("#pack_id").val('').trigger('change'); //id
            $("#quo_pack_per_fa").val('').trigger('change');
            $('#quo_pack_per_fa_value').val('').trigger('change');
            $("#quo_pack_taxable_val").val('');
            $('#quo_pack_gst_id').val('').trigger('change');
            $('#quo_pack_gst_per').val('');
            findQuotationPackageTotal();

        });
    });
    GrandTotal();

    $("#otdets_group").change(function(){   
        var principal_id = $(this).val();       

        $.ajax({
        type: "POST",
        url: "inc/cis_ajax/jquery_quotation_select_otdets.php",
        data: {principal_id:principal_id}
        }).done(function( msg ) {
            // alert(msg);
            $('#pack_id option').remove();
            var dataArr = msg.split('#');
            $.each(dataArr, function(i,element){
                if(dataArr[i]!=""){
                    var dataArr2 = dataArr[i].split('~');
                    $('#pack_id').append("<option value='"+dataArr2[0]+"'>"+dataArr2[1]+"</option>");
                }
            });
            $("#s2id_pack_id").select2('val','');
            $("#s2id_quo_pack_per_fa").select2('val','');
            $("#s2id_quo_pack_gst_id").select2('val','');
          //    $("#quo_pack_decp").trigger("liszt:updated");
        });
        
        
    });
    
</script>