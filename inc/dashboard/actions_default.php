
<div class="actions">
	<div id="quick-actions" style="margin:18px;">
			<!-- BEGIN DASHBOARD STATS -->
		<div class="widget">
		<div class="row-fluid">
			<div class="span3">
				<div class="dashboard-stat blue">
					<div class="visual">
						<i class="fa fa-tasks"></i>
					</div>
					<div class="details">
						<div class="number">
							<?php 								
								echo GetCountDistinct("tbl_task","task_status = 1 AND to_id",$_SESSION['_userid'],"tc_id,ref_id");
							?>
						</div>
						<div class="desc">
							 Total Pending
						</div>
					</div>
					<a href="mst_task.php" id="all" title="View All"  class="more">
						 View all Pending Tasks<i class="fal fa-arrow-circle-right"></i>
					</a>
				</div>
			</div>
			<div class="span3">
				<div class="dashboard-stat green">
					<div class="visual">
						<i class="fa fa-envelope-o"></i>
					</div>
					<div class="details">
						<div class="number">
							<?php 								
								echo GetCount("tbl_msg","msg_status = 0 AND to_id",$_SESSION['_userid']); 								
							?>					
						</div>
						<div class="desc">
							 Total Messages
						</div>
					</div>
					<a href="mst_msg.php?usr_id=<?php echo $_SESSION['_userid']; ?>" id="all" title="View All"  class="more">
						 View all Messages <i class="fal fa-arrow-circle-right"></i>
					</a>
				</div>
			</div>

			<?php 								
				$enq_approver = GetCount("tbl_tc_settings","tc_id = -1 AND app_by",$_SESSION['_userid']); 								
				//echo $enq_approver;
				if ($enq_approver >0)
				{
			?>	
			<div class="span3">
				<div class="dashboard-stat yellow">
					<div class="visual">
						<i class="fa fa-check-square-o"></i>
					</div>
					<div class="details">
						<div class="number">
							<?php 								
								//echo GetCount("mst_enquiry_tosuppliers","rec_edit_status = '1' AND rec_del_status='1' AND approval_status='0' AND  1",1); 								
								echo GetCount("mst_enquiry","overall_status = 2 AND enq_status",1);
							?>					
						</div>
						<div class="desc">
							 Enquiry Verification Pending
						</div>
					</div>
					<a href="lst_enquiry_quotes.php?usr_id=<?php echo $_SESSION['_userid']; ?>" id="all" title="View All"  class="more">
						 View all Enquiry<i class="fal fa-arrow-circle-right"></i>
					</a>
				</div>
			</div>
			<div class="span3">
				<div class="dashboard-stat red">
					<div class="visual">
						<i class="fas fa-check-double"></i>
					</div>
					<div class="details">
						<div class="number">
							<?php 								
								echo GetCount("mst_enquiry","overall_status = 6 AND enq_status",1) 								
							?>					
						</div>
						<div class="desc">
							 Enquiry Completions
						</div>
					</div>
					<a href="lst_enquiry_completions.php?usr_id=<?php echo $_SESSION['_userid']; ?>" id="all" title="View All"  class="more">
						 View all <i class="fal fa-arrow-circle-right"></i>
					</a>
				</div>
			</div>
		<?php } ?>
		
		<?php 								
				
				$enq_creator = GetCount("tbl_tc_settings","tc_id = -1 AND edit_by",$_SESSION['_userid']); 								
				//echo $enq_creator;
				if ($enq_creator >0)
				{
			?>	
			<div class="span3">
				<div class="dashboard-stat yellow">
					<div class="visual">
						<i class="fa fa-check-square-o"></i>
					</div>
					<div class="details">
						<div class="number">
							<?php 																
								echo GetCount("mst_enquiry_task","task_status =0 AND task_to",$_SESSION['_userid']);  								
							?>					
						</div>
						<div class="desc">
							  Quotes Received
						</div>
					</div>
					<a href="lst_enquiry_quotes_received.php?usr_id=<?php echo $_SESSION['_userid']; ?>" id="all" title="View All"  class="more">
						 View all Enquiry Quotes<i class="fal fa-arrow-circle-right"></i>
					</a>
				</div>
			</div>
		<?php } ?>
		
			<?php 								
				
				$ship_creator = GetCount("tbl_tc_settings","tc_id = 11 AND edit_by",$_SESSION['_userid']); 								
				//echo $enq_creator;
				if ($ship_creator >0)
				{
			?>	
					<div class="span3">
						<div class="dashboard-stat yellow">
							<div class="visual">
								<i class="fa fa-check-square-o"></i>
							</div>
							<div class="details">
								<div class="number">
									<?php 																
										//echo GetCount("tbl_oeko_exception","rec_status = 0 AND creator_id",$_SESSION['_userid']);
										echo GetCount("tbl_oeko_exception","rec_status = '1' AND app_status='1' and cert_update_status <>'3' AND creator_id",$_SESSION['_userid']);	
									?>					
								</div>
								<div class="desc">
									  Oeko Tex Exceptions
								</div>
							</div>
							<a href="lst_exception_orders.php?usr_id=<?php echo $_SESSION['_userid']; ?>" id="all" title="View All"  class="more">
								 View all <i class="fal fa-arrow-circle-right"></i>
							</a>
						</div>
					</div>
			
			
			<?php } ?>
			</div>
			<?php 								
				if ($_SESSION['_userid'] ==10)
				{
			?>	
			<div class="row-fluid">
				<div class="span3">
					<div class="dashboard-stat yellow">
						<div class="visual">
							<i class="fa fa-crosshairs"></i>
						</div>
						<div class="details">
							<div class="number">
								<?php 								
									echo GetCount("mst_suppliers_oeko_history","approver_status='0' AND  approver",$_SESSION['_userid']); 								
													
								?>					
							</div>
							<div class="desc">
								Oekotex Cerficate Verification
							</div>
						</div>
						<a href="lst_oeko_exception_supp_verify.php?usr_id=<?php echo $_SESSION['_userid']; ?>" id="all" title="View All"  class="more">
							 View all <i class="fal fa-arrow-circle-right"></i>
						</a>
					</div>
				</div>
			
			</div>
			<?php
				}?>
				
			
				
				
	</div>
		<!-- END DASHBOARD STATS -->
</div>

<ul class="action-tabs">               
	<li><a href="#quick-actions" title=""></a></li>
</ul>
	
  </div>