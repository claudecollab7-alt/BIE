<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);


## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = $_POST['search']['value']; // Search value

if ($columnName == '') {
    $columnName = " item_grn ";
}
## Custom Field value
$searchByCode = isset($_POST['searchByCode']) ? $_POST['searchByCode'] : '';
$searchByBranch = isset($_POST['searchByBranch']) ? $_POST['searchByBranch'] : '';
$searchByYear = isset($_POST['searchByYear']) ? $_POST['searchByYear'] : '';



## Search 
$searchQuery = " ";
$app_qry = " ";
if ($searchByCode != '') {
    $searchQuery .= " and (a.rtn_refno like '%" . $searchByCode . "%' OR a.rtn_slno like '%" . $searchByCode . "%' OR b.supp_name like '%" . $searchByCode . "%' ) ";
}
if ($searchByBranch != '') {
    
    $searchQuery .= " and (a.branch_id =".$searchByBranch.") ";
}
if ($searchByYear != '') {
    
    $searchQuery .= " and (a.rtn_finyr =".$searchByYear.") ";
}
if($_SESSION['_user_id']==1 || $_SESSION['_user_branch']==1)
{
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_purchase_return a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id  WHERE 1 = 1" . $searchQuery);
    $records = $sel->fetch();
    $totalRecords = $records->allcount;

    ## Total number of records with filtering
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_purchase_return a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id  WHERE 1 = 1" . $searchQuery);
    $records = $sel->fetch();
    $totalRecordwithFilter = $records->allcount;

    ## Fetch records
    $itemQuery = "SELECT * FROM tbl_purchase_return a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id  WHERE 1 = 1" . $searchQuery .
        " order by a." . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage ;
}
else
{
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_purchase_return a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id  WHERE branch_id='".$_SESSION['_user_branch']."' ");
    $records = $sel->fetch();
    $totalRecords = $records->allcount;

    ## Total number of records with filtering
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_purchase_return a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id  WHERE branch_id='".$_SESSION['_user_branch']."' " . $searchQuery);
    $records = $sel->fetch();
    $totalRecordwithFilter = $records->allcount;

    ## Fetch records
    $itemQuery = "SELECT * FROM tbl_purchase_return a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id  WHERE branch_id='".$_SESSION['_user_branch']."' AND 1 = 1 "  . $searchQuery .
        " order by a." . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage ;
}

$sno = 1;
$itemRecords = $conn->query($itemQuery);
$data = array();




