<?php
ob_start();

session_start();
require_once("inc/common/userclass.php");

$conn = new dbconnect();	
$dbconn= new dbhandler();	

if(isset($_POST['LOGIN']))
{
	$sql = "SELECT * FROM tbl_user WHERE  usr_status = 1 AND  usr_access=1 AND usr_logname = '".$_REQUEST['txt_username']."' AND usr_logpwd  
			LIKE BINARY '".StandardHash($_REQUEST['txt_userpwd'])."' ";
	
	$res = $conn->query($sql);
	$no = $res->rowCount();	
	if ($no>0) 
	{		
		$obj = $res->fetch(PDO::FETCH_OBJ);		
		$_SESSION['_user']="crm_user";
		$_SESSION['_user_id']=$obj->usr_id;
		$_SESSION['_user_name']=$obj->usr_name;
		$_SESSION['_user_group']=$obj->usr_group;	
		$_SESSION['_user_type']=$obj->usr_type;	
		$_SESSION['_user_branch']=$obj->branch_id;	
		$_SESSION['session_id'] = date("Ymd").date("His");
		$_SESSION['_usr_avatar'] = $obj->usr_avatar;		
		$_SESSION['_msg']="";
		$_SESSION['_msg_err']="";
		$_SESSION['timer'] = time();		
		$_SESSION['_finyr']=$dbconn->GetSingleReconrd("mst_finyear","finyr_name","finyr_active",1);
		header("location:home.php");	
		die();
	}
	else
	{		
		$_SESSION['_user'] = "";
		$_SESSION['_user_id'] = "";		
		$_SESSION['_user_name'] = "";	
		$_SESSION['_user_group'] = "";	
		$_SESSION['_user_type'] = "";				
		$_SESSION['_user_branch']= "";	
		$_SESSION['session_id'] = "";
		$_SESSION['_usr_avatar'] = "";			
		$_SESSION['_msg']="Invalid User Name / Password. <br>Please Try Again..!";	
		$_SESSION['_msg_err']="";
		
		header("location:index.php");
		die();
	}
	
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Welcome to <?php echo PAGE_TITLE; ?> </title>
	
	<?php include_once("inc/common/css-js.php"); ?>	
	<style>
	body {
		
		background-repeat: no-repeat;		
		background-size: cover;
		background-position: center;
	}
</style>
</head>

<body>

	<!-- Page content -->
	<div class="page-content">

		<!-- Main content -->
		<div class="content-wrapper">

			<!-- Content area -->
			<div class="content d-flex justify-content-center align-items-center">

				<!-- Login card -->				
				<form name="thisForm" class="login-form" method="post" action="index.php" onSubmit="return fnValidate();">
					<div class="card mb-0">
						<div class="card-body">
							<div class="text-center mb-3">
								<!--i class="icon-reading icon-2x text-slate-300 border-slate-300 border-3 rounded-round p-3 mb-3 mt-1"></i-->
								<img src="img/BIE_logo.png" alt="" width="25%" >
								<hr>
								<h5 class="mb-0">Login to your account</h5>
								<span class="d-block text-muted">Enter your credentials below</span>
							</div>

							<div class="form-group form-group-feedback form-group-feedback-left">
								<input type="text" class="form-control" placeholder="Username" name="txt_username" id = "txt_username" value="admin@bie.com">
								<div class="form-control-feedback">
									<i class="icon-user text-muted"></i>
								</div>
							</div>
														
							<div class="form-group form-group-feedback form-group-feedback-left">
								
								<input type="password" class="form-control" placeholder="Password" name="txt_userpwd" id="txt_userpwd">
								<div class="form-control-feedback form-group-feedback-right">	
									<span toggle="#txt_userpwd" class="far fa-eye toggle-password"></span>
								</div>
							</div>
							
							
							<div class="form-group">
								<button type="submit" name="LOGIN" class="btn btn-primary btn-block">Sign in <i class="icon-circle-right2 ml-2"></i></button>
							</div>
							
							<?php								
								if(isset($_SESSION['_msg']) && $_SESSION['_msg']!="")
								{		
									echo '<span class="form-text text-center text-danger">
											<img src="img/icons/icon_msg.gif" width="16" height="16" alt=""> '.$_SESSION['_msg'] ."</span>";
										
									$_SESSION['_msg'] = "";		
								}
							?>
						</div>
					</div>
					
					<span class="form-text text-center" style="color:#a7a7a7">powered by <a href="http://www.tulipsmedia.com" target="_blank" style="color:#a7a7a7"><br><img class="pt-1" src="img/tulips-media.png" alt="Tulips Media"></span>
				</form>
				<!-- /login card -->

			</div>
			<!-- /content area -->

		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->

</body>

<script language="javascript">

	$(".toggle-password").click(function() 
	{	
	  $(this).toggleClass("fa-eye fa-eye-slash");
	  
	  var input = $($(this).attr("toggle"));
	  if (input.attr("type") == "password") {
		input.attr("type", "text");
	  } else {
		input.attr("type", "password");
	  }
	});


	function fnValidate()
	{
		with(document.thisForm)
		{
			if(isNull(txt_username,"the User Name"))return false;
			if(isNull(txt_userpwd,"the Password"))return false;			
		}
	}
</script>
	
</html>
