<?PHP

ob_start();
session_start();
require_once("inc/common/userclass.php");


isAdmin();

$conn = new dbconnect();
$dbconn= new dbhandler();

// 2026-10-13 csrf + check - users holding the Sales Order in Accounts menu, so_id bound

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

fnAjaxGuard();
if (!fnUserHasMenu($conn, 'lst_sales_receipt.php')) {
	fnAccessDenied(true);
}

$task = isset($_POST["task"]) ? $_POST["task"] : '';

if ($task == 'SO') {
	$status_update = $conn->prepare("UPDATE tbl_sales_order SET pay_status = 2,so_verify_status=1,so_user_approve_by='E'  WHERE so_id = :so_id");
	$status_update->execute(array(':so_id' => $_POST['dataId']));
	// exit;
	header("location:lst_sales_order.php");
	die();
}

?>
