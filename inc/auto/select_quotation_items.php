<?php
ob_start();
session_start();
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
require_once("item_search_lib.php");

$conn = new dbconnect();
$dbconn= new dbhandler();

// 2026-10-07 one query for the best 30 matches instead of every match plus five lookups each, term bound, session released early

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

// read what we need, then let go of the session so the next keystroke is not queued behind this one
$branch_id = isset($_SESSION['_user_branch']) ? $_SESSION['_user_branch'] : 0;
session_write_close();

	if(isset($_GET["q"]))
	{
		$q = strtolower($_GET["q"]);

		$col_price = fnItemStockColumn($conn, $branch_id, 'branch_item_selling_price');
		$col_min   = fnItemStockColumn($conn, $branch_id, 'branch_item_min_discount');
		$col_max   = fnItemStockColumn($conn, $branch_id, 'branch_item_max_discount');

		$rows  = fnItemSearch($conn, $q, $branch_id);
		$stock = fnItemStockFields($conn, $rows, array($col_price, $col_min, $col_max));

		$response = array();
		foreach ($rows as $row)
		{
			$s = isset($stock[$row['item_id']]) ? $stock[$row['item_id']] : array();

			$temp_array = array();
			$temp_array['value'] = $row['item_code'].' - '.$row['item_desciption'];
			$temp_array['unit_price'] = ($col_price != '' && isset($s[$col_price])) ? $s[$col_price] : '';
			$temp_array['id'] = $row['item_id'];
			$temp_array['item_type'] = $row['item_type'];
			$temp_array['uom'] = (string)$row['uom_name'];
			$temp_array['max_discount'] = ($col_max != '' && isset($s[$col_max])) ? $s[$col_max] : '';
			$temp_array['min_discount'] = ($col_min != '' && isset($s[$col_min])) ? $s[$col_min] : '';
			$temp_array['gst'] = number_format((float)$row['igst'], 2);
			$response[] = $temp_array;
		}

		echo json_encode($response, true);
	}
?>
