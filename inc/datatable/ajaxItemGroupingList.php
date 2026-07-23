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
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = $_POST['search']['value']; // Search value

if ($columnName == '') {
    $columnName = " grn_date ";
}
## Custom Field value
$searchByCode = isset($_POST['searchByCode']) ? $_POST['searchByCode'] : '';

## Search 
$searchQuery = " ";
$app_qry = " ";
if ($searchByCode != '') {
    $searchQuery .= " and (a.item_group_code like '%" . $searchByCode . "%' OR a.item_group_name like '%" . $searchByCode . "%'  ) ";
}

/*
$app_user = $dbconn->GetSingleReconrd("mst_task_setting","app_usr_id"," task_id", 1 );
if($app_user == $_SESSION['_user_id']){
	$searchQuery .= " AND a.grn_status > 1 ";
}
*/

## Total number of records without filtering
$sel = $conn->query("SELECT count(*) as allcount FROM tbl_item_group a WHERE a.status = 1 " . $searchQuery);
$records = $sel->fetch();
$totalRecords = $records->allcount;

## Total number of records with filtering
$sel = $conn->query("SELECT count(*) as allcount FROM tbl_item_group a WHERE a.status = 1  " . $searchQuery);
$records = $sel->fetch();
$totalRecordwithFilter = $records->allcount;

## Fetch records
$itemQuery = "SELECT * FROM tbl_item_group a WHERE a.status = 1 " . $searchQuery .
    " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;


$itemRecords = $conn->query($itemQuery);
$data = array();

$sno = 1;

while ($row = $itemRecords->fetch()) {

    
    $edit_link = '<a href="mst_item_grouping.php?group_id='.$row->item_group_id.'" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 mr-2"></i></a>';

    $del_link = '<a href="" class="delete" rel="'.$row->item_group_id.'" data-popup="tooltip" title="Remove" data-original-title="Remove" ><i class="icon-bin bg-delete mr-2"></i></a>';
    
    $get_count = $dbconn->GetCount("tbl_item_group_details","item_group_id",$row->item_group_id);
    
    $view_link = '<a data-toggle="modal" data-target="#modalitemgrpDets" href="" data-id="'.$row->item_group_id.'" data-popup="tooltip" title="View '.$row->item_group_code.'"><i class="icon-file-eye2 mr-2"></i></a>';


    $converter = new Encryption;
    //$token = $converter->encode($row->grn_id.'~'.$_SESSION['_user_id']);	

    $data[] = array(
        "item_group_id" => $sno,
        "item_group_code" => $row->item_group_code,
        "item_group_name" => $row->item_group_name,
        "itm_grp_count" => $get_count,
        "action" => $view_link . $edit_link . $del_link 
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
