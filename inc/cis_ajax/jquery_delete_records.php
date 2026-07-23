<?php

	require_once("../common/dbconnect.php");

	$delete_id = $_POST["id"];
	$table = $_POST["table"];
	$status = $_POST["status"];
	$value = $_POST["value"];
	$where = $_POST["where"];
	
	
	if ($delete_id) 
	{	
		try
		{
			$conn = new dbconnect();		
			$sql = "UPDATE $table SET $status = $value WHERE $where = '".$delete_id."'";
			$res = $conn->query($sql);				
			echo 1;
		}
		catch(Exception $e)
		{
			$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
			echo $str;
		}
		
		$conn=null;
	}	
		
?>