<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");


$conn = new dbconnect();
$dbconn= new dbhandler();

/*
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);*/

if (isset($_POST['SAVE'])){
	
		$is_exist = $dbconn->GetSingleReconrd("tbl_user","usr_id","usr_status = 1 AND usr_email",$_REQUEST['usr_email']);
		if($is_exist != ""){
			$_SESSION['_msg'] = "User details already exist..!";
			header("location:mst_user.php");	
			die();
		}
	
		if ($_FILES['usr_photo']['name']!="")
		{			
			if($_REQUEST["hide_usr_photo"] != "")	
			{		
				removeFile("project_img/usr_photo/".$_REQUEST["hide_usr_photo"]);
			}
			$ext = pathinfo($_FILES['usr_photo']['name'], PATHINFO_EXTENSION);
			$customfilename = $_REQUEST['usr_name'].'_'.$update_id.'.'.$ext;
			$_REQUEST['usr_photo'] = 
			post_img($customfilename, $_FILES['usr_photo']['tmp_name'],"project_img/usr_photo/");
			$_REQUEST['usr_photo_size'] = $_FILES['usr_photo']['size'];
			
			$_SESSION['_usr_avatar'] =$_REQUEST['usr_photo'];
		}
		$_REQUEST['pw_hint'] = $_REQUEST['usr_logpwd'];
		$_REQUEST['usr_logpwd'] = StandardHash($_REQUEST['usr_logpwd']);
		
		$sql = CIS_InsertRecord("tbl_user",$_REQUEST);	
		
		$conn->query($sql);	
		$_REQUEST['usr_id'] = $conn->LastInsertId();
		
		$_SESSION['_msg'] = "New User details has been successfully saved..!";
		
		header("location:mst_users_add.php");

		die();
	
}


if (isset($_POST['UPDATE'])){
	
		try {
		$update_id = $_REQUEST["txtHid"];
		$is_exist = $dbconn->GetSingleReconrd("tbl_user","usr_id","usr_id <> ".$update_id." AND usr_status = 1 AND usr_email",$_REQUEST['usr_email']);
		if($is_exist != ""){
			$_SESSION['_msg'] = "User details already exist..!";
			header("location:mst_user.php");		
			die();
		}
		if ($_FILES['usr_photo']['name']!="")
		{			
			if($_REQUEST["hide_usr_photo"] != "")	
			{		
				removeFile("project_img/usr_photo/".$_REQUEST["hide_usr_photo"]);
			}
			$ext = pathinfo($_FILES['usr_photo']['name'], PATHINFO_EXTENSION);
			$customfilename = $_REQUEST['usr_name'].'_'.$update_id.'.'.$ext;
			$_REQUEST['usr_photo'] = 
			post_img($customfilename, $_FILES['usr_photo']['tmp_name'],"project_img/usr_photo/");
			$_REQUEST['usr_photo_size'] = $_FILES['usr_photo']['size'];
			
			$_SESSION['_usr_avatar'] =$_REQUEST['usr_photo'];
		}
		else
		{
			$_REQUEST['usr_photo'] = $_REQUEST["hide_usr_photo"];
		}
		$_REQUEST['pw_hint'] = $_REQUEST['usr_logpwd'];
		$_REQUEST['usr_logpwd'] = StandardHash($_REQUEST['usr_logpwd']);
	
		$sql = CIS_UpdateRecord("tbl_user",$_REQUEST);
		 
		$sql = StrTruncate($sql,1)." WHERE usr_id = ".$update_id;
		
		//echo $sql;exit;
		$conn->query($sql);
		
		echo $_SESSION['_msg'] = "User details has been successfully updated..!";
		}catch(Exception $e){
			echo $_SESSION['_msg_err'] = $e;
		}
	
		header("location:mst_user.php");

		die();
	
}
$img_name = "user_avatar_holder.png";
$img_path = "img/user_avatar_holder.png";	
$img_size = 256; //kb	
if ($_REQUEST["id"]!=""){
	
	$converter = new Encryption;
	$url_data = $converter->decode($_REQUEST['id']);
	$url_data = explode("~",$url_data);
    if($url_data[1] == $_SESSION['_user_id']){
		$_REQUEST['usr_id'] = $url_data[0];
	}else{
		$_SESSION['_msg_err'] = "You don\'t have permission..!";	
		header("location:mst_users.php");			
		die();
	}
	
	$result = $conn->query("SELECT * FROM tbl_user WHERE usr_id=".$_REQUEST["usr_id"]);

	if ($result->rowCount()>0){
		$obj = $result->fetch();
		if($obj->usr_photo!=''){
		$img_name = $obj->usr_photo;
		$img_path = "project_img/usr_photo/".$obj->usr_photo;	
		$img_size= $obj->usr_photo_size;
		
		}else {
			$img_name = "user_avatar_holder.png";
			$img_path = "img/user_avatar_holder.png";	
			$img_size = 256; //kb	
		}
		
	}
	
}

