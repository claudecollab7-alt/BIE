<?php

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn= new dbhandler();

ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);



	//if(isset($_POST['itemtypeid']) && $_POST['mode'] == 'supp_item')
	if(isset($_POST['itemtypeid']))
	{
		$itemtypeid = $_POST["itemtypeid"];
		$stmt = null;
		$stmt = $conn->prepare("SELECT * FROM tbl_item_details as a  WHERE a.item_type = '".$itemtypeid."' ");
		//echo"<pre>";print_r($stmt);exit;
		$stmt->execute();
		$string = "";
		$string .= "" . "~" . "-- Select Item --" . "#";
		$count = $stmt->rowCount();
		if($count > 0)
		{
			while($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$string .= $row['item_id'] . "~<b>" .$row['item_purchase_code']."</b> | ".$row['item_desciption'] . "#";
			}
		}
		echo $string;
	}	

 //    if(isset($_POST['item_id']) && $_POST['mode'] == 'item_val')
	// {
	// 	$item_id = $_POST["item_id"];
	// 	$stmt = null;
	// 	$stmt = $conn->prepare("SELECT * FROM tbl_item_details as a  WHERE a.item_id = ".$item_id);
	// 	$stmt->execute();
	// 	$string = "";
	// 	$count = $stmt->rowCount();
	// 	if($count > 0)
	// 	{
	// 		while($row = $stmt->fetch(PDO::FETCH_ASSOC))
	// 		{
 //                $gst  = $dbconn->GetSingleReconrd("mst_hsn","igst","hsn_status = '1' AND hsn_id",$row['item_hsn']);
 //                $cgst  = $dbconn->GetSingleReconrd("mst_hsn","cgst","hsn_status = '1' AND hsn_id",$row['item_hsn']);
 //                $sgst  = $dbconn->GetSingleReconrd("mst_hsn","sgst","hsn_status = '1' AND hsn_id",$row['item_hsn']);
 //                $cost_price = $row['item_cost_price'];
 //                $sname = $row['item_desciption'];
 //                $scode = $row['item_code'];
 //                $sid = $row['item_id'];
 //                $qty = $row['item_curr_stock'];
 //                $item_order_min_qty = $row['item_order_min_qty'];
 //                $item_discount = $row['item_discount'];
 //                $item_price = $row['item_price'];
 //                $item_selling_price = $row['item_selling_price'];
 //                $uom = $dbconn->GetSingleReconrd("mst_uom","uom_code","uom_status='1' AND uom_id",$row['item_uom']);
 //                //echo "$sname - $scode | $qty ~ $sid ~ $cost_price ~ $gst ~ $uom\n";
	// 			$string .= "$sname - $scode | $qty ~ $sid ~ $cost_price ~ $gst ~ $uom\n ~ $item_order_min_qty ~ $item_discount ~ $item_price ~ $item_selling_price ~ $cgst ~ $sgst";
	// 		}
	// 	}
	// 	echo $string;
	// }	

	// if(isset($_POST['itemtypeid']) && $_POST['mode'] == 'cus_branch')
	// {

	// 	$itemtypeid = $_POST["itemtypeid"];
	// 	$stmt = null;
	// 	$stmt = $conn->prepare("SELECT * FROM mst_customer_branch as a WHERE a.item_type = ".$itemtypeid);
	// 	$stmt->execute();
	// 	$string = "";
	// 	$string .= "" . "~" . "-- Select Branch --" . "#";
	// 	$count = $stmt->rowCount();
	// 	if($count > 0)
	// 	{
	// 		while($row = $stmt->fetch(PDO::FETCH_ASSOC))
	// 		{
	// 			$string .= $row['branch_id'] . "~" .$row['branch_name']." | ".$row['branch_add1']." | ".$row['branch_add2']. "#";
	// 		}
	// 	}
	// 	echo $string;
	// }	

?>