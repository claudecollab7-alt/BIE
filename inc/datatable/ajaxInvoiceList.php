<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();

ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);


## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = $_POST['search']['value']; // Search value

if ($columnName == '') {
    $columnName = " item_invoice ";
}


## Custom Field value
$searchByCode = isset($_POST['searchByCode']) ? $_POST['searchByCode'] : '';
$searchBySupp = isset($_POST['searchBySupp']) ? $_POST['searchBySupp'] : '';
$searchByInv = isset($_POST['searchByInv']) ? $_POST['searchByInv'] : '';
$searchByBranch = isset($_POST['searchByBranch']) ? $_POST['searchByBranch'] : '';
$searchByYear = isset($_POST['searchByYear']) ? $_POST['searchByYear'] : '';


## Search 
$searchQuery = " ";
$app_qry = " ";
if ($searchByCode != '') {
    $searchQuery .= " and (a.inv_refno like '%" . $searchByCode . "%' ) ";
}
if ($searchBySupp != '') {
    $searchQuery .= " and (a.supp_id = " . $searchBySupp . ") ";
}
if ($searchByInv != '') {
    if($searchByInv == 1){
        $searchQuery .= " and (a.invoice_type =7) ";
    }else{
         $searchQuery .= " and (a.invoice_type != 7) ";
     }
}

if ($searchByBranch != '') {
    
    $searchQuery .= " and (a.branch_id =".$searchByBranch.") ";
}
if ($searchByYear != '') {
    
    $searchQuery .= " and (a.inv_finyr =".$searchByYear.") ";
}


// $sel = $conn->query("SELECT count(*) as allcount FROM tbl_invoice a left join mst_supplier_new as b  ON a.supp_id=b.supp_id WHERE 1 = 1" . $searchQuery);
// $records = $sel->fetch();
// $totalRecords = $records->allcount;

// ## Total number of records with filtering
// $sel = $conn->query("SELECT count(*) as allcount FROM tbl_invoice a left join mst_supplier_new as b  ON a.supp_id=b.supp_id WHERE 1 = 1" . $searchQuery);
// $records = $sel->fetch();
// $totalRecordwithFilter = $records->allcount;

## Fetch records
if($_SESSION['_user_id']==1 || $_SESSION['_user_branch']==1)
{
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_invoice a left join mst_supplier_new as b  ON a.supp_id=b.supp_id WHERE 1 = 1" . $searchQuery);
    $records = $sel->fetch();
    $totalRecords = $records->allcount;

    ## Total number of records with filtering
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_invoice a left join mst_supplier_new as b  ON a.supp_id=b.supp_id WHERE 1 = 1" . $searchQuery);
    $records = $sel->fetch();
    $totalRecordwithFilter = $records->allcount;

    $itemQuery = "SELECT * FROM tbl_invoice as a left join mst_supplier_new as b  ON a.supp_id=b.supp_id WHERE 1 = 1" . $searchQuery ." order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage ;
}
else
{
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_invoice a left join mst_supplier_new as b  ON a.supp_id=b.supp_id WHERE branch_id=".$_SESSION['_user_branch']);
    $records = $sel->fetch();
    $totalRecords = $records->allcount;

    ## Total number of records with filtering
    $sel = $conn->query("SELECT count(*) as allcount FROM tbl_invoice a left join mst_supplier_new as b  ON a.supp_id=b.supp_id WHERE branch_id =".$_SESSION['_user_branch'] . $searchQuery);
    $records = $sel->fetch();
    $totalRecordwithFilter = $records->allcount;

    $itemQuery = "SELECT * FROM tbl_invoice as a left join mst_supplier_new as b  ON a.supp_id=b.supp_id WHERE branch_id='".$_SESSION['_user_branch']."' AND 1 = 1" . $searchQuery ." order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage ;

}

$sno = 1;
$itemRecords = $conn->query($itemQuery);
$data = array();

