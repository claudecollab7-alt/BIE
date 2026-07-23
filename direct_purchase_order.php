<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
// require_once ("inc/common/dbrowiterator.php");	

isAdmin();


//ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);

$conn = new dbconnect();
$dbconn = new dbhandler();

$po_date = date("Y-m-d");


if (isset($_POST['Draft'])) {
	try {
		$_REQUEST['po_date'] = date("Y-m-d", strtotime($_REQUEST['po_date']));

		$_REQUEST['po_slno'] = $dbconn->GetMaxValue('tbl_purchase_order', 'po_slno', 'branch_id="'.$_SESSION['_user_branch'].'" AND 1 ', 1) + 1;
		$_REQUEST['po_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
		$po_approve_id = $dbconn->GetSingleReconrd("tbl_task_user", "user_id", "task_id", 1);
		$_REQUEST['branch'] = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id='".$_SESSION['_user_branch']."' AND branch_status", 1);

		$_REQUEST['po_refno'] = 'PO/' . leadingZeros($_REQUEST['po_slno'], 4) . '/BIE/'.$_REQUEST['branch'].'/' . $_REQUEST['po_finyr'];

		// $_REQUEST['payment_code'] = 'P'.leadingZeros($_REQUEST['payment_slno'],4);

		$_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
		$_REQUEST['modify_by'] = $_SESSION['_user_id'];

		$stmt = null;
		$stmt = $conn->prepare("INSERT INTO tbl_purchase_order (po_finyr, po_slno, po_refno, po_date, supp_id, po_type, po_remarks, branch_id, po_approve_id, modify_date_time, modify_by, po_status)
				 VALUES (:po_finyr, :po_slno, :po_refno, :po_date, :supp_id, :po_type, :po_remarks, :branch_id, :po_approve_id, :modify_date_time, :modify_by, :po_status)");
		$data = array(
			':po_finyr' => $_REQUEST['po_finyr'],
			':po_slno' => $_REQUEST['po_slno'],
			':po_refno' => $_REQUEST['po_refno'],
			':po_date' => $_REQUEST['po_date'],
			':supp_id' => $_REQUEST['supp_id'],
			':po_type' => 'D',
			':po_remarks' => $_REQUEST['po_remarks'],
			':branch_id' => $_SESSION['_user_branch'],
			':po_approve_id' => $po_approve_id,
			':modify_date_time' => $_REQUEST['modify_date_time'],
			':modify_by' => $_REQUEST['modify_by'],
			':po_status' => 0
		);
		$stmt->execute($data);
		$last_id = $conn->lastInsertId();
		/* ------------ SAVE tbl_po_details  -----------*/
		$delete_details =  "DELETE FROM tbl_purchase_order_details WHERE po_id = '" . $last_id . "'";
		$result = $conn->prepare($delete_details);
		$result->execute();

		/* details */
		$po_value = 0;
		if (isset($_REQUEST['temp_item_id'])) {
			for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) {

				//echo count($_REQUEST['temp_item_id']);
				$item_value = $item_taxval = $item_total = 0;
				$stmt1 = null;
				$stmt1 = $conn->prepare("INSERT INTO tbl_purchase_order_details (po_id, item_id, po_qty, po_unit, item_price, cost_price, vat, cgst_per, cgst_val, sgst_per, sgst_val, igst_per, 
				igst_val, discount_per, discount_val, po_value, tax_value, net_value) 
				VALUES (:po_id, :item_id, :po_qty, :po_unit, :item_price, :cost_price, :vat, :cgst_per, :cgst_val, :sgst_per, :sgst_val, :igst_per, :igst_val, :discount_per, :discount_val, 
				:po_value, :tax_value, :net_value)");

				$data = array(
					':po_id' => $last_id,
					':item_id' => $_REQUEST['temp_item_id'][$x],
					':po_qty' => $_REQUEST['temp_qty'][$x],
					':po_unit' => $_REQUEST['temp_unit'][$x],
					':item_price' => $_REQUEST['temp_item_price'][$x],
					':cost_price' => $_REQUEST['temp_cost_price'][$x],
					':vat' => $_REQUEST['temp_vat'][$x],
					':cgst_per' => $_REQUEST['temp_cgst'][$x],
					':cgst_val' => $_REQUEST['temp_cgst_val'][$x],
					':sgst_per' => $_REQUEST['temp_sgst'][$x],
					':sgst_val' => $_REQUEST['temp_sgst_val'][$x],
					':igst_per' => $_REQUEST['temp_vat'][$x],
					':igst_val' => $_REQUEST['temp_vat_val'][$x],
					':discount_per' => $_REQUEST['temp_discount_per'][$x],
					':discount_val' => $_REQUEST['temp_discount_val'][$x],
					':po_value' => $_REQUEST['temp_po_price'][$x],
					':tax_value' => $_REQUEST['temp_vat_val'][$x],
					':net_value' => $_REQUEST['temp_net_amt'][$x]
				);

				print_r($data);
				$stmt1->execute($data);
				$po_value = $po_value + $_REQUEST['temp_net_amt'][$x];
			}
		}

		/* details */

		/* details */
		$delete_details =  "DELETE FROM tbl_po_print_details WHERE po_id = '" . $last_id . "'";
		$result = $conn->prepare($delete_details);
		$result->execute();
		if (isset($_REQUEST['temp_pr_id'])) {
			for ($x = 0; $x < count($_REQUEST['temp_pr_id']); $x++) {

				//echo count($_REQUEST['temp_item_id']);
				$stmt1 = null;
				$stmt1 = $conn->prepare("INSERT INTO  tbl_po_print_details (po_id, pr_id, pr_name, pr_desc, pr_sort) 
								VALUES (:po_id, :pr_id, :pr_name, :pr_desc, :pr_sort)");

				$data = array(
					':po_id' => $last_id,
					':pr_id' => $_REQUEST['temp_pr_id'][$x],
					':pr_name' => $_REQUEST['temp_pr_name'][$x],
					':pr_desc' => $_REQUEST['temp_pr_desc'][$x],
					':pr_sort' => $_REQUEST['temp_pr_sort'][$x]
				);

				$stmt1->execute($data);
			}
		}

		/* details */


		$update_po = $conn->prepare("UPDATE  tbl_purchase_order SET po_value = :po_value WHERE po_id = :po_id");
		$data1 = array(
			':po_id' => $last_id,
			':po_value' => $po_value
		);
		$update_po->execute($data1);
	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		$_SESSION['_msg_err'] = $str;
	}



	$_SESSION['_msg'] = "Direct Purchase Order succesfully Saved..!";
	header("location:lst_direct_po.php");
	die();
}


if (isset($_POST['UPDATE'])) {
	$update_id = $_REQUEST['txtHid'];
	try {
		$_REQUEST['po_date'] = date("Y-m-d", strtotime($_REQUEST['po_date']));
		$po_approve_id = $dbconn->GetSingleReconrd("tbl_task_user", "user_id", "task_id", 1);
		$_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
		$_REQUEST['modify_by'] = $_SESSION['_user_id'];
		$stmt = null;
		$stmt = $conn->prepare("UPDATE  tbl_purchase_order SET po_date = :po_date, supp_id = :supp_id, po_remarks = :po_remarks, po_approve_id = :po_approve_id, 
		 modify_date_time = :modify_date_time, modify_by = :modify_by, po_approve_status = :po_approve_status, po_approve_sent_dtm = :po_approve_sent_dtm, po_approve_by = :po_approve_by, po_status = :po_status	WHERE po_id = :po_id");
		$data = array(
			':po_id' => $update_id,
			':po_date' => $_REQUEST['po_date'],
			':supp_id' => $_REQUEST['supp_id'],
			':po_remarks' =>  $_REQUEST['po_remarks'],
			
			':po_approve_id' => $po_approve_id,
			':modify_date_time' => $_REQUEST['modify_date_time'],
			':modify_by' => $_REQUEST['modify_by'],
			':po_approve_status' => '0',
			':po_approve_sent_dtm' => '',
			':po_approve_by' => '0',
			':po_status' => 0
		);
		//':branch_id' => $_SESSION['_user_branch'],

		$stmt->execute($data);

		$sql =  "DELETE FROM tbl_purchase_order_details WHERE po_id = '" . $update_id . "'";
		$result = $conn->prepare($sql);
		$result->execute();

		/* details */
		$po_value = 0;
		for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) {

			echo count($_REQUEST['temp_item_id']);
			$item_value = $item_taxval = $item_total = 0;
			$stmt1 = null;
			$stmt1 = $conn->prepare("INSERT INTO tbl_purchase_order_details (po_id, item_id, po_qty, po_unit, item_price, cost_price, vat, cgst_per, cgst_val, sgst_per, sgst_val, igst_per, 
				igst_val, discount_per, discount_val, po_value, tax_value, net_value) 
				VALUES (:po_id, :item_id, :po_qty, :po_unit, :item_price, :cost_price, :vat, :cgst_per, :cgst_val, :sgst_per, :sgst_val, :igst_per, :igst_val, :discount_per, :discount_val, 
				:po_value, :tax_value, :net_value)");


			$data = array(
				':po_id' => $update_id,
				':item_id' => $_REQUEST['temp_item_id'][$x],
				':po_qty' => $_REQUEST['temp_qty'][$x],
				':po_unit' => $_REQUEST['temp_unit'][$x],
				':item_price' => $_REQUEST['temp_item_price'][$x],
				':cost_price' => $_REQUEST['temp_cost_price'][$x],
				':vat' => $_REQUEST['temp_vat'][$x],
				':cgst_per' => $_REQUEST['temp_cgst'][$x],
				':cgst_val' => $_REQUEST['temp_cgst_val'][$x],
				':sgst_per' => $_REQUEST['temp_sgst'][$x],
				':sgst_val' => $_REQUEST['temp_sgst_val'][$x],
				':igst_per' => $_REQUEST['temp_vat'][$x],
				':igst_val' => $_REQUEST['temp_vat_val'][$x],
				':discount_per' => $_REQUEST['temp_discount_per'][$x],
				':discount_val' => $_REQUEST['temp_discount_val'][$x],
				':po_value' => $_REQUEST['temp_po_price'][$x],
				':tax_value' => $_REQUEST['temp_vat_val'][$x],
				':net_value' => $_REQUEST['temp_net_amt'][$x]
			);

			//print_r($data);
			$stmt1->execute($data);
			$po_value = $po_value + $_REQUEST['temp_net_amt'][$x];

		}

		/* details */

		/* details */
		$delete_details =  "DELETE FROM tbl_po_print_details WHERE po_id = '" . $update_id . "'";
		$result = $conn->prepare($delete_details);
		$result->execute();
		if (isset($_REQUEST['temp_pr_id'])) {
			for ($x = 0; $x < count($_REQUEST['temp_pr_id']); $x++) {

				//echo count($_REQUEST['temp_item_id']);
				$stmt1 = null;
				$stmt1 = $conn->prepare("INSERT INTO  tbl_po_print_details (po_id, pr_id, pr_name, pr_desc, pr_sort) 
									VALUES (:po_id, :pr_id, :pr_name, :pr_desc, :pr_sort)");

				$data = array(
					':po_id' => $update_id,
					':pr_id' => $_REQUEST['temp_pr_id'][$x],
					':pr_name' => $_REQUEST['temp_pr_name'][$x],
					':pr_desc' => $_REQUEST['temp_pr_desc'][$x],
					':pr_sort' => $_REQUEST['temp_pr_sort'][$x]
				);

				$stmt1->execute($data);
			}
		}

		/* details */
		$update_po = $conn->prepare("UPDATE  tbl_purchase_order SET po_value = :po_value WHERE po_id = :po_id");
		$data1 = array(
			':po_id' => $update_id,
			':po_value' => $po_value
		);
		$update_po->execute($data1);
	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		$_SESSION['_msg_err'] = $str;
	}
	$_SESSION['_msg'] = "Direct Purchase Order succesfully Updated..!";
	header("location:lst_direct_po.php");
	die();
}


if (isset($_POST['FINALIZE'])) {
	$update_id = $_REQUEST['txtHid'];
	if ($_REQUEST['txtHid'] != '' && $_REQUEST['txtHid'] > 0) {
		try {
			$_REQUEST['po_date'] = date("Y-m-d", strtotime($_REQUEST['po_date']));
			$po_approve_id = $dbconn->GetSingleReconrd("tbl_task_user", "user_id", "task_id", 1);
			$_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
			$_REQUEST['modify_by'] = $_SESSION['_user_id'];
			$stmt = null;
			$stmt = $conn->prepare("UPDATE  tbl_purchase_order SET po_date = :po_date, supp_id = :supp_id, po_remarks = :po_remarks, po_approve_id = :po_approve_id, 
			modify_date_time = :modify_date_time, modify_by = :modify_by, po_approve_status = :po_approve_status, po_approve_sent_dtm = :po_approve_sent_dtm, po_approve_date_time = :po_approve_date_time,
			po_approve_by = :po_approve_by, po_approve_remarks = :po_approve_remarks, po_status = :po_status WHERE po_id = :po_id");
			$data = array(
				':po_id' => $update_id,
				':po_date' => $_REQUEST['po_date'],
				':supp_id' => $_REQUEST['supp_id'],
				':po_remarks' =>  $_REQUEST['po_remarks'],
				':po_approve_id' => $po_approve_id,
				':modify_date_time' => $_REQUEST['modify_date_time'],
				':modify_by' => $_REQUEST['modify_by'],
				':po_approve_status' => '0',
				':po_approve_sent_dtm' => $_REQUEST['modify_date_time'],
				':po_approve_date_time' => '',
				':po_approve_by' => '0',
				':po_approve_remarks' => '',
				':po_status' => 3
			);

			$stmt->execute($data);

			$sql =  "DELETE FROM tbl_purchase_order_details WHERE po_id = '" . $update_id . "'";
			$result = $conn->prepare($sql);
			$result->execute();

			/* details */
			$po_value = 0;
			for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) {

				//echo count($_REQUEST['temp_item_id']);
				$item_value = $item_taxval = $item_total = 0;
				$stmt1 = null;
				$stmt1 = $conn->prepare("INSERT INTO tbl_purchase_order_details (po_id, item_id, po_qty, po_unit, item_price, cost_price, vat, cgst_per, cgst_val, sgst_per, sgst_val, igst_per, 
				igst_val, discount_per, discount_val, po_value, tax_value, net_value) 
				VALUES (:po_id, :item_id, :po_qty, :po_unit, :item_price, :cost_price, :vat, :cgst_per, :cgst_val, :sgst_per, :sgst_val, :igst_per, :igst_val, :discount_per, :discount_val, 
				:po_value, :tax_value, :net_value)");


				$data = array(
					':po_id' => $update_id,
					':item_id' => $_REQUEST['temp_item_id'][$x],
					':po_qty' => $_REQUEST['temp_qty'][$x],
					':po_unit' => $_REQUEST['temp_unit'][$x],
					':item_price' => $_REQUEST['temp_item_price'][$x],
					':cost_price' => $_REQUEST['temp_cost_price'][$x],
					':vat' => $_REQUEST['temp_vat'][$x],
					':cgst_per' => $_REQUEST['temp_cgst'][$x],
					':cgst_val' => $_REQUEST['temp_cgst_val'][$x],
					':sgst_per' => $_REQUEST['temp_sgst'][$x],
					':sgst_val' => $_REQUEST['temp_sgst_val'][$x],
					':igst_per' => $_REQUEST['temp_vat'][$x],
					':igst_val' => $_REQUEST['temp_vat_val'][$x],
					':discount_per' => $_REQUEST['temp_discount_per'][$x],
					':discount_val' => $_REQUEST['temp_discount_val'][$x],
					':po_value' => $_REQUEST['temp_po_price'][$x],
					':tax_value' => $_REQUEST['temp_vat_val'][$x],
					':net_value' => $_REQUEST['temp_net_amt'][$x]
				);

				print_r($data);
				$stmt1->execute($data);
				$po_value = $po_value + $_REQUEST['temp_net_amt'][$x];

			}

			/* details */

			/* details */
			$delete_details =  "DELETE FROM tbl_po_print_details WHERE po_id = '" . $update_id . "'";
			$result = $conn->prepare($delete_details);
			$result->execute();
			if (isset($_REQUEST['temp_pr_id'])) {
				for ($x = 0; $x < count($_REQUEST['temp_pr_id']); $x++) {

					//echo count($_REQUEST['temp_item_id']);
					$stmt1 = null;
					$stmt1 = $conn->prepare("INSERT INTO  tbl_po_print_details (po_id, pr_id, pr_name, pr_desc, pr_sort) 
								VALUES (:po_id, :pr_id, :pr_name, :pr_desc, :pr_sort)");

					$data = array(
						':po_id' => $update_id,
						':pr_id' => $_REQUEST['temp_pr_id'][$x],
						':pr_name' => $_REQUEST['temp_pr_name'][$x],
						':pr_desc' => $_REQUEST['temp_pr_desc'][$x],
						':pr_sort' => $_REQUEST['temp_pr_sort'][$x]
					);

					$stmt1->execute($data);
				}
			}

			/* details */
			$update_po = $conn->prepare("UPDATE  tbl_purchase_order SET po_value = :po_value WHERE po_id = :po_id");
			$data1 = array(
				':po_id' => $update_id,
				':po_value' => $po_value
			);
			$update_po->execute($data1);

			$_SESSION['_msg'] = "Direct Purchase Order succesfully Sent..!";
			header("location:lst_direct_po.php");
			die();
		} catch (Exception $e) {
			$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
			$_SESSION['_msg_err'] = $str;
		}
	} else {
		try {
			$_REQUEST['po_date'] = date("Y-m-d", strtotime($_REQUEST['po_date']));

			$_REQUEST['po_slno'] = $dbconn->GetMaxValue('tbl_purchase_order', 'po_slno', 'branch_id="'.$_SESSION['_user_branch'].'" AND 1 ', 1) + 1;
			$_REQUEST['po_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
			$_REQUEST['branch'] = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id='".$_SESSION['_user_branch']."' AND branch_status", 1);
			$po_approve_id = $dbconn->GetSingleReconrd("tbl_task_user", "user_id", "task_id", 1);


			$_REQUEST['po_refno'] = 'PO/' . leadingZeros($_REQUEST['po_slno'], 4) . '/BIE/'.$_REQUEST['branch'].'/' . $_REQUEST['po_finyr'];

			// $_REQUEST['payment_code'] = 'P'.leadingZeros($_REQUEST['payment_slno'],4);

			$_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
			$_REQUEST['modify_by'] = $_SESSION['_user_id'];

			$stmt = null;
			$stmt = $conn->prepare("INSERT INTO tbl_purchase_order (po_finyr, po_slno, po_refno, po_date, supp_id, po_type, po_remarks, branch_id, po_approve_id, modify_date_time, modify_by, po_approve_status, 
					po_approve_sent_dtm, po_approve_date_time, po_approve_by, po_approve_remarks, po_status)
					 VALUES (:po_finyr, :po_slno, :po_refno, :po_date, :supp_id, :po_type, :po_remarks, :branch_id, :po_approve_id, :modify_date_time, :modify_by, :po_approve_status, 
					 :po_approve_sent_dtm, :po_approve_date_time, :po_approve_by, :po_approve_remarks, :po_status)");
			$data = array(
				':po_finyr' => $_REQUEST['po_finyr'],
				':po_slno' => $_REQUEST['po_slno'],
				':po_refno' => $_REQUEST['po_refno'],
				':po_date' => $_REQUEST['po_date'],
				':supp_id' => $_REQUEST['supp_id'],
				':po_type' => 'D',
				':po_remarks' => $_REQUEST['po_remarks'],
				':branch_id' => $_SESSION['_user_branch'],
				':po_approve_id' => $po_approve_id,
				':modify_date_time' => $_REQUEST['modify_date_time'],
				':modify_by' => $_REQUEST['modify_by'],
				':po_approve_status' => '0',
				':po_approve_sent_dtm' => $_REQUEST['modify_date_time'],
				':po_approve_date_time' => '',
				':po_approve_by' => '0',
				':po_approve_remarks' => '',
				':po_status' => 3
			);
			$stmt->execute($data);
			$last_id = $conn->lastInsertId();
			/* ------------ SAVE tbl_po_details  -----------*/
			$delete_details =  "DELETE FROM tbl_purchase_order_details WHERE po_id = '" . $last_id . "'";
			$result = $conn->prepare($delete_details);
			$result->execute();

			/* details */
			$po_value = 0;
			for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) {

				//echo count($_REQUEST['temp_item_id']);
				$item_value = $item_taxval = $item_total = 0;
				$stmt1 = null;
				$stmt1 = $conn->prepare("INSERT INTO tbl_purchase_order_details (po_id, item_id, po_qty, po_unit, item_price, cost_price, vat, cgst_per, cgst_val, sgst_per, sgst_val, igst_per, 
				igst_val, discount_per, discount_val, po_value, tax_value, net_value) 
				VALUES (:po_id, :item_id, :po_qty, :po_unit, :item_price, :cost_price, :vat, :cgst_per, :cgst_val, :sgst_per, :sgst_val, :igst_per, :igst_val, :discount_per, :discount_val, 
				:po_value, :tax_value, :net_value)");


				$data = array(
					':po_id' => $last_id,
					':item_id' => $_REQUEST['temp_item_id'][$x],
					':po_qty' => $_REQUEST['temp_qty'][$x],
					':po_unit' => $_REQUEST['temp_unit'][$x],
					':item_price' => $_REQUEST['temp_item_price'][$x],
					':cost_price' => $_REQUEST['temp_cost_price'][$x],
					':vat' => $_REQUEST['temp_vat'][$x],
					':cgst_per' => $_REQUEST['temp_cgst'][$x],
					':cgst_val' => $_REQUEST['temp_cgst_val'][$x],
					':sgst_per' => $_REQUEST['temp_sgst'][$x],
					':sgst_val' => $_REQUEST['temp_sgst_val'][$x],
					':igst_per' => $_REQUEST['temp_vat'][$x],
					':igst_val' => $_REQUEST['temp_vat_val'][$x],
					':discount_per' => $_REQUEST['temp_discount_per'][$x],
					':discount_val' => $_REQUEST['temp_discount_val'][$x],
					':po_value' => $_REQUEST['temp_po_price'][$x],
					':tax_value' => $_REQUEST['temp_vat_val'][$x],
					':net_value' => $_REQUEST['temp_net_amt'][$x]
				);

				print_r($data);
				$stmt1->execute($data);
				$po_value = $po_value + $_REQUEST['temp_net_amt'][$x];

			}

			/* details */

			/* details */
			$delete_details =  "DELETE FROM tbl_po_print_details WHERE po_id = '" . $last_id . "'";
			$result = $conn->prepare($delete_details);
			$result->execute();
			if (isset($_REQUEST['temp_pr_id'])) {
				for ($x = 0; $x < count($_REQUEST['temp_pr_id']); $x++) {

					//echo count($_REQUEST['temp_item_id']);
					$stmt1 = null;
					$stmt1 = $conn->prepare("INSERT INTO  tbl_po_print_details (po_id, pr_id, pr_name, pr_desc, pr_sort) 
								VALUES (:po_id, :pr_id, :pr_name, :pr_desc, :pr_sort)");

					$data = array(
						':po_id' => $last_id,
						':pr_id' => $_REQUEST['temp_pr_id'][$x],
						':pr_name' => $_REQUEST['temp_pr_name'][$x],
						':pr_desc' => $_REQUEST['temp_pr_desc'][$x],
						':pr_sort' => $_REQUEST['temp_pr_sort'][$x]
					);

					$stmt1->execute($data);
				}
			}

			/* details */

			$update_po = $conn->prepare("UPDATE  tbl_purchase_order SET po_value = :po_value WHERE po_id = :po_id");
			$data1 = array(
				':po_id' => $last_id,
				':po_value' => $po_value
			);

			$update_po->execute($data1);
			$_SESSION['_msg'] = "Direct Purchase Order succesfully Sent..!";
			header("location:lst_direct_po.php");
			die();
		} catch (Exception $e) {
			$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
			$_SESSION['_msg_err'] = $str;
		}
	}
}

$branch_id=1;
if (isset($_REQUEST['po_id']) && $_REQUEST['po_id'] != "") {

	$result = $conn->query("SELECT * FROM tbl_purchase_order WHERE po_id = " . $_REQUEST['po_id']);
	if ($result->rowCount() > 0) {
		$get = $result->fetch(PDO::FETCH_OBJ);
		$supp_id = $get->supp_id;
		if ($get->po_date != "0000-00-00" && $get->po_date != "") {
			$po_date = date("Y-m-d", strtotime($get->po_date));
		}
		$po_remarks = $get->po_remarks;
		$branch_id = $get->branch_id;
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

<script type="text/javascript">
	function fnValidate() {

		if (isNull(document.thisForm.supp_id, "Supplier..!")) {
			return false;
		}
		if (isNull(document.thisForm.po_date, "Order date..!")) {
			return false;
		}

		var rowCount = $('#show_table tr').length;

		if (rowCount <= 2) {
			alert("Please enter atleast one item");
			return false;
		}

		document.thisForm.submit();
	}

	function fnValidate2() {

		if (isNull(document.thisForm.supp_id, "Supplier..!")) {
			return false;
		}

		var rowCount = $('#show_table tr').length;

		if (rowCount <= 2) {
			alert("Please enter atleast one item");
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

		$('#add_pr_items').click(function() {

			if ($('#pr_id').val() == "") {
				alert("Please select the Print Detail ...!");
				$('#pr_id').focus();
				return false;
			}

			if ($('#po_pr_des').val().trim() == "") {
				alert("Please enter the Description ...!");
				$('#po_pr_des').focus();
				return false;
			}
			var table = document.getElementById("show_table2");
			var rowCount = 1;
			//rowCount += table.tBodies[0].rows.length;
			var pr_id = $("#pr_id").val();
			var po_pr_des = $("#po_pr_des").val();

			var arr = [];
			var pr_id = $('#pr_id_no').val();
			$("#show_table2 tr").each(function() {
				arr.push(this.id);
			});
			if (jQuery.inArray(pr_id, arr) != -1) {
				$('#' + pr_id).remove();
			}

			$.ajax({
				type: "POST",
				url: "inc/cis_ajax/jquery_po_details.php",
				data: {
					"pr_id": pr_id,
					"po_pr_des": po_pr_des,
					'mode': 'save_pr'
				}
			}).done(function(msg) {
				//$('#show_table').html(msg);
				$("#show_table2 tbody").append(msg);
				//var n = msg.indexOf("tbody");
				//$('#po_items').val(n);
				$("#pr_id").val('').trigger('change');
				//$("#item_id").val('');
				$("#item_code").val('');
				$('#po_pr_des').val('');

			});

		});

		$('#add_items').click(function() {

			if ($('#supp_id').val() == "") {
				alert("Please Select the Supplier ...!");
				$('#supp_id').focus();
				return false;
			}
			if ($('#item_code').val() == "") {
				alert("Please enter the Item Code ...!");
				$('#item_code').focus();
				return false;
			}
			if ($('#po_qty').val() == "") {
				alert("Please enter the PO Quantity ...!");
				$('#po_qty').focus();
				return false;
			}
			if ($('#po_cost_price').val() == 0) {
				alert("Please Check the Item Price ...!");
				$('#po_cost_price').focus();
				return false;
			}
			var table = document.getElementById("show_table");
			var rowCount = 1;
			//rowCount += table.tBodies[0].rows.length;
			var po_qty = $("#po_qty").val();
			var po_cost_price = $("#po_cost_price").val();
			var po_vat = $("#po_vat").val();
			var po_unit = $("#po_unit").val();
			var po_net_amt = $("#po_net_amt").val();
			var po_price = $("#po_price").val();
			var po_dis = $("#po_dis").val();
			var po_item_price = $("#po_item_price").val();
			var po_dis_val = $("#po_dis_val").val();
			var po_cgst = $("#po_cgst").val();
			var po_sgst = $("#po_sgst").val();
			var po_vat_val = $("#po_vat_val").val();
			var po_cgst_val = $("#po_cgst_val").val();
			var po_sgst_val = $("#po_sgst_val").val();

			
			var arr = [];
			var item_id = $('#item_id_no').val();
			$("#show_table tr").each(function() {
				arr.push(this.id);
			});
			if (jQuery.inArray(item_id, arr) != -1) {
				$('#' + item_id).remove();
			}

			$.ajax({
				type: "POST",
				url: "inc/cis_ajax/jquery_po_details.php",
				data: {
					"item_id": item_id,
					"po_qty": po_qty,
					"po_cost_price": po_cost_price,
					"po_vat": po_vat,
					"po_unit": po_unit,
					"po_net_amt": po_net_amt,
					"po_price": po_price,
					"rowCount": rowCount,
					"po_dis": po_dis,
					"po_item_price": po_item_price,
					"po_dis_val": po_dis_val,
					"po_cgst": po_cgst,
					"po_sgst": po_sgst,
					"po_vat_val": po_vat_val,
					"po_cgst_val": po_cgst_val,
					"po_sgst_val": po_sgst_val,
					'mode': 'save'
				}
			}).done(function(msg) {
				//$('#show_table').html(msg);
				$("#show_table tbody").append(msg);
				//var n = msg.indexOf("tbody");
				//$('#po_items').val(n);
				$("#item_id").val('').trigger('change');
				//$("#item_id").val('');
				$("#item_code").val('');
				$('#po_qty').val('');
				$("#po_unit").val('');
				$('#po_cost_price').val('');
				$('#po_item_price').val('');
				$('#po_dis').val('');
				$('#po_price').val('');
				$('#po_vat').val('');
				$('#po_net_amt').val('');

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
					var temp_cost_price = $(this).closest('tr').find('.temp_cost_price').val();
					if (temp_cost_price > 0) {
						total_val2 = parseFloat(total_val2) + parseFloat(temp_cost_price);
					}
				});
				$('#show_table tfoot th#lastRow2').html(total_val2.toFixed(2));
				var total_val3 = 0;
				$("#show_table tr").each(function() {
					var temp_po_price = $(this).closest('tr').find('.temp_po_price').val();
					if (temp_po_price > 0) {
						total_val3 = parseFloat(total_val3) + parseFloat(temp_po_price);
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
				$('#show_table tfoot th#lastRow5').html(Math.round(total_val5).toFixed(2));

			});
		});

		$('#supp_id').change(function() {
			var supp_id = $('#supp_id').val();

			$.ajax({
				type: "POST",
				url: "inc/cis_ajax/jquery_select_po_item.php",
				data: {
					supp_id: supp_id,
					mode: 'supp_item'
				}
			}).done(function(msg) {

				$('#item_id option').remove();
				var dataArr = msg.split('#');
				$.each(dataArr, function(i, element) {
					if (dataArr[i] != "") {
						var dataArr2 = dataArr[i].split('~');
						$('#item_id').append("<option value='" + dataArr2[0] + "'>" + dataArr2[1] + "</option>");
					}
				});
				$("#s2id_item_id").select2('val', '');
				$("#item_id").trigger("liszt:updated");
				$("#po_unit").val('');
				$("#po_cost_price").val('');
				$("#po_dis").val('');
				$("#po_item_price").val('');
				$("#po_vat").val('');
			});
		}).trigger('change');

		$('#item_id').change(function() {
			var item_id = $('#item_id').val();
			$('#po_qty').val('');
			$('#po_price').val('');
			$('#po_net_amt').val('');
			
			$.ajax({
				type: "POST",
				url: "inc/cis_ajax/jquery_select_po_item.php",
				data: {
					item_id: item_id,
					mode: 'item_val'
				}
			}).done(function(msg) {
				string = msg.split("~");
				$("#item_id").val(string[1]);
				$("#po_cost_price").val(string[2]);
				$("#po_vat").val(string[3]);
				$("#po_cgst").val(string[9]);
				$("#po_sgst").val(string[10]);
				$("#po_unit").val(string[4]);
				$('#item_id_no').val(item_id);
				$('#item_order_min_qty').val(string[5]);
				$('#po_dis').val(string[6]);
				$('#po_sel_price').val(string[8]);
				$('#po_item_price').val(string[7]);
				if (string[5] == undefined) {
					$('#min_qty').html('');
				} else {
					$('#min_qty').html(' ( MOQ - ' + string[5] + ' )');
				}
			});
		});

		$('#pr_id').change(function() {
			var pr_id = $('#pr_id').val();
			$('#pr_id_no').val(pr_id);
		});

		$('#po_qty').change(function() {
			var qty = parseFloat($(this).val());
			var item_id = $("#item_id").val();
			var po_unit_price = $("#po_cost_price").val();
			var po_vat = $("#po_vat").val();
			var po_cgst = $("#po_cgst").val();
			var po_sgst = $("#po_sgst").val();
			var po_dis = $("#po_dis").val();
			var item_order_min_qty = parseFloat($("#item_order_min_qty").val());
			if (qty < item_order_min_qty) {
				alert("Please check the Quantity (MOQ " + item_order_min_qty + ")");
				$(this).val('');
				$('#po_price').val('');
		    	$('#po_net_amt').val('');
				return false;
			}
			
			
		    item_price = po_unit_price * qty;
	        tax_val = 0;
	        cost_price = item_price;

	        //if(po_dis > 0){
		      //  dis_val = ((item_price * po_dis) / 100);
		        //cost_price = item_price - dis_val;
	        //}

        	if(po_vat > 0){
        		tax_igst = ((cost_price * po_vat) / 100);
        	}
        	if(po_cgst > 0 && po_sgst > 0){
        		tax_cgst = ((cost_price * po_cgst) / 100);
        		tax_sgst = ((cost_price * po_sgst) / 100);
        		tax_val = tax_cgst +tax_sgst;
        	}
        	
        	net_val = cost_price + tax_val;
        
            $('#po_price').val(cost_price.toFixed(2));
        	$('#po_net_amt').val(net_val.toFixed(2));
        	$('#po_cgst_val').val(tax_cgst.toFixed(2));
        	$('#po_sgst_val').val(tax_sgst.toFixed(2));
        	$('#po_vat_val').val(tax_sgst.toFixed(2));
        	$('#po_dis_val').val(dis_val.toFixed(2));
        	
		});

	});



	function remove_item(auto_id) {
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
			var temp_cost_price = $(this).closest('tr').find('.temp_cost_price').val();
			if (temp_cost_price > 0) {
				total_val2 = parseFloat(total_val2) + parseFloat(temp_cost_price);
			}
		});
		$('#show_table tfoot th#lastRow2').html(total_val2.toFixed(2));
		var total_val3 = 0;
		$("#show_table tr").each(function() {
			var temp_po_price = $(this).closest('tr').find('.temp_po_price').val();
			if (temp_po_price > 0) {
				total_val3 = parseFloat(total_val3) + parseFloat(temp_po_price);
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
		$('#show_table tfoot th#lastRow5').html(Math.round(total_val5).toFixed(2));
	}
	function remove_item_print_details(prd_id){
		$('#prd' + prd_id).remove();


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
								<h6 class="card-title"> Direct Purchase Order</h6>
								<div class="header-elements">
									<div class="list-icons">
										<a class="list-icons-item" href="lst_direct_po.php" title="Direct Purchase Order List"><i class="icon-arrow-left52 mr-2"></i></a>
										<a class="list-icons-item" data-action="fullscreen"></a>
									</div>
								</div>

							</div>
							<form name='thisForm' class="form-horizontal" method='POST' action="">
								<input type="hidden" name="po_items" id="po_items" value="-1">
								<input type="hidden" name="gst" id="gst" value="">
								<input type="hidden" name="item_hsn" id="item_hsn" value="">
								<fieldset>
									<?php
									if ($_REQUEST['po_id'] != "") {
										$po_no = leadingZeros($dbconn->GetSingleReconrd('tbl_purchase_order', 'po_slno', 'po_id', $_REQUEST['po_id']), 4);
									} else {
										$po_no = leadingZeros($dbconn->GetMaxValue('tbl_purchase_order', 'po_slno', ' 1 ', 1) + 1, 4);
										// $po_no = leadingZeros($dbconn->GetMaxValue('tbl_purchase_order', 'po_slno', 'branch_id="'.$_SESSION['_user_branch'].'" AND 1 ', 1) + 1, 4);
									}
									?>
									<div class="card-body">
										<legend class="font-weight-semibold pb-0 mb-2"><span class="po_title">PO<?php echo $po_no; ?></span></legend>
										<div class="form-group">
											<div class="row">
												<input type="hidden" name="po_no" id="po_no" class="form-control" value="<?php echo $po_no; ?>" />
												<label class="col-lg-1 col-form-label">Supplier <span class="text-mandatory"> *</span></label>
												<div class="col-lg-3">
													
													<select name="supp_id" id="supp_id" data-placeholder="Choose a Supplier.." class="form-control select-search">
														<option value=""></option>
														<?php
														$dbconn = new dbhandler();
														echo $dbconn->fnFillComboFromTable_Where("supp_id", "supp_name", "mst_supplier_new", "supp_id", " WHERE  supp_status = '1' AND supp_type = 'S' AND supp_approve_status = '1'") ?>
													</select>
													<script>
														document.thisForm.supp_id.value = "<?php echo $get->supp_id; ?>";
													</script>
												</div>

												<label class="col-lg-1 col-form-label">Date <span class="text-mandatory"> *</span></label>
												<div class="col-lg-3">
													<input type="date" name="po_date" id="po_date" class="form-control" maxlength="75" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $po_date; ?>" placeholder="Date" />
												</div>
											</div>
										</div>
										<div class="form-group row">



										</div>

										<div class="row">
											<div class="col-md-12">
												<fieldset>
													<legend class="font-weight-semibold"><i class="icon-cart mr-2"></i>Purchase Order Details</legend>
													<div class="form-group row">
														<div class="form-group col-md-4">
															<label>Item <span class="text-mandatory">*</span></label>
															<select data-placeholder="Choose a Item.." name="item_id" id="item_id" class="form-control select-search">
																<option value="">-- Select Item --</option>
																<?php
																//echo $dbconn->fnFillComboFromTable_Where("item_id", "item_code", "tbl_item_details", "item_id", " WHERE item_status = 1");
																?>
															</select>
															<input type="hidden" name="item_id_no" id="item_id_no">
															<input type="hidden" name="item_order_min_qty" id="item_order_min_qty">
														</div>
														<div class="form-group pl-0 col-md-2">
															<label>Quantity <span class="text-mandatory"> * </span><span id="min_qty" style="color:red"></span></label>
															<input type="text" name="po_qty" id="po_qty" class="form-control number_only_dot" maxlength="9">
														</div>
														<div class="form-group pl-0 col-md-1">
															<label>Unit <span class="text-mandatory"></span></label>
															<input type="text" tabIndex="-1" readonly name="po_unit" id="po_unit" class="form-control">
														</div>
														<div class="form-group pl-0 col-md-2">
															<label>Item Price <span class="text-mandatory"></span></label>
															<input type="text" tabIndex="-1" readonly name="po_item_price" id="po_item_price" class="form-control number_only">
														</div>
														<div class="form-group pl-0 col-md-1">
															<label>Discount (%)<span class="text-mandatory"></span></label>
															<input type="text" readonly tabIndex="-1" name="po_dis" id="po_dis" class="form-control">
															<input type="hidden" readonly tabIndex="-1" name="po_dis_val" id="po_dis_val" class="form-control">
														</div>
														<div class="form-group pl-0 col-md-2">
															<label>Cost Price <span class="text-mandatory"></span></label>
															<input type="text" tabIndex="-1" readonly name="po_cost_price" id="po_cost_price" class="form-control number_only">
														</div>
													</div>
													<div class="form-group row">
														<div class="form-group pl-2 col-md-2">
															<label>Amount <span class="text-mandatory"></span></label>
															<input type="text" tabIndex="-1" maxlength="7" readonly name="po_price" id="po_price" maxlength="7" class="form-control">
														</div>
														<div class="form-group  pl-0 col-md-1">
															<label>GST (%)<span class="text-mandatory"></span></label>
															<input type="text" readonly tabIndex="-1" name="po_vat" id="po_vat" class="form-control">
															<input type="hidden" readonly tabIndex="-1" name="po_vat_val" id="po_vat_val" class="form-control">
															<input type="hidden" readonly tabIndex="-1" name="po_cgst" id="po_cgst" class="form-control">
															<input type="hidden" readonly tabIndex="-1" name="po_cgst_val" id="po_cgst_val" class="form-control">
															<input type="hidden" readonly tabIndex="-1" name="po_sgst" id="po_sgst" class="form-control">
															<input type="hidden" readonly tabIndex="-1" name="po_sgst_val" id="po_sgst_val" class="form-control">
														</div>
														<div class="form-group pl-0 col-md-2">
															<label>Net Amount <span class="text-mandatory"></span></label>
															<input type="text" readonly tabIndex="-1" name="po_net_amt" id="po_net_amt" class="form-control">
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
												<table class="table table-xs table-bordered table-dets-responsive" style="font-size: small !important;">
													<thead>
														<tr class="bg-teal">
															<th width="10%">Item Code</th>
															<th width="20%">Supplier Item Code</th>
															<th width="20%">Description</th>
															<th width="5%">Quantity</th>
															<th width="5%">Unit</th>
															<th width="5%">Item Price</th>
															<th width="5%">Discount (%)</th>
															<th width="5%">Cost Price</th>
															<th width="5%">Amount</th>
															<th width="5%">GST (%)</th>
															<th width="5%">Net Amount</th>
															<th width="2%"><i class=" icon-cog6 mr-2"></i></th>
														</tr>
													</thead>
													<tbody>
														<?php if ($_REQUEST['po_id'] != '') {

															$dets_sql = "SELECT * FROM tbl_purchase_order_details WHERE po_id = " . $_REQUEST['po_id'];
															$result_dets = $conn->query($dets_sql);
															$rowCnt = $result_dets->rowCount();
															if ($result_dets->rowCount() > 0) {
																$sno = 1;
																$tot_po_qty = $tot_cost_price = $tot_po_value = $tot_net_value = 0;
																while ($itm = $result_dets->fetch()) {
																	$item_desciption = $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_id", $itm->item_id);
																	$item_purchase_code = $dbconn->GetSingleReconrd("tbl_item_details", "item_purchase_code", "item_id", $itm->item_id);
																	$item_code = $dbconn->GetSingleReconrd("tbl_item_details", "supp_item_code", "item_id", $itm->item_id);
																	echo '
																	<tr id="' . $itm->item_id . '" >
																		<td>' . $item_purchase_code . '
																			<input type="hidden" class="temp_item_id" name="temp_item_id[]" value="' . $itm->item_id . '" />
																		</td>
																		<td>' . $item_code . '
																			<input type="hidden" class="item_code" name="item_code[]" value="' . $item_code . '" />
																		</td>
																		<td>' . $item_desciption . '
																			<input type="hidden" class="item_desciption" name="item_desciption[]" value="' . $item_desciption . '" />
																		</td>
																		<td class="text-right">' . $itm->po_qty . '
																			<input type="hidden" class="temp_qty" name="temp_qty[]" value="' . $itm->po_qty . '" />
																		</td>
																		<td class="text-right">' . $itm->po_unit . '
																			<input type="hidden" class="temp_unit" name="temp_unit[]" value="' . $itm->po_unit . '" />
																		</td>
																		<td class="text-right">' . $itm->item_price . '
																			<input type="hidden" class="temp_item_price" name="temp_item_price[]" value="' . $itm->item_price . '" />
																		</td>
																		<td class="text-right">' . $itm->discount_per . '
																			<input type="hidden" class="temp_discount_per" name="temp_discount_per[]" value="' . $itm->discount_per . '" />
																			<input type="hidden" class="temp_discount_val" name="temp_discount_val[]" value="' . $itm->discount_val . '">
																		</td>
																		<td class="text-right">' . $itm->cost_price . '
																			<input type="hidden" class="temp_cost_price" name="temp_cost_price[]" value="' . $itm->cost_price . '" />
																		</td>
																		<td class="text-right">' . $itm->po_value . '
																			<input type="hidden" class="temp_po_price" name="temp_po_price[]" value="' . $itm->po_value . '" />
																		</td>
																		<td class="text-right">' . $itm->vat . '
																			<input type="hidden" class="temp_vat" name="temp_vat[]" value="' . $itm->vat . '" />
																			<input type="hidden" class="temp_vat_val" name="temp_vat_val[]" value="' . $itm->igst_val . '" />
																			<input type="hidden" class="temp_cgst" name="temp_cgst[]" value="' . $itm->cgst_per . '" />
																			<input type="hidden" class="temp_cgst_val" name="temp_cgst_val[]" value="' . $itm->cgst_val . '" />
																			<input type="hidden" class="temp_sgst" name="temp_sgst[]" value="' . $itm->sgst_per . '" />
																			<input type="hidden" class="temp_sgst_val" name="temp_sgst_val[]" value="' . $itm->sgst_val . '" />
																		</td>
																		<td class="text-right">' .$itm->net_value . '
																			<input type="hidden" class="temp_net_amt" name="temp_net_amt[]" value="' . $itm->net_value . '" />
																		</td>
																		<td class="text-center">
																			<a href="javascript:remove_item(' . $itm->item_id . ');" class="" rel="' . $itm->item_id . '"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a>
																		</td>
																	</tr>';
																	$sno++;
																	$tot_po_qty += $itm->po_qty;
																	$tot_cost_price += $itm->cost_price;
																	$tot_po_value += $itm->po_value;
																	$tot_net_value += $itm->net_value;
																}
															}
														}
														?>
													</tbody>
													<tfoot>
														<tr>
															<th colspan="3" class="text-right">Total : </th>
															<!-- <th class="text-right" id="lastRow1"><?php //echo round($tot_po_qty); ?></th> -->
															<th></th>
															<th></th>
															<th></th>
															<th></th>
															<th></th>
															<th></th>

															<!-- <th class="text-right" id="lastRow2"><?php echo $tot_cost_price; ?></th>
															<th class="text-right" id="lastRow3"><?php echo $tot_po_value; ?></th> -->
															<th class="text-right"></th>
															<th class="text-right" id="lastRow5"><?php echo number_format(round($tot_net_value),2, ".", ""); ?></th>
															<th class="text-right"></th>
														</tr>
													</tfoot>
												</table>
											</div>
										</div>

										<div class="row pt-3">
											<div class="col-md-12">
												<fieldset>
													<legend class="font-weight-semibold"><i class="icon-printer4 mr-2"></i>Purchase Order Print Details</legend>
													<div class="form-group row">
														<div class="form-group col-md-4">
															<label>Print Detail <span class="text-mandatory">*</span></label>
															<select data-placeholder="Choose a Item.." name="pr_id" id="pr_id" class="form-control select-search">
																<option value="">-- Select Detail --</option>
																<?php
																echo $dbconn->fnFillComboFromTable_Where("pr_id", "pr_name", "mst_po_print", "pr_id", " WHERE 1 = 1");
																?>
															</select>
															<input type="hidden" name="pr_id_no" id="pr_id_no">
														</div>
														<div class="form-group pl-0 col-md-6">
															<label>Description <span class="text-mandatory"> * </span></label>
															<input type="text" name="po_pr_des" id="po_pr_des" class="form-control" maxlength="250">
														</div>
														<div class="form-group pl-0" id="item_indv_add_btn">
															<button class="btn btn-success mr-2 mt-4 pt-1" id="add_pr_items" name="add_pr_items" type="button"> +</button>
														</div>
													</div>
												</fieldset>
											</div>
										</div>

										<div class="row">
											<div id="show_table2" class="col-md-12">
												<table class="table table-xs table-bordered table-dets-responsive">
													<thead>
														<tr class="bg-teal">
															<th width="10%">Print Detail</th>
															<th width="20%">Description</th>
															<th width="2%"><i class=" icon-cog6 mr-2"></i></th>
														</tr>
													</thead>
													<tbody>
														<?php if ($_REQUEST['po_id'] != '') {

															$dets_sql = "SELECT * FROM tbl_po_print_details WHERE po_id = " . $_REQUEST['po_id'];
															$result_dets = $conn->query($dets_sql);
															$rowCnt = $result_dets->rowCount();
															if ($result_dets->rowCount() > 0) {
																$sno = 1;
																while ($itm = $result_dets->fetch()) {

																	echo '
																	<tr id="prd' . $itm->pr_id . '" >
																		<td>' . $itm->pr_name . '
																			<input type="hidden" class="temp_pr_name" name="temp_pr_name[]" value="' . $itm->pr_name . '" />
																			<input type="hidden" class="temp_pr_sort" name="temp_pr_sort[]" value="' . $itm->pr_sort . '" />
																			<input type="hidden" class="temp_pr_id" name="temp_pr_id[]" value="' . $itm->pr_id . '" />
																		</td>
																		<td>' . $itm->pr_desc . '
																			<input type="hidden" class="temp_pr_desc" name="temp_pr_desc[]" value="' . $itm->pr_desc . '" />
																		</td>
																		<td class="text-center">
																			<a href="javascript:remove_item_print_details(' . $itm->pr_id . ');" class="" rel="' . $itm->pr_id . '"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a>
																		</td>
																	</tr>';
																	$sno++;
																}
															}
														}
														?>
													</tbody>
												</table>
											</div>
										</div>

										<script type="text/javascript">
											//remove_item(0);
										</script>


										<div class="form-group pl-0 col-md-12 pt-4">
											<label>Purchase Order Note <span class="text-mandatory"></span></label>
											<textarea name="po_remarks" maxlength="500" id="po_remarks" class="form-control"><?php echo $get->po_remarks; ?></textarea>
										</div>

									</div>	
									
										
											<div class="card-footer text-center">
												<?php

												if (isset($_REQUEST["po_id"]) && $_REQUEST["po_id"] != '') { ?>
													<INPUT class="btn btn-primary mr-2" type="submit" name="UPDATE" value="UPDATE" onclick="return fnValidate2();">

													<INPUT class="btn btn-success mr-2" type="submit" name="FINALIZE" value="Send for Approval" onclick="return fnValidate();">

													<INPUT class="btn btn-light" type="button" name="Cancel" value="Cancel" onClick="javascript:window.location.href='lst_direct_po.php'">
													<input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['po_id']; ?>">
												<?php } else { ?>
													<INPUT class="btn btn-primary mr-2" type="submit" name="Draft" id="Draft" value="Draft" onclick="return fnValidate2();">
													<INPUT class="btn btn-success mr-2" type="submit" name="FINALIZE" value="Send for Approval" onclick="return fnValidate();">
													<INPUT class="btn btn-light" type="button" name="Cancel" value="Cancel" onClick="javascript:window.location.href='lst_direct_po.php'">
													<input type="hidden" name="txtHid" value="0">
												<?php } ?>
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