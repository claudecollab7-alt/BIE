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

$grn_id = $_POST['id'];

$supp_id = $dbconn->GetSingleReconrd("tbl_grn", "supp_id", "grn_id", $grn_id);
// $supplier = $dbconn->GetSingleReconrd("mst_supplier_new", "concat(supp_name)", "supp_id", $supp_id);
$grn_slno = $dbconn->GetSingleReconrd("tbl_grn", "grn_ref_code", "grn_id", $grn_id);
$grn_finyr = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);

// $so_id = $_GET['so_id'];

if ($grn_id != "") {

	$result = $conn->query("SELECT * FROM tbl_grn WHERE grn_id = " . $grn_id);

	if ($result->rowCount() > 0) {
		$obj = $result->fetch(PDO::FETCH_OBJ);

		$supp_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_status = 1 AND supp_id ", $obj->supp_id);
	}
}



$sales = '
<div class=" row" id="">
   <div class="col-lg-7">
		<label class="" style="text-align:left;">Cancelled GRN - <b>' .($obj->grn_ref_code).'</b></label>
    </div>
	
<div>';


$Credit = '
<br>
<div class="row">
	<div class="row col-lg-12"style="margin-left:10px;">

		<table width="100%">

			<tr><td width="30%">Supplier</td><td>: <strong style="color:blue;">' . $supp_name . '</strong></td></tr>

			<tr><td>GRN No</td><td>: <strong style="color:blue;">' . $obj->grn_ref_code . '</strong></td></tr>

			<tr><td>GRN Date</td><td>: <strong style="color:blue;">' . date("d-m-Y", strtotime($obj->grn_date)) . '</strong></td></tr>


		</table>

	</div>

</div>
<hr>

<div class="row-fluid well">
		
		<div class="row col-lg-12 ">
		<input type="hidden" name="grn_id" value="'.$grn_id.'">

		<label ><span><strong>Reason <span style="color: #e82828;font-weight:800;">(Please enter the Reason)</span></strong></span><br></label>
		<textarea  type="text" name="grn_cancel_reason" required id="grn_cancel_reason"  class=" col-lg-12 "  maxlength="250" ></textarea>

	</div>';
	

?>


<?php

echo $sales . '~' . $Credit;

?>
