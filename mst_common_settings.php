<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

if (isset($_POST['UPDATE'])) {
	try {

		$supp_ids = array();
		if (isset($_REQUEST['supp_id'])) {
			foreach ($_REQUEST['supp_id'] as $key => $value) {
				if ($value != '') {
					$supp_ids[] = $value;
				}
			}
		}

		$stmt = null;
		$stmt = $conn->prepare("UPDATE mst_supplier_new SET discount_apply = 0 WHERE supp_type = 'S' AND supp_status = 1");
		$stmt->execute();

		if (count($supp_ids) > 0) {
			$placeholders = implode(',', array_fill(0, count($supp_ids), '?'));
			$stmt = null;
			$stmt = $conn->prepare("UPDATE mst_supplier_new SET discount_apply = 1 WHERE supp_id IN (" . $placeholders . ")");
			$stmt->execute($supp_ids);
		}

		$_SESSION['_msg'] = "Common Settings successfully updated..!";
	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		$_SESSION['_msg_err'] = $str;
	}

	header("location:mst_common_settings.php");
	die();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo PAGE_TITLE; ?> - Common Settings</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<?php include_once("inc/common/css-js.php"); ?>

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
		});
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
							<a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
							<a href="#" class="breadcrumb-item"> Settings</a>
							<span class="breadcrumb-item active">Common Settings</span>
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

						<form name='thisForm' class="form-horizontal" method='POST' action="">

							<div class="card" style="width:50%; margin: 0 auto;">
								<div class="card-header bg-pgheader text-white header-elements-inline">
									<h6 class="card-title">Common Settings</h6>
									<div class="header-elements">
										<div class="list-icons">
											<a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
											<a class="list-icons-item" data-action="fullscreen"></a>
										</div>
									</div>
								</div>

								<div class="card-body">
									<div class="form-group row pt-2">

										<label class="col-lg-4 col-form-label">Discount Apply Suppliers</label>

										<div class="col-lg-8">
											<select name="supp_id[]" id="supp_id" data-placeholder="Choose Suppliers.." class="select" multiple>
												<?php
												$supplier_query = "SELECT supp_id, supp_name, discount_apply FROM mst_supplier_new WHERE supp_type = 'S' AND supp_status = 1 ORDER BY supp_name";
												$supp_res = $conn->query($supplier_query);
												if ($supp_res->rowCount() > 0) {
													while ($suppObj = $supp_res->fetch()) {
														$selected = '';
														if ($suppObj->discount_apply == 1) {
															$selected = "selected";
														}
														echo '<option value="' . $suppObj->supp_id . '" ' . $selected . '>' . $suppObj->supp_name . '</option>';
													}
												}
												?>
											</select>
										</div>
									</div>


								</div>
								<div class="card-footer text-center pt-2">
									<INPUT class="btn btn-info" type="submit" name="UPDATE" value="UPDATE">
									<INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:window.location.href='home.php'">
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


	<!-- /page content -->
</body>

</html>
