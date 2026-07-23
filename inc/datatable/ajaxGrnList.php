<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();

//ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);


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
    $searchQuery .= " and (a.grn_slno like '%" . $searchByCode . "%' OR a.grn_ref_code like '%" . $searchByCode . "%'  OR a.party_dc_no like '%" . $searchByCode . "%'
    OR a.grn_refno like '%" . $searchByCode . "%' OR b.supp_name like '%" . $searchByCode . "%' ) ";
}
if ($searchByBranch != '') {
    
    $searchQuery .= " and (branch_id =".$searchByBranch.") ";
}
if ($searchByYear != '') {
    
    $searchQuery .= " and (a.grn_finyr =".$searchByYear.") ";
}
if($_SESSION['_user_id']==1 || $_SESSION['_user_branch']==1)
{
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_grn a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id  WHERE 1 = 1" . $searchQuery);
    $records = $sel->fetch();
    $totalRecords = $records->allcount;

    ## Total number of records with filtering
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_grn a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id  WHERE 1 = 1" . $searchQuery);
    $records = $sel->fetch();
    $totalRecordwithFilter = $records->allcount;

    ## Fetch records
    $itemQuery = "SELECT * FROM tbl_grn a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id WHERE 1 = 1" . $searchQuery .
        " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage ;
}
else
{
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_grn a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id WHERE branch_id='".$_SESSION['_user_branch']."' ");
    $records = $sel->fetch();
    $totalRecords = $records->allcount;

    ## Total number of records with filtering
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_grn a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id WHERE branch_id='".$_SESSION['_user_branch']."' " . $searchQuery);
    $records = $sel->fetch();
    $totalRecordwithFilter = $records->allcount;

    ## Fetch records
    $itemQuery = "SELECT * FROM tbl_grn a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id  WHERE branch_id='".$_SESSION['_user_branch']."' AND 1 = 1 "  . $searchQuery .
        " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage ;
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
		$po_refno = $dbconn->GetSingleReconrd("tbl_purchase_order","po_refno","po_id",$row->po_id);

		$grn_status = '';

        // $grn_id = $dbconn->GetSingleReconrd("tbl_grn", "MAX(grn_id)", "po_id", $row->po_id);
        if($row->grn_status == 2){

        $grn_link = '<a href="" data-popup="tooltip" title="GRN" data-original-title="Edit" ><i class="icon-pencil5 bg-edit-disabled mr-2"></i></a>';
        $grn_print_link = '<a href="grn_view.php?grn_id='.$row->grn_id.'" data-popup="tooltip" title="View GRN" data-original-title="View GRN" ><i class="icon-eye bg-edit   mr-2"></i></a>';		


        }else{

        $grn_link = '<a href="grn_add.php?grn_id='.$row->grn_id.'" data-popup="tooltip" title="Edit" data-original-title="GRN" ><i class="icon-pencil5 bg-edit   mr-2"></i></a>';
        $grn_print_link='';
        }

        $grn_refno = '';
        $party_dc_no = '';
        $bill_status = '';

        // $grn_refno = $dbconn->GetSingleReconrd("tbl_grn","grn_refno","pi_id",$row->pi_id);
        // $party_dc_no = $dbconn->GetSingleReconrd("tbl_purchase_inward","party_dc_no","pi_id",$row->pi_id);

       if($row->grn_status < 2){

            $bill_status = '<span class="badge bg-info">Draft</span>';
            $grn_link = '<a href="grn_add.php?grn_id='.$row->grn_id.'" class="tip" title="Edit GRN"><i class="icon-pencil5 bg-edit mr-2"></i></a>';

        } else if($row->grn_refno == ''){
            $bill_status = '<span class="badge bg-info">Pending</span>';
            $grn_link = '<a href="grn_add.php?grn_id='.$row->grn_id.'" class="tip" title="Edit GRN"><i class="icon-pencil5 bg-edit mr-2"></i></a>';
        }
        else
        {
            $grn_link = '<a href="javascript:;" class="tip disable" title="GRN"><i class="icon-pencil5 bg-edit-disabled mr-2"></i></a>';
            $bill_status = '<span class="badge bg-success">Done</span>';

        }
        /*if($_SESSION['_user_id']== 1 || $_SESSION['_user_branch']==1){

            if($row->grn_status != 2 && $row->grn_cancel_status != 1){
                $reject_link = '<a data-toggle="modal" data-target="#modalRejectGrnDets" href="" data-id="'.$row->grn_id.'" data-popup="tooltip" title="GRN Details"><i class="icon-bin bg-delete mr-2"></i></a>';
            
                // $so_status = '<span class="badge bg-success">Approved</span>';


            }else{
                $reject_link = '<a href="javascript:;" class="tip disable" title="Cancel Sales Order"><i class="icon-bin bg-delete-disabled mr-2"></i></a>';
                // $grn_link = '<a href="javascript:;" class="tip disable" title="GRN"><i class="icon-pencil5 bg-edit-disabled mr-2"></i></a>';

            }
        }*/

            if ($row->grn_status == 3 && $row->grn_cancel_status == 1) {

                $bill_status = '<span class="badge bg-danger">Cancelled</span><br><b>'.$row->grn_cancel_reason;
                $grn_link = '<a href="javascript:;" data-popup="tooltip" title="Edit"><i class="icon-pencil5 bg-edit-disabled mr-2"></i></a>';
                // $so_print = '<a href="print_sales_order.php?so_id=' . $row->so_id . '" class="tip" title="Print Sales Order"><i class="icon-printer bg-edit mr-2"></i></a>';
                //$reject_link = '<a href="javascript:;" class="tip disable" title="Cancel Sales Order"><i class="icon-bin bg-delete-disabled mr-2"></i></a>';


            }

            $return_approve_status = $dbconn->GetSingleReconrd("tbl_purchase_return","rtn_approve_status","grn_id",$row->grn_id);

            $return_count = $dbconn->GetSingleReconrd("tbl_grn_details","SUM(grn_rejected_qty)","grn_id",$row->grn_id);
            
            if($return_count > 0 && $return_approve_status == '')
            {
                $purchase_return_add = '<a href="purchase_return_add.php?grn_id='.$row->grn_id.'" class="tip"  data-popup="tooltip" title="Generate Purchase Return"><i class="fa fa-arrow-circle-down mr-2"></i></a>';
            }	
            else
            {											
            $purchase_return_add = '<a  class="tip disable" data-popup="tooltip" title="Generate Purchase Return"><i class="fa fa-arrow-circle-down mr-2"></i></a>';
            }
                                         
	    // if($row->grn_status == 2)
		// {
    	// 	$grn_status = '<span class="badge bg-success">Completed</span>';
		// }
		// elseif($row->grn_status == 1 )
		// {
		// 	$grn_status = '<span class="badge bg-danger">Partial</span>';
		// }
		// else
		// {
		// 	$grn_status = '<span class="badge bg-grey">Draft</span>';
		// }

		//$grn_print_link = '<li><a href="grn_print.php?grn_id='.$row->grn_id.'" class="tip " title="View GRN"><i class="fa fa-print"></i></a></li>';
            
	
  $data[] = array(
        "grn_id" => $sno,
        "grn_ref_code" => $row->grn_ref_code,
        "grn_refno" => $row->grn_refno,
        "itm_grn_date" => date("d-m-Y", strtotime($row->grn_date)),
        "supp_name" => $supp_name,
        "po_refno" =>$po_refno,
        "itm_grn_items" => $item_count,
        "bill_status" => $bill_status,
        "action" => $grn_link . $grn_print_link .$purchase_return_add
		
         
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
