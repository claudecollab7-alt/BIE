<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

// 2026-10-13 new - Task Settings: tasks and who creates / verifies / approves / views each one, admin only

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

if (!fnTaskIsAdmin()) {
	fnAccessDenied(false, 'home.php');
}

$role_names = array('C' => 'Creator', 'V' => 'Verifier', 'A' => 'Approver', 'W' => 'Viewer');

// task row + its user rows from the posted form, inside the caller's transaction
function fnSaveTaskUsers($conn, $task_id, $roles)
{
	$conn->prepare("DELETE FROM tbl_task_assign WHERE task_id = :t")->execute(array(':t' => $task_id));
	$ins = $conn->prepare("INSERT IGNORE INTO tbl_task_assign (task_id, task_role, usr_id, branch_id, created_by, created_dtm)
						   VALUES (:t, :r, :u, :b, :by, :dtm)");
	$row_count = fnRowCount('assign_role');
	for ($x = 0; $x < $row_count; $x++) {
		$role = isset($_REQUEST['assign_role'][$x]) ? $_REQUEST['assign_role'][$x] : '';
		$usr = isset($_REQUEST['assign_usr'][$x]) ? (int)$_REQUEST['assign_usr'][$x] : 0;
		$branch = isset($_REQUEST['assign_branch'][$x]) ? (int)$_REQUEST['assign_branch'][$x] : 0;
		if ($usr <= 0 || strpos($roles, $role) === false || strlen($role) != 1) {
			continue;
		}
		if ($role == 'W') {
			$branch = 0;	// viewers see every branch
		}
		$ins->execute(array(':t' => $task_id, ':r' => $role, ':u' => $usr, ':b' => $branch, ':by' => $_SESSION['_user_id'], ':dtm' => date('Y-m-d H:i:s')));
	}
}

// roles ticked on the form, in C V A W order
function fnPostedRoles()
{
	$roles = '';
	foreach (array('C', 'V', 'A', 'W') as $r) {
		if (isset($_REQUEST['task_roles']) && is_array($_REQUEST['task_roles']) && in_array($r, $_REQUEST['task_roles'])) {
			$roles .= $r;
		}
	}
	return $roles;
}

if (isset($_POST['SAVE']) || isset($_POST['UPDATE'])) {
	if (!csrf_check('mst_task')) {
		csrf_fail('mst_task.php');
	}
	$task_id = isset($_POST['UPDATE']) ? (int)$_REQUEST['txtHid'] : 0;
	$code = strtoupper(trim(isset($_REQUEST['task_code']) ? $_REQUEST['task_code'] : ''));
	$name = trim(isset($_REQUEST['task_name']) ? $_REQUEST['task_name'] : '');
	$roles = fnPostedRoles();
	try {
		if ($name == '' || $roles == '' || ($task_id == 0 && !preg_match('/^[A-Z0-9_]{2,30}$/', $code))) {
			throw new Exception("Task Code (letters, numbers, _), Task Name and at least one role are needed..!");
		}
		db_begin($conn);
		if ($task_id == 0) {
			if (db_value($conn, "SELECT task_id FROM mst_task WHERE task_code = :c", array(':c' => $code)) != '') {
				throw new Exception("Task Code already exists..!");
			}
			$stmt = $conn->prepare("INSERT INTO mst_task (task_code, task_name, task_module, task_roles, task_note, task_status, created_by, created_dtm)
									VALUES (:code, :name, :module, :roles, :note, 1, :by, :dtm)");
			$stmt->execute(array(':code' => $code, ':name' => $name, ':module' => trim($_REQUEST['task_module']), ':roles' => $roles,
								 ':note' => trim($_REQUEST['task_note']), ':by' => $_SESSION['_user_id'], ':dtm' => date('Y-m-d H:i:s')));
			$task_id = $conn->lastInsertId();
		} else {
			// the code is what the screens check, so it is not changed here
			$stmt = $conn->prepare("UPDATE mst_task SET task_name = :name, task_module = :module, task_roles = :roles, task_note = :note,
									task_status = :status, modify_by = :by, modify_dtm = :dtm WHERE task_id = :id");
			$stmt->execute(array(':name' => $name, ':module' => trim($_REQUEST['task_module']), ':roles' => $roles, ':note' => trim($_REQUEST['task_note']),
								 ':status' => (isset($_REQUEST['task_status']) && $_REQUEST['task_status'] == '0') ? 0 : 1,
								 ':by' => $_SESSION['_user_id'], ':dtm' => date('Y-m-d H:i:s'), ':id' => $task_id));
		}
		fnSaveTaskUsers($conn, $task_id, $roles);
		db_commit($conn);
		$_SESSION['_msg'] = "Task successfully saved..!";
	} catch (Exception $e) {
		db_rollback($conn);
		fnLogError($e);
		$_SESSION['_msg_err'] = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		header("location:mst_task.php" . ($task_id ? "?task_id=" . $task_id : ""));
		die();
	}
	header("location:mst_task.php?task_id=" . $task_id);
	die();
}

$task = null;
$assigns = array();
if (isset($_REQUEST['task_id']) && (int)$_REQUEST['task_id'] > 0) {
	$stmt = $conn->prepare("SELECT * FROM mst_task WHERE task_id = :id");
	$stmt->execute(array(':id' => (int)$_REQUEST['task_id']));
	$task = $stmt->fetch(PDO::FETCH_OBJ);
	if ($task) {
		$stmt = $conn->prepare("SELECT a.*, u.usr_name, b.branch_name FROM tbl_task_assign a
								LEFT JOIN tbl_user u ON u.usr_id = a.usr_id
								LEFT JOIN mst_branch b ON b.branch_id = a.branch_id
								WHERE a.task_id = :id ORDER BY FIELD(a.task_role, 'C', 'V', 'A', 'W'), u.usr_name, a.branch_id");
		$stmt->execute(array(':id' => $task->task_id));
		$assigns = $stmt->fetchAll(PDO::FETCH_OBJ);
	}
}
$task_roles = $task ? $task->task_roles : 'A';

$users = $conn->query("SELECT u.usr_id, u.usr_name, u.usr_type, u.usr_status, b.branch_name FROM tbl_user u
					   LEFT JOIN mst_branch b ON b.branch_id = u.branch_id ORDER BY u.usr_status DESC, u.usr_name")->fetchAll(PDO::FETCH_OBJ);
$branches = $conn->query("SELECT branch_id, branch_name FROM mst_branch WHERE branch_status = 1 ORDER BY branch_id")->fetchAll(PDO::FETCH_OBJ);
$h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES); };
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo PAGE_TITLE; ?> - Task Settings</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<?php include_once("inc/common/css-js.php"); ?>
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
							<a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
							<a href="#" class="breadcrumb-item"> Settings</a>
							<span class="breadcrumb-item active">Task Settings</span>
						</div>
					</div>
				</div>
			</div>

			<div class="content pt-0">
				<div class="row">
					<div class="col-md-12">
						<form name="taskForm" class="form-horizontal" method="POST" action="mst_task.php" onSubmit="return fnValidate();">
							<?php csrf_fields('mst_task'); ?>
							<div class="card">
								<div class="card-header bg-pgheader text-white header-elements-inline">
									<h6 class="card-title"><?php echo $task ? 'Edit Task - ' . $h($task->task_name) : 'New Task'; ?></h6>
									<div class="header-elements">
										<div class="list-icons">
											<a class="list-icons-item" href="mst_task.php" title="New Task"><i class="icon-plus-circle2 mr-2"></i></a>
										</div>
									</div>
								</div>
								<div class="card-body">
									<div class="form-group row">
										<label class="col-lg-2 col-form-label">Task Code <span class="text-mandatory">*</span></label>
										<div class="col-lg-4">
											<input type="text" class="form-control text-uppercase" name="task_code" id="task_code" maxlength="30" value="<?php echo $task ? $h($task->task_code) : ''; ?>" <?php echo $task ? 'readonly tabindex="-1"' : ''; ?> placeholder="e.g. PURCHASE_ORDER">
											<span class="form-text text-muted">The screens check this code. A new task takes effect once a screen is set to check it.</span>
										</div>
										<label class="col-lg-2 col-form-label">Task Name <span class="text-mandatory">*</span></label>
										<div class="col-lg-4">
											<input type="text" class="form-control" name="task_name" id="task_name" maxlength="100" value="<?php echo $task ? $h($task->task_name) : ''; ?>">
										</div>
									</div>
									<div class="form-group row">
										<label class="col-lg-2 col-form-label">Module</label>
										<div class="col-lg-4">
											<input type="text" class="form-control" name="task_module" id="task_module" maxlength="50" value="<?php echo $task ? $h($task->task_module) : ''; ?>">
										</div>
										<label class="col-lg-2 col-form-label">Roles Used <span class="text-mandatory">*</span></label>
										<div class="col-lg-4 pt-2">
											<?php foreach ($role_names as $r => $label) { ?>
												<label class="mr-3"><input type="checkbox" class="task_role" name="task_roles[]" value="<?php echo $r; ?>" <?php echo strpos($task_roles, $r) !== false ? 'checked' : ''; ?>> <?php echo $label; ?></label>
											<?php } ?>
										</div>
									</div>
									<div class="form-group row">
										<label class="col-lg-2 col-form-label">Note</label>
										<div class="col-lg-<?php echo $task ? '6' : '10'; ?>">
											<textarea class="form-control" name="task_note" id="task_note" rows="2" maxlength="500"><?php echo $task ? $h($task->task_note) : ''; ?></textarea>
										</div>
										<?php if ($task) { ?>
											<label class="col-lg-2 col-form-label">Status</label>
											<div class="col-lg-2">
												<select name="task_status" id="task_status" class="form-control">
													<option value="1" <?php echo $task->task_status == 1 ? 'selected' : ''; ?>>Active</option>
													<option value="0" <?php echo $task->task_status == 0 ? 'selected' : ''; ?>>Inactive</option>
												</select>
											</div>
										<?php } ?>
									</div>

									<legend class="font-weight-semibold mt-3"><i class="icon-users mr-2"></i>Users</legend>
									<p class="text-muted mb-2">Creator: creates / edits. Verifier: sends for approval or confirms. Approver: approves, rejects and cancels. Viewer: sees every branch on the lists.
										Branch All = every branch; a branch = only that branch's documents. Admin users can do every task and need no rows.</p>
									<div class="form-group row">
										<div class="col-lg-2">
											<select id="add_role" class="form-control">
												<?php foreach ($role_names as $r => $label) { ?>
													<option value="<?php echo $r; ?>"><?php echo $label; ?></option>
												<?php } ?>
											</select>
										</div>
										<div class="col-lg-2">
											<select id="add_branch" class="form-control">
												<option value="0">All Branches</option>
												<?php foreach ($branches as $b) { ?>
													<option value="<?php echo $b->branch_id; ?>"><?php echo $h($b->branch_name); ?></option>
												<?php } ?>
											</select>
										</div>
										<div class="col-lg-6">
											<select id="add_users" class="select" multiple data-placeholder="Choose users..">
												<?php foreach ($users as $u) { ?>
													<option value="<?php echo $u->usr_id; ?>"><?php echo $h($u->usr_name . ' (' . $u->branch_name . ')' . ($u->usr_type == 'A' ? ' - admin' : '') . ($u->usr_status != 1 ? ' - inactive' : '')); ?></option>
												<?php } ?>
											</select>
										</div>
										<div class="col-lg-2">
											<button type="button" class="btn btn-info" id="add_rows"><i class="icon-plus2 mr-1"></i> Add</button>
										</div>
									</div>
									<table class="table table-xs table-bordered" id="assignTable">
										<thead>
											<tr class="bg-table-header">
												<th width="20%">Role</th>
												<th>User</th>
												<th width="25%">Branch</th>
												<th width="8%" class="text-center">Remove</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($assigns as $a) { ?>
												<tr data-key="<?php echo $a->task_role . '-' . $a->usr_id . '-' . $a->branch_id; ?>">
													<td><?php echo $role_names[$a->task_role]; ?><input type="hidden" name="assign_role[]" value="<?php echo $a->task_role; ?>"></td>
													<td><?php echo $h($a->usr_name); ?><input type="hidden" name="assign_usr[]" value="<?php echo $a->usr_id; ?>"></td>
													<td><?php echo $a->branch_id == 0 ? 'All Branches' : $h($a->branch_name); ?><input type="hidden" name="assign_branch[]" value="<?php echo $a->branch_id; ?>"></td>
													<td class="text-center"><a href="javascript:;" class="del_row" title="Remove"><i class="icon-bin bg-delete"></i></a></td>
												</tr>
											<?php } ?>
											<tr class="no_rows" <?php echo $assigns ? 'style="display:none"' : ''; ?>>
												<td colspan="4" class="text-muted">No users - only admin can do this task.</td>
											</tr>
										</tbody>
									</table>
								</div>
								<div class="card-footer text-center">
									<?php if ($task) { ?>
										<INPUT class="btn btn-custom" type="submit" name="UPDATE" value="Update">
										<input type="hidden" name="txtHid" value="<?php echo $task->task_id; ?>">
									<?php } else { ?>
										<INPUT class="btn btn-custom" type="submit" name="SAVE" value="Save">
										<input type="hidden" name="txtHid" value="0">
									<?php } ?>
									<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:window.location.href='mst_task.php'">
								</div>
							</div>
						</form>
					</div>

					<div class="col-md-12">
						<div class="card">
							<div class="card-header bg-pgheader text-white header-elements-inline">
								<h6 class="card-title">List of Tasks</h6>
							</div>
							<div class="card-body pt-0">
								<table class="table table-xs table-hover table-bordered" id="taskTable">
									<thead>
										<tr class="bg-table-header">
											<th>Code</th>
											<th>Task</th>
											<th>Module</th>
											<th>Creator</th>
											<th>Verifier</th>
											<th>Approver</th>
											<th>Viewer</th>
											<th class="text-center">Status</th>
											<th class="text-center">Edit</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$list = $conn->query("SELECT t.*, a.task_role, GROUP_CONCAT(CONCAT(u.usr_name, IF(a.branch_id = 0, '', CONCAT(' (', b.branch_name, ')'))) ORDER BY u.usr_name SEPARATOR ', ') AS who
															  FROM mst_task t
															  LEFT JOIN tbl_task_assign a ON a.task_id = t.task_id
															  LEFT JOIN tbl_user u ON u.usr_id = a.usr_id
															  LEFT JOIN mst_branch b ON b.branch_id = a.branch_id
															  GROUP BY t.task_id, a.task_role ORDER BY t.task_module, t.task_name")->fetchAll(PDO::FETCH_OBJ);
										$rows = array();
										foreach ($list as $l) {
											if (!isset($rows[$l->task_id])) {
												$rows[$l->task_id] = array('t' => $l, 'who' => array());
											}
											if ($l->task_role) {
												$rows[$l->task_id]['who'][$l->task_role] = $l->who;
											}
										}
										foreach ($rows as $r) {
											$t = $r['t'];
											echo '<tr>';
											echo '<td>' . $h($t->task_code) . '</td>';
											echo '<td>' . $h($t->task_name) . '</td>';
											echo '<td>' . $h($t->task_module) . '</td>';
											foreach (array('C', 'V', 'A', 'W') as $role) {
												if (strpos($t->task_roles, $role) === false) {
													echo '<td class="text-muted">-</td>';
												} else {
													echo '<td>' . (isset($r['who'][$role]) ? $h($r['who'][$role]) : '<span class="text-muted">admin only</span>') . '</td>';
												}
											}
											echo '<td class="text-center">' . ($t->task_status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-grey">Inactive</span>') . '</td>';
											echo '<td class="text-center"><a href="mst_task.php?task_id=' . $t->task_id . '" title="Edit"><i class="icon-pencil5 bg-edit"></i></a></td>';
											echo '</tr>';
										}
										if (!$rows) {
											echo '<tr><td colspan="9">No Records...</td></tr>';
										}
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php include("inc/common/footer.php") ?>
		</div>
	</div>
</body>

<script type="text/javascript">
	var roleNames = <?php echo json_encode($role_names); ?>;

	$(document).ready(function() {
		<?php
		if (isset($_SESSION['_msg']) && $_SESSION['_msg'] != "") {
			echo "$.jGrowl('" . addslashes($_SESSION['_msg']) . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-success', position: 'bottom-right', life:'2000', header: 'Success!' });";
			$_SESSION['_msg'] = "";
		}
		if (isset($_SESSION['_msg_err']) && $_SESSION['_msg_err'] != "") {
			echo "$.jGrowl('" . addslashes($_SESSION['_msg_err']) . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-danger', position: 'top-right', shutdown:'3000', header: 'Error!' });";
			$_SESSION['_msg_err'] = "";
		}
		?>

		// viewers always see every branch
		$('#add_role').change(function() {
			if ($(this).val() == 'W') {
				$('#add_branch').val('0').prop('disabled', true);
			} else {
				$('#add_branch').prop('disabled', false);
			}
		}).change();

		$('#add_rows').click(function() {
			var role = $('#add_role').val();
			if (!$('.task_role[value="' + role + '"]').is(':checked')) {
				alert('Tick ' + roleNames[role] + ' under Roles Used first..!');
				return;
			}
			var branch = role == 'W' ? '0' : $('#add_branch').val();
			var branchName = branch == '0' ? 'All Branches' : $('#add_branch option:selected').text();
			var picked = $('#add_users').val() || [];
			if (picked.length == 0) {
				alert('Please choose the users..!');
				return;
			}
			$.each(picked, function(i, usr) {
				var key = role + '-' + usr + '-' + branch;
				if ($('#assignTable tr[data-key="' + key + '"]').length) {
					return;
				}
				var name = $('#add_users option[value="' + usr + '"]').text().replace(/ \(.*$/, '');
				var tr = $('<tr>').attr('data-key', key);
				tr.append($('<td>').text(roleNames[role]).append($('<input type="hidden" name="assign_role[]">').val(role)));
				tr.append($('<td>').text(name).append($('<input type="hidden" name="assign_usr[]">').val(usr)));
				tr.append($('<td>').text(branchName).append($('<input type="hidden" name="assign_branch[]">').val(branch)));
				tr.append('<td class="text-center"><a href="javascript:;" class="del_row" title="Remove"><i class="icon-bin bg-delete"></i></a></td>');
				$('#assignTable tbody .no_rows').before(tr);
			});
			$('#add_users').val(null).trigger('change');
			$('#assignTable .no_rows').hide();
		});

		$('#assignTable').on('click', '.del_row', function() {
			$(this).closest('tr').remove();
			if ($('#assignTable tbody tr[data-key]').length == 0) {
				$('#assignTable .no_rows').show();
			}
		});
	});

	function fnValidate() {
		if ($('#task_code').val() == '' || !/^[A-Za-z0-9_]{2,30}$/.test($('#task_code').val())) {
			alert('Please enter the Task Code (letters, numbers, _)..!');
			$('#task_code').focus();
			return false;
		}
		if (isNull(document.taskForm.task_name, "Task Name..!")) {
			return false;
		}
		if ($('.task_role:checked').length == 0) {
			alert('Please tick at least one role..!');
			return false;
		}
		// rows for a role that is no longer ticked are not kept
		var dropped = 0;
		$('#assignTable tbody tr[data-key]').each(function() {
			var role = $(this).find('input[name="assign_role[]"]').val();
			if (!$('.task_role[value="' + role + '"]').is(':checked')) {
				dropped++;
			}
		});
		if (dropped > 0 && !confirm(dropped + ' user row(s) are for roles not ticked and will be removed. Continue?')) {
			return false;
		}
		return true;
	}
</script>

</html>
