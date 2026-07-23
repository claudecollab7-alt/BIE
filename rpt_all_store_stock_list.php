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
    <title><?php echo PAGE_TITLE; ?> - Stock List</title>

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


            $("#item_type_id").on("change",function(){
               var itemtypeid = $("#item_type_id").val();
               //alert(itemtypeid)
               $.ajax({
                type: "POST",
                url: "inc/cis_ajax/jquery_select_item_rpt.php",
                data: {
                    itemtypeid: itemtypeid 
                }

               }).done(function(msg) {
                $('#item_id option').remove();
                var dataArr = msg.split('#');
                $.each(dataArr, function(i, element) {
                    if (dataArr[i] != "") {
                        var dataArr2 = dataArr[i].split('~');
                        $('#item_id').append("<option value='" + dataArr2[0] + "'>" + dataArr2[1] + "</option>");
                    }
                });
                $("#s2id_item_id").select2('val', '');
                $("#item_id").trigger("liszt:updated");
                });
            })
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
                            <span class="breadcrumb-item active">Stock List</span>
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
                                <h6 class="card-title">Stock List </h6>
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
                                    <!-- <div class="form-group col-md-2">
                                        <label>From Date</label>
                                        <input type="date" class="form-control" name='from_dt' id='from_dt' value="<?php echo  $rpt_from_dt; ?>">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label>To Date</label>
                                        <input type="date" class="form-control" name='to_dt' id='to_dt' value="<?php echo  $rpt_to_dt; ?>">
                                    </div> -->
                                     <div class="form-group col-md-3">
                                        <label>Item Type</label>
                                        <select name="item_type_id" id="item_type_id" class="form-control select-search">
                                           <option value="">--- Select Item Type ---</option>
                                            <option value="6">Consumable</option>
                                            <option value="7">Group Trading</option>
                                            <option value="3">Raw Materials</option>
                                            <option value="2">Trading</option>
                                        </select>
                                        <script>
                                            document.getElementById('item_type_id').value = "<?php echo $_REQUEST['item_type_id']; ?>";

                                        </script>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>item</label>
                                        <select name="item_id" id="item_id" class="form-control select-search">
                                            <option value="">-- All Items --</option>
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
                                //echo "<pre>";print_r($_POST);exit;
                                $Itemname='';
                                // $branch_name1 = '';
                                $sql = "SELECT * FROM mst_branch WHERE branch_status = '1' ";
                                $branch_name1 = $conn->query($sql);
                            //    echo  $rowcount=mysqli_num_rows($result);
                                if (isset($_POST['Report'])) {
                                   // $from_dt = date("Y-m-d", strtotime($_REQUEST['from_dt']));
                                   // $to_dt = date("Y-m-d", strtotime($_REQUEST['to_dt']));

                                    if ($_REQUEST['item_id'] != ''){
                                        $item_name = " | " . $dbconn->GetSingleReconrd("tbl_item_details", "item_code", "item_id", $_REQUEST['item_id']);
                                    }
                                    else{
                                        $item_name = '';
                                    }
                                    if ($_REQUEST['item_type_id'] != ''){
                                        $item_name = " | " . $dbconn->GetSingleReconrd("tbl_item_details", "item_type", "item_id", $_REQUEST['item_type_id']);
                                    }
                                    else{
                                        $item_name = '';
                                    }
                                    
                                    if(isset($_REQUEST['item_type_id']) && $_REQUEST['item_type_id'] == 2){
                                        $Itemname = 'Trading';
                                    }
                                    elseif(isset($_REQUEST['item_type_id']) && $_REQUEST['item_type_id'] == 3){
                                        $Itemname = 'Raw Meterial';
                                    }
                                    elseif(isset($_REQUEST['item_type_id']) && $_REQUEST['item_type_id'] == 6 ){
                                        $Itemname = 'Consumable';
                                    }
                                    elseif (isset($_REQUEST['item_type_id']) && $_REQUEST['item_type_id'] == 7) {
                                        $Itemname = 'Group Trading';
                                    }
                                    else{
                                        $Itemname='';   
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
											<span class="font-size-lg font-weight-semibold text-uppercase">Stock List <br>as on '. date('d-m-Y H:i').'';
                                            $selectOption = $_REQUEST['item_type_id'];
                                            if( $selectOption !== ""){
                                             
                                                echo' | '. $Itemname.'';
                                            }
                                            echo' </span>
									  </div>';

                                    echo '<div class="col-md-12 pt-2 text-center">';

                                    echo '<table class="table table-xs invoice_tbl" id="stock_db_table">
		                    			<thead>
		                    				
                                            <tr class="rpt_heading">
                                                <th><b>#</b></th>
                                                <th style="text-align: left;"><b>Item Code</b></th>
                                                <th style="text-align: left;"><b>Purchase Code</b></th>
                                                <th style="text-align: left;"><b>Description</b></th>
												<th style="text-align: left;"><b>Item Image</b></th>
                                                <th><b>UOM</b></th>
                                                <th><b>Category</b></th>
												<th style="text-align: right;"><b>Selling Price</b></th>
												<th style="text-align: right;"><b>Net Price</b></th>';
                                                while($itm = $branch_name1->fetch()){
                                                echo '<th>'. $itm->branch_name .'</th>';
                                                 }
                                                echo '</tr>
		                    			</thead>
		                            	<tbody>';

                                     $SQL = "SELECT *  FROM tbl_item_details as a  LEFT JOIN mst_uom as b ON a.item_uom = b.uom_id
                                             LEFT JOIN mst_category as c on a.item_category = c.category_id 
                                             WHERE a.item_status = 1";

                                             //echo '**'.$_REQUEST['item_id'].'***';

                                    if ($_REQUEST['item_id'] > 0 && $_REQUEST['item_id'] != '') {
                                        $SQL .= " AND a.item_id=" . $_REQUEST['item_id'];
                                    }
                                      if ($_REQUEST['item_type_id'] != '') {

                                        $SQL .= " AND a.item_type=" . $_REQUEST['item_type_id'];
                                    }

                                    //echo $SQL;

                                    $result = $conn->query($SQL);
	                                $tax_item_selling_price = '';	


                                    if ($result->rowCount() > 0) {
                                        $Sno = 1;
                                        while ($obj = $result->fetch()) {
                                            $tot_sales_value = 0;
                                            $name = $trans_type = $color = $trans_qty = $trans_code = $approve_by = '';

                                            $name = $dbconn->GetSingleReconrd("tbl_item_details", "item_desciption", "item_id", $obj->item_id);
                                            $uomname = $dbconn->GetSingleReconrd("mst_uom", "uom_name", "uom_id", $obj->uom_id);
                                            $Itemtypename = $dbconn->GetSingleReconrd("tbl_item_details", "item_type", "item_id", $obj->item_type);
											
											 if ($obj->item_image != "") {
                                                $item_image = '<a class="fancybox fancybox.ajax"  style="text-align: center;" target="_blank" href="project_img/item_image/' . $obj->item_image . '"><img src="project_img/item_image/' . $obj->item_image . '" width="50px" height="50px" alt=""></a>';
                                            } else {
                                                $item_image    = '<img class="fancybox"  src="project_img/no-image.jpg" width="50px" height="50px" >';
                                            }

                                            $SQL1 = "SELECT * FROM tbl_item_stock where item_id = $obj->item_id";
                                            $result1 = $conn->query($SQL1);

                                            if($obj->item_type == 2){
                                                $Itemtypename = 'Trading';
                                            }
                                            elseif($obj->item_type == 3){
                                                $Itemtypename = 'Raw Meterial';
                                            }
                                            elseif($obj->item_type == 6 ){
                                                $Itemtypename = 'Consumable';
                                            }
                                            elseif ($obj->item_type == 7) {
                                                $Itemtypename = 'Group Trading';
                                            }
                                            $branch_item_selling_price = $dbconn->GetSingleReconrd("mst_branch","branch_item_selling_price","branch_id",$_SESSION['_user_branch']);
                                            $item_selling_price = $dbconn->GetSingleReconrd("tbl_item_stock", "$branch_item_selling_price", "item_id", $obj->item_id);

		                                    $igst = $dbconn->GetSingleReconrd("mst_hsn", "igst", "hsn_id", $obj->item_hsn);


                                            $with_tax = $item_selling_price * $igst / 100;

		                                    $tax_item_selling_price = $item_selling_price + $with_tax;


                                           // $total = ((int)$obj->trans_qty * (int)$obj->item_price);
                                            echo '<tr ' . $color . '>
													<td>' . $Sno . '</td>
													<td align="left">' .$obj->item_code .'</td>
                                                    <td align="left">' .$obj->item_purchase_code. '</td>
													<td align="left">' . $name . '</td>
													<td align="left">' . $item_image . '</td>
													<td align="center">' . $uomname . '</td>
													<td align="left">' . $obj->category_name . '</td>
													<td align="right">' . $item_selling_price. '</td>
													<td align="right">' . number_format($tax_item_selling_price,2). '</td>';
                                                        while($obj1 = $result1->fetch()){
                                                            echo '<td align="right">' . $obj1->ho_stock . '</td>';
                                                        
                                                           
                                                      
                                                          echo '<td style="text-align: right;">' . $obj1->kl_stock . '</td>';
                                                        }
												    echo '</tr>';
                                            //$tot_sales_value += $total;

                                            $Sno++;
                                        }
                                        echo ' <!--<tr class="font-weight-semibold rpt_footer ">
                                                    <td colspan="9" align="right"><b>Total</b></td>
                                                    <td align="right"><b>Rs. ' . number_format($tot_sales_value, 2, ".", "") . '</b></td>
                                                </tr>-->';
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