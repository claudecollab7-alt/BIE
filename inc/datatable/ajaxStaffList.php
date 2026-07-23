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

$search_staff_dets = $_POST['search_staff_dets'] ? $_POST['search_staff_dets'] : '';
$searchByBranch = isset($_POST['searchByBranch']) ? $_POST['searchByBranch'] : '';
$searchByDepartment = isset($_POST['searchByDepartment']) ? $_POST['searchByDepartment'] : '';
$searchByDesignation = isset($_POST['searchByDesignation']) ? $_POST['searchByDesignation'] : '';

$search ="";

if($search_staff_dets!=''){
    $search .=" AND (a.emp_name like '%" . $search_staff_dets . "%' OR a.emp_code like '%" . $search_staff_dets . "%' OR a.emp_mobile like '%" . $search_staff_dets . "%' OR b.department_name like '%" . $search_staff_dets . "%' OR c.designation_name like '%" . $search_staff_dets . "%' )";
}

if ($searchByBranch != '') {
    
    $search .= " and (a.branch_id =".$searchByBranch.") ";
}
if ($searchByDepartment != '') {
    
    $search .= " and (a.department_id =".$searchByDepartment.") ";
}
if ($searchByDesignation != '') {
    
    $search .= " and (a.designation_id =".$searchByDesignation.") ";
}

if($_SESSION['_user_id']==1 || $_SESSION['_user_branch']==1)
{
$sel = $conn->query("SELECT count('a.emp_id,a.emp_code,a.emp_name,a.emp_mobile,b.department_name,c.designation_name') as allcount FROM mst_employee as a 
LEFT JOIN  mst_department as b ON a.department_id = b.department_id 
LEFT JOIN mst_designation as c ON c.designation_id = a.designation_id WHERE  a.rec_del_status= 1 AND a.emp_type  = 1 " .$search);
$records = $sel->fetch();
$totalRecords = $records->allcount;

$sel = $conn->query("SELECT count('a.emp_id,a.emp_code,a.emp_name,a.emp_mobile,b.department_name,c.designation_name') as allcount FROM mst_employee as a 
LEFT JOIN  mst_department as b ON a.department_id = b.department_id 
LEFT JOIN mst_designation as c ON c.designation_id = a.designation_id WHERE  a.rec_del_status= 1  AND a.emp_type = 1 " .$search);
$records = $sel->fetch();
$totalRecordwithFilter = $records->allcount;


$cusQuery ="SELECT a.emp_id,a.emp_code,a.emp_name,a.emp_mobile,b.department_name,c.designation_name FROM mst_employee as a LEFT JOIN mst_department as b ON a.department_id = b.department_id LEFT JOIN mst_designation as c ON c.designation_id = a.designation_id WHERE  a.rec_del_status= 1  AND a.emp_type = 1 " .$search." order by ". $columnName. " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;
// print_r($cusQuery);
}else{
    $sel = $conn->query("SELECT count('a.emp_id,a.emp_code,a.emp_name,a.emp_mobile,b.department_name,c.designation_name') as allcount FROM mst_employee as a 
LEFT JOIN  mst_department as b ON a.department_id = b.department_id 
LEFT JOIN mst_designation as c ON c.designation_id = a.designation_id WHERE  a.rec_del_status= 1 AND a.emp_type  = 1 AND a.branch_id = ".$_SESSION['_user_branch']. "" .$search);
$records = $sel->fetch();
$totalRecords = $records->allcount;

$sel = $conn->query("SELECT count('a.emp_id,a.emp_code,a.emp_name,a.emp_mobile,b.department_name,c.designation_name') as allcount FROM mst_employee as a 
LEFT JOIN  mst_department as b ON a.department_id = b.department_id 
LEFT JOIN mst_designation as c ON c.designation_id = a.designation_id WHERE  a.rec_del_status= 1  AND a.emp_type = 1 AND a.branch_id = ".$_SESSION['_user_branch']. " " .$search);
$records = $sel->fetch();
$totalRecordwithFilter = $records->allcount;


$cusQuery ="SELECT a.emp_id,a.emp_code,a.emp_name,a.emp_mobile,b.department_name,c.designation_name FROM mst_employee as a LEFT JOIN mst_department as b ON a.department_id = b.department_id LEFT JOIN mst_designation as c ON c.designation_id = a.designation_id WHERE  a.rec_del_status= 1  AND a.emp_type = 1 AND a.branch_id = ".$_SESSION['_user_branch']. " " .$search." order by ". $columnName. " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;
}



$data = array();
$result = $conn->query($cusQuery);

// $totalRecords =$result->rowCount();
// $totalRecordwithFilter = $result->rowCount();

    $sno=1;
while($row = $result->fetch()){

    $edit_link = '<a href="mst_employee_add.php?emp_id=' . $row->emp_id . '" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';

    $del_link='<a href="javascript:;" rel="'.$row->emp_id.'" data-popup="tooltip" title="Remove" class="delete" data-original-title="Remove"><i class="icon-bin bg-delete mr-2"></i></a>';

    $emp_code = '<a data-toggle="modal" data-target="#modalEmplyeeDets" href="" data-id="'.$row->emp_id.'" data-popup="tooltip" title="Employee Details">'.$row->emp_code.'</a>';

    $data[] = array(
        "sno" =>$sno,
        "emp_code" => $emp_code,
        "emp_name" => $row->emp_name,
        "department_name" => $row->department_name,
        "designation_name" => $row->designation_name,
        "emp_mobile" => $row->emp_mobile,
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