<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
// require_once ("inc/common/dbrowiterator.php");	

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

// if (!isset($_REQUEST['branch_id']) || $_REQUEST['branch_id'] == '') {
//     $_REQUEST['branch_id'] = 2	;
// }
//  ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);


if (isset($_POST['UPDATE'])) {
	$update_id = $_REQUEST['txtHid'];
	try {
		$result = $conn->query("SELECT * FROM tbl_item_details WHERE item_id = " . $_REQUEST['item_id']);
		if ($result->rowCount() > 0) {
			$obj = $result->fetch(PDO::FETCH_OBJ);
		}
		$branch_ids = '1';

		if (isset($_REQUEST['branch_id'])) {
			foreach ($_REQUEST['branch_id'] as $key => $value) {
				$branch_ids = implode(',', $_REQUEST['branch_id']);
				// $branch_ids = '1,' . implode(',', $_REQUEST['branch_id']);
			}
		} else {
			$branch_ids = '';
		}

		$_REQUEST['updated_dtm'] = date('Y-m-d H:i:s');
		$_REQUEST['updated_by'] = $_SESSION['_userid'];


		$stmt = null;
		$stmt = $conn->prepare("UPDATE tbl_item_details SET branch_id = :branch_id, branch_status = :branch_status WHERE item_id = :item_id");
		$data = array(
			':item_id' => $update_id,
			':branch_id' => $branch_ids,
			':branch_status' => 1
		);
		$stmt->execute($data);
		



		$_SESSION['_msg'] = "Item Branches successfully updated..!";
	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		$_SESSION['_msg_err'] = $str;
	}

	header("location:lst_item_details.php");
	die();
}

