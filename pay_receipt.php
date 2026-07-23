<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
require_once("inc/common/css-js.php");

isAdmin();
$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$so_amoutn = $dbconn->GetSingleReconrd("tbl_sales_order", "bal_value", "so_id", $_REQUEST['so_id']);
$bal = floatval($so_amoutn);
if (isset($_POST['SAVE'])) {
    $_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
    $_REQUEST['pay_date'] = date("Y-m-d", strtotime($_REQUEST['pay_date']));
    $_REQUEST['pay_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
    $_REQUEST['pay_slno'] = $dbconn->GetMaxValue('tbl_receipt', 'pay_slno', 'pay_finyr', $_REQUEST['pay_finyr']) + 1;
    $chq_passed = '';
    if ($_REQUEST['pay_type'] == 'Q') {
        $chq_no = $_REQUEST['pay_chq_no'];
        $pay_card_no = '';
        $pay_refno = '';
        $account_transfer = '';
    } elseif ($_REQUEST['pay_type'] == 'B') {

        $pay_card_no = $_REQUEST['pay_cardno'];
        $chq_no = '';
        $pay_refno = '';
        $account_transfer = '';
    } elseif ($_REQUEST['pay_type'] == 'N') {

        $pay_refno = $_REQUEST['pay_refno'];
        $chq_no = '';
        $pay_card_no = '';
        $account_transfer = '';
    } elseif ($_REQUEST['pay_type'] == 'A') {

        $pay_refno = $_REQUEST['pay_refno'];
        $chq_no = '';
        $pay_card_no = '';
        // $pay_refno = '';
    } elseif ($_REQUEST['pay_type'] == 'C') {
        $chq_no = '';
        $pay_card_no = '';
        $pay_refno = '';
        $account_transfer = '';

        $chq_passed = "NO";
    }

    $stmt = null;
    $stmt = $conn->prepare("INSERT INTO tbl_receipt (so_id, supp_id, pay_slno, pay_finyr, ledger_id, pay_date, pay_type, pay_amount, pay_cardno, pay_refno, pay_chq_no, pay_chq_dt, pay_remarks, chq_passed, modify_date_time) VALUES (:so_id, :supp_id, :pay_slno, :pay_finyr, :ledger_id, :pay_date, :pay_type, :pay_amount, :pay_cardno, :pay_refno, :pay_chq_no, :pay_chq_dt, :pay_remarks, :chq_passed, :modify_date_time)");
    $data = array(
        ':so_id' => $_REQUEST['so_id'],
        ':supp_id' => $_REQUEST['supp_id'],
        ':pay_slno' => $_REQUEST['pay_slno'],
        ':pay_finyr' => $_REQUEST['pay_finyr'],
        ':ledger_id' => $_REQUEST['ledger_id'],
        ':pay_date' => $_REQUEST['pay_date'],
        ':pay_type' => $_REQUEST['pay_type'],
        ':pay_amount' => $_REQUEST['pay_amount'],
        ':pay_cardno' => $pay_card_no,
        ':pay_refno' => $pay_refno,
        ':pay_chq_no' => $chq_no,
        ':pay_chq_dt' => $_REQUEST['pay_chq_dt'],
        ':pay_remarks' => $_REQUEST['pay_remarks'],
        ':chq_passed' => $chq_passed,
        ':modify_date_time' => $_REQUEST['modify_date_time']
    );

    $stmt->execute($data);
    $last_id = $conn->lastInsertId();

    $conn->query("UPDATE tbl_sales_order set bal_value ='" . $_REQUEST['bal_value_hidd'] . "' WHERE so_id=" . $_REQUEST['so_id']);
    // print_r($conn); die();
    if ($_REQUEST['pay_type'] == 'C') {
        $stmt = null;
        $stmt = $conn->prepare("INSERT INTO tbl_receipt_details (receipt_id, cash_id, cash_count, cash_value) VALUES (:receipt_id, :cash_id, :cash_count, :cash_value)");

        $row_count = count($_REQUEST['receipt_cash_id']);
        for ($n = 0; $n < $row_count; $n++) {
            $data = array(
                ':receipt_id' => $last_id,
                ':cash_id' => $_REQUEST['receipt_cash_id'][$n],
                ':cash_count' => $_REQUEST['cash_count'][$n],
                ':cash_value' => $_REQUEST['cash_value'][$n]
            );
            $stmt->execute($data);
        }
    }

    if ($_REQUEST['pay_amount'] >= $_REQUEST['bal']) {
        $status_update = $conn->prepare("UPDATE tbl_sales_order SET pay_status = 1, so_user_approve_by = 'E', so_verify_status = 1 	WHERE so_id = '" . $_REQUEST['so_id'] . "'");
        // print_r($status_update );die();
        $status_update->execute();
    }
    header("location:lst_sales_receipt.php");
    die();
}


//------------------ECHO-----------------------//

if (isset($_REQUEST['so_id'])) {
    $get_val = $conn->query("SELECT * FROM tbl_sales_order WHERE  so_id =' " . $_REQUEST['so_id'] . "'");

    if ($get_val->rowCount() > 0) {
        $obj = $get_val->fetch(PDO::FETCH_OBJ);
        $item_net_val = $obj->item_net_val;
        $bal_value = $obj->bal_value;
        $exceed_amount = $obj->exceed_amount;
        $so_no = $obj->so_slno;
        $supp_id = $obj->supp_id;

        $customer_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_status = '1' AND supp_id", $obj->supp_id);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <title><?php echo PAGE_TITLE; ?> - Sales Receipt </title>
    <link href="css/main.css" rel="stylesheet" type="text/css" />

    <?php include_once("inc/common/css-js.php"); ?>

    <!-- AUTO COMPLETE -->
    <script type='text/javascript' src='js/auto/jquery.autocomplete.js'></script>
    <!-- <link rel="stylesheet" type="text/css" href="js/auto/jquery.autocomplete.css" /> -->
    <?php include_once("inc/common/css-js.php"); ?>


</head>

<body>
    <!-- Main navbar -->
    <?php include("inc/common/header.php") ?>
    <!-- Page content -->
    <div class="page-content">
        <!-- Main sidebar -->
        <?php include("inc/common/sidebar.php") ?>
        <!-- Main content -->
        <div class="content-wrapper">
            <!-- Page header -->
            <div class="page-header">
                <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                    <div class="d-flex">
                        <div class="breadcrumb">
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
                            <a href="#" class="breadcrumb-item">Work Area</a>
                            <span class="breadcrumb-item active">Sales Receipt</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>
            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-7">
                        <form name='thisForm' id="validate" class="form-horizontal" method='post' action="pay_receipt.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                            <input type="hidden" name="so_id" id="so_id" value="<?php echo $_REQUEST['so_id']; ?>">
                            <input type="hidden" name="supp_id" id="supp_id" value="<?php echo $obj->supp_id; ?>">
                            <input type="hidden" id="bal_value_hidd" name="bal_value_hidd" value='' />
                            <input type="hidden" id="bal" name="bal" value='<?php echo number_format($bal, 2, '.', ''); ?>' />
                            <input type="hidden" id="tot_value_hidd" name="tot_value_hidd" value="" />
                            <div class="card">
                                <div class="card-header bg-pgheader text-white header-elements-inline">
                                    <h6 class="card-title">Sales Order Details</h6>
                                    <div class="header-elements">
                                        <div class="list-icons">
                                            <a class="list-icons-item" href="lst_sales_receipt.php" title="Sales Orders in Accounts "><i class="icon-arrow-left52 mr-2"></i></a>
                                            <a class="list-icons-item" data-action="fullscreen"></a>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                $numbers = str_pad($so_no, 3, '0', STR_PAD_LEFT);
                                ?>
                                <div class="card-body">

                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">SO No.</label>
                                        <label class="col-lg-3 col-form-label" type="text" id="inv_no" name="inv_no" tabindex="-1" style="font-size:15px; border: none; text-align: left; color: blue; font-weight: bold;" value=""> <?php echo $numbers; ?></label>
                                        <label class="col-lg-3 col-form-label">Customer</label>
                                        <label class="col-lg-4 col-form-label" type="text" name="supp_name" id="supp_name" readonly tabindex="-1" style="font-size:12px; border: none; text-align: left; color: blue; font-weight: bold;" value=""><?php echo $customer_name; ?></label>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">SO Amount</label>
                                        <input class="col-md-3 col-form-label" type="text" id="so_value" name="so_value" readonly tabindex="-1" style="font-size:15px; border: none; text-align: left; color: blue; font-weight: bold;" value='<?php echo  number_format($item_net_val, 2, '.', ''); ?>' />
                                        <label class="col-md-3 col-form-label">Balance Amount</label>
                                        <input class="col-md-3 col-form-label" type="text" name="bal_value" id="bal_value" tabindex="-1" style="font-size:15px; border: none; text-align: left; color: blue; font-weight: bold;" value='<?php echo number_format($bal, 2, '.', ''); ?>' />
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Exceed Amt.</label>
                                        <label class="col-lg-3 col-form-label" type="text" id="exissed_amount" name="exissed_amount" tabindex="-1" style="font-size:15px; border: none; text-align: left; color: blue; font-weight: bold;" value=""></label>
                                        <?php if ($obj->so_approve_status == 3 && $obj->pay_status == 4){ ?>
                                        <label class="col-lg-7 col-form-label" style="color:red;"><b>Credit Sales</b></label>
                                    <?php } elseif($obj->so_approve_status == 3 && $obj->so_status == 1 ){ ?>
                                        <label class="col-lg-7 col-form-label" style="color:red;"><b>Credit Sales Completed</b></label>
										<?php } ?>
                                    </div>
                                    
                                </div>
                                <div class="card-header bg-pgheader text-white header-elements-inline">
                                    <h6 class="card-title">New Receipt Details</h6>
                                </div>

                                <div class="card-body">
                                    <div class="form-group row">
                                        <!-- &nbsp;  -->
                                        <label class="col-lg-2 col-form-label">Receipt Dt. <span class="text-mandatory"> *</span></label>
                                        <div class=" col-lg-4">
                                            <input type="date" class="form-control col-lg-12" id="pay_date" name="pay_date" maxlength="75" max="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>" />
                                        </div>
                                        <label class="col-lg-2 col-form-label">Amount <span class="text-mandatory"> *</span></label>
                                        <div class="  col-lg-4">
                                            <input type="text" name="pay_amount" id="pay_amount" class="form-control col-lg-12" onkeypress="return isNumberKey_With_Dot(event)" maxlength="12" value="" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Ledger <span class="text-mandatory"> *</span></label>
                                        <div class=" col-lg-10">
                                            <select class=" form-control col-lg-12 select-search" name="ledger_id" id="ledger_id" data-placeholder="Choose a Ledger ..">
                                                <option value="">-- Select Ledger --</option>
                                                <?php
                                                $dbconn = new dbhandler();
                                                echo $dbconn->fnFillComboFromTable_Where("ledger_id", "ledger_name", "mst_ledger", "ledger_id", " WHERE ledger_status = 1"); ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Pay Mode <span class="text-mandatory"> *</span></label>
                                        <div class="  col-lg-5">
                                            <select name="pay_type" id="pay_type" data-placeholder="Choose a Pay mode.." class=" col-lg-12 select">
                                                <option value="">--Select--</option>
                                                <option value="C">Cash</option>
                                                <option value="Q">Cheque</option>
                                                <option value="B">Card</option>
                                                <option value="N">Net Banking</option>
                                                <option value="A">Account Transfer</option>
                                            </select>
                                        </div>
                                        <div class="  col-lg-5">
                                            <input type="date" class="form-control" name="pay_chq_dt" id="pay_chq_dt" placeholder="Cheque Dt." value="" />
                                        </div>
                                    </div>
                                    <legend></legend>
                                    <div class="form-group row" id="pay_refno_div">
                                        <label class="col-lg-2 col-form-label">Ref No <span class="text-mandatory">*</span></label>
                                        <div class="form-group  col-lg-10">
                                            <input type="text" class="form-control" name="pay_refno" id="pay_refno" placeholder="Ref No." maxlength="50" value="" autocomplete="off" />
                                        </div>
                                    </div>

                                    <div class="form-group row" id="pay_chq_div">

                                        <label class=" col-lg-2 col-form-label">Cheque No.<span class="text-mandatory">*</span></label>
                                        <div class="col-lg-10">
                                            <input type="text" class="form-control  col-lg-12" name="pay_chq_no" id="pay_chq_no" placeholder="Cheque No." onkeypress="return isNumberKey(event)" maxlength="10" value="" autocomplete="off" />
                                        </div>
                                    </div>
                                    <div class="form-group row" id="pay_cardno_div">
                                        <label class="col-lg-2 col-form-label">Card No <span class="text-mandatory">*</span></label>
                                        <div class=" col-lg-10">
                                            <input type="text" class="form-control col-lg-12" name="pay_cardno" id="pay_cardno" placeholder="Card No" value="" maxlength="20" onkeypress="return isNumberKey(event)" />
                                        </div>
                                    </div>
                                    <div id="cash_denomination">
                                        <div class="form-group row">
                                            <label class="col-lg-3 col-form-label">Cash Denomination <span class="text-mandatory">*</span></label>
                                            <div class=" col-lg-3">
                                                <select name="cash_id" id="cash_id" data-placeholder="Choose a Cash.." class="form-control col-lg-12 select">
                                                    <option value=""></option>
                                                    <?php
                                                    $dbconn = new dbhandler();
                                                    echo $dbconn->fnFillComboFromTable_Where("cash_id", "cash_name", "tbl_cash_details", "cash_id", " WHERE cash_status = '1'") ?>
                                                </select>
                                            </div>
                                            <label class="  col-lg-2 col-form-label">Cash Count <span class="text-mandatory"> *</span></label>
                                            <div class="col-lg-2">
                                                <input type="text" class="form-control" name="cash_count" id="cash_count" max="3" maxlength="9" onkeypress="return isNumberKey_With_Dot(event)" value="" />
                                            </div>
                                            <div class="form-group col-lg-2">
                                                <button class=" form-control btn btn-success" id="add_items" name="add_items" type="button"> +</button>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div id="show_table" class="col-md-12">
                                                <table class="table table-xs table-bordered" style="font-size: small !important;">
                                                    <thead>
                                                        <tr class="bg-teal">
                                                            <th width="5%">SNo.</th>
                                                            <th width="20%">Cash</th>
                                                            <th width="5%">Count</th>
                                                            <th width="5%">Total</th>
                                                            <th width="5%">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>

                                                    </tbody>
                                                    <tfoot>
                                                        <td></td>
                                                        <td></td>
                                                        <td align="right"><b>Total</b></td>
                                                        <th style="text-align: right;" id="tot_cost"><b></b></th>
                                                        <td></td>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                        <!-- <script type="text/javascript">remove_item(0);</script> -->
                                    </div>
                                    <br>
                                    <!-- <legend></legend> -->
                                    <div class="form-group row">
                                        <label class="col-lg-2 col-form-label">Remarks <br> (if any)</label>
                                        <div class="col-lg-10">
                                            <textarea type="text" name="pay_remarks" id="pay_remarks" class="form-control" maxlength="250"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($obj->accounts_verify_status == 0) { ?>
                                    <div class="card-footer text-center">
                                        <INPUT class="btn btn-custom mr-2" type="submit" name="SAVE" value="SAVE">
                                        <INPUT class="btn btn-light mr-2" type="button" name="cancel" value="Cancel" onClick="javascript:window.location.href='lst_sales_receipt.php'">
                                        <?php } ?>
                                    <?php if($obj->pay_status == 0 && $obj->accounts_status){ ?>
            
                                      	<span class="btn btn-warning mr-2" id="Credit">Mark as Credit Sales</span>                             
                                      <?php } ?>

                                
                                </div>
                            </div>

                    </div>
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header bg-pgheader text-white header-elements-inline">
                                <h6 class="card-title">Receipt Details</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-xs table-bordered" style="font-size: small !important;">
                                    <thead>
                                        <tr class="bg-teal">
                                            <th width="20%">Date</th>
                                            <th>Amount</th>
                                            <th width="40%"> Payment Mode</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>

                                    <?php
                                    $result1 = $conn->query("SELECT * FROM tbl_receipt WHERE pay_status = 0 AND so_id = '" . $_REQUEST['so_id'] . "' ORDER BY pay_date");
                                    if ($result1->rowCount() > 0) {
                                        $Sno = 1;
                                        $total_amt = 0;


                                        echo "<tbody>";
                                        while ($pay = $result1->fetch()) {
                                            $remarks = '';
                                            // $pmode = '<a  data-toggle="modal" data-target="#modalCashDets" href="" data-id=' . $pay->receipt_id . 'data-popup="tooltip" title="View">Cash</a>';
                                            if ($pay->pay_type == "C") {
                                                $pmode = '<a  data-toggle="modal" data-target="#modalCashDets" href="" data-id=' . $pay->receipt_id . ' data-popup="tooltip" title="">Cash</a>';
                                            } elseif ($pay->pay_type == 'Q') {
                                                $pmode = "Cheque dt. " . date('d-m-y', strtotime($pay->pay_chq_dt)) . " <br><b>Ref.No:</b>" . "<small> $pay->pay_chq_no </small>";
                                            } elseif ($pay->pay_type == 'B') {
                                                $pmode = "Card <br> <b>Ref.No:</b>" . "<small> $pay->pay_cardno </small>";
                                            } elseif ($pay->pay_type == 'N') {
                                                $pmode = "Net Banking <br> <b>Ref.No:</b>" . "<small> $pay->pay_refno </small>";
                                            } elseif ($pay->pay_type == 'A') {
                                                $pmode = "Account Transfer <br> <b>Ref.No:</b>" . "<small> $pay->pay_refno </small>";
                                            } else {

                                                $pmode = "Credit note againt Sales Order";
                                                $remarks = 'Invoice ' . $invoice_no;
                                            }

                                            echo '<tr class="align-left">						
                                            		<td>' . date('d-m-y', strtotime($pay->pay_date)) . '</td>
                                            		<td align="right">' . number_format(round($pay->pay_amount), 2) . '</td>
                                            		<td >' . $pmode . '</td>									
                                            		<td>' . $pay->pay_remarks . '</td>									
                                            	</tr>';
                                            $total_amt = $total_amt + $pay->pay_amount;
                                            $Sno++;
                                        }
                                        echo "</tbody>";
                                    }



                                    $bal = floatval($so_amoutn);

                                    echo '<tr style="font-weight:bold">								
												<td align="right"> Total </td>
												 <td align="right">' . number_format(round($total_amt), 2) . '</td>
												<td align="right"></td>	
												<td align="right"></td>	
											</tr>';
                                    echo '<tr style="font-weight:bold">								
												<td align="right"> Balance </td>
												<td align="right">' . number_format(round($bal), 2) . '</td>
												<td align="right"></td>	
												<td align="right"></td>	
											</tr>';
                                    echo '</table>';

                                    ?>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </form>
            <?php include("inc/common/footer.php") ?>
        </div>
    </div>
    <?php include("modal_cash_dts.php") ?>

</html>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.js"></script>
<script language="javascript">
    $(document).ready(function() {
        $('#pay_chq_div').hide();
        $('#pay_refno_div').hide();
        $('#pay_chq_dt').hide();
        $('#pay_cardno_div').hide();
        $('#cash_denomination').hide();
        $('#pay_type').trigger("change");
    });
    $('#pay_type').change(function() {
        var pay_mode = $('#pay_type').val();
        if (pay_mode == "Q") {
            $('#pay_chq_div').show();
            $('#pay_refno_div').hide();
            $('#pay_refno').val('');
            $('#pay_chq_dt').show();
            $('#pay_cardno_div').hide();
            $('#pay_cardno').val('');
            $('#cash_denomination').hide();
        } else if (pay_mode == "N") {
            $('#pay_refno_div').show();
            $('#pay_chq_dt').show();
            $('#pay_chq_div').hide();
            $('#pay_chq_no').val('');
            $('#pay_cardno_div').hide();
            $('#pay_cardno').val('');
            $('#cash_denomination').hide();

        } else if (pay_mode == "B") {
            $('#pay_cardno_div').show();
            $('#pay_chq_dt').show();
            $('#pay_chq_div').hide();
            $('#pay_chq_no').val('');
            $('#pay_refno_div').hide();
            $('#pay_refno').val('');
            $('#cash_denomination').hide();
        } else if (pay_mode == "A") {
            $('#pay_refno_div').show();
            $('#pay_chq_dt').show();
            $('#pay_chq_div').hide();
            $('#pay_chq_no').val('');
            $('#pay_cardno_div').hide();
            $('#pay_cardno').val('');
            $('#cash_denomination').hide();
        } else if (pay_mode == "C") {
            $('#pay_refno_div').hide();
            $('#pay_chq_dt').hide();
            $('#pay_chq_div').hide();
            $('#pay_chq_no').val('');
            $('#pay_cardno_div').hide();
            $('#pay_cardno').val('');
            $('#cash_denomination').show();
        } else {
            $('#pay_chq_div').hide();
            $('#pay_chq_no').val('');
            $('#pay_refno_div').hide();
            $('#pay_refno').val('');
            $('#pay_chq_dt').hide();
            $('#pay_cardno_div').hide();
            $('#pay_cardno').val('');
            $('#cash_denomination').hide();
        }
    });

    function fnValidate() {

        if (isNull(document.thisForm.pay_amount, "Amount...!")) {
            return false;
        }
        if (document.thisForm.pay_amount.value < 1) {
            alert("Please enter the amount...");
            return false;
        }
        var bal_value = parseFloat(document.thisForm.bal_value.value);
        var pay_amount = parseFloat(document.thisForm.pay_amount.value);

        // if (pay_amount > bal_value) {
        //     alert("Pay Amount cannot be less than" + bal_value + " ...");
        // }
        if (notSelected(document.thisForm.ledger_id, "ledger...!")) {
            return false;
        }
        if (notSelected(document.thisForm.pay_type, "Payment Mode...!")) {
            return false;
        }

        if (document.thisForm.pay_type.value == "Q") {
            if (isNull(document.thisForm.pay_chq_dt, "Cheque Dt. ..!")) {
                return false;
            }
            if (isNull(document.thisForm.pay_chq_no, "Cheque No. ..!")) {
                return false;
            }

        }
        if (document.thisForm.pay_type.value == "B") {
            if (isNull(document.thisForm.pay_cardno, "Card No. ..!")) {
                return false;
            }
            if (isNull(document.thisForm.pay_chq_dt, "Date. ..!")) {
                return false;
            }
        }
        if (document.thisForm.pay_type.value == "N") {
            if (isNull(document.thisForm.pay_refno, "Reference No. ..!")) {
                return false;
            }
            if (isNull(document.thisForm.pay_chq_dt, "Date. ..!")) {
                return false;
            }
        }
        if (document.thisForm.pay_type.value == "C") {
            // alert();
            var rowCount = $('#show_table tr').length;
            if (rowCount == 2) {
                alert("Please add Cash Amount Details");
                return false;
            }
            // alert();
            var pay_amount = $('#pay_amount').val();
            var tot_cost = $('#tot_value_hidd ').val();
            if (pay_amount == tot_cost) {
                alert("Please Check The Amount");
                return false;
            }

        }
        if (document.thisForm.pay_type.value == "C") {
            var pay_amount = parseFloat($('#pay_amount').val());
            var tot_cost = parseFloat($('#tot_value_hidd ').val());
            if (pay_amount != tot_cost) {
                alert("Please Check The Amount");
                return false;
            }
        }
    }

    function remove_item(auto_id) {
        $('#' + auto_id).remove();
        findReciptTotal();
    }



    function findReciptTotal() {
        var sales_total_cost = 0;
        $("#show_table tr").each(function() {
            sales_order_reci = parseFloat($(this).closest('tr').find('.cash_value').val());
            if (isNaN(sales_order_reci)) sales_order_reci = 0;
            sales_total_cost += sales_order_reci;
        });
        $('#tot_cost').html(sales_total_cost.toFixed(2));
        $('#tot_value_hidd').val(sales_total_cost.toFixed(2));
    }


    $('#add_items').click(function() {

        if (notSelected(document.thisForm.cash_id, "Cash Denomination ..!")) {
            return false;
        }
        if (isNull(document.thisForm.cash_count, "Cash Count. ..!")) {
            return false;
        }

        var table = document.getElementById("show_table");
        var rowCount = ($('#show_table').find('tr').length) - 1;
        var cash_id = $("#cash_id").val();
        var cash_count = $("#cash_count").val();


        $.ajax({
            type: "POST",
            url: "inc/cis_ajax/jquery_cash_receipt_details.php",
            data: {
                "cash_id": cash_id,
                "cash_count": cash_count,
                "row_count": rowCount,
                'mode': 'save'
            }
        }).done(function(msg) {
            $("#show_table tbody").append(msg);
            $("#cash_count").val('');
            $("#cash_id").val('').trigger('change');
            findReciptTotal();
        });
    });
    $('#pay_amount').change(function() {
        var bal_value = $('#bal').val();
        var pay_amount = $('#pay_amount').val();
        var dis_bal_val = bal_value - pay_amount;
        $('#bal_value').val(dis_bal_val.toFixed(2)).trigger('change');
        $('#bal_value_hidd').val(dis_bal_val.toFixed(2));
    });
    $('#modalCashDets').on('show.bs.modal', function(e) {
        // alert();
        var id = $(e.relatedTarget).data('id');
        var cus_name = $('#supp_name').html();
        var inv_no = $('#inv_no').html();
        // var pay_remarks = $('#pay_remarks').html();
        // alert(cus_name);
        if (id != '') {
            $.ajax({
                type: 'post',
                url: 'inc/cis_ajax/jquery_modal_cash_dts.php',
                data: {
                    'id': id,
                    'cus_name': cus_name,
                    'inv_no': inv_no
                }
            }).done(function(data) {
                // alert(data); 
                string = data.split("~");
                $('#m_supp_code').html(string[0]);
                $('#m_cash_dets').html(string[1]);
                $('#m_supp_name').html(string[2]);
                // $('#m_supp_dets').html(string[1]);
            });
        }
    });


    $("#Credit").click(function(){
		if($("#pay_remarks").val()=='')
		{
			alert('Please Enter the Remarks'); 
			return false;

		}
		else
		{
			var remarks = $("#pay_remarks").val();
			var so_id = $("#so_id").val();
			$.ajax({
			type: "POST",
			url: "inc/cis_ajax/jquery_so_pay_status.php",
			data: {'remarks':remarks, 'so_id':so_id, 'mode':"admin"}
			}).done(function( msg ) {
				
				window.location.replace("lst_sales_receipt.php");
			});
		}
    });

    $("#confirm").click(function(){
			
		var so_id = $("#so_id").val();
		$.ajax({
		type: "POST",
		url: "inc/cis_ajax/jquery_so_pay_status.php",
		data: {'so_id':so_id, 'mode':"employee"}
		}).done(function( msg ) {
			
			window.location.replace("lst_sales_receipt.php");
		});
		

	});
</script>