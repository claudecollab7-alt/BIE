<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
require_once("../common/access.php");

$conn = new dbconnect();
$dbconn = new dbhandler();


// 2026-10-27 search / sort input through fnSqlText / fnSqlColumn / fnSqlDir - a ' in a search box broke the query
// 2026-10-26 proforma lookup read once for the page, not one query per quotation
// 2026-10-13 all branches for viewers in Task Settings (QUOTATION W) - was admin or head office
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
 

## Read value
$draw = $_POST['draw'];
$row = (int)($_POST['start']);
$rowperpage = (int)($_POST['length']); // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = fnSqlColumn($_POST['columns'][$columnIndex]['data']); // Column name
$columnSortOrder = fnSqlDir($_POST['order'][0]['dir']); // asc or desc
$searchValue = fnSqlText($_POST['search']['value']); // Search value


$searchByCode = fnSqlText(isset($_POST['searchByCode']) ? $_POST['searchByCode'] : '');
$searchByBranch = fnSqlText(isset($_POST['searchByBranch']) ? $_POST['searchByBranch'] : '');
$searchByYear = fnSqlText(isset($_POST['searchByYear']) ? $_POST['searchByYear'] : '');
$searchQuery = " ";

// $app_qry = " ";
if ($searchByCode != '') {
    $searchQuery .= " and (b.supp_name like '%" . $searchByCode . "%' OR a.quo_refno like '%" . $searchByCode . "%'  OR a.quo_date like '%" . $searchByCode . "%' ) ";
}

if ($searchByBranch != '') {
    
    $searchQuery .= " and (a.bie_branch_id =".$searchByBranch.") ";
}

if ($searchByYear != '') {
    
    $searchQuery .= " and (a.quo_finyr =".$searchByYear.") ";
}


if (fnTaskCan('QUOTATION', 'W'))
{
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_quotation as a 
    LEFT JOIN  mst_supplier_new as b ON a.supp_id = b.supp_id 
    WHERE a.rec_del_status = 0 " . $searchQuery);

    $records = $sel->fetch();
    $totalRecords = $records->allcount;

    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_quotation as a 
    LEFT JOIN  mst_supplier_new as b ON a.supp_id = b.supp_id 
    WHERE a.rec_del_status = 0 " . $searchQuery);

    $records = $sel->fetch();
    $totalRecordwithFilter = $records->allcount;

    $cusQuery = "SELECT @sno:=@sno+1 sno, a.quo_refno, a.quo_id,a.quo_date,a.quo_value,a.branch_id,a.quo_status,a.quo_des_edit,a.so_des_gen,b.supp_name,c.branch_name,c.branch_add2,a.inv_gen_status,a.quo_verify_status,a.quo_approve_status,a.quo_inv_id,a.so_gen_status,a.quo_so_id FROM (SELECT @sno:= 0) AS sno ,  tbl_quotation as a LEFT JOIN  mst_supplier_new as b  ON a.supp_id = b.supp_id LEFT JOIN  mst_customer_branch as c  ON a.branch_id = c.branch_id WHERE a.rec_del_status = 0 " . $searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
}
else
{
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_quotation as a 
    LEFT JOIN  mst_supplier_new as b ON a.supp_id = b.supp_id 
    WHERE a.rec_del_status = 0  AND a.bie_branch_id=".$_SESSION['_user_branch']);

    $records = $sel->fetch();
    $totalRecords = $records->allcount;

    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_quotation as a 
    LEFT JOIN  mst_supplier_new as b ON a.supp_id = b.supp_id 
    WHERE a.rec_del_status = 0 AND a.bie_branch_id=".$_SESSION['_user_branch'] . $searchQuery);

    $records = $sel->fetch();
    $totalRecordwithFilter = $records->allcount;

    $cusQuery = "SELECT @sno:=@sno+1 sno, a.quo_refno, a.quo_id,a.quo_date,a.quo_value,a.branch_id,a.quo_status,a.quo_des_edit,a.so_des_gen,b.supp_name,c.branch_name,c.branch_add2,a.inv_gen_status,a.quo_verify_status,a.quo_approve_status,a.quo_inv_id,a.so_gen_status,a.quo_so_id FROM (SELECT @sno:= 0) AS sno ,  tbl_quotation as a LEFT JOIN  mst_supplier_new as b  ON a.supp_id = b.supp_id LEFT JOIN  mst_customer_branch as c  ON a.branch_id = c.branch_id WHERE a.rec_del_status = 0 AND bie_branch_id=".$_SESSION['_user_branch']." " . $searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
}


$cusRecords = $conn->query($cusQuery);
$quo_page = $cusRecords->fetchAll();
$data = array();

// proforma of each quotation on the page in one query (first one, as before)
$quo_ids = array(0);
foreach ($quo_page as $r) {
    $quo_ids[] = (int)$r->quo_id;
}
$m_pro = $dbconn->GetMap("SELECT quo_id, pro_id FROM tbl_proforma WHERE quo_id IN (" . implode(',', array_unique($quo_ids)) . ") ORDER BY pro_id");