while ($row = $itemRecords->fetch())
	{
    	//$item_count = $dbconn->GetSingleReconrd("tbl_invoice_details","COUNT(inv_id)","inv_accepted_qty !='0' AND inv_id",$row->inv_id);

		$supp_name = $dbconn->GetSingleReconrd("mst_supplier_new","supp_name","supp_id",$row->supp_id);
		$branch_name = $dbconn->GetSingleReconrd("mst_customer_branch","branch_name","branch_id",$row->cus_branch_id);
		$pay_name = $dbconn->GetSingleReconrd("mst_pay_method","pay_name","pay_id",$row->pay_id);
		//$net_amount = $dbconn->GetSingleReconrd("tbl_invoice_details","SUM(net_value)","inv_id",$row->inv_id);

        

	    if($row->inv_status == 1)
		{
            $edit_link = '<i class="icon-pencil5 bg-edit-disabled mr-2"></i>';
            if($row->invoice_type=='Q' || $row->invoice_type=='D')
            {                
                $inv_print_link = '<a href="quo_invoice_print.php?inv_id='.$row->inv_id.'" data-popup="tooltip" title="Print" data-original-title="Print" ><i class="icon-printer bg-edit mr-2"></i></a>';
            }
            else
            {
                $inv_print_link = '<a href="invoice_print.php?inv_id='.$row->inv_id.'" data-popup="tooltip" title="Print" data-original-title="Print" ><i class="icon-printer bg-edit mr-2"></i></a>';
                
            }
            
		}
		else
		{ 
            if($row->invoice_type=='Q')
            {

                $edit_link = '<a href="quo_invoice.php?inv_id='.$row->inv_id.'" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';
                $inv_print_link = '<a href="quo_invoice_print.php?inv_id='.$row->inv_id.'" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-printer bg-edit mr-2"></i></a>';
            }
            elseif($row->invoice_type=='D')
            {

                $edit_link = '<a href="dc_invoice.php?inv_id='.$row->inv_id.'" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';
                $inv_print_link = '<a href="quo_invoice_print.php?inv_id='.$row->inv_id.'" data-popup="tooltip" title="Print" data-original-title="Print" ><i class="icon-printer bg-edit mr-2"></i></a>';
            }
            else
            {
                $inv_print_link = '<a href="invoice_print.php?inv_id='.$row->inv_id.'" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-printer bg-edit mr-2"></i></a>';
                $edit_link = '<a href="mng_invoice.php?inv_id='.$row->inv_id.'" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';
            }
		}
	

		//$grn_print_link = '<li><a href="grn_print.php?grn_id='.$row->grn_id.'" class="tip " title="View GRN"><i class="fa fa-print"></i></a></li>';
        //$inv_print_link = '<a href="invoice_print.php?inv_id='.$row->inv_id.'" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-printer bg-edit mr-2"></i></a>';	
        $credit_link ='';
        if($row->pay_id == 7)
        {
             //$credit_link = '<i class="icon-credit-card bg-edit-disabled mr-2"></i>';
            if($row->inv_status == 1)
            {
                $credit_link = '<a href="mng_credit.php?inv_id='.$row->inv_id.'" data-popup="tooltip" title="Credit" data-original-title="Credit" ><i class="icon-credit-card bg-edit mr-2"></i></a>';
            }
            else
            {
                $credit_link = '<a href="javascript:;" data-popup="tooltip" title="Credit" data-original-title="Credit" ><i class="icon-credit-card bg-edit-disabled mr-2"></i></a>';
            }
        }

        if($row->pay_id == 2){
            $pay_name .= '<br><small>'.$row->pay_chq_no.'</small>';
        }
        elseif($row->pay_id == 3){
            $pay_name .= '<br><small>'.$row->pay_cardno.'</small>';
        }
        elseif($row->pay_id == 4 ||$row->pay_id== 5 || $row->pay_id==6 ){
            $pay_name .= '<br><small>'.$row->pay_refno.'</small>';
        }

        $balval="NILL";
        if($row->pay_id == 7)  
        {
            $invtype ="Credit";
            //$netval =  $dbconn->GetSingleReconrd("tbl_invoice_details", "SUM(net_value)", "inv_id", $row->inv_id);
            $netval = $row->inv_tot_value;
            $paidval =  $dbconn->GetSingleReconrd("tbl_invoice_credit_details", "sum(paid_amount)", "inv_id", $row->inv_id); 
            $balval =  number_format($netval - $paidval,2, ".","");

                if($balval == 0)
                {
                    $balval="NILL";
                }
        }  
        elseif($row->pay_id == 0)
        {
            $invtype ="-";
        }
        else{
            $invtype ="Cash Invoice";
        }

        if($row->invoice_type == 'Q'){

            $quo_refno =  $dbconn->GetSingleReconrd("tbl_quotation", "quo_refno", "quo_status = 1 AND quo_inv_id", $row->inv_id);
            $inv_type = '<span class="badge bg-secondary">Against Quotation</span><br><small><b>'.$quo_refno.'</small></b>';


        }
        elseif($row->invoice_type == 'D')
        {
            $dc_refno =  $dbconn->GetSingleReconrd("tbl_dc", "dc_refno", "dc_inv_id", $row->inv_id);
            $inv_type = '<span class="badge bg-warning">Against DC</span><br><small><b>'.$dc_refno.'</small></b>';
        }
        elseif($row->invoice_type == 'I'){
            $inv_type = '<span class="badge bg-success">Direct Invoice</span>';

        }

        

  $data[] = array(
        "inv_slno" => $sno,
        "inv_id" => $row->inv_refno,
        "itm_inv_date" => date("d-m-Y", strtotime($row->inv_date)),
        "supp_name" => $row->supp_name.'<br><small>'.$branch_name.'</small>',
        "itm_inv_amt" => $row->inv_tot_value,
        "itm_inv_paymode" => $pay_name,
        "itm_inv_Due" => $balval,
        
        "invoice_type" => $inv_type,

        "action" => $edit_link.$inv_print_link.$credit_link
		
         
    );
    $sno++;
}
## Response
$response = array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $data
);

echo json_encode($response);
