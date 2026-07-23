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

//echo "<pre>";print_r($_REQUEST);exit;

if (isset($_POST['SAVE']))
{		
	
	try
	{
		$is_exist = $dbconn->GetSingleReconrd("mst_hsn","hsn_id",
					  "('".$_REQUEST['hsn_code']."' AND '".$_REQUEST['hsn_description']."' AND '".$_REQUEST['cgst']."' AND '".$_REQUEST['sgst']."' AND '".$_REQUEST['igst']."') AND hsn_status ",1);
				
		if($is_exist != ""){
			$_SESSION['_msg_err'] = "hsn Code  already exist..!";
			header("location:mst_hsn.php");	
			die();
		}
	
	
		$stmt = null;				
		$stmt = $conn->prepare("INSERT INTO mst_hsn (hsn_code, hsn_description, cgst, sgst, igst, hsn_status) VALUES 
											(:hsn_code, :hsn_description, :cgst, :sgst, :igst, '1')");		
		$data = array(				
			':hsn_code' => strtoupper($_REQUEST['hsn_code']),
			':hsn_description' => strtoupper($_REQUEST['hsn_code']),
			':cgst' => strtoupper($_REQUEST['cgst']),
			':sgst' => strtoupper($_REQUEST['sgst']),
			':igst' => strtoupper($_REQUEST['igst'])
			// ':created_by' => $_SESSION['_user_id'],
			// ':created_dtm' => date('Y-m-d H:i:s')
		);
		$stmt->execute($data);
		$_SESSION['_msg'] = "hsn Succesfully Saved..!";
	}
	catch (Exception $e)
	{		
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
					
		$_SESSION['_msg_err'] = $str;			
	}
	
	header("location:mst_hsn.php");	
	die();	
}


if (isset($_POST['UPDATE']))
{

	
	$update_id = $_REQUEST['txtHid'];	

	
	try
	{
		$mst_exist = $dbconn->GetSingleReconrd("mst_hsn","hsn_id","hsn_id <> ".$update_id." AND 
					  hsn_code ='".$_REQUEST['hsn_code']."' AND hsn_description ='".$_REQUEST['hsn_description']."' AND cgst ='".$_REQUEST['cgst']."' AND sgst ='".$_REQUEST['sgst']."' AND igst ='".$_REQUEST['igst']."' AND hsn_status", 1);
	
		if($mst_exist != ""){
			$_SESSION['_msg_err'] = "hsn Already Exist..!";
			header("location:mst_hsn.php");	
			die();
		}
	
		$stmt = null;				
		$stmt = $conn->prepare("UPDATE mst_hsn SET 
							hsn_code = :hsn_code,
                            hsn_description = :hsn_description, cgst = :cgst, sgst = :sgst, igst = :igst
				WHERE hsn_id = :hsn_id");		
		$data = array(				
			':hsn_id' => $update_id,
			':hsn_code' => strtoupper($_REQUEST['hsn_code']),
			':hsn_description' => strtoupper($_REQUEST['hsn_description']),
			':cgst' => strtoupper($_REQUEST['cgst']),
			':sgst' => strtoupper($_REQUEST['sgst']),
			':igst' => strtoupper($_REQUEST['igst'])
			// ':modify_by' => $_SESSION['_user_id'],
			// ':modify_dtm' => date('Y-m-d H:i:s')			
		);
		
		$stmt->execute($data);
		echo $stmt->fullQuery;
		
		$_SESSION['_msg'] = "hsn succesfully Updated..!";
	}
	catch (Exception $e)
	{		
		$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
		$_SESSION['_msg_err'] = $str;			
	}
	
	header("location:mst_hsn.php");	
	die();		
}


if (isset($_REQUEST['id']) && $_REQUEST['id'] != "")
{
	$converter = new Encryption;
	$url_data = $converter->decode($_REQUEST['id']);
	$url_data = explode("~",$url_data);
    
	if($url_data[1] == $_SESSION['_user_id']){
		$_REQUEST['hsn_id'] = $url_data[0];
	}else{
		$_SESSION['_msg_err'] = "You don\'t have permission..!";	
		header("location:mst_hsn.php");			
		die();
	}	
}

$hsn_id="";
$hsn_code="";
$hsn_description="";
$cgst="";
$sgst="";
$igst="";

if (isset($_REQUEST['hsn_id']) && $_REQUEST['hsn_id'] != "")
{
	$result = $conn->query("SELECT * FROM mst_hsn WHERE hsn_status = '1' AND hsn_id = ".$_REQUEST['hsn_id']);	
	if ($result->rowCount()>0)
	{
		$obj = $result->fetch(PDO::FETCH_OBJ);	
		$hsn_id=$obj->hsn_id;
		$hsn_code=$obj->hsn_code;	
		$hsn_description=$obj->hsn_description;	
		$cgst=$obj->cgst;	
		$sgst=$obj->sgst;	
		$igst=$obj->igst;	
	}
	
}



?>
<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo PAGE_TITLE; ?> - HSN</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />

	<?php include_once("inc/common/css-js.php"); ?>		
</head>

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
	$('#hsn_type').focus();
	
	$('#hsnTable').on('click', 'a.delete', function (e) {
		e.preventDefault();
		var id = $(this).attr('rel');
		var table = "mst_hsn";
		var status = "hsn_status";
		var value = "0";
		var where = "hsn_id";				
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
						//$('#hsnTable').DataTable().row(nRow).remove().draw();
				}
			});	
	});	
		

});

