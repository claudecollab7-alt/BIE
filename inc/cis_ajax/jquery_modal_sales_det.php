<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$conn = new dbconnect();
$dbconn = new dbhandler();

$so_id = $_POST['id'];

$so_slno = $dbconn->GetSingleReconrd("tbl_sales_order", "so_slno", "so_id", $so_id);
$so_finyr = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);

$inv_netamt = $dbconn->GetSingleReconrd("tbl_sales_order", "item_net_val", "so_id", $so_id);
$supp_id = $dbconn->GetSingleReconrd("tbl_sales_order", "supp_id", "so_id", $so_id);
$supplier = $dbconn->GetSingleReconrd("mst_supplier_new", "concat(supp_name,' - ',supp_mobile1)", "supp_id", $supp_id);


$sales = '';
$recno = '
<div class=" row" id="">
   <div class="col-lg-7">
		<label class="" style="text-align:left;">Receipt Details of - <b>'.(leadingZeros($so_slno, 3)). '/BEN/SO' .$so_finyr.'</b></label>
    </div>
	

<div>';
	
	


$sales .= '<div class=" pb-2 pt-2" style="">
<div class=" row" id="">
		<label class="col-lg-7" style="text-align: left;"><h6><b>Supplier : </b><b style="color:blue;">' . $supplier . '</b></h6></label>

		<label class="col-lg-5" style="text-align:right;"><h6><b>SO Amt :</b><b style="color:red;" >'.(number_format($inv_netamt, 2)) .'</b></h6></label>
	</div>									
		
			<div class=" pb-3 pt-2">
            <table class=" table table-xs table-bordered col-lg-12 " style="font-size: small !important;" >
				<thead style="width:10%">
                <tr class="bg-teal">
					<th> Date </th>
					<th> Amount </th>
					<th> Mode </th>
					<th > Remarks </th>
                    </tr>
				</thead>';
                
              
               
?>
                <?php

				$result = $conn->query("SELECT * FROM tbl_receipt WHERE pay_status = 0 AND so_id = '" . $so_id . "' ORDER BY pay_date");
				if ($result->rowCount() > 0) {
					$Sno = 1;
					$total_amt = 0;
					
					$sales .= '<tbody>';
					while ($obj = $result->fetch()) {
						
						$remarks = '';
						if ($obj->pay_type == "C") {
							$pmode = "Cash";
						} elseif ($obj->pay_type == 'Q') {
							$pmode = "Cheque dt. " . MyFormatDate($obj->pay_chq_dt) . " No. " . $obj->pay_chq_no;
						} elseif ($obj->pay_type == 'B') {
							$pmode = "Card";
						} elseif ($obj->pay_type == 'N') {
							$pmode = "Net Banking";
						} else {

							$pmode = "Credit note againt invoice";
							$remarks = 'Invoice ' . $invoice_no;
						}

						$sales .= '<tr class="align-left">						
								<td>' . MyFormatDate($obj->pay_date) . '</td>
								<td align="right">' . number_format($obj->pay_amount, 2) . '</td>
								<td>' . $pmode . '</td>									
								<td>' . $obj->pay_remarks .$remarks1.'</td>									
							</tr>';
						$total_amt = $total_amt + $obj->pay_amount;
						$Sno++;
					}
					$sales .= '</tbody>';
				}

				$bal = floatval($inv_netamt) - floatval($total_amt);

				$sales .= '<tr style="font-weight:bold">								
							<td align="right"> Total </td>
							<td align="right" style="color:red;">' . number_format($total_amt, 2) . '</td>
							<td align="right"></td>	
							<td align="right"></td>	
						</tr>';
				$sales .= '<tr style="font-weight:bold">								
							<td align="right"> Balance </td>
							<td align="right" style="color:blue;">' . number_format($bal, 2) . '</td>
							<td align="right"></td>	
							<td align="right"></td>	
						</tr>';
				$sales .= '</table>
				
				</div>
			</div>';
			
				echo $recno . '~' . $sales;
				?>


