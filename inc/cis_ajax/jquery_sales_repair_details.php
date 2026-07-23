<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn   = new dbconnect();
$dbconn = new dbhandler();

if ($_POST['mode'] == 'save' && isset($_POST['item_id'])) {

	$repair_item_id       = $_POST['repair_item_id'];
	$item_id              = $_POST['item_id'];
	$repair_qty           = $_POST['repair_qty'];
	$repair_selling_price = $_POST['repair_selling_price'];
	$repair_tax           = $_POST['repair_tax'];
	$repair_unit          = $_POST['repair_unit'];

	if ($repair_tax > 0) {
		$repair_value   = $repair_selling_price * $repair_qty;
		$repair_tax_val = ($repair_value * $repair_tax) / 100;
		$repair_net_val = $repair_value + $repair_tax_val;
	} else {
		$repair_value   = $repair_selling_price * $repair_qty;
		$repair_tax_val = 0;
		$repair_net_val = $repair_value;
	}

	$repair_item_name = $dbconn->GetSingleReconrd(
		"tbl_item_details",
		"CONCAT(item_desciption,' - ',item_code)",
		"item_status = '1' AND item_id",
		$repair_item_id
	);

	$spare_item_name = $dbconn->GetSingleReconrd(
		"tbl_item_details",
		"CONCAT(item_desciption,' - ',item_code)",
		"item_status = '1' AND item_id",
		$item_id
	);

	$row  = '<tr id="RI_' . $item_id . '">';
	$row .= '<td>' . $repair_item_name . '
				<input type="hidden" class="temp_repair_item_id" name="temp_repair_item_id[]" value="' . $repair_item_id . '" />
			</td>';
	// $row .= '<td>' . $spare_item_name . '
	// 			<input type="hidden" class="temp_spare_item_id" name="temp_spare_item_id[]" value="' . $item_id . '" />
	// 		</td>';
	$row .= '<td class="text-right">' . $repair_qty . '
				<input type="hidden" class="temp_repair_qty" name="temp_repair_qty[]" value="' . $repair_qty . '" />
			</td>';
	$row .= '<td class="text-center">' . $repair_unit . '
				<input type="hidden" class="temp_repair_unit" name="temp_repair_unit[]" value="' . $repair_unit . '" />
			</td>';
	$row .= '<td class="text-right">' . number_format($repair_selling_price, 2, '.', '') . '
				<input type="hidden" class="temp_repair_selling_price" name="temp_repair_selling_price[]" value="' . $repair_selling_price . '" />
			</td>';
	$row .= '<td class="text-right">' . number_format($repair_value, 2, '.', '') . '
				<input type="hidden" class="temp_repair_value" name="temp_repair_value[]" value="' . $repair_value . '" />
			</td>';
	$row .= '<td class="text-right">' . $repair_tax . ' %
				<input type="hidden" class="temp_repair_tax" name="temp_repair_tax[]" value="' . $repair_tax . '" />
				<input type="hidden" class="temp_repair_tax_val" name="temp_repair_tax_val[]" value="' . $repair_tax_val . '" />
			</td>';
	$row .= '<td class="text-right">' . number_format($repair_net_val, 2, '.', '') . '
				<input type="hidden" class="temp_repair_net_val" name="temp_repair_net_val[]" value="' . $repair_net_val . '" />
			</td>';
	$row .= '<td class="text-center">
				<a href="javascript:remove_item(' . $item_id . ');" rel="' . $item_id . '" title="Remove">
					<i class="icon-bin bg-delete mr-2"></i>
				</a>
			</td>';
	$row .= '</tr>';

	echo $row;
}
die;
?>
