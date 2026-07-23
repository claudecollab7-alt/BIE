<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
$conn = new dbconnect();
$dbconn= new dbhandler();

if(isset($_POST['desc_id']))
{
	$desc_id = $_POST['desc_id'];
	
	$qry="select * from mst_quo_details where  quo_id=". $desc_id;
	
	$res = $conn->query($qry);
	
	$type = $type_val= $gst ='';
    
    if ($res->rowCount() > 0)
	{		 
		$ob = $res->fetch();
				
		$type = $ob->quo_percent;
		$type_val = $ob->quo_pack_text;
		$gst = $ob->hsn_id;
	}
	
	echo $type .'~'. $gst.'~'. $type_val;

}
?>