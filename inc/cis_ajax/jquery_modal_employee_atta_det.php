<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");



$conn = new dbconnect();
$dbconn = new dbhandler();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$emp_id = $_POST['id'];
$atta_date = $_POST['atta_date'];

$emp_code =  $dbconn->GetSingleReconrd("mst_employee","emp_code","rec_del_status = '1' AND emp_id",$emp_id);
$emp_photo =  $dbconn->GetSingleReconrd("mst_employee","emp_photo","rec_del_status = '1' AND emp_id",$emp_id);
$prefix =  $dbconn->GetSingleReconrd("mst_employee","prefix","rec_del_status = '1' AND emp_id",$emp_id);
$emp_name =  $dbconn->GetSingleReconrd("mst_employee","emp_name","rec_del_status = '1' AND emp_id",$emp_id);
$department_id =  $dbconn->GetSingleReconrd("mst_employee","department_id","rec_del_status = '1' AND emp_id",$emp_id);
$department_name = $dbconn->GetSingleReconrd("mst_department","department_name","rec_del_status = '1' AND department_id",$department_id);
$emp_mobile =  $dbconn->GetSingleReconrd("mst_employee","emp_mobile","rec_del_status = '1' AND emp_id",$emp_id);
$emp_email =  $dbconn->GetSingleReconrd("mst_employee","emp_email","rec_del_status = '1' AND emp_id",$emp_id);

$emp_atta = "select * from tbl_attendance_import_new where emp_id =".$emp_id." and DATE(biometric_time) = '" . date('Y-m-d', strtotime($atta_date)) . "'";
$result = $conn->query($emp_atta);


$body ='';

$header='<div class="row">
			<div class="col-lg-6" style="">
				<span style="font-size: 14px; font-weight: bold;">Employee Attendance Log </span>
			</div>
        </div>';

$body .= '<div class="form-group p-2">
			<div class="row p-2">
				<div class="col-lg-4" style="width:98%; border:1px solid #d5d5d5;">';
					if($emp_photo !=''){
						$body .= '<img src="project_img/emp_photo/'.$emp_photo.'" class="img-fluid">';
					}else{
						$body .= '<img src="project_img/emp_photo/usravatar.png" class="img-fluid">';
					 }
$body .=		'</div>
				<div class="col-lg-7 p-2" style=" text-align: left;">
					<b style=" font-size: 15px;">'.$prefix.' '.$emp_name.',<span style="color: blue;"> 
					 '.$emp_code.'</span> </b><br>
					<b>Department :</b> '.$department_name.'<br>
					<b>Contact No. :</b> '.$emp_mobile.'<br>
					<b>Email :</b> '.$emp_email.'<br><br>
				</div>
					 
			</div><hr>
			<h6 style="text-align: center;"><b> Attendance Log Date : </b>'.date('d-m-Y',strtotime($atta_date)).'</h6>
			';

$body .='	
			<table class="table table-xs invoice_tbl">
				<thead style="text-align: center; font-weight:bold; ">
					<tr>
						<td>
							S.No.
						</td>
						<td>	
							Biometric Time
						</td>
						<td>
							Status
						</td>
					</tr>
				<thead>';
				
				$sno=1;
				while ($row = $result->fetch()) {

				$body .= '<tbody style="text-align: center;">
							<tr>
								<td>
									'.$sno.'
								
								</td>
								<td>
									'.date('d-m-Y | H:i:s',strtotime($row->biometric_time)).'
								</td>
								<td>
									'.$row->check_in_out.'
								</td>
							</tr>';
						$sno++;
				}	

				$body .='<tbody>
			</table>';

	 
			
			// $body .='"'.$emp_atta.'"';

		echo $header."~".$body;

?>


