<?php
ob_start();
session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn= new dbhandler();

$emp_id = $_POST["id"];
$emp_ctc = $_POST["ctc"];
$sal_id = $_POST['sal_id'];

$result_sal = "SELECT * FROM mst_salary_setting WHERE sal_id  = '".$sal_id."'";
$result = $conn->query($result_sal);
if ($result->rowCount() > 0)
{
	$obj = $result->fetch(PDO::FETCH_OBJ);
	// print_r($obj);	
}

$salary = $dbconn->GetSingleReconrd("mst_employee","emp_ctc","emp_id",$emp_id);
if($salary>0){
	$salary = $salary;
}else{
	$salary = $obj->sal_package_name;
}
$basic = round($emp_ctc*$obj->sal_basic/100);
$da = round($emp_ctc*$obj->sal_da/100);
$sum = round($basic+$da);
$hra = round($emp_ctc*$obj->sal_hra/100); 	
$convey = round($emp_ctc*$obj->sal_convey/100);
$pf = round($sum*$obj->sal_pf/100);
$total = round($emp_ctc-$pf);
if($obj->sal_cca !="")
{
	$cca = $obj->sal_cca;
	$cca_total = round($total+$obj->sal_cca);
}
echo $basic.'~'.$da.'~'.$sum.'~'.$hra.'~'.$convey.'~'.$pf.'~'.$total.'~'.$cca.'~'.$cca_total;

?>
