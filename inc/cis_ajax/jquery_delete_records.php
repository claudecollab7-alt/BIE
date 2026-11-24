<?php
	ob_start();
	session_start();

	require_once("../common/dbconnect.php");
	require_once("../common/csrf.php");
	require_once("../common/access.php");

	// 2026-10-13 login + csrf; only the table / column pairs the screens use, status set to 0, id bound
	//            - it took table, column and value from the post with no login, so any row of any table could be changed
	// 2026-10-23 a principal or brand still used by items cannot be deleted
	// 2026-11-24 an item with stock or on this year's quotation / sales order cannot be deleted (it went blank on the DC);
	//            item delete needs Task Settings ITEM creator, as the delete link on the list

	fnAjaxGuard();

	// table => array(status column, id column) - the delete links on the list and master screens
	$allowed = array(
		'mst_branch' => array('branch_status', 'branch_id'),
		'mst_brand' => array('brand_status', 'brand_id'),
		'mst_category' => array('category_status', 'category_id'),
		'mst_city' => array('city_status', 'city_id'),
		'mst_color' => array('color_status', 'color_id'),
		'mst_department' => array('rec_del_status', 'department_id'),
		'mst_designation' => array('rec_del_status', 'designation_id'),
		'mst_district' => array('district_status', 'district_id'),
		'mst_division' => array('division_status', 'division_id'),
		'mst_employee' => array('rec_del_status', 'emp_id'),
		'mst_hsn' => array('hsn_status', 'hsn_id'),
		'mst_labour' => array('rec_del_status', 'labour_id'),
		'mst_principal' => array('principal_status', 'principal_id'),
		'mst_salary_setting' => array('rec_del_status', 'sal_id'),
		'mst_state' => array('state_status', 'state_id'),
		'mst_supplier' => array('supp_status', 'supp_id'),
		'mst_supplier_new' => array('supp_status', 'supp_id'),
		'mst_uom' => array('uom_status', 'uom_id'),
		'tbl_dc_package_box' => array('box_status', 'box_id'),
		'tbl_item_details' => array('item_status', 'item_id'),
		'tbl_item_group' => array('status', 'item_group_id'),
		'tbl_month_master' => array('month_master_status', 'auto_id'),
		'tbl_store_indent' => array('si_status', 'si_id'),
		'tbl_user' => array('usr_status', 'usr_id'),
	);

	$delete_id = isset($_POST["id"]) ? $_POST["id"] : '';
	$table = isset($_POST["table"]) ? $_POST["table"] : '';
	$status = isset($_POST["status"]) ? $_POST["status"] : '';
	$where = isset($_POST["where"]) ? $_POST["where"] : '';

	if (!isset($allowed[$table]) || $allowed[$table][0] !== $status || $allowed[$table][1] !== $where) {
		fnAccessDenied(true);
	}

	// masters that items point to: table => array(item column, what it is)
	$in_use = array(
		'mst_principal' => array('item_principal', 'principal'),
		'mst_brand' => array('item_brand_make', 'brand'),
	);

	if ($delete_id !== '')
	{
		try
		{
			$conn = new dbconnect();
			if (isset($in_use[$table])) {
				$used = $conn->prepare("SELECT COUNT(*) FROM tbl_item_details WHERE item_status = 1 AND `" . $in_use[$table][0] . "` = :id");
				$used->execute(array(':id' => $delete_id));
				$n = (int)$used->fetchColumn();
				if ($n > 0) {
					http_response_code(409);
					die("This " . $in_use[$table][1] . " is used by " . $n . " item(s) - it cannot be deleted..!");
				}
			}
			if ($table == 'tbl_item_details') {
				if (!fnTaskCan('ITEM', 'C')) {
					fnAccessDenied(true);
				}
				// 2026-11-24 still in use: stock in any branch, or on a quotation / sales order of the active year that is not
				// cancelled - a deleted item showed blank on the DC and printed documents of those orders
				$why = array();
				$fields = $conn->query("SELECT branch_stock_field FROM mst_branch WHERE branch_stock_field <> ''")->fetchAll(PDO::FETCH_COLUMN);
				$fields = array_filter($fields, function ($f) { return preg_match('/^[a-z_]{1,30}$/', $f); });
				if (count($fields) > 0) {
					$q = $conn->prepare("SELECT SUM(" . implode(' + ', array_map(function ($f) { return "ABS(IFNULL(`" . $f . "`, 0))"; }, $fields)) . ") FROM tbl_item_stock WHERE item_id = :id");
					$q->execute(array(':id' => $delete_id));
					if ((float)$q->fetchColumn() > 0) {
						$why[] = "it has stock";
					}
				}
				$finyr = $conn->query("SELECT finyr FROM mst_finyear WHERE finyr_active = 1 LIMIT 1")->fetchColumn();
				$q = $conn->prepare("SELECT GROUP_CONCAT(DISTINCT s.so_refno ORDER BY s.so_id SEPARATOR ', ') FROM tbl_sales_order_details d
									 JOIN tbl_sales_order s ON s.so_id = d.so_id
									 WHERE d.item_id = :id AND s.so_finyr = :f AND s.so_cancel_status <> 1");
				$q->execute(array(':id' => $delete_id, ':f' => $finyr));
				$sos = (string)$q->fetchColumn();
				if ($sos != '') {
					$why[] = "it is on sales order " . (strlen($sos) > 120 ? substr($sos, 0, 120) . '...' : $sos);
				}
				$q = $conn->prepare("SELECT COUNT(DISTINCT d.quo_id) FROM tbl_quotation_details d JOIN tbl_quotation q ON q.quo_id = d.quo_id
									 WHERE d.item_id = :id AND q.quo_finyr = :f AND q.rec_del_status = 0");
				$q->execute(array(':id' => $delete_id, ':f' => $finyr));
				$nq = (int)$q->fetchColumn();
				if ($nq > 0) {
					$why[] = "it is on " . $nq . " quotation(s) this year";
				}
				if (count($why) > 0) {
					die("This item cannot be deleted - " . implode('; ', $why) . "..!");
				}
			}
			$stmt = $conn->prepare("UPDATE `" . $table . "` SET `" . $status . "` = 0 WHERE `" . $where . "` = :id");
			$stmt->execute(array(':id' => $delete_id));
			echo 1;
		}
		catch(Exception $e)
		{
			fnLogError($e);
			$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
			echo $str;
		}

		$conn=null;
	}

?>
