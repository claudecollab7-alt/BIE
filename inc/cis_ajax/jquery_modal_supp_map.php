<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

if (isset($_REQUEST['id'])) {

	$result = $conn->query("SELECT * FROM tbl_item_details WHERE item_id='" . $_REQUEST['id'] . "'");
   
   
	$supp = '<div class=" row" id="">
   <div class="col-lg-12">
		<label class="" style="text-align:left;">Supplier Mapping</b></b></label>
    </div>
<div>';


	$supp_map .= '<div class=" pb-2 pt-2" style="">
	
			<div class=" pb-3 pt-2">
				<table class=" table table-xs table-bordered" style="font-size: small !important;" >	

					<thead style="width:10%;">
					<tr class="bg-teal">
						<th>S No.</th>
						<th>Supplier Mapping</th>
						</tr>
					</thead>';
	if ($result->rowCount() > 0) {

        
		$supp_map .= "<tbody>";
		while ($obj = $result->fetch()) 
        {
            $supp_id = $dbconn->GetSingleReconrd("tbl_item_details", "supp_id", "supp_id !='' AND item_id", $obj->item_id);
            
            $iSno=1;
            $supp_id =  explode(',', $supp_id );
            foreach ($supp_id as $supplier) 
            {
                
                $supp_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_type='S' AND supp_id", $supplier);
				$supp_code = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_code", "supp_type='S' AND supp_id", $supplier);


                $supp_map .= '<tr class="align-left" >					
                                    <td>'.$iSno.'</td>
                                    <td >' . $supp_name . ' - '.$supp_code.'</td>								
                            </tr>';
                $iSno++;
            }
            
             
            
        }
		$supp_map .= "</tbody>";
	}

	$supp_map .= '</table>
			

        </div>       
    </div>';
	echo $supp . '~' .$supp_map ;
}
