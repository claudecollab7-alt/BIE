<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

// 2026-10-13 dashboard tiles for all-branch approvers in Task Settings - were the tbl_task_user ids
// 2026-10-13 GRN Payment Pending tile counts what its page lists (bill and payment both not done)
// 2026-10-13 a tile for every verify / approve job the user holds, any branch; count limited to their branches (task_queues.php)
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
						<?php if (fnTaskCan('PURCHASE_ORDER', 'A')) { ?>
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
						<?php
						// a tile for every verify / approve job the user holds; count limited to the branches they hold it for
						$tile_no = 0;
						foreach (fnTaskQueues() as $q_key => $q) {
							if (!fnQueueVisible($q_key)) {
								continue;
							}
							$tile_no++;
						?>
						<div class="col-md-3">
							<div class="card card-body <?php echo $tile_no % 2 ? 'bg-blue-400' : 'bg-purple-400'; ?> has-bg-image">
								<div class="media">
									<div class="media-body">
										<h3 class="mb-0"><?php echo fnQueueCount($conn, $q_key); ?></h3>
										<span class="text-uppercase font-size-xs"><?php echo $q['label']; ?></span>
									</div>
									<div class="ml-3 align-self-center">
										<a href="<?php echo $q['page']; ?>"><i class="<?php echo $q['icon']; ?> opacity-75 text-white"<?php echo isset($q['icon_size']) ? ' style="font-size:' . $q['icon_size'] . '"' : ''; ?>></i></a>
									</div>
								</div>
							</div>
						</div>
						<?php } ?>
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