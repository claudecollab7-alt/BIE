<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
$conn = new dbconnect();
$dbconn= new dbhandler();

if(isset($_POST['quo_pack_gst_id']))
{
	
	$quo_total_amt = $_POST['quo_total_amt'];
	 
	$quo_pack_per_fa =$_POST['quo_pack_per_fa'];
	$quo_pack_per_fa_value = $_POST['quo_pack_per_fa_value'];

	$quo_pack_gst_id = $_POST['quo_pack_gst_id'];
	$quo_pack_gst_per = $dbconn->GetSingleReconrd("mst_hsn","igst","hsn_id",$quo_pack_gst_id);

	if($quo_pack_per_fa == 1) // percent
	{
		$package_taxable_val = (($quo_total_amt * $quo_pack_per_fa_value) / 100);
		if($quo_pack_gst_per>0)
			$package_gst_val = (($package_taxable_val * $quo_pack_gst_per) / 100);
		else
			$package_gst_val = $package_taxable_val;
	}

	elseif($quo_pack_per_fa == 2) // FA
	{
		$package_taxable_val=0;
		if($quo_pack_gst_per>0)
			$package_gst_val = (($quo_pack_per_fa_value * $quo_pack_gst_per) / 100);
		else
			$package_gst_val = 0;
		
	}
	//print_r($_POST);
	echo $quo_pack_gst_per.'~'.$package_gst_val.'~'.$package_gst_val;


	// echo $balance.'~'.$dc_total_val;
}
?>