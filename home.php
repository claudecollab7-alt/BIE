<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);


?>
<!DOCTYPE html>

<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<!--meta http-equiv="refresh" content="60" -->
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
							<a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Home</a>
							<span class="breadcrumb-item active">Dashboard</span>
						</div>
						<a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
					</div>

					<div class="header-elements d-none ">
						<div class="breadcrumb justify-content-center ">
							<?php echo date("d-M-Y"); ?>
						</div>
					</div>
				</div>
			</div>
			<!-- /page header -->
			<div class="content pt-2">
				<div class="row">
						<div class="col-md-3">
							<!-- This Form UI Starts here --->
						<?php $po_user = $dbconn->GetSingleReconrd("tbl_task_user","user_id","task_id",1); 
						if($_SESSION['_user_id'] == $po_user){?>
							<!-- <div class="card card-body bg-orange-400 has-bg-image">
								<div class="media">
									<div class="media-body">
										<h3 class="mb-0"><?php echo $dbconn->GetSingleReconrd("mst_branch","COUNT(branch_id)","branch_id > 1 AND branch_status",1); ?></h3>
										<span class="text-uppercase font-size-xs">Logins</span>
									</div>
									<div class="ml-3 align-self-center">
										<a href="admin_multi_logins.php"><i class="fa fa-user-circle  opacity-75 text-white" style="font-size:36px"></i></a>
									</div>
								</div>
							</div> -->
							<?php } ?>

							<!-- End of This Form UI  --->
						</div>
				</div>
				<!-- <hr class="pt-2"> -->
			
			<!-- Content area -->
				<div class="content pt-0">
					<!-- Dashboard content -->
					<div class="row">
						<div class="col-md-3">
							<!-- This Form UI Starts here --->
						<?php $po_user = $dbconn->GetSingleReconrd("tbl_task_user","user_id","task_id",1); 
						if($_SESSION['_user_id'] == $po_user){?>
							<div class="card card-body bg-blue-400 has-bg-image">
								<div class="media">
									<div class="media-body">
										<h3 class="mb-0"><?php echo $dbconn->GetSingleReconrd("tbl_purchase_order","COUNT(po_id)","po_status",3); ?></h3>
										<span class="text-uppercase font-size-xs">Purchase Order InApproval</span>
									</div>
									<div class="ml-3 align-self-center">
										<a href="lst_direct_po_approval.php"><i class="icon-file-text2 icon-3x opacity-75 text-white"></i></a>
									</div>
								</div>
							</div>
							<?php } ?>

							<!-- End of This Form UI  --->
						</div>

						<div class="col-md-3">
							<!-- This Form UI Starts here --->
						<?php $supp_user = $dbconn->GetSingleReconrd("tbl_task_user","user_id","task_id",2); 
						if($_SESSION['_user_id'] == $supp_user){?>
							<div class="card card-body bg-purple-400 has-bg-image">
								<div class="media">
									<div class="media-body">
										<h3 class="mb-0"><?php echo $dbconn->GetSingleReconrd("mst_supplier_new","COUNT(supp_id)","supp_status = '1' AND supp_type = 'S' AND supp_approve_status",0); ?></h3>
										<span class="text-uppercase font-size-xs">Supplier InApproval</span>
									</div>
									<div class="ml-3 align-self-center">
										<a href="lst_supplier.php?approve=0"><i class="icon-bag icon-3x opacity-75 text-white"></i></a>
									</div>
								</div>
							</div>
							<?php } ?>

							<!-- End of This Form UI  --->
						</div>
						<div class="col-md-3">
							<!-- This Form UI Starts here --->
						<?php $po_user = $dbconn->GetSingleReconrd("tbl_task_user","user_id","task_id",3); 
						$approve_user = explode(",", $po_user);
						if (in_array($_SESSION['_user_id'], $approve_user)){?>
							<div class="card card-body bg-blue-400 has-bg-image">
								<div class="media">
									<div class="media-body">
										<h3 class="mb-0"><?php echo $dbconn->GetSingleReconrd("tbl_quotation","COUNT(*)","quo_verify_status = '1' AND quo_approve_status = '0' AND quo_status",1); ?></h3>
										<span class="text-uppercase font-size-xs">Quotation InApproval</span>
									</div>
									<div class="ml-3 align-self-center">
										<a href="lst_quo_approval.php"><i class="icon-file-text2 icon-3x opacity-75 text-white"></i></a>
									</div>
								</div>
							</div>
							<?php } ?>

							<!-- End of This Form UI  --->
						</div>


						<div class="col-md-3">
							<!-- This Form UI Starts here --->
						<?php $po_user = $dbconn->GetSingleReconrd("tbl_task_user","user_id","task_id",3); 
						$approve_user = explode(",", $po_user);
						if (in_array($_SESSION['_user_id'], $approve_user)){?>
							<div class="card card-body bg-purple-400 has-bg-image">
								<div class="media">
									<div class="media-body">
										<h3 class="mb-0"><?php echo $dbconn->GetSingleReconrd("tbl_sales_order","COUNT(*)","pay_status",3); ?></h3>
										<span class="text-uppercase font-size-xs">Credit Sales Order InApproval </span>
									</div>
									<div class="ml-3 align-self-center">
										<a href="lst_so_approval.php"><i class="icon-file-text2 icon-3x opacity-75 text-white"></i></a>
									</div>
								</div>
							</div>
							<?php } ?>

							<!-- End of This Form UI  --->
						</div>

						<div class="col-md-3">
							<!-- This Form UI Starts here --->
							<?php $po_user = $dbconn->GetSingleReconrd("tbl_task_user","user_id","task_id",3); 
							$approve_user = explode(",", $po_user);
							if (in_array($_SESSION['_user_id'], $approve_user)){?>
								<div class="card card-body bg-blue-400 has-bg-image">
									<div class="media">
										<div class="media-body">
											<h3 class="mb-0"><?php echo $dbconn->GetSingleReconrd("tbl_item_details","COUNT(*)","branch_status",0); ?></h3>
										<span class="text-uppercase font-size-xs">New Item InApproval </span>
									</div>
									<div class="ml-3 align-self-center">
										<a href="lst_item_approval.php"><i class="fas fa-database opacity-75 text-white"></i></a>
									</div>
								</div>
							</div>
							<?php } ?>

							<!-- End of This Form UI  --->
						</div>
						<div class="col-md-3">
							<!-- This Form UI Starts here --->
							<?php $po_user = $dbconn->GetSingleReconrd("tbl_task_user","user_id","task_id",3); 
							$approve_user = explode(",", $po_user);
							if (in_array($_SESSION['_user_id'], $approve_user)){?>
								<div class="card card-body bg-purple-400 has-bg-image">
									<div class="media">
										<div class="media-body">
											<h3 class="mb-0"><?php echo $dbconn->GetSingleReconrd("tbl_grn", "COUNT(*) ", "grn_bill_status = '0' AND grn_status", 2); ?></h3>
										<span class="text-uppercase font-size-xs">GRN Payment Pending </span>
									</div>
									<div class="ml-3 align-self-center">
										<a href="grn_payment_details_pending.php"><i class="fas fa-database opacity-75 text-white"></i></a>
									</div>
								</div>
							</div>
							<?php } ?>

							<!-- End of This Form UI  --->
						</div>


					</div>
					<!-- /dashboard content -->
				</div>
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
	$(function() {
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

	});
</script>

</html>