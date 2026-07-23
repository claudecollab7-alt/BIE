<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();


// ini_set('display_errors', '1');
//  ini_set('display_startup_errors', '1');
//  error_reporting(E_ALL);

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
    $searchQuery .= " and (d.supp_name like '%" . $searchByCode . "%' OR a.so_refno like '%" . $searchByCode . "%'  OR a.so_date like '%" . $searchByCode . "%') ";
}
if ($searchByBranch != '') {
    
    $searchQuery .= " and (a.bie_branch_id =".$searchByBranch.") ";
}

if($_SESSION['_user_id']== 1 || $_SESSION['_user_branch'] == 1)
{

    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_sales_order as a
    LEFT JOIN  mst_supplier_new as d ON a.supp_id = d.supp_id 
    WHERE a.accounts_verify_status = 0 " . $searchQuery);
    $records = $sel->fetch();
    $totalRecords = $records->allcount;

    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_sales_order as a 
    LEFT JOIN  mst_supplier_new as d ON a.supp_id = d.supp_id 
    WHERE a.accounts_verify_status = 0 " . $searchQuery);
    $records = $sel->fetch();
    $totalRecordwithFilter = $records->allcount;


    $cusQuery = "SELECT * from tbl_sales_order as a  LEFT JOIN tbl_quotation as b ON  b.quo_id = a.quo_id  LEFT JOIN mst_customer_branch as c  ON b.branch_id = c.branch_id LEFT JOIN mst_supplier_new as d  ON a.supp_id = d.supp_id 
    where a.accounts_verify_status = 0 " . $searchQuery . " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;
}
else{

    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_sales_order as a
    LEFT JOIN  mst_supplier_new as d ON a.supp_id = d.supp_id 
    WHERE a.accounts_verify_status = 0 AND a.bie_branch_id='".$_SESSION['_user_branch']."' " . $searchQuery);
    $records = $sel->fetch();
    $totalRecords = $records->allcount;

    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_sales_order as a 
    LEFT JOIN  mst_supplier_new as d ON a.supp_id = d.supp_id 
    WHERE a.accounts_verify_status = 0 AND a.bie_branch_id='".$_SESSION['_user_branch']."' " . $searchQuery);
    $records = $sel->fetch();
    $totalRecordwithFilter = $records->allcount;


    $cusQuery = "SELECT * from tbl_sales_order as a  LEFT JOIN tbl_quotation as b ON  b.quo_id = a.quo_id  LEFT JOIN mst_customer_branch as c  ON b.branch_id = c.branch_id LEFT JOIN mst_supplier_new as d  ON a.supp_id = d.supp_id 
    WHERE a.accounts_verify_status = 0 AND a.bie_branch_id='".$_SESSION['_user_branch']."' " . $searchQuery . " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;

}

  
$cusRecords = $conn->query($cusQuery);
$data = array();

