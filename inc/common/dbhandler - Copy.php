<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables 
 */

//$dbconn = new dbconnect();


ini_set('display_errors', 0);
 

class dbhandler
{

    private $conn;

	public function __construct()
	{	
		try
		{

			require_once dirname(__FILE__) . '/config.php';		

			$this->conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USERNAME ,DB_PASSWORD);		

			$this->conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);        

			$this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

			$this->conn->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

		}

		catch(PDOException  $e)
		{
			echo "Error Connecting Host :" .$e->getMessage();
		}

		catch(Exception  $e)
		{
			echo $e->getMessage();
		}
	}

	

	/*

	public function __construct($dbconn)

	{

		$this->conn = $dbconn;

	}

	 $args = func_get_args(); //any function that calls this method can take an arbitrary number of parameters

        switch(func_num_args())

        {

            //delegate to helper methods

        case 0:

            $this->construct0();

        break;

        case 1:

            $this->construct1($args[0]);

        break;

        case 2:

            $this->construct2($args[0], $args[1]);

        break;

        default:

            trigger_error('Incorrect number of arguments for Foo::__construct', E_USER_WARNING);

        }

	**/
	
	public function findAcctsDate($site)
	{
		/*$dt = GetSingleReconrd("mst_dayclosure","closure_date",",1);
		if ($dt =="" || is_null($dt))
		{
			$dt= date('Y-m-d');
		}
		return $dt;*/
		
		$SQL = " SELECT closure_date from mst_dayclosure WHERE site_id=".$site." AND active =1";
		$res = $this->conn->query($SQL);	

		$sf_value = $res->fetchColumn();

		return $sf_value;
	}
	

	public function GetLastRecord($tbl,$sf,$wf,$val,$order)
	{
		try
	   {
	      $SQL = "SELECT ".$sf." FROM ".$tbl." WHERE ".$wf." = '".$val."' ORDER BY ".$order." LIMIT 1";
	      $res = $this->conn->query($SQL);

		// echo $SQL;
		
		  $sf_value ='';
		   
		   if ($res->rowCount() > 0)
			$sf_value = $res->fetchColumn();
    
	    	return $sf_value;	
	   }
	    catch(Exception  $e)
		{
			echo $e->getMessage();
		}
	}

		

	public function GetSingleReconrd($tbl,$sf,$wf,$val)

	{	

	   try
	   {
	      $SQL = "SELECT ".$sf." FROM ".$tbl." WHERE ".$wf." = '".$val."' ";

	      $res = $this->conn->query($SQL);

		// echo $SQL;
		
		  $sf_value ='';
		    if ($res->rowCount() > 0)
			$sf_value = $res->fetchColumn();
    
	    	return $sf_value;	
	   }
	    catch(Exception  $e)
		{
			echo $e->getMessage();
		}
	}

	

	public function GetCount($tbl,$wf,$val)

	{

		$nos = 0;

		$SQL = "SELECT COUNT(*) as records_count FROM ".$tbl." WHERE ".$wf." = ".$val." ";		

		$res = $this->conn->query($SQL);			

		

		$records_count = $res->fetchColumn();

		return $records_count;		

	}



	public function GetCountDistinct($tbl,$wf,$val,$fld){

		$nos = 0;

		$SQL = "SELECT COUNT(DISTINCT ".$fld.") as records_count  FROM ".$tbl." WHERE ".$wf." = ".$val." ";

		$res = $this->conn->query($SQL);



		$records_count = $res->fetchColumn();

		return $records_count;		

	}



	public function GetMaxValue($tbl,$sf,$wf,$val){

		$SQL = "SELECT MAX(".$sf.") AS val FROM ".$tbl." WHERE ".$wf." = '".$val."'";

		$result = $this->conn->query($SQL);

		if ($result->rowCount() > 0)

		$obj5 = $result->fetch();

		return $obj5->val;

	}

	



	public function fnFillComboFromTable_Where( $field1, $field2, $table, $field3, $where )
	{

		$strOption = ""; $result = ""; $SQL = "";


		$SQL = "SELECT $field1 AS a,$field2 AS b FROM $table $where ORDER BY $field3";

		$result = $this->conn->query($SQL);
		

		if ($result->rowCount() > 0)
		{

			while ($obj = $result->fetch())
			{

				$strOption .="<option value=\"". $obj->a ."\">". $obj->b ."</option>";		
			}
			return $strOption;
		}
	}

	

	

	

	/*** CRUD Functions for any Table ***/

	public function db_InsertRecord($tbl,$data)

	{

		echo print_r($data);

		try

		{

			$sql='insert into '.$tbl .' (`'.implode( '`,`', array_keys( $data ) ) .'`) values (:'.implode(',:',array_keys( $data ) ).');';

			foreach( $data as $field => $value ) $params[":{$field}"]=$value;

			$statement = $this->conn->prepare( $sql );

			$statement->execute( $params );

			

			return 1;

		}

		catch(Exception $e)

		{

			return $e->getMessage();

		}

		

	}
	
	
	public function GetSuppPostalAddress($supp_id){	
		$sql="SELECT postal_adr1,postal_adr2,postal_district,postal_city,postal_state,postal_zip,physical_adr1,physical_adr2,physical_district,physical_city,physical_state,physical_zip,supp_gst_state_code  FROM mst_supplier WHERE supp_id  = ".$supp_id;
		$result = $this->conn->query($sql);
		$obj5 = $result->fetch();
		$add = "";
		if($obj5->postal_adr1 != ""){
			$add .= $obj5->postal_adr1;
		}
		if($obj5->postal_adr2 != ""){
			$add .= ", ".$obj5->postal_adr2;
		}
		if($obj5->postal_district != ""){$add .= ",<br> ".$obj5->postal_district;}
		if($obj5->physical_city != ""){$add .= ", ".$obj5->physical_city;}
		//$add .= ",<br> ".GetSingleReconrd("".CDB.".mst_pincode","pc_area","pc_id",$obj5->supp_pincode);
		$add .= ",<br> ".$this->GetSingleReconrd("mst_state","state_name","state_id",$obj5->postal_state);
		if($obj5->postal_zip != ""){
			$add .= " - ".$obj5->postal_zip;
		}
		$add .= '<br><b>State Code: </b>'.$obj5->supp_gst_state_code;
		return $add;
	}
	public function GetSSPPostalAddress($supp_id){	
		$sql="SELECT postal_adr1,postal_adr2,postal_district,postal_city,postal_state,postal_zip,physical_adr1,physical_adr2,physical_district,physical_city,physical_state,physical_zip,supp_gst_state_code  FROM mst_admin_dets WHERE supp_id  = ".$supp_id;
		$result = $this->conn->query($sql);
		$obj5 = $result->fetch();
		$add = "";
		if($obj5->postal_adr1 != ""){
			$add .= $obj5->postal_adr1;
		}
		if($obj5->postal_adr2 != ""){
			$add .= ", ".$obj5->postal_adr2;
		}
		if($obj5->postal_district != ""){$add .= ",<br> ".$obj5->postal_district;}
		if($obj5->physical_city != ""){$add .= ", ".$obj5->physical_city;}
		//$add .= ",<br> ".GetSingleReconrd("".CDB.".mst_pincode","pc_area","pc_id",$obj5->supp_pincode);
		$add .= ",<br> ".$this->GetSingleReconrd("mst_state","state_name","state_id",$obj5->postal_state);
		if($obj5->postal_zip != ""){
			$add .= " - ".$obj5->postal_zip;
		}
		$add .= '<br><b>State Code: </b>'.$obj5->supp_gst_state_code;
		return $add;
	}
	public function GetSuppPhysicalAddress($supp_id){
	
		$sql="SELECT physical_adr1,physical_adr2,physical_district,physical_city,physical_state,physical_zip,supp_gst_state_code  FROM mst_supplier WHERE supp_id  = ".$supp_id;
		$result = $this->conn->query($sql);
		$obj5 = $result->fetch();
		$add = "";
		if($obj5->physical_adr1 != ""){
			$add .= $obj5->physical_adr1;
		}
		if($obj5->physical_adr2 != ""){
			$add .= ", ".$obj5->physical_adr2;
		}
		if($obj5->physical_district != ""){$add .= ",<br> ".$obj5->physical_district;}
		if($obj5->physical_city != ""){$add .= ", ".$obj5->physical_city;}
		//$add .= ",<br> ".GetSingleReconrd("".CDB.".mst_pincode","pc_area","pc_id",$obj5->supp_pincode);
		$add .= ",<br> ".$this->GetSingleReconrd("mst_state","state_name","state_id",$obj5->physical_state);
		if($obj5->physical_zip != ""){
			$add .= " - ".$obj5->physical_zip;
		}
			$add .= '<br><b>State Code: </b>'.$obj5->supp_gst_state_code;
		return $add;
	}
	public function GetCusShippingAddress($cus_id){	
		$sql="SELECT shipp_adr1,shipp_adr2,shipp_district,shipp_city,shipp_state,shipp_zip,shipp_adr1,shipp_adr2,shipp_district,shipp_city,shipp_state,shipp_zip,cus_gst_state_code FROM mst_customer WHERE cus_id  = ".$cus_id;
		$result = $this->conn->query($sql);
		//echo $sql;
		$obj5 = $result->fetch();
		$add = "";
		if($obj5->shipp_adr1 != ""){
			$add .= $obj5->shipp_adr1;
		}
		if($obj5->shipp_adr2 != ""){
			$add .= ", ".$obj5->shipp_adr2;
		}
		if($obj5->shipp_district != ""){$add .= ",<br> ".$obj5->shipp_district;}
		if($obj5->shipp_city != ""){$add .= ", ".$obj5->shipp_city;}
		//$add .= ",<br> ".GetSingleReconrd("".CDB.".mst_pincode","pc_area","pc_id",$obj5->supp_pincode);
		$add .= ",<br> ".$this->GetSingleReconrd("mst_state","state_name","state_id",$obj5->shipp_state);
		if($obj5->shipp_zip != ""){
			$add .= " - ".$obj5->shipp_zip;
		}
		$add .= '<br><b>State Code: </b>'.$obj5->cus_gst_state_code;
		return $add;
	}
	public function GetCusBillingAddress($cus_id){
	
		$sql="SELECT bill_adr1,bill_adr2,bill_district,bill_city,bill_state,bill_zip,cus_gst_state_code FROM mst_customer WHERE cus_id  = ".$cus_id;
		$result = $this->conn->query($sql);
		$obj5 = $result->fetch();
		$add = "";
		if($obj5->bill_adr1 != ""){
			$add .= $obj5->bill_adr1;
		}
		if($obj5->bill_adr2 != ""){
			$add .= ", ".$obj5->bill_adr2;
		}
		if($obj5->bill_district != ""){$add .= ",<br> ".$obj5->bill_district;}
		if($obj5->bill_city != ""){$add .= ", ".$obj5->bill_city;}
		//$add .= ",<br> ".GetSingleReconrd("".CDB.".mst_pincode","pc_area","pc_id",$obj5->supp_pincode);
		$add .= ",<br> ".$this->GetSingleReconrd("mst_state","state_name","state_id",$obj5->bill_state);
		if($obj5->bill_zip != ""){
			$add .= " - ".$obj5->bill_zip;
		}
			$add .= '<br><b>State Code: </b>'.$obj5->cus_gst_state_code;
		return $add;
	}	
	


}

   

   /* public function getProfileDetails($user_id)	{

        $stmt = $this->conn->prepare("SELECT u.usr_id, u.usr_code, u.prefix, u.usr_name, u.usr_type, u.usr_telephone, u.usr_mobile, u.usr_email, u.usr_cr_add, u.usr_cr_add1, u.usr_cr_city, s.state_name, c.country_name, u.usr_cr_pincode  FROM tbl_user u, mst_state s, mst_country c WHERE u.usr_id = ? AND u.usr_cr_state=s.state_id AND u.usr_cr_country = c.country_id");

        $stmt->bind_param("i", $user_id);

        $stmt->execute(); 

        $stmt->bind_result($user_id, $user_code, $prefix, $user_name, $user_type, $phone, $mobile, $email, $address1, $address2, $city, $state, $country, $pincode);

        $userdata = array(); 

         while ($stmt->fetch()) {

            $userdata["user_id"] = $user_id;

            $userdata["user_code"] = $user_code;

            $userdata["prefix"] = $prefix;

            $userdata["user_name"] = $user_name;

            $userdata["user_type"] = $user_type;

            $userdata["phone"] = $phone;

            $userdata["mobile"] = $mobile;

            $userdata["email"] = $email;

            $userdata["address_1"] = $address1;

            $userdata["address_2"] = $address2;

            $userdata["city"] = $city;

            $userdata["state"] = $state;

            $userdata["country"] = $country;

            $userdata["pincode"] = $pincode;

           }

        return $userdata;

    }



    public function getUserPassword($user_id){

        $userPassword = "";

        $stmt = $this->conn->prepare("SELECT usr_logpwd from tbl_user where usr_id = ?");

        $stmt->bind_param("i", $user_id);

        $stmt->execute(); 

        $stmt->bind_result($usrPassword);

        while ($stmt->fetch()) {

            $userPassword = $usrPassword;

        }

        return $userPassword;

    }*/

        





?>