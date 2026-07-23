<?php
ob_start();
session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn= new dbhandler();

$sal_id = $_POST['id'];
$result_sal = "SELECT * FROM mst_salary_setting WHERE sal_id  = '".$sal_id."'";
$result = $conn->query($result_sal);
if ($result->rowCount() > 0)
{
	$obj = $result->fetch(PDO::FETCH_OBJ);
}
if($obj->sal_period == '1')
{
	$period = " Day";
}
elseif($obj->sal_period == '2')
{
	$period = " Month";
}

$basic = round($obj->sal_basic);
$da = round($obj->sal_da);
$hra = round($obj->sal_hra);
$convey = round($obj->sal_convey);
$pf = round($obj->sal_pf);
$salary = round($obj->sal_package_name);

echo $period.'~'.$basic.'~'.$da.'~'.$hra.'~'.$convey.'~'.$pf.'~'.$salary;
?>
 