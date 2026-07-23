<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
// require_once ("inc/common/dbrowiterator.php");	

isAdmin();


//ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);

$conn = new dbconnect();
$dbconn = new dbhandler();

$inv_date = date("Y-m-d");


if ($_REQUEST['inv_id'] != "") {
	$dbconn = new dbhandler();
	$result = $conn->query("SELECT * FROM tbl_invoice WHERE inv_id = " . $_REQUEST['inv_id']);
	if ($result->rowCount() > 0) {
		$obj = $result->fetch(PDO::FETCH_OBJ);

		$supp_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_status = 1 AND supp_id ", $obj->supp_id);

		$supp_gst = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_gst", "supp_status = 1 AND supp_id ", $obj->supp_id);

		$supp_pan = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_pan", "supp_status = 1 AND supp_id ", $obj->supp_id);

		if ($obj->inv_date != "0000-00-00" && $obj->inv_date != "") {
			$inv_date = date("d-m-Y", strtotime($obj->inv_date));
		}

		

		$dc_no = '';
		

		if ($obj->inv_dc_date != "0000-00-00" && $obj->inv_dc_date != "") {
			$dc_date = date("d-m-Y", strtotime($obj->inv_dc_date));
		}
		else
		{
			$dc_date='';	
		}

		$so_no = '';
		$so_dt = '';

		if ($so_dt != "0000-00-00" && $so_dt != "") {
			$so_date = date("d-m-Y", strtotime($so_dt));
		}
	}

	
	$get_add = $conn->query("SELECT * FROM mst_supplier_new WHERE supp_id = " . $obj->supp_id);
	if ($get_add->rowCount() > 0) {
		$obj1 = $get_add->fetch(PDO::FETCH_OBJ);
		// print_r($obj1);
		$add = "";
		$add .= $obj1->supp_add1;
		if ($obj1->supp_add2 != "") {
			$add .= ', ' . $obj1->supp_add2;
		}
		$add .= ', <br/>' . $dbconn->GetSingleReconrd("mst_city", "city_name", "city_status = 1 AND city_id ", $obj1->city_id);

		$add .= ', ' . $dbconn->GetSingleReconrd("mst_district", "district_name", "district_status = 1 AND district_id ", $obj1->district_id);

		$add .= ', <br/>' . $dbconn->GetSingleReconrd("mst_state", "state_name", "state_status = 1 AND state_id ", $obj1->state_id);

		$add .= ' - ' . $obj1->supp_pincode . '.';

		$state_code1 = $dbconn->GetSingleReconrd("mst_state", "state_code", "state_status = 1 AND state_id ", $obj1->state_id);
	}

	if($obj->cus_branch_id > 0){
		$get_add2 = $conn->query("SELECT * FROM  mst_customer_branch WHERE branch_id = " . $obj->cus_branch_id);
		if ($get_add2->rowCount() > 0) {
			$obj1 = $get_add2->fetch(PDO::FETCH_OBJ);
			// print_r($obj1);
			$supp_name2 = $supp_name.' - '.$obj1->branch_name;
			$add2 = "";
			$add2 .= $obj1->branch_add1;
			if ($obj1->branch_add2 != "") {
				$add2 .= ', ' . $obj1->branch_add2;
			}
			$add2 .= ', <br/>' . $dbconn->GetSingleReconrd("mst_city", "city_name", "city_status = 1 AND city_id ", $obj1->city_id);
	
			$add2 .= ', ' . $dbconn->GetSingleReconrd("mst_district", "district_name", "district_status = 1 AND district_id ", $obj1->district_id);
	
			$add2 .= ', <br/>' . $dbconn->GetSingleReconrd("mst_state", "state_name", "state_status = 1 AND state_id ", $obj1->state_id);
	
			$add2 .= ' - ' . $obj1->branch_pincode . '.';
	
			$state_code1 = $dbconn->GetSingleReconrd("mst_state", "state_code", "state_status = 1 AND state_id ", $obj1->state_id);
		}
	}else{
		$add2 = $add;
		$supp_name2 = $supp_name;
	}
	$company_add = $conn->query("SELECT * FROM  mst_branch WHERE branch_id = " . $obj->branch_id);
    if ($company_add->rowCount() > 0) {
        $res = $company_add->fetch(PDO::FETCH_OBJ);
        // print_r($obj1);
        $address = $res->company_address;
        $address .= '<br><b>PH : </b> +91' .$res->company_ph_no1 .' / '.$res->company_ph_no2;
        $address .= '<br><b>E-Mail : </b>'.$res->company_mail;
        $address .= '<br><b>Web : </b>'.$res->company_web;

        $gst_no = $res->company_gst;
        $pan_no = $res->company_pan;
        $branch_state_code = $res->branch_state_code;

    }
}



