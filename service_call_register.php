<?php
// 2026-11-16 new - service call register: a new call or an edit (the same flow as Benzear)
// a call has a customer, address, service details and one or more complaints; each complaint is completed on its own page
// the customer is picked from the list, or typed - then it is found (same GST / mobile / name) or made as a new customer
ob_start();
session_start();
require_once("inc/common/userclass.php");
require_once("inc/common/service_call.php");

isAdmin();
$conn = new dbconnect();
$dbconn = new dbhandler();

$finyr = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);

// the posted call fields, checked
function fnScPostedCall($conn)
{
	$r = $_POST;
	$req = array('state_id' => 'State', 'sc_date' => 'Date', 'customer_name' => 'Customer Name', 'contact_person' => 'Contact Person',
		'mobile_no' => 'Mobile No.', 'enq_add1' => 'Address Line 1', 'nature_of_complaint' => 'Nature of Complaint');
	foreach ($req as $k => $label) {
		if (!isset($r[$k]) || trim($r[$k]) === '' || $r[$k] === '0') {
			throw new Exception("Please enter " . $label . "..!");
		}
	}
	$types = fnScChargeTypes();
	$charge = (int)(isset($r['service_charge_type']) ? $r['service_charge_type'] : 0);
	if (!isset($types[$charge])) {
		throw new Exception("Please select the Service Charge type..!");
	}
	$amount = trim(isset($r['received_amount']) ? $r['received_amount'] : '');
	if ($amount !== '' && !preg_match('/^\d+(\.\d{1,2})?$/', $amount)) {
		throw new Exception("Chargeable Amount must be a number..!");
	}
	$supp_id = (int)(isset($r['supp_id']) ? $r['supp_id'] : 0);
	if ($supp_id > 0 && !$conn->query("SELECT supp_id FROM mst_supplier_new WHERE supp_type = 'C' AND supp_id = " . $supp_id)->fetch()) {
		throw new Exception("Please pick the Customer from the list..!");
	}
	$f = array();
	foreach (array('district_id', 'city_id', 'principal_id', 'state_id') as $k) {
		$f[$k] = (int)(isset($r[$k]) ? $r[$k] : 0);
	}
	foreach (array('enq_pincode', 'customer_name', 'sc_ref_no', 'enq_add1', 'enq_add2', 'prefix', 'contact_person', 'designation', 'mobile_no',
		'tel_no', 'fax_no', 'email', 'enq_website', 'enq_gst', 'enq_pan', 'nature_of_complaint') as $k) {
		$f[$k] = trim(isset($r[$k]) ? $r[$k] : '');
	}
	$f['enq_gst'] = strtoupper($f['enq_gst']);
	$f['enq_pan'] = strtoupper($f['enq_pan']);
	$f['customer_address'] = trim($f['enq_add1'] . ' ' . $f['enq_add2']);
	$f['sc_date'] = date('Y-m-d', strtotime($r['sc_date']));
	$f['supp_id'] = $supp_id;
	$f['service_charge_type'] = $charge;
	$f['received_amount'] = $amount === '' ? 0 : (float)$amount;
	$f['invoice_id'] = $charge == 1 ? (int)(isset($r['invoice_id']) ? $r['invoice_id'] : 0) : 0;
	return $f;
}

if (fnPostTooLarge()) {
	$_SESSION['_msg_err'] = "That was too much data to send in one go. Please try again.";
	header("location:service_call_list.php");
	die();
}

