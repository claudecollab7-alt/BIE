<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn   = new dbconnect();
$dbconn = new dbhandler();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


/* ---- Mode: InvoiceList — populate invoice dropdown by customer ---- */
if (isset($_POST['supp_id']) && $_POST['supp_id'] > 0 && $_POST['mode'] == 'InvoiceList') {

	$supp_id = $_POST['supp_id'];
	$stmt    = $conn->prepare("SELECT * FROM tbl_invoice WHERE inv_status = 1 AND supp_id = " . $supp_id . " ORDER BY inv_id ASC");
	$stmt->execute();

	$string  = "" . "~" . "--Select Invoice--" . "#";
	if ($stmt->rowCount() > 0) {
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$string .= $row['inv_id'] . "~" . $row['inv_slno'] . " - " . date('d-m-Y', strtotime($row['inv_date'])) . "#";
		}
	}
	echo $string;
}

/* ---- Mode: AMCDetails — show AMC status for selected invoice ---- */
if (isset($_POST['inv_id']) && $_POST['inv_id'] > 0 && $_POST['mode'] == 'AMCDetails') {

	$inv_id      = $_POST['inv_id'];
	$amc_end_date = "No AMC";

	$get_val = $conn->query("SELECT * FROM tbl_amc WHERE inv_id = " . $inv_id);
	if ($get_val->rowCount() > 0) {
		$get          = $get_val->fetch(PDO::FETCH_OBJ);
		$amc_end_date = "AMC End On: " . date("d-M-Y", strtotime($get->amc_end_date));
	}
	echo $amc_end_date;
}

/* ---- Mode: InvoiceItems — populate repair item dropdown by invoice ---- */
if (isset($_POST['inv_id']) && $_POST['inv_id'] > 0 && $_POST['mode'] == 'InvoiceItems') {

	$inv_id = $_POST['inv_id'];
	$stmt   = $conn->prepare("SELECT item_id FROM tbl_invoice_details WHERE inv_id = " . $inv_id . " ORDER BY item_id ASC");
	$stmt->execute();

	$string  = "" . "~" . "--Select Item--" . "#";
	if ($stmt->rowCount() > 0) {
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$item_desp = $dbconn->GetSingleReconrd(
				"tbl_item_details",
				"CONCAT(item_desciption,' - ',item_code)",
				"item_id",
				$row['item_id']
			);
			$string .= $row['item_id'] . "~" . $item_desp . "#";
		}
	}
	echo $string;
}
?>
