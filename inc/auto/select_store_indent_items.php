<?php
ob_start();
session_start();
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn= new dbhandler();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$q = strtolower($_GET["q"]);

	if(isset($_GET["q"]))
	{	
		
	//echo $srchQuery;
		
       $field_name = $dbconn->GetSingleReconrd("mst_branch","branch_stock_field","branch_id",$_SESSION['_user_branch']);
	   $branch_item_moq = $dbconn->GetSingleReconrd("mst_branch","branch_item_maq","branch_id",$_SESSION['_user_branch']);


	//    $branch_item_curr_stock = $dbconn->GetSingleReconrd("tbl_item_stock", "$field_name", "item_id", $_GET["q"]);


		$srchQuery = "SELECT * FROM tbl_item_details  WHERE item_status=1
		AND (item_code like '%$q%' OR item_desciption like '%$q%' OR item_purchase_code like '%$q%')";
		
		$srchRecords = $conn->query($srchQuery);
		$response = array();
		
		while ($row = $srchRecords->fetch(PDO::FETCH_ASSOC)) 
		{
            $people = explode(",", $row['branch_id']);

			if (in_array($_SESSION['_user_branch'], $people) || $_SESSION['_user_branch'] == 1)
			{
				$sname = $row['item_code'].' - '.$row['item_desciption'];
				
				$scode = $row['item_code'];
				$sid = $row['item_id'];
				// $item_order_min_qty = $row['item_order_min_qty'];
				$item_type = $row['item_type'];
				$item_curr_stock = $dbconn->GetSingleReconrd("tbl_item_stock","$field_name","item_id",$row['item_id']);
				$item_order_min_qty =  $dbconn->GetSingleReconrd("tbl_item_stock","$branch_item_moq","item_id",$row['item_id']);
				$uom = $dbconn->GetSingleReconrd("mst_uom","uom_name","uom_id",$row['item_uom']);
				
							
				$temp_array = array();
				$temp_array['value'] = $sname;
				$temp_array['item_order_min_qty'] = $item_order_min_qty;
				$temp_array['item_curr_stock'] = $item_curr_stock.'~ '.$uom;
				$temp_array['item_uom'] = $uom;
				$temp_array['id'] = $sid;
				
				$response[] = $temp_array;
			}
		}
		
		echo json_encode($response, true);
	}
?>

