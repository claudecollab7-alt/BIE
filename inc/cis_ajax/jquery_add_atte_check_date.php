<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$attn_date=$_POST['attn_date'];
$emp_id=$_POST['emp_id'];
$atten_year=$_POST['atten_year'];
$month_id=$_POST['month_id'];

if($_POST['mode']=="check"){
if($emp_id != '' && $attn_date != '' && $atten_year != '' && $month_id != ''){
    // echo "sdfsdf ";
    $is_exists  = $dbconn->GetSingleReconrd("tbl_attendance","work_date","work_date='".$attn_date."' AND emp_id='".$emp_id."' AND status",1) ;
}
// else{
//     $is_exists  = $dbconn->GetSingleReconrd("tbl_user","usr_id","usr_logname='".$emp_login_name."' AND usr_status",1) ;
// }
    
    
    if( $is_exists > 0)  { 
        echo '1';}
       else{
    echo '0';}  
    
}


?>