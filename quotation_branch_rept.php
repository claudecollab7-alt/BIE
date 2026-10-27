<?php
ob_start();
session_start();
require_once("inc/common/userclass.php");
isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();


// 2026-10-26 customer names and item counts read once for the report, not 5-7 queries per quotation
// 2026-10-27 search text quoted (fnSqlText) and branch taken as a number - a ' in the search box broke the report
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$from_date = $dbconn->GetSingleReconrd("mst_finyear","finyr_startdt","finyr_active",1);
if(isset($_REQUEST['from_date']) && $_REQUEST['from_date'] != ''){
	$from_date = $_REQUEST['from_date'];
}
$to_date   = $dbconn->GetSingleReconrd("mst_finyear","finyr_enddt","finyr_active",1);
if(isset($_REQUEST['to_date']) && $_REQUEST['to_date'] != ''){
	$to_date = $_REQUEST['to_date'];
}

if ($_REQUEST['branch_id'] == '')
    $branch = 1;
else
    $branch = $_REQUEST['branch_id'];


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Quotation - Report</title>

    <?php include_once("inc/common/css-js.php"); ?>

    <script type="text/javascript" src="print_me.js"></script>
    <script src="js/jquery.table2excel.min.js"></script>
    <script src="js/html2pdf.bundle.min.js"></script>

    <script>
        $(function() {

            $(".rpt_export").click(function(e) {
                var table = $('#lst_table');
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
                            <a href="#" class="breadcrumb-item"> Report</a>
                            <span class="breadcrumb-item active">Quotation </span>
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
                                <h6 class="card-title">Quotation - Report </h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>

                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>

                            <form name="rptForm" action="" method="POST" onSubmit="return fnValidate();">
                                <div class="card-body pt-2 pb-5">
                                    <div class="form-group row">


                                        <div class="form-group col-md-2">
                                            <label>From Date</label>
                                            <input type="date" class="form-control" name='from_date' id='from_date' value="<?php echo $from_date; ?>">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>To Date</label>
                                            <input type="date" class="form-control" name='to_date' id='to_date' value="<?php echo  $to_date; ?>">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Search</label>
                                            <input type="text" class="form-control" placeholder="Ref No. / Customer Name" name='keyword' id='keyword' autocomplete="off" value="<?php echo $_REQUEST['keyword']; ?>">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>All Branches</label>
                                            <select name="branch_id" id="branch_id" class="form-control select-search">

                                                <?php
                                                echo $dbconn->fnFillComboFromTable_Where("branch_id", "branch_name", "mst_branch", "branch_id", " WHERE branch_status = 1");
                                                ?>
                                            </select>
                                            <script>
                                                document.getElementById('branch_id').value = "<?php echo $branch; ?>";
                                            </script>
                                        </div>

                                        <div class="form-group col-md-2">
                                            <button class="btn btn-info mt-4" name="Report" value="Report" type="submit">
                                                <i class="icon-statistics mr-1"></i>Generate Report</button>
                                        </div>
                                    </div>
                                    <hr>
                                    <?php
                                    if (isset($_REQUEST['Report'])) {

                                        $branch_name =  $dbconn->GetSingleReconrd("mst_branch", "branch_name", "branch_id", $_REQUEST['branch_id']);

                                        if ($_REQUEST['keyword'] != '')
                                            $keyword = " | " . $_REQUEST['keyword'];
                                        else
                                            $keyword = '';


                                        echo '<div class="col-md-12 text-right">	
                                            <a href="javascript:" class="rpt_export">
                                            <button type="button" class="btn btn-sm btn-light" ><i class="icon-file-excel mr-s"></i> Excel</button></a>
    
                                            <a href="javascript:" class="rpt_pdf">
                                            <button type="button" class="buttons-html5 btn btn-light" >
                                            <i class="icon-file-pdf mr-1"></i> PDF</button>
                                            </a>
                                                              
                                            <a href="javascript:PrintPartsNew(new Array(\'rpt_division\'),\'Invoice - Report\');" class="rpt_print">
                                            <button type="button" class="btn btn-sm btn-light" ><i class="fas fa-print mr-2""></i> Print</button></a>
                                            </div>';

                                        echo ' <div class="invoice" id="rpt_division" style="width:100%;">
                                            <div class="table-responsive" style="width:100%" id="payment_tbl">';


                                        echo '<div class="col-md-12 text-center">
													<span class="font-size-lg font-weight-semibold text-uppercase">Quotation Details</span><br/><b>' . date("d-M-Y", strtotime($from_date)) . ' - ' . date("d-M-Y", strtotime($to_date)) . '' . $keyword . '</b>
												</div><br>';


                                        echo ' 
                                        <table class="table table-xs invoice_tbl" id="lst_table">
															<thead>
																<tr class="rpt_heading">
																	<th>#</th>
																	<th>Ref No.</th>
																	<th align="center">Quotation Date</th>
																	<th>Customer Name</th>
																	<th style="text-align:center;">No.of Items</th>
																	<th style="text-align:right;">Quotation Value</th>
																</tr>
															</thead>
														<tbody>';

                                        $SQL = "SELECT *,a.supp_id as supp_id, a.bie_branch_id, b.supp_name 
														FROM tbl_quotation AS a 
														LEFT JOIN mst_supplier_new AS b ON b.supp_id = a.supp_id  
														WHERE a.quo_status = '1'";

                                        if ($_REQUEST['keyword'] != "") {
                                            $SQL .= " AND (a.quo_refno LIKE '%" . fnSqlText($_REQUEST['keyword']) . "%' OR a.quo_value LIKE '%" . fnSqlText($_REQUEST['keyword']) . "%'  OR b.supp_name LIKE '%" . fnSqlText($_REQUEST['keyword']) . "%' )";
                                        }

                                         if ($_REQUEST['supp_id'] != '') {
                                            $SQL .= " AND (a.supp_id ='" . fnSqlText($_REQUEST['supp_id']) . "') ";
                                        }

                                        if (($_REQUEST['branch_id']) != '') {
    
                                            $SQL .= " AND (a.bie_branch_id =" . (int)$_REQUEST['branch_id'] . ") ";
                                        }

                                         
                                        if ($_REQUEST['from_date'] != '' || $_REQUEST['from_date'] == '0000-00-00' && $_REQUEST['to_date'] != "" || $_REQUEST['to_date'] == '0000-00-00') {

                                            $SQL .= "AND a.quo_date BETWEEN '" . date('Y-m-d', strtotime($_REQUEST['from_date'])) . "' AND '" . date('Y-m-d', strtotime($_REQUEST['to_date'])) . "' ";
                                        }
                                       
                                        $SQL .= " ORDER BY a.quo_date DESC";

                                        $result = $conn->query($SQL);
                                        $quo_rows = $result->fetchAll();

                                        // names and item counts for all the quotations listed, one query each
                                        $quo_ids = $supp_ids = $cbr_ids = array(0);
                                        foreach ($quo_rows as $r) {
                                            $quo_ids[] = (int)$r->quo_id;
                                            $supp_ids[] = (int)$r->supp_id;
                                            $cbr_ids[] = (int)$r->branch_id;
                                        }
                                        $in = implode(',', array_unique($supp_ids));
                                        $m_supp_name = $dbconn->GetMap("SELECT supp_id, supp_name FROM mst_supplier_new WHERE supp_id IN ($in)");
                                        $m_supp_add1 = $dbconn->GetMap("SELECT supp_id, supp_add1 FROM mst_supplier_new WHERE supp_id IN ($in)");
                                        $in = implode(',', array_unique($cbr_ids));
                                        $m_cbr_name = $dbconn->GetMap("SELECT branch_id, branch_name FROM mst_customer_branch WHERE branch_id IN ($in)");
                                        $m_cbr_add2 = $dbconn->GetMap("SELECT branch_id, branch_add2 FROM mst_customer_branch WHERE branch_id IN ($in)");
                                        $m_items = $dbconn->GetMap("SELECT quo_id, COUNT(*) FROM tbl_quotation_details WHERE quo_id IN (" . implode(',', array_unique($quo_ids)) . ") GROUP BY quo_id");

                                        if ($result->rowCount() > 0) {
                                            $Sno = 1;

                                            foreach ($quo_rows as $obj) {
                                                if ($obj->supp_id != '0') {
                                                    $buss_name = $dbconn->MapValue($m_supp_name, $obj->supp_id);
                                                    $add2 = ' - ' . $dbconn->MapValue($m_supp_add1, $obj->supp_id);
                                                }

                                                if ($obj->branch_id > 0) {
                                                    $branch_name = $dbconn->MapValue($m_cbr_name, $obj->branch_id);
                                                    $add2 = ' - ' . $dbconn->MapValue($m_cbr_add2, $obj->branch_id);
                                                    $cus_details = $buss_name . '<br/><b>Branch Name: </b>' . $branch_name . $add2;
                                                } else {
                                                    $cus_details = '<b>' . $buss_name . '</b>' . $add2;
                                                }
                                                if ($obj->quo_id > 0) {
                                                    $quotation_link = '<a href="print_quotation.php?quo_id=' . $obj->quo_id . '" target="_blank">' .
                                                        $obj->quo_refno . '</a>';
                                                } else {
                                                    $quotation_link = 'Direct Sales Order';
                                                }
                                                $total_count = $dbconn->MapValue($m_items, $obj->quo_id, 0);
                                                echo '<tr  style="background-color:#bcedc88f";>
																<td>' . $Sno . '</td>
																<td>' . $quotation_link . '</td>
																<td>' . date("d-m-Y", strtotime($obj->quo_date)) . '</td>
																<td>' . strtoupper($cus_details) . '</td>
																<td align="center">' . $total_count . '</td>
																<td align="right">' . number_format($obj->quo_value, 2, ".", "") . '</td>
																
															</tr>';
                                                $total_quo_value += $obj->quo_value;
                                                $Sno++;
                                            }

                                            echo '<tr style="color: #463ece; font-size: 15px; background-color:#bcedc88f;">
																
																<td colspan="5" align="right"><b>Total</B></td>
																<td align="right"><b>' . number_format($total_quo_value, 2, ".", "") . '<b></td>
																
															</tr>';
                                            $obj = null;
                                        } else {
                                            echo ' <tr class="font-weight-semibold rpt_footer ">
																	   <td colspan="10" align="center">No History found..!</td>
																	</tr>';
                                        }


                                        echo '</tbody>
												</table> </div></div>';
                                    }

                                    ?>
                                  
                                </div>
                            </form>

                        </div>
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

</html>
                                          