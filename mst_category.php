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
		$is_exist = $dbconn->GetSingleReconrd("mst_category","category_id",
					"(category_name = '".$_REQUEST['category_name']."' OR category_code = '".$_REQUEST['category_code']."') AND category_status ",1);
				
		if($is_exist != ""){
			$_SESSION['_msg_err'] = "category Name / Code  already exist..!";
			header("location:mst_category.php");	
			die();
		}
	
	
		$stmt = null;				
		$stmt = $conn->prepare("INSERT INTO mst_category (category_name, category_code, category_status, created_by, created_dtm) VALUES 
											(:category_name, :category_code, '1', :created_by, :created_dtm)");		
		$data = array(				
			':category_name' => ucwords($_REQUEST['category_name']),
			':category_code' => strtoupper($_REQUEST['category_code']),
			':created_by' => $_SESSION['_user_id'],
			':created_dtm' => date('Y-m-d H:i:s')
		);
		$stmt->execute($data);
		$_SESSION['_msg'] = "category Succesfully Saved..!";
	}
	catch (Exception $e)
	{		
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
		$_SESSION['_msg_err'] = $str;			
	}
	
	header("location:mst_category.php");	
	die();	
}


if (isset($_POST['UPDATE']))
{
	
	$update_id = $_REQUEST['txtHid'];		
	
	try
	{
		$mst_exist = $dbconn->GetSingleReconrd("mst_category","category_id","category_id <> ".$update_id." AND 
					 category_name ='".$_REQUEST['category_name']."' AND category_status", 1);
	
		if($mst_exist != ""){
			$_SESSION['_msg_err'] = "category Already Exist..!";
			header("location:mst_category.php");	
			die();
		}
	
		$stmt = null;				
		$stmt = $conn->prepare("UPDATE mst_category SET 
							category_name = :category_name, category_code = :category_code, 
							modify_by = :modify_by, modify_dtm = :modify_dtm
				WHERE category_id = :category_id");		
		$data = array(				
			':category_id' => $update_id,
			':category_name' => ucwords($_REQUEST['category_name']),
			':category_code' => strtoupper($_REQUEST['category_code']),
			':modify_by' => $_SESSION['_user_id'],
			':modify_dtm' => date('Y-m-d H:i:s')			
		);
		
		$stmt->execute($data);
		echo $stmt->fullQuery;
		
		$_SESSION['_msg'] = "category succesfully Updated..!";
	}
	catch (Exception $e)
	{		
		$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
		$_SESSION['_msg_err'] = $str;			
	}
	
	header("location:mst_category.php");	
	die();		
}


if (isset($_REQUEST['id']) && $_REQUEST['id'] != "")
{
	$converter = new Encryption;
	$url_data = $converter->decode($_REQUEST['id']);
	$url_data = explode("~",$url_data);
    
	if($url_data[1] == $_SESSION['_user_id']){
		$_REQUEST['category_id'] = $url_data[0];
	}else{
		$_SESSION['_msg_err'] = "You don\'t have permission..!";	
		header("location:mst_category.php");			
		die();
	}	
}

$category_id="";
$category_name="";
$category_code="";

if (isset($_REQUEST['category_id']) && $_REQUEST['category_id'] != "")
{
	$result = $conn->query("SELECT * FROM mst_category WHERE category_status = '1' AND category_id = ".$_REQUEST['category_id']);	
	if ($result->rowCount()>0)
	{
		$obj = $result->fetch(PDO::FETCH_OBJ);	
		$category_id=$obj->category_id;
		$category_name=$obj->category_name;
		$category_code=$obj->category_code;	
	}
	
}



?>
<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo PAGE_TITLE; ?> - Category</title>
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
							<span class="breadcrumb-item active">Category</span>
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
								<h6 class="card-title">List of Category</h6>
								<div class="header-elements">									
									<div class="list-icons">				                												
										<a class="list-icons-item" data-action="fullscreen"></a>
				                	</div>									
			                	</div>	
							</div>
							<div class="card-body pt-0">	
								<table class="datatable-col3 table table-xs table-hover table-bordered" id="categoryTable">
									<thead>
										<tr class="bg-table-header">	
											<th>Category Name</th>
											<th>Category Code</th>
											<th class="text-center">Actions</th>											
										</tr>							
									</thead>										

									<tbody>									
									<?php
											
										$sql = "SELECT * FROM mst_category WHERE category_status = 1";
										$searchRes1 = $conn->query($sql);	
										$iSno = 1 ;

										if ($searchRes1->rowCount() > 0)
										{	
											while($rs=$searchRes1->fetch())
											{										
												$converter = new Encryption;
												$token = $converter->encode($rs->category_id.'~'.$_SESSION['_user_id']);
												
												//$ref_records = $dbconn->GetSingleReconrd("mst_products","count(*)","prod_category",$rs->category_id);
												//$ref_records = 0;
												
												echo '<tr>';	
													echo '<td>'.$rs->category_name.' </td>';	
													echo '<td>'.$rs->category_code.'</td>';								
													echo '<td class="text-center">';													
													echo "<a href='mst_category.php?id=".$token."' data-popup='tooltip' title='Edit'>
															<i class='icon-pencil5 bg-edit mr-2'></i></a>";
														
														if($_SESSION['_user_type'] == 'S' || $_SESSION['_user_type'] == 'A')
														{															
															/*if($ref_records >0)
																echo '<i class="icon-bin bg-delete-disabled mr-2" data-popup="tooltip" title="You can\'t delete this"></i>';
															else*/
																echo '<a href="javascript:;" class="delete" rel="'.$rs->category_id.'" data-popup="tooltip" title="Delete">
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
						<form name='categoryForm' class="form-horizontal" method='POST' action=""   onSubmit="return fnValidate();" 	>							
							
							<div class="card">
								<div class="card-header bg-pgheader text-white header-elements-inline">
									<h6 class="card-title">New Category</h6>									
								</div>

								<div class="card-body" style="">
									<div class="row">
										<div class="col-md-12">
											<fieldset>										
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Category Name <span class="text-mandatory"> *</span></label>
													<div class="col-lg-9">
														<input type="text" class="form-control text-capitalize alpha_only" id="category_name" name="category_name" maxlength="75"  
														value="<?php echo $category_name; ?>">
													</div>
												</div>
												<div class="form-group row">
													<label class="col-lg-3 col-form-label">Category Code</label>
													<div class="col-lg-9">
														<input type="text" class="form-control text-uppercase" id="category_code" name="category_code" maxlength="4"
														value="<?php echo $category_code; ?>">
													</div>
												</div>														
											</fieldset>
										</div>
									</div>								
								</div>
								<div class="card-footer text-center">									
									<?php if(isset($_REQUEST["id"]) && $_REQUEST["category_id"]!='')  { ?>
										  <INPUT class="btn btn-custom" type="submit" name="UPDATE" value="Update">
										  <INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
										  <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['category_id'];?>">
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
	$('#category_name').focus();
	
	$('#categoryTable').on('click', 'a.delete', function (e) {
		e.preventDefault();
		var id = $(this).attr('rel');
		var table = "mst_category";
		var status = "category_status";
		var value = "0";
		var where = "category_id";				
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
						//$('#categoryTable').DataTable().row(nRow).remove().draw();
				}
			});	
	});	
		

});

function fnValidate()
{
	//alert("validations..");
	if(isNull(document.categoryForm.category_name,"Category Name...!")){ document.categoryForm.category_name.focus(); return false; }
	if(isNull(document.categoryForm.category_code,"Category Code...!")){ document.categoryForm.category_code.focus(); return false; }

	document.categoryForm.submit();

}



</script>
</html>
