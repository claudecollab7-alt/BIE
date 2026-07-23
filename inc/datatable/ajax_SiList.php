<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();


// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);


## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = $_POST['search']['value']; // Search value


## Custom Field value
$searchByCode = isset($_POST['searchByCode']) ? $_POST['searchByCode'] : '';
$searchByStatus = isset($_POST['searchByStatus']) ? $_POST['searchByStatus'] : '';
$searchBySupp = isset($_POST['searchBySupp']) ? $_POST['searchBySupp'] : '';
$searchByBranch = isset($_POST['searchByBranch']) ? $_POST['searchByBranch'] : '';
$searchByYear = isset($_POST['searchByYear']) ? $_POST['searchByYear'] : '';



## Search 
$searchQuery = " ";
$app_qry = " ";
if ($searchByCode != '') {
    $searchQuery .= " and (si_refno like '%" . $searchByCode . "%' OR si_date like '%" . $searchByCode . "%') ";
}
if ($searchByStatus != '') {
    $searchQuery .= " and (send_purchase_status = " . $searchByStatus . ") ";
}


if ($searchByBranch != '') {
    
    $searchQuery .= " and (branch_id =".$searchByBranch.") ";
}
if ($searchByYear != '') {
    
    $searchQuery .= " and (si_finyr =".$searchByYear.") ";
}
## Fetch records
if($_SESSION['_user_id']==1 || $_SESSION['_user_branch']==1)
{
    ## Total number of records without filtering
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_store_indent WHERE si_status > 0" . $searchQuery);
    $records = $sel->fetch();
    $totalRecords = $records->allcount;

    ## Total number of records with filtering
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_store_indent WHERE si_status > 0 " . $searchQuery);
    $records = $sel->fetch();
    $totalRecordwithFilter = $records->allcount;

   $cusQuery = "SELECT * FROM tbl_store_indent  WHERE 1 = 1 " . $searchQuery .
    " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;
}
else
{
    ## Total number of records without filtering
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_store_indent  WHERE branch_id='".$_SESSION['_user_branch']."' ");
    $records = $sel->fetch();
    $totalRecords = $records->allcount;

    ## Total number of records with filtering
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_store_indent  WHERE branch_id='".$_SESSION['_user_branch']."' " . $searchQuery);
    $records = $sel->fetch();
    $totalRecordwithFilter = $records->allcount;

    $cusQuery = "SELECT * FROM tbl_store_indent  WHERE branch_id='".$_SESSION['_user_branch']."' AND 1 = 1 " . $searchQuery .
    " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;

}


//$cusQuery = "SELECT po_id,po_code, po_date, supp_id, po_value, po_status FROM tbl_grn WHERE grn_del_status = 0 ";

$cusRecords = $conn->query($cusQuery);
$data = array();

$sno = 1;

while ($row = $cusRecords->fetch()) {
    //   $edit_link = '<a href="store_indent_add.php?si_id=' . $row->si_id . '" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';

   
   
    if ($row->si_status == 1) {
        $si_approval_status = '<span class="badge bg-grey">Draft</span>';
        $edit_link = '<a href="store_indent_add.php?si_id=' . $row->si_id . '" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';
        $si_print = '<a href="store_indent_print.php?si_id=' . $row->si_id . '" data-popup="tooltip" title="Print" data-original-title="Print" ><i class="icon-printer bg-edit mr-2"></i></a>';

    }
    
    if($row->send_purchase_status == 1){

        $si_approval_status = '<span class="badge bg-success">Completed</span>';

        $edit_link = '<a href="" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit-disabled mr-2"></i></a>';



    }
    $total_count = $dbconn->GetCount("tbl_store_indent_details", "si_id", $row->si_id);
    
    $si_items = $dbconn->GetSingleReconrd("tbl_store_indent_details","SUM(si_qty)","si_id",$row->si_id);

    $total_items = $si_items. ' / ' .$total_count ;

   
    $data[] = array(
        "sno" => $sno,
        "si_id" => $row->si_refno,
        "si_date" => date("d-m-Y", strtotime($row->si_date)),
        "si_items" => $total_items,
        "send_purchase_status" => $si_approval_status,
        "action" => $edit_link . $si_print
        //"action"=>$edit_link.$del_link.$po_print.$po_inward_link.$invoice_add_link
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
