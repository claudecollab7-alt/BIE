<?php
ob_start();
session_start();
require_once("inc/common/userclass.php");
isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

// 2026-10-22 new - Purchase Order Report: approved POs with what is received on GRNs and a status (Pending / Partial / Completed)
// received = accepted qty on finalised GRNs (po_qty repeats the PO qty on every GRN line); the status opens the PO's items and their stage
// branch: PO viewers in Task Settings (PURCHASE_ORDER W) pick any branch, others see their own

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
$from_date = isset($_REQUEST['from_date']) && $_REQUEST['from_date'] != '' ? $_REQUEST['from_date'] : $dbconn->GetSingleReconrd("mst_finyear", "finyr_startdt", "finyr_active", 1);
$to_date = isset($_REQUEST['to_date']) && $_REQUEST['to_date'] != '' ? $_REQUEST['to_date'] : $dbconn->GetSingleReconrd("mst_finyear", "finyr_enddt", "finyr_active", 1);
$keyword = isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';
$sch_by = isset($_REQUEST['sch_by']) && in_array($_REQUEST['sch_by'], array('1', '2', '3', '4'), true) ? $_REQUEST['sch_by'] : '3';
$all_branches = fnTaskCan('PURCHASE_ORDER', 'W');
$branch = $all_branches ? (isset($_REQUEST['branch_id']) ? $_REQUEST['branch_id'] : (string)$_SESSION['_user_branch']) : (string)$_SESSION['_user_branch'];
$branches = $conn->query("SELECT branch_id, branch_name FROM mst_branch WHERE branch_status = 1 ORDER BY branch_id")->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo PAGE_TITLE; ?> - Purchase Order Report</title>
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
							<a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Home</a>
							<a href="#" class="breadcrumb-item"> Report</a>
							<span class="breadcrumb-item active">Purchase Order</span>
						</div>
					</div>
				</div>
			</div>

			<div class="content pt-0">
				<div class="card">
					<div class="card-header bg-pgheader text-white header-elements-inline">
						<h6 class="card-title">Purchase Order - Report</h6>
						<div class="header-elements">
							<div class="list-icons">
								<a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
								<a class="list-icons-item" data-action="fullscreen"></a>
							</div>
						</div>
					</div>

					<form name="thisForm" method="post" action="">
						<div class="card-body pt-2 pb-3">
							<div class="form-group row">
								<div class="form-group col-md-2">
									<label>From Date</label>
									<input type="date" class="form-control" name="from_date" id="from_date" value="<?php echo $h($from_date); ?>">
								</div>
								<div class="form-group col-md-2">
									<label>To Date</label>
									<input type="date" class="form-control" name="to_date" id="to_date" value="<?php echo $h($to_date); ?>">
								</div>
								<div class="form-group col-md-2">
									<label>Search</label>
									<input type="text" class="form-control" placeholder="PO No. / Supplier Name" name="keyword" id="keyword" autocomplete="off" value="<?php echo $h($keyword); ?>">
								</div>
								<div class="form-group col-md-2">
									<label>Branch</label>
									<select name="branch_id" id="branch_id" class="form-control select-search" <?php echo $all_branches ? '' : 'disabled'; ?>>
										<?php if ($all_branches) { ?><option value="">All Branches</option><?php } ?>
										<?php foreach ($branches as $b) { ?>
											<option value="<?php echo (int)$b->branch_id; ?>" <?php echo (string)$b->branch_id === $branch ? 'selected' : ''; ?>><?php echo $h($b->branch_name); ?></option>
										<?php } ?>
									</select>
								</div>
								<div class="form-group col-md-2">
									<label>Status</label>
									<select name="sch_by" id="sch_by" class="select">
										<option value="3" <?php echo $sch_by == '3' ? 'selected' : ''; ?>>All</option>
										<option value="1" <?php echo $sch_by == '1' ? 'selected' : ''; ?>>Pending</option>
										<option value="4" <?php echo $sch_by == '4' ? 'selected' : ''; ?>>Partial</option>
										<option value="2" <?php echo $sch_by == '2' ? 'selected' : ''; ?>>Completed</option>
									</select>
								</div>
								<div class="form-group col-md-2">
									<button class="btn btn-info mt-4" name="Report" value="Report" type="submit"><i class="icon-statistics mr-1"></i>Generate Report</button>
								</div>
							</div>

							<?php
							if (isset($_REQUEST['Report'])) {
								$where = " a.po_status = 5 AND a.po_approve_status = 1 AND a.po_date BETWEEN :fd AND :td";
								$p = array(':fd' => date('Y-m-d', strtotime($from_date)), ':td' => date('Y-m-d', strtotime($to_date)));
								if ($branch !== '') {
									$where .= " AND a.branch_id = :br";
									$p[':br'] = (int)$branch;
								}
								if ($keyword !== '') {
									$where .= " AND (a.po_refno LIKE :k1 OR b.supp_name LIKE :k2)";
									$p[':k1'] = $p[':k2'] = '%' . $keyword . '%';
								}
								// per PO: items, items not fully received, items with something received - item lines grouped (a PO can list an item twice)
								$sql = "SELECT a.po_id, a.po_refno, a.po_date, a.po_value, b.supp_name, br.branch_code,
										IFNULL(s.items, 0) AS items, IFNULL(s.pending_items, 0) AS pending_items, IFNULL(s.got_items, 0) AS got_items, gr.grns, gr.draft_grns
									FROM tbl_purchase_order a
									LEFT JOIN mst_supplier_new b ON b.supp_id = a.supp_id
									LEFT JOIN mst_branch br ON br.branch_id = a.branch_id
									LEFT JOIN (
										SELECT o.po_id, COUNT(*) AS items, SUM(o.q > IFNULL(r.acc, 0)) AS pending_items, SUM(IFNULL(r.acc, 0) > 0) AS got_items
										FROM (SELECT po_id, item_id, SUM(po_qty) AS q FROM tbl_purchase_order_details GROUP BY po_id, item_id) o
										LEFT JOIN (SELECT g.po_id, gd.grn_item_id AS item_id, SUM(gd.grn_accepted_qty) AS acc FROM tbl_grn g JOIN tbl_grn_details gd ON gd.grn_id = g.grn_id
												   WHERE g.grn_status = 2 AND g.grn_cancel_status <> 1 GROUP BY g.po_id, gd.grn_item_id) r ON r.po_id = o.po_id AND r.item_id = o.item_id
										GROUP BY o.po_id) s ON s.po_id = a.po_id
									LEFT JOIN (
										SELECT po_id, GROUP_CONCAT(IF(grn_status = 2, CONCAT(grn_id, '~', grn_ref_code, '~', grn_date), NULL) ORDER BY grn_id SEPARATOR '|') AS grns,
											SUM(grn_status < 2) AS draft_grns
										FROM tbl_grn WHERE grn_cancel_status <> 1 GROUP BY po_id) gr ON gr.po_id = a.po_id
									WHERE" . $where . " ORDER BY a.po_date DESC, a.po_id DESC";
								$st = $conn->prepare($sql);
								$st->execute($p);
								$rows = '';
								$n = 0;
								$count = array('Pending' => 0, 'Partial' => 0, 'Completed' => 0);
								$total = 0;
								foreach ($st->fetchAll(PDO::FETCH_OBJ) as $r) {
									if ($r->got_items == 0) {
										$stat = 'Pending';
										$badge = 'bg-warning';
									} elseif ($r->pending_items == 0) {
										$stat = 'Completed';
										$badge = 'bg-success';
									} else {
										$stat = 'Partial';
										$badge = 'bg-grey';
									}
									if (($sch_by == '1' && $stat != 'Pending') || ($sch_by == '4' && $stat != 'Partial') || ($sch_by == '2' && $stat != 'Completed')) {
										continue;
									}
									$count[$stat]++;
									$total += (float)$r->po_value;
									$grn_links = '';
									foreach (array_filter(explode('|', (string)$r->grns)) as $g) {
										$x = explode('~', $g);
										$grn_links .= '<a href="grn_view.php?grn_id=' . (int)$x[0] . '" target="_blank"><b>' . $h($x[1]) . '</b></a> (' . date('d-m-Y', strtotime($x[2])) . ')<br>';
									}
									if ($r->draft_grns > 0) {
										$grn_links .= '<small class="text-info">' . (int)$r->draft_grns . ' GRN in draft</small>';
									}
									$n++;
									$rows .= '<tr>'
										. '<td>' . $n . '</td>'
										. '<td><a href="po_print.php?po_id=' . (int)$r->po_id . '" target="_blank">' . $h($r->po_refno) . '</a></td>'
										. '<td>' . date('d-m-Y', strtotime($r->po_date)) . '</td>'
										. '<td><b>' . $h($r->supp_name) . '</b></td>'
										. '<td>' . $h($r->branch_code) . '</td>'
										. '<td class="text-right">' . number_format((float)$r->po_value, 2) . '</td>'
										. '<td class="text-center">' . (int)$r->items . '</td>'
										. '<td class="text-center">' . ($r->items - $r->pending_items) . '</td>'
										. '<td>' . ($grn_links != '' ? $grn_links : '-') . '</td>'
										. '<td class="text-center"><a href="javascript:;" class="doc-items" data-url="inc/cis_ajax/jquery_po_item_status.php" data-id="' . (int)$r->po_id . '" data-title="Purchase Order Items" title="Items and their stage"><span class="badge ' . $badge . '">' . $stat . '</span></a></td>'
										. '</tr>';
								}
							?>
								<div class="table-responsive">
									<table class="table table-xs table-bordered table-hover">
										<thead>
											<tr class="bg-table-header">
												<th colspan="10" class="text-center">Purchase Order Details - <?php echo date('d-M-Y', strtotime($from_date)) . ' to ' . date('d-M-Y', strtotime($to_date)); ?></th>
											</tr>
											<tr class="bg-table-header">
												<th>#</th>
												<th>PO No.</th>
												<th>PO Date</th>
												<th>Supplier Name</th>
												<th>Branch</th>
												<th class="text-right">PO Value</th>
												<th class="text-center">Items</th>
												<th class="text-center">Fully Received</th>
												<th>GRN No.</th>
												<th class="text-center">Status</th>
											</tr>
										</thead>
										<tbody>
											<?php echo $rows != '' ? $rows : '<tr><td colspan="10" class="text-center">No Records Found</td></tr>'; ?>
										</tbody>
										<tfoot>
											<tr class="font-weight-bold">
												<td colspan="5">Total <?php echo $n; ?> PO(s) - Pending <?php echo $count['Pending']; ?>, Partial <?php echo $count['Partial']; ?>, Completed <?php echo $count['Completed']; ?></td>
												<td class="text-right"><?php echo number_format($total, 2); ?></td>
												<td colspan="4"></td>
											</tr>
										</tfoot>
									</table>
								</div>
							<?php } ?>
						</div>
					</form>
				</div>
			</div>
			<?php include("inc/common/footer.php") ?>
		</div>
	</div>
	<?php include("inc/common/doc_items_modal.php"); ?>
</body>

</html>
