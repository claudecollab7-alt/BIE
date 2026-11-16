<?php
// 2026-11-16 new - service call print, with send for approval / approve / revoke (Task Settings SERVICE_CALL) - same flow as Benzear
ob_start();
session_start();
require_once("inc/common/userclass.php");
require_once("inc/common/service_call.php");

isAdmin();
$conn = new dbconnect();
$dbconn = new dbhandler();

$sc_id = (int)(isset($_REQUEST['sc_id']) ? $_REQUEST['sc_id'] : 0);
$sc = $conn->query("SELECT * FROM tbl_service_call_register WHERE sc_id = " . $sc_id)->fetch(PDO::FETCH_OBJ);
if (!$sc) {
	header("location:service_call_list.php");
	die();
}
$h = function ($v) {
	return htmlspecialchars((string)$v);
};
$name = function ($table, $field, $key, $id) use ($dbconn) {
	return $id > 0 ? trim($dbconn->GetSingleReconrd($table, $field, $key, (int)$id)) : '';
};
$state = $name('mst_state', 'state_name', 'state_id', $sc->state_id);
$address = implode(', ', array_filter(array($sc->enq_add1, $sc->enq_add2, $name('mst_city', 'city_name', 'city_id', $sc->city_id),
	$name('mst_district', 'district_name', 'district_id', $sc->district_id), $state, $sc->enq_pincode)));
