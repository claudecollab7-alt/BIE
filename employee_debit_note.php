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
// $searchByBranch='1';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <title>
        <?php echo PAGE_TITLE; ?> - Employee Debit Note
    </title>
    <!-- <link href="css/main.css" rel="stylesheet" type="text/css" /> -->
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
        <!-- Main content -->
        <div class="content-wrapper">
            <!-- Page header -->
            <div class="page-header">
                <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                    <div class="d-flex">
                        <div class="breadcrumb">
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Home</a>
                            <a href="#" class="breadcrumb-item"> HR Management</a>
                            <span class="breadcrumb-item active">Employee Debit Note</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>
            <!-- Content area -->
            <div class="content pt-0">
                <!-- Dashboard content -->
                <div class="row">
                    <div class="col-md-12">
                        <!-- This Form UI Starts here --->
                        <!-- Basic datatable -->
                        <div class="card">
                            <div class="card-header bg-pgheader text-white header-elements-inline">
                                <h6 class="card-title">Employee Debit Note</h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-2">
                                <div class="form-group row pl-2">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Search</label>
                                            <input type='text' class="form-control" id='search_emp_debit' name="search_emp_debit" placeholder='Enter Employee Name / Employee Code'>
                                        </div>
                                    </div>
                                    <?php if ($_SESSION['_user_branch'] == 1) { ?>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Branch</label>
                                                <select name="searchByBranch" id="searchByBranch" class="form-control form-control-select2">
                                                    <option value="">-- All Branch--</option>
                                                    <?php
                                                    $dbconn = new dbhandler();
                                                    echo $dbconn->fnFillComboFromTable_Where("branch_id", "branch_name", "mst_branch", "branch_id", " WHERE branch_status = '1'") ?>
                                                </select>
                                                <script>
                                                    document.getElementById('searchByBranch').value = "<?php echo $searchByBranch; ?>";
                                                </script>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Employee Type</label>
                                            <select name="searchByEmployeeType" id="searchByEmployeeType" class="form-control-select2">
                                                <option value="">-- All Employee Type --</option>
                                                <option value="1">Staff</option>
                                                <option value="2">Labour</option>
                                                <option value="3">Others</option>
                                            </select>
                                            <script>document.getElementById('searchByEmployeeType').value="<?php echo $searchByEmployeeType; ?>";</script>
                                        </div>
                                    </div>
                                </div>
                                <!-- <hr class="mt-0 mb-1"> -->
                                <table class="table table-xs table-hover table-bordered mt-0" id="emp_sal">
                                    <thead>
                                        <tr class="bg-table-header">
                                            <th>#</th>
                                            <th>Employee Code</th>
                                            <th>Employee Name</th>
                                            <th>Debit Amount</th>
                                            <th>Return Amount</th>
                                            <th>Balance Amount</th>
                                            <th class="text-center" width="10%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                                <input type="hidden" name="iPageNum" value="<?php echo $iPageNum ?>">
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
    <?php include("modal_employee_det.php") ?>
</body>

</html>
<script type="text/javascript">
    //----------------ajax table----------------//




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

        var dataTable = $('#emp_sal').DataTable({
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
                [1, "asc"]
            ],


            'ajax': {
                'url': 'inc/datatable/ajaxEmployeeDebit.php',
                'data': function(data) {


                    var search_emp_debit = $('#search_emp_debit').val();
                    var searchByBranch = $('#searchByBranch').val();
                    var searchByEmployeeType = $('#searchByEmployeeType').val();

                    data.search_emp_debit = search_emp_debit;
                    data.searchByBranch = searchByBranch;
                    data.searchByEmployeeType = searchByEmployeeType;
                }
            },

            'columns': [

                {
                    data: 'sno'
                },
                {
                    data: 'emp_code'
                },
                {
                    data: 'emp_name'
                },
                {
                    data: 'debit_amount'
                },
                {
                    data: 'return_amount'
                },

                {
                    data: 'balance_amount'
                },
                {
                    data: 'action'
                },
            ],

            columnDefs: [{
                    orderable: false,
                    targets: [0,3,4,5,6]
                },
                {
                    targets: [6],
                    className: 'text-center'
                },
                {
                    targets: [],
                    className: 'text-right'
                },
            ],

        });
        $('#search_emp_debit').keyup(function() {
            dataTable.draw();
        });
        $('#searchByBranch').change(function() {
            dataTable.draw();
        });
        $('#searchByEmployeeType').change(function() {
            dataTable.draw();
        }); 
    }); 
</script>