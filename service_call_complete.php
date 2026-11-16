<?php
// 2026-11-16 new - complete one complaint of a service call (the same flow as Benzear): action, solving date, engineer,
// amounts, spare parts used on an on-site visit (recorded only - stock does not move), customer review copy;
// when every complaint of the call is completed, the call is completed
ob_start();
session_start();
require_once("inc/common/userclass.php");
require_once("inc/common/service_call.php");

isAdmin();
$conn = new dbconnect();
$dbconn = new dbhandler();

$sc_id = (int)(isset($_REQUEST['sc_id']) ? $_REQUEST['sc_id'] : 0);
$complaint_id = (int)(isset($_REQUEST['complaint_id']) ? $_REQUEST['complaint_id'] : 0);
$back = "service_call_complete.php?sc_id=" . $sc_id . "&complaint_id=" . $complaint_id;

if (fnPostTooLarge()) {
	$_SESSION['_msg_err'] = "That was too much data to send in one go (the review copy may be too big). Please try again.";
	header("location:" . $back);
	die();
}

if (isset($_POST['SAVE']) || isset($_POST['UPDATE'])) {
	if (!csrf_check('service_call_complete')) {
		csrf_fail($back);
	}
	try {
		db_begin($conn);
		// the complaint must be one of this call, and the call not sent for approval
		$c = $conn->query("SELECT c.complaint_id, c.sc_review, r.sc_no, r.sc_verify_status FROM tbl_sc_complaints c JOIN tbl_service_call_register r ON r.sc_id = c.sc_id
			WHERE c.complaint_id = " . $complaint_id . " AND c.sc_id = " . $sc_id . " FOR UPDATE")->fetch(PDO::FETCH_OBJ);
		if (!$c) {
			throw new Exception("Complaint not found..!");
		}
		if ($c->sc_verify_status == 1) {
			throw new Exception("The call is sent for approval - it can't be changed..!");
		}
		$action = (int)(isset($_POST['action_taken']) ? $_POST['action_taken'] : 0);
		if (!in_array($action, array(1, 2, 3))) {
			throw new Exception("Please select the Action Type..!");
		}
		$onsite = $action == 2;
		$engineer = $onsite ? (int)(isset($_POST['service_engineer_id']) ? $_POST['service_engineer_id'] : 0) : 0;
		if ($onsite && !$conn->query("SELECT emp_id FROM mst_employee WHERE emp_id = " . $engineer)->fetch()) {
			throw new Exception("Please select the Service Engineer..!");
		}
		$num = function ($k) {
			$v = trim(isset($_POST[$k]) ? $_POST[$k] : '');
			if ($v !== '' && !preg_match('/^\d+(\.\d{1,2})?$/', $v)) {
				throw new Exception("Amounts and visits must be numbers..!");
			}
			return $v === '' ? 0 : (float)$v;
		};
		$received = $num('total_received_amount');
		$expenses = $onsite ? $num('engineer_expenses') : 0;
		$visits = $onsite ? (int)$num('no_of_visits') : 0;
		$solving = !empty($_POST['solving_date']) ? date('Y-m-d', strtotime($_POST['solving_date'])) : date('Y-m-d');
		$spares = $onsite ? fnScSparesPosted($conn, $complaint_id) : array();

		// customer review copy: pdf / image only
		$review = $c->sc_review;
		if (isset($_FILES['sc_review']) && $_FILES['sc_review']['name'] != '') {
			$ext = strtolower(pathinfo($_FILES['sc_review']['name'], PATHINFO_EXTENSION));
			if (!in_array($ext, array('pdf', 'jpg', 'jpeg', 'png')) || $_FILES['sc_review']['error'] != 0) {
				throw new Exception("Review copy must be a PDF or an image (jpg / png)..!");
			}
			if (!is_dir("project_img/sc_review")) {
				mkdir("project_img/sc_review", 0755, true);
			}
			if ($review != '' && file_exists("project_img/sc_review/" . $review)) {
				unlink("project_img/sc_review/" . $review);
			}
			$review = post_img(preg_replace('/[^A-Za-z0-9_-]/', '-', $c->sc_no) . '_' . $complaint_id . '.' . $ext, $_FILES['sc_review']['tmp_name'], "project_img/sc_review");
		}

		$conn->prepare("UPDATE tbl_sc_complaints SET action_taken = :action, action_remarks = :remarks, solving_date = :solving, service_engineer_id = :engineer,
			customer_comments = :comments, sc_review = :review, chart_no = :chart, total_received_amount = :received, engineer_expenses = :expenses,
			no_of_visits = :visits, complaint_status = 1, complete_by = :by, complete_date_time = IFNULL(complete_date_time, :dt)
			WHERE complaint_id = :id")->execute(array(
			':action' => $action, ':remarks' => trim(isset($_POST['action_remarks']) ? $_POST['action_remarks'] : ''), ':solving' => $solving,
			':engineer' => $engineer, ':comments' => $onsite ? trim(isset($_POST['customer_comments']) ? $_POST['customer_comments'] : '') : '',
			':review' => (string)$review, ':chart' => trim(isset($_POST['chart_no']) ? $_POST['chart_no'] : ''), ':received' => $received,
			':expenses' => $expenses, ':visits' => $visits, ':by' => $_SESSION['_user_id'], ':dt' => date('Y-m-d H:i:s'), ':id' => $complaint_id
		));
		fnScSaveSpares($conn, $complaint_id, $spares);
		fnScRefreshStatus($conn, $sc_id);	// every complaint done -> the call is completed
		db_commit($conn);
		$_SESSION['_msg'] = "Complaint completion saved..!";
	} catch (Exception $e) {
		db_rollback($conn);
		if (strpos($e->getMessage(), 'SQLSTATE') !== false) {
			fnLogError($e);
		}
		$_SESSION['_msg_err'] = str_replace("'", "", $e->getMessage());
		header("location:" . $back);
		die();
	}
	header("location:service_call_complete_list.php?sc_id=" . $sc_id);
	die();
}