foreach ($quo_page as $row) {
    $gen_so ='';
    $invoice ='';
    if ($row->quo_verify_status == 0) 
    {
        $quo_approval_status = '<span class="badge bg-grey">Draft</span>';
        $edit_link = '<a href="quotation.php?quo_id=' . $row->quo_id . '" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';
    } 
    elseif ($row->quo_verify_status == 1 && $row->quo_approve_status==0) 
    {
        $quo_approval_status = '<span class="badge bg-info">In Approval</span>';
        $edit_link = '<a href="javascript:;" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit-disabled mr-2"></i></a>';    
    }
    elseif ($row->quo_verify_status == 1 && $row->quo_approve_status==1 && $row->inv_gen_status==0) 
    {
        $quo_approval_status = '<span class="badge bg-success">Approved</span>';

        $edit_link = '<a href="quotation.php?quo_id='.$row->quo_id.'&status=requote"  data-popup="tooltip" title="Edit" data-original-title="Print" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';
    }
    elseif ($row->quo_verify_status == 1 && $row->quo_approve_status==2) 
    {

        $quo_approval_status = '<span class="badge bg-danger">Approval Rejected</span>';
        $edit_link = '<a href="quotation.php?quo_id=' . $row->quo_id . '" data-popup="tooltip" title="Edit" data-original-title="Print" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';
    }
    else
    {
        $quo_approval_status = '<span class="badge bg-success">Approved</span>';
        $edit_link = '<a href="javascript:;" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit-disabled mr-2"></i></a>';
    }

    
    if ($row->quo_approve_status == 1 && $row->inv_gen_status==0 && $row->so_gen_status==0)
    {
        $gen_so = '<a href="gen_so.php?quo_id='.$row->quo_id.'"  data-popup="tooltip" title="Generate Sales Order" data-original-title="Print" ><i class="fa fa-shopping-bag bg-edit mr-2"></i></a>';
        $invoice = '<a href="quo_invoice.php?quo_id=' . $row->quo_id . '" data-popup="tooltip" title="invoice" data-original-title="Invoice" ><i class="fa fa-list-alt bg-edit mr-2"></i></a>';
        
    }
    elseif($row->quo_approve_status == 1 && $row->inv_gen_status==1)
    {
        $gen_so = '<a href="javascript:;"  data-popup="tooltip" title="Generate Sales Order" data-original-title="Print" ><i class="fa fa-shopping-bag bg-edit-disabled mr-2"></i></a>';
      
        $inv_status=$dbconn->GetSingleReconrd("tbl_invoice","inv_status","inv_id",$row->quo_inv_id);
        if($inv_status==1)
        {

            $invoice = '<i class="fa fa-list-alt bg-edit-disabled mr-2"></i>';
        } 
        else
        {
            $invoice = '<a href="quo_invoice.php?inv_id=' . $row->quo_inv_id . '" data-popup="tooltip" title="invoice" data-original-title="Invoice" ><i class="fa fa-list-alt bg-edit mr-2"></i></a>';
        }

    }
    elseif($row->quo_approve_status == 1 && $row->so_gen_status==1)
    {
        $gen_so = '<a href="javascript:;"  data-popup="tooltip" title="Generate Sales Order" data-original-title="Print" ><i class="fa fa-shopping-bag bg-edit-disabled mr-2"></i></a>';
        //need a change
        // $gen_so = '<a href="gen_so.php?so_id='.$row->quo_so_id.'"  data-popup="tooltip" title="Generate Sales Order" data-original-title="Print" ><i class="fa fa-shopping-bag bg-edit mr-2"></i></a>';
      
       $invoice = '<a href="javascript:;" data-popup="tooltip" title="invoice" data-original-title="Edit" ><i class="fa fa-list-alt bg-edit-disabled mr-2"></i></a>';

      
    }
     
    

    
    
    
   
    

    $quo_print = '<a href="print_quotation.php?quo_id=' . $row->quo_id . '" data-popup="tooltip" title="Print" data-original-title="Print" ><i class="icon-printer bg-edit mr-2"></i></a>';
    // $approve_user_dat = $dbconn->GetSingleReconrd("tbl_task_user", "approve_id", "task_id", 3);
  
    // $invoice = '<a href="quo_invoice.php?quo_id=' . $row->quo_id . '" data-popup="tooltip" title="invoice" data-original-title="Edit" ><i class="fa fa-list-alt bg-edit mr-2"></i></a>';
    $pro_invoice = '';
    $pro_id = $dbconn->MapValue($m_pro, $row->quo_id);
    if($pro_id != ''){
        $pro_invoice = '<a href="quo_proforma_invoice_print.php?pro_id=' . $pro_id . '" data-popup="tooltip" title="Proforma invoice" data-original-title="Proforma Invoice" ><i class="icon-printer2 mr-2"></i></a>';
    }

    $data[] = array(
        "sno" => $row->sno,
        "quo_refno" => $row->quo_refno,
        "quo_date" => date("d-m-Y", strtotime($row->quo_date)),
        "supp_name" => '<b>' . $row->supp_name . '</b><br><small>' . $row->branch_name . '</small>' . $row->branch_add2 . '',
        "quo_value" => $row->quo_value,
        "quo_status" => $quo_approval_status,
        // "follow_status" => '1,2,3',
        "action" => $edit_link . $quo_print . $gen_so .$invoice. $pro_invoice

    );
        }


## Response

$response = array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    
    "aaData" => $data
);


echo json_encode($response);
