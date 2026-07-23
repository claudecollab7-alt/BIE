<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);


## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = $_POST['search']['value']; // Search value

if ($columnName == '') {
    $columnName = " item_history ";
}
## Custom Field value
$searchByCode = isset($_POST['searchByCode']) ? $_POST['searchByCode'] : '';

## Search 
$searchQuery = " ";
$app_qry = " ";
if ($searchByCode != '') {
    $searchQuery .= " and ( b.item_code like '%" . $searchByCode . "%'  OR b.item_purchase_code like '%" . $searchByCode . "%'  OR a.new_selling_price like '%" . $searchByCode . "%' OR a.new_msq like '%" . $searchByCode . "%' OR a.new_maq like '%" . $searchByCode . "%' OR a.new_moq like '%" . $searchByCode . "%' ) ";
}
$sel = $conn->query("SELECT count(*) as allcount FROM tbl_itemprice_history as a LEFT JOIN tbl_item_details b ON a.item_id = b.item_id WHERE 1 = 1" . $searchQuery);
$records = $sel->fetch();
$totalRecords = $records->allcount;

## Total number of records with filtering
$sel = $conn->query("SELECT count(*) as allcount FROM tbl_itemprice_history as a LEFT JOIN tbl_item_details b ON a.item_id = b.item_id WHERE 1 = 1" . $searchQuery);
$records = $sel->fetch();
$totalRecordwithFilter = $records->allcount;

## Fetch records
$itemQuery = "SELECT * FROM tbl_itemprice_history as a LEFT JOIN tbl_item_details b ON a.item_id = b.item_id WHERE 1 = 1" . $searchQuery .
    " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage ;

$sno = 1;
$itemRecords = $conn->query($itemQuery);
$data = array();



while ($row = $itemRecords->fetch())
	{
        $user_branch = $_SESSION['_user_branch'];
    	$usr_name = $dbconn->GetSingleReconrd("tbl_user","usr_name","usr_id",$row->created_by);

				$result_branch = $conn->query("SELECT * FROM mst_branch WHERE branch_id = $user_branch");

                if ($result_branch->rowCount() > 0) {

					$branch_data = $result_branch->fetch(PDO::FETCH_OBJ);
                   
                    $result1 = $conn->query("SELECT ".$branch_data->branch_item_maq." as item_max_qty, ".$branch_data->branch_item_price." as item_price, ".$branch_data->branch_item_discount." as item_discount, ".
                    $branch_data->branch_item_cost_price." as item_cost_price, ".$branch_data->branch_item_selling_price." as item_selling_price, ".$branch_data->branch_item_msq." as item_min_qty, ".
                    $branch_data->branch_item_moq." as item_order_min_qty FROM tbl_item_stock WHERE item_id = '".$row->item_id."'");
                    }
                    if ($result1->rowCount()>0)
                    {
                        $res = $result1->fetch(PDO::FETCH_OBJ);	
                        $item_id=$res->item_id;
                        // $new_item_uom=$obj->new_item_uom;
                        // $new_item_hsn=$obj->new_item_hsn;
                        
                        
                                                                                            
                    }
        // $field_name = $dbconn->GetSingleReconrd("mst_branch","branch_item_selling_price","branch_id",$_SESSION['_user_branch']);


    	// $new_selling_price = $dbconn->GetSingleReconrd("tbl_item_stock","'.$field_name.'","item_id",$row->item_id);
    	// $usr_name = $dbconn->GetSingleReconrd("tbl_item_stock","usr_name","item_id",$row->item_id);
           
	
  $data[] = array(
        "auto_id"=> $sno,
        "item_code" => $row->item_code,
        "item_purchase_code" => $row->item_purchase_code,
        "item_desciption" => $row->item_desciption,
       /* "new_price" =>$row->new_price,
        "new_discount" => $row->new_discount,
        "new_cost_price" => $row->new_cost_price,*/
        "item_selling_price" => $res->item_selling_price,
        "item_min_qty" => $res->item_min_qty,
        "item_max_qty" => $res->item_max_qty,
        "item_order_min_qty" => $res->item_order_min_qty,
		"itm_upt" => '<b>'.$usr_name.'</b><br><small>'.date("d-m-Y H:i:s", strtotime($row->created_dtm)).'</small>',
        
		
         
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
