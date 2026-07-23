<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
session_start();
$conn = new dbconnect();
$dbconn= new dbhandler();
if(isset($_POST['id']))
{
	$stmt = null;
	$stmt = $conn->prepare("SELECT * FROM tbl_item_details WHERE item_id = '".$_POST['id']."'");
	$stmt->execute();
	if($stmt->rowCount() > 0)
	{
		$obj = $stmt->fetch(PDO::FETCH_OBJ);
		
		$unit = $dbconn->GetSingleReconrd("mst_uom","uom_code","uom_status = '1' AND uom_id",$obj->item_uom);
	
		$gst  = $dbconn->GetSingleReconrd("mst_hsn","igst","hsn_status = '1' AND hsn_id",$obj->item_hsn);
	}
	echo $unit.'~'.$gst;
}

if($_POST['mode'] == 'get_net_amt')
{
	$qty = $_POST['qty'];
	$po_unit_price = $_POST['po_cost_price'];
	$po_vat = $_POST['po_vat'];
	$po_cgst = $_POST['po_cgst'];
	$po_sgst = $_POST['po_sgst'];
	$po_dis = $_POST['po_dis'];
	$item_price = ($po_unit_price * $qty);
	$tax_val = 0;
	$cost_price = $item_price;

	//if($po_dis > 0){
		//$dis_val = (($item_price * $po_dis) / 100);
		//$cost_price = $item_price - $dis_val;
	//}

	if($po_vat > 0){
		$tax_igst = (($cost_price * $po_vat) / 100);
	}
	if($po_cgst > 0 && $po_sgst > 0){
		$tax_cgst = (($cost_price * $po_cgst) / 100);
		$tax_sgst = (($cost_price * $po_sgst) / 100);
		$tax_val = $tax_cgst + $tax_sgst;
	}
	
	$net_val = $cost_price + $tax_val;

	$no_gst = number_format($cost_price,2,".","");
	$with_gst = number_format($net_val,2,".","");
	$dis_value = number_format($dis_val,2,".","");
	$tax_cgst_val = number_format($tax_cgst,2,".","");
	$tax_sgst_val = number_format($tax_sgst,2,".","");
	$tax_igst_val = number_format($tax_igst,2,".","");

	echo $no_gst.'~'.$with_gst.'~'.$dis_value.'~'.$tax_cgst_val.'~'.$tax_sgst_val.'~'.$tax_igst_val;
}
