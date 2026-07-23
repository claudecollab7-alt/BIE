<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");



$conn = new dbconnect();
$dbconn= new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);


if(isset($_POST['UPDATE']))
{		
	try 
	{		
		// echo "<pre>";
		// print_r($_POST);
		// echo "<pre>";

		// exit();
		$sql="DELETE FROM tbl_user_rights WHERE usr_id = ".$_REQUEST["usr_id"];
		$conn->query($sql);
		
		
		$sql_mm = "SELECT DISTINCT * FROM mst_main_menu WHERE  mm_show = 1 ORDER BY mm_id";
		$res_mm = $conn->query($sql_mm);
		
		if ($res_mm->rowCount() > 0)
		{	
			$stmt = null;
			$stmt = $conn->prepare("INSERT INTO tbl_user_rights (usr_id, mm_id, sm_id) VALUES (:usr_id, :mm_id, :sm_id)");	
			
			while ($rs_mm = $res_mm->fetch())
			{

				$mm_id = $rs_mm->mm_id;		
				// print_r($_REQUEST['Chk'.$mm_id]);
				if(isset($_REQUEST['Chk'.$mm_id]) && $_REQUEST['Chk'.$mm_id] > 0){
					for($y=0;$y<count($_REQUEST['Chk'.$mm_id]);$y++)
					{
						$sm_id= $_REQUEST['Chk'.$mm_id][$y];					
						
						if($_REQUEST['Chk'.$mm_id][$y] != 0)
						{						
							$sm_id= $_REQUEST['Chk'.$mm_id][$y];
							
							$data = array(		
								':usr_id' => $_REQUEST['usr_id'],
								':mm_id' => $mm_id,
								':sm_id' => $sm_id
							);
							$stmt->execute($data);
						}						
					
					}
					// print_r($data);
					// // echo $sql.'<br>';
					// die();
				}
				
			}
		}	
		//exit;
		
		$_SESSION['_msg'] = "User rights has been successfully updated..!";
	}catch(Exception $e){
		echo $_SESSION['_msg_err'] = $e;
	}
	header("location:mst_user.php");
	//die();

}



