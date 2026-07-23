    <?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
// require_once ("inc/common/dbrowiterator.php");	

isAdmin();



$conn = new dbconnect();
$dbconn = new dbhandler();

//ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);
$po_pre_date = date("Y-m-d");

if (isset($_POST['SAVE'])) {

    try {
        $_REQUEST['po_prepare_date'] = date("Y-m-d", strtotime($_REQUEST['po_prepare_date']));

        $_REQUEST['po_prepare_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
        $_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
        $_REQUEST['modify_by'] = $_SESSION['_userid'];

        $stmt = null;
        $stmt = $conn->prepare("INSERT INTO tbl_po_prepare (po_prepare_date, po_prepare_finyr,si_id, po_prepare_remarks, company_id, modify_date_time, modify_by, send_to_admin_status) VALUES (:po_prepare_date, :po_prepare_finyr,:si_id, :po_prepare_remarks, :company_id, :modify_date_time, :modify_by, :send_to_admin_status)");
        $data = array(
            ':po_prepare_date' => $_REQUEST['po_prepare_date'],
            ':po_prepare_finyr' => $_REQUEST['po_prepare_finyr'],
            ':si_id' => $_REQUEST['si_id'],
            ':po_prepare_remarks' => $_REQUEST['si_remarks'],
            ':company_id' => '',
            ':modify_date_time' => '',
            ':modify_by' => '',
            ':send_to_admin_status' => 0
        );
        $stmt->execute($data);
        $last_id = $conn->lastInsertId();


        /* ------------ SAVE tbl_po_prepare_dets  -----------*/


        foreach ($_POST['supp_supp_id'] as $key => $value) {

            $supp_id = $_POST['supp_supp_id'][$key];

            $pi_new_qty = $_POST['pi_new_qty'][$key];
            //$gen_po_status = $dbconn->GetSingleReconrd("tbl_po_prepare_dets","gen_po_status","po_prepare_id = '".$last_id."' AND item_id", $_POST['new_item_id'][$key]);
            //if($gen_po_status == 0){
            if ($pi_new_qty == '' || $pi_new_qty <= 0) {
                $item_prepare_status = 1;
            } else {
                $item_prepare_status = 2;
            }

            $po_prepare_value = 0;
            $stmt2 = null;
            $stmt2 = $conn->prepare("INSERT INTO tbl_po_prepare_dets (po_prepare_id, si_id, supp_id, item_id, si_qty, uom, uom_code, item_discount, unit_price, price, gst, gst_id, net_amt, item_prepare_status)
             VALUES (:po_prepare_id, :si_id,  :supp_id, :item_id, :si_qty, :uom, :uom_code, :item_discount, :unit_price, :price, :gst, :gst_id, :net_amt, :item_prepare_status)");
            $data = array(
                ':po_prepare_id' => $last_id,
                ':si_id' => $_REQUEST['si_id'],
                ':supp_id' => $supp_id,
                ':item_id' => $_POST['new_item_id'][$key],
                ':si_qty' => $pi_new_qty,
                ':uom' => $_POST['uom'][$key],
                ':uom_code' => $_POST['uom_code'][$key],
                ':item_discount' => $_POST['item_discount'][$key],
                ':unit_price' => $_POST['unit_price'][$key],
                ':price' => $_POST['price'][$key],
                ':gst' => $_POST['vat'][$key],
                ':gst_id' => $_POST['vat_id'][$key],
                ':net_amt' => $_POST['net_amt'][$key],
                ':item_prepare_status' => $item_prepare_status


            );


            $stmt2->execute($data);
            //print_r($data);
            //die();
            //}

        }
        //print_r($data);
        //die();
        header("location:po_prepare_list.php");
    } catch (Exception $e) {
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        echo $_SESSION['_msg_err'] = $str;
    }

    $_SESSION['_msg'] = "Purchase Order Prepare succesfully Saved..!";
    header("location:po_prepare_list.php");
    die();
}
if (isset($_POST['UPDATE'])) {
    try {
        $update_id = $_REQUEST['txtHid'];
        $_REQUEST['po_prepare_date'] = date("Y-m-d", strtotime($_REQUEST['po_prepare_date']));

        $_REQUEST['po_prepare_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
        $_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
        $_REQUEST['modify_by'] = $_SESSION['_userid'];

        $stmt = null;
        $stmt = $conn->prepare("UPDATE tbl_po_prepare SET po_prepare_date=:po_prepare_date, po_prepare_finyr=:po_prepare_finyr,si_id=:si_id, po_prepare_remarks=:po_prepare_remarks, company_id=:company_id, modify_date_time=:modify_date_time, modify_by=:modify_by, send_to_admin_status=:send_to_admin_status  WHERE po_prepare_id = :po_prepare_id ");
        $data = array(
            ':po_prepare_id' => $update_id,
            ':po_prepare_date' => $_REQUEST['po_prepare_date'],
            ':po_prepare_finyr' => $_REQUEST['po_prepare_finyr'],
            ':si_id' => $_REQUEST['si_id'],
            ':po_prepare_remarks' => '',
            ':company_id' => '',
            ':modify_date_time' => '',
            ':modify_by' => '',
            ':send_to_admin_status' => 0
        );

        $stmt->execute($data);
        /* ------------ SAVE tbl_po_prepare_dets  for multiple supplier dets and item,qty dets  -----------*/

        $del_qry = $conn->query("DELETE FROM tbl_po_prepare_dets WHERE po_prepare_id = " . $update_id . " AND gen_po_status = 0");


        foreach ($_POST['supp_supp_id'] as $key => $value) {
            $supp_id = $_POST['supp_supp_id'][$key];
            $item_id = $_POST['new_item_id'][$key];

            $pi_new_qty = $_POST['pi_new_qty'][$key];
            $details_id = $dbconn->GetSingleReconrd("tbl_purchase_order_details", "details_id", "item_id = '" . $item_id . "' AND po_prepare_id", $update_id);

            if ($details_id == '') {

                if ($pi_new_qty == '' || $pi_new_qty <= 0) {
                    $item_prepare_status = 1;
                } else {
                    $item_prepare_status = 2;
                }


                $po_prepare_value = 0;
                $stmt2 = null;
                $stmt2 = $conn->prepare("INSERT INTO tbl_po_prepare_dets (po_prepare_id, si_id,  supp_id, item_id, si_qty, uom, uom_code, item_discount, unit_price, price, gst, gst_id, net_amt, item_prepare_status) 
                VALUES (:po_prepare_id, :si_id,  :supp_id, :item_id, :si_qty, :uom, :uom_code, :item_discount, :unit_price, :price, :gst, :gst_id, :net_amt, :item_prepare_status)");
                

                $data = array(
                    ':po_prepare_id' => $update_id,
                    ':si_id' => $_REQUEST['si_id'],
                    ':supp_id' => $supp_id,
                    ':item_id' => $item_id,
                    ':si_qty' => $pi_new_qty,
                    ':uom' => $_POST['uom'][$key],
					':uom_code' => $_POST['uom_code'][$key],
					':item_discount' => $_POST['item_discount'][$key],
					':unit_price' => $_POST['unit_price'][$key],
					':price' => $_POST['price'][$key],
					':gst' => $_POST['vat'][$key],
					':gst_id' => $_POST['vat_id'][$key],
					':net_amt' => $_POST['net_amt'][$key],
                    ':item_prepare_status' => $item_prepare_status
                );
                //echo $gen_po_status;
                $stmt2->execute($data);
            }
        }
        //print_r($data);
        //die();
        //die;

    } catch (Exception $e) {
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        echo $_SESSION['_msg_err'] = $str;
    }
    $_SESSION['_msg'] = "Purchase Order Prepare succesfully updated..!";
    header("location:po_prepare_list.php");
    die();
}

if (isset($_POST['send_to_admin'])) {
    try {
        $update_id = $_REQUEST['txtHid'];
        $_REQUEST['po_prepare_date'] = date("Y-m-d", strtotime($_REQUEST['po_prepare_date']));

        $_REQUEST['po_prepare_finyr'] = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
        $_REQUEST['modify_date_time'] = date('Y-m-d H:i:s');
        $_REQUEST['modify_by'] = $_SESSION['_userid'];

        $stmt = null;
        $stmt = $conn->prepare("UPDATE tbl_po_prepare SET po_prepare_date=:po_prepare_date, po_prepare_finyr=:po_prepare_finyr,si_id=:si_id, po_prepare_remarks=:po_prepare_remarks, company_id=:company_id, modify_date_time=:modify_date_time, modify_by=:modify_by, send_to_admin_status=:send_to_admin_status  WHERE po_prepare_id = :po_prepare_id ");
        $data = array(
            ':po_prepare_id' => $update_id,
            ':po_prepare_date' => $_REQUEST['po_prepare_date'],
            ':po_prepare_finyr' => $_REQUEST['po_prepare_finyr'],
            ':si_id' => $_REQUEST['si_id'],
            ':po_prepare_remarks' => '',
            ':company_id' => '',
            ':modify_date_time' => '',
            ':modify_by' => '',
            ':send_to_admin_status' => 1
        );

        $stmt->execute($data);
        /* ------------ SAVE tbl_po_prepare_dets  for multiple supplier dets and item,qty dets  -----------*/

        $del_qry = $conn->query("DELETE FROM tbl_po_prepare_dets WHERE po_prepare_id = " . $update_id . " AND gen_po_status = 0 ");


        foreach ($_POST['supp_supp_id'] as $key => $value) {
            $supp_id = $_POST['supp_supp_id'][$key];
            $item_id = $_POST['new_item_id'][$key];

            $pi_new_qty = $_POST['pi_new_qty'][$key];

            $details_id = $dbconn->GetSingleReconrd("tbl_purchase_order_details", "details_id", "item_id = '" . $item_id . "' AND po_prepare_id", $update_id);

            if ($details_id == '') {
                if ($pi_new_qty == '' || $pi_new_qty <= 0) {
                    $item_prepare_status = 1;
                } else {
                    $item_prepare_status = 3;
                }


                $po_prepare_value = 0;
                $stmt2 = null;
                $stmt2 = $conn->prepare("INSERT INTO tbl_po_prepare_dets (po_prepare_id, si_id,  supp_id, item_id, si_qty, uom, uom_code, item_discount, unit_price, price, gst, gst_id, net_amt, item_prepare_status) 
                VALUES (:po_prepare_id, :si_id,  :supp_id, :item_id, :si_qty, :uom, :uom_code, :item_discount, :unit_price, :price, :gst, :gst_id, :net_amt, :item_prepare_status)");
                $data = array(
                    ':po_prepare_id' => $update_id,
                    ':si_id' => $_REQUEST['si_id'],
                    ':supp_id' => $supp_id,
                    ':item_id' => $item_id,
                    ':si_qty' => $pi_new_qty,
                    ':uom' => $_POST['uom'][$key],
					':uom_code' => $_POST['uom_code'][$key],
					':item_discount' => $_POST['item_discount'][$key],
					':unit_price' => $_POST['unit_price'][$key],
					':price' => $_POST['price'][$key],
					':gst' => $_POST['vat'][$key],
					':gst_id' => $_POST['vat_id'][$key],
					':net_amt' => $_POST['net_amt'][$key],
                    ':item_prepare_status' => $item_prepare_status
                );
                $stmt2->execute($data);
            }
        }
        $sum_pi_qty = $dbconn->GetSingleReconrd("tbl_po_prepare_dets", "SUM(si_qty)", "si_id", $_REQUEST['si_id']);
        $sum_po_qty = $dbconn->GetSingleReconrd("tbl_store_indent_details", "SUM(si_qty)", "si_id", $_REQUEST['si_id']);
        $send_admin_status = 0;
        if ($sum_pi_qty == $sum_po_qty) {
            $send_admin_status = 2;
        } else {
            $send_admin_status = 1;
        }
        // print_r($sum_pi_qty);
        // print_r($sum_po_qty);
        // print_r($send_admin_status);
        //die();
        if (isset($_POST['send_to_admin'])) {
            $stmt1 = null;
            $stmt1 = $conn->prepare("UPDATE tbl_po_prepare SET send_admin_status=:send_admin_status WHERE po_prepare_id = :po_prepare_id ");
            $data1 = array(
                ':po_prepare_id' => $update_id,
                ':send_admin_status' => $send_admin_status,
            );
            $stmt1->execute($data1);
        }
    } catch (Exception $e) {
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        echo $_SESSION['_msg_err'] = $str;
    }
    $_SESSION['_msg'] = "Purchase Order Prepare succesfully updated..!";
    header("location:po_prepare_list.php");
    die();
}

// $po_prepare_date = date('d-m-Y');


if ($_REQUEST['po_prepare_id'] != "") {
    $dbconn = new dbhandler();
    $res = $conn->query("SELECT * FROM tbl_po_prepare WHERE po_prepare_id = " . $_REQUEST['po_prepare_id']);
    if ($res->rowCount() > 0) {
        $po_obj = $res->fetch(PDO::FETCH_OBJ);
    $si_date = $dbconn->GetSingleReconrd("tbl_store_indent", "si_date", "si_id", $po_obj->si_id);
        
        if ($si_date != "0000-00-00" && $si_date != "") {
            $si_date = date("d-m-Y", strtotime($si_date));
        }
        $si_id = $po_obj->si_id;
        $po_prepare_date = date('d-m-Y', strtotime($po_obj->po_prepare_date));
    }

    $res1 = $conn->query("SELECT * FROM tbl_store_indent WHERE si_id = " . $si_id);
    if ($res1->rowCount() > 0) {
        $pi_obj = $res1->fetch(PDO::FETCH_OBJ);
    }
    $si_type = $dbconn->GetSingleReconrd("tbl_store_indent", "si_type", "si_id", $si_id);
}

if ($_REQUEST['si_id'] != "") {
    $si_id = $_REQUEST['si_id'];
    $dbconn = new dbhandler();
    $res = $conn->query("SELECT * FROM tbl_store_indent WHERE si_id = " . $_REQUEST['si_id']);
    if ($res->rowCount() > 0) {
        $pi_obj = $res->fetch(PDO::FETCH_OBJ);

        if ($pi_obj->si_date != "0000-00-00" && $pi_obj->si_date != "") {
            $si_date = date("d-m-Y", strtotime($pi_obj->si_date));
        }

        // $si_date = $po_obj->si_date;
        // echo date('d-m-Y', strtotime($obj->po_prepare_date));
        // $si_dates = date('Y-m-d', strtotime($si_dates));

    }
    $isexists = $dbconn->GetSingleReconrd("tbl_po_prepare", "po_prepare_id", "si_id", $_REQUEST['si_id']);
    if ($isexists > 0) {
        header("location:po_prepare.php?po_prepare_id=" . $isexists);
        //die();
    }
    $si_type = $dbconn->GetSingleReconrd("tbl_store_indent", "si_type", "si_id", $_REQUEST['si_id']);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Prepare Purchase Order</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <?php include_once("inc/common/css-js.php"); ?>

    <!-- AUTO COMPLETE -->
    <script type='text/javascript' src='js/auto/jquery.autocomplete.js'></script>
    <link rel="stylesheet" type="text/css" href="js/auto/jquery.autocomplete.css" />

  

    <script type="text/javascript">
        var wasSubmitted = false;

        function fnValidate() {

            var a = 0;
            var b = 0;
            var c = 0;
            var d = 0;
            if (($("#si_type").val()) == 'S') {
                // alert();


                $(".supp_supp_id").each(function() {
                    // alert();

                    a++;
                    if ($(this).val() != '') {
                        b++;
                    }
                });
                $(".pi_new_qty").each(function() {

                    c++;
                    if ($(this).val() != '') {
                        d++;
                    }

                });

                // if(a != b){
                //     alert('Please Select the Supplier. ');
                //     return false;
                // }
                // if(c != d){
                //     alert('Please Check the Quantity, one or more Quantity values missing. ');
                //     return false;
                // }
                // $err_found=0;
                // $('.avl_qty').each(function() 
                // {
                // 	allbox = 0;
                // 	var id = $( this ).val();
                // 	$('.qty_'+id).each(function() 
                // 	{
                // 		allbox += parseFloat( $( this ).val() ) || 0;			 
                // 	});
                // 	var tot_item = $('.avl_'+id).val();

                // if (allbox != tot_item) {
                //     $err_found = 1;
                //     alert('Please Check the Quantity. It is not equal to Store Indent Quantity.');
                //     return false;
                // }
                // });



                if ($err_found == 0) {
                    if (!wasSubmitted) {
                        wasSubmitted = true;
                        document.thisForm.submit();
                        return true;
                    }
                }
                return false;

            } else {

            }
        }

        function getCount() {
            return $("select.supp_id option:selected[value!='']").length;
        }
        $(function() {

            <?php
            if ($_SESSION['_msg'] != "") {
                echo "$.jGrowl('" . $_SESSION['_msg'] . "', { sticky: false, theme: 'growl-success',shutdown:'0.5', header: 'Success!' });";
                $_SESSION['_msg'] = "";
            }

            if ($_SESSION['_msg_err'] != "") {
                echo "$.jGrowl('" . $_SESSION['_msg_err'] . "', { sticky: false, theme: 'growl-error', shutdown:'5.0', header: 'Error!' });";
                $_SESSION['_msg_err'] = "";
            }
            ?>
            $('.supp_id').change(function() {
                var item_id = $(this).closest('tr').find('.indent_id').val();
                var si_qty = $(this).closest('tr').find('.si_qty').val();
                var supp_id = $(this).val();
                var $this = $(this);
                //alert(item_id); 
                if (supp_id > 0) {
                    //alert(supp_id);
                    var arr = [];
                    $('.supp_supp_id').each(function() {
                        arr.push($(this).val());
                    });

                    if ($.inArray(supp_id, arr) != -1) {
                        alert('gjg');
                        return false;
                    }


                    $.ajax({
                        type: "POST",
                        url: "inc/cis_ajax/jquery_get_item_dets.php",
                        data: {
                            'supp_id': supp_id,
                            'item_id': item_id,
                            'si_qty': si_qty
                        }
                    }).done(function(msg) {
                        var response = msg.trim();
                        string = response.split("~");
                        console.log($this.closest('tr').find('.unit_price').val(string[0]));
                        $this.closest('tr').find('.unit_price').val(string[0]);
                        $this.closest('tr').find('.cls_unit_price').html(string[0]);

                        $this.closest('tr').find('.vat').val(string[1]);
                        $this.closest('tr').find('.vat_id').val(string[5]);
                        $this.closest('tr').find('.cls_vat').html(string[1]);

                        $this.closest('tr').find('.price').val(string[3]);
                        $this.closest('tr').find('.cls_price').html(string[3]);

                        $this.closest('tr').find('.net_amt').val(string[4]);
                        $this.closest('tr').find('.cls_net_amt').html(string[4]);
                    });
                }

            }).change();

            $(document).on('change', '.supp_supp_id', function() {
                if ($(this).val() > 0) {

                    var suppitm = ($(this).data('suppitm'));
                    var arr = [];
                    $('.itmid_' + suppitm).each(function() {
                        var value = $(this).val();

                        if (arr.indexOf(value) == -1) {
                            arr.push(value);
                        } else {
                            alert('This Supplier was already selected.');
                            $(this).val('').change();
                            
                            
                        }

                    });

                }

            });






        });
    </script>
    <script type="text/javascript">
        $(function() {



            $('.select-search').select2({
                placeholder: 'Select an Option',
                allowClear: true,
                
            });

            //alert ($("#pi_type").val());

            $('.add_supp').click(function() {

                /* appened supplier and piqty for each item  */
                var id = $(this).closest('tr').find('.indent_id').val(); //getting indent id
                var old_count = $('#' + id).children('tr').length; //getting table length of an item
                var si_id = $('#si_id').val();
                var count = old_count + 1;

                $.ajax({
                    type: "POST",
                    url: "inc/cis_ajax/jquery_multi_supp_details.php",
                    data: {

                        mode: 'getsupp',
                        id: id,
                        count: count,
                        si_id: si_id,

                    }
                }).done(function(msg) {
                    $('#' + id).append(msg);

                    $('.select-search').select2({
                        placeholder: 'Select an Option',
                        allowClear: true
                    });
                });

            });




            $(document).on('click', '.delete', function() {
                $(this).closest('tr').remove();
            });


            $(document).on('change', '.pi_new_qty', function() {

                var qtyid = ($(this).data('qtyid'));
                var tot_item = parseFloat($('.avl_' + qtyid).val());
                var siqty = parseFloat($(".si_qty").val());
                if ($(this).val() <= 0) {
                    alert('Please enter valid Quantity.');
                    $(this).val('');
                }
                var allbox = 0;
                $('.qty_' + qtyid).each(function() {

                    allbox += parseFloat($(this).val()) || 0;
                });

                var tot = tot_item - parseFloat(allbox);
                if (allbox > tot_item) {
                    alert('Please Check the Quantity. It is more than Store Indent Quantity.');
                    $(this).val('');
                }

            });

        });
    </script>

</head>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.js"></script>

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
                            <span class="breadcrumb-item active">Prepare Purchase Order</span>
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
                                <h6 class="card-title"> Prepare Purchase Order</h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="po_prepare_list.php" title="List of Prepare Purchae Order"><i class="icon-arrow-left52 mr-2"></i></a>
                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>
                            <form name='thisForm' id="validate" class="form-horizontal" method='post' action="po_prepare.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                                <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['si_id']; ?>">
                                <input type="hidden" name="si_id" id="si_id" value="<?php echo $si_id; ?>">
                                <input type="hidden" name="si_type" id="si_type" value="<?php echo $si_type; ?>">




                                <fieldset>
                                    <!-- <?php
                                            // if ($_REQUEST['si_id'] != "") {
                                            //     $si_no = leadingZeros($dbconn->GetSingleReconrd('tbl_store_indent', 'si_slno', 'si_id', $_REQUEST['si_id']), 4);
                                            // } else {
                                            //     $si_no = leadingZeros($dbconn->GetMaxValue('tbl_store_indent', 'si_slno', 'branch_id="'.$_SESSION['_user_branch'].'" AND 1 ', 1) + 1, 4);
                                            // }
                                            ?> -->


                                    <div class="card-body">
                                        <div class="form-group">
                                            <div class="row">


                                                <label class="col-lg-1 col-form-label">PO Date <span class="text-mandatory"> *</span></label>
                                                <div class="col-lg-3">
                                                    <input type="date" name="po_prepare_date" id="po_prepare_date" class="form-control" maxlength="75" min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $po_pre_date; ?>" placeholder="Date" />
                                                </div>

                                               
                                                <div class="col-md-4 text-right" style="line-height:2rem;">
                                                    Store Indent No. <a target="_blank" href="store_indent_print.php?si_id=<?php echo $si_id; ?>"><label ><?php echo $pi_obj->si_refno; ?></label></a>
                                                </div>

                                                <div class="col-md-4 text-right" style="line-height:2rem;">
                                                    Store Indent Date : <?php echo '<b>' . $si_date . '</b>'; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <legend class="font-weight-semibold"><i class="icon-cart mr-2"></i>Store Indent Details</legend>




                                        <div class="form-group row">
                                            <div id="show_table" class="col-md-12">
                                                <table class="table table-xs table-bordered" style="font-size: small !important;">
                                                    <thead>
                                                        <tr class="bg-teal">

                                                            <th width="1%">S.No</th>
                                                            <th width="40%">Item</th>
                                                            <th width="50%">Supplier</th>
                                                            <th width="6%">SI. Qty</th>
                                                            <th width="5%">Unit</th>
                                                            <th width="5%">Unit Price</th>
                                                            <th width="5%">Discount%</th>
                                                            <th width="5%" style="text-align:right">Amount</th>
                                                            <th width="5%">GST%</th>
                                                            <th width="5%" style="text-align:right">Net Amt.</th>
                                                            <!--<th width="8%">&nbsp;</th>-->
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $pisql = "SELECT * FROM tbl_store_indent
													LEFT JOIN tbl_store_indent_details ON tbl_store_indent.si_id = tbl_store_indent_details.si_id
													WHERE tbl_store_indent.si_id = '" . $si_id . "' AND tbl_store_indent_details.item_id IN (SELECT item_id FROM tbl_item_details WHERE item_type != 1 AND item_type != 7  ) ";

                                                        $result = $conn->query($pisql);

                                                        if ($result->rowCount() > 0) {

                                                            $iSno = 1;
                                                            $netTotal = 0;
                                                            while ($pod = $result->fetch()) {

                                                                $item_name = $dbconn->GetSingleReconrd("tbl_item_details", "CONCAT(item_desciption,' - ',item_code)", "item_status = '1' AND item_id", $pod->item_id);

                                                                $item_uom = $dbconn->GetSingleReconrd("tbl_item_details", "item_uom", "item_status = '1' AND item_id", $pod->item_id);
                                                                $item_uom = $dbconn->GetSingleReconrd("tbl_item_details", "item_uom", "item_status = '1' AND item_id", $pod->item_id);

                                                                $uom = $dbconn->GetSingleReconrd("mst_uom", "uom_code", "uom_status='1' AND uom_id", $item_uom);

                                                        ?>
                                                                <tr>
                                                                    <td>
                                                                        <?php echo $iSno; ?>
                                                                    </td>
                                                                    <td>
                                                                        <input type="hidden" class="item_id" name="item_id[]" value="<?php echo $pod->item_id; ?>" />
                                                                        <?php echo $item_name; ?>



                                                                    </td>
                                                                    <td>
                                                                        <input type="hidden" class="avl_qty" name="avl_qty[]" value="<?php echo $pod->item_id; ?>" />
                                                                        <?php
                                                                        if (isset($_REQUEST['po_prepare_id']) && $_REQUEST['po_prepare_id'] > 0) {
                                                                            $posql = "SELECT * FROM tbl_po_prepare_dets WHERE item_id = " . $pod->item_id . " AND po_prepare_id = " . $_REQUEST['po_prepare_id'] . " ";

                                                                            $res = $conn->query($posql);

                                                                            if ($res->rowCount() > 0) {
	
                                                                                $sno = 0;
                                                                                while ($poc = $res->fetch()) {
                                                                                    echo '<table class="table table-bordered table-xs"><tr>
																	
                                                                    <td width="100%" style="border-bottom-style: none;border-top-style: none;"><select data-placeholder="Select Supplier" name="supp_supp_id[]" class="select-search supp_supp_id itmid_' . $poc->item_id . '" data-suppitm = "' . $poc->item_id . '">
																			 ';

                                                                                    $supp_ids = $dbconn->GetSingleReconrd("tbl_item_details", "supp_id", "item_status = '1' AND item_id", $poc->item_id);

                                                                                    $sql3 = null;
                                                                                    $supp_ids = $dbconn->GetSingleReconrd("tbl_item_details", "supp_id", "item_status = '1' AND item_id", $poc->item_id);
                                                                                    $dbconn = new dbhandler();
                                                                                    if ($supp_ids != '') {
                                                                                        $sql3 = "SELECT supp_id,supp_name FROM mst_supplier_new  WHERE supp_id IN (" . $supp_ids . ") AND supp_status='1' AND  company_branch_id= '".$_SESSION['_user_branch']."'  AND supp_type = 'S' order by supp_name asc ";
                                                                                    } else {
                                                                                        $sql3 = "SELECT supp_id,supp_name FROM mst_supplier_new  WHERE supp_status='1' AND   company_branch_id= '".$_SESSION['_user_branch']."'  AND supp_type = 'S' order by supp_name asc ";
                                                                                    }
                                                                                    $res4 = $conn->query($sql3);
                                                                                    if ($res4->rowCount() > 0) {
                                                                                        $supp_id_update = 0;
                                                                                        $selected = "";
                                                                                        while ($ob2 = $res4->fetch(PDO::FETCH_OBJ)) {

                                                                                            $supp_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_id ='" . $ob2->supp_id . "'  AND  company_branch_id= '".$_SESSION['_user_branch']."' AND  1", 1);
                                                                                            if (isset($_REQUEST['po_prepare_id']) && $_REQUEST['po_prepare_id'] > 0) {
                                                                                                $supp_id_update = $poc->supp_id;
                                                                                                $selected = "";
                                                                                            }
                                                                                            if ($supp_id_update == $ob2->supp_id) {
                                                                                                $selected = " selected ";
                                                                                            }

                                                                                            echo '<option value= "' . $ob2->supp_id . '"    ' . $selected . ' >  ' . $supp_name . ' </option>';
                                                                                        }
                                                                                    }



                                                                                    echo '</select> </td>
																	
																	<td width="20%" style="border-bottom-style: none;border-top-style: none;">
																		<input type="hidden" class="new_item_id" name="new_item_id[]" value=" ' . $poc->item_id . ' " />
																		<input  size="5%" type="text" onkeypress="return event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)" class=" pi_new_qty qty_' . $poc->item_id . ' "  maxlength="8" name="pi_new_qty[]" data-qtyid =' . $poc->item_id . '  data-qtyval =' . $poc->si_qty . ' value="' . $poc->si_qty . '" />
																		<input  type="hidden" class="si_qty"  value=" ' . $poc->si_qty . '" />
																	</td>';
                                                                                    if ($sno == 0) {
                                                                                        echo '<td>
																		<a class="add_supp"><i class="fa fa-plus"  style="padding: 10px;"  ></i></a>
																		<input  type="hidden" class=" indent_id"  value="' . $pod->item_id . '" />
																		</td>';
                                                                                    } else {
                                                                                        echo '<td style="border-bottom-style: none;border-top-style: none;"><a class="delete" data-delid="' . $sno . '"  title="Remove"><i class="fa fa-times-circle" style="padding: 20px;"></i></a></td>';
                                                                                    }

                                                                                    echo '</tr>
																	</table>';



                                                                                    $sno++;
                                                                                }
                                                                            }
                                                                        ?>

                                                                        <?php } else { ?>

                                                                            <table class="table table-bordered table-xs" >
                                                                                <tr>
                                                                                    <td width="100%" style="border-bottom-style: none;border-top-style: none;">
                                                                                        <select data-placeholder="Select Supplier" name="supp_supp_id[]" class="select-search supp_supp_id  itmid_<?php echo $pod->item_id; ?> " data-suppitm="<?php echo $pod->item_id; ?>">
                                                                                            <option value="">--Select Supplier--</option>
                                                                                            <?php
                                                                                            $supp_ids = $dbconn->GetSingleReconrd("tbl_item_details", "supp_id", "item_status = '1' AND item_id", $pod->item_id);

                                                                                            $supp_ids = $dbconn->GetSingleReconrd("tbl_item_details", "supp_id", "item_status = '1' AND item_id", $pod->item_id);
                                                                                            $dbconn = new dbhandler();
                                                                                            if ($supp_ids != '') {
                                                                                                $SQL2 = "SELECT supp_id,supp_name FROM mst_supplier_new  WHERE supp_id IN (" . $supp_ids . ") AND supp_status='1' AND  company_branch_id= '".$_SESSION['_user_branch']."'  AND supp_type = 'S' order by supp_name asc ";
                                                                                            } else {
                                                                                                $SQL2 = "SELECT supp_id,supp_name FROM mst_supplier_new  WHERE supp_status='1' AND  company_branch_id= '".$_SESSION['_user_branch']."'  AND supp_type = 'S' order by supp_name asc ";
                                                                                            }
                                                                                            $res1 = $conn->query($SQL2);
                                                                                            if ($res1->rowCount() > 0) {
                                                                                                $supp_id_update = 0;
                                                                                                $selected = "";
                                                                                                while ($ob1 = $res1->fetch(PDO::FETCH_OBJ)) {
                                                                                                    //$selected = "";
                                                                                                    $supp_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_id = '" . $ob1->supp_id . "' AND  company_branch_id= '".$_SESSION['_user_branch']."' AND   1", 1);
                                                                                                    if (isset($_REQUEST['po_prepare_id']) && $_REQUEST['po_prepare_id'] > 0) {
                                                                                                        $supp_id_update = $dbconn->GetSingleReconrd("tbl_po_prepare as a,tbl_po_prepare_dets b", "b.supp_id", "a.po_prepare_id = b.po_prepare_id AND b.item_id = " . $pod->item_id . " AND a.po_prepare_id", $_REQUEST['po_prepare_id']);
                                                                                                        $selected = "";
                                                                                                    }
                                                                                                    if ($supp_id_update == $ob1->supp_id) {
                                                                                                        $selected = " selected ";
                                                                                                    }
                                                                                            ?>
                                                                                                    <option value="<?php echo $ob1->supp_id; ?>" <?php echo $selected; ?>><?php echo $supp_name; ?></option>
                                                                                            <?php         }
                                                                                            }

                                                                                            ?>
                                                                                        </select>
                                                                                    </td>

                                                                                    <td width="20%" style="border-bottom-style: none;border-top-style: none;">
                                                                                        <input type="hidden" class="  new_item_id" name="new_item_id[]" value="<?php echo $pod->item_id; ?>" />
                                                                                        <input size="5%" type="text" onkeypress="return event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)" class="  pi_new_qty qty_<?php echo $pod->item_id; ?>" maxlength="8" name="pi_new_qty[]" data-qtyid=<?php echo $pod->item_id; ?> data-qtyval=<?php echo $pod->si_qty; ?> value="" />
                                                                                        <input type="hidden" class="si_qty" value="<?php echo $pod->si_qty; ?>" />
                                                                                    </td>
                                                                                    <td style="border-bottom-style: none;border-top-style: none;">
                                                                                        <a class="add_supp"><i class="fa fa-plus" style="padding: 10px;"></i></a>
                                                                                        <input type="hidden" class="indent_id" value="<?php echo $pod->item_id; ?>" />
                                                                                    </td>
                                                                                </tr>
                                                                              
                                                                            </table>

                                                                        <?php } ?>

                                                                        <?php

                                                                        $si_qty = $pod->si_qty;

                                                                        ?>
                                                                        <table class="new_add table-bordered table-xs" id="<?php echo $pod->item_id; ?>">
                                                                        </table>
                                                                       
                                                                        <?php
																        $item_uom = $dbconn->GetSingleReconrd("tbl_item_details", "item_uom", "item_id", $pod->item_id);

                                                                        $multi_uom_id = $dbconn->GetSingleReconrd("tbl_item_details", "multi_uom_id", "item_id", $pod->item_id);
                                                                        //echo $multi_uom_id;
																		$unit_price=0;
                                                                        $new_item_uom = explode(",",$multi_uom_id);
                                                                        if($pod->si_unit != $item_uom){
                                                                            for($x = 0; $x < count($new_item_uom); $x++ )
                                                                            {
                                                                                $uom_name = $dbconn->GetSingleReconrd("mst_uom", "uom_name", "uom_id", $pod->si_unit);
                                                                                $uom_name = $dbconn->GetSingleReconrd("mst_uom", "uom_name", "uom_id", $pod->si_unit);

                                                                                if ($pod->item_id != "")
                                                                                {
                                                                                    $result1 = $conn->query("SELECT * FROM tbl_multiuom_itemprice_history WHERE item_id = '".$pod->item_id."' AND new_item_uom = '".$uom_name."' AND branch_id = '".$pod->branch_id."' ");	
                                                                                    if ($result1->rowCount()>0)
                                                                                    {
                                                                                        $row = $result1->fetch(PDO::FETCH_OBJ);	

                                                                                        $unit_price = $row->new_cost_price;
                                                                                        $uom = $dbconn->GetSingleReconrd("mst_uom","uom_code","uom_status='1' AND uom_id",$pod->si_unit);
                                                                                        $item_hsn = $dbconn->GetSingleReconrd("tbl_item_details","item_hsn","item_id",$row->item_id);
                                                                                        $gst  = $dbconn->GetSingleReconrd("mst_hsn","igst","hsn_status = '1' AND hsn_id",$item_hsn);
                                                                                        
                                                                                        $po_unit_price = $unit_price;
                                                                                        $po_vat = $gst;
                                                                                        $vat_id = $item_hsn;
                                                                                        $vat= $gst;
                                                                                        $qty = $si_qty;
                                                                                        $price = number_format(((float)$po_unit_price * (float)$qty),2,".","");
                                                                                        $tax_val = (((float)$price * (float)$po_vat) / 100);
                                                                                        $net_val = $price + $tax_val;
                                                                                        $no_gst = number_format($price,2,".","");
                                                                                        $with_gst = number_format($net_val,2,".","");
                                                                                        $net_amt = $with_gst;
                                                                                        $item_discount=$row->new_discount;

                                                                                        
                                                                                                                                                            
                                                                                    }
                                                                                }
                                                                            }
                                                                        }else
                                                                        {
																			
                                                                            $SQL1 = null;
                                                                            $SQL1 = "SELECT * FROM tbl_item_details  WHERE item_id=" . $pod->item_id . " ";
                                                                            $result1 = $conn->query($SQL1);
                                                                            if ($result1->rowCount() > 0) {

                                                                                $obj1 = $result1->fetch(PDO::FETCH_OBJ);

                                                                                $gst  = $dbconn->GetSingleReconrd("mst_hsn", "igst", "hsn_status = '1' AND hsn_id", $obj1->item_hsn);
                                                                                // $quo_id = $dbconn->GetLastRecord("tbl_po_quotation a,tbl_po_quotation_details b ","a.quo_id","a.quo_id = b.quo_id  AND b.item_id",$pod->item_id,"a.quo_id DESC");
                                                                                $branch_item_cost_price = $dbconn->GetSingleReconrd("mst_branch","branch_item_cost_price","branch_id",$_SESSION['_user_branch']);
                                                                                
                                                                                $item_cost_price =  $dbconn->GetSingleReconrd("tbl_item_stock","$branch_item_cost_price","item_id",$obj1->item_id);
                                                                                $branch_item_discount = $dbconn->GetSingleReconrd("mst_branch","branch_item_discount","branch_id",$_SESSION['_user_branch']);
                                                                                $item_discount =  $dbconn->GetSingleReconrd("tbl_item_stock","$branch_item_discount","item_id",$obj1->item_id);


                                                                                // $quo_date = '';
                                                                                /* modified by 	NAGARAJ */
                                                                                // if ($quo_id > 0) {
                                                                                //     $quo_date = $dbconn->GetSingleReconrd("tbl_po_quotation", "quo_date", "quo_id", $quo_id);
                                                                                //     $unit_price = $dbconn->GetSingleReconrd("tbl_po_quotation_details", "selling_price", "item_id = " . $pod->item_id . " AND quo_id", $quo_id);
                                                                                // } else {
                                                                                //     $unit_price = $item_cost_price;
                                                                                //     $itm_dis = $item_discount;
                                                                                // }
                                                                                $unit_price = $item_cost_price;
                                                                                $uom = $dbconn->GetSingleReconrd("mst_uom", "uom_code", "uom_status='1' AND uom_id", $obj1->item_uom);

                                                                                $po_unit_price = $unit_price;
                                                                                $po_vat = $gst;
                                                                                $vat_id = $obj1->item_hsn;
                                                                                $vat = $gst;
                                                                                $qty = $si_qty;
                                                                                $price = number_format(((float)$po_unit_price * (float)$qty), 2, ".", "");
                                                                                $tax_val = (((float)$price * (float)$po_vat) / 100);
                                                                                $net_val = $price + $tax_val;
                                                                                $no_gst = number_format($price, 2, ".", "");
                                                                                $with_gst = number_format($net_val, 2, ".", "");
                                                                                $net_amt = $with_gst;
                                                                                $item_discount=$item_discount;
                                                                            }
                                                                        }
                                                                        ?>


                                                                    </td>

                                                                    <td style="text-align:center;"><?php echo $si_qty; ?>
                                                                        <input type="hidden" name="curr_qty[]" class="curr_qty avl_<?php echo $pod->item_id; ?>" id="<?php echo $pod->item_id; ?>" value="<?php echo $si_qty; ?>" />

                                                                    </td>
                                                                    <td><?php echo $uom; ?><input type="hidden" class="uom" name="uom[]" value="<?php echo $pod->si_unit; ?>" />
                                                                        <input type="hidden" class="uom_code" name="uom_code[]" value="<?php echo $uom; ?>" />
                                                                    </td>
                                                                    <td style="text-align:right;"><span class="cls_unit_price"><?php echo $unit_price; ?></span>
                                                                        <input type="hidden" class="unit_price" name="unit_price[]" value="<?php echo $unit_price; ?>" />
                                                                    </td>
                                                                    <td style="text-align:right;"><span class="cls_item_discount"><?php echo $item_discount; ?></span>
                                                                        <input type="hidden" class="item_discount" name="item_discount[]" value="<?php echo $item_discount; ?>" />
                                                                    </td>

                                                                    <td style="text-align:right;"><span class="cls_price"><?php echo $price; ?></span><input type="hidden" class="price" name="price[]" value="<?php echo $price; ?>" /></td>

                                                                    <td><span class="cls_vat"><?php echo $vat; ?></span>
                                                                        <input type="hidden" class="vat" name="vat[]" value="<?php echo $vat; ?>" />
                                                                        <input type="hidden" class="vat_id" name="vat_id[]" value="<?php echo $vat_id; ?>" />
                                                                    </td>

                                                                    <td style="text-align:right;"><span class="cls_net_amt"><?php echo $net_amt; ?></span><input type="hidden" class="net_amt" name="net_amt[]" value="<?php echo $net_amt; ?>" />

                                                                    </td>

                                                                </tr>



                                                        <?php
                                                                $iSno++;
                                                            }
                                                        }
                                                        ?>
                                                        <input type="hidden" name="tot_items" id="tot_items" value="<?php echo $iSno - 1; ?>" />

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <!-- <script type="text/javascript">
                                                    remove_item(0);
                                                </script> -->

                                        <div class="form-group pl-0 col-md-12 pt-4">
                                            <label>Remarks (if any) : <span class="text-mandatory"></span></label>
                                            <textarea name="si_remarks" maxlength="500" id="si_remarks" class="form-control"><?php echo $get->si_remarks; ?></textarea>
                                        </div>

                                    </div>
                                    <div class="card-footer text-center">

                                        <?php if ($_REQUEST["po_prepare_id"] != '') {
                                            if ($po_obj->send_admin_status == 0 || $po_obj->send_admin_status < 2) {
                                        ?>
                                                <INPUT class="btn btn-info" type="submit" name="UPDATE" value="Draft">
                                                <INPUT class="btn btn-success" type="submit" name="send_to_admin" value="Send to Admin">
                                            <?php } else { ?>
                                                <span style="color:red !important;"> In Admin Approvel &nbsp;</span>
                                            <?php } ?>
                                            <INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
                                            <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['po_prepare_id']; ?>">
                                        <?php } else { ?>
                                            <INPUT class="btn btn-info" type="submit" name="SAVE" value="Save">
                                            <INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:history.go(-1);">
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