<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
// require_once ("inc/common/dbrowiterator.php");	

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

// if (!isset($_REQUEST['branch_id']) || $_REQUEST['branch_id'] == '') {
//     $_REQUEST['branch_id'] = 2	;
// }
//  ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$_SESSION['_admin_multi_login']="";
if(isset($_POST['SAVE']))
{
	echo $sql = "SELECT * FROM tbl_user WHERE  usr_status = 1 AND  usr_access=1 AND usr_logname = '".$_REQUEST['txt_username']."' AND usr_logpwd = '".trim($_REQUEST['txt_userpwd'])."' ";
	
	$res = $conn->query($sql);
	$no = $res->rowCount();	
	// print_r($_REQUEST['txt_username']);
	// print_r($_REQUEST['txt_userpwd']);
	// die();
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
		$_SESSION['_admin_multi_login']=$_REQUEST['admin_multi_login'];
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
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<title><?php echo PAGE_TITLE; ?> - Item Price History </title>
	<link href="css/main.css" rel="stylesheet" type="text/css" />
	<!--[if IE 8]><link href="css/ie8.css" rel="stylesheet" type="text/css" /><![endif]-->

	<?php include_once("inc/common/css-js.php"); ?>
   

<!-- Include your JavaScript file -->
<script src="js/your_script.js"></script>

	<script type="text/javascript">
		$(document).ready(function() {
			<?php
			if (isset($_SESSION['_msg']) && $_SESSION['_msg'] != "") {
				echo "$.jGrowl('" . $_SESSION['_msg'] . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-success', position: 'bottom-right', life:'2000', header: 'Success!' });";
				$_SESSION['_msg'] = "";
			}
			if (isset($_SESSION['_msg_err']) && $_SESSION['_msg_err'] != "") {
				echo "$.jGrowl('" . $_SESSION['_msg_err'] . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-danger', position: 'top-right', shutdown:'3000', header: 'Error!' });";
				$_SESSION['_msg_err'] = "";
			}
			?>
			

            $(document).ready(function () {
                $('#branch_id').change(function () {
                    var selectedBranch = $(this).val();

                    if (selectedBranch !== '') {
                        // Make an AJAX call to the server
                        $.ajax({
                            type: "POST",
                            url: "inc/cis_ajax/jquery_multi_login.php",
                            data: { branch_id: selectedBranch },
                         
                        }).done(function(response) {
							string = response.split("~");
							$("#txt_username").val(string[1]);
							$("#txt_userpwd").val(string[2]);
							$("#admin_multi_login").val("_admin_only");
			            });
                    }
                });
            });

                
						

			<?php
			//if ($_SESSION['_user_type'] == 'A')
			//{ 
			?>
			//$('.hide_price').show();
			<?php	//}else{ 
			?>
			//$('.hide_price').hide();
			<?php	//}  
			?>

		

		});

		function fnValidate() {

			if (notSelected(document.thisForm.branch_id, "Branch..!")) {
			return false;
		     }

			document.thisForm.submit();
		}
	</script>
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
							<a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Dashboard</a>
							<a href="#" class="breadcrumb-item"> Logins</a>
							<span class="breadcrumb-item active">Direct Branch Logins</span>
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

						<!-- Basic datatable -->
						<form name='thisForm' class="form-horizontal" method='POST' action="" onSubmit="return fnValidate();" enctype="multipart/form-data">
							<input type="hidden" name="item_id" id="item_id" value="<?php echo $_REQUEST['item_id']; ?>">
							
							<input type="hidden" class="form-control" placeholder="" name="txt_username" id = "txt_username">
							<input type="hidden" class="form-control" placeholder="" name="txt_userpwd" id="txt_userpwd">
							<input type="hidden" class="form-control" placeholder="" name="admin_multi_login" id="admin_multi_login">



							<div class="card" style="width:50%; margin: 0 auto;">
								<div class="card-header bg-pgheader text-white header-elements-inline">
									<h6 class="card-title">Direct Branch Logins</h6>
									<div class="header-elements">
										<div class="list-icons">
											<a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
											<a class="list-icons-item" href="home.php" title="Home"><i class="icon-arrow-left52 mr-2"></i></a>
											<a class="list-icons-item" data-action="fullscreen"></a>

										</div>
									</div>
								</div>

								<div class="card-body">
									<div class="form-group row pt-2">

										<label class="col-lg-2 col-form-label ">Branch<span class="text-mandatory"> *</span></label>

										<div class="col-lg-4">
                                            <select class="form-select form-control select-search form-control-lg mb-2 select-search" name="branch_id" id="branch_id" data-placeholder="Select an option">
                                                <option value="">Select Branch</option>
                                                <?php
                                                echo $dbconn->fnFillComboFromTable_Where("branch_id", "branch_name", "mst_branch", "branch_name", " WHERE branch_id > 1 AND branch_status = 1");
                                                ?>
                                            </select>
                                            <script>
                                                document.thisForm.supp_id.value = "<?php echo $obj->supp_id; ?>";
                                            </script>
										</div>
									</div>


								</div>
								<div class="card-footer text-center pt-2">
									
										<INPUT class="btn btn-info" type="submit" name="SAVE" id="SAVE" value="Direct Login">
										<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:window.location.href='home.php'">
										<input type="hidden" name="txtHid" value="0">
									
								</div>


							</div>

						</form>

					</div>

					<!-- End of This Form UI  --->

				</div>

				<!-- /dashboard content -->
			</div>
			<!-- /content area -->
			<?php include("inc/common/footer.php") ?>


		</div>

	</div>

	</div>


	<!-- /page content -->
</body>

</html>