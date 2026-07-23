<?php
ob_start();
session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$conn = new dbconnect();
$dbconn = new dbhandler();

//-------------------save--------------//

if ($_POST['mode'] == 'save') {

    // if ($mst_exist != "") {
    //     $_SESSION['_msg_err'] = "stock Type Already Exist..!";
    //     header("location:store_stock_list.php");
    //     die();
    // }

    $branch_rack = $dbconn->GetSingleReconrd("mst_branch", "branch_stock_location_rack_field", "branch_id", $_SESSION['_user_branch']);
    $branch_row = $dbconn->GetSingleReconrd("mst_branch", "branch_stock_location_row_field", "branch_id", $_SESSION['_user_branch']);

    $stmt = null;
    $stmt = $conn->prepare("UPDATE  tbl_item_stock SET $branch_row = :branch_loc_row, $branch_rack = :branch_loc_rack WHERE item_id = :item_id");
    $data = array(

        ':item_id' => $_POST['item_id'],
        ':branch_loc_row' => $_POST['branch_loc_row'],
        ':branch_loc_rack' => $_POST['branch_loc_rack']
    );
    $stmt->execute($data);
    die();
}
