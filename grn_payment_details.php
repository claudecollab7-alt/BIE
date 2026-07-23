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

$searchByBranch = '1';
$searchByYear = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <title>
        <?php echo PAGE_TITLE; ?> - GRN Payment List
    </title>
    <?php include_once("inc/common/css-js.php"); ?>
</head>

<body>
    <!-- Main navbar -->
    <?php include("inc/common/header.php") ?>
    <div class="page-content">
        <!-- Main sidebar -->
        <?php include("inc/common/sidebar.php") ?>
        <div class="content-wrapper">

            <div class="page-header">
                <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                    <div class="d-flex">
                        <div class="breadcrumb">
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Home</a>
                            <a href="#" class="breadcrumb-item"> Accounts </a>
                            <span class="breadcrumb-item active">GRN Payment List</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>
            <?php include("modal_sales_det.php") ?>
            <!-- Content area -->
            <div class="content pt-0">
                <!-- Dashboard content -->
                <div class="row">
                    <div class="col-md-12">
                        <!-- Basic datatable -->
                        <div class="card">
                            <div class="card-header bg-pgheader text-white header-elements-inline">
                                <h6 class="card-title">List of GRN Payments </h6>
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
                                            <input type='text' class="form-control" id='searchByCode' name="searchByCode" placeholder='Grn No. / Supplier Name '>
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
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select name="searchByStatus" id="searchByStatus" class="form-control form-control-select2 js-basic-single">
                                                <option value="">-- Select Status--</option>
                                                <option value="0">Not Yet</option>
                                                <option value="1">Pending</option>
                                                <option value="2">Partial Payment</option>
                                                <option value="3">Completed</option>
                                            </select>
                                            <script>
                                                document.getElementById('searchByStatus').value = "<?php echo $searchByStatus; ?>";
                                            </script>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Financial Year</label>
                                            <select name="searchByYear" id="searchByYear" class="form-control form-control-select2">
                                                <option value="">-- All Financial Year--</option>
                                                <?php
                                                $dbconn = new dbhandler();
                                                echo $dbconn->fnFillComboFromTable_Where("finyr", "finyr", "mst_finyear", "finyr", " WHERE rec_del_status = '0'") ?>
                                            </select>
                                            <script>
                                                document.getElementById('searchByYear').value = "<?php echo $searchByYear; ?>";
                                            </script>
                                        </div>
                                    </div>
                                </div>
                                <hr class="mt-0 mb-1">
                                <table class="table table-xs table-hover table-bordered mt-0" id="DcTable">
                                    <thead>
                                        <tr class="bg-table-header">
                                            <th width="5px">#</th>
                                            <th>GRN No</th>
                                            <th>GRN Date</th>
                                            <th>Supplier Name</th>
                                            <th style="text-align:center"> Party Bill No</th>
                                            <th style="text-align:center"> Party Bill Date</th>
                                            <th style="text-align:center"> Bill Due Date</th>
                                            <th style="text-align:center"> Bill Amount</th>
                                            <th style="text-align:center"> Paid Amount</th>
                                            <th style="text-align:center">Bill Status</th>
                                            <th style="text-align:center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                                <input type="hidden" name="iPageNum" value="<?php echo $iPageNum ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
