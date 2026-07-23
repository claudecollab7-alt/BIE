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
                            <span class="breadcrumb-item active">Item Stock </span>
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
                                <h6 class="card-title">Stock List - <?php echo $_SESSION['_user_name']; ?></h6>
                            </div>
                            <div class="table-overflow">
                                <form name='thisForm' id="validate" method='post' action="">

                                    <table class="table table-xs table-hover table-bordered mt-0" id="itemDetailsTable1" width="100%">
                                        <tr>
                                            <td>

                                                <div class="form-group row pl-2">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Search </label>
                                                            <input type='text' class="form-control" id='searchByCode' name="searchByCode" placeholder='Description'>
                                                        </div>
                                                    </div>
                                            </td>
                                        </tr>
                                    </table>
                                    <table class="table table-xs table-hover table-bordered mt-0" id="itemStockDetailsTable">
                                        <thead>
                                            <tr class="">
                                                <th width="3%">#</th>
                                                <th width="10%">Item Image</th>
                                                <th width="10%">Item Code</th>
                                                <th width="15%">Description</th>
                                                <th width="10%">UOM</th>
                                                <th width="10%">Category</th>
                                                <th width="10%">Location</th>
                                                <?php
                                                if ($_SESSION['_user_branch'] == 1) { ?>
                                                    <th width="10%"> Stock</th>
                                                <?php } elseif ($_SESSION['_user_branch'] == 3) {
                                                ?>
                                                    <th width="10%"> Stock</th>
                                                <?php } ?>

                                                <th width="10%">Action</th>
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
        var dataTable = $('#itemStockDetailsTable').DataTable({

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
                'url': 'inc/datatable/ajaxStoreStockList.php',
                'data': function(data) {
                    // Read values
                    var supp = $('#searchByCode').val();
                    // Append to data
                    data.searchByCode = supp;
                }
            },
            'columns': [{
                    data: 'item_id'
                },
                {
                    data: 'item_image'
                },
                {
                    data: 'item_code'
                },
                {
                    data: 'item_desciption'
                },
                {
                    data: 'uom_name'
                },
                {
                    data: 'category_name'
                },
                {
                    data: 'location'
                },
                {
                    data: 'item_stock'
                },
                {
                    data: 'action'
                },

            ],

            columnDefs: [{
                    orderable: false,
                    targets: [6, 7, 8]
                },
                {
                    targets: [1, 4, 8],
                    className: 'text-center'
                },
            ],

        });

        $('#searchByCode').keyup(function() {
            dataTable.draw();
        });
    });

    $('#modalStockDets').on('show.bs.modal', function(e) {
        var id = $(e.relatedTarget).data('id');
        if (id != '') {
            $.ajax({
                type: 'post',
                url: 'inc/cis_ajax/jquery_modal_stock_det.php',
                data: 'id=' + id,
                success: function(data) {
                    string = data.split("~");
                    //  $('#m_sales_amt').html(string[2]);
                    //  $('#m_sales_code').html(data);
                    $('#m_loc_dets').html(string[0]);
                }
            });
        }
    });
</script>