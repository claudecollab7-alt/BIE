<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
session_start();
$conn = new dbconnect();
$dbconn= new dbhandler();



if($_POST['mode'] == 'get_net_amt')
{
	$qty = $_POST['qty'];
	$quo_discount  = $_POST['quo_discount'];
	$quo_selling_price = $_POST['quo_selling_price'];
	$quo_vat = $_POST['quo_vat'];
	if($quo_vat > 0)
	{
		$price = ($quo_selling_price * $qty);

		if($quo_discount >0)
		{
			$discount_amt = (($price * $quo_discount) / 100);
			$dis = $price-$discount_amt;
		}
		else
		{
			$dis = $price;
		}

		$tax_val = (($dis * $quo_vat) / 100);
		$net_val = $dis + $tax_val;
	}
	else
	{
		$price = ($quo_selling_price * $qty);

		if($quo_discount >0)
		{
			$discount_amt = (($price * $quo_discount) / 100);
			$dis = $price-$discount_amt;
		}
		else
		{
			$dis = $price;
		}

		$net_val = $dis;
	}

	$no_gst = number_format($dis,2,".","");
	$with_gst = number_format($net_val,2,".","");
	echo $no_gst.'~'.$with_gst;
}	



if($_POST['mode'] == 'get_so_net_amt')
{
	$qty = $_POST['qty'];
	$so_discount  = $_POST['so_discount'];
	$so_selling_price = $_POST['so_selling_price'];
	$so_vat = $_POST['so_vat'];
	if($so_vat > 0)
	{
		$price = ($so_selling_price * $qty);

		if($so_discount >0)
		{
			$discount_amt = (($price * $so_discount) / 100);
			$dis = $price-$discount_amt;
		}
		else
		{
			$dis = $price;
		}

		$tax_val = (($dis * $so_vat) / 100);
		$net_val = $dis + $tax_val;
	}
	else
	{
		$price = ($so_selling_price * $qty);

		if($so_discount >0)
		{
			$discount_amt = (($price * $so_discount) / 100);
			$dis = $price-$discount_amt;
		}
		else
		{
			$dis = $price;
		}

		$net_val = $dis;
	}

	$no_gst = number_format($dis,2,".","");
	$with_gst = number_format($net_val,2,".","");
	echo $no_gst.'~'.$with_gst;
}
?>