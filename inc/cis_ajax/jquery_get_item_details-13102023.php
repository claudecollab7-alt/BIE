<?php
ob_start();
session_start();
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
$conn = new dbconnect();
$dbconn= new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);


	$item_id = $_POST['item_id'];

	$SQL = "SELECT * FROM tbl_item_details WHERE item_status = '1' AND item_id='".$item_id."' ";
	$result = $conn->query($SQL);
	if ($result->rowCount() > 0)
	{
		$obj1 = $result->fetch(PDO::FETCH_OBJ);
		
		echo trim("$obj1->item_price~$obj1->item_discount~$obj1->item_cost_price~$obj1->item_selling_price~$obj1->item_min_qty~$obj1->item_max_qty~$obj1->item_order_min_qty~$obj1->item_curr_stock~$obj1->item_uom~$obj1->item_hsn");
				
	}
	
?>