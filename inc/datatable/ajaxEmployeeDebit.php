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

$search_emp_debit = $_POST['search_emp_debit'] ? $_POST['search_emp_debit'] : '';
$searchByBranch = isset($_POST['searchByBranch']) ? $_POST['searchByBranch'] : '';
$searchByEmployeeType = $_POST['searchByEmployeeType'] ? $_POST['searchByEmployeeType'] : '';

$search ="";

if($search_emp_debit!=''){
    $search .=" AND (emp_name like '%" . $search_emp_debit . "%' OR emp_code like '%" . $search_emp_debit . "%')";
}
if ($searchByBranch != '') {
    
    $search .= " and (branch_id =".$searchByBranch.") ";
}

if ($searchByEmployeeType != '') {
    
    $search .= " and (emp_type =".$searchByEmployeeType.") ";
}

if($_SESSION['_user_id']==1 || $_SESSION['_user_branch']==1)
{
$sel = $conn->query("SELECT count('emp_code,emp_id,emp_name') as allcount FROM mst_employee 
     WHERE  rec_del_status= 1 " . $search);
$records = $sel->fetch();
$totalRecords = $records->allcount;

$sel = $conn->query("SELECT count('emp_code,emp_id,emp_name') as allcount FROM mst_employee 
 WHERE  rec_del_status= 1 " . $search);
$records = $sel->fetch();
$totalRecordwithFilter = $records->allcount;

$cusQuery = "SELECT emp_code,emp_id,emp_name FROM mst_employee  WHERE rec_del_status= 1 " . $search . " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;
}else{
    $sel = $conn->query("SELECT count('emp_code,emp_id,emp_name') as allcount FROM mst_employee 
     WHERE  rec_del_status= 1 and branch_id = ".$_SESSION['_user_branch']. " " . $search);
$records = $sel->fetch();
$totalRecords = $records->allcount;

$sel = $conn->query("SELECT count('emp_code,emp_id,emp_name') as allcount FROM mst_employee 
 WHERE  rec_del_status= 1 and branch_id = ".$_SESSION['_user_branch']. " " . $search);
$records = $sel->fetch();
$totalRecordwithFilter = $records->allcount;

$cusQuery = "SELECT emp_code,emp_id,emp_name FROM mst_employee  WHERE rec_del_status= 1 and branch_id = ".$_SESSION['_user_branch']. " " . $search . " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;
}

$data = array();
$result = $conn->query($cusQuery);

    $sno=1;
while($row = $result->fetch()){

    $edit_link = '<a href="emp_debit_note.php?emp_id='.$row->emp_id. '" class="badge bg-info" title="" data-original-title="Modify Debit">Modify Debit</a>';

    $debit_amount = (float)$dbconn->GetSingleReconrd("tbl_emp_debit_note","SUM(debit_amount)","is_current=1 AND emp_id",$row->emp_id);
	$return_amount = (float)$dbconn->GetSingleReconrd("tbl_emp_debit_note","SUM(return_amount)","is_current=1 AND emp_id",$row->emp_id);
    $balance_amount = (float)$dbconn->GetSingleReconrd("tbl_emp_debit_note","SUM(balance_amount)","is_current=1 AND emp_id",$row->emp_id);

    $debit_amount_fix = number_format(($debit_amount),2);
    $return_amount_fix = number_format(($return_amount),2);
    $balance_amount_fix = number_format(($balance_amount),2);

    
    
    $data[] = array(
        "sno" =>$sno,
        "emp_code" => $row->emp_code,
        "emp_name" => $row->emp_name,
        "debit_amount" => $debit_amount_fix,
        "return_amount" =>$return_amount_fix,
        "balance_amount" =>$balance_amount_fix,
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
