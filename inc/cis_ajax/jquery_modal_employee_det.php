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

$emp_code =  $dbconn->GetSingleReconrd("mst_employee","emp_code","rec_del_status = '1' AND emp_id",$emp_id);
$emp_photo =  $dbconn->GetSingleReconrd("mst_employee","emp_photo","rec_del_status = '1' AND emp_id",$emp_id);
$prefix =  $dbconn->GetSingleReconrd("mst_employee","prefix","rec_del_status = '1' AND emp_id",$emp_id);
$emp_name =  $dbconn->GetSingleReconrd("mst_employee","emp_name","rec_del_status = '1' AND emp_id",$emp_id);
$department_id =  $dbconn->GetSingleReconrd("mst_employee","department_id","rec_del_status = '1' AND emp_id",$emp_id);
$department_name = $dbconn->GetSingleReconrd("mst_department","department_name","rec_del_status = '1' AND department_id",$department_id);
$emp_mobile =  $dbconn->GetSingleReconrd("mst_employee","emp_mobile","rec_del_status = '1' AND emp_id",$emp_id);
$emp_email =  $dbconn->GetSingleReconrd("mst_employee","emp_email","rec_del_status = '1' AND emp_id",$emp_id);

$emp_cr_add1 =  $dbconn->GetSingleReconrd("mst_employee","emp_cr_add1","rec_del_status = '1' AND emp_id",$emp_id);
$emp_cr_add2 =  $dbconn->GetSingleReconrd("mst_employee","emp_cr_add2","rec_del_status = '1' AND emp_id",$emp_id);

$cr_city_id =  $dbconn->GetSingleReconrd("mst_employee","cr_city_id","rec_del_status = '1' AND emp_id",$emp_id);
$cr_city_name =  $dbconn->GetSingleReconrd("mst_city","city_name","city_status = '1' AND city_id",$cr_city_id);

$cr_district_id =  $dbconn->GetSingleReconrd("mst_employee","cr_district_id","rec_del_status = '1' AND emp_id",$emp_id);
$cr_district_name =  $dbconn->GetSingleReconrd("mst_district","district_name","district_status = '1' AND district_id",$cr_district_id);

$cr_state_id =  $dbconn->GetSingleReconrd("mst_employee","cr_state_id","rec_del_status = '1' AND emp_id",$emp_id);
$cr_state_name =  $dbconn->GetSingleReconrd("mst_state","state_name","state_status = '1' AND state_id",$cr_state_id);

$cr_pincode =  $dbconn->GetSingleReconrd("mst_employee","cr_pincode","rec_del_status = '1' AND emp_id",$emp_id);

$login_access =  $dbconn->GetSingleReconrd("mst_employee","login_access","rec_del_status = '1' AND emp_id",$emp_id);
$login_name =  $dbconn->GetSingleReconrd("mst_employee","emp_login_name","rec_del_status = '1' AND emp_id",$emp_id);
$login_password =  $dbconn->GetSingleReconrd("mst_employee","emp_login_password","rec_del_status = '1' AND emp_id",$emp_id);
$body ='';

$header='<div class="row">
			<div class="col-lg-6" style="">
				<span style="font-size: 14px; font-weight: bold;">Employee Code - '.$emp_code.'</span>
			</div>
        </div>';

$body .= '<div class="form-group pt-2">
			<div class="row">
				<div class="col-lg-4" style="width:98%; border:1px solid #d5d5d5;">';
					if($emp_photo !=''){
						$body .= '<img src="project_img/emp_photo/'.$emp_photo.'" width="100%" hight="100%">';
					}else{
						$body .= '<img src="project_img/emp_photo/usravatar.png" width="100%" hight="100%">';
					 }
$body .=		'</div>
				<div class="col-lg-7 p-2" style=" text-align: left;">
					<b style=" font-size: 15px;">'.$prefix.' '.$emp_name.' ,<span style="color: blue;"> 
					'.$emp_code.'</span> </b><br>
					<b>Department. :</b>'.$department_name.'<br>
					<b>Contact No. :</b> '.$emp_mobile.'<br>
					<b>Email :</b> '.$emp_email.'<br><br>
					<b>Contact Address :</b><br>
					 '.$emp_cr_add1.'';if($emp_cr_add2){$body .= ' , '.$emp_cr_add2.'';}else{$body .= '';}
					 $body .=' ,<br>
					 '.$cr_city_name.' , '.$cr_district_name.' ,<br>
					 '.$cr_state_name .' - '.$cr_pincode.' . <br><br>';
					 if($login_access==1){
						$body .='<legend class="font-weight-semibold"></legend><b> Login Details <br>
						Login Name : </b>'.$login_name.' <br>
						<b>Login Password : </b>'.$login_password.'';
					 }
					 $body .=' </div>';
					 
					 $body .='</div>
		</div>';

		echo $header."~".$body;

?>


