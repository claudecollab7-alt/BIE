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

$selectedBranch = $_POST['branch_id'];

$branch_name  = $dbconn->GetSingleReconrd("mst_branch", "branch_name", "branch_status = '1' AND branch_id", $selectedBranch);
$usr_email  = $dbconn->GetSingleReconrd("tbl_user", "usr_logname", "usr_status = '1' AND branch_id", $selectedBranch);
$pw_hint  = $dbconn->GetSingleReconrd("tbl_user", "usr_logpwd", "usr_status = '1' AND branch_id", $selectedBranch);

$sql = "SELECT * FROM tbl_user WHERE usr_status = 1 AND usr_access = 1 AND usr_logname = '" . $usr_email . "' AND usr_logpwd = '" . trim($pw_hint) . "' ";
$res = $conn->query($sql);

$no = $res->rowCount();
if ($no > 0) {
    $obj = $res->fetch(PDO::FETCH_OBJ);
    // $_SESSION['_user'] = "crm_user";
    // $_SESSION['_user_id'] = $obj->usr_id;
    $_SESSION['_user_name'] = $obj->usr_name;
    // $_SESSION['_user_group'] = $obj->usr_group;
    // $_SESSION['_user_type'] = $obj->usr_type;
    $_SESSION['_user_branch'] = $obj->branch_id;
    // $_SESSION['session_id'] = date("Ymd") . date("His");
    // $_SESSION['_usr_avatar'] = $obj->usr_avatar;
    // $_SESSION['_msg'] = "";
    // $_SESSION['_admin_multi_login'] = $_REQUEST['admin_multi_login'];
    // $_SESSION['_msg_err'] = "";
    // $_SESSION['timer'] = time();
    // $_SESSION['_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr_name", "finyr_active", 1);
    // header("location:../../home.php");
    die();
} else {
    $_SESSION['_user'] = "";
    $_SESSION['_user_id'] = "";
    $_SESSION['_user_name'] = "";
    $_SESSION['_user_group'] = "";
    $_SESSION['_user_type'] = "";
    $_SESSION['_user_branch'] = "";
    $_SESSION['session_id'] = "";
    $_SESSION['_usr_avatar'] = "";
    $_SESSION['_msg'] = "Invalid User Name / Password. <br>Please Try Again..!";
    $_SESSION['_msg_err'] = "";

    // header("location:../../index.php");
    die();
}
