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
$q = strtolower($_GET["q"]);

	if(isset($_GET["q"]))
	{	
		
	//echo $srchQuery;
		$branch_item_selling_price = $dbconn->GetSingleReconrd("mst_branch","branch_item_selling_price","branch_id",$_SESSION['_user_branch']);
		$branch_item_min_discount = $dbconn->GetSingleReconrd("mst_branch","branch_item_min_discount","branch_id",$_SESSION['_user_branch']);
		$branch_item_max_discount = $dbconn->GetSingleReconrd("mst_branch","branch_item_max_discount","branch_id",$_SESSION['_user_branch']);

		
		$srchQuery = "SELECT * FROM tbl_item_details WHERE item_status=1
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
				$unit_price = $dbconn->GetSingleReconrd("tbl_item_stock","$branch_item_selling_price","item_id",$row['item_id']);
				$min_discount = $dbconn->GetSingleReconrd("tbl_item_stock","$branch_item_min_discount","item_id",$row['item_id']);
				$max_discount = $dbconn->GetSingleReconrd("tbl_item_stock","$branch_item_max_discount","item_id",$row['item_id']);
				$item_type = $row['item_type'];
				$uom = $dbconn->GetSingleReconrd("mst_uom","uom_name","uom_id",$row['item_uom']);
				// $cgst = $dbconn->GetSingleReconrd("mst_hsn","cgst","hsn_id",$row['item_hsn']);
				// $sgst = $dbconn->GetSingleReconrd("mst_hsn","sgst","hsn_id",$row['item_hsn']);
				$igst = $dbconn->GetSingleReconrd("mst_hsn","igst","hsn_id",$row['item_hsn']);
				$vat = $igst;
				$gst = number_format((float)$vat,2);
							
				$temp_array = array();
				$temp_array['value'] = $sname;
				$temp_array['unit_price'] = $unit_price;
				$temp_array['id'] = $sid;
				// $temp_array['label'] = $scode.' - '.$sname.'';
				$temp_array['item_type'] = $item_type;
				$temp_array['uom'] = $uom;
				$temp_array['max_discount'] = $max_discount;
				$temp_array['min_discount'] = $min_discount;
				// $temp_array['cgst'] = $cgst;
				// $temp_array['sgst'] = $sgst;
				// $temp_array['igst'] = $igst;
				// $temp_array['vat'] = $vat;
				$temp_array['gst'] = $gst;
				$response[] = $temp_array;
			}
		}
		
		echo json_encode($response, true);
	}
?>