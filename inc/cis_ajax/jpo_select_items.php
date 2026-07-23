<?php
ob_start();
session_start();
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
$conn = new dbconnect();
$dbconn= new dbhandler();
$q = strtolower($_GET["q"]);
if (!$q) return;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/*
if(isset($_GET['id']))
{
	$supp_id = $_GET['id'];

	$stmt = null;
	$qry = "SELECT * FROM tbl_item_details as a  WHERE a.supp_id LIKE '%".$supp_id."%' AND (a.item_desciption like '$q%' OR a.item_code like '$q%' OR a.item_purchase_code like '$q%')";
	$stmt = $conn->prepare($qry);
	$stmt->execute();
	$count = $stmt->rowCount();
	if($count > 0)
	{
		while($row = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			$gst  = $dbconn->GetSingleReconrd("mst_hsn","igst","hsn_status = '1' AND hsn_id",$row['item_hsn']);
			$cost_price = $row['item_cost_price'];
			$sname = $row['item_desciption'];
			$scode = $row['item_code'];
			$sid = $row['item_id'];
			$qty = $row['item_curr_stock'];
			$uom = $dbconn->GetSingleReconrd("mst_uom","uom_code","uom_status='1' AND uom_id",$row['item_uom']);
			echo "$sname - $scode | $qty ~ $sid ~ $cost_price ~ $gst ~ $uom\n";
			// echo "$cus_prefix $cus_name ~ $cus_mobile |$cus_id\n";
		}
	}
}*/

if(isset($_GET["q"]))
	{	
		
		//$srchQuery = "SELECT * FROM bar_mst_items WHERE rec_del_status=0 AND
		//		( item_name LIKE '%".$_GET["q"]."%' or item_code LIKE '%".$_GET["q"]."%' )
		//			ORDER BY item_name ASC ";
		$srchQuery = "SELECT * FROM tbl_item_details as a  WHERE a.supp_id LIKE '%".$supp_id."%' AND (a.item_desciption like '$q%' OR a.item_code like '$q%' OR a.item_purchase_code like '$q%')";

		//echo $srchQuery;
		
		$srchRecords = $conn->query($srchQuery);
		
		$response = array();
		
		while ($row = $srchRecords->fetch()) 
		{
			$temp_array = array();
			$temp_array['value'] = $row->item_name;
			$temp_array['current_stock'] = $row->item_curr_stock;
			$temp_array['item_action_zero'] = $row->item_action_zero; /*A-allow, P-block*/
			$temp_array['id'] = $row->item_id;
			$temp_array['label'] = $row->item_code.' - '.$row->item_name.'';
			$temp_array['purchase_price'] = $row->item_sales_price;
			$response[] = $temp_array;
		}
		
		echo json_encode($response, true);
	}

?>

