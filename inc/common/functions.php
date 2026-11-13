<?php
// 2026-10-14 isAdmin also needs the user id and branch - a session with only _user="crm_user" ran pages with no user or branch
// 2026-11-11 fnItemExtraUoms / fnItemUomPrice - extra unit list and price per extra unit per branch
// 2026-11-11 fnCreatedUser / fnCreatedDtm - created by / on for prints
// 2026-11-13 fnIndentUnit, fnItemUomFactor, fnStockQtyInMainUnit - extra unit conversion for stock
require_once(dirname(__FILE__) . '/error_log.php');
ob_start();
date_default_timezone_set('Asia/Calcutta');

$ip=$_SERVER['REMOTE_ADDR']; 


// logged in to this app: the login sets the user and the branch - a session holding only _user
// (for example from another app on the same server) is not a BIE login
// 2026-10-27 list / report search text and sort input safe to put into SQL - a ' in a search box broke the query
// text inside a quoted SQL string ('...'); what was typed is searched for as it is
function fnSqlText($s)
{
	return str_replace(array('\\', "'", "\0"), array('\\\\', "\\'", ''), (string)$s);
}

// sort column sent by a list: a plain column name (or alias.column), else the first selected column
function fnSqlColumn($s)
{
	return preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\.[A-Za-z_][A-Za-z0-9_]*)?$/', (string)$s) ? $s : '1';
}

// sort direction sent by a list: asc or desc only
function fnSqlDir($s)
{
	return strtolower((string)$s) === 'desc' ? 'desc' : 'asc';
}

// 2026-10-30 a quantity for display: up to 3 decimals, no trailing zeros (2.000 -> 2, 0.500 -> 0.5) - items can be sold in part qty
function fnQty($v)
{
	return rtrim(rtrim(number_format((float)$v, 3, '.', ''), '0'), '.');
}

// 2026-11-11 who made a record and when, for "Created by" on prints: created_by / created_dtm, or the old modify_by /
// modify_date_time on a row from before those were kept
function fnCreatedUser($obj)
{
	return (isset($obj->created_by) && $obj->created_by > 0) ? $obj->created_by : (isset($obj->modify_by) ? $obj->modify_by : 0);
}
// 2026-11-11 the "on <date>" part, or nothing when the row has no real date (0000-00-00 printed as 30-Nov--1)
function fnCreatedOn($obj, $prefix, $fmt)
{
	$dtm = fnCreatedDtm($obj);
	return ($dtm >= '2000-01-01') ? $prefix . date($fmt, strtotime($dtm)) : '';
}
function fnCreatedDtm($obj)
{
	return (isset($obj->created_dtm) && $obj->created_dtm >= '2000-01-01') ? $obj->created_dtm : (isset($obj->modify_date_time) ? $obj->modify_date_time : '');
}

