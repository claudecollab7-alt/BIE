<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
require_once("../common/csrf.php");
require_once("../common/access.php");

// 2026-10-22 new - sales order items with their stage (DC / invoice), for the status popup on the sales order report
// dispatched = dc_dispatch_qty (dc_qty holds the ordered qty again on every DC); invoiced from the SO's invoices

fnAjaxGuard();

$conn = new dbconnect();
$so_id = isset($_POST['so_id']) ? (int)$_POST['so_id'] : 0;
$h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
$q = function ($v) { return rtrim(rtrim(number_format((float)$v, 3, '.', ''), '0'), '.'); };

$so = $conn->prepare("SELECT a.so_id, a.so_refno, a.so_date, a.bie_branch_id, b.supp_name FROM tbl_sales_order a LEFT JOIN mst_supplier_new b ON b.supp_id = a.supp_id WHERE a.so_id = :id");
$so->execute(array(':id' => $so_id));
$so = $so->fetch(PDO::FETCH_OBJ);
if (!$so) {
	http_response_code(404);
	die('Sales order not found..!');
}
$stock_field = $conn->query("SELECT branch_stock_field FROM mst_branch WHERE branch_id = " . (int)$so->bie_branch_id)->fetchColumn();

// DC lines of the SO: per item, approved and not yet approved, with the DC numbers
$dcs = array();
$st = $conn->prepare("SELECT dd.dc_item_id, dd.dc_dispatch_qty, d.dc_id, d.dc_refno, d.dc_approve_status FROM tbl_dc d JOIN tbl_dc_details dd ON dd.dc_id = d.dc_id
					  WHERE d.so_id = :id AND d.dc_status = 1 AND dd.dc_dispatch_qty > 0 ORDER BY d.dc_id");
$st->execute(array(':id' => $so_id));
foreach ($st->fetchAll(PDO::FETCH_OBJ) as $r) {
	$i = (int)$r->dc_item_id;
	if (!isset($dcs[$i])) {
		$dcs[$i] = array('approved' => 0, 'waiting' => 0, 'refs' => array());
	}
	$dcs[$i][$r->dc_approve_status == 1 ? 'approved' : 'waiting'] += (float)$r->dc_dispatch_qty;
	$dcs[$i]['refs'][] = '<a href="dc_print.php?dc_id=' . (int)$r->dc_id . '" target="_blank">' . $h($r->dc_refno) . '</a> (' . $q($r->dc_dispatch_qty) . ($r->dc_approve_status == 1 ? '' : ', not approved') . ')';
}

// invoice lines of the SO
$invs = array();
$st = $conn->prepare("SELECT x.item_id, x.inv_qty, i.inv_id, i.inv_refno FROM tbl_invoice i JOIN tbl_invoice_details x ON x.inv_id = i.inv_id
					  WHERE i.so_id = :id AND i.inv_status = 1 ORDER BY i.inv_id");
$st->execute(array(':id' => $so_id));
foreach ($st->fetchAll(PDO::FETCH_OBJ) as $r) {
	$i = (int)$r->item_id;
	if (!isset($invs[$i])) {
		$invs[$i] = array('qty' => 0, 'refs' => array());
	}
	$invs[$i]['qty'] += (float)$r->inv_qty;
	$invs[$i]['refs'][] = '<a href="quo_invoice_print.php?inv_id=' . (int)$r->inv_id . '" target="_blank">' . $h($r->inv_refno) . '</a> (' . $q($r->inv_qty) . ')';
}

$st = $conn->prepare("SELECT s.item_id, s.so_qty, s.so_unit, d.item_code, d.item_desciption" . ($stock_field ? ", IFNULL(k." . $stock_field . ", 0) AS stock" : ", 0 AS stock") . "
					  FROM tbl_sales_order_details s LEFT JOIN tbl_item_details d ON d.item_id = s.item_id LEFT JOIN tbl_item_stock k ON k.item_id = s.item_id
					  WHERE s.so_id = :id ORDER BY s.so_details_id");
$st->execute(array(':id' => $so_id));
$rows = '';
$n = $pending = 0;
foreach ($st->fetchAll(PDO::FETCH_OBJ) as $r) {
	$i = (int)$r->item_id;
	$ordered = (float)$r->so_qty;
	$dc_ok = isset($dcs[$i]) ? $dcs[$i]['approved'] : 0;
	$dc_wait = isset($dcs[$i]) ? $dcs[$i]['waiting'] : 0;
	$invoiced = isset($invs[$i]) ? $invs[$i]['qty'] : 0;
	$to_dc = max(0, $ordered - $dc_ok - $dc_wait);
	$to_inv = max(0, $dc_ok - $invoiced);

	if ($dc_ok + $dc_wait <= 0) {
		$stage = '<span class="badge bg-warning">Not dispatched</span>';
	} elseif ($dc_wait > 0) {
		$stage = '<span class="badge bg-info">DC in approval</span>';
	} elseif ($to_dc > 0) {
		$stage = '<span class="badge bg-grey">Part dispatched</span>';
	} elseif ($to_inv > 0) {
		$stage = '<span class="badge bg-primary">' . ($invoiced > 0 ? 'Part invoiced' : 'Dispatched, not invoiced') . '</span>';
	} else {
		$stage = '<span class="badge bg-success">Completed</span>';
	}
	$is_pending = $to_dc > 0 || $dc_wait > 0 || $to_inv > 0;
	$pending += $is_pending ? 1 : 0;
	$n++;
	$rows .= '<tr class="' . ($is_pending ? 'so-pending' : 'so-done') . '">'
		. '<td>' . $n . '</td>'
		. '<td><b>' . $h($r->item_code) . '</b><br><small>' . $h($r->item_desciption) . '</small></td>'
		. '<td class="text-right">' . $q($ordered) . ' <small>' . $h($r->so_unit) . '</small></td>'
		. '<td class="text-right">' . $q($dc_ok) . ($dc_wait > 0 ? '<br><small class="text-info">+' . $q($dc_wait) . ' in approval</small>' : '') . '</td>'
		. '<td class="text-right">' . $q($invoiced) . '</td>'
		. '<td class="text-right' . ($to_dc > 0 ? ' text-danger font-weight-bold' : '') . '">' . $q($to_dc) . '</td>'
		. '<td class="text-right' . ($to_inv > 0 ? ' text-danger font-weight-bold' : '') . '">' . $q($to_inv) . '</td>'
		. '<td class="text-right' . ($to_dc > 0 && (float)$r->stock < $to_dc ? ' text-danger' : '') . '">' . $q($r->stock) . '</td>'
		. '<td>' . $stage . '</td>'
		. '<td><small>' . (isset($dcs[$i]) ? implode('<br>', $dcs[$i]['refs']) : '-') . '</small></td>'
		. '<td><small>' . (isset($invs[$i]) ? implode('<br>', $invs[$i]['refs']) : '-') . '</small></td>'
		. '</tr>';
}
?>
<div class="d-flex flex-wrap justify-content-between mb-2">
	<div><b><?php echo $h($so->so_refno); ?></b> &nbsp; <?php echo date('d-m-Y', strtotime($so->so_date)); ?> &nbsp; <?php echo $h($so->supp_name); ?></div>
	<div><span class="badge bg-warning"><?php echo $pending; ?> pending</span> <span class="badge bg-success"><?php echo $n - $pending; ?> completed</span> of <?php echo $n; ?> item(s)</div>
</div>
<table class="table table-xs table-bordered" id="soItemTable">
	<thead>
		<tr class="bg-table-header">
			<th>#</th>
			<th>Item</th>
			<th class="text-right">Ordered</th>
			<th class="text-right">DC Qty</th>
			<th class="text-right">Invoiced</th>
			<th class="text-right">To DC</th>
			<th class="text-right">To Invoice</th>
			<th class="text-right">Stock</th>
			<th>Stage</th>
			<th>DC No. (qty)</th>
			<th>Invoice No. (qty)</th>
		</tr>
	</thead>
	<tbody>
		<?php echo $rows; ?>
		<tr class="so-none"><td colspan="11" class="text-center text-muted">No pending items - tick Show all items to see them all.</td></tr>
	</tbody>
</table>
