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

?>
<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo PAGE_TITLE; ?> - List of Users</title>

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
							<a href="#" class="breadcrumb-item"> User Accounts</a>
							<span class="breadcrumb-item active">User List</span>
						</div>
						<a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
					</div>
					<!--div class="header-elements d-none">
						<div class="breadcrumb justify-content-center">
							<a class="breadcrumb-elements-item" href="mst_users_add.php" title="Creat New User"><i class="fas fa-plus-square mr-2"></i>New User</a>
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
					
					<!-- Basic datatable -->
				<div class="card">
					<div class="card-header bg-pgheader text-white header-elements-inline">
						<h6 class="card-title">List of Users</h6>
						<div class="header-elements">									
							<div class="list-icons">				                		
								<a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
								<a class="list-icons-item" href="mst_users_add.php" data-popup='tooltip' title="Create New User"><i class="icon-user-plus mr-2"></i></a>
								<a class="list-icons-item" data-action="fullscreen"></a>
								
							</div>									
						</div>	
					</div>

					<table class="table table-sm table-hover table-bordered" id="usrTable">
						<thead>
							<tr class="bg-table-header">
								<th>Image</th>
								<th>Name</th>
								<th>Email</th>
								<th>Mobile No</th>
								<th>Branch</th>
								<th class="text-center">Actions</th>
							</tr>
						</thead>
						<tbody>
						<?php
							/*a.inst_type != 'C' AND */
							$sql = "SELECT a.* FROM tbl_user a WHERE a.usr_type != 'A' AND a.usr_status = 1 ";
							//echo $sql;
							
							$searchRes1 = $conn->query($sql);	
							$iSno = 1 ;

							while($rs=$searchRes1->fetch())
							{
								
								$converter = new Encryption;
								$token = $converter->encode($rs->usr_id.'~'.$_SESSION['_user_id']);	
								
								echo '<tr>';
								//echo '<td>'.$iSno.'</td>';
								if($rs->usr_photo!=''){
								echo '<td><a href="#">
												<img src="project_img/usr_photo/'.$rs->usr_photo.'" class="rounded-circle" width="42" height="42" alt="">
											</a></td>';
								}else{
								echo '<td><a href="#">
												<img src="img/user_avatar_holder.png" class="rounded-circle" width="42" height="42" alt="">
											</a></td>';	
								}
								$branch_name = $dbconn->GetSingleReconrd("mst_branch","branch_name","branch_id",$rs->branch_id);
								echo '<td>'.$rs->usr_name.'</td>';
								echo '<td>'.$rs->usr_email.'</td>';
								echo '<td>'.$rs->usr_mobile.'</td>';
								echo '<td>'.$branch_name.'</td>';
								echo '<td class="text-center">';
							
								
								if($rs->usr_type=='S')
								{										
									//echo $_SESSION['_user_type'];
									if( $_SESSION['_user_type'] == 'A')
									{										
										
										echo "<a href='mst_users_add.php?id=".$token."' data-popup='tooltip' title='' data-original-title='Edit' ><i class='icon-pencil5 bg-edit mr-2'></i></a>";
										
										echo "<a class='serlink' href='mst_users_rights.php?id=".$token."' data-popup='tooltip' title='' data-original-title='Menu Rights' ><i class='icon-checkbox-checked mr-2'></i></a>";
										
										echo '<a href="javascript:;" class="delete" rel="'.$rs->usr_id.'" data-popup="tooltip" title="" 	data-original-title="Delete"><i class="icon-bin bg-delete mr-2"></i></a>';
									}else{
										echo "<a href='mst_users_add.php?id=".$token."' data-popup='tooltip' title='' data-original-title='Edit' ><i class='icon-pencil5 bg-edit mr-2'></i></a>";
										echo "";											
									}
								}
								else
								{
									if( $_SESSION['_user_type'] == 'A'){		
										echo "<a href='mst_users_add.php?id=".$token."' data-popup='tooltip' title='' data-original-title='Edit' ><i class='icon-pencil5 bg-edit mr-2'></i></a>";
									}
									
									
								}
								
							
								echo '</td>';
								echo '</tr>';
								$iSno++;
								}
							?>
						</tbody>
					</table>
				</div>
				<!-- /basic datatable -->
					
					
					
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
  
	 $('#usrTable').DataTable({
		autoWidth: false,
		"pageLength": 25,
		columnDefs: [{ 
			orderable: false,
			width: 150,
			targets: [ 5 ]
		}]
	
    });
	
	$('#usrTable').on('click', 'a.delete', function (e) {
			e.preventDefault();
			var id = $(this).attr('rel');
			var table = "tbl_user";
			var status = "usr_status";
			var value = "0";
			var where = "usr_id";				
			var nRow = $(this).parents('tr')[0];
			
				$.ajax({
					type:'post',
					url:'inc/cis_ajax/jquery_delete_records.php',
					data: {"id":id,"table":table,"status":status,"value":value,"where":where},
					beforeSend:function(){
						if (confirm('Are your sure want to Delete..?')) {
						} else {
						return false();
						}
					},
					complete:function(){
					},
					success:function(result){
						location.reload();
						 //$('#uomTable').DataTable().row(nRow).remove().draw();
					}
				});	
	});	
  
});
	
</script>

</html>