?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo PAGE_TITLE; ?> - Invoice</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<?php include_once("inc/common/css-js.php"); ?>
<script type="text/javascript" src="print_me.js"></script>

<script src="js/html2pdf.bundle.min.js"></script>

<script language="javascript">
    $(function() {
        $("body").on("click", "#cmd", function() {

            var element = document.getElementById('print_content1');
            //html2pdf(element);
            var opt = {
                margin: 0.5,
                filename: '<?php echo $obj->inv_refno; ?>' + '.pdf',
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 2,
                    logging: true
                },
                jsPDF: {
                    unit: 'cm',
                    format: 'A4',
                    orientation: 'portrait'
                }
            };

            // New Promise-based usage:
            html2pdf().set(opt).from(element).save();
            // Old monolithic-style usage:
            //html2pdf(element, opt);
        });


    });
</script>
</head>

<script type="text/javascript">
	$(function() {

		<?php
		if (isset($_SESSION['_msg']) && $_SESSION['_msg'] != "") {
			echo "$.jGrowl('" . $_SESSION['_msg'] . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-success', position: 'bottom-right', life:'2000', header: 'Success!' });";
			$_SESSION['_msg'] = "";
		}
		if (isset($_SESSION['_msg_err']) && $_SESSION['_msg_err'] != "") {
			echo "$.jGrowl('" . $_SESSION['_msg_err'] . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-danger', position: 'top-right', shutdown:'3000', header: 'Error!' });";
			$_SESSION['_msg_err'] = "";
		}
		?>



	});
</script>

