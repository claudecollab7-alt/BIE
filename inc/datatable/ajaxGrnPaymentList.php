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

$searchByCode = isset($_POST['searchByCode']) ? $_POST['searchByCode'] : '';
$searchByBranch = isset($_POST['searchByBranch']) ? $_POST['searchByBranch'] : '';
$searchByYear = isset($_POST['searchByYear']) ? $_POST['searchByYear'] : '';
$searchByStatus = isset($_POST['searchByStatus']) ? $_POST['searchByStatus'] : '';

$searchQuery = "";


if ($searchByCode != '') {
    $searchQuery .= " and (a.grn_slno like '%" . $searchByCode . "%' OR b.supp_name like '%" . $searchByCode . "%' OR a.grn_ref_code like '%" . $searchByCode . "%') ";
}

if ($searchByBranch != '') {
    
    $searchQuery .= " and (a.branch_id =".$searchByBranch.") ";
}
if ($searchByYear != '') {
    
    $searchQuery .= " and (a.grn_finyr =".$searchByYear.") ";
}

if ($searchByStatus != '') {
    
    $searchQuery .= " and (a.grn_pay_finished_status =".$searchByStatus.") ";
}



if($_SESSION['_user_id']==1 || $_SESSION['_user_branch']==1){
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_grn as a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id  WHERE a.grn_status = 2 " . $searchQuery);
    $records = $sel->fetch();
    $totalRecords = $records->allcount;

    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_grn as a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id  WHERE a.grn_status = 2 " . $searchQuery);
    $records = $sel->fetch();
    $totalRecordwithFilter = $records->allcount;

    $cusQuery = "SELECT *  from tbl_grn as a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id  where a.grn_status = 2 " . $searchQuery . " GROUP BY a.grn_id order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;
}else{
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_grn as a  LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id  WHERE a.grn_status = 2 AND a.branch_id=".$_SESSION['_user_branch']);
    $records = $sel->fetch();
    $totalRecords = $records->allcount;

    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_grn as a  LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id  WHERE a.grn_status = 2 AND a.branch_id=".$_SESSION['_user_branch'] . $searchQuery);
    $records = $sel->fetch();
    $totalRecordwithFilter = $records->allcount;

    $cusQuery = "SELECT * from tbl_grn as a LEFT JOIN mst_supplier_new b ON a.supp_id=b.supp_id   where a.grn_status = 2 AND a.branch_id=".$_SESSION['_user_branch']." " . $searchQuery . " GROUP BY a.grn_id order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;   
}


 
$cusRecords = $conn->query($cusQuery);
$data = array();

