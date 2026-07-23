<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

## Read value
$draw            = $_POST['draw'];
$row             = $_POST['start'];
$rowperpage      = $_POST['length'];
$columnIndex     = $_POST['order'][0]['column'];
$columnName      = $_POST['columns'][$columnIndex]['data'];
$columnSortOrder = $_POST['order'][0]['dir'];
$searchValue     = $_POST['search']['value'];

if ($columnName == '') {
    $columnName = " item_desciption ";
}

## Custom Field value
$searchByCode = isset($_POST['searchByCode']) ? $_POST['searchByCode'] : '';

## Search
$searchQuery = " ";
if ($searchByCode != '') {
    $searchQuery .= " AND ( a.item_code LIKE '" . $searchByCode . "%'
                       OR a.item_desciption LIKE '%" . $searchByCode . "%'
                       OR a.item_purchase_code LIKE '" . $searchByCode . "%' ) ";
}

## Total number of records without filtering
$sel          = $conn->query("SELECT count(*) as allcount FROM tbl_item_details a WHERE a.item_status = 1 AND a.item_type IN (1,2,5,8,7)" . $searchQuery);
$records      = $sel->fetch();
$totalRecords = $records->allcount;

## Total number of records with filtering
$sel                    = $conn->query("SELECT count(*) as allcount FROM tbl_item_details a WHERE a.item_status = 1 AND a.item_type IN (1,2,5,8,7)" . $searchQuery);
$records                = $sel->fetch();
$totalRecordwithFilter  = $records->allcount;

## Fetch records
$cusQuery = "SELECT item_id, item_code, item_purchase_code, item_division, item_desciption
             FROM tbl_item_details a
             WHERE a.item_status = 1 AND a.item_type IN (1,2,5,8,7)" . $searchQuery .
            " ORDER BY " . $columnName . " " . $columnSortOrder .
            " LIMIT " . $row . "," . $rowperpage;

$cusRecords = $conn->query($cusQuery);
$data       = array();
$sno        = 1;

while ($row = $cusRecords->fetch()) {

    $item_division   = $dbconn->GetSingleReconrd("mst_division", "division_name", "division_id", $row->item_division);
    $item_desciption = '<a class="fancybox fancybox.ajax" href="inc/popup/fancybox_view_item_details.php?item_id=' . $row->item_id . '" title="View Details">' . $row->item_desciption . '</a>';
    $edit_link       = '<a href="spare_mapping.php?item_id=' . $row->item_id . '" data-popup="tooltip" title="Edit"><i class="icon-pencil5 bg-edit mr-2"></i></a>';
    $spare_cnt       = $dbconn->GetCount("tbl_spare_mapping", "item_id", $row->item_id);

    $data[] = array(
        "item_id"               => $sno,
        "item_code"         => $row->item_code,
        "item_purchase_code"=> $row->item_purchase_code,
        "item_division"     => $item_division,
        "item_desciption"   => $item_desciption,
        "spare_cnt"         => $spare_cnt,
        "action"            => $edit_link
    );
    $sno++;
}

## Response
$response = array(
    "draw"                => intval($draw),
    "iTotalRecords"       => $totalRecords,
    "iTotalDisplayRecords"=> $totalRecordwithFilter,
    "aaData"              => $data
);

echo json_encode($response);