// 2026-11-11 unit, price and gst of one indent line - the same for the screen and for every save
// indent unit = main unit -> branch cost price; an extra unit -> its price in tbl_item_uom_price (Item Price screen)
// 'missing' says why the price is 0 - such a line cannot be sent to admin
function fnPoPrepareLine($conn, $dbconn, $si_id, $item_id)
{
    $st = $conn->prepare("SELECT s.branch_id, d.si_unit, i.item_uom, i.item_hsn FROM tbl_store_indent s
        JOIN tbl_store_indent_details d ON d.si_id = s.si_id
        JOIN tbl_item_details i ON i.item_id = d.item_id
        WHERE s.si_id = ? AND d.item_id = ? LIMIT 1");
    $st->execute(array((int)$si_id, (int)$item_id));
    $r = $st->fetch(PDO::FETCH_OBJ);
    $line = array('uom' => 0, 'uom_code' => '', 'unit_price' => 0, 'item_discount' => 0, 'gst' => 0, 'gst_id' => 0, 'missing' => '');
    if (!$r) {
        $line['missing'] = 'Item not found in the store indent';
        return $line;
    }
    $branch_id = $r->branch_id > 0 ? $r->branch_id : $_SESSION['_user_branch'];
    $branch = $conn->query("SELECT * FROM mst_branch WHERE branch_id = " . (int)$branch_id)->fetch(PDO::FETCH_OBJ);
    $unit = $r->si_unit > 0 ? (int)$r->si_unit : (int)$r->item_uom;
    $line['uom'] = $unit;
    $line['uom_code'] = $dbconn->GetSingleReconrd("mst_uom", "uom_code", "uom_id", $unit);
    $line['gst_id'] = $r->item_hsn;
    $line['gst'] = $dbconn->GetSingleReconrd("mst_hsn", "igst", "hsn_status = '1' AND hsn_id", $r->item_hsn);
    if ($unit != (int)$r->item_uom) {
        $p = fnItemUomPrice($conn, $item_id, $unit, $branch_id);
        if ($p && (float)$p->cost_price > 0) {
            $line['unit_price'] = $p->cost_price;	// decimal text as stored, shown as before
            $line['item_discount'] = $p->item_discount;
        } else {
            $line['missing'] = 'No ' . $dbconn->GetSingleReconrd("mst_uom", "uom_name", "uom_id", $unit) . ' price for ' . ($branch ? $branch->branch_name : 'this branch');
        }
    } else {
        // first stock row, as everywhere else
        $stock = $conn->query("SELECT * FROM tbl_item_stock WHERE item_id = " . (int)$item_id . " ORDER BY stock_id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if ($stock && $branch) {
            $line['unit_price'] = isset($stock[$branch->branch_item_cost_price]) ? $stock[$branch->branch_item_cost_price] : 0;
            $line['item_discount'] = isset($stock[$branch->branch_item_discount]) ? $stock[$branch->branch_item_discount] : 0;
        }
        if ((float)$line['unit_price'] <= 0) {
            $line['missing'] = 'No cost price for ' . ($branch ? $branch->branch_name : 'this branch');
        }
    }
    return $line;
}

// 2026-11-11 the detail row values worked out here, not taken from the page: the page has one price per item but one
// qty per supplier row, so with an item split between suppliers the posted prices went to the wrong items
function fnPoPrepareDetsData($conn, $dbconn, $si_id, $item_id, $qty)
{
    $line = fnPoPrepareLine($conn, $dbconn, $si_id, $item_id);
    $price = round((float)$line['unit_price'] * (float)$qty, 2);
    return array(
        ':uom' => $line['uom'],
        ':uom_code' => $line['uom_code'],
        ':item_discount' => $line['item_discount'],
        ':unit_price' => $line['unit_price'],
        ':price' => $price,
        ':gst' => $line['gst'],
        ':gst_id' => $line['gst_id'],
        ':net_amt' => round($price + $price * (float)$line['gst'] / 100, 2)
    );
}

// 2026-11-13 store indent unit: a blank / 0 unit is the item's main unit
function fnIndentUnit($dbconn, $si_unit, $item_id)
{
	$si_unit = (int)$si_unit;
	return $si_unit > 0 ? $si_unit : (int)$dbconn->GetSingleReconrd("tbl_item_details", "item_uom", "item_id", (int)$item_id);
}

// 2026-11-13 extra units to save on an item: real units only, not 0, not the main unit, no repeats (text like "12,6")
function fnCleanExtraUoms($conn, $ids, $main_uom)
{
	$keep = array();
	foreach ((array)$ids as $v) {
		$v = (int)$v;
		if ($v > 0 && $v != (int)$main_uom && !in_array($v, $keep)) {
			$keep[] = $v;
		}
	}
	if (count($keep) == 0) {
		return '';
	}
	return implode(',', $conn->query("SELECT uom_id FROM mst_uom WHERE uom_id IN (" . implode(',', $keep) . ") ORDER BY FIELD(uom_id, " . implode(',', $keep) . ")")->fetchAll(PDO::FETCH_COLUMN));
}

// 2026-11-13 GRN line in another unit than the item's: the conversion used for stock, or that none is set
function fnGrnUnitNote($conn, $item_id, $unit_text)
{
	$cv = fnStockQtyInMainUnit($conn, $item_id, $unit_text, 1);
	$main = $conn->query("SELECT u.uom_code FROM tbl_item_details i JOIN mst_uom u ON u.uom_id = i.item_uom WHERE i.item_id = " . (int)$item_id)->fetchColumn();
	if ($cv['error'] != '') {
		return '<div class="small text-danger">No conversion to ' . $main . ' - set it on the Item Price screen</div>';
	}
	return $cv['factor'] != 1 ? '<div class="small text-success">1 ' . trim($unit_text) . ' = ' . fnQty($cv['factor']) . ' ' . $main . ' in stock</div>' : '';
}

// 2026-11-13 the main unit cannot change once the item has stock in any branch or any stock movement
function fnItemUnitLocked($conn, $item_id)
{
	$item_id = (int)$item_id;
	if ($conn->query("SELECT COUNT(*) FROM tbl_stock_flow WHERE item_id = " . $item_id)->fetchColumn() > 0) {
		return true;
	}
	$fields = array();
	foreach ($conn->query("SELECT branch_stock_field FROM mst_branch WHERE branch_stock_field <> ''")->fetchAll(PDO::FETCH_COLUMN) as $f) {
		if (preg_match('/^[a-z_]+$/', $f)) {
			$fields[] = "`$f` <> 0";
		}
	}
	return count($fields) > 0 && $conn->query("SELECT COUNT(*) FROM tbl_item_stock WHERE item_id = " . $item_id . " AND (" . implode(' OR ', $fields) . ")")->fetchColumn() > 0;
}

// 2026-11-13 how many of the main unit one extra unit is (1 MTR = 100 NOS -> 100); false when not set
function fnItemUomFactor($conn, $item_id, $uom_id)
{
	$st = $conn->prepare("SELECT uom_factor FROM tbl_item_uom WHERE item_id = ? AND uom_id = ? AND uom_factor > 0");
	$st->execute(array((int)$item_id, (int)$uom_id));
	$f = $st->fetchColumn();
	return $f ? (float)$f : false;
}

// 2026-11-13 a received qty in the unit written on the PO / GRN (text: code, or a name cut to 4 letters on old POs)
// as qty of the item's main unit for stock: array(qty, unit id, factor, error)
// same unit, blank or unknown -> unchanged (as before); an extra unit -> qty x factor; no factor -> error
function fnStockQtyInMainUnit($conn, $item_id, $unit_text, $qty)
{
	$st = $conn->prepare("SELECT i.item_code, u.uom_id, u.uom_code, u.uom_name FROM tbl_item_details i LEFT JOIN mst_uom u ON u.uom_id = i.item_uom WHERE i.item_id = ?");
	$st->execute(array((int)$item_id));
	$m = $st->fetch(PDO::FETCH_OBJ);
	$t = strtoupper(trim((string)$unit_text));
	$out = array('qty' => (float)$qty, 'uom_id' => $m ? (int)$m->uom_id : 0, 'factor' => 1, 'error' => '');
	if (!$m || $t === '' || $t === strtoupper($m->uom_code) || $t === strtoupper($m->uom_name) || $t === strtoupper(substr($m->uom_name, 0, 4))) {
		return $out;
	}
	$st = $conn->prepare("SELECT uom_id, uom_name FROM mst_uom WHERE uom_id > 0 AND (UPPER(uom_code) = ? OR UPPER(uom_name) = ? OR UPPER(LEFT(uom_name, 4)) = ?)
		ORDER BY UPPER(uom_code) = ? DESC, uom_status DESC, uom_id LIMIT 1");
	$st->execute(array($t, $t, $t, $t));
	$u = $st->fetch(PDO::FETCH_OBJ);
	if (!$u || (int)$u->uom_id == (int)$m->uom_id) {
		return $out;	// not a known unit - kept as before
	}
	$f = fnItemUomFactor($conn, $item_id, $u->uom_id);
	$out['uom_id'] = (int)$u->uom_id;
	if ($f === false) {
		$extra = isset(fnItemExtraUoms($conn, $item_id)[(int)$u->uom_id]);
		$out['error'] = $m->item_code . ': no conversion from ' . $u->uom_name . ' to ' . $m->uom_name
			. ($extra ? '' : ' (add ' . $u->uom_name . ' as an extra unit in Item Details first)');
		return $out;
	}
	$out['factor'] = $f;
	$out['qty'] = round((float)$qty * $f, 3);
	return $out;
}

// 2026-11-11 extra units (multiple UOM) of an item: uom_id => {uom_id, uom_name, uom_code}
// only units that exist - not 0, not the main unit, no repeats (multi_uom_id is text like "12,2")
function fnItemExtraUoms($conn, $item_id)
{
	$st = $conn->prepare("SELECT item_uom, multi_uom_id FROM tbl_item_details WHERE item_id = ?");
	$st->execute(array((int)$item_id));
	$item = $st->fetch(PDO::FETCH_OBJ);
	$list = array();
	if (!$item) {
		return $list;
	}
	$ids = array();
	foreach (explode(',', (string)$item->multi_uom_id) as $v) {
		$v = (int)trim($v);
		if ($v > 0 && $v != (int)$item->item_uom && !in_array($v, $ids)) {
			$ids[] = $v;
		}
	}
	if (count($ids) > 0) {
		$res = $conn->query("SELECT uom_id, uom_name, uom_code FROM mst_uom WHERE uom_id IN (" . implode(',', $ids) . ") ORDER BY uom_name");
		while ($u = $res->fetch(PDO::FETCH_OBJ)) {
			$list[(int)$u->uom_id] = $u;
		}
	}
	return $list;
}

// 2026-11-11 price of an item in one of its extra units for a branch (tbl_item_uom_price); false when not set
function fnItemUomPrice($conn, $item_id, $uom_id, $branch_id)
{
	$st = $conn->prepare("SELECT * FROM tbl_item_uom_price WHERE item_id = ? AND uom_id = ? AND branch_id = ?");
	$st->execute(array((int)$item_id, (int)$uom_id, (int)$branch_id));
	return $st->fetch(PDO::FETCH_OBJ);
}

function fnIsLoggedIn()
{
	return isset($_SESSION['_user']) && $_SESSION['_user'] == "crm_user"
		&& !empty($_SESSION['_user_id']) && !empty($_SESSION['_user_branch']);
}

function isAdmin()
{
	if(isset($_SESSION['_user']))
	{
	    if(!fnIsLoggedIn())
	    {			
		    $_SESSION['_msg']="Session Expired. Please Relogin";	
		    header("Location:index.php");		
		    die();
	    }
		else
		{
			$_SESSION['timer'] = time();
		}
	}
	else
	{
	    $_SESSION['_msg']="Session Expired. Please Relogin";	
		header("Location:index.php");		
		die();
	}
}


	
// rows posted as name[] - 0 when the field is missing, empty or came in as a single value.
// count() on a string is a fatal error from php 8 and counted 1 on php 7.
function fnRowCount($key)
{
	return (isset($_REQUEST[$key]) && is_array($_REQUEST[$key])) ? count($_REQUEST[$key]) : 0;
}

function StandardHash($plain) {
	return md5("$plain:Tulips2015");
}		

function GetFieldList($table) 
{			
	# returns a CSV list of fields in $table in $db in the order in which
	# they appear, EXCLUDING the "id" field (which all tables should have)
		
$conn = new dbconnect();	
$dbconn= new dbhandler();
$fieldlist = '';
	$sth = $conn->query("DESCRIBE $table");
	//echo mysql_error();
	while ($row = $sth->fetch(PDO::FETCH_OBJ)) {
		//print_r($row);
		if ($row->Field != "id") {
			$fieldlist .= "$row->Field,";
		}
	}
	return StrTruncate($fieldlist,1);		
}
function StrTruncate($str,$chars) {

# returns $str, truncated by $chars characters
return substr($str,0,strlen($str)-$chars);

}

function post_img($fileName,$tempFile,$targetFolder)
{	
 	if ($fileName!="")
	{
		if(!(is_dir($targetFolder)))

		mkdir($targetFolder);

		$counter=0;

		$NewFileName=$fileName;

		if(file_exists($targetFolder."/".$NewFileName))
		{
			do
			{ 
				$counter=$counter+1;
				$NewFileName=$counter."".$fileName;
			}
			while(file_exists($targetFolder."/".$NewFileName));
		}

		$NewFileName=str_replace(",","-",$NewFileName);
		$NewFileName=str_replace(" ","_",$NewFileName);		
		copy($tempFile, $targetFolder."/".$NewFileName);
		return $NewFileName;
	}
}

 

function removeFile($filename)
{
	if (file_exists($filename))
	{
		unlink($filename);
	}
}



function send_mail($strTo,$strFrom,$strSubject,$strContent)
{
	$to=$strTo;
	$subject=$strSubject; 
	$headers="MIME-Version: 1.0\r\n";
	$headers.="Content-type: text/html; charset=iso-8859-1\r\n";
	$headers.="From: UKTILLS <".$strFrom."> \r\n";	
//	$headers.="Cc: KAVIYAN Team <admin@kaviyan.in> \r\n";	
//	$headers .= 'Bcc:' . "\r\n";	
	$isSent = mail($to,$subject,$strContent,$headers);
}


function send_mail_cc($strTo,$strFrom,$strCC,$strSubject,$strContent)
{
	$to=$strTo;
	$subject=$strSubject; 
	$headers="MIME-Version: 1.0\r\n";
	$headers.="Content-type: text/html; charset=iso-8859-1\r\n";
	$headers.="From: KAVIYAN Team <".$strFrom."> \r\n";	
//	$headers.="Cc: KAVIYAN Team <".$strCC."> \r\n";	
//	$headers .= 'Bcc:' . "\r\n";	
	$isSent = mail($to,$subject,$strContent,$headers);
}


function fnFillComboFromTable( $field1, $field2, $table, $field3, $isSelect )
{

	$strOption = ""; $result = ""; $SQL = "";
	$SQL = "SELECT $field1,$field2 FROM $table ORDER BY $field3";
	$result = mysql_query($SQL);

	if (mysql_num_rows($result) > 0)
	{
		while ($obj = mysql_fetch_object($result))
		{
			$strOption .="<option value=\"". $obj->$field1 ."\">". $obj->$field2 ."</option>";
		}
		return $strOption;
	}
}


function fnFillComboFromTable_Where( $field1, $field2, $table, $field3, $where )
{

	$strOption = ""; $result = ""; $SQL = "";

	$SQL = "SELECT $field1 AS a,$field2 AS b FROM $table $where ORDER BY $field3";

	$result = mysql_query($SQL);
	
	if (mysql_num_rows($result) > 0)
	{
		while ($obj = mysql_fetch_object($result))
		{
			$strOption .="<option value=\"". $obj->a ."\">". $obj->b ."</option>";		
		}
		return $strOption;
	}
}


function MyFormatDate($MyDate)
{
	/*
			Take a date in yyyy-mm-dd format and return it to the user
			in a PHP timestamp
	*/
	if($MyDate!="0000-00-00" && $MyDate!=""){
		
		$date_array = explode("-",$MyDate); // split the array
		
		$var_year = $date_array[0];
		$var_month = $date_array[1];
		$var_day = $date_array[2];

		$var_Dt =  $var_day."-".substr(date('F', mktime(0, 0, 0, $var_month,1)),0,3)."-".$var_year;
		
		//$var_Dt =  $var_day."-".$var_month."-".$var_year;
		
		return($var_Dt); // return it to the user
	
	}
}

function MyFormatDate_new($MyDate,$dateFormat)
{
	if($MyDate!="0000-00-00" && $MyDate!=""){
        $date_array = explode("-",$MyDate); // split the array
        $var_year = $date_array[0];
        $var_month = $date_array[1];
        $var_day = $date_array[2];
		
		  switch ($dateFormat) {
			case "dmy" :
			  $var_Dt =  $var_day.".".$var_month.".".$var_year;
			  return($var_Dt);
			case "ymd" :
			  $var_Dt =  $var_year."-".$var_month."-".$var_day;
			  return($var_Dt);
			case "mdy" :
			  $var_Dt =  $var_month."-".$var_day."-".$var_year;
			  return($var_Dt);
			case "MY" :
			  $var_Dt =  date('F', mktime(0, 0, 0, $var_month))."-".$var_year;
			  return($var_Dt);
			case "DMY" :
			  $var_Dt =  $var_day."-".substr(date('F', mktime(0, 0, 0, $var_month)),0,3)."-".$var_year;
			  return($var_Dt);
			case "D" :
			  $var_Dt =  $var_day;
			  return($var_Dt); 
			case "M" :
			  $var_Dt =  strtoupper(substr(date('F', mktime(0, 0, 0, $var_month)),0,3));
			  return($var_Dt); 
			case "m" :
			  $var_Dt =  $var_month;
			  return($var_Dt);  
			case "Y" :
			  $var_Dt =  $var_year;
			  return($var_Dt);  
			default :
			  $var_Dt =  $var_day."-".substr(date('F', mktime(0, 0, 0, $var_month)),0,3)."-".$var_year;
			  return($var_Dt);
		  }
        
	}   //return($var_Dt); // return it to the user
}

function MyFormatDateTime($MyDate)
{
        /*
                Take a date in yyyy-mm-dd format and return it to the user
                in a PHP timestamp
                Robin 06/10/1999
        */
        $date_array = explode("-",substr($MyDate,0,10)); // split the array
        
        $var_year = $date_array[0];
        $var_month = $date_array[1];
        $var_day = $date_array[2];

        $var_Dt =  $var_day."-".substr(date('F', mktime(0, 0, 0, $var_month)),0,3)."-".$var_year;
        
		$var_Tm = substr($MyDate,11,19); // split the array
		
		$var_Dt_Tm = $var_Dt . " &nbsp; " . $var_Tm ;
		
		return($var_Dt_Tm); // return it to the user
}

function Find_AGE($MyDate){
		
		$date_array = explode("-",substr($MyDate,0,10)); // split the array
        
        $var_year = $date_array[0];
        $var_month = $date_array[1];
        $var_day = $date_array[2];
		
		$ageTime = mktime(0, 0, 0, $var_month, $var_day, $var_year); // Get the person's birthday timestamp
		$t = time(); // Store current time for consistency
		$age = ($ageTime < 0) ? ( $t + ($ageTime * -1) ) : $t - $ageTime;
		$year = 60 * 60 * 24 * 365;
		$ageYears = round($age / $year);
		 
		return($ageYears);
}

function dateDiff($start, $end) 
{
	$start_ts = strtotime($start);
	$end_ts = strtotime($end);

	$diff = $end_ts - $start_ts;
	return round($diff / 86400);

}

function lastday($month,$year) 
{
   if (empty($month)) {
      $month = date('m');
   }
   if (empty($year)) {
      $year = date('Y');
   }
   $result = strtotime("{$year}-{$month}-01");
   $result = strtotime('-1 second', strtotime('+1 month', $result));
   return date('Y-m-d', $result);
}

function TodayDays4Month($month,$year)
{
	if (empty($month)) {
      $month = date('m');
   }
   if (empty($year)) {
      $year = date('Y');
   }
   $result = strtotime("{$year}-{$month}-01");
   $result = strtotime('-1 second', strtotime('+1 month', $result));
   return date('t', $result);
}

function GetMonthString($n)
{
    $timestamp = mktime(0, 0, 0, $n, 1, 2005);    
    return date("M", $timestamp);
}

function get_previous_month($date) 
{
	$date = str_replace("/", "-", $date);
	$year=date("Y",strtotime($date));
	$month=date("n",strtotime($date)) - 1;
	if ($month == 0)
	{
		$month = 12;
		$year = $year - 1;
	}
	return date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));
}

