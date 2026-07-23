<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
session_start();
$conn = new dbconnect();
$dbconn= new dbhandler();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);




if(isset($_POST['item_id']) && $_POST['mode']=='save')
{
	$row = '';
	$row1 = '';
	$item_qry = 'SELECT * FROM tbl_item_details WHERE item_id = '.$_POST['item_id'].' ';
	$item_dets = $conn->query($item_qry);
	if($item_dets->rowCount() > 0 ){
		
		$itm = $item_dets->fetch();
		$row .= '<tr id="'.$itm->item_id.'" >
				<td>'.$itm->item_purchase_code.'
					<input type="hidden" class="temp_item_id" name="temp_item_id[]" value="'.$itm->item_id.'" />
				</td>
                <td>'.$itm->supp_item_code.'
                    <input type="hidden" class="item_code" name="item_code[]" value="'.$itm->item_code.'" />
                </td>
                <td>'.$itm->item_desciption.'
					<input type="hidden" class="item_desciption" name="item_desciption[]" value="'.$itm->item_desciption.'" />
				</td>
				<td class="text-right">'.$_POST['po_qty'].'
					<input type="hidden" class="temp_qty" name="temp_qty[]" value="'.$_POST['po_qty'].'" />
				</td>
                <td class="text-right">'.$_POST['po_unit'].'
					<input type="hidden" class="temp_unit" name="temp_unit[]" value="'.$_POST['po_unit'].'" />
                </td>
				<td class="text-right">' . $_POST['po_item_price'] . '
					<input type="hidden" class="temp_item_price" name="temp_item_price[]" value="' . $_POST['po_item_price'] . '" />
				</td>
				<td class="text-right">' . $_POST['po_dis']  . '
					<input type="hidden" class="temp_discount_per" name="temp_discount_per[]" value="' . $_POST['po_dis'] . '" />
					<input type="hidden" class="temp_discount_val" name="temp_discount_val[]" value="' . $_POST['po_dis_val'] . '">
				</td>
				<td class="text-right">'.$_POST['po_cost_price'].'
					<input type="hidden" class="temp_cost_price" name="temp_cost_price[]" value="'.$_POST['po_cost_price'].'" />
                </td>
                <td class="text-right">'.$_POST['po_price'].'
					<input type="hidden" class="temp_po_price" name="temp_po_price[]" value="'.$_POST['po_price'].'" />
                </td>
				<td class="text-right">'.$_POST['po_vat'].'
					<input type="hidden" class="temp_vat" name="temp_vat[]" value="'.$_POST['po_vat'].'" />
                	<input type="hidden" class="temp_vat_val" name="temp_vat_val[]" value="'.$_POST['po_vat_val'].'" />
					<input type="hidden" class="temp_cgst" name="temp_cgst[]" value="'.$_POST['po_cgst'].'" />
					<input type="hidden" class="temp_cgst_val" name="temp_cgst_val[]" value="'.$_POST['po_cgst_val'].'" />
					<input type="hidden" class="temp_sgst" name="temp_sgst[]" value="'.$_POST['po_sgst'].'" />
					<input type="hidden" class="temp_sgst_val" name="temp_sgst_val[]" value="'.$_POST['po_sgst_val'].'" />
                </td>
                <td class="text-right">'.$_POST['po_net_amt'].'
					<input type="hidden" class="temp_net_amt" name="temp_net_amt[]" value="'.$_POST['po_net_amt'].'" />
                </td>
				<td class="text-center">
                    <a href="javascript:remove_item('.$itm->item_id.');" class="" rel="'.$itm->item_id.'"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a>
                </td>
			</tr>';
	}
}





if(isset($_POST['pr_id']) && $_POST['mode']=='save_pr')
{
	$row = '';
	$row1 = '';
	$item_qry = 'SELECT * FROM mst_po_print WHERE pr_id = '.$_POST['pr_id'].' ';
	$item_dets = $conn->query($item_qry);
	if($item_dets->rowCount() > 0 ){
		
		$itm = $item_dets->fetch();
		$row .= '<tr id="'.$itm->pr_id.'" >
				<td>'.$itm->pr_name.'
					<input type="hidden" class="temp_pr_name" name="temp_pr_name[]" value="'.$itm->pr_name.'" />
					<input type="hidden" class="temp_pr_sort" name="temp_pr_sort[]" value="'.$itm->pr_sort.'" />
					<input type="hidden" class="temp_pr_id" name="temp_pr_id[]" value="'.$_POST['pr_id'].'" />
				</td>
                <td>'.$_POST['po_pr_des'].'
                    <input type="hidden" class="temp_pr_desc" name="temp_pr_desc[]" value="'.htmlspecialchars($_POST['po_pr_des']).'" />
                </td>
				<td class="text-center">
                    <a href="javascript:remove_item('.$itm->pr_id.');" class="" rel="'.$itm->pr_id.'"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a>
                </td>
			</tr>';
	}
}


