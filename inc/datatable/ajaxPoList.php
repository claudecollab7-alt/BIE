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

if ($columnName == '') {
    $columnName = " grn_date ";
}
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
    $searchQuery .= " and (a.po_refno like '%" . $searchByCode . "%'  OR b.supp_name like '%" . $searchByCode . "%') ";
}
if ($searchByStatus != '') {
    $searchQuery .= " and (a.po_status = " . $searchByStatus . ") ";
}
if ($searchBySupp != '') {
    $searchQuery .= " and (b.supp_id = " . $searchBySupp . ") ";
}

if ($searchByBranch != '') {
    
    $searchQuery .= " and (a.branch_id =".$searchByBranch.") ";
}
if ($searchByYear != '') {
    
    $searchQuery .= " and (a.po_finyr =".$searchByYear.") ";
}
/*
$app_user = $dbconn->GetSingleReconrd("mst_task_setting","app_usr_id"," task_id", 1 );
if($app_user == $_SESSION['_user_id']){
	$searchQuery .= " AND a.grn_status > 1 ";
}
*/

## Total number of records without filtering
// $sel = $conn->query("SELECT count(*) as allcount FROM tbl_purchase_order a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id WHERE a.po_status > 0" . $searchQuery);
// $records = $sel->fetch();
// $totalRecords = $records->allcount;

## Total number of records with filtering
// $sel = $conn->query("SELECT count(*) as allcount FROM tbl_purchase_order a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id WHERE a.po_status > 0 " . $searchQuery);
// $records = $sel->fetch();
// $totalRecordwithFilter = $records->allcount;

## Fetch records
if($_SESSION['_user_id']==1 || $_SESSION['_user_branch']==1)
{
    ## Total number of records without filtering
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_purchase_order a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id WHERE a.po_status > 0" . $searchQuery);
    $records = $sel->fetch();
    $totalRecords = $records->allcount;

    ## Total number of records with filtering
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_purchase_order a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id WHERE a.po_status > 0 " . $searchQuery);
    $records = $sel->fetch();
    $totalRecordwithFilter = $records->allcount;

   $cusQuery = "SELECT * FROM tbl_purchase_order a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id WHERE 1 = 1 " . $searchQuery .
    " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;
}
else
{
    ## Total number of records without filtering
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_purchase_order a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id WHERE branch_id='".$_SESSION['_user_branch']."' ");
    $records = $sel->fetch();
    $totalRecords = $records->allcount;

    ## Total number of records with filtering
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_purchase_order a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id WHERE branch_id='".$_SESSION['_user_branch']."' " . $searchQuery);
    $records = $sel->fetch();
    $totalRecordwithFilter = $records->allcount;

    $cusQuery = "SELECT * FROM tbl_purchase_order a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id WHERE branch_id='".$_SESSION['_user_branch']."' AND 1 = 1 " . $searchQuery .
    " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;

}


//$cusQuery = "SELECT po_id,po_code, po_date, supp_id, po_value, po_status FROM tbl_grn WHERE grn_del_status = 0 ";

$cusRecords = $conn->query($cusQuery);
$data = array();

$sno = 1;

