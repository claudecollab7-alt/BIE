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
// //}
//  ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

if (isset($_POST['SAVE'])) {
	// print_r($_POST);
	// exit;
	try {
		$result = $conn->query("SELECT * FROM tbl_item_details WHERE item_id = " . $_REQUEST['item_id']);
		if ($result->rowCount() > 0) {
			$obj = $result->fetch(PDO::FETCH_OBJ);
		}


		if (
			$_REQUEST['branch_new_price'] != $obj->item_price || $_REQUEST['branch_new_discount'] != $obj->item_discount || $_REQUEST['branch_new_cost_price'] != $obj->item_cost_price ||
			$_REQUEST['branch_new_selling_price'] != $obj->item_selling_price || $_REQUEST['new_msq'] != $obj->item_min_qty || $_REQUEST['new_maq'] != $obj->item_max_qty ||
			$_REQUEST['new_moq'] != $obj->item_order_min_qty || $_REQUEST['new_item_curr_stock'] != $obj->item_curr_stock || $_REQUEST['new_item_uom'] != $obj->item_uom ||
			$_REQUEST['new_item_hsn'] != $obj->item_hsn
		) {


			// $_REQUEST['created_dtm'] = date('Y-m-d H:i:s');
			// $_REQUEST['created_by'] = $_SESSION['_user_id'];

			if (isset($_REQUEST['branch_new_discount'])) {
				for ($x = 0; $x < count($_REQUEST['branch_new_discount']); $x++) {

					$_REQUEST['created_dtm'] = date('Y-m-d H:i:s');
					$_REQUEST['created_by'] = $_SESSION['_user_id'];
					$stmt1 = null;
					$stmt1 = $conn->prepare("INSERT INTO tbl_multiuom_itemprice_history (item_id, branch_id, new_price, new_discount, new_min_discount, new_max_discount, new_cost_price, new_selling_price, new_margin_percent, new_item_uom, 
						created_dtm, created_by) 
						VALUES (:item_id, :branch_id, :new_price, :new_discount, :new_min_discount, :new_max_discount, :new_cost_price, :new_selling_price, :new_margin_percent, :new_item_uom, :created_dtm, :created_by)");
					$data1 = array(
						':item_id' => $_REQUEST['item_id'],
						':branch_id' =>  $_REQUEST['branch_id'][$x],
						':new_price' => $_REQUEST['branch_new_price'][$x],
						':new_discount' => $_REQUEST['branch_new_discount'][$x],
						':new_min_discount' => $_REQUEST['branch_new_min_discount'][$x],
						':new_max_discount' => $_REQUEST['branch_new_max_discount'][$x],
						':new_cost_price' => $_REQUEST['branch_new_cost_price'][$x],
						':new_selling_price' => $_REQUEST['branch_new_selling_price'][$x],
						':new_margin_percent' => $_REQUEST['margin_percent'][$x],
						':new_item_uom' => $_REQUEST['new_item_uom'][$x],
						':created_dtm' => $_REQUEST['created_dtm'],
						':created_by' => $_REQUEST['created_by']
					);
					$stmt1->execute($data1);
					// print_r($data1);
					// die();
				}
			}


			// print_r($data);
			// die();
			$_SESSION['_msg'] = "Item Price History successfully saved..!";
		}
	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		echo $_SESSION['_msg_err'] = $str;
	}
	header("location:mst_itemprice_history_list.php");
	die();
}

