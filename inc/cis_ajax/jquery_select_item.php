<?php
ob_start();

session_start();
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);

if (isset($_POST['supp_id']) && $_POST['mode'] == 'supp_item') {
	$supp_id = $_POST["supp_id"];
	$stmt = null;
	$stmt = $conn->prepare("SELECT * FROM tbl_item_details as a LEFT JOIN tbl_supp_items as b on a.item_id = b.item_id WHERE b.supp_id LIKE '%" . $supp_id . "%' ");
	$stmt->execute();
	$string = "";
	$string .= "" . "~" . "-- Select Item --" . "#";
	$count = $stmt->rowCount();
	if ($count > 0) {
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$string .= $row['item_id'] . "~<b>" . $row['item_purchase_code'] . "</b> | " . $row['item_desciption'] . "#";
		}
	}
	echo $string;
}

if (isset($_POST['item_id']) && $_POST['mode'] == 'item_val') {
	$item_id = $_POST["item_id"];
	$stmt = null;
	$stmt = $conn->prepare("SELECT * FROM tbl_item_details as a  WHERE a.item_id = " . $item_id);
	$stmt->execute();
	$string = "";
	$count = $stmt->rowCount();
	if ($count > 0) {
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$gst  = $dbconn->GetSingleReconrd("mst_hsn", "igst", "hsn_status = '1' AND hsn_id", $row['item_hsn']);
			$cgst  = $dbconn->GetSingleReconrd("mst_hsn", "cgst", "hsn_status = '1' AND hsn_id", $row['item_hsn']);
			$sgst  = $dbconn->GetSingleReconrd("mst_hsn", "sgst", "hsn_status = '1' AND hsn_id", $row['item_hsn']);

			$field_name = $dbconn->GetSingleReconrd("mst_branch", "branch_stock_field", "branch_id", $_SESSION['_user_branch']);
			$branch_item_selling_price = $dbconn->GetSingleReconrd("mst_branch", "branch_item_selling_price", "branch_id", $_SESSION['_user_branch']);
			$item_order_min_qty = $dbconn->GetSingleReconrd("mst_branch", "branch_item_maq", "branch_id", $_SESSION['_user_branch']);
			$branch_item_price = $dbconn->GetSingleReconrd("mst_branch", "branch_item_price", "branch_id", $_SESSION['_user_branch']);
			$branch_item_discount = $dbconn->GetSingleReconrd("mst_branch", "branch_item_discount", "branch_id", $_SESSION['_user_branch']);
			$branch_item_cost_price = $dbconn->GetSingleReconrd("mst_branch", "branch_item_cost_price", "branch_id", $_SESSION['_user_branch']);

			$branch_item_min_discount = $dbconn->GetSingleReconrd("mst_branch", "branch_item_min_discount", "branch_id", $_SESSION['_user_branch']);
			$branch_item_max_discount = $dbconn->GetSingleReconrd("mst_branch", "branch_item_max_discount", "branch_id", $_SESSION['_user_branch']);


			$curr_stock = $dbconn->GetSingleReconrd("tbl_item_stock", "$field_name", "item_id", $item_id);
			$item_order_min_qty = $dbconn->GetSingleReconrd("tbl_item_stock", "$item_order_min_qty", "item_id", $item_id);
			$item_selling_price = $dbconn->GetSingleReconrd("tbl_item_stock", "$branch_item_selling_price", "item_id", $item_id);
			$item_price = $dbconn->GetSingleReconrd("tbl_item_stock", "$branch_item_price", "item_id", $item_id);
			$item_discount = $dbconn->GetSingleReconrd("tbl_item_stock", "$branch_item_discount", "item_id", $item_id);
			$item_cost_price  = $dbconn->GetSingleReconrd("tbl_item_stock", "$branch_item_cost_price", "item_id", $item_id);

			$min_discount = $dbconn->GetSingleReconrd("tbl_item_stock", "$branch_item_min_discount", "item_id", $item_id);
			$max_discount = $dbconn->GetSingleReconrd("tbl_item_stock", "$branch_item_max_discount", "item_id", $item_id);

			$cost_price = $item_cost_price;
			$sname = $row['item_desciption'];
			$scode = $row['item_code'];
			$sid = $row['item_id'];
			$qty = $curr_stock;
			$item_order_min_qty = $item_order_min_qty;
			$item_discount = $item_discount;
			$item_price = $item_price;
			$item_selling_price = $item_selling_price;
			$item_type = $row['item_type'];
			$uom = $dbconn->GetSingleReconrd("mst_uom", "uom_code", "uom_status='1' AND uom_id", $row['item_uom']);
			//echo "$sname - $scode | $qty ~ $sid ~ $cost_price ~ $gst ~ $uom\n";
			$string .= "$sname - $scode | $qty ~ $sid ~ $cost_price ~ $gst ~ $uom\n ~ $item_order_min_qty ~ $item_discount ~ $item_price ~ $item_selling_price ~ $cgst ~ $sgst ~ $qty ~ $item_type ~ $min_discount ~ $max_discount";
		}
	}
	echo $string;
}

if (isset($_POST['supp_id']) && $_POST['mode'] == 'cus_branch') {

	$supp_id = $_POST["supp_id"];
	$stmt = null;
	$stmt = $conn->prepare("SELECT * FROM mst_customer_branch as a WHERE a.supp_id = " . $supp_id);
	$stmt->execute();
	$string = "";
	$string .= "" . "~" . "-- Select Branch --" . "#";
	$count = $stmt->rowCount();
	if ($count > 0) {
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$string .= $row['branch_id'] . "~" . $row['branch_name'] . " | " . $row['branch_add1'] . " | " . $row['branch_add2'] . "#";
		}
	}

	$discount_apply = $dbconn->GetSingleReconrd("mst_supplier_new", "discount_apply", "supp_id", $supp_id);
	// echo $string;
	 echo $discount_apply . "||" . $string;
}
