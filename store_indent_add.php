<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
// require_once ("inc/common/dbrowiterator.php");	

isAdmin();



$conn = new dbconnect();
$dbconn = new dbhandler();

//ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);

$si_date = date("Y-m-d");

if (isset($_POST['SAVE'])) {
	try {
		$_REQUEST['si_date'] = date("Y-m-d", strtotime($_REQUEST['si_date']));

		$_REQUEST['si_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
		$_REQUEST['si_slno'] = $dbconn->GetMaxValue('tbl_store_indent', 'si_slno', ' branch_id="'.$_SESSION['_user_branch'].'" AND si_finyr',$_REQUEST['si_finyr']) + 1;
        // $_REQUEST['inv_slno'] = $dbconn->GetMaxValue('tbl_invoice', 'inv_slno', 'branch_id="'.$_SESSION['_user_branch'].'" AND inv_finyr="'.$_REQUEST['inv_finyr'].'" AND 1', 1) + 1;

		$si_approve_id = $dbconn->GetSingleReconrd("tbl_task_user", "user_id", "task_id", 1);
		$_REQUEST['branch'] = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id='".$_SESSION['_user_branch']."' AND branch_status", 1);

		$_REQUEST['si_refno'] = 'SI/' . leadingZeros($_REQUEST['si_slno'], 4) . '/BIE/'.$_REQUEST['branch'].'/' . $_REQUEST['si_finyr'];

		// $_REQUEST['payment_code'] = 'P'.leadingZeros($_REQUEST['payment_slno'],4);

		$_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
		$_REQUEST['modify_by'] = $_SESSION['_user_id'];

		$stmt = null;
		$stmt = $conn->prepare("INSERT INTO tbl_store_indent (si_finyr, si_slno, si_refno, si_date, supp_id, si_remarks, branch_id, si_approve_id, modify_date_time, modify_by, si_status)
				 VALUES (:si_finyr, :si_slno, :si_refno, :si_date, :supp_id, :si_remarks, :branch_id, :si_approve_id, :modify_date_time, :modify_by, :si_status)");
		$data = array(
			':si_finyr' => $_REQUEST['si_finyr'],
			':si_slno' => $_REQUEST['si_slno'],
			':si_refno' => $_REQUEST['si_refno'],
			':si_date' => $_REQUEST['si_date'],
			':supp_id' => $_REQUEST['supp_id'],
			':si_remarks' => $_REQUEST['si_remarks'],
			':branch_id' => $_SESSION['_user_branch'],
			':si_approve_id' => $si_approve_id,
			':modify_date_time' => $_REQUEST['modify_date_time'],
			':modify_by' => $_REQUEST['modify_by'],
			':si_status' => 1
		);
		$stmt->execute($data);
       
		$last_id = $conn->lastInsertId();
		/* ------------ SAVE tbl_po_details  -----------*/
		// $delete_details =  "DELETE FROM tbl_purchase_order_details WHERE si_id = '" . $last_id . "'";
		// $result = $conn->prepare($delete_details);
		// $result->execute();

        // print_r($_REQUEST);

		// exit;
		$stmt = null;
		$stmt = $conn->prepare("INSERT INTO tbl_store_indent_details (si_id,  item_id, si_qty, item_moq, item_uom, si_unit, curr_stock) 
	    						VALUES (:si_id, :item_id, :si_qty, :item_moq, :item_uom, :si_unit, :curr_stock)");

		$row_count = count($_REQUEST['temp_item_id']);
		for ($n = 0; $n < $row_count; $n++) {

			$data = array(
				':si_id' => $last_id,
				':item_id' => $_REQUEST['temp_item_id'][$n],
				':si_qty' => $_REQUEST['temp_si_qty'][$n],
				':item_moq' => $_REQUEST['item_moq'][$n],
				':item_uom' => $_REQUEST['temp_item_uom'][$n],
				':si_unit' => $_REQUEST['temp_si_unit'][$n],
				':curr_stock' => $_REQUEST['curr_stock'][$n]
				
			);
			$stmt->execute($data);
            // print_r($data);
            // die();
		}

		
	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		$_SESSION['_msg_err'] = $str;
	}



	$_SESSION['_msg'] = "Store Indent succesfully Saved..!";
	header("location:store_indent_list.php");
	die();
}

if (isset($_POST['UPDATE'])) {
	$update_id = $_REQUEST['txtHid'];
	try {
		$_REQUEST['si_date'] = date("Y-m-d", strtotime($_REQUEST['si_date']));
		$si_approve_id = $dbconn->GetSingleReconrd("tbl_task_user", "user_id", "task_id", 1);
		$_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
		$_REQUEST['modify_by'] = $_SESSION['_user_id'];
		$stmt = null;
		$stmt = $conn->prepare("UPDATE  tbl_store_indent SET si_date = :si_date, supp_id = :supp_id, si_remarks = :si_remarks, si_approve_id = :si_approve_id, 
		 modify_date_time = :modify_date_time, modify_by = :modify_by, si_status = :si_status	WHERE si_id = :si_id");
		$data = array(
			':si_id' => $update_id,
			':si_date' => $_REQUEST['si_date'],
			':supp_id' => $_REQUEST['supp_id'],
			':si_remarks' =>  $_REQUEST['si_remarks'],
			':si_approve_id' => $si_approve_id,
			':modify_date_time' => $_REQUEST['modify_date_time'],
			':modify_by' => $_REQUEST['modify_by'],
			':si_status' => 1
		);
		//':branch_id' => $_SESSION['_user_branch'],

		$stmt->execute($data);

		$sql =  "DELETE FROM tbl_store_indent_details WHERE si_id = '" . $update_id . "'";
		$result = $conn->prepare($sql);
		$result->execute();

		/* details */
		$po_value = 0;
		for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) {

			echo count($_REQUEST['temp_item_id']);
			$item_value = $item_taxval = $item_total = 0;
			$stmt1 = null;
			$stmt1 = $conn->prepare("INSERT INTO tbl_store_indent_details (si_id, item_id, si_qty, item_moq, item_uom, si_unit, curr_stock) 
				VALUES (:si_id, :item_id, :si_qty, :item_moq, :item_uom, :si_unit, :curr_stock)");


			$data = array(
				':si_id' => $update_id,
				':item_id' => $_REQUEST['temp_item_id'][$x],
				':si_qty' => $_REQUEST['temp_si_qty'][$x],
				':item_moq' => $_REQUEST['item_moq'][$x],
				':item_uom' => $_REQUEST['temp_item_uom'][$x],
				':si_unit' => $_REQUEST['temp_si_unit'][$x],
				':curr_stock' => $_REQUEST['curr_stock'][$x],
				
			);

			//print_r($data);
			$stmt1->execute($data);
			

		}

	} catch (Exception $e) {
		$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
		$_SESSION['_msg_err'] = $str;
	}
	$_SESSION['_msg'] = "Store Indent succesfully Updated..!";
	header("location:store_indent_list.php");
	die();
}

if (isset($_POST['FINALIZE'])) {
	$update_id = $_REQUEST['txtHid'];
	if ($_REQUEST['txtHid'] != '' && $_REQUEST['txtHid'] > 0) {
		try {
			$_REQUEST['si_date'] = date("Y-m-d", strtotime($_REQUEST['si_date']));
			$si_approve_id = $dbconn->GetSingleReconrd("tbl_task_user", "user_id", "task_id", 1);
			$_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
			$_REQUEST['send_purchase_dt'] = date('Y-m-d H:i:s');
			$_REQUEST['modify_by'] = $_SESSION['_user_id'];
			$_REQUEST['send_purchase_by'] = $_SESSION['_user_id'];
			$stmt = null;
			$stmt = $conn->prepare("UPDATE  tbl_store_indent SET si_date = :si_date, si_remarks = :si_remarks, si_approve_id = :si_approve_id, 
			modify_date_time = :modify_date_time, modify_by = :modify_by, send_purchase_by = :send_purchase_by, send_purchase_dt = :send_purchase_dt, send_purchase_status = :send_purchase_status
			WHERE si_id = :si_id");
			$data = array(
				':si_id' => $update_id,
				':si_date' => $_REQUEST['si_date'],
				':si_remarks' =>  $_REQUEST['si_remarks'],
				':si_approve_id' => $si_approve_id,
				':modify_date_time' => $_REQUEST['modify_date_time'],
				':modify_by' => $_REQUEST['modify_by'],
				':send_purchase_by' => $_REQUEST['send_purchase_by'],
				':send_purchase_dt' =>  $_REQUEST['modify_date_time'],
				':send_purchase_status' => 1,

			);
           

			$stmt->execute($data);

			$sql =  "DELETE FROM tbl_store_indent_details WHERE si_id = '" . $update_id . "'";
			$result = $conn->prepare($sql);
			$result->execute();

			/* details */
			
			for ($x = 0; $x < count($_REQUEST['temp_item_id']); $x++) {

				//echo count($_REQUEST['temp_item_id']);
				$item_value = $item_taxval = $item_total = 0;
				$stmt1 = null;
				$stmt1 = $conn->prepare("INSERT INTO tbl_store_indent_details (si_id, item_id, si_qty, item_moq, item_uom, si_unit, curr_stock) 
				VALUES (:si_id, :item_id, :si_qty, :item_moq, :item_uom, :si_unit, :curr_stock)");


				$data = array(
					':si_id' => $update_id,
					':item_id' => $_REQUEST['temp_item_id'][$x],
					':si_qty' => $_REQUEST['temp_si_qty'][$x],
					':item_moq' => $_REQUEST['item_moq'][$x],
					':item_uom' => $_REQUEST['temp_item_uom'][$x],
                    ':si_unit' => $_REQUEST['temp_si_unit'][$x],
					':curr_stock' => $_REQUEST['curr_stock'][$x]
					
				);
				
				$stmt1->execute($data);
                // print_r($data);
                // die();
				
			}
            $_SESSION['_msg'] = "Store Indent succesfully Sent..!";
			header("location:store_indent_list.php");
			die();

		} catch (Exception $e) {
			$str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
			$_SESSION['_msg_err'] = $str;
		}
	}

}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Store Indent</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <?php include_once("inc/common/css-js.php"); ?>

</head>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.js"></script>
<!-- <script type='text/javascript' src='js/auto/jquery.autocomplete.js'></script>
<link rel="stylesheet" type="text/css" href="js/auto/jquery.autocomplete.css" /> -->


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

$('#add_items').click(function() {
		
//  alert();
		var table = document.getElementById("show_table");
		var rowCount = 1;
		var item_id = $("#item_id").val();
		var supp_id = $("#supp_id").val();
		var curr_stock = $("#curr_stock").val();
		var si_qty = parseFloat($("#si_qty").val());
		var item_moq = parseFloat($("#item_moq").val());
		var item_uom = $("#item_uom").val();
		var si_unit = $("#si_unit").val();
        
       
		var arr = [];
		var is_ch = 0;

        if (isNull(document.thisForm.item_code, "Item Name..!")) {
            return false;
        }
        if (isNull(document.thisForm.si_qty, "Quantity...!")) {
            return false;
        }
        if(si_qty < item_moq)
        {
            alert("Please check the Quantity (MOQ " + item_moq + ")");
            $("#si_qty").val('');
            return false; 
        }
       
       // alert();


		$("#show_table tr").each(function() {
			arr.push(this.id);
		});

		if (jQuery.inArray(item_id, arr) != -1) {

			var is_ch = 1;
		}

		$.ajax({
			type: "POST",
			url: "inc/cis_ajax/jquery_store_indent_details.php",
			data: {
				"item_id": item_id,
				"curr_stock": curr_stock,
				"item_uom": item_uom,
				"item_moq": item_moq,
				"si_qty": si_qty,
				"si_unit": si_unit,
				'mode': 'save'
			}
		}).done(function(msg) {
            //alert(msg);
			if (is_ch == 0) {
				$("#show_table tbody").append(msg);
			} else {
				$("#" + item_id).replaceWith(msg);
                //alert(msg);
			}
			$("#item_id").val('');
			$("#item_code").val('');
			$("#curr_stock").val('');
			$("#item_uom").val('');
			$("#item_moq").val('');
			$("#si_qty").val('');
			$("#si_unit").val('');
			
			// findQuotationItemTotal();
		});
        
        // if(si_qty < item_moq)
        // {
        //     // alert();
        //     alert('Store Indent Qty Must be Greater or Equal to MOQ Qty');
            
        //     return false;
        //     $("#si_qty").val('');
        // }
        // document.thisForm.submit();

	});

    

    $('#item_code').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "inc/auto/select_store_indent_items.php",
                    dataType: "json",
                    data: {
                        q: request.term
                    },
                    beforeSend:function(){
                        $('#curr_stock').val('');
                        $('#item_moq').val('');
                        $('#si_qty').val('');
                        $('#item_uom').val('');
                        $('#si_unit').val('');
                       
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            minLength: 1,
            select: function(event, ui) {

                // $('#search_item').val(ui.item.value);
                $("#curr_stock").val(ui.item.item_curr_stock);
                $("#item_uom").val(ui.item.item_uom);
                $("#item_moq").val(ui.item.item_order_min_qty);
                $('#item_id').val(ui.item.id);
            }
        }).data('ui-autocomplete')._renderItem = function(ul, item) {
            return $("<li class='ui-autocomplete-row'></li>")
                .data("item.autocomplete", item)
                .append(item.label)
                .appendTo(ul);
            };

            $("#item_code").on("autocompleteselect", function(event, ui) {
				if (ui && ui.item) {
					var string = ui.item.value.split('~'); // Assuming 'formatted' contains the selected value
					$("#item_id").val(string[3]);
					$("#curr_stock").val(string[1]);
					$("#item_moq").val(string[2]);
					$("#item_uom").val(string[4]);

                    var item_id = ui.item.id;
					//var item_id = string[3];
					
					$.ajax({
						type: "POST",
						url: "inc/cis_ajax/jquery_multi_uom.php",
						data: {
							"item_id": item_id,
						}
					}).done(function(msg) {
                       // alert(msg);
                        console.log("Response from PHP script:", msg); 
						// console.log("Response from PHP script:", msg);
						// alert("Dropdown population function called");
						$('#si_unit').empty(); // Clear the dropdown before appending options
						var optionArray = msg.split('#');
						for (var i = 0; i < optionArray.length; i++) {
							if (optionArray[i].trim() !== "") {
								var optionDetails = optionArray[i].split('~');
								// console.log("Option details:", optionDetails); 
								// console.log("Option value:", optionDetails[0]); // Log the option value
                                // console.log("Option label:", optionDetails[1]); // Log the option label
								$('#si_unit').append("<option value='" + optionDetails[0] + "'>" + optionDetails[1] + "</option>");
							}
						}
						// Reinitialize the Select2 plugin after updating options
						$("#si_unit").select2();
					});
				}
			});

       

          
           
});

function fnValidate() 
{
    var rowCount = $('.table-bordered tr').length;
        if (rowCount == "1"){
        alert("Please add Products to this Store Indent..");
        return false;
    }
    document.thisForm.submit();
}

function remove_item_si_details(item_id){

$('#item' + item_id).remove();


}

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
                            <span class="breadcrumb-item active">Store Indent</span>
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
                                <h6 class="card-title"> Store Indent</h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="store_indent_list.php" title="Direct Store Indent"><i class="icon-arrow-left52 mr-2"></i></a>
                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>
                                <form name='thisForm' id="validate" class="form-horizontal" method='post' action="store_indent_add.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                                    <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['si_id']; ?>">
                                    <input type="hidden" name="pi_items" id="pi_items" value="-1">
                                    <input type="hidden" name="curr_stock" id="current_stock" value="">
                                    <input type="hidden" name="item_moq" id="items_moq" value="">
                                    <!-- <input type="hidden" name="si_qty" id="si_qty" value=""> -->
                                    <!--<input type="hidden" name="manu_item_id" id="manu_item_id" value="">-->

                                    <fieldset>
                                        <?php
										echo $_SESSION['_user_branch'];
                                            if ($_REQUEST['si_id'] != "") {
                                                $si_no = leadingZeros($dbconn->GetSingleReconrd('tbl_store_indent', 'si_slno', 'si_id', $_REQUEST['si_id']), 4);
                                            } else {
                                                // $si_no = leadingZeros($dbconn->GetMaxValue('tbl_store_indent', 'si_slno', '  1 ', 1) + 1, 4);
												$si_finyr=$dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
                                                //$si_no = leadingZeros($dbconn->GetMaxValue('tbl_store_indent', 'si_slno', 'si_finyr',$si_finyr) + 1, 4);
												$si_no = leadingZeros($dbconn->GetMaxValue('tbl_store_indent', 'si_slno', ' branch_id="'.$_SESSION['_user_branch'].'" AND si_finyr',$si_finyr) + 1, 4);
                                                

                                            }
                                        ?>

                                       
                                        <div class="card-body">
                                            <div class="form-group">
                                                <div class="row">
                                                    <label class="col-lg-1 col-form-label">Store Indent No. <span class="text-mandatory"> *</span></label>
                                                    <div class="col-lg-3">
                                                        <input type="text" class="form-control" name="si_no" readonly value="<?php echo $si_no; ?>" />
                                                    </div>

                                                    <label class="col-lg-1 col-form-label">Indent Date <span class="text-mandatory"> *</span></label>
                                                    <div class="col-lg-3">
													    <input type="date" name="si_date" id="si_date" class="form-control" maxlength="75" max="<?php echo date('Y-m-d'); ?>"  value="<?php echo $si_date; ?>" placeholder="Date" />
                                                    </div>
                                                </div>
                                            </div>
                                            <legend class="font-weight-semibold"><i class="icon-cart mr-2"></i>Store Indent Details</legend>

                                            <div class="form-group row">
                                                <div class="form-group col-md-4">
                                                    <label>Item Name<span class="text-mandatory"> *</span></label>
                                                    <input type="text" class="form-control" placeholder="Items" name="item_code" id="item_code" autocomplete="" />
											        <input type="hidden" name="item_id" id="item_id" value="">
                                                </div>

                                                <div class="form-group pl-0 col-md-1">
                                                    <p><b>In Stock</b></p>
                                                    <input type="text" class="form-control" name="curr_stock" id="curr_stock" maxlength="9" readonly value="" />
                                                </div>

                                               

                                                <div class="form-group pl-0 col-md-2">
                                                    <p><b>MOQ</b></p>
                                                 
                                                        <input type="text" class="form-control" name="item_moq" id="item_moq" maxlength="7" tabIndex="-1" readonly value="" />
                                                 
                                                </div>
                                                <div class="form-group pl-0 col-md-2">
                                                    <p><b>Quantity</b><span class="text-mandatory"> *</span></p>
                                                    
                                                        <input type="text" class="form-control" name="si_qty" id="si_qty" maxlength="7" tabIndex="-1" onkeypress="return isNumberKey_With_Dot(event)" value="" />
                                                  
                                                </div>
                                                <div class="form-group pl-0 col-md-2">
                                                    <p><b>UOM</b></p>
                                                    <select name="si_unit" id="si_unit"  data-placeholder="Choose a UOM.." class="select">
                                                       
                                                    </select>
                                                    <!-- <script>
                                                        document.thisForm.pi_unit.value = "<?php //echo $get->pi_unit; ?>";
                                                    </script> -->
                                                </div>

                                                <div class="form-group pl-0" id="item_indv_add_btn">
                                                    <button class="btn btn-success mr-2 mt-4 pt-1" id="add_items" name="add_items" type="button"> +</button>
                                                </div>
                                            </div>


                                            <div class="form-group row">
                                                <div id="show_table" class="col-md-12">
                                                    <table class="table table-bordered" style="font-size: small !important;">
                                                        <thead>
                                                            <tr class="bg-teal">
                                                                
                                                                <th width="10%">Item Code</th>
                                                                <th width="20%">Description</th>
                                                                <th width="5%">In Stock</th>
                                                                <th width="5%" style="text-align:center;">UOM</th>
                                                                <th width="5%">MOQ</th>
                                                                <th width="5%" style="text-align:center;">Quantity</th>
                                                                <th width="10%" style="text-align:center;">Action</th>
                                                                <!--<th width="8%">&nbsp;</th>-->
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php if ($_REQUEST['si_id'] != '') {

                                                                $dets_sql = "SELECT * FROM tbl_store_indent_details WHERE si_id = " . $_REQUEST['si_id'];
                                                                $result_dets = $conn->query($dets_sql);
                                                                $rowCnt = $result_dets->rowCount();
                                                                if ($result_dets->rowCount() > 0) {
                                                                    
                                                                    while ($itm = $result_dets->fetch()) {
                                                                        $item_desciption = $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_id", $itm->item_id);
                                                                        $item_purchase_code = $dbconn->GetSingleReconrd("tbl_item_details", "item_purchase_code", "item_id", $itm->item_id);
                                                                        $item_code = $dbconn->GetSingleReconrd("tbl_item_details", "supp_item_code", "item_id", $itm->item_id);
                                                                        $uom_no = $dbconn->GetSingleReconrd("tbl_item_details", "item_uom", "item_id", $itm->item_id);
                                                                    	

                                                                        if($itm->si_unit == 0 || $itm->si_unit == '' || $itm->si_unit == 'NULL'){
                                                                            $si_uom = $dbconn->GetSingleReconrd("mst_uom","uom_name","uom_id",$uom_no);
                                                                        }else{
                                                                            $si_uom = $dbconn->GetSingleReconrd("mst_uom","uom_name","uom_id",$itm->si_unit);

                                                                        }
                                                                    	$si_remarks = $dbconn->GetSingleReconrd("tbl_store_indent","si_remarks","si_id",$itm->si_id);

                                                                        echo '
                                                                        <tr id="item' . $itm->item_id . '" >
                                                                            <td>' . $item_purchase_code . '
                                                                                <input type="hidden" class="temp_item_id" name="temp_item_id[]" value="' . $itm->item_id . '" />
                                                                            </td>
                                                                           <!-- <td>' . $item_code . '
                                                                                <input type="hidden" class="item_code" name="item_code[]" value="' . $item_code . '" />
                                                                            </td>-->
                                                                            <td>' . $item_desciption . '
                                                                                <input type="hidden" class="item_desciption" name="item_desciption[]" value="' . $item_desciption . '" />
                                                                            </td>
                                                                            <td>' . $itm->curr_stock . '
                                                                                <input type="hidden" class="curr_stock" name="curr_stock[]" value="' . $itm->curr_stock . '" />
                                                                            </td>
                                                                            <td class="text-center">' .  $si_uom . '
                                                                            <input type="hidden" class="temp_item_uom" name="temp_item_uom[]" value="' . $uom_no  . '" />
                                                                            <input type="hidden" class="temp_si_unit" name="temp_si_unit[]" value="' . $itm->si_unit  . '" />
                                                                            </td>
                                                                            <td>' . $itm->item_moq . '
                                                                                <input type="hidden" class="item_moq" name="item_moq[]" value="' . $itm->item_moq . '" />
                                                                            </td>
                                                                            
                                                                            <td class="text-center">' . $itm->si_qty . '</td>
                                                                            <input type="hidden" class="temp_si_qty" name="temp_si_qty[]" value="' . $itm->si_qty . '" />
                                                                            </td>
                                                                           
                                                                            <td class="text-center">
                                                                                <a href="javascript:remove_item_si_details(' . $itm->item_id . ');" class="" rel="' . $itm->item_id . '"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a>
                                                                            </td>
                                                                        </tr>';
                                                                        
                                                                       
                                                                    }
                                                                }
                                                            }
                                                            ?>
													   </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                                <!-- <script type="text/javascript">
                                                    remove_item(0);
                                                </script> -->

                                            <div class="form-group pl-0 col-md-12 pt-4">
                                                <label>Remarks (if any) : <span class="text-mandatory"></span></label>
                                                <textarea name="si_remarks" maxlength="500" id="si_remarks" class="form-control"><?php echo $si_remarks; ?></textarea>
										    </div>

                                        </div>
                                        <div class="card-footer text-center">

                                            <?php if ($_REQUEST["si_id"] != '') { ?>
                                                <INPUT class="btn btn-info" type="submit" name="UPDATE" value="UPDATE">
                                                <INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
												<INPUT class="btn btn-success mr-2" type="submit" name="FINALIZE" value="Send to Purchase" onclick="return fnValidate();">
                                                <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['si_id']; ?>">
                                            <?php } else { ?>
                                                <INPUT class="btn btn-info" type="submit" name="SAVE" value="SAVE">
                                                <INPUT class="btn btn-light " type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
                                                <input type="hidden" name="txtHid" value="0">
                                            <?php } ?>

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
</body>

</html>