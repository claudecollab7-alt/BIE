<?php
session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");


$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$item_id=$_POST['item_id'];

if ($_POST['mode'] == 'save' && isset($_POST['item_id'])) {

	$item_id = $_POST['item_id'];	
	$item_selling_price = $_POST['quo_selling_price'];	
	$quo_qty = $_POST['quo_qty'];	
	$total_price = $item_selling_price * $quo_qty;
	$gst_id =  $_POST['quo_vat'];

	// $igst = $dbconn->GetSingleReconrd("mst_hsn", "igst", "hsn_id", $gst_id);
		
	if($gst_id >0 )
		$tax_val = (($total_price * $gst_id) / 100);
	else
		$tax_val = 0;
			

	$row = '';
	$item_qry = 'SELECT item_id, item_code, item_desciption FROM tbl_item_details WHERE item_id = ' . $_POST['item_id'];
	//echo $item_qry;
	$item_dets = $conn->query($item_qry);

	if ($item_dets->rowCount() > 0) {


	$itm = $item_dets->fetch();
	$row .= '<tr id="' . $itm->item_id . '" >
			<td>' . $itm->item_desciption . '
				<input type="hidden" class="item_desciption" name="item_desciption[]" value="' . $itm->item_desciption . '" />
			</td>
			<td>' . $itm->item_code . '
				<input type="hidden" class="temp_item_id" name="temp_item_id[]" value="' . $itm->item_id . '" />
			</td>
			
			<td class="text-right">' . $_POST['quo_qty'] . '</span>
				<input type="hidden" class="temp_qty" name="temp_qty[]" value="' . $_POST['quo_qty'] . '" />
			</td>
			<td class="text-right">' . $_POST['quo_unit'] . '
				<input type="hidden" class="temp_unit" name="temp_unit[]" value="' . $_POST['quo_unit'] . '" />
			</td>
			<td class="text-right">' . $_POST['quo_selling_price'] . '
				<input type="hidden" class="temp_selling_price" name="temp_selling_price[]" value="' . $_POST['quo_selling_price'] . '" />
			</td>
			<td class="text-right">' . $_POST['quo_discount']  . '
				<input type="hidden" class="temp_discount_per" name="temp_discount_per[]" value="' . $_POST['quo_discount'] . '" />
				<input type="hidden" class="temp_discount_val" name="temp_discount_val[]" value="' . $_POST['quo_discount1'] . '">
			</td>

			<td class="text-right">' . $_POST['quo_price'] . '</span>
				<input type="hidden" class="temp_quo_price" name="temp_quo_price[]" value="' . $_POST['quo_price'] . '" />
			</td>
			
		
			<td class="text-right">' . $_POST['quo_vat'] . '
				<input type="hidden" class="temp_vat" name="temp_vat[]" value="' . $_POST['quo_vat'] . '" />
				<input type="hidden" class="quo_pack_taxable_value" name="quo_pack_taxable_value[]" value="' . $tax_val . '"/>
				
			</td>
			<td class="text-right">' . $_POST['quo_net_amt'] . '</span>
				<input type="hidden" class="temp_net_amt" name="temp_net_amt[]"  id="net_total" value="' . $_POST['quo_net_amt'] . '" />
			</td>
			
			<td class="text-center">
				<a href="javascript:remove_item(' . $itm->item_id . ');" class="" rel="' . $itm->item_id . '"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a>
			</td>
			
			
		</tr>';

	
}
}
echo $row;



//------------------------------------------------group--------------------------------------------//

$row = '';
if ($_POST['mode'] == 'save' && isset($_POST['group_id'])) {

	$group_id = $_POST['group_id'];	
	$quo_set = $_POST['quo_set'];
	// echo "haii";


	$qry = "select * from tbl_item_group_details where item_group_id=" . $group_id . " ORDER BY item_position ASC";
	//echo $qry ;
	$qry_res = $conn->query($qry);

	if ($qry_res->rowCount() > 0) 
	{
		while ($qry_obj = $qry_res->fetch()) {
			//1500.00~1~1~asdfasd2

			$item_data = $dbconn->GetSingleReconrd("tbl_item_details", "concat(item_selling_price,'~',item_hsn,'~',item_code,'~',item_desciption,'~',item_uom)", "item_id", $qry_obj->item_id);
			$item_dets = explode("~", $item_data);

			$selling_price = $item_dets[0];
			$gst_id =  $item_dets[1];
			$temp_item_code = $item_dets[2];
			$temp_item_name = $item_dets[3];
			$uom_id = $item_dets[4];
			$igst = $dbconn->GetSingleReconrd("mst_hsn", "igst", "hsn_id", $gst_id);
			$item_unit = $dbconn->GetSingleReconrd("mst_uom", "uom_name", "uom_id ", $uom_id);

			//---------cal------//

			// $total_price = $item_selling_price * $qry_obj->item_qty;

			$quo_qty_val = $qry_obj->item_qty * $quo_set;
			$total_price = $selling_price * $qry_obj->item_qty;
			$quo_tol = $quo_qty_val*$selling_price;
			if($igst >0 )
				$tax_val = (($total_price * $igst) / 100);
			else
				$tax_val = 0;

			$temp_net_value = $quo_tol + $tax_val;
		

			try {
				$row .= '<tr id="' . $qry_obj->item_id . '" class="g'.$item_id.'" >
					            <td>' . $temp_item_name . '
								<input type="hidden" class="temp_item_id" name="temp_item_id[]" value="' . $qry_obj->item_id . '" />
								</td>

								<td>' . $temp_item_code . '
									
									<input type="hidden" class="temp_group_id" name="temp_group_id[]" value="' . $group_id . '" />
								</td>
								
								
								<td class="text-right">' . $quo_qty_val . '</span>
									<input type="hidden" class="temp_qty" name="temp_qty[]" value="' . $quo_qty_val . '" />
								</td>
					            <td class="text-right">' . $item_unit . '
									<input type="hidden" class="temp_unit" name="temp_unit[]" value="' . $item_unit . '" />
					            </td>

								<td class="text-right">' . $selling_price . '
									<input type="hidden" class="temp_selling_price" name="temp_selling_price[]" value="' . $selling_price . '" />
								</td>


								<td class="text-right">' . $quo_discount_amt  . '
									<input type="hidden" class="temp_discount_per" name="temp_discount_per[]" value="' . $quo_discount . '" />
									<input type="hidden" class="temp_discount_val" name="temp_discount_val[]" value="' .  $quo_discount_amt . '">
										<input type="hidden" class="temp_quo_price" name="temp_quo_price[]" value="' . $quo_tol . '" />
								</td>
								<td align="right">' . number_format($quo_tol, 2, ".", '') . '</td>
								
								
					            </td>
								
								<td class="text-right">' . $igst . '
									<input type="hidden" class="temp_vat" name="temp_vat[]" value="' . $igst . '" />
									<input type="hidden" class="quo_pack_taxable_value" name="quo_pack_taxable_value[]" value="' . $tax_val . '"/>
									<input type="hidden" class="temp_net_amt" name="temp_net_amt[]" id="net_total" value="' .  $temp_net_value . '" />
					            </td>
					            <td class="text-right">' . number_format($temp_net_value, 2, ".", '') . '</span>
									
					            </td>
								
								<td class="text-center">
					                <a href="javascript:remove_item(' . $qry_obj->item_id . ');" class="" rel="' . $qry_obj->item_id . '"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a>
					            </td>
							</tr>';
			} catch (Exception $e) {
				$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
				$_SESSION['_msg_err'] = $str;
			}
		}
	}
}

echo $row;
die;
