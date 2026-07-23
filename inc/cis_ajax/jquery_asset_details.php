<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
session_start();
$conn = new dbconnect();
$dbconn= new dbhandler();
if(($_POST['asset_id'] && $_POST['mode']=="save"))
{
	$asset_id = $_POST['asset_id'];	
	$issue_date = $_POST['issue_date'];
	$asset_qty = $_POST['asset_qty'];
	$asset_value = $_POST['asset_value'];
	$sim_no = $_POST['sim_no'];
	$mobile_no = $_POST['mobile_no'];
	$sim_limit = $_POST['sim_limit'];	


	$row = '';

	$asset_name = 'SELECT  asset_id, asset_name FROM mst_company_asset WHERE asset_id = ' . $_POST['asset_id'];
	$asset_name_dtes = $conn->query($asset_name);

	if ($asset_name_dtes->rowCount() > 0) {


	$itm = $asset_name_dtes->fetch();
	$row .= '<tr id=' . $itm->asset_id . ' >
			 	<td>' . $itm->asset_name . '<input type="hidden" class="hidd_asset_id" name="hidd_asset_id[]" value="' . $itm->asset_id . '" /> </td>
			 	<td type="date">' . date('d-m-Y ',strtotime($issue_date)). '<input type="hidden" class="hidd_asset_date" name="hidd_asset_date[]" value="' . $issue_date. '" /> </td>
			 	<td>' . $asset_qty . '<input type="hidden" class="hidd_asset_qty" name="hidd_asset_qty[]" value="' . $asset_qty . '" /> </td>
			 	<td class="text-right">' . number_format($asset_value,2). '<input type="hidden" class="hidd_asset_val" name="hidd_asset_val[]" value="' . $asset_value. '" /> </td>
			 	<td class="text-center"><input type="hidden" class="hidd_sim_no" name="hidd_sim_no[]" value="' . $sim_no. '" /><input type="hidden" class="hidd_mobile_no" name="hidd_mobile_no[]" value="' . $mobile_no. '" /><input type="hidden" class="hidd_sim_limit" name="hidd_sim_limit[]" value="' . $sim_limit. '" /><a href="javascript:remove_item(' . $itm->asset_id . ');" class="" rel="' . $itm->asset_id . '"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a></td>
			 </tr';
	}
}
echo $row;
?>
</table>