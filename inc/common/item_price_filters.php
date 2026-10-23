<?php
// 2026-10-16 new - item set filters shared by the bulk discount / margin and price list screens
// 2026-10-23 principal dropdown without data-placeholder - with it the All option was taken as the placeholder and hidden
$h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
$fl_types = array(2 => 'Trading', 3 => 'Raw Materials', 6 => 'Consumable', 7 => 'Group Products', 8 => 'Labour Charges');
?>
<div class="form-group row">
	<label class="col-lg-1 col-form-label">Principal</label>
	<div class="col-lg-3">
		<select id="f_principal" class="form-control select-search item-filter">
			<option value="">--- All ---</option>
			<?php foreach ($conn->query("SELECT principal_id, principal_name, principal_code FROM mst_principal WHERE principal_status = 1 ORDER BY principal_name")->fetchAll(PDO::FETCH_OBJ) as $r) { ?>
				<option value="<?php echo (int)$r->principal_id; ?>"><?php echo $h($r->principal_name . ($r->principal_code != '' ? ' - ' . $r->principal_code : '')); ?></option>
			<?php } ?>
		</select>
	</div>
	<label class="col-lg-1 col-form-label">Brand</label>
	<div class="col-lg-3">
		<select id="f_brand" class="form-control select-search item-filter">
			<option value="">--- All ---</option>
			<?php foreach ($conn->query("SELECT brand_id, brand_name FROM mst_brand WHERE brand_status = 1 ORDER BY brand_name")->fetchAll(PDO::FETCH_OBJ) as $r) { ?>
				<option value="<?php echo (int)$r->brand_id; ?>"><?php echo $h($r->brand_name); ?></option>
			<?php } ?>
		</select>
	</div>
	<label class="col-lg-1 col-form-label">Division</label>
	<div class="col-lg-3">
		<select id="f_division" class="form-control select-search item-filter">
			<option value="">--- All ---</option>
			<?php foreach ($conn->query("SELECT division_id, division_name FROM mst_division WHERE division_status = 1 ORDER BY division_name")->fetchAll(PDO::FETCH_OBJ) as $r) { ?>
				<option value="<?php echo (int)$r->division_id; ?>"><?php echo $h($r->division_name); ?></option>
			<?php } ?>
		</select>
	</div>
</div>
<div class="form-group row">
	<label class="col-lg-1 col-form-label">Item Type</label>
	<div class="col-lg-3">
		<select id="f_item_type" class="form-control select-search item-filter">
			<option value="">--- All ---</option>
			<?php foreach ($fl_types as $k => $v) { ?>
				<option value="<?php echo $k; ?>"><?php echo $v; ?></option>
			<?php } ?>
		</select>
	</div>
	<label class="col-lg-1 col-form-label">Code / Name</label>
	<div class="col-lg-3">
		<input type="text" id="f_search" class="form-control item-filter" maxlength="50" placeholder="Type part of a code or name" autocomplete="off">
	</div>
<?php // the page closes this row after its branch field ?>