if (isset($_POST['SAVE']) || isset($_POST['UPDATE'])) {
	$update_id = (int)(isset($_POST['txtHid']) ? $_POST['txtHid'] : 0);
	$back = "service_call_register.php" . ($update_id > 0 ? "?sc_id=" . $update_id : "");
	if (!csrf_check('service_call_register')) {
		csrf_fail($back);
	}
	try {
		db_begin($conn);
		$f = fnScPostedCall($conn);
		$complaints = fnScPostedComplaints($conn, $update_id);
		if ($update_id > 0) {
			$saved = $conn->query("SELECT * FROM tbl_service_call_register WHERE sc_id = " . $update_id . " FOR UPDATE")->fetch(PDO::FETCH_OBJ);
			if (!$saved) {
				throw new Exception("Service call not found..!");
			}
			if ($saved->sc_verify_status == 1) {
				throw new Exception("The call is sent for approval - it can't be changed..!");
			}
			// the number is kept; a change of state gives the next number of the new state
			$f['sc_no'] = ($saved->state_id == $f['state_id'] && $saved->sc_no != '') ? $saved->sc_no : fnScNextNo($conn, $f['state_id'], $finyr);
			$f['branch_id'] = $saved->branch_id;
		} else {
			$f['sc_no'] = fnScNextNo($conn, $f['state_id'], $finyr);
			$f['branch_id'] = (int)$_SESSION['_user_branch'];
		}
		if ($f['supp_id'] == 0) {
			$f['supp_id'] = fnScLinkCustomer($conn, (object)$f);	// typed customer: found or made
		}
		$cols = array('sc_no', 'state_id', 'district_id', 'city_id', 'enq_pincode', 'sc_date', 'customer_name', 'supp_id', 'sc_ref_no', 'enq_add1', 'enq_add2',
			'customer_address', 'prefix', 'contact_person', 'designation', 'mobile_no', 'tel_no', 'fax_no', 'email', 'enq_website', 'enq_gst', 'enq_pan',
			'principal_id', 'service_charge_type', 'received_amount', 'invoice_id', 'nature_of_complaint', 'branch_id');
		$data = array();
		foreach ($cols as $c) {
			$data[':' . $c] = $f[$c];
		}
		if ($update_id > 0) {
			$set = array();
			foreach ($cols as $c) {
				$set[] = "$c = :$c";
			}
			$data[':sc_id'] = $update_id;
			$conn->prepare("UPDATE tbl_service_call_register SET " . implode(', ', $set) . " WHERE sc_id = :sc_id")->execute($data);
			$sc_id = $update_id;
		} else {
			$data[':sc_slno'] = (int)$conn->query("SELECT IFNULL(MAX(sc_slno), 0) + 1 FROM tbl_service_call_register")->fetchColumn();
			$data[':sc_finyr'] = $finyr;
			$conn->prepare("INSERT INTO tbl_service_call_register (" . implode(', ', $cols) . ", sc_slno, sc_finyr, sc_status)
				VALUES (:" . implode(', :', $cols) . ", :sc_slno, :sc_finyr, 1)")->execute($data);
			$sc_id = (int)$conn->lastInsertId();
		}
		fnScSaveComplaints($conn, $sc_id, $complaints);
		fnScRefreshStatus($conn, $sc_id);	// an open complaint added to a completed call puts it back to pending
		db_commit($conn);
		$_SESSION['_msg'] = "Service Call " . $f['sc_no'] . " saved..!";
	} catch (Exception $e) {
		db_rollback($conn);
		if (strpos($e->getMessage(), 'SQLSTATE') !== false) {
			fnLogError($e);
		}
		$_SESSION['_msg_err'] = str_replace("'", "", $e->getMessage());
		header("location:" . $back);
		die();
	}
	header("location:service_call_list.php");
	die();
}

$get = (object)array('sc_id' => 0, 'state_id' => 0, 'district_id' => 0, 'city_id' => 0, 'supp_id' => 0, 'sc_no' => '', 'sc_date' => date('Y-m-d'),
	'customer_name' => '', 'sc_ref_no' => '', 'enq_add1' => '', 'enq_add2' => '', 'enq_pincode' => '', 'prefix' => 'Mr.', 'contact_person' => '',
	'designation' => '', 'mobile_no' => '', 'tel_no' => '', 'fax_no' => '', 'email' => '', 'enq_website' => '', 'enq_gst' => '', 'enq_pan' => '',
	'principal_id' => 0, 'service_charge_type' => 0, 'received_amount' => '', 'invoice_id' => 0, 'nature_of_complaint' => '', 'sc_verify_status' => 0,
	'src_system' => '', 'src_sc_no' => '');
$complaints = array();
if (!empty($_REQUEST['sc_id'])) {
	$row = $conn->query("SELECT * FROM tbl_service_call_register WHERE sc_id = " . (int)$_REQUEST['sc_id'])->fetch(PDO::FETCH_OBJ);
	if (!$row) {
		header("location:service_call_list.php");
		die();
	}
	$get = $row;
	$complaints = $conn->query("SELECT * FROM tbl_sc_complaints WHERE sc_id = " . (int)$get->sc_id . " ORDER BY complaint_id")->fetchAll(PDO::FETCH_OBJ);
}
$locked = $get->sc_verify_status == 1;
function h($v)
{
	return htmlspecialchars((string)$v);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<title><?php echo PAGE_TITLE; ?> - Service Call</title>
	<?php include_once("inc/common/css-js.php"); ?>
	<script type="text/javascript">
		var wasSubmitted = false;

		function fnValidate() {
			var need = [['state_id', 'State'], ['sc_date', 'Date'], ['customer_name', 'Customer Name'], ['contact_person', 'Contact Person'],
				['mobile_no', 'Mobile No.'], ['enq_add1', 'Address Line 1'], ['service_charge_type', 'Service Charge'], ['nature_of_complaint', 'Nature of Complaint']];
			for (var i = 0; i < need.length; i++) {
				var v = $.trim($('#' + need[i][0]).val() || '');
				if (v === '' || v === '0') {
					alert('Please enter ' + need[i][1] + '..!');
					$('#' + need[i][0]).focus();
					return false;
				}
			}
			if ($('#sc_lines tr').length == 0) {
				alert('Please add at least one complaint..!');
				return false;
			}
			if (wasSubmitted) {
				return false;
			}
			wasSubmitted = true;
			return true;
		}

		function scRows() {
			$('#sc_lines tr').each(function(i) {
				$(this).find('.line_sno').text(i + 1);
			});
		}

		function remove_complaint(el) {
			if ($(el).closest('tr').data('saved') && !confirm('Remove this complaint?')) {
				return;
			}
			$(el).closest('tr').remove();
			scRows();
		}

		function fnLoadInvoices(supp_id, selected) {
			$('#invoice_id').html("<option value='0'>-- Select Invoice --</option>");
			if (!(supp_id > 0)) {
				return;
			}
			$.post('inc/cis_ajax/jquery_get_invoices_by_supp.php', { supp_id: supp_id }).done(function(msg) {
				$.each(msg.split('#'), function(i, o) {
					if (o !== '') {
						var d = o.split('~');
						$('#invoice_id').append($('<option>').val(d[0]).text(d[1]));
					}
				});
				$('#invoice_id').val(selected || 0).trigger('change.select2');
			});
		}

		function fnFillDistricts(state_id, district_id, city_id) {
			$.post('inc/cis_ajax/jquery_select_district.php', { state_id: state_id }).done(function(msg) {
				$('#district_id').empty();	// the reply starts with its own "-- Select District --"
				$.each(msg.split('#'), function(i, o) {
					if (o !== '') {
						var d = o.split('~');
						$('#district_id').append($('<option>').val(d[0]).text(d[1]));
					}
				});
				$('#district_id').val(district_id || 0).trigger('change.select2');
				fnFillCities(district_id, city_id);
			});
		}

		function fnFillCities(district_id, city_id) {
			$('#city_id').html("<option value='0'>-- Select City --</option>");
			if (!(district_id > 0)) {
				return;
			}
			$.post('inc/cis_ajax/jquery_select_city.php', { district_id: district_id }).done(function(msg) {
				$('#city_id').empty();
				$.each(msg.split('#'), function(i, o) {
					if (o !== '') {
						var d = o.split('~');
						$('#city_id').append($('<option>').val(d[0]).text(d[1]));
					}
				});
				$('#city_id').val(city_id || 0).trigger('change.select2');
			});
		}

		$(function() {
			<?php fnScMessages(); ?>
			scRows();
			fnFillDistricts(<?php echo (int)$get->state_id; ?>, <?php echo (int)$get->district_id; ?>, <?php echo (int)$get->city_id; ?>);
			fnLoadInvoices(<?php echo (int)$get->supp_id; ?>, <?php echo (int)$get->invoice_id; ?>);
			$('#invoice_box').toggle($('#service_charge_type').val() == '1');

			$('#state_id').on('change', function() {
				fnFillDistricts($(this).val(), 0, 0);
				<?php if ($get->sc_id == 0) { ?>
					$.post('inc/cis_ajax/jquery_get_sc_no.php', { state_id: $(this).val() }).done(function(no) {
						$('#sc_no_display').val(no);
					});
				<?php } ?>
			});
			$('#district_id').on('change', function() {
				fnFillCities($(this).val(), 0);
			});
			$('#service_charge_type').on('change', function() {
				$('#invoice_box').toggle($(this).val() == '1');
			});
			// a customer from the list fills its details; "New customer" keeps what is typed
			$('#supp_id').on('change', function() {
				var id = $(this).val();
				fnLoadInvoices(id, 0);
				if (!(id > 0)) {
					return;
				}
				$.post('inc/cis_ajax/jquery_sc_customer.php', { supp_id: id }, null, 'json').done(function(c) {
					if (!c.supp_name) {
						return;
					}
					$('#customer_name').val($.trim(c.supp_name));
					$('#contact_person').val(c.supp_contact_person1);
					$('#mobile_no').val(c.supp_mobile1);
					$('#tel_no').val(c.supp_landline1);
					$('#email').val(c.supp_email);
					$('#enq_website').val(c.supp_website);
					$('#enq_gst').val(c.supp_gst);
					$('#enq_pan').val(c.supp_pan);
					$('#enq_add1').val(c.supp_add1);
					$('#enq_add2').val(c.supp_add2);
					$('#enq_pincode').val(c.supp_pincode);
					if (c.state_id > 0) {
						$('#state_id').val(c.state_id).trigger('change.select2');
						fnFillDistricts(c.state_id, c.district_id, c.city_id);
						<?php if ($get->sc_id == 0) { ?>
							$.post('inc/cis_ajax/jquery_get_sc_no.php', { state_id: c.state_id }).done(function(no) {
								$('#sc_no_display').val(no);
							});
						<?php } ?>
					}
				});
			});
			$('#add_complaint').on('click', function() {
				var d = $.trim($('#complaint_desc').val());
				if (d === '') {
					alert('Please enter Complaint Description..!');
					return;
				}
				$.post('inc/cis_ajax/jquery_sc_complaints.php', { complaint_desc: d }).done(function(row) {
					row = $.trim(row);	// the reply can start with blank lines
					if (row.indexOf('ERR:') === 0) {
						alert(row.substr(4));
						return;
					}
					$('#sc_lines').append(row);
					$('#complaint_desc').val('').focus();
					scRows();
				});
			});
		});
	</script>
</head>

<body>
	<?php include("inc/common/header.php") ?>
	<div class="page-content">
		<?php include("inc/common/sidebar.php") ?>
		<div class="content-wrapper">
			<div class="page-header">
				<div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
					<div class="d-flex">
						<div class="breadcrumb">
							<a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Home</a>
							<a href="service_call_list.php" class="breadcrumb-item">Service Call Register</a>
							<span class="breadcrumb-item active"><?php echo $get->sc_id ? 'Edit' : 'New'; ?> Service Call</span>
						</div>
					</div>
				</div>
			</div>

			<div class="content pt-0">
				<form name="thisForm" id="thisForm" method="post" action="service_call_register.php<?php echo $get->sc_id ? '?sc_id=' . (int)$get->sc_id : ''; ?>" onsubmit="return fnValidate();">
					<?php csrf_fields('service_call_register'); ?>
					<input type="hidden" name="txtHid" value="<?php echo (int)$get->sc_id; ?>">
					<div class="card">
						<div class="card-header bg-pgheader text-white header-elements-inline">
							<h6 class="card-title">Service Call Register
								<?php if ($get->src_system == 'BENZEAR') { ?><span class="badge badge-light ml-2">Transferred from Benzear <?php echo h($get->src_sc_no); ?></span><?php } ?>
							</h6>
							<div class="header-elements"><a class="list-icons-item text-white" href="service_call_list.php" title="List"><i class="icon-arrow-left52"></i></a></div>
						</div>
						<div class="card-body">
							<?php if ($locked) { ?><div class="alert alert-info py-2">This call is sent for approval - it can't be changed.</div><?php } ?>
							<div class="form-group row">
								<label class="col-lg-2 col-form-label">State <span class="text-danger">*</span></label>
								<div class="col-lg-2">
									<select name="state_id" id="state_id" class="form-control select-search" data-placeholder="State">
										<option value="0">-- Select State --</option>
										<?php echo $dbconn->fnFillComboFromTable_Where("state_id", "state_name", "mst_state", "state_name", " WHERE state_status = 1"); ?>
									</select>
									<script>$('#state_id').val('<?php echo (int)$get->state_id; ?>');</script>
								</div>
								<label class="col-lg-1 col-form-label">Call No.</label>
								<div class="col-lg-2"><input type="text" id="sc_no_display" class="form-control" readonly value="<?php echo h($get->sc_no != '' ? $get->sc_no : ''); ?>" placeholder="given on save"></div>
								<label class="col-lg-1 col-form-label">Date <span class="text-danger">*</span></label>
								<div class="col-lg-2"><input type="date" name="sc_date" id="sc_date" class="form-control" value="<?php echo h($get->sc_date); ?>"></div>
								<label class="col-lg-1 col-form-label">Call Reg. No.</label>
								<div class="col-lg-1"><input type="text" name="sc_ref_no" class="form-control" maxlength="50" value="<?php echo h($get->sc_ref_no); ?>"></div>
							</div>

							<legend class="font-weight-semibold"><i class="icon-user mr-2"></i>Customer Details</legend>
							<div class="form-group row">
								<label class="col-lg-2 col-form-label">Customer</label>
								<div class="col-lg-4">
									<select name="supp_id" id="supp_id" class="form-control select-search">
										<option value="0">-- New customer (type the name) --</option>
										<?php echo $dbconn->fnFillComboFromTable_Where("supp_id", "supp_name", "mst_supplier_new", "supp_name", " WHERE supp_type = 'C' AND supp_status = '1'"); ?>
									</select>
									<script>$('#supp_id').val('<?php echo (int)$get->supp_id; ?>');</script>
								</div>
								<label class="col-lg-2 col-form-label">Customer Name <span class="text-danger">*</span></label>
								<div class="col-lg-4"><input type="text" name="customer_name" id="customer_name" class="form-control" maxlength="150" value="<?php echo h($get->customer_name); ?>"></div>
							</div>
							<div class="form-group row">
								<label class="col-lg-2 col-form-label">Contact Person <span class="text-danger">*</span></label>
								<div class="col-lg-1">
									<select name="prefix" class="form-control">
										<?php foreach (array('Mr.', 'Ms.', 'Mrs.', 'Dr.') as $p) {
											echo '<option ' . ($get->prefix == $p ? 'selected' : '') . '>' . $p . '</option>';
										} ?>
									</select>
								</div>
								<div class="col-lg-3"><input type="text" name="contact_person" id="contact_person" class="form-control" maxlength="100" value="<?php echo h($get->contact_person); ?>"></div>
								<label class="col-lg-2 col-form-label">Designation</label>
								<div class="col-lg-4"><input type="text" name="designation" class="form-control" maxlength="50" value="<?php echo h($get->designation); ?>"></div>
							</div>
							<div class="form-group row">
								<label class="col-lg-2 col-form-label">Mobile No. <span class="text-danger">*</span></label>
								<div class="col-lg-2"><input type="text" name="mobile_no" id="mobile_no" class="form-control" maxlength="20" value="<?php echo h($get->mobile_no); ?>"></div>
								<label class="col-lg-1 col-form-label">Tel No.</label>
								<div class="col-lg-2"><input type="text" name="tel_no" id="tel_no" class="form-control" maxlength="20" value="<?php echo h($get->tel_no); ?>"></div>
								<label class="col-lg-1 col-form-label">Fax No.</label>
								<div class="col-lg-2"><input type="text" name="fax_no" class="form-control" maxlength="20" value="<?php echo h($get->fax_no); ?>"></div>
							</div>
							<div class="form-group row">
								<label class="col-lg-2 col-form-label">Email</label>
								<div class="col-lg-2"><input type="text" name="email" id="email" class="form-control" maxlength="100" value="<?php echo h($get->email); ?>"></div>
								<label class="col-lg-1 col-form-label">Website</label>
								<div class="col-lg-2"><input type="text" name="enq_website" id="enq_website" class="form-control" maxlength="100" value="<?php echo h($get->enq_website); ?>"></div>
								<label class="col-lg-1 col-form-label">GST No.</label>
								<div class="col-lg-2"><input type="text" name="enq_gst" id="enq_gst" class="form-control text-uppercase" maxlength="15" value="<?php echo h($get->enq_gst); ?>"></div>
								<label class="col-lg-1 col-form-label">PAN</label>
								<div class="col-lg-1"><input type="text" name="enq_pan" id="enq_pan" class="form-control text-uppercase" maxlength="10" value="<?php echo h($get->enq_pan); ?>"></div>
							</div>

							<legend class="font-weight-semibold"><i class="icon-location4 mr-2"></i>Customer Address</legend>
							<div class="form-group row">
								<label class="col-lg-2 col-form-label">Address Line 1 <span class="text-danger">*</span></label>
								<div class="col-lg-4"><input type="text" name="enq_add1" id="enq_add1" class="form-control" maxlength="100" value="<?php echo h($get->enq_add1); ?>"></div>
								<label class="col-lg-2 col-form-label">Address Line 2</label>
								<div class="col-lg-4"><input type="text" name="enq_add2" id="enq_add2" class="form-control" maxlength="100" value="<?php echo h($get->enq_add2); ?>"></div>
							</div>
							<div class="form-group row">
								<label class="col-lg-2 col-form-label">District</label>
								<div class="col-lg-2"><select name="district_id" id="district_id" class="form-control select-search"><option value="0">-- Select District --</option></select></div>
								<label class="col-lg-1 col-form-label">City</label>
								<div class="col-lg-3"><select name="city_id" id="city_id" class="form-control select-search"><option value="0">-- Select City --</option></select></div>
								<label class="col-lg-2 col-form-label">Pin Code</label>
								<div class="col-lg-2"><input type="text" name="enq_pincode" id="enq_pincode" class="form-control" maxlength="10" value="<?php echo h($get->enq_pincode); ?>"></div>
							</div>

							<legend class="font-weight-semibold"><i class="icon-wrench mr-2"></i>Service Details</legend>
							<div class="form-group row">
								<label class="col-lg-2 col-form-label">Authorized Service Center</label>
								<div class="col-lg-2">
									<select name="principal_id" id="principal_id" class="form-control select-search">
										<option value="0">-- Select --</option>
										<?php echo $dbconn->fnFillComboFromTable_Where("principal_id", "principal_name", "mst_principal", "principal_name", " WHERE principal_status = 1"); ?>
									</select>
									<script>$('#principal_id').val('<?php echo (int)$get->principal_id; ?>');</script>
								</div>
								<label class="col-lg-1 col-form-label">Service Charge <span class="text-danger">*</span></label>
								<div class="col-lg-2">
									<select name="service_charge_type" id="service_charge_type" class="form-control">
										<option value="0">-- Select --</option>
										<?php foreach (fnScChargeTypes() as $k => $v) {
											echo '<option value="' . $k . '" ' . ($get->service_charge_type == $k ? 'selected' : '') . '>' . $v . '</option>';
										} ?>
									</select>
								</div>
								<label class="col-lg-1 col-form-label">Chargeable Amount</label>
								<div class="col-lg-1"><input type="text" name="received_amount" class="form-control" maxlength="12" value="<?php echo h($get->received_amount > 0 ? $get->received_amount : ''); ?>"></div>
								<div class="col-lg-3" id="invoice_box">
									<select name="invoice_id" id="invoice_id" class="form-control select-search"><option value="0">-- Select Invoice --</option></select>
									<span class="form-text text-muted">Invoice (Under Warranty)</span>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-lg-2 col-form-label">Nature of Complaint <span class="text-danger">*</span></label>
								<div class="col-lg-10"><textarea name="nature_of_complaint" id="nature_of_complaint" class="form-control" rows="2"><?php echo h($get->nature_of_complaint); ?></textarea></div>
							</div>

							<legend class="font-weight-semibold"><i class="icon-list mr-2"></i>Complaint Details</legend>
							<?php if (!$locked) { ?>
								<div class="form-group row">
									<div class="col-lg-10"><input type="text" id="complaint_desc" class="form-control" maxlength="500" placeholder="Complaint description"></div>
									<div class="col-lg-2"><button type="button" id="add_complaint" class="btn btn-info btn-block">Add</button></div>
								</div>
							<?php } ?>
							<table class="table table-xs table-bordered">
								<thead>
									<tr class="bg-teal">
										<th width="5%">#</th>
										<th>Complaint</th>
										<th width="12%">Status</th>
									</tr>
								</thead>
								<tbody id="sc_lines">
									<?php foreach ($complaints as $c) { ?>
										<tr data-saved="1">
											<td class="line_sno"></td>
											<td><?php echo h($c->complaint_desc); ?><input type="hidden" name="complaints[d<?php echo $c->complaint_id; ?>][keep]" value="1"></td>
											<td align="center">
												<?php if ($c->complaint_status == 1) { ?>
													<span class="badge badge-success">Completed</span>
												<?php } else { ?>
													<span class="badge badge-warning">Open</span>
													<?php if (!$locked) { ?><a href="javascript:;" onclick="remove_complaint(this);" title="Remove" class="ml-2"><i class="icon-cross2 text-danger"></i></a><?php } ?>
												<?php } ?>
											</td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
						<div class="card-footer text-center">
							<?php if (!$locked) { ?>
								<input class="btn btn-info" type="submit" name="<?php echo $get->sc_id ? 'UPDATE' : 'SAVE'; ?>" value="<?php echo $get->sc_id ? 'UPDATE' : 'SAVE'; ?>">
							<?php } ?>
							<input class="btn btn-light" type="button" value="Cancel" onclick="window.location.href='service_call_list.php'">
						</div>
					</div>
				</form>
			</div>
			<?php include("inc/common/footer.php") ?>
		</div>
	</div>
</body>

</html>
