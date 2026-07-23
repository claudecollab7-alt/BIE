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


		$(document).ready(function() {
			$('#branch_id_login').change(function() {
				var selectedBranch = $(this).val();

				if (selectedBranch !== '') {
					// Make an AJAX call to the server
					$.ajax({
						type: "POST",
						url: "inc/cis_ajax/jquery_multi_login_direct.php",
						data: {
							branch_id: selectedBranch
						},

					}).done(function(response) {
						location.reload();
						// string = response.split("~");
						// $("#txt_username").val(string[1]);
						// $("#txt_userpwd").val(string[2]);
						// $("#admin_multi_login").val("_admin_only");
					});
				}
			});
		});
	});
</script>

<div class="navbar navbar-expand-md navbar-light">
	<!-- Header with logos -->
	<div class="navbar-header navbar-dark d-none d-md-flex align-items-md-center">
		<div class="navbar-brand navbar-brand-md">
			<a href="home.php" class="d-inline-block">
				<img src="img/BIE_logo.png" alt="">
			</a>
		</div>

		<div class="navbar-brand navbar-brand-xs">
			<a href="home.php" class="d-inline-block">
				<img src="img/BIE_logo.png" alt="">
			</a>
		</div>
	</div>
	<!-- /header with logos -->

	<!-- Mobile controls -->
	<div class="d-flex flex-1 d-md-none">
		<div class="navbar-brand mr-auto">
			<a href="home.php" class="d-inline-block">
				<img src="img/BIE_logo.png" alt="">
			</a>
		</div>

		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-mobile">
			<i class="icon-tree5"></i>
		</button>

		<button class="navbar-toggler sidebar-mobile-main-toggle" type="button">
			<i class="icon-paragraph-justify3"></i>
		</button>
	</div>
	<!-- /mobile controls -->


	<!-- Navbar content -->
	<div class="collapse navbar-collapse" id="navbar-mobile">
		<ul class="navbar-nav">
			<li class="nav-item">
				<a href="#" class="navbar-nav-link sidebar-control sidebar-main-toggle d-none d-md-block">
					<i class="icon-paragraph-justify3"></i>
				</a>
			</li>
		</ul>

		<span class="badge bg-pink-400 badge-pill ml-md-3 mr-md-auto"><?php echo $_SESSION['_finyr']; ?></span>
		<?php if ($_SESSION['_user_type'] == 'A') { ?>
			<div class="">
				<div class="mt-0">
					<select class="form-select form-control select-search form-control-lg mb-2 select-search" name="branch_id_login" id="branch_id_login" data-placeholder="Select an option">
						<option value="">Select Branch</option>
						<?php
						echo $dbconn->fnFillComboFromTable_Where("branch_id", "branch_name", "mst_branch", "branch_name", " WHERE branch_id != '" . $_SESSION['_user_branch'] . "' AND branch_status = 1");
						?>
					</select>
				</div>
			</div>
		<?php } ?>

		<span class="ml-md-3 mr-md-auto f-20 font-weight-semibold text-blue-800"><i class="icon-pin-alt m-1"></i>
			<?php echo $dbconn->GetSingleReconrd("mst_branch", "branch_name", "branch_id", $_SESSION['_user_branch']); ?>&nbsp;&nbsp;</span>

		<ul class="navbar-nav">
			<li class="nav-item dropdown dropdown-user">
				<a href="#" class="navbar-nav-link d-flex align-items-center dropdown-toggle" data-toggle="dropdown">
					<?php
					if (isset($_SESSION['_usr_avatar']) && $_SESSION['_usr_avatar'] != '')
						echo '<img src="project_img/usr_avatar/' . $_SESSION['_usr_avatar'] . '" class="rounded-circle mr-2" height="34" alt="">';
					else
						echo '<img src="img/user_avatar_holder.png" class="rounded-circle mr-2" height="34" alt="">';
					?>
					<span><?php echo isset($_SESSION['_username']) ? $_SESSION['_username'] : ''; ?></span>
				</a>

				<div class="dropdown-menu dropdown-menu-right">
					<a href="myprofile.php" class="dropdown-item"><i class="icon-user-plus"></i> My profile</a>
					<!--a href="#" class="dropdown-item"><i class="icon-comment-discussion"></i> Messages <span class="badge badge-pill bg-indigo-400 ml-auto">58</span></a-->
					<div class="dropdown-divider"></div>
					<a href="#" class="dropdown-item"><i class="icon-cog5"></i> Account settings</a>
					<a href="logout.php" class="dropdown-item"><i class="icon-switch2"></i> Logout</a>
				</div>
			</li>
		</ul>
	</div>
	<!-- /navbar content -->

</div>