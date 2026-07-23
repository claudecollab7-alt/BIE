<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");



$conn = new dbconnect();
$dbconn= new dbhandler();

$user_addnew = $dbconn->GetSingleReconrd("tbl_user_actions","addnew","sm_id = ".$sm_id." AND inst_id",$_SESSION['_user_id']);
$user_alt = $dbconn->GetSingleReconrd("tbl_user_actions","alt","sm_id = ".$sm_id." AND inst_id",$_SESSION['_user_id']);
$user_del = $dbconn->GetSingleReconrd("tbl_user_actions","del","sm_id = ".$sm_id." AND inst_id",$_SESSION['_user_id']);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(isset($_POST['UPDATE'])){
	
		$sql =  "DELETE FROM tbl_user_actions WHERE inst_id = ".$_REQUEST["inst_id"];
		$conn->query($sql);
		
		$sql =  "INSERT INTO tbl_user_actions (inst_id,sm_id) SELECT tbl_user_rights.inst_id,tbl_user_rights.sm_id FROM tbl_user_rights WHERE inst_id = ".$_REQUEST["inst_id"];
		$conn->query($sql);
		
		
		$sql =  "UPDATE tbl_user_actions SET addnew=0,alt=0,del=0 WHERE inst_id = ".$_REQUEST["inst_id"];
		$conn->query($sql);
		
		for($z=1;$z<=6;$z++){
			
			if($_REQUEST['ChkAdd'.$z]<>""){
				for($y=0;$y<count($_REQUEST['ChkAdd'.$z]);$y++){
					if($_REQUEST['ChkAdd'.$z][$y] != 0){
						$_REQUEST['sm_id'] = $_REQUEST['ChkAdd'.$z][$y];
						$sql =  "UPDATE tbl_user_actions SET addnew = 1 WHERE inst_id = ".$_REQUEST["inst_id"]." AND sm_id=".$_REQUEST['sm_id'];
						$conn->query($sql);
					}
				}
			}
			
			if($_REQUEST['ChkAlt'.$z]<>""){
				for($y=0;$y<count($_REQUEST['ChkAlt'.$z]);$y++){
					if($_REQUEST['ChkAlt'.$z][$y] != 0){
						$_REQUEST['sm_id'] = $_REQUEST['ChkAlt'.$z][$y];
						$sql =  "UPDATE tbl_user_actions SET alt = 1 WHERE inst_id = ".$_REQUEST["inst_id"]." AND sm_id=".$_REQUEST['sm_id'];
						$conn->query($sql);
					}
				}
			}
			
			if($_REQUEST['ChkDel'.$z]<>""){
				for($y=0;$y<count($_REQUEST['ChkDel'.$z]);$y++){
					if($_REQUEST['ChkDel'.$z][$y] != 0){
						$_REQUEST['sm_id'] = $_REQUEST['ChkDel'.$z][$y];
						$sql =  "UPDATE tbl_user_actions SET del = 1 WHERE inst_id = ".$_REQUEST["inst_id"]." AND sm_id=".$_REQUEST['sm_id'];
						$conn->query($sql);
					}
				}
			}
		
		}
		
		$_SESSION['_msg'] = "User page actions has been successfully updated..!";
		header("location:mst_users.php");

	die();

}
?>
<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo PAGE_TITLE; ?> - Employees Page Actions</title>

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
							<span class="breadcrumb-item active">EMPLOYEE RIGHTS</span>
						</div>
						<a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
					</div>
					
					<div class="header-elements d-none">
						<div class="breadcrumb justify-content-center">
							<a class="breadcrumb-elements-item" href="mst_users.php" title="Page Actions"><i class="fas fa-angle-left mr-2"></i>Back</a>
						</div>
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
						<!--div class="card-header header-elements-inline"-->
						<!--div class="card-header bg-light border-grey-300 header-elements-inline"-->
						<div class="card-header bg-pgheader text-white header-elements-inline">
						<!--div class="card-header bg-secondary text-white header-elements-inline"-->
							<h5 class="card-title">EMPLOYEE ACTIONS</h5>							
						</div>

						<div class="card-body">
							<form name='thisForm' class="form-horizontal" method='POST' action=""   onSubmit="return fnValidate();" 	
									enctype="multipart/form-data"> 
									
								<div class="row">
									<div class="col-md-12">
										<fieldset>
											<legend class="font-weight-semibold"><i class="far fa-play-circle mr-2"></i>Employee's Actions</legend>

											<div class="form-group row">
												
												<input type="hidden" name="inst_id" value="<?php echo $_REQUEST['inst_id']; ?>">
                    
					<table cellpadding="0" cellspacing="0" border="0" width="100%">
                            
                            <tr><td>&nbsp;</td></tr>
							<tr><td align="center" valign="top" bgcolor="#E5E5E5">
                            
                            <?php 

								  
								 $sql_mm = "SELECT DISTINCT a.* FROM mst_main_menu a, mst_sub_menu b WHERE a.mm_id = b.mm_id AND a.mm_show = 1 AND sm_id IN(SELECT sm_id FROM tbl_user_rights WHERE inst_id = ".$_SESSION['_user_id']." ) ORDER BY mm_id";

								 // $sql_mm = "SELECT DISTINCT * FROM mst_main_menu WHERE mm_id IN (SELECT mm_id FROM tbl_user_rights WHERE inst_id = ".$_REQUEST['inst_id'].") AND mm_for = 'CBS' AND mm_default = 0 ORDER BY mm_id";
								  $result_mm = $conn->query($sql_mm);

								  if ($result_mm->rowCount() > 0){
									
									echo "<table cellpadding=7 cellspacing=1 width='100%' align='left'>";
									
									while ($rs_mm = $result_mm->fetch()){
											
											echo "<tr align=left bgcolor=#e0f2ff class='menu'><td width=20%>".$rs_mm->mm_name." Pages </td>
																							  <td width=20%><input type='checkbox' id='mmAdd".$rs_mm->mm_id."' name='mmAdd".$rs_mm->mm_id."' value='1' onClick=\"check_sm(this.value,'ChkAdd".$rs_mm->mm_id."[]','mmAdd".$rs_mm->mm_id."')\"><label for='mmAdd".$rs_mm->mm_id."'>&nbsp; Select Add</label></td> 
																							  <td width=20%><input type='checkbox' id='mmAlt".$rs_mm->mm_id."' name='mmAlt".$rs_mm->mm_id."' value='1' onClick=\"check_sm(this.value,'ChkAlt".$rs_mm->mm_id."[]','mmAlt".$rs_mm->mm_id."')\"><label for='mmAlt".$rs_mm->mm_id."'>&nbsp; Select Alter</label></td> 
																							  <td width=20%><input type='checkbox' id='mmDel".$rs_mm->mm_id."' name='mmDel".$rs_mm->mm_id."' value='1' onClick=\"check_sm(this.value,'ChkDel".$rs_mm->mm_id."[]','mmDel".$rs_mm->mm_id."')\"><label for='mmDel".$rs_mm->mm_id."'>&nbsp; Select Delete</label></td>
																							  <!--<td width=20%><input type='checkbox' id='mmApprove".$rs_mm->mm_id."' name='mmApprove".$rs_mm->mm_id."' value='1' onClick=\"check_sm(this.value,'ChkApprove".$rs_mm->mm_id."[]','mmApprove".$rs_mm->mm_id."')\"><label for='mmApprove".$rs_mm->mm_id."'>&nbsp; Select Approve</label></td>-->
											</tr>";
											
											$sql_sm = "SELECT DISTINCT * FROM mst_sub_menu WHERE mm_id = ".$rs_mm->mm_id." ORDER BY sm_index";
											$result_sm = $conn->query($sql_sm);
											if ($result_sm->rowCount() > 0){
											
												echo "<tr align=left class='menu'><td bgcolor=#F5F5F5 colspan=5>";
													
													$iCnt=0;
													while ($rs_sm = $result_sm->fetch()){
														
														echo "<table cellpadding=7 cellspacing=1 width='100%' border='0'><tr bgcolor='#FFFFFF'>";
														
														$checked = $dbconn->GetSingleReconrd('tbl_user_rights','inst_id','sm_id = '.$rs_sm->sm_id.' AND inst_id',$_REQUEST['inst_id']);
														if($checked != ""){
															echo "<td width=20%>".$rs_sm->sm_name."</td>";
															
															$ChkAdd = $dbconn->GetSingleReconrd('tbl_user_actions','inst_id','addnew = 1 AND sm_id = '.$rs_sm->sm_id.' AND inst_id',$_REQUEST['inst_id']);
															if($ChkAdd != ""){
																echo "<td width=20%><input type='checkbox' id='ChkAdd".$rs_sm->sm_id."' name='ChkAdd".$rs_mm->mm_id."[]' value='".$rs_sm->sm_id."' checked><label for='ChkAdd".$rs_sm->sm_id."'>&nbsp; Add</label></td>";
															}else{
																echo "<td width=20%><input type='checkbox' id='ChkAdd".$rs_sm->sm_id."' name='ChkAdd".$rs_mm->mm_id."[]' value='".$rs_sm->sm_id."'><label for='ChkAdd".$rs_sm->sm_id."'>&nbsp; Add</label></td>";
															}
															
															$ChkAlt = $dbconn->GetSingleReconrd('tbl_user_actions','inst_id','alt = 1 AND sm_id = '.$rs_sm->sm_id.' AND inst_id',$_REQUEST['inst_id']);
															if($ChkAlt != ""){
																echo "<td width=20%><input type='checkbox' id='ChkAlt".$rs_sm->sm_id."' name='ChkAlt".$rs_mm->mm_id."[]' value='".$rs_sm->sm_id."' checked><label for='ChkAlt".$rs_sm->sm_id."'>&nbsp; Alter</label></td>";
															}else{
																echo "<td width=20%><input type='checkbox' id='ChkAlt".$rs_sm->sm_id."' name='ChkAlt".$rs_mm->mm_id."[]' value='".$rs_sm->sm_id."'><label for='ChkAlt".$rs_sm->sm_id."'>&nbsp; Alter</label></td>";
															}
															
															$ChkDel = $dbconn->GetSingleReconrd('tbl_user_actions','inst_id','del = 1 AND sm_id = '.$rs_sm->sm_id.' AND inst_id',$_REQUEST['inst_id']);
															if($ChkDel != ""){
																echo "<td width=20%><input type='checkbox' id='ChkDel".$rs_sm->sm_id."' name='ChkDel".$rs_mm->mm_id."[]' value='".$rs_sm->sm_id."' checked><label for='ChkDel".$rs_sm->sm_id."'>&nbsp; Delete</label></td>";
															}else{
																echo "<td width=20%><input type='checkbox' id='ChkDel".$rs_sm->sm_id."' name='ChkDel".$rs_mm->mm_id."[]' value='".$rs_sm->sm_id."'><label for='ChkDel".$rs_sm->sm_id."'>&nbsp; Delete</label></td>";
															}
															
															/*$ChkApprove = GetSingleReconrd('tbl_user_actions','inst_id','approve = 1 AND sm_id = '.$rs_sm->sm_id.' AND inst_id',$_REQUEST['inst_id']);
															if($ChkApprove != ""){
																echo "<td width=20%><input type='checkbox' id='ChkApprove".$rs_sm->sm_id."' name='ChkApprove".$rs_mm->mm_id."[]' value='".$rs_sm->sm_id."' checked><label for='ChkApprove".$rs_sm->sm_id."'>&nbsp; Approve</label></td>";
															}else{
																echo "<td width=20%><input type='checkbox' id='ChkApprove".$rs_sm->sm_id."' name='ChkApprove".$rs_mm->mm_id."[]' value='".$rs_sm->sm_id."'><label for='ChkApprove".$rs_sm->sm_id."'>&nbsp; Approve</label></td>";
															}*/
															
														}
														echo "</tr>";
				
														$iCnt++;
														
													}
													
													echo "</tr></table>";
												
												echo "</td></tr>";
											}
									
									
									}
									echo "</table>";
								  }
							?>
                            
                            </td></tr>						
					        
                      <tr><td>&nbsp;</td></tr>
			          </table>
											</div>	
											
											
										</fieldset>
									</div>
								</div>
								

								<hr class="m-t-0 m-b-10">
								
								<div class="text-center"><?php if($_REQUEST["inst_id"]!='') { ?>
                              <INPUT class="btn btn-custom" type="submit" name="UPDATE" value="UPDATE">
                              <INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
							  <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['inst_id'];?>">
							  <?php }?>
								</div>
							</form>
						</div>
					</div>
					
					
					
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
</body>
<script language="javascript" type="text/javascript">
function check_sm(value,obj,chkname){
	var field = document.thisForm.elements[obj];
	for (i = 0; i < field.length; i++){
		field[i].checked = document.getElementById(chkname).checked;
	}
}
</script>
</html>
