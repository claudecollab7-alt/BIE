<?php
// 2026-11-16 new - service call report (the same filters as Benzear): one row per complaint - action, engineer, solving
// date and days are kept per complaint
ob_start();
session_start();
require_once("inc/common/userclass.php");
require_once("inc/common/service_call.php");

isAdmin();
$conn = new dbconnect();
$dbconn = new dbhandler();

$from_date = !empty($_REQUEST['from_date']) ? date('Y-m-d', strtotime($_REQUEST['from_date'])) : date('Y-m-01');
$to_date = !empty($_REQUEST['to_date']) ? date('Y-m-d', strtotime($_REQUEST['to_date'])) : date('Y-m-d');
$f_status = isset($_REQUEST['sch_status']) ? $_REQUEST['sch_status'] : '';
$f_action = (int)(isset($_REQUEST['sch_action']) ? $_REQUEST['sch_action'] : 0);
$f_state = (int)(isset($_REQUEST['sch_state']) ? $_REQUEST['sch_state'] : 0);
$f_engineer = (int)(isset($_REQUEST['sch_engineer']) ? $_REQUEST['sch_engineer'] : 0);
$f_from = isset($_REQUEST['sch_from']) ? $_REQUEST['sch_from'] : '';
$keyword = trim(isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : '');

$sql = "SELECT sc.*, c.complaint_id, c.complaint_desc, c.complaint_status, c.action_taken, c.solving_date, c.total_received_amount, e.emp_name, s.state_name
	FROM tbl_service_call_register sc
	JOIN tbl_sc_complaints c ON c.sc_id = sc.sc_id
	LEFT JOIN mst_employee e ON e.emp_id = c.service_engineer_id
	LEFT JOIN mst_state s ON s.state_id = sc.state_id
	WHERE sc.sc_date BETWEEN :f AND :t";
