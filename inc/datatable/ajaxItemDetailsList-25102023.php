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

if ($columnName == '') {
    $columnName = " grn_date ";
}
## Custom Field value
$searchByCode = isset($_POST['searchByCode']) ? $_POST['searchByCode'] : '';

## Search 
$searchQuery = " ";
$app_qry = " ";
if ($searchByCode != '') {
    $searchQuery .= " and (a.item_code like '%" . $searchByCode . "%' OR a.item_purchase_code like '%" . $searchByCode . "%'  OR a.supp_item_code like '%" . $searchByCode . "%'
    OR a.item_desciption like '%" . $searchByCode . "%' ) ";
}

/*
$app_user = $dbconn->GetSingleReconrd("mst_task_setting","app_usr_id"," task_id", 1 );
if($app_user == $_SESSION['_user_id']){
	$searchQuery .= " AND a.grn_status > 1 ";
}
*/

## Total number of records without filtering
$sel = $conn->query("SELECT count(*) as allcount FROM tbl_item_details a WHERE a.item_status = 1 " . $searchQuery);
$records = $sel->fetch();
$totalRecords = $records->allcount;

## Total number of records with filtering
$sel = $conn->query("SELECT count(*) as allcount FROM tbl_item_details a WHERE a.item_status = 1  " . $searchQuery);
$records = $sel->fetch();
$totalRecordwithFilter = $records->allcount;

## Fetch records
$itemQuery = "SELECT * FROM tbl_item_details a WHERE a.item_status = 1 " . $searchQuery .
    " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;


$itemRecords = $conn->query($itemQuery);
$data = array();

$sno = 1;

while ($row = $itemRecords->fetch()) {

    $del_item="";
    $supp_map =  "";
    if($row->supp_id!='')
    {
        $del_item = $dbconn->GetSingleReconrd("tbl_item_details", "supp_id", "supp_id !='' AND item_id",$row->item_id);
        $supp_dets = explode(",",$del_item);
        $supp_map = count($supp_dets);
    }
    else{
        $supp_map='';
    }
    
   
    
    $item_uom = $dbconn->GetSingleReconrd("mst_uom", "uom_name", "uom_id", $row->item_uom);
    //$item_type = $dbconn->GetSingleReconrd("item_type","item_name","item_type_id",$row->item_type);
    $item_category = $dbconn->GetSingleReconrd("mst_category", "category_name", "category_id", $row->item_category);
    $item_division  = $dbconn->GetSingleReconrd("mst_division", "division_name", "division_id", $row->item_division);
    $item_hsn = $dbconn->GetSingleReconrd("mst_hsn", "hsn_code", "hsn_id", $row->item_hsn);

    // if ($_SESSION['_user_type'] == 'A' || $_SESSION['_user_type'] == 'S') {
    //     $del_link = '<a href="" class="delete" rel="'.$row->item_id.'" data-popup="tooltip" title="Remove" data-original-title="Remove" ><i class="icon-bin bg-delete mr-2"></i></a>';
    // }
    // $edit_link = '<a href="mst_item_details.php?item_id=' . $row->item_id . '" class="tip" title="Edit"><i class="fa fa-edit"></i></a>';
	
    $manu_link = '';
    $SQL1 = "SELECT COUNT(supp_id) as supp_cnt FROM tbl_supp_items  WHERE item_id='" . $row->item_id . "' ";
    $res = $conn->query($SQL1);
    if ($res->rowCount() > 0) {
        $row1 = $res->fetch(PDO::FETCH_OBJ);
    }
    $supp_link = '<a data-toggle="modal" data-target="#suppLink" href="" data-id="'.$row->item_id.'" data-popup="tooltip" title="Supplier (' . $row1->supp_cnt . ')"><i class="icon-file-eye mr-2"></i></a>';

    if ($row->item_image != "") {
        $item_image = '<a class="fancybox" href="project_img/item_image/' . $row->item_image . '"><img src="project_img/item_image/' . $row->item_image . '" width="50px" height="50px" alt=""></a>';
    } else {
        $item_image    = '<img class="fancybox"  src="project_img/no-image.jpg" width="50px" height="50px" >';
    }
    $supp_link = '';

    $price_update_link = '<a href="mst_itemprice_history.php?item_id='.$row->item_id.'" data-popup="tooltip" title="Item Price Update" data-original-title="Edit" ><i class="icon-pencil4 mr-2"></i></a>';
    $price_view_link = '<a data-toggle="modal" data-target="#priceViewLink" href="" data-id="'.$row->item_id.'" data-popup="tooltip" title="Item View"><i class="icon-file-eye mr-2"></i></a>';

    $view_link = '<a data-toggle="modal" data-target="#viewLink" href="" data-id="'.$row->item_id.'" data-popup="tooltip" title="Item View"><i class="icon-file-eye2 mr-2"></i></a>';
    // $edit_link = '<a href="mst_item_details.php?item_id='.$row->item_id.'" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 mr-2"></i></a>';
    
    if($_SESSION['_user_branch'] == '1'){
    $edit_link = '<a href="mst_item_details.php?item_id='.$row->item_id.'" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 mr-2"></i></a>';
	$price_history_link = '<a href="mst_itemprice_history.php?item_id=' . $row->item_id . '" class="tip" title="Update Price History" data-original-title="Item Price Update"><i class="fa fa-history mr-2"></i></a>';
	$update_history_link = '<a data-toggle="modal" data-target="#modalupdateitms" href="" data-id="'.$row->item_id.'" class="tip" title="Updated History " data-original-title="Item Price Update"><i class="fa fa-retweet mr-2"></i></a>';
    $del_link = '<a href="" class="delete" rel="'.$row->item_id.'" data-popup="tooltip" title="Remove" data-original-title="Remove" ><i class="icon-bin bg-delete mr-2"></i></a>';

    }
    else{
        $edit_link = '<a href="javascript:;"  data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 mr-2 bg-edit-disabled mr-2"></i></a>';
        $price_history_link = '<a href="javascript:;"  data-popup="tooltip" title="Update Price History" data-original-title="Item Price Update" ><i class="fa fa-history mr-2 bg-edit-disabled mr-2"></i></a>';
        $update_history_link = '<a href="javascript:;"  data-popup="tooltip" title="Update History" data-original-title="Item Price Update" ><i class="fa fa-retweet mr-2 bg-edit-disabled mr-2"></i></a>';
        $del_link = '<a href="javascript:;"  data-popup="tooltip" title="Remove" data-original-title="Remove" ><i class="icon-bin bg-delete-disabled mr-2"></i></a>';
        
    }
    $item_desciption = '<a data-toggle="modal" data-target="#modalitemDets" href="" data-id="'.$row->item_id.'" data-popup="tooltip" title="Item Details">'.$row->item_desciption.'</a>';

    $supp_map_link = '<a data-toggle="modal" data-target="#modalSuppMap" href="" data-id="'.$row->item_id
    .'" data-popup="tooltip" title="Sales Details">'.$supp_map.'</a>';
    	
    $data[] = array(
        "item_code" => $row->item_code,
        "item_purchase_code" => $row->item_purchase_code,
        "itm_det_img" => $item_image,
        "itm_det_div" => $item_division,
        "item_desciption" => $item_desciption,
        "itm_det_hsn" => $item_hsn,
        "itm_det_uom" => $item_uom,
        "itm_supp_map" => $supp_map_link,
        //"action" => $view_link . $edit_link . $manu_link . $del_link . $supp_link . $price_update_link . $price_view_link
        "action" => $edit_link . $del_link . $price_history_link . $update_history_link
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
