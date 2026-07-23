<?php

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
session_start();
$conn = new dbconnect();
$dbconn= new dbhandler();

	if(isset($_GET["q"]))
	{	
		
		if(isset($_GET["cat"]) && $_GET["cat"] != '')
		{
			$srchQuery = "SELECT * FROM bar_mst_items WHERE rec_del_status=0 AND item_category = ".$_GET["cat"]. " AND
					( item_name LIKE '%".$_GET["q"]."%' or item_code LIKE '%".$_GET["q"]."%' )
						ORDER BY item_name ASC ";
		}
		else
		{
			$srchQuery = "SELECT * FROM bar_mst_items WHERE rec_del_status=0 AND
					( item_name LIKE '%".$_GET["q"]."%' or item_code LIKE '%".$_GET["q"]."%' )
						ORDER BY item_name ASC ";
		}
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