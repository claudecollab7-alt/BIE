<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
require_once("../common/csrf.php");
require_once("../common/access.php");

// 2026-10-22 new - purchase order items with their stage (GRN / return), for the status popup on the purchase order report
// received / accepted / rejected from finalised GRNs (po_qty repeats the PO qty on every GRN line); still to receive = ordered - accepted

fnAjaxGuard();

$conn = new dbconnect();
$po_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
$q = function ($v) { return rtrim(rtrim(number_format((float)$v, 3, '.', ''), '0'), '.'); };

$po = $conn->prepare("SELECT a.po_id, a.po_refno, a.po_date, a.branch_id, b.supp_name FROM tbl_purchase_order a LEFT JOIN mst_supplier_new b ON b.supp_id = a.supp_id WHERE a.po_id = :id");
$po->execute(array(':id' => $po_id));
$po = $po->fetch(PDO::FETCH_OBJ);
if (!$po) {
	http_response_code(404);
	die('Purchase order not found..!');
}
$stock_field = $conn->query("SELECT branch_stock_field FROM mst_branch WHERE branch_id = " . (int)$po->branch_id)->fetchColumn();

// GRN lines of the PO: finalised ones count, drafts shown apart
$grns = array();
$st = $conn->prepare("SELECT gd.grn_item_id, gd.grn_recived_qty, gd.grn_accepted_qty, gd.grn_rejected_qty, g.grn_id, g.grn_ref_code, g.grn_status
					  FROM tbl_grn g JOIN tbl_grn_details gd ON gd.grn_id = g.grn_id
					  WHERE g.po_id = :id AND g.grn_cancel_status <> 1 AND gd.grn_recived_qty > 0 ORDER BY g.grn_id");
$st->execute(array(':id' => $po_id));
foreach ($st->fetchAll(PDO::FETCH_OBJ) as $r) {
	$i = (int)$r->grn_item_id;
	if (!isset($grns[$i])) {
		$grns[$i] = array('received' => 0, 'accepted' => 0, 'rejected' => 0, 'draft' => 0, 'refs' => array());
	}
	if ($r->grn_status == 2) {
		$grns[$i]['received'] += (float)$r->grn_recived_qty;
		$grns[$i]['accepted'] += (float)$r->grn_accepted_qty;
		$grns[$i]['rejected'] += (float)$r->grn_rejected_qty;
	} else {
		$grns[$i]['draft'] += (float)$r->grn_recived_qty;
	}
	$grns[$i]['refs'][] = '<a href="grn_view.php?grn_id=' . (int)$r->grn_id . '" target="_blank">' . $h($r->grn_ref_code) . '</a> (' . $q($r->grn_recived_qty)
		. ((float)$r->grn_rejected_qty > 0 ? ', ' . $q($r->grn_rejected_qty) . ' rejected' : '') . ($r->grn_status == 2 ? '' : ', draft') . ')';
}

// approved purchase returns of the PO
$rtns = array();
$st = $conn->prepare("SELECT d.rtn_item_id, d.rtn_rejected_qty, r.rtn_id, r.rtn_refno FROM tbl_purchase_return r JOIN tbl_purchase_return_details d ON d.rtn_id = r.rtn_id
					  WHERE r.po_id = :id AND r.rtn_status = 1 AND r.rtn_approve_status = 1 AND d.rtn_rejected_qty > 0 ORDER BY r.rtn_id");
$st->execute(array(':id' => $po_id));
foreach ($st->fetchAll(PDO::FETCH_OBJ) as $r) {
	$i = (int)$r->rtn_item_id;
	if (!isset($rtns[$i])) {
		$rtns[$i] = array('qty' => 0, 'refs' => array());
	}
	$rtns[$i]['qty'] += (float)$r->rtn_rejected_qty;
	$rtns[$i]['refs'][] = '<a href="purchase_return_view.php?rtn_id=' . (int)$r->rtn_id . '" target="_blank">' . $h($r->rtn_refno) . '</a> (' . $q($r->rtn_rejected_qty) . ')';
}

// one row per item - a PO can list the same item twice
$st = $conn->prepare("SELECT p.item_id, SUM(p.po_qty) AS po_qty, MAX(p.po_unit) AS po_unit, d.item_code, d.item_desciption" . ($stock_field ? ", IFNULL(k." . $stock_field . ", 0) AS stock" : ", 0 AS stock") . "
					  FROM tbl_purchase_order_details p LEFT JOIN tbl_item_details d ON d.item_id = p.item_id LEFT JOIN tbl_item_stock k ON k.item_id = p.item_id
					  WHERE p.po_id = :id GROUP BY p.item_id ORDER BY MIN(p.details_id)");
$st->execute(array(':id' => $po_id));
$rows = '';
$n = $pending = 0;
foreach ($st->fetchAll(PDO::FETCH_OBJ) as $r) {
	$i = (int)$r->item_id;
	$g = isset($grns[$i]) ? $grns[$i] : array('received' => 0, 'accepted' => 0, 'rejected' => 0, 'draft' => 0, 'refs' => array());
	$ordered = (float)$r->po_qty;
	$returned = isset($rtns[$i]) ? $rtns[$i]['qty'] : 0;
	$to_receive = max(0, $ordered - $g['accepted']);
	$to_return = max(0, $g['rejected'] - $returned);

	if ($g['received'] + $g['draft'] <= 0) {
		$stage = '<span class="badge bg-warning">Not received</span>';
	} elseif ($g['draft'] > 0) {
		$stage = '<span class="badge bg-info">GRN in draft</span>';
	} elseif ($to_receive > 0) {
		$stage = '<span class="badge bg-grey">Part received</span>';
	} elseif ($to_return > 0) {
		$stage = '<span class="badge bg-danger">Rejected, not returned</span>';
	} else {
		$stage = '<span class="badge bg-success">Completed</span>';
	}
	$is_pending = $to_receive > 0 || $g['draft'] > 0 || $to_return > 0;
	$pending += $is_pending ? 1 : 0;
	$n++;
	$rows .= '<tr class="' . ($is_pending ? 'doc-pending' : 'doc-done') . '">'
		. '<td>' . $n . '</td>'
		. '<td><b>' . $h($r->item_code) . '</b><br><small>' . $h($r->item_desciption) . '</small></td>'
		. '<td class="text-right">' . $q($ordered) . ' <small>' . $h($r->po_unit) . '</small></td>'
		. '<td class="text-right">' . $q($g['received']) . ($g['draft'] > 0 ? '<br><small class="text-info">+' . $q($g['draft']) . ' in draft</small>' : '') . '</td>'
		. '<td class="text-right">' . $q($g['accepted']) . '</td>'
		. '<td class="text-right' . ($g['rejected'] > 0 ? ' text-danger' : '') . '">' . $q($g['rejected']) . '</td>'
		. '<td class="text-right">' . $q($returned) . '</td>'
		. '<td class="text-right' . ($to_receive > 0 ? ' text-danger font-weight-bold' : '') . '">' . $q($to_receive) . '</td>'
		. '<td class="text-right">' . $q($r->stock) . '</td>'
		. '<td>' . $stage . '</td>'
		. '<td><small>' . ($g['refs'] ? implode('<br>', $g['refs']) : '-') . '</small></td>'
		. '<td><small>' . (isset($rtns[$i]) ? implode('<br>', $rtns[$i]['refs']) : '-') . '</small></td>'
		. '</tr>';
}
?>
<div class="d-flex flex-wrap justify-content-between mb-2">
	<div><b><?php echo $h($po->po_refno); ?></b> &nbsp; <?php echo date('d-m-Y', strtotime($po->po_date)); ?> &nbsp; <?php echo $h($po->supp_name); ?></div>
	<div><span class="badge bg-warning"><?php echo $pending; ?> pending</span> <span class="badge bg-success"><?php echo $n - $pending; ?> completed</span> of <?php echo $n; ?> item(s)</div>
</div>
<table class="table table-xs table-bordered" id="docItemTable">
	<thead>
		<tr class="bg-table-header">
			<th>#</th>
			<th>Item</th>
			<th class="text-right">Ordered</th>
			<th class="text-right">Received</th>
			<th class="text-right">Accepted</th>
			<th class="text-right">Rejected</th>
			<th class="text-right">Returned</th>
			<th class="text-right">To Receive</th>
			<th class="text-right">Stock</th>
			<th>Stage</th>
			<th>GRN No. (qty)</th>
			<th>Return No. (qty)</th>
		</tr>
	</thead>
	<tbody>
		<?php echo $rows; ?>
		<tr class="doc-none"><td colspan="12" class="text-center text-muted">No pending items - tick Show all items to see them all.</td></tr>
	</tbody>
</table>
