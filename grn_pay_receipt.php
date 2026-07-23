<?PHP
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
	// $no_bal = $_REQUEST['pay_amount'] - $_REQUEST['bal_value'];



	$file_pre = str_replace('/', '', $_REQUEST['grn_id']);


	if ($_FILES['grn_bill_copy']['name'] != "") {
		$ext = pathinfo($_FILES['grn_bill_copy']['name'], PATHINFO_EXTENSION);
		$customfilename = $file_pre . '_grn_bill.' . $ext;
		$_REQUEST['grn_bill_copy'] = post_img($customfilename, $_FILES['grn_bill_copy']['tmp_name'], "project_img/grn_payment/");
	} else {
		$_REQUEST['grn_bill_copy'] = $_REQUEST["hide_grn_bill_copy"];
	}

	try {

		$_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
		$_REQUEST['modify_by'] = $_SESSION['_userid'];
		//$last_id = $conn->lastInsertId();
		$pay_type = "S";

		if (isset($_REQUEST['grn_ids'])) {
			foreach ($_REQUEST['grn_ids'] as $key => $value) {
				$grn_ids = implode(',', $_REQUEST['grn_ids']);
			}

			if (count($_REQUEST['grn_ids']) > 0) {
				$pay_type = "M";
			}
		} else {
			$grn_ids = '';
		}

		if (isset($_REQUEST['saved_grn_ids'])) {
			if ($_REQUEST['saved_grn_ids'] != "") {
				$reverse_multi_grn = $conn->prepare("UPDATE tbl_grn SET pay_type= 'S', grn_ids= NULL, grn_bill_copy= '', grn_pay_amount=0, grn_bal_value=0  WHERE grn_id IN (" . $_REQUEST['saved_grn_ids'] . ") AND po_id= '" . $_REQUEST['po_id'] . "' ");
				$reverse_multi_grn->execute();
			}
		}

		// print_r($grn_ids);

		$update_so = $conn->prepare("UPDATE tbl_grn SET grn_bill_copy = :grn_bill_copy, grn_pay_amount = :grn_pay_amount, grn_bal_value = :grn_bal_value, pay_type = :pay_type, grn_ids = :grn_ids, grn_bill_status = :grn_bill_status, grn_pay_finished_status = :grn_pay_finished_status, modify_by= :modify_by, modify_date_time= :modify_date_time WHERE grn_id = :grn_id");
		$data1 = array(
			':grn_id' => $_REQUEST['grn_id'],
			':grn_bill_copy' =>  $_REQUEST['grn_bill_copy'],
			':grn_pay_amount' => $_REQUEST['grn_pay_amount'],
			':grn_bal_value' => $_REQUEST['grn_bal_value'],
			':pay_type' => $pay_type,
			':grn_ids' => $grn_ids,
			':grn_bill_status' => 1,
			':grn_pay_finished_status' => 1,
			':modify_by' => $_REQUEST['modify_by'],
			':modify_date_time' => $_REQUEST['modify_date_time']


		);
		$update_so->execute($data1);

		if (isset($_REQUEST['grn_ids'])) {
			if (count($_REQUEST['grn_ids']) > 0) {
				$update_multi_grn = $conn->prepare("UPDATE tbl_grn SET pay_type= 'N', grn_ids= '" . $_REQUEST['grn_id'] . "', grn_bill_copy= '" . $_REQUEST['grn_bill_copy'] . "', grn_pay_amount=0, grn_bal_value=0  WHERE grn_id IN (" . $grn_ids . ") AND po_id= '" . $_REQUEST['po_id'] . "' ");
				$update_multi_grn->execute();
			}
		}

		$_SESSION['_msg'] = "GRN Payment succesfully saved..!";
	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		$_SESSION['_msg_err'] = $str;
	}

	header("location:grn_pay_receipt.php?grn_id=" . $_REQUEST['grn_id']);
	die();
}

