<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
require_once("../common/csrf.php");
require_once("../common/access.php");
require_once("../common/db_txn.php");
require_once("../common/stock_flow.php");
require_once("../common/item_price.php");

// 2026-10-16 new - item prices for the bulk discount / margin screen and the price list screen
// login + csrf, Item creator in Task Settings (ITEM C) or admin; cost and margin always worked out here

fnAjaxGuard();
if (!fnTaskCan('ITEM', 'C')) {
	fnAccessDenied(true);
}
header('Content-Type: application/json');

function fnPriceOut($code, $data)
{
	http_response_code($code);
	echo json_encode($data);
	die();
}

$conn = new dbconnect();
$branches = fnPriceBranches($conn);
$mode = isset($_POST['mode']) ? $_POST['mode'] : '';

// branches picked on the screen, only active ones
$pick = array();
foreach (explode(',', isset($_POST['branches']) ? (string)$_POST['branches'] : '') as $b) {
	if (isset($branches[(int)$b])) {
		$pick[] = (int)$b;
	}
}

if ($mode == 'list') {
	$f = array();
	foreach (array('principal', 'brand', 'division', 'item_type', 'search') as $k) {
		$f[$k] = isset($_POST[$k]) ? (string)$_POST[$k] : '';
	}
	if ($f['principal'] . $f['brand'] . $f['division'] . $f['item_type'] . trim($f['search']) === '' || !$pick) {
		fnPriceOut(200, array('rows' => array()));
	}
	fnPriceOut(200, array('rows' => fnPriceList($conn, $f, $pick)));
}

if ($mode == 'save_row') {
	$item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
	$bid = isset($_POST['branch_id']) ? (int)$_POST['branch_id'] : 0;
	$price = isset($_POST['price']) ? $_POST['price'] : '';
	$discount = isset($_POST['discount']) ? $_POST['discount'] : '';
	$selling = isset($_POST['selling']) ? $_POST['selling'] : '';
	if ($item_id <= 0 || !isset($branches[$bid])) {
		fnPriceOut(400, array('msg' => 'Unknown item or branch..!'));
	}
	if (!is_numeric($price) || !is_numeric($discount) || !is_numeric($selling) || $price < 0 || $selling < 0 || $discount < 0 || $discount > 100) {
		fnPriceOut(400, array('msg' => 'Item price, selling price (0 or more) and discount (0 - 100) are needed..!'));
	}
	try {
		db_begin($conn);
		$new = fnPriceSave($conn, $item_id, $bid, $price, $discount, $selling);
		db_commit($conn);
	} catch (Exception $e) {
		db_rollback($conn);
		fnLogError($e);
		fnPriceOut(500, array('msg' => 'Save failed - please try again..!'));
	}
	if ($new === false) {
		fnPriceOut(200, array('msg' => 'Nothing changed', 'changed' => 0));
	}
	fnPriceOut(200, array('msg' => 'Saved', 'changed' => 1, 'row' => array('price' => $new['price'], 'discount' => $new['discount'],
		'cost' => $new['cost_price'], 'margin' => $new['margin'], 'selling' => $new['selling_price'])));
}

if ($mode == 'bulk') {
	$what = isset($_POST['what']) ? $_POST['what'] : '';
	$value = isset($_POST['value']) ? $_POST['value'] : '';
	if (!in_array($what, array('discount', 'margin'), true) || !is_numeric($value) || $value < ($what == 'discount' ? 0 : -100) || ($what == 'discount' && $value > 100)) {
		fnPriceOut(400, array('msg' => 'Give a discount (0 - 100) or a margin %..!'));
	}
	// rows as item:branch pairs in one field - a long list would pass the post field limit as separate fields
	$pairs = array();
	foreach (explode(',', isset($_POST['rows']) ? (string)$_POST['rows'] : '') as $p) {
		$x = explode(':', $p);
		if (count($x) == 2 && (int)$x[0] > 0 && isset($branches[(int)$x[1]])) {
			$pairs[(int)$x[0] . ':' . (int)$x[1]] = array((int)$x[0], (int)$x[1]);
		}
	}
	if (!$pairs) {
		fnPriceOut(400, array('msg' => 'No items to change..!'));
	}
	@set_time_limit(600);
	$by_branch = array();
	foreach ($pairs as $p) {
		$by_branch[$p[1]][] = $p[0];
	}
	$changed = 0;
	try {
		db_begin($conn);
		foreach ($by_branch as $bid => $ids) {
			$changed += fnPriceBulk($conn, $bid, $ids, $what, $value);
		}
		db_commit($conn);
	} catch (Exception $e) {
		db_rollback($conn);
		fnLogError($e);
		fnPriceOut(500, array('msg' => 'Save failed, nothing was changed - please try again..!'));
	}
	$_SESSION['_msg'] = $changed . " item price row(s) updated..!";
	fnPriceOut(200, array('msg' => $_SESSION['_msg'], 'changed' => $changed, 'total' => count($pairs)));
}

fnPriceOut(400, array('msg' => 'Unknown request..!'));
