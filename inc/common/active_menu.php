<?php		
	
	$dbcon= new dbhandler();
	$currentFile = $_SERVER["PHP_SELF"];
	$parts = Explode('/', $currentFile);
	$sm_url = $parts[count($parts) - 1];
	
	
	$_REQUEST['sm_id'] = $dbcon->GetLastRecord("mst_sub_menu","sm_id","sm_url",$sm_url,"sm_index");
	
	if($_REQUEST['sm_id'] != "")
	{
		$_SESSION['mm_id'] = $dbcon->GetSingleReconrd("mst_sub_menu","mm_id","sm_id",$_REQUEST['sm_id']);
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

