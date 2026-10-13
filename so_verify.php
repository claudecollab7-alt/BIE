<?PHP

ob_start();
session_start();
require_once("inc/common/userclass.php");


isAdmin();

$conn = new dbconnect();
$dbconn= new dbhandler();

// 2026-10-13 csrf + check - users holding the Sales Order in Accounts menu, so_id bound
// 2026-10-13 verifier from Task Settings (SALES_RECEIPT V) - was the Sales Order in Accounts menu

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

fnAjaxGuard();
$so_branch = $dbconn->GetSingleReconrd("tbl_sales_order", "bie_branch_id", "so_id", (int)$_POST["dataId"]);
if (!fnTaskCan('SALES_RECEIPT', 'V', $so_branch)) {
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
