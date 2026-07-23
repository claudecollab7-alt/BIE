<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");
require_once("inc/common/css-js.php");

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

if (isset($_POST['SAVE'])) {
	try {
		$_REQUEST['sal_repair_date'] = date("Y-m-d", strtotime($_REQUEST['sal_repair_date']));

		$_REQUEST['sal_repair_slno'] = $dbconn->GetMaxValue('tbl_sales_repair', 'sal_repair_slno', 'company_id', $_SESSION['_user_branch']) + 1;
		$_REQUEST['sal_repair_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);

		$_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
		$_REQUEST['modify_by'] = $_SESSION['_user_id'];


		$stmt = null;
		$stmt = $conn->prepare("INSERT INTO tbl_sales_repair (sal_repair_finyr, sal_repair_slno, sal_repair_date, so_id, inv_id, supp_id, company_id, modify_by, modify_date_time) VALUES (:sal_repair_finyr, :sal_repair_slno, :sal_repair_date, :so_id, :inv_id, :supp_id, :company_id, :modify_by, :modify_date_time)");
		$data = array(
			':sal_repair_finyr' => $_REQUEST['sal_repair_finyr'],
			':sal_repair_slno' => $_REQUEST['sal_repair_slno'],
			':sal_repair_date' => $_REQUEST['sal_repair_date'],
			':so_id' => $_REQUEST['so_id'],
			':inv_id' => $_REQUEST['inv_id'],
			':supp_id' => $_REQUEST['supp_id'],
			':company_id' => $_SESSION['_user_branch'],
			':modify_by' => $_REQUEST['modify_by'],
			':modify_date_time' => $_REQUEST['modify_date_time'],
		);

		$stmt->execute($data);
		$last_id = $conn->lastInsertId();

		/* ------------ SAVE tbl_po_details  -----------*/
		$delete_details =  "DELETE FROM tbl_sales_repair_details WHERE sal_repair_id = '" . $last_id . "'";
		$result = $conn->prepare($delete_details);
		$result->execute();

		$stmt = null;
		$stmt = $conn->prepare("INSERT INTO tbl_sales_repair_details (sal_repair_id, repair_item_id, spare_item_id, repair_qty, repair_unit, repair_tax, repair_selling_price, repair_value, repair_tax_val, repair_net_val, repair_remarks)
		 VALUES (:sal_repair_id, :repair_item_id, :spare_item_id, :repair_qty, :repair_unit, :repair_tax, :repair_selling_price, :repair_value, :repair_tax_val, :repair_net_val, :repair_remarks)");

		$row_count = count($_REQUEST['temp_repair_item_id']);
		$sal_repair_value = 0;
		for ($n = 0; $n < $row_count; $n++) {

			$data = array(
				':sal_repair_id' => $last_id,
				':repair_item_id' => $_REQUEST['temp_repair_item_id'][$n],
				':spare_item_id' => 0,
				':repair_qty' => $_REQUEST['temp_repair_qty'][$n],
				':repair_unit' => $_REQUEST['temp_repair_unit'][$n],
				':repair_tax' => $_REQUEST['temp_repair_tax'][$n],
				':repair_selling_price' => $_REQUEST['temp_repair_selling_price'][$n],
				':repair_value' => $_REQUEST['temp_repair_value'][$n],
				':repair_tax_val' => $_REQUEST['temp_repair_tax_val'][$n],
				':repair_net_val' => $_REQUEST['temp_repair_net_val'][$n],
				':repair_remarks' => ''
			);
			$sal_repair_value = $sal_repair_value + $_REQUEST['temp_repair_net_val'][$n];

			$stmt->execute($data);
		}

		$update_quo = $conn->prepare("UPDATE  tbl_sales_repair SET sal_repair_value = :sal_repair_value	WHERE sal_repair_id = :sal_repair_id");
		$data1 = array(
			':sal_repair_id' => $last_id,
			':sal_repair_value' => $sal_repair_value
		);
		$update_quo->execute($data1);

		// $update_enq = $conn->prepare("UPDATE tbl_invoice SET repair_submit_status = :repair_submit_status WHERE inv_id = :inv_id");
		// $data1 = array(
		// 	':inv_id' => $_REQUEST['inv_id'],
		// 	':repair_submit_status' => 1
		// );
		// $update_enq->execute($data1);
	} catch (Exception $e) {
		$str = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
		$_SESSION['_msg_err'] = $str;
	}



	$_SESSION['_msg'] = "Repair Indent succesfully Saved..!";
	header("location:repair_indent_list.php");
	die();
}


if (isset($_POST['UPDATE'])) {
	$update_id = $_REQUEST['txtHid'];
	try {
		$_REQUEST['sal_repair_date'] = date("Y-m-d", strtotime($_REQUEST['sal_repair_date']));
		$_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
		$_REQUEST['modify_by'] = $_SESSION['_user_id'];
		$stmt = null;
		$stmt = $conn->prepare("UPDATE  tbl_sales_repair SET sal_repair_date = :sal_repair_date, company_id = :company_id, modify_by = :modify_by, modify_date_time = :modify_date_time,  sal_repair_verify_status = :sal_repair_verify_status, sal_repair_verify_date_time = :sal_repair_verify_date_time, sal_repair_verify_by = :sal_repair_verify_by WHERE sal_repair_id = :sal_repair_id");
		$data = array(
			':sal_repair_id' => $update_id,
			':sal_repair_date' => $_REQUEST['sal_repair_date'],
			':company_id' => $_SESSION['_user_branch'],
			':modify_by' => $_REQUEST['modify_by'],
			':modify_date_time' => $_REQUEST['modify_date_time'],
			':sal_repair_verify_status' => '0',
			':sal_repair_verify_date_time' => '',
			':sal_repair_verify_by' => '0'
		);

		$stmt->execute($data);

		$sql =  "DELETE FROM tbl_sales_repair_details WHERE sal_repair_id = '" . $update_id . "'";
		$result = $conn->prepare($sql);
		$result->execute();

		$stmt = null;
		$stmt = $conn->prepare("INSERT INTO tbl_sales_repair_details (sal_repair_id, repair_item_id, spare_item_id, repair_qty, repair_unit, repair_tax, repair_selling_price, repair_value, repair_tax_val, repair_net_val, repair_remarks)
		 VALUES (:sal_repair_id, :repair_item_id, :spare_item_id, :repair_qty, :repair_unit, :repair_tax, :repair_selling_price, :repair_value, :repair_tax_val, :repair_net_val, :repair_remarks)");

		$row_count = count($_REQUEST['temp_repair_item_id']);
		$sal_repair_value = 0;
		for ($n = 0; $n < $row_count; $n++) {

			$data = array(
				':sal_repair_id' => $update_id,
				':repair_item_id' => $_REQUEST['temp_repair_item_id'][$n],
				':spare_item_id' => 0,
				':repair_qty' => $_REQUEST['temp_repair_qty'][$n],
				':repair_unit' => $_REQUEST['temp_repair_unit'][$n],
				':repair_tax' => $_REQUEST['temp_repair_tax'][$n],
				':repair_selling_price' => $_REQUEST['temp_repair_selling_price'][$n],
				':repair_value' => $_REQUEST['temp_repair_value'][$n],
				':repair_tax_val' => $_REQUEST['temp_repair_tax_val'][$n],
				':repair_net_val' => $_REQUEST['temp_repair_net_val'][$n],
				':repair_remarks' => ''
			);
			$sal_repair_value = $sal_repair_value + $_REQUEST['temp_repair_net_val'][$n];
			$stmt->execute($data);
		}

		$update_po = $conn->prepare("UPDATE tbl_sales_repair SET sal_repair_value = :sal_repair_value WHERE sal_repair_id = :sal_repair_id");
		$data1 = array(
			':sal_repair_id' => $update_id,
			':sal_repair_value' => $sal_repair_value
		);
		$update_po->execute($data1);
	} catch (Exception $e) {
		$str = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
		$_SESSION['_msg_err'] = $str;
	}
	$_SESSION['_msg'] = "Repair Indent succesfully Updated..!";
	header("location:repair_indent_list.php");
	die();
}

$sal_repair_date = date('Y-m-d');
$so_id = 0;
$inv_id = 0;
$supp_id = 0;
$supp_name = '';

if (isset($_REQUEST['sal_repair_id'])) {
	$result = $conn->query("SELECT * FROM tbl_sales_repair WHERE sal_repair_status = '1' AND sal_repair_id = " . $_REQUEST['sal_repair_id']);
	if ($result->rowCount() > 0) {
		$get = $result->fetch(PDO::FETCH_OBJ);

		if ($get->sal_repair_date != "0000-00-00" && $get->sal_repair_date != "") {
			$sal_repair_date = date("Y-m-d", strtotime($get->sal_repair_date));
		}

		$so_id = $get->so_id;
		$inv_id = $get->inv_id;
		$supp_id = $get->supp_id;
		$supp_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_id", $supp_id);
	}
} elseif (isset($_REQUEST['inv_id'])) {
	$get_val = $conn->query("SELECT * FROM tbl_invoice WHERE inv_status = '1' AND inv_id = " . $_REQUEST['inv_id']);
	if ($get_val->rowCount() > 0) {
		$get = $get_val->fetch(PDO::FETCH_OBJ);

		if ($get->inv_date != "0000-00-00" && $get->inv_date != "") {
			$inv_date = date("d-m-Y", strtotime($get->inv_date));
		}

		$inv_id = $get->inv_id;
		$supp_id = $get->supp_id;

		$inv_no = $dbconn->GetSingleReconrd("tbl_invoice", "inv_slno", "inv_id", $inv_id);
		$supp_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_id", $supp_id);
		$so_id = $dbconn->GetSingleReconrd("tbl_invoice", "so_id", "inv_id", $inv_id);
	}
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>
		<?php echo PAGE_TITLE; ?>-Repair Indent
	</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<?php include_once("inc/common/css-js.php"); ?>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css" />
</head>

<body>
	<!-- Main navbar -->
	<?php include("inc/common/header.php") ?>
	<!-- Page content -->
	<div class="page-content">
		<!-- Main sidebar -->
		<?php include("inc/common/sidebar.php") ?>
		<!-- Main content -->
		<div class="content-wrapper">
			<!-- Page header -->
			<div class="page-header">
				<div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
					<div class="d-flex">
						<div class="breadcrumb">
							<a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
							<a href="#" class="breadcrumb-item">Work Area</a>
							<span class="breadcrumb-item active">Repair Indent</span>
						</div>
						<a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
					</div>
				</div>
			</div>
			<!-- /page header -->
			<!-- This Form UI Starts here --->
			<div class="content pt-0">
				<div class="row">
					<div class="col-md-12">
						<form name='thisForm' class="form-horizontal" method='post' action="repair_indent_add.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
							<fieldset>
								<div class="card">
									<div class="card-header bg-pgheader text-white header-elements-inline">
										<h6 class="card-title"> Repair Indent Add</h6>
										<?php
										if (isset($_REQUEST['sal_repair_id']) && $_REQUEST['sal_repair_id'] != "") {
											$repair_no = leadingZeros($dbconn->GetSingleReconrd('tbl_sales_repair', 'sal_repair_slno', 'sal_repair_status = "1" AND sal_repair_id', $_REQUEST['sal_repair_id']), 3);
										} else {
											$repair_no = leadingZeros($dbconn->GetMaxValue('tbl_sales_repair', 'sal_repair_slno', 'company_id', $_SESSION['_user_branch']) + 1, 3);
										}
										?>
										<input type="hidden" name="repair_items" id="repair_items" value="-1">
										<input type="hidden" name="so_id" value="<?php echo $so_id; ?>">

										<div class="header-elements">
											<div class="list-icons">
												<a class="list-icons-item" href="repair_indent_list.php" title="Repair Indent List"><i class="icon-arrow-left52 mr-2"></i></a>
												<a class="list-icons-item" data-action="fullscreen"></a>
											</div>
										</div>
									</div>
									<div class="card-body">

										<div class="form-group row">
											<label class="col-lg-2 col-form-label">Repair No <span class="text-mandatory"></span></label>
											<div class="col-lg-4">
												<input type="text" class="form-control" readonly id="repair_no" name="repair_no" tabindex="-1" style="font-size: 16px; color: blue; font-weight: bold;" value="<?php echo $repair_no; ?>" />
											</div>
											<label class="col-lg-2 col-form-label">Repair Date <span class="text-mandatory"> *</span></label>
											<div class="col-lg-4">
												<input type="date" name="sal_repair_date" id="sal_repair_date" class="form-control" value="<?php echo $sal_repair_date; ?>" placeholder="Date" />
											</div>
										</div>

										<div class="form-group row">
											<label class="col-lg-2 col-form-label">Customer<span class="text-mandatory"> *</span></label>
											<div class="col-lg-4">
												<select name="supp_id" id="supp_id" data-placeholder="Choose a Customer.." class="select select-search">
													<option value="">-- Select Customer --</option>
													<?php
													$dbconn = new dbhandler();
													echo $dbconn->fnFillComboFromTable_Where("supp_id", "CONCAT(supp_name,' - ',supp_add2)", "mst_supplier_new", "supp_id", " WHERE supp_status = '1' AND supp_type = 'C'");
													?>
												</select>
												<script>
													document.thisForm.supp_id.value = "<?php echo $supp_id; ?>";
												</script>
											</div>

											<label class="col-lg-2 col-form-label">Invoice </label>
											<div class="col-lg-4">
												<select name="inv_id" id="inv_id" data-placeholder="Choose a Customer.." class="select select-search">
													<option value="" selected>-- Select Invoice --</option>
													<?php
													if ($inv_id != '') {
														$dbconn = new dbhandler();
														echo $dbconn->fnFillComboFromTable_Where("inv_id", "CONCAT(inv_slno,' - ',inv_date)", "tbl_invoice", "inv_id", " WHERE inv_status = 1 and inv_id=" . $inv_id);
													} ?>
												</select>
												<script>
													document.thisForm.inv_id.value = "<?php echo $inv_id; ?>";
												</script>
											</div>
										</div>

										<!-- <legend class="font-weight-semibold"></legend> -->
										<legend class="font-weight-semibold "><i class="icon-address-book  mr-2"></i>Repair Indent Details</legend>

										<div class="row pt-1">
											<div class="col-md-12">
												<fieldset>
													<div class="form-group row">
														<div class="form-group col-md-3">
															<p><b>Item Name</b></p>
															<div>
																<select name="repair_item_id" id="repair_item_id" class="select  select-search">
																	<option value="">-- Select --</option>
																	<?php
																	$dbconn = new dbhandler();
																	echo $dbconn->fnFillComboFromTable_Where("item_id", "CONCAT(item_desciption,' - ',item_code)", "tbl_item_details", "item_id", " WHERE item_status=1 AND item_dept_sales=1");
																	?>
																</select>
															</div>
														</div>

														<!-- <div class="form-group col-md-2">
															<p><b>Spare Item Name</b></p>
															<input type="text" class="form-control" name="item_name" id="item_name" value="" />
															<input type="hidden" name="item_id" id="item_id" value="">
														</div> -->

														<div class="form-group pl-0 col-md-1">
															<p><b>Qty</b></p>
															<input type="text" class="form-control" name="repair_qty" id="repair_qty" onkeypress="return isNumberKey_With_Dot(event)" value="" />
														</div>

														<div class="form-group pl-2 col-md-1">
															<p><b>Unit</b></p>
															<input type="text" class="form-control" name="repair_unit" id="repair_unit" value="" tabIndex="-1" readonly />
														</div>

														<div class="form-group pl-0 col-md-1">
															<p><b>Sale Price</b></p>
															<input type="text" class="form-control" name="repair_selling_price" id="repair_selling_price" onkeypress="return isNumberKey_With_Dot(event)" tabIndex="-1" readonly value="" />
														</div>

														<div class="form-group pl-1 col-md-1">
															<p><b>VAT</b></p>
															<div class="input-append">
																<input type="text" class="form-control" name="repair_tax" id="repair_tax" maxlength="9" tabindex="-1" readonly value="" />
															</div>
														</div>

														<div class="form-group pl-1 col-md-1">
															<p><b>Total</b></p>
															<input type="text" class="form-control" name="repair_total_value" id="repair_total_value" maxlength="9" tabindex="-1" readonly value="" />
														</div>

														<div class="form-group pl-0" id="item_indv_add_btn">
															<button class="btn btn-success mr-2 mt-4 pt-1" id="add_items" name="add_items" type="button"> +</button>
														</div>
													</div>
												</fieldset>
											</div>
										</div>

										<div class="form-group row">
											<div id="package_table" class="col-md-12">
												<table class="table table-xs table-bordered" style="font-size: small !important;">
													<thead>
														<tr class="bg-teal">
															<th>Repair Item</th>
															<!-- <th>Spare Item</th> -->
															<th>Rate</th>
															<th>Qty</th>
															<th>Unit</th>
															<th>Item Value</th>
															<th>Taxable Value</th>
															<th>Tax %</th>
															<th>Amount</th>
														</tr>
													</thead>
													<tbody>
														<?php
														if (isset($_REQUEST['sal_repair_id'])) {
															$get_quo_pack_dets =  $conn->query("SELECT * FROM tbl_sales_repair_details WHERE sal_repair_id = '" . $_REQUEST['sal_repair_id'] . "'");

															if ($get_quo_pack_dets->rowCount() > 0) {
																while ($obj = $get_quo_pack_dets->fetch(PDO::FETCH_OBJ)) {
																	$repair_item_name = $dbconn->GetSingleReconrd(
																		"tbl_item_details",
																		"CONCAT(item_desciption,' - ',item_code)",
																		"item_status = '1' AND item_id",
																		$obj->repair_item_id
																	);

																	$spare_item_name = $dbconn->GetSingleReconrd(
																		"tbl_item_details",
																		"CONCAT(item_desciption,' - ',item_code)",
																		"item_status = '1' AND item_id",
																		$obj->spare_item_id
																	);


																	$row  = '<tr id="RI_' . $obj->spare_item_id . '">';
																	$row .= '<td>' . $repair_item_name . '
																			<input type="hidden" class="temp_repair_item_id" name="temp_repair_item_id[]" value="' . $obj->repair_item_id . '" />
																		</td>';
																	// $row .= '<td>' . $spare_item_name . '
																	// 		<input type="hidden" class="temp_spare_item_id" name="temp_spare_item_id[]" value="' . $obj->spare_item_id . '" />
																	// 	</td>';
																	$row .= '<td class="text-right">' . $obj->repair_qty . '
																			<input type="hidden" class="temp_repair_qty" name="temp_repair_qty[]" value="' . $obj->repair_qty . '" />
																		</td>';
																	$row .= '<td class="text-center">' . $obj->repair_unit . '
																			<input type="hidden" class="temp_repair_unit" name="temp_repair_unit[]" value="' . $obj->repair_unit . '" />
																		</td>';
																	$row .= '<td class="text-right">' . number_format($obj->repair_selling_price, 2, '.', '') . '
																			<input type="hidden" class="temp_repair_selling_price" name="temp_repair_selling_price[]" value="' . $obj->repair_selling_price . '" />
																		</td>';
																	$row .= '<td class="text-right">' . number_format($obj->repair_value, 2, '.', '') . '
																			<input type="hidden" class="temp_repair_value" name="temp_repair_value[]" value="' . $obj->repair_value . '" />
																		</td>';
																	$row .= '<td class="text-right">' . $obj->repair_tax . ' %
																			<input type="hidden" class="temp_repair_tax" name="temp_repair_tax[]" value="' . $obj->repair_tax . '" />
																			<input type="hidden" class="temp_repair_tax_val" name="temp_repair_tax_val[]" value="' . $obj->repair_tax_val . '" />
																		</td>';
																	$row .= '<td class="text-right">' . number_format($obj->repair_net_val, 2, '.', '') . '
																			<input type="hidden" class="temp_repair_net_val" name="temp_repair_net_val[]" value="' . $obj->repair_net_val . '" />
																		</td>';
																	// $row .= '<td class="text-center">
																	// 		<a href="javascript:remove_item(RI_' . $obj->spare_item_id . ');" rel="' . $obj->spare_item_id . '" title="Remove">
																	// 			<i class="icon-bin bg-delete mr-2"></i>
																	// 		</a>
																	// 	</td>';

																	$row .= '<td class="text-center">
																		<a href="javascript:remove_item(\'' . $obj->spare_item_id . '\');" title="Remove">
																			<i class="icon-bin bg-delete mr-2"></i>
																		</a>
																	</td>';
																	$row .= '</tr>';

																	echo $row;
																}
															}
														}
														$pack_total = $dbconn->GetSingleReconrd("tbl_sales_repair_details", "SUM(repair_net_val)", "sal_repair_id", $_REQUEST['sal_repair_id']);
														?>
													</tbody>
												</table>
											</div>
										</div>
									</div>
						</form>
						<div class="card-footer text-center pt-2">

							<?php if (isset($_REQUEST["sal_repair_id"])) { ?>
								<INPUT class="btn btn-custom mr-2" type="submit" name="UPDATE" value="UPDATE">
								<input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['sal_repair_id']; ?>">
								<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
							<?php } else { ?>
								<INPUT class="btn btn-custom mr-2" type="submit" name="SAVE" value="Save">
								<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
								<input type="hidden" name="txtHid" value="0">
							<?php } ?>

							<!-- End of This Form UI  --->
						</div>
					</div>
				</div>
			</div>

		</div>
</body>
<!-- Footer -->
<?php include("inc/common/footer.php") ?>
<!-- /footer -->
<!---------script-------->

<script language="javascript">
	var wasSubmitted = false;

	function fnValidate() {

		if (isNull(document.thisForm.supp_id, "Customer..!")) {
			return false;
		}

		var rowCount = $('#package_table tr').length;
		if (rowCount <= 1) {
			alert("Please add Repair Indent Details");
			return false;
		}

		if (!wasSubmitted) {
			wasSubmitted = true;
			document.thisForm.submit();
			return true;
		}
		return false;
	}
</script>

<!-- <script type="text/javascript"> 
	$(function() {
		<?php
		if ($_SESSION['_msg'] != "") {
			echo "$.jGrowl('" . $_SESSION['_msg'] . "', { sticky: false, theme: 'growl-success',shutdown:'0.9', header: 'Success!' });";
			$_SESSION['_msg'] = "";
		}

		if ($_SESSION['_msg_err'] != "") {
			echo "$.jGrowl('" . $_SESSION['_msg_err'] . "', { sticky: false, theme: 'growl-error', shutdown:'0.9', header: 'Error!' });";
			$_SESSION['_msg_err'] = "";
		}
		?>

		$('#repair_qty').change(function() {
			var qty = $(this).val();
			var inv_qty = $("#inv_qty").val();
			var item_id = $("#item_id").val();
			var repair_selling_price = $("#repair_selling_price").val();
			var repair_tax = $("#repair_tax").val();
			if (parseInt(qty) > parseInt(inv_qty)) {
				alert('Return Qty Must be Less Than the Invoice Qty');
				$("#repair_qty").val('');
				return false;
			}

			$.ajax({
				type: "POST",
				url: "inc/cis_ajax/jquery_get_sales_repair_total.php",
				data: {
					"qty": qty,
					"repair_selling_price": repair_selling_price,
					"repair_tax": repair_tax,
					"mode": 'get_net_amt'
				}
			}).done(function(msg) {
				$('#repair_total_value').val(msg);
			});
		});


		$('#add_items').click(function() {
			if (isNull(document.thisForm.repair_qty, "Qty..!")) {
				return false;
			}
			var repair_item_id = $("#repair_item_id").val();
			var item_id = $("#item_id").val();
			var repair_qty = $("#repair_qty").val();
			var repair_selling_price = $("#repair_selling_price").val();
			var repair_tax = $("#repair_tax").val();
			var repair_unit = $("#repair_unit").val();
			var arr = [];
			var is_ch = 0;

			$("#package_table tr").each(function() {
				arr.push(this.id);
			});
			// console.log(arr);
			if (jQuery.inArray('RI_' + item_id, arr) != -1) {
				var is_ch = 1;
			}

			$.ajax({
				type: "POST",
				url: "inc/cis_ajax/jquery_sales_repair_details.php",
				data: {
					"repair_item_id": repair_item_id,
					"item_id": item_id,
					"repair_qty": repair_qty,
					"repair_selling_price": repair_selling_price,
					"repair_tax": repair_tax,
					"repair_unit": repair_unit,
					'mode': 'save'
				}
			}).done(function(msg) {
				if (is_ch == 0) {
					$("#package_table tbody").append(msg);
				} else {
					$("#RI_" + item_id).replaceWith(msg);
				}

				var n = msg.indexOf("tbody");
				$('#repair_items').val(n);
				$("#item_id").val('');
				$("#item_name").val('');
				$("#repair_qty").val('');
				$("#inv_qty").val('');
				$('#repair_unit').val('');
				$("#repair_selling_price").val('');
				$('#repair_tax').val('');
				$('#repair_total_value').val('');

			});
		});

		$("#supp_id").change(function() {
			let supp_id = $(this).val();
			if (supp_id > 0) {
				$.ajax({
					type: "POST",
					url: "inc/cis_ajax/jquery_select_customer_invoice.php",
					data: {
						"supp_id": supp_id,
						"mode": "InvoiceList",

					}
				}).done(function(msg) {
					$('#inv_id option').remove();
					var dataArr = msg.split('#');
					$.each(dataArr, function(i, element) {
						if (dataArr[i] != "") {
							var dataArr2 = dataArr[i].split('~');
							$('#inv_id').append("<option value='" + dataArr2[0] + "'>" + dataArr2[1] + "</option>");
						}
					});
					$("#s2id_inv_id").select2('val', '');
					$("#inv_id").trigger("liszt:updated");
				});
			}
		});

		// $("#inv_id").change(function() {
		// 	let inv_id = $(this).val();
		// 	if (inv_id > 0) {
		// 		$.ajax({
		// 			type: "POST",
		// 			url: "inc/cis_ajax/jquery_select_customer_invoice.php",
		// 			data: {
		// 				"inv_id": inv_id,
		// 				"mode": "AMCDetails",

		// 			}
		// 		}).done(function(msg) {
		// 			$("#amc_data").html(msg);
		// 		});
		// 	}
		// }).trigger('change');

		$("#inv_id").change(function() {
			let inv_id = $(this).val();
			if (inv_id > 0) {
				$.ajax({
					type: "POST",
					url: "inc/cis_ajax/jquery_select_customer_invoice.php",
					data: {
						"inv_id": inv_id,
						"mode": "InvoiceItems",

					}
				}).done(function(msg) {

					$('#repair_item_id option').remove();
					var dataArr = msg.split('#');
					$.each(dataArr, function(i, element) {
						if (dataArr[i] != "") {
							var dataArr2 = dataArr[i].split('~');
							$('#repair_item_id').append("<option value='" + dataArr2[0] + "'>" + dataArr2[1] + "</option>");
						}
					});
					$("#s2id_repair_item_id").select2('val', '');
					$("#repair_item_id").trigger("liszt:updated");
				});
			}
		}).trigger('change');
		$("#repair_item_id").change(function() {

			$("#item_name").val('');
			$("#item_id").val('');
			$("#inv_qty").val('');
			$("#repair_selling_price").val('');
			$("#repair_tax").val('');
			$("#repair_unit").val('');

			var id = $(this).val();

			$("#item_name").autocomplete({
				minLength: 1,
				source: function(request, response) {
					$.ajax({
						url: "inc/auto/select_repair_indent_items.php",
						dataType: "text",
						data: {
							q: request.term,
							id: id
						},
						success: function(data) {

							var lines = data.split("\n");
							var result = [];

							$.each(lines, function(i, line) {
								if ($.trim(line) != '') {

									var parts = line.split("|");

									if (parts.length >= 2) {
										result.push({
											label: $.trim(parts[0]),
											value: $.trim(parts[0]),
											extra: $.trim(parts[1])
										});
									}
								}
							});

							response(result);
						}
					});
				},
				select: function(event, ui) {

					var string = ui.item.extra.split("~");

					$("#inv_qty").val($.trim(string[0]));
					$("#item_id").val($.trim(string[1]));
					$("#repair_selling_price").val($.trim(string[2]));
					$("#repair_tax").val($.trim(string[3]));
					$("#repair_unit").val($.trim(string[4]));
				}
			});

		});


	});

	function remove_item(temp_sal_repair_id) {
		$('#RI_' + temp_sal_repair_id).remove();

		// Update repair_items flag
		var rowCount = $('#package_table tbody tr').length;
		$('#repair_items').val(rowCount > 0 ? 1 : -1);
	}
</script> -->

<script type="text/javascript">
	$(function() {
		<?php
		if ($_SESSION['_msg'] != "") {
			echo "$.jGrowl('" . $_SESSION['_msg'] . "', { sticky: false, theme: 'growl-success',shutdown:'0.9', header: 'Success!' });";
			$_SESSION['_msg'] = "";
		}

		if ($_SESSION['_msg_err'] != "") {
			echo "$.jGrowl('" . $_SESSION['_msg_err'] . "', { sticky: false, theme: 'growl-error', shutdown:'0.9', header: 'Error!' });";
			$_SESSION['_msg_err'] = "";
		}
		?>

		// When qty changes, recalculate total
		$('#repair_qty').change(function() {
			var qty = $(this).val();
			var repair_selling_price = $("#repair_selling_price").val();
			var repair_tax = $("#repair_tax").val();

			$.ajax({
				type: "POST",
				url: "inc/cis_ajax/jquery_get_sales_repair_total.php",
				data: {
					"qty": qty,
					"repair_selling_price": repair_selling_price,
					"repair_tax": repair_tax,
					"mode": 'get_net_amt'
				}
			}).done(function(msg) {
				$('#repair_total_value').val(msg);
			});
		});


		$('#add_items').click(function() {
			if (isNull(document.thisForm.repair_qty, "Qty..!")) {
				return false;
			}

			var repair_item_id = $("#repair_item_id").val();
			var repair_qty = $("#repair_qty").val();
			var repair_selling_price = $("#repair_selling_price").val();
			var repair_tax = $("#repair_tax").val();
			var repair_unit = $("#repair_unit").val();

			// Check if this item is already in the table
			var arr = [];
			$("#package_table tr").each(function() {
				arr.push(this.id);
			});
			var is_ch = (jQuery.inArray('RI_' + repair_item_id, arr) != -1) ? 1 : 0;

			$.ajax({
				type: "POST",
				url: "inc/cis_ajax/jquery_sales_repair_details.php",
				data: {
					"repair_item_id": repair_item_id,
					"item_id": repair_item_id, // kept for backward-compat with AJAX handler
					"repair_qty": repair_qty,
					"repair_selling_price": repair_selling_price,
					"repair_tax": repair_tax,
					"repair_unit": repair_unit,
					"mode": 'save'
				}
			}).done(function(msg) {
				if (is_ch == 0) {
					$("#package_table tbody").append(msg);
				} else {
					$("#RI_" + repair_item_id).replaceWith(msg);
				}

				var n = msg.indexOf("tbody");
				$('#repair_items').val(n);

				// Reset all item input fields
				$("#repair_item_id").val('').trigger("liszt:updated");
				$("#repair_qty").val('');
				$('#repair_unit').val('');
				$("#repair_selling_price").val('');
				$('#repair_tax').val('');
				$('#repair_total_value').val('');
			});
		});


		// When customer changes — load their invoices
		$("#supp_id").change(function() {
			let supp_id = $(this).val();
			if (supp_id > 0) {
				$.ajax({
					type: "POST",
					url: "inc/cis_ajax/jquery_select_customer_invoice.php",
					data: {
						"supp_id": supp_id,
						"mode": "InvoiceList",
					}
				}).done(function(msg) {
					$('#inv_id option').remove();
					var dataArr = msg.split('#');
					$.each(dataArr, function(i, element) {
						if (dataArr[i] != "") {
							var dataArr2 = dataArr[i].split('~');
							$('#inv_id').append("<option value='" + dataArr2[0] + "'>" + dataArr2[1] + "</option>");
						}
					});
					$("#s2id_inv_id").select2('val', '');
					$("#inv_id").trigger("liszt:updated");
				});
			}
		});


		// When invoice changes — load its items into repair_item_id dropdown
		$("#inv_id").change(function() {
			let inv_id = $(this).val();
			if (inv_id > 0) {
				$.ajax({
					type: "POST",
					url: "inc/cis_ajax/jquery_select_customer_invoice.php",
					data: {
						"inv_id": inv_id,
						"mode": "InvoiceItems",
					}
				}).done(function(msg) {
					$('#repair_item_id option').remove();
					var dataArr = msg.split('#');
					$.each(dataArr, function(i, element) {
						if (dataArr[i] != "") {
							var dataArr2 = dataArr[i].split('~');
							$('#repair_item_id').append("<option value='" + dataArr2[0] + "'>" + dataArr2[1] + "</option>");
						}
					});
					$("#s2id_repair_item_id").select2('val', '');
					$("#repair_item_id").trigger("liszt:updated");
				});
			}
		}).trigger('change');


		// When item changes — directly fetch price/tax/unit/qty from server
		// $("#repair_item_id").change(function() {
		// 	// Clear all dependent fields first
		// 	$("#repair_qty").val('');
		// 	$("#repair_selling_price").val('');
		// 	$("#repair_tax").val('');
		// 	$("#repair_unit").val('');
		// 	$('#repair_total_value').val('');

		// 	var repair_item_id = $(this).val();
		// 	if (!repair_item_id) return;

		// 	$.ajax({
		// 		type: "POST",
		// 		url: "inc/auto/select_repair_indent_items.php",
		// 		data: {
		// 			"id": repair_item_id,
		// 			"mode": "get_item_details" // signal to return JSON/pipe-delimited details directly
		// 		}
		// 	}).done(function(data) {
		// 		// Expected response: "inv_qty~item_id~selling_price~tax~unit"
		// 		// (same format as the old autocomplete 'extra' field)
		// 		var parts = $.trim(data).split("~");
		// 		if (parts.length >= 5) {
		// 			$("#repair_selling_price").val($.trim(parts[2]));
		// 			$("#repair_tax").val($.trim(parts[3]));
		// 			$("#repair_unit").val($.trim(parts[4]));
		// 		}
		// 	});
		// });

		$("#repair_item_id").change(function() {
			// Clear dependent fields
			$("#repair_qty").val('');
			$("#repair_selling_price").val('');
			$("#repair_tax").val('');
			$("#repair_unit").val('');
			$('#repair_total_value').val('');

			var repair_item_id = $(this).val();
			if (!repair_item_id) return;

			$.ajax({
				type: "GET", // ← GET, not POST
				url: "inc/auto/select_repair_indent_items.php", // ← your actual path
				data: {
					"id": repair_item_id,
					"mode": "get_item_details"
				}
			}).done(function(data) {
				var parts = $.trim(data).split("~");
				// parts: [0]=qty, [1]=item_id, [2]=selling_price, [3]=tax, [4]=unit
				if (parts.length >= 5) {
					$("#repair_selling_price").val($.trim(parts[2]));
					$("#repair_tax").val($.trim(parts[3]));
					$("#repair_unit").val($.trim(parts[4]));
				}
			});
		});

	});


	function remove_item(repair_item_id) {
		$('#RI_' + repair_item_id).remove();

		var rowCount = $('#package_table tbody tr').length;
		$('#repair_items').val(rowCount > 0 ? 1 : -1);
	}
</script>

</html>