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
    $stmt = $conn->prepare("INSERT INTO tbl_invoice (inv_finyr, inv_slno, inv_refno, inv_date, supp_id, cus_branch_id, quo_id, inv_mode_of_trans, inv_vechicle_no, inv_trans_charge,inv_tot_value,inv_bal_value,inv_remarks, pay_id, pay_chq_dt, pay_refno, pay_chq_no, pay_cardno, credit_remarks, invoice_type, modify_by, modify_date_time, branch_id) VALUES (:inv_finyr, :inv_slno, :inv_refno, :inv_date, :supp_id, :cus_branch_id, :quo_id, :inv_mode_of_trans, :inv_vechicle_no, :inv_trans_charge, :inv_tot_value, :inv_bal_value, :inv_remarks, :pay_id, :pay_chq_dt, :pay_refno, :pay_chq_no, :pay_cardno, :credit_remarks, :invoice_type, :modify_by, :modify_date_time, :branch_id)");
    $data = array(
        ':inv_finyr' => $_REQUEST['inv_finyr'],      
        ':inv_slno' => $_REQUEST['inv_slno'],
        ':inv_refno' => $_REQUEST['inv_refno'],
        ':inv_date' => $_REQUEST['inv_date'],
        ':supp_id' => $_REQUEST['supp_id'],
        ':cus_branch_id' => $_REQUEST['cus_branch_id'],
        ':quo_id' => $_REQUEST['quo_id'],
        ':inv_mode_of_trans' => $_REQUEST['inv_mode_of_trans'],
        ':inv_vechicle_no' => $_REQUEST['inv_vechicle_no'],
        ':inv_trans_charge' => $_REQUEST['inv_trans_charge'],
        ':inv_tot_value' => $_REQUEST['inv_tot_value'],
        ':inv_bal_value' => $_REQUEST['inv_tot_value'],
        ':inv_remarks' => $_REQUEST['inv_remarks'],
        ':pay_id' => $_REQUEST['pay_id'],
        ':pay_chq_dt' => $_REQUEST['pay_chq_dt'],
        ':pay_refno' => $_REQUEST['pay_refno'],
        ':pay_chq_no' => $_REQUEST['pay_chq_no'],
        ':pay_cardno' => $_REQUEST['pay_cardno'],
        ':credit_remarks' => $_REQUEST['credit_remarks'],
        ':invoice_type' => 'Q',
        ':modify_by' => $_REQUEST['modify_by'],
        ':modify_date_time' => $_REQUEST['modify_date_time'],
        ':branch_id' => $_SESSION['_user_branch']
    );
    // print_r($data);
    $stmt->execute($data);
    $last_id = $conn->lastInsertId();
    if($last_id>0)
    {
        $conn->query("UPDATE tbl_quotation SET inv_gen_status = 1, quo_inv_id='".$last_id."' WHERE quo_id=" . $_REQUEST['quo_id']);
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
// echo "<pre>";
//     print_r($_POST);
//     echo "<pre>";
//     exit;
    // package charges details...

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

    if($_REQUEST['pay_id']==1)
    {
        $sql = "DELETE FROM tbl_invoice_denomination_details WHERE inv_id = '" . $last_id . "'";
        $result = $conn->prepare($sql);
        $result->execute();
        $cash = null;
        $cash = $conn->prepare("INSERT INTO tbl_invoice_denomination_details (inv_id, cash_id, den_name, den_count, den_total) VALUES (:inv_id, :cash_id, :den_name, :den_count, :den_total)");

        $cash_row_count = (count($_REQUEST['temp_cash_id']));
        if ($cash_row_count > 0) {
            for ($n = 0; $n < $cash_row_count; $n++) {
                
                $cash_data = array(
                    ':inv_id' => $last_id,
                    ':cash_id' => $_REQUEST['temp_cash_id'][$n],
                    ':den_name' => $_REQUEST['temp_cash_name'][$n],
                    ':den_count' => $_REQUEST['temp_cash_count'][$n],
                    ':den_total' => $_REQUEST['temp_total'][$n]
                );
                $cash->execute($cash_data);
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
    $stmt = $conn->prepare("UPDATE tbl_invoice SET inv_date = :inv_date, supp_id = :supp_id, cus_branch_id = :cus_branch_id, inv_mode_of_trans = :inv_mode_of_trans, inv_vechicle_no = :inv_vechicle_no, inv_trans_charge = :inv_trans_charge, inv_tot_value = :inv_tot_value, inv_bal_value = :inv_bal_value, inv_remarks = :inv_remarks, pay_id = :pay_id, pay_chq_dt = :pay_chq_dt, pay_refno = :pay_refno, pay_chq_no = :pay_chq_no, pay_cardno = :pay_cardno, credit_remarks=:credit_remarks, modify_date_time = :modify_date_time, modify_by = :modify_by  WHERE  inv_id = :inv_id");

    $data = array(
        ':inv_id' => $update_id,
        ':inv_date' => $_REQUEST['inv_date'],       
        ':supp_id' => $_REQUEST['supp_id'],
        ':cus_branch_id' => $_REQUEST['cus_branch_id'],
        ':inv_mode_of_trans' => $_REQUEST['inv_mode_of_trans'],
        ':inv_vechicle_no' => $_REQUEST['inv_vechicle_no'],
        ':inv_trans_charge' => $_REQUEST['inv_trans_charge'],
        ':inv_tot_value' => $_REQUEST['inv_tot_value'],
        ':inv_bal_value' => $_REQUEST['inv_tot_value'],
        ':inv_remarks' => $_REQUEST['inv_remarks'],
        ':pay_id' => $_REQUEST['pay_id'],
        ':pay_chq_dt' => $_REQUEST['pay_chq_dt'],
        ':pay_refno' => $_REQUEST['pay_refno'],
        ':pay_chq_no' => $_REQUEST['pay_chq_no'],
        ':pay_cardno' => $_REQUEST['pay_cardno'],
        ':credit_remarks' => $_REQUEST['credit_remarks'],       
        ':modify_date_time' => $_REQUEST['modify_date_time'],
        ':modify_by' => $_REQUEST['modify_by']
    );
    $stmt->execute($data);
    // print_r($data);die();
    // $conn->query("UPDATE tbl_quotation SET so_des_gen = 1 WHERE quo_id=" . $_REQUEST['txtHid']);
    // $conn->query("UPDATE tbl_sales_order SET accounts_status = 1 WHERE quo_id=" . $_REQUEST['txtHid']);

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

    if($_REQUEST['pay_id']==1)
    {
        $sql = "DELETE FROM tbl_invoice_denomination_details WHERE inv_id = '" . $update_id . "'";
        $result = $conn->prepare($sql);
        $result->execute();
        $cash = null;
        $cash = $conn->prepare("INSERT INTO tbl_invoice_denomination_details (inv_id, cash_id, den_name, den_count, den_total) VALUES (:inv_id, :cash_id, :den_name, :den_count, :den_total)");

        $cash_row_count = (count($_REQUEST['temp_cash_id']));
        if ($cash_row_count > 0) {
            for ($n = 0; $n < $cash_row_count; $n++) {
                
                $cash_data = array(
                    ':inv_id' => $update_id,
                    ':cash_id' => $_REQUEST['temp_cash_id'][$n],
                    ':den_name' => $_REQUEST['temp_cash_name'][$n],
                    ':den_count' => $_REQUEST['temp_cash_count'][$n],
                    ':den_total' => $_REQUEST['temp_total'][$n]
                );
                $cash->execute($cash_data);
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
    $stmt = $conn->prepare("UPDATE tbl_invoice SET inv_date = :inv_date, supp_id = :supp_id, cus_branch_id = :cus_branch_id, inv_mode_of_trans = :inv_mode_of_trans, inv_vechicle_no = :inv_vechicle_no, inv_trans_charge = :inv_trans_charge, inv_tot_value = :inv_tot_value, inv_bal_value = :inv_bal_value, inv_remarks = :inv_remarks, pay_id = :pay_id, pay_chq_dt = :pay_chq_dt, pay_refno = :pay_refno, pay_chq_no = :pay_chq_no, pay_cardno = :pay_cardno, credit_remarks=:credit_remarks, modify_date_time = :modify_date_time, modify_by = :modify_by, inv_status = :inv_status  WHERE  inv_id = :inv_id");

    $data = array(
        ':inv_id' => $update_id,
        ':inv_date' => $_REQUEST['inv_date'],       
        ':supp_id' => $_REQUEST['supp_id'],
        ':cus_branch_id' => $_REQUEST['cus_branch_id'],
        ':inv_mode_of_trans' => $_REQUEST['inv_mode_of_trans'],
        ':inv_vechicle_no' => $_REQUEST['inv_vechicle_no'],
        ':inv_trans_charge' => $_REQUEST['inv_trans_charge'],
        ':inv_tot_value' => $_REQUEST['inv_tot_value'],
        ':inv_bal_value' => $_REQUEST['inv_tot_value'],
        ':inv_remarks' => $_REQUEST['inv_remarks'],
        ':pay_id' => $_REQUEST['pay_id'],
        ':pay_chq_dt' => $_REQUEST['pay_chq_dt'],
        ':pay_refno' => $_REQUEST['pay_refno'],
        ':pay_chq_no' => $_REQUEST['pay_chq_no'],
        ':pay_cardno' => $_REQUEST['pay_cardno'],
        ':credit_remarks' => $_REQUEST['credit_remarks'],
        ':modify_date_time' => $_REQUEST['modify_date_time'],
        ':modify_by' => $_REQUEST['modify_by'],
        ':inv_status' => 1
    );
    $stmt->execute($data);
    // print_r($data);die();
    // $conn->query("UPDATE tbl_quotation SET so_des_gen = 1 WHERE quo_id=" . $_REQUEST['txtHid']);
    // $conn->query("UPDATE tbl_sales_order SET accounts_status = 1 WHERE quo_id=" . $_REQUEST['txtHid']);

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

    if($_REQUEST['pay_id']==1)
    {
        $sql = "DELETE FROM tbl_invoice_denomination_details WHERE inv_id = '" . $update_id . "'";
        $result = $conn->prepare($sql);
        $result->execute();
        $cash = null;
        $cash = $conn->prepare("INSERT INTO tbl_invoice_denomination_details (inv_id, cash_id, den_name, den_count, den_total) VALUES (:inv_id, :cash_id, :den_name, :den_count, :den_total)");

        $cash_row_count = (count($_REQUEST['temp_cash_id']));
        if ($cash_row_count > 0) {
            for ($n = 0; $n < $cash_row_count; $n++) {
                
                $cash_data = array(
                    ':inv_id' => $update_id,
                    ':cash_id' => $_REQUEST['temp_cash_id'][$n],
                    ':den_name' => $_REQUEST['temp_cash_name'][$n],
                    ':den_count' => $_REQUEST['temp_cash_count'][$n],
                    ':den_total' => $_REQUEST['temp_total'][$n]
                );
                $cash->execute($cash_data);
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

    for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) {
       
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

$inv_date=date('Y-m-d');
if (isset($_REQUEST['inv_id'])) {
    $get_inv_val = $conn->query("SELECT * FROM tbl_invoice WHERE  inv_id =' " . $_REQUEST['inv_id'] . "'");

    if ($get_inv_val->rowCount() > 0) {
        $obj = $get_inv_val->fetch(PDO::FETCH_OBJ);
        $supp_id = $obj->supp_id;
        $so_date = $obj->inv_date;
        $branch_id = $obj->cus_branch_id;
        $branch_name = $obj->cus_branch_id;
        $sales_order_total = $obj->inv_tot_value;
        $inv_remarks=$obj->inv_remarks;
        $pay_id=$obj->pay_id;
        $inv_date=$obj->inv_date;
        $pay_chq_dt=$obj->pay_chq_dt;
        $pay_chq_no=$obj->pay_chq_no;
        $pay_cardno=$obj->pay_cardno;
        $pay_refno=$obj->pay_refno;
        $credit_remarks=$obj->credit_remarks;

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
                        <form name='thisForm' id="validate" class="form-horizontal" method='post' action="quo_invoice.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                            <input type="hidden" name="quo_id" id="quo_id" value="<?php echo $_REQUEST['quo_id']; ?>">
                            <fieldset>
                                <div class="card">
                                    <div class="card-header bg-pgheader text-white header-elements-inline">
                                        <h6 class="card-title">Quotation Invoice</h6>
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
                                                <input type="hidden" name="inv_no" id="inv_no" class="form-control" value="<?php echo $inv_no; ?>" />
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
                                                        if (isset($_REQUEST['inv_id']) && $_REQUEST['inv_id'] != "") {
                                                            echo $dbconn->fnFillComboFromTable_Where("branch_id", "CONCAT(branch_name,' ~ ',branch_add1,' ~ ',branch_add2)", "mst_customer_branch", "branch_id", " WHERE branch_status = '1' AND supp_id = '" . $supp_id . "'");
                                                        }
                                                        //echo $dbconn->fnFillComboFromTable_Where("branch_id", "CONCAT(branch_name,' ~ ',branch_add1,' ~ ',branch_add2)", "mst_customer_branch", "branch_id", " WHERE branch_status = '1'") 
                                                        ?>
                                                    </select>
                                                    <script>
                                                        document.thisForm.branch_id.value = "<?php echo $get->cus_branch_id; ?>";
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
																	
																	$field_name = $dbconn->GetSingleReconrd("mst_branch","branch_stock_field","branch_id",$_SESSION['_user_branch']);
                                                                    $curr_stock = $dbconn->GetSingleReconrd("tbl_item_stock", "$field_name", "item_id", $obj->item_id);

                                                                    $item_type = $dbconn->GetSingleReconrd("tbl_item_details","item_type","item_status = '1' AND item_id",$obj->item_id);

																	if($curr_stock <= 0 && $item_type !=8){
																	echo '<tr id="' . $obj->item_id . '" style="color:red;" class="g' . $obj->group_id . '">';
																	}else{
																	echo '<tr id="' . $obj->item_id . '" >';
																	 }

                                                                    
																	echo '<td>' . $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_id", $obj->item_id) . '</td>
																	<input type="hidden" class="item_desciption" name="item_desciption[]" value="' . $obj->item_id . '" />

																	<td>' . $dbconn->GetSingleReconrd("tbl_item_details", "item_code", "item_id", $obj->item_id) . '</td>
																	<input type="hidden" class="temp_item_id" name="temp_item_id[]" value="' . $obj->item_id . '" />
																	<td class="text-right">' . $curr_stock . '</td>
																	<input type="hidden" class="temp_item_qty" name="temp_item_qty[]" value="' . $curr_stock. '" />

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

                                                        //Invoice Details
                                                        if (isset($_REQUEST['inv_id'])) {
                                                            $get_inv_dets =  $conn->query("SELECT * FROM tbl_invoice_details WHERE  inv_id = '" . $_REQUEST['inv_id'] . "'");
                                                            if ($get_inv_dets->rowCount() > 0) {
                                                                while ($obj = $get_inv_dets->fetch(PDO::FETCH_OBJ)) {

                                                                    $field_name = $dbconn->GetSingleReconrd("mst_branch","branch_stock_field","branch_id",$_SESSION['_user_branch']);
                                                                    $curr_stock = $dbconn->GetSingleReconrd("tbl_item_stock", "$field_name", "item_id", $obj->item_id);
                                                                    echo '<tr id="' . $obj->item_id . '" class="g' . $obj->group_id . '">
                                                                    <td>' . $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_id", $obj->item_id) . '</td>
                                                                    <input type="hidden" class="item_desciption" name="item_desciption[]" value="' . $obj->item_id . '" />

                                                                    <td>' . $dbconn->GetSingleReconrd("tbl_item_details", "item_code", "item_id", $obj->item_id) . '</td>
                                                                    <input type="hidden" class="temp_item_id" name="temp_item_id[]" value="' . $obj->item_id . '" />
                                                                    <td class="text-left">' . $curr_stock . '</td>
																	<input type="hidden" class="temp_item_qty" name="temp_item_qty[]" value="' . $curr_stock. '" />
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

                                                        //Invoice Pack Details
                                                        if (isset($_REQUEST['inv_id'])) {
                                                            $get_inv_pack_dets =  $conn->query("SELECT * FROM tbl_invoice_pack_details WHERE  inv_id = '" . $_REQUEST['inv_id'] . "'");

                                                            if ($get_inv_pack_dets->rowCount() > 0) {
                                                                while ($obj = $get_inv_pack_dets->fetch(PDO::FETCH_OBJ)) {

                                                                    $percent = $obj->inv_pack_percent;
                                                                    if ($percent == 1) {
                                                                        $percent_val =   $obj->inv_pack_text . " %";
                                                                    } else {
                                                                        $percent_val = number_format($obj->inv_pack_text, 0) . " - FA";
                                                                    }
                                                                    echo '<tr id="PK_' . $obj->pack_inv_details_id . '"  class="p' . $obj->inv_id . '">                                                             

                                                                        <td>' . $dbconn->GetSingleReconrd("mst_quo_details", "quo_pack_decp", "quo_id",  $obj->inv_pack_decp) . '
                                                                        <input type="hidden" class="pack_id" name="pack_id[]" value="'  . $obj->inv_pack_decp . '" />
                                                                        
                                                                        </td>
                                                                        
                                                                        <td>' . $percent_val . '
                                                                        <input type="hidden" class="quo_pack_per_fa_value" name="quo_pack_per_fa_value[]" value="' . $percent_val . '" />
                                                                        <input type="hidden" class="quo_pack_taxable_val" name="quo_pack_taxable_val[]" value="' . $obj->inv_pack_taxable_val . '" />
                                                                        <input type="hidden" class="quo_pack_gst_amt" name="quo_pack_gst_amt[]" value="' . $obj->inv_pack_value . '" >
                                                                        </td>

                                                                        <td class="text-right disp_tax_val">' . number_format($obj->inv_pack_taxable_val, 2)  . '
                                                                        
                                                                        </td>

                                                                        <td class="text-right">' . $obj->gst_id . '
                                                                        <input type="hidden" class="quo_pack_gst_id" name="quo_pack_gst_id[]" value="' . $obj->gst_id . '" >
                                                                        <input type="hidden" class="quo_pack_per_fa" name="quo_pack_per_fa[]" value="' . $percent . '" >
                                                                        </td>

                                                                        <td class="text-right">' . $obj->inv_pack_vat . '   
                                                                        <input type="hidden" class="quo_pack_gst_per" name="quo_pack_gst_per[]" value="' . $obj->inv_pack_vat . '" />
                                                                        <input type="hidden" class="quo_pack_total" name="quo_pack_total[]" id="quo_pack_total" value="' . $obj->inv_pack_total . '" />
                                                                        </td>

                                                                        <td class="text-right disp_gst_amt">' . number_format($obj->inv_pack_value, 2) . '
                                                                        
                                                                        </td>

                                                                        <td class="text-right disp_pack_total">' . number_format($obj->inv_pack_total, 2) . '
                                                                        
                                                                        </td>

                                                                        

                                                                    </tr>';
                                                                }
                                                            }
                                                            $pack_total = $dbconn->GetSingleReconrd("tbl_invoice_pack_details", "SUM(inv_pack_total)", "inv_id", $_REQUEST['inv_id']);
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
                                            <span style="font-size: 15px;">Invoice Value: </span>
                                            <input type="text" readonly style="font-size:15px; border: none; text-align: left; color: blue; font-weight: bold;" name="inv_tot_value" id="inv_tot_value" value="<?php echo $sales_order_total; ?>" />
                                        </div>
                                        <!------------------------------cash------------------------------------->
                                        <div class="form-group pt-2">
                                            <legend class="font-weight-semibold"><i class="icon-cash3 mr-2"></i>Payment Details</legend>
                                            <div class="row">
                                                <label class="col-lg-1 col-form-label">Pay Mode <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3">
                                                    <select name="pay_id" id="pay_id" data-placeholder="Choose a Pay mode.." class="form-control select-search">
                                                        <option value="">-- Select Denomination --</option>
                                                        <?php
                                                        echo $dbconn->fnFillComboFromTable_Where("pay_id", "pay_name", "mst_pay_method", "pay_id", " WHERE pay_status = '1'") ?>
                                                    </select>
                                                    <script>
                                                        document.thisForm.pay_id.value = "<?php echo $pay_id; ?>";
                                                    </script>
                                                </div>
                                                <label class="col-lg-1 col-form-label pay_chq_dt">Date <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3 pay_chq_dt">
                                                    <input type="date" name="pay_chq_dt" id="pay_chq_dt" class="form-control" value="<?php echo $pay_chq_dt; ?>" placeholder="Date" />
                                                </div>
                                                <label class="col-lg-1 col-form-label pay_refno_div">Ref No <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3 pay_refno_div">
                                                    <input type="text" name="pay_refno" id="pay_refno" class="form-control " value="<?php echo $pay_refno; ?>" maxlength="50" placeholder="Ref No" />
                                                </div>
                                                <label class="col-lg-1 col-form-label pay_chq_div">Cheque No. <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3 pay_chq_div">
                                                    <input type="text" name="pay_chq_no" id="pay_chq_no" class="form-control" maxlength="50" placeholder="Cheque No." value="<?php echo $pay_chq_no; ?>" />
                                                </div>
                                                <label class="col-lg-1 col-form-label pay_cardno_div">Card No <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3 pay_cardno_div">
                                                    <input type="text" name="pay_cardno" id="pay_cardno" value="<?php echo $pay_cardno; ?>" class="form-control " maxlength="20" placeholder="Card No" />
                                                </div>
                                                <label class="col-lg-1 col-form-label cred_remark_div">Credit Remarks <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3 cred_remark_div">
                                                    <input type="text" name="credit_remarks" id="credit_remarks" class="form-control " maxlength="20" value="<?php echo $credit_remarks; ?>" placeholder="Credit Remarks" />
                                                </div>
                                            </div>
                                        </div>

                                        <!---------------------------------cash demo---------------------------------->

                                        <div class="row pt-3 cash_denomination">
                                            <div class="col-md-12">
                                                <fieldset>
                                                    <legend class="font-weight-semibold"><i class="icon-cash mr-2"></i>Cash Denomination</legend>
                                                    <div class="form-group row">
                                                        <div class="form-group col-md-2">
                                                            <label>Cash Denomination <span class="text-mandatory">*</span></label>
                                                            <select data-placeholder="Choose a Denomination.." name="cash_id" id="cash_id" class="form-control select-search">
                                                                <option value="">-- Select Denomination --</option>
                                                                <?php
                                                                echo $dbconn->fnFillComboFromTable_Where("cash_id", "cash_name", "mst_cash_details", "cash_id", " WHERE cash_status = '1'") ?>
                                                            </select>
                                                            <input type="hidden" name="cash_id_no" id="cash_id_no">
                                                        </div>
                                                        <div class="form-group pl-0 col-md-2">
                                                            <label>Cash Count <span class="text-mandatory"> * </span></label>
                                                            <input type="text" name="cash_count" id="cash_count" class="form-control" maxlength="250">
                                                        </div>
                                                        <div class="form-group pl-0" id="item_indv_add_btn">
                                                            <button class="btn btn-success mr-2 mt-4 pt-1" id="add_pay_items" name="add_pay_items" type="button"> +</button>
                                                        </div>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>

                                        <div class="row cash_denomination">
                                            <div id="show_table2" class="col-md-6">
                                                <table class="table table-xs table-bordered table-dets-responsive">
                                                    <thead>
                                                        <tr>
                                                            <th width="10%">Cash</th>
                                                            <th width="20%">Count</th>
                                                            <th width="20%">Total</th>
                                                            <th width="2%"><i class=" icon-cog6 mr-2"></i></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if ($_REQUEST['inv_id'] != '') {

                                                            $dets_sql = "SELECT * FROM tbl_invoice_denomination_details WHERE inv_id = " . $_REQUEST['inv_id'];
                                                            $result_dets = $conn->query($dets_sql);
                                                            $rowCnt = $result_dets->rowCount();
                                                            if ($result_dets->rowCount() > 0) {
                                                                $sno = 1;
                                                                $tot_total = 0;
                                                                while ($itm = $result_dets->fetch()) {

                                                                    echo '
                                                                    <tr id="tbl' . $itm->cash_id . '" >
                                                                        <td>' . $itm->den_name . '
                                                                            <input type="hidden" class="temp_cash_name" name="temp_cash_name[]" value="' . $itm->den_name . '" />
                                                                            <input type="hidden" class="temp_cash_id" name="temp_cash_id[]" value="' . $itm->cash_id . '" />
                                                                        </td>
                                                                        <td class="text-right">' . round($itm->den_count) . '
                                                                            <input type="hidden" class="temp_cash_count" name="temp_cash_count[]" value="' . $itm->den_count . '" />
                                                                        </td>
                                                                        <td class="text-right">' . number_format(($itm->den_total), 2) . '
                                                                            <input type="hidden" class="temp_total" name="temp_total[]" value="' . $itm->den_total . '" />
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <a href="javascript:remove_item(' . $itm->cash_id . ');" class="" rel="' . $itm->cash_id . '"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a>
                                                                        </td>
                                                                    </tr>';
                                                                    $sno++;
                                                                    $tot_total += $itm->den_total;
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <th colspan="2" class="text-right">Total : </th>
                                                            <th class="text-right" id="lastRow01"><?php echo number_format($tot_total, 2); ?></th>
                                                            <th class="text-right"></th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
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
                                        
                                        <?php if((isset($_REQUEST['quo_id']) && $_REQUEST['quo_id']>0)){ ?>
                                            <?php if($curr_stock <= 0 && $item_type !=8) { ?>
                                                <INPUT class="btn btn-custom mr-2" type="submit" name="SAVE" value="Draft" disabled>
                                                <INPUT class="btn btn-light mr-2" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);" >
                                            <?php } else{ ?> 

                                                <INPUT class="btn btn-custom mr-2" type="submit" name="SAVE" value="Draft" >
                                                <INPUT class="btn btn-light mr-2" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);" >

                                            <?php } ?>

                                    <?php }else{?>
                                        <input type="hidden" name="txtHid" value="<?php echo $_REQUEST['inv_id']; ?>" >
                                        <INPUT class="btn btn-custom mr-2" type="submit" name="UPDATE" value="Update" >
                                        <INPUT class="btn btn-success mr-2" type="submit" name="FINALIZE" value="Save Invoice">
                                        <INPUT class="btn btn-light mr-2" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);" >
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
        

        if (notSelected(document.thisForm.pay_id, "Payment Mode..!")) {
            return false;
        }

        if (document.thisForm.pay_id.value == "2") {
            if (isNull(document.thisForm.pay_chq_dt, "Cheque Date. ..!")) {
                return false;
            }
            if (isNull(document.thisForm.pay_chq_no, "Cheque No. ..!")) {
                return false;
            }
        }
        if (document.thisForm.pay_id.value == "3") {
            if (isNull(document.thisForm.pay_chq_dt, "Date. ..!")) {
                return false;
            }
            if (isNull(document.thisForm.pay_cardno, "Card No. ..!")) {
                return false;
            }
        }
        if (document.thisForm.pay_id.value == "4") {
            if (isNull(document.thisForm.pay_chq_dt, "Date. ..!")) {
                return false;
            }
            if (isNull(document.thisForm.pay_refno, "Reference No. ..!")) {
                return false;
            }
        }
        if (document.thisForm.pay_id.value == "5") {
            if (isNull(document.thisForm.pay_chq_dt, "Date. ..!")) {
                return false;
            }
            if (isNull(document.thisForm.pay_refno, "Reference No. ..!")) {
                return false;
            }
        }
        if (document.thisForm.pay_id.value == "6") {
            if (isNull(document.thisForm.pay_chq_dt, "Date. ..!")) {
                return false;
            }
            if (isNull(document.thisForm.pay_refno, "Reference No. ..!")) {
                return false;
            }
        }
        if (document.thisForm.pay_id.value == "7") {
            if (isNull(document.thisForm.pay_chq_dt, "Date. ..!")) {
                return false;
            }
            if (isNull(document.thisForm.credit_remarks, "Credit Remarks. ..!")) {
                return false;
            }
        }

        // var rowCount = $('#show_table tr').length;
        // if (rowCount <= 2) {
        //     alert("Please enter atleast one item");
        //     return false;
        // }

        // net value cannot be 0
        // var total_val5 = 0;
        // $("#show_table tr").each(function() {
        //     var temp_net_amt = $(this).closest('tr').find('.temp_net_amt').val();
        //     alert(temp_net_amt);
        //     if (temp_net_amt > 0) {
        //         total_val5 = parseFloat(total_val5) + parseFloat(temp_net_amt);
        //         alert(total_val5);
        //     }
        // });
        // if (total_val5 <= 0) {
        //     alert("Kindly check net amount");
        //     return false;
        // }
        //end


        //cah amount and net amount should be equal
        var total_val = 0;
        $("#show_table2 tr").each(function() {
            var temp_total = $(this).closest('tr').find('.temp_total').val();
            if (temp_total > 0) {
                total_val = parseFloat(total_val) + parseFloat(temp_total);
                //alert(total_val)
            }
        });
        var total_val5 = parseFloat($('#inv_tot_value').val());
        var pay_mode = $('#pay_id').val();
        if (pay_mode == 1) {
            if (total_val < total_val5) {
                alert("Please check the cash amount and net amount");
                return false;
            }
        }
        //end
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
    
</script>