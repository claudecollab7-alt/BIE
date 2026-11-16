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
// the customer's invoices, for a call under warranty: id~ref - date#
$st = $conn->prepare("SELECT inv_id, inv_refno, inv_date FROM tbl_invoice WHERE inv_status = 1 AND supp_id = ? ORDER BY inv_id DESC");
$st->execute(array((int)(isset($_POST['supp_id']) ? $_POST['supp_id'] : 0)));
$out = '';
while ($r = $st->fetch(PDO::FETCH_OBJ)) {
	$out .= $r->inv_id . '~' . $r->inv_refno . ' - ' . date('d-m-Y', strtotime($r->inv_date)) . '#';
}
echo $out;
