<?php
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
} else if ($_POST['mode'] == "multi_uom")
	$item_id = $_POST['item_id'];

$SQL = "SELECT * FROM tbl_multiuom_itemprice_history WHERE branch_id = '" . $user_branch . "' AND new_item_uom = '" . $new_item_uom . "' ";
$result = $conn->query($SQL);
if ($result->rowCount() > 0) {
	$obj1 = $result->fetch(PDO::FETCH_OBJ);

	echo trim("$obj1->new_price~$obj1->new_discount~$obj1->new_cost_price~$obj1->new_selling_price~$obj1->new_margin_percent~$obj1->new_min_discount~$obj1->new_max_discount");
}
