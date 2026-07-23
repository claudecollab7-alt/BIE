<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

session_start();

$conn = new dbconnect();
$dbconn = new dbhandler();

if(isset($_POST['spare_item_id']) && $_POST['mode'] == 'SpareMapping')
{
	$row = '';

	$item_qry = "SELECT item_id,item_code,item_desciption 
				 FROM tbl_item_details 
				 WHERE item_id = ".$_POST['spare_item_id'];

	$item_dets = $conn->query($item_qry);

	if($item_dets->rowCount() > 0)
	{
		$itm = $item_dets->fetch();

		$delete = '<a href="javascript:void(0);" class="remove_row" title="Delete"><i class="icon-bin"></i></a>';

		$row .= '<tr>';
		$row .= '<td class="slno text-center"></td>';
		$row .= '<td class="text-center">'.$itm->item_code.'</td>';
		$row .= '<td>'.$itm->item_desciption.'
					<input type="hidden" name="spare_item_id[]" value="'.$itm->item_id.'">
				</td>';
		$row .= '<td class="text-center">'.$delete.'</td>';
		$row .= '</tr>';
	}

	echo $row;
}
?>