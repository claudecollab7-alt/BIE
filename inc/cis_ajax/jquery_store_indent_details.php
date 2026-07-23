<?php
session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");


$conn = new dbconnect();
$dbconn = new dbhandler();

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

$item_id=$_POST['item_id'];

if ($_POST['mode'] == 'save' && isset($_POST['item_id'])) {

	$item_id = $_POST['item_id'];	
    $si_qty = $_POST['si_qty'];
    $si_unit = $_POST['si_unit'];
	

	// $igst = $dbconn->GetSingleReconrd("mst_hsn", "igst", "hsn_id", $gst_id);
		
	
			

	$row = '';
    $field_name = $dbconn->GetSingleReconrd("mst_branch","branch_stock_field","branch_id",$_SESSION['_user_branch']);
    $branch_item_moq = $dbconn->GetSingleReconrd("mst_branch","branch_item_maq","branch_id",$_SESSION['_user_branch']);
    $uom = $dbconn->GetSingleReconrd("mst_uom","uom_name","uom_id",$_POST['si_unit']);
	$item_qry = 'SELECT item_id, item_code, item_desciption, item_order_min_qty, item_uom FROM tbl_item_details WHERE item_id = ' . $_POST['item_id'];
	//echo $item_qry;
	$item_dets = $conn->query($item_qry);

	if ($item_dets->rowCount() > 0) {

        
	$itm = $item_dets->fetch();
    $item_curr_stock =  $dbconn->GetSingleReconrd("tbl_item_stock","$field_name","item_id",$itm->item_id);
    $item_order_min_qty =  $dbconn->GetSingleReconrd("tbl_item_stock","$branch_item_moq","item_id",$itm->item_id);
	
	// $uom_name = $dbconn->GetSingleReconrd("mst_uom","uom_name","uom_id",$itm->item_uom);


	$row .= '<tr id="item' . $itm->item_id . '" >
			
			<td>' . $itm->item_code . '
				<input type="hidden" class="temp_item_id" name="temp_item_id[]" value="' . $itm->item_id . '" />
			</td>
            <td>' . $itm->item_desciption . '
				<input type="hidden" class="item_desciption" name="item_desciption[]" value="' . $itm->item_desciption . '" />
			</td>
           
			
			<td class="text-left">' . $_POST['curr_stock'] . '</span>
				<input type="hidden" class="curr_stock" name="curr_stock[]" value="' .$item_curr_stock. '" />
			</td>

			<td class="text-center">' . $uom . '</span>
				<input type="hidden" class="temp_si_unit" name="temp_si_unit[]" value="' .$_POST['si_unit']. '" />
				<input type="hidden" class="temp_item_uom" name="temp_item_uom[]" value="' .$itm->item_uom. '" />
			</td>

            <td class="text-left">' .$item_order_min_qty.'</span>
				<input type="hidden" class="item_moq" name="item_moq[]" value="' .$item_order_min_qty. '" />
			</td>
			
			<td class="text-center">'.$_POST['si_qty'].'</span>
					<input type="hidden" class="temp_si_qty" name="temp_si_qty[]" value="'.$_POST['si_qty'].'" />
			</td>
			<td class="text-center">
				<a href="javascript:remove_item_si_details(' . $itm->item_id . ');" class="" rel="' . $itm->item_id . '"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a>
			</td>
			
			
		</tr>';

	
}
}
echo $row;

die;
