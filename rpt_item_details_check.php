<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

if ($_REQUEST['from_dt'] == '')
    $rpt_from_dt = date('Y-m-d');
else
    $rpt_from_dt = $_REQUEST['from_dt'];

if ($_REQUEST['to_dt'] == '')
    $rpt_to_dt = date('Y-m-d');
else
    $rpt_to_dt = $_REQUEST['to_dt'];

//ini_set('display_errors', '1');
//ini_set('display_startup_errors', '1');
//error_reporting(E_ALL);

?>


<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Item List</title>

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
                            <span class="breadcrumb-item active">Item List</span>
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
                                <h6 class="card-title">Item List </h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>

                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="card-body pt-2 pb-5">
                                
                                
                                <?php
                                //echo "<pre>";print_r($_POST);exit;
                                
                                   


                                    echo '<div id="stock_division">
									  <div class="col-md-12 text-center">	
											<span class="font-size-lg font-weight-semibold text-uppercase">Item Details <br>as on '. date('d-m-Y H:i').'';
                                            
                                            echo' </span>
									  </div>';

                                    echo '<div class="col-md-12 pt-2 text-center">';

                                    echo '<table class="table table-xs invoice_tbl" id="stock_db_table">
		                    			<thead>
		                    				
                                            <tr class="rpt_heading">
                                                <th align="center">Item ID</th>
                                                <th style="text-align: left;">Item Description</th>
                                                <th style="text-align:center;">UOM</th>
                                                <th style="text-align:right;">Multiple UOM</th>
                                                <th style="text-align:center;">HSN Code</th>
                                            </tr>
		                    			</thead>
		                            	<tbody>';

                                     
                                   $SQL = "SELECT item_id, item_code, item_desciption, item_uom, multi_uom_id, item_hsn FROM tbl_item_details WHERE item_status = '1' ORDER BY item_id ASC";

                                    $result = $conn->query($SQL);
	                                $tax_item_selling_price = '';	


                                    if ($result->rowCount() > 0) {
                                        while ($obj = $result->fetch()) {
                                            $item_name = "<b>".$obj->item_code."</b> - ".$obj->item_desciption;
                                            $item_uom = $dbconn->GetSingleReconrd("mst_uom","uom_name","uom_id",$obj->item_uom);
                                            $item_multi_uom = $dbconn->GetSingleReconrd("mst_uom","uom_name","uom_id",$obj->multi_uom_id);
                                            $item_hsn = $dbconn->GetSingleReconrd("mst_hsn","hsn_code","hsn_id",$obj->item_hsn);


                                           echo '<tr>
                                                    <td>'.$obj->item_id.'</td>
                                                    <td style="text-align: left;">'.$item_name.'</td>
                                                    <td>'.$item_uom.'</td>
                                                    <td>'.$item_multi_uom.'</td>
                                                    <td>'.$item_hsn.'</td>
                                            </tr>';

                                            
                                        }
                                    } else {
                                        echo ' <tr class="font-weight-semibold rpt_footer ">
																	   <td colspan="10" align="center">No History found..!</td>
																	</tr>';
                                    }

                                    echo     '</tbody>
									   </table>
									</div></div>';
                                
                                ?>

                            </div>
                            
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