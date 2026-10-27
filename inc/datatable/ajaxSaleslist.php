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
// 2026-10-26 DC / invoice / qty / quotation lookups read once for the page's SOs, not about 11 queries per SO
// 2026-10-13 all branches for viewers in Task Settings (SALES_ORDER W) - was admin or head office
// 2026-10-13 cancel link for Sales Order Cancel in Task Settings (SO_CANCEL A) - was admin
// ini_set('display_errors', '1');
//  ini_set('display_startup_errors', '1');
//  error_reporting(E_ALL);

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

if ($searchByCode != '') {
    $searchQuery .= " and (d.supp_name like '%" . $searchByCode . "%' OR a.so_refno like '%" . $searchByCode . "%'  OR a.so_date like '%" . $searchByCode . "%') ";
}
if ($searchByBranch != '') {
    
    $searchQuery .= " and (a.bie_branch_id =".$searchByBranch.") ";
}
if ($searchByYear != '') {
    
    $searchQuery .= " and (a.so_finyr =".$searchByYear.") ";
}

if (fnTaskCan('SALES_ORDER', 'W'))
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
$so_page = $cusRecords->fetchAll();
$data = array();

// lookups for the SOs on this page in one query each - item count, qty, DCs, dispatched qty, invoices, quotation no.
$so_ids = $quo_ids = array(0);
foreach ($so_page as $r) {
    $so_ids[] = (int)$r->so_id;
    $quo_ids[] = (int)$r->quo_id;
}
$in = implode(',', array_unique($so_ids));
$m_items = $dbconn->GetMap("SELECT so_id, COUNT(*) FROM tbl_sales_order_details WHERE so_id IN ($in) GROUP BY so_id");
$m_qty = $dbconn->GetMap("SELECT so_id, SUM(so_qty) FROM tbl_sales_order_details WHERE so_id IN ($in) GROUP BY so_id");
$m_dc_first = $dbconn->GetMap("SELECT so_id, dc_id FROM tbl_dc WHERE so_id IN ($in) ORDER BY dc_id");
$m_dc_max = $dbconn->GetMap("SELECT so_id, MAX(dc_id) FROM tbl_dc WHERE so_id IN ($in) GROUP BY so_id");
$m_dc_approve = $dbconn->GetMap("SELECT dc_id, dc_approve_status FROM tbl_dc WHERE so_id IN ($in)");
$m_dc_verify = $dbconn->GetMap("SELECT dc_id, dc_verify_status FROM tbl_dc WHERE so_id IN ($in)");
$m_dispatch = $dbconn->GetMap("SELECT d.so_id, SUM(dd.dc_dispatch_qty) FROM tbl_dc d JOIN tbl_dc_details dd ON dd.dc_id = d.dc_id
    WHERE dd.dc_dispatch_qty > 0 AND d.so_id IN ($in) GROUP BY d.so_id");
$m_inv = $dbconn->GetMap("SELECT so_id, GROUP_CONCAT(inv_id,'~',inv_slno ORDER BY inv_id) FROM tbl_invoice WHERE so_id IN ($in) GROUP BY so_id");
$m_quo = $dbconn->GetMap("SELECT quo_id, quo_refno FROM tbl_quotation WHERE quo_id IN (" . implode(',', array_unique($quo_ids)) . ")");

$iSno = 1;
foreach ($so_page as $row) {
    $credit ='';
    $pay_link = '';
	$item_status ='';
	
    $approve_count = 0;



    $del_item = $dbconn->MapValue($m_items, $row->so_id, 0);

    $quotation_link = '<a href="print_quotation.php?quo_id=' . $row->quo_id . '" target="_blank">' .
        $dbconn->MapValue($m_quo, $row->quo_id) . '</a>';


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
    // elseif ($row->so_approve_status == 0 && $row->so_status == 6) {
    //     $so_status = '<span class="badge bg-danger">Cancelled</span>';
    //     $edit_link = '<a href="javascript:;" data-popup="tooltip" title="Edit"><i class="icon-pencil5 bg-edit-disabled mr-2"></i></a>';
    //     $so_print = '<a href="print_sales_order.php?so_id=' . $row->so_id . '" class="tip" title="Print Sales Order"><i class="icon-printer bg-edit mr-2"></i></a>';
    //     $reject_link = '<a href="javascript:;" class="tip disable" title="Cancel Sales Order"><i class="icon-bin bg-delete-disabled px-2 mr-2"></i></a>';

    // }

   
    //For DC
    $dc_id = $dbconn->MapValue($m_dc_first, $row->so_id);

    $max_dc_id = $dbconn->MapValue($m_dc_max, $row->so_id, null);

    $total_count = $dbconn->MapValue($m_qty, $row->so_id, null);

    $dc_approve_status = $dbconn->MapValue($m_dc_approve, $dc_id);

    $dc_verify_status = $dbconn->MapValue($m_dc_verify, $dc_id);

    if($dc_id != '')
    {                                               
        //$approve_count = $dbconn->GetCount("tbl_dc_details","bal_qty = '0' AND dc_id",$max_dc_id);
        //$approve_count = $dbconn->GetCount("tbl_dc_details","dc_dispatch_qty != '0' AND dc_id",$max_dc_id);
        $approve_count = $dbconn->MapValue($m_dispatch, $row->so_id, null);
       
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
                    
                    $max_dc_approve = $dbconn->MapValue($m_dc_approve, $max_dc_id);

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
                    $max_dc_approve = $dbconn->MapValue($m_dc_approve, $max_dc_id);

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
    if($row->so_approve_status == 0 && $row->so_status == 6)
    {

        $dc_add_link = '<a href="javascript:;" class="tip disable" title="Generate DC"><i class="fas fa-truck bg-edit-disabled"></i></a>';
        $so_status = '<span class="badge bg-danger">Cancelled</span>';



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
                    
                    $max_dc_approve = $dbconn->MapValue($m_dc_approve, $max_dc_id);

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

if (fnTaskCan('SO_CANCEL', 'A', $row->bie_branch_id)) {

        if($row->so_approve_status != 1 && ($row->so_status !=6  && $row->dc_status == 0 && $row->so_cancel_status == 0)){
            $reject_link = '<a data-toggle="modal" data-target="#modalRejectDets" href="" data-id="'.$row->so_id.'" data-popup="tooltip" title="Sales Details"><i class="icon-bin bg-delete px-2 mr-2"></i></a>';
            // $so_status = '<span class="badge bg-success">Approved</span>';


        }else{
            $reject_link = '<a href="javascript:;" class="tip disable" title="Cancel Sales Order"><i class="icon-bin bg-delete-disabled px-2 mr-2"></i></a>';
            // $so_status = '<span class="badge bg-danger">Cancelled</span>';


        }

        if ($row->so_approve_status == 0 && $row->so_status == 6 && $row->dc_status == 0 && $row->so_credit_days == 0) {

            $so_status = '<span class="badge bg-danger">Cancelled</span><br><b>'.$row->so_cancel_reason;
            $edit_link = '<a href="javascript:;" data-popup="tooltip" title="Edit"><i class="icon-pencil5 bg-edit-disabled mr-2"></i></a>';
            $so_print = '<a href="print_sales_order.php?so_id=' . $row->so_id . '" class="tip" title="Print Sales Order"><i class="icon-printer bg-edit mr-2"></i></a>';
            $reject_link = '<a href="javascript:;" class="tip disable" title="Cancel Sales Order"><i class="icon-bin bg-delete-disabled px-2 mr-2"></i></a>';
            $dc_add_link = '<a href="javascript:;" class="tip disable" title="Generate DC"><i class="fas fa-truck bg-edit-disabled"></i></a>';


        }
}
    
    $invoice_nos="";

    $inv_data = $dbconn->MapValue($m_inv, $row->so_id, null);

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
        "action" => $edit_link . $so_print . $pay_link .  $dc_add_link . $reject_link

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
