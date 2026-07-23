<?php

ob_start();
session_start();
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
$conn = new dbconnect();
$dbconn= new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$selectedBranch = $_POST['branch_id'];

$branch_name  = $dbconn->GetSingleReconrd("mst_branch","branch_name","branch_status = '1' AND branch_id",$selectedBranch);
$usr_email  = $dbconn->GetSingleReconrd("tbl_user","usr_logname","usr_status = '1' AND branch_id",$selectedBranch);
$pw_hint  = $dbconn->GetSingleReconrd("tbl_user","usr_logpwd","usr_status = '1' AND branch_id",$selectedBranch);


echo $branch_name."~".$usr_email."~".$pw_hint;
?>
    