function random_password()
{

    $chars = "abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ023456789";
	
	//echo strlen($chars);
	
    srand((double)microtime()*1000000);

    $i = 0;

    $pass = '' ;

    while ($i <= 8) 
	{

        $num = rand() % 60;

        $tmp = substr($chars, $num, 1);

        $pass = $pass . $tmp;

        $i++;

    }
    return $pass;	
}

function random_captcha_code()
{
    $chars = "abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ023456789";
	
	//echo strlen($chars);
	
    srand((double)microtime()*1000000);

    $i = 0;

    $pass = '' ;

    while ($i <= 4) {

        $num = rand() % 60;

        $tmp = substr($chars, $num, 1);

        $pass = $pass . $tmp;

        $i++;

    }
    return $pass;	
}



function Find_Dropdown_Value($id){
	return GetSingleReconrd("mst_main","mst_main_value","mst_main_id",$id);
}
	
function getcurrentpath()
{   $curPageURL = "";
	if ($_SERVER["HTTPS"] != "on")
			$curPageURL .= "http://";
	 else
		$curPageURL .= "https://" ;
	if ($_SERVER["SERVER_PORT"] == "80")
		$curPageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	 else
		$curPageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		$count = strlen(basename($curPageURL));
		$path = substr($curPageURL,0, -$count);
	return $path ;
}	


