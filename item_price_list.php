<?php
// 2026-10-16 new screen - item prices of one branch as a list, edited and saved one row at a time
// price / discount change cost and margin; selling changes only from margin or selling. Item creator (ITEM C) or admin
// 2026-10-16 loader over the page while loading and saving
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
	<title><?php echo PAGE_TITLE; ?> - Item Price Update</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<?php include_once("inc/common/css-js.php"); ?>
	<style>
		#priceTable input.form-control { height: 30px; padding: 2px 6px; text-align: right; min-width: 80px; }
		#priceTable tr.dirty td { background: #fff3cd; }
		#priceTable tr.saved td { background: #e8f5e9; }
		#priceTable input.neg { color: #d32f2f; font-weight: bold; }
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
							<span class="breadcrumb-item active">Item Price Update</span>
						</div>
					</div>
				</div>
			</div>

			<div class="content pt-0">
				<div class="card">
					<div class="card-header bg-pgheader text-white header-elements-inline">
						<h6 class="card-title">Choose Items</h6>
					</div>
					<div class="card-body pb-0">
						<?php include("inc/common/item_price_filters.php"); ?>
							<label class="col-lg-1 col-form-label">Branch</label>
							<div class="col-lg-3">
								<select id="f_branches" class="form-control select-search item-filter">
									<?php foreach ($branches as $b) { ?>
										<option value="<?php echo (int)$b->branch_id; ?>" <?php echo $b->branch_id == $_SESSION['_user_branch'] ? 'selected' : ''; ?>><?php echo $h($b->branch_name); ?></option>
									<?php } ?>
								</select>
							</div>
							<div class="col-lg-4 pt-2 text-muted">Edit a row and press Save (or Enter) on that row. Price / discount change cost and margin; selling changes only from margin or selling.</div>
						</div>
					</div>
				</div>

				<div class="card">
					<div class="card-header bg-pgheader text-white header-elements-inline">
						<h6 class="card-title">Items <span id="row_count" class="badge badge-light ml-2">0</span></h6>
					</div>
					<div class="card-body pt-2">
						<table class="table table-xs table-bordered" id="priceTable" width="100%">
							<thead>
								<tr class="bg-table-header">
									<th>Item Code</th>
									<th>Item Name</th>
									<th class="text-right">Item Price</th>
									<th class="text-right">Discount %</th>
									<th class="text-right">Cost Price</th>
									<th class="text-right">Margin %</th>
									<th class="text-right">Selling Price</th>
									<th class="text-center">Save</th>
								</tr>
							</thead>
						</table>
					</div>
				</div>
			</div>
			<?php include("inc/common/footer.php") ?>
		</div>
	</div>
</body>

