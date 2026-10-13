<?php
ob_start();
session_start();
require_once("inc/common/userclass.php");

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

// 2026-10-13 new - waiting list behind a dashboard tile (DC, sales order, payment, purchase return, repair), same rule as its count

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$q_key = isset($_REQUEST['q']) ? $_REQUEST['q'] : '';
$q = fnTaskQueue($q_key);
if (!$q || empty($q['open']) || !fnQueueVisible($q_key)) {
	fnAccessDenied(false, 'home.php');
}

$cols = "t." . $q['id'] . " AS rec_id, t." . $q['ref'] . " AS rec_ref, t." . $q['date'] . " AS rec_date, t.supp_id"
	. ($q['branch'] ? ", t." . $q['branch'] . " AS rec_branch" : ", 0 AS rec_branch")
	. ($q['value'] ? ", t." . $q['value'] . " AS rec_value" : ", '' AS rec_value");
// waiting rows picked inside the subquery, so the joined tables cannot clash with its column names
$sql = "SELECT " . $cols . ", s.supp_name, b.branch_name
		FROM (SELECT * FROM " . $q['table'] . " WHERE " . $q['where'] . fnQueueScopeSql($q_key) . ") t
		LEFT JOIN mst_supplier_new s ON s.supp_id = t.supp_id
		LEFT JOIN mst_branch b ON b.branch_id = " . ($q['branch'] ? "t." . $q['branch'] : "0") . "
		ORDER BY t." . $q['date'] . " DESC, t." . $q['id'] . " DESC";
$rows = $conn->query($sql)->fetchAll(PDO::FETCH_OBJ);
$h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES); };
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo PAGE_TITLE; ?> - <?php echo $h($q['label']); ?></title>
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
							<a href="#" class="breadcrumb-item"> Work Area</a>
							<span class="breadcrumb-item active"><?php echo $h($q['label']); ?></span>
						</div>
					</div>
				</div>
			</div>

			<div class="content pt-0">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-header bg-pgheader text-white header-elements-inline">
								<h6 class="card-title"><?php echo $h($q['label']); ?></h6>
								<div class="header-elements">
									<div class="list-icons">
										<a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
										<a class="list-icons-item" data-action="fullscreen"></a>
									</div>
								</div>
							</div>
							<div class="card-body pt-0">
								<table class="datatable-col7 table table-xs table-hover table-bordered" id="pendingTable">
									<thead>
										<tr class="bg-table-header">
											<th width="2%">#</th>
											<th>Ref No.</th>
											<th>Date</th>
											<th>Branch</th>
											<th>Customer / Supplier</th>
											<th class="text-right">Value</th>
											<th class="text-center" width="10%">Open</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$sno = 1;
										foreach ($rows as $r) {
											echo '<tr>';
											echo '<td>' . $sno++ . '</td>';
											echo '<td>' . $h($r->rec_ref) . '</td>';
											echo '<td>' . ($r->rec_date ? date('d-m-Y', strtotime($r->rec_date)) : '') . '</td>';
											echo '<td>' . $h($r->branch_name) . '</td>';
											echo '<td>' . $h($r->supp_name) . '</td>';
											echo '<td class="text-right">' . ($r->rec_value !== '' ? number_format((float)$r->rec_value, 2) : '') . '</td>';
											echo '<td class="text-center"><a href="' . $q['open'] . (int)$r->rec_id . '" title="Open"><i class="icon-eye bg-edit mr-2"></i></a></td>';
											echo '</tr>';
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

</html>
