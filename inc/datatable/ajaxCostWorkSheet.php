<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();

/*ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);*/

## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = $_POST['search']['value']; // Search value


## Custom Field value
$searchByCode = isset($_POST['searchByCode']) ? $_POST['searchByCode'] : '';
$item_type_id = isset($_POST['item_type_id']) ? $_POST['item_type_id'] : '';
## Search 
$searchQuery = " ";
// $app_qry = " ";
if ($item_type_id != '') {
    
    $searchQuery .= " and (a.item_type =".$item_type_id.") ";
}
if ($searchByCode != '') {
    $searchQuery .= " and (a.item_code like '%" . $searchByCode . "%' OR a.item_purchase_code like '%" . $searchByCode . "%'  OR a.item_desciption like '%" . $searchByCode . "%'
    OR a.item_cost_price like '%" . $searchByCode . "%' ) ";
}


$sel = $conn->query("SELECT count(*) as allcount  FROM tbl_item_details as a 
									        LEFT JOIN mst_uom as b ON a.item_uom = b.uom_id
									        LEFT JOIN mst_category as c on a.item_category = c.category_id 
									        LEFT JOIN item_type as d ON a.item_type = d.item_type_id
									        WHERE a.item_status = 1 " . $searchQuery);


$records = $sel->fetch();
$totalRecords = $records->allcount;

## Total number of records with filtering
$sel = $conn->query("SELECT count(*) as allcount  FROM tbl_item_details as a 
									        LEFT JOIN mst_uom as b ON a.item_uom = b.uom_id
									        LEFT JOIN mst_category as c on a.item_category = c.category_id 
									        LEFT JOIN item_type as d ON a.item_type = d.item_type_id
									        WHERE a.item_status = 1 " . $searchQuery);


$records = $sel->fetch();
$totalRecordwithFilter = $records->allcount;

## Fetch records
$itemQuery =  "SELECT a.*, b.uom_name, c.category_name,d.item_name FROM tbl_item_details as a 
LEFT JOIN mst_uom as b ON a.item_uom = b.uom_id
LEFT JOIN mst_category as c on a.item_category = c.category_id 
LEFT JOIN item_type as d ON a.item_type = d.item_type_id
WHERE a.item_status = 1 " . $searchQuery .
    " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;


$itemRecords = $conn->query($itemQuery);
$data = array();
$sno = 1;



// if($_REQUEST['item_type_id'] != "")
// {
//     $sel .=" AND a.item_type = '".$_REQUEST['item_type_id']."'";
// }
// else
// {
//     $sel .=" AND a.item_type IN (1,2,5,4,3,6)";
// }

if ($itemRecords->rowCount() > 0) {

    $Sno = 1;
    $margin_price = 0;
    $item_selling_price = $item_cost_price = 0;
    $margin_percentage = '';

    while ($row = $itemRecords->fetch()) {
        $item_uom = $dbconn->GetSingleReconrd("mst_uom", "uom_name", "uom_id", $row->item_uom);

        if ($row->item_image != "") {
            $img_link = '<a class="fancybox" href="project_img/item_image/' . $row->item_image . '">
            <img src="project_img/item_image/' . $row->item_image . '" width="30px" height="30px" alt=""></a>';
        } else {
            $img_link = '';
        }
        $branch_item_selling_price = $dbconn->GetSingleReconrd("mst_branch","branch_item_selling_price","branch_id",$_SESSION['_user_branch']);
        $branch_item_cost_price = $dbconn->GetSingleReconrd("mst_branch","branch_item_cost_price","branch_id",$_SESSION['_user_branch']);
        $item_selling_price = $dbconn->GetSingleReconrd("tbl_item_stock", "$branch_item_selling_price", "item_id", $row->item_id);
        $item_cost_price = $dbconn->GetSingleReconrd("tbl_item_stock", "$branch_item_cost_price", "item_id", $row->item_id);

        $item_selling_price = $item_selling_price;
        $item_cost_price = $item_cost_price;


       if ($item_selling_price != 0 && $item_cost_price != 0) {

            $margin_price = (float)$item_selling_price - (float)$item_cost_price;


            $margin_percentage = ((($margin_price) / (float)$item_cost_price) * 100);
        } else {

            $margin_price = '';
            $margin_percentage = '';
        }


        // if ($_REQUEST['item_type_id'] != "") {
        //     $sql .= " AND a.item_type = '" . $_REQUEST['item_type_id'] . "'";
        // } else {
        //     $sql .= " AND a.item_type IN (1,2,5,4,3,6)";
        // }
        $margin_price_formatted = number_format((float)$margin_price, 2); 
        $margin_percentage_formatted = number_format((float)$margin_percentage, 2);

        // if ($_REQUEST['item_type_id'] != "") {
        //     $sql .= " AND a.item_type = '" . $_REQUEST['item_type_id'] . "'";
        // } else {
        //     $sql .= " AND a.item_type IN (1,2,5,4,3,6)";
        // }


        $data[] = array(
            "item_id" => $sno,
            "img_link" => $img_link,
            "item_code" => $row->item_code,
            "item_purchase_code" => $row->item_purchase_code,
            "item_desciption" => $row->item_desciption,
            "item_selling_price" => number_format(($row->item_selling_price), 2),
            "item_cost_price" => number_format(($row->item_cost_price), 2),
            "margin_price" => $margin_price_formatted,
            "margin_percentage" => $margin_percentage_formatted
        );
        $sno++;
    }
}

## Response
$response = array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $data
);

echo json_encode($response);
