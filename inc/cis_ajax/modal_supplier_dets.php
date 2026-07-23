<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn= new dbhandler();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


if(isset($_REQUEST['id']) )
{
		$_REQUEST['supp_id'] = $_REQUEST['id'];
	
		$branch = '';

	$result = $conn->query("SELECT * FROM mst_supplier_new WHERE supp_id = '".$_REQUEST['supp_id']."'");	
	if ($result->rowCount()>0)
	{
		$obj = $result->fetch(PDO::FETCH_OBJ);
        if($obj->supp_type == 'S')
        {
            $supp_type = 'Supplier';
			//$br_add = '';
        }
        elseif($obj->supp_type == 'C')
        {
            $supp_type = 'Customer';
			$br_add = '';
			$result1 = $conn->query("SELECT * FROM mst_customer_branch WHERE supp_id = '".$_REQUEST['supp_id']."'");	
			if ($result1->rowCount()>0)
			{
				$branch = '';
					while ($obj1 = $result1->fetch(PDO::FETCH_OBJ)) {
						$br_add = '';
						 $br_add .= '<b>'.ucwords(strtolower($obj1->branch_name)). ', <br>' .ucwords(strtolower($obj1->branch_contact_person)). '</b> - <b>Ph : </b>' .$obj1->branch_contact_no. ', <br>' .$obj1->branch_add1. ' ,  
						 '.$obj1->branch_add2. ', <br>' .$dbconn->GetSingleReconrd("mst_city","city_name","city_status = 1 AND city_id ",$obj1->city_id).', 
						 '.$dbconn->GetSingleReconrd("mst_district","district_name","district_status = 1 AND district_id ",$obj1->district_id). ', 
						 <br>' .$dbconn->GetSingleReconrd("mst_state","state_name","state_status = 1 AND state_id ",$obj1->state_id). ' - ' .$obj1->branch_pincode. '. <br><br>'  ;
						//$br_add .=         $obj1->branch_add2.'<br>'; 
						$branch .= $br_add.'<hr class="p-1 m-0" style="border-top: 1px dashed;">';
					}

				
				
			}
			
        }

        $add = "";
        $add .= $obj->supp_add1;
        if($obj->supp_add2 != "")
        {
            $add .= ', '.$obj->supp_add2;
        }

        if($obj->city_id != "0")
        {
            $add .= ', <br/>'.$dbconn->GetSingleReconrd("mst_city","city_name","city_status = 1 AND city_id ",$obj->city_id);
        }

        if($obj->district_id != "0")
        {

            $add .=', '.$dbconn->GetSingleReconrd("mst_district","district_name","district_status = 1 AND district_id ",$obj->district_id);
        }


        $add .=', <br/>'.$dbconn->GetSingleReconrd("mst_state","state_name","state_status = 1 AND state_id ",$obj->state_id);

        $add .=' - '.$obj->supp_pincode.'.';
			
	}
		
			
	$html_output ="";
	$html_output .= '<div class="row pt-2">
						<div class="col-md-12">
							<p class="modal-heading">' . $obj->supp_name. '</p>
						</div>
					</div>	
					<div class="row">
						<div class="col-md-6">
							<b> Contact Person : </b>'. $obj->supp_contact_person1. '<br>
							<b> GST Number : </b>'. $obj->supp_gst. '<br>
							<b> PAN Number : </b>'. $obj->supp_pan. '<br><br>
						</div>
						<div id="comm_dets" class="col-md-6">
							<b> Email : </b>'. $obj->supp_email. '<br>
							<b> Mobile No. : </b>'. $obj->supp_mobile1. '<br>
							<b> Landline No. : </b>'. $obj->supp_landline1. '<br><br>
    					</div>
					</div>
					<hr class="p-1 m-0">
					<div class="row">
					    <div class="col-md-12">
						    <h6 class="modal-text"><b> Business Address <br></h6></b>'. $add. '<br><br>
						<div>
					</div>';
					if($obj->supp_type == 'C')
        {
			$html_output .= '					<hr class="p-1 m-0">

					<div class="row pt-2 mb-1">
					    <div class="col-md-12">
							<h6 class="modal-text"><b> Branch Address <br></h6></b>'.$branch.'<br>
						</div>
                    <div>';
		}
					
		$html_output .= '					<hr class="p-1 m-0">
				
				    ';
                         

                    
	
	echo $obj->supp_code .'~'.$html_output;
}
?>