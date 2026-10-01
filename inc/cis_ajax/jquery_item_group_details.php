<?php
// 2026-10-01 temp table dropped - renders group item rows, the form posts them back as arrays

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
	echo '<tr><td colspan="5" class="text-danger">Session expired. Please reload the page.</td></tr>';
	die();
}

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$sno  = isset($_POST['sno']) ? (int)$_POST['sno'] : 1;

// one item
if ($mode == 'row') {
	echo fnItemGroupRow($dbconn, $_POST['item_id'], $_POST['item_qty'], $_POST['item_position'], $sno);
	die();
}

// every item of an existing group, so it can be pulled into this one
if ($mode == 'group_rows') {
	$stmt = $conn->prepare("SELECT item_id, item_qty, item_position FROM tbl_item_group_details
							 WHERE item_group_id = :gid ORDER BY item_position ASC");
	$stmt->execute(array(':gid' => (int)$_POST['group_id']));

	$qty = isset($_POST['item_qty']) ? $_POST['item_qty'] : 0;
	while ($g = $stmt->fetch(PDO::FETCH_OBJ)) {
		echo fnItemGroupRow($dbconn, $g->item_id, $qty, $g->item_position, $sno++);
	}
	die();
}

echo '';
die();
