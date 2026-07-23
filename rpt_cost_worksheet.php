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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <title><?php echo PAGE_TITLE; ?> - Item Stock List</title>
    <link href="css/main.css" rel="stylesheet" type="text/css" />
    <?php include_once("inc/common/css-js.php"); ?>

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
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Dashboard</a>
                            <a href="#" class="breadcrumb-item"> Report</a>
                            <span class="breadcrumb-item active"> Cost Work Sheet</span>
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
                                <h6 class="card-title">Item Stock List- <?php echo $_SESSION['_user_name']; ?></h6>
                            </div>
                            <div class="table-overflow">
                                <form name='thisForm' id="validate" method='post' action="">
                                    <div class="card-body pt-2 pb-2">
                                        <div class="form-group row">
                                            <div class="form-group col-md-4">
                                                <label>Search </label>
                                                <input type='text' class="form-control" id='searchByCode' name="searchByCode" placeholder=''>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label>All Type</label>
                                                <select name="item_type_id" id="item_type_id" class="form-control select-search">
                                                    <option value="">-- All Types --</option>
                                                    <?php
                                                    echo $dbconn->fnFillComboFromTable_Where("item_type_id", "item_name", "item_type", "item_type_id", " WHERE item_type_status = '1'");
                                                    ?>

                                                </select>
                                              

                                            <script>document.getElementById('searchByStatus').value="<?php echo $item_type_id; ?>";</script>

                                            </div>


                                        </div>
                                    </div>
                                    <table class="table table-xs table-hover table-bordered mt-0" id="costworksheet">
                                        <thead>
                                            <tr class="">
                                                <th width="3%" >#</th>
                                                <th width="10%" style="text-align:center;">Item Image</th>
                                                <th width="25%">Item Code / Sale Code</th>
                                                <th width="10%">Purchase Code</th>
                                                <th width="10%">Description</th>
                                                <th width="10%" style="text-align:right;">Selling Price</th>
                                                <th width="10%" style="text-align:right;">Cost Price</th>
                                                <th width="10%" style="text-align:right;">Margin</th>
                                                <th width="10%" style="text-align:right;">Percentage (%)</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <?php include("inc/common/footer.php") ?>
            <?php include("modal_stock_det.php") ?>
</body>

</html>


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
        var dataTable = $('#costworksheet').DataTable({

            dom: '<"datatable-header length-left"lp><"datatable-scroll"rt><"datatable-footer"ip>',
            'processing': true,
            'responsive': true,
            "language": {
                processing: '<i class="icon-spinner spinner mr-2"></i>Loading...'
            },
            'serverSide': true,
            'serverMethod': 'post',
            'lengthChange': true, // Remove default Page Length Control
            'searching': false, // Remove default Search Control
            "pageLength": 25,
            "order": [
                [0, "desc"]
            ],
            'ajax': {
                'url': 'inc/datatable/ajaxCostWorkSheet.php',
                'data': function(data) {
                    // Read values
                    var supp = $('#searchByCode').val();
                    var item_type_id = $('#item_type_id').val();
                    // Append to data
                    data.searchByCode = supp;
                    data.item_type_id = item_type_id;
                }
            },
            'columns': [{
                    data: 'item_id'
                },
                {
                    data: 'img_link'
                },
                {
                    data: 'item_code'
                },
                {
                    data: 'item_purchase_code'
                },
                {
                    data: 'item_desciption'
                },
                {
                    data: 'item_selling_price'
                },
                {
                    data: 'item_cost_price'
                },
                {
                    data: 'margin_price'
                },
                {
                    data: 'margin_percentage'
                },

            ],

            columnDefs: [{
                    orderable: false,
                    targets: [0, 1, 8, 7]
                },
                {
                    targets: [1, 4, 8],
                    className: 'text-center'
                },
                {
                    targets: [5,6,7,8],
                    className: 'text-right'
                },
            ],

        });

        $('#searchByCode').keyup(function() {
            dataTable.draw();
        });

        $('#item_type_id').change(function() {
                dataTable.draw();
            }); 
    });
</script>