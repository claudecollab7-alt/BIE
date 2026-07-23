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


$item_id = $_POST['id'];

$item_name = $dbconn->GetSingleReconrd("tbl_item_details", "concat(item_code,' - ',item_desciption)", "item_id", $item_id);

$branch_rack = $dbconn->GetSingleReconrd("mst_branch", "branch_stock_location_rack_field", "branch_id", $_SESSION['_user_branch']);
$branch_row = $dbconn->GetSingleReconrd("mst_branch", "branch_stock_location_row_field", "branch_id", $_SESSION['_user_branch']);

if (isset($_POST['id'])) {
	$get_val = $conn->query("SELECT $branch_rack as branch_rack, $branch_row as branch_row  FROM tbl_item_stock  WHERE item_id =' " . $item_id . "'");
	if ($get_val->rowCount() > 0) {
		$obj = $get_val->fetch(PDO::FETCH_OBJ);

		$rack_field = $obj->branch_rack;
		$row_field = $obj->branch_row;
	}
}
$stock = '

 <input type="hidden" name="item_id" id="item_id" value="' . $item_id . '">
 <input type="hidden" name="item_id" id="item_id" value="' . $_SESSION['_user_id'] . '">

<form name="thisForm" class="form-horizontal" method="post" action ="jquery_modal_stock_det.php" onSubmit="return fnValidate();">	
	<label class="col-lg-12 pt-2" style="text-align: left;"><b>Item : </b><b style="color:blue;">' . $item_name . '</b></label>
	<label class="col-lg-12 pt-2" style="text-align: left;"><b>Branch : </b><b style="color: red;">' . $_SESSION['_user_name']  . '</b></label>
<div style="text-align: center;">
		<div class="row pb-2 pt-2" >
			<div  class="col-lg-3" style="font-size: 14px; font-weight: bold; text-align: right;">Rack :</div>
			<div  class="col-lg-5" style="font-size: 14px; font-weight: bold; text-align: left;">
				<input type="text" id="branch_loc_rack" class="form-control"  name="branch_loc_rack" value="' . $rack_field . '" maxlength="5" onkeypress="return (event.charCode !=8 && event.charCode ==0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))" />
			</div>
		</div>
		<div class="row pt-2">
			<div  class="col-lg-3" style="font-size: 14px; font-weight: bold; text-align: right;">Row :</div>

			<div  class="col-lg-5" style="font-size: 14px; font-weight: bold; text-align: left;">
				<input  type="text" id="branch_loc_row" class="form-control"  name="branch_loc_row" value="' . $row_field . '" maxlength="5"  onkeypress="return (event.charCode !=8 && event.charCode ==0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))"/>
			</div>

		</div>
      </div>
		<br>
		<div class="card-footer text-center">
			<input class="btn btn-custom" type="button" name="SAVE" id="SAVE" value="SAVE">
			<a href = "store_stock_list.php"><input class="btn btn-light" type="button" name="CANCEL" id="CANCEL" value="CANCEL"></a>
		</div>  
		<br>      
</form>';

echo $stock;
?>

<script type="text/javascript">
	$('#SAVE').click(function() {

		var item_id = $('#item_id').val();
		var branch_loc_row = $('#branch_loc_row').val();
		var branch_loc_rack = $('#branch_loc_rack').val();


		if (branch_loc_rack <= 0) {
			alert("Please Enter The Rack..!");
			$('#branch_loc_rack').val('');
			return false();
		}
		if (branch_loc_row <= 0) {
			alert("Please Enter The Row..!");
			$('#branch_loc_row').val('');
			return false();
		}

		$.ajax({
				type: "POST",
				url: 'inc/cis_ajax/jquery_store_location_dets.php',
				data: {
					"item_id": item_id,
					"branch_loc_row": branch_loc_row,
					"branch_loc_rack": branch_loc_rack,
					"mode": 'save'
				}
			})
			.done(function(msg) {
				$('.close').trigger('click');
			});
	});
</script>