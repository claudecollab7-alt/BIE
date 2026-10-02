<?php
// 2026-10-01 temp table dropped - renders one branch row, the form posts the rows back as arrays

ob_start();
session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
require_once("../common/csrf.php");
require_once("../common/form_rows.php");

$conn   = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

if (empty($_SESSION['_user_id']) || !csrf_check_ajax()) {
	echo '<tr><td colspan="4" class="text-danger">Session expired. Please reload the page.</td></tr>';
	die();
}

if (isset($_POST['mode']) && $_POST['mode'] == 'row') {
	echo fnBranchRow($dbconn, $_POST, isset($_POST['sno']) ? (int)$_POST['sno'] : 1);
	die();
}

echo '';
die();
