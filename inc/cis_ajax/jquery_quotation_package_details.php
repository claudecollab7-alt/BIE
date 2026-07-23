<?php
session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");


$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
$pack_id=$_POST['pack_id'];

if ($_POST['mode'] == 'save' && isset($_POST['pack_id'])) 
{
	
	$row = '';
	
	$pack_id = trim($_POST['pack_id']);
	$quo_pack_per_fa = $_POST['quo_pack_per_fa'];
	$quo_pack_per_fa_value = $_POST['quo_pack_per_fa_value'];
	$quo_pack_gst_id = $_POST['quo_pack_gst_id']; 
	$quo_pack_gst_per = $_POST['quo_pack_gst_per'];
	$quo_pack_gst_amt = $_POST['quo_pack_gst_amt'];
	$quo_pack_taxable_value = $_POST['quo_pack_taxable_value'];

	if($quo_pack_gst_per>0)
	$quo_pack_gst_amt1 = ($quo_pack_taxable_value * $quo_pack_gst_per) /100;
	
	else
	$quo_pack_gst_amt1=0;

	$quo_pack_gst_id = $dbconn->GetSingleReconrd("mst_hsn","hsn_code","hsn_id",$quo_pack_gst_id);
	if($quo_pack_per_fa == 1)
	{	
		// %
		$quo_pack_total = (float)$quo_pack_taxable_value + (float)$quo_pack_gst_amt1;
		
		
		$display = $quo_pack_per_fa_value . ' %';
	}
	elseif($quo_pack_per_fa == 2)
	{
		// Fixed Amount
		
		$quo_pack_total = (float)$quo_pack_per_fa_value + (float)$quo_pack_gst_amt;	

		$display = $quo_pack_per_fa_value . ' - FA';
		$quo_pack_taxable_value = $quo_pack_per_fa_value;
	}
	
	$row .= '<tr id="PK_' . $pack_id . '" >																	
		
                <td>' . $dbconn->GetSingleReconrd("mst_quo_details", "quo_pack_decp", "quo_id", $pack_id) . '
					<input type="hidden" class="pack_id" name="pack_id[]" value="' . $pack_id . '" />
					<input type="hidden" class="quo_pack_gst_id" name="quo_pack_gst_id[]" value="' . $quo_pack_gst_id . '" >
					<input type="hidden" class="quo_pack_taxable_val" name="quo_pack_taxable_val[]" value="' . $quo_pack_taxable_value . '" />
					<input type="hidden" class="quo_pack_total" name="quo_pack_total[]" id="quo_pack_total" value="' . $quo_pack_total . '" />	
				</td>

				<td>'.$display.'
					<input type="hidden" class="quo_pack_per_fa" name="quo_pack_per_fa[]" value="' . $quo_pack_per_fa . '" >
					<input type="hidden" class="quo_pack_per_fa_value" name="quo_pack_per_fa_value[]" value="' . $quo_pack_per_fa_value . '" />
					<input type="hidden" class="quo_pack_gst_amt" name="quo_pack_gst_amt[]" value="' . $quo_pack_gst_amt . '" >
				</td>
				
				<td class="text-right disp_tax_val">' .number_format($quo_pack_taxable_value,2)  . '</td>
                <td class="text-right">' . $quo_pack_gst_id . '</td>
					
              
				<td class="text-right">' .$quo_pack_gst_per  . '
				<input type="hidden" class="quo_pack_gst_per" name="quo_pack_gst_per[]" value="' . $quo_pack_gst_per . '" />
				</td>				

				<td class="text-right disp_gst_amt">' .number_format($quo_pack_gst_amt,2) . '</td>
					
				<td class="text-right disp_pack_total">' .number_format($quo_pack_total,2) . '</td>
					
				
				<td class="text-center">
                <a href="javascript:remove_item1(' . $pack_id . ');" class="" rel="pk_' . $pack_id . '"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a> </td>
					
			</tr>';
			
}
echo $row;
die;