?>
<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo PAGE_TITLE; ?> - Create New User</title>

	<?php include_once("inc/common/css-js.php"); ?>		
</head>
<body>
	<!-- Main navbar -->
	<?php include("inc/common/header.php") ?>
	<!-- /main navbar -->

					
	<!-- Page content -->
	<div class="page-content">

		<!-- Main sidebar -->
		<?php  include("inc/common/sidebar.php") ?>
		<!-- /main sidebar -->


		<!-- Main content -->
		<div class="content-wrapper">

			<!-- Page header -->
			<div class="page-header">
				<div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
					<div class="d-flex">
					<div class="breadcrumb">
							<a href="index.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Home</a>
							<a href="#" class="breadcrumb-item"> User Accounts</a>
							<a href="mst_user.php" class="breadcrumb-item"> User List</a>
							<span class="breadcrumb-item active">User Details</span>
						</div>
						<a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
					</div>	
					<!--div class="header-elements d-none">
						<div class="breadcrumb justify-content-center">
							<a class="breadcrumb-elements-item" href="mst_users.php" title="Page Actions"><i class="fas fa-angle-left mr-2"></i>Back</a>
						</div>
					</div-->
				</div>	
			</div>
			<!-- /page header -->



			<!-- Content area -->
			<div class="content pt-0">
				<!-- Dashboard content -->
				<div class="row">
				<div class="col-md-12">	
					<!-- This Form UI Starts here --->
					<form name='thisForm' class="form-horizontal" method='POST' action=""   onSubmit="return fnValidate();" 	
									enctype="multipart/form-data"> 
						<div class="card">						
							<div class="card-header bg-pgheader text-white header-elements-inline">
								<h6 class="card-title">User Details</h6>
									<div class="header-elements">									
									<div class="list-icons">				                		
										<a class="list-icons-item" href="mst_user.php" title="Users List"><i class="icon-arrow-left52 mr-2"></i></a>
										<a class="list-icons-item" data-action="fullscreen"></a>
				                	</div>									
			                	</div>									
							</div>

							<div class="card-body">		
								<div class="row">
									<div class="col-md-6">
										<fieldset>
											<legend class="font-weight-semibold"><i class="icon-reading mr-2"></i>User Details</legend>

											<div class="form-group row">
												<label class="col-lg-4 col-form-label">Name <span class="text-mandatory">*</span></label>
												<div class="col-lg-8">
													<input type="text" name="usr_name" id="usr_name" class="form-control name_only" maxlength="100" value="<?php echo $obj->usr_name; ?>">
													<input type="hidden" name="usr_type" id="usr_type" class="form-control name_only"  value="S">
													<input type="hidden" name="usr_group" id="usr_group" class="form-control name_only"  value="B">
													<input type="hidden" name="usr_access" id="usr_access" class="form-control name_only"  value="1">
													
												</div>
											</div>	
											<div class="form-group row">
												<label class="col-lg-4 col-form-label">Branch <span class="text-mandatory">*</span></label>
												<div class="col-lg-8">
                                                    <select name="branch_id" id="branch_id" data-placeholder="Choose a Branch.." class="form-control select-search">
                                                        <option value=""></option>
                                                        <?php
                                                        $dbconn = new dbhandler();
                                                        echo $dbconn->fnFillComboFromTable_Where("branch_id", "branch_name", "mst_branch", "branch_id", " WHERE branch_status = '1'"); ?>
                                                    </select>
                                                    <script>
                                                        document.thisForm.branch_id.value = "<?php echo $obj->branch_id; ?>";
                                                    </script>
                                                </div>												
											</div>
											
											<div class="form-group row">
												<label class="col-lg-4 col-form-label">Mobile No <span class="text-mandatory">*</span></label>
												<div class="col-lg-8">
													<input type="text" name="usr_mobile" id="usr_mobile" minlength="10" maxlength="21" class="form-control number_only_comma" value="<?php echo $obj->usr_mobile; ?>">
												</div>
											</div>
											<div class="form-group row">
												<label class="col-lg-4 col-form-label">Email ID <span class="text-mandatory">*</span></label>
												<div class="col-lg-8">
													<input type="email" name="usr_email" id="usr_email" maxlength="150" class="form-control email_only" value="<?php echo $obj->usr_email; ?>">
												</div>
											</div>	
											
										</fieldset>
									</div>
									
									<div class="col-md-6">
										<fieldset>
											<legend class="font-weight-semibold"><i class="icon-image2 mr-2"></i>Profile photo</legend>
											<div class="form-group row">
												<div class="col-lg-12">
													<input type="file" id="usr_photo" name="usr_photo" class="file-input-overwrite image_only" data-size="250" data-submit='1' data-fouc >
													<div id="usr_photo_error" class="cis-feedback help-block form-text text-muted">Accepted formats: jpeg, png, jpg. </div>
													<input type="hidden" name="hide_usr_photo" value="<?php echo $obj->usr_photo; ?>">
												</div>
											</div>
										</fieldset>
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-12">
										<fieldset>
											<legend class="font-weight-semibold"><i class="icon-key mr-2"></i>Login Details</legend>

											<div class="form-group row">
												<label class="col-lg-2 col-form-label">Login Name <span class="text-mandatory">*</span></label>
												<div class="col-lg-4">
													<input type="text" name="usr_logname" id="usr_logname" maxlength="50" class="form-control splname_only" value="<?php echo $obj->usr_logname; ?>">
												</div>
												<label class="col-lg-2 col-form-label">Password <span class="text-mandatory">*</span></label>
												<div class="col-lg-4">
													<input type="password" name="usr_logpwd" id="usr_logpwd" maxlength="50" class="form-control" value="<?php echo $obj->pw_hint; ?>">
												</div>
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
								  <?php }else{ ?>
								  <INPUT class="btn btn-custom" type="submit" name="SAVE" value="Save">
								  <INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
								  <input type="hidden" name="txtHid" value="0">
							 <?php } ?>								
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
</body>

