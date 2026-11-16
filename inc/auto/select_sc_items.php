<?php
// 2026-11-16 new - spare parts for a service call (active sales spares), for the select2 search
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
session_start();
$conn = new dbconnect();
if (empty($_SESSION['_user_id'])) {
	exit;
}
session_write_close();
$q = '%' . trim(isset($_GET['q']) ? $_GET['q'] : '') . '%';
$st = $conn->prepare("SELECT item_id AS id, CONCAT(item_code, ' - ', item_desciption) AS text FROM tbl_item_details
	WHERE item_status = 1 AND item_dept_sales = 1 AND item_type = 5 AND (item_code LIKE ? OR item_desciption LIKE ?) ORDER BY item_code LIMIT 30");
$st->execute(array($q, $q));
header('Content-Type: application/json');
echo json_encode(array('results' => $st->fetchAll(PDO::FETCH_ASSOC)));
