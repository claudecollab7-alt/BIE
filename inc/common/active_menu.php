<?php		
	
	$dbcon= new dbhandler();
	$currentFile = $_SERVER["PHP_SELF"];
	$parts = Explode('/', $currentFile);
	$sm_url = $parts[count($parts) - 1];
	
	
	// 2026-10-26 the page's sub menu and its main menu in one query (was two)
	// 2026-10-27 line breaks in a stored menu URL ignored - those menus (New Employee, Staff List...) never showed as active
	$menu_row = $dbcon->GetRow("SELECT sm_id, mm_id FROM mst_sub_menu WHERE REPLACE(REPLACE(sm_url, CHAR(13), ''), CHAR(10), '') = :u ORDER BY sm_index LIMIT 1", array(':u' => $sm_url));
	$_REQUEST['sm_id'] = $menu_row ? (string)$menu_row->sm_id : '';
	
	if($_REQUEST['sm_id'] != "")
	{
		$_SESSION['mm_id'] = (string)$menu_row->mm_id;
		$_SESSION['sm_id'] = $_REQUEST['sm_id'];
		$mm_id = $_SESSION['mm_id'];
		$sm_id = $_SESSION['sm_id'];
	}
	else
	{
		$mm_id = 0;
		$sm_id = 0;
	}
	//echo $mm_id .'---'. $sm_id;
	
?>

