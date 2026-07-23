<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
// require_once ("inc/common/dbrowiterator.php");	

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$searchByBranch='1';
$searchByYear=$dbconn->GetSingleReconrd("mst_finyear","finyr","finyr_active",1);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <title><?php echo PAGE_TITLE; ?> - Prepare Purchase Order</title>
    <!--<link href="css/main.css" rel="stylesheet" type="text/css" />-->
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

            // $('.select-search').select2({
            //         placeholder: 'All Supplier',
            //         allowClear: true
            //  });
            $('.js-basic-single').select2({
                placeholder: 'All Status',
                allowClear: true
            });

            //dom: '<"top"l>rt<"bottom"ip>',
            var dataTable = $('#siTable').DataTable({

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
                    'url': 'inc/datatable/ajaxPoPrepareList.php',
                    'data': function(data) {
                        // Read values
                        var supp = $('#searchByCode').val();
                        var searchByStatus = $('#searchByStatus').val();
                        var searchByBranch = $('#searchByBranch').val();
                        var searchByYear = $('#searchByYear').val();
                        

                      


                        // Append to data
                        
                        data.searchByCode = supp;
                        data.searchByStatus = searchByStatus;
                        data.searchByBranch = searchByBranch;
                        data.searchByYear = searchByYear;


                        

                    }
                },
                'columns': [{
                        data: 'sno'
                    },
                    {
                        data: 'si_id'
                    },
                    {
                        data: 'si_date'
                    },
                    {
                        data: 'si_items'
                    },
                    {
                        data: 'send_admin_status'
                    },
                    {
                        data: 'action'
                    },
                ],

                columnDefs: [{
                        orderable: false,
                        targets: [0, 2, 3, 5]
                    },
                    {
                        targets: [3,4,5],
                        className: 'text-center'
                    },
                    {
                        targets: [],
                        className: 'text-right'
                    },
                ],

            });

            $('#searchByCode').keyup(function() {
                dataTable.draw();
            });
            $('#searchByStatus').change(function() {
                dataTable.draw();
            });

            $('#searchByBranch').change(function() {
                dataTable.draw();
            });
            $('#searchByYear').change(function() {
                dataTable.draw();
            });


            // if(($_SESSION['_user_branch'] ) != '1'){
               
            //     $('.hide_supplier').hide();
            // }else
            // {
            //     $('.hide_supplier').show();

            // }

            $('#siTable').on('click', 'a.delete', function(e) {
                e.preventDefault();
                if (confirm("Are you sure, you want to delete this Supplier?")) {

                    var id = $(this).attr('rel');
                    var table = "tbl_store_indent";
                    var status = "si_status";
                    var value = "0";
                    var where = "si_id";

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
                                $('#siTable').dataTable().fnDeleteRow(nRow);
                                $.jGrowl('Direct SI List deleted..!', {
                                    sticky: false,
                                    theme: 'alert-styled-left alert-arrow-left alert-danger',
                                    position: 'top-right',
                                    shutdown: '3000',
                                    header: 'Success!'
                                });
                            } else if (result == 0)
                                $.jGrowl('Direct SI List Not deleted..!', {
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
                            <a href="#" class="breadcrumb-item"> Work Area</a>
                            <span class="breadcrumb-item active">Prepare Purchase Order</span>
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
                                <h6 class="card-title">List of Store Indent to Prepare Purchase Order</h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <!-- <a class="list-icons-item" href="store_indent_add.php" data-popup='tooltip' title="New Direct Store Indent "><i class="icon-plus-circle2 mr-2"></i></a> -->
                                        <a class="list-icons-item" data-action="fullscreen"></a>

                                    </div>
                                </div>
                            </div>

                            <div class="card-body pt-2">
                                <div class="form-group row pl-2">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Ref. No. / Store Indent Date</label>
                                            <input type='text' class="form-control" id='searchByCode' name="searchByCode" placeholder='Enter Ref. No. /  Store Indent Date'>
                                        </div>
                                    </div>
                                   
                                    <!-- <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select name="searchByStatus" id="searchByStatus" class="form-control form-control-select2 js-basic-single">
                                            <option value="">-- Select Status--</option>
                                            <option value="0">Draft</option>
                                            <option value="1">Pending</option>
                                            <option value="2">Completed</option>
                                            </select>
                                            <script>document.getElementById('searchByStatus').value="// $searchByStatus; ";</script>
                                        </div>
                                    </div> -->
                                    <?php if($_SESSION['_user_branch']==1){ ?>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Branch</label>
                                            <select name="searchByBranch" id="searchByBranch" class="form-control form-control-select2">
                                            <option value="">-- All Branch--</option>
                                                <?php
                                                $dbconn = new dbhandler();
                                                echo $dbconn->fnFillComboFromTable_Where("branch_id", "branch_name", "mst_branch", "branch_id", " WHERE branch_status = '1'") ?>
                                            </select>
                                            <script>document.getElementById('searchByBranch').value="<?php echo $searchByBranch; ?>";</script>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Financial Year</label>
                                            <select name="searchByYear" id="searchByYear" class="form-control form-control-select2">
                                            <option value="">-- All Financial Year--</option>
                                                <?php
                                                $dbconn = new dbhandler();
                                                echo $dbconn->fnFillComboFromTable_Where("finyr", "finyr", "mst_finyear", "finyr", " WHERE rec_del_status = '0'") ?>
                                            </select>
                                            <script>document.getElementById('searchByYear').value="<?php echo $searchByYear; ?>";</script>
                                        </div>
                                    </div>

                                </div>
                               
                                <hr class="mt-0 mb-1">
                                <table class="table table-xs table-hover table-bordered mt-0" id="siTable">
                                    <thead>
                                        <tr class="bg-table-header">
                                            <th width="2%">#</th>
                                            <th >Store Indent Ref. No.</th>
                                            <th >Store Indent Date</th>
                                            <th >Store Indent Quantity / Items</th>
                                            <th > PO Status</th>
                                            <th width="10%" style="text-align: center;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
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

</html>