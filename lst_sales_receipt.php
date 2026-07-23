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
    <title>
        <?php echo PAGE_TITLE; ?> - Sales Order Receipt
    </title>
    <?php include_once("inc/common/css-js.php"); ?>
    <?php include("modal_supp_dets.php") ?>
    <?php include("modal_item_map.php") ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body>
    <!-- Main navbar -->
    <?php include("modal_supp_dets.php") ?>
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
                            <a href="#" class="breadcrumb-item">Work Area</a>
                            <span class="breadcrumb-item active">Sales Order Receipt</span>
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
                                <h6 class="card-title">List of Sales Orders in Accounts - Receipts</h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <!-- <a class="list-icons-item" href="quotation.php" data-popup='tooltip' title="New Quotation"><i class="icon-plus-circle2 mr-2"></i></a> -->
                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-2">
                                <div class="form-group row pl-2">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Search</label>
                                            <input type='text' class="form-control" id='searchByCode' name="searchByCode" placeholder='SO No. / Dealer Name / SO Date ( Y-m-d )'>
                                        </div>
                                    </div>
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
                                    <div class="col-md-3">
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
                                <table class="table table-xs table-hover table-bordered mt-0" id="receiptTable">
                                    <thead>
                                        <tr class="bg-table-header">
                                            <th>#</th>
                                            <th>SO No.</th>
                                            <th>SO Date</th>
                                            <th>Dealer Name</th>
                                            <th>Quotation</th>
                                            <th class="text-left" width="10%">SO Value</th>
                                            <th class="text-left" width="10%">Balance Value</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Actions</th>
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
    <?php include("modal_sales_det.php") ?>

</body>

</html>

<script type="text/javascript">
    //----------------ajax table----------------//

    $(document).ready(function() {
        var dataTable = $('#receiptTable').DataTable({
            
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
                'url': 'inc/datatable/ajaxReceiptlist.php',
                'data': function(data) {
                    var so = $('#searchByCode').val();
                    var searchByBranch = $('#searchByBranch').val();
                    var searchByYear = $('#searchByYear').val();

                    

                    data.searchByCode = so;
                    data.searchByBranch = searchByBranch;
                    data.searchByYear = searchByYear;


                }
            },

            'columns': [

                {
                    data: 'sno'
                },
                {
                    data: 'so_refno'
                },
                {
                    data: 'so_date'
                },
                {
                    data: 'supp_name'
                },

                {
                    data: 'quo_refno'
                },
                {
                    data: 'so_value'
                },
                {
                    data: 'bal_value'
                },
                {
                    data: 'so_status'
                },

                {
                    data: 'action'
                },
            ],

            columnDefs: [{
                    orderable: false,
                    targets: [0,5,7,8,6]
                },
                {
                    targets: [2,7,8],
                    className: 'text-center'
                },
                {
                    targets: [5,6],
                    className: 'text-right'
                },
            ],

        });
        $('#searchByCode').keyup(function() {
            dataTable.draw();
        });
        $('#searchByBranch').change(function() {
                dataTable.draw();
         });
        $('#searchByYear').change(function() {
            dataTable.draw();
        }); 
    });
    $('body').on('click', '.q', function() {
        var task = "SO";
        var dataId = $(this).attr('data-soid');
        // alert(dataId);
        $.ajax({
            type: 'post',
            url: 'so_verify.php',
            data: {
               "task":task,
               "dataId":dataId
            },
            beforeSend: function() {
                if (confirm('Are you sure, to Confirm Payment and Send SO for Verification?')) {} else {
                    return false;
                }
            },
            complete: function() {
                // alert(result);
            },
            success: function(result) {

                window.location.href = "lst_sales_order.php";
            }
        });
        return false;
    });

    $('#modalSalesDets').on('show.bs.modal', function (e) {
                var id = $(e.relatedTarget).data('id');
                
            // alert(id);
                if(id !=''){
                    $.ajax({
                        type : 'post',
                        url : 'inc/cis_ajax/jquery_modal_sales_det.php', 
                        data :  'id='+ id, 
                        success : function(data){
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

 