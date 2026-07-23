<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn= new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);


if(isset($_REQUEST['id']) )
{
		$_REQUEST['item_id'] = $_REQUEST['id'];
	$result = $conn->query("SELECT * FROM tbl_item_details WHERE item_id = '".$_REQUEST['item_id']."'");	
	if ($result->rowCount()>0)
	{
		$obj1 = $result->fetch(PDO::FETCH_OBJ);
		
		$item_max_qty = $obj1->item_max_qty;
		$item_hsn = $dbconn->GetSingleReconrd("mst_hsn", "hsn_code", "hsn_id", $obj1->item_hsn);
		$item_uom = $dbconn->GetSingleReconrd("mst_uom", "uom_name", "uom_id", $obj1->item_uom);
		
		
		if($obj1->item_max_qty=='') $item_max_qty = '-'; else $item_max_qty = $obj1->item_max_qty;
		if($obj1->item_order_min_qty=='') $item_order_min_qty = '-'; else $item_order_min_qty = $obj1->item_order_min_qty;
		if($obj1->item_min_qty=='') $item_min_qty = '-'; else $item_min_qty = $obj1->item_min_qty;
		if($obj1->item_cost_price=='') $item_cost_price = '-'; else $item_cost_price = $obj1->item_cost_price;
		if($obj1->item_selling_price=='') $item_selling_price = '-'; else $item_selling_price = $obj1->item_selling_price;
		if($obj1->item_model_manufac=='') $item_model_manufac = '-'; else $item_model_manufac = $obj1->item_model_manufac;

		//if($item_category=='') $item_category = '-';
		//if($item_color=='') $item_color = '-';
		if($item_hsn=='') $item_hsn = '-';
		if($item_uom=='') $item_uom = '-';
		//if($item_brand_make=='') $item_brand_make = '-';

	}

	
			
	$html_output ="";
	$html_output .= ' 
	
	                <div class="row pt-2">
						<div class="col-md-4 ">';
					    	
								if($obj1->item_image!='')
									$html_output .=  '<img src="project_img/item_image/'. $obj1->item_image.'" width="150px" height="150px">';
								else
									$html_output .=  '<img src="project_img/no-image.jpg" width="150px" height="150px" >';
						$html_output .= '</div>
						<div class="col-md-8 pt-2"> 
						    <div class="row">
							    <div class="col-md-4 pt-2">
								<h6><b> Sales Code </b><br>
						            <b > Purchase Code </b><br></h6><br>
							        
						        </div>
								<div class="col-md-8 pt-2">
								<h6 class="modal-text"> '. $obj1->item_code.  '<br>
						            '.  $obj1->item_purchase_code. '</h6><br>
							        
						        </div>
							</div>	
						</div>   
                    </div>		
					
					';
$html_output .= '	<table class="table table-xs table-hover table-bordered mt-2 mb-2" id="history_table">
			<thead>
				<tr>
				    <th width="10%">Created On / By</th>
					<th width="10%">Item Price In Rs.</th>
					<th width="10%">Item Discount % </th>
					<th width="10%">Cost Price In Rs.</th>
					<th width="10%">Selling Price In Rs. </i></th>
					<th width="10%">MSQ</th>
					<th width="10%">MAQ</th>
					<th width="10%">MOQ</th>
				</tr>
			</thead>';

			$user_branch = $_SESSION['_user_branch'];
			$item_id = $_REQUEST['item_id'];

				$result_branch = $conn->query("SELECT * FROM mst_branch WHERE branch_id = $user_branch");
				$auto_id = $dbconn->GetMaxValue("tbl_itemprice_history", "auto_id", "branch_id", $user_branch);

				if ($result_branch->rowCount() > 0) {
					$branch_data = $result_branch->fetch(PDO::FETCH_OBJ);

					if ($auto_id != "")
		            {
						$result = $conn->query("SELECT  ".$branch_data->branch_old_maq." as old_item_max_qty , ".$branch_data->branch_old_price." as old_item_price,
						".$branch_data->branch_old_discount." as old_item_discount, ".$branch_data->branch_old_cost_price." as old_item_cost_price, ".$branch_data->branch_old_selling_price." as old_item_selling_price,
						".$branch_data->branch_old_msq." as old_item_min_qty, ".$branch_data->branch_old_moq." as old_item_order_min_qty, created_dtm FROM tbl_itemprice_history WHERE item_id = ".$item_id ." AND branch_id = ".$user_branch." Order by auto_id desc limit 10");	
				    }

					$result1 = $conn->query("SELECT ".$branch_data->branch_item_maq." as item_max_qty ,".$branch_data->branch_item_price." as item_price, ".$branch_data->branch_item_discount." as item_discount,
						".$branch_data->branch_item_cost_price." as item_cost_price, ".$branch_data->branch_item_selling_price." as item_selling_price, ".$branch_data->branch_item_msq." as item_min_qty, 
						".$branch_data->branch_item_moq." as item_order_min_qty FROM tbl_item_stock WHERE item_id = ".$item_id);	
						}
						if ($result1->rowCount()>0)
						{
							$res = $result1->fetch(PDO::FETCH_OBJ);	
							$item_id=$res->item_id;
							// $new_item_uom=$obj->new_item_uom;
							// $new_item_hsn=$obj->new_item_hsn;
							
							
																								
						}

					if ($result->rowCount()>0)
					{
						while($obj=$result->fetch())
							{
								$user_name = $dbconn->GetSingleReconrd("tbl_user","usr_name","usr_id",$obj->created_by);
							if($res->item_price != $obj->old_item_price)
							{
								$price = 'C.Price: '.$obj->old_item_price.'<br>N.Price: <span style="color:red"><b>'.$res->item_price.'</b></span>';
							}
							else{
								$price = 'C.Price: '.$obj->old_item_price.'<br> N.Price: '.$res->item_price.'</span>';
							}
							if($obj->ho_new_discount != $obj->old_item_discount)
							{
								$discount = 'C.Discount: '.$obj->old_item_discount.'<br>N.Discount: <span style="color:red"><b>'.$res->item_discount.'</b></span>';
							}
							else{
								$discount = 'C.Discount: '.$obj->old_discount.'<br>N.Discount: '.$res->item_discount.'';
							}
							
							if($res->item_cost_price != $obj->old_item_cost_price)
							{
								$cost_price = 'C.Cost Price: '.$obj->old_item_cost_price.'<br>N.Cost Price: <span style="color:red"><b>'.$res->item_cost_price.'</b></span>';
							}
							else{
								$cost_price = 'C.Cost Price: '.$obj->old_item_cost_price.'<br>N.Cost Price: '.$res->item_cost_price.'';
							}
							
							if($res->item_selling_price != $obj->old_item_selling_price)
							{
								$selling_price = 'C.Selling Price: '.$obj->old_item_selling_price.'<br>N.Selling Price: <span style="color:red"><b>'.$res->item_selling_price.'</b></span>';
							}
							else{
								$selling_price = 'C.Selling Price: '.$obj->old_item_selling_price.'<br>N.Selling Price: '.$res->item_selling_price.'</span>';
							}
							
							if($res->item_min_qty != $obj->old_item_min_qty)
							{
								$msq = 'C.MSQ: '.$obj->old_item_min_qty.'<br>N.MSQ: <span style="color:red"><b>'.$res->item_min_qty.'</b></span>';
							}
							else{
								$msq = 'C.MSQ: <span>'.$obj->old_item_min_qty.'<br>N.MSQ: '.$res->item_min_qty.'</span>';
							}
							if($res->item_max_qty != $obj->old_item_max_qty)
							{
								$maq = 'C.MAQ: '.$obj->old_item_max_qty.'<br>N.MAQ: <span style="color:red"><b>'.$res->item_max_qty.'</b></span>';
							}
							else{
								$maq = 'C.MAQ: '.$obj->old_item_max_qty.'<br>N.MAQ: '.$res->item_max_qty.'</span>';
							}
							if($res->item_order_min_qty != $obj->old_item_order_min_qty)
							{
								$moq = 'C.MOQ: '.$obj->old_item_order_min_qty.'<br>N.MOQ: <span style="color:red"><b>'.$res->item_order_min_qty.'</b></span>';
							}
							else{
								$moq = 'C.MOQ: '.$obj->old_item_order_min_qty.'<br>N.MOQ: '.$res->item_order_min_qty.'</span>';
							}
							
							$html_output .= '<tr>
											<td><b>'.date('d-M-y @ h:i a',strtotime($obj->created_dtm)).'</b><br><samll>'.$user_name.'</samll>'.'</td>
											<td>'.$price.'</td>
											<td>'.$discount.'</td>
											<td>'.$cost_price.'</td>
											<td>'.$selling_price.'</td>
											<td>'.$msq.'</td>
											<td>'.$maq.'</td>
											<td>'.$moq.'</td>
											</tr>';		
									
						}
				}
					else
					{
						$html_output .= '<tr><td colspan="9" align="center">No History</td></tr>';
					}
					
	$html_output .= '</table>';     	

                    
	
	echo $obj1->item_desciption .'~'.$html_output;
}
?>


