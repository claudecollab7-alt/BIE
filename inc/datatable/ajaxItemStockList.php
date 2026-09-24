<?php
// 2026-09-23 new - rows for lst_item_stock.php, one stock column per active branch

ob_start();
session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
require_once("../common/stock_flow.php");

$conn   = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

## Read value
$draw       = isset($_POST['draw']) ? $_POST['draw'] : 0;
$row        = isset($_POST['start']) ? (int)$_POST['start'] : 0;
$rowperpage = isset($_POST['length']) ? (int)$_POST['length'] : 25;

$columnName      = '';
$columnSortOrder = 'asc';
if (isset($_POST['order'][0]['column'])) {
    $columnIndex = $_POST['order'][0]['column'];
    if (isset($_POST['columns'][$columnIndex]['data'])) {
        $columnName = $_POST['columns'][$columnIndex]['data'];
    }
    if (isset($_POST['order'][0]['dir'])) {
        $columnSortOrder = $_POST['order'][0]['dir'];
    }
}

// DataTables sends -1 for All
if ($rowperpage <= 0) {
    $rowperpage = 1000;
}

## branch stock columns, read from the branch master
$branches   = array();
$branch_res = $conn->query("SELECT branch_id, branch_name, branch_code, branch_stock_field
                              FROM mst_branch
                             WHERE branch_status = 1 AND branch_stock_field <> ''
                             ORDER BY branch_id");
while ($b = $branch_res->fetch(PDO::FETCH_OBJ)) {
    // column name goes into the SQL below, so only allow real ones
    if (preg_match('/^[A-Za-z0-9_]{1,30}$/', $b->branch_stock_field)) {
        $branches[] = $b;
    }
}

## sortable columns, anything else falls back
$sortable = array('item_code' => 'a.item_code', 'item_purchase_code' => 'a.item_purchase_code',
                  'item_desciption' => 'a.item_desciption', 'uom_name' => 'b.uom_name',
                  'category_name' => 'c.category_name');
foreach ($branches as $b) {
    $sortable['stock_' . $b->branch_id] = 'd.' . $b->branch_stock_field;
}

$orderBy  = isset($sortable[$columnName]) ? $sortable[$columnName] : 'a.item_code';
$orderDir = (strtolower($columnSortOrder) == 'desc') ? 'DESC' : 'ASC';

## Custom Field value
$searchByCode = isset($_POST['searchByCode']) ? $_POST['searchByCode'] : '';
$itemTypeId   = isset($_POST['itemTypeId']) ? $_POST['itemTypeId'] : '';
$stockFilter  = isset($_POST['stockFilter']) ? $_POST['stockFilter'] : '';

## Search
$searchQuery = " ";
$params      = array();

if ($searchByCode != '') {
    $searchQuery .= " AND (a.item_code LIKE :kw OR a.item_purchase_code LIKE :kw2 OR a.supp_item_code LIKE :kw3
                           OR a.item_desciption LIKE :kw4) ";
    $params[':kw']  = '%' . $searchByCode . '%';
    $params[':kw2'] = '%' . $searchByCode . '%';
    $params[':kw3'] = '%' . $searchByCode . '%';
    $params[':kw4'] = '%' . $searchByCode . '%';
}

if ($itemTypeId != '' && ctype_digit((string)$itemTypeId)) {
    $searchQuery .= " AND a.item_type = :item_type ";
    $params[':item_type'] = (int)$itemTypeId;
}

// stock filter works on the total across all branches
$stock_sum = '0';
foreach ($branches as $b) {
    $stock_sum .= ' + COALESCE(d.' . $b->branch_stock_field . ', 0)';
}
if ($stockFilter == 'instock') {
    $searchQuery .= " AND (" . $stock_sum . ") > 0 ";
} elseif ($stockFilter == 'nostock') {
    $searchQuery .= " AND (" . $stock_sum . ") <= 0 ";
}

$baseFrom = " FROM tbl_item_details AS a
              LEFT JOIN mst_uom AS b ON a.item_uom = b.uom_id
              LEFT JOIN mst_category AS c ON a.item_category = c.category_id
              LEFT JOIN tbl_item_stock AS d ON a.item_id = d.item_id
              WHERE a.item_status = '1' ";

## Total number of records without filtering
$sel = $conn->query("SELECT COUNT(*) AS allcount FROM tbl_item_details WHERE item_status = '1'");
$totalRecords = $sel->fetch()->allcount;

## Total number of records with filtering
$sel = $conn->prepare("SELECT COUNT(*) AS allcount " . $baseFrom . $searchQuery);
$sel->execute($params);
$totalRecordwithFilter = $sel->fetch()->allcount;

## Fetch records
$stockCols = '';
foreach ($branches as $b) {
    $stockCols .= ", COALESCE(d." . $b->branch_stock_field . ", 0) AS stock_" . $b->branch_id;
}

$itemQuery = "SELECT a.item_id, a.item_code, a.item_purchase_code, a.item_desciption, a.item_image,
                     b.uom_name, c.category_name" . $stockCols . $baseFrom . $searchQuery .
             " ORDER BY " . $orderBy . " " . $orderDir . " LIMIT " . (int)$row . "," . (int)$rowperpage;

$itemRecords = $conn->prepare($itemQuery);
$itemRecords->execute($params);

$data = array();
$sno  = (int)$row + 1;

while ($rs = $itemRecords->fetch(PDO::FETCH_OBJ)) {

    if ($rs->item_image != "") {
        $img_link = '<a class="fancybox" href="project_img/item_image/' . $rs->item_image . '">
                     <img src="project_img/item_image/' . $rs->item_image . '" width="30px" height="30px" alt=""></a>';
    } else {
        $img_link = '<img src="project_img/no-image.jpg" width="30px" height="30px" alt="">';
    }

    $entry = array(
        "sno"                => $sno,
        "item_image"         => $img_link,
        "item_code"          => htmlspecialchars($rs->item_code),
        "item_purchase_code" => htmlspecialchars($rs->item_purchase_code),
        "item_desciption"    => htmlspecialchars($rs->item_desciption),
        "uom_name"           => htmlspecialchars($rs->uom_name),
        "category_name"      => htmlspecialchars($rs->category_name)
    );

    // each qty is a link that opens the stock flow modal
    foreach ($branches as $b) {
        $key = 'stock_' . $b->branch_id;
        $qty = (float)$rs->$key;
        $txt = rtrim(rtrim(number_format($qty, 3, '.', ''), '0'), '.');
        if ($txt === '' || $txt === '-') {
            $txt = '0';
        }

        $cls = ($qty > 0) ? 'text-success' : (($qty < 0) ? 'text-danger' : 'text-muted');

        $entry[$key] = '<a href="javascript:;" class="stock-flow-link font-weight-bold ' . $cls . '"
                           data-toggle="modal" data-target="#modalItemStockFlow"
                           data-item-id="' . (int)$rs->item_id . '"
                           data-branch-id="' . (int)$b->branch_id . '"
                           data-popup="tooltip" title="Stock flow at ' . htmlspecialchars($b->branch_name) . '">'
                     . $txt . '</a>';
    }

    $data[] = $entry;
    $sno++;
}

## Response
$response = array(
    "draw"                 => intval($draw),
    "iTotalRecords"        => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData"               => $data
);

echo json_encode($response);
