<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();


if ($_REQUEST['from_dt'] == '')
    $rpt_from_dt = date('Y-m-d');
else
    $rpt_from_dt = $_REQUEST['from_dt'];

if ($_REQUEST['to_dt'] == '')
    $rpt_to_dt = date('Y-m-d');
else
    $rpt_to_dt = $_REQUEST['to_dt'];

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

?>


<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Sales List</title>

    <?php include_once("inc/common/css-js.php"); ?>
    <script type="text/javascript" src="print_me.js"></script>
    <script src="js/jquery.table2excel.min.js"></script>
    <script src="js/html2pdf.bundle.min.js"></script>

    <script>
        function fnValidate() {


            document.rptForm.submit();
        }


        $(function() {



            $(".rpt_export").click(function(e) {
                var table = $('#stock_db_table');
                if (table && table.length) {
                    //var preserveColors = (table.hasClass('table2excel_with_colors') ? true : false);
                    $(table).table2excel({
                        exclude: ".noExl",
                        name: "SR",
                        filename: "SR" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xls",
                        fileext: ".xls",
                        exclude_img: true,
                        exclude_links: true,
                        exclude_inputs: true,
                        preserveColors: true,
                    });
                }
            });
            $(".rpt_pdf").click(function(e) {
                var element = document.getElementById('stock_division');
                //html2pdf(element);
                var opt = {
                    margin: 1,
                    filename: '<?php echo "SR" . date("dMY"); ?>' + '.pdf',
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
            });


        });
    </script>

</head>

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
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Home</a>
                            <a href="#" class="breadcrumb-item"> Reports</a>
                            <span class="breadcrumb-item active">Sales List</span>
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

                        <!-- Basic datatable -->
                        <div class="card">

                            <div class="card-header bg-pgheader text-white header-elements-inline">
                                <h6 class="card-title">Sales List </h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>

                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>

                            <form name="rptForm" action="" method="POST" onSubmit="return fnValidate();" />
                            <div class="card-body pt-2 pb-5">
                                <div class="form-group row">
                                    <div class="form-group col-md-2">
                                        <label>From Date</label>
                                        <input type="date" class="form-control" name='from_dt' id='from_dt' value="<?php echo  $rpt_from_dt; ?>">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label>To Date</label>
                                        <input type="date" class="form-control" name='to_dt' id='to_dt' value="<?php echo  $rpt_to_dt; ?>">
                                    </div>
                                     <div class="form-group col-md-3">
                                        <label>Payment Method</label>
                                        <select name="pay_id" id="pay_id" class="form-control select-search">
                                            <option value="">-- All Method --</option>
                                            <?php
                                            echo $dbconn->fnFillComboFromTable_Where("pay_id", "pay_name", "mst_pay_method", "pay_id", " WHERE pay_status = 1");
                                            ?>
                                        </select>
                                        <script>
                                            document.getElementById('pay_id').value = "<?php echo $_REQUEST['pay_id']; ?>";

                                        </script>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Customer</label>
                                        <select name="supp_id" id="supp_id" class="form-control select-search">
                                            <option value="">-- All Customer --</option>
                                            <?php
                                            echo $dbconn->fnFillComboFromTable_Where("supp_id", "supp_name", "mst_supplier_new", "supp_id", " WHERE supp_type ='C' AND supp_status= 1");
                                            ?>
                                        </select>
                                        <script>
                                            document.getElementById('supp_id').value = "<?php echo $_REQUEST['supp_id']; ?>";

                                        </script>
                                    </div>

                                    <div class="form-group col-md-2">
                                        <button class="btn btn-info mt-4" name="Report" value="Report" type="submit">
                                            <i class="icon-statistics mr-1"></i>Generate Report</button>
                                    </div>
                                </div>
                                <hr>
                                <?php
                                //echo"<pre>";print_r($_POST);exit;
                                if (isset($_POST['Report'])) {

                                   $from_dt = date("Y-m-d", strtotime($_REQUEST['from_dt']));
                                   $to_dt = date("Y-m-d", strtotime($_REQUEST['to_dt']));

                                    if ($_REQUEST['supp_id'] != '')
                                    {
                                        $item_name = " | " . $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_id", $_REQUEST['supp_id']);
                                    }
                                    else{
                                        $item_name = '';
                                    }

                                    if ($_REQUEST['pay_id'] != ''){
                                        $pay_id = " | " . $dbconn->GetSingleReconrd("tbl_invoice", "inv_id", "pay_id", $_REQUEST['pay_id']);
                                    }
                                    else{
                                        $pay_id = '';
                                    }


                                    echo '<div class="col-md-12 text-right">	
											<a href="javascript:" class="rpt_export">
												<button type="button" class="buttons-html5 btn btn-light" ><i class="icon-file-excel mr-1"></i> Excel</button></a>
											<a href="javascript:" class="rpt_pdf">
												<button type="button" class="buttons-html5 btn btn-light" >
												<i class="icon-file-pdf mr-1"></i> PDF</button>
											</a>
											<a href="javascript:PrintPartsNew(new Array(\'stock_division\'),\'Day Book\');" class="rpt_print">
												<button type="button" class="buttons-html5 btn btn-light" ><i class="icon-printer mr-1"></i> Print</button></a>
									  </div>';



                                    echo '<div id="stock_division">
									  <div class="col-md-12 text-center">	
											<span class="font-size-lg font-weight-semibold text-uppercase">Sales List</span><br>
											<b>
											' . date("d-M-Y", strtotime($from_dt)) . ' - ' . date("d-M-Y", strtotime($to_dt)) . ' '.$item_name.'
									   </div>';

                                    echo '<div class="col-md-12 pt-2 text-center">';

                                    echo '<table class="table table-xs invoice_tbl" id="stock_db_table">
		                    			<thead>
		                    				
                                            <tr class="rpt_heading">
                                                <th><b>#</b></th>
                                                <th><b>Date</b></th>
                                                <th><b>Invoice Ref No</b></th>
                                                <th><b>Customer Name</th>
                                                <th><b>Payment Method</b></th>
                                                <th><b>Invoice Amount</b></th>
                                            </tr>
		                    			</thead>
		                            	<tbody>';

                                    /*if (isset($_REQUEST['supp_id']) && $_REQUEST['supp_id'] != '') {
                                        $SQL = "SELECT *  FROM tbl_invoice_details as a 
                                            LEFT JOIN tbl_invoice as b ON a.inv_id = b.inv_id
                                            LEFT JOIN tbl_item_details as c on a.supp_id = c.supp_id 
                                            WHERE c.item_status = 1  AND a.supp_id =" . $_REQUEST['supp_id']." AND b.pay_id = ".$_REQUEST['pay_id']." ORDER BY c.supp_id asc ";
                                    } else {
                                        $SQL = "SELECT *  FROM tbl_invoice_details as a 
                                            LEFT JOIN tbl_invoice as b ON a.inv_id = b.inv_id
                                            LEFT JOIN tbl_item_details as c on a.supp_id = c.supp_id 
                                            WHERE c.item_status = 1   ORDER BY c.supp_id asc ";
                                    }*/

                                    $SQL = "SELECT *,b.supp_id as suppid FROM tbl_invoice_details as a 
                                            LEFT JOIN tbl_invoice as b ON a.inv_id = b.inv_id
                                            LEFT JOIN mst_supplier_new as d ON b.supp_id = d.supp_id
                                            LEFT JOIN tbl_item_details as c on a.item_id = c.item_id 
                                            WHERE 1 = 1";

                                    if ($_REQUEST['supp_id'] != '') {
                                        $SQL .= " AND d.supp_id=" . $_REQUEST['supp_id'];
                                    }
                                      if ($_REQUEST['pay_id'] != '') {

                                        $SQL .= " AND b.pay_id=" . $_REQUEST['pay_id'];
                                    }

                                    $SQL .= " AND inv_date between '" . $from_dt . "' AND '" . $to_dt . "' order by b.supp_id asc";


                                    //echo $SQL;

                                    $result = $conn->query($SQL);

                                    if ($result->rowCount() > 0) {
                                        $Sno = 1;
                                        $tot_sales_value = 0;
                                        while ($obj = $result->fetch()) {
                                            
                                            $name = $trans_type = $color = $trans_qty = $trans_code = $approve_by = '';

                                            // if ($obj->trans_type == 'GRN') {
                                            //     $color = 'style="background-color:#d9def9"';
                                            //     $trans_qty = $obj->trans_qty;
                                            //     $trans_type = 'GRN';
                                            //     $trans_code = $dbconn->GetSingleReconrd("tbl_grn", "grn_ref_code", "grn_id ", $obj->trans_id);
                                            //     $approve_by = "";
                                            // } else if ($obj->trans_type == 'SALE') {
                                            //     $color = 'style="background-color:#defbe9"';

                                            //     $trans_qty = ($obj->trans_qty * -1);
                                            //     $trans_type = 'SALE';

                                                /*$trans_code = $dbconn->GetSingleReconrd("hk_laund_head","lau_code","lau_id",
																	$obj->trans_id);
																	
																	$approve_by =$dbconn->GetSingleReconrd("tbl_user","usr_name","usr_id ",$obj->created_by).' on '.date('d-M-y @ h:i a',strtotime($obj->created_dtm));
*/
                                            // } else if ($obj->trans_type == 'ADJ') {
                                            //     $color = 'style="background-color:#ffe4e4"';
                                            //     $trans_qty = ($obj->trans_qty);
                                            //     $trans_type = 'Adjustment';
                                                /*	$trans_code = $dbconn->GetSingleReconrd("hk_item_adjustment_head","adj_code","adj_id",$obj->trans_id);
																	
																	$approval_qry = "SELECT apprv_usr_id,apprv_usr_dtm FROM hk_item_adjustment_approval_dets WHERE adj_id = ".$obj->trans_id." "; 
																	$approval_res = $conn->query($approval_qry);
																	if($approval_res->rowCount() > 0)
																	{
																		$approve_by = "";
																		while($approveObj = $approval_res->fetch())
																		{
																
																			$approve_by .= $dbconn->GetSingleReconrd("tbl_user","
																			usr_name","usr_id ",$approveObj->apprv_usr_id).' on '.date('d-M-y @ h:i a',strtotime($approveObj->apprv_usr_dtm))."<br>";
																		}
																	}*/
                                            //}
                                            $name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_id", $obj->suppid);
                                            $payname = $dbconn->GetSingleReconrd("mst_pay_method", "pay_name", "pay_id", $obj->pay_id);

                                            if($obj->pay_id == 2){
                                            $payname .= '<br><small>'.$obj->pay_chq_no.'</small>';
                                            }
                                            elseif($obj->pay_id == 3){
                                                $payname .= '<br><small>'.$obj->pay_cardno.'</small>';
                                            }
                                            elseif($obj->pay_id == 4 ||$obj->pay_id== 5 || $obj->pay_id==6 ){
                                                $payname .= '<br><small>'.$obj->pay_refno.'</small>';
                                            }
                                            //$total = ((int)$obj->trans_qty * (int)$obj->item_price);
                                            echo '<tr ' . $color . '>
																			<td>' . $Sno . '</td>
                                                                            <td align="center">' .date('d-m-Y',strtotime($obj->inv_date)) . '</td>
																			<td align="center">' .$obj->inv_refno . '</td>
																			<td align="center">' . $name . '</td>
																			<td align="center">' . $payname . '</td>
																			<td align="Right">' . $obj->net_value . '</td>
																			
																		</tr>';
                                            $tot_sales_value += $obj->net_value;
                                           // echo $tot_sales_value;exit;
                                            $Sno++;
                                        }
                                        echo ' <tr class="font-weight-semibold rpt_footer ">
                                                    <td colspan="5" align="right"><b>Total</b></td>
                                                    <td align="right"><b>Rs. ' . number_format($tot_sales_value, 2, ".", "") . '</b></td>
                                                </tr>';
                                    } else {
                                        echo ' <tr class="font-weight-semibold rpt_footer ">
																	   <td colspan="10" align="center">No History found..!</td>
																	</tr>';
                                    }

                                    echo     '</tbody>
									   </table>
									</div></div>';
                                }
                                ?>

                            </div>
                            </form>
                        </div>
                        <!-- /basic datatable -->
                        <!-- End of This Form UI  --->
                    </div>
                </div>
                <!-- /dashboard content -->
            </div>
            <!-- /content area -->

            <!-- Footer -->
            <?php include("inc/common/footer.php") ?>
            <!-- /footer -->
        </div>
        <!-- /main content -->
    </div>
    <!-- /page content -->
</body>

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

</html>