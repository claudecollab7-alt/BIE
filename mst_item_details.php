<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL); 


if (isset($_POST['SAVE'])) {
	try {
		if (isset($_FILES['item_image']) && $_FILES['item_image']['name'] != "") {
			$ext = pathinfo($_FILES['item_image']['name'], PATHINFO_EXTENSION);
			$customfilename = str_replace("/", "-", $_REQUEST['item_code']) . '.' . $ext;
			$_REQUEST['item_image'] = post_img($customfilename, $_FILES['item_image']['tmp_name'], "project_img/item_image/");
		} else {
			$_REQUEST['item_image'] = '';
		}

		$item_dept_sales = 1;
		$item_dept_purchase = 1;


		/*if ($_REQUEST['item_dept_sales'] != '') {
			$item_dept_sales = $_REQUEST['item_dept_sales'];
		}
		if ($_REQUEST['item_dept_purchase'] != '') {
			$item_dept_purchase = $_REQUEST['item_dept_purchase'];
		}*/

		if (!isset($_REQUEST['item_group_id']) && $_REQUEST['item_group_id'] == '') {
			$_REQUEST['item_group_id'] = 0;
		}
		if (isset($_REQUEST['supp_id'])) {
			foreach ($_REQUEST['supp_id'] as $key => $value) {
				$supp_ids = implode(',', $_REQUEST['supp_id']);
			}
		} else {
			$supp_ids = '';
		}

		if (isset($_REQUEST['multi_uom_id'])) {
			foreach ($_REQUEST['multi_uom_id'] as $key => $value) {
				$uom_ids = implode(',', array_diff($_REQUEST['multi_uom_id'], [0]));
				// $uom_ids = implode(',', $_REQUEST['multi_uom_id']);
			}
		} else {
			$uom_ids = '';
		}

		if (!isset($_REQUEST['current_supp_id'])) {
			$_REQUEST['current_supp_id'] = '';
		}

		if ($_SESSION['_user_branch'] == 1) {

			$branch_status = 1;
			$branch_id = 1;
		} else {
			$branch_status = 0;
			$branch_id = 0;
		}
		//echo $supp_id;exit;
		$stmt = null;
		$stmt = $conn->prepare("INSERT INTO tbl_item_details (item_type, item_group_id, supp_id, branch_id, branch_status, current_supp_id, item_principal, item_division, item_code, 
		item_purchase_code, supp_item_code, item_category, item_desciption, item_uom,  multi_uom_id, item_brand_make, item_model_manufac, item_color, item_price, item_discount, item_cost_price, 
		item_selling_price, margin_percent, item_image, item_min_qty, 
		item_order_min_qty, item_remarks, item_hsn, item_dept_sales, item_dept_purchase, item_max_qty) VALUES (:item_type,:item_group_id,:supp_id, :branch_id, :branch_status, :current_supp_id,
		 :item_principal, :item_division, :item_code,
		:item_purchase_code, :supp_item_code, :item_category, :item_desciption, :item_uom, :multi_uom_id, :item_brand_make, :item_model_manufac, :item_color, :item_price, :item_discount, :item_cost_price, 
		:item_selling_price, :margin_percent, :item_image, :item_min_qty, :item_order_min_qty, :item_remarks, :item_hsn, :item_dept_sales, :item_dept_purchase, :item_max_qty)");
		$data = array(
			':item_type' => $_REQUEST['item_type'],
			':item_group_id' => $_REQUEST['item_group_id'],
			':supp_id' => $supp_ids,
			':branch_id' =>  $branch_id,
			':branch_status' =>  $branch_status,
			':current_supp_id' => $_REQUEST['current_supp_id'],
			':item_principal' => $_REQUEST['item_principal'],
			':item_division' => $_REQUEST['item_division'],
			':item_code' => $_REQUEST['item_code'],
			':item_purchase_code' => $_REQUEST['item_purchase_code'],
			':supp_item_code' => $_REQUEST['supp_item_code'],
			':item_category' => $_REQUEST['item_category'],
			':item_desciption' => strtoupper($_REQUEST['item_desciption']),
			':item_uom' => $_REQUEST['item_uom'],
			':multi_uom_id' => $uom_ids,
			':item_brand_make' => $_REQUEST['item_brand_make'],
			':item_model_manufac' => $_REQUEST['item_model_manufac'],
			':item_color' => $_REQUEST['item_color'],
			':item_price' => $_REQUEST['item_price'],
			':item_discount' => $_REQUEST['item_discount'],
			':item_cost_price' => $_REQUEST['item_cost_price'],
			':item_selling_price' => $_REQUEST['item_selling_price'],
			':margin_percent' => $_REQUEST['margin_percent'],
			':item_image' => $_REQUEST['item_image'],
			':item_min_qty' => $_REQUEST['item_min_qty'],
			':item_order_min_qty' => $_REQUEST['item_order_min_qty'],
			// ':item_curr_stock' => 0,
			':item_remarks' => $_REQUEST['item_remarks'],
			':item_hsn' => $_REQUEST['item_hsn'],
			':item_dept_sales' => $item_dept_sales,
			':item_dept_purchase' => $item_dept_purchase,
			':item_max_qty' => $_REQUEST['item_max_qty']

		);
		$stmt->execute($data);

		$last_id = $conn->lastInsertId();
		if ($last_id > 0) {
			$stock_id = $conn->query("INSERT INTO tbl_item_stock (item_id) VALUES ('" . $last_id . "')");
			$stock_id = $conn->lastInsertId();
			$result1 =  $conn->query("SELECT * FROM mst_branch");

			while ($obj = $result1->fetchAll(PDO::FETCH_ASSOC)) {
				foreach ($obj as $row => $value) {
					$stmt1 = null;
					$stmt1 = $conn->prepare("UPDATE tbl_item_stock SET " . $value['branch_item_price'] . " = :branch_item_price, " . $value['branch_item_discount'] . " = :branch_item_discount, " . $value['branch_item_cost_price'] . " = :branch_item_cost_price,
					" . $value['branch_item_selling_price'] . " = :branch_item_selling_price, " . $value['branch_item_margin'] . " = :margin_percent, " . $value['branch_item_msq'] . " = :branch_item_msq, " . $value['branch_item_maq'] . " = :branch_item_maq,
					" . $value['branch_item_moq'] . " = :branch_item_moq WHERE stock_id = :stock_id");

					$data1 = array(
						':stock_id' => $stock_id,
						':branch_item_price' => $_REQUEST['item_price'],
						':branch_item_discount' => $_REQUEST['item_discount'],
						':branch_item_cost_price' => $_REQUEST['item_cost_price'],
						':branch_item_selling_price' => $_REQUEST['item_selling_price'],
						':margin_percent' => $_REQUEST['margin_percent'],
						':branch_item_msq' => $_REQUEST['item_min_qty'],
						':branch_item_maq' => $_REQUEST['item_order_min_qty'],
						':branch_item_moq' => $_REQUEST['item_max_qty']

					);
					$stmt1->execute($data1);
				}
			}
		}
		$_SESSION['_msg'] = "Item details successfully saved..!";
	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		$_SESSION['_msg_err'] = $str;
	}

	header("location:lst_item_details.php");
	die();
}

if (isset($_POST['UPDATE'])) {
	$update_id = $_REQUEST['txtHid'];
	try {
		$item_dept_sales = 1;
		$item_dept_purchase = 1;

		/*if ($_REQUEST['item_dept_sales'] != '') {
			$item_dept_sales = $_REQUEST['item_dept_sales'];
		}
		if ($_REQUEST['item_dept_purchase'] != '') {
			$item_dept_purchase = $_REQUEST['item_dept_purchase'];
		}*/

		if (isset($_FILES['item_image']) && $_FILES['item_image']['name'] != "") {
			if ($_REQUEST["hide_item_image"] != "") {
				removeFile("project_img/item_image/" . $_REQUEST["hide_item_image"]);
			}
			$ext = pathinfo($_FILES['item_image']['name'], PATHINFO_EXTENSION);
			//$customfilename = $_REQUEST['item_code'].'.'.$ext; 
			$customfilename = str_replace("/", "-", $_REQUEST['item_code']) . '.' . $ext;
			$_REQUEST['item_image'] = post_img($customfilename, $_FILES['item_image']['tmp_name'], "project_img/item_image/");
		} else {
			$_REQUEST['item_image'] = $_REQUEST["hide_item_image"];
		}
		if (!isset($_REQUEST['item_group_id']) && $_REQUEST['item_group_id'] == '') {
			$_REQUEST['item_group_id'] = 0;
		}

		if (isset($_REQUEST['supp_id'])) {
			foreach ($_REQUEST['supp_id'] as $key => $value) {
				$supp_ids = implode(',', $_REQUEST['supp_id']);
			}
		} else {
			$supp_ids = '';
		}
		if (!isset($_REQUEST['current_supp_id'])) {
			$_REQUEST['current_supp_id'] = '';
		}

		if (isset($_REQUEST['multi_uom_id'])) {
			foreach ($_REQUEST['multi_uom_id'] as $key => $value) {
			$uom_ids = implode(',', array_diff($_REQUEST['multi_uom_id'], [0]));	
			// $uom_ids = implode(',', $_REQUEST['multi_uom_id']);
			}
		} else {
			$uom_ids = '';
		}
		$stmt = null;
		$stmt = $conn->prepare("UPDATE tbl_item_details SET item_type = :item_type, item_group_id = :item_group_id, supp_id = :supp_id, current_supp_id = :current_supp_id, item_principal = :item_principal,
		 item_division = :item_division, item_code = :item_code, item_purchase_code = :item_purchase_code, supp_item_code = :supp_item_code, item_category = :item_category, item_desciption = :item_desciption, 
		 item_brand_make = :item_brand_make, item_model_manufac= :item_model_manufac, item_color = :item_color, multi_uom_id = :multi_uom_id, item_image = :item_image, item_remarks = :item_remarks, item_dept_sales = :item_dept_sales, item_dept_purchase = :item_dept_purchase WHERE item_id = :item_id");
		$data = array(
			':item_id' => $update_id,
			':item_type' => $_REQUEST['item_type'],
			':item_group_id' => $_REQUEST['item_group_id'],
			':supp_id' => $supp_ids,
			':current_supp_id' => $_REQUEST['current_supp_id'],
			':item_principal' => $_REQUEST['item_principal'],
			':item_division' => $_REQUEST['item_division'],
			':item_code' => $_REQUEST['item_code'],
			':item_purchase_code' => $_REQUEST['item_purchase_code'],
			':supp_item_code' => $_REQUEST['supp_item_code'],
			':item_category' => $_REQUEST['item_category'],
			':item_desciption' => $_REQUEST['item_desciption'],
			':item_brand_make' => $_REQUEST['item_brand_make'],
			':item_model_manufac' => $_REQUEST['item_model_manufac'],
			':item_color' => $_REQUEST['item_color'],
			':multi_uom_id' => $uom_ids,
			':item_image' => $_REQUEST['item_image'],
			// ':item_curr_stock' => 0,
			':item_remarks' => $_REQUEST['item_remarks'],
			':item_dept_sales' => $item_dept_sales,
			':item_dept_purchase' => $item_dept_purchase,
		);

		$stmt->execute($data);
		//echo $stmt->fullQuery;

		$_SESSION['_msg'] = "Item details successfully updated..!";
	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		$_SESSION['_msg_err'] = $str;
	}

	header("location:lst_item_details.php");
	die();
}


$img_name = "user_avatar_holder.png";
$img_path = "img/user_avatar_holder.png";
$img_size = 256; //kb	


if (isset($_REQUEST['item_id']) && $_REQUEST['item_id'] != "") {
	$result = $conn->query("SELECT * FROM tbl_item_details WHERE item_id = " . $_REQUEST['item_id']);
	if ($result->rowCount() > 0) {
		$obj = $result->fetch(PDO::FETCH_OBJ);
		$item_id = $obj->item_id;

		//$item_type = $dbconn->GetSingleReconrd("item_type","item_type_id","item_name","item_type_id",$obj->item_type);

	}
}



?>
<!DOCTYPE html>

<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo PAGE_TITLE; ?> - Item Details</title>
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

		$("#multi_uom_id").change(function() {
			validateUOMSelection();
			// alert();
		});
		$("#item_uom").change(function() {
			validateUOMSelection();
			// alert();
		});


		function validateUOMSelection() {
			const itemUOM = document.getElementById('item_uom');
			const multiUOM = document.getElementById('multi_uom_id');
			const selectedItemUOM = itemUOM.value;
			const selectedMultiUOM = Array.from(multiUOM.options).filter(option => option.selected).map(option => option.value);


			// Ensure itemUOM is not in multiUOM
			if (selectedMultiUOM.includes(selectedItemUOM)) {
				// $("#item_uom").val("").trigger('change');
				alert('The selected UOM cannot be selected in both fields. Please choose a different UOM.');
				return false;
			}
		}


		$("#item_price,#item_discount,#margin_percent").keyup(function() {
			var item_price = parseFloat($("#item_price").val());
			var item_discount = parseFloat($("#item_discount").val());
			var margin_percent = parseFloat($("#margin_percent").val());

			if (item_price == '') {
				item_price = 0;
			}
			if (item_discount == '') {
				item_discount = 0;
			}
			if (margin_percent == '') {
				margin_percent = 0;
			}

			if (isNaN(item_price)) {
				item_price = 0;
			}
			if (isNaN(item_discount)) {
				item_discount = 0;
			}
			if (isNaN(margin_percent)) {
				margin_percent = 0;
			}

			var dis_amt = ((item_price * item_discount) / 100);
			var cost_price = item_price - dis_amt;

			var margin_per = ((cost_price * margin_percent) / 100);
			var margin_tot = cost_price + margin_per;

			$("#item_cost_price").val(cost_price.toFixed(2));
			$("#item_selling_price").val(margin_tot.toFixed(2));

		}).trigger('keyup');

		$("#item_selling_price").change(function() {
			var item_price = parseFloat($("#item_price").val());
			var item_discount = parseFloat($("#item_discount").val());
			var selling_price = parseFloat($("#item_selling_price").val());
			var new_cost_price = parseFloat($("#item_cost_price").val());

			// if(isNaN(item_price) || isNaN(item_discount) || isNaN(selling_price) || isNaN(new_cost_price)) {
			// 	console.error("Invalid numeric input detected. Please check your input fields.");
			// 	return;
			// }
			// alert(selling_price);


			console.log("Before calculation - selling_price:", selling_price, "new_cost_price:", new_cost_price);

			// Check if new_cost_price is zero to avoid division by zero
			var selling_price_per = new_cost_price !== 0 ? ((selling_price - new_cost_price) / new_cost_price) * 100 : 0;

			console.log("After calculation - selling_price_per:", selling_price_per);

			// Check if selling_price_per is NaN
			if (isNaN(selling_price_per)) {
				selling_price_per = 0;
			}

			console.log("After NaN check - selling_price_per:", selling_price_per);

			$("#margin_percent").val(selling_price_per.toFixed(2));
		}).trigger('change');



		$("#item_type").change(function() {
			var optionValue = $('#item_type :selected').val();
			if (optionValue == 7) {
				$(".item_type7").fadeIn();
			} else {
				$(".item_type7").fadeOut();
				$("#item_group_id").val('0');
			}
		}).change();

		<?php
		if ($_SESSION['_user_id'] == 1) { ?>
			$('.hide_price').show();
		<?php	} else { ?>
			$('.hide_price').show();
		<?php	}  ?>

	});


	/* File Upload */

	// Modal template
	var modalTemplate = '<div class="modal-dialog modal-lg" role="document">\n' +
		'  <div class="modal-content">\n' +
		'    <div class="modal-header align-items-center">\n' +
		'      <h6 class="modal-title">{heading} <small><span class="kv-zoom-title"></span></small></h6>\n' +
		'      <div class="kv-zoom-actions btn-group">{toggleheader}{fullscreen}{borderless}{close}</div>\n' +
		'    </div>\n' +
		'    <div class="modal-body">\n' +
		'      <div class="floating-buttons btn-group"></div>\n' +
		'      <div class="kv-zoom-body file-zoom-content"></div>\n' + '{prev} {next}\n' +
		'    </div>\n' +
		'  </div>\n' +
		'</div>\n';

	// Buttons inside zoom modal
	var previewZoomButtonClasses = {
		toggleheader: 'btn btn-light btn-icon btn-header-toggle btn-sm',
		fullscreen: 'btn btn-light btn-icon btn-sm',
		borderless: 'btn btn-light btn-icon btn-sm',
		close: 'btn btn-light btn-icon btn-sm'
	};

	// Icons inside zoom modal classes
	var previewZoomButtonIcons = {
		prev: '<i class="icon-arrow-left32"></i>',
		next: '<i class="icon-arrow-right32"></i>',
		toggleheader: '<i class="icon-menu-open"></i>',
		fullscreen: '<i class="icon-screen-full"></i>',
		borderless: '<i class="icon-alignment-unalign"></i>',
		close: '<i class="icon-cross2 font-size-base"></i>'
	};

	// File actions
	var fileActionSettings = {
		zoomClass: '',
		zoomIcon: '<i class="icon-zoomin3"></i>',
		dragClass: 'p-2',
		dragIcon: '<i class="icon-three-bars"></i>',
		removeClass: '',
		removeErrorClass: 'text-danger',
		removeIcon: '<i class="icon-bin"></i>',
		indicatorNew: '<i class="icon-file-plus text-success"></i>',
		indicatorSuccess: '<i class="icon-checkmark3 file-icon-large text-success"></i>',
		indicatorError: '<i class="icon-cross2 text-danger"></i>',
		indicatorLoading: '<i class="icon-spinner2 spinner text-muted"></i>'
	};

	$('#item_image').fileinput({
		browseLabel: 'Browse',
		browseIcon: '<i class="icon-file-plus mr-2"></i>',
		uploadIcon: '<i class="icon-file-upload2 mr-2"></i>',
		removeIcon: '<i class="icon-cross2 font-size-base mr-2"></i>',
		layoutTemplates: {
			icon: '<i class="icon-file-check"></i>',
			modal: modalTemplate
		},
		initialPreview: [
			'<?php echo $img_path; ?>'
		],
		initialPreviewConfig: [{
			caption: '<?php echo $img_name; ?>',
			size: <?php echo $img_size; ?>,
			key: 1,
			url: 'img/'
		}],
		initialPreviewAsData: true,
		overwriteInitial: true,
		previewZoomButtonClasses: previewZoomButtonClasses,
		previewZoomButtonIcons: previewZoomButtonIcons,
		fileActionSettings: fileActionSettings
	});


	/* File Upload */
	var wasSubmitted = false;

	function fnValidate() {


		if (notSelected(document.thisForm.item_type, "Item Type..!")) {
			return false;
		}

		var optionValue = $('#item_type :selected').val();
		if (optionValue == 1) {
			if (notSelected(document.thisForm.item_group_id, "Item Group..!")) {
				return false;
			}
		}
		if (notSelected(document.thisForm.item_principal, "Principal..!")) {
			return false;
		}
		if (notSelected(document.thisForm.item_division, "Division..!")) {
			return false;
		}



		if (isNull(document.thisForm.item_desciption, "Description..!")) {
			return false;
		}
		if (isNull(document.thisForm.item_purchase_code, "Purchase Code..!")) {
			return false;
		}
		if (isNull(document.thisForm.item_code, "Sale Item Code..!")) {
			return false;
		}

		/*if (document.thisForm.item_dept_purchase.checked == false && document.thisForm.item_dept_sales.checked == false) {
			alert("Please select any one department..!");
			return false;
		}

        if (document.thisForm.item_dept_purchase.checked == true) {
			if (isNull(document.thisForm.item_purchase_code, "Purchase Item Code..!")) {
				return false;
			}
		}
		
		if (document.thisForm.item_dept_sales.checked == true) {
			if (isNull(document.thisForm.item_code, "Sales Item Code..!")) {
				return false;
			}
		} */

		//alert(document.thisForm.item_uom);
		if ($("#item_category").val() == "") {
			alert("Please Select Category..!");
			return false;
		}
		if (notSelected(document.thisForm.item_uom, "UOM..!")) {
			return false;
		}
		if (isNull(document.thisForm.multi_uom_id, "Multiple UOM..!")) {
			return false;
		}
		if (document.thisForm.session_user_id.value == 1) {
			if (isNull(document.thisForm.item_price, "Item Price..!")) {
				return false;
			}
			if (isNull(document.thisForm.item_cost_price, "Cost Price..!")) {
				return false;
			}
			if (isNull(document.thisForm.item_selling_price, "Selling Price..!")) {
				return false;
			}
		}

		if ($("#item_hsn").val() == "") {
			alert("Please Select HSN..!");
			return false;
		}
		// if(notSelected(document.thisForm.,"HSN..!")){ return false; }
		const itemUOM = document.getElementById('item_uom');
		const multiUOM = document.getElementById('multi_uom_id');
		const selectedItemUOM = itemUOM.value;
		const selectedMultiUOM = Array.from(multiUOM.options).filter(option => option.selected).map(option => option.value);


		// Ensure itemUOM is not in multiUOM
		if (selectedMultiUOM.includes(selectedItemUOM)) {
			alert('The selected UOM cannot be selected in both fields. Please choose a different UOM.');
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
							<a href="#" class="breadcrumb-item"> Settings</a>
							<span class="breadcrumb-item active">Item Details</span>
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
							<input type="hidden" name="session_user_id" value="<?php echo $_SESSION['_userid']; ?>">
							<div class="card">
								<div class="card-header bg-pgheader text-white header-elements-inline">
									<h6 class="card-title"> Item Details</h6>
									<div class="header-elements">
										<div class="list-icons">
											<a class="list-icons-item" href="lst_item_details.php" title="customer List"><i class="icon-arrow-left52 mr-2"></i></a>
											<a class="list-icons-item" data-action="fullscreen"></a>
										</div>
									</div>
								</div>
								<div class="card-body">
									<div class="form-group row pt-2">
										<label class="col-lg-2 col-form-label ">Branch<span class="text-mandatory"> *</span></label>

										<div class="col-lg-4">
											<select name="branch_id" id="branch_id" data-placeholder="Choose a Item Type.." class="select">
												<option value="">--- Select Branch ---</option>
												<?php

												echo $dbconn->fnFillComboFromTable_Where("branch_id", "branch_name", "mst_branch", "branch_id", " WHERE branch_status = 1"); ?>
											</select>
											<script>
												document.thisForm.branch_id.value = "<?php echo $obj->branch_id; ?>"
											</script>
											<!-- <input type="hidden" class="form-control"  name="branch_id" id="branch_id"  maxlength="9" value="<?php //echo $obj->branch_id; 
																																					?>" /> -->
										</div>
									</div>
									<div class="form-group row pt-2">

										<label class="col-lg-2 col-form-label">Item Type<span class="text-mandatory"> *</span></label>
										<div class="col-lg-4">
											<!--<select name="item_type" id="item_type" class="form-control select-search">
												<option value="">--- Select Item Type ---</option>
												<?php

												//echo $dbconn->fnFillComboFromTable_Where("item_type_id", "item_name", "item_type", "item_type_id", " WHERE item_type_status = 1"); 
												?>
											</select>-->
											<select name="item_type" id="item_type" class="form-control select-search">
												<option value="">--- Select Item Type ---</option>
												<option value="6">Consumable</option>
												<option value="7">Group Products</option>
												<option value="3">Raw Materials</option>
												<option value="2">Trading</option>
												<option value="8">Labour Charges</option>


											</select>
											<script>
												document.thisForm.item_type.value = "<?php echo $obj->item_type; ?>";
											</script>
										</div>

										<label class="col-lg-2 col-form-label item_type7">Group<span class="text-mandatory"> *</span></label>
										<div class="col-lg-4 item_type7">
											<select name="item_group_id" id="item_group_id" class="form-control select-search">
												<option value="">--- Select Group Item ---</option>
												<?php
												echo $dbconn->fnFillComboFromTable_Where("item_group_id", "item_group_name", "tbl_item_group", "item_group_id", " WHERE status = 1"); ?>
											</select>
											<script>
												document.thisForm.item_group_id.value = "<?php echo $obj->item_group_id; ?>";
											</script>
										</div>
									</div>
									<div class="form-group row pt-2">
										<label class="col-lg-2 col-form-label">Principal<span class="text-mandatory"> *</span></label>
										<div class="col-lg-4">
											<select name="item_principal" id="item_principal" data-placeholder="Choose a Principal.." class="form-control select-search">
												<option value="">--- Select Principal ---</option>
												<?php

												echo $dbconn->fnFillComboFromTable_Where("principal_id", "principal_name", "mst_principal", "principal_id", " WHERE principal_status = 1"); ?>
											</select>
											<script>
												document.thisForm.item_principal.value = "<?php echo $obj->item_principal; ?>";
											</script>

										</div>
										<label class="col-lg-2 col-form-label">Division<span class="text-mandatory"> *</span></label>
										<div class="col-lg-4">
											<select name="item_division" id="item_division" data-placeholder="Choose a Division.." class="form-control select-search">
												<option value="">--- Select Division ---</option>
												<?php

												echo $dbconn->fnFillComboFromTable_Where("division_id", "division_name", "mst_division", "division_id", " WHERE division_status = 1"); ?>
											</select>
											<script>
												document.thisForm.item_division.value = "<?php echo $obj->item_division; ?>";
											</script>

										</div>
									</div>
									<div class="form-group pt-2">
										<div class="row">
											<label class="col-lg-2  col-form-label">Description <span class="text-mandatory"> *</span></label>
											<!-- text-capitalize -->
											<div class="col-lg-4 ">
												<input type="text" name="item_desciption" id="item_desciption" class="form-control text-uppercase" maxlength="250" value="<?php echo str_replace('"', "'", $obj->item_desciption); ?>">
											</div>

											<label class="col-lg-2 col-form-label">Purchase Code<span class="text-mandatory"> *</span></label>
											<div class="col-lg-4">
												<input type="text" name="item_purchase_code" id="item_purchase_code" class="form-control" maxlength="40" placeholder="Enter Purchase Code" value="<?php echo $obj->item_purchase_code; ?>">
											</div>
										</div>
									</div>
									<div class="form-group row pt-2">
										<label class="col-lg-2 col-form-label">Sales Item Code<span class="text-mandatory"> *</span></label>
										<div class="col-lg-4">
											<input type="text" class="form-control" name="item_code" id="item_code" maxlength="40" value="<?php echo $obj->item_code; ?>" placeholder="Enter Code">
										</div>
										<label class="col-lg-2 col-form-label">Supplier Item Code</label>
										<div class="col-lg-4">
											<input type="text" class="form-control" name="supp_item_code" id="supp_item_code" maxlength="40" value="<?php echo $obj->supp_item_code; ?>" placeholder="Enter Code">
										</div>
									</div>
									<div class="form-group row pt-2">

										<label class="col-lg-2 col-form-label">Supplier Mapping</label>
										<div class="col-lg-4">
											<select name="supp_id[]" id="supp_id" data-placeholder="Choose a Supplier.." class="select" multiple>
												<option value="">--- Select Supplier ---</option>
												<?php
												//echo 'test';
												$supplier_query = "SELECT supp_id,supp_name,supp_code,company_branch_id FROM mst_supplier_new WHERE supp_type = 'S' AND supp_status = 1 ";
												$supp_res = $conn->query($supplier_query);
												if ($supp_res->rowCount() > 0) {
													if ($obj->supp_id != '') {
														$supp_ids = explode(',', $obj->supp_id);
													}
													while ($suppObj = $supp_res->fetch()) {
														$selected = '';
														if (isset($suppObj->supp_id) && isset($supp_ids)) {
															//if ($obj->supp_id!='') {
															if (in_array($suppObj->supp_id, $supp_ids)) {
																$selected = "selected";
															}
														}
														$branch_code = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_status = '1' AND branch_id", $suppObj->company_branch_id);

														echo '<option value="' . $suppObj->supp_id . '" ' . $selected . '>' . $suppObj->supp_name . ' - ' . $suppObj->supp_code . ' ~ ' . $branch_code . '</option>';
													}
												}
												/*echo $dbconn->fnFillComboFromTable_Where("supp_id","supp_name","mst_supplier_new","supp_id"," WHERE supp_status = 1");*/
												?>
											</select>
										</div>

										<label class="col-lg-2 col-form-label">Default Supplier</label>
										<div class="col-lg-4">
											<select name="current_supp_id" data-placeholder="Choose a Supplier.." class="form-control select-search">
												<option value="">--- Select Supplier ---</option>
												<?php
												echo $dbconn->fnFillComboFromTable_Where("supp_id", "supp_name", "mst_supplier_new", "supp_id", " WHERE supp_type = 'S'  AND supp_approve_status = '1' AND supp_status = 1"); ?>

											</select>
											<script>
												document.thisForm.current_supp_id.value = "<?php echo $obj->current_supp_id; ?>";
											</script>
										</div>
									</div>

									<div class="form-group row pt-2">
										<?php if ($_REQUEST['item_id'] == '') { ?>

											<label class="col-lg-2 col-form-label">UOM<span class="text-mandatory"> *</span></label>
											<div class="col-lg-4">
												<select name="item_uom" id="item_uom" data-placeholder="Choose a UOM.." class="form-control select-search">
													<option value="">--- Select UOM ---</option>
													<?php

													echo $dbconn->fnFillComboFromTable_Where("uom_id", "uom_name", "mst_uom", "uom_id", " WHERE uom_status = 1"); ?>
												</select>
												<script>
													document.thisForm.item_uom.value = "<?php echo $obj->item_uom; ?>";
												</script>
											</div>
										<?php } ?>

										<label class="col-lg-2 col-form-label">Multiple UOM<?php //echo $obj->supp_id; 
																							?><span class="text-mandatory"> *</span></label>
										<div class="col-lg-4">
											<select name="multi_uom_id[]" id="multi_uom_id" data-placeholder="Choose a UOM.." class="select" multiple>
												<option value="">--- Select UOM ---</option>
												<?php
												$uom_query = "SELECT uom_id,uom_name FROM mst_uom WHERE uom_id NOT IN (SELECT item_uom FROM tbl_item_details WHERE item_id= '" . $obj->item_id . "') AND uom_status = 1 ";
												$uom_res = $conn->query($uom_query);
												if ($uom_res->rowCount() > 0) {
													if ($obj->multi_uom_id != '') {
														$uom_ids = explode(',', $obj->multi_uom_id);
													}
													print_r($uom_ids);
													while ($uomObj = $uom_res->fetch()) {
														$selected = '';
														// echo '**'.$uomObj->uom_id;
														// echo '***'.$obj->multi_uom_id;

														if (isset($uomObj->uom_id) && isset($uom_ids)) {

															if (in_array($uomObj->uom_id, $uom_ids)) {
																$selected = "selected";
															}
														}
														echo '<option value="' . $uomObj->uom_id . '" ' . $selected . '>' . $uomObj->uom_name . '</option>';
													}
												}
												/*echo $dbconn->fnFillComboFromTable_Where("supp_id","supp_name","mst_supplier","supp_id"," WHERE supp_status = 1");*/
												?>
											</select>
											<!--<script>document.thisForm.supp_id.value="<?php //echo $obj->supp_id; 
																							?>";</script>-->
										</div>

									</div>
									<div class="form-group row pt-2">
										<label class="col-lg-2 col-form-label">Brand/Make</label>
										<div class="col-lg-4">
											<select name="item_brand_make" id="item_brand_make" data-placeholder="Choose a Brand / Make.." class="form-control select-search">
												<option value="">--- Select Brand ---</option>
												<?php

												echo $dbconn->fnFillComboFromTable_Where("brand_id", "brand_name", "mst_brand", "brand_id", " WHERE brand_status = 1"); ?>
											</select>
											<script>
												document.thisForm.item_brand_make.value = "<?php echo $obj->item_brand_make; ?>";
											</script>
										</div>
										<label class="col-lg-2 col-form-label">Category<span class="text-mandatory"> *</span></label>
										<div class="col-lg-4">
											<select name="item_category" id="item_category" data-placeholder="Choose a Category.." class="form-control select-search">
												<option value="">--- Select Category ---</option>
												<?php

												echo $dbconn->fnFillComboFromTable_Where("category_id", "category_name", "mst_category", "category_id", " WHERE category_status = 1"); ?>
											</select>
											<script>
												document.thisForm.item_category.value = "<?php echo $obj->item_category; ?>";
											</script>
										</div>

									</div>

									<div class="form-group row pt-2">
										<label class="col-lg-2 col-form-label">Color</label>
										<div class="col-lg-4">
											<select name="item_color" id="item_color" data-placeholder="Choose a Color.." class="form-control select-search">
												<option value="">--- Select Color ---</option>
												<?php

												echo $dbconn->fnFillComboFromTable_Where("color_id", "color_name", "mst_color", "color_id", " WHERE color_status = 1"); ?>
											</select>
											<script>
												document.thisForm.item_color.value = "<?php echo $obj->item_color; ?>";
											</script>
										</div>
										<label class="col-lg-2 col-form-label">Model No</label>
										<div class="col-lg-4">
											<input type="text" name="item_model_manufac" id="item_model_manufac" class="form-control" maxlength="50" placeholder="Enter Manufacturing Model" value="<?php echo $obj->item_model_manufac; ?>">
										</div>


									</div>

									<!--<div class="col-md-4">
										<fieldset>
											<legend class="font-weight-semibold"><i class="icon-image2 mr-2"></i>User Avatar</legend>
											<div class="form-group">
												<div class="col-lg-12">
													<input type="file" id="usr_photo" name="usr_photo" class="file-input-overwrite image_only" data-size="250" data-submit='1' data-fouc>
													<div id="usr_photo_error" class="cis-feedback help-block form-text text-muted">Accepted formats: jpeg, png, jpg. </div>
													<input type="text" name="hide_usr_photo" value="">
												</div>
											</div>
										</fieldset>
									</div>-->
									<?php if ($_REQUEST['item_id'] == '') { ?>
										<div class="form-group row pt-2 hide_price">
											<label class="col-lg-2 col-form-label">Item Price <span class="text-mandatory"> *</span></label>
											<div class="col-lg-4">
												<input type="text" class="form-control" name="item_price" id="item_price" maxlength="9" value="<?php echo $obj->item_price; ?>" onkeypress="return isNumberKey_With_Dot(event)" placeholder="Enter Price">
											</div>
											<label class="col-lg-2 col-form-label">Item Discount</label>
											<div class="col-lg-4">
												<div class="input-group">
													<span class="input-group-append">
														<input type="text" class="form-control number_only" name="item_discount" id="item_discount" maxlength="9" value="<?php echo $obj->item_discount; ?>" placeholder="Enter Discount" onkeypress="return isNumberKey_With_Dot(event)">
														<span class="input-group-text">%</span>
													</span>
												</div>
											</div>
										</div>
										<div class="form-group row pt-2 hide_price">
											<label class="col-lg-2 col-form-label">Cost Price<span class="text-mandatory"> *</span></label>
											<div class="col-lg-4">
												<input type="text" class="form-control number_only" name="item_cost_price" id="item_cost_price" value="<?php echo $obj->item_cost_price; ?>" readonly tabindex="-1" placeholder="Enter Cost Price" onkeypress="return isNumberKey_With_Dot(event)">
											</div>
											<label class="col-lg-2 col-form-label">Margin (%)<span class="text-mandatory"> *</span></label>
											<div class="col-lg-4">
												<div class="input-group">
													<span class="input-group-append">
														<input type="text" class="form-control" name="margin_percent" id="margin_percent" maxlength="9" value="<?php echo $obj->margin_percent; ?>" placeholder="Enter Margin" onKeyPress="return isNumberKey_With_Dot(event)" />
													</span>
													<span class="input-group-text">%</span>
												</div>
											</div>

										</div>

										<div class="form-group row pt-2">
											<div class="col-lg-2">
												<label class="col-form-label">MSQ</label>
											</div>
											<div class="col-lg-4">
												<input type="text" class="form-control number_only" name="item_min_qty" id="item_min_qty" value="<?php echo $obj->item_min_qty; ?>" placeholder="Enter Minimum Sales Order Quantity" onkeypress="return isNumberKey_With_Dot(event)">
											</div>
											<label class="col-lg-2 col-form-label">Selling Price <span class="text-mandatory"> *</span></label>
											<div class="col-lg-4">
												<input type="text" class="form-control " name="item_selling_price" id="item_selling_price" maxlength="" value="<?php echo $obj->item_selling_price; ?>" onkeypress="return isNumberKey_With_Dot(event)" placeholder="Enter Selling Price">
											</div>

										</div>
										<div class="form-group row pt-2">
											<label class="col-lg-2 col-form-label">MOQ</label>
											<div class="col-lg-4">
												<input type="text" class="form-control number_only" name="item_order_min_qty" id="item_order_min_qty" value="<?php echo $obj->item_order_min_qty; ?>" placeholder="Enter Minimum Order Quantity" onkeypress="return isNumberKey_With_Dot(event)">
											</div>
											<label class="col-lg-2 col-form-label">MAQ</label>
											<div class="col-lg-4">
												<input type="text" class="form-control number_only" name="item_max_qty" id="item_max_qty" value="<?php echo $obj->item_max_qty; ?>" placeholder="Enter Maximum Order Quantity" onkeypress="return isNumberKey_With_Dot(event)">
											</div>

										</div>
									<?php } ?>

									<div class="form-group row pt-2">
										<!--<label class="col-lg-2 col-form-label">Current Stock</label>
										<div class="col-lg-4">
											<input type="text" class="form-control number_only" name="item_curr_stock" id="item_curr_stock number_only" value="<?php echo $obj->item_curr_stock; ?>" placeholder="Enter Current Stock">
										</div>-->

										<?php if ($_REQUEST['item_id'] == '') { ?>
											<label class="col-lg-2 col-form-label">HSN<span class="text-mandatory"> *</span></label>
											<div class="col-lg-4">
												<select name="item_hsn" id="item_hsn" data-placeholder="Choose a HSN.." class="form-control select-search">
													<option value="">--- Select HSN ---</option>
													<?php

													echo $dbconn->fnFillComboFromTable_Where("hsn_id", "CONCAT(hsn_code,' - ',igst,'%')", "mst_hsn", "hsn_id", " WHERE hsn_status = 1"); ?>
												</select>
												<script>
													document.thisForm.item_hsn.value = "<?php echo $obj->item_hsn; ?>";
												</script>
											</div>

										<?php } ?>
										<label class="col-lg-2 col-form-label">Image</label>
										<div class="form-group">
											<div class="col-lg-12">
												<input type="file" id="item_image" name="item_image">
												<input type="hidden" name="hide_item_image" value="<?php echo $obj->item_image; ?>">
											</div>
										</div>
									</div>
									<div class="form-group row pt-2">
										<label class="col-lg-2 col-form-label">Remarks</label>
										<div class="col-lg-8">
											<input type="text" class="form-control" name="item_remarks" id="item_remarks" value="<?php echo $obj->item_remarks; ?>" maxlength="100" placeholder="Enter Remarks">
										</div>
									</div>
								</div>

								<div class="card-footer text-center pt-2">
									<?php if ($_REQUEST["item_id"] != '') { ?>
										<INPUT class="btn btn-info" type="submit" name="UPDATE" value="UPDATE">
										<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
										<input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['item_id']; ?>">
									<?php } else { ?>
										<INPUT class="btn btn-info" type="submit" name="SAVE" value="SAVE">
										<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
										<input type="hidden" name="txtHid" value="0">
									<?php } ?>
								</div>

						</form>
					</div>



					<!-- End of This Form UI  --->

				</div>
				<!-- /dashboard content -->
			</div>
			<!-- /content area -->


		</div>
		<!-- Footer -->
		<?php include("inc/common/footer.php") ?>
		<!-- /footer -->

		<!-- /page content -->
</body>

</html>