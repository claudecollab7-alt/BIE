<?php//Don't change the order of filesrequire_once ("inc/common/dbconnect.php"); // instead of configrequire_once("inc/common/definitions.php");require_once ("inc/common/dbhandler.php");	// instead of table_classrequire_once("inc/common/functions.php"); 
require_once("inc/common/stock_flow.php");	// central stock + tbl_stock_flow helper
require_once("inc/common/csrf.php");		// csrf + form tokens
require_once("inc/common/db_txn.php");		// transaction helpersinclude_once("inc/common/active_menu.php");include_once("inc/common/images.php");?>