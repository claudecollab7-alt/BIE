<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn   = new dbconnect();
$dbconn = new dbhandler();

if ($_POST['mode'] == 'get_net_amt') {

	$qty                  = $_POST['qty'];
	$repair_selling_price = $_POST['repair_selling_price'];
	$repair_tax           = $_POST['repair_tax'];

	$price   = $repair_selling_price * $qty;
	$tax_val = ($repair_tax > 0) ? ($price * $repair_tax) / 100 : 0;
	$net_val = $price + $tax_val;

	echo number_format($net_val, 2, '.', '');
}
?>
