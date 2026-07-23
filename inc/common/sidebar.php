<div class="sidebar sidebar-dark sidebar-main sidebar-expand-md">
	<!-- Sidebar mobile toggler -->
	<div class="sidebar-mobile-toggler text-center">
		<a href="#" class="sidebar-mobile-main-toggle">
			<i class="icon-arrow-left8"></i>
		</a>
		Navigation
		<a href="#" class="sidebar-mobile-expand">
			<i class="icon-screen-full"></i>
			<i class="icon-screen-normal"></i>
		</a>
	</div>
	<!-- /sidebar mobile toggler -->

	<!-- Sidebar content -->
	<div class="sidebar-content">				
		<!-- Main navigation -->
		<div class="card card-sidebar-mobile">
			<ul class="nav nav-sidebar" data-nav-type="accordion">

				<!-- Main -->
				<li class="nav-item-header"><div class="text-uppercase font-size-xs line-height-xs">Main</div> 
					<i class="icon-menu" title="Main"></i></li>
					
				<li class="nav-item">				 
					<?php
					
						if($mm_id==0)
						{
							echo '<a href="home.php" class="nav-link active">
									<i class="icon-home4"></i>
									<span>
										Dashboard
										<!--span class="d-block font-weight-normal opacity-50">No active orders</span-->
									</span>
								</a>';
						}
						else
						{
							echo '<a href="home.php" class="nav-link">
								<i class="icon-home4"></i>
								<span>
									Dashboard
									<!--span class="d-block font-weight-normal opacity-50">No active orders</span-->
								</span>
							</a>';
						}						
					?>
				</li>
				 <?php
					$conn= new dbconnect();
					$dbconn= new dbhandler();
										
					$qry="SELECT * FROM mst_main_menu WHERE mm_show = 1";
					$res = $conn->query($qry);
					
				//echo $_SESSION['_user_id'];
				//echo $_SESSION['_user_type'];
					if ($res->rowCount()>0) 
					{				
						while($ob = $res->fetch(PDO::FETCH_OBJ))
						{								
							/* Sub Menu Generation */															
							
							if( $_SESSION['_user_type']=='A')									
							{
								if($mm_id == $ob->mm_id)
									echo '<li class="nav-item nav-item-submenu nav-item-expanded nav-item-open">
											<a href="'.$ob->mm_url.'" class="nav-link active">
											<i class="'.$ob->mm_class.'"></i> <span>'.$ob->mm_name.'</span></a>';
									else
										echo '<li class="nav-item nav-item-submenu">
											<a href="'.$ob->mm_url.'" class="nav-link">
											<i class="'.$ob->mm_class.'"></i> <span>'.$ob->mm_name.'</span></a>';
											
											
								$sql="SELECT * FROM mst_sub_menu WHERE mm_id =".$ob->mm_id." AND `sm_show` = 1 ORDER BY sm_index";
								
								$res1 = $conn->query($sql);
							
								if ($res1->rowCount()>0) 
								{
									echo '<ul class="nav nav-group-sub" data-submenu-title="'.$ob->mm_name.'">';
									while($ob1 = $res1->fetch(PDO::FETCH_OBJ))
									{
										if($sm_id == $ob1->sm_id)
											echo '<li class="nav-item"><a href="'.$ob1->sm_url.'" class="nav-link active">'.$ob1->sm_name .'</a></li>';										
										else
											echo '<li class="nav-item"><a href="'.$ob1->sm_url.'" class="nav-link">'.$ob1->sm_name .'</a></li>';
									}
									echo '</ul>';
								}
									
							}
							else
							{
								
								$sm_ids ='';
								$sm_ids = $dbconn->GetSingleReconrd("tbl_user_rights","group_concat(sm_id)","mm_id = '".$ob->mm_id."' AND usr_id",$_SESSION['_user_id']);
								

								if(	$sm_ids	!='')
								{
									$sql="SELECT * FROM mst_sub_menu WHERE mm_id =".$ob->mm_id." AND sm_id IN (".$sm_ids.") AND `sm_show` = 1
										ORDER BY sm_index";								
									//echo $sm_ids.' **'.$sql;
									
									$res1 = $conn->query($sql);
								
									if ($res1->rowCount()>0) 
									{
										if($mm_id == $ob->mm_id)
										echo '<li class="nav-item nav-item-submenu nav-item-expanded nav-item-open">
												<a href="'.$ob->mm_url.'" class="nav-link active">
												<i class="'.$ob->mm_class.'"></i> <span>'.$ob->mm_name.'</span></a>';
										else
											echo '<li class="nav-item nav-item-submenu">
												<a href="'.$ob->mm_url.'" class="nav-link">
												<i class="'.$ob->mm_class.'"></i> <span>'.$ob->mm_name.'</span></a>';
										
										
										echo '<ul class="nav nav-group-sub" data-submenu-title="'.$ob->mm_name.'">';
										while($ob1 = $res1->fetch(PDO::FETCH_OBJ))
										{
											if($sm_id == $ob1->sm_id)
												echo '<li class="nav-item"><a href="'.$ob1->sm_url.'" class="nav-link active">'.$ob1->sm_name .'</a></li>';										
											else
												echo '<li class="nav-item"><a href="'.$ob1->sm_url.'" class="nav-link">'.$ob1->sm_name .'</a></li>';
										}
										echo '</ul>';
									}
								}
								
								
							}	
							
							
							/* End of Sub Menu Generation */	
							
							
							echo '</li>';
						}
					}						
	
				?>	
				<!-- /main -->
			</ul>
		</div>
		<!-- /main navigation -->

	</div>
	<!-- /sidebar content -->
	
</div>