function moneyFormatIndia($rupee)
{
    $explrestunits = "" ;
	
	$num = (int) $rupee;  
	$paise = $rupee - (int)$num;	
	
    if(strlen($num)>3)
	{
		
        $lastthree = substr($num, strlen($num)-3, strlen($num));
        $restunits = substr($num, 0, strlen($num)-3); // extracts the last three digits
        $restunits = (strlen($restunits)%2 == 1)?"0".$restunits:$restunits; // explodes the remaining digits in 2's formats, adds a zero in the beginning to maintain the 2's grouping.
        $expunit = str_split($restunits, 2);
        for($i=0; $i<sizeof($expunit); $i++){
            // creates each of the 2's group and adds a comma to the end
            if($i==0)
            {
                $explrestunits .= (int)$expunit[$i].","; // if is first value , convert into integer
            }else{
                $explrestunits .= $expunit[$i].",";
            }
        }
        $thecash = $explrestunits.$lastthree;
		
		
    } else {
        $thecash = $num;
    }	
	
	if ($paise ==0)
	  $thecash =$thecash . '.00';
	else		
	{	
		$str_rs = number_format($rupee,2)."";
		$data= explode('.',$str_rs);
		$thecash = $thecash .'.'.$data[1] ;
	}

    return $thecash; // writes the final format where $currency is the currency symbol.
}

