<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$supp_id = $_POST['supp_id'];

if ($_POST['mode'] == "delete") {

	$sql =  "DELETE FROM tbl_supp_items WHERE supp_item_id = '" . $_POST['auto_id'] . "'";
	$result = $conn->prepare($sql);
	$result->execute();
}
?>
<table class="datatable-col3 table table-xs table-hover table-bordered" id="lst_table">
	<thead>
		<tr>
			<th width="50px">#</th>
			<th>Item Code</th>
			<th width="10%">Actions</th>

		</tr>
	</thead>

	<?php

	$SQL = "SELECT * FROM tbl_supp_items WHERE supp_item_status = '1' AND supp_id = '" . $supp_id . "'  ORDER BY item_id ASC";
	$result = $conn->query($SQL);

	if ($result->rowCount() > 0) {
		$Sno = 1;

		while ($obj = $result->fetch()) {

			$items = $dbconn->GetSingleReconrd("tbl_item_details", "CONCAT(item_desciption,' - ',item_code)", "item_status = '1' AND item_id", $obj->item_id);

			$del_link = '<li><a href="" class="tip delete" rel="' . $obj->supp_item_id . '"  title="Remove"><i class="fa fa-times-circle"></i></a></li>';

			echo '<tr>

					<td>' . $Sno . '</td>
					
					
					<td>' . $items . '</td>
					
					<td align="center"><a href="javascript:remove_item(' . $obj->supp_item_id . ');" class="" rel="' . $obj->supp_item_id . '"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a></td></td>
				</tr>';
			$Sno++;
		}

		$obj = null;
	}

	?>
</table>