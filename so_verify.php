<?PHP

ob_start();
session_start();
require_once("inc/common/userclass.php");


isAdmin();

$conn = new dbconnect();
$dbconn= new dbhandler();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$task = $_POST["task"];

if ($task == 'SO') {
	$status_update = $conn->prepare("UPDATE tbl_sales_order SET pay_status = 2,so_verify_status=1,so_user_approve_by='E'  WHERE so_id = '".$_POST['dataId']."'");
	$status_update->execute();
	// exit;
	header("location:lst_sales_order.php");
	die();
}

?>
