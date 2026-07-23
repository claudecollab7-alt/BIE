<?php
ob_start();
session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn= new dbhandler();

/*ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
*/


	$supp_id = $_POST['supp_id'];

	$SQL = "SELECT supp_id, supp_name, supp_add2,supp_email,supp_mobile1 FROM mst_supplier_new
	WHERE  supp_id=".$supp_id;
	$result = $conn->query($SQL);

	//echo $SQL;

	if ($result->rowCount() > 0)
	{
		$obj1 = $result->fetch(PDO::FETCH_OBJ);
		
		echo trim($obj1->supp_email."~".$obj1->supp_mobile1);
				
	}

?>