$iSno = 1;
while ($row = $cusRecords->fetch()) {
    $credit ='';
    $pay_link = '';
	$item_status ='';
	
    $approve_count = 0;



    $del_item = $dbconn->GetCount("tbl_sales_order_details", "so_id", $row->so_id);

    $quotation_link = '<a href="print_quotation.php?quo_id=' . $row->quo_id . '" target="_blank">' .
        $dbconn->GetSingleReconrd("tbl_quotation", "quo_refno", "quo_id", $row->quo_id) . '</a>';


        $dc_add_link='';
    if ($row->so_status == 1 && $row->accounts_status == 0) {
        $so_status = '<span class="badge bg-primary">Processing</span>';
        $edit_link = '<a href="gen_so.php?so_id=' . $row->so_id . '" data-popup="tooltip" title="Edit" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';
        $so_print = '<a href="print_sales_order.php?so_id=' . $row->so_id . '" class="tip" title="Print Sales Order"><i class="icon-printer bg-edit mr-2"></i></a>';
    } elseif ($row->so_status == 1 && $row->accounts_status == 1) {
        $so_status = '<span class="badge bg-grey">In Accounts</span>';
        $edit_link = '<a href="javascript:;" data-popup="tooltip" title="Edit"><i class="icon-pencil5 bg-edit-disabled mr-2"></i></a>';
        $so_print = '<a href="print_sales_order.php?so_id=' . $row->so_id . '" class="tip" title="Print Sales Order"><i class="icon-printer bg-edit mr-2"></i></a>';
    }
    if ($row->accounts_status == 1 && $row->pay_status == 2) {
        $so_status = '<span class="badge bg-warning">In Approval</span>';
    } elseif ($row->accounts_status == 1 && $row->bal_value <= 0) {
        $so_status = '<span class="badge bg-warning">In Approval</span>';
    }

    if ($row->so_approve_status == 2 && $row->so_status == 4) {
        $so_status = '<span class="badge bg-danger">Approval Rejected</span>';
        $edit_link = '<a href="gen_so.php?so_id=' . $row->so_id . '" data-popup="tooltip" title="Edit" data-original-title="Print" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';
        $so_print = '<a href="print_sales_order.php?so_id=' . $row->so_id . '" class="tip" title="Print Sales Order"><i class="icon-printer bg-edit mr-2"></i></a>';
        
    } elseif ($row->so_approve_status == 1 && $row->so_status == 5) {
        $so_status = '<span class="badge bg-success">Approved</span>';
        $edit_link = '<a href="javascript:;" data-popup="tooltip" title="Edit"><i class="icon-pencil5 bg-edit-disabled mr-2"></i></a>';
        $so_print = '<a href="print_sales_order.php?so_id=' . $row->so_id . '" class="tip" title="Print Sales Order"><i class="icon-printer bg-edit mr-2"></i></a>';
    }

   
    //For DC
    $dc_id = $dbconn->GetSingleReconrd("tbl_dc","dc_id","so_id",$row->so_id);

    $max_dc_id = $dbconn->GetSingleReconrd("tbl_dc","MAX(dc_id)","so_id",$row->so_id);

    $total_count = $dbconn->GetSingleReconrd("tbl_sales_order_details","SUM(so_qty)","so_id",$row->so_id);

    $dc_approve_status = $dbconn->GetSingleReconrd("tbl_dc","dc_approve_status","dc_id",$dc_id);

    $dc_verify_status = $dbconn->GetSingleReconrd("tbl_dc","dc_verify_status","dc_id",$dc_id);

    if($dc_id != '')
    {                                               
        //$approve_count = $dbconn->GetCount("tbl_dc_details","bal_qty = '0' AND dc_id",$max_dc_id);
        //$approve_count = $dbconn->GetCount("tbl_dc_details","dc_dispatch_qty != '0' AND dc_id",$max_dc_id);
        $dc_ids = $dbconn->GetSingleReconrd("tbl_dc","group_concat(dc_id)","so_id",$row->so_id);                
        $approve_count = $dbconn->GetSingleReconrd("tbl_dc_details","SUM(dc_dispatch_qty)","dc_id IN (".$dc_ids.") AND dc_dispatch_qty>0 AND 1",1);     
       
    }
    //DC End 
    $full_dc ='';
    if ($row->so_status == 5  && $row->so_approve_status == 1) {
        $full_dc = '<span class="badge bg-success">'.$approve_count. ' / ' .$total_count.'</span>';
		$item_status = '<span class="badge bg-primary">Processing</span>';

       // $dc_link = '<a href="dc_add.php?so_id=' . $row->so_id . '" class="tip" title="Generate DC" ><i class="fas fa-truck"></i></a>';
        if($row->dc_status == 1)
        {
            if($dc_approve_status == 0) // DC Not approved
            {
                if($dc_verify_status == 1 && $dc_approve_status!=2)
                {

                    $dc_add_link = '<a href="javascript:;" class="tip disable" title="Generate DC"><i class="fas fa-truck bg-edit-disabled"></i></a>';
                }
                else
                {

                    $dc_add_link = '<a href="dc_add.php?dc_id='.$dc_id.'" class="tip" title="Generate DC"><i class="fas fa-truck"></i></a>';
                }
            }
            else // DC Approve
            {
                if($total_count == $approve_count)
                {
                    $full_dc = '<span class="badge bg-success">'.$approve_count. ' / ' .$total_count.'</span>';
					$item_status = '<span class="badge bg-success">Despatched</span>';

                    $dc_add_link = '<a href="javascript:;" class="tip disable" title="Generate DC"><i class="fas fa-truck bg-edit-disabled"></i></a>';
                }
                elseif($total_count > $approve_count)
                {
                    $full_dc = '<span class="badge bg-success">'.$approve_count. ' / ' .$total_count.'</span>';
				    $item_status = '<span class="badge bg-secondary">Partially Despatched</span>';
                    
                    $max_dc_approve = $dbconn->GetSingleReconrd("tbl_dc","dc_approve_status","dc_id",$max_dc_id);

                    if($max_dc_approve==0)
                    {
                        $dc_add_link = '<a href="dc_add.php?dc_id='.$max_dc_id.'" class="tip" title="Generate DC"><i class="fas fa-truck"></i></a>';
                    }
                    else
                    {
                        $dc_add_link = '<a href="dc_add.php?so_id='.$row->so_id.'&status=partial" class="tip" title="Generate DC"><i class="fas fa-truck"></i></a>';
                    }
                }

            }
        }
        else
        {
            $dc_add_link = '<a href="dc_add.php?so_id='.$row->so_id.'" class="tip" title="Generate DC"><i class="fas fa-truck"></i></a>';
        }
    }
    if($row->so_approve_status == 2 && $row->so_status == 4) {
        $so_rej = $row->so_remarks;
    } elseif ($row->so_approve_status == 0 && $row->so_status == 1){
        $so_rej = '';
    } elseif ($row->so_approve_status == 1 && $row->so_status == 5){
        $so_rej = '';
    }
    if($row->so_verify_status == 1 && $row->pay_status == 3) {
        $so_status = '<span class="badge bg-danger">In Credit Sales</span>';
    }
    if($row->so_approve_status == 3 && $row->pay_status == 4){
        $so_status = '<span class="badge bg-success">Approved</span>';
        //$dc_link = '<a href="dc_add.php?so_id=' . $row->so_id . '" class="tip" title="Generate DC" ><i class="fas fa-truck"></i></a>'; 
        $so_print = '<a href="print_sales_order.php?so_id=' . $row->so_id . '" class="tip" title="Print Sales Order"><i class="icon-printer bg-edit mr-2"></i></a>';
        $credit = '<span style="color:red;">Credit Sales<span>';
        $full_dc = '<span class="badge bg-success">'.$approve_count. ' / ' .$total_count.'</span>';
		$item_status = '<span class="badge bg-primary">Processing</span>';


        if($row->dc_status == 1)
        {
            if($dc_approve_status == 0) // DC Not approved
            {
                if($dc_verify_status == 1 && $dc_approve_status!=2)
                {

                    $dc_add_link = '<a href="javascript:;" class="tip disable" title="Generate DC"><i class="fas fa-truck bg-edit-disabled"></i></a>';
                }
                else
                {

                    $dc_add_link = '<a href="dc_add.php?dc_id='.$dc_id.'" class="tip" title="Generate DC"><i class="fas fa-truck"></i></a>';
                }
            }
            else // DC Approve
            {
                if($total_count == $approve_count)
                {
                    $full_dc = '<span class="badge bg-success">'.$approve_count. ' / ' .$total_count.'</span>';
					$item_status = '<span class="badge bg-success">Despatched</span>';

                    $dc_add_link = '<a href="javascript:;" class="tip disable" title="Generate DC"><i class="fas fa-truck bg-edit-disabled"></i></a>';
                }
                elseif($total_count > $approve_count)
                {
                    $full_dc = '<span class="badge bg-success">'.$approve_count. ' / ' .$total_count.'</span>';
				    $item_status = '<span class="badge bg-secondary">Partially Despatched</span>';
                    $max_dc_approve = $dbconn->GetSingleReconrd("tbl_dc","dc_approve_status","dc_id",$max_dc_id);

                    if($max_dc_approve==0)
                    {
                        $dc_add_link = '<a href="dc_add.php?dc_id='.$max_dc_id.'" class="tip" title="Generate DC"><i class="fas fa-truck"></i></a>';
                    }
                    else
                    {
                        $dc_add_link = '<a href="dc_add.php?so_id='.$row->so_id.'&status=partial" class="tip" title="Generate DC"><i class="fas fa-truck"></i></a>';
                    }
                }

            }
        }
        else
        {
            $dc_add_link = '<a href="dc_add.php?so_id='.$row->so_id.'" class="tip" title="Generate DC"><i class="fas fa-truck"></i></a>';
        }
    }

    if($row->so_approve_status = 3  && $row->so_credit_days > 0){
        $so_status = '<span class="badge bg-success">Approved</span>';
        //$dc_link = '<a href="dc_add.php?so_id=' . $row->so_id . '" class="tip" title="Generate DC" ><i class="fas fa-truck"></i></a>'; 
        $so_print = '<a href="print_sales_order.php?so_id=' . $row->so_id . '" class="tip" title="Print Sales Order"><i class="icon-printer bg-edit mr-2"></i></a>'; 
        $credit = '<span style="color:red;">Credit Sales<span>';
        $full_dc = '<span class="badge bg-success">'.$approve_count. ' / ' .$total_count.'</span>';
		$item_status = '<span class="badge bg-primary">Processing</span>';


        if($row->dc_status == 1)
        {
            if($dc_approve_status == 0) // DC Not approved
            {
                if($dc_verify_status == 1 && $dc_approve_status!=2)
                {

                    $dc_add_link = '<a href="javascript:;" class="tip disable" title="Generate DC"><i class="fas fa-truck bg-edit-disabled"></i></a>';
                }
                else
                {

                    $dc_add_link = '<a href="dc_add.php?dc_id='.$dc_id.'" class="tip" title="Generate DC"><i class="fas fa-truck"></i></a>';
                }
            }
            else // DC Approve
            {
                if($total_count == $approve_count)
                {
                    $full_dc = '<span class="badge bg-success">'.$approve_count. ' / ' .$total_count.'</span>';
					$item_status = '<span class="badge bg-success">Despatched</span>';

                    $dc_add_link = '<a href="javascript:;" class="tip disable" title="Generate DC"><i class="fas fa-truck bg-edit-disabled"></i></a>';
                }
                elseif($total_count > $approve_count)
                {
                    $full_dc = '<span class="badge bg-success">'.$approve_count. ' / ' .$total_count.'</span>';
				    $item_status = '<span class="badge bg-secondary">Partially Despatched</span>';
                    
                    $max_dc_approve = $dbconn->GetSingleReconrd("tbl_dc","dc_approve_status","dc_id",$max_dc_id);

                    if($max_dc_approve==0)
                    {
                        $dc_add_link = '<a href="dc_add.php?dc_id='.$max_dc_id.'" class="tip" title="Generate DC"><i class="fas fa-truck"></i></a>';
                    }
                    else
                    {
                        $dc_add_link = '<a href="dc_add.php?so_id='.$row->so_id.'&status=partial" class="tip" title="Generate DC"><i class="fas fa-truck"></i></a>';
                    }
                }

            }
        }
        else
        {
            $dc_add_link = '<a href="dc_add.php?so_id='.$row->so_id.'" class="tip" title="Generate DC"><i class="fas fa-truck"></i></a>';
        }
    }
    
    $so_refno = '<a data-toggle="modal" data-target="#modalSalesDets" href="" data-id="'.$row->so_id.'" data-popup="tooltip" title="Sales Details">'.$row->so_refno.'</a>';
    
    $invoice_nos="";

    $inv_data = $dbconn->GetSingleReconrd("tbl_invoice","group_concat(inv_id,'~',inv_slno)","so_id",$row->so_id);

    if($inv_data !="")
    {
        $inv_dets = explode(",",$inv_data);
        foreach ($inv_dets as $value) {
            $ind_inv_dets = explode("~",$value);
            
            $invoice_nos .=  '<a target="_blank" href="invoice_print.php?inv_id='.$ind_inv_dets[0].'">'.$ind_inv_dets[1]. "</a>,<br>";
        }
    }

   
    
    $del_item = $del_item;


    $data[] = array(
        "sno" => $iSno,
        "so_refno" => $so_refno,
        "so_date" => date("d-m-Y", strtotime($row->so_date)),
        "supp_name" => '<b>' . $row->supp_name . '</b><br><small>' . $row->branch_name . '<br></small>' . $row->branch_add2 . '',
        "quo_refno" => $quotation_link,
        "so_value" => $row->item_net_val,
        "bal_value" => $row->bal_value,
        "del_item" => $del_item,
        "so_status" => $so_status.'<br>'.$so_rej.'<b><small>'.$credit.'</b><small>',
        "invoice_nos" => $invoice_nos,
        "full_dc"=>$full_dc,
        "dc_status"=>$item_status,
        "action" => $edit_link . $so_print . $pay_link .  $dc_add_link

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
