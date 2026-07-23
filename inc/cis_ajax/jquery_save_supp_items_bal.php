<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();

ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);

$supp_id = $_REQUEST['id'];

## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = $_POST['search']['value']; // Search value

if ($columnName == '') {
    $columnName = " item_id ";
}
## Custom Field value

## Search 
$searchByCode = isset($_POST['searchByCode']) ? $_POST['searchByCode'] : '';
$searchQuery = " ";

if ($searchByCode != '') {
    $searchQuery .= "AND (item_code like '%" . $searchByCode . "%' OR item_desciption like '%" . $searchByCode . "%' ) ";
}

$sel = $conn->query("SELECT count(*) as allcount FROM tbl_item_details WHERE item_status = '1' AND item_id NOT IN (SELECT item_id FROM tbl_supp_items WHERE supp_item_status = '1' AND supp_id = '".$supp_id."')" . $searchQuery);
$records = $sel->fetch();
$totalRecords = $records->allcount;

## Total number of records with filtering
$sel = $conn->query("SELECT count(*) as allcount FROM tbl_item_details WHERE item_status = '1' AND item_id NOT IN (SELECT item_id FROM tbl_supp_items WHERE supp_item_status = '1' AND supp_id = '".$supp_id."')" . $searchQuery);
$records = $sel->fetch();
$totalRecordwithFilter = $records->allcount;

## Fetch records
//$SQL = "SELECT item_id, item_code FROM tbl_item_details WHERE item_status = '1' AND item_id NOT IN (SELECT item_id FROM tbl_supp_items WHERE supp_item_status = '1' AND supp_id = '".$supp_id."')   ORDER BY item_id ASC";

$itemQuery = "SELECT item_id, item_code, item_desciption FROM tbl_item_details WHERE item_status = '1' AND item_id NOT IN (SELECT item_id FROM tbl_supp_items WHERE supp_item_status = '1' AND supp_id = '".$supp_id."')" . $searchQuery .
    " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage ;

$sno = 1;
$itemRecords = $conn->query($itemQuery);
$data = array();

while ($row = $itemRecords->fetch())
	{

		$text = '<input type="checkbox"  name="select_item[]" value = "'.$row->item_id.'" >';												
	
	
  $data[] = array(
        "item_id" => $sno,
        "item_code" => $row->item_desciption.' - '.$row->item_code,
        "action" => $text
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
