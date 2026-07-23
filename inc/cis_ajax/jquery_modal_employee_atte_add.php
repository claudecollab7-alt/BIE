<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();



// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
 
$emp_id = $_POST['id'];
// $date = $_POST['date'];
//  $attn_id = $_POST['attn_id'];
$month = $_POST['month'];
$year = $_POST['year'];


$min = date('Y-m-d', strtotime($year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01'));

// $max = date('Y-m-t',strtotime($year.'-'.str_pad($month, 2, '0', STR_PAD_LEFT).'-01'));


$emp_code =  $dbconn->GetSingleReconrd("mst_employee", "emp_code", "rec_del_status = '1' AND emp_id", $emp_id);

$emp_photo =  $dbconn->GetSingleReconrd("mst_employee", "emp_photo", "rec_del_status = '1' AND emp_id", $emp_id);

$prefix =  $dbconn->GetSingleReconrd("mst_employee", "prefix", "rec_del_status = '1' AND emp_id", $emp_id);

$emp_name =  $dbconn->GetSingleReconrd("mst_employee", "emp_name", "rec_del_status = '1' AND emp_id", $emp_id);

$check_in =  $dbconn->GetSingleReconrd("tbl_attendance", "check_in", "status = '1' AND attn_id = '" . $attn_id . "' AND emp_id", $emp_id);

$check_out = $dbconn->GetSingleReconrd("tbl_attendance", "check_out", "status = '1' AND attn_id = '" . $attn_id . "' AND emp_id", $emp_id);

$body = '';

$header = '<div class="row">
			<div class="col-lg-6" style="">
				<span style="font-size: 14px; font-weight: bold;"> Add Attendance</span>
			</div>
        </div>';

$body .= '<form name="thisForm" class="form-horizontal" method="POST" action="checkattendance.php" onSubmit="fnValidate();" enctype="multipart/form-data">
<fieldset class="p-2">
	<div class="card">
		<div class = "row  ml-0 mr-0  pl-2 pr-2" style="background-color:#E0E0E0">
			<div class="col-lg-6">
				<label class="col-form-label" ><h6><b>Employee Name : </b>' . $prefix . '' . '' . $emp_name . '</h6></label>
			</div>
			<div class="col-lg-6" style="text-align: right;">
				<label class="col-form-label" ><h6><b>Employee Code : <span style="color: blue ;font-weight:bold; ">' . $emp_code . '</span></h6></label>
			</div>
		</div>
		<div class="card-body" id="">
			<div class="form-group">
				<div class="row">
					<div class="col-lg-4" style="width:98%;  border:0px solid #d5d5d5;">';
if ($emp_photo != '') {
	$body .= '<img src="project_img/emp_photo/' . $emp_photo . '" width="100%" hight="100%">';
} else {
	$body .= '<img src="project_img/emp_photo/usravatar.png" width="100%" hight="100%">';
}
$body .=			'</div>
					<div class="col-lg-7" style=" text-align: left;">
						<div class="row p-2">
							<div class="col-lg-5 " >
								<label class="col-form-label">Date <span class="text-mandatory"> *</span></label>
							</div>
							<div class="col-lg-7 " >
								<input type="date" class="form-control" name="attn_date" id="attn_date" min="' . $min . '" max="' . date('Y-m-d') . '"/>
							</div>
						</div>
						<div class="row p-2">
							<div class="col-lg-5 " >
								<label class="col-form-label">Check In <span class="text-mandatory"> *</span></label>
							</div>
							<div class="col-lg-7 " >
								<input type="time" class="form-control" name="check_in_time" id="check_in_time" value=' . $check_in . ' />
							</div>
						</div>
						<div class="row p-2">
							<div class="col-lg-5 " >
								<label class="col-form-label">Check In <span class="text-mandatory"> *</span></label>
							</div>
							<div class="col-lg-7 " >
								<input type="time" class="form-control" name="check_out_time" id="check_out_time" value=' . $check_out . ' />
							</div>
						</div>
						<div class="row p-2">
							<div class="col-lg-5 " >
								<label class="">Is Over Time  </label>
								<input type="hidden" class="form-control" name="check_date" id="check_date" value="" />
							</div>
							<div class="col-lg-7 " >
								<input type="checkbox" class="" name="is_ot" id="is_ot" />
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="card-footer">
			<div class="row ">
				<div class="col-lg-12  " style = "text-align:center;" >
					<INPUT class="btn btn-custom mr-2 mr-2" type="submit" name="ADD" id="ADD" value="Save">
					<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
					<input type="hidden" name="month_id" id="month_id" value=' . $month . '>
					<input type="hidden" name="atten_year" id="atten_year" value=' . $year . '>	
					<input type="hidden" name="emp_id" id="emp_id" value=' . $emp_id . '>
				</div>
			</div> 
		</div>
	</div>
</fieldset>
</form>';

echo $header . "~" . $body;

?>
<script language="javascript" type="text/javascript">
	// $('#check_out_time').hide();
	$('#ADD').click(function() {
		var attn_date = $('#attn_date').val();
		var check_in_time = $('#check_in_time').val();
		var check_out_time = $('#check_out_time').val();
		var check_date = $('#check_date').val();
		if (attn_date == '') {
			alert("Please Select the Date...!");
			$('#attn_date').focus();
			return false;
		}
		if (check_in_time == '') {
			alert("Please Select the Check In...!");
			$('#check_in_time').focus();
			return false;
		}
		if (check_out_time == '') {
			alert("Please Select the Check Out...!");
			$('#check_out_time').focus();
			return false;
		}
		if (check_date == 1 ) {
			alert("Entry Already Exist for this Date...!");
			$('#attn_date').val('');
			$('#attn_date').focus();
			return false;
		}
	});

	$('#attn_date').change(function() {
		// alert();
		var attn_date = $('#attn_date').val();
		var emp_id = $('#emp_id').val();
		var atten_year = $('#atten_year').val();
		var month_id = $('#month_id').val();
		$.ajax({
			type: "POST",
			url: "inc/cis_ajax/jquery_add_atte_check_date.php",			
			data: {
				attn_date: attn_date,
				emp_id: emp_id,
				atten_year: atten_year,
				month_id: month_id,
				mode:"check",
			},
			success: function(msg) {
				$('#check_date').val(msg);
				
            }
		});
	});
</script>