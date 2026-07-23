<!-- Basic modal -->
<?PHP

ob_start();
// session_start();

require_once("inc/common/dbconnect.php");
require_once("inc/common/functions.php");
require_once("inc/common/dbhandler.php");

$conn = new dbconnect();
$dbconn= new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

if (isset($_POST['CONFIRM']))
{
	$so_date = $dbconn->GetSingleReconrd("tbl_sales_order","so_date","so_id",$_REQUEST['so_id']);
	$_REQUEST['so_credit_due_date'] = date('Y-m-d', strtotime($so_date. ' + '.$_REQUEST['so_credit_days'].' days')); 
	$stmt = null;				
	$stmt = $conn->prepare("UPDATE tbl_sales_order SET so_credit_approval_remarks = :so_credit_approval_remarks, so_credit_days = :so_credit_days, so_credit_due_date = :so_credit_due_date, so_approve_status = :so_approve_status, so_approve_by = :so_approve_by, pay_status = :pay_status ,so_approve_date_time = :so_approve_date_time WHERE so_id = :so_id");
	$data = array(
		'so_id'=>$_REQUEST['so_id'],
		':so_credit_days' => $_REQUEST['so_credit_days'],
		':so_credit_due_date' => $_REQUEST['so_credit_due_date'],
		':so_credit_approval_remarks' => $_REQUEST['so_credit_approval_remarks'],
		':so_approve_status' => '3',
		':so_approve_by' =>'1',
		':pay_status' =>'4',
		':so_approve_date_time' => date('Y-m-d H:i:s')
	);
	$stmt->execute($data);
	header("location:lst_sales_receipt.php");	
	die();
	// print_r($data);die();
	}
?>

<div id="modalSoDets" class="modal fade" tabindex="-1">
	<div class="modal-dialog   modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header pb-2 pt-2 bg-modal">
				<h6 class="modal-title" style="width: 700px;">
				</span><span id="m_sales_code1" class="font-weight-bold"></span>
				</h6>
				<!-- <h6 class="modal-title font-weight-bold"><span id="m_sales_rec"></span> - <span id="inv_netamt" class="font-weight-bold"></span></h6> -->
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<form action="modal_so_det.php" method="POST">
			<div class="modal-body py-0" id="m_sales_id">
				<div class="col-md-6 pt-5 pb-5 text-center">
					<span id="spinner-light" class="text-loading">
						<i class="icon-spinner spinner mr-2 "></i>
						Loading ...
					</span>
				</div>
			</div>


			<!-- <div class="modal-body py-0" id="m_sales_name">
				<div class="col-md-6 pt-5 pb-5 text-center">
					<span id="spinner-light" class="text-loading">
						<i class="icon-spinner spinner mr-2 "></i>
						Loading ...
					</span>
				</div>
			</div> -->

					<div class="container">
					<div style="text-align: center"><hr>
	
						<INPUT class="btn btn-success" type="submit" name="CONFIRM" value="Approve Credit Sales">
						<INPUT class="btn btn-light mr-2" type="button" name="cancel" value="Close" onClick="javascript:window.location.href='lst_so_approval.php'">
					
					</div>
				</div><br>
			</form>

			<div class="modal-footer pt-0 pb-2 bg-modal">
				
			</div>
			
		</div>
	</div>
</div>
<!-- /basic modal -->