if(isset($_POST['item_id']) && $_POST['mode']=='inv_save')
{
	$row = '';
	$row1 = '';
	$item_qry = 'SELECT * FROM tbl_item_details WHERE item_id = '.$_POST['item_id'].' ';
	$item_dets = $conn->query($item_qry);
	if($item_dets->rowCount() > 0 ){
		
		$itm = $item_dets->fetch();
		$row .= '<tr id="'.$itm->item_id.'" >
		
				<td>'.$itm->item_purchase_code.'
					<input type="hidden" class="temp_item_id" name="temp_item_id[]" value="'.$itm->item_id.'" />
				</td>
                <td>'.$itm->item_desciption.'
					<input type="hidden" class="item_desciption" name="item_desciption[]" value="'.$itm->item_desciption.'" />
				</td>
				<td class="text-right">'.$_POST['po_qty'].'
					<input type="hidden" class="temp_qty" name="temp_qty[]" value="'.$_POST['po_qty'].'" />
				</td>
                <td class="text-right">'.$_POST['po_unit'].'
					<input type="hidden" class="temp_unit" name="temp_unit[]" value="'.$_POST['po_unit'].'" />
                </td>
				<td class="text-right">'.$_POST['po_unit_price'].'
					<input type="hidden" class="temp_unit_price" name="temp_unit_price[]" value="'.$_POST['po_unit_price'].'" />
                </td>
                <td class="text-right">'.$_POST['po_price'].'
					<input type="hidden" class="temp_inv_price" name="temp_inv_price[]" value="'.$_POST['po_price'].'" />
                </td>
				<td class="text-right">'.$_POST['inv_vat'].'
					<input type="hidden" class="temp_vat" name="temp_vat[]" value="'.$_POST['inv_vat'].'" />
					<input type="hidden" class="temp_vat_val" name="temp_vat_val[]" value="'.$_POST['inv_vat_val'].'" />
					<input type="hidden" class="temp_cgst" name="temp_cgst[]" value="'.$_POST['inv_cgst'].'" />
					<input type="hidden" class="temp_cgst_val" name="temp_cgst_val[]" value="'.$_POST['inv_cgst_val'].'" />
					<input type="hidden" class="temp_sgst" name="temp_sgst[]" value="'.$_POST['inv_sgst'].'" />
					<input type="hidden" class="temp_sgst_val" name="temp_sgst_val[]" value="'.$_POST['inv_sgst_val'].'" />
                </td>
                <td class="text-right">'.$_POST['po_net_amt'].'
					<input type="hidden" class="temp_net_amt" name="temp_net_amt[]" value="'.$_POST['po_net_amt'].'" />
                </td>
				<td class="text-center">
                    <a href="javascript:remove_item('.$itm->item_id.');" class="" rel="'.$itm->item_id.'"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a>
                </td>
			</tr>';
	}
}








if(isset($_POST['cash_id']) && $_POST['mode']=='save_pay')
{
	$row = '';
	$row1 = '';
	$item_qry = 'SELECT * FROM mst_cash_details WHERE cash_id = '.$_POST['cash_id'].' ';
	$item_dets = $conn->query($item_qry);
	if($item_dets->rowCount() > 0 ){
		$itm = $item_dets->fetch();

		$total=0;
		$total = (float)$_POST['cash_count'] * (float)$itm->cash_name;
		
		$row .= '<tr id="tbl'.$itm->cash_id.'" >
				<td>'.$itm->cash_name.'
					<input type="hidden" class="temp_cash_name" name="temp_cash_name[]" value="'.$itm->cash_name.'" />
					<input type="hidden" class="temp_cash_id" name="temp_cash_id[]" value="'.$_POST['cash_id'].'" />
				</td>
                <td class="text-right">'.$_POST['cash_count'].'
                    <input type="hidden" class="temp_cash_count" name="temp_cash_count[]" value="'.$_POST['cash_count'].'" />
                </td>
				<td class="text-right">'.number_format(($total), 2).'
                    <input type="hidden" class="temp_total" name="temp_total[]" value="'.$total.'" />
                </td>
				<td class="text-center">
                    <a href="javascript:remove_item('.$itm->cash_id.');" class="" rel="'.$itm->cash_id.'"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a>
                </td>
			</tr>';
	}
}
echo $row; die;