<body>
	<!-- Main navbar -->
	<?php include("inc/common/header.php") ?>
	<!-- /main navbar -->


	<!-- Page content -->
	<div class="page-content">

		<!-- Main sidebar -->
		<?php include("inc/common/sidebar.php") ?>
		<!-- /main sidebar -->


		<!-- Main content -->
		<div class="content-wrapper">

			<!-- Page header -->
			<div class="page-header">

				<div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
					<div class="d-flex">
						<div class="breadcrumb">
							<a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
							<a href="#" class="breadcrumb-item"> Work Area</a>
							<span class="breadcrumb-item active">Invoice</span>
						</div>
						<a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
					</div>
				</div>
			</div>
			<!-- /page header -->

			<!-- Content area -->
			<div class="content pt-0">
				<!-- Dashboard content -->
				<div class="row">
					<div class="col-md-12">
						<!-- This Form UI Starts here --->


						<div class="card">
							<div class="card-header bg-pgheader text-white header-elements-inline">
								<h6 class="card-title">Invoice - <?php echo $obj->inv_refno; ?></h6>
								<div class="header-elements">
									<div class="list-icons">
										<a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
										<a class="list-icons-item" href="invoice_list.php" title="PO List"><i class="icon-arrow-left52 mr-2"></i></a>
										<?php if($obj->inv_status == 1){ ?>
										<a class="list-icons-item" href="javascript:PrintPartsNew(new Array('print_content1'),'<?php echo $obj->inv_refno; ?>');" id="print_page" title="Print PO"><i class="icon-printer2 mr-1"></i></a>
										<?php }?>
										<a class="list-icons-item" data-action="fullscreen"></a>

									</div>
								</div>
							</div>
							<div class="card-body">
								<div class="row">
									<div class="col-md-12 pdf_page" id="print_content1">
										<!--<table class="table table-xs table-bordered po_print_table">
											<tbody>
												<tr>
													<td>-->
										<table class="table table-xs table-bordered po_print_table">

											<thead>
												<tr>
                                                    <td align="center" colspan="15" style="font-size: 20px; font-weight: bold; background-color: #bebebe;">INVOICE</td>
                                                </tr>
												<tr>
													<td colspan="6" class="text-center" style="vertical-align: center">
														<span ><img src="img/BIE_logo.png" alt="" width="100px" height="auto"></span>
													</td>
													<td colspan="9" align="left"><?php echo $address; ?>
                                                    </td>
												</tr>
												<tr>
													<td colspan="2" style="background-color: #D3D3D3;"><b>GSTIN NO.</b></td>
                                                    <td colspan="4" align="left"><?php echo $gst_no; ?></td>
													<td colspan="2" style="background-color: #D3D3D3;"><b>INVOICE NO</b></td>
                                                    <td colspan="2" align="left"><?php echo ($obj->inv_refno); ?></td>
													<td colspan="2" style="background-color: #D3D3D3;"><b>MODE OF TRANSPORT</b></td>
                                                    <td colspan="3" align="left"><?php echo $obj->inv_mode_of_trans; ?></td>
                                                </tr>
												<tr>
													<td colspan="2" style="background-color: #D3D3D3;"><b>PAN NO.</b></td>
                                                    <td colspan="4" align="left"><?php echo $pan_no; ?></td>
													<td colspan="2" style="background-color: #D3D3D3;"><b>INVOICE DATE</b></td>
                                                    <td colspan="2" align="left"><?php echo $inv_date; ?></td>
													<td colspan="2" style="background-color: #D3D3D3;"><b>VEHICLE NO.</b></td>
                                                    <td colspan="3" align="left"><?php echo $obj->inv_vechicle_no; ?></td>
                                                </tr>
												<tr>
													<td colspan="2" style="background-color: #D3D3D3;"><b>STATE CODE</b></td>
                                                    <td colspan="4" align="left"><?php echo $branch_state_code; ?></td>
													<td colspan="2" style="background-color: #D3D3D3;"><b>TIME OF INVOICE</b></td>
                                                    <td colspan="2" align="left"><?php echo date('h:i:s'); ?></td>
													<td colspan="2" style="background-color: #D3D3D3;"><b>TRANSPORT CHARGES</b></td>
                                                    <td colspan="3" align="left"><?php echo $obj->inv_trans_charge; ?></td>
                                                </tr>
												<tr>
                                                    <td colspan="6" align="left"></td>
													<td colspan="2" style="background-color: #D3D3D3;"><b>DC NO.</b></td>
                                                    <td colspan="2" align="left"><?php echo $obj->inv_dc_no; ?></td>
													<td colspan="2" style="background-color: #D3D3D3;"><b>DC DATE</b></td>
                                                    <td colspan="3" align="left"><?php echo date('d-m-Y', strtotime($obj->inv_dc_date)); ?></td>
                                                </tr>

												<tr>
													<td colspan="6" align="left">
														<p align="center"><b>CONSIGNEE ADDRESS</b></p>
														<p ><?php echo '<b>' . $supp_name. '</b><br/>' . $add; ?></p>
													</td>
													<td colspan="9" align="left">
														<p align="center"><b>DELIVERY ADDRESS</b></p>
														<p ><?php echo '<b>' . $supp_name2 . '</b><br/>' . $add2; ?></p>
													</td>
												</tr>


												<tr>
													<td colspan="2" style="background-color: #D3D3D3;"><b>GSTIN NO.</b></td>
                                                    <td colspan="4" align="left"><?php echo $supp_gst; ?></td>
													<td colspan="4" style="background-color: #D3D3D3;"><b>GSTIN NO.</b></td>
                                                    <td colspan="5" align="left"><?php echo $supp_gst; ?></td>
                                                </tr>
												<tr>
													<td colspan="2" style="background-color: #D3D3D3;"><b>PAN NO.</b></td>
                                                    <td colspan="4" align="left"><?php echo $supp_pan; ?></td>
													<td colspan="4" style="background-color: #D3D3D3;"><b>PAN NO.</b></td>
                                                    <td colspan="5" align="left"><?php echo $supp_pan; ?></td>
                                                </tr>

												<tr>
													<td colspan="2" style="background-color: #D3D3D3;"><b>STATE CODE</b></td>
                                                    <td colspan="4" align="left"><?php echo $state_code1; ?></td>
													<td colspan="4" style="background-color: #D3D3D3;"><b>STATE CODE</b></td>
                                                    <td colspan="5" align="left"><?php echo $state_code1; ?></td>
                                                </tr>

												<tr style="font-weight:bold; background-color: #D3D3D3;" class="align-center uppercase">
													<td width="3%" rowspan="4">#</td>

												</tr>
												<tr style="font-weight:bold; background-color: #D3D3D3;" class="align-center uppercase">
													<td width="30%" rowspan="3">Description of Goods</td>
													<td width="10%" rowspan="4">Model</td>
													<td width="5%" rowspan="5">HSN Code</td>
													<td width="5%" rowspan="6">Rate</td>
													<td width="5%" rowspan="7">Qty</td>
													<td width="6%" rowspan="8">Unit</td>
													<td width="5%" rowspan="9">Discount %</td>
													<td width="5%" rowspan="9">Taxable Value</td>
												</tr>
												<tr style="font-weight:bold; background-color: #D3D3D3;" class="align-center uppercase">
													<td width="12%" colspan="2">CGST</td>
													<td width="12%" colspan="2">SGST</td>
													<td width="12%" colspan="2">IGST</td>
												</tr>
												<tr style="font-weight:bold; background-color: #D3D3D3;" class="align-center uppercase">
													<td width="">Rate</td>
													<td width="5%">Amount</td>
													<td width="">Rate</td>
													<td width="5%">Amount</td>
													<td width="">Rate</td>
													<td width="5%">Amount</td>
												</tr>
											</thead>
											<tbody>

												<?php

												$posql = "SELECT * FROM tbl_invoice as a
														LEFT JOIN tbl_invoice_details as b ON a.inv_id = b.inv_id
														WHERE a.inv_id = '" . $_REQUEST['inv_id'] . "'";

												$result = $conn->query($posql);

												//echo mysql_error();

												if ($result->rowCount() > 0) 
												{

													$iSno = 1;
													$netTotal = 0;
													$item_val_no_tax = 0;
													$tax_val1 = 0;
													$tax_val2 = 0;
													$tax_val3 = 0;

													while ($inv = $result->fetch()) 
													{
															$tr_class = 0;
															$igst_per  = $cgst_per  = $sgst_per   = $cgst_vat = $cgst_val  =$sgst_vat  = $sgst_val  ='0';
															$igst_vat = $igst_val =$igst_val = $igst_vat = $igst_val =  $igst_val = $igst_vat  =$igst_val = $igst_val ='0';
														$item_code = $dbconn->GetSingleReconrd("tbl_item_details", "item_code", "item_status='1' AND item_id", $inv->item_id);
														$item_name = $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_status='1' AND item_id", $inv->item_id);
														$item_model = $dbconn->GetSingleReconrd("tbl_item_details", "item_model_manufac", "item_status='1' AND item_id", $inv->item_id);
														$gst_id = $dbconn->GetSingleReconrd("tbl_item_details", "item_hsn", "item_status='1' AND item_id", $inv->item_id);
                                                        //$branch_item_selling_price = $dbconn->GetSingleReconrd("mst_branch","branch_item_selling_price","branch_id",$_SESSION['_user_branch']);
														//$selling_price = $dbconn->GetSingleReconrd("tbl_item_stock", "$branch_item_selling_price", "item_id", $inv->item_id);
														//$selling_price = $dbconn->GetSingleReconrd("tbl_item_details", "item_selling_price", "item_status='1' AND item_id", $inv->item_id);


														$hsn = $dbconn->GetSingleReconrd("mst_hsn", "hsn_code", "hsn_status='1' AND hsn_id", $gst_id);

														$cgst = $dbconn->GetSingleReconrd("mst_hsn", "cgst", "hsn_status = '1' AND hsn_id", $gst_id);
														$sgst = $dbconn->GetSingleReconrd("mst_hsn", "sgst", "hsn_status = '1' AND hsn_id", $gst_id);
														$igst = $dbconn->GetSingleReconrd("mst_hsn", "igst", "hsn_status = '1' AND hsn_id", $gst_id);


														// $taxable_val = ($selling_price * $inv->inv_qty);
														// $item_taxval = (($taxable_val * $igst) / 100);
														// $item_total = $taxable_val + $item_taxval;
														$taxable_val = ($inv->unit_price * $inv->inv_qty);
														if($inv->inv_discount > 0)
														{
															$dis_amt=(($taxable_val*$inv->inv_discount)/100);
															$taxable_val=(float)$taxable_val-(float)$dis_amt;
														}
														$item_taxval = (($taxable_val * $igst) / 100);
														$item_total = $taxable_val + $item_taxval;

														if ($state_code1 == $branch_state_code) {
															$cgst_vat = $cgst;
															$cgst_val = (((float)$taxable_val * (float)$cgst_vat) / 100);
															$sgst_vat = $sgst;
															$sgst_val = (((float)$taxable_val * (float)$sgst_vat) / 100);
														} else {
															$igst_vat = $igst;
															$igst_val = (((float)$taxable_val * (float)$igst_vat) / 100);
														}


														echo '<tr ' . $tr_class . ' valign="top">
															<td class="text-center">' . $iSno . '</td>
															<td class="text-left">' . $item_name . '</td>
                                                            <td class="text-center"><b>' . $item_code . '</b></td>
                                                            <td class="text-center">' . $hsn . '</td>
                                                            <td class="text-right">' . $inv->unit_price . '</td>
                                                            <td class="text-center">' . $inv->inv_qty . '</td>
                                                            <td class="text-center">' . $inv->inv_unit . '</td>
                                                            <td class="text-right">' . number_format($inv->inv_discount, 2) . '</td>
                                                            <td class="text-right">' . number_format($taxable_val, 2) . '</td>
                                                            <td class="text-right">' . number_format($cgst_vat, 2) . '</td>
                                                            <td class="text-right">' . number_format($cgst_val, 2) . '</td>
                                                            <td class="text-right">' . number_format($sgst_vat, 2) . '</td>
															<td class="text-right">' . number_format($sgst_val, 2) . '</td>
															<td class="text-right">' . number_format($igst_vat, 2) . '</td>
															<td class="text-right">' . number_format($igst_val, 2) . '</td>
                                                        </tr>';

														$item_val_no_tax = $item_val_no_tax + $taxable_val;
														$tax_val1 = $tax_val1 + $cgst_val;
														$tax_val2 = $tax_val2 + $sgst_val;
														$tax_va3 = $tax_va3 + $igst_val;
														$total = $item_val_no_tax + $tax_val1 + $tax_val2 + $tax_val3;
														$total_tax_val =  $tax_val1 + $tax_val2 + $tax_val3;
														$tr_class = 'class="topborderzero"';


														$iSno++;
													}
												}

												$no_items = $iSno;
												$items_height = $no_items * 210;
												if ($items_height < 500) {
													$height = 500 - $items_height;
												} else {
													$height = 10;
												}
												//$height = 200;
												echo '<tr ' . $tr_class . ' valign="top">
													<td colspan><p style="min-height:' . $height . 'px;">&nbsp;</p></td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
													<td>&nbsp;</td>
												</tr>';

												
												echo '<tr>
													<td colspan="8" align="right" style="background-color: #D3D3D3;"><b>SUB TOTAL</b></td>
													<td align="right">' . number_format($item_val_no_tax, 2) . '</td>
													<td></td>
													<td align="right">' . number_format($tax_val1, 2) . '</td>
													<td></td>
													<td align="right">' . number_format($tax_val2, 2) . '</td>
													<td></td>
													<td align="right">' . number_format($tax_va3, 2) . '</td>
												 </tr>';
												 echo'<tr></tr>';
												 $net_total = $item_val_no_tax + $tax_val1 + $tax_val2 + $tax_va3;
												 $round_amt = round($net_total);
												 $round_val = $net_total - $round_amt;
												 $total_in_words = 'RUPEES '.ucwords(number_to_words(number_format($net_total,0,".",""))). "";
												 $tax_amt = $tax_val1+$tax_val2+$tax_va3;
												
												 $total_tax_amt = '<b>Total Tax Value : Rs.'.$tax_amt.'</b> &nbsp;&nbsp; RUPEES '.ucwords(number_to_words($tax_amt))."";

												echo '<tr>
												 	<td colspan="14" align="right" style="background-color: #D3D3D3;"><b>ROUND OFF (+/-)</b></td>
												 	<td align="right">' . number_format($round_val, 2) . '</td>
												 </tr>

												 <tr>
												 	<td colspan="14" align="right" style="background-color: #D3D3D3;"><b>GRAND TOTAL VALUE (In Figure)</b></td>
												 	<td align="right">' . number_format($round_amt, 2) . '</td>
												 </tr>

												 <tr>
												 	<td colspan="2" align="left" style="background-color: #D3D3D3;"><b>Total Value (In Words)</b></td>
												 	<td colspan="13" align="left">' . $total_in_words . '</td>	
												 </tr>

												 <tr>
												 	<td colspan = "2" align="left" style="background-color: #D3D3D3;"><b>Amount of Tax Subjected to Reverse Charges</td>
													<td colspan="13" align="left" >' . $total_tax_amt . '</td>	
												 </tr>
												
												 <tr>
												 	<td colspan="6" align="left" style="background-color: #D3D3D3;"><b>Payment Terms & Bank Details</b></td>

												 	<td colspan="3" align="center" style="background-color: #D3D3D3;"><b>Prepared By</b></td>
													<td colspan="2" align="center" style="background-color: #D3D3D3;"><b>Checked By</b></td>
												 	<td colspan="4" align="right" style="background-color: #D3D3D3;"><b>FOR BENZEAR INDUSTRIAL ENTERPRISES</b></td>
												 </tr>
												 <tr>
												 	<td colspan="6" align="left" style="background-color: #D3D3D3;"><b>Interest @24%p.a. will charged on overdue payments.  All disputes are subject to Coimbatore Jurisdiction</b></td>
												 	<td colspan="3" rowspan="2"></td>
													<td colspan="2" rowspan="2"></td>
												 	<td colspan="4" rowspan="2"><p style="padding-top: 120px; padding-left: 116px;"><b>Authorised Signature</b></p></td>
												 </tr>
												 
												 <tr>
												 	<td colspan="6" align="left" style="background-color: #D3D3D3;"><b>Bank Details : </b><br><b>Acc. Name : BENZEAR INDUSTRIAL ENTERPRISES<br/>Bank Name : Indian Bank <br/>Account No.: 6176803403</b><br>
													 <b>IFS Code : IDIB000C062</b><br><b>Branch : 1/202, Avinashi Road, Chinniampalayam, Coimbatore.</td>
													
												 </tr>

												 <tr>
												 	<td colspan="15" style="background-color: #D3D3D3;"><b>Toll Free No: 1800 599 2323 </b></td>
												 </tr>';



												?>

											</tbody>
										</table>


										
										<!--</td>
												</tr>


												<tr><td>&nbsp;</td></tr>--

											</tbody>


										</table>-->
									</div>
								</div>

							</div>
						</div>


						<div class="row">
							<div class="text-left col-lg-6">
								<div class="rec_create_dets"><b>Created by : </b><?php echo $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id ", $obj->modify_by) . ' on ' . date('d-M-y @ H:i', strtotime($obj->modify_date_time)); ?></b>
								</div>
							</div>
						</div>


						<!-- End of This Form UI  --->
					</div>
				</div>
				<!-- /dashboard content -->
			</div>
			<?php include("inc/common/footer.php") ?>
		</div>
	</div>
</body>

<script type="text/javascript">
	$(document).ready(function() {
		<?php
		if (isset($_SESSION['_msg']) && $_SESSION['_msg'] != "") {
			echo "$.jGrowl('" . $_SESSION['_msg'] . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-success', position: 'bottom-right', life:'2000', header: 'Success!' });";
			$_SESSION['_msg'] = "";
		}
		if (isset($_SESSION['_msg_err']) && $_SESSION['_msg_err'] != "") {
			echo "$.jGrowl('" . $_SESSION['_msg_err'] . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-danger', position: 'top-right', shutdown:'3000', header: 'Error!' });";
			$_SESSION['_msg_err'] = "";
		}
		?>

	});
</script>

</html>