function fnValidate()
{
	var hsncode = $("#hsn_code").val();
	if(hsncode == '')
	{
		alert("Please enter the HSN Code..!");
		return false;
	}
	
	var cgst = $("#cgst").val();
	if(cgst == '')
	{
		alert("Please enter the CGST..! ");
		return false;
	}
	var SGST = $("#sgst").val();
	if(SGST == '')
	{
		alert("Please enter the SGST..! ");
		return false;
	}
	var igst = $("#igst").val();
	if(igst == '')
	{
		alert("Please enter the IGST..! ");
		return false;
	}
	//alert(hsncode);
	//alert("validations..");
	// if(isNull(document.hsnForm.hsn_code,"HSN Code...!")){ document.hsnForm.hsn_code.focus(); return false; }
	// if(isNull(document.hsnForm.hsn_description,"HSN description...!")){ document.hsnForm.hsn_description.focus(); return false; }
	// if(isNull(document.hsnForm.cgst,"CGST...!")){ document.hsnForm.cgst.focus(); return false; }
	// if(isNull(document.hsnForm.sgst,"SGST...!")){ document.hsnForm.sgst.focus(); return false; }
	// if(isNull(document.hsnForm.igst,"IGST...!")){ document.hsnForm.igst.focus(); return false; }

	document.hsnForm.submit();

}



</script>
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
							<span class="breadcrumb-item active">HSN</span>
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
								<h6 class="card-title"> List of HSN</h6>
								<div class="header-elements">									
									<div class="list-icons">				                												
										<a class="list-icons-item" data-action="fullscreen"></a>
				                	</div>									
			                	</div>	
							</div>
							<div class="card-body pt-0">	
								<table class="datatable-col6 table table-xs table-hover table-bordered" id="hsnTable">
									<thead>
										<tr class="bg-table-header">	
											<th>HSN Code</th>
											<th>HSN Description</th>
											<th>CGST</th>
											<th>SGST</th>
											<th>IGST</th>
											<th class="text-center">Actions</th>											
										</tr>							
									</thead>										

									<tbody>									
									<?php
											
										$sql = "SELECT * FROM mst_hsn WHERE hsn_status = 1";
										$searchRes1 = $conn->query($sql);	
										$iSno = 1 ;

										if ($searchRes1->rowCount() > 0)
										{	
											while($rs=$searchRes1->fetch())
											{										
												$converter = new Encryption;
												$token = $converter->encode($rs->hsn_id.'~'.$_SESSION['_user_id']);
												
												//$ref_records = $dbconn->GetSingleReconrd("mst_products","count(*)","prod_hsn",$rs->hsn_id);
												//$ref_records = 0;
												
												echo '<tr>';		
													echo '<td>'.$rs->hsn_code.'</td>';										
													echo '<td>'.$rs->hsn_description.'</td>';										
													echo '<td>'.$rs->cgst.'</td>';										
													echo '<td>'.$rs->sgst.'</td>';										
													echo '<td>'.$rs->igst.'</td>';										
													
													echo '<td class="text-center">';													
													echo "<a href='mst_hsn.php?id=".$token."' data-popup='tooltip' title='Edit'>
															<i class='icon-pencil5 bg-edit mr-2'></i></a>";
														
														if($_SESSION['_user_type'] == 'S' || $_SESSION['_user_type'] == 'A')
														{															
															/*if($ref_records >0)
																echo '<i class="icon-bin bg-delete-disabled mr-2" data-popup="tooltip" title="You can\'t delete this"></i>';
															else*/
																echo '<a href="javascript:;" class="delete" rel="'.$rs->hsn_id.'" data-popup="tooltip" title="Delete">
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
						<form type='hsnForm' class="form-horizontal" method='POST' action=""   onSubmit="return fnValidate();" 	>							
							
							<div class="card">
								<div class="card-header bg-pgheader text-white header-elements-inline">
									<h6 class="card-title">New HSN</h6>									
								</div>

								<div class="card-body" style="">
									<div class="row">
										<div class="col-md-12">
											<fieldset>										
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">HSN Code <span class="text-mandatory">*</span></label>
													<div class="col-lg-9">
														<input type="text" class="form-control text-uppercase" id="hsn_code" name="hsn_code" maxlength="8"
														value="<?php echo $hsn_code; ?>">
													</div>
												</div>
                                                <div class="form-group row">
													<label class="col-lg-3 col-form-label">HSN Description</label>
													<div class="col-lg-9">
														<input type="text" class="form-control text-uppercase" id="hsn_description" name="hsn_description" maxlength="8"
														value="<?php echo $hsn_description; ?>">
													</div>
												</div>
                                                <div class="form-group row">
													<label class="col-lg-3 col-form-label"> CGST <span class="text-mandatory">*</span></label>
													<div class="col-lg-9">
														<input type="text" class="form-control text-uppercase" id="cgst" name="cgst" maxlength="4"
														value="<?php echo $cgst; ?>">
													</div>
												</div>
                                                <div class="form-group row">
													<label class="col-lg-3 col-form-label"> SGST <span class="text-mandatory">*</span></label>
													<div class="col-lg-9">
														<input type="text" class="form-control text-uppercase" id="sgst" name="sgst" maxlength="4"
														value="<?php echo $sgst; ?>">
													</div>
												</div>
                                                <div class="form-group row">
													<label class="col-lg-3 col-form-label"> IGST <span class="text-mandatory">*</span></label>
													<div class="col-lg-9">
														<input type="text" class="form-control text-uppercase" id="igst" name="igst" maxlength="4"
														value="<?php echo $igst; ?>">
													</div>
												</div>												
											</fieldset>
										</div>
									</div>								
								</div>
								<div class="card-footer text-center">									
									<?php if(isset($_REQUEST["id"]) && $_REQUEST["hsn_id"]!='')  { ?>
										  <INPUT class="btn btn-custom" type="submit" name="UPDATE" value="Update">
										  <INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
										  <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['hsn_id'];?>">
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

</html>
