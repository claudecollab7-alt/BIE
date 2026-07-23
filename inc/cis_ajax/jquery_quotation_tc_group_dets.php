<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
$conn = new dbconnect();
$dbconn= new dbhandler();

if(isset($_POST['tc_principal_id']))
{
	$tc_principal_id = $_POST['tc_principal_id'];
	
	$qry="select * from mst_terms_condition where terms_con_status=1 ANd principal_id=". $tc_principal_id .' order by terms_con_category';
		
	$res = $conn->query($qry);
	
	$con1 = $con2= $con3= $con4= $con5= $con6= $con7= $con8= '';
    
    if ($res->rowCount() > 0) 
	{		 
		while($ob = $res->fetch())
		{		
			switch($ob->terms_con_category)
			{
				case 1 : $con1 = $ob->terms_con_id; break; 
				case 2 : $con2 = $ob->terms_con_id; break; 
				case 3 : $con3 = $ob->terms_con_id; break;	
				case 4 : $con4 = $ob->terms_con_id; break;	
				case 5 : $con5 = $ob->terms_con_id; break;		
				case 6 : $con6 = $ob->terms_con_id; break;	
				case 7 : $con7 = $ob->terms_con_id; break;	
				case 8 : $con8 = $ob->terms_con_id; break;	
			}
		}
		
	}
	echo $con1 .'~'. $con2.'~'. $con3.'~'. $con4.'~'. $con5.'~'. $con6.'~'. $con7.'~'. $con8;

}
?>