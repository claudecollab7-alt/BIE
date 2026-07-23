<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
// require_once ("inc/common/dbrowiterator.php");	

isAdmin();



$conn = new dbconnect();
$dbconn = new dbhandler();
// ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);

$grn_date = date("Y-m-d");

if (isset($_REQUEST['rtn_id']) && $_REQUEST['rtn_id'] != "") {

	$result = $conn->query("SELECT * FROM tbl_purchase_return WHERE rtn_id = " . $_REQUEST['rtn_id']);
	if ($result->rowCount() > 0) {
		$get = $result->fetch(PDO::FETCH_OBJ);
		$supp_id = $get->supp_id;
		if ($get->grn_date != "0000-00-00" && $get->grn_date != "") {
			$grn_date = date("Y-m-d", strtotime($get->grn_date));
		}
		$common_remarks = $get->common_remarks;
	}

    
	$result1 = $conn->query("SELECT * FROM tbl_grn WHERE grn_id = '".$get->grn_id."' ");
	if ($result1->rowCount() > 0) {
		$row = $result1->fetch(PDO::FETCH_OBJ);
		
		if ($row->po_date != "0000-00-00" && $row->po_date != "") {
			$po_date = date("Y-m-d", strtotime($row->po_date));
		}
        // $po_no = $row->po_refno;
		$po_no = $dbconn->GetSingleReconrd("tbl_purchase_order","po_refno","po_id",$row->po_id);
		$po_date = $dbconn->GetSingleReconrd("tbl_purchase_order","po_date","po_id",$row->po_id);


		// $common_remarks = $get->common_remarks;
	}

    
    $resSupp = $conn->query("SELECT * FROM mst_supplier_new WHERE supp_id = '" . $get->supp_id . "'");
    if ($resSupp->rowCount() > 0) {
        $obj1 = $resSupp->fetch(PDO::FETCH_OBJ);

        $add = "";
		$add .= $obj1->supp_add1;
		if ($obj1->supp_add2 != "") {
			$add .= ', ' . $obj1->supp_add2;
		}
		$add .= ', <br/>' . $dbconn->GetSingleReconrd("mst_city", "city_name", "city_status = 1 AND city_id ", $obj1->city_id);

		$add .= ', ' . $dbconn->GetSingleReconrd("mst_district", "district_name", "district_status = 1 AND district_id ", $obj1->district_id);

		$add .= ', <br/>' . $dbconn->GetSingleReconrd("mst_state", "state_name", "state_status = 1 AND state_id ", $obj1->state_id);

		$add .= ' - ' . $obj1->supp_pincode . '.';
		$add .= ' <br/> <b>mobile : </b> ' . $obj1->supp_mobile1 ;
		$add .= ' <br/> <b>mail : </b>' . $obj1->supp_email;

		$state_code = $dbconn->GetSingleReconrd("mst_state", "state_code", "state_status = 1 AND state_id ", $obj1->state_id);
    }

   
}
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

<script type="text/javascript" src="print_me.js"></script>

<script src="js/html2pdf.bundle.min.js"></script>

