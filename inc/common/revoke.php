<?php
// 2026-10-15 new - revoke a wrong approve / verify (Task Settings role R, admin only until users are given it)
// one entry per form: state it must be in, what blocks it, the state it goes back to; every revoke is logged in tbl_revoke_log
// approve / verify never moves stock (stock moves at GRN and invoice), so a revoke is refused once those are made

function fnRevokeDocs()
{
	return array(
		// purchase order: approved -> draft, creator edits and sends again
		'PO' => array(
			'task' => 'PURCHASE_ORDER', 'table' => 'tbl_purchase_order', 'id' => 'po_id', 'ref' => 'po_refno', 'branch' => 'branch_id',
			'state' => "po_status = 5 AND po_approve_status = 1",
			'cols' => array('po_status', 'po_approve_status', 'po_approve_by', 'po_approve_date_time', 'po_approve_remarks'),
			'set' => "po_status = 0, po_approve_status = 0, po_approve_by = 0, po_approve_remarks = ''",
			'blocks' => array(
				array("SELECT COUNT(*) FROM tbl_grn WHERE po_id = :id AND grn_cancel_status != 1", "A GRN is made against this PO - cancel the GRN first..!"),
			),
			'done' => "PO approval revoked - back to draft..!",
		),
		// supplier: approved -> not approved, only while no purchase document uses it
		'SUPPLIER' => array(
			'task' => 'SUPPLIER', 'table' => 'mst_supplier_new', 'id' => 'supp_id', 'ref' => 'supp_name', 'branch' => 'company_branch_id',
			'state' => "supp_approve_status = 1",
			'cols' => array('supp_approve_status', 'supp_approve_by', 'supp_approve_dt'),
			'set' => "supp_approve_status = 0, supp_approve_by = 0",
			'blocks' => array(
				array("SELECT COUNT(*) FROM tbl_purchase_order WHERE supp_id = :id", "This supplier is used in a PO - it cannot be revoked..!"),
				array("SELECT COUNT(*) FROM tbl_grn WHERE supp_id = :id", "This supplier is used in a GRN - it cannot be revoked..!"),
				array("SELECT COUNT(*) FROM tbl_purchase_return WHERE supp_id = :id", "This supplier is used in a purchase return - it cannot be revoked..!"),
			),
			'done' => "Supplier approval revoked - back to not approved..!",
		),
		// purchase return: approved -> draft (a rejected return cannot be edited, so not via reject)
		'PR' => array(
			'task' => 'PURCHASE_RETURN', 'table' => 'tbl_purchase_return', 'id' => 'rtn_id', 'ref' => 'rtn_refno', 'branch' => 'branch_id',
			'state' => "rtn_verify_status = 1 AND rtn_approve_status = 1",
			'cols' => array('rtn_verify_status', 'rtn_verify_by', 'rtn_approve_status', 'rtn_approve_by', 'rtn_approve_date_time', 'rtn_approve_remarks'),
			'set' => "rtn_verify_status = 0, rtn_approve_status = 0, rtn_approve_by = 0, rtn_approve_remarks = ''",
			'blocks' => array(),
			'done' => "Purchase return approval revoked - back to draft..!",
		),
		// quotation: approved -> draft, only while nothing is made from it
		'QUO' => array(
			'task' => 'QUOTATION', 'table' => 'tbl_quotation', 'id' => 'quo_id', 'ref' => 'quo_refno', 'branch' => 'bie_branch_id',
			'state' => "quo_status = 1 AND rec_del_status = 0 AND quo_verify_status = 1 AND quo_approve_status = 1",
			'cols' => array('quo_verify_status', 'quo_approve_status', 'quo_approve_id', 'quo_approve_date_time', 'quo_approve_remarks'),
			'set' => "quo_verify_status = 0, quo_approve_status = 0, quo_approve_remarks = ''",
			'blocks' => array(
				array("SELECT COUNT(*) FROM tbl_quotation WHERE quo_id = :id AND (so_gen_status = 1 OR quo_so_id > 0)", "A sales order is made from this quotation - it cannot be revoked..!"),
				array("SELECT COUNT(*) FROM tbl_sales_order WHERE quo_id = :id", "A sales order is made from this quotation - it cannot be revoked..!"),
				array("SELECT COUNT(*) FROM tbl_quotation WHERE quo_id = :id AND (inv_gen_status = 1 OR quo_inv_id > 0)", "An invoice is made from this quotation - it cannot be revoked..!"),
				array("SELECT COUNT(*) FROM tbl_invoice WHERE quo_id = :id", "An invoice is made from this quotation - it cannot be revoked..!"),
				array("SELECT COUNT(*) FROM tbl_proforma WHERE quo_id = :id", "A proforma invoice is made from this quotation - it cannot be revoked..!"),
				array("SELECT COUNT(*) FROM tbl_quotation WHERE prev_quo_id = :id AND rec_del_status = 0", "A newer version (requote) of this quotation exists - it cannot be revoked..!"),
			),
			'done' => "Quotation approval revoked - back to draft..!",
		),
		// sales order approve: approved -> in approval again (payment / credit stay as they are); reject from there sends it back for editing
		'SO' => array(
			'task' => 'SALES_ORDER', 'table' => 'tbl_sales_order', 'id' => 'so_id', 'ref' => 'so_refno', 'branch' => 'bie_branch_id',
			'state' => "so_status = 5 AND so_approve_status = 1 AND so_cancel_status = 0",
			'cols' => array('so_status', 'so_approve_status', 'so_approve_by', 'so_approve_date_time', 'pay_status'),
			'set' => "so_status = 1, so_approve_status = IF(pay_status = 4, 3, 0), so_approve_by = IF(pay_status = 4, so_approve_by, 0)",
			'blocks' => array(
				array("SELECT COUNT(*) FROM tbl_dc WHERE so_id = :id AND dc_status = 1", "A DC is made for this sales order - it cannot be revoked..!"),
				array("SELECT COUNT(*) FROM tbl_invoice WHERE so_id = :id", "An invoice is made for this sales order - it cannot be revoked..!"),
			),
			'done' => "Sales order approval revoked - back to in approval..!",
		),
		// credit approval: approved -> credit requested, only before the sales order itself is approved
		'CREDIT' => array(
			'task' => 'CREDIT_SALES', 'table' => 'tbl_sales_order', 'id' => 'so_id', 'ref' => 'so_refno', 'branch' => 'bie_branch_id',
			'state' => "so_status = 1 AND so_approve_status = 3 AND pay_status = 4 AND so_cancel_status = 0",
			'cols' => array('pay_status', 'so_approve_status', 'so_approve_by', 'so_approve_date_time', 'so_credit_days', 'so_credit_due_date', 'so_credit_approval_remarks'),
			'set' => "pay_status = 3, so_approve_status = 0, so_approve_by = 0, so_credit_days = 0, so_credit_approval_remarks = ''",
			'blocks' => array(
				array("SELECT COUNT(*) FROM tbl_dc WHERE so_id = :id AND dc_status = 1", "A DC is made for this sales order - it cannot be revoked..!"),
				array("SELECT COUNT(*) FROM tbl_invoice WHERE so_id = :id", "An invoice is made for this sales order - it cannot be revoked..!"),
			),
			'wrong_state' => array(
				array("SELECT COUNT(*) FROM tbl_sales_order WHERE so_id = :id AND so_status = 5", "The sales order is approved - revoke the sales order approval first..!"),
			),
			'done' => "Credit approval revoked - back to credit requested..!",
		),
		// payment confirm / mark as credit: back to payment pending, only before credit or sales order approval
		'PAY' => array(
			'task' => 'SALES_RECEIPT', 'table' => 'tbl_sales_order', 'id' => 'so_id', 'ref' => 'so_refno', 'branch' => 'bie_branch_id',
			'state' => "so_status = 1 AND so_verify_status = 1 AND pay_status IN (1, 2, 3) AND so_approve_status = 0 AND so_cancel_status = 0",
			'cols' => array('pay_status', 'so_verify_status', 'so_verify_by', 'so_verify_date_time', 'so_user_approve_by', 'accounts_verify_status', 'accounts_by', 'pay_remarks'),
			'set' => "pay_status = 0, so_verify_status = 0, so_verify_by = 0, so_user_approve_by = '', accounts_verify_status = 0, accounts_by = 0",
			'blocks' => array(
				array("SELECT COUNT(*) FROM tbl_dc WHERE so_id = :id AND dc_status = 1", "A DC is made for this sales order - it cannot be revoked..!"),
				array("SELECT COUNT(*) FROM tbl_invoice WHERE so_id = :id", "An invoice is made for this sales order - it cannot be revoked..!"),
			),
			'wrong_state' => array(
				array("SELECT COUNT(*) FROM tbl_sales_order WHERE so_id = :id AND so_status = 5", "The sales order is approved - revoke the sales order approval first..!"),
				array("SELECT COUNT(*) FROM tbl_sales_order WHERE so_id = :id AND pay_status = 4", "Credit is approved - revoke the credit approval first..!"),
			),
			'done' => "Payment confirm revoked - back to payment pending..!",
		),
		// delivery challan: approved -> draft, only before it is invoiced
		'DC' => array(
			'task' => 'DC', 'table' => 'tbl_dc', 'id' => 'dc_id', 'ref' => 'dc_refno', 'branch' => 'branch_id',
			'state' => "dc_status = 1 AND dc_verify_status = 1 AND dc_approve_status = 1",
			'cols' => array('dc_verify_status', 'dc_verify_by', 'dc_approve_status', 'dc_approve_by', 'dc_approve_date_time', 'dc_remarks'),
			'set' => "dc_verify_status = 0, dc_approve_status = 0, dc_approve_by = 0",
			'blocks' => array(
				array("SELECT COUNT(*) FROM tbl_dc WHERE dc_id = :id AND (dc_inv_status = 1 OR dc_inv_id > 0)", "This DC is invoiced - it cannot be revoked..!"),
				array("SELECT COUNT(*) FROM tbl_invoice WHERE dc_id = :id", "This DC is invoiced - it cannot be revoked..!"),
			),
			'done' => "DC approval revoked - back to draft..!",
		),
		// sales repair approve: approved -> in approval
		'REPAIR_A' => array(
			'task' => 'SALES_REPAIR', 'table' => 'tbl_sales_repair', 'id' => 'sal_repair_id', 'ref' => 'sal_repair_slno', 'branch' => 'company_id',
			'state' => "sal_repair_verify_status = 1 AND sal_repair_approve_status = 1",
			'cols' => array('sal_repair_approve_status', 'sal_repair_approve_by', 'sal_repair_approve_date_time'),
			'set' => "sal_repair_approve_status = 0, sal_repair_approve_by = 0",
			'blocks' => array(),
			'done' => "Sales repair approval revoked - back to in approval..!",
		),
		// sales repair send for approval: in approval -> draft
		'REPAIR_V' => array(
			'task' => 'SALES_REPAIR', 'table' => 'tbl_sales_repair', 'id' => 'sal_repair_id', 'ref' => 'sal_repair_slno', 'branch' => 'company_id',
			'state' => "sal_repair_verify_status = 1 AND sal_repair_approve_status = 0",
			'cols' => array('sal_repair_verify_status', 'sal_repair_verify_by', 'sal_repair_verify_date_time'),
			'set' => "sal_repair_verify_status = 0, sal_repair_verify_by = 0",
			'blocks' => array(),
			'wrong_state' => array(
				array("SELECT COUNT(*) FROM tbl_sales_repair WHERE sal_repair_id = :id AND sal_repair_approve_status = 1", "The repair is approved - revoke the approval first..!"),
			),
			'done' => "Send for approval revoked - back to draft..!",
		),
		// salary issue: issued -> not issued, its accounts entry removed so it can be issued again
		'SALARY' => array(
			'task' => 'SALARY', 'table' => 'tbl_emp_monthly_salary', 'id' => 'emp_sal_id', 'ref' => "CONCAT(salary_for, ' ', salary_type)",
			'branch_sql' => "SELECT e.branch_id FROM tbl_emp_monthly_salary s JOIN mst_employee e ON e.emp_id = s.emp_id WHERE s.emp_sal_id = :id",
			'state' => "salary_status = 1",
			'cols' => array('emp_id', 'salary_for', 'salary_type', 'salary_status', 'net_amount', 'total_ot_amount', 'issued_by', 'issued_dtm'),
			'set' => "salary_status = 0",
			'also' => array("DELETE FROM tbl_accounts WHERE emp_sal_id = :id"),
			'blocks' => array(),
			'not_now' => "Salary is not issued now - it may already be revoked..!",
			'done' => "Salary issue revoked - it can be issued again..!",
		),
	);
}

