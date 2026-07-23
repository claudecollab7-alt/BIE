<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();

$emp_login_name=$_POST['emp_login_name'];

$txtHid=$_POST['txtHid'];

if($_POST['mode']=="check"){
if($txtHid != ''){
    $is_exists  = $dbconn->GetSingleReconrd("tbl_user","usr_id","usr_logname='".$emp_login_name."' AND usr_id='".$txtHid."' AND usr_status",1) ;
}else{
    $is_exists  = $dbconn->GetSingleReconrd("tbl_user","usr_id","usr_logname='".$emp_login_name."' AND usr_status",1) ;
}
    
    
    if( $is_exists > 0)  { 
        echo '~1';}
       else{
    echo 'Success~0';}  
    
}


?>