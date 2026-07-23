<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();

//ini_set('display_errors', '1');
//ini_set('display_startup_errors', '1');
//error_reporting(E_ALL);

## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = $_POST['search']['value']; // Search value

if ($columnName == '') {
    $columnName = " item_stock ";
}

## Custom Field value
$searchByCode = isset($_POST['searchByCode']) ? $_POST['searchByCode'] : '';
## Search 
$searchQuery = " ";
$app_qry = " ";

if ($searchByCode != '') {
    $searchQuery .= " and (a.item_code like '%" . $searchByCode . "%' OR a.item_purchase_code like '%" . $searchByCode . "%'  OR a.supp_item_code like '%" . $searchByCode . "%'
    OR a.item_desciption like '%" . $searchByCode . "%' ) ";
}

## Total number of records without filtering
$sel = $conn->query("SELECT count(*) as allcount FROM tbl_item_details as a 
									        LEFT JOIN mst_uom as b ON a.item_uom = b.uom_id
									        LEFT JOIN mst_category as c ON a.item_category = c.category_id 
                                            LEFT JOIN tbl_item_stock as d ON a.item_id = d.item_id
									        WHERE a.item_status = '1' " . $searchQuery);
$records = $sel->fetch();
$totalRecords = $records->allcount;

## Total number of records with filtering
$sel = $conn->query("SELECT count(*) as allcount FROM tbl_item_details as a 
									        LEFT JOIN mst_uom as b ON a.item_uom = b.uom_id
									        LEFT JOIN mst_category as c ON a.item_category = c.category_id 
                                            LEFT JOIN tbl_item_stock as d ON a.item_id = d.item_id
									        WHERE a.item_status = '1' " . $searchQuery);
$records = $sel->fetch();
$totalRecordwithFilter = $records->allcount;

## Fetch records
$itemQuery = "SELECT a.*, b.uom_name, c.category_name, a.item_desciption as item_name, d.ho_stock FROM tbl_item_details as a 
									        LEFT JOIN mst_uom as b ON a.item_uom = b.uom_id
									        LEFT JOIN mst_category as c ON a.item_category = c.category_id 
                                            LEFT JOIN tbl_item_stock as d ON a.item_id = d.item_id
									        WHERE a.item_status = '1' " . $searchQuery .
    " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;


$itemRecords = $conn->query($itemQuery);
$data = array();
$sno = 1;

while ($row = $itemRecords->fetch()) {

    $field_name = $dbconn->GetSingleReconrd("mst_branch", "branch_stock_field", "branch_id", $_SESSION['_user_branch']);
    $item_stock = $dbconn->GetSingleReconrd("tbl_item_stock", "$field_name", "item_id", $row->item_id);


    $branch_rack = $dbconn->GetSingleReconrd("mst_branch", "branch_stock_location_rack_field", "branch_id", $_SESSION['_user_branch']);
    $branch_row = $dbconn->GetSingleReconrd("mst_branch", "branch_stock_location_row_field", "branch_id", $_SESSION['_user_branch']);

    $rack_field = $dbconn->GetSingleReconrd("tbl_item_stock", "$branch_rack", "item_id", $row->item_id);
    $row_field = $dbconn->GetSingleReconrd("tbl_item_stock", "$branch_row", "item_id", $row->item_id);

   
    if ($row->item_image != "") {
        $img_link = '<a class="fancybox" href="project_img/item_image/' . $row->item_image . '"><img src="project_img/item_image/' . $row->item_image . '" width="30px" height="30px" alt=""></a>';
    } else {
        $img_link = '';
    }

    if ($rack_field == 0) {
        $rack_field_val = '';
    } else {
        $rack_field_val = $rack_field;
    }

    if ($row_field == 0) {
        $row_field_val = '';
    } else {
        $row_field_val = $row_field;
    }

    $edit_link = '<a data-toggle="modal" data-target="#modalStockDets" href="" data-id="' . $row->item_id . '" data-popup="tooltip" title="Stock Details"><i class="icon-pencil5 bg-edit mr-2"></i></a>';

    $data[] = array(
        "item_id" => $sno,
        "item_image" => $img_link,
        "item_code" => $row->item_code,
        "item_desciption" => $row->item_desciption,
        "uom_name" => $row->uom_name,
        "category_name" => $row->category_name,
        "location" => '' . '<small>Rack</small>&nbsp' . '-&nbsp' . $rack_field_val . '<br>' . '' . '<small>Row</small>&nbsp' . '-&nbsp' . $row_field_val,
        "item_stock" => $item_stock,
        "action" => $edit_link,
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
