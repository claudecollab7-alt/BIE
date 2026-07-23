<?php
ob_start();
session_start();
// isAdmin();
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
$conn = new dbconnect();
$dbconn= new dbhandler();

$code = $_POST['supp_code'];
$supp_id = $_POST['supp_id'];

$code_exist = $dbconn->GetSingleReconrd("mst_supplier","supp_id","supp_id <> ".$supp_id." AND supp_code",$code);
	if($code_exist != ""){
		echo "~1";
	}else{
		echo "~0";
	}

?>