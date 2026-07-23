<?PHP

ob_start();

session_start();
require_once("inc/common/userclass.php");
isAdmin();


$conn = new dbconnect();
$dbconn = new dbhandler();

if (isset($_POST['SAVE'])) {
	try {
		$_REQUEST['sal_repair_date'] = date("Y-m-d", strtotime($_REQUEST['sal_repair_date']));

		$_REQUEST['sal_repair_slno'] = $dbconn->GetMaxValue('tbl_sales_repair', 'sal_repair_slno', 'company_id', $_SESSION['company_id']) + 1;
		$_REQUEST['sal_repair_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);

		$_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
		$_REQUEST['modify_by'] = $_SESSION['_userid'];


		$stmt = null;
		$stmt = $conn->prepare("INSERT INTO tbl_sales_repair (sal_repair_finyr, sal_repair_slno, sal_repair_date, so_id, inv_id, supp_id, company_id, modify_by, modify_date_time) VALUES (:sal_repair_finyr, :sal_repair_slno, :sal_repair_date, :so_id, :inv_id, :supp_id, :company_id, :modify_by, :modify_date_time)");
		$data = array(
			':sal_repair_finyr' => $_REQUEST['sal_repair_finyr'],
			':sal_repair_slno' => $_REQUEST['sal_repair_slno'],
			':sal_repair_date' => $_REQUEST['sal_repair_date'],
			':so_id' => $_REQUEST['so_id'],
			':inv_id' => $_REQUEST['inv_id'],
			':supp_id' => $_REQUEST['supp_id'],
			':company_id' => $_SESSION['company_id'],
			':modify_by' => $_REQUEST['modify_by'],
			':modify_date_time' => $_REQUEST['modify_date_time'],
		);

		$stmt->execute($data);
		$last_id = $conn->lastInsertId();
		/* ------------ SAVE tbl_po_details  -----------*/
		$delete_details =  "DELETE FROM tbl_sales_repair_details 
					WHERE sal_repair_id = '" . $last_id . "'";
		$result = $conn->prepare($delete_details);
		$result->execute();


		$result = $conn->query("SELECT * FROM tbl_sales_repair_details_temp WHERE session_id = '" . $_SESSION['session_id'] . "' ORDER BY temp_sal_repair_id");

		if ($result->rowCount() > 0) {
			$sal_repair_value = 0;
			$stmt = null;
			$stmt = $conn->prepare("INSERT INTO tbl_sales_repair_details (sal_repair_id, repair_item_id, spare_item_id, repair_qty, repair_unit, repair_tax, repair_selling_price, repair_value, repair_tax_val, repair_net_val, repair_remarks) VALUES (:sal_repair_id, :repair_item_id, :spare_item_id, :repair_qty, :repair_unit, :repair_tax, :repair_selling_price, :repair_value, :repair_tax_val, :repair_net_val, :repair_remarks)");

			while ($obj = $result->fetchAll(PDO::FETCH_ASSOC)) {
				foreach ($obj as $row => $value) {
					$data = array(
						':sal_repair_id' => $last_id,
						':repair_item_id' => $value['temp_repair_item_id'],
						':spare_item_id' => $value['temp_spare_item_id'],
						':repair_qty' => $value['temp_repair_qty'],
						':repair_unit' => $value['temp_repair_unit'],
						':repair_tax' => $value['temp_repair_tax'],
						':repair_selling_price' => $value['temp_repair_selling_price'],
						':repair_value' => $value['temp_repair_value'],
						':repair_tax_val' => $value['temp_repair_tax_val'],
						':repair_net_val' => $value['temp_repair_net_val'],
						':repair_remarks' => $value['temp_repair_remarks']
					);
					$stmt->execute($data);
					$sal_repair_value = $sal_repair_value + $value['temp_repair_net_val'];
				}
			}

			$sql =  "DELETE FROM tbl_sales_repair_details_temp 
					WHERE session_id = '" . $_SESSION['session_id'] . "'";
			$result = $conn->prepare($sql);
			$result->execute();
		}


		$update_quo = $conn->prepare("UPDATE  tbl_sales_repair SET sal_repair_value = :sal_repair_value	WHERE sal_repair_id = :sal_repair_id");
		$data1 = array(
			':sal_repair_id' => $last_id,
			':sal_repair_value' => $sal_repair_value
		);
		$update_quo->execute($data1);

		$update_enq = $conn->prepare("UPDATE tbl_invoice SET repair_submit_status = :repair_submit_status WHERE inv_id = :inv_id");
		$data1 = array(
			':inv_id' => $_REQUEST['inv_id'],
			':repair_submit_status' => 1
		);
		$update_enq->execute($data1);
	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		$_SESSION['_msg_err'] = $str;
	}



	$_SESSION['_msg'] = "Sales Return succesfully Saved..!";
	header("location:repair_indent_list.php");
	die();
}


if (isset($_POST['UPDATE'])) {
	$update_id = $_REQUEST['txtHid'];
	try {
		$_REQUEST['sal_repair_date'] = date("Y-m-d", strtotime($_REQUEST['sal_repair_date']));
		$_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
		$_REQUEST['modify_by'] = $_SESSION['_userid'];
		$stmt = null;
		$stmt = $conn->prepare("UPDATE  tbl_sales_repair SET sal_repair_date = :sal_repair_date, company_id = :company_id, modify_by = :modify_by, modify_date_time = :modify_date_time,  sal_repair_verify_status = :sal_repair_verify_status, sal_repair_verify_date_time = :sal_repair_verify_date_time, sal_repair_verify_by = :sal_repair_verify_by WHERE sal_repair_id = :sal_repair_id");
		$data = array(
			':sal_repair_id' => $update_id,
			':sal_repair_date' => $_REQUEST['sal_repair_date'],
			':company_id' => $_SESSION['company_id'],
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

		$result = $conn->query("SELECT * FROM tbl_sales_repair_details_temp WHERE session_id = '" . $_SESSION['session_id'] . "' ORDER BY temp_sal_repair_id");

		if ($result->rowCount() > 0) {
			$sal_repair_value = 0;
			$stmt = null;
			$stmt = $conn->prepare("INSERT INTO tbl_sales_repair_details (sal_repair_id, repair_item_id, spare_item_id, repair_qty, repair_unit, repair_tax, repair_selling_price, repair_value, repair_tax_val, repair_net_val, repair_remarks) VALUES (:sal_repair_id, :repair_item_id, :spare_item_id, :repair_qty, :repair_unit, :repair_tax, :repair_selling_price, :repair_value, :repair_tax_val, :repair_net_val, :repair_remarks)");

			while ($obj = $result->fetchAll(PDO::FETCH_ASSOC)) {
				foreach ($obj as $row => $value) {
					$data = array(
						':sal_repair_id' => $update_id,
						':repair_item_id' => $value['temp_repair_item_id'],
						':spare_item_id' => $value['temp_spare_item_id'],
						':repair_qty' => $value['temp_repair_qty'],
						':repair_unit' => $value['temp_repair_unit'],
						':repair_tax' => $value['temp_repair_tax'],
						':repair_selling_price' => $value['temp_repair_selling_price'],
						':repair_value' => $value['temp_repair_value'],
						':repair_tax_val' => $value['temp_repair_tax_val'],
						':repair_net_val' => $value['temp_repair_net_val'],
						':repair_remarks' => $value['temp_repair_remarks']
					);
					$stmt->execute($data);
					$sal_repair_value = $sal_repair_value + $value['temp_repair_net_val'];
				}
			}

			$sql =  "DELETE FROM tbl_sales_repair_details_temp WHERE session_id = '" . $_SESSION['session_id'] . "'";
			$result = $conn->prepare($sql);
			$result->execute();
		}
		$update_po = $conn->prepare("UPDATE tbl_sales_repair SET sal_repair_value = :sal_repair_value WHERE sal_repair_id = :sal_repair_id");
		$data1 = array(
			':sal_repair_id' => $update_id,
			':sal_repair_value' => $sal_repair_value
		);
		$update_po->execute($data1);
	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		$_SESSION['_msg_err'] = $str;
	}
	$_SESSION['_msg'] = "Sales Return succesfully Updated..!";
	header("location:repair_indent_list.php");
	die();
}


$sal_repair_date = date('d-m-Y');
$sql1 =  "DELETE FROM tbl_sales_repair_details_temp";
$result1 = $conn->prepare($sql1);
$result1->execute();

if (isset($_REQUEST['sal_repair_id'])) {
	try {
		$sql =  "DELETE FROM tbl_sales_repair_details_temp 
					WHERE session_id = '" . $_SESSION['session_id'] . "'";
		$result = $conn->prepare($sql);
		$result->execute();
		$result1 = $conn->query("SELECT * FROM tbl_sales_repair
						LEFT JOIN tbl_sales_repair_details ON tbl_sales_repair.sal_repair_id = tbl_sales_repair_details.sal_repair_id
						WHERE tbl_sales_repair.sal_repair_status = 1 AND tbl_sales_repair_details.sal_repair_id =" . $_REQUEST['sal_repair_id']);
		if ($result1->rowCount() > 0) {
			$stmt = null;
			$stmt = $conn->prepare("INSERT INTO tbl_sales_repair_details_temp (temp_repair_item_id, temp_spare_item_id, temp_repair_qty, temp_repair_unit, temp_repair_tax, temp_repair_selling_price, temp_repair_value, temp_repair_tax_val, temp_repair_net_val, temp_repair_remarks, session_id, temp_date) VALUES (:temp_repair_item_id, :temp_spare_item_id, :temp_repair_qty, :temp_repair_unit, :temp_repair_tax, :temp_repair_selling_price, :temp_repair_value, :temp_repair_tax_val, :temp_repair_net_val, :temp_repair_remarks, :session_id, :temp_date)");
			while ($obj = $result1->fetchAll(PDO::FETCH_ASSOC)) {
				foreach ($obj as $key => $value) {
					$data = array(
						':temp_repair_item_id' => $value['repair_item_id'],
						':temp_spare_item_id' => $value['spare_item_id'],
						':temp_repair_qty' => $value['repair_qty'],
						':temp_repair_unit' => $value['repair_unit'],
						':temp_repair_tax' => $value['repair_tax'],
						':temp_repair_selling_price' => $value['repair_selling_price'],
						':temp_repair_value' => $value['repair_value'],
						':temp_repair_tax_val' => $value['repair_tax_val'],
						':temp_repair_net_val' => $value['repair_net_val'],
						':temp_repair_remarks' => $value['repair_remarks'],
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

	$result = $conn->query("SELECT * FROM tbl_sales_repair WHERE sal_repair_status = '1' AND sal_repair_id = " . $_REQUEST['sal_repair_id']);
	if ($result->rowCount() > 0) {
		$get = $result->fetch(PDO::FETCH_OBJ);

		if ($get->sal_repair_date != "0000-00-00" && $get->sal_repair_date != "") {
			$sal_repair_date = date("d-m-Y", strtotime($get->sal_repair_date));
		}


		$so_id = $get->so_id;
		$inv_id = $get->inv_id;
		$supp_id = $get->supp_id;
		// $inv_no = $dbconn->GetSingleReconrd("tbl_invoice","inv_slno","inv_id",$inv_id);
		$supp_name = $dbconn->GetSingleReconrd("mst_supplier", "supp_name", "supp_id", $supp_id);

		// $inv_date = $dbconn->GetSingleReconrd("tbl_invoice","inv_date","inv_id",$inv_id);
		// if($inv_date != "0000-00-00" && $inv_date != ""){
		// 	$inv_date = date("d-m-Y", strtotime($inv_date));
		// }



	}
} elseif (isset($_REQUEST['inv_id'])) {
	// try
	// {
	// 	$sql =  "DELETE FROM tbl_sales_repair_details_temp WHERE session_id = '".$_SESSION['session_id']."'";
	// 		$result = $conn->prepare($sql);
	// 		$result->execute();
	// 	$result1 = $conn->query("SELECT * FROM tbl_invoice as a LEFT JOIN tbl_invoice_details as b ON a.inv_id = b.inv_id WHERE a.inv_status = 1 AND b.inv_id =".$_REQUEST['inv_id']);	
	// 	if ($result1->rowCount()>0)
	// 	{
	// 		$stmt = null;				
	// 		$stmt = $conn->prepare("INSERT INTO tbl_sales_repair_details_temp (temp_spare_item_id, temp_repair_qty, temp_repair_unit, temp_repair_tax, temp_repair_selling_price, temp_repair_value, temp_repair_tax_val, temp_repair_net_val, temp_repair_remarks, session_id, temp_date) VALUES (:temp_spare_item_id, :temp_repair_qty, :temp_repair_unit, :temp_repair_tax, :temp_repair_selling_price, :temp_repair_value, :temp_repair_tax_val, :temp_repair_net_val, :temp_repair_remarks, :session_id, :temp_date)");
	// 		while($obj = $result1->fetchAll(PDO::FETCH_ASSOC))
	// 		{
	// 			foreach ($obj as $key => $value) 
	// 			{
	// 	            $temp_repair_selling_price = $dbconn->GetSingleReconrd("mst_stock_items","item_unit_price","item_status = '1' AND item_id",$value['inv_item_id']);

	// 	            $gst_id = $dbconn->GetSingleReconrd("mst_stock_items","gst_id","item_status = '1' AND item_id",$value['inv_item_id']);


	//                    $igst = $dbconn->GetSingleReconrd("mst_gst","igst","gst_status = '1' AND gst_id",$gst_id);
	//                    $temp_repair_tax = $igst;

	//                    $temp_repair_value = ($temp_repair_selling_price * $value['grn_rejected_qty']);
	//                 $temp_repair_tax_val = (($temp_repair_value * $temp_repair_tax) / 100);
	//                 $temp_repair_net_val = $temp_repair_value + $temp_repair_tax_val;
	// 				$data = array(
	// 					':temp_spare_item_id' => $value['inv_item_id'],
	// 					':temp_repair_qty' => $value['grn_rejected_qty'],
	// 					':temp_repair_unit' => $value['grn_unit'],
	// 					':temp_repair_tax' => $temp_repair_tax,
	// 					':temp_repair_selling_price' => $temp_repair_selling_price,
	// 					':temp_repair_value' => $temp_repair_value,
	// 					':temp_repair_tax_val' => $temp_repair_tax_val,
	// 					':temp_repair_net_val' => $temp_repair_net_val,
	// 					':temp_repair_remarks' => $value['grn_remarks_id'],
	// 					':session_id' => $_SESSION['session_id'],
	// 					':temp_date' => date('Y-m-d')
	// 				);
	// 				$stmt->execute($data);
	// 			}
	// 		}
	// 	}
	// }
	// catch (Exception $e)
	// {		
	// 	$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
	// 	$_SESSION['_msg_err'] = $str;			
	// }

	$get_val = $conn->query("SELECT * FROM tbl_invoice WHERE inv_status = '1' AND inv_id = " . $_REQUEST['inv_id']);
	if ($get_val->rowCount() > 0) {
		$get = $get_val->fetch(PDO::FETCH_OBJ);

		if ($get->inv_date != "0000-00-00" && $get->inv_date != "") {
			$inv_date = date("d-m-Y", strtotime($get->inv_date));
		}

		$inv_id = $get->inv_id;
		$supp_id = $get->supp_id;

		$inv_no = $dbconn->GetSingleReconrd("tbl_invoice", "inv_slno", "inv_id", $inv_id);
		$supp_name = $dbconn->GetSingleReconrd("mst_supplier", "supp_name", "supp_id", $supp_id);
		$so_id = $dbconn->GetSingleReconrd("tbl_invoice", "so_id", "inv_id", $inv_id);
	}
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<title><?php echo PAGE_TITLE; ?> - Repair Indent</title>
	<link href="css/main.css" rel="stylesheet" type="text/css" />
	<!--[if IE 8]><link href="css/ie8.css" rel="stylesheet" type="text/css" /><![endif]-->

	<?php

	include_once("inc/common/css-js.php"); ?>
	<!-- AUTO COMPLETE -->
	<script type='text/javascript' src='js/auto/jquery.autocomplete.js'></script>
	<link rel="stylesheet" type="text/css" href="js/auto/jquery.autocomplete.css" />
	<script src="http://www.datejs.com/build/date.js" type="text/javascript"></script>

	<script type="text/javascript">
		var wasSubmitted = false;

		function fnValidate() {
			if (notSelected(document.thisForm.supp_id, "Customer..!")) {
				return false;
			}
			// if(isNull(document.thisForm.supp_id,"Supplier..!")){ return false; }

			var repair_items = document.thisForm.repair_items.value;
			if (document.thisForm.repair_items.value == "-1") {
				alert("Please add Repair Indent Details..");
				return false;
			}


			if (!wasSubmitted) {
				wasSubmitted = true;
				document.thisForm.submit();
				return true;
			}
			return false;

		}
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
					$('#show_table').html(msg);
					var n = msg.indexOf("tbody");
					$('#repair_items').val(n);
					//$('#mc_id').val('');
					// $('#i_name').val('');
					//$("#repair_item_id").val('').trigger('change');
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

			$("#inv_id").change(function() {
				let inv_id = $(this).val();
				if (inv_id > 0) {
					$.ajax({
						type: "POST",
						url: "inc/cis_ajax/jquery_select_customer_invoice.php",
						data: {
							"inv_id": inv_id,
							"mode": "AMCDetails",

						}
					}).done(function(msg) {
						$("#amc_data").html(msg);
					});
				}
			}).trigger('change');

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
				var id = $(this).val();

				$("#item_name").autocomplete("inc/auto/select_repair_indent_items.php?id=" + id, {
					width: 290,
					selectFirst: false,
					autoFocus: true
				});
			});

			$("#item_name").result(function(event, data, formatted) {
				if (data) {
					var name = data[1];
					string = name.split("~");

					$("#inv_qty").val(string[0]);
					$("#item_id").val(string[1]);
					$("#repair_selling_price").val(string[2]);
					$("#repair_tax").val(string[3]);
					$("#repair_unit").val(string[4]);
				}
			});


		});



		function remove_item(temp_sal_repair_id) {

			$.ajax({
				type: "POST",
				url: "inc/cis_ajax/jquery_sales_repair_details.php",
				data: {
					temp_sal_repair_id: temp_sal_repair_id,
					mode: 'delete'
				}
			}).done(function(msg) {
				$('#show_table').html(msg);
				var n = msg.indexOf("tbody");
				$('#repair_items').val(n);
				// find_gst();
			});
		}
	</script>

	<style>
		.borderless {
			border: 0px none transparent !important;
			background-color: transparent !important;
			font-weight: bold;
			font-size: 14px !important;
		}

		.borderless1 {
			border: 0px none transparent !important;
			background-color: transparent !important;
			font-weight: bold;
			color: #c32121 !important;
			font-size: 18px !important;
		}
	</style>

</head>

<body>
	<?php include("inc/common/fixedtop.php") ?>

	<div id="container">
		<?php include("inc/common/sidebar.php") ?>
		<div id="content">

			<div class="wrapper">
				<div class="crumbs">
					<ul id="breadcrumbs" class="breadcrumb">
						<li><a href="home.php">Dashboard</a></li>
						<li><a href="javascript:;">Production</a></li>
						<li class="active"><a href="javascript:;">Repair Indent Details</a></li>
					</ul>
					<?php include("inc/common/recent_pages.php") ?>
				</div>
				<div class="page-header margint20"></div>
				<div class="widget">
					<div class="navbar">
						<div class="navbar-inner">
							<h6>New Repair Indent Details</h6>
							<ul class="nav pull-right">
								<li><a href="repair_indent_list.php"><i class="fa fa-list"></i>List of Repair Indent</a></li>
							</ul>
						</div>
					</div>

					<form name='thisForm' id="validate" class="form-horizontal" method='post' onSubmit="return fnValidate();" enctype="multipart/form-data">
						<fieldset>
							<?php
							if ($_REQUEST['sal_repair_id'] != "") {
								$repair_no = leadingZeros($dbconn->GetSingleReconrd('tbl_sales_repair', 'sal_repair_slno', 'sal_repair_status = "1" AND sal_repair_id', $_REQUEST['sal_repair_id']), 3);
							} else {
								$repair_no = leadingZeros($dbconn->GetMaxValue('tbl_sales_repair', 'sal_repair_slno', 'company_id', $_SESSION['company_id']) + 1, 3);
							}
							?>
							<div class="row-fluid well">
								<input type="hidden" name="repair_items" id="repair_items" value="-1">
								<input type="hidden" name="so_id" value="<?php echo $so_id; ?>">

								<div class="control-group">
									<div class="span3">
										<label class="control-label">Repair No </label>
										<div class="controls">
											<input style="font-size:15px;width:100%" type="text" tabindex="-1" readonly name="repair_no" id="repair_no" value="<?php echo $repair_no; ?>" />
										</div>
									</div>

									<div class="span3">
										<label class="control-label">Repair Date <span class="text-error">*</span></label>
										<div class="controls"><input type="text" class="datepicker span12" id="sal_repair_date" name="sal_repair_date" value="<?php echo $sal_repair_date; ?>" /></div>
									</div>

									<div class="span3">
										<label class="control-label">Customer <span class="text-error">*</span></label>
										<div class="controls">
											<select name="supp_id" id="supp_id" data-placeholder="Choose a Customer.." class="select" style="width: 100%;">
												<option value="">-- Select Customer --</option>
												<?php
												$dbconn = new dbhandler();
												echo $dbconn->fnFillComboFromTable_Where("supp_id", "CONCAT(supp_name,' - ',supp_add2)", "mst_supplier", "supp_id", " WHERE supp_status = '1' AND supp_type = 'C'");
												?>
											</select>
											<script>
												document.thisForm.supp_id.value = "<?php echo $supp_id; ?>";
											</script>
										</div>
									</div>

									<div class="span3">
										<label class="control-label">Invoice <span class="text-error"></span></label>
										<div class="controls">
											<select name="inv_id" id="inv_id" data-placeholder="Choose a Invoice.." class="select">
												<option value="" selected>Select Invoice</option>
												<?php
												if ($inv_id != '') {
													$dbconn = new dbhandler();
													echo $dbconn->fnFillComboFromTable_Where("inv_id", "CONCAT(inv_slno,' - ',inv_date)", "tbl_invoice", "inv_id", " WHERE inv_status = 1 and inv_id=" . $inv_id);
												} ?>
											</select>
											<script>
												document.thisForm.inv_id.value = "<?php echo $inv_id; ?>";
											</script>
											<span id="amc_data" style="color: red;"></span>
										</div>
									</div>



								</div>





								<div class="form-headings align-left">
									<label class="control-label"><strong>Repair Indent Details</strong></label>
								</div>

								<div class="control-group">


									<div class="span3">
										<p><b>Item Name</b></p>
										<div>
											<select name="repair_item_id" id="repair_item_id" data-placeholder="Choose a Item.." class="select" style="width: 100%;">
												<option value=""></option>
												<?php
												$dbconn = new dbhandler();
												echo $dbconn->fnFillComboFromTable_Where("item_id", "CONCAT(item_desciption,' - ',item_code)", "tbl_item_details", "item_id", " WHERE item_status=1 AND item_dept_sales=1");
												?>
											</select>
										</div>
									</div>
									<div class="span3">
										<p><b>Spare Item Name</b></p>
										<div>
											<input type="text" class="span12 noshadow" placeholder="Items" name="item_name" id="item_name" />
											<input type="hidden" name="item_id" id="item_id" value="">

										</div>
									</div>

									<!-- <div class="span1">
                                    <p><b>Inv Qty</b></p>
                                	<input type="text" class="span12" name="inv_qty" id="inv_qty" maxlength="9" readonly tabindex="-1" onkeypress="return isNumberKey_With_Dot(event)" value="" />
                                </div> -->

									<div class="span1">
										<p><b>Qty</b></p>
										<input type="text" class="span12" name="repair_qty" id="repair_qty" maxlength="9" onkeypress="return isNumberKey_With_Dot(event)" value="" />
									</div>

									<div class="span1">
										<p><b>Unit</b></p>
										<div class="input-append">
											<input type="text" class="span12" name="repair_unit" id="repair_unit" maxlength="7" tabIndex="-1" readonly value="" />
										</div>
									</div>

									<div class="span1">
										<p><b>Sale Price</b></p>
										<input type="text" class="span12" name="repair_selling_price" id="repair_selling_price" maxlength="9" tabindex="-1" onkeypress="return isNumberKey_With_Dot(event)" readonly value="" />
									</div>

									<div class="span1">
										<p><b>VAT</b></p>
										<input type="text" class="span12" name="repair_tax" id="repair_tax" maxlength="9" tabindex="-1" readonly onkeypress="return isNumberKey_With_Dot(event)" value="" />
									</div>

									<div class="span1">
										<p><b>Total</b></p>
										<input type="text" class="span12" name="repair_total_value" id="repair_total_value" maxlength="9" tabindex="-1" readonly onkeypress="return isNumberKey_With_Dot(event)" value="" />
									</div>




									<div class="span1">
										<p>&nbsp;</p>
										<button class="btn btn-small btn-warning" id="add_items" type="button"><i class="fa fa-plus"></i>&nbsp; ADD</button>
									</div>


								</div>




								<div class="control-group">
									<div id="show_table" class="span12">
										<table class="table table-bordered">
											<thead>
												<tr>
													<th>Repair Item</th>
													<th>Spare Item</th>
													<th>Rate</th>
													<th>Qty</th>
													<th>Unit</th>
													<th>Item Value</th>
													<th>Taxable Value</th>
													<th>Tax %</th>
													<th>Amount</th>
												</tr>
											</thead>
										</table>
									</div>
								</div>
								<script type="text/javascript">
									remove_item(0);
								</script>





								<script>
									$(function() {
										$("#cus_type").trigger('change');
									});
								</script>
								<script>
									$(function() {
										$("#cus_id").trigger('change');
									});
								</script>

								<div class="form-actions align-center">

									<?php if (isset($_REQUEST["sal_repair_id"])) { ?>
										<INPUT class="btn btn-info" type="submit" name="UPDATE" value="UPDATE">
										<INPUT class="btn" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
										<input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['sal_repair_id']; ?>">
									<?php } else { ?>
										<INPUT class="btn btn-info" type="submit" name="SAVE" value="SAVE">
										<INPUT class="btn" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
										<input type="hidden" name="txtHid" value="0">
									<?php } ?>

								</div>
							</div>

						</fieldset>


					</form>
				</div>
			</div>
		</div>
	</div>

	<?php include("inc/common/footer.php") ?>


</body>

</html>