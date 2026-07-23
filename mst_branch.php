<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");

isAdmin();

$conn = new dbconnect();
$dbconn= new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);


if (isset($_POST['SAVE']))
{		
	
	try
	{
		$is_exist = $dbconn->GetSingleReconrd("mst_branch","branch_id",
					"(branch_name = '".$_REQUEST['branch_name']."' OR branch_code = '".$_REQUEST['branch_code']."') AND branch_status ",1);
				
		if($is_exist != ""){
			$_SESSION['_msg_err'] = "Branch Name / Code  already exist..!";
			header("location:mst_branch.php");	
			die();
		}
	
	
		$stmt = null;				
		$stmt = $conn->prepare("INSERT INTO mst_branch (branch_name, branch_code,branch_state_code, company_address, created_by, created_dtm) VALUES 
											(:branch_name, :branch_code, :branch_state_code, :company_address, :created_by, :created_dtm)");		
		$data = array(				
			':branch_name' => ucwords($_REQUEST['branch_name']),
			':branch_code' => strtoupper($_REQUEST['branch_code']),
			':branch_state_code' => ($_REQUEST['branch_state_code']),
			':company_address' => $_REQUEST['company_address'],
			':created_by' => $_SESSION['_user_id'],
			':created_dtm' => date('Y-m-d H:i:s')
		);
		$stmt->execute($data);
		$_SESSION['_msg'] = "Branch Succesfully Saved..!";
	}
	catch (Exception $e)
	{		
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
		$_SESSION['_msg_err'] = $str;			
	}
	
	header("location:mst_branch.php");	
	die();	
}


if (isset($_POST['UPDATE']))
{
	
	$update_id = $_REQUEST['txtHid'];		
	
	try
	{
		$mst_exist = $dbconn->GetSingleReconrd("mst_branch","branch_id","branch_id <> ".$update_id." AND 
					 branch_name ='".$_REQUEST['branch_name']."' AND branch_status", 1);
	
		if($mst_exist != ""){
			$_SESSION['_msg_err'] = "Branch Already Exist..!";
			header("location:mst_branch.php");	
			die();
		}
	
		$stmt = null;				
		$stmt = $conn->prepare("UPDATE mst_branch SET 
							branch_name = :branch_name, branch_code = :branch_code, 
							branch_state_code = :branch_state_code, company_address = :company_address,
							modify_by = :modify_by, modify_dtm = :modify_dtm
				WHERE branch_id = :branch_id");		
		$data = array(				
			':branch_id' => $update_id,
			':branch_name' => ucwords($_REQUEST['branch_name']),
			':branch_code' => strtoupper($_REQUEST['branch_code']),
			':branch_state_code' => ($_REQUEST['branch_state_code']),
			':company_address' => $_REQUEST['company_address'],
			':modify_by' => $_SESSION['_user_id'],
			':modify_dtm' => date('Y-m-d H:i:s')			
		);
		
		$stmt->execute($data);
		
		$_SESSION['_msg'] = "Branch succesfully Updated..!";
	}
	catch (Exception $e)
	{		
		$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
		$_SESSION['_msg_err'] = $str;			
	}
	
	header("location:mst_branch.php");	
	die();		
}


if (isset($_REQUEST['id']) && $_REQUEST['id'] != "")
{
	$converter = new Encryption;
	$url_data = $converter->decode($_REQUEST['id']);
	$url_data = explode("~",$url_data);
    
	if($url_data[1] == $_SESSION['_user_id']){
		$_REQUEST['branch_id'] = $url_data[0];
	}else{
		$_SESSION['_msg_err'] = "You don\'t have permission..!";	
		header("location:mst_branch.php");			
		die();
	}	
}

$branch_id="";
$branch_name="";
$branch_code="";
$branch_state_code="";
$company_address="";

if (isset($_REQUEST['branch_id']) && $_REQUEST['branch_id'] != "")
{
	$result = $conn->query("SELECT * FROM mst_branch WHERE branch_status = '1' AND branch_id = ".$_REQUEST['branch_id']);	
	if ($result->rowCount()>0)
	{
		$obj = $result->fetch(PDO::FETCH_OBJ);	
		$branch_id=$obj->branch_id;
		$branch_name=$obj->branch_name;
		$branch_code=$obj->branch_code;	
		$branch_state_code = $obj->branch_state_code;
		$company_address = $obj->company_address;
	}
	
}



?>
<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo PAGE_TITLE; ?> - Branch</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />

	<?php include_once("inc/common/css-js.php"); ?>

	<!-- CKEditor 4 CDN -->
	<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
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
							<a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
							<a href="#" class="breadcrumb-item"> Settings</a>
							<span class="breadcrumb-item active">Branch</span>
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
				
					<!-- This Form UI Starts here --->
					
					
					<div class="col-md-6">
						<div class="card">
							<div class="card-header bg-pgheader text-white header-elements-inline">
								<h6 class="card-title">List of Branch</h6>
								<div class="header-elements">									
									<div class="list-icons">				                												
										<a class="list-icons-item" data-action="fullscreen"></a>
				                	</div>									
			                	</div>	
							</div>
							<div class="card-body pt-0">	
								<table class="datatable-col3 table table-xs table-hover table-bordered" id="BranchTable">
									<thead>
										<tr class="bg-table-header">	
											<th>Branch Name</th>
											<th>Branch Code</th>
											<th>Branch State Code</th>
											<th class="text-center">Actions</th>											
										</tr>							
									</thead>										

									<tbody>									
									<?php
											
										$sql = "SELECT * FROM mst_branch WHERE branch_status = 1";
										$searchRes1 = $conn->query($sql);	
										$iSno = 1 ;

										if ($searchRes1->rowCount() > 0)
										{	
											while($rs=$searchRes1->fetch())
											{										
												$converter = new Encryption;
												$token = $converter->encode($rs->branch_id.'~'.$_SESSION['_user_id']);
												
												echo '<tr>';	
													echo '<td>'.$rs->branch_name.' </td>';	
													echo '<td>'.$rs->branch_code.'</td>';								
													echo '<td>'.$rs->branch_state_code.'</td>';								
													echo '<td class="text-center">';													
													echo "<a href='mst_branch.php?id=".$token."' data-popup='tooltip' title='Edit'>
															<i class='icon-pencil5 bg-edit mr-2'></i></a>";
														
														if($_SESSION['_user_type'] == 'S' || $_SESSION['_user_type'] == 'A')
														{															
															echo '<a href="javascript:;" class="delete" rel="'.$rs->branch_id.'" data-popup="tooltip" title="Delete">
																<i class="icon-bin bg-delete mr-2"></i></a>';
															
														}else{
															echo "<a href='javascript:;' title='Delete'><i class='icon-bin bg-delete mr-2'></i></a>";
														}
													echo '</td>';
												echo '</tr>';
												$iSno++;
											}
										}
										else
										{
											echo '<tr><td colspan="4">No Records...</td></tr>';	
										}
									?>
									</tbody>									
								</table>
							</div>
						</div>
					</div>
					<!-- /basic datatable -->

					<div class="col-md-6">
						<form name='BranchForm' class="form-horizontal" method='POST' action="" onSubmit="return fnValidate();">							
							
							<div class="card">
								<div class="card-header bg-pgheader text-white header-elements-inline">
									<h6 class="card-title">New Branch</h6>									
								</div>

								<div class="card-body">
									<div class="row">
										<div class="col-md-12">
											<fieldset>										
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Branch Name <span class="text-mandatory">*</span></label>
													<div class="col-lg-9">
														<input type="text" class="form-control text-capitalize alpha_only" id="branch_name" name="branch_name" maxlength="75"  
														value="<?php echo $branch_name; ?>">
													</div>
												</div>
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Branch Code <span class="text-mandatory">*</span></label>
													<div class="col-lg-9">
														<input type="text" class="form-control text-uppercase" id="branch_code" name="branch_code" maxlength="4"
														value="<?php echo $branch_code; ?>">
													</div>
												</div>	
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Branch State Code <span class="text-mandatory">*</span></label>
													<div class="col-lg-9">
														<input type="text" class="form-control text-uppercase" id="branch_state_code" name="branch_state_code" maxlength="4"
														onkeypress='return event.charCode >= 48 && event.charCode <= 57' value="<?php echo $branch_state_code; ?>">
													</div>
												</div>
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Company Address</label>
													<div class="col-lg-9">
														<textarea name="company_address" id="company_address" class="form-control"><?php echo $company_address; ?></textarea>
													</div>
												</div>
											</fieldset>
										</div>
									</div>								
								</div>
								<div class="card-footer text-center">									
									<?php if(isset($_REQUEST["id"]) && $_REQUEST["branch_id"]!='')  { ?>
										  <INPUT class="btn btn-custom" type="submit" name="UPDATE" value="Update">
										  <INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
										  <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['branch_id'];?>">
									<?php }else{ ?>
										  <INPUT class="btn btn-custom" type="submit" name="SAVE" value="Save">
										  <INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
										  <input type="hidden" name="txtHid" value="0">
									<?php } ?>
								</div>
							</div>
						</form>
					</div>
					
					<!-- End of This Form UI  --->
				
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
$(document).ready(function(){
	
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
	$('#branch_name').focus();
	
	// Initialize CKEditor on company_address textarea
	CKEDITOR.replace('company_address', {
		toolbar: [
			{ name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', '-', 'RemoveFormat'] },
			{ name: 'paragraph',   items: ['NumberedList', 'BulletedList', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight'] },
			{ name: 'links',       items: ['Link', 'Unlink'] },
			{ name: 'tools',       items: ['Source'] }
		],
		height: 150,
		removePlugins: 'elementspath',
		resize_enabled: false
	});

	$('#BranchTable').on('click', 'a.delete', function (e) {
		e.preventDefault();
		var id = $(this).attr('rel');
		var table = "mst_branch";
		var status = "branch_status";
		var value = "0";
		var where = "branch_id";				
		var nRow = $(this).parents('tr')[0];			
			$.ajax({
				type:'post',
				url:'inc/cis_ajax/jquery_delete_records.php',
				data: {"id":id,"table":table,"status":status,"value":value,"where":where},
				beforeSend:function(){
					if (confirm('Are your sure, to Delete this Record..?')) {
					} else {
					return false();
					}
				},
				complete:function(){
				},
				success:function(result){
					location.reload();
				}
			});	
	});	
		

});

function fnValidate()
{
	// Sync CKEditor content back to textarea before validation/submit
	for (var instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}

	if(isNull(document.BranchForm.branch_name,"Branch Name...!")){ document.BranchForm.branch_name.focus(); return false; }
	if(isNull(document.BranchForm.branch_code,"Branch Code...!")){ document.BranchForm.branch_code.focus(); return false; }
	if(isNull(document.BranchForm.branch_state_code,"Branch State Code...!")){ document.BranchForm.branch_state_code.focus(); return false; }

	document.BranchForm.submit();
}
</script>
</html>