if (isset($_POST['UPDATE'])) {

	// $grn_pay_status = 0;
	$update_id = $_REQUEST['txtHid'];
	$_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
	$_REQUEST['pay_date'] = date("Y-m-d", strtotime($_REQUEST['pay_date']));
	$_REQUEST['pay_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
	$_REQUEST['pay_slno'] = $dbconn->GetMaxValue('tbl_grn_pay_receipt', 'pay_slno', 'pay_finyr', $_REQUEST['pay_finyr']) + 1;

	$file_pre = str_replace('/', '', $_REQUEST['grn_id']);


	if ($_FILES['grn_bill_copy']['name'] != "") {
		$ext = pathinfo($_FILES['grn_bill_copy']['name'], PATHINFO_EXTENSION);
		$customfilename = $file_pre . '_grn_bill.' . $ext;
		$_REQUEST['grn_bill_copy'] = post_img($customfilename, $_FILES['grn_bill_copy']['tmp_name'], "project_img/grn_payment/");
	} else {
		$_REQUEST['grn_bill_copy'] = $_REQUEST["hide_grn_bill_copy"];
	}

	if ($_REQUEST['pay_type'] == 'Q') {
		$chq_passed = "NO";
	} else {
		$chq_passed = "";
	}

	try {

		$stmt = null;
		$stmt = $conn->prepare("INSERT INTO tbl_grn_pay_receipt (grn_id, pay_slno, pay_finyr, pay_date, pay_type, pay_amount, pay_cardno, pay_creditcardno, pay_debitcardno, pay_at, pay_netbank, pay_refno, pay_chq_no, pay_chq_dt, pay_remarks, 
		chq_passed, modify_date_time, pay_status) VALUES (:grn_id, :pay_slno, :pay_finyr, :pay_date, :pay_type, :pay_amount, :pay_cardno, :pay_creditcardno, :pay_debitcardno, :pay_at, :pay_netbank,	 :pay_refno, :pay_chq_no, :pay_chq_dt, 
		:pay_remarks, :chq_passed, :modify_date_time, :pay_status)");
		$data = array(
			':grn_id' => $update_id,
			':pay_slno' => $_REQUEST['pay_slno'],
			':pay_finyr' => $_REQUEST['pay_finyr'],
			':pay_date' => $_REQUEST['pay_date'],
			':pay_type' => $_REQUEST['pay_type'],
			':pay_amount' => $_REQUEST['pay_amount'],
			':pay_cardno' => $_REQUEST['pay_cardno'],
			':pay_refno' => strtoupper($_REQUEST['pay_refno']),
			':pay_creditcardno' => ($_REQUEST['pay_creditcardno']),
			':pay_debitcardno' => ($_REQUEST['pay_debitcardno']),
			':pay_at' => ($_REQUEST['pay_at']),
			':pay_netbank' => ($_REQUEST['pay_netbank']),
			':pay_chq_no' => $_REQUEST['pay_chq_no'],
			':pay_chq_dt' => $_REQUEST['pay_chq_dt'],
			':pay_remarks' => $_REQUEST['pay_remarks'],
			':chq_passed' => $chq_passed,
			':modify_date_time' => $_REQUEST['modify_date_time'],
			':pay_status' => 1

		);
		$stmt->execute($data);


		$sum_pay_amount = $dbconn->GetSingleReconrd("tbl_grn_pay_receipt", "SUM(pay_amount)", "grn_id", $update_id);
		$payable_amount = $dbconn->GetSingleReconrd("tbl_grn", "grn_pay_amount", "grn_id", $update_id);


		if ((float)$sum_pay_amount == (float)$payable_amount) {
			$grn_pay_finish_status = 3;
		} else if ((float)$sum_pay_amount != (float)$payable_amount) {
			$grn_pay_finish_status = 2;
		} else {
			$grn_pay_finish_status = 1;
		}

		$update_so = $conn->prepare("UPDATE tbl_grn SET grn_bill_copy = :grn_bill_copy, grn_bal_value = :grn_bal_value, grn_pay_status = :grn_pay_status, grn_pay_finished_status = :grn_pay_finished_status WHERE grn_id = :grn_id");
		$data1 = array(
			':grn_id' => $update_id,
			':grn_bill_copy' =>  $_REQUEST['grn_bill_copy'],
			':grn_bal_value' => $_REQUEST['grn_bal_value1'],
			':grn_pay_status' =>  1,
			':grn_pay_finished_status' =>  $grn_pay_finish_status



		);
		$update_so->execute($data1);


		$_SESSION['_msg'] = "GRN Payment succesfully Updated..!";
	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		$_SESSION['_msg_err'] = $str;
	}

	header("location:grn_payment_details.php");
	die();
}


//------------------ECHO-----------------------//

$pay_date = date('d-m-Y');
$minDate = date('Y-m-d', strtotime('-6 days'));


if ($_REQUEST['grn_id'] != "") {
	$result = $conn->query("SELECT * FROM  tbl_grn_pay_receipt WHERE  grn_id = '" . $_REQUEST['grn_id'] . "'");
	if ($result->rowCount() > 0) {
		$res = $result->fetch(PDO::FETCH_OBJ);
		$pay_amount = $res->pay_amount;


		// print_r($res);
	}
}

$so_link = $so_no = '';
if ($_REQUEST['grn_id'] != "") {
	$result1 = $conn->query("SELECT * FROM  tbl_grn WHERE grn_id = '" . $_REQUEST['grn_id'] . "'");
	if ($result1->rowCount() > 0) {
		$obj = $result1->fetch(PDO::FETCH_OBJ);

		$grn_pay_amount = $obj->grn_pay_amount;
		$grn_bill_copy = $obj->grn_bill_copy;

		$grn_slno = leadingZeros($obj->grn_slno, 3);
		$paid_amount = $dbconn->GetSingleReconrd("tbl_grn_pay_receipt", "sum(pay_amount)", "grn_id", $obj->grn_id);


		$grn_bal_value = $grn_pay_amount - $paid_amount;


		$customer_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_status = '1' AND supp_id", $obj->supp_id);
		// $party_bill_date = $dbconn->GetSingleReconrd("tbl_purchase_inward","party_bill_date","pi_id",$obj->pi_id);
		$pi_slno = $dbconn->GetSingleReconrd("tbl_purchase_order", "po_slno", "po_id", $obj->po_id);
		$pi_date = $dbconn->GetSingleReconrd("tbl_purchase_order", "po_date", "po_id", $obj->po_id);
		$pi_refno = $dbconn->GetSingleReconrd("tbl_purchase_order", "po_refno", "po_id", $obj->po_id);
		// $party_dc_no = $dbconn->GetSingleReconrd("tbl_purchase_order","party_dc_no","pi_id",$obj->pi_id);
		// $po_ids = $dbconn->GetSingleReconrd("tbl_purchase_order","po_id","pi_id",$obj->pi_id);
		// $po_slno = $dbconn->GetSingleReconrd("tbl_purchase_order","po_slno","po_id",$po_ids);


		$so_link .= '<a href="grn_view.php?grn_id=' . $obj->grn_id . '" target="_blank">' . leadingZeros($obj->grn_slno, 3) . '</a>';
	}
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<title><?php echo PAGE_TITLE; ?> - Sales Receipt </title>
	<link href="css/main.css" rel="stylesheet" type="text/css" />

	<?php include_once("inc/common/css-js.php"); ?>

	<!-- AUTO COMPLETE -->
	<script type='text/javascript' src='js/auto/jquery.autocomplete.js'></script>
	<!-- <link rel="stylesheet" type="text/css" href="js/auto/jquery.autocomplete.css" /> -->
	<?php include_once("inc/common/css-js.php"); ?>


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
							<span class="breadcrumb-item active">Sales Receipt</span>
						</div>
						<a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
					</div>
				</div>
			</div>
			<div class="content pt-0">
				<div class="row">
					<div class="col-md-7">
						<form name='thisForm' id="validate" class="form-horizontal" method='post' action="grn_pay_receipt.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
							<input type="hidden" name="grn_id" id="grn_id" value="<?php echo $_REQUEST['grn_id']; ?>">
							<input type="hidden" name="saved_grn_ids" id="saved_grn_ids" value="<?php echo $obj->grn_ids; ?>">
							<input type="hidden" name="supp_id" id="supp_id" value="<?php echo $obj->supp_id; ?>">
							<input type="hidden" name="grn_pay_status" id="grn_pay_status" value="0.00">
							<input type="hidden" name="po_id" id="po_id" value="<?php echo $obj->po_id; ?>">
							<input type="hidden" name="grn_paid_amount" id="grn_paid_amount" value="<?php echo $paid_amount; ?>">
							<input type="hidden" name="grn_bal_value" id="grn_bal_value" value="<?php echo $grn_bal_value; ?>">
							<input type="hidden" name="grn_bal_value1" id="grn_bal_value1" value="">
							<div class="card">
								<div class="card-header bg-pgheader text-white header-elements-inline">
									<h6 class="card-title">GRN Payment Details</h6>
									<div class="header-elements">
										<div class="list-icons">
											<a class="list-icons-item" href="grn_payment_details.php" title="GRN Payment List "><i class="icon-arrow-left52 mr-2"></i></a>
											<a class="list-icons-item" data-action="fullscreen"></a>
										</div>
									</div>
								</div>
								<?php
								$numbers = str_pad($so_no, 3, '0', STR_PAD_LEFT);
								?>
								<div class="card-body">
									<div class="form-group row">

										<label class="col-lg-2 col-form-label">Supplier :</label>
										<label class="col-lg-3 col-form-label" type="text" name="supp_name" id="supp_name" readonly tabindex="-1" style="font-size:12px; border: none; text-align: left; color: blue; font-weight: bold;" value=""><?php echo $customer_name; ?></label>
									</div>

									<div class="form-group row">
										<label class="col-lg-2 col-form-label">GRN No. :</label>
										<label class="col-lg-3 col-form-label" type="text" id="so_value" name="so_value" tabindex="-1" style="font-size:15px; border: none; text-align: left; color: blue; font-weight: bold;" value=""> <?php echo $obj->grn_ref_code; ?></label>
										<label class="col-lg-3 col-form-label">GRN Date :</label>
										<label class="col-lg-4 col-form-label" type="text" name="bal_value" id="bal_value" readonly tabindex="-1" style="font-size:12px; border: none; text-align: left; color: blue; font-weight: bold;" value=""><?php echo date('d-m-Y', strtotime($obj->grn_date)); ?></label>
									</div>
									<div class="form-group row">
										<label class="col-lg-2 col-form-label">PO No. :</label>
										<label class="col-lg-3 col-form-label" type="text" id="so_value" name="so_value" tabindex="-1" style="font-size:15px; border: none; text-align: left; color: blue; font-weight: bold;" value=""> <?php echo $pi_refno; ?></label>
										<label class="col-lg-3 col-form-label">PO Date :</label>
										<label class="col-lg-4 col-form-label" type="text" name="bal_value" id="bal_value" readonly tabindex="-1" style="font-size:12px; border: none; text-align: left; color: blue; font-weight: bold;" value=""><?php echo date('d-m-Y', strtotime($pi_date)); ?></label>
									</div>
									<div class="form-group row">
										<label class="col-lg-2 col-form-label">Party Bill No. :</label>
										<input class="col-md-3 col-form-label" type="text" id="exissed_amount" name="exissed_amount" readonly tabindex="-1" style="font-size:15px; border: none; text-align: left; color: blue; font-weight: bold;" value='<?php echo $obj->grn_refno; ?>' />
										<label class="col-md-3 col-form-label">Party Bill Dt. :</label>
										<input class="col-md-3 col-form-label" type="text" name="exissed_amount" id="exissed_amount" tabindex="-1" style="font-size:15px; border: none; text-align: left; color: blue; font-weight: bold;" value='<?php echo date('d-m-Y', strtotime($obj->party_bill_date)); ?>' />
									</div>


								</div>
								<div class="card-header bg-pgheader text-white header-elements-inline">
									<h6 class="card-title">Bill Details</h6>
								</div>
								<div class="card-body">
									<div class="form-group row">
										<!-- &nbsp;  -->
										<label class="col-lg-2 col-form-label">Bill Copy <span class="text-mandatory"> *</span></label>
										<div class="col-lg-4"><input type="file" name="grn_bill_copy" id="grn_bill_copy" class="styled">
											<?php if ($grn_bill_copy != "") {
												echo '<a href="javascript:void(0)" onClick="window.open(\'project_img/grn_payment/' . $grn_bill_copy . '\',\'' . $grn_bill_copy . '\',\'width=700,height=505,menubar=no,status=no,scrollbars=no\')" class=\'sub\' title=\'' . $grn_bill_copy . '\' >' . $grn_bill_copy . '</a>';
											} ?>

											<input type="hidden" name="hide_grn_bill_copy" id="hide_grn_bill_copy" value="<?php echo $grn_bill_copy; ?>">
										</div>
										<label class="col-lg-2 col-form-label">Bill Amount <span class="text-mandatory"> *</span></label>
										<div class="  col-lg-4">
											<input type="text" name="grn_pay_amount" id="grn_pay_amount" class="form-control col-lg-12" style="font-size:18px; font-weight:bold; !important" onkeypress="return isNumberKey_With_Dot(event)" maxlength="12" value="<?php echo $grn_pay_amount; ?>" autocomplete="off" />

										</div>
									</div>
									<div class="form-group row">
										<label class="col-lg-2 col-form-label">Multiple GRN <span class="text-mandatory"> </span></label>
										<div class="col-lg-4">
											<select name="grn_ids[]" id="grn_ids" data-placeholder="Choose a GRN.." class="select" multiple>
												<option value="">--- Select GRN ---</option>
												<?php
												$grn_query = "SELECT a.grn_slno,a.grn_id,b.supp_name FROM tbl_grn  as a LEFT JOIN mst_supplier_new as b ON a.supp_id=b.supp_id WHERE a.supp_id ='" . $obj->supp_id . "' AND grn_id !='" . $_REQUEST['grn_id'] . "' AND grn_pay_finished_status = 0 ";
												$grn_res = $conn->query($grn_query);
												if ($grn_res->rowCount() > 0) {
													if ($obj->grn_ids != '') {
														$grn_ids = explode(',', $obj->grn_ids);
													}

													while ($grnObj = $grn_res->fetch()) {
														$selected = '';

														if (isset($grnObj->grn_id) && isset($grn_ids)) {

															if (in_array($grnObj->grn_id, $grn_ids)) {
																$selected = "selected";
															}
														}
														echo '<option value="' . $grnObj->grn_id . '" ' . $selected . '>' . $grnObj->grn_slno . ' - ' . $grnObj->supp_name . '</option>';
													}
												}
												?>
											</select>

										</div>
									</div>
									<div class="form-group row">
										<div class="col-lg-12">
											<center>
												<?php if ($obj->grn_pay_amount > 0 && $obj->grn_pay_finished_status != 3 && $_SESSION['_userid'] == 1) { ?>
													<INPUT class="btn btn-info" type="button" name="edit_grn_amt" id="edit_grn_amt" value="Edit">
													<INPUT class="btn btn-success" type="submit" name="SAVE" id="save_grn_amt" value="SAVE">
													<INPUT class="btn btn-light" type="button" name="Cancel" id="cancel_edit" onClick="javascript:window.location.href='grn_payment_details.php'" value="Cancel">
												<?php } ?>
											</center>
										</div>
									</div>

								</div>
								<div class="card-header bg-pgheader text-white header-elements-inline hide_pay">
									<h6 class="card-title">New Payment Details</h6><span style="font-size:16px;  float:right;">Balance Amount : <?php echo '<span style="color:red; font-size:20px;">' . $grn_bal_value . '</span>'; ?></b></span>
								</div>

								<div class="card-body">
									<div class="form-group row hide_pay">
										<!-- &nbsp;  -->
										<label class="col-lg-2 col-form-label">Payment Dt. <span class="text-mandatory"> *</span></label>
										<div class=" col-lg-4">
											<input type="date" class="form-control col-lg-12" id="pay_date" name="pay_date" maxlength="75" min="<?php echo $minDate; ?>" max="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>" />
										</div>
										<label class="col-lg-2 col-form-label">Amount <span class="text-mandatory"> *</span></label>
										<div class="  col-lg-4">
											<input type="text" name="pay_amount" id="pay_amount" class="form-control col-lg-12" onkeypress="return isNumberKey_With_Dot(event)" maxlength="12" value="" autocomplete="off">
										</div>
									</div>

									<div class="form-group row hide_pay">
										<label class="col-lg-2 col-form-label">Pay Mode <span class="text-mandatory"> *</span></label>
										<div class="  col-lg-5">
											<select name="pay_type" id="pay_type" data-placeholder="Choose a Pay mode.." class=" col-lg-12 select">
												<option value="">--Select--</option>
												<option value="C">Cash</option>
												<option value="Q">Cheque</option>
												<option value="B">Card</option>
												<option value="N">Net Banking</option>
												<option value="A">Account Transfer</option>
												<option value="CN">Credit Note</option>
												<option value="DN">Debit Note</option>
											</select>
										</div>
										<div class="col-lg-5">
											<input type="date" class="form-control" name="pay_chq_dt" id="pay_chq_dt" placeholder="Cheque Dt." value="" />
										</div>
									</div>
									<legend></legend>
									<div class="form-group row hide_pay" id="pay_refno_div">
										<label class="col-lg-2 col-form-label">Ref No <span class="text-mandatory">*</span></label>
										<div class="form-group  col-lg-10">
											<input type="text" class="form-control" name="pay_refno" id="pay_refno" placeholder="Ref No." maxlength="50" value="" autocomplete="off" />
										</div>
									</div>

									<div class="form-group row hide_pay" id="pay_chq_div">

										<label class=" col-lg-2 col-form-label">Cheque No. <span class="text-mandatory">*</span></label>
										<div class="col-lg-10">
											<input type="text" class="form-control  col-lg-12" name="pay_chq_no" id="pay_chq_no" placeholder="Cheque No." onkeypress="return isNumberKey(event)" maxlength="10" value="" autocomplete="off" />
										</div>
									</div>
									<div class="form-group row hide_pay" id="pay_cardno_div">
										<label class="col-lg-2 col-form-label">Card No. <span class="text-mandatory">*</span></label>
										<div class=" col-lg-10">
											<input type="text" class="form-control col-lg-12" name="pay_cardno" id="pay_cardno" placeholder="Card No" value="" maxlength="20" onkeypress="return isNumberKey(event)" />
										</div>
									</div>
									<div class="form-group row hide_pay" id="pay_net_bank_div">
										<label class="col-lg-2 col-form-label">Net Banking Reference No. <span class="text-mandatory">*</span></label>
										<div class=" col-lg-10">
											<input type="text" class="form-control col-lg-12" name="pay_netbank" id="pay_netbank" placeholder="Net Banking Reference No." value="" maxlength="20" onkeypress="return isNumberKey(event)" />
										</div>
									</div>
									<div class="form-group row hide_pay" id="pay_at_div">
										<label class="col-lg-2 col-form-label">Account Transfer <span class="text-mandatory">*</span></label>
										<div class=" col-lg-10">
											<input type="text" class="form-control col-lg-12" name="pay_at" id="pay_at" placeholder="Account Transfer No" value="" maxlength="20" onkeypress="return isNumberKey(event)" />
										</div>
									</div>
									<div class="form-group row hide_pay" id="pay_creditcardno_div">
										<label class="col-lg-2 col-form-label">Credit Card No. <span class="text-mandatory">*</span></label>
										<div class=" col-lg-10">
											<input type="text" class="form-control col-lg-12" name="pay_creditcardno" id="pay_creditcardno" placeholder="Credit Card No" value="" maxlength="20" onkeypress="return isNumberKey(event)" />
										</div>
									</div>
									<div class="form-group row hide_pay" id="pay_debitcardno_div">
										<label class="col-lg-2 col-form-label">Debit Card No. <span class="text-mandatory">*</span></label>
										<div class=" col-lg-10">
											<input type="text" class="form-control col-lg-12" name="pay_debitcardno" id="pay_debitcardno" placeholder="Debit Card No" value="" maxlength="20" onkeypress="return isNumberKey(event)" />
										</div>
									</div>
									<!-- <legend></legend> -->
									<div class="form-group row hide_pay">
										<label class="col-lg-2 col-form-label">Remarks <br> (if any)</label>
										<div class="col-lg-10">
											<textarea type="text" name="pay_remarks" id="pay_remarks" class="form-control" maxlength="250"></textarea>
										</div>
									</div>
								</div>
								<div class="card-footer text-center">
									<?php if ($grn_pay_amount === '0.000' || $grn_pay_amount === '') { ?>
										<INPUT class="btn btn-custom mr-2" type="submit" name="SAVE" value="SAVE">
										<INPUT class="btn btn-light mr-2" type="button" name="Cancel" value="Cancel" onClick="javascript:window.location.href='grn_payment_details.php'">
										<input type="hidden" name="txtHid" id="txtHid" value="0">
									<?php } else { ?>
										<?php if ($grn_pay_amount != $paid_amount) { ?>
											<INPUT class="btn btn-info hide_btns" type="submit" name="UPDATE" value="Update">
											<input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['grn_id']; ?>">
										<?php } ?>
										<INPUT class="btn btn-light mr-2 hide_btns" type="button" name="Cancel" value="Cancel" onClick="javascript:window.location.href='grn_payment_details.php'">

									<?php } ?>
								</div>

							</div>

					</div>
					<div class="col-md-5">
						<div class="card">
							<div class="card-header bg-pgheader text-white header-elements-inline">
								<h6 class="card-title">Payment Details for GRN No. : <span style="font-size:18px;"><b><?php echo $so_link; ?></b></span></h6>
							</div>
							<div class="card-body">
								<table class="table table-xs table-bordered" style="font-size: small !important;">
									<thead>
										<tr class="bg-teal">
											<th width="20%">Date</th>
											<th>Pay Amount</th>
											<th width="40%"> Payment Mode</th>
											<th>Remarks</th>
										</tr>
									</thead>

									<?php
									$result1 = $conn->query("SELECT * FROM tbl_grn_pay_receipt WHERE pay_status = 1 AND grn_id = '" . $_REQUEST['grn_id'] . "' ORDER BY pay_date");

									if ($result1->rowCount() > 0) {
										$Sno = 1;
										$total_amt = 0;


										echo "<tbody>";
										while ($pay = $result1->fetch()) {
											$remarks = '';
											if ($pay->pay_type == "C") {
												// $pmode="Cash";
												$pmode = "Cash";
											} elseif ($pay->pay_type == 'Q') {
												$pmode = "Cheque No. " . $pay->pay_chq_no . " | dt. " . MyFormatDate($pay->pay_chq_dt);
											} elseif ($pay->pay_type == 'B') {
												$pmode = "Card";
											} elseif ($pay->pay_type == 'N') {
												$pmode = "Net Banking <br> <b>Ref.No:</b>" . $pay->pay_netbank;
											} elseif ($pay->pay_type == 'A') {
												$pmode = "Account Transfer <br> <b>Ref.No:</b>" . $pay->pay_at;
											} elseif ($pay->pay_type == 'CN') {
												$pmode = "Credit Card No. <br> <b>Ref.No:</b>" . $pay->pay_creditcardno;
											} elseif ($pay->pay_type == 'DN') {
												$pmode = "Debit Card No. <br> <b>Ref.No:</b>" . $pay->pay_debitcardno;
											}
											// else
											// {

											// 	$pmode="Credit note againt Sales Order";
											// 	$remarks = 'Invoice ' .$invoice_no;	
											// }	
											//<a href = "print_receipt.php?pay_receipt_id='.$pay->pay_receipt_id.'">'.number_format(round($pay->pay_amount),2).'</a>
											echo '<tr class="align-left">						
													<td>' . MyFormatDate($pay->pay_date) . '</td>
													<td align="right">' . number_format(round($pay->pay_amount), 2) . '</td>
													<td>' . $pmode . '</td>									
													<td>' . $pay->pay_remarks . '</td>									
												</tr>';
											$total_amt = $total_amt + $pay->pay_amount;
											$Sno++;
										}
										echo "</tbody>";
									}




									$grn_pay_amt = $dbconn->GetSingleReconrd("tbl_grn_pay_receipt", "sum(pay_amount)", "grn_id", $_REQUEST['grn_id']);
									$bal = floatval($grn_pay_amount) - floatval($pay_amount);

									echo '<tr style="font-weight:bold">								
												<td align="right"> Total </td>
												 <td align="right">' . number_format(round($grn_pay_amt), 2) . '</td>
												<td align="right"></td>	
												<td align="right"></td>	
											</tr>';
									echo '<tr style="font-weight:bold">								
												<td align="right"> Balance </td>
												<td align="right">' . number_format(round($bal), 2) . '</td>
												<td align="right"></td>	
												<td align="right"></td>	
											</tr>';
									echo '</table>';

									?>

								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
			</form>
			<?php include("inc/common/footer.php") ?>
		</div>
	</div>
	<?php include("modal_cash_dts.php") ?>

</html>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.js"></script>
<script language="javascript">
	var wasSubmitted = false;

	function fnValidate() {


		var grn_pay_status = $('#grn_pay_status').val();


		if (grn_pay_status == '0') {

			var grn_pay_amount = parseFloat(document.thisForm.grn_pay_amount.value);
			grn_pay_amount = isNaN(grn_pay_amount) ? 0 : grn_pay_amount;

			if (grn_pay_amount <= 0) {
				alert("Please enter the Bill Amount");
				$('#grn_pay_amount').val('');
				return false;
			}

			var grn_paid_amount = parseFloat(document.thisForm.grn_paid_amount.value);
			grn_paid_amount = isNaN(grn_paid_amount) ? 0 : grn_paid_amount;
			if (grn_pay_amount < grn_paid_amount) {
				alert("GRN amount can't be less than paid amount Rs. " + grn_paid_amount);
				$('#grn_pay_amount').val('');
				return false;
			}
		} else {

			if (isNull(document.thisForm.pay_date, "Pay Date...!")) {
				return false;
			}
			if (isNull(document.thisForm.pay_amount, "Amount...!")) {
				return false;
			}
			if (document.thisForm.pay_amount.value < 1) {
				alert("Please enter the amount...");
				return false;
			}

			// var grn_bal_value = $('#grn_bal_value').val();
			// var pay_amount = parseFloat(document.thisForm.pay_amount.value);


			// if(pay_amount > grn_bal_value)
			// { 
			// 	alert("Pay Amount should not be greater then Bill Amount" + grn_pay_amount +" ...");
			// 	$('#pay_amount').val('');
			// 	return false; 


			// }

			if (notSelected(document.thisForm.pay_type, "Payment Mode..!")) {
				return false;
			}

			if (document.thisForm.pay_type.value == "Q") {
				if (isNull(document.thisForm.pay_chq_no, "Cheque No. ..!")) {
					return false;
				}
				if (isNull(document.thisForm.pay_chq_dt, "Cheque Dt. ..!")) {
					return false;
				}
			}
			if (document.thisForm.pay_type.value == "B") {
				if (isNull(document.thisForm.pay_cardno, "Card No. ..!")) {
					return false;
				}
			}
			if (document.thisForm.pay_type.value == "N") {
				if (isNull(document.thisForm.pay_netbank, "Net Banking Reference No. ..!")) {
					return false;
				}
			}
			if (document.thisForm.pay_type.value == "CN") {
				if (isNull(document.thisForm.pay_creditcardno, "Credit Card No. ..!")) {
					return false;
				}
			}
			if (document.thisForm.pay_type.value == "DN") {
				if (isNull(document.thisForm.pay_debitcardno, "Debit Card No. ..!")) {
					return false;
				}
			}
			if (document.thisForm.pay_type.value == "A") {
				if (isNull(document.thisForm.pay_at, "Account Transfer No. ..!")) {
					return false;
				}
			}

		}
		// 	alert();
		if (!wasSubmitted) {
			wasSubmitted = true;
			document.thisForm.submit();
			return true;
		}
		return false;

	}

	$(function() {
		$("#save_grn_amt").hide();
		$("#edit_grn_amt").click(function() {
			$(this).hide();
			$("#save_grn_amt").show();
			$('#grn_pay_amount').prop('readonly', false);
			$('#grn_pay_amount').val("");
			$('.hide_pay').hide();
			$(".hide_btns").hide();
			$('#grn_pay_status').val("0");

		});

		$("#pay_amount").change(function() {
			// alert();
			var pay_amount = parseFloat($('#pay_amount').val());
			var grn_bal_value = parseFloat($('#grn_bal_value').val());

			var bal = grn_bal_value - pay_amount;


			$('#grn_bal_value1').val(bal);

			if (grn_bal_value < pay_amount) {
				alert("Please check your payment amount >" + grn_bal_value + " ...");
				$('#pay_amount').val('');
			}

		});

		$(document).ready(function() {
			//  alert();
			var grn_pay_amount = $('#grn_pay_amount').val();
			var grn_bill_copy = $('#hide_grn_bill_copy').val();
			var grn_pay_status = $('#grn_pay_status').val();
			var pay_amount = $('#pay_amount').val();
			var grn_bal_value = $('#grn_bal_value').val();

			// alert(grn_pay_status);



			if (grn_pay_amount === '' || grn_pay_amount === '0.000') {
				// alert();
				$('.hide_pay').hide();
				$('#grn_pay_status').val("0");
			} else {
				// alert();

				$('.hide_pay').show();
				$('#grn_pay_status').val("1");

			}
			if (grn_pay_amount > 0 || grn_pay_amount !== '0.000') {

				// alert(grn_pay_amount);

				$('#grn_pay_amount').prop('readonly', true);

			} else {
				$('#grn_pay_amount').prop('readonly', false);

			}

			if (grn_bill_copy == '') {
				$('#grn_bill_copy').prop('disabled', false);
			} else {
				$('#grn_bill_copy').prop('disabled', true);
			}

		});

		$(document).ready(function() {
			$('#pay_chq_div').hide();
			$('#pay_refno_div').hide();
			$('#pay_chq_dt').hide();
			$('#pay_cardno_div').hide();
			$('#cash_denomination').hide();
			$('#pay_debitcardno_div').hide();
			$('#pay_creditcardno_div').hide();
			$('#pay_type').trigger("change");

		});

		$('#pay_type').change(function() {
			var pay_mode = $('#pay_type').val();
			if (pay_mode == "Q") {
				$('#pay_chq_div').show();
				$('#pay_refno_div').hide();
				$('#pay_refno').val('');
				$('#pay_chq_dt').show();
				$('#pay_cardno_div').hide();
				$('#pay_cardno').val('');
				$('#pay_creditcardno_div').hide();
				$('#pay_debitcardno_div').hide();
				$('#pay_at_div').hide();
				$('#pay_net_bank_div').hide();
				$('#cash_denomination').hide();
			} else if (pay_mode == "N") {
				$('#pay_net_bank_div').show();
				$('#pay_chq_dt').hide();
				$('#pay_chq_div').hide();
				$('#pay_chq_no').val('');
				$('#pay_cardno_div').hide();
				$('#pay_cardno').val('');
				$('#pay_creditcardno_div').hide();
				$('#pay_debitcardno_div').hide();
				$('#pay_at_div').hide();
				$('#cash_denomination').hide();

			} else if (pay_mode == "B") {
				$('#pay_cardno_div').show();
				//$('#pay_refno').show();
				$('#pay_chq_dt').hide();
				$('#pay_chq_div').hide();
				$('#pay_chq_no').val('');
				$('#pay_refno_div').hide();
				$('#pay_refno').val('');
				$('#pay_creditcardno_div').hide();
				$('#pay_debitcardno_div').hide();
				$('#pay_at_div').hide();
				$('#pay_net_bank_div').hide();

				$('#cash_denomination').hide();
			} else if (pay_mode == "A") {

				$('#pay_at_div').show();
				$('#pay_chq_dt').hide();
				$('#pay_chq_div').hide();
				$('#pay_chq_no').val('');
				$('#pay_cardno_div').hide();
				$('#pay_cardno').val('');
				$('#pay_refno_div').hide();
				$('#pay_refno').val('');
				$('#pay_net_bank_div').hide();
				$('#cash_denomination').hide();
			} else if (pay_mode == "C") {
				$('#pay_refno_div').hide();
				$('#pay_chq_dt').hide();
				$('#pay_chq_div').hide();
				$('#pay_chq_no').val('');
				$('#pay_cardno_div').hide();
				$('#pay_cardno').val('');
				$('#pay_creditcardno_div').hide();
				$('#pay_debitcardno_div').hide();
				$('#pay_at_div').hide();
				$('#pay_net_bank_div').hide();
				$('#cash_denomination').show();
			} else if (pay_mode == "CN") {
				$('#pay_creditcardno_div').show();
				$('#pay_debitcardno_div').hide();
				$('#pay_chq_dt').hide();
				$('#pay_chq_div').hide();
				$('#pay_chq_no').val('');
				$('#pay_refno_div').hide();
				$('#pay_refno').val('');
				$('#pay_cardno_div').hide();
				$('#pay_at_div').hide();
				$('#pay_net_bank_div').hide();
				$('#pay_cardno').val('');
				$('#cash_denomination').hide();
			} else if (pay_mode == "DN") {
				$('#pay_debitcardno_div').show();
				$('#pay_creditcardno_div').hide();
				$('#pay_chq_dt').hide();
				$('#pay_chq_div').hide();
				$('#pay_refno_div').hide();
				$('#pay_refno').val('');
				$('#pay_chq_no').val('');
				$('#pay_cardno_div').hide();
				$('#pay_at_div').hide();
				$('#pay_net_bank_div').hide();
				$('#pay_cardno').val('');
				$('#cash_denomination').hide();
			} else {
				$('#pay_chq_div').hide();
				$('#pay_chq_no').val('');
				$('#pay_refno_div').hide();
				$('#pay_refno').val('');
				$('#pay_chq_dt').hide();
				$('#pay_cardno_div').hide();
				$('#pay_cardno').val('');
				$('#pay_debitcardno_div').hide();
				$('#pay_debitcardno').val();
				$('#pay_creditcardno_div').hide();
				$('#pay_at_div').hide();
				$('#pay_net_bank_div').hide();
				$('#pay_creditcardno').val();
				$('#cash_denomination').hide();
			}
		});



	});
</script>