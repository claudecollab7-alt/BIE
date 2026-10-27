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

					// 2026-10-26 every sub menu the user can see in one query, grouped by main menu - it was 1-2 queries per main menu
					// (admin: all shown sub menus; others: the ones in their user rights for that main menu)
					if ($_SESSION['_user_type'] == 'A') {
						$sub_res = $conn->query("SELECT * FROM mst_sub_menu WHERE `sm_show` = 1 ORDER BY sm_index, sm_id");
					} else {
						$sub_res = $conn->prepare("SELECT s.* FROM mst_sub_menu s WHERE s.`sm_show` = 1
							AND EXISTS (SELECT 1 FROM tbl_user_rights r WHERE r.usr_id = :u AND r.mm_id = s.mm_id AND r.sm_id = s.sm_id) ORDER BY s.sm_index, s.sm_id");
						$sub_res->execute(array(':u' => $_SESSION['_user_id']));
					}
					$sub_menus = array();
					foreach ($sub_res->fetchAll(PDO::FETCH_OBJ) as $sm) {
						$sub_menus[$sm->mm_id][] = $sm;
					}
					
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
											
											
								$res1 = isset($sub_menus[$ob->mm_id]) ? $sub_menus[$ob->mm_id] : array();
							
								if (count($res1)>0) 
								{
									echo '<ul class="nav nav-group-sub" data-submenu-title="'.$ob->mm_name.'">';
									foreach($res1 as $ob1)
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
								
								$res1 = isset($sub_menus[$ob->mm_id]) ? $sub_menus[$ob->mm_id] : array();

								if(	count($res1) > 0)
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
									foreach($res1 as $ob1)
									{
										if($sm_id == $ob1->sm_id)
											echo '<li class="nav-item"><a href="'.$ob1->sm_url.'" class="nav-link active">'.$ob1->sm_name .'</a></li>';										
										else
											echo '<li class="nav-item"><a href="'.$ob1->sm_url.'" class="nav-link">'.$ob1->sm_name .'</a></li>';
									}
									echo '</ul>';
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