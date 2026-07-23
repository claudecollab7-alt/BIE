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



$searchByBranch='1';
$searchByYear=$dbconn->GetSingleReconrd("mst_finyear","finyr","finyr_active",1);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <title><?php echo PAGE_TITLE; ?> - Invoice List</title>
   
    <!--[if IE 8]><link href="css/ie8.css" rel="stylesheet" type="text/css" /><![endif]-->

    <?php include_once("inc/common/css-js.php"); ?>


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

            var dataTable = $('#invoice_table').DataTable({

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
                    [1, "DESC"]
                ],
                'ajax': {
                    'url': 'inc/datatable/ajaxInvoiceList.php',
                    'data': function(data) {
                        // Read values
                        var supp = $('#searchByCode').val();
                        var searchBySupp = $('#searchBySupp').val();
                        var searchByInv = $('#searchByInv').val();
                        var searchByBranch = $('#searchByBranch').val();
                        var searchByYear = $('#searchByYear').val();

                        // Append to data
                        data.searchByCode = supp;
                        data.searchBySupp = searchBySupp;
                        data.searchByInv = searchByInv;
                        data.searchByBranch = searchByBranch;
                        data.searchByYear = searchByYear;

                    }
                },
                'columns': [{
                        data: 'inv_slno'
                    },
                    {
                        data: 'inv_id'
                    },
                    {
                        data: 'itm_inv_date'
                    },
                    {
                        data: 'supp_name'
                    },
                    {
                        data: 'itm_inv_amt'
                    },
                    {
                        data: 'itm_inv_paymode'
                    },
                    {
                        data: 'itm_inv_Due'
                    },
                     
                    {
                        data: 'invoice_type'
                    },
                    {
                        data: 'action'
                    },
                ],

                columnDefs: [{
                        orderable: false,
                        targets: [0, 2, 4, 5, 6, 8]
                    },
                    {
                        targets: [8],
                        className: 'text-center'
                    },
                    {
                        targets: [4,6],
                        className: 'text-right'
                    },
                ],

            });

            $('#searchByCode').keyup(function() {
                dataTable.draw();
            });
             $('#searchBySupp').change(function() {
                dataTable.draw();
            });
            $('#searchByInv').change(function() {
                dataTable.draw();
            }); 
            $('#searchByBranch').change(function() {
                dataTable.draw();
            }); 
            $('#searchByYear').change(function() {
                dataTable.draw();
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
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Dashboard</a>
                            <a href="#" class="breadcrumb-item"> Work Area</a>
                            <span class="breadcrumb-item active">Invoice </span>
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
                                <h6 class="card-title">List of Invoice </h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" href="mng_invoice.php" data-popup='tooltip' title="New invoice"><i class="icon-plus-circle2 mr-2"></i></a>
                                        <a class="list-icons-item" data-action="fullscreen"></a>

                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-2">
                                <div class="form-group row pl-2">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Search </label>
                                            <input type='text' class="form-control" id='searchByCode' name="searchByCode" placeholder='Enter Invoice No'>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Customer</label>
                                            <select name="searchBySupp" id="searchBySupp" class="form-control form-control-select2">
                                            <option value="">-- Select Customer--</option>
                                                <?php
                                                $dbconn = new dbhandler();
                                                echo $dbconn->fnFillComboFromTable_Where("supp_id", "supp_name", "mst_supplier_new", "supp_id", " WHERE supp_status = '1' AND supp_type = 'C'") ?>
                                            </select>
                                            <script>document.getElementById('searchBySupp').value="<?php echo $searchBySupp; ?>";</script>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Invoice Type</label>
                                            <select name="searchByInv" id="searchByInv" class="form-control form-control-select2">
                                            <option value="">-- Select Invoice Type--</option>
                                            <option value="0">Cash Invoice</option>
                                            <option value="1">Credit Invoice</option>
                                            </select>
                                            <script>document.getElementById('searchByInv').value="<?php echo $searchByInv; ?>";</script>
                                        </div>
                                    </div>
                                    <?php if($_SESSION['_user_branch']==1){ ?>
                                    <div class="col-md-2">
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
                                    <table class="table table-xs table-hover table-bordered mt-0" id="invoice_table">
                                        <thead>
                                            <tr class="bg-table-header">
                                                <th width="50px">#</th>
                                                <th>Invoice No</th>
                                                <th>Invoice Date</th>
                                                <th>Customer Name</th>
                                                <th>Invoice Amount</th>
                                                <th>Paymode</th>
                                                <th>Invoice Due</th>
                                                <th>Invoice Type</th>
                                                <th width="15%">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include("inc/common/footer.php") ?>
        </div>
    </div>


</body>

</html>