function indian_number_format($num) {
    $num = "".$num;
    if( strlen($num) < 4) return $num;
    $tail = substr($num,-3);
    $head = substr($num,0,-3);
    $head = preg_replace("/\B(?=(?:\d{2})+(?!\d))/",",",$head);
    return $head.",".$tail;
}

function getStartAndEndDate($week, $year) 
{
  // Adding leading zeros for weeks 1 - 9.
  $date_string = $year . 'W' . sprintf('%02d', $week);
  $return[0] = date('Y-n-j', strtotime($date_string));
  $return[1] = date('Y-n-j', strtotime($date_string . '7'));
  return $return;
}	

function sendSMS($numbers = FALSE, $msg = FALSE){
	
	$msg = urlencode($msg);
	$msg = str_replace('%3Cbr%3E','%0A',$msg);
	$remove = array("\n", "\r\n", "\r",";",",,",",,,");
	$numbers = str_replace($remove, ',',$numbers);
	$numbers = str_replace($remove, ',',$numbers);
	
	$url="http://myvaluefirst.com/smpp/sendsms?username=tulipmedia&password=tulipsm989&to=".$numbers."&from=eurose&text=".$msg."";
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$store = curl_exec ($ch);
	curl_close ($ch);
	return $store;
		
}

function CIS_cron_SMS()
{

	$sql_sms="SELECT sc_id,numbers,message,user_id,ip_add FROM sms_compous WHERE sent_status = 0 AND 
	schedule_dtm <= '".date('Y-m-d H:i:s')."' ORDER BY schedule_dtm LIMIT 10";
	
	$result_sms = mysql_query($sql_sms);
	
	if (mysql_num_rows($result_sms) > 0){

		while ($obj_sms = mysql_fetch_object($result_sms))
		{
				$numbers = $obj_sms->numbers;
				$message = $obj_sms->message;
			  
			  	sendSMS($numbers,$message);
				mysql_query("UPDATE sms_compous SET sent_dt='".date('Y-m-d')."', sent_dt_time='".date('Y-m-d H:i:s')."',sent_status=1 WHERE sc_id=".$obj_sms->sc_id);
			
		}
	}	
	
}


