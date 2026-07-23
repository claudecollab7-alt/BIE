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
	$tax_item_selling_price = '';	
	if ($result->rowCount()>0)
	{
		$obj = $result->fetch(PDO::FETCH_OBJ);
		
		$item_max_qty = $obj->item_max_qty;
		$item_hsn = $dbconn->GetSingleReconrd("mst_hsn", "hsn_code", "hsn_id", $obj->item_hsn);
		$igst = $dbconn->GetSingleReconrd("mst_hsn", "igst", "hsn_id", $obj->item_hsn);
		$item_uom = $dbconn->GetSingleReconrd("mst_uom", "uom_name", "uom_id", $obj->item_uom);
		$item_division  = $dbconn->GetSingleReconrd("mst_division", "division_name", "division_id", $obj->item_division);
		$item_category = $dbconn->GetSingleReconrd("mst_category", "category_name", "category_id", $obj->item_category);
		$item_principal = $dbconn->GetSingleReconrd("mst_principal","principal_name","principal_id",$obj->item_principal);
		$item_color = $dbconn->GetSingleReconrd("mst_color","color_name","color_id",$obj->item_color);
		$item_brand_make = $dbconn->GetSingleReconrd("mst_brand","brand_name","brand_id",$obj->item_brand_make);
		//if($obj->item_type == 0) $item_type = 'Individual Trading';
		//else $item_type = 'Group Trading';
		$branch_item_selling_price = $dbconn->GetSingleReconrd("mst_branch","branch_item_selling_price","branch_id",$_SESSION['_user_branch']);
		$item_order_min_qty = $dbconn->GetSingleReconrd("mst_branch","branch_item_maq","branch_id",$_SESSION['_user_branch']);
		$branch_item_price = $dbconn->GetSingleReconrd("mst_branch","branch_item_price","branch_id",$_SESSION['_user_branch']);
		$branch_item_discount = $dbconn->GetSingleReconrd("mst_branch","branch_item_discount","branch_id",$_SESSION['_user_branch']);
		$branch_item_cost_price = $dbconn->GetSingleReconrd("mst_branch","branch_item_cost_price","branch_id",$_SESSION['_user_branch']);
		$item_max_qty = $dbconn->GetSingleReconrd("mst_branch","branch_item_moq","branch_id",$_SESSION['_user_branch']);
		$branch_item_msq = $dbconn->GetSingleReconrd("mst_branch","branch_item_msq","branch_id",$_SESSION['_user_branch']);

		$item_order_min_qty = $dbconn->GetSingleReconrd("tbl_item_stock", "$item_order_min_qty", "item_id", $obj->item_id);
		$item_selling_price = $dbconn->GetSingleReconrd("tbl_item_stock", "$branch_item_selling_price", "item_id", $obj->item_id);
		$item_price = $dbconn->GetSingleReconrd("tbl_item_stock", "$branch_item_price", "item_id", $obj->item_id);
		$item_discount = $dbconn->GetSingleReconrd("tbl_item_stock", "$branch_item_discount", "item_id", $obj->item_id);
		$item_cost_price  = $dbconn->GetSingleReconrd("tbl_item_stock", "$branch_item_cost_price", "item_id", $obj->item_id);
		$item_max_qty  = $dbconn->GetSingleReconrd("tbl_item_stock", "$item_max_qty", "item_id", $obj->item_id);
		$item_min_qty  = $dbconn->GetSingleReconrd("tbl_item_stock", "$branch_item_msq", "item_id", $obj->item_id);
		
		if($obj->item_max_qty=='') $item_max_qty = '-'; else $item_max_qty = $item_max_qty;
		if($obj->item_order_min_qty=='') $item_order_min_qty = '-'; else $item_order_min_qty = $item_order_min_qty;
		if($obj->item_min_qty=='') $item_min_qty = '-'; else $item_min_qty = $item_min_qty;
		if($obj->item_cost_price=='') $item_cost_price = '-'; else $item_cost_price = $item_cost_price;
		if($obj->item_selling_price=='') $item_selling_price = '-'; else $item_selling_price = $item_selling_price;
		if($obj->item_model_manufac=='') $item_model_manufac = '-'; else $item_model_manufac = $obj->item_model_manufac;

		if($item_category=='') $item_category = '-';
		if($item_color=='') $item_color = '-';
		if($item_hsn=='') $item_hsn = '-';
		if($item_uom=='') $item_uom = '-';
		if($item_brand_make=='') $item_brand_make = '-';

		$with_tax = $item_selling_price * $igst / 100;

		$tax_item_selling_price = $item_selling_price + $with_tax;



	}
			
	$html_output ="";
	$html_output .= ' <div class="row pt-2">
						<p class="modal-heading pl-2 ">'. strtoupper($obj->item_desciption).'</p>
						
					</div>
					<div class="row pt-2">
						<div class="col-md-4 ">';
					    	
								if($obj->item_image!='')
									$html_output .=  '<img src="project_img/item_image/'. $obj->item_image.'" width="150px" height="150px">';
								else
									$html_output .=  '<img src="project_img/no-image.jpg" width="150px" height="150px" >';
						$html_output .= '</div>
						<div class="col-md-8 pt-2"> 
						    <div class="row">
							    <div class="col-md-4 pt-2">
								<h6><b> Sales Code </b><br>
						            <b > Purchase Code </b><br></h6><br>
							        <b> Principal </b><br>
							        <b> Division </b><br>
							        <b> Net Amount </b><br>
						        </div>
								<div class="col-md-8 pt-2">
								<h6 class="modal-text"> '. $obj->item_code.  '<br>
						            '.  $obj->item_purchase_code. '</h6><br>
							        '. $item_principal. '<br>
							        '.  $item_division. '<br>
									<span style="color:blue; font-size:20px;"><b>'. number_format($tax_item_selling_price,2). '</b></span><br>

						        </div>
							</div>	
						</div>   
                    </div>	
                    <hr class="mt-4 mb-1">	
                    <div class="row">
                        <div class="col-md-6 pt-2">
							<div class="row">
								<div class="col-md-6">
									<b> Brand/Make </b><br>
									<b> UOM </b><br>
									<b> HSN </b><br>
								</div>
								<div class="col-md-6">
									' . $item_brand_make. '<br>
									' . $item_uom. '<br>
									'.  $item_hsn. ' ('.$igst.' %)<br>
								</div>
							</div>
						</div>					
						<div class="col-md-6 pt-2">
						    <div class="row">
						        <div class="col-md-6">
							        <b> Color </b><br>
							        <b> Category </b><br>
							        <b> Model No. </b><br>
						        </div>
								<div class="col-md-6">
								    '. $item_color. '<br>
							        '. $item_category. '<br>
							        '. $item_model_manufac. '<br>
								</div>
							</div>
						</div>
                    </div>						
				    <hr class="mt-4 mb-1">
					<div class="row pt-2">';
						// if($_SESSION['_user_id'] != '6' && $_SESSION['_user_branch']==1){ 
							$html_output .= '<div class="col-md-6 pt-2">
								<div class="row">
									<div class="col-md-6">
										<b> Selling Price </b><br>
										<b> Cost Price </b><br>
									</div>
									<div class="col-md-6">
										'. $item_selling_price. '<br>
										'. $item_cost_price. '<br>
									</div>
								</div>
							</div>	';
						// }else{
						// 	$html_output .= '<div class="col-md-6 pt-2">
						// 		<div class="row">
						// 			<div class="col-md-6">
						// 				<b> Selling Price </b><br>
										
						// 			</div>
						// 			<div class="col-md-6">
						// 				'. $item_selling_price. '<br>
										
						// 			</div>
						// 		</div>
						// 	</div>	';
						// }
						$html_output .= '<div class="col-md-6 pt-2">
                            <div class="row">
                                <div class="col-md-6">							
							        <b> MSQ </b><br>
							        <b> MOQ </b><br>
							        <b> MAQ </b><br>
								</div>
								<div class="col-md-6">							
							        '. $item_min_qty. '<br>
							        '. $item_order_min_qty. '<br>
							        '. $item_max_qty. '<br>
								</div>
							</div>	
						</div>
                    </div>
					<hr class="mt-4 mb-1">   
					<div class="row pt-2">
                        <div class="col-md-12 pt-2">
                            <div class="row">						
						        <div class="col-md-12">
							        <b> Remarks </b><br>
						        </div>
							    <div class="col-md-12">
							        '.  $item_remarks. '<br>
						        </div>
							<div>
						</div>
                        						
                    </div>
					<hr class="mt-4 mb-1">   
					';
                         	

                    
	
	echo $obj->item_code .'~'.$html_output;
}
?>