if ($_REQUEST['item_id'] != "") {

	$result = $conn->query("SELECT * FROM tbl_item_details WHERE item_id = " . $_REQUEST['item_id']);
	if ($result->rowCount() > 0) {
		$obj = $result->fetch(PDO::FETCH_OBJ);
		$item_id = $obj->item_id;
		$new_item_uom = $obj->item_uom;
		$new_item_hsn = $obj->item_hsn;
	}
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<title><?php echo PAGE_TITLE; ?> - Item Update </title>
	<link href="css/main.css" rel="stylesheet" type="text/css" />
	<!--[if IE 8]><link href="css/ie8.css" rel="stylesheet" type="text/css" /><![endif]-->

	<?php include_once("inc/common/css-js.php"); ?>


	<script type="text/javascript">
		$(document).ready(function() {
			<?php
			if (isset($_SESSION['_msg']) && $_SESSION['_msg'] != "") {
				echo "$.jGrowl('" . $_SESSION['_msg'] . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-success', position: 'bottom-right', life:'2000', header: 'Success!' });";
				$_SESSION['_msg'] = "";
			}
			if (isset($_SESSION['_msg_err']) && $_SESSION['_msg_err'] != "") {
				echo "$.jGrowl('" . $_SESSION['_msg_err'] . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-danger', position: 'top-right', shutdown:'3000', header: 'Error!' });";
				$_SESSION['_msg_err'] = "";
			}
			?>
			$("#branch_id").change(function() {
				var branch_id = $(this).val();
				var item_id = $('#item_id').val();
				$.ajax({
					type: "POST",
					url: "inc/cis_ajax/jquery_get_item_details.php",
					data: {
						"branch_id": branch_id,
						"item_id": item_id
					}
				}).done(function(msg) {
					// alert(msg);
					var data = msg.split('~');
					$('#branch_new_price').val(data[0]);
					$('#branch_new_discount').val(data[1]);
					$('#branch_new_cost_price').val(data[2]);
					$('#branch_new_selling_price').val(data[3]);
					$('#branch_new_msq').val(data[4]);
					$('#branch_new_maq').val(data[5]);
					$('#branch_new_moq').val(data[6]);
					$('#branch_stock_field').val(data[7]);

				});
			}).trigger('change');

			<?php
			//if ($_SESSION['_user_type'] == 'A')
			//{ 
			?>
			//$('.hide_price').show();
			<?php	//}else{ 
			?>
			//$('.hide_price').hide();
			<?php	//}  
			?>

			$("#branch_new_price,#branch_new_discount").keyup(function() {
				var item_price = parseFloat($("#branch_new_price").val());
				var item_discount = parseFloat($("#branch_new_discount").val());
				if (item_price == '') {
					item_price = 0;
				}
				if (item_discount == '') {
					item_discount = 0;
				}
				if (isNaN(item_price)) {
					item_price = 0;
				}
				if (isNaN(item_discount)) {
					item_discount = 0;
				}
				var dis_amt = ((item_price * item_discount) / 100);
				var cost_price = item_price - dis_amt;

				$("#branch_new_cost_price").val(cost_price.toFixed(2));
			}).trigger('keyup');

		});

		function fnValidate() {
			// alert("validation")

			var selectElement = document.getElementById("branch_id");

			// Check if at least one option is selected
			if (selectElement.selectedIndex === -1) {
			alert("Please select at least one Branch");
			return false;
			}
			return true;

			document.thisForm.submit();
		}
	</script>
</head>


<body>
	<!-- Main navbar -->
	<?php include("inc/common/header.php") ?>
	<!-- /main navbar -->


	<!-- Page content -->
	<div class="page-content">

		<!-- Main sidebar -->
		<?php include("inc/common/sidebar.php") ?>
		<!-- /main sidebar -->


		<!-- Main content -->
		<div class="content-wrapper">

			<!-- Page header -->
			<div class="page-header">
				<div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
					<div class="d-flex">
						<div class="breadcrumb">
							<a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Dashboard</a>
							<a href="#" class="breadcrumb-item"> Item Masters</a>
							<span class="breadcrumb-item active">Branch Wise Item Update</span>
						</div>
						<a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
					</div>
				</div>
			</div>
			<!-- /page header -->

			<!-- Content area -->
			<div class="content pt-0">
				<!-- Dashboard content -->
				<div class="row">
					<div class="col-md-12">
						<!-- This Form UI Starts here --->

						<!-- Basic datatable -->
						<form name='thisForm' class="form-horizontal" method='POST' action="" onSubmit="return fnValidate();" enctype="multipart/form-data">
							<input type="hidden" name="item_id" id="item_id" value="<?php echo $_REQUEST['item_id']; ?>">

							<div class="card" style="width:50%; margin: 0 auto;">
								<div class="card-header bg-pgheader text-white header-elements-inline">
									<h6 class="card-title">Update Item Details</h6>
									<div class="header-elements">
										<div class="list-icons">
											<a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
											<a class="list-icons-item" href="lst_item_details.php" title="customer List"><i class="icon-arrow-left52 mr-2"></i></a>
											<a class="list-icons-item" data-action="fullscreen"></a>

										</div>
									</div>
								</div>

								<div class="card-body">
									<div class="form-group row pt-2">

										<label class="col-lg-2 col-form-label ">Branch<span class="text-mandatory"> *</span></label>

										<div class="col-lg-4">
											<select name="branch_id[]" id="branch_id" data-placeholder="Choose a Item Type.." class="select" multiple>
												<option value="">--- Select Branch ---</option>
												<?php
												//echo 'test';
												$supplier_query = "SELECT branch_id,branch_name FROM mst_branch WHERE branch_status = 1 ";
												$supp_res = $conn->query($supplier_query);
												if ($supp_res->rowCount() > 0) {

													if ($obj->branch_id != '') {
														$branch_ids = explode(',', $obj->branch_id);
														// Add '1' to the beginning of the array
														array_unshift($branch_ids, '1');
														$supp_ids = $branch_ids;
													}

													while ($suppObj = $supp_res->fetch()) {
														$selected = '';
														if (isset($suppObj->branch_id) && isset($supp_ids)) {
															if (in_array($suppObj->branch_id, $supp_ids)) {
																$selected = "selected";
															}
														}

														echo '<option value="' . $suppObj->branch_id . '" ' . $selected . '>' . $suppObj->branch_name . '</option>';
													}
												}
												?>
											</select>
										</div>
									</div>


								</div>
								<div class="card-footer text-center pt-2">
									<?php if ($_REQUEST["item_id"] != '') { ?>
										<INPUT class="btn btn-info" type="submit" name="UPDATE" value="UPDATE">
										<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:window.location.href='lst_item_details.php'">
										<input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['item_id']; ?>">
									<?php } else { ?>
										<INPUT class="btn btn-info" type="submit" name="SAVE" value="SAVE">
										<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:window.location.href='lst_item_details.php'">
										<input type="hidden" name="txtHid" value="0">
									<?php } ?>
								</div>


							</div>

						</form>

					</div>

					<!-- End of This Form UI  --->

				</div>

				<!-- /dashboard content -->
			</div>
			<!-- /content area -->
			<?php include("inc/common/footer.php") ?>


		</div>

	</div>

	</div>


	<!-- /page content -->
</body>

</html>