/* --------------  NUMBER TO WORDS  ---------------- */
function number_to_words_paise($number)
{
	$decimal = round($number - ($no = floor($number)), 2) * 100;
    $hundred = null;
    $digits_length = strlen($no);
    $i = 0;
    $str = array();
    $words = array(0 => '', 1 => 'one', 2 => 'two',
        3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six',
        7 => 'seven', 8 => 'eight', 9 => 'nine',
        10 => 'ten', 11 => 'eleven', 12 => 'twelve',
        13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen',
        16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen',
        19 => 'nineteen', 20 => 'twenty', 30 => 'thirty',
        40 => 'forty', 50 => 'fifty', 60 => 'sixty',
        70 => 'seventy', 80 => 'eighty', 90 => 'ninety');
    $digits = array('', 'hundred','thousand','lakh', 'crore');
    while( $i < $digits_length ) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
        } else $str[] = null;
    }
    $Rupees = implode('', array_reverse($str));
    $paise = ($decimal) ? " " . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
    return ($Rupees ? $Rupees . 'Rupees ' : '') . $paise;
}



function number_to_words($no)
{
	$words = array('0'=> '' ,'1'=> 'one' ,'2'=> 'two' ,'3' => 'three','4' => 'four','5' => 'five','6' => 'six','7' => 'seven','8' => 'eight','9' => 'nine','10' => 'ten','11' => 'eleven','12' => 'twelve','13' => 'thirteen','14' => 'fouteen','15' => 'fifteen','16' => 'sixteen','17' => 'seventeen','18' => 'eighteen','19' => 'nineteen','20' => 'twenty','30' => 'thirty','40' => 'fourty','50' => 'fifty','60' => 'sixty','70' => 'seventy','80' => 'eighty','90' => 'ninty','100' => 'hundred &','1000' => 'thousand','100000' => 'lakh','10000000' => 'crore');
	if($no == 0)
	return ' ';
	else {
	$novalue='';
	$highno=$no;
	$remainno=0;
	$value=100;
	$value1=1000;
	while($no>=100) {
	if(($value <= $no) &&($no < $value1)) {
	$novalue=$words["$value"];
	$highno = (int)($no/$value);
	$remainno = $no % $value;
	break;
	}
	$value= $value1;
	$value1 = $value * 100;
	}
	if(array_key_exists("$highno",$words))
	return $words["$highno"]." ".$novalue." ".number_to_words($remainno);
	else {
	$unit=$highno%10;
	$ten =(int)($highno/10)*10;
	return $words["$ten"]." ".$words["$unit"]." ".$novalue." ".number_to_words($remainno);
	}
	}
}
 function GetVar($var) {
	# returns $var, whether it's from $_POST or $_GET
	if ($_GET[$var]) { return $_GET[$var]; }
	if ($_POST[$var]) { return $_POST[$var]; }

}

