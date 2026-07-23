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

//  echo '***'.$searchValue;

$atten_year = $_POST['atten_year'] ? $_POST['atten_year'] : '';

// $search_emp_att_dets = isset($_POST['search_emp_att_dets']) ? $_POST['search_emp_att_dets'] : '';
$month_id = isset($_POST['month_id']) ? $_POST['month_id'] : '';

$search ="";

$search .= " and (YEAR(a.work_date) =".date('Y').")  AND  (MONTH(a.work_date) =".date('m').") ";
 
if($searchValue!=''){
     $search .=" AND (b.emp_name like '%" . $searchValue . "%' OR b.emp_code like '%" . $searchValue . "%' OR a.work_date  like '%" . date('Y-m-d', strtotime($searchValue)) . "%' OR a.check_in like '%" . $searchValue . "%' OR c.shift_name like '%" . $searchValue . "%' )";
}

// if($searchValue!=''){
//     $search =" AND (b.emp_name like '%" . $searchValue . "%')";
// }date('Y-m-d', strtotime($atta_date))

if ($atten_year != '') {
    $search .= " and (YEAR(a.work_date) =".$atten_year.") ";
}
if ($month_id != '') {
    $search .= " and (MONTH(a.work_date) =".$month_id.") ";
}

if($_SESSION['_user_id']==1 || $_SESSION['_user_branch']==1)
{

$sel = $conn->query("SELECT count('b.emp_code,b.emp_id,a.attn_id,b.emp_name,a.work_date,a.check_in,a.check_in_dtm,a.check_out,a.work_time,a.late_in,a.break_time,a.early_out,a.check_out_dtm,c.shift_name') as allcount FROM tbl_attendance as a 
LEFT JOIN  mst_employee as b ON a.emp_id = b.emp_id 
LEFT JOIN mst_shifts as c ON c.shift_id = a.shift_id WHERE  a.status= 1  " .$search);
$records = $sel->fetch();
$totalRecords = $records->allcount;

$sel = $conn->query("SELECT count('b.emp_code,b.emp_id,a.attn_id,b.emp_name,a.work_date,a.check_in,a.check_in_dtm,a.check_out,a.work_time,a.late_in,a.break_time,a.early_out,a.check_out_dtm,c.shift_name') as allcount FROM tbl_attendance as a 
LEFT JOIN  mst_employee as b ON a.emp_id = b.emp_id 
LEFT JOIN mst_shifts as c ON c.shift_id = a.shift_id WHERE  a.status= 1  " .$search);
$records = $sel->fetch();
$totalRecordwithFilter = $records->allcount;


$cusQuery ="SELECT b.emp_code,b.emp_id,a.attn_id,b.emp_name,a.work_date,a.check_in,a.check_in_dtm,a.check_out,a.work_time,a.late_in,a.break_time,a.early_out,a.check_out_dtm,c.shift_name FROM tbl_attendance as a LEFT JOIN mst_employee as b ON a.emp_id = b.emp_id LEFT JOIN mst_shifts as c ON c.shift_id = a.shift_id WHERE  a.status= 1  " .$search." order by ". $columnName. " " . $columnSortOrder . " limit " . $row . "," . $rowperpage ;
// print_r($cusQuery);
}else{

    $sel = $conn->query("SELECT count('b.emp_code,b.emp_id,a.attn_id,b.emp_name,a.work_date,a.check_in,a.check_in_dtm,a.check_out,a.work_time,a.late_in,a.break_time,a.early_out,a.check_out_dtm,c.shift_name') as allcount FROM tbl_attendance as a 
LEFT JOIN  mst_employee as b ON a.emp_id = b.emp_id 
LEFT JOIN mst_shifts as c ON c.shift_id = a.shift_id WHERE  a.status= 1" .$search);
$records = $sel->fetch();
$totalRecords = $records->allcount;

$sel = $conn->query("SELECT count('b.emp_code,b.emp_id,a.attn_id,b.emp_name,a.work_date,a.check_in,a.check_in_dtm,a.check_out,a.work_time,a.late_in,a.break_time,a.early_out,a.check_out_dtm,c.shift_name') as allcount FROM tbl_attendance as a 
LEFT JOIN  mst_employee as b ON a.emp_id = b.emp_id 
LEFT JOIN mst_shifts as c ON c.shift_id = a.shift_id WHERE  a.status= 1 " .$search);
$records = $sel->fetch();
$totalRecordwithFilter = $records->allcount;


$cusQuery ="SELECT b.emp_code,b.emp_id,a.attn_id,b.emp_name,a.work_date,a.check_in,a.check_out,a.check_in_dtm,a.work_time,a.late_in,a.break_time,a.early_out,a.check_out_dtm,c.shift_name FROM tbl_attendance as a LEFT JOIN mst_employee as b ON a.emp_id = b.emp_id LEFT JOIN mst_shifts as c ON c.shift_id = a.shift_id WHERE  a.status= 1 " .$search." order by ". $columnName. " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;

}

$data = array();
$result = $conn->query($cusQuery);

    $sno=1;
while($row = $result->fetch()){

    $w_minutes = $row->work_time;
    $default_wrk_hours = "480"; // 498 in minutes
    $w_hours = floor($w_minutes / 60);
    $w_min = $w_minutes - ($w_hours * 60);
    $work_time = $w_hours.":".leadingZeros($w_min,2);

    $ot_minutes = $w_minutes - $default_wrk_hours;
    $ot_hours = floor($ot_minutes / 60);
    $ot_min = $ot_minutes - ($ot_hours * 60);
    $ot_hrs = "0:00";
    if($w_minutes > $default_wrk_hours && $ot_minutes > 0){
        $ot_hrs = $ot_hours.":".leadingZeros($ot_min,2);
    }

    $b_minutes = $row->break_time;
    $b_hours = floor($b_minutes / 60);
    $b_min = $b_minutes - ($b_hours * 60);
    $break_time = $b_hours.":".leadingZeros($b_min,2);
    
    $late_in_min = $row->late_in;
    $late_in_hours = floor($late_in_min / 60);
    $late_in_min = $late_in_min - ($late_in_hours * 60);
    $late_in = $late_in_hours.":".leadingZeros($late_in_min,2);
    
    
    $e_out_min = $row->early_out;
    $e_out_hours = floor($e_out_min / 60);
    $e_out_min = $e_out_min - ($e_out_hours * 60);
    $early_out = $e_out_hours.":".leadingZeros($e_out_min,2);
    if($row->check_out_dtm !='0000-00-00 00:00:00'){

     $check_out_dtm = date('H:i:s',strtotime($row->check_out_dtm));

    }else{
        $check_out_dtm = '00:00:00';
    }
    $emp_att_det='<a data-toggle="modal" data-target="#modalEmplyeeAttaDets" href="" data-id="'.$row->emp_id.'" data-atta_date="'.$row->work_date.'" data-popup="tooltip" title="Employee Attendance Details">View Details</a>';


    $data[] = array(
        "sno" =>$sno,
        "emp_code" => $row->emp_code,
        "emp_name" => $row->emp_name,
        "work_date" => date('d-m-Y',strtotime($row->work_date)),
        "check_in" => date('H:i:s',strtotime($row->check_in_dtm)),
        "check_out" => $check_out_dtm,
        "work_time" => $work_time,
        "break_time" => $break_time,
        "late_in" => $late_in,
        "early_out" => $early_out,
        "ot_hrs" => $ot_hrs,
        "shift_name" => $row->shift_name,
        "action" =>$emp_att_det 
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