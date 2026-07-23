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

//$q = strtolower($_GET["q"]);
$item_id = $_POST['item_id'];
$supp_id = $_POST['supp_id'];
$qty = $_POST['si_qty'];
$SQL = "SELECT * FROM tbl_item_details  WHERE item_id=".$item_id." ";

$result = $conn->query($SQL);
if ($result->rowCount() > 0)

{
	echo'****';
	
	$obj1 = $result->fetch(PDO::FETCH_OBJ);
	
	$gst  = $dbconn->GetSingleReconrd("mst_hsn","igst","hsn_status = '1' AND hsn_id",$obj1->item_hsn);
	//GetLastRecord($tbl,$sf,$wf,$val,$order)
	$quo_id = $dbconn->GetLastRecord("tbl_po_quotation a,tbl_po_quotation_details b ","a.quo_id","a.quo_id = b.quo_id AND a.supp_id = '".$supp_id."' AND b.item_id",$item_id,"a.quo_id DESC");
	$quo_date = '';
	$unit_price = '';
	/*if($quo_id > 0){
		$quo_date = $dbconn->GetSingleReconrd("tbl_po_quotation","quo_date","quo_id",$quo_id);
		$unit_price = $dbconn->GetSingleReconrd("tbl_po_quotation_details","selling_price","item_id = ".$item_id." AND quo_id",$quo_id);
	}else{
		$unit_price = $obj1->item_cost_price;
		
	}*/
	
	$unit_price = $obj1->item_cost_price;
	
	
	$uom = $dbconn->GetSingleReconrd("mst_uom","uom_code","uom_status='1' AND uom_id",$obj1->item_uom);
	
	
	$po_unit_price = $unit_price;
	$po_vat = $gst;
	
		$price = ($po_unit_price * $qty);
		$tax_val = (($price * $po_vat) / 100);
		$net_val = $price + $tax_val;
	$no_gst = number_format($price,2,".","");
	$with_gst = number_format($net_val,2,".","");
	//echo $no_gst.'~'.$with_gst;
	
	echo trim("$po_unit_price~$gst~$uom~$no_gst~$with_gst~$obj1->item_hsn");
			
}
?>

