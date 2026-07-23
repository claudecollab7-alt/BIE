<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
// require_once ("inc/common/dbrowiterator.php");	

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <title><?php echo PAGE_TITLE; ?> - List of Repair Indent</title>
    <link href="css/main.css" rel="stylesheet" type="text/css" />
    <!--[if IE 8]><link href="css/ie8.css" rel="stylesheet" type="text/css" /><![endif]-->

    <?php include_once("inc/common/css-js.php"); ?>


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

            //dom: '<"top"l>rt<"bottom"ip>',
            var dataTable = $('#indentTable').DataTable({

                dom: '<"datatable-header length-left"lp><"datatable-scroll"rt><"datatable-footer"ip>',
                'processing': true,
                "language": {
                    processing: '<i class="icon-spinner spinner mr-2"></i>Loading...'
                },
                'serverSide': true,
                'serverMethod': 'post',
                'lengthChange': true, // Remove default Page Length Control
                'searching': false, // Remove default Search Control
                "pageLength": 25,
                "order": [
                    [1, "desc"]
                ],
                'ajax': {
                    'url': 'inc/datatable/ajaxIndentList.php',
                    'data': function(data) {
                        // Read values
                        var supp = $('#searchByCode').val();

                        // Append to data
                        data.searchByCode = supp;
                    }
                },
                'columns': [{
                        data: 'sal_repair_id'
                    },
                    {
                        data: 'sal_repair_slno'
                    },
                    {
                        data: 'sal_repair_date'
                    },
                    {
                        data: 'supp_name'
                    },
                    {
                        data: 'repair_status'
                    },
                    {
                        data: 'action'
                    },
                ],

                columnDefs: [{
                        orderable: false,
                        targets: [1, 2, 3, 4, 5]
                    },
                    {
                        targets: [5],
                        className: 'text-center'
                    },
                ],

            });

            $('#searchByCode').keyup(function() {
                dataTable.draw();
            });

            $('#indentTable').on('click', 'a.delete', function(e) {
                e.preventDefault();
                if (confirm("Are you sure, you want to delete this Supplier?")) {

                    var id = $(this).attr('rel');
                    var table = "mst_supplier_new";
                    var status = "supp_status";
                    var value = "0";
                    var where = "supp_id";

                    var nRow = $(this).parents('tr')[0];

                    $.ajax({
                        type: 'post',
                        url: 'inc/cis_ajax/jquery_delete_records.php',
                        data: {
                            "id": id,
                            "table": table,
                            "status": status,
                            "value": value,
                            "where": where
                        },
                        beforeSend: function() {
                            //launchpreloader();
                        },
                        complete: function() {
                            //$.jGrowl('GSM deleted..!', { sticky: false, theme: 'growl-success',shutdown:'0.5', header: 'Success!' });
                        },
                        success: function(result) {
                            //alert(result);
                            if (result > 0) {
                                $('#indentTable').dataTable().fnDeleteRow(nRow);
                                $.jGrowl('Indent deleted..!', {
                                    sticky: false,
                                    theme: 'alert-styled-left alert-arrow-left alert-danger',
                                    position: 'top-right',
                                    shutdown: '3000',
                                    header: 'Success!'
                                });
                            } else if (result == 0)
                                $.jGrowl('Indent Not deleted..!', {
                                    sticky: false,
                                    theme: 'alert-styled-left alert-arrow-left alert-danger',
                                    position: 'top-right',
                                    shutdown: '3000',
                                    header: 'Error!'
                                });
                            else
                                $.jGrowl(result, {
                                    sticky: false,
                                    theme: 'alert-styled-left alert-arrow-left alert-danger',
                                    position: 'top-right',
                                    shutdown: '3000',
                                    header: 'Error!'
                                });

                        }
                    });

                }
            });
        });
    </script>
    <?php include("modal_supp_dets.php") ?>
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
                            <a href="#" class="breadcrumb-item"> Masters</a>
                            <span class="breadcrumb-item active">Indent List</span>
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
                                <h6 class="card-title">List of Repair Indent</h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" href="repair_indent_add.php" data-popup='tooltip' title="New Repair Indent"><i class="icon-plus-circle2 mr-2"></i></a>
                                        <a class="list-icons-item" data-action="fullscreen"></a>

                                    </div>
                                </div>
                            </div>

                            <div class="card-body pt-2">
                                <div class="form-group row pl-2">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Repair No / Dealer </label>
                                            <input type='text' class="form-control" id='searchByCode' name="searchByCode" placeholder='Enter Repair No, Date'>
                                        </div>
                                    </div>

                                </div>
                                <hr class="mt-0 mb-1">
                                <table class="table table-xs table-hover table-bordered mt-0" id="indentTable">
                                    <thead>
                                        <tr class="bg-table-header">
                                            <th width="50px">#</th>
                                            <th>Repair No</th>
                                            <th>Repair Date</th>
                                            <th>Dealer Name</th>
                                            <th>Status</th>
                                            <th width="15%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- /basic datatable -->


                        <?php //include ('modal_view_po.php'); 
                        ?>

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

</html>