$p = array(':f' => $from_date, ':t' => $to_date);
if ($f_status == 'open') {
	$sql .= " AND c.complaint_status = 0";
} elseif ($f_status == 'done') {
	$sql .= " AND c.complaint_status = 1";
} elseif ($f_status == 'approved') {
	$sql .= " AND sc.sc_approve_status = 1";
}
if ($f_action > 0) {
	$sql .= " AND c.complaint_status = 1 AND c.action_taken = " . $f_action;
}
if ($f_state > 0) {
	$sql .= " AND sc.state_id = " . $f_state;
}
if ($f_engineer > 0) {
	$sql .= " AND c.service_engineer_id = " . $f_engineer;
}
if ($f_from == 'bie') {
	$sql .= " AND sc.src_system = ''";
} elseif ($f_from == 'benzear') {
	$sql .= " AND sc.src_system = 'BENZEAR'";
}
if ($keyword != '') {
	$sql .= " AND (sc.sc_no LIKE :k OR sc.customer_name LIKE :k OR sc.mobile_no LIKE :k OR c.complaint_desc LIKE :k OR sc.src_sc_no LIKE :k)";
	$p[':k'] = '%' . $keyword . '%';
}
$st = $conn->prepare($sql . " ORDER BY sc.sc_date, sc.sc_id, c.complaint_id");
$st->execute($p);
$rows = $st->fetchAll(PDO::FETCH_OBJ);
$types = fnScChargeTypes();
$actions = array(1 => 'Over Phone', 2 => 'On Site', 3 => 'In House');
$h = function ($v) {
	return htmlspecialchars((string)$v);
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<title><?php echo PAGE_TITLE; ?> - Service Call Report</title>
	<?php include_once("inc/common/css-js.php"); ?>
	<script src="js/jquery.table2excel.min.js"></script>
	<script type="text/javascript">
		$(function() {
			$('.rpt_export').click(function() {
				$('#sc_rept').table2excel({ name: 'Service Calls', filename: 'ServiceCalls' + new Date().toISOString().replace(/[\-\:\.]/g, '') + '.xls', fileext: '.xls' });
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
							<a href="#" class="breadcrumb-item">Reports</a>
							<span class="breadcrumb-item active">Service Call Report</span>
						</div>
					</div>
				</div>
			</div>
			<div class="content pt-0">
				<div class="card">
					<div class="card-header bg-pgheader text-white header-elements-inline">
						<h6 class="card-title">Service Call Report</h6>
					</div>
					<div class="card-body pt-3">
						<form method="get" class="mb-3">
							<div class="form-row">
								<div class="col-md-2"><label>From</label><input type="date" name="from_date" class="form-control form-control-sm" value="<?php echo $from_date; ?>"></div>
								<div class="col-md-2"><label>To</label><input type="date" name="to_date" class="form-control form-control-sm" value="<?php echo $to_date; ?>"></div>
								<div class="col-md-2"><label>Status</label>
									<select name="sch_status" class="form-control form-control-sm">
										<?php foreach (array('' => 'All', 'open' => 'Open', 'done' => 'Completed', 'approved' => 'Approved') as $k => $v) {
											echo '<option value="' . $k . '" ' . ($f_status === (string)$k ? 'selected' : '') . '>' . $v . '</option>';
										} ?>
									</select>
								</div>
								<div class="col-md-2"><label>Action</label>
									<select name="sch_action" class="form-control form-control-sm">
										<option value="0">All</option>
										<?php foreach ($actions as $k => $v) {
											echo '<option value="' . $k . '" ' . ($f_action == $k ? 'selected' : '') . '>' . $v . '</option>';
										} ?>
									</select>
								</div>
								<div class="col-md-2"><label>State</label>
									<select name="sch_state" id="sch_state" class="form-control form-control-sm select-search">
										<option value="0">All</option>
										<?php echo $dbconn->fnFillComboFromTable_Where("state_id", "state_name", "mst_state", "state_name", " WHERE state_status = 1"); ?>
									</select>
									<script>$('#sch_state').val('<?php echo $f_state; ?>');</script>
								</div>
								<div class="col-md-2"><label>Engineer</label>
									<select name="sch_engineer" id="sch_engineer" class="form-control form-control-sm select-search">
										<option value="0">All</option>
										<?php echo $dbconn->fnFillComboFromTable_Where("emp_id", "emp_name", "mst_employee", "emp_name", " WHERE emp_type = 1"); ?>
									</select>
									<script>$('#sch_engineer').val('<?php echo $f_engineer; ?>');</script>
								</div>
							</div>
							<div class="form-row mt-2">
								<div class="col-md-2"><label>Made in</label>
									<select name="sch_from" class="form-control form-control-sm">
										<?php foreach (array('' => 'BIE and Benzear', 'bie' => 'BIE', 'benzear' => 'From Benzear') as $k => $v) {
											echo '<option value="' . $k . '" ' . ($f_from === (string)$k ? 'selected' : '') . '>' . $v . '</option>';
										} ?>
									</select>
								</div>
								<div class="col-md-4"><label>Search</label><input type="text" name="keyword" class="form-control form-control-sm" value="<?php echo $h($keyword); ?>" placeholder="Call no, customer, mobile, complaint"></div>
								<div class="col-md-6 text-right" style="padding-top:26px;">
									<button type="submit" class="btn btn-info btn-sm">Show</button>
									<button type="button" class="btn btn-light btn-sm rpt_export"><i class="icon-file-excel mr-1"></i> Excel</button>
								</div>
							</div>
						</form>
						<table class="table table-xs table-bordered" id="sc_rept">
							<thead>
								<tr class="bg-teal">
									<th>#</th>
									<th>Call No.</th>
									<th>Date</th>
									<th>Customer</th>
									<th>Mobile</th>
									<th>State</th>
									<th>Complaint</th>
									<th>Action</th>
									<th>Engineer</th>
									<th>Solving Date</th>
									<th>Days</th>
									<th>Charge Type</th>
									<th class="text-right">Received</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								<?php $i = 1;
								$total = 0;
								foreach ($rows as $r) {
									$done = $r->complaint_status == 1;
									$days = ($done && $r->solving_date) ? (new DateTime($r->sc_date))->diff(new DateTime($r->solving_date))->days : '';
									$total += $r->total_received_amount; ?>
									<tr>
										<td><?php echo $i++; ?></td>
										<td><?php echo $h($r->sc_no); ?><?php echo $r->src_system == 'BENZEAR' ? '<br><small>Benzear ' . $h($r->src_sc_no) . '</small>' : ''; ?></td>
										<td><?php echo date('d-m-Y', strtotime($r->sc_date)); ?></td>
										<td><?php echo $h($r->customer_name); ?></td>
										<td><?php echo $h($r->mobile_no); ?></td>
										<td><?php echo $h(trim($r->state_name)); ?></td>
										<td><?php echo $h($r->complaint_desc); ?></td>
										<td><?php echo $done && isset($actions[$r->action_taken]) ? $actions[$r->action_taken] : ''; ?></td>
										<td><?php echo $h($r->emp_name); ?></td>
										<td><?php echo $done && $r->solving_date ? date('d-m-Y', strtotime($r->solving_date)) : ''; ?></td>
										<td><?php echo $days; ?></td>
										<td><?php echo isset($types[$r->service_charge_type]) ? $types[$r->service_charge_type] : ''; ?></td>
										<td class="text-right"><?php echo $r->total_received_amount > 0 ? number_format($r->total_received_amount, 2) : ''; ?></td>
										<td><?php echo $r->sc_approve_status == 1 ? 'Approved' : ($done ? 'Completed' : 'Open'); ?></td>
									</tr>
								<?php }
								if (count($rows) == 0) { ?>
									<tr><td colspan="14" class="text-center">No service calls for these filters..!</td></tr>
								<?php } else { ?>
									<tr class="font-weight-semibold"><td colspan="12" class="text-right">Total</td><td class="text-right"><?php echo number_format($total, 2); ?></td><td></td></tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<?php include("inc/common/footer.php") ?>
		</div>
	</div>
</body>

</html>
