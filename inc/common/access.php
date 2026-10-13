<?php
// 2026-10-13 access checks for approve / verify / reject actions

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

// user holds the menu right for this page (admin sees every menu)
function fnUserHasMenu($conn, $url)
{
	if (isset($_SESSION['_user_type']) && $_SESSION['_user_type'] == 'A') {
		return true;
	}
	$stmt = $conn->prepare("SELECT COUNT(*) FROM tbl_user_rights r JOIN mst_sub_menu s ON s.sm_id = r.sm_id
							WHERE r.usr_id = :u AND TRIM(s.sm_url) = :url");
	$stmt->execute(array(':u' => (int)$_SESSION['_user_id'], ':url' => $url));
	return $stmt->fetchColumn() > 0;
}

// user listed against a tbl_task_user row (comma list of user ids)
function fnTaskUserListed($conn, $task_id)
{
	$stmt = $conn->prepare("SELECT user_id FROM tbl_task_user WHERE task_id = :t");
	$stmt->execute(array(':t' => $task_id));
	$ids = array_map('trim', explode(',', (string)$stmt->fetchColumn()));
	return in_array((string)$_SESSION['_user_id'], $ids, true);
}
