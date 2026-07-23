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

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Item Stock - Report</title>

    <?php include_once("inc/common/css-js.php"); ?>

    <script type="text/javascript" src="print_me.js"></script>
    <script src="js/jquery.table2excel.min.js"></script>
    <script src="js/html2pdf.bundle.min.js"></script>


    <script>
        $(function() {

            $(".rpt_export").click(function(e) {
                var table = $('#rpt_db_table');
                if (table && table.length) {
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
                var element = document.getElementById('rpt_division');
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
                            <span class="breadcrumb-item active">Item Stock</span>
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
                                <h6 class="card-title">Item Stock History - Report </h6>
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
                                        <label>Item Name</label>
                                        <select name="item_id" id="item_id" class="form-control select-search">
                                            <option value="">Select an Item</option>
                                            <?php
                                            echo $dbconn->fnFillComboFromTable_Where("item_id", "CONCAT(item_code,'~',item_desciption)", "tbl_item_details", "item_id", " WHERE item_status = 1");
                                            ?>
                                        </select>
                                        <script>
                                            document.getElementById('item_id').value = "<?php echo $_REQUEST['item_id']; ?>";
                                        </script>
                                    </div>


                                    <div class="form-group col-md-2">
                                        <button class="btn btn-info mt-4" name="Report" value="Report" type="submit">
                                            <i class="icon-statistics mr-1"></i>Generate Report</button>
                                    </div>

                                </div>
                                <hr>

                                <?php
                                if (isset($_POST['Report'])) {
                                    $from_dt = date("Y-m-d", strtotime($_REQUEST['from_dt']));
                                    $to_dt = date("Y-m-d", strtotime($_REQUEST['to_dt']));



                                    $itemName = " | " . $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_id", $_REQUEST['item_id']);



                                    echo '<div class="col-md-12 text-right">	
										  <a href="javascript:" class="rpt_export">
										  <button type="button" class="btn btn-sm btn-light" ><i class="icon-file-excel mr-s"></i> Excel</button></a>

                                          <a href="javascript:" class="rpt_pdf">
												<button type="button" class="buttons-html5 btn btn-light" >
												<i class="icon-file-pdf mr-1"></i> PDF</button>
											</a>
															
										  <a href="javascript:PrintPartsNew(new Array(\'rpt_division\'),\'Item Stock Flow - Report\');" class="rpt_print">
										  <button type="button" class="btn btn-sm btn-light" ><i class="fas fa-print mr-2""></i> Print</button></a>
										  </div>';


                                    echo '<div class="table-responsive">';
                                    echo '<div id="rpt_division">
                                          <div class="col-md-12 text-center">	
                                          <span class="rpt-heading">Item Stock Flow- Report</span><br>
                                          <span class= "rpt-subtitle">
                                          ' . date("d-M-Y", strtotime($from_dt)) . ' - ' . date("d-M-Y", strtotime($to_dt)) . '' . $itemName . ' 
                                          </span></div>';

                                    echo '<div class="col-md-12 pt-2 text-center">';


                                    echo '<table class="table table-xs invoice_tbl" id="rpt_db_table">
										  <thead>
										    <tr style="display:none">
												<th colspan="10"><center><b>Item Stock History - Item Stock - Report</b><br>as on '
                                        . date("d-M-Y", strtotime($from_dt)) . ' - ' . date("d-M-Y", strtotime($to_dt)) . '' . $itemName . ' </center></th>
											</tr>
															<tr class="rpt_heading">
																<th><b>#</b></th>
																<th><b>Date</b></th>
																<th><b>Type</b></th>
																<th><b>Ref Code</b></th>
																<th><b>Before Qty</b></th>
																<th><b>Transaction Quantity</b></th>														
																<th><b>After Qty</b></th>
																<th><b>Approved By</b></th>
                                                               
                                                               
															</tr>
														</thead>
														<tbody>';



                                    $SQL = " SELECT * FROM tbl_stock_flow
											 WHERE trans_qty > 0 AND trans_date between '" . $from_dt . "' AND '" . $to_dt . "'
                                             AND item_id ='" . $_REQUEST['item_id'] . "' ORDER BY auto_id asc ";

                                    //  echo $SQL;

                                    $result = $conn->query($SQL);
                                    if ($result->rowCount() > 0) {
                                        $Sno = 1;
                                        while ($obj = $result->fetch()) {


                                            //------grn ------//

                                            if ($obj->trans_type == 'GRN') {
                                                $color = 'style="background-color:#bcedc88f"';
                                                $trans_qty = ($obj->trans_qty);
                                                $trans_type = 'GRN';

                                                $trans_code = $dbconn->GetSingleReconrd("tbl_grn", "grn_ref_code", "grn_id", $obj->trans_id);

                                                $st_by = $dbconn->GetSingleReconrd("tbl_grn", "modify_by", "grn_id", $obj->trans_id);

                                                $st_dtm = $dbconn->GetSingleReconrd("tbl_grn", "modify_date_time", "grn_id", $obj->trans_id);

                                                $approve_by = $dbconn->GetSingleReconrd("tbl_user", "
												usr_name", "usr_id ", $st_by) . ' on ' . date('d-M-y @ h:i a', strtotime($st_dtm)) . "<br>";


                                                //---------Invoice -----//

                                            } else if ($obj->trans_type == 'INV') {
                                                $color = 'style="background-color:#7dc5f5"';
                                                $trans_qty = ($obj->trans_qty);
                                                $trans_type = 'INV';

                                                $trans_code = $dbconn->GetSingleReconrd("tbl_invoice", "inv_refno", "inv_id", $obj->trans_id);

                                                $created_by = $dbconn->GetSingleReconrd("tbl_invoice", "modify_by", "inv_id", $obj->trans_id);

                                                $created_dtm = $dbconn->GetSingleReconrd("tbl_invoice", "modify_date_time", "inv_id", $obj->trans_id);

                                                $approve_by = $dbconn->GetSingleReconrd("tbl_user", "
                                            	usr_name", "usr_id ", $created_by) . ' on ' . date('d-M-y @ h:i a', strtotime($created_dtm)) . "<br>";
                                            }

                                            echo '<tr ' . $color . '>

												       <td>' . $Sno . '</td>
												        <td align="center">' . date('d-m-y', strtotime($obj->trans_date)) . '</td>
												        <td align="center">' . $trans_type . '</td>
												        <td align="center">' . $trans_code . '</td>
												        <td>' . $obj->before_qty . '</td>
												        <td>' . $trans_qty . '</td>
												        <td>' . $obj->after_qty . '</td>
												        <td align="left">' . $approve_by . '</td>
                                                                          						
											    </tr>';
                                            $Sno++;
                                        }
                                    } else {
                                        echo ' <tr class="font-weight-semibold rpt_footer ">
											            <td colspan="8" align="center">No History found..!</td>
											  </tr>';
                                    }

                                    echo     '</tbody>
											  </table>
											  </div></div></div>';
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

        $('.select-search').select2({
            placeholder: 'Select an Item',
            allowClear: true
        });


    });

    function fnValidate() {

        if (isNull(document.rptForm.item_id, "Item ..!")) {
            return false;
        }
        document.rptForm.submit();
    }
</script>

</html>