<script language="javascript">
    $(function() {
        $("body").on("click", "#cmd", function() {

            var element = document.getElementById('print_content1');
            //html2pdf(element);
            var opt = {
                margin: 0.5,
                filename: '<?php echo $get->grn_refno; ?>' + '.pdf',
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
                            <span class="breadcrumb-item active">Stores</span>
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
                                <h6 class="card-title">GRN </h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" href="purchase_return_list.php" title="Purchase Return List"><i class="icon-arrow-left52 mr-2"></i></a>
                                        <?php
                                        if ($get->rtn_approve_status == 1 && $get->rtn_refno !='') {
                                        ?>
                                            <a class="list-icons-item" href="javascript:PrintPartsNew(new Array('print_content1'),'<?php echo $get->grn_refno; ?>');" id="print_page" title="Print GRN"><i class="icon-printer2 mr-1"></i></a>
                                            <a class="list-icons-item" id="cmd" href="javascript:;" title="PDF"><i class="icon-file-pdf  mr-2"></i></a>
                                        <?php
                                        }
                                        ?>
                                        <a class="list-icons-item" data-action="fullscreen"></a>

                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12 invoice" id="print_content1">
                                        <!-- <table class="table po_print_table" width="100%" border=0> -->
                                            <tbody>
                                                <table class="table po_print_table" width="100%">
                                                    <tbody>
                                                        <tr>
                                                            <td align="center" width="40%"><img src="img/BIE_logo.png" style="width: 60px;" alt=""></td>
                                                            <td align="center" style="font-size: 28px;"><p><b>PURCHASE RETURN NOTE</b></p></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                    
                                                <tr>
                                                    <td>
                                                        <table class="table po_print_table" cellpadding="7px" width="100%">
                                                            <tbody>
                                                                <tr>
                                                                    <td width="40%" rowspan = "4" class="text-uppercase"><b>supplier name :   </b><b><?php echo $obj1->supp_name; ?></b><br><br><b>ADDRESS : </b><?php echo $add;  ?></td>
                                                                    <td><b>Purchase Return No.</b></td>
                                                                    <td><?php echo $get->rtn_ref_code; ?></td>
                                                                    <td><b>Purchase Return Date</b></td>
                                                                    <td><?php echo date('d-m-Y', strtotime($get->rtn_date)); ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><b>Purchase Order No. </b></td>
                                                                    <td><?php echo $po_no; ?></td>
                                                                    <td><b>Purchase Order Date</b></td>
                                                                    <td><?php echo date('d-m-Y', strtotime($po_date)); ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><b>Party Bill No. </b></td>
                                                                    <td><?php echo $get->rtn_refno; ?></td>
                                                                    <td><b>Party DC No. </b></td>
                                                                    <td><?php echo $get->party_dc_no; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><b>Party Bill Date </b></td>
                                                                    <td><?php echo date('d-m-Y', strtotime($row->party_bill_date)); ?></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                              
                                                
                                                     <!-- </td> -->
                                                    
                                                    
                                                       
                                                        <p class="text-center">
                                                        <table class="table table-xs table-bordered po_print_table">
                                                            <thead>
                                                                <tr class="text-uppercase font-weight-semibold" style="font-weight:bold;";>
                                                                    <td style="text-align:center;">#</td>
                                                                    <td>Product Name</td>
                                                                    <td style="text-align:center;">Unit</td>
                                                                    <td style="text-align:center;">PO Qty</td>
                                                                    <td style="text-align:right;">Rejected</td>
                                                                   
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                $sql = "SELECT DISTINCT * FROM tbl_purchase_return_details a
																		WHERE rtn_id = " . $_REQUEST['rtn_id'] . " AND rtn_rejected_qty > '0' ORDER BY a.rtn_details_id";

                                                                //echo $sql;
                                                                $result = $conn->query($sql);
                                                                $num_rows = $result->rowCount();
                                                                if ($num_rows > 0) {
                                                                    $tot_unit = $sub_tot = 0;
                                                                    $tot_qty = $tax_tot = 0;
                                                                    $iSno = 1;
                                                                    $colspan = "4";
                                                                    while ($objT = $result->fetch(PDO::FETCH_OBJ)) {
                                                                        $item_name = $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_status = '1' AND item_id", $objT->rtn_item_id);
                                                                        $rtn_verify_status = $dbconn->GetSingleReconrd("tbl_purchase_return", "rtn_verify_status", "rtn_id", $objT->rtn_id);
                                                                        $rtn_approve_status = $dbconn->GetSingleReconrd("tbl_purchase_return", "rtn_approve_status", "rtn_id", $objT->rtn_id);
                                                                        // $item_id = $dbconn->GetSingleReconrd("tbl_grn_details", "grn_item_id", "grn_id", $objT->grn_id);
                                                                        // $po_qty = $dbconn->GetSingleReconrd("tbl_purchase_order_details", "sum(po_qty)", "item_id = $item_id AND po_id", $po_id);
                                                                        // $po_qty = $dbconn->GetSingleReconrd("tbl_purchase_order_details", "po_qty", "item_id", $item_id);

                                                                ?>

                                                                        <tr>
                                                                            <td align="center" class=" text-center"><?php echo $iSno; ?></td>
                                                                            <td class=" text-left"><?php echo $item_name; ?></td>
                                                                            <td class=" text-center"><?php echo $objT->rtn_unit; ?></td>
                                                                            <td class=" text-center"><?php echo $objT->po_qty; ?></td>
                                                                            <td class=" text-right"><?php echo $objT->rtn_rejected_qty; ?></td>
                                                                           
                                                                        </tr>

                                                                    <?php $iSno++;
                                                                      
                                                                        $tax_tot += $objT->rtn_rejected_qty;
                                                                       
                                                                    } ?>


                                                                    <tr>
                                                                        <td colspan="<?php echo $colspan; ?>" align="right"><strong>Grand Total</strong></td>
                                                                        
                                                                        <td align="right"><strong><?php echo number_format(($tax_tot),3); ?></strong></td>
                                                                    </tr>
                                                                <?php } ?>
                                                            </tbody>
                                                        </table>
                                                        </p>
                                                        <?php if (($rtn_approve_status == 0 || $rtn_approve_status == 1)) { ?> 
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <label>Remarks </label>
                                                                    <textarea name="pr_approve_remarks" id="pr_approve_remarks" class="form-control" rows="2" maxlength="250"><?php echo $obj->pr_approve_remarks; ?></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php } ?>
                                                        <!-- <p>
                                                            <b>GRN Note: </b><?php //echo $common_remarks; ?>
                                                        </p> -->
                                                        <?php if ($rtn_verify_status == 1 && $rtn_approve_status == 0) { ?> 
                                                        <div class="card-footer text-center">
                                                            <input type="hidden" name="rtn_id" id="rtn_id" value="<?php echo $_REQUEST['rtn_id']; ?>" />
                                                            <INPUT class="btn btn-custom" type="button" id="APPROVE" name="APPROVE" value="Approve">
                                                            <INPUT class="btn btn-danger" type="button" id="REJECT" name="REJECT" value="Reject">
                                                        </div>
                                                        <?php } ?>

                                                        
                                                    
                                            </tbody>
                                            

                                        <!-- </table> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                       
                        <div class="row">
                            <div class="text-left col-lg-6">
                                <div class="rec_create_dets"><b>Created by : </b><?php echo $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id ", $get->modify_by) . ' on ' . date('d-M-y @ H:i', strtotime($get->modify_date_time)); ?></b>
                                </div>
                            </div>
                            <div class="text-right col-lg-6">
                                <?php
                                if ($get->rtn_approve_status != 0) {
                                ?>
                                    <div class="rec_create_dets"><b>Approved by : </b><?php echo $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id ", $get->rtn_approve_by) . ' on ' . date('d-M-y @ H:i', strtotime($get->rtn_approve_date_time)); ?></b><br><b>Remarks : </b> <?php echo $get->rtn_approve_remarks; ?>
                                    </div>
                                <?php
                                }
                                ?>
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


        $('#APPROVE').click(function() {

            var rtn_id = $('#rtn_id').val();
            var remarks = $('#pr_approve_remarks').val();
            var task = "PR_APP";

            $.ajax({
                type: 'post',
                url: 'inc/cis_ajax/jquery_purchase_return_approval.php',
                data: {
                    "id": rtn_id,
                    "task": task,
                    "remarks": remarks
                },
                beforeSend: function() {
                    if (confirm('Are you sure to Approve this Purchase Return Order..?')) {} else {
                        return false;
                    }
                },
                complete: function() {},
                success: function(result) {
                    //location.reload();
               
                    window.location.href = "purchase_return_list.php";
                }
            });
            return false;
        });

        $('#REJECT').click(function() {

            if ($('#pr_approve_remarks').val() == '') {
                alert("Please enter the Purchase Return Rejection Remarks..!");
                $('#pr_approve_remarks').focus();
                return false;
            }

            var rtn_id = $('#rtn_id').val();
            var remarks = $('#pr_approve_remarks').val();
            var task = "PR_REJ";
            $.ajax({
                type: 'post',
                url: 'inc/cis_ajax/jquery_purchase_return_approval.php',
                data: {
                    "id": rtn_id,
                    "task": task,
                    "remarks": remarks
                },
                beforeSend: function() {
                    if (confirm('Are you sure to Reject this Purchase Return Order..?')) {} else {
                        return false;
                    }
                },
                complete: function() {},
                success: function(result) {
                    //location.reload();
                    window.location.href = "purchase_return_list.php";
                }
            });
            return false;
        });

    });
</script>

</html>