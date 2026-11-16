<?php
// 2026-11-16 new - service call register list (the same flow as Benzear): calls made here and calls transferred from Benzear
ob_start();
session_start();
require_once("inc/common/userclass.php");
require_once("inc/common/service_call.php");

isAdmin();
$conn = new dbconnect();
$dbconn = new dbhandler();

// calls Benzear sent and BIE has not taken in yet - BIE number, branch, customer
fnScReceiveTransfers($conn);

$from_date = !empty($_REQUEST['from_date']) ? date('Y-m-d', strtotime($_REQUEST['from_date'])) : date('Y-m-d', strtotime('-3 months'));
$to_date = !empty($_REQUEST['to_date']) ? date('Y-m-d', strtotime($_REQUEST['to_date'])) : date('Y-m-d');
$show = isset($_REQUEST['show']) ? $_REQUEST['show'] : 'all';

$sql = "SELECT * FROM tbl_service_call_register WHERE sc_date BETWEEN :f AND :t";
if ($show == 'pending') {
	$sql .= " AND sc_status = 1";
} elseif ($show == 'benzear') {
	$sql .= " AND src_system = 'BENZEAR'";
}
$st = $conn->prepare($sql . " ORDER BY sc_date DESC, sc_id DESC");
$st->execute(array(':f' => $from_date, ':t' => $to_date));
$calls = $st->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<title><?php echo PAGE_TITLE; ?> - Service Call Register</title>
	<?php include_once("inc/common/css-js.php"); ?>
	<script type="text/javascript">
		$(function() {
			<?php fnScMessages(); ?>
			$('#sc_table').DataTable({
				order: [],
				columnDefs: [{ orderable: false, targets: [8] }, { className: 'text-center', targets: [8] }]
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
							<a href="#" class="breadcrumb-item">Service</a>
							<span class="breadcrumb-item active">Service Call Register</span>
						</div>
					</div>
				</div>
			</div>

			<div class="content pt-0">
				<div class="card">
					<div class="card-header bg-pgheader text-white header-elements-inline">
						<h6 class="card-title">Service Call Register</h6>
						<div class="header-elements">
							<a href="service_call_register.php" class="btn btn-light btn-sm"><i class="icon-plus2 mr-1"></i> New Service Call</a>
						</div>
					</div>
					<div class="card-body pt-3">
						<form method="get" class="form-inline mb-3">
							<label class="mr-2">From</label>
							<input type="date" name="from_date" class="form-control form-control-sm mr-3" value="<?php echo $from_date; ?>">
							<label class="mr-2">To</label>
							<input type="date" name="to_date" class="form-control form-control-sm mr-3" value="<?php echo $to_date; ?>">
							<select name="show" class="form-control form-control-sm mr-3">
								<option value="all" <?php echo $show == 'all' ? 'selected' : ''; ?>>All calls</option>
								<option value="pending" <?php echo $show == 'pending' ? 'selected' : ''; ?>>Pending only</option>
								<option value="benzear" <?php echo $show == 'benzear' ? 'selected' : ''; ?>>From Benzear only</option>
							</select>
							<button type="submit" class="btn btn-info btn-sm">Show</button>
						</form>
						<table class="table table-xs table-bordered table-hover" id="sc_table">
							<thead>
								<tr class="bg-teal">
									<th>#</th>
									<th>Call No.</th>
									<th>Date</th>
									<th>Customer</th>
									<th>Nature of Complaint</th>
									<th>Status</th>
									<th>Solving Date</th>
									<th>Days</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$i = 1;
								foreach ($calls as $sc) {
									$solving = $conn->query("SELECT MAX(solving_date) FROM tbl_sc_complaints WHERE sc_id = " . (int)$sc->sc_id . " AND complaint_status = 1")->fetchColumn();
									$days = '';
									if ($sc->sc_status >= 2 && $solving) {
										$days = (new DateTime($sc->sc_date))->diff(new DateTime($solving))->days . ' days';
									}
									$from = $sc->src_system == 'BENZEAR' ? '<br><span class="badge badge-flat border-primary text-primary" title="Transferred from Benzear by ' . htmlspecialchars($sc->src_transfer_by) . '">Benzear ' . htmlspecialchars($sc->src_sc_no) . '</span>' : '';
									$edit = $sc->sc_status == 1
										? '<a href="service_call_register.php?sc_id=' . $sc->sc_id . '" class="list-icons-item mr-2" title="Edit"><i class="icon-pencil7"></i></a>'
										: '<span class="list-icons-item mr-2 text-muted" title="Completed - no edit"><i class="icon-pencil7"></i></span>';
								?>
									<tr>
										<td><?php echo $i++; ?></td>
										<td><b><?php echo htmlspecialchars($sc->sc_no); ?></b><?php echo $from; ?></td>
										<td data-order="<?php echo $sc->sc_date; ?>"><?php echo date('d-m-Y', strtotime($sc->sc_date)); ?></td>
										<td><?php echo htmlspecialchars($sc->customer_name); ?><br><small class="text-muted"><?php echo htmlspecialchars($sc->mobile_no); ?></small></td>
										<td><?php echo nl2br(htmlspecialchars($sc->nature_of_complaint)); ?></td>
										<td><?php echo fnScStatusLabel($sc); ?></td>
										<td><?php echo $solving ? date('d-m-Y', strtotime($solving)) : ''; ?></td>
										<td><?php echo $days; ?></td>
										<td style="white-space:nowrap;">
											<?php echo $edit; ?>
											<a href="service_call_complete_list.php?sc_id=<?php echo $sc->sc_id; ?>" class="list-icons-item mr-2" title="Complaints / Complete"><i class="icon-checkmark-circle"></i></a>
											<a href="service_call_print.php?sc_id=<?php echo $sc->sc_id; ?>" class="list-icons-item" title="Print / Approve"><i class="icon-printer"></i></a>
										</td>
									</tr>
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
