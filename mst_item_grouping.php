<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

//ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL); 


if (isset($_POST['SAVE'])) {
	$_REQUEST['item_group_slno'] = $dbconn->GetMaxValue('tbl_item_group', 'item_group_slno', '1', 1) + 1;
	$_REQUEST['created_dt'] = date('Y-m-d H:i:s');
	$_REQUEST['created_by'] = $_SESSION['_user_id'];

	try {
		$stmt = null;
		$stmt = $conn->prepare("INSERT INTO tbl_item_group (item_group_slno, item_group_index,item_group_name, item_group_code, uom_id, item_group_remarks, company_id, created_dt, created_by) VALUES (:item_group_slno, :item_group_index,:item_group_name, :item_group_code, :uom_id, :item_group_remarks, :company_id, :created_dt, :created_by)");
		$data = array(
			':item_group_slno' => $_REQUEST['item_group_slno'],
			':item_group_index' => $_REQUEST['item_group_index'],
			':item_group_name' => $_REQUEST['item_group_name'],
			':item_group_code' => strtoupper($_REQUEST['item_group_code']),
			':uom_id' => $_REQUEST['uom_id'],
			':item_group_remarks' => $_REQUEST['item_group_remarks'],
			':company_id' => 1,
			':created_dt' => $_REQUEST['created_dt'],
			':created_by' => $_REQUEST['created_by']
		);
		$stmt->execute($data);
		$last_id = $conn->lastInsertId();


		$delete_details =  "DELETE FROM tbl_item_group_details WHERE item_group_id = '" . $last_id . "'";
		$result = $conn->prepare($delete_details);
		$result->execute();

		$result = $conn->query("SELECT * FROM tbl_item_group_details_temp WHERE session_id = '" . $_SESSION['session_id'] . "'");

		if ($result->rowCount() > 0) {
			$stmt = null;
			$stmt = $conn->prepare("INSERT INTO tbl_item_group_details (item_group_id, item_id, item_qty, item_position) VALUES (:item_group_id, :item_id, :item_qty, :item_position)");

			while ($obj = $result->fetchAll(PDO::FETCH_ASSOC)) {
				foreach ($obj as $row => $value) {
					$data = array(
						':item_group_id' => $last_id,
						':item_id' => $value['temp_item_id'],
						':item_qty' => $value['temp_item_qty'],
						':item_position' => $value['temp_item_position']
					);
					$stmt->execute($data);
				}
			}

			$sql =  "DELETE FROM tbl_item_group_details_temp WHERE session_id = '" . $_SESSION['session_id'] . "'";
			$result = $conn->prepare($sql);
			$result->execute();
		}
		$_SESSION['_msg'] = "Group succesfully saved..!";
	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		$_SESSION['_msg_err'] = $str;
	}

	header("location:lst_item_grouping.php");
	die();
}

