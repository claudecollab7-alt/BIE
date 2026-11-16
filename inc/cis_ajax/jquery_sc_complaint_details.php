<?php
// 2026-11-16 new - service call register (same flow as Benzear)
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
require_once("../common/csrf.php");
require_once("../common/access.php");
require_once("../common/service_call.php");
session_start();
$conn = new dbconnect();
$dbconn = new dbhandler();
fnAjaxGuard();
// returns the row of one spare part used - the page keeps it till save
try {
	$l = fnScSpareLine($conn, isset($_POST['item_id']) ? $_POST['item_id'] : 0, isset($_POST['sc_qty']) ? $_POST['sc_qty'] : 0);
} catch (Exception $e) {
	echo "ERR:" . $e->getMessage();
	exit;
}
echo fnScSpareRow($dbconn, fnLineNewKey(), $l);
