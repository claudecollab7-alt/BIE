<?php
/* Returns the logged-in branch's current stock for one item.
   Used by stock_adjustment.php to show the before/after preview. */

ob_start();
session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
require_once("../common/stock_flow.php");

$conn   = new dbconnect();
$dbconn = new dbhandler();

header('Content-Type: application/json');

$item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;

if ($item_id <= 0 || !isset($_SESSION['_user_id'])) {
    echo json_encode(array('ok' => false, 'msg' => 'Invalid request'));
    die();
}

try {
    $branch_id   = (int)$_SESSION['_user_branch'];
    $stock_field = fnGetBranchStockField($conn, $branch_id);
    $curr_stock  = fnGetItemBranchStock($conn, $item_id, $stock_field);

    $stmt = $conn->prepare("SELECT d.item_code, d.item_desciption, u.uom_name
                              FROM tbl_item_details d
                              LEFT JOIN mst_uom u ON u.uom_id = d.item_uom
                             WHERE d.item_id = :item_id");
    $stmt->execute(array(':item_id' => $item_id));
    $item = $stmt->fetch(PDO::FETCH_OBJ);

    echo json_encode(array(
        'ok'          => true,
        'item_id'     => $item_id,
        'item_code'   => $item ? $item->item_code : '',
        'description' => $item ? $item->item_desciption : '',
        'uom'         => $item ? $item->uom_name : '',
        'stock_field' => $stock_field,
        'curr_stock'  => (float)$curr_stock
    ));
} catch (Exception $e) {
    echo json_encode(array('ok' => false, 'msg' => $e->getMessage()));
}
die();
