<?PHP

ob_start();

session_start();

require_once("inc/common/userclass.php");

isAdmin();

$conn = new dbconnect();
$dbconn= new dbhandler();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

?>
<table class="table table-bordered">
	<tbody>
		<?php 								
			$srch = $_REQUEST['atten_year']."-".$_REQUEST['month_id'];

			$epf_file = fopen("ECRKYCUploadable-".$srch.".txt", "w") or die("Unable to open file!");	

			$SQL = "SELECT * FROM tbl_emp_monthly_salary WHERE salary_for= '".$srch."' AND salary_type = 'WPF'  ORDER BY salary_for ASC";

			$result = $conn->query($SQL);
			
			//echo $SQL;
			if ($result->rowCount() > 0)
			{				
				$Sno = 1;
				while ($obj = $result->fetch())
				{
					$emp_det = $dbconn->GetSingleReconrd("mst_employee","CONCAT(emp_uan_no,'~',emp_name)","emp_id",$obj->emp_id);
					$emp = explode('~',$emp_det);
					
					$eps = ($obj->basic_da/100)*8.33;
					$edlf = $obj->epf_amount - $eps;
					$rounval = round($obj->work_days);
					$pointvalue = $rounval - $obj->work_days;
					$work_days = $obj->work_days;
					if($pointvalue > 0){
						$work_days = $obj->work_days - $pointvalue;
					}
					//echo $work_days;
					$ncp_days = $obj->total_working_days - $work_days;
					$epf_text =  $emp[0].'#~#'.$emp[1].'#~#'.round($obj->basic_da).'#~#'.round($obj->basic_da).'#~#'.round($obj->basic_da).'#~#'.round($obj->basic_da).'#~#'.round($obj->epf_amount).'#~#'.round($eps).'#~#'.round($edlf).'#~#'.$ncp_days.'#~#0';		
					
					 fwrite($epf_file, $epf_text);
					 fwrite($epf_file, PHP_EOL);									
					$Sno++;	
				}
				$obj=null;
			}		
		fclose($epf_file);		
		$_SESSION['_msg'] = "ECRKYCUploadable-".$srch." File Saved";
		header("location:attendance_report_epf.php?month_id=".$_REQUEST['month_id']."&atten_year=".$_REQUEST['atten_year']);
		die();
	
		?>
	 </tbody>
 </table>