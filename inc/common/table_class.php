<?php

function GetLastRecord($conn,$tbl,$sf,$wf,$val,$order)
{	
	$SQL = "SELECT ".$sf." FROM ".$tbl." WHERE ".$wf." = '".$val."' ORDER BY ".$order." LIMIT 1";
	$res = $conn->query($SQL);	
	
	if ($res->rowCount() >0)
	{
		$obj5 =$res->fetch(PDO::FETCH_OBJ);
		return $obj5->$sf;
	}
}

function GetSingleReconrd($tbl,$sf,$wf,$val)
{	
	$SQL = "SELECT ".$sf." FROM ".$tbl." WHERE ".$wf." = '".$val."' ";
	$res = $conn->query($SQL);	
	if ($res->rowCount() >0)
	{
		$obj5 =$res->fetch(PDO::FETCH_OBJ);
		return $obj5->$sf;
	}
}



/*
function GetVar($var) {
	# returns $var, whether it's from $_POST or $_GET
	if ($_GET[$var]) { return $_GET[$var]; }
	if ($_POST[$var]) { return $_POST[$var]; }

}

function Execute($sql){
	$res=mysql_query($sql);
	return $res;
}	

function leadingZeros($num,$numDigits) {
   return sprintf("%0".$numDigits."d",$num);
}

function removeBreak($str) {
  $str = str_replace("<br/>","\n",$str);
  return $str;
}

function GetCountry() {
	$res = Execute("select * from tbl_country_master order by country");
	if (mysql_num_rows($res) > 0) {
		while ($country = mysql_fetch_array($res)) {
			echo "<option value=".$country[countryid].">" . $country[country]."</option>";
		}	
	}			
}

function GetStates() {
	$res = Execute("select * from tbl_state_master where country = 1 order by state ");
	if (mysql_num_rows($res) > 0) {
		while ($States = mysql_fetch_array($res)) {
			echo "<option value=".$States[stateid].">" . $States[state]."</option>";
		}	
	}			
}

function GetCity($stateid) {
	$res = Execute("select * from tbl_city_master where stateid = ".$stateid." order by city ");
	if (mysql_num_rows($res) > 0) {
		while ($City = mysql_fetch_array($res)) {
			echo "<option value=".$City[cityid].">" . $City[city]."</option>";
		}	
	}			
}


function GetStateInIndia() {
	
	 $resstate = Execute("select * from tbl_state_master where country in (select id from tbl_country_master where country = 'India') order by id");
	 
	if (mysql_num_rows($resstate) > 0) { 
		while ($stateMaster = mysql_fetch_array($resstate)) {
			echo "<option value=\"" . $stateMaster[id] . "\">" . $stateMaster[state] ."</option>";					
		}
	}
}

function GetSingleReconrd($tbl,$sf,$wf,$val){
	
	$SQL = "SELECT ".$sf." FROM ".$tbl." WHERE ".$wf." = '".$val."' ";
	//echo $SQL;
	$result = mysql_query($SQL);
	if (mysql_num_rows($result) > 0){
	$obj5 = mysql_fetch_object($result);
	return $obj5->$sf;
	}
}

function GetMultiReconrd($tbl,$sf,$wf,$val){
	$str = "";
	$SQL = "SELECT ".$sf." FROM ".$tbl." WHERE ".$wf." = '".$val."' ";
	$result = mysql_query($SQL);
	if (mysql_num_rows($result) > 0) { 
		while ($obj5 = mysql_fetch_object($result)) {
			if($str != ""){
				$str = $str.', '.$obj5->$sf;
			}else{
				$str = $obj5->$sf;
			}
		}
	}
	return $str;
}

function GetSingleReconrd_MultiTable($tbl,$sf,$return,$wf,$val){
	
	$SQL = "SELECT ".$sf." AS ".$return." FROM ".$tbl." WHERE ".$wf." = '".$val."' ";
	$result = mysql_query($SQL);
	if (mysql_num_rows($result) > 0){
	$obj5 = mysql_fetch_object($result);
	return $obj5->$return;
	}
}


function GetCount($tbl,$wf,$val){
	$nos = 0;
	$SQL = "SELECT COUNT(*) FROM ".$tbl." WHERE ".$wf." = ".$val." ";
	//echo '<br>'.$SQL;
	$result1 = mysql_query($SQL);
	$result = mysql_fetch_row($result1);
	if ($result[0]!=""){
		$nos = $result[0];
	}
	return $nos;
}

function GetCountDistinct($tbl,$wf,$val,$fld){
	$nos = 0;
	$SQL = "SELECT COUNT(DISTINCT ".$fld.") FROM ".$tbl." WHERE ".$wf." = ".$val." ";
	//echo '<br>'.$SQL;
	$result1 = mysql_query($SQL);
	$result = mysql_fetch_row($result1);
	if ($result[0]!=""){
		$nos = $result[0];
	}
	return $nos;
}

/*function GetCountRate($tbl,$wf,$val){
	$nos = 0;
	$SQL = "SELECT DISTINCT COUNT(ref_id) FROM ".$tbl." WHERE ".$wf." = ".$val." ";
	//echo '<br>'.$SQL;
	$result1 = mysql_query($SQL);
	$result = mysql_fetch_row($result1);
	if ($result[0]!=""){
		$nos = $result[0];
	}
	return $nos;
}*
function GetCountRate($val){
	$nos = 0;
	$SQL = "SELECT COUNT(DISTINCT ref_id) FROM tbl_task WHERE ref_id != 0 AND parent_id = ".$val." ";
	//echo '<br>'.$SQL;
	$result1 = mysql_query($SQL);
	$result = mysql_fetch_row($result1);
	if ($result[0]!=""){
		$nos = $result[0];
	}
	return $nos;
}
function GetSum($tbl,$sf,$wf,$val){
	$total = 0;
	$SQL = "SELECT SUM(".$sf.") FROM ".$tbl." WHERE ".$wf." = ".$val." ";
	$result1 = mysql_query($SQL);
	$result = mysql_fetch_row($result1);
	if ($result[0]!=""){
		$total = $result[0];
	}
	return $total;
}

function GetMaxValue($tbl,$sf,$wf,$val){
	
	$SQL = "SELECT MAX(".$sf.") AS val FROM ".$tbl." WHERE ".$wf." = '".$val."'";
	$result = mysql_query($SQL);
	if (mysql_num_rows($result) > 0){
	$obj5 = mysql_fetch_object($result);
	return $obj5->val;
	}
}

function update_stock_new($store_id,$item_id,$qty,$type){
	
	if($type == 'IN'){
		$stock_id = GetSingleReconrd("tbl_item_stock","stock_id","store_id = ".$store_id." AND item_id",$item_id);
		if($stock_id != ""){
			$sql_update = "UPDATE tbl_item_stock SET stock_qty = stock_qty + ".$qty." WHERE stock_id = ".$stock_id;
		}else{
			$sql_update = "INSERT INTO tbl_item_stock (store_id,item_id,stock_qty) VALUES ('".$store_id."','".$item_id."','".$qty."')";
		}
	}elseif($type == 'OUT'){
		$stock_id = GetSingleReconrd("tbl_item_stock","stock_id","store_id = ".$store_id." AND item_id",$item_id);
		if($stock_id != ""){
			$sql_update = "UPDATE tbl_item_stock SET stock_qty = stock_qty - ".$qty." WHERE stock_id = ".$stock_id;
		}else{
			$sql_update = "INSERT INTO tbl_item_stock (store_id,item_id,stock_qty) VALUES ('".$store_id."','".$item_id."','-".$qty."')";
		}
	}
	return $sql_update;
	//mysql_query($sql_update);
}

function GetEmpName($usr_id){
	return ucwords(GetSingleReconrd("tbl_user","CONCAT(prefix,' ',usr_name)","usr_id",$usr_id));
}

function getServiceType($field_name){
	if($field_name==0){
			$field_name = "<span>AMC Service</span>";
		}else if($field_name==1){
			$field_name = "<span >Warranty Service</span>";
		}else if($field_name==2){
			$field_name = "<span >Paid Service</span>";
		}
	
	return $field_name;
}
function GetPriority($priority){
	if($priority==0){
			$priority = "<span>High</span>";
		}else if($priority==1){
			$priority = "<span >Medium</span>";
		}else if($priority==2){
			$priority = "<span >Low</span>";
		}
	return $priority;
}	
function GetOpenCloseStatus($field_name){
	    if($field_name==0){
			$field_name = "<span class='label label-success tip' >Open</span>";
		}else if($field_name==1){
			$field_name = "<span class='label label-warning tip'>Apprv. Pending</span>";
		}else if($field_name==2){
			$field_name = "<span class='label label-important tip'>Closed</span>";
		}
		return $field_name;
}
function GetSuppAddress($supp_id){
	
	$sql="SELECT supp_add1,supp_add2,supp_city,supp_district,supp_state,supp_country,supp_pincode  FROM mst_suppliers WHERE supp_id  = ".$supp_id;
	$result = mysql_query($sql);
	$obj5 = mysql_fetch_object($result);
	$add = "";
	if($obj5->supp_add1 != ""){
		$add .= $obj5->supp_add1;
	}
	if($obj5->supp_add2 != ""){
		$add .= ", ".$obj5->supp_add2;
	}
	
	//$add .= ",<br>".$obj5->supp_pincode;
	//$add .= ",<br> ".GetSingleReconrd("mst_pincode","pc_area","pc_id",$obj5->supp_pincode);
	$add .= ", ".GetSingleReconrd("mst_city","city_name","city_id",$obj5->supp_city);
	$add .= ", ".GetSingleReconrd("mst_district","district_name","district_id",$obj5->supp_district)." Dist.";
	$add .= ",<br> ".GetSingleReconrd("mst_state","state_name","state_id",$obj5->supp_state);
	$add .= ", ".GetSingleReconrd("mst_country","country_name","country_id",$obj5->supp_country);
	if($obj5->supp_pincode != ""){
		$add .= " - ".$obj5->supp_pincode;
	}
	return $add;
}

function GetCustAddress($cust_id){
	
	$sql="SELECT cust_add1,cust_add2,cust_country,cust_state,cust_city,cust_district,cust_pincode FROM tbl_customer WHERE cust_id  = ".$cust_id;
	$result = mysql_query($sql);
	$obj5 = mysql_fetch_object($result);
	$add = "";
	if($obj5->cust_add1 != ""){
		$add .= $obj5->cust_add1;
	}
	if($obj5->cust_add2 != ""){
		$add .= ", ".$obj5->cust_add2;
	}
	
	$add .= ",<br> ".GetSingleReconrd("mst_city","city_name","city_id",$obj5->cust_city);
	$add .= ", ".GetSingleReconrd("mst_district","district_name","district_id",$obj5->cust_district)." Dist.";
	$add .= ",<br> ".GetSingleReconrd("mst_state","state_name","state_id",$obj5->cust_state);
	$add .= ", ".GetSingleReconrd("mst_country","country_name","country_id",$obj5->cust_country);
	if($obj5->cust_pincode != ""){
		$add .= " - ".$obj5->cust_pincode;
	}
	return $add;
}

function GetEmployeeAddress($usr_id,$type){
	
	$sql="SELECT usr_cr_add,usr_cr_add1,usr_cr_city,usr_cr_district,usr_cr_state,usr_cr_country,usr_cr_pincode,usr_pr_add,usr_pr_add1,usr_pr_city,usr_pr_district,usr_pr_state,usr_pr_country,usr_pr_pincode FROM tbl_user WHERE usr_id  = ".$usr_id;
	$result = mysql_query($sql);
	$usr = mysql_fetch_object($result);
	if($type == "cr"){
		
		$add = "";
		if($usr->usr_cr_add != ""){
			$add .= $usr->usr_cr_add;
		}
		if($usr->usr_cr_add1 != ""){
			$add .= ", ".$usr->usr_cr_add1;
		}
		
		$add .= "<br> ".GetSingleReconrd("mst_city","city_name","city_id",$usr->usr_cr_city);
		$add .= ", ".GetSingleReconrd("mst_district","district_name","district_id",$usr->usr_cr_district)." Dist.";
		if($usr->usr_cr_pincode != ""){
			$add .= " - ".$usr->usr_cr_pincode.",";
		}
		$add .= "<br> ".GetSingleReconrd("mst_state","state_name","state_id",$usr->usr_cr_state);
		$add .= ", ".GetSingleReconrd("mst_country","country_name","country_id",$usr->usr_cr_country);
		
	
	}else{
		
		$add = "";
		if($usr->usr_pr_add != ""){
			$add .= $usr->usr_pr_add;
		}
		if($usr->usr_pr_add1 != ""){
			$add .= ", ".$usr->usr_pr_add1;
		}
		
		$add .= "<br> ".GetSingleReconrd("mst_city","city_name","city_id",$usr->usr_pr_city);
		$add .= ", ".GetSingleReconrd("mst_district","district_name","district_id",$usr->usr_pr_district)." Dist.";
		if($usr->usr_pr_pincode != ""){
			$add .= " - ".$usr->usr_pr_pincode.",";
		}
		$add .= "<br> ".GetSingleReconrd("mst_state","state_name","state_id",$usr->usr_pr_state);
		$add .= ", ".GetSingleReconrd("mst_country","country_name","country_id",$usr->usr_pr_country);
		
	}
	return $add;
	
}
*/




?>