$types = fnScChargeTypes();
$actions = array(1 => 'Over Phone Call', 2 => 'On Site Visit', 3 => 'In House Service');
$complaints = $conn->query("SELECT c.*, e.emp_name FROM tbl_sc_complaints c LEFT JOIN mst_employee e ON e.emp_id = c.service_engineer_id
	WHERE c.sc_id = " . $sc_id . " ORDER BY c.complaint_id")->fetchAll(PDO::FETCH_OBJ);
$invoice = $sc->invoice_id > 0 ? $name('tbl_invoice', 'inv_refno', 'inv_id', $sc->invoice_id) : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<title><?php echo PAGE_TITLE; ?> - Service Call <?php echo $h($sc->sc_no); ?></title>
	<?php include_once("inc/common/css-js.php"); ?>
	<script type="text/javascript" src="print_me.js"></script>
	<style>
		.sc-print td, .sc-print th { border: 1px solid #ccc; padding: 4px 6px; font-size: 12px; vertical-align: top; }
		.sc-band { background: #e8e8e8; font-weight: bold; text-align: center; }
		.sc-label { width: 18%; font-weight: bold; }
	</style>
	<script type="text/javascript">
		$(function() {
			<?php fnScMessages(); ?>
			$('.sc_step').on('click', function(e) {
				e.preventDefault();
				var mode = $(this).data('mode');
				if (!confirm(mode == 'verify' ? 'Send this service call for approval?' : 'Approve this service call?')) {
					return;
				}
				$.post('inc/cis_ajax/jquery_sc_approval_status.php', { id: $(this).data('id'), mode: mode })
					.done(function() { location.reload(); })
					.fail(function(x) { alert(x.status == 403 ? 'You are not allowed to do this..!' : ($.trim(x.responseText) || 'Not saved - please try again..!')); });
			});
		});
	</script>
</head>

<body>
	<?php include("inc/common/header.php") ?>
	<div class="page-content">
		<?php include("inc/common/sidebar.php") ?>
		<div class="content-wrapper">
			<div class="page-header">
				<div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
					<div class="d-flex">
						<div class="breadcrumb">
							<a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Home</a>
							<a href="service_call_list.php" class="breadcrumb-item">Service Call Register</a>
							<span class="breadcrumb-item active"><?php echo $h($sc->sc_no); ?></span>
						</div>
					</div>
				</div>
			</div>
			<div class="content pt-0">
				<div class="card">
					<div class="card-header bg-pgheader text-white header-elements-inline">
						<h6 class="card-title">Service Call - <?php echo $h($sc->sc_no); ?></h6>
						<div class="header-elements">
							<div class="list-icons">
								<a class="list-icons-item" href="service_call_list.php" title="List"><i class="icon-arrow-left52 mr-2"></i></a>
								<?php if ($sc->sc_verify_status == 0) { ?>
									<a class="list-icons-item" href="service_call_register.php?sc_id=<?php echo $sc_id; ?>" title="Edit"><i class="icon-pencil7 mr-2"></i></a>
								<?php } ?>
								<a class="list-icons-item" href="service_call_complete_list.php?sc_id=<?php echo $sc_id; ?>" title="Complaints"><i class="icon-checkmark-circle mr-2"></i></a>
								<a class="list-icons-item" href="javascript:PrintPartsNew(new Array('print_content'),'SC-<?php echo $h($sc->sc_no); ?>');" title="Print"><i class="icon-printer2"></i></a>
							</div>
						</div>
					</div>
					<div class="card-body">
						<div id="print_content">
							<table class="sc-print" width="100%">
								<tr>
									<td colspan="4" style="border:none;">
										<span style="font-size:16px;font-weight:bold;">SERVICE CALL REGISTER</span>
										<span style="float:right;"><img src="img/BIE_logo.png" alt="" width="50px"></span>
									</td>
								</tr>
								<tr>
									<td class="sc-label">Service Call No.</td>
									<td><b style="color:blue;"><?php echo $h($sc->sc_no); ?></b>
										<?php if ($sc->src_system == 'BENZEAR') { ?><br><small>Transferred from Benzear: <?php echo $h($sc->src_sc_no); ?> (<?php echo $h($sc->src_transfer_by); ?>, <?php echo $sc->src_transfer_dtm ? date('d-m-Y H:i', strtotime($sc->src_transfer_dtm)) : ''; ?>)</small><?php } ?></td>
									<td class="sc-label">Date</td>
									<td><?php echo date('d-m-Y', strtotime($sc->sc_date)); ?></td>
								</tr>
								<tr>
									<td class="sc-label">Call Register No.</td>
									<td><?php echo $h($sc->sc_ref_no); ?></td>
									<td class="sc-label">Status</td>
									<td><?php echo fnScStatusLabel($sc); ?></td>
								</tr>
								<tr>
									<td class="sc-label">Financial Year</td>
									<td><?php echo $h($sc->sc_finyr); ?></td>
									<td class="sc-label">State</td>
									<td><?php echo $h($state); ?></td>
								</tr>
								<tr><td colspan="4" class="sc-band">CUSTOMER DETAILS</td></tr>
								<tr>
									<td class="sc-label">Customer Name</td>
									<td><b><?php echo $h($sc->customer_name); ?></b></td>
									<td class="sc-label">Contact Person</td>
									<td><?php echo $h($sc->prefix . ' ' . $sc->contact_person); ?><?php echo $sc->designation != '' ? ' (' . $h($sc->designation) . ')' : ''; ?></td>
								</tr>
								<tr>
									<td class="sc-label">Mobile No.</td>
									<td><?php echo $h($sc->mobile_no); ?></td>
									<td class="sc-label">Tel No.</td>
									<td><?php echo $h($sc->tel_no); ?></td>
								</tr>
								<tr>
									<td class="sc-label">Email</td>
									<td><?php echo $h($sc->email); ?></td>
									<td class="sc-label">GST No.</td>
									<td><?php echo $h($sc->enq_gst); ?></td>
								</tr>
								<tr>
									<td class="sc-label">Address</td>
									<td colspan="3"><?php echo $h($address); ?></td>
								</tr>
								<tr><td colspan="4" class="sc-band">SERVICE DETAILS</td></tr>
								<tr>
									<td class="sc-label">Auth. Service Center</td>
									<td><?php echo $h($name('mst_principal', 'principal_name', 'principal_id', $sc->principal_id)); ?></td>
									<td class="sc-label">Service Charge</td>
									<td><?php echo isset($types[$sc->service_charge_type]) ? $types[$sc->service_charge_type] : ''; ?><?php echo $invoice != '' ? ' - Invoice ' . $h($invoice) : ''; ?></td>
								</tr>
								<tr>
									<td class="sc-label">Chargeable Amount</td>
									<td colspan="3"><?php echo $sc->received_amount > 0 ? number_format($sc->received_amount, 2) : '-'; ?></td>
								</tr>
								<tr>
									<td class="sc-label">Nature of Complaint</td>
									<td colspan="3"><?php echo nl2br($h($sc->nature_of_complaint)); ?></td>
								</tr>
								<tr><td colspan="4" class="sc-band">COMPLAINT DETAILS</td></tr>
								<tr>
									<td colspan="4" style="padding:0;border:none;">
										<table class="sc-print" width="100%">
											<tr style="background:#f3f3f3;font-weight:bold;">
												<td width="4%">#</td>
												<td>Complaint</td>
												<td width="14%">Action</td>
												<td width="14%">Engineer</td>
												<td width="11%">Solving Date</td>
												<td width="10%">Status</td>
											</tr>
											<?php $i = 1;
											foreach ($complaints as $c) { ?>
												<tr>
													<td><?php echo $i++; ?></td>
													<td><?php echo $h($c->complaint_desc); ?><?php echo $c->action_remarks != '' ? '<br><small><i>Remarks: ' . $h($c->action_remarks) . '</i></small>' : ''; ?></td>
													<td><?php echo $c->complaint_status == 1 && isset($actions[$c->action_taken]) ? $actions[$c->action_taken] : '-'; ?></td>
													<td><?php echo $h($c->emp_name ? $c->emp_name : '-'); ?></td>
													<td><?php echo $c->solving_date ? date('d-m-Y', strtotime($c->solving_date)) : '-'; ?></td>
													<td><?php echo $c->complaint_status == 1 ? 'Completed' : 'Open'; ?></td>
												</tr>
											<?php } ?>
										</table>
									</td>
								</tr>
								<tr>
									<td colspan="2" class="text-center" style="padding-top:30px;">
										<p>Created By</p>
										<?php echo $h($dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id", fnCreatedUser($sc))) . fnCreatedOn($sc, '<br/> On ', 'd-m-Y'); ?>
									</td>
									<td class="text-center" style="padding-top:30px;">
										<?php if ($sc->sc_verify_status == 1) {
											echo '<p>Sent for Approval By</p>' . $h($dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id", $sc->sc_verify_by)) . '<br/> On ' . date('d-m-Y', strtotime($sc->sc_verify_date_time));
											if ($sc->sc_approve_status == 0) {
												echo '<br/>' . fnRevokeButton('SC_V', $sc->sc_id, $sc->branch_id, 'Revoke', 'badge badge-danger d-print-none');
											}
										} elseif ($sc->sc_status == 2 && fnTaskCan('SERVICE_CALL', 'V', $sc->branch_id)) { ?>
											<a href="javascript:;" class="sc_step d-print-none" data-mode="verify" data-id="<?php echo $sc->sc_id; ?>"><span class="badge badge-warning">Send for Approval</span></a>
										<?php } elseif ($sc->sc_status == 1) {
											echo '<span class="text-muted d-print-none">Complete every complaint to send for approval</span>';
										} ?>
									</td>
									<td class="text-center" style="padding-top:30px;">
										<?php if ($sc->sc_approve_status == 1) {
											echo '<p>Approved By</p>' . $h($dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id", $sc->sc_approve_by)) . '<br/> On ' . date('d-m-Y', strtotime($sc->sc_approve_date_time));
											echo '<br/>' . fnRevokeButton('SC_A', $sc->sc_id, $sc->branch_id, 'Revoke', 'badge badge-danger d-print-none');
										} elseif ($sc->sc_verify_status == 1 && fnTaskCan('SERVICE_CALL', 'A', $sc->branch_id)) { ?>
											<a href="javascript:;" class="sc_step d-print-none" data-mode="approve" data-id="<?php echo $sc->sc_id; ?>"><span class="badge badge-warning">Approve Now</span></a>
										<?php } else {
											echo '&nbsp;';
										} ?>
									</td>
								</tr>
							</table>
							<?php echo fnRevokeNote($conn, 'SC_A', $sc->sc_id) . fnRevokeNote($conn, 'SC_V', $sc->sc_id); ?>
						</div>
					</div>
				</div>
			</div>
			<?php include("inc/common/footer.php") ?>
		</div>
	</div>
</body>

</html>