if ($_REQUEST['id'] != ""){
	$converter = new Encryption;
	$url_data = $converter->decode($_REQUEST['id']);
	$url_data = explode("~",$url_data);
	
    if($url_data[1] == $_SESSION['_user_id']){
		$_REQUEST['usr_id'] = $url_data[0];
	}else{
		$_SESSION['_msg_err'] = "You don\'t have permission..!";	
		header("location:mst_user.php");			
		//die();
	}
	
}
?>
<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo PAGE_TITLE; ?> - User Rights</title>

	<?php include_once("inc/common/css-js.php"); ?>		
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
							<a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Home</a>
							<span class="breadcrumb-item active">User Rights</span>
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
					
					<form name='thisForm' class="form-horizontal" method='POST' action=""   onSubmit="return fnValidate();"> 
					<input type="hidden" name="usr_id" value="<?php echo $_REQUEST['usr_id']; ?>">
						<div class="card">						
							<div class="card-header bg-pgheader text-white header-elements-inline">						
								<h6 class="card-title">User Rights</h6>	
								<div class="header-elements">									
									<div class="list-icons">				                		
										<a class="list-icons-item" href="mst_user.php" title="Users List"><i class="icon-arrow-left52 mr-2"></i></a>
										<a class="list-icons-item" data-action="fullscreen"></a>
				                	</div>									
			                	</div>								
							</div>														
							
							<div class="card-body">
								<div class="row">
								<div class="col-md-12">
									<fieldset>
										<legend class="font-weight-semibold"><i class="far fa-check-circle mr-2"></i>All Menu Rights</legend>
										<div class="form-group row">
											<table cellpadding="0" cellspacing="0" border="0" width="100%">												
												<tr><td align="center" valign="top" bgcolor="#E5E5E5">
                           
												<?php 
												  $sql_mm = "SELECT DISTINCT * FROM mst_main_menu WHERE mm_show = 1 ORDER BY mm_id";
												  $result_mm = $conn->query($sql_mm);
												  //print_r($result_mm);

												  if ($result_mm->rowCount() > 0)
												  {													
													echo "<table cellpadding=5 border='0' cellspacing=1 width='100%' align='left'>";
													
													while ($rs_mm = $result_mm->fetch())
													{		

														$mnu_status=''; $cnt_sub_items = $cnt_usr_sub_items = 0;
														$cnt_sub_items = $dbconn->GetSingleReconrd('mst_sub_menu',"count(sm_id)",'mm_id = '.$rs_mm->mm_id.' AND sm_show',1);
														
														$cnt_usr_sub_items = $dbconn->GetSingleReconrd('tbl_user_rights',"count(distinct sm_id)",'mm_id='.$rs_mm->mm_id .' AND usr_id',$_REQUEST['usr_id']);
														
														if($cnt_sub_items >0 &&( $cnt_usr_sub_items  >=  $cnt_sub_items))
															$mnu_status='checked';
														//". $cnt_sub_items.	'-'. $cnt_usr_sub_items."
														
														echo "<tr align=left bgcolor=#e0f2ff><td colspan='2'>
																<div class='form-check form-check-inline'>
																<label class='form-check-label' for='mm".$rs_mm->mm_id."'>
																<input class='form-check-input-styled ss".$rs_mm->mm_id."' type='checkbox' 
																onClick=\"check_sm(this.value,'Chk".$rs_mm->mm_id."[]','mm".$rs_mm->mm_id."')\"
																name='Chk".$rs_mm->mm_id."[]' id='mm".$rs_mm->mm_id."' value='".$rs_mm->mm_id."' ".$mnu_status.">".$rs_mm->mm_name."</label>
																</div></td></tr>";
																														
														$sql_sm = "SELECT DISTINCT * FROM mst_sub_menu WHERE mm_id = ".$rs_mm->mm_id." AND sm_show = 1 ORDER BY sm_index";
														$result_sm = $conn->query($sql_sm);
														if ($result_sm->rowCount() > 0)
														{											
															echo "<tr align=left bgcolor=#FFFFFF>
																  <td style='padding-left:50px'>";
															while ($rs_sm = $result_sm->fetch())
															{	
																
																$checked = $dbconn->GetSingleReconrd('tbl_user_rights','usr_id','sm_id = '.$rs_sm->sm_id.' AND usr_id',$_REQUEST['usr_id']);
																if($checked == ""){
																	echo "<div class='form-check form-check-inline col-sm-3'>
																			<label class='form-check-label' for='sm".$rs_sm->sm_id."'>
																			<input class='form-check-input-styled checkbox2' type='checkbox' data-sm='ss".$rs_mm->mm_id."'
																			name='Chk".$rs_mm->mm_id."[]' id='sm".$rs_sm->sm_id."'
																			value='".$rs_sm->sm_id."'>".$rs_sm->sm_name."</label></div>";
																}else{
																	echo "<div class='form-check form-check-inline col-sm-3'>
																		<label class='form-check-label' for='sm".$rs_sm->sm_id."'>
																		<input class='form-check-input-styled checkbox2'  type='checkbox' data-sm='ss".$rs_mm->mm_id."'
																		name='Chk".$rs_mm->mm_id."[]' id='sm".$rs_sm->sm_id."' 
																		value='".$rs_sm->sm_id."' checked>".$rs_sm->sm_name."</label></div>";
																}																
															
															}														
															echo "</td></tr>";
														}	
													}
													echo "</table>";
												}
											?>                          
											</td></tr>	
											</table>
										</div>	
									</fieldset>
								</div>
								</div>
							</div>	
							<div class="card-footer text-center"> 
								<?php if($_REQUEST["usr_id"]!='') { ?>
									<INPUT class="btn btn-custom" type="submit" name="UPDATE" value="Update">
									<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
									<input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['usr_id'];?>">
							  <?php }?>
							</div>
						</div>		
					</form>
						
					<!-- End of This Form UI  --->
				</div>
				</div>
				<!-- /dashboard content -->
			</div>
			<!-- /content area -->

			<!-- Footer -->
				<?php include("inc/common/footer.php") ?>
			<!-- /footer -->
		</div>
		<!-- /main content -->
	</div>
	<!-- /page content -->
	<script language="javascript" type="text/javascript">
		function check_sm(value,obj,chkname)
		{
			var field = document.thisForm.elements[obj];		
			
			for (i = 0; i < field.length; i++)
			{				
				var smid = (field[i].id);				
				field[i].checked = document.getElementById(chkname).checked;
				if(field[i].checked == true){
					$('#'+smid).closest('span').addClass('checked');
				}
				else if(field[i].checked == false){
					$('#'+smid).closest('span').removeClass('checked');
				}				 
			}
		}
		// $('.checkbox2').click(function()
		// {	
        //     //alert($(this).attr("data-sm") );
		// 	var sm_exe = $(this).attr("data-sm");
		// 	// $("."+sm_exe).attr("checked",true);
		// 	$("."+sm_exe).attr('checked');
		// });
</script>
</body>


</html>
