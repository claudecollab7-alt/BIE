<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn= new dbhandler();


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

if($columnName == ''){
	$columnName = " grn_date ";
}
## Custom Field value
$searchByCode = isset($_POST['searchByCode'])? $_POST['searchByCode'] : '';

## Search 
$searchQuery = " ";
$app_qry = " ";
if($searchByCode != ''){
    $searchQuery .= " and (a.supp_name like '%".$searchByCode."%' OR a.supp_code like '%".$searchByCode."%'  OR a.supp_contact_person1 like '%".$searchByCode."%'
    OR a.supp_mobile1 like '%".$searchByCode."%' OR a.supp_gst like '%".$searchByCode."%' ) ";
}

/*
$app_user = $dbconn->GetSingleReconrd("mst_task_setting","app_usr_id"," task_id", 1 );
if($app_user == $_SESSION['_user_id']){
	$searchQuery .= " AND a.grn_status > 1 ";
}
*/

## Total number of records without filtering
$sel = $conn->query("SELECT count(*) as allcount FROM mst_supplier_new a WHERE a.supp_status = 1 AND a.supp_type = 'C'".$searchQuery);
$records =$sel->fetch();
$totalRecords = $records->allcount;

## Total number of records with filtering
$sel = $conn->query("SELECT count(*) as allcount FROM mst_supplier_new a WHERE a.supp_status = 1 AND a.supp_type = 'C' ".$searchQuery);
$records = $sel->fetch();
$totalRecordwithFilter = $records->allcount;

## Fetch records
$cusQuery = "SELECT * FROM mst_supplier_new a WHERE a.supp_status = 1 AND a.supp_type = 'C'".$searchQuery.
		" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;

//$cusQuery = "SELECT po_id,po_code, po_date, supp_id, po_value, po_status FROM tbl_grn WHERE grn_del_status = 0 ";

$cusRecords = $conn->query($cusQuery);
$data = array();

$sno = 1;

while ($row = $cusRecords->fetch()) 
{
    $branch_dets = '';

    $branch_qry = "SELECT * FROM mst_customer_branch WHERE supp_id = ".$row->supp_id; //GROUP BY branch_name
												
    $result1 = $conn->query($branch_qry);	
    if ($result1->rowCount()>0)
    {
        $brch_name=''; 
        while ($row1 = $result1->fetch())
        {
            $brch_name .= $row1->branch_name.', ';
            
        }
        $branch_dets .= $brch_name;
    }
    
    $branch_count = $dbconn->GetSingleReconrd("mst_customer_branch","COUNT(supp_id)","supp_id",$row->supp_id);
    if($branch_count > 0 )
    {
        
        $branch =  $brch_name;
    }
    else
    {
        $branch =  ' - ';
        $branch_dets = '';
        $brch_name = '';
    }
	$converter = new Encryption;
	//$token = $converter->encode($row->grn_id.'~'.$_SESSION['_user_id']);	
	
    $del_link = '<a href="" class="delete" rel="'.$row->supp_id.'" data-popup="tooltip" title="Remove" data-original-title="Remove" ><i class="icon-bin bg-delete mr-2"></i></a>';
    $branch_link = '<a href="mst_customer_branch.php?supp_id='.$row->supp_id.'" data-popup="tooltip" title="Branch" data-original-title="Branch" ><i class="icon-link bg-edit mr-2"></i></a>';
    $edit_link = '<a href="mst_customer_new.php?supp_id='.$row->supp_id.'" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';
	$supp_name = '<a data-toggle="modal" data-target="#modalSuppDets" href="" data-id="'.$row->supp_id.'" data-popup="tooltip" title="Supplier Details">'.$row->supp_name.'</a>';
	    $data[] = array(
    		"sno"=>$sno,
    		"supp_id"=>$row->supp_code,
    		"cus_person"=>$supp_name,
    		"cus_mobile"=>'<b>'.$row->supp_contact_person1.'</b><br><small>'.$row->supp_mobile1.'</small>',
    		"cus_gst"=>$row->supp_gst,
    		"cus_branch"=>ucwords(strtolower($branch)),
    		//"action"=>$edit_link.$del_link.$branch_link
    		"action"=>$edit_link.$del_link
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

