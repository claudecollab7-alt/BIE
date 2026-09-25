<?php
ob_start();
session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
require_once("../common/csrf.php");

// 2026-09-22 column names validated, cannot touch a qty column
// 2026-09-24 login + csrf check added

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$conn = new dbconnect();
$dbconn = new dbhandler();

//-------------------save--------------//

// login + csrf required before any write
if (empty($_SESSION['_user_id']) || !csrf_check_ajax()) {
    echo "Session expired. Please reload the page.";
    die();
}

if ($_POST['mode'] == 'save') {

    // if ($mst_exist != "") {
    //     $_SESSION['_msg_err'] = "stock Type Already Exist..!";
    //     header("location:store_stock_list.php");
    //     die();
    // }

    $branch_rack = $dbconn->GetSingleReconrd("mst_branch", "branch_stock_location_rack_field", "branch_id", $_SESSION['_user_branch']);
    $branch_row = $dbconn->GetSingleReconrd("mst_branch", "branch_stock_location_row_field", "branch_id", $_SESSION['_user_branch']);

    // location only, never a qty column, so nothing is written to tbl_stock_flow
    if (!preg_match('/^[A-Za-z0-9_]{1,30}$/', (string)$branch_row)
        || !preg_match('/^[A-Za-z0-9_]{1,30}$/', (string)$branch_rack)
        || substr($branch_row, -6) === '_stock' || substr($branch_rack, -6) === '_stock') {
        echo "Invalid location columns configured for this branch.";
        die();
    }

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
