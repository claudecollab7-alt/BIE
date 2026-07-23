<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
// require_once ("inc/common/dbrowiterator.php");	

isAdmin();


//ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);

$conn = new dbconnect();
$dbconn = new dbhandler();

if (isset($_POST['SAVE'])) {
	try {

		$ledger_type = $dbconn->GetSingleReconrd("mst_accounts_group", "group_type", "group_id", $_REQUEST['group_id']);

		$credit_ledger = $conn->prepare("INSERT INTO mst_ledger (group_id, ledger_name, ledger_type, open_bal, open_bal_type) VALUES (:group_id, :ledger_name, :ledger_type, :open_bal, :open_bal_type)");
		$credit_data = array(
			':group_id' => $_REQUEST['group_id'],
			':ledger_name' => ucwords($_REQUEST['ledger_name']),
			':ledger_type' => $ledger_type,
			':open_bal' => $_REQUEST['open_bal'],
			':open_bal_type' => $_REQUEST['open_bal_type']
		);
		$credit_ledger->execute($credit_data);
		$ledger_last_id = $conn->lastInsertId();

		$supp_id = $dbconn->GetSingleReconrd("mst_supplier_new", "max(supp_id)", "1", "1") + 1;
		$_REQUEST['po_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
		$_REQUEST['branch'] = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id='".$_SESSION['_user_branch']."' AND branch_status", 1);
		//if($_REQUEST['supp_type']=='S')
		// $_REQUEST['supp_code'] = 'S' . leadingzeros($supp_id, 4);
		$_REQUEST['supp_code'] = 'S/' . leadingZeros($supp_id, 4) . '/BIE/'.$_REQUEST['branch'].'/' . $_REQUEST['po_finyr'];
		//else
		//$_REQUEST['supp_code'] ='C'. leadingzeros($supp_id,4);

		$stmt = null;
		$stmt = $conn->prepare("INSERT INTO mst_supplier_new (supp_type, company_branch_id, supp_name, supp_code,  supp_contact_person1, supp_contact_person2, supp_mobile1, supp_mobile2, supp_landline1, supp_landline2, supp_email, supp_website, supp_gst, supp_pan, supp_add1, supp_add2, state_id, district_id, city_id, supp_pincode, supp_bank_name, supp_account_holder_name, supp_account_type, supp_account_no, supp_ifsc_code, supp_bank_branch, supp_credit_days, supp_pay_mode, ledger_id) VALUES 
			(:supp_type, :company_branch_id, :supp_name, :supp_code, :supp_contact_person1, :supp_contact_person2, :supp_mobile1, :supp_mobile2, :supp_landline1, :supp_landline2, :supp_email, :supp_website, :supp_gst, :supp_pan, :supp_add1, :supp_add2, :state_id, :district_id, :city_id, :supp_pincode, :supp_bank_name, :supp_account_holder_name, :supp_account_type, :supp_account_no, :supp_ifsc_code, :supp_bank_branch, :supp_credit_days, :supp_pay_mode, :ledger_id)");
		$data = array(
			':supp_type' => 'S',
			':company_branch_id' => $_SESSION['_user_branch'],
			':supp_name' => ucwords($_REQUEST['supp_name']),
			':supp_code' => $_REQUEST['supp_code'],
			':supp_contact_person1' => ucwords($_REQUEST['supp_contact_person1']),
			':supp_contact_person2' => ucwords($_REQUEST['supp_contact_person2']),
			':supp_mobile1' => $_REQUEST['supp_mobile1'],
			':supp_mobile2' => $_REQUEST['supp_mobile2'],
			':supp_landline1' => $_REQUEST['supp_landline1'],
			':supp_landline2' => $_REQUEST['supp_landline2'],
			':supp_email' => $_REQUEST['supp_email'],
			':supp_website' => $_REQUEST['supp_website'],
			':supp_gst' => strtoupper($_REQUEST['supp_gst']),
			':supp_pan' => strtoupper($_REQUEST['supp_pan']),
			':supp_add1' => $_REQUEST['supp_add1'],
			':supp_add2' => $_REQUEST['supp_add2'],
			':state_id' => $_REQUEST['state_id'],
			':district_id' => $_REQUEST['district_id'],
			':city_id' => $_REQUEST['city_id'],
			':supp_pincode' => $_REQUEST['supp_pincode'],
			':supp_bank_name' => $_REQUEST['supp_bank_name'],
			':supp_account_holder_name' => $_REQUEST['supp_account_holder_name'],
			':supp_account_type' => $_REQUEST['supp_account_type'],
			':supp_account_no' => $_REQUEST['supp_account_no'],
			':supp_ifsc_code' => $_REQUEST['supp_ifsc_code'],
			':supp_bank_branch' => $_REQUEST['supp_bank_branch'],
			':supp_credit_days' => $_REQUEST['supp_credit_days'],
			':supp_pay_mode' => $_REQUEST['supp_pay_mode'],
			':ledger_id' => $ledger_last_id
		);
		$stmt->execute($data);
		$last_id = $conn->lastInsertId();
		$_SESSION['_msg'] = "Supplier succesfully saved..!";
	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		$_SESSION['_msg_err'] = $str;
	}

	header("location:lst_supplier.php");
	die();
}


if (isset($_POST['UPDATE'])) {
	$update_id = $_REQUEST['txtHid'];
	$_REQUEST['branch'] = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id='".$_SESSION['_user_branch']."' AND branch_status", 1);

	$mst_exist = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_id", "supp_id <> " . $update_id . " AND supp_status = 1 AND supp_code", $_REQUEST['supp_code']);

	if ($mst_exist != "") {
		$_SESSION['_msg_err'] = "Supplier Code Already Exist..!";
		header("location:lst_supplier.php");
		die();
	}
	try {
		$ledger_id = $dbconn->GetSingleReconrd("mst_supplier_new", "ledger_id", "supp_id", $update_id);
		$ledger_type = $dbconn->GetSingleReconrd("mst_accounts_group", "group_type", "group_id", $_REQUEST['group_id']);
		$update_ledger = $conn->prepare("UPDATE  mst_ledger SET group_id = :group_id, ledger_name = :ledger_name, ledger_type = :ledger_type, open_bal = :open_bal, open_bal_type = :open_bal_type WHERE ledger_id = :ledger_id");
		$ledger_data = array(
			':ledger_id' => $_REQUEST['ledger_id'],
			':group_id' => $_REQUEST['group_id'],
			':ledger_name' => ucwords($_REQUEST['ledger_name']),
			':ledger_type' => $ledger_type,
			':open_bal' => $_REQUEST['open_bal'],
			':open_bal_type' => $_REQUEST['open_bal_type']
		);

		$update_ledger->execute($ledger_data);


		$stmt = null;
		$stmt = $conn->prepare("UPDATE  mst_supplier_new SET supp_type = :supp_type, company_branch_id = :company_branch_id, supp_name = :supp_name, supp_contact_person1 = :supp_contact_person1, supp_contact_person2 = :supp_contact_person2, supp_mobile1 = :supp_mobile1, supp_mobile2 = :supp_mobile2, supp_landline1 = :supp_landline1, supp_landline2 = :supp_landline2, supp_email = :supp_email, supp_website = :supp_website, supp_gst = :supp_gst, supp_pan = :supp_pan, supp_add1 = :supp_add1, supp_add2 = :supp_add2, state_id = :state_id, district_id = :district_id, city_id = :city_id, supp_pincode = :supp_pincode, branch_add1 = :branch_add1, branch_add2 = :branch_add2, branch_state_id = :branch_state_id, branch_district_id = :branch_district_id, branch_city_id = :branch_city_id, branch_pincode = :branch_pincode, delivery_add1 = :delivery_add1, delivery_add2 = :delivery_add2, delivery_state_id = :delivery_state_id, delivery_district_id = :delivery_district_id, delivery_city_id = :delivery_city_id, delivery_pincode = :delivery_pincode, delivery_gst = :delivery_gst, delivery_pan = :delivery_pan, supp_bank_name = :supp_bank_name, supp_account_holder_name = :supp_account_holder_name, supp_account_type = :supp_account_type, supp_account_no = :supp_account_no, supp_ifsc_code = :supp_ifsc_code, supp_bank_branch = :supp_bank_branch, supp_credit_days = :supp_credit_days, supp_pay_mode = :supp_pay_mode WHERE supp_id = :supp_id");
		$data = array(
			':supp_id' => $update_id,
			':supp_type' => 'S',
			':company_branch_id' => $_SESSION['_user_branch'],
			':supp_name' => ucwords($_REQUEST['supp_name']),
			':supp_contact_person1' => ucwords($_REQUEST['supp_contact_person1']),
			':supp_contact_person2' => ucwords($_REQUEST['supp_contact_person2']),
			':supp_mobile1' => $_REQUEST['supp_mobile1'],
			':supp_mobile2' => $_REQUEST['supp_mobile2'],
			':supp_landline1' => $_REQUEST['supp_landline1'],
			':supp_landline2' => $_REQUEST['supp_landline2'],
			':supp_email' => $_REQUEST['supp_email'],
			':supp_website' => $_REQUEST['supp_website'],
			':supp_gst' => strtoupper($_REQUEST['supp_gst']),
			':supp_pan' => strtoupper($_REQUEST['supp_pan']),
			':supp_add1' => $_REQUEST['supp_add1'],
			':supp_add2' => $_REQUEST['supp_add2'],
			':state_id' => $_REQUEST['state_id'],
			':district_id' => $_REQUEST['district_id'],
			':city_id' => $_REQUEST['city_id'],
			':supp_pincode' => $_REQUEST['supp_pincode'],
			':branch_add1' => $_REQUEST['branch_add1'],
			':branch_add2' => $_REQUEST['branch_add2'],
			':branch_state_id' => $_REQUEST['branch_state_id'],
			':branch_district_id' => $_REQUEST['branch_district_id'],
			':branch_city_id' => $_REQUEST['branch_city_id'],
			':branch_pincode' => $_REQUEST['branch_pincode'],
			':delivery_add1' => $_REQUEST['delivery_add1'],
			':delivery_add2' => $_REQUEST['delivery_add2'],
			':delivery_state_id' => $_REQUEST['delivery_state_id'],
			':delivery_district_id' => $_REQUEST['delivery_district_id'],
			':delivery_city_id' => $_REQUEST['delivery_city_id'],
			':delivery_pincode' => $_REQUEST['delivery_pincode'],
			':delivery_gst' => strtoupper($_REQUEST['delivery_gst']),
			':delivery_pan' => strtoupper($_REQUEST['delivery_pan']),
			':supp_bank_name' => $_REQUEST['supp_bank_name'],
			':supp_account_holder_name' => $_REQUEST['supp_account_holder_name'],
			':supp_account_type' => $_REQUEST['supp_account_type'],
			':supp_account_no' => $_REQUEST['supp_account_no'],
			':supp_ifsc_code' => $_REQUEST['supp_ifsc_code'],
			':supp_bank_branch' => $_REQUEST['supp_bank_branch'],
			':supp_credit_days' => $_REQUEST['supp_credit_days'],
			':supp_pay_mode' => $_REQUEST['supp_pay_mode']
		);

		$stmt->execute($data);


		$_SESSION['_msg'] = "Supplier succesfully Updated..!";
	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		$_SESSION['_msg_err'] = $str;
	}

	header("location:lst_supplier.php");
	die();
}

$supp_id = "";
$supp_name = "";
if (isset($_REQUEST['supp_id'])) {
	$get_val = $conn->query("SELECT * FROM mst_supplier_new WHERE supp_status = '1' AND supp_id = " . $_REQUEST['supp_id']);
	if ($get_val->rowCount() > 0) {
		$obj = $get_val->fetch(PDO::FETCH_OBJ);
		$supp_credit_days = $obj->supp_credit_days;
		$supp_pay_mode = $obj->supp_pay_mode;
		$supp_id = $obj->supp_id;
		$supp_name = $obj->supp_name;
		$supp_code = $obj->supp_code;
		$company_id = $obj->company_id;
		$supp_contact_person1 = $obj->supp_contact_person1;
		$supp_contact_person2 = $obj->supp_contact_person2;
		$supp_mobile1 = $obj->supp_mobile1;
		$supp_mobile2 = $obj->supp_mobile2;
		$supp_landline1 = $obj->supp_landline1;
		$supp_landline2 = $obj->supp_landline2;
		$supp_email = $obj->supp_email;
		$supp_gst = $obj->supp_gst;
		$supp_phone = $obj->supp_phone;
		$supp_add1 = $obj->supp_add1;
		$supp_add2 = $obj->supp_add2;
		$state_id = $obj->state_id;
		$district_id = $obj->district_id;
		$city_id = $obj->city_id;
		$supp_pincode = $obj->supp_pincode;
		$supp_bank_name = $obj->supp_bank_name;
		$supp_account_holder_name = $obj->supp_account_holder_name;
		$supp_account_type = $obj->supp_account_type;
		$supp_account_no = $obj->supp_account_no;
		$supp_ifsc_code = $obj->supp_ifsc_code;
		$supp_bank_branch = $obj->supp_bank_branch;
		$ledger_name = $dbconn->GetSingleReconrd("mst_ledger", "ledger_name", "ledger_id", $obj->ledger_id);
		$open_bal_type = $dbconn->GetSingleReconrd("mst_ledger", "open_bal_type", "ledger_id", $obj->ledger_id);
		$open_bal = $dbconn->GetSingleReconrd("mst_ledger", "open_bal", "ledger_id", $obj->ledger_id);
		$group_id = $dbconn->GetSingleReconrd("mst_ledger", "group_id", "ledger_id", $obj->ledger_id);
	}
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo PAGE_TITLE; ?> - Supplier</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<?php include_once("inc/common/css-js.php"); ?>

	<link rel="stylesheet" type="text/css" href="js/auto/jquery.autocomplete.css" />
</head>


<script type="text/javascript">
	$(function() {

		<?php
			if(isset($_SESSION['_msg']) && $_SESSION['_msg']!=""){
				echo "$.jGrowl('".$_SESSION['_msg']."', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-success', position: 'bottom-right', life:'2000', header: 'Success!' });";
				$_SESSION['_msg'] = "";
			}		
			if(isset($_SESSION['_msg_err']) && $_SESSION['_msg_err']!=""){
				echo "$.jGrowl('".$_SESSION['_msg_err']."', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-danger', position: 'top-right', shutdown:'3000', header: 'Error!' });";
				$_SESSION['_msg_err'] = "";
			}
		?>	
  



		$('#state_id').change(function() {

			var state_id = $('#state_id').val();
			$.ajax({
				type: "POST",
				url: "inc/cis_ajax/jquery_select_district.php",
				data: {
					state_id: state_id
				}
			}).done(function(msg) {

				$('#district_id option').remove();
				var dataArr = msg.split('#');
				$.each(dataArr, function(i, element) {
					if (dataArr[i] != "") {
						var dataArr2 = dataArr[i].split('~');
						$('#district_id').append("<option value='" + dataArr2[0] + "'>" + dataArr2[1] + "</option>");
					}
				});
				$("#s2id_district_id").select2('val', '');
				$("#district_id").trigger("liszt:updated");
			});
		});

		$('#district_id').change(function() {

			var district_id = $('#district_id').val();
			$.ajax({
				type: "POST",
				url: "inc/cis_ajax/jquery_select_city.php",
				data: {
					district_id: district_id
				}
			}).done(function(msg) {

				$('#city_id option').remove();
				var dataArr = msg.split('#');
				$.each(dataArr, function(i, element) {
					if (dataArr[i] != "") {
						var dataArr2 = dataArr[i].split('~');
						$('#city_id').append("<option value='" + dataArr2[0] + "'>" + dataArr2[1] + "</option>");
					}
				});
				$("#s2id_city_id").select2('val', '');
				$("#city_id").trigger("liszt:updated");
			});
		});

		$("#supp_name").change(function() {
			var supp_name = $(this).val();
			$("#ledger_name").val(supp_name);
		});


	});

	function fnValidate() {
		//if(notSelected(document.thisForm.supp_type,"Customer Type..!")){ return false; }
		if (isNull(document.thisForm.supp_name, "Business Name..")) {
			return false;
		}
		//if(isNull(document.thisForm.supp_code,"Supplier Code..")){ return false; }
		if (isNull(document.thisForm.supp_contact_person1, "Contact Person1..")) {
			return false;
		}
		if (isNull(document.thisForm.supp_mobile1, "Mobile no..!")) {
			return false;
		}
		// if(isNull(document.thisForm.supp_email,"Email ID..!")){ return false; }
		if ((document.thisForm.supp_email.value) != '') {
			if (notEmail(document.thisForm.supp_email, "e-mail id..!")) {
				return false;
			}
		}
		// if(isNull(document.thisForm.supp_phone,"Phone no..!")){ return false; }

		if (isNull(document.thisForm.supp_gst, "GST..!")) {
			return false;
		}
		if (isNull(document.thisForm.supp_pan, "PAN No..!")) {
			return false;
		}

		if (isNull(document.thisForm.supp_add1, "Address..!")) {
			return false;
		}
		if (notSelected(document.thisForm.state_id, "State..!")) {
			return false;
		}
		if ((document.thisForm.district_id.value) == 0) {
			alert("Please select District..");
			$('#district_id').focus();
			return false;
		}
		if ((document.thisForm.city_id.value) == 0) {
			alert("Please select City..");
			$('#city_id').focus();
			return false;
		}
		if (isNull(document.thisForm.supp_pincode, "Pin Code..!")) {
			return false;
		}
		if (isNull(document.thisForm.ledger_name, "Ledger Name..!")) {
			return false;
		}
		// if(notSelected(document.thisForm.open_bal_type,"Opening Balance Type..!")){ return false; }
		// if(isNull(document.thisForm.open_bal,"Opening Balance..!")){ return false; }
		if (notSelected(document.thisForm.group_id, "Under Group..!")) {
			return false;
		}
		if (isNull(document.thisForm.supp_credit_days, "Credit Days..!")) {
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
							<a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
							<a href="#" class="breadcrumb-item"> Settings</a>
							<span class="breadcrumb-item active">Supplier</span>
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
						<div class="card">
							<div class="card-header bg-pgheader text-white header-elements-inline">
								<h6 class="card-title"> Supplier</h6>
								<div class="header-elements">
									<div class="list-icons">
										<a class="list-icons-item" href="lst_supplier.php" title="Supplier List"><i class="icon-arrow-left52 mr-2"></i></a>
										<a class="list-icons-item" data-action="fullscreen"></a>
									</div>
								</div>

							</div>
							<form name='thisForm' class="form-horizontal" method='POST' action="" onSubmit="return fnValidate();">
								<input type="hidden" name="ledger_id" value="<?php echo $obj->ledger_id; ?>">
								<input type="hidden" name="supp_type" value="<?php echo $obj->supp_type; ?>">
								<fieldset>
									<div class="card-body">
										<div class="form-group">
											<div class="row">
												<label class="col-lg-2  col-form-label">Business Name <span class="text-mandatory"> *</span></label>
												<!-- text-capitalize -->
												<div class="col-lg-4 ">
													<input type="text" name="supp_name" id="supp_name" class="form-control" maxlength="75" placeholder="Business Name" value="<?php echo $supp_name; ?>" />
												</div>
											</div>
										</div>
										<div class="form-group row">
											<label class="col-lg-2 col-form-label">Contact Person1 <span class="text-mandatory"> *</span></label>
											<div class="col-lg-4">
												<input type="text" name="supp_contact_person1" id="supp_contact_person1" class="form-control" maxlength="75" value="<?php echo $supp_contact_person1; ?>" placeholder="Contact Person1" />
											</div>
											<label class="col-lg-2 col-form-label">Contact Person2</label>
											<div class="col-lg-4">
												<input type="text" name="supp_contact_person2" id="supp_contact_person2" class="form-control" maxlength="75" value="<?php echo $supp_contact_person2; ?>" placeholder="Contact Person2">
											</div>
										</div>
										<div class="form-group row">
											<label class="col-lg-2 col-form-label">Mobile No. 1 <span class="text-mandatory"> *</span></label>
											<div class="col-lg-4">
												<div class="input-group">
													<span class="input-group-prepend">
														<span class="input-group-text">+91</span>
													</span>
													<input type="tel" name="supp_mobile1" id="supp_mobile1" class="form-control" data-mask="9999999999" maxlength="10" value="<?php echo $supp_mobile1; ?>" />
												</div>
											</div>
											<label class="col-lg-2 col-form-label">Mobile No. 2</label>
											<div class="col-lg-4">
												<div class="input-group">
													<span class="input-group-prepend">
														<span class="input-group-text">+91</span>
													</span>
													<input type="tel" name="supp_mobile2" id="supp_mobile2" class="form-control" maxlength="10" value="<?php echo $supp_mobile2; ?>" />
												</div>
											</div>
										</div>
										<div class="form-group row">
											<label class="col-lg-2 col-form-label">Landline No. 1</label>
											<div class="col-lg-4">
												<input type="text" name="supp_landline1" id="supp_landline1" class="form-control" maxlength="20" value="<?php echo $supp_landline1; ?>" placeholder="Landline No. 1">
											</div>
											<label class="col-lg-2 col-form-label">Landline No. 2</label>
											<div class="col-lg-4">
												<input type="text" name="supp_landline2" id="supp_landline2" class="form-control" maxlength="20" value="<?php echo $supp_landline2; ?>" placeholder="Landline No. 2">
											</div>
										</div>
										<div class="form-group row">
											<label class="col-lg-2 col-form-label">Email</label>
											<div class="col-lg-4">
												<input type="text" class="form-control email_only" name="supp_email" id="supp_email" maxlength="250" placeholder="Email" value="<?php echo $supp_email; ?>" />
											</div>
											<label class="col-lg-2 col-form-label">Website </label>
											<div class="col-lg-4">
												<div class="input-group">
													<span class="input-group-prepend">
														<span class="input-group-text">www.</span>
													</span>
													<input type="text" name="supp_website" id="supp_website" class="form-control" maxlength="50" value="<?php echo $obj->supp_website; ?>" />
												</div>
											</div>
										</div>
										<div class="form-group row">
											<label class="col-lg-2 col-form-label">GST No.<span class="text-mandatory"> (*)</span></label>
											<div class="col-lg-4">
												<input type="text" class="form-control" name="supp_gst" id="supp_gst" maxlength="15" data-mask="99aaaaa9999a9a*" value="<?php echo $obj->supp_gst; ?>" />
											</div>
											<label class="col-lg-2 col-form-label">PAN No. <span class="text-mandatory"> (*)</span></label>
											<div class="col-lg-4">
												<input type="text" name="supp_pan" id="supp_pan" class="form-control" maxlength="10" data-mask="aaaaa9999a" value="<?php echo $obj->supp_pan; ?>" />
											</div>
										</div>


										<div class="row ml-0 mr-0 pt-1 pb-1" style="background-color:#f9f6f6;">
											<div class="col-md-6 font-weight-semibold">
												<i class="icon-address-book  mr-2"></i>Contact Address
											</div>

										</div>

										<div class="form-group row pt-2">
											<label class="col-lg-2 col-form-label">Address Line1<span class="text-mandatory"> *</span></label>
											<div class="col-lg-4">
												<input type="text" name="supp_add1" id="supp_add1" class="form-control" maxlength="50" value="<?php echo $supp_add1; ?>" />
											</div>
											<label class="col-lg-2 col-form-label">Address Line2</label>
											<div class="col-lg-4">
												<input type="text" name="supp_add2" id="supp_add2" class="form-control" maxlength="50" value="<?php echo $supp_add2; ?>" />
											</div>
										</div>

										<div class="form-group row">
											<label class="col-lg-2 col-form-label">State<span class="text-mandatory"> *</span></label>
											<div class="col-lg-4">
												<select name="state_id" id="state_id" class="form-control select-search" data-fouc>
													<option value="">-- Select State --</option>
													<?php
													$dbconn = new dbhandler();
													echo $dbconn->fnFillComboFromTable_Where("state_id", "state_name", "mst_state", "state_id", "WHERE state_status = 1");
													?>
												</select>
												<script>
													document.thisForm.state_id.value = "<?php echo $obj->state_id; ?>";
												</script>
											</div>
											<label class="col-lg-2 col-form-label">District<span class="text-mandatory"> *</span></label>
											<div class="col-lg-4">
												<select name="district_id" id="district_id" class="form-control select-search" data-fouc>
													<option value="">-- Select District --</option>
													<?php
													if ($obj->state_id != '') {
														$dbconn = new dbhandler();
														echo $dbconn->fnFillComboFromTable_Where("district_id", "district_name", "mst_district", "district_id", " WHERE district_status = 1 and state_id=" . $obj->state_id);
													} ?>
												</select>
												<script>
													document.thisForm.district_id.value = "<?php echo $obj->district_id; ?>";
												</script>
											</div>
										</div>

										<div class="form-group row">
											<label class="col-lg-2 col-form-label">City<span class="text-mandatory"> *</span></label>
											<div class="col-lg-4">
												<select name="city_id" id="city_id" class="form-control select-search" data-fouc>
													<option value="">-- Select City --</option>
													<?php
													if ($obj->district_id != '') {
														$dbconn = new dbhandler();
														echo $dbconn->fnFillComboFromTable_Where("city_id", "city_name", "mst_city", "city_id", " WHERE city_status = 1 and district_id=" . $obj->district_id);
													} ?>
												</select>
												<script>
													document.thisForm.city_id.value = "<?php echo $obj->city_id; ?>";
												</script>
											</div>
											<label class="col-lg-2 col-form-label">Pincode<span class="text-mandatory"> *</span></label>
											<div class="col-lg-4">
												<input type="text" name="supp_pincode" id="supp_pincode" class="form-control" maxlength="6" data-mask="999999" placeholder="Pincode" value="<?php echo $supp_pincode; ?>" />
											</div>
										</div>

										<div class="row ml-0 mr-0 pt-1 pb-1" style="background-color:#f9f6f6;">
											<div class="col-md-6 font-weight-semibold">
												<i class="icon-address-book  mr-2"></i>Bank Deatails
											</div>

										</div>

										<div class="form-group row pt-2">
											<label class="col-lg-2 col-form-label">Bank Name</label>
											<div class="col-lg-4">
												<input type="text" class="form-control name_only text-capitalize" name="supp_bank_name" id="supp_bank_name" maxlength="250" placeholder="Bank Name" value="<?php echo $obj->supp_bank_name; ?>" />
											</div>
											<label class="col-lg-2 col-form-label">Acc. Holder Name</label>
											<div class="col-lg-4">
												<input type="text" class="form-control name_only text-capitalize" name="supp_account_holder_name" id="supp_account_holder_name" maxlength="100" placeholder="Account Holder Name" value="<?php echo $supp_account_holder_name; ?>">
											</div>
										</div>
										<div class="form-group row pt-2">
											<label class="col-lg-2 col-form-label">Account Type</label>
											<div class="col-lg-4">
												<select name="supp_account_type" class="select span12">
													<option value="">Type</option>
													<option value="Current Account">Current Account</option>
													<option value="Saving Account">Saving Account</option>
												</select>
												<script>
													document.thisForm.supp_account_type.value = "<?php echo $supp_account_type; ?>";
												</script>
											</div>
											<label class="col-lg-2 col-form-label">Account No.</label>
											<div class="col-lg-4">
												<input type="text" name="supp_account_no" id="supp_account_no" class="form-control" maxlength="100" onkeypress="return isNumberKey(event)" placeholder="Account No." value="<?php echo $supp_account_no; ?>" />
											</div>
										</div>

										<div class="form-group row">
											<label class="col-lg-2 col-form-label">IFSC Code</label>
											<div class="col-lg-4">
												<input type="text" name="supp_ifsc_code" id="supp_ifsc_code" class="form-control" maxlength="100" placeholder="IFSC Code" value="<?php echo $supp_ifsc_code; ?>" />
											</div>
											<label class="col-lg-2 col-form-label">Branch Name</label>
											<div class="col-lg-4">
												<input type="text" class="form-control name_only  text-capitalize" name="supp_bank_branch" id="supp_bank_branch" maxlength="100" placeholder="Branch Name" value="<?php echo $supp_bank_branch; ?>" />
											</div>
										</div>

										<div class="row ml-0 mr-0 pt-1 pb-1" style="background-color:#f9f6f6;">
											<div class="col-md-12 font-weight-semibold">
												<i class="icon-cabinet  mr-2"></i>Account Details
											</div>
										</div>


										<div class="form-group row pt-2">
											<label class="col-lg-2 col-form-label">Ledger Name <span class="text-mandatory"> *</span></label>
											<div class="col-lg-4">
												<input type="text" name="ledger_name" id="ledger_name" class="form-control alpha_numeric text-capitalize" maxlength="100" placeholder="Ledger Name" value="<?php echo $ledger_name; ?>" />
											</div>
											<label class="col-lg-2 col-form-label">Under Group <span class="text-mandatory"> *</span></label>
											<div class="col-lg-4">
												<select name="group_id" id="group_id" class="form-control select-search" data-fouc>
													<option value="">-- Select Group --</option>
													<?php
													$dbconn = new dbhandler();
													echo $dbconn->fnFillComboFromTable_Where("group_id", "group_name", "mst_accounts_group", "group_id", " WHERE group_status = 1 AND group_pid >'0'"); ?>
												</select>
												<script>
													document.thisForm.group_id.value = "<?php echo $group_id; ?>";
												</script>
											</div>
										</div>


										<div class="form-group row pt-2">
											<label class="col-lg-2 col-form-label">Credit Days <span class="text-mandatory">*</span></label>
											<div class="col-lg-4">
												<input type="text" class="form-control" id="supp_credit_days" name="supp_credit_days" value="<?php echo $supp_credit_days; ?>" />
											</div>
											<label class="col-lg-2 col-form-label">Pay Mode<span class="text-mandatory"></span></label>
												<div class="col-lg-4">
													<select name="supp_pay_mode" id="supp_pay_mode" class="select span6">
														<option value="">Select Pay Mode</option>
														<option value="Cash">Cash</option>
														<option value="Cheque">Cheque</option>
														<option value="NEFT">NEFT/RTGS</option>
														<option value="IMPS">IMPS</option>
													</select>
													<script>
														document.thisForm.supp_pay_mode.value = "<?php echo $supp_pay_mode; ?>";
													</script>
												</div>
										</div>

										<div class="form-group row pt-2">
											<label class="col-lg-2 col-form-label">Opening Balance </label>
											<div class="col-lg-2">
												<select name="open_bal_type" id="open_bal_type" class="form-control select">
													<option value="">Type</option>
													<option value="DR">DR</option>
													<option value="CR">CR</option>
												</select>
												<script>
													document.thisForm.open_bal_type.value = "<?php echo $open_bal_type; ?>";
												</script>
											</div>
											<div class="col-lg-2">
												<input type="text" class="form-control" name="open_bal" id="open_bal" onkeypress="return isNumberKey_With_Dot(event)" placeholder="Opening Balance" maxlength="9" value="<?php echo $open_bal; ?>" />
											</div>
										</div>
									</div>
									<div class="card-footer text-center pt-2">
									
										<div class="form-group ">
											<div class="">
												<?php if ($_REQUEST["supp_id"] != '') { ?>
													<INPUT class="btn btn-info" type="submit" name="UPDATE" value="UPDATE">
													<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:window.location.href='lst_supplier.php'">
													<input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['supp_id']; ?>">
												<?php } else { ?>
													<INPUT class="btn btn-info" type="submit" name="SAVE" value="SAVE">
													<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:window.location.href='lst_supplier.php'">
													<input type="hidden" name="txtHid" id="txtHid" value="0">
												<?php } ?>
											</div>
										</div>
									</div>
										
									
								</fieldset>
							</form>
						</div>
					</div>
				</div>
			</div>
	<?php include("inc/common/footer.php") ?>

		</div>

	</div>
</body>

</html>