<?php
// 2026-11-16 new - print of one completed complaint of a service call (the same flow as Benzear)
ob_start();
session_start();
require_once("inc/common/userclass.php");
require_once("inc/common/service_call.php");

isAdmin();
$conn = new dbconnect();
$dbconn = new dbhandler();

$complaint_id = (int)(isset($_REQUEST['complaint_id']) ? $_REQUEST['complaint_id'] : 0);
$c = $conn->query("SELECT c.*, e.emp_name, u.usr_name AS completed_by FROM tbl_sc_complaints c LEFT JOIN mst_employee e ON e.emp_id = c.service_engineer_id
	LEFT JOIN tbl_user u ON u.usr_id = c.complete_by WHERE c.complaint_id = " . $complaint_id)->fetch(PDO::FETCH_OBJ);
$sc = $c ? $conn->query("SELECT * FROM tbl_service_call_register WHERE sc_id = " . (int)$c->sc_id)->fetch(PDO::FETCH_OBJ) : false;
if (!$sc) {
	header("location:service_call_list.php");
	die();
}
$h = function ($v) {
	return htmlspecialchars((string)$v);
};
$types = fnScChargeTypes();
$actions = array(1 => 'Over Phone Call', 2 => 'On Site Visit', 3 => 'In House Service');
$spares = $conn->query("SELECT d.*, i.item_code, i.item_desciption FROM tbl_sc_complaint_details d LEFT JOIN tbl_item_details i ON i.item_id = d.item_id
	WHERE d.complaint_id = " . $complaint_id . " ORDER BY d.auto_id")->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<title><?php echo PAGE_TITLE; ?> - Complaint <?php echo $h($sc->sc_no); ?></title>
	<?php include_once("inc/common/css-js.php"); ?>
	<script type="text/javascript" src="print_me.js"></script>
	<style>
		.sc-print td, .sc-print th { border: 1px solid #ccc; padding: 4px 6px; font-size: 12px; vertical-align: top; }
		.sc-band { background: #e8e8e8; font-weight: bold; text-align: center; }
		.sc-label { width: 18%; font-weight: bold; }
	</style>
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
							<a href="service_call_complete_list.php?sc_id=<?php echo $sc->sc_id; ?>" class="breadcrumb-item"><?php echo $h($sc->sc_no); ?></a>
							<span class="breadcrumb-item active">Complaint Print</span>
						</div>
					</div>
				</div>
			</div>
			<div class="content pt-0">
				<div class="card">
					<div class="card-header bg-pgheader text-white header-elements-inline">
						<h6 class="card-title">Complaint - <?php echo $h($sc->sc_no); ?></h6>
						<div class="header-elements">
							<div class="list-icons">
								<a class="list-icons-item" href="service_call_complete_list.php?sc_id=<?php echo $sc->sc_id; ?>" title="Complaints"><i class="icon-arrow-left52 mr-2"></i></a>
								<a class="list-icons-item" href="javascript:PrintPartsNew(new Array('print_content'),'SC-<?php echo $h($sc->sc_no . '-' . $complaint_id); ?>');" title="Print"><i class="icon-printer2"></i></a>
							</div>
						</div>
					</div>
					<div class="card-body">
						<div id="print_content">
							<table class="sc-print" width="100%">
								<tr>
									<td colspan="4" style="border:none;">
										<span style="font-size:16px;font-weight:bold;">SERVICE REPORT</span>
										<span style="float:right;"><img src="img/BIE_logo.png" alt="" width="50px"></span>
									</td>
								</tr>
								<tr>
									<td class="sc-label">Service Call No.</td>
									<td><b style="color:blue;"><?php echo $h($sc->sc_no); ?></b></td>
									<td class="sc-label">Call Date</td>
									<td><?php echo date('d-m-Y', strtotime($sc->sc_date)); ?></td>
								</tr>
								<tr><td colspan="4" class="sc-band">CUSTOMER DETAILS</td></tr>
								<tr>
									<td class="sc-label">Customer Name</td>
									<td><b><?php echo $h($sc->customer_name); ?></b></td>
									<td class="sc-label">Contact Person</td>
									<td><?php echo $h($sc->prefix . ' ' . $sc->contact_person); ?></td>
								</tr>
								<tr>
									<td class="sc-label">Mobile No.</td>
									<td><?php echo $h($sc->mobile_no); ?></td>
									<td class="sc-label">Email</td>
									<td><?php echo $h($sc->email); ?></td>
								</tr>
								<tr>
									<td class="sc-label">Address</td>
									<td colspan="3"><?php echo $h($sc->customer_address); ?></td>
								</tr>
								<tr><td colspan="4" class="sc-band">COMPLAINT DETAILS</td></tr>
								<tr>
									<td class="sc-label">Complaint</td>
									<td colspan="3"><?php echo nl2br($h($c->complaint_desc)); ?></td>
								</tr>
								<tr>
									<td class="sc-label">Nature of Complaint</td>
									<td colspan="3"><?php echo nl2br($h($sc->nature_of_complaint)); ?></td>
								</tr>
								<tr>
									<td class="sc-label">Service Charge</td>
									<td><?php echo isset($types[$sc->service_charge_type]) ? $types[$sc->service_charge_type] : ''; ?></td>
									<td class="sc-label">Status</td>
									<td><?php echo $c->complaint_status == 1 ? 'Completed' : 'Open'; ?></td>
								</tr>
								<tr><td colspan="4" class="sc-band">COMPLETION DETAILS</td></tr>
								<tr>
									<td class="sc-label">Action Type</td>
									<td><?php echo isset($actions[$c->action_taken]) ? $actions[$c->action_taken] : '-'; ?></td>
									<td class="sc-label">Solving Date</td>
									<td><?php echo $c->solving_date ? date('d-m-Y', strtotime($c->solving_date)) : '-'; ?></td>
								</tr>
								<tr>
									<td class="sc-label">Service Engineer</td>
									<td><?php echo $h($c->emp_name ? $c->emp_name : '-'); ?></td>
									<td class="sc-label">Completed On</td>
									<td><?php echo $c->complete_date_time ? date('d-m-Y H:i', strtotime($c->complete_date_time)) . ' / ' . $h($c->completed_by) : '-'; ?></td>
								</tr>
								<tr>
									<td class="sc-label">Chart No</td>
									<td><?php echo $h($c->chart_no); ?></td>
									<td class="sc-label">Received Amount</td>
									<td><?php echo $c->total_received_amount > 0 ? number_format($c->total_received_amount, 2) : '-'; ?></td>
								</tr>
								<?php if ($c->action_taken == 2) { ?>
									<tr>
										<td class="sc-label">Engineer Expenses</td>
										<td><?php echo $c->engineer_expenses > 0 ? number_format($c->engineer_expenses, 2) : '-'; ?></td>
										<td class="sc-label">No. of Visits</td>
										<td><?php echo (int)$c->no_of_visits; ?></td>
									</tr>
								<?php } ?>
								<tr>
									<td class="sc-label">Action Remarks</td>
									<td colspan="3"><?php echo nl2br($h($c->action_remarks)); ?></td>
								</tr>
								<tr>
									<td class="sc-label">Customer Comments</td>
									<td colspan="3"><?php echo nl2br($h($c->customer_comments)); ?></td>
								</tr>
								<?php if (count($spares) > 0) { ?>
									<tr><td colspan="4" class="sc-band">SPARE PARTS USED</td></tr>
									<tr>
										<td colspan="4" style="padding:0;border:none;">
											<table class="sc-print" width="100%">
												<tr style="background:#f3f3f3;font-weight:bold;">
													<td width="5%">#</td>
													<td width="18%">Item Code</td>
													<td>Description</td>
													<td width="10%" class="text-right">Qty</td>
													<td width="10%">Unit</td>
												</tr>
												<?php $i = 1;
												foreach ($spares as $s) { ?>
													<tr>
														<td><?php echo $i++; ?></td>
														<td><?php echo $h($s->item_code); ?></td>
														<td><?php echo $h($s->item_desciption); ?></td>
														<td class="text-right"><?php echo fnQty($s->sc_qty); ?></td>
														<td><?php echo $h($s->sc_unit); ?></td>
													</tr>
												<?php } ?>
											</table>
										</td>
									</tr>
								<?php } ?>
								<tr>
									<td colspan="2" class="text-center" style="padding-top:50px;">Customer Signature</td>
									<td colspan="2" class="text-center" style="padding-top:50px;">Service Engineer</td>
								</tr>
							</table>
							<?php if ($c->sc_review != '') { ?><p class="d-print-none mt-2">Customer review copy: <a href="project_img/sc_review/<?php echo rawurlencode($c->sc_review); ?>" target="_blank"><?php echo $h($c->sc_review); ?></a></p><?php } ?>
						</div>
					</div>
				</div>
			</div>
			<?php include("inc/common/footer.php") ?>
		</div>
	</div>
</body>

</html>
