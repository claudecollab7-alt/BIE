<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");

isAdmin();

$conn = new dbconnect();
$dbconn= new dbhandler();

/*
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);*/

if(isset($_POST['SAVE']))
{
	try
	{
		
		$_REQUEST['created_by'] = $_SESSION['_user_id'];
		$_REQUEST['created_dtm'] = date('Y-m-d H:i:s');
		
		if(!isset($_REQUEST['prod_diminishing'])){
			$_REQUEST['prod_diminishing'] = 0;
		}	
		if(!isset($_REQUEST['prod_sellable'])){
			$_REQUEST['prod_sellable'] = 0;
		}if(!isset($_REQUEST['prod_controlled_drug'])){
			$_REQUEST['prod_controlled_drug'] = 0;
		}
		//print_r($_FILES);
		if ($_FILES['prod_img']['name']!="")
		{			
			if($_REQUEST["hide_prod_img"] != "")	
			{		
				removeFile("project_img/prod_img/".$_REQUEST["hide_prod_img"]);
			}
			$ext = pathinfo($_FILES['prod_img']['name'], PATHINFO_EXTENSION);
			$customfilename = $_REQUEST['prod_code'].'_'.date('Ymd').'.'.$ext;
			$_REQUEST['prod_img'] = 
			post_img($customfilename, $_FILES['prod_img']['tmp_name'],"project_img/prod_img/");
			$_REQUEST['prod_img_size'] = $_FILES['prod_img']['size'];

		}
		//print_r($_REQUEST);exit;
		$prod_insert = null;		
		//sales_ac_name, purchase_ac_name, expense_ac_name,		
		
		$prod_insert = $conn->prepare("INSERT INTO mst_products (prod_code, prod_name, prod_generic_id, prod_desc, 
		 prod_uom, prod_packing, prod_group_id, prod_diminishing,  prod_sellable, prod_min_stock_alert, 
		 prod_max_ord_qty, prod_reord_qty,  prod_gst, prod_purchase_price, prod_sales_price, prod_mrp, prod_manufac, prod_salt, prod_controlled_drug, prod_rack, prod_img, prod_img_size, prod_scheme, created_by, created_dtm) VALUES (:prod_code, :prod_name, :prod_generic_id, :prod_desc, 
		:prod_uom, :prod_packing, :prod_group_id, :prod_diminishing,:prod_sellable, :prod_min_stock_alert, 
		:prod_max_ord_qty, :prod_reord_qty,  :prod_gst, :prod_purchase_price, :prod_sales_price, :prod_mrp, :prod_manufac, :prod_salt, :prod_controlled_drug, :prod_rack, :prod_img, :prod_img_size, :prod_scheme, :created_by, :created_dtm)");	

		//:sales_ac_name, :purchase_ac_name, :expense_ac_name,		
		$data = array(
			':prod_code' => strtoupper($_REQUEST['prod_code']),	
			':prod_name' => strtoupper($_REQUEST['prod_name']),
			':prod_generic_id' => $_REQUEST['prod_generic_id'],
			':prod_desc' => $_REQUEST['prod_desc'],			
			':prod_uom' => $_REQUEST['prod_uom'],
			':prod_packing' => $_REQUEST['prod_packing'],
			':prod_group_id' => $_REQUEST['prod_group_id'],
			':prod_diminishing' => $_REQUEST['prod_diminishing'],			
			':prod_sellable' => $_REQUEST['prod_sellable'],
			':prod_min_stock_alert' => $_REQUEST['prod_min_stock_alert'],			
			':prod_max_ord_qty' => $_REQUEST['prod_max_ord_qty'],
			':prod_reord_qty' => $_REQUEST['prod_reord_qty'],
			':prod_gst' => $_REQUEST['prod_gst'],
			':prod_purchase_price' => $_REQUEST['prod_purchase_price'],
			':prod_sales_price' => $_REQUEST['prod_sales_price'],
			':prod_mrp' => $_REQUEST['prod_mrp'],
			':prod_manufac' =>$_REQUEST['prod_manufac'],
			':prod_salt' => $_REQUEST['prod_salt'],
			':prod_controlled_drug' => $_REQUEST['prod_controlled_drug'],
			':prod_rack' => $_REQUEST['prod_rack'],
			':prod_img' => $_REQUEST['prod_img'],
			':prod_img_size' => $_REQUEST['prod_img_size'],
			':prod_scheme' => $_REQUEST['prod_scheme'],
			
			':created_by' => $_REQUEST['created_by'],
			':created_dtm' => $_REQUEST['created_dtm']					
		);
		
		$prod_insert->execute($data);
		$last_id = $conn->lastInsertId();
		$_SESSION['_msg'] = "New Product succesfully Saved..!";
	}
	catch (Exception $e)
	{		
		$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
		$_SESSION['_msg_err'] = $str;	
		
	}
	
	header("location:mst_product.php");	
	die();
}

