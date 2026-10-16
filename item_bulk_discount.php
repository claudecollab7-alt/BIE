<?php
// 2026-10-16 new screen - change item discount or margin % for a set of items in the chosen branches in one save
// discount: cost and margin follow, selling stays; margin: selling follows, cost stays. Item creator (ITEM C) or admin
ob_start();
session_start();

require_once("inc/common/userclass.php");

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

if (!fnTaskCan('ITEM', 'C')) {
	fnAccessDenied(false, 'home.php');
}
$branches = $conn->query("SELECT branch_id, branch_name FROM mst_branch WHERE branch_status = 1 ORDER BY branch_id")->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo PAGE_TITLE; ?> - Bulk Discount / Margin</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<?php include_once("inc/common/css-js.php"); ?>
	<style>
		#bulkTable td.chg { background: #fff3cd; font-weight: bold; }
		#bulkTable td.neg { color: #d32f2f; font-weight: bold; }
		#bulkTable small.old { display: block; color: #888; font-weight: normal; text-decoration: line-through; }
	</style>
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
							<a href="#" class="breadcrumb-item"> Item Masters</a>
							<span class="breadcrumb-item active">Bulk Discount / Margin</span>
						</div>
					</div>
				</div>
			</div>

			<div class="content pt-0">
				<div class="card">
					<div class="card-header bg-pgheader text-white header-elements-inline">
						<h6 class="card-title">Choose Items</h6>
					</div>
					<div class="card-body">
						<?php include("inc/common/item_price_filters.php"); ?>
							<label class="col-lg-1 col-form-label">Branches</label>
							<div class="col-lg-7">
								<select id="f_branches" class="select item-filter" multiple data-placeholder="Choose branches..">
									<?php foreach ($branches as $b) { ?>
										<option value="<?php echo (int)$b->branch_id; ?>" selected><?php echo $h($b->branch_name); ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="form-group row mb-0">
							<label class="col-lg-1 col-form-label">Change</label>
							<div class="col-lg-3 pt-2">
								<label class="mr-3"><input type="radio" name="what" value="discount" checked> Item Discount %</label>
								<label><input type="radio" name="what" value="margin"> Margin %</label>
							</div>
							<label class="col-lg-1 col-form-label">New Value</label>
							<div class="col-lg-2">
								<div class="input-group">
									<input type="text" id="bulk_value" class="form-control" maxlength="7" autocomplete="off" onkeypress="return isNumberKey_With_Dot(event)">
									<span class="input-group-append"><span class="input-group-text">%</span></span>
								</div>
							</div>
							<div class="col-lg-5 pt-2" id="bulk_hint">
								<span class="text-muted">Discount: cost price and margin change, selling price stays. Margin: selling price changes, cost stays.</span>
							</div>
						</div>
					</div>
				</div>

				<div class="card">
					<div class="card-header bg-pgheader text-white header-elements-inline">
						<h6 class="card-title">Items <span id="row_count" class="badge badge-light ml-2">0</span></h6>
					</div>
					<div class="card-body pt-2">
						<table class="table table-xs table-bordered table-hover" id="bulkTable" width="100%">
							<thead>
								<tr class="bg-table-header">
									<th>Item Code</th>
									<th>Item Name</th>
									<th>Branch</th>
									<th class="text-right">Item Price</th>
									<th class="text-right">Discount %</th>
									<th class="text-right">Cost Price</th>
									<th class="text-right">Margin %</th>
									<th class="text-right">Selling Price</th>
									<th class="text-center">Remove</th>
								</tr>
							</thead>
						</table>
					</div>
					<div class="card-footer text-center">
						<input type="button" class="btn btn-custom" id="bulk_save" value="Save">
						<input type="button" class="btn btn-light ml-2" value="Cancel" onclick="window.location.href='lst_item_details.php'">
					</div>
				</div>
			</div>
			<?php include("inc/common/footer.php") ?>
		</div>
	</div>
</body>

<script>
	$(document).ready(function() {
		<?php
		if (isset($_SESSION['_msg']) && $_SESSION['_msg'] != "") {
			echo "$.jGrowl(" . json_encode($_SESSION['_msg']) . ", { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-success', position: 'bottom-right', life:'3000', header: 'Success!' });";
			$_SESSION['_msg'] = "";
		}
		?>
		var f2 = function(v) { return (Math.round(v * 100) / 100).toFixed(2); };
		var esc = function(s) { return $('<div>').text(s).html(); };

		// the new values a row will get with the value typed in
		function preview(r) {
			var what = $('input[name=what]:checked').val(), v = parseFloat($('#bulk_value').val());
			var n = { discount: r.discount, cost: r.cost, margin: r.margin, selling: r.selling };
			if (isNaN(v)) return n;
			if (what == 'discount') {
				n.discount = v;
				n.cost = fnPrice.cost(r.price, v);
				n.margin = fnPrice.margin(r.selling, n.cost);
			} else {
				n.cost = fnPrice.cost(r.price, r.discount);
				n.margin = v;
				n.selling = Math.round(fnPrice.selling(n.cost, v) * 100) / 100;
			}
			return n;
		}
		function cell(key) {
			return function(d, type, r) {
				if (type !== 'display') return r[key];
				var n = preview(r)[key];
				return Math.abs(n - r[key]) >= 0.005 ? f2(n) + '<small class="old">' + f2(r[key]) + '</small>' : f2(r[key]);
			};
		}
		var table = $('#bulkTable').DataTable({
			data: [],
			pageLength: 50,
			order: [],
			autoWidth: false,
			columns: [
				{ data: 'code', render: function(d, t) { return t === 'display' ? esc(d) : d; } },
				{ data: 'name', render: function(d, t) { return t === 'display' ? esc(d) : d; } },
				{ data: 'branch' },
				{ data: 'price', className: 'text-right', render: function(d, t) { return t === 'display' ? f2(d) : d; } },
				{ data: 'discount', className: 'text-right', render: cell('discount') },
				{ data: 'cost', className: 'text-right', render: cell('cost') },
				{ data: 'margin', className: 'text-right', render: cell('margin') },
				{ data: 'selling', className: 'text-right', render: cell('selling') },
				{ data: null, orderable: false, className: 'text-center', render: function() { return '<a href="javascript:;" class="row-remove" title="Remove from the list"><i class="icon-bin bg-delete"></i></a>'; } }
			],
			createdRow: function(tr) { $(tr).attr('data-row', 1); },
			rowCallback: function(tr, r) {
				var n = preview(r);
				$('td', tr).removeClass('chg neg');
				[['discount', 4], ['cost', 5], ['margin', 6], ['selling', 7]].forEach(function(k) {
					if (Math.abs(n[k[0]] - r[k[0]]) >= 0.005) $('td', tr).eq(k[1]).addClass('chg');
				});
				if (n.margin < 0) $('td', tr).eq(6).addClass('neg');
			}
		});
		function count() { $('#row_count').text(table.rows().count()); }

		function load() {
			var data = { mode: 'list', branches: ($('#f_branches').val() || []).join(',') };
			['principal', 'brand', 'division', 'item_type', 'search'].forEach(function(k) { data[k] = $('#f_' + k).val() || ''; });
			$.ajax({ type: 'POST', url: 'inc/cis_ajax/jquery_item_price.php', dataType: 'json', data: data }).done(function(res) {
				table.clear().rows.add(res.rows).draw();
				count();
			}).fail(function(x) { alert(x.status == 403 ? 'You are not allowed to change item prices..!' : 'Loading the items failed..!'); });
		}
		var timer = null;
		$('.item-filter').on('change', load);
		$('#f_search').off('change').on('input', function() { clearTimeout(timer); timer = setTimeout(load, 400); });
		// redraw on typing only - a redraw on blur would swallow a click on Remove
		var redraw = function() { table.rows().invalidate('data').draw(false); };
		$('#bulk_value').on('input', redraw);
		$('input[name=what]').on('change', redraw);
		$('#bulkTable').on('click', '.row-remove', function() { table.row($(this).closest('tr')).remove().draw(false); count(); });

		$('#bulk_save').click(function() {
			var what = $('input[name=what]:checked').val(), v = $.trim($('#bulk_value').val()), n = table.rows().count();
			if (n == 0) { alert('Choose the items first..!'); return; }
			if (v === '' || isNaN(parseFloat(v)) || (what == 'discount' && (v < 0 || v > 100))) { alert('Give the new ' + (what == 'discount' ? 'discount (0 - 100)' : 'margin') + ' %..!'); $('#bulk_value').focus(); return; }
			var neg = 0;
			table.rows().every(function() { if (preview(this.data()).margin < 0) neg++; });
			var msg = 'Set ' + (what == 'discount' ? 'item discount' : 'margin') + ' to ' + v + '% for ' + n + ' item / branch row(s)?';
			if (neg) msg += '\n\n' + neg + ' row(s) will have cost above selling price (negative margin).';
			if (!confirm(msg)) return;
			var rows = [];
			table.rows().every(function() { var r = this.data(); rows.push(r.item_id + ':' + r.branch_id); });
			$(this).prop('disabled', true).val('Saving..');
			$.ajax({ type: 'POST', url: 'inc/cis_ajax/jquery_item_price.php', dataType: 'json', data: { mode: 'bulk', what: what, value: v, rows: rows.join(',') } })
				.done(function() { window.location.reload(); })
				.fail(function(x) {
					$('#bulk_save').prop('disabled', false).val('Save');
					alert(x.status == 403 ? 'You are not allowed to change item prices..!' : ((x.responseJSON && x.responseJSON.msg) || 'Save failed - please try again..!'));
				});
		});
	});
</script>

</html>
