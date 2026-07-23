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
    $searchQuery .= " and (send_admin_status = " . $searchByStatus . ") ";
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
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_store_indent WHERE send_purchase_status > 0" . $searchQuery);
    $records = $sel->fetch();
    $totalRecords = $records->allcount;

    ## Total number of records with filtering
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_store_indent WHERE send_purchase_status > 0 " . $searchQuery);
    $records = $sel->fetch();
    $totalRecordwithFilter = $records->allcount;

   $cusQuery = "SELECT * FROM tbl_store_indent  WHERE send_purchase_status > 0 " . $searchQuery .
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

    $cusQuery = "SELECT * FROM tbl_store_indent  WHERE branch_id='".$_SESSION['_user_branch']."' AND send_purchase_status > 0 " . $searchQuery .
    " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;

}


//$cusQuery = "SELECT po_id,po_code, po_date, supp_id, po_value, po_status FROM tbl_grn WHERE grn_del_status = 0 ";

$cusRecords = $conn->query($cusQuery);
$data = array();

$sno = 1;

while ($row = $cusRecords->fetch()) {

    $curr_plus_icon = 0;
    $po_prepare = "";
    $po_prepare_id = "";
    // $si_print = '<a href="store_indent_print.php?si_id=' . $row->si_id . '" data-popup="tooltip" title="Print" data-original-title="Print" ><i class="icon-printer bg-edit mr-2"></i></a>';
    // $edit_link = '<a href="po_prepare.php?si_id=' . $row->si_id . '" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';
   
   
    $item_prepare_status = $dbconn->GetSingleReconrd("tbl_po_prepare_dets","SUM(item_prepare_status)","si_id",$row->si_id);
    $send_admin_status = $dbconn->GetSingleReconrd("tbl_po_prepare","send_admin_status","si_id",$row->si_id);
    
    
    $total_items_count = $dbconn->GetSingleReconrd("tbl_po_prepare_dets","count(*)","si_id",$row->si_id);
    
    $si_dets = $dbconn->GetSingleReconrd("tbl_store_indent","CONCAT(si_refno,'~',si_date)","si_id",$row->si_id);
    
    $si_det = explode('~',$si_dets);
    $supp_name = $dbconn->GetSingleReconrd("mst_supplier_new","supp_name","supp_id",$row->supp_id);
    $branch_name = $dbconn->GetSingleReconrd("mst_customer_branch","branch_name","supp_id",$row->supp_id);
    if($branch_name!='') {
        $branch_name = ' <i>'.$branch_name.'</i>';
    }
    
    if($item_prepare_status == $total_items_count )
        $indent_status = "";
    elseif($item_prepare_status == ($total_items_count*3))
        $indent_status = '<span class="badge bg-success">Completed</span>';
    else
    {
        if($send_admin_status == 1 || $item_prepare_status == 1)
        {
            $indent_status = '<span class="badge bg-warning">Pending</span>';
        }else{
             $indent_status = '<span class="badge bg-grey">Draft</span>';
        }
    }
        


    // $created_dept = $dbconn->GetSingleReconrd("mst_task_setting","department_id","task_id",6);
    
    if($row->send_purchase_status == 1)
    {
    $si_print = '<a href="store_indent_print.php?si_id='.$row->si_id.'" class="tip" title="View Store Indent"><i class="icon-eye bg-edit mr-2"></i></a>';
    
        $po_prepare_id = $dbconn->GetSingleReconrd("tbl_po_prepare","po_prepare_id","po_prepare_status = 1 AND si_id",$row->si_id);
      
        if($po_prepare_id>0){
            $admin_status = $dbconn->GetSingleReconrd("tbl_po_prepare","send_admin_status","po_prepare_status = 1 AND po_prepare_id",$po_prepare_id);
            $send_to_admin_status = $dbconn->GetSingleReconrd("tbl_po_prepare","send_to_admin_status","po_prepare_id",$po_prepare_id);
            
            if($admin_status == 0 && $item_prepare_status != 0 && $send_to_admin_status == 1){
                $po_prepare = '<a href="po_prepare.php?si_id='.$row->si_id.'" class="tip" title="Prepare PO"><i class="fa fa-plus"></i></a>';
                $curr_plus_icon = 1;
            }else{	
                if($send_to_admin_status == 1){													
                    $po_prepare .= '<a href="po_prepare_print.php?po_prepare_id='.$po_prepare_id.'" class="tip" title="PO Prepare"><i class="icon-printer bg-edit mr-2"></i></a>';
                }
            }
        }else{
            $po_prepare = '<a href="po_prepare.php?si_id='.$row->si_id.'" class="tip" title="Prepare PO"><i class="fa fa-plus"></i></a>';
            $curr_plus_icon = 1;
        }
    }else{
    $si_print = '<a href="store_indent_print.php?si_id='.$row->si_id.'" class="tip" title="View Store Indent"><i class="icon-eye bg-edit mr-2"></i></a>';
    }
    
    $sum_pi_qty = $dbconn->GetSingleReconrd("tbl_po_prepare_dets","SUM(si_qty)","si_id",$row->si_id);
    $sum_po_qty = $dbconn->GetSingleReconrd("tbl_store_indent_details","SUM(si_qty)","si_id",$row->si_id);
    $new_plus_icon = '';
    if(($sum_pi_qty != $sum_po_qty || $item_prepare_status != ($total_items_count*3)) && $curr_plus_icon == 0 && $send_admin_status < 2){
        
        $new_plus_icon = '<a href="po_prepare.php?si_id='.$row->si_id.'" class="tip" title="Prepare PO"><i class="fa fa-plus"></i></a>';
        
        
    }
    $total_count = $dbconn->GetCount("tbl_store_indent_details", "si_id", $row->si_id);
    
    $si_items = $dbconn->GetSingleReconrd("tbl_store_indent_details","SUM(si_qty)","si_id",$row->si_id);

    $total_items = $si_items. ' / ' .$total_count ;
    
   

    // $branch_link = '<a href="mst_customer_branch.php?supp_id=' . $row->supp_id . '" data-popup="tooltip" title="Branch" data-original-title="Branch" ><i class="icon-link bg-edit mr-2"></i></a>';
    $data[] = array(
        "sno" => $sno,
        "si_id" =>  $row->si_refno,
        "si_date" => date("d-m-Y", strtotime($row->si_date)),
        "si_items" =>$total_items,
        "send_admin_status" =>$indent_status,
        "action" => $si_print . $po_prepare . $new_plus_icon 
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
