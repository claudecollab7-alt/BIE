<?php
ob_start();
session_start();
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
require_once("item_search_lib.php");

$conn = new dbconnect();
$dbconn= new dbhandler();

// 2026-10-07 one query for the best 30 matches instead of every match plus three lookups each, term bound, session released early

// read what we need, then let go of the session so the next keystroke is not queued behind this one
$branch_id = isset($_SESSION['_user_branch']) ? $_SESSION['_user_branch'] : 0;
session_write_close();

	if(isset($_GET["q"]))
	{
		$q = strtolower($_GET["q"]);

		$col_stock = fnItemStockColumn($conn, $branch_id, 'branch_stock_field');
		$col_moq   = fnItemStockColumn($conn, $branch_id, 'branch_item_maq');

		$rows  = fnItemSearch($conn, $q, $branch_id);
		$stock = fnItemStockFields($conn, $rows, array($col_stock, $col_moq));

		$response = array();
		foreach ($rows as $row)
		{
			$s = isset($stock[$row['item_id']]) ? $stock[$row['item_id']] : array();
			$uom = (string)$row['uom_name'];

			$temp_array = array();
			$temp_array['value'] = $row['item_code'].' - '.$row['item_desciption'];
			$temp_array['item_order_min_qty'] = ($col_moq != '' && isset($s[$col_moq])) ? $s[$col_moq] : '';
			$temp_array['item_curr_stock'] = (($col_stock != '' && isset($s[$col_stock])) ? $s[$col_stock] : '').'~ '.$uom;
			$temp_array['item_uom'] = $uom;
			$temp_array['id'] = $row['item_id'];
			$response[] = $temp_array;
		}

		echo json_encode($response, true);
	}
?>