if (isset($_POST['UPDATE']))
{
	$update_id = $_REQUEST['txtHid'];
	try
	{		
		if(!isset($_REQUEST['prod_diminishing'])){
			$_REQUEST['prod_diminishing'] = 0;
		}
		if(!isset($_REQUEST['prod_sellable'])){
			$_REQUEST['prod_sellable'] = 0;
		}
		if(!isset($_REQUEST['prod_controlled_drug'])){
			$_REQUEST['prod_controlled_drug'] = 0;
		}
		
		if ($_FILES['prod_img']['name']!="")
		{			
			if($_REQUEST["hide_prod_img"] != "")	
			{		
				removeFile("project_img/prod_img/".$_REQUEST["hide_prod_img"]);
			}
			$ext = pathinfo($_FILES['prod_img']['name'], PATHINFO_EXTENSION);
			$customfilename = $_REQUEST['prod_code'].'_'.date('Ymd').'.'.$ext;
			$_REQUEST['prod_img'] = 
			post_img($customfilename, $_FILES['prod_img']['tmp_name'],"project_img/prod_img/");
			$_REQUEST['prod_img_size'] = $_FILES['prod_img']['size'];

		}
		else
		{
			$_REQUEST['prod_img'] = $_REQUEST["hide_prod_img"];
		}
		
		$_REQUEST['last_modify_by'] = $_SESSION['_user_id'];
		$_REQUEST['updated_dtm'] = date('Y-m-d H:i:s');
		
		$prod_update = null;				
		$prod_update = $conn->prepare("UPDATE  mst_products SET  prod_code =:prod_code, prod_name=:prod_name, prod_generic_id =:prod_generic_id, prod_desc=:prod_desc, 
		 prod_uom=:prod_uom, prod_packing=:prod_packing, prod_group_id=:prod_group_id, prod_diminishing=:prod_diminishing, prod_sellable=:prod_sellable, prod_min_stock_alert=:prod_min_stock_alert,  prod_max_ord_qty=:prod_max_ord_qty, prod_reord_qty=:prod_reord_qty,  prod_gst=:prod_gst, prod_purchase_price=:prod_purchase_price, prod_sales_price=:prod_sales_price, prod_mrp = :prod_mrp, prod_manufac=:prod_manufac, prod_salt=:prod_salt, prod_controlled_drug=:prod_controlled_drug, prod_rack=:prod_rack, prod_img=:prod_img, prod_img_size=:prod_img_size, prod_scheme=:prod_scheme,last_modify_by = :last_modify_by, updated_dtm = :updated_dtm WHERE prod_id = :prod_id");	
		//sales_ac_name=:sales_ac_name, purchase_ac_name=:purchase_ac_name, expense_ac_name=:expense_ac_name,		
		
		$data = array(				
			':prod_id' => $update_id,
			':prod_code' => strtoupper($_REQUEST['prod_code']),	
			':prod_name' => strtoupper($_REQUEST['prod_name']),
			':prod_generic_id' => $_REQUEST['prod_generic_id'],
			':prod_desc' => $_REQUEST['prod_desc'],		
			':prod_uom' => $_REQUEST['prod_uom'],
			':prod_packing' => $_REQUEST['prod_packing'],
			':prod_group_id' => $_REQUEST['prod_group_id'],
			':prod_diminishing' => $_REQUEST['prod_diminishing'],			
			':prod_sellable' => $_REQUEST['prod_sellable'],
			':prod_min_stock_alert' => $_REQUEST['prod_min_stock_alert'],			
			':prod_max_ord_qty' => $_REQUEST['prod_max_ord_qty'],
			':prod_reord_qty' => $_REQUEST['prod_reord_qty'],
			':prod_gst' => $_REQUEST['prod_gst'],
			':prod_purchase_price' => $_REQUEST['prod_purchase_price'],
			':prod_sales_price' => $_REQUEST['prod_sales_price'],
			':prod_mrp' => $_REQUEST['prod_mrp'],
			':prod_manufac' => $_REQUEST['prod_manufac'],
			':prod_salt' => $_REQUEST['prod_salt'],
			':prod_controlled_drug' => $_REQUEST['prod_controlled_drug'],
			':prod_rack' => $_REQUEST['prod_rack'],
			':prod_img' => $_REQUEST['prod_img'],
			':prod_img_size' => $_REQUEST['prod_img_size'],
			':prod_scheme' => $_REQUEST['prod_scheme'],
			
			':last_modify_by' => $_REQUEST['last_modify_by'],
			':updated_dtm' => $_REQUEST['updated_dtm']			
		);
	
		$prod_update->execute($data);		
		
		$_SESSION['_msg']=  "Product Details Successfully Updated..!";
	}
	catch (Exception $e)
	{		
		$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
		$_SESSION['_msg_err'] = $str;			
	}
	
	header("Location:mst_product.php");	
	die();
}

$prod_sellable = 1;

if (isset($_REQUEST['id']))
{
	$converter = new Encryption;
	$url_data = $converter->decode($_REQUEST['id']);
	$url_data = explode("~",$url_data);
	if($url_data[1] == $_SESSION['_user_id']){
		$_REQUEST['prod_id'] = $url_data[0];
	}else{
		$_SESSION['_msg_err'] = "You don\'t have permission..!";	
		header("location:mst_product.php");			
		die();
	}
	$result = $conn->query("SELECT * FROM mst_products WHERE prod_id = '".$_REQUEST['prod_id']."'");	
	if ($result->rowCount()>0)
	{
		$obj = $result->fetch(PDO::FETCH_OBJ);		
		$prod_sellable = $obj->prod_sellable;		
		
	}
}
$sales_rate_per = $dbconn->GetSingleReconrd("mst_product_settings","value","auto_id",1);
?>
<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo PAGE_TITLE; ?> - New Product</title>

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
							<a href="#" class="breadcrumb-item"> Settings</a>
							<span class="breadcrumb-item active">Product Details</span>
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
					<!-- This Form UI Starts here --->
					<form name='prodForm' class="form-horizontal" method='POST' action="" enctype="multipart/form-data"   onSubmit="return fnValidate();"> 
					<div class="card">
						<div class="card-header bg-pgheader text-white header-elements-inline">					
							<h6 class="card-title">Product Details</h6>		
							<div class="header-elements">									
								<div class="list-icons">				                		
									<a class="list-icons-item" href="mst_product.php" title="Product List"><i class="icon-arrow-left52 mr-2"></i></a>
									<a class="list-icons-item" data-action="fullscreen"></a>
								</div>									
			                </div>										
						</div>

						<div class="card-body">
							<div class="row">
								<div class="col-md-12">
									<fieldset>
										<legend class="font-weight-semibold"><i class="icon-eyedropper3 mr-2"></i>Product Details</legend>
										<div class="form-group row">
											<label class="col-lg-2 col-form-label">Generic<span class="text-mandatory"> *</span></label>
											<div class="col-lg-4">
												
												<select name="prod_generic_id" id="prod_generic_id" class="form-control select-search" data-fouc>
												<option value="">-- Select Generic --</option> 
												<?php
													   echo $dbconn->fnFillComboFromTable_Where("prod_generic_id","prod_generic_name","mst_product_generic","prod_generic_name"," WHERE prod_generic_status = 1");
													?>
												</select> 
												<script>document.prodForm.prod_generic_id.value="<?php echo $obj->prod_generic_id; ?>";</script>
												<div  id="wrp_prdgeneric" style="display: none;">
													<a class="col-form-label" data-toggle="modal" style="color:#0e4571" 
													data-target="#modal_new_prdgeneric" href="" data-popup="tooltip" title="New Generic"> <i class="icon-plus-circle2 text-warning"></i> New Generic</a>
												</div>	
											</div>
										</div>
										<div class="form-group row">
											<label class="col-lg-2 col-form-label">Product Name <span class="text-mandatory">*</span></label>
											<div class="col-lg-4">
												<input type="text" name="prod_name" id="prod_name" class="form-control text-uppercase" maxlength="150" value="<?php echo $obj->prod_name; ?>">
											</div>
											
											<label class="col-lg-2 col-form-label">Product Code <span class="text-mandatory">*</span></label>
											<div class="col-lg-4">
												<input type="text" name="prod_code" id="prod_code" class="form-control text-uppercase alpha_numeric" maxlength="15" value="<?php echo $obj->prod_code; ?>">
											</div>
										</div>
										
										<div class="form-group row">
											<label class="col-lg-2 col-form-label">Product Group <span class="text-mandatory">*</span></label>
											<div class="col-lg-4">
												
												<select name="prod_group_id" id="prod_group_id" class="form-control select-search" data-fouc>
												<option value="">-- Select Group --</option> 
												<?php
													   echo $dbconn->fnFillComboFromTable_Where("prod_group_id","prod_group_name","mst_product_group","prod_group_name"," WHERE prod_group_status = 1");
													?>
												</select> 
												<script>document.prodForm.prod_group_id.value="<?php echo $obj->prod_group_id; ?>";</script>
												<div  id="wrp_group" style="display: none;">
													<a class="col-form-label" data-toggle="modal" style="color:#0e4571" 
													data-target="#modal_new_prdGrp" href="" data-popup="tooltip" title="New Product Group"> <i class="icon-plus-circle2 text-warning"></i> New Product Group</a>
												</div>	
											</div>
											
											
											<label class="col-lg-2 col-form-label">Product HSN <span class="text-mandatory">*</span></label>
											<div class="col-lg-4">
												<select name="prod_gst" id="prod_gst" class="form-control  select-search" data-fouc>
													<option value="">-- Select HSN --</option> 
													<?php
														   echo $dbconn->fnFillComboFromTable_Where("gst_id","CONCAT(gst_code,' - ',gst,'%')","mst_gst","gst_code"," WHERE 1 = 1");
													?>
													</select> 
													<script>document.prodForm.prod_gst.value="<?php echo $obj->prod_gst; ?>";</script>
												<div  id="wrp_hsn" style="display: none;">
												<a class="col-form-label" data-toggle="modal"  style="color:#0e4571" 
												data-target="#modal_new_hsn" href="" data-popup="tooltip" title="New HSN"> 
												<i class="icon-plus-circle2 text-warning"></i> New HSN</a>
												</div>	
													
											</div>
										</div>
										<div class="form-group row">
											<label class="col-lg-2 col-form-label">Product Description <span class="text-mandatory">*</span></label>
											<div class="col-lg-4">
												<textarea name="prod_desc" id="prod_desc" class="form-control"
												maxlength="150"><?php echo $obj->prod_desc;?></textarea>
											</div>
											<label class="col-lg-2 col-form-label">Product Image</label>
											<div class="col-lg-4">
													<input type="file" id="prod_img" name="prod_img" class="file-input image_only" data-size="300" data-submit='1' data-show-preview="false" data-fouc data-fouc >
													<div id="prod_img_error" class="cis-feedback help-block form-text text-muted">Accepted formats: jpeg, png, jpg. </div>
													<input type="hidden" name="hide_prod_img" value="<?php echo $obj->prod_img; ?>">
											</div>
										</div>
									
										
										<legend class="font-weight-semibold"><i class="icon-cabinet mr-2"></i>Product Packing</legend>
										
										<div class="form-group row">
											<label class="col-lg-2 col-form-label">Product UOM <span class="text-mandatory">*</span></label>
											<div class="col-lg-4 ">
												
													
												<select name="prod_uom" id="prod_uom" class="form-control select-search" data-fouc>
												<option value="">-- Select UOM --</option> 
												<?php
													   echo $dbconn->fnFillComboFromTable_Where("uom_id","CONCAT(uom_code,' - ',uom_name)","mst_uom","uom_name"," WHERE 1 = 1");
												?>
												</select> 
												<script>document.prodForm.prod_uom.value="<?php echo $obj->prod_uom; ?>";</script>
																								
												<div  id="wrp_uom" style="display: none;">
												<a class="col-form-label" data-toggle="modal"  style="color:#0e4571" 
												data-target="#modal_new_uom" href="" data-popup="tooltip" title="New UOM"> 
												<i class="icon-plus-circle2 text-warning"></i> New UOM</a>
												</div>	
												
												
											</div>
											
											<label class="col-lg-2 col-form-label">Product Sellable <span class="text-mandatory">*</span></label>
											<div class="col-lg-4">
												<div class="form-check form-check-switch form-check-switch-left">
													<label class="form-check-label d-flex align-items-center">
														<input type="checkbox" class="form-check-input form-check-input-switch" name="prod_sellable" id="prod_sellable"  data-on-text="Yes" data-off-text="No" data-on-color="info" data-off-color="default" value="1" <?php if($prod_sellable == 1){?>checked <?php }?> >
													</label>
												</div>
											</div>
											
										</div>
										<div class="form-group row">
											<label class="col-lg-2 col-form-label">Product Packing</label>
											<div class="col-lg-4">
												<input type="text" name="prod_packing" id="prod_packing" class="form-control number_only" maxlength="3" 
												value="<?php echo $obj->prod_packing; ?>">
											</div>
											<label class="col-lg-2 col-form-label">Never Diminishing</label>
											<div class="col-lg-4">													
												<div class="form-check form-check-switch form-check-switch-left">
													<label class="form-check-label d-flex align-items-center">
														<input type="checkbox" class="form-check-input form-check-input-switch" name="prod_diminishing" id="prod_diminishing" data-on-text="Yes" data-off-text="No" data-on-color="info" data-off-color="default" value="1" <?php if($obj->prod_diminishing == 1){?>checked <?php }?> >
													</label>
												</div>
											</div>											
										</div>
										
										
										<legend class="font-weight-semibold"><i class="icon-database mr-2"></i>Product Stock</legend>
										
										<div class="form-group row">
											
											<label class="col-lg-2 col-form-label">Purchase Price (excl. tax)</label>
											<div class="col-lg-4">
												<div class="input-group">
													<span class="input-group-prepend">
														<span class="input-group-text">Rs.</span>
													</span>
													<input type="text" name="prod_purchase_price" id="prod_purchase_price" class="form-control number_only_dot" maxlength="8" value="<?php echo $obj->prod_purchase_price; ?>">
													
													<input type="hidden" name="sales_rate_per" id="sales_rate_per" class="form-control number_only_dot"  value="<?php echo $sales_rate_per; ?>">
													
													<input type="hidden" name="final_sales_price" id="final_sales_price" class="form-control number_only_dot"  value="">
												</div>		
											</div>	
											<label class="col-lg-2 col-form-label">Product Min.Stock Alert</label>
											<div class="col-lg-4">
												<input type="text" name="prod_min_stock_alert" id="prod_min_stock_alert" class="form-control number_only" maxlength="3" value="<?php echo $obj->prod_min_stock_alert; ?>">
											</div>
										</div>
										<div class="form-group row">											
											
											<label class="col-lg-2 col-form-label">Sales Price (excl. tax)</label>
											<div class="col-lg-4">
												<div class="input-group">
													<span class="input-group-prepend">
														<span class="input-group-text">Rs.</span>
													</span>
													<input type="text" name="prod_sales_price" id="prod_sales_price" class="form-control number_only_dot" maxlength="8" value="<?php echo $obj->prod_sales_price; ?>">
												</div>										
											</div>	
											<label class="col-lg-2 col-form-label">Max. Order Qty (PO)
											<i class="icon-question3 text-warning  ml-2" data-popup="popover" title="" data-html="true" data-trigger="hover"
											data-content="Maximum quantity that can be ordered in a Purchase Order(PO). <br><b>Example:</b>
											<br>0 - No Limit<br>50 - 50 is the Max. Qty allowed for this product in any PO" data-original-title="Maximum Order Qty"></i>
											
											</label>
											<div class="col-lg-4">
												<input type="text" name="prod_max_ord_qty" id="prod_max_ord_qty" class="form-control number_only" 
												maxlength="3" value="<?php echo $obj->prod_max_ord_qty; ?>">
											</div>											
										</div>
										
										<div class="form-group row">
											
											<label class="col-lg-2 col-form-label">MRP</label>
											<div class="col-lg-4">
												<input type="text" name="prod_mrp" id="prod_mrp" class="form-control number_only" maxlength="8" value="<?php echo $obj->prod_mrp; ?>">
											</div>
											<label class="col-lg-2 col-form-label">Reorder Qty </label>
											<div class="col-lg-4">
												<input type="text" name="prod_reord_qty" id="prod_reord_qty" class="form-control number_only" maxlength="3" value="<?php echo $obj->prod_reord_qty; ?>">
											</div>
										</div>
										
										<div class="form-group row">
											<label class="col-lg-2 col-form-label">Scheme (%)</label>
											<div class="col-lg-4">
												<input type="text" name="prod_scheme" id="prod_scheme" class="form-control number_only" maxlength="3" value="<?php echo $obj->prod_scheme; ?>">
											</div>
										</div>										
										
										<legend class="font-weight-semibold"><i class="icon-office mr-2"></i>Product Company</legend>
										
										<div class="form-group row">
											
											<label class="col-lg-2 col-form-label">Company Name  <span class="text-mandatory">*</span></label>
											<div class="col-lg-4">
												
												<select name="prod_manufac" id="prod_manufac" class="form-control select-search" data-fouc>
												<option value="">-- Select Company --</option> 
												<?php
													   echo $dbconn->fnFillComboFromTable_Where("manufac_id","CONCAT(manufac_code,' - ',manufac_name)","mst_manufacturer","manufac_name"," WHERE manufac_status = 1");
												?>
												</select> 
												<script>document.prodForm.prod_manufac.value="<?php echo $obj->prod_manufac; ?>";</script>
																								
												<div  id="wrp_company" style="display: none;">
													<a class="col-form-label" data-toggle="modal" style="color:#0e4571" 
													data-target="#modal_new_company" href="" data-popup="tooltip" title="New Company"> 
													<i class="icon-plus-circle2 text-warning"></i> New Company</a>
												</div>	
												
													
											</div>
											<label class="col-lg-2 col-form-label">Salt</label>
											<div class="col-lg-4">
												<input type="text" name="prod_salt" id="prod_salt" class="form-control text-capitalize" maxlength="50" value="<?php echo $obj->prod_salt; ?>">
											</div>
										</div>
										<div class="form-group row">
											<label class="col-lg-2 col-form-label">Controlled Drug</label>
											<div class="col-lg-4">												
												<input type="checkbox" class="form-check-input form-check-input-switch" name="prod_controlled_drug" id="prod_controlled_drug"  data-on-text="Yes" data-off-text="No" data-on-color="info" data-off-color="default" value="1" <?php if($obj->prod_controlled_drug == 1){?>checked <?php }?> >
											</div>
											<label class="col-lg-2 col-form-label">Rack</label>
											<div class="col-lg-4">
												<input type="text" name="prod_rack" id="prod_rack" class="form-control number_only"
												maxlength="2" value="<?php echo $obj->prod_rack; ?>">
											</div>
											
										</div>
									</fieldset>
								</div>
							</div>
						</div>
						<div class="card-footer text-center"> 
							<?php if($_REQUEST["prod_id"]!='') { ?>
								  <INPUT class="btn btn-custom" type="submit" name="UPDATE" value="Update">
								  <INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
								  <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['prod_id'];?>">
								  <?php }else{ ?>
								  <INPUT class="btn btn-custom" type="submit" name="SAVE" value="Save">
								  <INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
								  <input type="hidden" name="txtHid" value="0">
							  <?php } ?>
						</div>
					</div>
					</form>
					<!-- End of This Form UI  --->
					<br><br>
					<?php 
						include ('modal_new_product_generic.php'); 
						include ('modal_new_uom.php'); 
						include ('modal_new_productgroup.php'); 						
						include ('modal_new_hsn.php'); 
						include ('modal_new_company.php'); 
					?>
							
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

<script type="text/javascript">

$(function() {
	var prdgeneric_flg = 0;
	var grp_flg = 0;
	var hsn_flg = 0;
	var uom_flg = 0;
	var comp_flg = 0;
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
	var _validImageExtensions = [".jpg",".gif", ".jpeg",".png"];
		$('.image_only').change(function(){
		var name = $(this).attr('name');
		var max_size = parseInt($(this).attr('data-size'));
		$(this).closest('.form-group').removeClass('has-danger');
		$('#'+name+'_error').html('');
		var size = parseFloat(this.files[0].size / 1024).toFixed(2);
		if(size>max_size){
			$(this).closest('.form-group').addClass('has-danger');
			$('#'+name+'_error').removeClass('text-muted');
			$('#'+name+'_error').html('Sorry, filesize should be lessthen '+max_size+' KB');
			$(this).attr('data-submit',0);
			return false;
		}			
		var sFileName = $(this).val();
		 if (sFileName.length > 0) {
			var blnValid = false;
			for (var j = 0; j < _validImageExtensions.length; j++) {
				var sCurExtension = _validImageExtensions[j];
				if (sFileName.substr(sFileName.length - sCurExtension.length, sCurExtension.length).toLowerCase() == sCurExtension.toLowerCase()) {
					blnValid = true;
					break;
				}
			}				 
			if (!blnValid) {
				$(this).closest('.form-group').addClass('has-danger');
				$('#'+name+'_error').html("Sorry, filetype is invalid, allowed extensions are: " + _validImageExtensions.join(", "));
				$(this).attr('data-submit',0);
				return false;
			}
		}			
		$(this).attr('data-submit',1);
	});
	
	$('#prod_purchase_price').change(function (){
		if($(this).val() =='')
		{	$('#final_sales_price').val('');
			$('#prod_sales_price').val('');
		}
		else
		{
			var sale_price = parseFloat($(this).val() * $('#sales_rate_per').val()) / 100;
			var final_sales_price = parseFloat($(this).val()) + sale_price;
			$('#final_sales_price').val(final_sales_price.toFixed(2));			
			$('#prod_sales_price').val(final_sales_price.toFixed(2));
		}
	});	
 	
	$('#prod_generic_id').on("select2:open", function () {
      prdgeneric_flg++;
      if (prdgeneric_flg == 1) 
	  {
        $this_html = jQuery('#wrp_prdgeneric').html();
        $(".select2-results").append("<div class='select2-results__option' style='background-color:#bde2ff'>" + 
		$this_html + "</div>");
		var a = $(this).data('select2');
		
		$(".select2-results").on('click', function (b) {
              a.trigger('close');
		 });
      }
    });
	
	
	$('#prod_group_id').on("select2:open", function () {
      grp_flg++;
      if (grp_flg == 1) 
	  {
        $this_html = jQuery('#wrp_group').html();
        $(".select2-results").append("<div class='select2-results__option' style='background-color:#bde2ff'>" + 
		$this_html + "</div>");
		var a = $(this).data('select2');
		
		$(".select2-results").on('click', function (b) {
              a.trigger('close');
		 });
      }
    });
	
	$('#prod_gst').on("select2:open", function () {
      hsn_flg++;
      if (hsn_flg == 1) 
	  {
        $this_html = jQuery('#wrp_hsn').html();
        $(".select2-results").append("<div class='select2-results__option' style='background-color:#bde2ff'>" + 
		$this_html + "</div>");
		var a = $(this).data('select2');
		
		$(".select2-results").on('click', function (b) {
              a.trigger('close');
		 });
      }
    });
	
	$('#prod_uom').on("select2:open", function () {
      uom_flg++;
      if (uom_flg == 1) 
	  {
        $this_html = jQuery('#wrp_uom').html();
        $(".select2-results").append("<div class='select2-results__option' style='background-color:#bde2ff'>" + 
		$this_html + "</div>");
		var a = $(this).data('select2');
		
		$(".select2-results").on('click', function (b) {
              a.trigger('close');
		 });
      }
    });
	

    $('#prod_manufac').on("select2:open", function () {
      comp_flg++;
      if (comp_flg == 1) 
	  {
        $this_html = jQuery('#wrp_company').html();
        $(".select2-results").append("<div class='select2-results__option' style='background-color:#bde2ff'>" + 
		$this_html + "</div>");
		var a = $(this).data('select2');
		
		$(".select2-results").on('click', function (b) {
              a.trigger('close');
		 });
      }
    });
	
});
	
</script>

<script language="javascript" type="text/javascript">
function fnValidate()
{
	//alert("validations..");
	
	if(notSelected(document.prodForm.prod_generic_id,"Generic...!")){ return false; }
	if(isNull(document.prodForm.prod_name,"Product name...!")){ return false; }
	if(isNull(document.prodForm.prod_code,"Product Code...!")){ return false; }	
	if(notSelected(document.prodForm.prod_group_id,"Product Group...!")){ return false; }
	if(notSelected(document.prodForm.prod_gst,"Product HSN...!")){ return false; }
	if(isNull(document.prodForm.prod_desc,"Product Description...!")){ return false; }	
	if(notSelected(document.prodForm.prod_uom,"Product UOM...!")){ return false; }
	if(notSelected(document.prodForm.prod_manufac,"Product Manufacturer...!")){ return false; }
	
	
	var sales = parseFloat($('#prod_sales_price').val());
	var final_sales = parseFloat($('#final_sales_price').val());
	
	if(isNaN(sales))sales=0;
	if(isNaN(final_sales))final_sales=0;
	
	
	if( sales <  final_sales){
		alert("Please Check Sales Price.\nIt should be min. "+$('#sales_rate_per').val()+" % more than Purchase..!");
		return false;
	}
	document.prodForm.submit();

}



</script>
</html>
