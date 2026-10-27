<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();
// 2026-10-27 one joined query: search on columns that exist (it named deleted ones), sort by the values shown,
// an item without a stock row shows blanks (it showed the previous row's values)
// 2026-10-27 search / sort input through fnSqlText / fnSqlColumn / fnSqlDir - a ' in a search box broke the query

// ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);


## Read value
$draw = $_POST['draw'];
$row = (int)($_POST['start']);
$rowperpage = (int)($_POST['length']); // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = fnSqlColumn($_POST['columns'][$columnIndex]['data']); // Column name
$columnSortOrder = fnSqlDir($_POST['order'][0]['dir']); // asc or desc
$searchValue = fnSqlText($_POST['search']['value']); // Search value

if ($columnName == '') {
    $columnName = " item_history ";
}
## Custom Field value
$searchByCode = fnSqlText(isset($_POST['searchByCode']) ? $_POST['searchByCode'] : '');

// the user's branch: its stock columns for the price / qty shown, its prefix (ho / kl / rjpm) for the history columns
$branch_data = $conn->query("SELECT * FROM mst_branch WHERE branch_id = " . (int)$_SESSION['_user_branch'])->fetch(PDO::FETCH_OBJ);
$pre = $branch_data ? preg_replace('/_stock$/', '', $branch_data->branch_stock_field) : 'ho';

## Search - item code / purchase code / description, and the branch's new selling price / qty in the history row
$searchQuery = " ";
if ($searchByCode != '') {
    $k = "'%" . $searchByCode . "%'";
    $searchQuery .= " and (b.item_code like $k OR b.item_purchase_code like $k OR b.item_desciption like $k
        OR a.{$pre}_new_selling_price like $k OR a.{$pre}_new_msq like $k OR a.{$pre}_new_maq like $k OR a.{$pre}_new_moq like $k) ";
}
$sel = $conn->query("SELECT count(*) as allcount FROM tbl_itemprice_history as a LEFT JOIN tbl_item_details b ON a.item_id = b.item_id WHERE 1 = 1");
$records = $sel->fetch();
$totalRecords = $records->allcount;

## Total number of records with filtering
$sel = $conn->query("SELECT count(*) as allcount FROM tbl_itemprice_history as a LEFT JOIN tbl_item_details b ON a.item_id = b.item_id WHERE 1 = 1" . $searchQuery);
$records = $sel->fetch();
$totalRecordwithFilter = $records->allcount;

// sort only by what the list shows (it sorted the price / qty columns by old item master fields)
$sorts = array('auto_id' => 'a.auto_id', 'item_code' => 'b.item_code', 'item_purchase_code' => 'b.item_purchase_code', 'item_desciption' => 'b.item_desciption',
    'item_selling_price' => 'item_selling_price', 'item_min_qty' => 'item_min_qty', 'item_max_qty' => 'item_max_qty', 'item_order_min_qty' => 'item_order_min_qty', 'itm_upt' => 'a.created_dtm');
$orderBy = isset($sorts[$columnName]) ? $sorts[$columnName] : 'a.auto_id';

## Fetch records - the item's first stock row (as before) and the user name in the same query
$itemQuery = "SELECT a.item_id, a.created_by, a.created_dtm, b.item_code, b.item_purchase_code, b.item_desciption, u.usr_name";
if ($branch_data) {
    $itemQuery .= ", s." . $branch_data->branch_item_selling_price . " AS item_selling_price, s." . $branch_data->branch_item_msq . " AS item_min_qty,
        s." . $branch_data->branch_item_maq . " AS item_max_qty, s." . $branch_data->branch_item_moq . " AS item_order_min_qty";
} else {
    $itemQuery .= ", NULL AS item_selling_price, NULL AS item_min_qty, NULL AS item_max_qty, NULL AS item_order_min_qty";
}
$itemQuery .= " FROM tbl_itemprice_history as a LEFT JOIN tbl_item_details b ON a.item_id = b.item_id
    LEFT JOIN tbl_item_stock s ON s.stock_id = (SELECT MIN(x.stock_id) FROM tbl_item_stock x WHERE x.item_id = a.item_id)
    LEFT JOIN tbl_user u ON u.usr_id = a.created_by
    WHERE 1 = 1" . $searchQuery . " order by " . $orderBy . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;

$sno = 1;
$itemRecords = $conn->query($itemQuery);
$data = array();

while ($row = $itemRecords->fetch())
	{
  $data[] = array(
        "auto_id"=> $sno,
        "item_code" => $row->item_code,
        "item_purchase_code" => $row->item_purchase_code,
        "item_desciption" => $row->item_desciption,
        "item_selling_price" => $row->item_selling_price,
        "item_min_qty" => $row->item_min_qty,
        "item_max_qty" => $row->item_max_qty,
        "item_order_min_qty" => $row->item_order_min_qty,
		"itm_upt" => '<b>'.$row->usr_name.'</b><br><small>'.date("d-m-Y H:i:s", strtotime($row->created_dtm)).'</small>',
    );
    $sno++;
}
## Response
$response = array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $data
);

echo json_encode($response);