function fnRevokeDef($doc)
{
	$d = fnRevokeDocs();
	return isset($d[$doc]) ? $d[$doc] : null;
}

// branch the document belongs to - the Revoker row must cover it
function fnRevokeBranch($conn, $doc, $id)
{
	$def = fnRevokeDef($doc);
	if (!$def) {
		return null;
	}
	$sql = isset($def['branch_sql']) ? $def['branch_sql'] : "SELECT " . $def['branch'] . " FROM " . $def['table'] . " WHERE " . $def['id'] . " = :id";
	$stmt = $conn->prepare($sql);
	$stmt->execute(array(':id' => (int)$id));
	$b = $stmt->fetchColumn();
	return $b === false ? null : $b;
}

function fnRevokeCan($doc, $branch_id)
{
	$def = fnRevokeDef($doc);
	return $def && $branch_id !== null && fnTaskCan($def['task'], 'R', $branch_id);
}

// the button placed where approve / verify was - shown only to a Revoker of the document's branch
function fnRevokeButton($doc, $id, $branch_id, $label = 'Revoke', $class = 'btn btn-danger mr-2')
{
	if (!fnRevokeCan($doc, $branch_id)) {
		return '';
	}
	return '<a href="javascript:;" class="revoke-btn ' . $class . '" data-doc="' . htmlspecialchars($doc) . '" data-id="' . (int)$id . '" title="Revoke - undo this by mistake">' . htmlspecialchars($label) . '</a>';
}

