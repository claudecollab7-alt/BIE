<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();

ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);


## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = $_POST['search']['value']; // Search value

if ($columnName == '') {
    $columnName = " item_invoice ";
}
## Custom Field value
$searchByCode = isset($_POST['searchByCode']) ? $_POST['searchByCode'] : '';

## Search 
$searchQuery = " ";
$app_qry = " ";
if ($searchByCode != '') {
    $searchQuery .= " and (inv_refno like '%" . $searchByCode . "%' ) ";
}
$sel = $conn->query("SELECT count(*) as allcount FROM tbl_invoice  WHERE 1 = 1" . $searchQuery);
$records = $sel->fetch();
$totalRecords = $records->allcount;

## Total number of records with filtering
$sel = $conn->query("SELECT count(*) as allcount FROM tbl_invoice  WHERE 1 = 1" . $searchQuery);
$records = $sel->fetch();
$totalRecordwithFilter = $records->allcount;

## Fetch records
$itemQuery = "SELECT * FROM tbl_invoice WHERE 1 = 1" . $searchQuery .
    " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage ;

$sno = 1;
$itemRecords = $conn->query($itemQuery);
$data = array();

while ($row = $itemRecords->fetch())
	{
    	//$item_count = $dbconn->GetSingleReconrd("tbl_invoice_details","COUNT(inv_id)","inv_accepted_qty !='0' AND inv_id",$row->inv_id);

		$supp_name = $dbconn->GetSingleReconrd("mst_supplier_new","supp_name","supp_id",$row->supp_id);
		$branch_name = $dbconn->GetSingleReconrd("mst_customer_branch","branch_name","branch_id",$row->cus_branch_id);
		$pay_name = $dbconn->GetSingleReconrd("mst_pay_method","pay_name","pay_id",$row->pay_id);
		$net_amount = $dbconn->GetSingleReconrd("tbl_invoice_details","SUM(net_value)","inv_id",$row->inv_id);
	    if($row->inv_status == 1)
		{
            $edit_link = '<i class="icon-pencil5 bg-edit-disabled mr-2"></i>';
		}
		else
		{
            $edit_link = '<a href="mng_invoice.php?inv_id='.$row->inv_id.'" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';
		}
	

		//$grn_print_link = '<li><a href="grn_print.php?grn_id='.$row->grn_id.'" class="tip " title="View GRN"><i class="fa fa-print"></i></a></li>';
        $inv_print_link = '<a href="invoice_print.php?inv_id='.$row->inv_id.'" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-printer bg-edit mr-2"></i></a>';		
        if($row->pay_id == 2){
            $pay_name .= '<br><small>'.$row->pay_chq_no.'</small>';
        }
        elseif($row->pay_id == 3){
            $pay_name .= '<br><small>'.$row->pay_cardno.'</small>';
        }
        elseif($row->pay_id == 4 ||$row->pay_id== 5 || $row->pay_id==6 ){
            $pay_name .= '<br><small>'.$row->pay_refno.'</small>';
        }

            
	
  $data[] = array(
        "inv_id" => $sno,
        "itm_inv_no" => $row->inv_refno,
        "itm_inv_date" => date("d-m-Y", strtotime($row->inv_date)),
        "itm_inv_supp" => $supp_name.'<br><small>'.$branch_name.'</small>',
        "itm_inv_amt" => $net_amount,
        "itm_inv_paymode" => $pay_name,
        "itm_inv_Due" => 'NILL',
        "action" => $edit_link.$inv_print_link
		
         
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