$iSno = 1;
while ($obj = $cusRecords->fetch()) {
    // echo "<pre>";
    // print_r($row);
    // echo "<pre>";
    // exit;
    // $supp_name = $dbconn->GetSingleReconrd("mst_supplier_new","supp_name","supp_id",$obj->supp_id);
    // $supp_credit_days = $dbconn->GetSingleReconrd("mst_supplier_new","supp_credit_days","supp_id",$obj->supp_id);
    // $obj->party_bill_date = $dbconn->GetSingleReconrd("tbl_purchase_inward","obj->party_bill_date","pi_id",$obj->pi_id);


    $grn_due_date =  date("Y-m-d", strtotime($obj->party_bill_date. '+'.$obj->supp_credit_days.' day'));

    if($obj->party_bill_date == "0000-00-00"){
        $next_dt = "";
    }
    else{
        
        if(($grn_due_date >= date("Y-m-d")))
        {
            $next_dt = "<span class='badge bg-warning'>". date("d-m-Y", strtotime($grn_due_date)) ."</span>";
        }
        else if(($grn_due_date >= date("Y-m-d") && date("Y-m-d",strtotime('-6 days'))))
        {
            $next_dt = "<span class='badge bg-success'>". date("d-m-Y", strtotime($grn_due_date)) ."</span>";
        }
        elseif(($grn_due_date < date("Y-m-d")))
        {
            $next_dt = "<span class='badge bg-danger'>". date("d-m-Y", strtotime($grn_due_date)) ."</span>";
        }
       
    }   


    $sub_grn_nos="";
    if($obj->pay_type =="M")
    {
        $grn_dets = explode(",",$obj->grn_ids);
        foreach ($grn_dets as $value) {
            $sub_grn_nos .=$dbconn->GetSingleReconrd("tbl_grn","grn_slno","grn_id",$value).",";
        }
    }

    $pay_amount = $dbconn->GetSingleReconrd("tbl_grn_pay_receipt","sum(pay_amount)","grn_id",$obj->grn_id);

    if(($obj->grn_pay_amount == $pay_amount) && $obj->grn_pay_finished_status == 3){

        $next_dt = "<span class='badge bg-success'>". date("d-m-Y", strtotime($grn_due_date)) ."</span>";
        $pay_status = "<span class='badge bg-success'>Completed</span><br/>".$sub_grn_nos;
        if($obj->grn_bill_copy !=''){
            $bill_amt = '<a  target="_blank" onClick="window.open(\'project_img/grn_payment/'.$obj->grn_bill_copy.'\',\''.$obj->grn_bill_copy.'\',\'width=700,height=505,menubar=no,status=no,scrollbars=no\')" class=\'sub\' title=\''.$obj->grn_bill_copy.'\' >'.$obj->grn_pay_amount.'</a>';
        }else{
            $bill_amt = $obj->grn_pay_amount;

        }
        // $grn_pay_link = '<li><a href="" class="tip disable" title="GRN Payment"><i class="fa fa-rupee"></i></a></li>';
        $paid_amt = $pay_amount;


    }else if(($obj->grn_bill_status == 1 && $obj->grn_pay_amount > 0)  && $obj->grn_pay_finished_status == 1){
        $pay_status = "<span class='badge bg-primary'>Pending</span><br/>".$sub_grn_nos;

        if($obj->grn_bill_copy !=''){
            $bill_amt = '<a onClick="window.open(\'project_img/grn_payment/'.$obj->grn_bill_copy.'\',\''.$obj->grn_bill_copy.'\',\'width=700,height=505,menubar=no,status=no,scrollbars=no\')"  class= \'sub\' title=\''.$obj->grn_bill_copy.'\' target="_blank" >'.$obj->grn_pay_amount.'</a>';
        }else{
            $bill_amt = $obj->grn_pay_amount;

        }
		 $paid_amt = $pay_amount;

    }else if(($obj->grn_pay_amount != $pay_amount) && $obj->grn_pay_finished_status == 2){

        $pay_status = "<span class='badge bg-warning'>Partial Payment</span><br/>".$sub_grn_nos;

        if($obj->grn_bill_copy !=''){
            $bill_amt = '<a onClick="window.open(\'project_img/grn_payment/'.$obj->grn_bill_copy.'\',\''.$obj->grn_bill_copy.'\',\'width=700,height=505,menubar=no,status=no,scrollbars=no\')"  class= \'sub\' title=\''.$obj->grn_bill_copy.'\' target="_blank" >'.$obj->grn_pay_amount.'</a>';
        }else{
            $bill_amt = $obj->grn_pay_amount;

        }

        $paid_amt = $pay_amount;


    }else{

        $pay_status = "<span class='badge bg-info'>Not Yet</span>";
        $bill_amt = '';
        $paid_amt = '';
    }

    if($obj->pay_type=='N'){

        $main_no=$dbconn->GetSingleReconrd("tbl_grn","grn_slno","grn_id",$obj->grn_ids);
        $grn_pay_link = '';
        $pay_status = "<span class='badge bg-info'>Multiple</span><br/>".$main_no;

    }else{
        $grn_pay_link = '<a href="grn_pay_receipt.php?grn_id='.$obj->grn_id.'" data-popup="tooltip" title="GRN Payment"><i class="icon-pencil5 bg-edit mr-2" style="font-size:16px"></i></a>';
    }
  


    // $no_item =  $no_item;
    $data[] = array(
        "sno" => $iSno,
        "grn_slno" => $obj->grn_ref_code,
        "grn_date" =>  date("d-m-Y", strtotime($obj->grn_date)),
        "supp_id" => $obj->supp_name,
        "party_bill_no" => $obj->grn_refno,
        "party_bill_date" => date("d-m-Y", strtotime($obj->party_bill_date)),
        "no_item" =>  ($next_dt==""? "": $next_dt),
        "bill_amt" =>  $bill_amt,
        "paid_amt" =>  $paid_amt,
        "pay_status" =>  $pay_status,
        "action" =>   $grn_pay_link

    );
    $iSno++;
}

##Response
$response = array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $data
);
echo json_encode($response);