<script type="text/javascript">

$(function() {
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
  
  	$('#inst_state').change(function(){
			
			var state_id = $('#inst_state').val();
			
			$.ajax({
			type: "POST",
			url: "assets/cis_ajax/jquery_select_city.php",
			data: {state_id:state_id}
			}).done(function( msg ) {
				
				$('#inst_city option').remove();
				
                var dataArr = msg.split('#');
                $.each(dataArr, function(i,element){
					if(dataArr[i]!=""){
						var dataArr2 = dataArr[i].split('~');
						$('#inst_city').append("<option value='"+dataArr2[0]+"'>"+dataArr2[1]+"</option>");
					}
                });
				
			  	$("#inst_city").val('');
				//show_sc_other(0);
				
			});
	});
	
	$('#inst_city').change(function(){
			var city_id = $('#inst_city').val();
			if(city_id == 0){
				$('#cityrow').slideDown(300);	
			}else{
				$('#city_name').val('');	
				$('#cityrow').slideUp(300);	
			}
	});
	
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
	
	
	 // Modal template
        var modalTemplate = '<div class="modal-dialog modal-lg" role="document">\n' +
            '  <div class="modal-content">\n' +
            '    <div class="modal-header align-items-center">\n' +
            '      <h6 class="modal-title">{heading} <small><span class="kv-zoom-title"></span></small></h6>\n' +
            '      <div class="kv-zoom-actions btn-group">{toggleheader}{fullscreen}{borderless}{close}</div>\n' +
            '    </div>\n' +
            '    <div class="modal-body">\n' +
            '      <div class="floating-buttons btn-group"></div>\n' +
            '      <div class="kv-zoom-body file-zoom-content"></div>\n' + '{prev} {next}\n' +
            '    </div>\n' +
            '  </div>\n' +
            '</div>\n';

        // Buttons inside zoom modal
        var previewZoomButtonClasses = {
            toggleheader: 'btn btn-light btn-icon btn-header-toggle btn-sm',
            fullscreen: 'btn btn-light btn-icon btn-sm',
            borderless: 'btn btn-light btn-icon btn-sm',
            close: 'btn btn-light btn-icon btn-sm'
        };

        // Icons inside zoom modal classes
        var previewZoomButtonIcons = {
            prev: '<i class="icon-arrow-left32"></i>',
            next: '<i class="icon-arrow-right32"></i>',
            toggleheader: '<i class="icon-menu-open"></i>',
            fullscreen: '<i class="icon-screen-full"></i>',
            borderless: '<i class="icon-alignment-unalign"></i>',
            close: '<i class="icon-cross2 font-size-base"></i>'
        };

        // File actions
        var fileActionSettings = {
            zoomClass: '',
            zoomIcon: '<i class="icon-zoomin3"></i>',
            dragClass: 'p-2',
            dragIcon: '<i class="icon-three-bars"></i>',
            removeClass: '',
            removeErrorClass: 'text-danger',
            removeIcon: '<i class="icon-bin"></i>',
            indicatorNew: '<i class="icon-file-plus text-success"></i>',
            indicatorSuccess: '<i class="icon-checkmark3 file-icon-large text-success"></i>',
            indicatorError: '<i class="icon-cross2 text-danger"></i>',
            indicatorLoading: '<i class="icon-spinner2 spinner text-muted"></i>'
        };
		
	$('#usr_photo').fileinput({
            browseLabel: 'Browse',
            browseIcon: '<i class="icon-file-plus mr-2"></i>',
            uploadIcon: '<i class="icon-file-upload2 mr-2"></i>',
            removeIcon: '<i class="icon-cross2 font-size-base mr-2"></i>',
            layoutTemplates: {
                icon: '<i class="icon-file-check"></i>',
                modal: modalTemplate
            },
            initialPreview: [
                '<?php echo $img_path;?>'
            ],
            initialPreviewConfig: [
                {caption: '<?php echo $img_name; ?>', size: <?php echo $img_size;?>, key: 1, url: 'img/'}
            ],
            initialPreviewAsData: true,
            overwriteInitial: true,
            previewZoomButtonClasses: previewZoomButtonClasses,
            previewZoomButtonIcons: previewZoomButtonIcons,
            fileActionSettings: fileActionSettings
        });
		
  
});
	
</script>

<script language="javascript" type="text/javascript">
function fnValidate(){

//alert("validations..");

	if(isNull(document.thisForm.usr_name,"name...!")){ return false; }
	
	if(isNull(document.thisForm.branch_id,"Branch...!")){ return false; }
	
	if(isNull(document.thisForm.usr_mobile,"contact no...!")){ return false; }
	if(isMultiPhone(document.thisForm.usr_mobile,"contact no...!")){ return false; }

	if(isNull(document.thisForm.usr_email,"email id...!")){ return false; }			
	if(notEmail(document.thisForm.usr_email,"email id..")){ return false; }
	
		if(document.thisForm.usr_photo.value != ""){
			if(notImageFile(document.thisForm.usr_photo,"Photo..")){ return false; } 	
			var datasub = $('#usr_photo').attr('data-submit');
			if(datasub == 0){
				alert("Please select user photo correctly..!");return false;
			}
		}
			
	if(isNull(document.thisForm.usr_logname,"logname...!")){ return false; }
	if(isPassword(document.thisForm.usr_logpwd)){ return false; }		

	document.thisForm.submit();

}

</script>
<script>
		autosize(document.querySelectorAll('textarea'));
</script>
</html>