<script>
	$(document).ready(function() {
		var f2 = function(v) { return (Math.round(v * 100) / 100).toFixed(2); };
		var esc = function(s) { return $('<div>').text(s).html(); };
		function box(cls, v, ro) {
			return '<input type="text" class="form-control ' + cls + (ro ? ' bg-light' : '') + '" value="' + f2(v) + '" maxlength="12" autocomplete="off"'
				+ (ro ? ' readonly tabindex="-1"' : ' onkeypress="return isNumberKey_With_Dot(event)"') + '>';
		}
		var table = $('#priceTable').DataTable({
			data: [],
			pageLength: 50,
			order: [],
			autoWidth: false,
			columns: [
				{ data: 'code', render: function(d, t) { return t === 'display' ? esc(d) : d; } },
				{ data: 'name', render: function(d, t) { return t === 'display' ? esc(d) : d; } },
				{ data: 'price', orderable: false, render: function(d, t) { return t === 'display' ? box('p-price', d) : d; } },
				{ data: 'discount', orderable: false, render: function(d, t) { return t === 'display' ? box('p-discount', d) : d; } },
				{ data: 'cost', orderable: false, render: function(d, t) { return t === 'display' ? box('p-cost', d, true) : d; } },
				{ data: 'margin', orderable: false, render: function(d, t) { return t === 'display' ? box('p-margin', d) : d; } },
				{ data: 'selling', orderable: false, render: function(d, t) { return t === 'display' ? box('p-selling', d) : d; } },
				{ data: null, orderable: false, className: 'text-center', render: function() { return '<button type="button" class="btn btn-sm btn-custom row-save">Save</button>'; } }
			],
			// each row: its own price fields linked, highlighted once changed
			createdRow: function(tr) {
				fnPrice.link({ price: $('.p-price', tr), discount: $('.p-discount', tr), cost: $('.p-cost', tr), margin: $('.p-margin', tr), selling: $('.p-selling', tr) });
				$('input', tr).on('input change', function() {
					$(tr).removeClass('saved').addClass('dirty');
					$('.p-margin', tr).toggleClass('neg', parseFloat($('.p-margin', tr).val()) < 0);
				});
				$('.p-margin', tr).toggleClass('neg', parseFloat($('.p-margin', tr).val()) < 0);
			}
		});
		function dirty() { return $(table.rows().nodes()).filter('.dirty').length; }

		function load() {
			if (dirty() && !confirm(dirty() + ' changed row(s) are not saved and will be lost. Continue?')) return;
			var data = { mode: 'list', branches: $('#f_branches').val() || '' };
			['principal', 'brand', 'division', 'item_type', 'search'].forEach(function(k) { data[k] = $('#f_' + k).val() || ''; });
			fnPrice.busy(true);
			$.ajax({ type: 'POST', url: 'inc/cis_ajax/jquery_item_price.php', dataType: 'json', data: data }).done(function(res) {
				table.clear().rows.add(res.rows).draw();
				$('#row_count').text(res.rows.length);
			}).fail(function(x) { alert(x.status == 403 ? 'You are not allowed to change item prices..!' : 'Loading the items failed..!'); })
			.always(function() { fnPrice.busy(false); });
		}
		var timer = null;
		$('.item-filter').on('change', load);
		$('#f_search').off('change').on('input', function() { clearTimeout(timer); timer = setTimeout(load, 400); });

		function save(tr) {
			var r = table.row(tr).data(), btn = $('.row-save', tr);
			var send = { mode: 'save_row', item_id: r.item_id, branch_id: r.branch_id, price: $('.p-price', tr).val(), discount: $('.p-discount', tr).val(), selling: $('.p-selling', tr).val() };
			if (parseFloat(send.discount) > 100) { alert('Discount cannot be above 100..!'); return; }
			if (parseFloat($('.p-margin', tr).val()) < 0 && !confirm('Cost price is above selling price (negative margin). Save anyway?')) return;
			btn.prop('disabled', true).text('..');
			fnPrice.busy(true);
			$.ajax({ type: 'POST', url: 'inc/cis_ajax/jquery_item_price.php', dataType: 'json', data: send }).done(function(res) {
				if (res.row) {
					$.extend(r, res.row);
					$('.p-price', tr).val(f2(r.price)); $('.p-discount', tr).val(f2(r.discount)); $('.p-cost', tr).val(f2(r.cost));
					$('.p-margin', tr).val(f2(r.margin)); $('.p-selling', tr).val(f2(r.selling));
				}
				$(tr).removeClass('dirty').addClass('saved');
				btn.text(res.changed ? 'Saved' : 'No change');
				setTimeout(function() { btn.text('Save'); }, 1500);
			}).fail(function(x) {
				alert(x.status == 403 ? 'You are not allowed to change item prices..!' : ((x.responseJSON && x.responseJSON.msg) || 'Save failed - please try again..!'));
				btn.text('Save');
			}).always(function() { btn.prop('disabled', false); fnPrice.busy(false); });
		}
		$('#priceTable').on('click', '.row-save', function() { save($(this).closest('tr')); });
		$('#priceTable').on('keydown', 'input', function(e) {
			if (e.which == 13) { e.preventDefault(); $(this).trigger('change'); save($(this).closest('tr')); }
		});
		$(window).on('beforeunload', function() { if (dirty()) return 'Changed rows are not saved.'; });
	});
</script>

</html>
