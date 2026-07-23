<?php

ob_start();
session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn= new dbhandler();

	$delete_id = $_POST["id"];
	$table = $_POST["table"];
	$status = $_POST["status"];
	$value = $_POST["value"];
	$rec_del_by = $_POST["rec_del_by"];
	$rec_del_dtm = $_POST["rec_del_dtm"];
	$value1 = $_SESSION['_user_id'];
	$value2 = date('Y-m-d H:i:s');
	$where = $_POST["where"];
	
	
	if ($delete_id) 
	{	
		try
		{
			$conn = new dbconnect();		
			// $sql = "UPDATE $table SET $status = $value WHERE $where = '".$delete_id."'";\
			echo $sql = "UPDATE $table SET $status = '".$value."' , $rec_del_by = '".$value1."' , $rec_del_dtm = '".$value2."' WHERE $where = '".$delete_id."' ";
			$conn->query($sql);
			// $res = $conn->query($sql);				
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