<?php
function tbl_header($dc_id, $boxtype, $boxno)
{
	include_once("inc/common/css-js.php");
	require_once("inc/common/userclass.php");

	isAdmin();
	$conn = new dbconnect();
	$dbconn = new dbhandler();

	$result = $conn->query("SELECT * FROM tbl_dc WHERE dc_id = " . $_REQUEST['dc_id']);
	if ($result->rowCount() > 0) {
		$obj = $result->fetch(PDO::FETCH_OBJ);

		$supp_code = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_code", "supp_status = 1 AND supp_id ", $obj->supp_id);
		$supp_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_status = 1 AND supp_id ", $obj->supp_id);

		if ($obj->dc_date != "0000-00-00" && $obj->dc_date != "") {
			$dc_date = date("d-m-Y", strtotime($obj->dc_date));
		}
	}

	if ($boxtype == 1) {
		$box_type = 'Corrugated Box';
	} elseif ($boxtype == 2) {
		$box_type = 'Wooden Box';
	} elseif ($boxtype == 3) {
		$box_type = 'Gunny Bags';
	} elseif ($boxtype == 4) {
		$box_type = 'Poly Bags';
	}

	//--------------------- Table Started ---------------------//

	echo ' <table class="table table-xs border-collapse: collapse;">

			<thead>
				<tr>
				<td>
				<table class="table table-xs table-bordered" witdh="100%">
				<tbody>

				<tr>
				<td rowspan="5" style="width:200px; border: 1px solid black !important;" align="center" ><img src="img/BIE_logo.png" style="width:100px;" alt=""></td>
				</tr>

				<tr>
				<td width="40%" align="right" style="background-color: #bebebe; border: 1px solid black !important;"><b>CUSTOMER NAME : </b></td>
				<td style = "border: 1px solid black !important;">' . $supp_name . '</td>
				</tr>

				<tr>
				<td align="right" style="background-color: #bebebe; border: 1px solid black !important;"><b>DC. NO : </b></td>
				<td style = "border: 1px solid black !important;">' . $obj->dc_refno . '</td>
				</tr>

				<tr>
				<td align="right" style="background-color: #bebebe; border: 1px solid black !important;"><b>DC. DATE : </b></td>
				<td style = "border: 1px solid black !important;">' . $dc_date . '</td>
				</tr>

				<tr>
				<td align="right" style="background-color: #bebebe; border: 1px solid black !important;"><b>BOX : </b></td>
				<td style = "border: 1px solid black !important;">' . $box_type . ' - <b>' . $boxno . '</b></td>
				</tr>
				</tbody>
				</table>
				</td>
				</tr>
			</thead>


			 <tbody>
              <tr><td>
					<table class="table table-xs table-bordered mystyle" witdh="100%" >
                    	<thead>
                            <tr style="font-weight:bold; background-color: #bebebe;" align="center" >
                                <td width="3%" style = "border: 1px solid black !important;">#</td>
                                <td width="25%" style = "border: 1px solid black !important;">PARTICULARS</td>
                                <td width="10%" style = "border: 1px solid black !important;">DC QTY</td>
                                <td width="10%" style = "border: 1px solid black !important;">UNIT</td>
                                <td width="10%" style = "border: 1px solid black !important;">CHECKED QTY</td>
                            </tr>
                    	</thead>
                        <tbody>';
}

//------------------- tbl_body ---------------------//

function tbl_body($item_id, $qty, $sno)
{
	require_once("inc/common/userclass.php");
	isAdmin();
	$conn = new dbconnect();
	$dbconn = new dbhandler();


	$item_code = $dbconn->GetSingleReconrd("tbl_item_details", "item_code", "item_status = '1' AND item_id", $item_id);
	$item_name = $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_status = '1' AND item_id", $item_id);
	$uom_id = $dbconn->GetSingleReconrd("tbl_item_details", "item_uom", "item_status = '1' AND item_id", $item_id);
	$unit = $dbconn->GetSingleReconrd("mst_uom", "uom_code", "uom_status = '1' AND uom_id", $uom_id);


	echo '<tr valign="top">
			<td align="center" style = "border: 1px solid black !important;">' . $sno . '</td>
			<td class="align-left" style = "border: 1px solid black !important;">' . $item_name . " - <b>" . $item_code . '</b></td>
            <td align="center" style = "border: 1px solid black !important;">' . $qty . '</td>
            <td align="center" style = "border: 1px solid black !important;">' . $unit . '</td>
            <td align="center" style = "border: 1px solid black !important;"></td>
        </tr>';
}


//----------------------tbl_foot----------------//

function tbl_foot()
{
	require_once("inc/common/userclass.php");
	isAdmin();
	$conn = new dbconnect();
	$dbconn = new dbhandler();

	echo '</tbody>
			</table>
      </td></tr>
    </tbody>
    <tfoot>
    	<tr>
    		<td>
				<table class="table table-xs table-bordered">
    				<tbody>
    					<tr>
    						<td align="center" style = "border: 1px solid black !important;">
    							</br>
    							</br>
    							</br>
    							Prepared By
    						</td>
    						<td align="center" style = "border: 1px solid black !important;">
    							</br>
    							</br>
    							</br>
    							Checked By
    						</td>
    						<td align="center" style = "border: 1px solid black !important;">
    							</br>
    							</br>
    							</br>
    							Dispatch By
    						</td>
    					</tr>
    				</tbody>
    			</table>
    		</td>
    	</tr>
    </tfoot>     
</table>';
	echo '<br/>';
	echo '<br/>';
	echo '<br/>';
}
