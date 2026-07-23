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

    if(!isset($_REQUEST['finyr_name']) ){

        $finyr_name = $dbconn->GetSingleReconrd("mst_finyear","finyr","finyr_active",1);
    
    }else{
        $finyr_name = ($_REQUEST['finyr_name']);
    }
    

if ($_REQUEST['from_dt'] == '')
    $rpt_from_dt = date('Y-m-d');
else
    $rpt_from_dt = $_REQUEST['from_dt'];

if ($_REQUEST['to_dt'] == '')
    $rpt_to_dt = date('Y-m-d');
else
    $rpt_to_dt = $_REQUEST['to_dt'];

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
    <title><?php echo PAGE_TITLE; ?> - Branch Wise Stock List</title>

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


            $("#item_type_id").on("change", function() {
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
                            <span class="breadcrumb-item active">Branch Wise Stock List</span>
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
                                <h6 class="card-title">Branch Wise Stock List </h6>
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
                                   

                                    <!-- <div class="form-group col-md-3">
                                        <label>Item Type</label>
                                        <select name="item_type_id" id="item_type_id" class="form-control select-search">
                                            <option value="">--- Select Item Type ---</option>
                                            <option value="6">Consumable</option>
                                            <option value="7">Group Trading</option>
                                            <option value="3">Raw Materials</option>
                                            <option value="2">Trading</option>
                                        </select>
                                        <script>
                                            document.getElementById('item_type_id').value = "<?php //echo $_REQUEST['item_type_id']; ?>";
                                        </script>
                                    </div> -->
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

                                $item_name = '';
                                $sql = "SELECT * FROM mst_branch WHERE branch_status = '1' ";
                                $branch_name1 = $conn->query($sql);

                                if (isset($_POST['Report'])) {

                                    if ($_REQUEST['item_id'] != '') {
                                        $item_name = " | " . $dbconn->GetSingleReconrd("tbl_item_details", "item_code", "item_id", $_REQUEST['item_id']);
                                    } else {
                                        $item_name = '';
                                    }
                                    // if ($_REQUEST['item_type_id'] != '') {
                                    //     $item_name = " | " . $dbconn->GetSingleReconrd("tbl_item_details", "item_type", "item_id", $_REQUEST['item_type_id']);
                                    // } else {
                                    //     $item_name = '';
                                    // }

                                    $branch_name =  $dbconn->GetSingleReconrd("mst_branch", "branch_name", "branch_id", $_REQUEST['branch_id']);

                                    // if (isset($_REQUEST['item_type_id']) && $_REQUEST['item_type_id'] == 2) {
                                    //     $Itemname = 'Trading';
                                    // } elseif (isset($_REQUEST['item_type_id']) && $_REQUEST['item_type_id'] == 3) {
                                    //     $Itemname = 'Raw Meterial';
                                    // } elseif (isset($_REQUEST['item_type_id']) && $_REQUEST['item_type_id'] == 6) {
                                    //     $Itemname = 'Consumable';
                                    // } elseif (isset($_REQUEST['item_type_id']) && $_REQUEST['item_type_id'] == 7) {
                                    //     $Itemname = 'Group Trading';
                                    // } else {
                                    //     $Itemname = '';
                                    // }
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
											<span class="font-size-lg font-weight-semibold text-uppercase">Stock List <br>as on ' . date('d-m-Y H:i') . '';


                                    echo ' | ' . $branch_name . '  '.$item_name.'';

                                    echo ' </span>
									  </div>';

                                    echo '<div class="col-md-12 pt-2 text-center">';

                                    echo '<table class="table table-xs invoice_tbl" id="stock_db_table">
		                    			<thead>
		                    				
                                            <tr class="rpt_heading">
                                            <th width="3%">#</th>
                                            <th width="10%">Item Image</th>
                                            <th width="10%">Item Code</th>
                                            <th width="15%">Description</th>
                                            <!--<th width="10%">Item Type</th>-->
                                            <th width="10%" style="text-align:center;">UOM</th>
                                            <th width="10%" style="text-align:center;">Current Stock</th>
                                            <th width="10%" style="text-align:center;">SO Qty</th>
                                            <th width="10%" style="text-align:center;">Required Qty</th>
                                            
                                               
		                    			</thead>
		                            	<tbody>';


                                        $sql = "SELECT a.item_id as item_id, a.item_uom, a.item_image, a.item_code, a.item_type, a.item_desciption, a.item_curr_stock, b.uom_name, c.category_name,a.branch_id FROM tbl_item_details as a 
                                        LEFT JOIN mst_uom as b ON a.item_uom = b.uom_id
                                        LEFT JOIN mst_category as c on a.item_category = c.category_id  
                                        LEFT JOIN tbl_item_stock as d on a.item_id = d.item_id 

                                        WHERE a.item_status = 1";
                                        $field_name = $dbconn->GetSingleReconrd("mst_branch", "branch_stock_field", "branch_id", $_SESSION['_user_branch']);
                                            
                                            // if($_REQUEST['keyword'] != ""){
                                            //     $sql .= " AND (a.item_code LIKE '%".$_REQUEST['keyword']."%' OR a.item_purchase_code LIKE '%".$_REQUEST['keyword']."%'  OR a.item_desciption LIKE '%".$_REQUEST['keyword']."%' )";
                                            // }
                                            // if ($_REQUEST['item_type_id'] != "" && $_REQUEST['item_type_id'] > 0) {
                                            //     $sql .= " AND a.item_type = '" . $_REQUEST['item_type_id'] . "' ";
                                            // } else {
                                            //     $sql .= " AND a.item_type IN (1,2,3,4,5,6)";
                                            // }
                                            if ($_REQUEST['item_id'] > 0 && $_REQUEST['item_id'] != '') {
                                                $sql .= " AND a.item_id=" . $_REQUEST['item_id'];
                                            }

                                            if (($_REQUEST['branch_id'] == $field_name) != '') {

                                                $sql .= " AND branch_stock_field =" . $_REQUEST['branch_id'];
                                            }
                                            

                                            $sql .= " ORDER BY a.item_id ASC";
                                    $result = $conn->query($sql);

                                    if ($result->rowCount() > 0) {
                                        $Sno = 1;
                                        while ($obj = $result->fetch()) {

                                            $so_pending_qty = $available_qty = 0;
        
                                            $item_uom = $dbconn->GetSingleReconrd("mst_uom", "uom_name", "uom_id", $obj->item_uom);
                                            $field_name = $dbconn->GetSingleReconrd("mst_branch", "branch_stock_field", "branch_id", $_REQUEST['branch_id']);
                                            $branch_item_curr_stock = $dbconn->GetSingleReconrd("tbl_item_stock", "$field_name", "item_id", $obj->item_id);
        
                                            if ($obj->item_image != "") {
                                                $img_link = '<a class="fancybox" href="project_img/item_image/' . $obj->item_image . '">
                                                        <img src="project_img/item_image/' . $obj->item_image . '" width="30px" height="30px" alt=""></a>';
                                            } else {
                                                $img_link = '';
                                            }
        
        
                                            if ($_SESSION['_user_id'] == 1 ||  $_SESSION['_user_id'] == 9) {
                                                $edit_link = '<a href="inc/popup/fancybox_store_stocklist.php?item_id=' . $obj->item_id . '"
                                                class="tip fancybox various fancybox.ajax" title="Update Location"><i class="fa fa-edit"></i></a>';
                                            } else {
                                                $edit_link = '';
                                            }
                                            // echo "SELECT SUM(b.so_qty) as so_qty, a.so_finyr FROM tbl_sales_order as a LEFT JOIN tbl_sales_order_details as b ON 
                                            // a.so_id=b.so_id WHERE a.so_finyr='".$finyr_name."' AND a.bie_branch_id ='" . $_REQUEST['branch_id']."' AND a.dc_status=0 AND a.so_cancel_status = 0 AND b.item_id='".$obj->item_id."'";

                                            $so_details_qty = $conn->query("SELECT SUM(b.so_qty) as so_qty, a.so_finyr FROM tbl_sales_order as a LEFT JOIN tbl_sales_order_details as b ON 
                                            a.so_id=b.so_id WHERE a.so_finyr='".$finyr_name."' AND a.bie_branch_id ='" . $_REQUEST['branch_id']."' AND a.dc_status=0 AND a.so_cancel_status = 0 AND b.item_id='".$obj->item_id."'");
        
                                        
                                            if ($so_details_qty->rowCount() > 0) {
                                                
                                                $get = $so_details_qty->fetch(PDO::FETCH_OBJ);
                                                
                                                $so_qty = $get->so_qty;
        
                                                if($_REQUEST['finyr_name'] !="")
                                                {
                                                    $so_qty .= "AND a.so_finyr = '".($_REQUEST['finyr_name'])."' ";
                                                }
                                            
        
                                            }
        
        
                                            $so_pending_qty = (float)$so_qty;
                                            
                                            if($so_pending_qty > 0 && $so_pending_qty > $branch_item_curr_stock){
                                                
                                                $available_qty = (float)$branch_item_curr_stock - (float)$so_pending_qty;
                                                
        
                                            }else{
                                                $available_qty = 0;
                                            }
                                            
        
                                            echo '<tr>
                                                            <td>' . $Sno . '</td>
                                                            <td>' . $img_link . '</td>
                                                            <td >' . $obj->item_code . '</td>
                                                            <td>' . $obj->item_desciption . '</td>
                                                            <!--<td>' . $obj->item_name . '</td>-->
                                                            <td style="text-align:center;">' . $obj->uom_name . '</td>
                                                            <td style="text-align:center;">' . $branch_item_curr_stock. '</td>
                                                            <td style="text-align:center;">'.$so_pending_qty.'</td>
                                                            <td style="text-align:center;">'.$available_qty.'</td>
                                                            
                                                        </tr>';
                                            $Sno++;
                                            
                                        }
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