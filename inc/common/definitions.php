<?php

	$conn = new dbconnect();		
	$sql ="SELECT * FROM mst_application";
	$res = $conn->query($sql);	
	$rs_CRM = $res->fetch(PDO::FETCH_OBJ);
	
	define ("APPLICATION_NAME",$rs_CRM->app_name);
	define ("VERSION","1.0");
	define ("CLIENT_NAME",$rs_CRM->app_client_web);
	define ("CLIENT_EMAIL",$rs_CRM->app_client_email);
	define ("PAGE_LOGO",$rs_CRM->app_client_logo);
	define ("PAGE_TITLE",$rs_CRM->app_page_title);
	define ("PAGE_COPYRIGHT",$rs_CRM->app_page_copyright." ".date("Y"));
	define ("DATE_FORMAT","MDY");
	define ("DATE_REP","/");
	define ("DISPLAY_LINKS",10);
	define ("LIST_LENGTH",10);
	define ("LIST_LENGTH_20",20);
	define ("YEAR_START",1970);
	define ("YEAR_END",(date('Y')+5));
	define ("SPAM_BG_COLOR","255`255`255");
	define ("SPAM_FONT_COLOR","255`102`0");
	define ("SPAM_BGDOT_COLOR","255`204`0");
	define ("SPAM_FONT_SIZE","5");
	define ("SPAM_BG_DOT","NO");
	define ("SUPPORT_EMAIL","kabilanju@gmail.com");
	define ("ADMIN_EMAIL","kabilanju@gmail.com");
	define ("DONT_REPLY_EMAIL","do-not-reply@tulipsmedia.com");

?>
