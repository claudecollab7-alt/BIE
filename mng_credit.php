<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
// require_once ("inc/common/dbrowiterator.php");	

isAdmin();


// ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);

$conn = new dbconnect();
$dbconn = new dbhandler();
$inv_no = '001';

$inv_date = date("Y-m-d");




if (isset($_POST['UPDATE'])) {
    //echo "<pre>";print_r($_POST);

    $update_id = $_REQUEST['txtHid'];
    // echo "<pre>";print_r($update_id);exit;
    try {
        $_REQUEST['inv_date'] = date("Y-m-d", strtotime($_REQUEST['inv_date']));
        $_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
        $_REQUEST['modify_by'] = $_SESSION['_user_id'];
        if (isset($_REQUEST['branch_id'])) {
            $_REQUEST['branch_id'] = $_REQUEST['branch_id'];
        } else {
            $_REQUEST['branch_id'] = 0;
        }

        $stmtcred = null;
        $stmtcred = $conn->prepare("INSERT INTO tbl_invoice_credit_details (inv_id,pay_id,cash_id,paid_amount,paid_date,remarks,pay_refno,pay_chq_no,pay_cardno) VALUES(:inv_id, :pay_id, :cash_id, :paid_amount, :paid_date, :remarks, :pay_refno, :pay_chq_no, :pay_cardno)");
        //echo $stmtcred;exit;

        $datacred = array(
            ':inv_id' => $update_id,
            ':pay_id' => $_REQUEST['pay_id'],
            ':cash_id' => $_REQUEST['temp_cash_id'][$x],
            ':paid_amount' => $_REQUEST['amount'],
            ':paid_date' => date("Y-m-d", strtotime($_REQUEST['pay_chq_dt'])),
            ':remarks' => $_REQUEST['inv_remarks'],
            ':pay_refno' => $_REQUEST['pay_refno'],
            ':pay_chq_no' => $_REQUEST['pay_chq_no'],
            ':pay_cardno' => $_REQUEST['pay_cardno']
        );
        //echo "<pre>";print_r($datacred);exit;
        $stmtcred->execute($datacred);
        $last_id = $conn->lastInsertId();

        $bal_amt = $dbconn->GetSingleReconrd("tbl_invoice", "inv_bal_value", "inv_id", $update_id);
        $update_bal_amt = ((float)$bal_amt - (float)$_REQUEST['amount']);
        $conn->query("UPDATE tbl_invoice SET inv_bal_value = '" . $update_bal_amt . "' WHERE inv_id=" . $update_id);

        /* details */
        //    $delete_details =  "DELETE FROM  tbl_invoice_denomination_details WHERE inv_id = '" . $update_id . "'";
        //    $result = $conn->prepare($delete_details);
        //    $result->execute();


        /* details */
        if (isset($_REQUEST['temp_cash_id'])) {
            for ($x = 0; $x < count($_REQUEST['temp_cash_id']); $x++) {

                //echo count($_REQUEST['temp_cash_id']);
                $stmt1 = null;
                $stmt1 = $conn->prepare("INSERT INTO tbl_invoice_denomination_details (inv_id, crd_id, cash_id, den_name, den_count, den_total) 
                 VALUES (:inv_id, :crd_id, :cash_id, :den_name, :den_count, :den_total)");
                $data = array(
                    ':inv_id' => $update_id,
                    ':crd_id' =>   $last_id,
                    ':cash_id' => $_REQUEST['temp_cash_id'][$x],
                    ':den_name' => $_REQUEST['temp_cash_name'][$x],
                    ':den_count' => $_REQUEST['temp_cash_count'][$x],
                    ':den_total' => $_REQUEST['temp_total'][$x]
                );

                //print_r($data);
                $stmt1->execute($data);
            }
        }

        /* details */
    } catch (Exception $e) {
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
    }
    $_SESSION['_msg'] = "Direct Invoice succesfully Updated..!";
    header("location:invoice_list.php");
    die();
}


if (isset($_REQUEST['inv_id']) && $_REQUEST['inv_id'] != "") {

    $result = $conn->query("SELECT * FROM tbl_invoice WHERE inv_id = " . $_REQUEST['inv_id']);
    if ($result->rowCount() > 0) {
        $inv_dc_date = '';
        $get = $result->fetch(PDO::FETCH_OBJ);

        if ($get->inv_date != "0000-00-00" && $get->inv_date != "") {
            $inv_date = date("Y-m-d", strtotime($get->inv_date));
        }
        if ($get->inv_dc_date != "0000-00-00" && $get->inv_dc_date != "") {
            $inv_dc_date = date("Y-m-d", strtotime($get->inv_dc_date));
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
    <title><?php echo PAGE_TITLE; ?> - Invoice</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <?php include_once("inc/common/css-js.php"); ?>

</head>

<script type="text/javascript">
    function fnValidate2() {

        //To validate cash text box amount and total amount of cash are equal
        //To get the cash total amount
        var total_val = 0;
        $("#show_table2 tr").each(function() {
            var temp_total = $(this).closest('tr').find('.temp_total').val();
            if (temp_total > 0) {
                total_val = parseFloat(total_val) + parseFloat(temp_total);

            }
        });

        if (isNull(document.thisForm.pay_id, "Pay Mode. ..!")) {
            return false;
        }

        //To get the cash text box amount
        var amount = $("#amount").val();
        if (amount == '') {
            alert("Kindly enter the amount");
            return false;
        }

        //To get the paytype 
        var paytype = $("#pay_id").val();


        //check the condition for payment type is cash 
        if (paytype == 1) {

            if (total_val < amount) {
                alert("Kindly check the Cash amount and total amount");
                return false;
            }
        }

        var bal_amt = $("#bal_amt").val();
        if (parseFloat(amount) > parseFloat(bal_amt)) {
            // alert(amount);
            // alert(bal_amt);
            alert("Amount is greater than Balance amount");
            return false;
        }

        //End of validation for cash

        // if (notSelected(document.thisForm.pay_id, "Payment Mode..!")) {
        //    alert("select pay method");
        //        return false;
        //    }

        if (document.thisForm.pay_id.value == "2") {
            //alert("2")
            if (isNull(document.thisForm.pay_chq_dt, "Cheque Date. ..!")) {
                return false;
            }
            if (isNull(document.thisForm.pay_chq_no, "Cheque No. ..!")) {
                return false;
            }
        }
        if (document.thisForm.pay_id.value == "3") {
            if (isNull(document.thisForm.pay_chq_dt, "Date. ..!")) {
                return false;
            }
            if (isNull(document.thisForm.pay_cardno, "Card No. ..!")) {
                return false;
            }
        }
        if (document.thisForm.pay_id.value == "4") {
            if (isNull(document.thisForm.pay_chq_dt, "Date. ..!")) {
                return false;
            }
            if (isNull(document.thisForm.pay_refno, "Reference No. ..!")) {
                return false;
            }
        }
        if (document.thisForm.pay_id.value == "5") {
            if (isNull(document.thisForm.pay_chq_dt, "Date. ..!")) {
                return false;
            }
            if (isNull(document.thisForm.pay_refno, "Reference No. ..!")) {
                return false;
            }
        }
        if (document.thisForm.pay_id.value == "6") {
            if (isNull(document.thisForm.pay_chq_dt, "Date. ..!")) {
                return false;
            }
            if (isNull(document.thisForm.pay_refno, "Reference No. ..!")) {
                return false;
            }
        }



        document.thisForm.submit();
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

        $('#cash_id').change(function() {
            var cash_id = $('#cash_id').val();
            $('#cash_id_no').val(cash_id);
        });

        $('.pay_chq_div').hide();
        $('.pay_refno_div').hide();
        $('.pay_chq_dt').show();
        $('.pay_cardno_div').hide();
        $('.amount_div').hide();
        $('.cash_denomination').hide();
        $('.pay_id').trigger("change");

        $('#pay_id').change(function() {
            var pay_mode = $('#pay_id').val();
            if (pay_mode == "2") {
                $('.pay_chq_div').show();
                $('.pay_refno_div').hide();
                $('#pay_refno').val('');
                $('.pay_chq_dt').show();
                $('.pay_cardno_div').hide();
                $('#pay_cardno').val('');
                $('.amount_div').show();
                $('#amount').val();
                $('.cash_denomination').hide();
            } else if (pay_mode == "4") {
                $('.pay_refno_div').show();
                $('.pay_chq_dt').show();
                $('.pay_chq_div').hide();
                $('#pay_chq_no').val('');
                $('.pay_cardno_div').hide();
                $('#pay_cardno').val('');
                $('.amount_div').show();
                $('#amount').val();
                $('.cash_denomination').hide();
            } else if (pay_mode == "3") {
                $('.pay_cardno_div').show();
                $('.pay_chq_dt').show();
                $('.pay_chq_div').hide();
                $('#pay_chq_no').val('');
                $('.pay_refno_div').hide();
                $('#pay_refno').val('');
                $('.amount_div').show();
                $('#amount').val();
                $('.cash_denomination').hide();
            } else if (pay_mode == "5") {
                $('.pay_refno_div').show();
                $('.pay_chq_dt').show();
                $('.pay_chq_div').hide();
                $('#pay_chq_no').val('');
                $('.pay_cardno_div').hide();
                $('#pay_cardno').val('')
                $('.amount_div').show();
                $('#amount').val();;
                $('.cash_denomination').hide();
            } else if (pay_mode == "1") {
                $('.pay_refno_div').hide();
                $('.pay_chq_dt').show();
                $('.pay_chq_div').hide();
                $('#pay_chq_no').val('');
                $('.pay_cardno_div').hide();
                $('#pay_cardno').val('');
                $('.cash_denomination').show();
            } else if (pay_mode == "6") {
                $('.pay_refno_div').show();
                $('.pay_chq_dt').show();
                $('.pay_chq_div').hide();
                $('#pay_chq_no').val('');
                $('.pay_cardno_div').hide();
                $('#pay_cardno').val('');
                $('.amount_div').show();
                $('#amount').val();
                $('.cash_denomination').hide();
            } else {
                $('.pay_chq_div').hide();
                $('#pay_chq_no').val('');
                $('.pay_refno_div').hide();
                $('#pay_refno').val('');
                $('.pay_chq_dt').show();
                $('.pay_cardno_div').hide();
                $('#pay_cardno').val('');
                $('.amount_div').show();
                $('#amount').val();
                $('.cash_denomination').hide();
            }
        }).trigger('change');

        $('#add_pay_items').click(function() {

            if ($('#cash_id').val() == "") {
                alert("Please select the Cash Denomination ...!");
                $('#cash_id').focus();
                return false;
            }

            if ($('#cash_count').val() == "") {
                alert("Please enter the Count ...!");
                $('#cash_count').focus();
                return false;
            }
            var table = document.getElementById("show_table2");
            var cash_id = $("#cash_id").val();
            var cash_count = $("#cash_count").val();

            var arr = [];
            var cash_id = $('#cash_id_no').val();
            $("#show_table2 tr").each(function() {
                arr.push(this.id);
            });
            if (jQuery.inArray(cash_id, arr) != -1) {
                $('#' + cash_id).remove();
            }

            $.ajax({
                type: "POST",
                url: "inc/cis_ajax/jquery_po_details.php",
                data: {
                    "cash_id": cash_id,
                    "cash_count": cash_count,
                    'mode': 'save_pay'
                }
            }).done(function(msg) {
                $("#show_table2 tbody").append(msg);
                $("#cash_id").val('').trigger('change');
                $("#item_code").val('');
                $('#cash_count').val('');

                findReciptTotal();
            });

        });

        $('#modalCashDet').on('show.bs.modal', function(e) {
            var id = $(e.relatedTarget).data('id');
            var po_title = $("#po_title").html();
            // alert(po_title);            
            if (id != '') {
                $.ajax({
                    type: 'post',
                    url: 'inc/cis_ajax/jquery_modal_cash_invoice.php',
                    data: {
                        "id": id,
                        "po_title": po_title
                    },
                    success: function(data) {
                        // alert(data);
                        // $('#m_cash_inv').html(data);
                        string = data.split("~");
                        $('#m_inv_name').html(string[0]);
                        $('#m_cash_inv').html(string[1]);
                    }
                });
            }
        });
    });


    function remove_item(auto_id) {
        $('#tbl' + auto_id).remove();
        findReciptTotal();
    }

    function findReciptTotal() {
        var sales_total_cost = 0;
        $("#show_table2 tr").each(function() {
            sales_order_reci = parseFloat($(this).closest('tr').find('.temp_total').val());
            if (isNaN(sales_order_reci)) sales_order_reci = 0;
            sales_total_cost += sales_order_reci;
            $('#show_table2 tfoot th#lastRow01').html(sales_total_cost.toFixed(2));
        });
    }

    $(function() {
        var today = new Date().toISOString().split('T')[0];
        document.getElementById("pay_chq_dt").setAttribute('max', today);
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
                                <h6 class="card-title"> Invoice</h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="invoice_list.php" title="Invoice List"><i class="icon-arrow-left52 mr-2"></i></a>
                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>

                            </div>
                            <form name='thisForm' class="form-horizontal" method='POST' action="">
                                <input type="hidden" name="inv_items" id="inv_items" value="-1">
                                <input type="hidden" name="gst" id="gst" value="">
                                <input type="hidden" name="item_hsn" id="item_hsn" value="">
                                <fieldset>

                                    <div class="card-body">
                                        <?php
                                        if ($_REQUEST['inv_id'] != "") {
                                            $inv_refno = $get->inv_refno;
                                        } else {
                                            $_REQUEST['inv_slno'] = $dbconn->GetMaxValue(' tbl_invoice', 'inv_slno', '1', 1) + 1;
                                            $_REQUEST['inv_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
                                            $inv_refno = 'INV/' . leadingZeros($_REQUEST['inv_slno'], 4) . '/' . $_REQUEST['inv_finyr'];
                                        }
                                        ?>


                                        <legend class="font-weight-semibold pb-0 mb-2"><span class="po_title" id="po_title"><?php echo $inv_refno; ?></span></legend>
                                        <div class="form-group">
                                            <div class="row">
                                                <input type="hidden" name="inv_no" id="inv_no" class="form-control" value="<?php echo $inv_no; ?>" />
                                                <input type="hidden" name="invo_id" id="invo_id" class="form-control" value="<?php echo $_REQUEST['inv_id']; ?>" />
                                                <div class="col-md-6">
                                                    <p> <b> Customer : </b><?php echo $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_id", $get->supp_id); ?></p>
                                                    <p><b> Customer Branch / Delivery Address : </b> <?php echo $dbconn->GetSingleReconrd("mst_supplier_new", "delivery_add1", "supp_id", $get->supp_id); ?></p>
                                                    <p><b>Invoice Date : </b><?php echo date('d-m-Y', strtotime($dbconn->GetSingleReconrd("tbl_invoice", "inv_date", "inv_id", $_REQUEST['inv_id']))); ?></p>
                                                    <p><b> Mode of Transport : </b><?php echo $dbconn->GetSingleReconrd("tbl_invoice", "inv_mode_of_trans", "inv_id", $_REQUEST['inv_id']); ?></p>
                                                </div>
                                                <div id="comm_dets" class="col-md-6">
                                                    <p> <b> Vehicle No : </b><?php echo $dbconn->GetSingleReconrd("tbl_invoice", "inv_vechicle_no", "inv_id", $_REQUEST['inv_id']); ?></p>
                                                    <p><b> Transport Charges : </b> <?php echo $dbconn->GetSingleReconrd("tbl_invoice", "inv_trans_charge", "inv_id", $_REQUEST['inv_id']); ?></p>
                                                    <?php if ($get->invoice_type != 'Q') { ?>
                                                        <p><b> DC No : </b><?php echo $dbconn->GetSingleReconrd("tbl_invoice", "inv_dc_no", "inv_id", $_REQUEST['inv_id']); ?></p>
                                                        <p><b> DC Date : </b><?php echo $inv_dc_date ?></p>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <div class="row" style="background-color: aliceblue;font-size: 16px;  text-align: center;">
                                                <input type="hidden" name="inv_no" id="inv_no" class="" value="<?php echo $inv_no; ?>" />
                                                <div class="col-md-4">
                                                    <p> <b> Net Amount : &#8377; <span style="color:blue"><?php echo $get->inv_tot_value; ?></span></b></p>
                                                </div>
                                                <div id="comm_dets" class="col-md-4">
                                                    <p> <b> Paid Amount : &#8377;<span> <?php echo number_format($dbconn->GetSingleReconrd(" tbl_invoice_credit_details", "sum(paid_amount)", "inv_id", $get->inv_id), 2); ?></span></b></p>
                                                    <?php
                                                    //$netval =  $dbconn->GetSingleReconrd("tbl_invoice_details", "net_value", "inv_id", $get->inv_id);
                                                    $netval = $get->inv_tot_value;
                                                    $paidval =  $dbconn->GetSingleReconrd("tbl_invoice_credit_details", "sum(paid_amount)", "inv_id", $get->inv_id);
                                                    $balval = $netval - $paidval;
                                                    ?>
                                                </div>
                                                <div id="comm_dets" class="col-md-4">
                                                    <input type="hidden" name="bal_amt" id="bal_amt" value="<?php echo $balval; ?>">
                                                    <p><b>Balance Amount : &#8377; <span style="color:red"><?php echo number_format($balval, 2, ".", ""); ?></span></b></p>
                                                </div>
                                            </div>

                                            <?php
                                            $cred_sql = "SELECT * FROM tbl_invoice_credit_details WHERE inv_id = " . $_REQUEST['inv_id'];
                                            $result_cred = $conn->query($cred_sql);
                                            $rowCnt = $result_cred->rowCount();
                                            if ($result_cred->rowCount() > 0) {
                                                $sno = 1;
                                                $tot_total = 0;

                                                if ($paidval != "") {
                                                    echo "
                                                <div class='row'>
                                                	<div class='col-md-12'>
	                                                	<table class='table table-xs table-striped table-hover table-responsive'>
				                                            <thead class='thead-dark'>
				                                            <th>Date</th>
				                                            <th class=text-left>Amount</th>
				                                            <th>Pay Mode</th>
				                                            <th>Remarks</th>
				                                            </thead>
				                                            <tbody>
				                                            <tr>";
                                                    while ($cred = $result_cred->fetch()) {
                                                        if ($cred->pay_id == 1) {
                                                            $paymode = "Cash ";
                                                            $paymode = '<a  data-toggle="modal" data-target="#modalCashDet" href="" data-id=' . $cred->crd_id . ' data-popup="tooltip" title="">Cash</a>';
                                                        } elseif ($cred->pay_id == 2) {
                                                            $paymode = "Cheque <br> <small>" . $cred->pay_chq_no . "</small>";
                                                        } elseif ($cred->pay_id == 3) {
                                                            $paymode = "Card <br> <small>" . $cred->pay_cardno . "</small>";
                                                        } elseif ($cred->pay_id == 4) {
                                                            $paymode = "Net Banking <br> <small>" . $cred->pay_refno . "</small>";
                                                        } elseif ($cred->pay_id == 5) {
                                                            $paymode = "Account Transfer <br> <small>" . $cred->pay_refno . "</small>";
                                                        } elseif ($cred->pay_id == 6) {
                                                            $paymode = "UPI <br> <small>" . $cred->pay_refno . "</small>";
                                                        }
                                                        echo "<td>" . date('d-m-Y', strtotime($cred->paid_date)) . "</td>
				                                            <td align='left'>" . $cred->paid_amount . "</td>
				                                            <td>" . $paymode . "</td>
				                                            <td>" . $cred->remarks . "</td>
				                                            </tr>";
                                                    }
                                                    echo "</tbody>
	                                                	</table>
													</div>
                                                </div>";
                                                }
                                            }
                                            ?>
                                            <?php
                                            if ($balval != 0) {
                                            ?>
                                                <div class="form-group pt-2">
                                                    <legend class="font-weight-semibold"><i class="icon-cash3 mr-2"></i>Payment Details</legend>
                                                    <div class="row">
                                                        <label class="col-lg-1 col-form-label">Pay Mode <span class="text-mandatory"> *</span></label>
                                                        <div class="col-lg-2">
                                                            <select name="pay_id" id="pay_id" data-placeholder="Choose a Pay mode.." class="form-control select-search">
                                                                <option value="">-- Select Pay Mode --</option>
                                                                <?php
                                                                echo $dbconn->fnFillComboFromTable_Where("pay_id", "pay_name", "mst_pay_method", "pay_id", " WHERE pay_status = '1' AND pay_id != '7'") ?>
                                                            </select>
                                                            <script>
                                                                document.thisForm.pay_id.value = "<?php echo $get->pay_id; ?>";
                                                            </script>
                                                        </div>
                                                        <label class="col-lg-1 col-form-label pay_chq_dt">Date <span class="text-mandatory"> *</span></label>
                                                        <div class="col-lg-2 pay_chq_dt">
                                                            <input type="date" name="pay_chq_dt" id="pay_chq_dt" class="form-control" placeholder="Date" />
                                                        </div>
                                                        <label class="col-lg-1 col-form-label pay_refno_div">Ref No <span class="text-mandatory"> *</span></label>
                                                        <div class="col-lg-2 pay_refno_div">
                                                            <input type="text" name="pay_refno" id="pay_refno" class="form-control " maxlength="50" placeholder="Ref No" />
                                                        </div>
                                                        <label class="col-lg-1 col-form-label pay_chq_div">Cheque No. <span class="text-mandatory"> *</span></label>
                                                        <div class="col-lg-2 pay_chq_div">
                                                            <input type="text" name="pay_chq_no" id="pay_chq_no" class="form-control" maxlength="50" placeholder="Cheque No." value="" />
                                                        </div>
                                                        <label class="col-lg-1 col-form-label pay_cardno_div">Card No <span class="text-mandatory"> *</span></label>
                                                        <div class="col-lg-2 pay_cardno_div">
                                                            <input type="text" name="pay_cardno" id="pay_cardno" class="form-control " maxlength="20" placeholder="Card No" />
                                                        </div>
                                                        <label class="col-lg-1 col-form-label amount_div">Amount <span class="text-mandatory"> *</span></label>
                                                        <div class="col-lg-2 amount_div">
                                                            <input type="text" name="amount" id="amount" class="form-control number_only_dot" maxlength="20" placeholder="Amount" />
                                                        </div>
                                                    </div>



                                                </div>

                                                <div class="row pt-3 cash_denomination">
                                                    <div class="col-md-12">
                                                        <fieldset>
                                                            <legend class="font-weight-semibold"><i class="icon-cash mr-2"></i>Cash Denomination</legend>
                                                            <div class="form-group row">
                                                                <div class="form-group col-md-2">
                                                                    <label>Cash Denomination <span class="text-mandatory">*</span></label>
                                                                    <select data-placeholder="Choose a Denomination.." name="cash_id" id="cash_id" class="form-control select-search">
                                                                        <option value="">-- Select Denomination --</option>
                                                                        <?php
                                                                        echo $dbconn->fnFillComboFromTable_Where("cash_id", "cash_name", "mst_cash_details", "cash_id", " WHERE cash_status = '1'") ?>
                                                                    </select>
                                                                    <input type="hidden" name="cash_id_no" id="cash_id_no">
                                                                </div>
                                                                <div class="form-group pl-0 col-md-2">
                                                                    <label>Cash Count <span class="text-mandatory"> * </span></label>
                                                                    <input type="text" name="cash_count" id="cash_count" class="form-control number_only_dot" maxlength="250">
                                                                </div>
                                                                <div class="form-group pl-0" id="item_indv_add_btn">
                                                                    <button class="btn btn-success mr-2 mt-4 pt-1" id="add_pay_items" name="add_pay_items" type="button"> +</button>
                                                                </div>
                                                            </div>
                                                        </fieldset>
                                                    </div>
                                                </div>

                                                <div class="row cash_denomination">
                                                    <div id="show_table2" class="col-md-6">
                                                        <table class="table table-xs table-bordered table-dets-responsive">
                                                            <thead>
                                                                <tr>
                                                                    <th width="10%">Cash</th>
                                                                    <th width="20%">Count</th>
                                                                    <th width="20%">Total</th>
                                                                    <th width="2%"><i class=" icon-cog6 mr-2"></i></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if ($_REQUEST['inv_id'] != '') {

                                                                    // $dets_sql = "SELECT * FROM tbl_invoice_denomination_details WHERE inv_id = " . $_REQUEST['inv_id'];
                                                                    // $result_dets = $conn->query($dets_sql);
                                                                    // $rowCnt = $result_dets->rowCount();
                                                                    // if ($result_dets->rowCount() > 0) {
                                                                    //     $sno = 1;
                                                                    //     $tot_total = 0;
                                                                    //     while ($itm = $result_dets->fetch()) {

                                                                    //         echo '
                                                                    //             <tr id="'.$itm->cash_id.'" >
                                                                    //                 <td>'.$itm->den_name.'
                                                                    //                     <input type="hidden" class="temp_cash_name" name="temp_cash_name[]" value="'.$itm->den_name.'" />
                                                                    //                     <input type="hidden" class="temp_cash_id" name="temp_cash_id[]" value="'.$itm->cash_id.'" />
                                                                    //                 </td>
                                                                    //                 <td class="text-right">'.round($itm->den_count).'
                                                                    //                     <input type="hidden" class="temp_cash_count" name="temp_cash_count[]" value="'.$itm->den_count.'" />
                                                                    //                 </td>
                                                                    //                 <td class="text-right">'.number_format(($itm->den_total), 2).'
                                                                    //                     <input type="hidden" class="temp_total" name="temp_total[]" value="'.$itm->den_total.'" />
                                                                    //                 </td>
                                                                    //                 <td class="text-center">
                                                                    //                     <a href="javascript:remove_item('.$itm->cash_id.');" class="" rel="'.$itm->cash_id.'"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a>
                                                                    //                 </td>
                                                                    //             </tr>';
                                                                    //         $sno++;
                                                                    //         $tot_total += $itm->den_total;
                                                                    //     }
                                                                    // }
                                                                }
                                                                ?>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <th colspan="2" class="text-right">Total : </th>
                                                                    <th class="text-right" id="lastRow01"><?php echo number_format($tot_total, 2); ?></th>
                                                                    <th class="text-right"></th>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="form-group row pt-4">
                                                    <label class="col-lg-2 col-form-label">Remarks</label>
                                                    <div class="col-lg-10">
                                                        <textarea name="inv_remarks" id="inv_remarks" class="form-control" rows="2"><?php echo $get->inv_remarks; ?></textarea>
                                                    </div>
                                                </div>
                                        </div>

                                    </div>

                                    <div class="card-footer text-center pt-2">
                                        <?php

                                                if (isset($_REQUEST["inv_id"]) && $_REQUEST["inv_id"] != '') { ?>
                                            <INPUT class="btn btn-primary mr-2" type="submit" name="UPDATE" value="SAVE" onclick="return fnValidate2();">

                                            <INPUT class="btn btn-light" type="button" name="Cancel" value="Cancel" onClick="javascript:window.location.href='invoice_list.php'">
                                            <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['inv_id']; ?>">
                                        <?php }  ?>
                                    <?php

                                            } else {

                                                echo "<br><div style=text-align:center;font-size:16px;color:green;><b>Invoice Completed</b></div>";
                                            }
                                    ?>
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



    <?php include("modal_cash_invoice.php") ?>

</body>


</html>