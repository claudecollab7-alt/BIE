<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
// require_once ("inc/common/dbrowiterator.php");	

isAdmin();


//ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);

$conn = new dbconnect();
$dbconn = new dbhandler();
$inv_no = '001';

$inv_date = date("Y-m-d");


if (isset($_POST['Draft'])) {
    try {
        $_REQUEST['inv_date'] = date("Y-m-d", strtotime($_REQUEST['inv_date']));
        $_REQUEST['branch'] = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id='" . $_SESSION['_user_branch'] . "' AND branch_status", 1);
        $_REQUEST['inv_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);

        $_REQUEST['inv_slno'] = $dbconn->GetMaxValue('tbl_invoice', 'inv_slno', 'branch_id="' . $_SESSION['_user_branch'] . '" AND inv_finyr="' . $_REQUEST['inv_finyr'] . '" AND 1', 1) + 1;


        $_REQUEST['inv_refno'] = 'INV/' . leadingZeros($_REQUEST['inv_slno'], 4) . '/BIE/' . $_REQUEST['branch'] . '/' . $_REQUEST['inv_finyr'];


        // $_REQUEST['payment_code'] = 'P'.leadingZeros($_REQUEST['payment_slno'],4);
        if (isset($_REQUEST['branch_id'])) {
            $_REQUEST['branch_id'] = $_REQUEST['branch_id'];
        } else {
            $_REQUEST['branch_id'] = 0;
        }
        $_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
        $_REQUEST['modify_by'] = $_SESSION['_user_id'];

        $stmt = null;
        $stmt = $conn->prepare("INSERT INTO  tbl_invoice (inv_finyr, inv_slno, inv_refno, inv_date, supp_id, inv_remarks, cus_branch_id, inv_mode_of_trans, modify_date_time, modify_by, inv_vechicle_no, inv_trans_charge, inv_tot_value, inv_bal_value, inv_dc_no, inv_dc_date, pay_id, pay_chq_dt, pay_refno, pay_chq_no, pay_cardno, credit_remarks, invoice_type, branch_id) VALUES (:inv_finyr, :inv_slno, :inv_refno, :inv_date, :supp_id, :inv_remarks, :cus_branch_id, :inv_mode_of_trans, :modify_date_time, :modify_by, :inv_vechicle_no, :inv_trans_charge, :inv_tot_value, :inv_bal_value, :inv_dc_no, :inv_dc_date, :pay_id, :pay_chq_dt, :pay_refno, :pay_chq_no, :pay_cardno, :credit_remarks, :invoice_type, :branch_id)");
        $data = array(
            ':inv_finyr' => $_REQUEST['inv_finyr'],
            ':inv_slno' => $_REQUEST['inv_slno'],
            ':inv_refno' => $_REQUEST['inv_refno'],
            ':inv_date' => $_REQUEST['inv_date'],
            ':supp_id' => $_REQUEST['supp_id'],
            ':inv_remarks' => $_REQUEST['inv_remarks'],
            ':cus_branch_id' => $_REQUEST['branch_id'],
            ':inv_mode_of_trans' => $_REQUEST['inv_mode_of_trans'],
            ':modify_date_time' => $_REQUEST['modify_date_time'],
            ':modify_by' => $_REQUEST['modify_by'],
            ':inv_vechicle_no' => $_REQUEST['inv_vechicle_no'],
            ':inv_trans_charge' => $_REQUEST['inv_trans_charge'],
            ':inv_tot_value' => $_REQUEST['inv_tot_value'],
            ':inv_bal_value' => $_REQUEST['inv_tot_value'],
            ':inv_dc_no' => strtoupper($_REQUEST['inv_dc_no']),
            ':inv_dc_date' => $_REQUEST['inv_dc_date'],
            ':pay_id' => $_REQUEST['pay_id'],
            ':pay_chq_dt' => $_REQUEST['pay_chq_dt'],
            ':pay_refno' => $_REQUEST['pay_refno'],
            ':pay_chq_no' => $_REQUEST['pay_chq_no'],
            ':pay_cardno' => $_REQUEST['pay_cardno'],
            ':credit_remarks' => $_REQUEST['cred_remark'],
            ':invoice_type' => 'I',
            ':branch_id' => $_SESSION['_user_branch']

        );
        //echo"<pre>";print_r($data);exit;
        $stmt->execute($data);

        $last_id = $conn->lastInsertId();
        /* ------------ SAVE tbl_details  -----------*/
        $delete_details =  "DELETE FROM  tbl_invoice_details WHERE inv_id = '" . $last_id . "'";
        $result = $conn->prepare($delete_details);
        $result->execute();


        /* details */
        if (isset($_REQUEST['temp_item_id'])) {
            for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) {

                //echo count($_REQUEST['temp_item_id']);
                $item_value = $item_taxval = $item_total = 0;
                $stmt1 = null;
                $stmt1 = $conn->prepare("INSERT INTO  tbl_invoice_details (inv_id, item_id, inv_qty, inv_unit, unit_price, inv_discount, inv_discount_amt, vat, cgst_per, cgst_val, sgst_per, sgst_val, igst_per, 
				igst_val, inv_value, tax_value, net_value) 
				VALUES (:inv_id, :item_id, :inv_qty, :inv_unit, :unit_price, :inv_discount, :inv_discount_amt, :vat, :cgst_per, :cgst_val, :sgst_per, :sgst_val, :igst_per, :igst_val, 
				:inv_value, :tax_value, :net_value)");

                $data = array(
                    ':inv_id' => $last_id,
                    ':item_id' => $_REQUEST['temp_item_id'][$x],
                    ':inv_qty' => $_REQUEST['temp_qty'][$x],
                    ':inv_unit' => $_REQUEST['temp_unit'][$x],
                    ':unit_price' => $_REQUEST['temp_unit_price'][$x],
                    ':inv_discount' => $_REQUEST['temp_discount_per'][$x],
                    ':inv_discount_amt' => $_REQUEST['temp_discount_val'][$x],
                    ':vat' => $_REQUEST['temp_vat'][$x],
                    ':cgst_per' => $_REQUEST['temp_cgst'][$x],
                    ':cgst_val' => $_REQUEST['temp_cgst_val'][$x],
                    ':sgst_per' => $_REQUEST['temp_sgst'][$x],
                    ':sgst_val' => $_REQUEST['temp_sgst_val'][$x],
                    ':igst_per' => $_REQUEST['temp_vat'][$x],
                    ':igst_val' => $_REQUEST['temp_vat_val'][$x],
                    ':inv_value' => $_REQUEST['temp_inv_price'][$x],
                    ':tax_value' => $_REQUEST['temp_vat_val'][$x],
                    ':net_value' => $_REQUEST['temp_net_amt'][$x]
                );

                //print_r($data);
                $stmt1->execute($data);
            }
        }

        /* details */
        $delete_details =  "DELETE FROM tbl_invoice_denomination_details WHERE inv_id = '" . $update_id . "'";
        $result = $conn->prepare($delete_details);
        $result->execute();
        /* details */
        if (isset($_REQUEST['temp_cash_id'])) {
            for ($x = 0; $x < count($_REQUEST['temp_cash_id']); $x++) {

                //echo count($_REQUEST['temp_cash_id']);
                $stmt1 = null;
                $stmt1 = $conn->prepare("INSERT INTO tbl_invoice_denomination_details (inv_id, cash_id, den_name, den_count, den_total) 
				VALUES (:inv_id, :cash_id, :den_name, :den_count, :den_total)");

                $data = array(
                    ':inv_id' => $last_id,
                    ':cash_id' => $_REQUEST['temp_cash_id'][$x],
                    ':den_name' => $_REQUEST['temp_cash_name'][$x],
                    ':den_count' => $_REQUEST['temp_cash_count'][$x],
                    ':den_total' => $_REQUEST['temp_total'][$x]
                );

                //print_r($data);
                $stmt1->execute($data);
            }
        }

        /* details */
    } catch (Exception $e) {
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
    }



    $_SESSION['_msg'] = "Invoice succesfully Saved..!";
    header("location:invoice_list.php");
    die();
}


if (isset($_POST['UPDATE'])) {
    $update_id = $_REQUEST['txtHid'];
    //echo $update_id;exit;
    echo "<pre>";
    print_r($_POST);
    echo "<pre>";
    // exit;
    try {

        $_REQUEST['inv_date'] = date("Y-m-d", strtotime($_REQUEST['inv_date']));
        $_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
        $_REQUEST['modify_by'] = $_SESSION['_user_id'];
        if (isset($_REQUEST['branch_id'])) {
            $_REQUEST['branch_id'] = $_REQUEST['branch_id'];
        } else {
            $_REQUEST['branch_id'] = 0;
        }
        $stmt = null;
        $stmt = $conn->prepare("UPDATE tbl_invoice SET inv_date = :inv_date, supp_id = :supp_id, 
		 modify_date_time = :modify_date_time, modify_by = :modify_by, inv_remarks = :inv_remarks, cus_branch_id = :cus_branch_id, inv_mode_of_trans = :inv_mode_of_trans, inv_vechicle_no = :inv_vechicle_no,  
         inv_trans_charge = :inv_trans_charge, inv_tot_value=:inv_tot_value, inv_bal_value=:inv_bal_value, inv_dc_no = :inv_dc_no, inv_dc_date = :inv_dc_date, pay_id = :pay_id, pay_chq_dt = :pay_chq_dt, pay_refno = :pay_refno, pay_chq_no = :pay_chq_no, pay_cardno = :pay_cardno, credit_remarks = :credit_remarks, invoice_type = :invoice_type	
         WHERE inv_id = :inv_id");
        $data = array(
            ':inv_id' => $update_id,
            ':inv_date' => $_REQUEST['inv_date'],
            ':supp_id' => $_REQUEST['supp_id'],
            ':inv_remarks' => $_REQUEST['inv_remarks'],
            ':cus_branch_id' => $_REQUEST['branch_id'],
            ':inv_mode_of_trans' => $_REQUEST['inv_mode_of_trans'],
            ':modify_date_time' => $_REQUEST['modify_date_time'],
            ':modify_by' => $_REQUEST['modify_by'],
            ':inv_vechicle_no' => $_REQUEST['inv_vechicle_no'],
            ':inv_trans_charge' => $_REQUEST['inv_trans_charge'],
            ':inv_tot_value' => $_REQUEST['inv_tot_value'],
            ':inv_bal_value' => $_REQUEST['inv_tot_value'],
            ':inv_dc_no' => strtoupper($_REQUEST['inv_dc_no']),
            ':inv_dc_date' => $_REQUEST['inv_dc_date'],
            ':pay_id' => $_REQUEST['pay_id'],
            ':pay_chq_dt' => $_REQUEST['pay_chq_dt'],
            ':pay_refno' => $_REQUEST['pay_refno'],
            ':pay_chq_no' => $_REQUEST['pay_chq_no'],
            ':pay_cardno' => $_REQUEST['pay_cardno'],
            ':credit_remarks' => $_REQUEST['cred_remark'],
            ':invoice_type' => 'I'
        );
        //echo "<pre>";print_r($data);exit;

        $stmt->execute($data);

        /* ------------ SAVE tbl_details  -----------*/
        $delete_details =  "DELETE FROM tbl_invoice_details WHERE inv_id = '" . $update_id . "'";
        $result = $conn->prepare($delete_details);
        $result->execute();


        /* details */
        if (isset($_REQUEST['temp_item_id'])) {
            for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) {

                //echo count($_REQUEST['temp_item_id']);
                $item_value = $item_taxval = $item_total = 0;
                $stmt1 = null;
                $stmt1 = $conn->prepare("INSERT INTO tbl_invoice_details (inv_id, item_id, inv_qty, inv_unit, unit_price, inv_discount, inv_discount_amt, vat, cgst_per, cgst_val, sgst_per, sgst_val, igst_per, 
                 igst_val, inv_value, tax_value, net_value) 
                 VALUES (:inv_id, :item_id, :inv_qty, :inv_unit, :unit_price, :inv_discount, :inv_discount_amt, :vat, :cgst_per, :cgst_val, :sgst_per, :sgst_val, :igst_per, :igst_val, 
                 :inv_value, :tax_value, :net_value)");

                $data = array(
                    ':inv_id' => $update_id,
                    ':item_id' => $_REQUEST['temp_item_id'][$x],
                    ':inv_qty' => $_REQUEST['temp_qty'][$x],
                    ':inv_unit' => $_REQUEST['temp_unit'][$x],
                    ':unit_price' => $_REQUEST['temp_unit_price'][$x],
                    ':inv_discount' => $_REQUEST['temp_discount_per'][$x],
                    ':inv_discount_amt' => $_REQUEST['temp_discount_val'][$x],
                    ':vat' => $_REQUEST['temp_vat'][$x],
                    ':cgst_per' => $_REQUEST['temp_cgst'][$x],
                    ':cgst_val' => $_REQUEST['temp_cgst_val'][$x],
                    ':sgst_per' => $_REQUEST['temp_sgst'][$x],
                    ':sgst_val' => $_REQUEST['temp_sgst_val'][$x],
                    ':igst_per' => $_REQUEST['temp_vat'][$x],
                    ':igst_val' => $_REQUEST['temp_vat_val'][$x],
                    ':inv_value' => $_REQUEST['temp_inv_price'][$x],
                    ':tax_value' => $_REQUEST['temp_vat_val'][$x],
                    ':net_value' => $_REQUEST['temp_net_amt'][$x]
                );

                //print_r($data);
                $stmt1->execute($data);
            }
        }

        /* details */
        $delete_details =  "DELETE FROM  tbl_invoice_denomination_details WHERE inv_id = '" . $update_id . "'";
        $result = $conn->prepare($delete_details);
        $result->execute();

        /* details */
        if (isset($_REQUEST['temp_cash_id'])) {
            for ($x = 0; $x < count($_REQUEST['temp_cash_id']); $x++) {

                //echo count($_REQUEST['temp_cash_id']);
                $stmt1 = null;
                $stmt1 = $conn->prepare("INSERT INTO tbl_invoice_denomination_details (inv_id, cash_id, den_name, den_count, den_total) 
                 VALUES (:inv_id, :cash_id, :den_name, :den_count, :den_total)");

                $data = array(
                    ':inv_id' => $update_id,
                    ':cash_id' => $_REQUEST['temp_cash_id'][$x],
                    ':den_name' => $_REQUEST['temp_cash_name'][$x],
                    ':den_count' => $_REQUEST['temp_cash_count'][$x],
                    ':den_total' => $_REQUEST['temp_total'][$x]
                );

                //print_r($data);
                $stmt1->execute($data);
            }
        }

        /* details */
    } catch (Exception $e) {
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
    }
    $_SESSION['_msg'] = "Direct Invoice succesfully Updated..!";
    header("location:invoice_list.php");
    die();
}



if (isset($_POST['FINALIZE'])) {
    $update_id = $_REQUEST['txtHid'];
    try {

        $_REQUEST['inv_date'] = date("Y-m-d", strtotime($_REQUEST['inv_date']));
        $_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
        $_REQUEST['modify_by'] = $_SESSION['_user_id'];
        if (isset($_REQUEST['branch_id'])) {
            $_REQUEST['branch_id'] = $_REQUEST['branch_id'];
        } else {
            $_REQUEST['branch_id'] = 0;
        }
        $stmt = null;
        $stmt = $conn->prepare("UPDATE tbl_invoice SET inv_date = :inv_date, supp_id = :supp_id, 
		 modify_date_time = :modify_date_time, modify_by = :modify_by, inv_remarks = :inv_remarks, cus_branch_id = :cus_branch_id, inv_mode_of_trans = :inv_mode_of_trans, inv_vechicle_no = :inv_vechicle_no,
         inv_trans_charge = :inv_trans_charge, inv_tot_value = :inv_tot_value, inv_bal_value = :inv_bal_value, inv_dc_no = :inv_dc_no, inv_dc_date = :inv_dc_date, pay_id = :pay_id, pay_chq_dt = :pay_chq_dt, pay_refno = :pay_refno, pay_chq_no = :pay_chq_no, pay_cardno = :pay_cardno, credit_remarks = :credit_remarks, invoice_type = :invoice_type, 
         inv_status = :inv_status WHERE inv_id = :inv_id");

        $data = array(
            ':inv_id' => $update_id,
            ':inv_date' => $_REQUEST['inv_date'],
            ':supp_id' => $_REQUEST['supp_id'],
            ':inv_remarks' => $_REQUEST['inv_remarks'],
            ':cus_branch_id' => $_REQUEST['branch_id'],
            ':inv_mode_of_trans' => $_REQUEST['inv_mode_of_trans'],
            ':modify_date_time' => $_REQUEST['modify_date_time'],
            ':modify_by' => $_REQUEST['modify_by'],
            ':inv_vechicle_no' => $_REQUEST['inv_vechicle_no'],
            ':inv_trans_charge' => $_REQUEST['inv_trans_charge'],
            ':inv_tot_value' => $_REQUEST['inv_tot_value'],
            ':inv_bal_value' => $_REQUEST['inv_tot_value'],
            ':inv_dc_no' => strtoupper($_REQUEST['inv_dc_no']),
            ':inv_dc_date' => $_REQUEST['inv_dc_date'],
            ':pay_id' => $_REQUEST['pay_id'],
            ':pay_chq_dt' => $_REQUEST['pay_chq_dt'],
            ':pay_refno' => $_REQUEST['pay_refno'],
            ':pay_chq_no' => $_REQUEST['pay_chq_no'],
            ':pay_cardno' => $_REQUEST['pay_cardno'],
            ':credit_remarks' => $_REQUEST['cred_remark'],
            ':invoice_type' => 'I',
            ':inv_status' => 1
        );
        //echo"<pre>";print_r($data);exit;
        $stmt->execute($data);

        /* ------------ SAVE tbl_details  -----------*/
        $delete_details =  "DELETE FROM tbl_invoice_details WHERE inv_id = '" . $update_id . "'";
        $result = $conn->prepare($delete_details);
        $result->execute();


        /* details */
        if (isset($_REQUEST['temp_item_id'])) {
            for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) {

                //echo count($_REQUEST['temp_item_id']);
                $item_value = $item_taxval = $item_total = 0;
                $stmt1 = null;
                $stmt1 = $conn->prepare("INSERT INTO tbl_invoice_details (inv_id, item_id, inv_qty, inv_unit, unit_price, inv_discount, inv_discount_amt, vat, cgst_per, cgst_val, sgst_per, sgst_val, igst_per, 
                 igst_val, inv_value, tax_value, net_value) 
                 VALUES (:inv_id, :item_id, :inv_qty, :inv_unit, :unit_price, :inv_discount, :inv_discount_amt, :vat, :cgst_per, :cgst_val, :sgst_per, :sgst_val, :igst_per, :igst_val, 
                 :inv_value, :tax_value, :net_value)");

                $data = array(
                    ':inv_id' => $update_id,
                    ':item_id' => $_REQUEST['temp_item_id'][$x],
                    ':inv_qty' => $_REQUEST['temp_qty'][$x],
                    ':inv_unit' => $_REQUEST['temp_unit'][$x],
                    ':unit_price' => $_REQUEST['temp_unit_price'][$x],
                    ':inv_discount' => $_REQUEST['temp_discount_per'][$x],
                    ':inv_discount_amt' => $_REQUEST['temp_discount_val'][$x],
                    ':vat' => $_REQUEST['temp_vat'][$x],
                    ':cgst_per' => $_REQUEST['temp_cgst'][$x],
                    ':cgst_val' => $_REQUEST['temp_cgst_val'][$x],
                    ':sgst_per' => $_REQUEST['temp_sgst'][$x],
                    ':sgst_val' => $_REQUEST['temp_sgst_val'][$x],
                    ':igst_per' => $_REQUEST['temp_vat'][$x],
                    ':igst_val' => $_REQUEST['temp_vat_val'][$x],
                    ':inv_value' => $_REQUEST['temp_inv_price'][$x],
                    ':tax_value' => $_REQUEST['temp_vat_val'][$x],
                    ':net_value' => $_REQUEST['temp_net_amt'][$x]
                );

                //print_r($data);
                $stmt1->execute($data);
            }
        }

        /* details */
        $delete_details =  "DELETE FROM  tbl_invoice_denomination_details WHERE inv_id = '" . $update_id . "'";
        $result = $conn->prepare($delete_details);
        $result->execute();

        /* details */
        if (isset($_REQUEST['temp_cash_id'])) {
            for ($x = 0; $x < count($_REQUEST['temp_cash_id']); $x++) {

                //echo count($_REQUEST['temp_cash_id']);
                $stmt1 = null;
                $stmt1 = $conn->prepare("INSERT INTO tbl_invoice_denomination_details (inv_id, cash_id, den_name, den_count, den_total) 
                 VALUES (:inv_id, :cash_id, :den_name, :den_count, :den_total)");

                $data = array(
                    ':inv_id' => $update_id,
                    ':cash_id' => $_REQUEST['temp_cash_id'][$x],
                    ':den_name' => $_REQUEST['temp_cash_name'][$x],
                    ':den_count' => $_REQUEST['temp_cash_count'][$x],
                    ':den_total' => $_REQUEST['temp_total'][$x]
                );

                //print_r($data);
                $stmt1->execute($data);
            }
        }
        /* details */

        /* STOCK DETAILS */

        $stmt1 = null;
        $stmt1 = $conn->prepare("INSERT INTO tbl_stock_flow 
                        (trans_type, trans_id, branch_id, trans_date, item_id, item_price, before_qty, rcvd_qty, trans_qty, reje_qty, pend_qty, after_qty, modify_by, modify_date_time) 
                        VALUES
                        (:trans_type, :trans_id, :branch_id, :trans_date, :item_id, :item_price, :before_qty, :rcvd_qty, :trans_qty, :reje_qty, :pend_qty, :after_qty, :modify_by, :modify_date_time)");

        /* New Current Stock Update Branch */
        $field_name = $dbconn->GetSingleReconrd("mst_branch", "branch_stock_field", "branch_id", $_SESSION['_user_branch']);
        $stmt2 = null;
        $stmt2 = $conn->prepare("UPDATE tbl_item_stock SET " . $field_name . " = :branch_stock WHERE item_id = :item_id ");

        for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) {

            //$item_curr_stock = $dbconn->GetSingleReconrd("tbl_item_details", "item_curr_stock", "item_id", $_REQUEST['temp_item_id'][$x]);

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
        // 	$stmt1 = null;
        // 	$stmt1 = $conn->prepare("UPDATE tbl_item_details SET item_curr_stock = :item_curr_stock, modify_date_time=:modify_date_time, modify_by=:modify_by WHERE item_id = :item_id ");

        //     $item_curr_stock = $dbconn->GetSingleReconrd("tbl_item_details", "item_curr_stock", "item_id", $_REQUEST['temp_item_id'][$x]);

        // 	$data = array(
        // 		':item_id' => $_REQUEST['temp_item_id'][$x],
        // 		':item_curr_stock' => $after_qty,
        // 		':modify_date_time' => $_REQUEST['modify_date_time'],
        // 		':modify_by' => $_REQUEST['modify_by'],
        // 	);
        // 	$stmt1->execute($data);
        // }

        /* ITEM DETAILS */
    } catch (Exception $e) {
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
    }
    $_SESSION['_msg'] = "Direct Invoice succesfully Saved..!";
    header("location:invoice_list.php");
    die();
}
$discount_apply = 0;

if (isset($_REQUEST['inv_id']) && $_REQUEST['inv_id'] != "") {

    $result = $conn->query("SELECT * FROM tbl_invoice WHERE inv_id = " . $_REQUEST['inv_id']);
    if ($result->rowCount() > 0) {
        $get = $result->fetch(PDO::FETCH_OBJ);

        if ($get->inv_date != "0000-00-00" && $get->inv_date != "") {
            $inv_date = date("Y-m-d", strtotime($get->inv_date));
        }
        if ($get->inv_dc_date != "0000-00-00" && $get->inv_dc_date != "") {
            $inv_dc_date = date("Y-m-d", strtotime($get->inv_dc_date));
        }
		$discount_apply = $dbconn->GetSingleReconrd("mst_supplier_new", "discount_apply", "supp_id", $get->supp_id);

    }
}




?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Invoice</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <?php include_once("inc/common/css-js.php"); ?>

</head>

<script type="text/javascript">
    var wasSubmitted = false;

    function fnValidate() {

        if (notSelected(document.thisForm.supp_id, "Customer..!")) {
            return false;
        }

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


        var rowCount = $('#show_table tr').length;
        if (rowCount <= 2) {
            alert("Please enter atleast one item");
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
            if (isNull(document.thisForm.cred_remark, "Credit Remarks. ..!")) {
                return false;
            }
        }



        // net value cannot be 0
        var total_val5 = 0;
        $("#show_table tr").each(function() {
            var temp_net_amt = $(this).closest('tr').find('.temp_net_amt').val();
            if (temp_net_amt > 0) {
                total_val5 = parseFloat(total_val5) + parseFloat(temp_net_amt);
            }
        });
        if (total_val5 <= 0) {
            alert("Kindly check net amount");
            return false;
        }
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
        var pay_mode = $('#pay_id').val();
        if (pay_mode == 1) {
            if (total_val != total_val5) {
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
                $('#cred_remark').val('');
                $('.cash_denomination').hide();

            } else if (pay_mode == "4") {
                $('.pay_refno_div').show();
                $('.pay_chq_dt').show();
                $('.pay_chq_div').hide();
                $('#pay_chq_no').val('');
                $('.pay_cardno_div').hide();
                $('#pay_cardno').val('');
                $('.cred_remark_div').hide();
                $('#cred_remark').val('');
                $('.cash_denomination').hide();

            } else if (pay_mode == "3") {
                $('.pay_cardno_div').show();
                $('.pay_chq_dt').show();
                $('.pay_chq_div').hide();
                $('#pay_chq_no').val('');
                $('.pay_refno_div').hide();
                $('#pay_refno').val('');
                $('.cred_remark_div').hide();
                $('#cred_remark').val('');
                $('.cash_denomination').hide();

            } else if (pay_mode == "5") {
                $('.pay_refno_div').show();
                $('.pay_chq_dt').show();
                $('.pay_chq_div').hide();
                $('#pay_chq_no').val('');
                $('.pay_cardno_div').hide();
                $('#pay_cardno').val('');
                $('.cred_remark_div').hide();
                $('#cred_remark').val('');
                $('.cash_denomination').hide();

            } else if (pay_mode == "1") {
                $('.pay_refno_div').hide();
                $('.pay_chq_dt').hide();
                $('.pay_chq_div').hide();
                $('#pay_chq_no').val('');
                $('.pay_cardno_div').hide();
                $('#pay_cardno').val('');
                $('.cred_remark_div').hide();
                $('#cred_remark').val('');
                $('.cash_denomination').show();

            } else if (pay_mode == "6") {
                $('.pay_refno_div').show();
                $('.pay_chq_dt').show();
                $('.pay_chq_div').hide();
                $('#pay_chq_no').val('');
                $('.pay_cardno_div').hide();
                $('#pay_cardno').val('');
                $('.cred_remark_div').hide();
                $('#cred_remark').val('');
                $('.cash_denomination').hide();

            } else if (pay_mode == "7") {
                $('.cred_remark_div').show();
                $('.pay_chq_dt').show();
                $('.pay_chq_div').hide();
                $('#pay_chq_no').val('');
                $('.pay_refno_div').hide();
                $('.pay_cardno_div').hide();
                $('#pay_cardno').val('');

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
                $('#cred_remark').val('');
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
                url: "inc/cis_ajax/jquery_po_inv_details.php",
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

                var total_val = 0;
                $("#show_table2 tr").each(function() {
                    var temp_total = $(this).closest('tr').find('.temp_total').val();
                    if (temp_total > 0) {
                        total_val = parseFloat(total_val) + parseFloat(temp_total);

                    }
                });
                $('#show_table2 tfoot th#lastRow01').html(total_val.toFixed(2));

            });

        });




        $('#add_items').click(function() {


            if ($('#item_code').val() == "") {
                alert("Please enter the Item Code ...!");
                $('#item_code').focus();
                return false;
            }
            if ($('#inv_qty').val() == "") {
                alert("Please enter the Item Quantity ...!");
                $('#inv_qty').focus();
                return false;
            }
            if ($('#inv_unit_price').val() == "" || $('#inv_unit_price').val() == 0) {
                alert("Please Check the Unit Price ...!");
                $('#inv_unit_price').focus();
                return false;
            }


            var arr = [];
            var item_id = $('#item_id_no').val();
            //alert(item_id);
            $("#show_table tr").each(function() {
                arr.push(this.id);
            });
            if (jQuery.inArray(item_id, arr) !== -1) {
                $('#' + item_id).remove();
            }

            var table = document.getElementById("show_table");
            var rowCount = 1;
            //rowCount += table.tBodies[0].rows.length;
            var po_qty = $("#inv_qty").val();
            var po_unit_price = $("#inv_unit_price").val();
            var inv_vat = $("#inv_vat").val();
            var po_unit = $("#inv_unit").val();
            var po_net_amt = $("#inv_net_amt").val();
            var po_price = $("#inv_price").val();
            var inv_dis = $("#inv_dis").val();
            var po_item_price = $("#inv_item_price").val();
            var inv_dis_val = $("#inv_dis_val").val();
            var inv_cgst = $("#inv_cgst").val();
            var inv_sgst = $("#inv_sgst").val();
            var inv_vat_val = $("#inv_vat_val").val();
            var inv_cgst_val = $("#inv_cgst_val").val();
            var inv_sgst_val = $("#inv_sgst_val").val();

            $.ajax({
                type: "POST",
                url: "inc/cis_ajax/jquery_po_inv_details.php",
                data: {
                    "item_id": item_id,
                    "po_qty": po_qty,
                    "po_unit_price": po_unit_price,
                    "inv_vat": inv_vat,
                    "po_unit": po_unit,
                    "po_net_amt": po_net_amt,
                    "po_price": po_price,
                    "rowCount": rowCount,
                    "inv_dis": inv_dis,
                    "po_item_price": po_item_price,
                    "inv_dis_val": inv_dis_val,
                    "inv_cgst": inv_cgst,
                    "inv_sgst": inv_sgst,
                    "inv_vat_val": inv_vat_val,
                    "inv_cgst_val": inv_cgst_val,
                    "inv_sgst_val": inv_sgst_val,
                    'mode': 'inv_save'
                }
            }).done(function(msg) {
                //$('#show_table').html(msg);
                $("#show_table tbody").append(msg);
                //var n = msg.indexOf("tbody");
                //$('#inv_items').val(n);
                $("#item_id").val('').trigger('change');
                //$("#item_id").val('');
                $("#item_code").val('');
                $('#inv_qty').val('');
                $("#inv_unit").val('');
                $('#inv_unit_price').val('');
                $('#inv_price').val('');
                $('#inv_vat').val('');
                $('#inv_net_amt').val('');
                $('#inv_dis').val('');
                var total_val1 = 0;

                var total_val1 = 0;
                $("#show_table tr").each(function() {
                    var temp_qty = $(this).closest('tr').find('.temp_qty').val();
                    if (temp_qty > 0) {
                        total_val1 = parseFloat(total_val1) + parseFloat(temp_qty);
                    }
                });
                $('#show_table tfoot th#lastRow1').html(total_val1.toFixed(0));
                var total_val2 = 0;
                $("#show_table tr").each(function() {
                    var temp_unit_price = $(this).closest('tr').find('.temp_unit_price').val();
                    if (temp_unit_price > 0) {
                        total_val2 = parseFloat(total_val2) + parseFloat(temp_unit_price);
                    }
                });
                $('#show_table tfoot th#lastRow2').html(total_val2.toFixed(2));
                var total_val3 = 0;
                $("#show_table tr").each(function() {
                    var temp_inv_price = $(this).closest('tr').find('.temp_inv_price').val();
                    if (temp_inv_price > 0) {
                        total_val3 = parseFloat(total_val3) + parseFloat(temp_inv_price);
                    }
                });
                $('#show_table tfoot th#lastRow3').html(total_val3.toFixed(2));
                var total_val5 = 0;
                $("#show_table tr").each(function() {
                    var temp_net_amt = $(this).closest('tr').find('.temp_net_amt').val();
                    if (temp_net_amt > 0) {
                        total_val5 = parseFloat(total_val5) + parseFloat(temp_net_amt);
                    }
                });
                $('#show_table tfoot th#lastRow5').html(total_val5.toFixed(2));
                $('#inv_tot_value').val(total_val5);

            });

        });


        $('#item_id').change(function() {
            var item_id = $('#item_id').val();
            $("#inv_qty").val('');
            $.ajax({
                type: "POST",
                url: "inc/cis_ajax/jquery_select_item.php",
                data: {
                    item_id: item_id,
                    mode: 'item_val'
                }
            }).done(function(msg) {

                string = msg.split("~");
                //string = msg.split("~");
                $("#item_id").val(string[1]);
                $("#inv_unit_price").val(string[2]);
                $("#inv_unit_price").val(string[8]);
                $("#inv_vat").val(string[3]);
                $("#inv_cgst").val(string[9]);
                $("#inv_sgst").val(string[10]);
                $("#inv_unit").val(string[4]);
                $('#item_id_no').val(item_id);
                $('#item_order_min_qty').val(string[11]);
                $('#item_type').val(string[12]);
                // $('#inv_dis').val(string[6]);
                $('#inv_sel_price').val(string[8]);
                $('#inv_item_price').val(string[7]);

                $("#inv_dis").data("min_discount", string[13]);
                $("#inv_dis").data("max_discount", string[14]);

                //alert(string[12]);
                if (string[11] != 8) {
                    if (string[11] == undefined) {
                        $('#min_qty').html('');
                    } else {
                        $('#min_qty').html(' ( C.ST - ' + string[11] + ' )');
                    }
                }
            });
        });

        $('#inv_qty, #inv_dis').change(function() {
            // alert();
            var qty = $('#inv_qty').val();
            var inv_discount = $('#inv_dis').val();
            var inv_selling_price = $("#inv_unit_price").val();
            var inv_vat = $("#inv_vat").val();
            var discount_apply = $('#discount_apply').val();

            var max_discount = parseFloat($("#inv_dis").data("max_discount")) || 0;
            var min_discount = parseFloat($("#inv_dis").data("min_discount")) || 0;

            // Validate discount range
            if (discount_apply == 0) {
                if (inv_discount > 0) {
                    if (inv_discount < min_discount || inv_discount > max_discount) {
                        alert('Discount must be between ' + min_discount + '% and ' + max_discount + '%');
                        $('#inv_dis').val(0);
                        $('#inv_dis').trigger('change');
                        return false;
                    }
                }
            }

            final_price = 0;
            price = (inv_selling_price * qty);

            if (inv_discount > 0) {
                inv_amt = ((price * inv_discount) / 100);
                final_price = price - inv_amt;
            } else {
                final_price = price;
            }
            if (inv_vat > 0)
                tax_val = ((final_price * inv_vat) / 100);
            else
                tax_val = 0;

            net_val = final_price + tax_val;

            // alert(final_price);
            // alert(net_val);
            // alert(price);


            $('#inv_price').val(final_price.toFixed(2));
            $('#inv_net_amt').val(net_val.toFixed(2));
            $('#inv_dis_val').val(final_price.toFixed(2));
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
                /* $('#branch_id option').remove();
                 var dataArr = msg.split('#');
                 $.each(dataArr, function(i, element) {
                     if (dataArr[i] != "") {
                         var dataArr2 = dataArr[i].split('~');
                         $('#branch_id').append("<option value='" + dataArr2[0] + "'>" + dataArr2[1] + "</option>");
                     }
                 });
                 $("#s2id_branch_id").select2('val', '');
                 $("#branch_id").trigger("liszt:updated");*/

                // Split response
                var response = msg.split("||");

                var discount_apply = response[0];
                var branchData = response[1];

                // Save into hidden input
                $('#discount_apply').val(discount_apply);

                $('#branch_id option').remove();

                var dataArr = branchData.split('#');

                $.each(dataArr, function(i, element) {
                    if (element != "") {
                        var dataArr2 = element.split('~');
                        $('#branch_id').append(
                            "<option value='" + dataArr2[0] + "'>" + dataArr2[1] + "</option>"
                        );
                    }
                });

                $("#s2id_branch_id").select2('val', '');
                $("#branch_id").trigger("liszt:updated");
                $("#inv_dis").trigger("change");
            });
        });

        $('#inv_qty').keyup(function() {
            var qty = parseFloat($(this).val());
            var item_id = $("#item_id").val();
            var inv_unit_price = $("#inv_unit_price").val();
            var inv_vat = $("#inv_vat").val();
            //var item_order_min_qty = parseFloat($("#item_order_min_qty").val());
            var inv_cgst = $("#inv_cgst").val();
            var inv_sgst = $("#inv_sgst").val();
            var item_type = $("#item_type").val();
            // var po_dis = $("#po_dis").val();
            var item_order_min_qty = parseFloat($("#item_order_min_qty").val());
            // alert(item_type);
            if (item_type != 8) {
                if (qty > item_order_min_qty) {
                    alert("Please check the Quantity (Current Stock " + item_order_min_qty + ")");
                    $(this).val('');
                    return false;
                }
            }
            $.ajax({
                type: "POST",
                url: "inc/cis_ajax/jquery_get_unit_inv_gst.php",
                data: {
                    "qty": qty,
                    "po_cost_price": inv_unit_price,
                    "po_vat": inv_vat,
                    "po_cgst": inv_cgst,
                    "po_sgst": inv_sgst,
                    // "po_dis": po_dis,
                    "mode": 'get_net_amt'
                }
            }).done(function(msg) {
                // alert(msg);
                var dataSal = msg.split('~');

                $('#inv_price').val(dataSal[0]);
                $('#inv_net_amt').val(dataSal[1]);
                $('#inv_cgst_val').val(dataSal[3]);
                $('#inv_sgst_val').val(dataSal[4]);
                $('#inv_vat_val').val(dataSal[5]);
                // $('#po_dis').val(dataSal[2]);
            });
        });

    });


    function remove_item2(auto_id) {
        $('#' + auto_id).remove();

        var total_val1 = 0;
        $("#show_table tr").each(function() {
            var temp_qty = $(this).closest('tr').find('.temp_qty').val();
            if (temp_qty > 0) {
                total_val1 = parseFloat(total_val1) + parseFloat(temp_qty);
            }
        });
        $('#show_table tfoot th#lastRow1').html(total_val1.toFixed(2));
        var total_val2 = 0;
        $("#show_table tr").each(function() {
            var temp_unit_price = $(this).closest('tr').find('.temp_unit_price').val();
            if (temp_unit_price > 0) {
                total_val2 = parseFloat(total_val2) + parseFloat(temp_unit_price);
            }
        });
        $('#show_table tfoot th#lastRow2').html(total_val2.toFixed(2));

        var total_val3 = 0;
        $("#show_table tr").each(function() {
            var temp_inv_price = $(this).closest('tr').find('.temp_inv_price').val();
            if (temp_inv_price > 0) {
                total_val3 = parseFloat(total_val3) + parseFloat(temp_inv_price);
            }
        });
        $('#show_table tfoot th#lastRow3').html(total_val3.toFixed(2));
        var total_val5 = 0;
        $("#show_table tr").each(function() {
            var temp_net_amt = $(this).closest('tr').find('.temp_net_amt').val();
            if (temp_net_amt > 0) {
                total_val5 = parseFloat(total_val5) + parseFloat(temp_net_amt);
            }
        });
        $('#show_table tfoot th#lastRow5').html(total_val5.toFixed(2));
        $('#inv_tot_value').val(total_val5);

        var total_val = 0;
        $("#show_table2 tr").each(function() {
            var temp_total = $(this).closest('tr').find('.temp_total').val();
            if (temp_total > 0) {
                total_val = parseFloat(total_val) + parseFloat(temp_total);
            }
        });
        $('tfoot th#lastRow01').html(total_val.toFixed(2));
    }

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
                            <span class="breadcrumb-item active">Invoice</span>
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
                                <h6 class="card-title"> Invoice</h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="invoice_list.php" title="Invoice List"><i class="icon-arrow-left52 mr-2"></i></a>
                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>

                            </div>
                            <form name='thisForm' class="form-horizontal" method='POST' action="">
                                <input type="hidden" name="inv_items" id="inv_items" value="-1">
                                <input type="hidden" name="gst" id="gst" value="">
                                <input type="hidden" name="item_hsn" id="item_hsn" value="">
                                <input type="hidden" name="item_type" id="item_type" value="">
                                <input type="hidden" name="item_type" id="item_type" value="">
                                <input type="hidden" name="discount_apply" id="discount_apply" value="<?php echo $discount_apply; ?>">
                                <fieldset>

                                    <div class="card-body">
                                        <?php
                                        if ($_REQUEST['inv_id'] != "") {
                                            $inv_refno = $get->inv_refno;
                                        } else {
                                            $_REQUEST['branch'] = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id='" . $_SESSION['_user_branch'] . "' AND branch_status", 1);

                                            $_REQUEST['inv_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
                                            $_REQUEST['inv_slno'] = $dbconn->GetMaxValue(' tbl_invoice', 'inv_slno', 'branch_id="' . $_SESSION['_user_branch'] . '" AND inv_finyr="' . $_REQUEST['inv_finyr'] . '" AND 1', 1) + 1;

                                            $inv_refno = 'INV/' . leadingZeros($_REQUEST['inv_slno'], 4) . '/BIE/' . $_REQUEST['branch'] . '/' . $_REQUEST['inv_finyr'];
                                        }
                                        ?>
                                        <legend class="font-weight-semibold pb-0 mb-2"><span class="po_title"><?php echo $inv_refno; ?></span></legend>
                                        <div class="form-group">
                                            <div class="row">
                                                <input type="hidden" name="inv_no" id="inv_no" class="form-control" value="<?php echo $inv_no; ?>" />
                                                <input type="hidden" name="inv_tot_value" id="inv_tot_value" value="<?php echo $get->inv_tot_value; ?>">
                                                <label class="col-lg-1 col-form-label">Customer <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3">
                                                    <select name="supp_id" id="supp_id" data-placeholder="Choose a Customer.." class="form-control select-search">
                                                        <option value=""></option>
                                                        <?php
                                                        $dbconn = new dbhandler();
                                                        echo $dbconn->fnFillComboFromTable_Where("supp_id", "supp_name", "mst_supplier_new", "supp_id", " WHERE supp_status = '1' AND supp_type = 'C'") ?>
                                                    </select>
                                                    <script>
                                                        document.thisForm.supp_id.value = "<?php echo $get->supp_id; ?>";
                                                    </script>
                                                </div>
                                                <label class="col-lg-1 col-form-label">Customer Branch / Delivery Address </label>
                                                <div class="col-lg-3">
                                                    <select name="branch_id" id="branch_id" data-placeholder="Choose a Customer Branch.." class="form-control select-search">
                                                        <option value=""></option>
                                                        <?php
                                                        $dbconn = new dbhandler();
                                                        if (isset($_REQUEST['inv_id']) && $_REQUEST['inv_id'] != "") {
                                                            echo $dbconn->fnFillComboFromTable_Where("branch_id", "CONCAT(branch_name,' ~ ',branch_add1,' ~ ',branch_add2)", "mst_customer_branch", "branch_id", " WHERE branch_status = '1' AND supp_id = '" . $get->supp_id . "'");
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
                                                    <input type="text" name="inv_mode_of_trans" id="inv_mode_of_trans" class="form-control" maxlength="100" placeholder="Mode of Transport" value="<?php echo $get->inv_mode_of_trans; ?>" />
                                                </div>
                                                <label class="col-lg-1 col-form-label">Vehicle No. <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3">
                                                    <input type="text" name="inv_vechicle_no" id="inv_vechicle_no" class="form-control" maxlength="75" placeholder="Vehicle No." value="<?php echo $get->inv_vechicle_no; ?>" />
                                                </div>
                                                <label class="col-lg-1 col-form-label">Transport Charges <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3">
                                                    <input type="text" name="inv_trans_charge" id="inv_trans_charge" class="form-control number_only_dot" maxlength="7" placeholder="Transport Charges" value="<?php echo $get->inv_trans_charge; ?>" />
                                                </div>
                                            </div>
                                            <div class="row pt-2">
                                                <label class="col-lg-1 col-form-label">DC No. <span class="text-mandatory"> </span></label>
                                                <div class="col-lg-3">
                                                    <input type="text" name="inv_dc_no" id="inv_dc_no" class="form-control" maxlength="100" placeholder="DC No." value="<?php echo $get->inv_dc_no; ?>" />
                                                </div>
                                                <label class="col-lg-1 col-form-label">DC Date <span class="text-mandatory"> </span></label>
                                                <div class="col-lg-3">
                                                    <input type="date" name="inv_dc_date" id="inv_dc_date" class="form-control" maxlength="75" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $inv_dc_date; ?>" placeholder="Date" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <fieldset>
                                                    <legend class="font-weight-semibold"><i class="icon-cart mr-2"></i>Invoice Details</legend>
                                                    <div class="form-group row">
                                                        <div class="form-group col-md-4">
                                                            <label>Item <span class="text-mandatory">*</span></label>
                                                            <select data-placeholder="Choose a Item.." name="item_id" id="item_id" class="form-control select-search">
                                                                <option value="">-- Select Item --</option>
                                                                <?php
                                                                echo $dbconn->fnFillComboFromTable_Where("item_id", "CONCAT(item_purchase_code, ' | ',item_desciption)", "tbl_item_details", "item_id", " WHERE FIND_IN_SET(" . $_SESSION['_user_branch'] . ", branch_id) > 0 AND item_status = 1");
                                                                ?>
                                                            </select>
                                                            <input type="hidden" name="item_id_no" id="item_id_no">
                                                            <input type="hidden" name="item_order_min_qty" id="item_order_min_qty">
                                                        </div>
                                                        <div class="form-group pl-0 col-md-2">
                                                            <label>Quantity <span class="text-mandatory">*</span><span id="min_qty" style="color:red"></span></label>
                                                            <input type="text" name="inv_qty" id="inv_qty" class="form-control number_only" maxlength="9">
                                                        </div>
                                                        <div class="form-group pl-0 col-md-2">
                                                            <label>Unit <span class="text-mandatory">*</span></label>
                                                            <input type="text" tabIndex="-1" readonly name="inv_unit" id="inv_unit" class="form-control">
                                                        </div>
                                                        <div class="form-group pl-0 col-md-2">
                                                            <label>Unit Price <span class="text-mandatory">*</span></label>
                                                            <input type="text" tabIndex="-1" readonly name="inv_unit_price" id="inv_unit_price" class="form-control number_only">
                                                        </div>
                                                        <div class="form-group pl-0 col-md-1" id="inv_desc_discount">
                                                            <p><b>Discount(%)</b></p>
                                                            <input type="text" class="form-control" name="inv_dis" id="inv_dis" maxlength="9" value="" />
                                                            <input type="hidden" readonly name="inv_dis_val" id="inv_dis_val" value="">
                                                        </div>



                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="form-group pl-0 col-md-2">
                                                            <label>Amount <span class="text-mandatory">*</span></label>
                                                            <input type="text" tabIndex="-1" maxlength="7" readonly name="inv_price" id="inv_price" maxlength="7" class="form-control">
                                                            <!-- <input type="hidden" readonly tabIndex="-1" name="inv_dis_val" id="inv_dis_val" class="form-control"> -->
                                                        </div>
                                                        <div class="form-group col-md-2">
                                                            <label>GST <span class="text-mandatory">*</span></label>
                                                            <input type="text" readonly tabIndex="-1" name="inv_vat" id="inv_vat" class="form-control">
                                                            <input type="hidden" readonly tabIndex="-1" name="inv_vat_val" id="inv_vat_val" class="form-control">
                                                            <input type="hidden" readonly tabIndex="-1" name="inv_cgst" id="inv_cgst" class="form-control">
                                                            <input type="hidden" readonly tabIndex="-1" name="inv_cgst_val" id="inv_cgst_val" class="form-control">
                                                            <input type="hidden" readonly tabIndex="-1" name="inv_sgst" id="inv_sgst" class="form-control">
                                                            <input type="hidden" readonly tabIndex="-1" name="inv_sgst_val" id="inv_sgst_val" class="form-control">
                                                        </div>
                                                        <div class="form-group pl-0 col-md-2">
                                                            <label>Net Amt <span class="text-mandatory">*</span></label>
                                                            <input type="text" readonly tabIndex="-1" name="inv_net_amt" id="inv_net_amt" class="form-control">
                                                        </div>
                                                        <div class="form-group pl-0" id="item_indv_add_btn">
                                                            <button class="btn btn-success mr-2 mt-4 pt-1" id="add_items" name="add_items" type="button"> +</button>
                                                        </div>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div id="show_table" class="col-md-12">
                                                <table class="table table-xs table-bordered table-dets-responsive">
                                                    <thead>
                                                        <tr class="bg-teal">
                                                            <th width="10%">Item Code</th>
                                                            <th width="20%">Description</th>
                                                            <th width="5%">Quantity</th>
                                                            <th width="5%">Unit</th>
                                                            <th width="5%">Unit Price</th>
                                                            <th width="5%">Disc %</th>
                                                            <th width="5%">Amount</th>
                                                            <th width="5%">GST (%)</th>
                                                            <th width="5%">Net Amount</th>
                                                            <th width="2%"><i class=" icon-cog6 mr-2"></i></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if ($_REQUEST['inv_id'] != '') {

                                                            $dets_sql = "SELECT * FROM tbl_invoice_details WHERE inv_id = " . $_REQUEST['inv_id'];
                                                            $result_dets = $conn->query($dets_sql);
                                                            $rowCnt = $result_dets->rowCount();
                                                            if ($result_dets->rowCount() > 0) {
                                                                $sno = 1;
                                                                $tot_inv_qty = $tot_unit_price = $tot_inv_value = $tot_net_value = 0;
                                                                while ($itm = $result_dets->fetch()) {
                                                                    $item_desciption = $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_id", $itm->item_id);
                                                                    $item_purchase_code = $dbconn->GetSingleReconrd("tbl_item_details", "item_purchase_code", "item_id", $itm->item_id);
                                                                    echo '
                                                                <tr id="' . $itm->item_id . '" >
                                                                    <td>' . $item_purchase_code . '
                                                                        <input type="hidden" class="temp_item_id" name="temp_item_id[]" value="' . $itm->item_id . '" />
                                                                    </td>
                                                                    <td>' . $item_desciption . '
                                                                        <input type="hidden" class="item_desciption" name="item_desciption[]" value="' . $item_desciption . '" />
                                                                    </td>
                                                                    <td class="text-right">' . $itm->inv_qty . '
                                                                        <input type="hidden" class="temp_qty" name="temp_qty[]" value="' . $itm->inv_qty . '" />
                                                                    </td>
                                                                    <td class="text-right">' . $itm->inv_unit . '
                                                                        <input type="hidden" class="temp_unit" name="temp_unit[]" value="' . $itm->inv_unit . '" />
                                                                    </td>
                                                                    <td class="text-right">' . $itm->unit_price . '
                                                                        <input type="hidden" class="temp_unit_price" name="temp_unit_price[]" value="' . $itm->unit_price . '" />
                                                                    </td>
                                                                    <td class="text-right">' . $itm->inv_discount . '
																	<input type="hidden" class="temp_discount_per" name="temp_discount_per[]" value="' . $itm->inv_discount . '" />
                                                                    </td>
                                                                    <td class="text-right">' . $itm->inv_value . '
                                                                        <input type="hidden" class="temp_inv_price" name="temp_inv_price[]" value="' . $itm->inv_value . '" />
																	    <input type="hidden" class="temp_discount_val" name="temp_discount_val[]" value="' .  $itm->inv_discount_amt . '">


                                                                    </td>
                                                                    <td class="text-right">' . $itm->vat . '
                                                                        <input type="hidden" class="temp_vat" name="temp_vat[]" value="' . $itm->vat . '" />
                                                                        <input type="hidden" class="temp_vat_val" name="temp_vat_val[]" value="' . $itm->igst_val . '" />
                                                                        <input type="hidden" class="temp_cgst" name="temp_cgst[]" value="' . $itm->cgst_per . '" />
                                                                        <input type="hidden" class="temp_cgst_val" name="temp_cgst_val[]" value="' . $itm->cgst_val . '" />
                                                                        <input type="hidden" class="temp_sgst" name="temp_sgst[]" value="' . $itm->sgst_per . '" />
                                                                        <input type="hidden" class="temp_sgst_val" name="temp_sgst_val[]" value="' . $itm->sgst_val . '" />
                                                                    </td>
                                                                    <td class="text-right">' . $itm->net_value . '
                                                                        <input type="hidden" class="temp_net_amt" name="temp_net_amt[]" value="' . $itm->net_value . '" />
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <a href="javascript:remove_item2(' . $itm->item_id . ');" class="" rel="' . $itm->item_id . '"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a>
                                                                    </td>
                                                                </tr>';
                                                                    $sno++;
                                                                    $tot_inv_qty += $itm->inv_qty;
                                                                    $tot_unit_price += $itm->unit_price;
                                                                    $tot_inv_value += $itm->inv_value;
                                                                    $tot_net_value += $itm->net_value;
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <th colspan="2" class="text-right">Total : </th>
                                                            <!-- <th class="text-right" id="lastRow1"><?php echo round($tot_inv_qty); ?></th>
                                                            <th class="text-right"></th>
															<th class="text-right" id="lastRow2"><?php echo number_format($tot_unit_price, 2); ?></th>
															<th class="text-right" id="lastRow3"><?php echo number_format($tot_inv_value, 2); ?></th> -->
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>

                                                            <th class="text-right"></th>
                                                            <th class="text-right"></th>
                                                            <th class="text-right" id="lastRow5"><?php echo number_format($tot_net_value, 2); ?></th>
                                                            <th class="text-right"></th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>

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
                                                        document.thisForm.pay_id.value = "<?php echo $get->pay_id; ?>";
                                                    </script>
                                                </div>
                                                <label class="col-lg-1 col-form-label pay_chq_dt">Date <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3 pay_chq_dt">
                                                    <input type="date" name="pay_chq_dt" id="pay_chq_dt" value="<?php echo $get->pay_chq_dt; ?>" class="form-control" placeholder="Date" />
                                                </div>
                                                <label class="col-lg-1 col-form-label pay_refno_div">Ref No <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3 pay_refno_div">
                                                    <input type="text" name="pay_refno" id="pay_refno" value="<?php echo $get->pay_refno; ?>" class="form-control " maxlength="50" placeholder="Ref No" />
                                                </div>
                                                <label class="col-lg-1 col-form-label pay_chq_div">Cheque No. <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3 pay_chq_div">
                                                    <input type="text" name="pay_chq_no" id="pay_chq_no" value="<?php echo $get->pay_chq_no; ?>" class="form-control" maxlength="50" placeholder="Cheque No." value="" />
                                                </div>
                                                <label class="col-lg-1 col-form-label pay_cardno_div">Card No <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3 pay_cardno_div">
                                                    <input type="text" name="pay_cardno" id="pay_cardno" value="<?php echo $get->pay_cardno; ?>" class="form-control " maxlength="20" placeholder="Card No" />
                                                </div>
                                                <label class="col-lg-1 col-form-label cred_remark_div">Credit Remarks <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3 cred_remark_div">
                                                    <input type="text" name="cred_remark" id="cred_remark" value="<?php echo $get->credit_remarks; ?>" class="form-control " maxlength="20" placeholder="Credit Remarks" />
                                                </div>
                                            </div>



                                        </div>

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
                                        <hr>
                                        <div class="form-group row pt-4">
                                            <label class="col-lg-2 col-form-label">Remarks</label>
                                            <div class="col-lg-10">
                                                <textarea name="inv_remarks" id="inv_remarks" class="form-control" rows="2"><?php echo $get->inv_remarks; ?></textarea>
                                            </div>
                                        </div>


                                    </div>
                                    <div class="card-footer text-center pt-2">
                                        <div class="">
                                            <?php

                                            if (isset($_REQUEST["inv_id"]) && $_REQUEST["inv_id"] != '') { ?>
                                                <INPUT class="btn btn-primary mr-2" type="submit" name="UPDATE" value="UPDATE" onclick="return fnValidate();">
                                                <INPUT class="btn btn-success mr-2" type="submit" name="FINALIZE" value="Save Invoice" onclick="return fnValidate();">
                                                <INPUT class="btn btn-light" type="button" name="Cancel" value="Cancel" onClick="javascript:window.location.href='invoice_list.php'">
                                                <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['inv_id']; ?>">
                                            <?php } else { ?>
                                                <INPUT class="btn btn-primary mr-2" type="submit" name="Draft" id="Draft" value="Draft" onclick="return fnValidate();">
                                                <INPUT class="btn btn-light" type="button" name="Cancel" value="Cancel" onClick="javascript:window.location.href='invoice_list.php'">
                                                <input type="hidden" name="txtHid" value="0">
                                            <?php } ?>


                                        </div>
                                    </div>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php include("inc/common/footer.php") ?>
        </div>
    </div>
</body>

</html>