if (isset($_POST['SAVE1'])) {
	try {
		$result = $conn->query("SELECT * FROM mst_branch WHERE branch_id = '" . $_REQUEST['branch_id'] . "' ");
		if ($result->rowCount() > 0) {
			$obj = $result->fetch(PDO::FETCH_OBJ);
		}
		$result1 = $conn->query("SELECT * FROM tbl_item_details WHERE item_id = '" . $_REQUEST['item_id'] . "' ");
		if ($result1->rowCount() > 0) {
			$res = $result1->fetch(PDO::FETCH_OBJ);
		}
		// $update_id = $_REQUEST['item_id'];

		$result2 = $conn->query("SELECT 
		" . $obj->branch_item_maq . " as item_max_qty , 
		" . $obj->branch_stock_field . " as branch_stock_field, 
		" . $obj->branch_item_price . " as item_price, 
		" . $obj->branch_item_discount . " as item_discount,
		" . $obj->branch_item_min_discount . " as item_min_discount,
		" . $obj->branch_item_max_discount . " as item_max_discount,
		" . $obj->branch_item_cost_price . " as item_cost_price, 
		" . $obj->branch_item_selling_price . " as item_selling_price, 
		" . $obj->branch_item_margin . " as item_branch_margin,  
		" . $obj->branch_item_msq . " as item_min_qty, 
		" . $obj->branch_item_moq . " as item_order_min_qty 
		FROM tbl_item_stock WHERE item_id = " . $_REQUEST['item_id']);

		if ($result2->rowCount() > 0) {
			$old = $result2->fetch(PDO::FETCH_OBJ);
		}

		if (
			$_REQUEST['branch_new_price'] != $obj->branch_new_price || $_REQUEST['branch_stock_field'] != $obj->branch_stock_field || $_REQUEST['branch_new_discount'] != $obj->branch_new_discount || $_REQUEST['branch_new_cost_price'] != $obj->branch_new_cost_price ||
			$_REQUEST['branch_new_selling_price'] != $obj->branch_new_selling_price || $_REQUEST['margin_percent'] != $obj->branch_new_margin || $_REQUEST['branch_new_msq'] != $obj->branch_new_msq || $_REQUEST['branch_new_maq'] != $obj->branch_new_maq ||
			$_REQUEST['branch_new_moq'] != $obj->branch_new_moq || $_REQUEST['new_item_uom'] != $obj->branch_new_item_uom || $_REQUEST['new_item_hsn'] != $obj->branch_new_item_hsn
		) {

			$_REQUEST['created_dtm'] = date('Y-m-d H:i:s');
			$_REQUEST['created_by'] = $_SESSION['_user_id'];

			$stmt = null;
			$stmt = $conn->prepare("INSERT INTO tbl_itemprice_history (item_id, branch_id,  $obj->branch_new_price, $obj->branch_new_discount, $obj->branch_new_min_discount, $obj->branch_new_max_discount, $obj->branch_new_cost_price, $obj->branch_new_selling_price, $obj->branch_new_margin, $obj->branch_new_item_curr_stock, $obj->branch_new_msq, $obj->branch_new_maq, $obj->branch_new_moq, $obj->branch_new_item_uom, 
			$obj->branch_new_item_hsn, $obj->branch_old_price, $obj->branch_old_discount, $obj->branch_old_min_discount, $obj->branch_old_max_discount, $obj->branch_old_cost_price, $obj->branch_old_selling_price, $obj->branch_old_margin,  $obj->branch_old_item_curr_stock,  $obj->branch_old_msq, $obj->branch_old_maq, $obj->branch_old_moq, $obj->branch_old_item_hsn, $obj->branch_old_item_uom, created_dtm, created_by)
			VALUES (:item_id, :branch_id, :new_price, :new_discount, :new_min_discount, :new_max_discount, :new_cost_price, :new_selling_price, :new_item_margin,  :branch_stock_field, :new_msq, 
			:new_maq, :new_moq, :item_uom, :item_hsn, :old_price, :old_discount, :old_min_discount, :old_max_discount,
			 :old_cost_price, :old_selling_price, :old_item_margin,  :old_branch_stock_field, :old_msq, :old_maq, :old_moq, :old_item_uom, :old_item_hsn, :created_dtm, :created_by)");
			$data = array(
				':item_id' => $_REQUEST['item_id'],
				':branch_id' =>  $_REQUEST['branch_id'],
				':new_price' => $_REQUEST['branch_new_price'],
				':new_discount' => $_REQUEST['branch_new_discount'],
				':new_min_discount' => $_REQUEST['branch_new_min_discount'],
				':new_max_discount' => $_REQUEST['branch_new_max_discount'],
				':new_cost_price' => $_REQUEST['branch_new_cost_price'],
				':new_selling_price' => $_REQUEST['branch_new_selling_price'],
				':new_item_margin' => $_REQUEST['margin_percent'],
				':branch_stock_field' => $_REQUEST['branch_stock_field'],
				':new_msq' => $_REQUEST['branch_new_msq'],
				':new_maq' => $_REQUEST['branch_new_maq'],
				':new_moq' => $_REQUEST['branch_new_moq'],
				':item_uom' => $_REQUEST['new_item_uom'],
				':item_hsn' => $_REQUEST['new_item_hsn'],
				':old_price' => $old->item_price,
				':old_discount' => $old->item_discount,
				':old_min_discount' => $old->item_min_discount,
				':old_max_discount' => $old->item_max_discount,
				':old_cost_price' => $old->item_cost_price,
				':old_selling_price' => $old->item_selling_price,
				':old_item_margin' => $old->item_branch_margin,
				':old_branch_stock_field' => $old->branch_stock_field,
				':old_msq' => $old->item_min_qty,
				':old_maq' => $old->item_max_qty,
				':old_moq' => $old->item_order_min_qty,
				':old_item_uom' => $res->item_uom,
				':old_item_hsn' => $res->item_hsn,
				':created_dtm' => $_REQUEST['created_dtm'],
				':created_by' => $_REQUEST['created_by']
			);
			$stmt->execute($data);

			// print_r($data);
			// die();

			$stmt2 = $conn->prepare("UPDATE tbl_item_details SET item_uom = :item_uom, item_hsn = :item_hsn WHERE item_id = :item_id");
			$data2 = array(
				':item_id' => $_REQUEST['item_id'],
				':item_uom' => $_REQUEST['new_item_uom'],
				':item_hsn' => $_REQUEST['new_item_hsn']
			);
			$stmt2->execute($data2);

			$update_id = $_REQUEST['item_id'];
			if ($update_id > 0) {


				$stmt1 = null;
				$stmt1 = $conn->prepare("UPDATE tbl_item_stock SET 
				" . $obj->branch_item_price . " = :branch_item_price, 
				" . $obj->branch_item_discount . " = :branch_item_discount,  
				" . $obj->branch_item_min_discount . " = :branch_item_min_discount, 
				" . $obj->branch_item_max_discount . " = :branch_item_max_discount,
				" . $obj->branch_item_cost_price . " = :branch_item_cost_price,
				" . $obj->branch_item_selling_price . " = :branch_item_selling_price, 
				" . $obj->branch_item_margin . " = :branch_item_margin, 
				" . $obj->branch_stock_field . " = :branch_stock_field, 
				" . $obj->branch_item_msq . " = :branch_item_msq, 
				" . $obj->branch_item_maq . " = :branch_item_maq,
				" . $obj->branch_item_moq . " = :branch_item_moq 
				WHERE item_id = :item_id");

				$data1 = array(
					':item_id' => $update_id,
					':branch_item_price' => $_REQUEST['branch_new_price'],
					':branch_item_discount' => $_REQUEST['branch_new_discount'],
					':branch_item_min_discount' => $_REQUEST['branch_new_min_discount'],
					':branch_item_max_discount' => $_REQUEST['branch_new_max_discount'],
					':branch_item_cost_price' => $_REQUEST['branch_new_cost_price'],
					':branch_item_selling_price' => $_REQUEST['branch_new_selling_price'],
					':branch_item_margin' => $_REQUEST['margin_percent'],
					':branch_stock_field' => $_REQUEST['branch_stock_field'],
					':branch_item_msq' => $_REQUEST['branch_new_msq'],
					':branch_item_maq' => $_REQUEST['branch_new_maq'],
					':branch_item_moq' => $_REQUEST['branch_new_moq']

				);
				$stmt1->execute($data1);
				//print_r($data1);
				//	 echo 1;
			}
			//print_r($data1);
			//die();




			$_SESSION['_msg'] = "Item Price History successfully saved..!";
		}
	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		echo $_SESSION['_msg_err'] = $str;
	}
	header("location:mst_itemprice_history_list.php");
	die();
}


/*
if(isset($_POST['UPDATE']))
{
	$update_id = $_REQUEST['txtHid'];
	try
		{
			$result = $conn->query("SELECT * FROM tbl_item_details WHERE item_id = ".$_REQUEST['item_id']);	
			if ($result->rowCount()>0)
			{
				$obj = $result->fetch(PDO::FETCH_OBJ);	
			}
				
				
			if($_REQUEST['item_price'] != $obj->item_price || $_REQUEST['item_discount'] != $obj->item_discount || $_REQUEST['item_cost_price'] != $obj->item_cost_price || $_REQUEST['item_selling_price'] != $obj->item_selling_price || $_REQUEST['item_min_qty'] != $obj->item_min_qty || $_REQUEST['item_order_min_qty'] != $obj->item_order_min_qty || $_REQUEST['item_max_qty'] != $obj->item_max_qty)
			{
				$_REQUEST['updated_dtm'] = date('Y-m-d H:i:s');
				$_REQUEST['updated_by'] = $_SESSION['_userid'];
				
			
				$stmt = null;				
				$stmt = $conn->prepare("UPDATE tbl_itemprice_history SET item_id = :item_id, item_price = :item_price, item_discount = :item_discount, item_cost_price = :item_cost_price, item_selling_price = :item_selling_price, item_min_qty = :item_min_qty, item_order_min_qty = :item_order_min_qty, item_max_qty = :item_max_qty, updated_dtm = :updated_dtm, updated_by = :updated_by  WHERE auto_id = :auto_id");		
				$data = array(	
				':auto_id' => $update_id,
				':item_id' => $_REQUEST['item_id'],
				':item_price' => $_REQUEST['item_price'],
				':item_discount' => $_REQUEST['item_discount'],			
				':item_cost_price' => $_REQUEST['item_cost_price'],
				':item_selling_price' => $_REQUEST['item_selling_price'],
				':item_min_qty' => $_REQUEST['item_min_qty'],
				':item_order_min_qty' => $_REQUEST['item_order_min_qty'],
				':item_max_qty' => $_REQUEST['item_max_qty'],
				':updated_dtm' => $_REQUEST['updated_dtm'],
				':updated_by' => $_REQUEST['updated_by']
				);
				$stmt->execute($data);
				
				$stmt1 = $conn->prepare("UPDATE tbl_item_details SET item_price = :item_price, item_discount = :item_discount, item_cost_price = :item_cost_price, item_selling_price = :item_selling_price, item_min_qty = :item_min_qty, item_order_min_qty = :item_order_min_qty,  item_max_qty = :item_max_qty WHERE item_id = :item_id");		
				$data1 = array(	
				':item_id' => $_REQUEST['item_id'],
				':item_price' => $_REQUEST['item_price'],
				':item_discount' => $_REQUEST['item_discount'],			
				':item_cost_price' => $_REQUEST['item_cost_price'],
				':item_selling_price' => $_REQUEST['item_selling_price'],
				':item_min_qty' => $_REQUEST['item_min_qty'],
				':item_order_min_qty' => $_REQUEST['item_order_min_qty'],
				':item_max_qty' => $_REQUEST['item_max_qty']
				);
				$stmt1->execute($data1);
				
				$_SESSION['_msg'] = "Item Price History successfully updated..!";
			}
		}
		catch (Exception $e)
		{		
			$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
			$_SESSION['_msg_err'] = $str;			
		}
		
		header("location:mst_itemprice_history_list.php");	
		die();
}
*/
// $field_name = $dbconn->GetSingleReconrd("mst_branch","branch_stock_field","branch_id",$_SESSION['_user_branch']);


// $result = $conn->query("SELECT * FROM mst_branch WHERE branch_id = ".$_SESSION['_user_branch']);	
// if ($result->rowCount()>0)
// {
// 	$res = $result->fetch(PDO::FETCH_OBJ);	

// }

// $user_branch = $_SESSION['_user_branch'];

// $result_branch = $conn->query("SELECT * FROM mst_branch WHERE branch_id = $user_branch");
// // $auto_id = $dbconn->GetMaxValue("tbl_item_stock", "item_id", "item_id", $_REQUEST['item_id']);
// $update_id = $_REQUEST['item_id'];


// if ($result_branch->rowCount() > 0) {
// 	$branch_data = $result_branch->fetch(PDO::FETCH_OBJ);

// 	if ($update_id != "")
// 	{

// 		$result = $conn->query("SELECT ".$branch_data->branch_item_maq." as item_max_qty ,".$branch_data->branch_item_price." as item_price, ".$branch_data->branch_item_discount." as item_discount,
// 		 ".$branch_data->branch_item_cost_price." as item_cost_price, ".$branch_data->branch_item_selling_price." as item_selling_price, ".$branch_data->branch_item_msq." as item_min_qty, 
// 		 ".$branch_data->branch_item_moq." as item_order_min_qty FROM tbl_item_stock WHERE item_id = ".$update_id);	
// 		if ($result->rowCount()>0)
// 		{
// 			$obj1 = $result->fetch(PDO::FETCH_OBJ);	
// 			$item_id=$obj->item_id;
// 			$new_item_uom=$obj->new_item_uom;
// 			$new_item_hsn=$obj->new_item_hsn;



// 		}
// 	}

// }

$branch_id = $_SESSION['_user_branch'];

if ($_REQUEST['auto_id'] != "") {
	$result = $conn->query("SELECT * FROM tbl_itemprice_history WHERE auto_id = " . $_REQUEST['auto_id']);
	if ($result->rowCount() > 0) {
		$obj = $result->fetch(PDO::FETCH_OBJ);
		$item_id = $obj->item_id;
		$new_item_uom = $obj->new_item_uom;
		$new_item_hsn = $obj->new_item_hsn;
		$margin_per = $obj->new_margin_percent;
		$branch_id = $obj->branch_id;
	}
}


if ($_REQUEST['item_id'] != "") {

	$result = $conn->query("SELECT * FROM tbl_item_details WHERE item_id = " . $_REQUEST['item_id']);
	if ($result->rowCount() > 0) {
		$obj = $result->fetch(PDO::FETCH_OBJ);
		$item_id = $obj->item_id;
		$new_item_uom = $obj->item_uom;
		$new_item_hsn = $obj->item_hsn;
		// $branch_id = $obj->branch_id;	


		if ($obj->multi_uom_id != '') {
			$multi_uom = explode(",", $obj->multi_uom_id);
			$new_item_uoms = $multi_uom;
			// print_r($new_item_uoms);

		}
	}
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<title><?php echo PAGE_TITLE; ?> - Item Price History </title>
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
						"item_id": item_id,
						"mode": "single_uom"
					}
				}).done(function(msg) {
					// alert(msg);
					var data = msg.split('~');
					$('#branch_new_price').val(data[0]);
					$('#branch_new_discount').val(data[1]);
					$('#branch_new_min_discount').val(data[11]);
					$('#branch_new_max_discount').val(data[12]);
					$('#branch_new_cost_price').val(data[2]);
					$('#branch_new_selling_price').val(data[3]);
					$('#branch_new_msq').val(data[4]);
					$('#branch_new_maq').val(data[5]);
					$('#branch_new_moq').val(data[6]);
					$('#branch_stock_field').val(data[7]);
					$('#margin_percent').val(data[8]);

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

			$("#branch_new_price,#branch_new_discount,#margin_percent").keyup(function() {
				var item_price = parseFloat($("#branch_new_price").val());
				var item_discount = parseFloat($("#branch_new_discount").val());
				var margin_percent = parseFloat($("#margin_percent").val());
				console.log("item_price:", item_price, "item_discount:", item_discount, "margin_percent:", margin_percent);

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

				var margin_per = ((item_price * margin_percent) / 100);
				var margin_tot = item_price + margin_per;

				$("#branch_new_cost_price").val(cost_price.toFixed(2));
				$("#branch_new_selling_price").val(margin_tot.toFixed(2));

			}).trigger('keyup');

			$("#branch_new_selling_price").change(function() {
				var item_price = parseFloat($("#branch_new_price").val());
				var item_discount = parseFloat($("#branch_new_discount").val());
				var selling_price = parseFloat($("#branch_new_selling_price").val());
				var new_cost_price = parseFloat($("#branch_new_cost_price").val());

				if (isNaN(item_price) || isNaN(item_discount) || isNaN(selling_price) || isNaN(new_cost_price)) {
					console.error("Invalid numeric input detected. Please check your input fields.");
					return;
				}

				console.log("Before calculation - selling_price:", selling_price, "new_cost_price:", item_price);

				// Check if new_cost_price is zero to avoid division by zero
				var selling_price_per = item_price !== 0 ? ((selling_price - item_price) / item_price) * 100 : 0;

				console.log("After calculation - selling_price_per:", selling_price_per);

				// Check if selling_price_per is NaN
				if (isNaN(selling_price_per)) {
					selling_price_per = 0;
				}

				console.log("After NaN check - selling_price_per:", selling_price_per);

				$("#margin_percent").val(selling_price_per.toFixed(2));
			}).trigger('change');

		});

		function isNumberKey_with_dot(evt) {
			var charCode = evt.which ? evt.which : evt.keyCode;
			var input = evt.target;
			var charStr = String.fromCharCode(charCode);

			// Allow control keys
			if (
				charCode === 8 || // Backspace
				charCode === 9 || // Tab
				charCode === 13 || // Enter
				charCode === 37 || // Left Arrow
				charCode === 39 || // Right Arrow
				charCode === 46 // Delete
			) {
				return true;
			}

			// Allow minus sign only as first character
			if (charStr === '-') {
				return input.selectionStart === 0 &&
					input.value.indexOf('-') === -1;
			}

			// Allow only one decimal point
			if (charStr === '.') {
				return input.value.indexOf('.') === -1;
			}

			// Allow digits only
			return /[0-9]/.test(charStr);
		}

		function fnValidate() {
			// alert("validation")

			if (isNull(document.thisForm.new_price, "Item Price..!")) {
				return false;
			}
			if (isNull(document.thisForm.new_cost_price, "Cost Price..!")) {
				return false;
			}
			if (isNull(document.thisForm.new_selling_price, "Selling Price..!")) {
				return false;
			}

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
							<span class="breadcrumb-item active">Item Price History</span>
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

							<div class="card">
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
										<label class="col-lg-2 col-form-label item_type7">Item<span class="text-mandatory"> *</span></label>

										<div class="col-lg-4">
											<select disabled name="item_id_old" id="item_id_old" data-placeholder="Choose a Item Type.." class="select">
												<option value="">--- Select Item ---</option>
												<?php

												echo $dbconn->fnFillComboFromTable_Where("item_id", "CONCAT(item_code,' - ',item_purchase_code)", "tbl_item_details", "item_id", " WHERE item_status = 1"); ?>
											</select>
											<script>
												document.thisForm.item_id_old.value = "<?php echo $obj->item_id; ?>";
											</script>
											<input type="hidden" class="form-control" name="item_id" id="item_id" maxlength="9" value="<?php echo $obj->item_id; ?>" />
										</div>
									</div>
									<div class="form-group row pt-2">
										<label class="col-lg-2 col-form-label">Branch<span class="text-mandatory"> *</span></label>
										<div class="col-lg-4">
											<select name="branch_id" id="branch_id" data-placeholder="Choose a Item Type.." class="select">
												<option value="">--- Select Branch ---</option>
												<?php echo $dbconn->fnFillComboFromTable_Where("branch_id", "branch_name", "mst_branch", "branch_id", " WHERE branch_status = 1"); ?>
											</select>
											<script>
												document.thisForm.branch_id.value = "<?php echo $branch_id; ?>"
											</script>
											<!-- <input type="hidden" class="form-control"  name="branch_id" id="branch_id"  maxlength="9" value="<?php //echo $obj->branch_id; 
																																					?>" /> -->
										</div>
										<label class="col-lg-2 col-form-label">Item Purchase Discount<span class="text-mandatory"> </span></label>
										<div class="col-lg-4">
											<div class="input-group">
												<span class="input-group-append">
													<input type="text" class="form-control" name="branch_new_discount" id="branch_new_discount" maxlength="9" value="" placeholder="Enter Discount" onKeyPress="return isNumberKey_with_dot(event)" />
												</span>
												<span class="input-group-text">%</span>
											</div>
										</div>
									</div>


									<div class="form-group row pt-2 hide_price">
										<label class="col-lg-2 col-form-label">Item Price<span class="text-mandatory"> *</span></label>
										<div class="col-lg-4">
											<input type="text" class="form-control" name="branch_new_price" id="branch_new_price" maxlength="9" value="" onKeyPress="return isNumberKey_with_dot(event)" placeholder="Enter Price" />
										</div>
										<label class="col-lg-2 col-form-label">Margin (%)<span class="text-mandatory"> *</span></label>
										<div class="col-lg-4">
											<div class="input-group">
												<span class="input-group-append">
													<input type="text" class="form-control" name="margin_percent" id="margin_percent" maxlength="9" value="" placeholder="Enter Margin" onKeyPress="return isNumberKey_with_dot(event)" />
												</span>
												<span class="input-group-text">%</span>
											</div>
										</div>
									</div>
									<div class="form-group row pt-2 hide_price">
										<label class="col-lg-2 col-form-label">Cost Price<span class="text-mandatory"> *</span></label>
										<div class="col-lg-4">
											<input type="text" class="form-control" name="branch_new_cost_price" id="branch_new_cost_price" maxlength="9" value="" onKeyPress="return isNumberKey_with_dot(event)" placeholder="Enter Cost Price" readonly />
										</div>
										<label class="col-lg-2 col-form-label">Selling Price<span class="text-mandatory"> * </span></label>
										<div class="col-lg-4">
											<input type="text" class="form-control" name="branch_new_selling_price" id="branch_new_selling_price" maxlength="9" value="" placeholder="Enter Selling Price" onKeyPress="return isNumberKey_with_dot(event)" />
										</div>
									</div>

									<div class="form-group row pt-2">
										<label class="col-lg-2 col-form-label">Min Discount<span class="text-mandatory"> </span></label>
										<div class="col-lg-4">
											<div class="input-group">
												<span class="input-group-append">
													<input type="text" class="form-control" name="branch_new_min_discount" id="branch_new_min_discount" maxlength="9" value="" placeholder="Enter Min Discount" onKeyPress="return isNumberKey_with_dot(event)" />
												</span>
												<span class="input-group-text">%</span>
											</div>
										</div>
										<label class="col-lg-2 col-form-label">Max Discount<span class="text-mandatory"> </span></label>
										<div class="col-lg-4">
											<div class="input-group">
												<span class="input-group-append">
													<input type="text" class="form-control" name="branch_new_max_discount" id="branch_new_max_discount" maxlength="9" value="" placeholder="Enter Max Discount" onKeyPress="return isNumberKey_with_dot(event)" />
												</span>
												<span class="input-group-text">%</span>
											</div>
										</div>
									</div>

									<div class="form-group row pt-2">
										<label class="col-lg-2 col-form-label">MSQ</label>
										<div class="col-lg-4">
											<input type="text" class="form-control" name="branch_new_msq" id="branch_new_msq" maxlength="9" value="" onKeyPress="return isNumberKey_with_dot(event)" placeholder="Enter MSQ" />
										</div>
										<label class="col-lg-2 col-form-label">MAQ</label>
										<div class="col-lg-4">
											<input type="text" class="form-control" name="branch_new_maq" id="branch_new_maq" maxlength="9" value="" placeholder="Enter MAQ" onKeyPress="return isNumberKey_with_dot(event)" />
										</div>
									</div>
									<div class="form-group row pt-2">
										<label class="col-lg-2 col-form-label">MOQ</label>
										<div class="col-lg-4">
											<input type="text" class="form-control" name="branch_new_moq" id="branch_new_moq" maxlength="9" value="" onKeyPress="return isNumberKey_with_dot(event)" placeholder="Enter MOQ" />
										</div>
										<label class="col-lg-2 col-form-label">Current Stock</label>
										<div class="col-lg-4">
											<input type="text" class="form-control" name="branch_stock_field" id="branch_stock_field" maxlength="9" value="" placeholder="Enter MAQ" onKeyPress="return isNumberKey_with_dot(event)" />
										</div>

									</div>

									<div class="form-group row pt-2">
										<label class="col-lg-2 col-form-label">UOM</label>
										<div class="col-lg-4">
											<select name="new_item_uom" id="new_item_uom" data-placeholder="Choose a UOM.." class="select-search">
												<option value="">--- Select UOM ---</option>
												<?php
												echo $dbconn->fnFillComboFromTable_Where("uom_id", "uom_name", "mst_uom", "uom_id", " WHERE uom_status = 1"); ?>
											</select>
											<script>
												document.thisForm.new_item_uom.value = "<?php echo $new_item_uom; ?>";
											</script>
										</div>
										<label class="col-lg-2 col-form-label">HSN</label>
										<div class="col-lg-4">
											<select name="new_item_hsn" id="new_item_hsn" data-placeholder="Choose a HSN.." class="select-search">
												<option value="">--- Select HSN ---</option>
												<?php

												echo $dbconn->fnFillComboFromTable_Where("hsn_id", "CONCAT(hsn_code,' - ',igst,'%')", "mst_hsn", "hsn_id", " WHERE hsn_status = 1"); ?>
											</select>
											<script>
												document.thisForm.new_item_hsn.value = "<?php echo $new_item_hsn; ?>";
											</script>

										</div>
									</div>


								</div>
								<div class="card-footer text-center pt-2">
									<?php if ($_REQUEST["auto_id"] != '') { ?>
										<INPUT class="btn btn-info" type="submit" name="UPDATE" value="UPDATE">
										<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:window.location.href='lst_item_details.php'">
										<input type="hiddden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['auto_id']; ?>">
									<?php } else { ?>
										<INPUT class="btn btn-info" type="submit" name="SAVE1" value="SAVE">
										<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:window.location.href='lst_item_details.php'">
										<input type="hidden" name="txtHid" value="0">
									<?php } ?>
								</div>


							</div>

						</form>

						<form name='thisForm2' id='thisForm2' class="form-horizontal" method='POST' action="" onSubmit="return fnValidate();" enctype="multipart/form-data">

							<?php $multi_uom_id = $dbconn->GetSingleReconrd("tbl_item_details", "multi_uom_id", "item_id", $obj->item_id);
							// $new_item_uom = explode(",", $multi_uom_id);
							// if ($multi_uom_id != '') {
							if (!empty($multi_uom_id)) {
								$new_item_uom = array_filter(
									explode(',', $multi_uom_id),
									function ($value) {
										return is_numeric($value) && (int)$value != 0;
									}
								);

								// Optional: reindex array
								$new_item_uom = array_values($new_item_uom);
								// }
								for ($x = 0; $x < count($new_item_uom); $x++) {
									$uom_name = $dbconn->GetSingleReconrd("mst_uom", "uom_name", "uom_id", $new_item_uom[$x]);

									if ($obj->item_id != "") {
										$result1 = $conn->query("SELECT * FROM tbl_multiuom_itemprice_history WHERE item_id = '" . $obj->item_id . "' AND new_item_uom = '" . $uom_name . "' ");
										if ($result1->rowCount() > 0) {
											$row = $result1->fetch(PDO::FETCH_OBJ);
											// $item_id=$obj->item_id;
											$uom_name = $row->new_item_uom;
											$margin_percent = $row->new_margin_percent;
											$branch_id = $row->branch_id;
											// $new_item_hsn=$obj->new_item_hsn;


										}
									}

							?>
									<br>

									<div class="card">


										<div class="card-body">

											<div class="form-group row pt-2">

												<label class="col-lg-2 col-form-label ">Branch<span class="text-mandatory"> *</span></label>

												<div class="col-lg-4">
													<select name="branch_id[]" id="branch_id<?php echo $x; ?>" data-placeholder="Choose a Item Type.." class="select">
														<option value="">--- Select Branch ---</option>
														<?php

														echo $dbconn->fnFillComboFromTable_Where("branch_id", "branch_name", "mst_branch", "branch_id", " WHERE branch_status = 1"); ?>
													</select>
													<script>
														document.thisForm.branch_id.value = "<?php echo $branch_id; ?>"
													</script>
													<input type="hidden" name="item_id" id="item_id" value="<?php echo $_REQUEST['item_id']; ?>">

													<!-- <input type="hidden" class="form-control"  name="branch_id" id="branch_id"  maxlength="9" value="<?php // echo $branch_id; 
																																							?>" /> -->
												</div>



											</div>
											<div class="form-group row pt-2 hide_price">
												<label class="col-lg-2 col-form-label">Item Discount<span class="text-mandatory"> </span></label>
												<div class="col-lg-4">
													<div class="input-group">
														<span class="input-group-append">
															<input type="text" class="form-control" name="branch_new_discount[]" id="branch_new_discount<?php echo $x; ?>" maxlength="9" value="<?php echo $row->new_discount; ?>" placeholder="Enter Discount" onKeyPress="return isNumberKey_with_dot(event)" />
														</span>
														<span class="input-group-text">%</span>
													</div>
												</div>
												<label class="col-lg-2 col-form-label">Margin (%)<span class="text-mandatory"> *</span></label>
												<div class="col-lg-4">
													<div class="input-group">
														<span class="input-group-append">
															<input type="text" class="form-control" name="margin_percent[]" id="margin_percent<?php echo $x; ?>" maxlength="9" value="<?php echo $margin_percent; ?>" placeholder="Enter Margin" onKeyPress="return isNumberKey_with_dot(event)" />
														</span>
														<span class="input-group-text">%</span>
													</div>
												</div>


											</div>
											<div class="form-group row pt-2 hide_price">
												<label class="col-lg-2 col-form-label">Item Price<span class="text-mandatory"> *</span></label>
												<div class="col-lg-4">
													<input type="text" class="form-control" name="branch_new_price[]" id="branch_new_price<?php echo $x; ?>" maxlength="9" value="<?php echo $row->new_price; ?>" onKeyPress="return isNumberKey_with_dot(event)" placeholder="Enter Price" />
												</div>
												<label class="col-lg-2 col-form-label">Selling Price<span class="text-mandatory"> * </span></label>
												<div class="col-lg-4">
													<input type="text" class="form-control" name="branch_new_selling_price[]" id="branch_new_selling_price<?php echo $x; ?>" maxlength="9" value="<?php echo $row->new_selling_price; ?>" placeholder="Enter Selling Price" onKeyPress="return isNumberKey_with_dot(event)" />
												</div>
											</div>


											<div class="form-group row pt-2">
												<label class="col-lg-2 col-form-label">UOM</label>

												<div class="col-lg-4">
													<input type="text" class="form-control" name="new_item_uom[]" id="new_item_uom<?php echo $x; ?>" maxlength="" value="<?php echo $uom_name; ?>" tabindex="-1" readonly placeholder="Enter Cost Price" onKeyPress="return isNumberKey_with_dot(event)" />
												</div>

												<label class="col-lg-2 col-form-label">Cost Price<span class="text-mandatory"> *</span></label>
												<div class="col-lg-4">
													<input type="text" class="form-control" name="branch_new_cost_price[]" id="branch_new_cost_price<?php echo $x; ?>" maxlength="9" value="<?php echo $row->new_cost_price; ?>" onKeyPress="return isNumberKey_with_dot(event)" placeholder="Enter Cost Price" readonly />
												</div>

											</div>


										</div>
									</div>
									<script>
										$("#branch_id<?php echo $x; ?>").change(function() {

											var branch_id = $(this).val();
											var item_id = $('#item_id').val();
											var new_item_uom = $('#new_item_uom<?php echo $x; ?>').val();
											// alert(branch_id);
											$.ajax({
												type: "POST",
												url: "inc/cis_ajax/jquery_get_item_details.php",
												data: {
													"branch_id": branch_id,
													"item_id": item_id,
													"new_item_uom": new_item_uom,
													"mode": "multi_uom"
												}
											}).done(function(msg) {
												// alert(msg);
												var data = msg.split('~');
												$('#branch_new_price<?php echo $x; ?>').val(data[0]);
												$('#branch_new_discount<?php echo $x; ?>').val(data[1]);
												$('#branch_new_min_discount<?php echo $x; ?>').val(data[5]);
												$('#branch_new_max_discount<?php echo $x; ?>').val(data[6]);
												$('#branch_new_cost_price<?php echo $x; ?>').val(data[2]);
												$('#branch_new_selling_price<?php echo $x; ?>').val(data[3]);
												$('#margin_percent<?php echo $x; ?>').val(data[4]);

											});
										}).trigger('change');
										$("#branch_new_price<?php echo $x; ?>,#branch_new_discount<?php echo $x; ?>,#margin_percent<?php echo $x; ?>").keyup(function() {
											var item_price = parseFloat($("#branch_new_price<?php echo $x; ?>").val());
											var item_discount = parseFloat($("#branch_new_discount<?php echo $x; ?>").val());
											var margin_percent = parseFloat($("#margin_percent<?php echo $x; ?>").val());

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

											$("#branch_new_cost_price<?php echo $x; ?>").val(cost_price.toFixed(2));
											$("#branch_new_selling_price<?php echo $x; ?>").val(margin_tot.toFixed(2));

										}).trigger('keyup');

										$("#branch_new_selling_price<?php echo $x; ?>").change(function() {
											var item_price = parseFloat($("#branch_new_price<?php echo $x; ?>").val());
											var item_discount = parseFloat($("#branch_new_discount<?php echo $x; ?>").val());
											var selling_price = parseFloat($("#branch_new_selling_price<?php echo $x; ?>").val());
											var new_cost_price = parseFloat($("#branch_new_cost_price<?php echo $x; ?>").val());

											if (isNaN(item_price) || isNaN(item_discount) || isNaN(selling_price) || isNaN(new_cost_price)) {
												console.error("Invalid numeric input detected. Please check your input fields.");
												return;
											}

											console.log("Before calculation - selling_price:", selling_price, "new_cost_price:", new_cost_price);

											// Check if new_cost_price is zero to avoid division by zero
											var selling_price_per = new_cost_price !== 0 ? ((selling_price - new_cost_price) / new_cost_price) * 100 : 0;

											console.log("After calculation - selling_price_per:", selling_price_per);

											// Check if selling_price_per is NaN
											if (isNaN(selling_price_per)) {
												selling_price_per = 0;
											}

											console.log("After NaN check - selling_price_per:", selling_price_per);

											$("#margin_percent<?php echo $x; ?>").val(selling_price_per.toFixed(2));
										}).trigger('change');
									</script>
								<?php } ?>


								<div class="card-footer text-center pt-2">
									<?php if ($_REQUEST["auto_id"] != '') { ?>
										<INPUT class="btn btn-info" type="submit" name="UPDATE" value="UPDATE">
										<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:window.location.href='lst_item_details.php'">
										<input type="hiddden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['auto_id']; ?>">
									<?php } else { ?>
										<INPUT class="btn btn-info" type="submit" name="SAVE" value="SAVE">
										<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:window.location.href='lst_item_details.php'">
										<input type="hidden" name="txtHid" value="0">
									<?php } ?>
								</div>

							<?php } ?>






						</form>

					</div>

					<!-- End of This Form UI  --->

				</div>

				<?php include("inc/common/footer.php") ?>
				<!-- /dashboard content -->
			</div>
			<!-- /content area -->


		</div>

	</div>

	</div>


	<!-- /page content -->
</body>

</html>