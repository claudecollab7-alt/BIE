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
// returns the row of one new complaint - the page keeps it till save
try {
	$desc = fnScComplaintDesc(isset($_POST['complaint_desc']) ? $_POST['complaint_desc'] : '');
} catch (Exception $e) {
	echo "ERR:" . $e->getMessage();
	exit;
}
echo fnScComplaintRow(fnLineNewKey(), $desc);
