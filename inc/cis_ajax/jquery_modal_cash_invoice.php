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

$inv_refno = $_REQUEST['po_title'];

if (isset($_REQUEST['id'])) {

	$result = $conn->query("SELECT * FROM tbl_invoice_denomination_details WHERE crd_id='" . $_REQUEST['id'] . "'");
	$cash_amount = $dbconn->GetSingleReconrd("tbl_invoice_credit_details", "paid_amount", "crd_id", $_REQUEST['id']);
	$pay_date = $dbconn->GetSingleReconrd("tbl_invoice_credit_details", "paid_date", "crd_id", $_REQUEST['id']);
	

	$inv = '<div class=" row" id="">
   <div class="col-lg-12">
		<label class="" style="text-align:left;">Invoice Cash Details of- <b>'.$inv_refno.'</b></b></label>
    </div>
<div>';

	$cash = "";
	$cash .= '<div class=" pb-2 pt-2" style="">
	
	<div class=" row" id="">
							<label class=" col-lg-4 " style="text-align: left;"><h6>Pay Date : <b>'.MyFormatDate($pay_date ).'</b></h6></label>

							
							<label  class=" col-lg-8" style="text-align: right;" ><h6> Received Amount : <b style="color:red;">' .$cash_amount . '</b></h6>   </label>
							
						</div>	

			<div class=" pb-3 pt-2">
				<table class=" table table-xs table-bordered" style="font-size: small !important;" >	

					<thead style="width:10%;">
					<tr class="bg-teal">
						<th > Cash Denomination </th>
						<th> Cash Count </th>
						<th> Cash Value </th>
						</tr>
					</thead>';
	if ($result->rowCount() > 0) {

		$total_amt = 0;
		$cash .= "<tbody>";
		while ($obj = $result->fetch()) {
			$cash_name = $dbconn->GetSingleReconrd("tbl_cash_details", "cash_name", "cash_id", $obj->cash_id);

			$cash .= '<tr class="align-left" >					
																	<td >' . $cash_name . '</td>
																	<td >' . $obj->den_count . '</td>
																	<td align="right">' . number_format($obj->den_total, 2) . '</td>									
																</tr>';
			$total_amt = $total_amt + $obj->den_total;
		}
		$cash .= "</tbody>";
	}
	$cash .= '<tr style="font-weight:bold">								
													<td align="right"></td>	
													<td align="right"> Total </td>
													<td align="right" style="color:red;">' . number_format($total_amt, 2) . '</td>
												</tr>';

	$cash .= '</table>
			

        </div>       
    </div>';
	echo $inv . '~' .$cash ;
}
