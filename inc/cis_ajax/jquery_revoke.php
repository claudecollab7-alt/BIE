<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
require_once("../common/csrf.php");
require_once("../common/access.php");
require_once("../common/db_txn.php");
require_once("../common/revoke.php");

// 2026-10-15 new - revoke a wrong approve / verify, one call for every form (rules per form in inc/common/revoke.php)
// login + csrf, Revoker (Task Settings role R) for the document's branch, reason needed

fnAjaxGuard();

$doc = isset($_POST['doc']) ? (string)$_POST['doc'] : '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$reason = trim(isset($_POST['reason']) ? (string)$_POST['reason'] : '');

$def = fnRevokeDef($doc);
if (!$def || $id <= 0) {
	http_response_code(400);
	die('Unknown document..!');
}

$conn = new dbconnect();
if (!fnRevokeCan($doc, fnRevokeBranch($conn, $doc, $id))) {
	fnAccessDenied(true);
}
if ($reason === '') {
	http_response_code(400);
	die('Please give the reason for revoking..!');
}

$res = fnRevokeRun($conn, $doc, $id, substr($reason, 0, 250));
if ($res !== true) {
	http_response_code(409);
	die($res);
}
$_SESSION['_msg'] = $def['done'];
echo 'OK';
