<!-- Basic modal -->
<?PHP

ob_start();
// session_start();

require_once("inc/common/dbconnect.php");
require_once("inc/common/functions.php");
require_once("inc/common/dbhandler.php");

$conn = new dbconnect();
$dbconn= new dbhandler();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (isset($_POST['CONFIRM']))
{
	$so_date = $dbconn->GetSingleReconrd("tbl_grn","grn_date","grn_id",$_REQUEST['grn_id']);
	// $_REQUEST['so_credit_due_date'] = date('Y-m-d', strtotime($so_date. ' + '.$_REQUEST['so_credit_days'].' days')); 
	$stmt = null;				
	$stmt = $conn->prepare("UPDATE tbl_grn SET grn_cancel_by = :grn_cancel_by, grn_cancel_date_time = :grn_cancel_date_time, grn_cancel_reason = :grn_cancel_reason, grn_status = :grn_status, 
	grn_approve_status = :grn_approve_status, grn_cancel_status = :grn_cancel_status WHERE grn_id = :grn_id");
	$data = array(
		'grn_id'=>$_REQUEST['grn_id'],
		':grn_cancel_by' => '1',
		':grn_cancel_date_time' => date('Y-m-d H:i:s'),
		':grn_cancel_reason' => $_REQUEST['grn_cancel_reason'],
		':grn_status' => '3',
		':grn_approve_status' => '0',
		':grn_cancel_status' => '1'
	
	);
   
	$stmt->execute($data);
    // .

// print_r($data);die();
	header("location:grn_list.php");	
	die();
	
	}
?>
<!-- Basic modal -->

<div id="modalRejectGrnDets" class="modal fade" tabindex="-1" data-backdrop="false">
		<div class="modal-dialog   modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header pb-2 pt-2 bg-modal">
					<h6 class="modal-title" style="width: 800px;">
	                <span id="m_sales_rej_rec" class="font-weight-bold"></span></h6>
					<!-- <h6 class="modal-title font-weight-bold"><span id="m_sales_rec"></span> - <span id="inv_netamt" class="font-weight-bold"></span></h6> -->
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
            <form action="modal_grn_reject_dets.php" method="POST">
                <div class="modal-body py-0" id="m_sales_rej_code">				
                    <div class="col-md-6 pt-5 pb-5 text-center">
                        <span id="spinner-light" class="text-loading">
                            <i class="icon-spinner spinner mr-2 "></i>
                                Loading ...
                        </span>
                    </div>
                </div>
                <div class="container">
					<div style="text-align: center"><hr>
	
						<INPUT class="btn btn-danger" type="submit" name="CONFIRM" value="Cancelled">
						<INPUT class="btn btn-light mr-2" type="button" name="cancel" value="Close" onClick="javascript:window.location.href='grn_list.php'">
					
					</div>
				</div><br>
			</form>
			
			<div class="modal-footer pt-0 pb-2 bg-modal">				
				<!--button type="button" class="btn btn-light" data-dismiss="modal">Close</button-->				
			</div>
		</div>
	</div>
		</div>			
<!-- /basic modal -->