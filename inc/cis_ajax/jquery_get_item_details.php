<?php
// 2026-11-11 multi_uom mode reads tbl_item_uom_price for this item only
ob_start();
session_start();
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$user_branch = $_POST['branch_id'];
$new_item_uom = $_POST['new_item_uom'];
if (isset($_POST['branch_id']) && $_POST['mode'] == "single_uom") {
	$result_branch = $conn->query("SELECT * FROM mst_branch WHERE branch_id = '" . $user_branch . "'");
	// $auto_id = $dbconn->GetMaxValue("tbl_item_stock", "item_id", "item_id", $_REQUEST['item_id']);
	$update_id = $_POST['item_id'];

	if ($result_branch->rowCount() > 0) {
		$branch_data = $result_branch->fetch(PDO::FETCH_OBJ);

		if ($update_id != "") {

			$result = $conn->query("SELECT 
			" . $branch_data->branch_item_maq . " as item_max_qty ,
			" . $branch_data->branch_stock_field . " as item_curr_stock, 
			" . $branch_data->branch_item_price . " as item_price, 
			" . $branch_data->branch_item_discount . " as item_discount, 
			" . $branch_data->branch_item_min_discount . " as item_min_discount, 
			" . $branch_data->branch_item_max_discount . " as item_max_discount, 
			" . $branch_data->branch_item_cost_price . " as item_cost_price, 
			" . $branch_data->branch_item_selling_price . " as item_selling_price, 
			" . $branch_data->branch_item_margin . " as margin_percent, 
			" . $branch_data->branch_item_msq . " as item_min_qty, 
			" . $branch_data->branch_item_moq . " as item_order_min_qty 
			FROM tbl_item_stock WHERE item_id = " . $update_id);
			if ($result->rowCount() > 0) {
				$obj1 = $result->fetch(PDO::FETCH_OBJ);
				// $item_id = $obj->item_id;
				// $new_item_uom = $obj->new_item_uom;
				// $new_item_hsn = $obj->new_item_hsn;

				echo trim("$obj1->item_price~$obj1->item_discount~$obj1->item_cost_price~$obj1->item_selling_price~$obj1->item_min_qty~$obj1->item_max_qty~$obj1->item_order_min_qty~$obj1->item_curr_stock~$obj1->margin_percent~$obj1->item_uom~$obj1->item_hsn~$obj1->item_min_discount~$obj1->item_max_discount");
			}
		}
	}
} else if ($_POST['mode'] == "multi_uom") {
	// 2026-11-11 this item's price in that unit for the branch (was any item's price; ran for every mode - no braces)
	$uom_id = $dbconn->GetSingleReconrd("mst_uom", "uom_id", "uom_name", $new_item_uom);
	$obj1 = fnItemUomPrice($conn, $_POST['item_id'], $uom_id, $user_branch);
	if ($obj1) {
		echo trim("$obj1->item_price~$obj1->item_discount~$obj1->cost_price~$obj1->selling_price~$obj1->margin_percent~0~0");
	}
}
