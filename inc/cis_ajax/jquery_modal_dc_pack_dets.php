<?php
// 2026-10-01 temp table dropped - renders the packing inputs from values sent by the page

ob_start();
session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
require_once("../common/csrf.php");

$conn   = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

if (empty($_SESSION['_user_id']) || !csrf_check_ajax()) {
	echo '~<div class="alert alert-danger m-3">Session expired. Please reload the page.</div>';
	die();
}

$box_count    = isset($_POST['box_count']) ? (int)$_POST['box_count'] : 0;
$dispatch_qty = isset($_POST['dispatch_qty']) ? $_POST['dispatch_qty'] : 0;
$item_id      = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;

// what this line already has packed, sent up from the row's hidden fields
$pack_box_no   = (isset($_POST['pack_box_no']) && $_POST['pack_box_no'] !== '')
			   ? explode(',', $_POST['pack_box_no']) : array();
$pack_item_qty = (isset($_POST['pack_item_qty']) && $_POST['pack_item_qty'] !== '')
			   ? explode(',', $_POST['pack_item_qty']) : array();
$pack_total    = isset($_POST['pack_total_qty']) ? $_POST['pack_total_qty'] : '';

$item_code = $dbconn->GetSingleReconrd("tbl_item_details", "item_code", "item_status = '1' AND item_id", $item_id);
$item_name = $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_status = '1' AND item_id", $item_id);

$header = '<div class="row">
		<div class="col-lg-6" style="text-align: left;">
			<span style="font-size: 14px; font-weight: bold;">' . htmlspecialchars($item_name) . ' - ' . htmlspecialchars($item_code) . '</span>
		</div>
		<div class="col-lg-6" style="text-align: right;">
			<span style="font-size: 14px; font-weight: bold;">Dispatch Qty : ' . htmlspecialchars($dispatch_qty) . ' </span>
		</div>
	</div>';

$modal_dets = '<input type="hidden" id="pack_box_count" value="' . $box_count . '">
	<input type="hidden" id="pack_item_id" value="' . $item_id . '">
	<input type="hidden" id="pack_dispatch_qty" value="' . htmlspecialchars($dispatch_qty, ENT_QUOTES) . '">';

for ($i = 1; $i <= $box_count; $i++) {
	$bn = isset($pack_box_no[$i - 1]) ? trim($pack_box_no[$i - 1]) : '';
	$bq = isset($pack_item_qty[$i - 1]) ? trim($pack_item_qty[$i - 1]) : '';

	$modal_dets .= '<div class="row pb-2">
			<div class="col-lg-6" style="text-align: center;">
				<input type="text" class="form-control" onKeyPress="return isNumberKey(event)"
					name="pack_box_no[]" id="pack_box_no' . $i . '" value="' . htmlspecialchars($bn, ENT_QUOTES) . '" />
			</div>
			<div class="col-lg-6" style="text-align: center;">
				<input type="text" class="form-control qty" onKeyPress="return isNumberKey(event)"
					name="pack_item_qty[]" id="pack_item_qty' . $i . '" value="' . htmlspecialchars($bq, ENT_QUOTES) . '" />
			</div>
		</div>';
}

$modal_dets .= '<div class="row">
		<div class="col-lg-6" style="font-size: 14px; font-weight: bold; text-align: center;">Total</div>
		<div class="col-lg-6" style="font-size: 14px; font-weight: bold; text-align: center;">
			<input readonly type="text" id="total" class="form-control font-weight-bold" name="total"
				value="' . htmlspecialchars($pack_total, ENT_QUOTES) . '" />
		</div>
	</div>
	<hr>
	<div class="col-lg-12" style="font-size: 14px; font-weight: bold; text-align: center;">
		<input class="btn btn-custom" type="button" name="SAVE" id="SAVE" value="SAVE">
	</div>';

echo $header . '~' . $modal_dets;
die();