function leadingZeros($num,$numDigits) 
{
	return sprintf("%0".$numDigits."d",$num);
}

function CIS_InsertRecord($table,$subdata) {

# builds a SQL statement to create a new record in $db/$table using
# $subdata, which must be a $_POST or $HTTP_POST_VARS associative
# array. Returns the SQL statement, which must be SQLExecute()-ed

# retrieve field lists for comparison (field data will only be entered
# into the table if the field keys are present in the table)
//echo "table".$table."<br>";
//echo "subdata".print_r($subdata);

$postfields = array_keys($subdata);			
$tablefields = explode(",",GetFieldList($table));
# build SQL statement
$sql = "INSERT INTO $table (";
foreach ($postfields as $field) {
	if (in_array($field,$tablefields)) { echo $sql .= "$field,"; }
}			
$sql = StrTruncate($sql,1).") VALUES (";
foreach ($postfields as $field) {
	$subdata[$field] = trim(str_replace("'", "&prime;",$subdata[$field]));
	if (in_array($field,$tablefields)) { $sql .= "'".$subdata[$field]."',"; }
}
$sql = StrTruncate($sql,1).");";

return $sql;

}


function CIS_UpdateRecord($table,$subdata) {
	
			# builds a SQL statement to update record $rid in $db/$table using
			# $subdata, which must be a $_POST or $HTTP_POST_VARS associative
			# array. Returns the SQL statement, which must be SQLExecute()-ed
			//print_r($subdata);
			//die();
			global $config;
		
			# retrieve field lists for comparison (field data will only be entered
			# into the table if the field keys are present in the table)
			$postfields = array_keys($subdata);
			$tablefields = explode(",",GetFieldList($table));	
				
			# build SQL statement, excluding hashed fields that require no changes
			$sql = "UPDATE $table SET ";
		
			foreach ($postfields as $field) {		
			
				if (in_array($field,$tablefields)) {		
					# add normal field to SQL statement					
	
					if ($subdata[$field] || $subdata[$field] == 0) {
						$subdata[$field] = trim(str_replace("'", "&prime;",$subdata[$field]));
						$sql .= "$field='". $subdata[$field]."',";
					}			
		
				}
		
			}			
			//$sql = StrTruncate($sql,1)." WHERE id='$rid';";
		
			return $sql;
		
}
/*
class Encryption {
    var $skey = "TulipsMedia2017";
    public function encode($string){ 
		$encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($this->skey), $string, MCRYPT_MODE_CBC, md5(md5($this->skey))));
		$encrypted = str_replace(array('+','/','='),array('-','_',''),$encrypted);
        return $encrypted;
    }
    public function decode($encrypted){
        $encrypted = str_replace(array('-','_'),array('+','/'),$encrypted);
        $mod4 = strlen($encrypted) % 4;
        if ($mod4) {
            $encrypted .= substr('====', $mod4);
        }
        $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($this->skey), base64_decode($encrypted), MCRYPT_MODE_CBC, md5(md5($this->skey))), "\0");
		return $decrypted;
    }
}*/
class Encryption {
    public function encode($string){ 
		$encrypted = base64_encode($string);
		$encrypted = str_replace(array('+','/','='),array('-','_',''),$encrypted);
        return $encrypted;
    }
    public function decode($encrypted){
        $encrypted = str_replace(array('-','_'),array('+','/'),$encrypted);
        $mod4 = strlen($encrypted) % 4;
        if ($mod4) {
            $encrypted .= substr('====', $mod4);
        }
        $decrypted = rtrim(base64_decode($encrypted));
		return $decrypted;
    }
}

function roundofnum($num)
{	
	$num= $num*1.00;
	$dec = fmod($num,1);
	if($dec >0)
		return round($num,1);
	else
		return round($num);
}	

								
								
function find_po_status($num)
{	
	$status='';
	switch ($num) 
	{
		case 1 : $status ="Draft"; break;
		case 2 : $status ="In Approval"; break;
		case 3 : $status ="Rejected"; break;
		case 4 : $status ="<span class='text-info'>Placed</span>"; break;
		case 5 : $status ="<span class='text-warning'>Receipted</span>"; break;
		case 6 : $status ="<span class='text-success'>Completed</span>"; break;
		case 7 : $status ="<span class='text-danger'>Cancelled</span>"; break;
		
	}
	return $status;
}	

?>