if (isset($_POST['UPDATE'])) {

	$_REQUEST['modify_dt'] = date('Y-m-d H:i:s');
	$_REQUEST['modify_by'] = $_SESSION['_user_id'];

	$update_id = $_REQUEST['txtHid'];
	try {
		$stmt = null;
		$stmt = $conn->prepare("UPDATE tbl_item_group SET item_group_index=:item_group_index,item_group_name = :item_group_name, item_group_code = :item_group_code, uom_id = :uom_id, item_group_remarks = :item_group_remarks, company_id = :company_id, modify_dt = :modify_dt, modify_by = :modify_by
					WHERE item_group_id = :item_group_id");
		$data = array(
			':item_group_id' => $update_id,
			':item_group_index' => $_REQUEST['item_group_index'],
			':item_group_name' => $_REQUEST['item_group_name'],
			':item_group_code' => strtoupper($_REQUEST['item_group_code']),
			':uom_id' => $_REQUEST['uom_id'],
			':item_group_remarks' => $_REQUEST['item_group_remarks'],
			':company_id' => 1,
			':modify_dt' => $_REQUEST['modify_dt'],
			':modify_by' => $_REQUEST['modify_by']
		);

		$stmt->execute($data);

		$sql =  "DELETE FROM tbl_item_group_details WHERE item_group_id = '" . $update_id . "'";
		$result = $conn->prepare($sql);
		$result->execute();
		$result = $conn->query("SELECT * FROM tbl_item_group_details_temp WHERE session_id = '" . $_SESSION['session_id'] . "'");

		if ($result->rowCount() > 0) {
			$stmt = null;
			$stmt = $conn->prepare("INSERT INTO tbl_item_group_details (item_group_id, item_id, item_qty, item_position) VALUES (:item_group_id, :item_id, :item_qty, :item_position)");

			while ($obj = $result->fetchAll(PDO::FETCH_ASSOC)) {
				foreach ($obj as $row => $value) {
					$data = array(
						':item_group_id' => $update_id,
						':item_id' => $value['temp_item_id'],
						':item_qty' => $value['temp_item_qty'],
						':item_position' => $value['temp_item_position']
					);
					$stmt->execute($data);
				}
			}

			$sql =  "DELETE FROM tbl_item_group_details_temp WHERE session_id = '" . $_SESSION['session_id'] . "'";
			$result = $conn->prepare($sql);
			$result->execute();
		}


		$_SESSION['_msg'] = "Group succesfully Updated..!";
	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		$_SESSION['_msg_err'] = $str;
	}

	header("location:lst_item_grouping.php");
	die();
}


$enq_date = date('d-m-Y');
$prefix = "Mr.";
$sql1 =  "DELETE FROM tbl_item_group_details_temp";
$result1 = $conn->prepare($sql1);
$result1->execute();

if (isset($_REQUEST['group_id'])) {
	try {
		$sql =  "DELETE FROM tbl_item_group_details_temp WHERE session_id = '" . $_SESSION['session_id'] . "'";
		$result = $conn->prepare($sql);
		$result->execute();

		/*$result1 = $conn->query("SELECT * FROM tbl_item_group as a LEFT JOIN tbl_item_group_details as b 
					ON a.item_group_id = b.item_group_id WHERE a.status = 1 AND b.item_group_id =".$_REQUEST['group_id']);	*/

		$result1 = $conn->query("SELECT * FROM tbl_item_group_details  WHERE item_group_id =" . $_REQUEST['group_id']);
		if ($result1->rowCount() > 0) {
			$stmt = null;
			$stmt = $conn->prepare("INSERT INTO tbl_item_group_details_temp (temp_item_id, temp_item_qty, temp_item_position, session_id, temp_date) VALUES (:temp_item_id, :temp_item_qty, :temp_item_position, :session_id, :temp_date)");
			while ($obj = $result1->fetchAll(PDO::FETCH_ASSOC)) {
				foreach ($obj as $key => $value) {
					$data = array(
						':temp_item_id' => $value['item_id'],
						':temp_item_qty' => $value['item_qty'],
						':temp_item_position' => $value['item_position'],
						':session_id' => $_SESSION['session_id'],
						':temp_date' => date('Y-m-d')
					);
					$stmt->execute($data);
				}
			}
		}
	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		$_SESSION['_msg_err'] = $str;
	}

	$get_val = $conn->query("SELECT * FROM tbl_item_group WHERE status = '1' AND item_group_id = " . $_REQUEST['group_id']);
	if ($get_val->rowCount() > 0) {
		$get = $get_val->fetch(PDO::FETCH_OBJ);
	}
}


?>
<!DOCTYPE html>

<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo PAGE_TITLE; ?> - Item Grouping</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<?php include_once("inc/common/css-js.php"); ?>
</head>

<script type="text/javascript">
	$(function() {

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

		$("#div_item_group").hide();
		$("#item_group_add_btn").hide();

		$("#div_prod_desc").show();
		$("#item_indv_add_btn").show();

		//Group Type Change
		$("#group_type").change(function() {
			var group_type = $(this).val();

			$("#item_name").val('');
			$("#item_id").val('');
			$("#item_qty").val('');

			if (group_type == 'G') {
				$("#div_item_group").show();
				$("#item_group_add_btn").show();

				$("#div_prod_desc").hide();
				$("#item_indv_add_btn").hide();
			} else {
				$("#div_item_group").hide();
				$("#item_group_add_btn").hide();

				$("#div_prod_desc").show();
				$("#item_indv_add_btn").show();
			}

			$("#group_id").val('')
			$("#item_name").val('');
			$("#item_qty").val('');

		});

		$("#item_name").change(function() {
			var item_name = $(this).val();
			$("#item_id").val(item_name);
		});

	/*	$('#search_item').autocomplete({	
			source: function( request, response ) {
				var item_cat = $('#cat_id').val();
				$.ajax({
					url: "inc/cis_ajax/get_item_list.php",
					dataType: "json",
					data: {
						q: request.term,
						cat: item_cat
					},
					success: function( data ) {
						response( data );
				}
				});
			},	  
			minLength: 1,		
			select: function(event, ui)
			{
				$('#search_item').val(ui.item.value);
				$('#select_item_id').val(ui.item.id);
				$('#select_item_qty').focus();
			}
		}).data('ui-autocomplete')._renderItem = function(ul, item){
			return $("<li class='ui-autocomplete-row'></li>")
			.data("item.autocomplete", item)
			.append(item.label)
			.appendTo(ul);
		};*/

		/*$("#item_name").autocomplete("inc/auto/select_item_grouping.php", {
			width: 300,
			selectFirst: false,
			autoFocus: true
		});

		$("#item_name").result(function(event, data, formatted) {
			if (data) {
				var id = data[1];
				$("#item_id").val(id);
			}
		});*/

		$("#add_items").click(function() {
			if (isNull(document.thisForm.item_name, "Product Description..!")) {
				return false;
			}
			if (isNull(document.thisForm.item_qty, "Product Qty..")) {
				return false;
			}
			if (isNull(document.thisForm.item_position, "Position..")) {
				return false;
			}

			var item_id = $('#item_id').val();
			var item_qty = $('#item_qty').val();
			var item_position = $('#item_position').val();

			$.ajax({
				type: "POST",
				url: "inc/cis_ajax/jquery_item_group_details.php",
				data: {
					"item_id": item_id,
					"item_qty": item_qty,
					"item_position": item_position,
					'mode': 'save',
					'rec_type': 'ind'
				}
			}).done(function(msg) {
				//alert(msg);
				$('#show_table').html(msg);
				var n = msg.indexOf("tbody");
				$('#trade_items').val(n);

				$("#item_name").val('');
				$("#item_id").val('');
				$("#item_qty").val('');
				$("#item_position").val('');
			});

		});

		//Trading Group
		$("#add_items_group").click(function() {
			/*if (notSelected(document.thisForm.group_id, "Group Name..!")) {
				return false;
			}*/
			if (isNull(document.thisForm.item_qty, "Qty..")) {
				return false;
			}
			if (isNull(document.thisForm.item_position, "Position..")) {
				return false;
			}

			var group_id = $("#group_id").val();
			var item_qty = $("#item_qty").val();
			var item_position = $("#item_position").val();

			$.ajax({
				type: "POST",
				url: "inc/cis_ajax/jquery_item_group_details.php",
				data: {
					"group_id": group_id,
					"item_qty": item_qty,
					"item_position": item_position,
					'mode': 'save',
					'rec_type': 'group'
				}
			}).done(function(msg) {
				$('#show_table').html(msg);
				var n = msg.indexOf("tbody");
				$('#trade_items').val(n);
				$("#group_id").select2('val', '');
				$("#item_qty").val('');
				$("#item_position").val('');

			});
		});

	});


	function remove_item(temp_id) {
		$.ajax({
			type: "POST",
			url: "inc/cis_ajax/jquery_item_group_details.php",
			data: {
				temp_id: temp_id,
				mode: 'delete'
			}
		}).done(function(msg) {
			$('#show_table').html(msg);
			var n = msg.indexOf("tbody");
			$('#trade_items').val(n);
		});
	}

	function fnValidate() {
		if (isNull(document.thisForm.item_group_name, "Group Name..")) {
			return false;
		}
		if (isNull(document.thisForm.item_group_code, "Code..")) {
			return false;
		}
		if (notSelected(document.thisForm.uom_id, "UOM..!")) {
			return false;
		}
		if (isNull(document.thisForm.item_group_index, "Index..!")) {
			return false;
		}
		if (isNull(document.thisForm.item_group_remarks, "Remarks If Any..!")) {
			return false;
		}

		var trade_items = document.thisForm.trade_items.value;
		if (document.thisForm.trade_items.value == "-1") {
			alert("Please add Items to Group..");
			return false;
		}

		document.thisForm.submit();
	}
</script>


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
							<a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Dashboard</a>
							<a href="#" class="breadcrumb-item"> Item Master</a>
							<span class="breadcrumb-item active">Item Grouping</span>
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
						<form name='thisForm' class="form-horizontal" method='POST' action="" onSubmit="return fnValidate();" enctype="multipart/form-data">
							<div class="card">
								<div class="card-header bg-pgheader text-white header-elements-inline">
									<h6 class="card-title"> Item Details</h6>
									<div class="header-elements">
										<div class="list-icons">
											<a class="list-icons-item" href="lst_item_grouping.php" title="customer List"><i class="icon-arrow-left52 mr-2"></i></a>
											<a class="list-icons-item" data-action="fullscreen"></a>
										</div>
									</div>
								</div>
								<div class="card-body">
									<div class="form-group row pt-2">
										<label class="col-lg-2 col-form-label">Group Name<span class="text-mandatory"> *</span></label>
										<div class="col-lg-4">
											<input type="text" name="item_group_name" id="item_group_name" class="form-control text-uppercase" maxlength="250" value="<?php echo $get->item_group_name; ?>">
										</div>
										<label class="col-lg-2 col-form-label">Code<span class="text-mandatory"> *</span></label>
										<div class="col-lg-4">
											<input type="text" name="item_group_code" id="item_group_code" class="form-control text-uppercase" maxlength="75" value="<?php echo $get->item_group_code; ?>">
										</div>
									</div>
									<div class="form-group pt-2">
										<div class="row">
											<label class="col-lg-2  col-form-label">UOM <span class="text-mandatory"> *</span></label>
											<div class="col-lg-4 ">
												<select name="uom_id" id="uom_id" class="form-control select-search">
													<option value="">Select UOM</option>
													<option value="0">NA</option> <?php
																					echo $dbconn->fnFillComboFromTable_Where("uom_id", "uom_name", "mst_uom", "uom_code", " WHERE uom_status = 1 ");
																					?>
												</select>
												<script>
													document.thisForm.uom_id.value = "<?php echo $get->uom_id; ?>";
												</script>
											</div>
											<label class="col-lg-2 col-form-label">Index <span class="text-mandatory"> *</span></label>
											<div class="col-lg-4">
												<input type="text" name="item_group_index" id="item_group_index" class="form-control number_only" maxlength="40" placeholder="Enter Index" value="<?php echo $get->item_group_index; ?>">
											</div>
										</div>
									</div>
									<div class="form-group row pt-2">
										<label class="col-lg-2 col-form-label">Remarks if any :<span class="text-mandatory"> *</span></label>
										<div class="col-lg-4">
											<textarea name="item_group_remarks" maxlength="250" id="item_group_remarks" class="form-control"><?php echo $get->item_group_remarks; ?></textarea>
										</div>
									</div>

									<div class="row ml-0 mr-0 pt-1 pb-1" style="background-color:#f9f6f6;">
										<div class="col-md-6 font-weight-semibold">
											<i class="icon-make-group  mr-2"></i>Grouping
										</div>
									</div>

									<div class="row pt-4">
										<div class="col-md-12">
											<fieldset>
												<div class="form-group row">
													<div class="form-group col-md-4">
														<label>Item Type <span class="text-mandatory">*</span></label>
														<select name="group_type" id="group_type" data-placeholder="Choose a Type.." class="select">
															<option value="I">Individual</option>
															<option value="G">Group</option>
														</select>
													</div>
													<div class="form-group pl-0 col-md-2" id="div_item_group">
														<label>Group Name</label>
														<select data-placeholder="Choose a Group Name.." name="group_id" id="group_id" class="form-control select-search">
															<option value="">--Select Group Name----</option>
															<?php
															//$dbconn= new dbhandler(); 
															if (isset($_REQUEST['group_id'])) {
																$cond = " WHERE status = 1 and item_group_id !=" . $_REQUEST['group_id'];
																echo $dbconn->fnFillComboFromTable_Where("item_group_id", "concat(item_group_code,'-',item_group_name)", "tbl_item_group", "item_group_code", $cond);
															} else {
																echo $dbconn->fnFillComboFromTable_Where("item_group_id", "concat(item_group_code,'-',item_group_name)", "tbl_item_group", "item_group_code", " WHERE status = 1");
															}

															?>
														</select>
													</div>
													<!--<div class="form-group pl-0 col-md-3" id="div_prod_desc">
														<label>Product Description <span class="text-mandatory">*</span></label>
														<input type="text" name="item_name" id="item_name" class="form-control" maxlength="250">
														<input type="hidden" name="item_id" id="item_id">
													</div>-->
													<div class="form-group pl-0 col-md-3" id="div_prod_desc">
														<label>Item Description-Sales Code <span class="text-mandatory">*</span></label>
															<select data-placeholder="Choose Item Name.." name="item_name" id="item_name" class="form-control select-search">
																<option value="">--Select Group Name----</option>
																<?php
																	echo $dbconn->fnFillComboFromTable_Where("item_id", "item_code", "tbl_item_details", "item_id", " WHERE item_status = 1");
																?>
															</select>
															<input type="hidden" name="item_id" id="item_id">
													</div>
													<div class="form-group pl-0 col-md-2">
														<label>Quantity <span class="text-mandatory">*</span></label>
														<input type="text" name="item_qty" id="item_qty" class="form-control number_only_dot" maxlength="6">
													</div>
													<div class="form-group pl-0 col-md-2">
														<label>Position <span class="text-mandatory">*</span></label>
														<input type="text" name="item_position" id="item_position" maxlength="3" class="form-control number_only">
													</div>
													<div class="form-group pl-0" id="item_indv_add_btn">
														<button class="btn btn-success mr-2 mt-4 pt-1" id="add_items" name="add_items" type="button"> +</button>
													</div>
													
													<div class="form-group pl-0" id="item_group_add_btn">
														<button class="btn btn-success mr-2 mt-4 pt-1" id="add_items_group" name="add_items_group" type="button"> +</button>
													</div>
												</div>
											</fieldset>
										</div>
									</div>

									<div class="row">
										<div id="show_table" class="col-md-12">
											<table class="table table-xs table-bordered ">
												<thead>
													<tr class="bg-teal">
														<th width="1%">S.No</th>
														<?php if ($_SESSION['_user_type'] != 'S') { ?>
															<th width="1%">Position</th>
														<?php } ?>
														<th width="20%">Item Description</th>
														<th width="20%">Quantity</th>
														<th width="5%">Action</th>
													</tr>
												</thead>
											</table>
										</div>
									</div>
									<script type="text/javascript">remove_item(0);</script>


								
									</div>
									<div class="card-footer text-center pt-2">
										<?php if($_REQUEST["group_id"]!='') { ?>
											<INPUT class="btn btn-info" type="submit" name="UPDATE" value="UPDATE">
											<INPUT class="btn" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
											<input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['group_id'];?>">
											<?php }else{ ?>
											<INPUT class="btn btn-info" type="submit" name="SAVE" value="SAVE">
											<INPUT class="btn" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
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

			<!-- Footer -->
			<?php include("inc/common/footer.php") ?>
			<!-- /footer -->
		</div>

		<!-- /page content -->
</body>

</html>