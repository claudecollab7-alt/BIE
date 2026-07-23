<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
// require_once ("inc/common/dbrowiterator.php");	

isAdmin();



$conn = new dbconnect();
$dbconn = new dbhandler();
// ini_set('display_errors', '	1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);

$rtn_date = date("Y-m-d");

if (isset($_POST['SAVE']))
{
	try
	{
		$_REQUEST['rtn_date'] = date("Y-m-d", strtotime($_REQUEST['rtn_date']));

		

		$_REQUEST['rtn_finyr'] = $dbconn->GetSingleReconrd("mst_finyear","finyr","finyr_active",1);
		// $_REQUEST['rtn_slno'] = $dbconn->GetMaxValue('tbl_purchase_return','rtn_slno','rtn_finyr = "' . $_REQUEST['rtn_finyr'] . '" AND 1',1)+1;

		$_REQUEST['rtn_slno'] = $dbconn->GetMaxValue('tbl_purchase_return', 'rtn_slno', 'branch_id="'.$_SESSION['_user_branch'].'" AND 1 ', 1) + 1;
	
		$_REQUEST['branch'] = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id='".$_SESSION['_user_branch']."' AND branch_status", 1);

		$_REQUEST['rtn_refno'] = 'PR/' . leadingZeros($_REQUEST['rtn_slno'], 4) . '/BIE/'.$_REQUEST['branch'].'/' . $_REQUEST['rtn_finyr'];

			 
		$_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
		$_REQUEST['modify_by'] = $_SESSION['_user_id'];
		
		// if($_REQUEST['po_id'] !='')
		// {
			$stmt = null;				
			$stmt = $conn->prepare("INSERT INTO tbl_purchase_return (rtn_finyr, branch_id, rtn_slno, rtn_refno,rtn_date, supp_id, grn_id, common_remarks, modify_date_time, modify_by) 
			VALUES (:rtn_finyr, :branch_id, :rtn_slno, :rtn_refno,:rtn_date, :supp_id, :grn_id, :common_remarks, :modify_date_time, :modify_by)");
			$data = array(				
				':rtn_finyr' => $_REQUEST['rtn_finyr'],
				':branch_id' => $_SESSION['_user_branch'],
				':rtn_slno' => $_REQUEST['rtn_slno'],		
				':rtn_refno' => $_REQUEST['rtn_refno'],
				':rtn_date' => $_REQUEST['rtn_date'],
				':supp_id' => $_REQUEST['supp_id'],
				':grn_id' => $_REQUEST['grn_id'],
				':common_remarks' => $_REQUEST['common_remarks'],				
				':modify_date_time' => $_REQUEST['modify_date_time'],
				':modify_by' => $_REQUEST['modify_by']		
			);
		// }
		$stmt->execute($data);

			// print_r($data);
			// die();
		$last_id = $conn->lastInsertId();

			/* ------------ SAVE tbl_pi_details  -----------*/
		$delete_details =  "DELETE FROM tbl_purchase_return_details 
					WHERE rtn_id = '".$last_id."'";
			$result = $conn->prepare($delete_details);
			$result->execute();
			
		
		
		if (isset($_REQUEST['temp_rtn_item_id'])) {	
			
		    for ($x = 0; $x < count($_REQUEST['temp_rtn_item_id']); $x++) {
					
				$stmt1 = null;				
			    $stmt1 = $conn->prepare("INSERT INTO tbl_purchase_return_details (rtn_id, rtn_item_id, rtn_unit, po_qty, cus_dc_qty, rtn_rejected_qty) VALUES (:rtn_id, :rtn_item_id, :rtn_unit, :po_qty, :cus_dc_qty, :rtn_rejected_qty)");
				

					$data1 = array(				
						':rtn_id' => $last_id,
						':rtn_item_id' => $_REQUEST['temp_rtn_item_id'][$x],
						':rtn_unit' => $_REQUEST['temp_rtn_unit'][$x],
						':po_qty' => $_REQUEST['temp_po_qty'][$x],
						':cus_dc_qty' => $_REQUEST['cus_dc_qty'][$x],
						':rtn_rejected_qty' => $_REQUEST['rtn_rejected_qty'][$x]
						
						
					);
					$stmt1->execute($data1);
					// echo '3';
					// print_r($data1);
			        // die();
			}
			

			// $sql =  "DELETE FROM tbl_grn_details_temp 
			// 		WHERE session_id = '".$_SESSION['session_id']."'";
			// $result = $conn->prepare($sql);
			// $result->execute();
		}

		//update indent po status
		
		if($_REQUEST['grn_id'] !='')
		{
			$update_po = $conn->prepare("UPDATE tbl_grn SET rtn_submit_status = :rtn_submit_status WHERE grn_id = :grn_id");
			$data1 = array(
				':grn_id' => $_REQUEST['grn_id'],
				':rtn_submit_status' => 1
			);
			$update_po->execute($data1);
		}
	
	}
	catch (Exception $e)
	{		
		$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
		$_SESSION['_msg_err'] = $str;			
	}
					
	
	
	$_SESSION['_msg'] = "Purchase Return succesfully Saved..!";
	header("location:purchase_return_list.php");	
	die();
}


if (isset($_POST['UPDATE']))
{
  	$update_id = $_REQUEST['txtHid'];
	try
	{
		$_REQUEST['rtn_date'] = date("Y-m-d", strtotime($_REQUEST['rtn_date']));

		$_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
		$_REQUEST['modify_by'] = $_SESSION['_user_id'];
		$stmt = null;				
		$stmt = $conn->prepare("UPDATE  tbl_purchase_return SET rtn_date = :rtn_date, supp_id = :supp_id, common_remarks = :common_remarks, modify_date_time = :modify_date_time, modify_by = :modify_by, rtn_verify_status = :rtn_verify_status, rtn_verify_by = :rtn_verify_by, rtn_verify_date_time = :rtn_verify_date_time	WHERE rtn_id = :rtn_id");		
		$data = array(
			':rtn_id' => $update_id,				
			':rtn_date' => $_REQUEST['rtn_date'],
			':supp_id' => $_REQUEST['supp_id'],
			':common_remarks' => $_REQUEST['common_remarks'],			
			':modify_date_time' => $_REQUEST['modify_date_time'],
			':modify_by' => $_REQUEST['modify_by'],
			':rtn_verify_status' => 0,
			':rtn_verify_by' => 0,
			':rtn_verify_date_time' => ''
		);

		
		
		$stmt->execute($data);

		$sql =  "DELETE FROM tbl_purchase_return_details WHERE rtn_id = '".$update_id."'";
			$result = $conn->prepare($sql);
			$result->execute();

		// $result = $conn->query("SELECT * FROM tbl_grn_details_temp WHERE session_id = '".$_SESSION['session_id']."' ORDER BY temp_rtn_id");
		if (isset($_REQUEST['temp_rtn_item_id'])) {	
			
		    for ($x = 0; $x < count($_REQUEST['temp_rtn_item_id']); $x++) {
					
				$stmt1 = null;				
			    $stmt1 = $conn->prepare("INSERT INTO tbl_purchase_return_details (rtn_id, rtn_item_id, rtn_unit, po_qty, cus_dc_qty, rtn_rejected_qty) VALUES (:rtn_id, :rtn_item_id, :rtn_unit, :po_qty, :cus_dc_qty, :rtn_rejected_qty)");
				

					$data1 = array(				
						':rtn_id' => $update_id,
						':rtn_item_id' => $_REQUEST['temp_rtn_item_id'][$x],
						':rtn_unit' => $_REQUEST['temp_rtn_unit'][$x],
						':po_qty' => $_REQUEST['temp_po_qty'][$x],
						':cus_dc_qty' => $_REQUEST['cus_dc_qty'][$x],
						':rtn_rejected_qty' => $_REQUEST['rtn_rejected_qty'][$x]
						
						
					);
					$stmt1->execute($data1);
					// echo '3';
					// print_r($data1);
			        // die();
			}
			

			// $sql =  "DELETE FROM tbl_grn_details_temp 
			// 		WHERE session_id = '".$_SESSION['session_id']."'";
			// $result = $conn->prepare($sql);
			// $result->execute();
		}
	}
	catch (Exception $e)
	{		
		$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
		echo $_SESSION['_msg_err'] = $str;			
	}
	$_SESSION['_msg'] = "Purchase Return succesfully Updated..!";
	header("location:purchase_return_list.php");	
	die();
}

if (isset($_POST['FINALIZE']))
{
  	$update_id = $_REQUEST['txtHid'];
	try
	{
		$_REQUEST['rtn_date'] = date("Y-m-d", strtotime($_REQUEST['rtn_date']));

		
		

		$_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
		$_REQUEST['modify_by'] = $_SESSION['_user_id'];
		$stmt = null;				
		$stmt = $conn->prepare("UPDATE  tbl_purchase_return SET rtn_date = :rtn_date, supp_id = :supp_id, common_remarks = :common_remarks, modify_date_time = :modify_date_time, modify_by = :modify_by, rtn_verify_status = :rtn_verify_status, rtn_verify_by = :rtn_verify_by, rtn_verify_date_time = :rtn_verify_date_time	WHERE rtn_id = :rtn_id");		
		$data = array(
			':rtn_id' => $update_id,				
			':rtn_date' => $_REQUEST['rtn_date'],
			':supp_id' => $_REQUEST['supp_id'],
			':common_remarks' => $_REQUEST['common_remarks'],			
			':modify_date_time' => $_REQUEST['modify_date_time'],
			':modify_by' => $_REQUEST['modify_by'],
			':rtn_verify_status' => 1,
			':rtn_verify_by' => $_REQUEST['modify_by'],
			':rtn_verify_date_time' =>  $_REQUEST['modify_date_time']
		);

		
		
		$stmt->execute($data);

		$sql =  "DELETE FROM tbl_purchase_return_details WHERE rtn_id = '".$update_id."'";
			$result = $conn->prepare($sql);
			$result->execute();

		// $result = $conn->query("SELECT * FROM tbl_grn_details_temp WHERE session_id = '".$_SESSION['session_id']."' ORDER BY temp_rtn_id");
		if (isset($_REQUEST['temp_rtn_item_id'])) {	
			
		    for ($x = 0; $x < count($_REQUEST['temp_rtn_item_id']); $x++) {
					
				$stmt1 = null;				
			    $stmt1 = $conn->prepare("INSERT INTO tbl_purchase_return_details (rtn_id, rtn_item_id, rtn_unit, po_qty, cus_dc_qty, rtn_rejected_qty) VALUES (:rtn_id, :rtn_item_id, :rtn_unit, :po_qty, :cus_dc_qty, :rtn_rejected_qty)");
				

					$data1 = array(				
						':rtn_id' => $update_id,
						':rtn_item_id' => $_REQUEST['temp_rtn_item_id'][$x],
						':rtn_unit' => $_REQUEST['temp_rtn_unit'][$x],
						':po_qty' => $_REQUEST['temp_po_qty'][$x],
						':cus_dc_qty' => $_REQUEST['cus_dc_qty'][$x],
						':rtn_rejected_qty' => $_REQUEST['rtn_rejected_qty'][$x]
						
						
					);
					$stmt1->execute($data1);
					// echo '3';
					// print_r($data1);
			        // die();
			}
			

			// $sql =  "DELETE FROM tbl_grn_details_temp 
			// 		WHERE session_id = '".$_SESSION['session_id']."'";
			// $result = $conn->prepare($sql);
			// $result->execute();
		}
	}
	catch (Exception $e)
	{		
		$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
		echo $_SESSION['_msg_err'] = $str;			
	}
	$_SESSION['_msg'] = "Purchase Return succesfully Updated..!";
	header("location:purchase_return_list.php");	
	die();
}


$rtn_date = date('d-m-Y');

	if(isset($_REQUEST['grn_id'])){

	
		$result = $conn->query("SELECT * FROM tbl_grn WHERE grn_status = '2' AND grn_id = '".$_REQUEST['grn_id']."' ");	
		if ($result->rowCount()>0)
		{
			$get = $result->fetch(PDO::FETCH_OBJ);	
			
			$po_date = $dbconn->GetSingleReconrd("tbl_purchase_order","po_date","po_id",$get->po_id);
			$po_no = $dbconn->GetSingleReconrd("tbl_purchase_order","po_refno","po_id",$get->po_id);

			$supp_id = $get->supp_id;
			if($po_date != "0000-00-00" && $po_date != ""){
				$po_date = date("d-m-Y", strtotime($po_date));
			}
			$pi_remarks = $get->pi_remarks;
			$grn_id = $get->grn_id;
			$rtn_refno = $get->po_refno;
			// $po_no = leadingZeros($po_slno,3);
			$party_dc_no = $get->party_dc_no;
		}
	}else{

	

	// echo "SELECT * FROM tbl_purchase_return WHERE rtn_status = '1' AND rtn_id = ".$_REQUEST['rtn_id'];
		$result1 = $conn->query("SELECT * FROM tbl_purchase_return WHERE rtn_status = '1' AND rtn_id = ".$_REQUEST['rtn_id']);	
		if ($result1->rowCount()>0)
		{
			$row = $result1->fetch(PDO::FETCH_OBJ);	
			
			$po_id = $dbconn->GetSingleReconrd("tbl_grn","po_id","grn_id",$row->grn_id);
			$po_date = $dbconn->GetSingleReconrd("tbl_purchase_order","po_date","po_id",$po_id);
			$po_no = $dbconn->GetSingleReconrd("tbl_purchase_order","po_refno","po_id",$po_id);
			$party_dc_no = $dbconn->GetSingleReconrd("tbl_grn","party_dc_no","grn_id",$row->grn_id);
			$grn_refno = $dbconn->GetSingleReconrd("tbl_grn","grn_refno","grn_id",$row->grn_id);

			$supp_id = $row->supp_id;
			if($po_date != "0000-00-00" && $po_date != ""){
				$po_date = date("d-m-Y", strtotime($po_date));
			}
			// $rtn_dates = $row->rtn_date;

			// if($rtn_dates != "0000-00-00" && $rtn_dates != ""){
			// 	$rtn_date = date("d-m-Y", strtotime($rtn_dates));
			// }

			// $pi_remarks = $get->pi_remarks;
			// $grn_id = $get->grn_id;
			$rtn_refno = $row->rtn_refno;
			// $po_no = leadingZeros($po_slno,3);
			// $party_dc_no = $party_dc_no;
		}
	}
// }


?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo PAGE_TITLE; ?> - GRN</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<?php include_once("inc/common/css-js.php"); ?>

</head>

<script type="text/javascript">

function fnValidate()
{	
	
	

	// var dc_items = document.thisForm.dc_items.value;
	// if(document.thisForm.dc_items.value == "-1"){
	// 	alert("Please add Purchase Details..");
	// 	return false;
	// }

	
	
}
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

	
	//Calculate the Balance QTY
	$(".cus_dc_qty").change(function(){
		var cus_dc_qty = $(this).val();
		$(this).closest('tr').find('.grn_recived_qty').val(cus_dc_qty);
		$(this).closest('tr').find('.rtn_rejected_qty').val(cus_dc_qty);
		$(this).closest('tr').find('.grn_rejected_qty').val('');
	});
	
	$(".grn_recived_qty").change(function(){
		var grn_recived_qty = $(this).val();
		$(this).closest('tr').find('.rtn_rejected_qty').val(grn_recived_qty);
		$(this).closest('tr').find('.grn_rejected_qty').val('');
	});

	$(".rtn_rejected_qty").change(function(){
		var rtn_rejected_qty = $(this).val();
		var grn_recived_qty = $(this).closest('tr').find('.grn_recived_qty').val();
		var rejected_qty = parseInt(grn_recived_qty) - parseInt(rtn_rejected_qty);
		if(parseInt(rtn_rejected_qty) > parseInt(grn_recived_qty))
		{
			alert('Accepted Qty must be less than the Received Qty');
			$(this).closest('tr').find('.rtn_rejected_qty').val('');
			$(this).closest('tr').find('.grn_rejected_qty').val('');
			return false;
		}
		else
		{
			$(this).closest('tr').find('.grn_rejected_qty').val(rejected_qty);

		}
	}).trigger('change');

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
							<span class="breadcrumb-item active">GRN</span>
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
								<h6 class="card-title"> GRN</h6>
								<div class="header-elements">
									<div class="list-icons">
										<a class="list-icons-item" href="purchase_return_list.php" title="GRN List"><i class="icon-arrow-left52 mr-2"></i></a>
										<a class="list-icons-item" data-action="fullscreen"></a>
									</div>
								</div>

							</div>
							<form name='thisForm' id="validate" class="form-horizontal" method='post' action="purchase_return_add.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                                  
                                <fieldset>
                                    <?php 
                                        
										if ($_REQUEST['rtn_id'] != "") {
											$rtn_no = leadingZeros($dbconn->GetSingleReconrd('tbl_purchase_return', 'rtn_slno', 'rtn_id', $_REQUEST['rtn_id']), 4);
										} else {
											$rtn_no = leadingZeros($dbconn->GetMaxValue('tbl_purchase_return', 'rtn_slno', 'branch_id="'.$_SESSION['_user_branch'].'" AND 1 ', 1) + 1, 4);
										}
                                    ?>                  
                                    <div class="row-fluid well">	
                                    <input type="hidden" name="rtn_pi_items" id="rtn_pi_items" value="-1">
                                    <input type="hidden" name="gst" id="gst" value="">
                                    
                                    <input type="hidden" name="grn_id" id="grn_id" value="<?php echo $grn_id; ?>">
                                    <input type="hidden" name="item_hsn" id="item_hsn" value="">
                                    

                                        <div class="row pt-2 pl-2 pr-2">
                                            
                                                <label class="col-lg-1 col-form-label">Purchase Order No <span class="text-error"></span></label>
                                                <div class="col-lg-3"><input type="text" tabindex="-1" class="form-control" name="po_no" readonly value="<?php echo $po_no; ?>" /></div>
                                            
                                          
                                                <label class="col-lg-1 col-form-label">Purchase Order Date <span class="text-error"></span></label>
                                                <div class="col-lg-3"><input type="text" readonly tabindex="-1" class="form-control" name="pi_date" value="<?php echo $po_date; ?>" /></div>
                                            
                                            
                                                <label class="col-lg-1 col-form-label">Suppliers <span class="text-error">*</span></label>
                                                <div class="col-lg-3">
                                                    <select name="supp_id" id="supp_id" data-placeholder="Choose a Supplier.." class="select">
                                                        <option value="">Select Supplier</option> 
                                                        <?php
                                                        $dbconn= new dbhandler(); 
                                                        echo $dbconn->fnFillComboFromTable_Where("supp_id","supp_name","mst_supplier_new","supp_id"," WHERE supp_status = '1' AND supp_type = 'S' AND supp_approve_status = '1'") ?>
                                                    </select> 
                                                    <script>document.thisForm.supp_id.value="<?php echo $supp_id; ?>";</script>
                                                </div>
                                            

                                            
                                        </div>

                                        <div class="row pt-2 pl-2 pr-2">
                                            
                                                <label class="col-lg-1 col-form-label">Purchase Return No <span class="text-error"></span></label>
                                                <div class="col-lg-3"><input type="text" class="form-control" name="rtn_no" readonly tabindex="-1" value="<?php echo $rtn_no; ?>" /></div>
                                           
                                            
                                            
                                                <label class="col-lg-1 col-form-label">Purchase Return Date <span class="text-error"></span></label>
                                                <div class="col-lg-3"><input type="date" class="form-control" max="<?php echo date('Y-m-d'); ?>" name="rtn_date" value="<?php echo $row->rtn_date; ?>" placeholder="Date" /></div>
                                           
                                            
                                            
                                                <label class="col-lg-1 col-form-label">Bill Ref.No<span class="text-error"></span></label>
                                                <div class="col-lg-3"><input type="text" class="form-control" name="rtn_refno" value="<?php echo $grn_refno; ?>" /></div>
                                           
                                            
                                        </div>


                                        <div class="row pt-2 pl-2 pr-2">
                                            
                                                <label class="col-lg-1 col-form-label">Party DC No <span class="text-error"></span></label>
                                                <div class="col-lg-3"><input type="text" class="form-control" name="party_dc_no"  readonly tabindex="-1" value="<?php echo $party_dc_no; ?>" /></div>
                                           
                                                <label class="col-lg-1 col-form-label">Remarks:</label>
                                                <div class="col-lg-3"><textarea name="common_remarks" id="common_remarks" maxlength="250" class="form-control"><?php echo $get->common_remarks; ?></textarea></div>
                                           
                                        </div>

                                    

                                    <div class="form-headings align-left">
                                        <label class="col-lg-3"><strong>Return Details</strong></label>
                                    </div>
                                
                                        <div class="row pr-2 pl-2">
                                            <div id="show_table" class="col-md-12">
                                                <table class="table table-xs table-bordered table-dets-responsive" style="font-size: small !important;">
                                                    <thead>
                                                        <tr class="bg-teal">
                                                            <th width="20%">Description</th>
                                                            <th width="5%">Unit</th>
                                                            <th width="5%">PO Qty</th>
                                                            <th width="5%">Bill/DC Qty</th>
                                                            <th width="5%">Return Qty</th> 
                                                            <!-- <th width="1%">Remarks</th>     -->
                                                        </tr>
                                                    </thead>
                                                    <?php
												    if ($_REQUEST['grn_id'] != '') {

                                                        $sql_temp = "SELECT * FROM tbl_grn as a left join `tbl_grn_details` as b on a.grn_id = b.grn_id where grn_status = 2 AND rtn_submit_status = 0 AND grn_rejected_qty > '0' AND grn_accepted_qty > 0 ";

                                                        $result = $conn->query($sql_temp);
                                                        
                                                        if ($result->rowCount() > 0){		 
                                                            
                                                            $iSno=1;
															// echo $result;
															// $already_rtn_rejected_qty=0;
                                                            echo "<tbody>";
                                                            while ($obj = $result->fetch())
                                                            {
																

                                                                // $already_rtn_rejected_qty = $conn->query("SELECT SUM(grn_rejected_qty) as total_rejected_qty FROM `tbl_grn_details` WHERE rtn_id IN (SELECT rtn_id FROM tbl_purchase_return WHERE pi_id='".$pi_id."' AND rtn_approve_status='1') AND rtn_item_id='".$obj->temp_rtn_item_id."'");
                                                                // if ($already_rtn_rejected_qty->rowCount()>0)
                                                                // {
                                                                //     $obj1 = $already_rtn_rejected_qty->fetch(PDO::FETCH_OBJ);
                                                                // }

                                                                $temp_item_code = $dbconn->GetSingleReconrd("tbl_item_details","item_code","item_status = '1' AND item_id",$obj->grn_item_id);
                                                                $temp_item_name = $dbconn->GetSingleReconrd("tbl_item_details","item_desciption","item_status = '1' AND item_id",$obj->grn_item_id);
                                                                // $pi_remarks_id = $dbconn->GetSingleReconrd("tbl_grn_details","pi_remarks_id","pi_id = '".$pi_id."' AND pi_item_id",$obj->temp_rtn_item_id);
                                                                
                                                                    echo '<tr>
                                                                            <td style = "text-align:left;">'.$temp_item_name.' - <b>'.$temp_item_code.'</b></td>
																			<input type="hidden" name="temp_rtn_item_id[]" id="temp_rtn_item_id" readonly class="span12 validate[required] temp_rtn_item_id" value="'.$obj->grn_item_id.'"></td>

                                                                            <td style = "text-align:center;">'.$obj->grn_unit.'
																			<input type="hidden" name="temp_rtn_unit[]" id="temp_rtn_unit" readonly class="span12 validate[required] temp_rtn_unit" value="'.$obj->grn_unit.'"></td>

                                                                            <td style = "text-align:center;">'.$obj->po_qty.'
																			<input type = "hidden" name="temp_po_qty[]" class="temp_po_qty" value="'.$obj->po_qty.'"></td>
                                                                            
                                                                            <td>'.$obj->po_qty.'
																			<input type="hidden" name="cus_dc_qty[]" id="cus_dc_qty" readonly class="span12 validate[required] cus_dc_qty" value="'.$obj->po_qty.'"></td>

                                                                            <td>'.$obj->grn_rejected_qty.'
																			<input type="hidden" name="rtn_rejected_qty[]" id="rtn_rejected_qty" readonly class="span12 validate[required] rtn_rejected_qty" value="'.$obj->grn_rejected_qty.'"></td>
                                                                            
																			
																	</tr>';
                                                                $iSno++;
                                                            }
                                                            
                                                            echo "</tbody>";
                                                            
                                                                        
                                                        }
													}
													else{

														$sql_temp = "SELECT * FROM tbl_purchase_return as a left join tbl_purchase_return_details as b on a.rtn_id = b.rtn_id where rtn_status = 1 AND rtn_verify_status=0 AND rtn_approve_status = 0  ";

                                                        $result = $conn->query($sql_temp);
                                                        
                                                        if ($result->rowCount() > 0){		 
                                                            
                                                            $iSno=1;
															// echo $result;
															// $already_rtn_rejected_qty=0;
                                                            echo "<tbody>";
                                                            while ($obj = $result->fetch())
                                                            {
																

                                                                // $already_rtn_rejected_qty = $conn->query("SELECT SUM(grn_rejected_qty) as total_rejected_qty FROM `tbl_grn_details` WHERE rtn_id IN (SELECT rtn_id FROM tbl_purchase_return WHERE pi_id='".$pi_id."' AND rtn_approve_status='1') AND rtn_item_id='".$obj->temp_rtn_item_id."'");
                                                                // if ($already_rtn_rejected_qty->rowCount()>0)
                                                                // {
                                                                //     $obj1 = $already_rtn_rejected_qty->fetch(PDO::FETCH_OBJ);
                                                                // }

                                                                $temp_item_code = $dbconn->GetSingleReconrd("tbl_item_details","item_code","item_status = '1' AND item_id",$obj->rtn_item_id);
                                                                $temp_item_name = $dbconn->GetSingleReconrd("tbl_item_details","item_desciption","item_status = '1' AND item_id",$obj->rtn_item_id);
                                                                // $pi_remarks_id = $dbconn->GetSingleReconrd("tbl_grn_details","pi_remarks_id","pi_id = '".$pi_id."' AND pi_item_id",$obj->temp_rtn_item_id);
                                                                
                                                                    echo '<tr>
                                                                            <td style = "text-align:left;">'.$temp_item_name.' - <b>'.$temp_item_code.'</b></td>
																			<input type="hidden" name="temp_rtn_item_id[]" id="temp_rtn_item_id" readonly class="span12 validate[required] temp_rtn_item_id" value="'.$obj->rtn_item_id.'"></td>

                                                                            <td style = "text-align:center;">'.$obj->rtn_unit.'
																			<input type="hidden" name="temp_rtn_unit[]" id="temp_rtn_unit" readonly class="span12 validate[required] temp_rtn_unit" value="'.$obj->rtn_unit.'"></td>

                                                                            <td style = "text-align:center;">'.$obj->po_qty.'
																			<input type = "hidden" name="temp_po_qty[]" class="temp_po_qty" value="'.$obj->po_qty.'"></td>
                                                                            
                                                                            <td>'.$obj->po_qty.'
																			<input type="hidden" name="cus_dc_qty[]" id="cus_dc_qty" readonly class="span12 validate[required] cus_dc_qty" value="'.$obj->po_qty.'"></td>

                                                                            <td>'.$obj->rtn_rejected_qty.'
																			<input type="hidden" name="rtn_rejected_qty[]" id="rtn_rejected_qty" readonly class="span12 validate[required] rtn_rejected_qty" value="'.$obj->rtn_rejected_qty.'"></td>
                                                                            
																			
																	</tr>';
                                                                $iSno++;
                                                            }
                                                            
                                                            echo "</tbody>";
                                                            
                                                                        
                                                        }

													}
                                                    ?>
                                                </table>
                                            </div>
                                        </div>
                                        <script type="text/javascript">remove_item(0);</script>
                                        <!-- new -->
                                        
                                        
                                            <div class="row pt-2 pl-2 pr-2 pb-2">
                                                <label class="col-lg-1 col-form-label">Return Remarks:</label>
												<div class="col-lg-12">
													 <textarea name="pur_remarks" id="pur_remarks" maxlength="250" class="form-control"><?php  echo $obj->pur_remarks; ?></textarea>
												</div>
                                            </div>
                                     
                                        
                                
                                    <!-- <script>$(function(){ $("#cus_type").trigger('change');  });</script>
                                    <script>$(function(){ $("#cus_id").trigger('change');  });</script> -->
                                    
                                        <div class="card-footer text-center">
                                            <?php if(isset($_REQUEST["rtn_id"])) { ?>
                                            <INPUT class="btn btn-info mr-2" type="submit" name="UPDATE" value="UPDATE">
                                            <INPUT class="btn btn-light mr-2" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
											<INPUT class="btn btn-success" type="submit" name="FINALIZE" value="Send for Approval" onclick="return fnValidate();">
                                            <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['rtn_id'];?>">
                                            
                                            <?php } else if(isset($_REQUEST['grn_id'])){ ?>
												<INPUT class="btn btn-custom mr-2" type="submit" name="SAVE" value="Draft" >
                                                <INPUT class="btn btn-light mr-2" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);" />
                                            <input type="hidden" name="txtHid" value="0">
                                        <?php } ?>
                                            
                                        </div>
                                    </div>    
                                    
                                </fieldset>
                                
                                
                            </form>
						</div>
					</div>
				</div>
			</div>
	<?php include("inc/common/footer.php") ?>

		</div>
	</div>
    <!-- <script type="text/javascript">
    	$(".rtn_remarks_id").trigger('ready');
    </script> -->
</body>

</html>