// last revoke on a document, for the print / view pages
function fnRevokeNote($conn, $doc, $id)
{
	$stmt = $conn->prepare("SELECT l.revoked_dtm, l.revoke_reason, u.usr_name FROM tbl_revoke_log l LEFT JOIN tbl_user u ON u.usr_id = l.revoked_by
							WHERE l.revoke_doc = :d AND l.doc_id = :id ORDER BY l.revoke_id DESC LIMIT 1");
	$stmt->execute(array(':d' => $doc, ':id' => (int)$id));
	$r = $stmt->fetch(PDO::FETCH_OBJ);
	if (!$r) {
		return '';
	}
	return '<small class="text-danger d-block">Revoked by ' . htmlspecialchars($r->usr_name) . ' on ' . date('d-m-Y H:i', strtotime($r->revoked_dtm))
		. ($r->revoke_reason != '' ? ' - ' . htmlspecialchars($r->revoke_reason) : '') . '</small>';
}

// does the revoke in one transaction; true when done, else the message saying why not
function fnRevokeRun($conn, $doc, $id, $reason)
{
	$def = fnRevokeDef($doc);
	$id = (int)$id;
	$p = array(':id' => $id);
	try {
		db_begin($conn);
		$stmt = $conn->prepare("SELECT " . implode(', ', $def['cols']) . ", " . $def['ref'] . " AS doc_ref FROM " . $def['table']
			. " WHERE " . $def['id'] . " = :id AND " . $def['state'] . " FOR UPDATE");
		$stmt->execute($p);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$row) {
			$msg = isset($def['not_now']) ? $def['not_now'] : "Not approved now - it may already be revoked..!";
			foreach (isset($def['wrong_state']) ? $def['wrong_state'] : array() as $w) {
				if ((int)db_value($conn, $w[0], $p) > 0) {
					$msg = $w[1];
					break;
				}
			}
			db_rollback($conn);
			return $msg;
		}
		foreach ($def['blocks'] as $b) {
			if ((int)db_value($conn, $b[0], $p) > 0) {
				db_rollback($conn);
				return $b[1];
			}
		}
		$upd = $conn->prepare("UPDATE " . $def['table'] . " SET " . $def['set'] . " WHERE " . $def['id'] . " = :id AND " . $def['state']);
		$upd->execute($p);
		if ($upd->rowCount() != 1) {
			db_rollback($conn);
			return "Not approved now - it may already be revoked..!";
		}
		foreach (isset($def['also']) ? $def['also'] : array() as $sql) {
			$conn->prepare($sql)->execute($p);
		}
		$doc_ref = $row['doc_ref'];
		unset($row['doc_ref']);
		$log = $conn->prepare("INSERT INTO tbl_revoke_log (revoke_doc, task_code, doc_id, doc_ref, branch_id, old_values, revoke_reason, revoked_by, revoked_dtm)
							   VALUES (:doc, :task, :id, :ref, :branch, :old, :reason, :by, :dtm)");
		$log->execute(array(':doc' => $doc, ':task' => $def['task'], ':id' => $id, ':ref' => (string)$doc_ref,
			':branch' => (int)fnRevokeBranch($conn, $doc, $id), ':old' => json_encode($row), ':reason' => $reason,
			':by' => (int)$_SESSION['_user_id'], ':dtm' => date('Y-m-d H:i:s')));
		db_commit($conn);
		return true;
	} catch (Exception $e) {
		db_rollback($conn);
		fnLogError($e);
		return "Revoke failed - please try again..!";
	}
}
