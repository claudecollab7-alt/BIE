<?php
// 2026-10-13 dashboard tiles - one queue per verify / approve job; the tile count and the page it opens use the same rule

// table, waiting rule, branch column (null = not per branch), page the tile opens, and for the shared
// pending page: id / ref / date / value columns and the screen where the job is done
function fnTaskQueues()
{
	return array(
		'PO_APPROVE' => array('task' => 'PURCHASE_ORDER', 'role' => 'A', 'label' => 'Purchase Order InApproval', 'icon' => 'icon-file-text2 icon-3x',
			'table' => 'tbl_purchase_order', 'where' => "po_status = 3", 'branch' => 'branch_id', 'page' => 'lst_direct_po_approval.php'),
		'SUPP_APPROVE' => array('task' => 'SUPPLIER', 'role' => 'A', 'label' => 'Supplier InApproval', 'icon' => 'icon-bag icon-3x',
			'table' => 'mst_supplier_new', 'where' => "supp_status = '1' AND supp_type = 'S' AND supp_approve_status = 0", 'branch' => 'company_branch_id', 'page' => 'lst_supplier.php?approve=0'),
		'QUO_APPROVE' => array('task' => 'QUOTATION', 'role' => 'A', 'label' => 'Quotation InApproval', 'icon' => 'icon-file-text2 icon-3x',
			'table' => 'tbl_quotation', 'where' => "quo_verify_status = 1 AND quo_approve_status = 0 AND quo_status = 1", 'branch' => 'bie_branch_id', 'page' => 'lst_quo_approval.php'),
		'SO_APPROVE' => array('task' => 'SALES_ORDER', 'role' => 'A', 'label' => 'Sales Order InApproval', 'icon' => 'icon-file-text2 icon-3x',
			'table' => 'tbl_sales_order', 'where' => "so_verify_status = 1 AND so_status = 1 AND so_approve_status = 0", 'branch' => 'bie_branch_id', 'page' => 'lst_pending.php?q=SO_APPROVE',
			'id' => 'so_id', 'ref' => 'so_refno', 'date' => 'so_date', 'value' => 'item_net_val', 'open' => 'print_sales_order.php?so_id='),
		'CREDIT_APPROVE' => array('task' => 'CREDIT_SALES', 'role' => 'A', 'label' => 'Credit Sales Order InApproval', 'icon' => 'icon-file-text2 icon-3x',
			'table' => 'tbl_sales_order', 'where' => "pay_status = 3", 'branch' => 'bie_branch_id', 'page' => 'lst_so_approval.php'),
		'PAY_CONFIRM' => array('task' => 'SALES_RECEIPT', 'role' => 'V', 'label' => 'Sales Order Payment to Confirm', 'icon' => 'fa fa-rupee', 'icon_size' => '36px',
			'table' => 'tbl_sales_order', 'where' => "accounts_status = 1 AND pay_status = 0 AND so_status = 1 AND so_cancel_status = 0", 'branch' => 'bie_branch_id', 'page' => 'lst_pending.php?q=PAY_CONFIRM',
			'id' => 'so_id', 'ref' => 'so_refno', 'date' => 'so_date', 'value' => 'item_net_val', 'open' => 'pay_receipt.php?so_id='),
		'DC_APPROVE' => array('task' => 'DC', 'role' => 'A', 'label' => 'Delivery Challan InApproval', 'icon' => 'icon-truck icon-3x',
			'table' => 'tbl_dc', 'where' => "dc_status > 0 AND dc_verify_status = 1 AND dc_approve_status = 0", 'branch' => 'branch_id', 'page' => 'lst_pending.php?q=DC_APPROVE',
			'id' => 'dc_id', 'ref' => 'dc_refno', 'date' => 'dc_date', 'value' => '', 'open' => 'dc_print.php?dc_id='),
		'PR_APPROVE' => array('task' => 'PURCHASE_RETURN', 'role' => 'A', 'label' => 'Purchase Return InApproval', 'icon' => 'icon-undo2 icon-3x',
			'table' => 'tbl_purchase_return', 'where' => "rtn_verify_status = 1 AND rtn_approve_status = 0", 'branch' => 'branch_id', 'page' => 'lst_pending.php?q=PR_APPROVE',
			'id' => 'rtn_id', 'ref' => 'rtn_refno', 'date' => 'rtn_date', 'value' => '', 'open' => 'purchase_return_view.php?rtn_id='),
		'REPAIR_SEND' => array('task' => 'SALES_REPAIR', 'role' => 'V', 'label' => 'Sales Repair to Send for Approval', 'icon' => 'icon-hammer-wrench icon-3x',
			'table' => 'tbl_sales_repair', 'where' => "sal_repair_status = '1' AND sal_repair_verify_status = 0", 'branch' => null, 'page' => 'lst_pending.php?q=REPAIR_SEND',
			'id' => 'sal_repair_id', 'ref' => 'sal_repair_slno', 'date' => 'sal_repair_date', 'value' => 'sal_repair_value', 'open' => 'sales_repair_print.php?sal_repair_id='),
		'REPAIR_APPROVE' => array('task' => 'SALES_REPAIR', 'role' => 'A', 'label' => 'Sales Repair InApproval', 'icon' => 'icon-hammer-wrench icon-3x',
			'table' => 'tbl_sales_repair', 'where' => "sal_repair_status = '1' AND sal_repair_verify_status = 1 AND sal_repair_approve_status = 0", 'branch' => null, 'page' => 'lst_pending.php?q=REPAIR_APPROVE',
			'id' => 'sal_repair_id', 'ref' => 'sal_repair_slno', 'date' => 'sal_repair_date', 'value' => 'sal_repair_value', 'open' => 'sales_repair_print.php?sal_repair_id='),
		'ITEM_APPROVE' => array('task' => 'ITEM', 'role' => 'A', 'label' => 'New Item InApproval', 'icon' => 'fas fa-database', 'icon_size' => '36px',
			'table' => 'tbl_item_details', 'where' => "branch_status = 0", 'branch' => null, 'page' => 'lst_item_approval.php'),
		'GRN_PAY' => array('task' => 'GRN_PAYMENT', 'role' => 'A', 'label' => 'GRN Payment Pending', 'icon' => 'fas fa-database', 'icon_size' => '36px',
			'table' => 'tbl_grn', 'where' => "grn_status = 2 AND grn_bill_status != '1' AND grn_pay_status != '1'", 'branch' => 'branch_id', 'page' => 'grn_payment_details_pending.php'),
	);
}

