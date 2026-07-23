<?php
ob_start();
date_default_timezone_set('Asia/Calcutta');

$ip=$_SERVER['REMOTE_ADDR']; 


function isAdmin()
{
	if(isset($_SESSION['_user']))
	{
	    if($_SESSION['_user'] != "crm_user")
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