while ($row = $itemRecords->fetch())
	{
    	$item_count = $dbconn->GetSingleReconrd("tbl_grn_details","COUNT(grn_id)","grn_accepted_qty !='0' AND grn_id",$row->grn_id);
    	$grn_pending_qty = $dbconn->GetSingleReconrd("tbl_grn_details","grn_pending_qty","grn_id",$row->grn_id);
    	$grn_accepted_qty = $dbconn->GetSingleReconrd("tbl_grn_details","grn_accepted_qty","grn_id",$row->grn_id);

		$supp_name = $dbconn->GetSingleReconrd("mst_supplier_new","supp_name","supp_id",$row->supp_id);
		$po_id = $dbconn->GetSingleReconrd("tbl_grn","po_id","grn_id",$row->grn_id);
		$po_refno = $dbconn->GetSingleReconrd("tbl_purchase_order","po_refno","po_id",$po_id);

		$grn_status = '';

        // $grn_id = $dbconn->GetSingleReconrd("tbl_grn", "MAX(grn_id)", "po_id", $row->po_id);
        if($row->rtn_verify_status == 1){

        $grn_print_link = '<a href="purchase_return_view.php?rtn_id='.$row->rtn_id.'" data-popup="tooltip" title="View GRN" data-original-title="View GRN" ><i class="icon-eye bg-edit   mr-2"></i></a>';		


        }else{

        $grn_print_link='';
        }

        $grn_refno = '';
        $party_dc_no = '';
        $bill_status = '';

        $grn_refno = $dbconn->GetSingleReconrd("tbl_grn","grn_refno","grn_id",$row->grn_id);
        // $party_dc_no = $dbconn->GetSingleReconrd("tbl_purchase_inward","party_dc_no","pi_id",$row->pi_id);

     
        if($_SESSION['_user_id']== 1 || $_SESSION['_user_branch']==1){

            if($row->grn_status != 2 && $row->grn_cancel_status != 1){
                $reject_link = '<a data-toggle="modal" data-target="#modalRejectGrnDets" href="" data-id="'.$row->grn_id.'" data-popup="tooltip" title="GRN Details"><i class="icon-bin bg-delete mr-2"></i></a>';
            
                // $so_status = '<span class="badge bg-success">Approved</span>';


            }else{
                $reject_link = '<a href="javascript:;" class="tip disable" title="Cancel Sales Order"><i class="icon-bin bg-delete-disabled mr-2"></i></a>';
                // $grn_link = '<a href="javascript:;" class="tip disable" title="GRN"><i class="icon-pencil5 bg-edit-disabled mr-2"></i></a>';

            }
        }

            if ($row->grn_status == 3 && $row->grn_cancel_status == 1) {

                $bill_status = '<span class="badge bg-danger">Cancelled</span><br><b>'.$row->grn_cancel_reason;
                // $so_print = '<a href="print_sales_order.php?so_id=' . $row->so_id . '" class="tip" title="Print Sales Order"><i class="icon-printer bg-edit mr-2"></i></a>';
                $reject_link = '<a href="javascript:;" class="tip disable" title="Cancel Sales Order"><i class="icon-bin bg-delete-disabled mr-2"></i></a>';


            }

            $return_approve_status = $dbconn->GetSingleReconrd("tbl_purchase_return","rtn_approve_status","grn_id",$row->grn_id);

            $return_count = $dbconn->GetSingleReconrd("tbl_grn_details","SUM(grn_rejected_qty)","grn_id",$row->grn_id);
            
            if($row->rtn_id !='' )
            {
                $purchase_return_add = '<a href="purchase_return_add.php?rtn_id='.$row->rtn_id.'" class="tip"  data-popup="tooltip" title="Generate Purchase Return"><i class="fa fa-arrow-circle-down mr-2"></i></a>';
            }	
            else
            {											
            $purchase_return_add = '<a  class="tip disable" data-popup="tooltip" title="Generate Purchase Return"><i class="fa fa-arrow-circle-down mr-2"></i></a>';
            }

            if ($row->rtn_status == 1 && $row->rtn_approve_status == 0 && $row->rtn_verify_status == 0) {
                $bill_status = '<span class="badge bg-grey">Draft</span>';
                $purchase_return_add = '<a href="purchase_return_add.php?rtn_id='.$row->rtn_id.'" class="tip"  data-popup="tooltip" title="Generate Purchase Return"><i class="fa fa-arrow-circle-down mr-2"></i></a>';
            } elseif ($row->rtn_verify_status == 1 && $row->rtn_approve_status == 0) {
                $bill_status = '<span class="badge bg-primary">In Approval</span>';
                $purchase_return_add = '<a  class="tip disable" data-popup="tooltip" title="Generate Purchase Return"><i class="fa fa-arrow-circle-down mr-2"></i></a>';
            } elseif ($row->rtn_approve_status == 2 && $row->rtn_verify_status == 1) {
                $purchase_return_add = '<a  class="tip disable" data-popup="tooltip" title="Generate Purchase Return"><i class="fa fa-arrow-circle-down mr-2"></i></a>';
                $bill_status = '<span class="badge bg-danger">Approval Rejected</span>';
            } elseif ($row->rtn_approve_status == 1 && $row->rtn_verify_status == 1) {
                $purchase_return_add = '<a  class="tip disable" data-popup="tooltip" title="Generate Purchase Return"><i class="fa fa-arrow-circle-down mr-2"></i></a>';
                $bill_status = '<span class="badge bg-success">Approved</span>';
    
            }
            
	
  $data[] = array(
        "grn_id" => $sno,
        "rtn_refno" => $row->rtn_refno,
        "grn_refno" => $grn_refno,
        "itm_grn_date" => date("d-m-Y", strtotime($row->rtn_date)),
        "supp_id" => $supp_name,
        "po_refno" =>$po_refno,
        "itm_grn_items" => $item_count,
        "rtn_status" => $bill_status,
        "action" =>  $purchase_return_add. $grn_print_link . $reject_link
		
         
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
