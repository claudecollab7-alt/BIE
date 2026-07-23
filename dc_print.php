<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");

isAdmin();
$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

if ($_REQUEST['dc_id'] != "") {
    $result = $conn->query("SELECT * FROM tbl_dc  WHERE dc_id = " . $_REQUEST['dc_id']);
    if ($result->rowCount() > 0) {

        $obj = $result->fetch(PDO::FETCH_OBJ);

        $slno = $obj->dc_slno;

        $dc  = $obj->dc_refno;
        $supp_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_status = 1 AND supp_id ", $obj->supp_id);
        $so_no = $dbconn->GetSingleReconrd("tbl_sales_order", "so_refno", "so_status = 5 AND so_id ", $obj->so_id);
    }
    $company_add = $conn->query("SELECT * FROM  mst_branch WHERE branch_id = " . $obj->branch_id);
    if ($company_add->rowCount() > 0) {
        $res = $company_add->fetch(PDO::FETCH_OBJ);
        // print_r($obj1);
        $address = $res->company_address;
        $address .= '<br><b>PH : </b> +91 ' .$res->company_ph_no1 .' / '.$res->company_ph_no2;
        $address .= '<br><b>Toll Free : </b> '.$res->company_toll_free;
        $address .= '<br><b>E-Mail : </b>'.$res->company_mail;
        $address .= '<br><b>Web : </b>'.$res->company_web;

        $gst_no = $res->company_gst;
        $pan_no = $res->company_pan;
        $company_state_code = $res->branch_state_code;

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

    $state_code = $dbconn->GetSingleReconrd("mst_state", "state_code", "state_status = 1 AND state_id ", $obj1->state_id);
    $phone_no = $obj1->supp_mobile1;

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

        $state_code = $dbconn->GetSingleReconrd("mst_state", "state_code", "state_status = 1 AND state_id ", $obj1->state_id);
        $phone_no = $obj1->branch_contact_no;

    }
}else{
    $add2 = $add;
    $supp_name2 = $supp_name;
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Delivery Challan</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <?php include_once("inc/common/css-js.php"); ?>
    <style>
        table>thead>tr>td {
            border: 1px solid black !important;
        }

        table>tbody {
            border: 1px solid black !important;
        }
    </style>


    <script language="javascript">
        $(function() {
            $("body").on("click", "#cmd", function() {

                var element = document.getElementById('print_content1');
                var opt = {
                    margin: 0.5,
                    filename: '<?php echo $obj->dc_refno; ?>' + '.pdf',
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
                html2pdf().set(opt).from(element).save();

            });
        });
    </script>


</head>

<script type="text/javascript" src="print_me.js"></script>
<script src="js/html2pdf.bundle.min.js"></script>

<body>
    <?php include("inc/common/header.php") ?>
    <div class="page-content">
        <?php include("inc/common/sidebar.php") ?>
        <div class="content-wrapper">
            <div class="page-header">
                <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                    <div class="d-flex">
                        <div class="breadcrumb">
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
                            <a href="#" class="breadcrumb-item">Work Area</a>
                            <span class="breadcrumb-item active">Delivery Challan</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>
            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-pgheader text-white header-elements-inline">
                                <h6 class="card-title">Delivery Challan Details - <?php echo  $dc; ?></h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" href="dc_list.php" title="DC List"><i class="icon-arrow-left52 mr-2"></i></a>

                                        <?php
                                        if ($obj->dc_verify_status == 1 && $obj->dc_approve_status == 1) {
                                        ?>
                                            <a class="list-icons-item" href="javascript:PrintPartsNew(new Array('print_content1'),'<?php echo $obj->dc_refno; ?>');" id="print_page" title="Print DC"><i class="icon-printer2 mr-1"></i></a>
                                            <a class="list-icons-item" id="cmd" href="javascript:;" title="PDF"><i class="icon-file-pdf  mr-2"></i></a>
                                        <?php
                                        }
                                        ?>

                                        <a class="list-icons-item" data-action="fullscreen" title="Full Screen"></a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="invoice" id="print_content1" style="width:100%;">
                                        <table class="table table-xs table-bordered bold po_print_table">
                                            <thead>
                                                <tr>
                                                    <td class="text-center" colspan="14" style="font-size: 20px; font-weight: bold; ">&nbsp;&nbsp;DELIVERY CHALLAN</td>
                                                </tr>
                                                <tr>
                                                    <!-- <td colspan="14"></td> -->
                                                </tr>
                                                <tr>
                                                    <td colspan="2" rowspan="6" align="center"><img src="img/BIE_logo.png" alt="" width="100px" height="auto"></td>
                                                <tr>
                                                    <td colspan="4" style="background-color: #bebebe;"><b>DC NO. :</b></td>
                                                    <td colspan="4"><?php echo $obj->dc_refno; ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" style="background-color: #bebebe;"><b>DC DATE. :</b></td>
                                                    <td colspan="4"><?php echo date('d-m-Y', strtotime($obj->dc_date)); ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" style="background-color: #bebebe;"><b>YOUR ORDER NO :</b></td>
                                                    <td colspan="4"><?php echo leadingZeros($obj->dc_slno, 3); ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" style="background-color: #bebebe;"><b>ORDER DATE :</b></td>
                                                    <td colspan="4"><?php echo date('d-m-Y', strtotime($obj->dc_date)); ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" style="background-color: #bebebe;"><b>SO NO. :</b></td>
                                                    <td colspan="4"><?php echo $so_no; ?></td>
                                                </tr>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" align="left" style="vertical-align: top;">
                                                    <?php echo $address; ?>
                                                    </td>
                                                    <td colspan="6" align="left" style="vertical-align: top;"><b>Details of Receiver (Billed to)</b><br /><?php echo '<b>' . $obj1->supp_name . '</b><br/>' . $add; ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" rowspan="2" align="left"><b>GSTIN NO :</b> <?php echo $gst_no; ?> </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6" align="left"><b>PH :</b> <?php echo $phone_no; ?> </td>
                                                </tr>

                                                <tr>
                                                    <td colspan="4" rowspan="2" align="left"><b>PAN NO :</b> <?php echo $pan_no; ?></td>
                                                </tr>

                                                <tr>
                                                    <td colspan="6" align="left"><b>GSTIN NO :</b> <?php echo $obj1->supp_gst; ?></td>
                                                </tr>

                                                <tr>
                                                    <td colspan="4" rowspan="2" align="left"><b>STATE CODE :</b> <?php echo $company_state_code; ?> </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6" align="left"><b>PAN NO :</b> <?php echo $obj1->supp_pan; ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" rowspan="2"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6" align="left"><b>STATE CODE :</b> <?php echo $state_code; ?></td>
                                                </tr>

                                                <tr style="font-weight:bold; background-color: #bebebe;">
                                                    <td>#</td>
                                                    <td align="center" width="300px">DESCRIPTION OF GOODS</td>
                                                    <td align="center">MODEL</td>
                                                    <td align="center">HSN Code</td>
                                                    <td align="center">QTY</td>
                                                    <td align="center">UNITS</td>
                                                    <td align="center">BOX TYPE / NO.</td>
                                                    <td align="center">REMARKS</td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php

                                                $dcsql = "SELECT * FROM tbl_dc as a LEFT JOIN tbl_dc_details as b ON a.dc_id = b.dc_id WHERE b.dc_dispatch_qty !='0' AND a.dc_id = '" . $_REQUEST['dc_id'] . "'";

                                                $result = $conn->query($dcsql);

                                                if ($result->rowCount() > 0) {
                                                    $iSno = 1;
                                                    $netTotal = 0;
                                                    while ($dc = $result->fetch()) {

                                                        if ($dc->box_id == 1) {
                                                            $box_type = 'Corrugated Box';
                                                        } elseif ($dc->box_id == 2) {
                                                            $box_type = 'Wooden Box';
                                                        } elseif ($dc->box_id == 3) {
                                                            $box_type = 'Gunny Bags';
                                                        } elseif ($dc->box_id == 4) {
                                                            $box_type = 'Poly Bags';
                                                        } else {
                                                            $box_type = '';
                                                        }




                                                        $item_code = $dbconn->GetSingleReconrd("tbl_item_details", "item_code", "item_status='1' AND item_id", $dc->dc_item_id);

                                                        $item_name = $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_status='1' AND item_id", $dc->dc_item_id);


                                                        $gst_id = $dbconn->GetSingleReconrd("tbl_item_details", "item_hsn", "item_status='1' AND item_id", $dc->dc_item_id);

                                                        $item_uom_id = $dbconn->GetSingleReconrd("tbl_item_details", "item_uom", "item_id", $dc->dc_item_id);

                                                        $item_uom = $dbconn->GetSingleReconrd("mst_uom", "uom_code", "uom_id", $item_uom_id);


                                                        $hsn_code = $dbconn->GetSingleReconrd("mst_hsn", "hsn_code", "hsn_status='1' AND hsn_id", $gst_id);

                                                        echo '<tr ' . $tr_class . ' valign="top" >
                                                                <td align="center" style=" border: 1px solid black !important;">' . $iSno . '</td>
                                                                <td align="left" style=" border: 1px solid black !important;">' . $item_name . '</b></td>
                                                                <td align="center" style=" border: 1px solid black !important;">' . $item_code . '</td>
                                                                <td align="center" style=" border: 1px solid black !important;">' . $hsn_code . '</td>
                                                                <td align="center"style=" border: 1px solid black !important;">' . $dc->dc_dispatch_qty . '</td>
                                                                <td align="center"style=" border: 1px solid black !important;">' . $item_uom . '</td>
                                                                <td align="center"style=" border: 1px solid black !important;">' . $box_type . ' - <b>' . $dc->no_of_box . '</b></td>
                                                                <td align="left"style=" border: 1px solid black !important;">' . $dc->dc_remarks . '</td>
                                                            </tr>';

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

                                                echo '<tr ' . $tr_class . ' valign="top">
													<td style=" border: 1px solid black !important;"><p style="min-height:' . $height . 'px;">&nbsp;</p></td>
													<td style=" border: 1px solid black !important;">&nbsp;</td>
													<td style=" border: 1px solid black !important;">&nbsp;</td>
													<td style=" border: 1px solid black !important;">&nbsp;</td>
													<td style=" border: 1px solid black !important;">&nbsp;</td>
													<td style=" border: 1px solid black !important;">&nbsp;</td>
													<td style=" border: 1px solid black !important;">&nbsp;</td>
													<td style=" border: 1px solid black !important;">&nbsp;</td>
												</tr>';

                                                echo '<tr>
												<td colspan = "8" align="left" style="background-color: #bebebe; border: 1px solid black !important;">  <p><b> PACKING DETAILS </b></p>  </td>
											</tr>';
                                                if ($obj->corrugated_box > 0)
                                                    echo '<tr>
													<td colspan = "8" align="left" style=" border: 1px solid black !important;"><b>CORRUGATED BOX : ' . $obj->corrugated_box . '</b></td>
												</tr>';
                                                if ($obj->wooden_box > 0)
                                                    echo '<tr>
													<td colspan = "8" align="left" style=" border: 1px solid black !important;"><b>WOODEN BOX : ' . $obj->wooden_box . '</b></td>
												</tr>';
                                                if ($obj->gunny_bags > 0)
                                                    echo '<tr>
													<td colspan = "8" align="left" style=" border: 1px solid black !important;"><b>GUNNY BOX : ' . $obj->gunny_bags . '</b></td>
												</tr>';
                                                if ($obj->poly_bags > 0)
                                                    echo '<tr>
													<td colspan = "8" align="left" style=" border: 1px solid black !important;"><b>POLY BAGS : ' . $obj->poly_bags . '</b></td>
												</tr>';
                                                echo '<tr>
													<td colspan = "8" align="center"style=" border-right: 1px solid black !important; border-left: 1px solid black !important; " ><small>Please sign and return one copy of the challan within 3 days on receipt of consignment, failing which will be understood that the goods have been correctly received and accepted</small></td>
												</tr>';
                                                ?>
                                            </tbody>
                                        </table>

                                        <tfoot>
                                            <tr>
                                                <td>
                                                    <table class="table table-xs table-bordered bold po_print_table" width="100%"  style="border: none !important;table-layout: fixed;" cellspacing="0" cellpadding="0" border="1">
                                                        <tbody>
                                                            <tr>
                                                                <td width="35%" align="center">
                                                                    <p><b>Received in good condition</b></p>
                                                                    <p>&nbsp;</p>
                                                                    <p>&nbsp;</p>
                                                                    <p>&nbsp;</p>
                                                                    <p>&nbsp;</p>


                                                                    <p><?php echo $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id", $obj->last_modify_by); ?>&nbsp;</p>
                                                                </td>
                                                                <td width="15%" align="center">
                                                                    <p><b>Prepared By</b></p>
                                                                    <p>&nbsp;</p>
                                                                    <p>&nbsp;</p>



                                                                    <p><small><?php echo $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id", $obj->modify_by) . ' On ' . date("d-m-Y", strtotime($obj->modify_date_time));  ?></small></p>
                                                                    <p>&nbsp;</p>
                                                                    <p>&nbsp;</p>
                                                                </td>
                                                                <td width="15%" align="center"><br>
                                                                    <p><b>Despatch By</b></p>
                                                                    <p>&nbsp;</p>
                                                                    <p>&nbsp;</p>
                                                                    <p>&nbsp;</p>
                                                                    <p>&nbsp;</p>
                                                                    <p>&nbsp;</p>
                                                                    <p>&nbsp;</p>


                                                                </td>
                                                                <td align="center"><br>
                                                                    <p><b>FOR BENZEAR INDUSTRIAL ENTERPRISES</b></p>
                                                                    <p>&nbsp;</p>
                                                                    <p>&nbsp;</p>
                                                                    <p>&nbsp;</p>
                                                                    <p>&nbsp;</p><br>
                                                                    <p>Authorized Signatory</p>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </div>
                                </div>
                            </div>
                            <?php if ($obj->dc_verify_status == 1 && $obj->dc_approve_status == 0) {

                                //$approve_user_dat = $dbconn->GetSingleReconrd("tbl_task_user", "user_id", "task_id", 3);

                                if ($_SESSION['_user_id'] == 1 || ($obj->branch_id==$_SESSION['_user_branch'])) {
                            ?>

                                    <br>
                                   
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Remarks </label>
                                            <textarea name="dc_remarks" id="dc_remarks" class="form-control" rows="2" maxlength="250"></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer text-center">
                                        <input type="hidden" name="dcs_id" id="dcs_id" value="<?php echo $_REQUEST['dc_id']; ?>" />
                                        <INPUT class="btn btn-custom" type="button" id="APPROVE" name="APPROVE" value="Approve">
                                        <INPUT class="btn btn-danger" type="button" id="REJECT" name="REJECT" value="Reject">
                                    </div>
                            <?php
                                }
                            } ?>
                            
                            
                        </div>
                        <div class="row">
                            <div class="text-left col-lg-6">
                                <div class="rec_create_dets"><b>Created by : </b><?php echo $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id ", $obj->modify_by) . ' on ' . date('d-M-y @ H:i', strtotime($obj->modify_date_time)); ?></b><br><b>Remarks : </b> <?php echo $obj->dc_remarks; ?>
                                </div>
                            </div>

                            <div class="text-right col-lg-6">
                                <?php
                                if ($obj->dc_approve_status > 0) {
                                ?>
                                    <div class="rec_create_dets"><b>Approved by : </b><?php echo $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id ", $obj->dc_id) . ' on ' . date('d-M-y @ H:i', strtotime($obj->dc_approve_date_time)); ?></b>
                                    </div>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include("inc/common/footer.php") ?>
        </div>
    </div>
</body>

</html>

<script>
    $('#APPROVE').click(function() {
        var dc_id = $('#dcs_id').val();
        var remarks = $('#dc_remarks').val();
        var task = "DC_APP";

        $.ajax({
            type: 'post',
            url: 'inc/cis_ajax/jquery_dc_approval.php',
            data: {
                "dc_id": dc_id,
                "task": task,
                "remarks": remarks
            },
            beforeSend: function() {

                if (confirm('Are you sure to Approve this Delivery Challan..?')) {} else {
                    return false;
                }
            },
            complete: function() {

            },
            success: function(result) {

                window.location.href = "javascript:history.go(-1)";
            }
        });
        return false;
    });



    $('#REJECT').click(function() {

        if ($('#dc_remarks').val() == '') {
            alert("Please enter the  Rejection Remarks..!");
            $('#dc_remarks').focus();
            return false;
        }

        var dc_id = $('#dcs_id').val();
        var remarks = $('#dc_remarks').val();
        var task = "DC_REJ";
        var dc_slno = $('#dcs_id').val();

        $.ajax({
            type: 'post',
            url: 'inc/cis_ajax/jquery_dc_approval.php',
            data: {
                "dc_id": dc_id,
                "task": task,
                "remarks": remarks,
                "dc_slno": dc_slno
            },
            beforeSend: function() {
                if (confirm('Are you sure to Reject this Delivery Challan..?')) {} else {
                    return false;
                }
            },
            complete: function() {},
            success: function(result) {

                window.location.href = "javascript:history.go(-1)";
            }
        });
        return false;
    });
</script>