while ($row = $cusRecords->fetch()) {

    $supp_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_id", $row->supp_id);
    $po_print = '';
    $grn_link = '';
    $grn_status= $final_grn='';
    $grn_details_qty = '';
    
    if($_SESSION['_user_id']==1 || $_SESSION['_user_branch']==1){

        if($row->po_status == 0 || $row->po_status == 1 || $row->po_status == 2 || $row->po_status == 4){

        $edit_link = '<a href="direct_purchase_order.php?po_id=' . $row->po_id . '" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';
        $del_link = '<a href="" class="" data-popup="tooltip" title="Remove" data-original-title="Remove" ><i class="icon-bin bg-delete-disabled mr-2"></i></a>';
           

        }
        elseif($row->po_status == 3 || $row->po_status == 5){

            $edit_link = '<a href="javascript:;" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit-disabled mr-2"></i></a>';

           
        }else{
            $edit_link ='';
            $del_link = '';

        }

    }

        if ($row->po_status == 0) {
            $po_approval_status = '<span class="badge bg-grey">Draft</span>';
          //  $edit_link = '<a href="direct_purchase_order.php?po_id=' . $row->po_id . '" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';
        } elseif ($row->po_status == 1) {
            $po_approval_status = '<span class="badge bg-info">In Verification</span>';
           // $edit_link = '<a href="javascript:;" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit-disabled mr-2"></i></a>';
            
        } elseif ($row->po_status == 2) {
            $po_approval_status = '<span class="badge bg-danger">Verification Rejected</span>';
           // $edit_link = '<a href="direct_purchase_order.php?po_id=' . $row->po_id . '" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';
        } elseif ($row->po_status == 3) {
            $po_approval_status = '<span class="badge bg-primary">In Approval</span>';
           // $edit_link = '<a href="javascript:;" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit-disabled mr-2"></i></a>';
            $po_print = '<a href="po_print.php?po_id=' . $row->po_id . '" data-popup="tooltip" title="Print" data-original-title="Print" ><i class="icon-printer bg-edit mr-2"></i></a>';


        } elseif ($row->po_status == 4) {
            $po_approval_status = '<span class="badge bg-danger">Approval Rejected</span>';
           // $edit_link = '<a href="direct_purchase_order.php?po_id=' . $row->po_id . '" data-popup="tooltip" title="" data-original-title="Print" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';
        } elseif ($row->po_status == 5) {
            $po_approval_status = '<span class="badge bg-success">Approved</span>';
           // $edit_link = '<a href="javascript:;" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit-disabled mr-2"></i></a>';
            // $grn_link = '<a href="grn_add.php?po_id=' . $row->po_id . '" data-popup="tooltip" title="GRN1" data-original-title="GRN" ><i class="icon-link bg-edit mr-2"></i></a>';
            $po_print = '<a href="po_print.php?po_id=' . $row->po_id . '" data-popup="tooltip" title="Print" data-original-title="Print" ><i class="icon-printer bg-edit mr-2"></i></a>';

        }
   

    $grn_id = $dbconn->GetCount("tbl_purchase_order_details", "po_id", $row->po_id);

    // if ($row->grn_status == 0) {
    // } elseif ($row->grn_status == 1) {
    // } elseif ($row->grn_status == 2) {
    //     $grn_link = '<i class="icon-link bg-disabled mr-2"></i>';
    // }
    // if ($row->grn_status == 2) {
    //     $grn_link = '<i class="icon-link bg-disabled mr-2"></i>';
    // }

    // if ($row->po_approve_status == 1) {
        
    //     $del_link = '<a href="" class="" data-popup="tooltip" title="Remove" data-original-title="Remove" ><i class="icon-bin bg-delete-disabled mr-2"></i></a>';
    //     $invoice_add_link = '<a href="supp_invoice_add.php?po_id=' . $row->po_id . '" data-popup="tooltip" title="Invoice" data-original-title="Edit" ><i class="icon-pencil3 bg-edit mr-2"></i></a>';
    // } else {
    //     $del_link = '<a href="" class="" data-popup="tooltip" title="Remove" data-original-title="Remove" ><i class="icon-bin bg-delete-disabled mr-2"></i></a>';
    //     $invoice_add_link = '<a href="" data-popup="tooltip" title="Invoice" data-original-title="Edit" ><i class="icon-pencil3 bg-edit-disabled mr-2"></i></a>';
    // }

    $approve_count = 0;
    $max_grn_id = '';
    $total_count = $dbconn->GetCount("tbl_purchase_order_details", "po_id", $row->po_id);
    $grn_id = $dbconn->GetSingleReconrd("tbl_grn", "MAX(grn_id)", "po_id", $row->po_id);

    if ($grn_id != '') {
        $approve_count1 = $dbconn->GetCount("tbl_purchase_order_details", "po_id", $row->po_id);
        $grn_link = '<a href="grn_add.php?po_id=' . $row->po_id . '&grn_id=' . $grn_id . '" data-popup="tooltip" title="Branch" data-original-title="GRN" ><i class="icon-link bg-edit mr-2"></i></a>';
    $grn_grn_status = $dbconn->GetSingleReconrd("tbl_grn", "grn_status", "grn_id", $grn_id);
    }
   
    $approve_qty = $dbconn->GetSingleReconrd("tbl_purchase_order_details","SUM(po_qty)","po_id",$row->po_id);

    $full_grn = $approve_qty. ' / ' .$total_count;

    $grn_ids = $dbconn->GetSingleReconrd("tbl_grn","group_concat(grn_id)","grn_status = 2 AND po_id",$row->po_id);

    if($grn_ids != ''){
        $grn_details_qty = $dbconn->GetSingleReconrd("tbl_grn_details","SUM(grn_accepted_qty)","grn_id IN ($grn_ids) AND grn_accepted_qty > 0 AND 1",1);

    }
 
    $grn_end_id = $dbconn->GetSingleReconrd("tbl_grn","grn_id","grn_status != 2 AND po_id",$row->po_id);
 
    if (($row->po_approve_status == 1) && $approve_qty != $grn_details_qty) {
           
        
        if($grn_end_id != ''){

           // $grn_link = '<a href="" data-popup="tooltip" title="GRN"  data-original-title="GRN" ><i class="icon-link bg-edit-disabled mr-2"></i></a>';
              $grn_link = '<a href="grn_add.php?grn_id='.$grn_id.'" data-popup="tooltip" title="Edit" data-original-title="GRN" ><i class="icon-link bg-edit mr-2"></i></a>';

    
       }else{
        $grn_link = '<a href="grn_add.php?po_id=' . $row->po_id . '" data-popup="tooltip" title="GRN" data-original-title="GRN" ><i class="icon-link bg-edit mr-2"></i></a>';
    
       }

        
    }
    else{

        $grn_link = '<a href="" data-popup="tooltip" title="GRN"  data-original-title="GRN" ><i class="icon-link bg-edit-disabled mr-2"></i></a>';


    }
   


    $grn_nos="";

  

    if($row->po_approve_status == 1){

        $grn_data = $dbconn->GetSingleReconrd("tbl_grn","group_concat(grn_id,'~',grn_slno)","grn_status = 2 AND po_id",$row->po_id);

        if($grn_data !="")
        {
            $grn_dets = explode(",",$grn_data);
            foreach ($grn_dets as $value) {
                $ind_grn_dets = explode("~",$value);
                
                $grn_nos .=  '<a target="_blank" href="grn_view.php?grn_id='.$ind_grn_dets[0].'">'.$ind_grn_dets[1]. "</a>,";
            }
        }
        $final_grn = '<span class="badge bg-success">'.$grn_details_qty. ' / ' .$approve_qty.'</span>';

        // $full_pi = '<span class="label label-success">'.$pi_details_qty. ' / ' .$total_count.'</span>';
    }else{
        $final_grn = '';
    }


    if($row->po_approve_status == 1 && $row->grn_status >= 0){
												
        if($approve_qty == 0 || $grn_details_qty == ''){
            $grn_status = '<span class="badge bg-info">Not Yet</span>';

        }else if($approve_qty == $grn_details_qty){
            $grn_status = '<span class="badge bg-success">Completed</span>';
        }
        else {
            $grn_status = '<span class="badge bg-grey">Partial</span>';
        }
    }
    

    // if ($total_count == $approve_count) {
    //     $full_grn = $approve_count;
    // } else {
    //     $full_grn = $total_count;
    // }


    $branch_link = '<a href="mst_customer_branch.php?supp_id=' . $row->supp_id . '" data-popup="tooltip" title="Branch" data-original-title="Branch" ><i class="icon-link bg-edit mr-2"></i></a>';
    $data[] = array(
        "sno" => $sno,
        "po_id" => $row->po_refno,
        "po_date" => date("d-m-Y", strtotime($row->po_date)),
        "supp_name" => $supp_name,
        "po_value" => $row->po_value,
        "po_status" => $po_approval_status,
        "full_grn" => $full_grn,
        "grn_status" => $grn_status,
        "grn_deli_qty" => $final_grn.'<br>'.$grn_nos ,
        "action" => $edit_link . $del_link . $po_print. $grn_link
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
