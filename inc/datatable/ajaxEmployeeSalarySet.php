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

// echo '***'.$searchValue;

$search_salary = $_POST['search_salary'] ? $_POST['search_salary'] : '';
$searchByBranch = isset($_POST['searchByBranch']) ? $_POST['searchByBranch'] : '';
$searchByEmployeeType = $_POST['searchByEmployeeType'] ? $_POST['searchByEmployeeType'] : '';

$search ="";

if($search_salary!=''){
    $search .=" AND (emp_name like '%" . $search_salary . "%' OR a.emp_code like '%" . $search_salary . "%' OR a.emp_mobile like '%" . $search_salary . "%' OR b.department_name like '%" . $search_salary . "%' OR c.designation_name like '%" . $search_salary . "%' )";
}
if ($searchByBranch != '') {
    
    $search .= " and (a.branch_id =".$searchByBranch.") ";
}

if ($searchByEmployeeType != '') {
    
    $search .= " and (a.emp_type =".$searchByEmployeeType.") ";
}

if($_SESSION['_user_id']==1 || $_SESSION['_user_branch']==1)
{
$sel = $conn->query("SELECT count('a.emp_id,a.em_id,a.emp_type,a.emp_code,a.emp_name,a.emp_ctc,b.department_name,c.designation_name') as allcount FROM mst_employee as a 
    LEFT JOIN  mst_department as b ON a.department_id = b.department_id 
    LEFT JOIN mst_designation as c ON c.designation_id = a.designation_id WHERE  a.rec_del_status= 1 " . $search);
$records = $sel->fetch();
$totalRecords = $records->allcount;

$sel = $conn->query("SELECT count('a.emp_id,a.em_id,a.emp_type,a.emp_code,a.emp_name,a.emp_ctc,b.department_name,c.designation_name') as allcount FROM mst_employee as a 
 LEFT JOIN  mst_department as b ON a.department_id = b.department_id 
LEFT JOIN mst_designation as c ON c.designation_id = a.designation_id WHERE  a.rec_del_status= 1 " . $search);
$records = $sel->fetch();
$totalRecordwithFilter = $records->allcount;

$cusQuery = "SELECT a.emp_id,a.em_id,a.emp_type,a.emp_code,a.emp_name,a.emp_ctc,b.department_name,c.designation_name FROM mst_employee as a LEFT JOIN mst_department as b ON a.department_id = b.department_id LEFT JOIN mst_designation as c ON c.designation_id = a.designation_id WHERE  a.rec_del_status= 1  " . $search . " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage; 
// print_r($cusQuery);
}else{
    $sel = $conn->query("SELECT count('a.emp_id,a.em_id,a.emp_type,a.emp_code,a.emp_name,a.emp_ctc,b.department_name,c.designation_name') as allcount FROM mst_employee as a 
    LEFT JOIN  mst_department as b ON a.department_id = b.department_id 
    LEFT JOIN mst_designation as c ON c.designation_id = a.designation_id WHERE  a.rec_del_status= 1 and  a.branch_id = ".$_SESSION['_user_branch']. " " . $search);
$records = $sel->fetch();
$totalRecords = $records->allcount;

$sel = $conn->query("SELECT count('a.emp_id,a.em_id,a.emp_type,a.emp_code,a.emp_name,a.emp_ctc,b.department_name,c.designation_name') as allcount FROM mst_employee as a 
 LEFT JOIN  mst_department as b ON a.department_id = b.department_id 
LEFT JOIN mst_designation as c ON c.designation_id = a.designation_id WHERE  a.rec_del_status= 1 and a.branch_id = ".$_SESSION['_user_branch']. " " . $search);
$records = $sel->fetch();
$totalRecordwithFilter = $records->allcount;

$cusQuery = "SELECT a.emp_id,a.em_id,a.emp_type,a.emp_code,a.emp_name,a.emp_ctc,b.department_name,c.designation_name FROM mst_employee as a LEFT JOIN mst_department as b ON a.department_id = b.department_id LEFT JOIN mst_designation as c ON c.designation_id = a.designation_id WHERE  a.rec_del_status= 1 and a.branch_id = ".$_SESSION['_user_branch']. "  " . $search . " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;
}
// print_r($cusQuery);

$data = array();
$result = $conn->query($cusQuery);

    $sno=1;
while($row = $result->fetch()){

if($row->em_id>0){
    $edit_link = '<a href="emp_setsalary.php?emp_id='.$row->emp_id. '" class="badge bg-info" title="" data-original-title="Modify Salary">Modify Salary</a>';
}else{
    $edit_link = '<a href="emp_setsalary.php?emp_id='.$row->emp_id.'" class="badge bg-success" title="" data-original-title="Modify Salary"> Set Salary</a>';
}

    if($row->emp_type==1){

        $emp_type = "Staff";

    }elseif($row->emp_type==2){

        $emp_type = "Labour";

    }else{

        $emp_type = "Others";
    }

    $data[] = array(
        "sno" =>$sno,
        "emp_code" => $row->emp_code,
        "emp_name" => $row->emp_name,
        "emp_type" => $emp_type,
        "department_name" => $row->department_name,
        "designation_name" => $row->designation_name,
        "emp_ctc" => $row->emp_ctc,
        "action" =>  $edit_link
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
