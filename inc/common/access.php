<?php
// 2026-10-13 access checks for approve / verify / reject actions
// 2026-10-13 task roles from Task Settings (mst_task + tbl_task_assign) instead of user ids in the code

// stop a call the user may not make - ajax gets 403, a page post goes back with a message
function fnAccessDenied($ajax = true, $back = 'home.php')
{
	if ($ajax) {
		http_response_code(403);
		die('Not allowed');
	}
	$_SESSION['_msg_err'] = "You are not allowed to do this..!";
	header("location:" . $back);
	die();
}

// ajax writes need a logged in user and the csrf token
function fnAjaxGuard()
{
	if (empty($_SESSION['_user_id']) || !function_exists('csrf_check_ajax') || !csrf_check_ajax()) {
		fnAccessDenied(true);
	}
}

// admin users can do every task
function fnTaskIsAdmin()
{
	return isset($_SESSION['_user_type']) && $_SESSION['_user_type'] == 'A';
}

// the logged in user's rows, read once per request: [task_code][role] => list of branch ids (0 = all)
function fnTaskRows()
{
	static $rows = null;
	if ($rows !== null) {
		return $rows;
	}
	$rows = array();
	if (empty($_SESSION['_user_id'])) {
		return $rows;
	}
	try {
		$db = new dbconnect();
		$stmt = $db->prepare("SELECT t.task_code, a.task_role, a.branch_id FROM tbl_task_assign a
							  JOIN mst_task t ON t.task_id = a.task_id AND t.task_status = 1
							  WHERE a.usr_id = :u");
		$stmt->execute(array(':u' => (int)$_SESSION['_user_id']));
		foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
			$rows[$r['task_code']][$r['task_role']][] = (int)$r['branch_id'];
		}
	} catch (Exception $e) {
		fnLogError($e);
	}
	return $rows;
}

// can the user act in this role - for the given branch, or for every branch when none is given
// roles: C creator / editor, V verifier, A approver (also rejects), W viewer (sees every branch)
function fnTaskCan($code, $role, $branch_id = null)
{
	if (empty($_SESSION['_user_id'])) {
		return false;
	}
	if (fnTaskIsAdmin()) {
		return true;
	}
	$rows = fnTaskRows();
	$branches = isset($rows[$code][$role]) ? $rows[$code][$role] : array();
	if (in_array(0, $branches, true)) {
		return true;
	}
	return $branch_id !== null && $branch_id !== '' && in_array((int)$branch_id, $branches, true);
}

// user saved as the named approver on a new document: first all-branch holder of the role, else the admin
function fnTaskDefaultUser($code, $role)
{
	try {
		$db = new dbconnect();
		$stmt = $db->prepare("SELECT a.usr_id FROM tbl_task_assign a JOIN mst_task t ON t.task_id = a.task_id AND t.task_status = 1
							  WHERE t.task_code = :c AND a.task_role = :r AND a.branch_id = 0 ORDER BY a.assign_id LIMIT 1");
		$stmt->execute(array(':c' => $code, ':r' => $role));
		$id = $stmt->fetchColumn();
		if ($id) {
			return $id;
		}
		return $db->query("SELECT MIN(usr_id) FROM tbl_user WHERE usr_type = 'A'")->fetchColumn();
	} catch (Exception $e) {
		fnLogError($e);
		return 1;
	}
}
