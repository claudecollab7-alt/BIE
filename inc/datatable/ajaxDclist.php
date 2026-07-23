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
$searchByYear = isset($_POST['searchByYear']) ? $_POST['searchByYear'] : '';

$searchQuery = " ";


if ($searchByCode != '') {
    $searchQuery .= " and (d.supp_name like '%" . $searchByCode . "%' OR a.dc_slno like '%" . $searchByCode . "%'
      OR a.dc_date like '%" . $searchByCode . "%') ";
}

if ($searchByBranch != '') {
    
    $searchQuery .= " and (a.branch_id =".$searchByBranch.") ";
}
if ($searchByYear != '') {
    
    $searchQuery .= " and (a.dc_finyr =".$searchByYear.") ";
}


if($_SESSION['_user_id']==1 || $_SESSION['_user_branch']==1)
{
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_dc as a
    LEFT JOIN  mst_supplier_new as d ON a.supp_id = d.supp_id 
    WHERE a.dc_status > 0 " . $searchQuery);
    $records = $sel->fetch();
    $totalRecords = $records->allcount;

    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_dc as a 
    LEFT JOIN  mst_supplier_new as d ON a.supp_id = d.supp_id 
    WHERE a.dc_status > 0 " . $searchQuery);
    $records = $sel->fetch();
    $totalRecordwithFilter = $records->allcount;

    $cusQuery = "SELECT * ,a.dc_remarks as dc_remarks from tbl_dc as a 
     LEFT JOIN tbl_sales_order as c  ON a.so_id = c.so_id 
     LEFT JOIN mst_supplier_new as d  ON a.supp_id = d.supp_id 
     LEFT JOIN mst_customer_branch as e ON a.supp_id = e.supp_id 
     where a.dc_status > 0 " . $searchQuery . " GROUP BY a.dc_id order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;
}
else
{
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_dc as a
    LEFT JOIN  mst_supplier_new as d ON a.supp_id = d.supp_id 
    WHERE a.dc_status > 0 AND a.branch_id=".$_SESSION['_user_branch']);
    $records = $sel->fetch();
    $totalRecords = $records->allcount;

    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_dc as a 
    LEFT JOIN  mst_supplier_new as d ON a.supp_id = d.supp_id 
    WHERE a.dc_status > 0 AND a.branch_id=".$_SESSION['_user_branch'] . $searchQuery);
    $records = $sel->fetch();
    $totalRecordwithFilter = $records->allcount;

    $cusQuery = "SELECT * ,a.dc_remarks as dc_remarks from tbl_dc as a 
     LEFT JOIN tbl_sales_order as c  ON a.so_id = c.so_id 
     LEFT JOIN mst_supplier_new as d  ON a.supp_id = d.supp_id 
     LEFT JOIN mst_customer_branch as e ON a.supp_id = e.supp_id 
     where a.dc_status > 0 AND a.branch_id=".$_SESSION['_user_branch']." " . $searchQuery . " GROUP BY a.dc_id order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;   
}


 
$cusRecords = $conn->query($cusQuery);
$data = array();

$iSno = 1;
while ($row = $cusRecords->fetch()) {
    // echo "<pre>";
    // print_r($row);
    // echo "<pre>";
    // exit;
    $dc_status = '';
    $package_link = '';
    // $no_item = $dbconn->GetCount("tbl_dc_details", "dc_id", $row->dc_id);
    $no_item = $dbconn->GetSingleReconrd("tbl_dc_details", "COUNT(*)","dc_dispatch_qty>0 AND dc_id",$row->dc_id);
    // $dc_remarks = $dbconn->GetSingleReconrd("tbl_dc","dc_remarks","dc_id",$row->dc_id);

    if($row->dc_verify_status == 1 && $row->dc_approve_status == 0)
    {
        $dc_status = '<span class="badge bg-warning">In Approval</span>';
    }
    elseif ($row->dc_verify_status == 1 && $row->dc_approve_status == 2) 
    {
        $dc_status = '<span class="badge bg-danger">Approval Rejected</span><br><b>'.$dc_remarks;
        $dc_reject = $row->dc_remarks; 
    } 
    elseif ($row->dc_verify_status == 1 && $row->dc_approve_status == 1)
    {
        $dc_status = '<span class="badge bg-success">Approved</span>';
        $dc_reject = ''; 

    } 
    else {
        $dc_status = '<span class="badge bg-grey">Draft</span>';
        $dc_reject = ''; 

    }



    if ($row->dc_approve_status == 1) {

        
    }

    if($row->dc_verify_status==1 && $row->dc_approve_status==0) 
    {
        $edit_link = '<a href="javascript:;"><i class="icon-pencil5 bg-edit-disabled mr-2"></i></a>';
        $package_link = '';
        $invoice ='';
    }
    elseif($row->dc_verify_status==1 && $row->dc_approve_status==2)
    {

        $edit_link = '<a href="dc_add.php?dc_id='.$row->dc_id.'" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';
        $package_link = '';
        $invoice ='';
    }
    elseif($row->dc_verify_status==1 && $row->dc_approve_status==1)
    {
        $edit_link = '<a href="javascript:;"><i class="icon-pencil5 bg-edit-disabled mr-2"></i></a>';
        $package_link = '<a href="package_list.php?dc_id=' . $row->dc_id . '" class="tip" title="Package List"><i class="fa fa-th-large"></i></a>';
        if($row->dc_inv_status==1)
        {
            $invoice = '<a href="javascript:;"><i class="fa fa-list-alt bg-edit-disabled mr-2"></i></a>';
        }
        else
        {
            $invoice = '<a href="dc_invoice.php?dc_id=' . $row->dc_id . '" data-popup="tooltip" title="invoice" data-original-title="Invoice" ><i class="fa fa-list-alt bg-edit mr-2"></i></a>';
        }
    }
    else
    {
        $edit_link = '<a href="dc_add.php?dc_id='.$row->dc_id.'" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';
        $package_link = '';
        $invoice ='';
    }
    
    $print_link = '<a href="dc_print.php?dc_id=' . $row->dc_id . '" class="tip" title="Print Dc"><i class="icon-printer bg-edit mr-2"></i></a>';

    $so_refno = '<a data-toggle="modal" data-target="#modalSalesDets" href="" data-id="' . $row->dc_id . '" data-popup="tooltip" title="Sales Details">' . $row->so_refno . '</a>';


    $no_item =  $no_item;
    $data[] = array(
        "sno" => $iSno,
        "dc_slno" => $row->dc_refno,
        "dc_date" =>  date("d-m-Y", strtotime($row->dc_date)),
        "so_refno" => $so_refno,
        "supp_name" => '<b>' . $row->supp_name . '</b><br><small>' . $row->branch_name . '<br></small>' . $row->branch_add2 . '',
        "no_item" =>  $no_item,
        "dc_status" =>  $dc_status.''.$dc_reject,
        "action" =>   $edit_link.$print_link.$package_link.'&nbsp;&nbsp;&nbsp;'.$invoice

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
