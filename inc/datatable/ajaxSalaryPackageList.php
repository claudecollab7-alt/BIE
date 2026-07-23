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

// echo '***'.$searchValue;

$search_salary_pakage_dets = $_POST['search_salary_pakage_dets'] ? $_POST['search_salary_pakage_dets'] : '';
// $searchByBranch = isset($_POST['searchByBranch']) ? $_POST['searchByBranch'] : '';

$search ="";

// if($search_salary_pakage_dets!=''){
//     $search .=" AND (emp_name like '%" . $search_salary_pakage_dets . "%' OR a.emp_code like '%" . $search_salary_pakage_dets . "%' OR a.emp_mobile like '%" . $search_salary_pakage_dets . "%' OR b.department_name like '%" . $search_salary_pakage_dets . "%' OR c.designation_name like '%" . $search_salary_pakage_dets . "%' )";
// }
if($search_salary_pakage_dets!=''){
    $search .=" AND (sal_package_name like '%" . $search_salary_pakage_dets . "%' OR sal_basic like '%" . $search_salary_pakage_dets . "%'  )";
}

// if ($searchByBranch != '') {
    
//     $search .= " and (a.branch_id =".$searchByBranch.") ";
// }

// if($_SESSION['_user_id']==1 || $_SESSION['_user_branch']==1)
// {

$sel = $conn->query("SELECT count('sal_id,sal_period,sal_package_name,sal_basic,sal_da,sal_hra,sal_convey,sal_pf') as allcount FROM mst_salary_setting  WHERE rec_del_status= 1 " .$search);
$records = $sel->fetch();
$totalRecords = $records->allcount;

$sel = $conn->query("SELECT count('sal_id,sal_period,sal_package_name,sal_basic,sal_da,sal_hra,sal_convey,sal_pf') as allcount FROM mst_salary_setting WHERE  rec_del_status= 1 " .$search);
$records = $sel->fetch();
$totalRecordwithFilter = $records->allcount;


$cusQuery ="SELECT sal_id,sal_period,sal_package_name,sal_basic,sal_da,sal_hra,sal_convey,sal_pf FROM mst_salary_setting  WHERE  rec_del_status = 1 " .$search." order by ". $columnName. " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;

// }
// else{

//     $sel = $conn->query("SELECT count('sal_id,sal_period,sal_package_name,sal_basic,sal_da,sal_hra,sal_convey,sal_pf') as allcount FROM mst_salary_setting  WHERE rec_del_status= 1 and branch_id = ".$_SESSION['_user_branch']. "" .$search);
// $records = $sel->fetch();
// $totalRecords = $records->allcount;

// $sel = $conn->query("SELECT count('sal_id,sal_period,sal_package_name,sal_basic,sal_da,sal_hra,sal_convey,sal_pf') as allcount FROM mst_salary_setting WHERE  rec_del_status= 1 and branch_id = ".$_SESSION['_user_branch']. " " .$search);
// $records = $sel->fetch();
// $totalRecordwithFilter = $records->allcount;


// $cusQuery ="SELECT sal_id,sal_period,sal_package_name,sal_basic,sal_da,sal_hra,sal_convey,sal_pf FROM mst_salary_setting  WHERE  rec_del_status = 1 and branch_id = ".$_SESSION['_user_branch']. " " .$search." order by ". $columnName. " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;

// }

$data = array();
$result = $conn->query($cusQuery);

// $totalRecords =$result->rowCount();
// $totalRecordwithFilter = $result->rowCount();

    $sno=1;
while($row = $result->fetch()){

    $edit_link = '<a href="mst_salary_package_add.php?sal_id=' . $row->sal_id . '" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';

    $del_link='<a href="javascript:;" rel="'.$row->sal_id.'" data-popup="tooltip" title="Remove" class="delete" data-original-title="Remove"><i class="icon-bin bg-delete mr-2"></i></a>';

    // $emp_code = '<a data-toggle="modal" data-target="#modalEmplyeeDets" href="" data-id="'.$row->sal_id.'" data-popup="tooltip" title="Employee Details">'.$row->emp_code.'</a>';

    if($row->sal_period==1){
        $sal_period = "Per Day";
    }elseif($row->sal_period==2){
        $sal_period = "Per Month";
    }

    $data[] = array(
        "sno" =>$sno,
        "sal_package_name" => $row->sal_package_name,
        "sal_period" => $sal_period,
        "sal_basic" => $row->sal_basic,
        "sal_da" => $row->sal_da,
        "sal_hra" => $row->sal_hra,
        "sal_convey" => $row->sal_convey,
        "sal_pf" => $row->sal_pf,
        "action" =>  $edit_link.$del_link
    );
        
    $sno++;
}
    // }    
## Response

$response = array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $data
);


echo json_encode($response);
?>