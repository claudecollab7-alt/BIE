<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn= new dbhandler();


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

if($columnName == ''){
	$columnName = " grn_date ";
}
## Custom Field value
$searchByCode = isset($_POST['searchByCode'])? $_POST['searchByCode'] : '';
$searchByStatus = isset($_POST['searchByStatus']) ? $_POST['searchByStatus'] : '';
$searchByBranch = isset($_POST['searchByBranch']) ? $_POST['searchByBranch'] : '';


## Search 
$searchQuery = " ";
$app_qry = " ";
if($searchByCode != ''){
    $searchQuery .= " and (a.supp_name like '%".$searchByCode."%' OR a.supp_code like '%".$searchByCode."%'  OR a.supp_contact_person1 like '%".$searchByCode."%'
    OR a.supp_mobile1 like '%".$searchByCode."%' OR a.supp_gst like '%".$searchByCode."%' ) ";
}

if ($searchByStatus != '') {
    $searchQuery .= " and (a.supp_approve_status = " . $searchByStatus . ") ";
}
if ($searchByBranch != '') {
    
    $searchQuery .= " and (a.company_branch_id =".$searchByBranch.") ";
}
//echo $searchQuery;exit;

/*
$app_user = $dbconn->GetSingleReconrd("mst_task_setting","app_usr_id"," task_id", 1 );
if($app_user == $_SESSION['_user_id']){
	$searchQuery .= " AND a.grn_status > 1 ";
}
*/
if($_SESSION['_user_id']== 1 || $_SESSION['_user_branch'] == 1)
{

## Total number of records without filtering
$sel = $conn->query("SELECT count(*) as allcount FROM mst_supplier_new a WHERE 1 = 1 AND a.supp_type = 'S' AND a.supp_status = 1".$searchQuery);

$records =$sel->fetch();
$totalRecords = $records->allcount;

## Total number of records with filtering
$sel = $conn->query("SELECT count(*) as allcount FROM mst_supplier_new a WHERE 1 = 1 AND a.supp_type = 'S' AND a.supp_status = 1".$searchQuery);
$records = $sel->fetch();
$totalRecordwithFilter = $records->allcount;

## Fetch records
$cusQuery = "SELECT * FROM mst_supplier_new a WHERE 1 = 1 AND a.supp_type = 'S' AND a.supp_status = 1".$searchQuery.
		" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;


}
else{

## Total number of records without filtering
$sel = $conn->query("SELECT count(*) as allcount FROM mst_supplier_new a WHERE  1 = 1 AND a.company_branch_id=".$_SESSION['_user_branch']." AND a.supp_type = 'S' AND a.supp_status = 1".$searchQuery);

$records =$sel->fetch();
$totalRecords = $records->allcount;

## Total number of records with filtering
$sel = $conn->query("SELECT count(*) as allcount FROM mst_supplier_new a WHERE  1 = 1 AND  a.company_branch_id=".$_SESSION['_user_branch']." AND a.supp_type = 'S' AND a.supp_status = 1".$searchQuery);
$records = $sel->fetch();
$totalRecordwithFilter = $records->allcount;

## Fetch records
$cusQuery = "SELECT * FROM mst_supplier_new a WHERE  1 = 1 AND a.company_branch_id=".$_SESSION['_user_branch']." AND a.supp_type = 'S' AND a.supp_status = 1".$searchQuery.
		" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;



}

$cusRecords = $conn->query($cusQuery);
$data = array();

$sno = 1;

while ($row = $cusRecords->fetch()) 
{
    $contact_no = '';
    $contact_no .= $row->supp_mobile1;
    $supp_status = '';
    $count ='';
    // $count = $dbconn->GetCount("tbl_supp_items","supp_id",$row->supp_id);
    // $data_new = '<a href = "supp_items.php?supp_id='.$row->supp_id.'">'.$count.'</a>';
    $item_assign = $dbconn->GetSingleReconrd("tbl_supp_items","supp_id","supp_item_status = '1' AND supp_id",$row->supp_id);
    $supp_user = $dbconn->GetSingleReconrd("tbl_task_user","user_id","task_id",2);

    $supp_id = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_id","supp_id",$row->supp_id);

    if($row->supp_mobile2 !=''){
        $contact_no .= ',<br/>'.$row->supp_mobile2;
    }

    if(($row->supp_id == $item_assign || $row->branch_type == 'H') && $_SESSION['_user_branch'] != 1){
        $del_link = '<a href="javascript:;" rel="'.$row->supp_id.'" data-popup="tooltip" title="Remove" data-original-title="Remove" ><i class="icon-bin bg-delete-disabled mr-2"></i></a>';
    }
    else{
        $del_link = '<a href="" class="delete" rel="'.$row->supp_id.'" data-popup="tooltip" title="Remove" data-original-title="Remove" ><i class="icon-bin bg-delete mr-2"></i></a>';
    }

    if($row->supp_approve_status == 1){
        $supp_status = '<span class="badge bg-success">Approved</span>';
        if($_SESSION['_user_branch'] == 1){
            $supp_link = '<a href="supp_items.php?supp_id='.$row->supp_id.'"><i class="icon-link  bg-edit mr-2" title="Item Mapping"></i></a>';  
        }else{
        $supp_link = '<a href="javascript:;"><i class="icon-link bg-edit-disabled mr-2" title="Item Mapping"></i></a>';  

        }
        $count = $dbconn->GetCount("tbl_supp_items","supp_id",$row->supp_id);

    }
    else{
        $supp_status = '<span class="badge bg-primary">Not Approved</span>';
        $count = '';

    }
	$converter = new Encryption;
	//$token = $converter->encode($row->grn_id.'~'.$_SESSION['_user_id']);	
	
    //$supp_name = '<a class="fancybox fancybox.ajax" href="inc/popup/fancybox_supplier.php?supp_id='.$row->supp_id.'">'.$row->supp_name.'</a>';
    $supp_name = '<a data-toggle="modal" data-target="#modalSuppDets" href="" data-id="'.$row->supp_id.'" data-popup="tooltip" title="Supplier Details">'.$row->supp_name.'</a>';
   // $del_link = '<a href="" rel="'.$row->supp_id.'" data-popup="tooltip" title="Remove" data-original-title="Remove" ><i class="icon-bin bg-delete mr-2"></i></a>';
    $edit_link = '<a href="mst_supplier_new.php?supp_id='.$row->supp_id.'" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';
	
    if($row->company_branch_id == 1){

    $branch_wise = 'Head Office';

    }elseif($row->company_branch_id == 2){

    $branch_wise = 'Kerala';

   }
    $approve_link = '';

   

    if($_SESSION['_user_id'] == 1){
        $approve_link = '<a href="mst_supplier_approve.php?supp_id='.$row->supp_id.'" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-eye bg-edit mr-2"></i></a>';
        
    }
	$data[] = array(
        "sno"=>$sno,
        "supp_code"=>$row->supp_code,
        "supp_name"=>$supp_name,
        "supp_person"=>$row->supp_contact_person1,
        "supp_mobile"=>$contact_no,
        "supp_gst"=>$row->supp_gst,
        "supp_approve_status"=>$supp_status,
		"branch_status"=>$branch_wise,
        "supp_id"=>$count,
        "action"=>$edit_link.$del_link.$approve_link.$supp_link
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