$sc = $conn->query("SELECT * FROM tbl_service_call_register WHERE sc_id = " . $sc_id)->fetch(PDO::FETCH_OBJ);
$complaint = $sc ? $conn->query("SELECT * FROM tbl_sc_complaints WHERE complaint_id = " . $complaint_id . " AND sc_id = " . $sc_id)->fetch(PDO::FETCH_OBJ) : false;
if (!$complaint) {
	header("location:service_call_list.php");
	die();
}
$spares = $conn->query("SELECT * FROM tbl_sc_complaint_details WHERE complaint_id = " . $complaint_id . " ORDER BY auto_id")->fetchAll(PDO::FETCH_OBJ);
$done = $complaint->complaint_status == 1;
$action = $done ? (int)$complaint->action_taken : 1;
$h = function ($v) {
	return htmlspecialchars((string)$v);
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<title><?php echo PAGE_TITLE; ?> - Complete Complaint</title>
	<?php include_once("inc/common/css-js.php"); ?>
	<script type="text/javascript">
		var wasSubmitted = false;

		function fnOnsite() {
			$('.onsite').toggle($('#action_taken').val() == '2');
		}

		function scRows() {
			$('#spare_lines tr').each(function(i) {
				$(this).find('.line_sno').text(i + 1);
			});
		}

		function remove_complaint_item(el) {
			$(el).closest('tr').remove();
			scRows();
		}

		function fnValidate() {
			if ($('#solving_date').val() === '') {
				alert('Please enter Solving Date..!');
				return false;
			}
			if ($('#action_taken').val() == '2' && !($('#service_engineer_id').val() > 0)) {
				alert('Please select the Service Engineer..!');
				return false;
			}
			if (wasSubmitted) {
				return false;
			}
			wasSubmitted = true;
			return true;
		}

		$(function() {
			<?php fnScMessages(); ?>
			fnOnsite();
			scRows();
			$('#action_taken').on('change', fnOnsite);
			$('#spare_item').select2({
				placeholder: 'Search spare..',
				minimumInputLength: 1,
				ajax: { url: 'inc/auto/select_sc_items.php', dataType: 'json', delay: 250, data: function(p) { return { q: p.term }; } }
			});
			$('#add_spare').on('click', function() {
				var item = $('#spare_item').val(), qty = $.trim($('#sc_qty').val());
				if (!item) {
					alert('Please pick the Item from the list..!');
					return;
				}
				$.post('inc/cis_ajax/jquery_sc_complaint_details.php', { item_id: item, sc_qty: qty }).done(function(row) {
					row = $.trim(row);	// the reply can start with blank lines
					if (row.indexOf('ERR:') === 0) {
						alert(row.substr(4));
						return;
					}
					$('#spare_lines tr[data-item="' + item + '"]').remove();	// the same item again replaces its row
					$('#spare_lines').append(row);
					$('#spare_item').val(null).trigger('change');
					$('#sc_qty').val('');
					scRows();
				});
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
							<a href="service_call_complete_list.php?sc_id=<?php echo $sc_id; ?>" class="breadcrumb-item"><?php echo $h($sc->sc_no); ?></a>
							<span class="breadcrumb-item active">Complete Complaint</span>
						</div>
					</div>
				</div>
			</div>
			<div class="content pt-0">
				<form name="thisForm" method="post" action="<?php echo $back; ?>" enctype="multipart/form-data" onsubmit="return fnValidate();">
					<?php csrf_fields('service_call_complete'); ?>
					<input type="hidden" name="sc_id" value="<?php echo $sc_id; ?>">
					<input type="hidden" name="complaint_id" value="<?php echo $complaint_id; ?>">
					<div class="card">
						<div class="card-header bg-pgheader text-white header-elements-inline">
							<h6 class="card-title">Complete Complaint - <?php echo $h($sc->sc_no); ?> / <?php echo $h($sc->customer_name); ?></h6>
							<div class="header-elements"><?php echo $done ? '<span class="badge badge-success">Completed</span>' : '<span class="badge badge-warning">Open</span>'; ?></div>
						</div>
						<div class="card-body">
							<div class="alert alert-light border py-2"><b>Complaint:</b> <?php echo nl2br($h($complaint->complaint_desc)); ?></div>
							<?php if ($sc->sc_verify_status == 1) { ?><div class="alert alert-info py-2">The call is sent for approval - it can't be changed.</div><?php } ?>
							<div class="form-group row">
								<label class="col-lg-2 col-form-label">Action Type <span class="text-danger">*</span></label>
								<div class="col-lg-2">
									<select name="action_taken" id="action_taken" class="form-control">
										<?php foreach (array(1 => 'Over Phone Call', 2 => 'On Site Visit', 3 => 'In House Service') as $k => $v) {
											echo '<option value="' . $k . '" ' . ($action == $k ? 'selected' : '') . '>' . $v . '</option>';
										} ?>
									</select>
								</div>
								<label class="col-lg-2 col-form-label">Solving Date <span class="text-danger">*</span></label>
								<div class="col-lg-2"><input type="date" name="solving_date" id="solving_date" class="form-control" value="<?php echo $h($complaint->solving_date ? $complaint->solving_date : date('Y-m-d')); ?>"></div>
								<label class="col-lg-1 col-form-label">Chart No</label>
								<div class="col-lg-1"><input type="text" name="chart_no" class="form-control" maxlength="100" value="<?php echo $h($complaint->chart_no); ?>"></div>
								<label class="col-lg-1 col-form-label">Received Amount</label>
								<div class="col-lg-1"><input type="text" name="total_received_amount" class="form-control" maxlength="12" value="<?php echo $complaint->total_received_amount > 0 ? $h($complaint->total_received_amount) : ''; ?>"></div>
							</div>
							<div class="form-group row onsite">
								<label class="col-lg-2 col-form-label">Service Engineer <span class="text-danger">*</span></label>
								<div class="col-lg-2">
									<select name="service_engineer_id" id="service_engineer_id" class="form-control select-search">
										<option value="0">-- Select Engineer --</option>
										<?php echo $dbconn->fnFillComboFromTable_Where("emp_id", "emp_name", "mst_employee", "emp_name", " WHERE emp_type = 1 AND rec_del_status = 1"); ?>
									</select>
									<script>$('#service_engineer_id').val('<?php echo (int)$complaint->service_engineer_id; ?>');</script>
								</div>
								<label class="col-lg-2 col-form-label">Engineer Expenses</label>
								<div class="col-lg-2"><input type="text" name="engineer_expenses" class="form-control" maxlength="12" value="<?php echo $complaint->engineer_expenses > 0 ? $h($complaint->engineer_expenses) : ''; ?>"></div>
								<label class="col-lg-2 col-form-label">No. of Visits</label>
								<div class="col-lg-2"><input type="text" name="no_of_visits" class="form-control" maxlength="4" value="<?php echo $complaint->no_of_visits > 0 ? (int)$complaint->no_of_visits : ''; ?>"></div>
							</div>

							<div class="onsite">
								<legend class="font-weight-semibold"><i class="icon-cogs mr-2"></i>Spare Parts Used <small class="text-muted">(recorded only - stock does not change)</small></legend>
								<div class="form-group row">
									<div class="col-lg-7"><select id="spare_item" class="form-control"></select></div>
									<div class="col-lg-2"><input type="text" id="sc_qty" class="form-control" maxlength="9" placeholder="Qty" onkeypress="return isNumberKey_With_Dot(event)"></div>
									<div class="col-lg-3"><button type="button" id="add_spare" class="btn btn-info btn-block">Add</button></div>
								</div>
								<table class="table table-xs table-bordered mb-3">
									<thead>
										<tr class="bg-teal">
											<th width="4%">#</th>
											<th>Item Code</th>
											<th>Description</th>
											<th class="text-right">Avl. Qty</th>
											<th class="text-center">Qty</th>
											<th>Unit</th>
											<th width="5%"></th>
										</tr>
									</thead>
									<tbody id="spare_lines">
										<?php foreach ($spares as $s) {
											echo fnScSpareRow($dbconn, 'd' . $s->auto_id, array('item_id' => $s->item_id, 'qty' => $s->sc_qty, 'unit' => $s->sc_unit, 'avl' => $s->sc_avl_qty));
										} ?>
									</tbody>
								</table>
							</div>

							<div class="form-group row">
								<label class="col-lg-2 col-form-label">Action Remarks</label>
								<div class="col-lg-10"><textarea name="action_remarks" class="form-control" rows="2" maxlength="500"><?php echo $h($complaint->action_remarks); ?></textarea></div>
							</div>
							<div class="form-group row onsite">
								<label class="col-lg-2 col-form-label">Customer Comments</label>
								<div class="col-lg-10"><textarea name="customer_comments" class="form-control" rows="2" maxlength="500"><?php echo $h($complaint->customer_comments); ?></textarea></div>
							</div>
							<div class="form-group row">
								<label class="col-lg-2 col-form-label">Customer Review Copy</label>
								<div class="col-lg-4"><input type="file" name="sc_review" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
								<div class="col-lg-6 col-form-label"><?php if ($complaint->sc_review != '') { ?><a href="project_img/sc_review/<?php echo rawurlencode($complaint->sc_review); ?>" target="_blank"><?php echo $h($complaint->sc_review); ?></a><?php } ?></div>
							</div>
						</div>
						<div class="card-footer text-center">
							<?php if ($sc->sc_verify_status == 0) { ?>
								<input class="btn <?php echo $done ? 'btn-info' : 'btn-success'; ?>" type="submit" name="<?php echo $done ? 'UPDATE' : 'SAVE'; ?>" value="<?php echo $done ? 'UPDATE' : 'MARK AS COMPLETE'; ?>">
							<?php } ?>
							<input class="btn btn-light" type="button" value="Cancel" onclick="window.location.href='service_call_complete_list.php?sc_id=<?php echo $sc_id; ?>'">
						</div>
					</div>
				</form>
			</div>
			<?php include("inc/common/footer.php") ?>
		</div>
	</div>
</body>

</html>
