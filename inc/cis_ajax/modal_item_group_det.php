<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$html_output = "";
if ($_REQUEST['id'] != "") {
    $_REQUEST['item_group_id'] = $_REQUEST['id'];
    $dbconn = new dbhandler();

    $result = $conn->query("SELECT * FROM tbl_item_group WHERE item_group_id = '" . $_REQUEST['item_group_id'] . "'");
    if ($result->rowCount() > 0) {
        $obj = $result->fetch(PDO::FETCH_OBJ);
		$item_group_code = $obj->item_group_code;
        $sql_temp = "SELECT * FROM tbl_item_group_details  WHERE item_group_id = '" . $_REQUEST['item_group_id'] . "'  ORDER BY item_position ASC ";
        $result = $conn->query($sql_temp);

        if ($result->rowCount() > 0) {

            $uom_id = $dbconn->GetSingleReconrd("mst_uom", "uom_name", "uom_status = '1' AND uom_id", $obj->uom_id);
            $html_output .=
         		 '<center><h6 class="modal-text pt-2"><b>'.$obj->item_group_name.'</h6></b></center>';
            $html_output .='<table class="table table-bordered table-striped"> 
							<tr>
								<th><b>S.No</b></th>';
            if ($_SESSION['_user_type'] == 'S') {
                $html_output .= '<th><b>Position</b></th>';
            }
            $html_output .= '<th><b>Sales Code</b></th>
			                    <th><b>Purchase Code</b></th>
								<th><b>Item Description</b></th>
								<th><b>Price</b></th>
								<th><b>Quantity</b></th>
							</tr>';

            $iSno = 1;

            while ($obj = $result->fetch()) {
                $product_decp = $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_status = '1' AND item_id ", $obj->item_id);
                $product_code = $dbconn->GetSingleReconrd("tbl_item_details", "item_code", "item_status = '1' AND item_id ", $obj->item_id);
				$purchase_code = $dbconn->GetSingleReconrd("tbl_item_details", "item_purchase_code", "item_status = '1' AND item_id ", $obj->item_id);
                $product_price = $dbconn->GetSingleReconrd("tbl_item_details", "item_selling_price", "item_status = '1' AND item_id ", $obj->item_id);

                $html_output .= '<tr>
										<td>' . $iSno . '</td>';
                if ($_SESSION['_user_type'] == 'S') {
                    $html_output .= '<td>' . $obj->item_position . '</td>';
                }
                $html_output .= '<td>' . $product_code . '</td>
				                        <td>' .$purchase_code . '</td>
										<td>' . $product_decp . '</td>
										<td style="text-align:right;">' . $product_price . '</td>
										<td>' . $obj->item_qty . '</td>
									</tr>';

                $iSno++;
            }
            $html_output .= '</table><br>';
        }
    }











    echo $item_group_code . '~' . $html_output;
}
