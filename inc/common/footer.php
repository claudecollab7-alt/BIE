		
<!-- Footer -->
<div class="navbar navbar-expand-lg navbar-light">
	<div class="text-center d-lg-none w-100">
		<button type="button" class="navbar-toggler dropdown-toggle" data-toggle="collapse" data-target="#navbar-footer">
			<i class="icon-unfold mr-2"></i>
			Footer
		</button>
	</div>

	<div class="navbar-collapse collapse" id="navbar-footer">
		<span class="navbar-text">
			<?php echo PAGE_COPYRIGHT; ?>. All Rights Reserved.
		</span>		
		<!--<div class="align=right ml-lg-auto" id="div_timer">				
		</div>-->
	</div>
</div>

<!--	<script type="text/javascript">
		$( function() {	
			$("#div_timer").load("loadtimer.php");

			$.sessionTimeout({
				heading: 'h6',
				title: 'Session Timeout',
				message: 'Your session is about to expire. Do you want to stay connected?',
				ignoreUserActivity: false,
				keepAliveUrl: '/',				
				warnAfter:  1200000, 
				redirAfter: 1440000,			
				redirUrl: 'logout.php',
				logoutUrl: 'logout.php',
				keepBtnClass: 'btn btn-danger',
				keepBtnText: 'Extend session',
				logoutBtnClass: 'btn btn-light',
				logoutBtnText: 'Logout',	
				countdownBar: true,
				countdownMessage: 'Redirecting in {timer} seconds.'				
			});

			setInterval(function(){
				//alert("");
				$("#div_timer").load("loadtimer.php");
			}, 1200000);  //10000 means 10 seconds
						
		});
	</script>-->
<!-- /footer -->
				
