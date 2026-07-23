<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$cus_name = $_POST['cus_name'];
$inv_no = $_POST['inv_no'];
// $pay_remarks = $_POST['pay_remarks'];
if (isset($_REQUEST['id'])) {

	$result = $conn->query("SELECT * FROM tbl_receipt as a LEFT JOIN tbl_receipt_details as b ON a.receipt_id = b.receipt_id WHERE b.receipt_id='" . $_REQUEST['id'] . "'");
	// $name = $result->fetch();
	$cash_amount = $dbconn->GetSingleReconrd("tbl_receipt", "pay_amount", "receipt_id", $_REQUEST['id']);
	$pay_date = $dbconn->GetSingleReconrd("tbl_receipt", "pay_date", "receipt_id", $_REQUEST['id']);
	$cash = "";
	$cash .= '<div class=" pb-2 pt-2" style="">
				
							<div class=" row" id="">
							<label class=" col-lg-4 " style="text-align: left;"><h6>Pay Date - <b>'.MyFormatDate($pay_date).'</b></h6></label>

							<label class=" col-lg-3 "><h6>Pay Mode - <b>Cash</b></h6></label>
							
							<label  class=" col-lg-5" style="text-align: right;" ><h6> Received Amount :<b>'  . number_format($cash_amount, 2) . '</b></h6>   </label>
							
						</div>	
				
	
			<div class=" pb-3 pt-2">
				<table class=" table table-xs table-bordered" style="font-size: small !important;" >	

					<thead style="width:10%;">
					<tr class="bg-teal">
						<th > Cash Denomination </th>
						<th> Cash Count </th>
						<th> Cash Value </th>
						<th> Remark </th>
						</tr>
					</thead>';
	if ($result->rowCount() > 0) {

		$total_amt = 0;
		$cash .= "<tbody>";
		while ($obj = $result->fetch()) {
			$cash_name = $dbconn->GetSingleReconrd("tbl_cash_details", "cash_name", "cash_id", $obj->cash_id);

			$cash .= '<tr class="align-left" >					
																	<td >' . $cash_name . '</td>
																	<td >' . $obj->cash_count . '</td>
																	<td >' . number_format($obj->cash_value, 2) . '</td>
																	<td >'.$obj->pay_remarks.'</td>										
																</tr>';
			$total_amt = $total_amt + $obj->cash_value;
		}
		$cash .= "</tbody>";
	}
	$cash .= '<tr style="font-weight:bold">								
													<td align="right"></td>	
													<td align="right"> Total </td>
													<td align="right">' . number_format($total_amt, 2) . '</td>
												</tr>';

	$cash .= '</table>
			

        </div>       
    </div>';
	echo $cus_name . '~' . $cash . '~' . $inv_no;
}
