<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");

isAdmin();

$conn = new dbconnect();
$dbconn= new dbhandler();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


if (isset($_POST['SAVE']))
{	
	
	try
	{ 
	    $is_exist = $dbconn->GetSingleReconrd("mst_principal","principal_id",
					"(principal_name = '".$_REQUEST['principal_name']."' OR principal_code = '".$_REQUEST['principal_code']."') AND principal_status ", 1);
				
		if($is_exist != ""){
			$_SESSION['_msg_err'] = "Principal Name / Code  already exist..!";
			header("location:mst_principal.php");	
			die();
		}
		$stmt = null;				
		$stmt = $conn->prepare("INSERT INTO mst_principal (principal_name, principal_code, principal_status ,created_by, created_dtm) VALUES 
											(:principal_name, :principal_code, '1', :created_by, :created_dtm)");		
		$data = array(				
			':principal_name' => ucwords($_REQUEST['principal_name']),
			':principal_code' => strtoupper($_REQUEST['principal_code']),
			':created_by' => $_SESSION['_user_id'],
			':created_dtm' => date('Y-m-d H:i:s')
		);
		$stmt->execute($data);
		$_SESSION['_msg'] = "Principal Succesfully Saved..!";
	}
	catch (Exception $e)
	{		
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
		$_SESSION['_msg_err'] = $str;			
	}
	
	header("location:mst_principal.php");	
	die();	
}


if (isset($_POST['UPDATE']))
{
	
	$update_id = $_REQUEST['txtHid'];		
	
	try
	{
		$mst_exist = $dbconn->GetSingleReconrd("mst_principal","principal_id","principal_id <> ".$update_id." AND 
					 principal_name ='".$_REQUEST['principal_name']."' AND principal_status", 1);
	
		if($mst_exist != ""){
			$_SESSION['_msg_err'] = "Principal Name Already Exist..!";
			header("location:mst_principal.php");	
			die();
		}
		$stmt = null;				
		$stmt = $conn->prepare("UPDATE mst_principal SET 
		            principal_name = :principal_name, principal_code= :principal_code,
		            modify_by= :modify_by, modify_dtm= :modify_dtm
		        WHERE principal_id = :principal_id");		
		$data = array(				
			':principal_id' => $update_id,
			':principal_name' => ucwords($_REQUEST['principal_name']),
			':principal_code' => strtoupper($_REQUEST['principal_code']),
			':modify_by' => $_SESSION['_user_id'],
			':modify_dtm' => date('Y-m-d H:i:s')
		);
		
		$stmt->execute($data);
		echo $stmt->fullQuery;
		
		$_SESSION['_msg'] = "Principal succesfully Updated..!";
	}
	catch (Exception $e)
	{		
		$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
		$_SESSION['_msg_err'] = $str;			
	}
	
	header("location:mst_principal.php");	
	die();		
}


if (isset($_REQUEST['id']) && $_REQUEST['id'] != "")
{
	$converter = new Encryption;
	$url_data = $converter->decode($_REQUEST['id']);
	$url_data = explode("~",$url_data);
    
	if($url_data[1] == $_SESSION['_user_id']){
		$_REQUEST['principal_id'] = $url_data[0];
	}else{
		$_SESSION['_msg_err'] = "You don\'t have permission..!";	
		header("location:mst_principal.php");			
		die();
	}	
}

$principal_id="";
$principal_name="";
$principal_code="";

if (isset($_REQUEST['principal_id']) && $_REQUEST['principal_id'] != "")
{
	$result = $conn->query("SELECT * FROM mst_principal WHERE principal_status = '1' AND principal_id = ".$_REQUEST['principal_id']);	
	if ($result->rowCount()>0)
	{
		$obj = $result->fetch(PDO::FETCH_OBJ);	
		$principal_id=$obj->principal_id;
		$principal_name=$obj->principal_name;
		$principal_code=$obj->principal_code;	
	}
	
}



?>
<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo PAGE_TITLE; ?> - Principal</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />

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
							<a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
							<a href="#" class="breadcrumb-item"> Settings</a>
							<span class="breadcrumb-item active">Principal</span>
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
								<h6 class="card-title">List of Principal</h6>
								<div class="header-elements">									
									<div class="list-icons">				                												
										<a class="list-icons-item" data-action="fullscreen"></a>
				                	</div>									
			                	</div>	
							</div>
							<div class="card-body pt-0">	
								<table class="datatable-col3 table table-xs table-hover table-bordered" id="principalTable">
									<thead>
										<tr class="bg-table-header">	
											<th>Principal Name</th>
											<th>Principal Code</th>
											<th class="text-center">Actions</th>											
										</tr>							
									</thead>										

									<tbody>									
									<?php
											
										$sql = "SELECT * FROM mst_principal WHERE principal_status = 1";
										$searchRes1 = $conn->query($sql);	
										$iSno = 1 ;

										if ($searchRes1->rowCount() > 0)
										{	
											while($rs=$searchRes1->fetch())
											{										
												$converter = new Encryption;
												$token = $converter->encode($rs->principal_id.'~'.$_SESSION['_user_id']);
												
												//$ref_records = $dbconn->GetSingleReconrd("mst_products","count(*)","prod_color",$rs->color_id);
												//$ref_records = 0;
												
												echo '<tr>';	
													echo '<td>'.$rs->principal_name.' </td>';	
													echo '<td>'.$rs->principal_code.'</td>';										
													
													echo '<td class="text-center">';													
													echo "<a href='mst_principal.php?id=".$token."' data-popup='tooltip' title='Edit'>
															<i class='icon-pencil5 bg-edit mr-2'></i></a>";
														
														if($_SESSION['_user_type'] == 'S' || $_SESSION['_user_type'] == 'A')
														{															
															/*if($ref_records >0)
																echo '<i class="icon-bin bg-delete-disabled mr-2" data-popup="tooltip" title="You can\'t delete this"></i>';
															else*/
																echo '<a href="javascript:;" class="delete" rel="'.$rs->principal_id.'" data-popup="tooltip" title="Delete">
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
											echo '<tr><td colspan="3">No Records...</td></tr>';	
										}
									?>
									</tbody>									
								</table>
							</div>
						</div>
					</div>
					<!-- /basic datatable -->

					<div class="col-md-6">
						<form name='principalForm' class="form-horizontal" method='POST' action=""   onSubmit="return fnValidate();" 	>							
							
							<div class="card">
								<div class="card-header bg-pgheader text-white header-elements-inline">
									<h6 class="card-title">New Principal</h6>									
								</div>

								<div class="card-body" style="">
									<div class="row">
										<div class="col-md-12">
											<fieldset>										
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Principal Name <span class="text-mandatory">*</span></label>
													<div class="col-lg-9">
														<input type="text" class="form-control text-capitalize alpha_only" id="principal_name" name="principal_name" maxlength="75"  
														value="<?php echo $principal_name; ?>">
													</div>
												</div>
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Principal Code <span class="text-mandatory">*</span></label>
													<div class="col-lg-9">
														<input type="text" class="form-control text-uppercase" id="principal_code" name="principal_code" maxlength="4"
														value="<?php echo $principal_code; ?>">
													</div>
												</div>														
											</fieldset>
										</div>
									</div>								
								</div>
								<div class="card-footer text-center">									
									<?php if(isset($_REQUEST["id"]) && $_REQUEST["principal_id"]!='')  { ?>
										  <INPUT class="btn btn-custom" type="submit" name="UPDATE" value="Update">
										  <INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
										  <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['principal_id'];?>">
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
	$('#principal_name').focus();
	
	$('#principalTable').on('click', 'a.delete', function (e) {
		e.preventDefault();
		var id = $(this).attr('rel');
		var table = "mst_principal";
		var status = "principal_status";
		var value = "0";
		var where = "principal_id";				
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
						//$('#colorTable').DataTable().row(nRow).remove().draw();
				}
			});	
	});	
		

});

function fnValidate()
{
	//alert("validations..");
	if(isNull(document.principalForm.principal_name,"principal Name...!")){ document.principalForm.principal_name.focus(); return false; }
	if(isNull(document.principalForm.principal_code,"principal Code...!")){ document.principalForm.principal_code.focus(); return false; }

	document.principalForm.submit();

}



</script>
</html>
