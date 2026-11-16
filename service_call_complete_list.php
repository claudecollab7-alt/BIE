<?php
// 2026-11-16 new - complaints of one service call, each completed on its own page (the same flow as Benzear)
ob_start();
session_start();
require_once("inc/common/userclass.php");
require_once("inc/common/service_call.php");

isAdmin();
$conn = new dbconnect();
$dbconn = new dbhandler();

$sc = $conn->query("SELECT * FROM tbl_service_call_register WHERE sc_id = " . (int)(isset($_REQUEST['sc_id']) ? $_REQUEST['sc_id'] : 0))->fetch(PDO::FETCH_OBJ);
if (!$sc) {
	header("location:service_call_list.php");
	die();
}
$complaints = $conn->query("SELECT c.*, e.emp_name FROM tbl_sc_complaints c LEFT JOIN mst_employee e ON e.emp_id = c.service_engineer_id
	WHERE c.sc_id = " . (int)$sc->sc_id . " ORDER BY c.complaint_id")->fetchAll(PDO::FETCH_OBJ);
$actions = array(1 => 'Over Phone Call', 2 => 'On Site Visit', 3 => 'In House Service');
$h = function ($v) {
	return htmlspecialchars((string)$v);
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<title><?php echo PAGE_TITLE; ?> - Complaint List</title>
	<?php include_once("inc/common/css-js.php"); ?>
	<script type="text/javascript">
		$(function() {
			<?php fnScMessages(); ?>
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
							<span class="breadcrumb-item active">Complaints of <?php echo $h($sc->sc_no); ?></span>
						</div>
					</div>
				</div>
			</div>
			<div class="content pt-0">
				<div class="card">
					<div class="card-header bg-pgheader text-white header-elements-inline">
						<h6 class="card-title">Call <?php echo $h($sc->sc_no); ?> - <?php echo $h($sc->customer_name); ?></h6>
						<div class="header-elements"><a class="list-icons-item text-white" href="service_call_list.php" title="List"><i class="icon-arrow-left52"></i></a></div>
					</div>
					<div class="card-body">
						<table class="table table-xs table-bordered mb-3">
							<tr>
								<td width="15%"><b>Date</b></td>
								<td width="35%"><?php echo date('d-m-Y', strtotime($sc->sc_date)); ?></td>
								<td width="15%"><b>Status</b></td>
								<td><?php echo fnScStatusLabel($sc); ?>
									<?php if ($sc->src_system == 'BENZEAR') { ?><span class="badge badge-flat border-primary text-primary ml-2">From Benzear <?php echo $h($sc->src_sc_no); ?></span><?php } ?></td>
							</tr>
							<tr>
								<td><b>Contact</b></td>
								<td><?php echo $h($sc->prefix . ' ' . $sc->contact_person); ?>, <?php echo $h($sc->mobile_no); ?></td>
								<td><b>Address</b></td>
								<td><?php echo $h($sc->customer_address); ?></td>
							</tr>
							<tr>
								<td><b>Nature of Complaint</b></td>
								<td colspan="3"><?php echo nl2br($h($sc->nature_of_complaint)); ?></td>
							</tr>
						</table>
						<table class="table table-xs table-bordered">
							<thead>
								<tr class="bg-teal">
									<th width="4%">#</th>
									<th>Complaint</th>
									<th>Action</th>
									<th>Engineer</th>
									<th>Solving Date</th>
									<th>Status</th>
									<th width="10%" class="text-center">Action</th>
								</tr>
							</thead>
							<tbody>
								<?php $i = 1;
								foreach ($complaints as $c) {
									$done = $c->complaint_status == 1; ?>
									<tr>
										<td><?php echo $i++; ?></td>
										<td><?php echo nl2br($h($c->complaint_desc)); ?></td>
										<td><?php echo $done && isset($actions[$c->action_taken]) ? $actions[$c->action_taken] : ''; ?></td>
										<td><?php echo $done ? $h($c->emp_name) : ''; ?></td>
										<td><?php echo $done && $c->solving_date ? date('d-m-Y', strtotime($c->solving_date)) : ''; ?></td>
										<td><?php echo $done ? '<span class="badge badge-success">Completed</span>' : '<span class="badge badge-warning">Open</span>'; ?></td>
										<td class="text-center" style="white-space:nowrap;">
											<?php if ($sc->sc_verify_status == 0) { ?>
												<a href="service_call_complete.php?sc_id=<?php echo $sc->sc_id; ?>&complaint_id=<?php echo $c->complaint_id; ?>" class="list-icons-item mr-2" title="<?php echo $done ? 'Edit Completion' : 'Complete'; ?>"><i class="<?php echo $done ? 'icon-pencil7' : 'icon-checkmark-circle'; ?>"></i></a>
											<?php } ?>
											<?php if ($done) { ?>
												<a href="service_call_complaint_print.php?sc_id=<?php echo $sc->sc_id; ?>&complaint_id=<?php echo $c->complaint_id; ?>" class="list-icons-item" title="Print"><i class="icon-printer"></i></a>
											<?php } ?>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
					<div class="card-footer text-center">
						<a href="service_call_print.php?sc_id=<?php echo $sc->sc_id; ?>" class="btn btn-info">Call Print / Approval</a>
						<a href="service_call_list.php" class="btn btn-light">Back</a>
					</div>
				</div>
			</div>
			<?php include("inc/common/footer.php") ?>
		</div>
	</div>
</body>

</html>
