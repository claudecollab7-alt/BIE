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


$searchByCode = isset($_POST['searchByCode']) ? $_POST['searchByCode'] : '';
$searchByBranch = isset($_POST['searchByBranch']) ? $_POST['searchByBranch'] : '';


$searchQuery = " ";

if ($searchByCode != '') {
    $searchQuery .= " and (d.supp_name like '%" . $searchByCode . "%' OR a.so_refno like '%" . $searchByCode . "%'  OR a.so_date like '%" . $searchByCode . "%' ) ";
}
if ($searchByBranch != '') {
    
    $searchQuery .= " and (a.bie_branch_id =".$searchByBranch.") ";
}

if($_SESSION['_user_id']==1 || $_SESSION['_user_branch'] == 1)
{

    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_sales_order as a
    LEFT JOIN  mst_supplier_new as d ON a.supp_id = d.supp_id 
    WHERE a.accounts_status = 1 " . $searchQuery);
    $records = $sel->fetch();
    $totalRecords = $records->allcount;

    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_sales_order as a 
    LEFT JOIN  mst_supplier_new as d ON a.supp_id = d.supp_id 
    WHERE a.accounts_status = 1 " . $searchQuery);
    $records = $sel->fetch();
    $totalRecordwithFilter = $records->allcount;
    $cusQuery = "SELECT * from tbl_sales_order as a  LEFT JOIN tbl_quotation as b ON  b.quo_id = a.quo_id  LEFT JOIN mst_customer_branch as c  ON b.branch_id = c.branch_id LEFT JOIN mst_supplier_new as d  ON a.supp_id = d.supp_id where a.accounts_status = 1" . $searchQuery . " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;

}
else{

    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_sales_order as a
    LEFT JOIN  mst_supplier_new as d ON a.supp_id = d.supp_id 
    WHERE a.accounts_status = 1 AND a.bie_branch_id='".$_SESSION['_user_branch']."' " . $searchQuery);
    $records = $sel->fetch();
    $totalRecords = $records->allcount;

    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_sales_order as a 
    LEFT JOIN  mst_supplier_new as d ON a.supp_id = d.supp_id 
    WHERE a.accounts_status = 1 AND a.bie_branch_id='".$_SESSION['_user_branch']."' " . $searchQuery);
    $records = $sel->fetch();
    $totalRecordwithFilter = $records->allcount;
    $cusQuery = "SELECT * from tbl_sales_order as a  LEFT JOIN tbl_quotation as b ON  b.quo_id = a.quo_id  LEFT JOIN mst_customer_branch as c  ON b.branch_id = c.branch_id LEFT JOIN mst_supplier_new as d  ON a.supp_id = d.supp_id where a.accounts_status = 1 AND a.bie_branch_id='".$_SESSION['_user_branch']."' " . $searchQuery . " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;


}



$cusRecords = $conn->query($cusQuery);
$data = array();

$iSno = 1;
while ($row = $cusRecords->fetch()) {
    $credit ='';
    $pay_link = '';
    $so_link = '';
    $quotation_link = '<a href="print_quotation.php?quo_id=' . $row->quo_id . '" target="_blank">' .
        $dbconn->GetSingleReconrd("tbl_quotation", "quo_refno", "quo_id", $row->quo_id) . '</a>';

    if ($row->so_status == 1 && $row->accounts_status == 1) {
        $pay_link = '<a href="pay_receipt.php?so_id=' . $row->so_id . '" data-popup="tooltip" title="Pay Receipt"><i class="fa fa-rupee mr-2" style="font-size:16px"></i></a>';
    }

    if(round($row->bal_value) <= 0){
												
        $sales_status = '<span class="badge bg-success">Completed</span>';
        $so_link = '';
    }elseif($row->pay_status == 2 && $row->so_verify_status == 1){
        $so_link = '<a href="javascript:;" class="q" title="Confirm payment"><i class="fa fa-plus bg-edit-disabled "></i></a>';
        $pay_link = '<a href="href="javascript:;" data-popup="tooltip" title="Pay Receipt"><i class="fa fa-rupee mr-2" style="font-size:16px"></i></a>';
    }
    if (round($row->item_net_val) == round($row->bal_value)) {
        $sales_status = '';
        $so_link = '';
    } else if (round($row->bal_value) > 0) {
        $sales_status = '<span class="badge bg-grey">Pending</span>';

        $isexists = $dbconn->GetSingleReconrd("tbl_sales_order", "pay_status", "so_id", $row->so_id);
        if ($isexists == '0' || $isexists == '') {
             
            $so_link = '<a href="javascript:;" class="tip q" id="example" data-soid=' . $row->so_id . ' title="Confirm payment"><i class="fa fa-plus"></i></a>';
            
        } else {
            $so_link = '<a href="javascript:;" class="q" title="Confirm payment"><i class="fa fa-plus"></i></a>';
        }
    }
    if($row->so_verify_status == 1 && $row->pay_status == 2){
        $so_link = '<a href="javascript:;" class="" title="Confirm payment"><i class="fa fa-plus bg-edit-disabled "></i></a>';
        $pay_link = '<a href="pay_receipt.php?so_id=' . $row->so_id . '" data-popup="tooltip" title="Pay Receipt"><i class="fa fa-rupee mr-2" style="font-size:16px"></i></a>';
    }
    if ($row->so_status == 5  && $row->so_approve_status == 1) {
     
        $pay_link = '<a href="pay_receipt.php?so_id=' . $row->so_id . '" data-popup="tooltip" title="Pay Receipt"><i class="fa fa-rupee mr-2" style="font-size:16px"></i></a>';
    }
    elseif ($row->so_approve_status == 2 && $row->so_status == 4) {
     
        $pay_link = '<a href="pay_receipt.php?so_id=' . $row->so_id . '" data-popup="tooltip" title="Pay Receipt"><i class="fa fa-rupee mr-2" style="font-size:16px"></i></a>';
    }
    if($row->so_verify_status == 1 && $row->pay_status == 3 ){
       						
        $sales_status = '<span class="badge bg-warning">Credit Sales</span>';
    }if($row->so_approve_status == 3 && $row->pay_status == 4){
        $sales_status = '<span class="badge bg-warning">Credit Sales</span>';
        $so_link = '';	
    }
    if($row->pay_remarks == $row->pay_remarks && $row->pay_status == 3){
        $so_link ='';
    }
    if($row->accounts_status == 1 && $row->pay_status == 0 ){				
        $sales_status = '<span class="badge bg-blue">Processing</span>';
    }elseif($row->so_approve_status == 3 && $row->so_status == 1 ){				
        $credit= '<span style="color:red;">Credit Sales<span>';
    }if($row->so_approve_status == 3 && $row->pay_status == 4){				
        $credit= '';
    }
    $so_refno = '<a data-toggle="modal" data-target="#modalSalesDets" href="" data-id="'.$row->so_id.'" data-popup="tooltip" title="Sales Details">'.$row->so_refno.'</a>';
    $data[] = array(
        "sno" => $iSno,
        "so_refno" => $so_refno,
        "so_date" => date("d-m-Y", strtotime($row->so_date)),
        "supp_name" => '<b>' . $row->supp_name . '</b>',
        "quo_refno" =>  $quotation_link,
        "so_value" => $row->item_net_val,
        "bal_value" => $row->bal_value,
        "so_status" => $sales_status.'<br>'.'<b><small>'.$credit.'</b><small>',
        "action" =>  $pay_link . $so_link
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