function fnTaskQueue($key)
{
	$q = fnTaskQueues();
	return isset($q[$key]) ? $q[$key] : null;
}

// branch rule for a queue as sql: '' every branch, ' AND col IN (..)' some, ' AND 1 = 0' none; $alias like 'a.'
function fnQueueScopeSql($key, $alias = '')
{
	$q = fnTaskQueue($key);
	if (!$q) {
		return ' AND 1 = 0';
	}
	$b = fnTaskBranches($q['task'], $q['role']);
	if ($b === null) {
		return '';
	}
	if (!$b) {
		return ' AND 1 = 0';
	}
	if ($q['branch'] === null) {
		return '';		// not kept per branch - a role for any branch covers it
	}
	return ' AND ' . $alias . $q['branch'] . ' IN (' . implode(',', array_map('intval', $b)) . ')';
}

// does the user hold the queue's job for any branch
function fnQueueVisible($key)
{
	$q = fnTaskQueue($key);
	return $q && fnTaskBranches($q['task'], $q['role']) !== array();
}

function fnQueueCount($conn, $key)
{
	$q = fnTaskQueue($key);
	return (int)$conn->query("SELECT COUNT(*) FROM " . $q['table'] . " WHERE " . $q['where'] . fnQueueScopeSql($key))->fetchColumn();
}
