<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$conn = new dbconnect();
$dbconn = new dbhandler();



$emp_id = $_POST['id'];
$date = $_POST['date'];
 $attn_id = $_POST['attn_id'];
 $month = $_POST['month'];
 $year = $_POST['year'];

$emp_code =  $dbconn->GetSingleReconrd("mst_employee","emp_code","rec_del_status = '1' AND emp_id",$emp_id);

$emp_photo =  $dbconn->GetSingleReconrd("mst_employee","emp_photo","rec_del_status = '1' AND emp_id",$emp_id);

$prefix =  $dbconn->GetSingleReconrd("mst_employee","prefix","rec_del_status = '1' AND emp_id",$emp_id);

$emp_name =  $dbconn->GetSingleReconrd("mst_employee","emp_name","rec_del_status = '1' AND emp_id",$emp_id);

$check_in =  $dbconn->GetSingleReconrd("tbl_attendance","check_in","status = '1' AND attn_id = '".$attn_id."' AND emp_id",$emp_id);

$check_out = $dbconn->GetSingleReconrd("tbl_attendance","check_out","status = '1' AND attn_id = '".$attn_id."' AND emp_id",$emp_id);

$body ='';

$header='<div class="row">
			<div class="col-lg-6" style="">
				<span style="font-size: 14px; font-weight: bold;">Attendance Update</span>
			</div>
        </div>';

$body .= '<form name="thisForm" class="form-horizontal" method="POST" action="checkattendance.php" onSubmit="returnfnValidate();" enctype="multipart/form-data">
	<fieldset class=" pt-1">
		<div class="card">
			<div class = "row  ml-0 mr-0 pt-1 pb-1 pl-2 pr-2" style="background-color:#E0E0E0">
				<div class="col-lg-6">
					<label class="col-form-label" ><h6><b>Employee Name : </b>'.$prefix.''.''.$emp_name .'</h6></label>
				</div>
				<div class="col-lg-6" style="text-align: right;">
					<label class="col-form-label" ><h6><b>Employee Code : <span style="color: blue ;font-weight:bold; ">'.$emp_code.'</span></h6></label>
				</div>
			</div>
			<div class="card-body" id="">
			
				<div class="form-group">
					
					<div class="row">
						<div class="col-lg-4 align-center" style="width:98%; border:0px solid #d5d5d5;">';
							if($emp_photo !=''){
								$body .= '<img src="project_img/emp_photo/'.$emp_photo.'" width="100%" hight="100%">';
							}else{
								$body .= '<img src="project_img/emp_photo/usravatar.png" width="100%" hight="100%">';
							}
$body .='				</div>
						<div class="col-lg-7" style=" text-align: left;">
							<div class="row p-2">
								<div class="col-lg-5 " >
									<label class="col-form-label">Log Date </label>
								</div>
								<div class="col-lg-7 " >
									<label class="col-form-label">'.date('d-m-Y',strtotime($date)).'</label>
								</div>
							</div>
							<div class="row p-2">
								<div class="col-lg-5 " >
									<label class="col-form-label">Check In <span class="text-mandatory"> *</span></label>
								</div>
								<div class="col-lg-7 " >
									<input type="time" class="form-control" name="check_in_time" id="check_in_time" value='.$check_in.' />
								</div>
							</div>
							<div class="row p-2">
								<div class="col-lg-5 " >
									<label class="col-form-label">Check Out <span class="text-mandatory"> *</span></label>
								</div>
								<div class="col-lg-7 " >
									<input type="time" class="form-control" name="check_out_time" id="check_out_time" value='.$check_out. ' />
								</div>
							</div>
							<div class="row p-2">
								<div class="col-lg-5 " >
									<label class="">Is Over Time  </label>
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
				<div class="row p-2">
					<div class="col-lg-12 " style = "text-align:center;" >
						<INPUT class="btn btn-info mr-2" type="submit" name="UPDATE" id="UPDATE" value="Update">
						<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
						<input type="hidden" name="attn_id" id="attn_id" value=' . $attn_id . '>
						<input type="hidden" name="month_id" id="month_id" value=' . $month . '>
						<input type="hidden" name="atten_year" id="atten_year" value=' . $year . '>
						<input type="hidden" name="emp_id" id="emp_id" value=' . $emp_id . '>
					</div>
				</div>
			</div>
		</div>
	</fieldset>
			
</form>';

		echo $header."~".$body;

?>
<script language="javascript" type="text/javascript">
$('#UPDATE').click(function() {
        // var attn_date = $('#attn_date').val();
		var check_in_time = $('#check_in_time').val();
		var check_out_time = $('#check_out_time').val();
		// alert(attn_date);
        // if (attn_date == '') {
        //     alert("Please Select the Date...!");
		// return false;
        // }
		if (check_in_time == '') {
            alert("Please Select the Check In...!");
		return false;
        }
		if (check_out_time == '') {
            alert("Please Select the Check Out...!");
		return false;
        }

        // document.thisForm.submit();
    });


 
</script>


