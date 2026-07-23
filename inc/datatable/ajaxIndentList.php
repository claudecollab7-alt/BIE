<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn  = new dbconnect();
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
    $columnName = " a.sal_repair_id ";
}

## Custom Field value
$searchByCode = isset($_POST['searchByCode']) ? $_POST['searchByCode'] : '';

## Search
$searchQuery = " ";
if ($searchByCode != '') {
    $searchQuery .= " AND ( a.sal_repair_slno LIKE '%".$searchByCode."%'
                       OR a.sal_repair_date  LIKE '%".$searchByCode."%' ) ";
}

## Total number of records without filtering
$sel          = $conn->query("SELECT count(*) as allcount FROM tbl_sales_repair a WHERE a.sal_repair_status = '1'".$searchQuery);
$records      = $sel->fetch();
$totalRecords = $records->allcount;

## Total number of records with filtering
$sel                   = $conn->query("SELECT count(*) as allcount FROM tbl_sales_repair a WHERE a.sal_repair_status = '1'".$searchQuery);
$records               = $sel->fetch();
$totalRecordwithFilter = $records->allcount;

## Fetch records
$cusQuery = "SELECT * FROM tbl_sales_repair a
             WHERE a.sal_repair_status = '1'".$searchQuery.
            " ORDER BY ".$columnName." ".$columnSortOrder.
            " LIMIT ".$row.",".$rowperpage;

$cusRecords = $conn->query($cusQuery);
$data       = array();
$sno        = 1;

while ($row = $cusRecords->fetch())
{
    $supp_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_id", $row->supp_id);

    if ($row->sal_repair_verify_status == 1 && $row->sal_repair_approve_status == 0) {
        $repair_status = '<span class="badge badge-warning">In Approval</span>';
    } elseif ($row->sal_repair_verify_status == 1 && $row->sal_repair_approve_status == 1) {
        $repair_status = '<span class="badge badge-success">Approved</span>';
    } else {
        $repair_status = '<span class="badge badge-info">Processing</span>';
    }

    $sales_repair_print = '<a href="sales_repair_print.php?sal_repair_id='.$row->sal_repair_id.'" data-popup="tooltip" title="Print Sales Repair"><i class="icon-printer bg-edit mr-2"></i></a>';

    if ($row->sal_repair_approve_status == 1) {
        $sales_repair_add = '<a href="javascript:;" data-popup="tooltip" title="Generate Sales Repair (Disabled)"><i class="icon-download bg-delete-disabled mr-2"></i></a>';
        $quotation_link   = '<a href="quotation.php?sal_repair_id='.$row->sal_repair_id.'" data-popup="tooltip" title="Generate Quotation"><i class="icon-file-text bg-edit mr-2"></i></a>';
    } else {
        $sales_repair_add = '<a href="repair_indent_add.php?sal_repair_id='.$row->sal_repair_id.'" data-popup="tooltip" title="Edit Sales Repair"><i class="icon-download bg-edit mr-2"></i></a>';
        $quotation_link   = '';
    }

    $data[] = array(
        "sal_repair_id"            => $sno,
        "sal_repair_slno"=> leadingZeros($row->sal_repair_slno, 3),
        "sal_repair_date"=> date("d-m-Y", strtotime($row->sal_repair_date)),
        "supp_name"      => $supp_name,
        "repair_status"  => $repair_status,
        "action"         => $sales_repair_add . $sales_repair_print . $quotation_link
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