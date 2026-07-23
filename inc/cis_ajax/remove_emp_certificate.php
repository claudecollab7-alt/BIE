<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

 ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL); 

// header('Content-type: text/json');
// header('Content-type: application/json');
// header('Access-Control-Allow-Origin: *');

$conn = new dbconnect();
$dbconn = new dbhandler();
// $gal_imgs = $_POST['gal_imgs'];
 $no = $_POST['no'];
   $hide_emp_certcopy =$_POST['hide_emp_certcopy'];


if (isset($_POST['hide_emp_certcopy'])) {
     $file = '../../project_img/emp_certificates/'.$hide_emp_certcopy.'';
    if(file_exists($file) == true){
      unlink($file);
    }  
    $conn->query("DELETE FROM mst_employee_certificate WHERE auto_id = $no");
}
?>