<?php
ob_start();
session_start();
// isAdmin();
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
$conn = new dbconnect();
$dbconn= new dbhandler();	

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
$string = '';

	 


	/*switch ($_SESSION['company_id'])
	{
		case '1'	:$com ='BIE'; break;
		case '2'	:$com ='FAB'; break;
		case '3'	:$com ='BEN'; break;
		case '4'	:$com ='BMF'; break;
		case '5'	:$com ='PJO'; break;
		case '6'	:$com ='AKD'; break;
	}*/
	// switch ($_POST['company_id'])
	// {
		// case 'BIE'	:$com ='BIE'; break;
		// case 'FAB'	:$com ='FAB'; break;
	// }
	
	switch ($_POST['emp_type'])
	{
		case '1'	:$etype ='S'; $estatus=''; break;
		case '2'	:$etype ='L';  $estatus=''; break;		
		case '3'	:$etype ='O';  $estatus=''; break;	
	}

	switch ($_POST['e_status'])
	{
		case '1'	:$estatus ='T'; break;
		case '2'	:$estatus ='P'; break;	
		default : $estatus ='';	
	}
	

	/*switch ($_POST['labour_status'])
	{
		case '1'	:$estatus ='T'; break;
		case '2'	:$estatus ='P'; break;		

	}*/

	$branch_code = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id='".$_POST['branch_id']."' AND branch_status", 1);
	if(isset($_POST['rec_type']) && $_POST['rec_type']==1)	
	{
		$emp_slno = $dbconn->GetMaxValue('mst_employee','emp_slno','rec_del_status',1)+1;	
	}
	else
	{
		$emp_slno = $_POST['emp_slno'];
	}

	$code = "BIE".'/'.$branch_code.'/'.$etype.$estatus .
	'/'.$emp_slno;
	echo $emp_slno.'~'.$code.'~'.$branch_code;
?>