<?php

ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();

$so_id = $_POST['id'];

$supp_id = $dbconn->GetSingleReconrd("tbl_sales_order", "supp_id", "so_id", $so_id);
// $supplier = $dbconn->GetSingleReconrd("mst_supplier_new", "concat(supp_name)", "supp_id", $supp_id);
$so_slno = $dbconn->GetSingleReconrd("tbl_sales_order", "so_slno", "so_id", $so_id);
$so_finyr = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);

// $so_id = $_GET['so_id'];

if ($so_id != "") {

	$result = $conn->query("SELECT * FROM tbl_sales_order WHERE so_id = " . $so_id);

	if ($result->rowCount() > 0) {
		$obj = $result->fetch(PDO::FETCH_OBJ);

		$supp_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_status = 1 AND supp_id ", $obj->supp_id);
	}
}



$sales = '
<div class=" row" id="">
   <div class="col-lg-7">
		<label class="" style="text-align:left;">Approval for Credit Sales - <b>' .(leadingZeros($so_slno, 3)). '/BEN/SO' .$so_finyr.'</b></label>
    </div>
	
<div>';


$Credit = '
<br>
<div class="row">
	<div class="row col-lg-12"style="margin-left:10px;">

		<table width="100%">

			<tr><td width="30%">Customer</td><td>: <strong style="color:blue;">' . $supp_name . '</strong></td></tr>

			<tr><td>SO No</td><td>: <strong style="color:blue;">' . leadingZeros($obj->so_slno, 3) . '</strong></td></tr>

			<tr><td>SO Date</td><td>: <strong style="color:blue;">' . date("d-m-Y", strtotime($obj->so_date)) . '</strong></td></tr>

			<tr><td>SO Value</td><td>: <strong style="color:red;">' . number_format($obj->item_net_val, 2) . '</strong></td></tr>

			<tr><td>Accounts Remarks</td><td>: <strong style="color:blue;">' . $obj->pay_remarks . '</strong></td></tr>

		</table>

	</div>

</div>
<hr>

<div class="row-fluid well">
		<div class="row col-lg-12 ">
		<label class="row col-lg-12 "><strong>Credit days  </strong></label><br>
		<input type="hidden" name="so_id" value="'.$so_id.'">
		
		<input class=" col-lg-12 " type="text" name="so_credit_days" onkeypress="return isNumberKey(event)" required id="so_credit_days" maxlength="2" />
		</div><hr>
		<div class="row col-lg-12 ">
		<label ><span><strong>Remarks <span style="color: #e82828;font-weight:800;">(Please specify service engineer)</span></strong></span><br></label>
		<textarea  type="text" name="so_credit_approval_remarks" required id="so_credit_approval_remarks"  class=" col-lg-12 "  maxlength="250" ></textarea>

	</div>';
	

?>


<?php

echo $sales . '~' . $Credit;

?>