<!-- Include DataTables library -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="custom.js"></script>
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

        var dataTable = $('#DcTable').DataTable({

            dom: '<"datatable-header length-left"lp><"datatable-scroll"rt><"datatable-footer"ip>',
            'processing': true,
            "language": {
                processing: '<i class="icon-spinner spinner mr-2"></i>Loading...'
            },
            'serverSide': true,
            'serverMethod': 'post',
            'lengthChange': true,
            'searching': false,
            "pageLength": 25,
            "order": [
                [2, "desc"]
            ],

            'ajax': {
                'url': 'inc/datatable/ajaxGrnPaymentList.php',
                'data': function(data) {
                    var dc = $('#searchByCode').val();
                    var searchByBranch = $('#searchByBranch').val();
                    var searchByYear = $('#searchByYear').val();
                    var searchByStatus = $('#searchByStatus').val();

                    data.searchByCode = dc;
                    data.searchByBranch = searchByBranch;
                    data.searchByYear = searchByYear;
                    data.searchByStatus = searchByStatus;

                }
            },

            'columns': [

                {
                    data: 'sno'
                },
                {
                    data: 'grn_slno'
                },
                {
                    data: 'grn_date'
                },
                {
                    data: 'supp_id'
                },
                {
                    data: 'party_bill_no'
                },
                {
                    data: 'party_bill_date'
                },
                {
                    data: 'no_item'
                },
                {
                    data: 'bill_amt'
                },
                {
                    data: 'paid_amt'
                },
                {
                    data: 'pay_status'
                },
                {
                    data: 'action'
                },
            ],

            columnDefs: [{
                    orderable: false,
                    targets: [0, 4, 5, 6, 7, 8, 9, 10]
                },
                {
                    targets: [0, 1, 2, 4, 5, 6, 7, 8, 9, 10],
                    className: 'text-center'
                },

            ],
        });

        // $('#searchByCode').keyup(function() {
        //     dataTable.draw();

        // });

        // $('#searchByBranch').change(function() {
        //     dataTable.draw();
        // }); 
        // $('#searchByYear').change(function() {
        //     dataTable.draw();
        // }); 
        // $('#searchByStatus').change(function() {
        //     dataTable.draw();
        // }); 
        $('#searchByCode, #searchByBranch, #searchByYear, #searchByStatus').on('keyup change', function() {
            dataTable.draw();
        });




    });

    $('#modalSalesDets').on('show.bs.modal', function(e) {
        var id = $(e.relatedTarget).data('id');
        var so_slno = $('#so_slno').html();
        var inv_netamt = $('#inv_netamt').html();
        //    alert(id);
        if (id != '') {
            $.ajax({
                type: 'post',
                url: 'inc/cis_ajax/jquery_modal_sales_det.php',
                data: 'id=' + id,
                success: function(data) {
                    // alert(data);
                    string = data.split("~");
                    //  $('#m_sales_amt').html(string[2]);
                    // $('#m_sales_code').html(data);
                    $('#m_sales_code').html(string[1]);
                    $('#m_sales_rec').html(string[0]);

                }
            });
        }
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {

        // Store a reference to the original send method
        var oldSend = XMLHttpRequest.prototype.send;

        // Override the send method
        XMLHttpRequest.prototype.send = function() {
            // You can add your custom logic here before calling the original send method

            // Call the original send method with the arguments it was called with
            return oldSend.apply(this, [].slice.call(arguments));
        };


        var table = $('#DcTable').DataTable();

        // Check if a status value is selected
        <?php if ($searchByStatus != ''): ?>
            var statusValue = '<?php echo $searchByStatus; ?>';
            var columnIndex = 4; // Assuming the status column index is 8
            var columnValue = statusValue; // Set the column search value based on $searchByStatus

            // For example, if you want to apply the search condition differently based on status values:
            if (statusValue == '0') {
                // Apply specific search condition for status 0
                columnValue = 'Not Yet'; // Update with your desired search value
            } else if (statusValue == '1') {
                // Apply specific search condition for status 1
                columnValue = 'Pending'; // Update with your desired search value
            } else if (statusValue == '2') {
                // Apply specific search condition for status 2
                columnValue = 'Partial Payment'; // Update with your desired search value
            } else if (statusValue == '3') {
                // Apply specific search condition for status 3
                columnValue = 'Completed'; // Update with your desired search value
            } // Add additional conditions if necessary

            // Apply the status filter and redraw the table
            table.column(columnIndex).search(columnValue).draw();
        <?php endif; ?>
    });
</script>