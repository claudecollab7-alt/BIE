<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");

isAdmin();

$conn = new dbconnect();
$dbconn= new dbhandler();



/*ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
*/

if (isset($_POST['UPDATE']))
{
	$update_id = $_REQUEST['txtHid'];
	$mst_exist = $dbconn->GetSingleReconrd("tbl_user","usr_id","usr_id <> ".$update_id." 
						AND usr_status = 1 AND usr_logname",$_REQUEST['usr_logname']);
	
	if($mst_exist != ""){
		$_SESSION['_msg_err'] = "Login Name Already Exist..!";
		header("location:myprofile.php");	
		die();
	}
		
	if ($_FILES['usr_photo']['name']!="")
	{			
		if($_REQUEST["hide_usr_photo"] != "")	
		{		
			removeFile("project_img/usr_avatar/".$_REQUEST["hide_usr_photo"]);
		}
		$ext = pathinfo($_FILES['usr_photo']['name'], PATHINFO_EXTENSION);
		$customfilename ='usravatar_'.$update_id.'.'.$ext;		
		post_img($customfilename, $_FILES['usr_photo']['tmp_name'],"project_img/usr_avatar/");
				
		$_SESSION['_usr_avatar'] = $customfilename;
		$_REQUEST['usr_avatar'] = $customfilename;
	}
	else
	{
		$_REQUEST['usr_avatar'] = $_REQUEST["hide_usr_photo"];
	}
	
	$_REQUEST['pw_hint'] = $_REQUEST['conf_pwd'];
	$_REQUEST['usr_logpwd'] = StandardHash($_REQUEST['conf_pwd']);
		
	try
	{
		$stmt = null;				
		$stmt = null;				
		$stmt = $conn->prepare("UPDATE  tbl_user SET usr_name = :usr_name, usr_logname = :usr_logname, 
								usr_logpwd = :usr_logpwd, usr_mobile = :usr_mobile, usr_email = :usr_email, 
								usr_avatar = :usr_avatar, pw_hint = :pw_hint WHERE usr_id = :usr_id");		
		$data = array(				
			':usr_id' => $update_id,
			':usr_name' => $_REQUEST['usr_name'],			
			':usr_logname' => $_REQUEST['usr_logname'],
			':usr_logpwd' => $_REQUEST['usr_logpwd'],
			':usr_mobile' => $_REQUEST['usr_mobile'],
			':usr_email' => $_REQUEST['usr_email'],				
			':usr_avatar' => $_REQUEST['usr_avatar'],
			':pw_hint' => $_REQUEST['pw_hint']	
		);	
		
		$stmt->execute($data);		
		$_SESSION['_msg']=  "Profile Details Successfully Updated..!";
	}
	catch (Exception $e)
	{		
		$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
		$_SESSION['_msg_err'] = $str;			
	}
	header("location:home.php");	
	die();
}

$img_name = "user_avatar_holder.png";
$img_path = "img/user_avatar_holder.png";	
$img_size = 256; //kb	


if (isset($_SESSION['_user_id']) && $_SESSION['_user_id'] != "")
{
	$result = $conn->query("SELECT * FROM tbl_user WHERE usr_id = '".$_SESSION['_user_id']."'");	
	if ($result->rowCount()>0)
	{
		$obj = $result->fetch(PDO::FETCH_OBJ);	
		
		if($obj->usr_avatar!=''){
			$img_name = $obj->usr_avatar;
			$img_path = "project_img/usr_avatar/".$obj->usr_avatar;	
			$img_size = filesize("project_img/usr_avatar/".$obj->usr_avatar);
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
	<title><?php echo PAGE_TITLE; ?> - Home</title>

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
							<a href="index.html" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
							<span class="breadcrumb-item active"> My Profile</span>
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
					<!-- This Form UI Starts here class="form-validate-jquery" --->
					<form name='profileForm'  method='POST' action=""   onSubmit="return fnValidate();" 	
									enctype="multipart/form-data">
					<input type="hidden" name="txtHid" id="txtHid" value="<?php echo isset($_SESSION['_user_id']) ? $_SESSION['_user_id'] : "" ;?>">  
					
					<div class="card">					
						<div class="card-header bg-pgheader text-white header-elements-inline">						
							<h6 class="card-title">My Profile</h6>							
						</div>

						<div class="card-body">
							<div class="row">
								<div class="col-md-4">
									<fieldset>
										<legend class="font-weight-semibold"><i class="icon-reading mr-2"></i>Personal details</legend>
										<div class="form-group">
											<label>Name <span class="text-mandatory">*</span></label>
											<input type="text" class="form-control" name="usr_name" maxlength="50"
												value="<?php echo $obj->usr_name; ?>">											
										</div>	
										
										<div class="form-group">
											<label>Email <span class="text-mandatory">*</span></label>
											<input type="text" class="form-control" name="usr_email" maxlength="250" 
											value="<?php echo $obj->usr_email; ?>">												
										</div>
										<div class="form-group">
											<label >Mobile Number <span class="text-mandatory">*</span></label>
											<input type="text" class="form-control" name="usr_mobile" 
											onKeyPress="return isNumberKey(event)" maxlength="10"  value="<?php echo $obj->usr_mobile; ?>">
										</div>																					
									</fieldset>
								</div>

								<div class="col-md-4">
									<fieldset>
										<legend class="font-weight-semibold"><i class="icon-user-tie mr-2"></i>Official details</legend>
										<div class="form-group">
											<label>User Name <span class="text-mandatory">*</span></label>
											<div class="form-group form-group-feedback form-group-feedback-right">
												<input type="text" class="form-control" name="usr_logname" maxlength="50"
												id="usr_logname"  value="<?php echo $obj->usr_logname; ?>">	
												<div class="form-control-feedback">													
													<i id="user_name_avl_status" ></i>
												</div>
											</div>
										</div>	
										
										<div class="form-group">
											<label >Password <span class="text-mandatory">*</span></label>
											<input type="password" maxlength="25" class="form-control" required name="pwd" id="pwd" value="<?php echo $obj->pw_hint; ?>">
										</div>
										<div class="form-group">
											<label >Confirm Password <span class="text-mandatory">*</span></label>
											<input type="password" maxlength="25" class="form-control" required name="conf_pwd" id="conf_pwd" value="<?php echo $obj->pw_hint; ?>">
										</div>										
									</fieldset>
								</div>
								
								<div class="col-md-4">
									<fieldset>
										<legend class="font-weight-semibold"><i class="icon-image2 mr-2"></i>User Avatar</legend>
										<div class="form-group">
											<div class="col-lg-12">
												<input type="file" id="usr_photo" name="usr_photo" class="file-input-overwrite image_only" data-size="250" data-submit='1' data-fouc >
												<div id="usr_photo_error" class="cis-feedback help-block form-text text-muted">Accepted formats: jpeg, png, jpg. </div>
												<input type="hidden" name="hide_usr_photo" value="<?php echo $obj->usr_avatar; ?>">
											</div>
										</div>											
									</fieldset>
								</div>
							</div>	
						</div>
						<div class="card-footer text-center"> 
							<INPUT class="btn btn-custom" type="submit" name="UPDATE" value="Update">
							<input type="button" class="btn btn-light" value="Cancel" onClick="javascript:window.location.href='home.php'" />
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


<script language="javascript">
	function fnValidate()
	{	
		if(isNull(document.profileForm.usr_name,"Name..!")){ return false; }
		if(isNull(document.profileForm.usr_email,"Email..!")){ return false; }
		if(isNull(document.profileForm.usr_mobile,"Mobile..!")){ return false; }		
	
		if(isNull(document.profileForm.usr_logname,"User Name..!")){ return false; }
		if(isNull(document.profileForm.pwd,"New Password..!")){ return false; }
		if(isNull(document.profileForm.conf_pwd,"Confirm Password..!")){ return false; }
		if(isPassword(document.profileForm.pwd)){ return false; }
		if(Trim(document.profileForm.pwd.value)!= Trim(document.profileForm.conf_pwd.value)){
			alert("New password and Confirm password does not match"); 
			document.profileForm.conf_pwd.focus();
			return false;
		}
		
		if(document.profileForm.usr_photo.value != ""){
			if(notImageFile(document.profileForm.usr_photo,"Photo..")){ return false; } 	
			var datasub = $('#usr_photo').attr('data-submit');
			if(datasub == 0){
				alert("Please select user photo correctly..!");return false;
			}
		}	
		
	}
	$(function() 
	{				
		
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
		
			
		$('#usr_logname').change(function()
		{
			var usr_logname = $(this).val();
			var usr_id = $("#txtHid").val();
			if(usr_logname != "")
			{
				$.ajax({
				type: "POST",
				url: "inc/cis_ajax/jquery_check_user_name.php",
				data: {'usr_logname':usr_logname,'usr_id':usr_id}
				}).done(function( msg ) 
				{				
					var dataArr = msg.split('~');
					$("#user_name_avl").val(dataArr[1]);
					if(dataArr[1] == 0){						
						 $("#user_name_avl_status").attr("class","icon-checkmark-circle text-success"); 
					}else{						
						 $("#user_name_avl_status").attr("class","icon-cancel-circle2 text-danger"); 
					}
				});
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
	
	/* File Upload */	
	
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
		
	
/* File Upload */


	/*	var validator = $('.form-validate-jquery').validate({
            ignore: 'input[type=hidden], .select2-search__field', // ignore hidden fields
            errorClass: 'validation-invalid-label',
            successClass: 'validation-valid-label',
            validClass: 'validation-valid-label',
            highlight: function(element, errorClass) {
                $(element).removeClass(errorClass);
            },
            unhighlight: function(element, errorClass) {
                $(element).removeClass(errorClass);
            },
            success: function(label) {
                label.addClass('validation-valid-label').text('Success.'); // remove to hide Success message
            },

            // Different components require proper error label placement
            errorPlacement: function(error, element) {
                 error.insertAfter(element);
            },
            rules: {
                pwd: {
                    minlength: 6
                },
                conf_pwd: {
                    equalTo: '#pwd'
                },
				usr_email: {
                    email: true
                },
            }
        });
*/


})	;
</script>

</html>