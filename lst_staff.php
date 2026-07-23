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
        <?php echo PAGE_TITLE; ?> - Staff List
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
                            <span class="breadcrumb-item active">Staff List</span>
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
                                <h6 class="card-title">List of Staff</h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" href="mst_employee_add.php" data-popup='tooltip' title="New Employee"><i class="icon-plus-circle2 mr-2"></i></a>
                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-2">
                                <div class="form-group row pl-2">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Search</label>
                                            <input type='text' class="form-control" id='search_staff_dets' name="search_staff_dets" placeholder='Enter  Code /  Name /  Mobile No.'>
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
                                            <label>Department</label>

                                            <select name="searchByDepartment" id="searchByDepartment" class="form-control form-control-select2">
                                            <option value="">-- All Department --</option>
                                                <?php
                                                echo $dbconn->fnFillComboFromTable_Where("department_id", "department_name", "mst_department", "department_id", " WHERE rec_del_status = '1'") ?>
                                            </select>
                                            <script>document.getElementById('searchByDepartment').value="<?php echo $searchByDepartment; ?>";</script>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Designation</label>

                                            <select name="searchByDesignation" id="searchByDesignation" class="form-control form-control-select2">
                                            <option value="">-- All Designation --</option>
                                                <?php
                                                echo $dbconn->fnFillComboFromTable_Where("designation_id", "designation_name", "mst_designation", "designation_id", " WHERE rec_del_status = '1'") ?>
                                            </select>
                                            <script>document.getElementById('searchByDesignation').value="<?php echo $searchByDesignation; ?>";</script>
                                        </div>
                                    </div>
                                    
                                </div>
                                <!-- <hr class="mt-0 mb-1"> -->
                                <table class="table table-xs table-hover table-bordered mt-0" id="emp_list">
                                    <thead>
                                        <tr class="bg-table-header">
                                            <th width="2%">#</th>
                                            <th>Staff Code</th>
                                            <th>Staff Name</th>
                                            <th>Department </th>
                                            <th>Designation</th>
                                            <th>Mobile</th>
                                            <th class="text-center" width="15%">Actions</th>
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

        var dataTable = $('#emp_list').DataTable({
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
                'url': 'inc/datatable/ajaxStaffList.php',
                'data': function(data) {


                    var search_staff_dets = $('#search_staff_dets').val();
                    var searchByBranch = $('#searchByBranch').val();
                    var searchByDepartment = $('#searchByDepartment').val();
                    var searchByDesignation = $('#searchByDesignation').val();

                    data.search_staff_dets = search_staff_dets;
                    data.searchByBranch = searchByBranch;
                    data.searchByDepartment = searchByDepartment;
                    data.searchByDesignation = searchByDesignation;  
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
                    data: 'department_name'
                },
                {
                    data: 'designation_name'
                },

                {
                    data: 'emp_mobile'
                },

                {
                    data: 'action'
                },
            ],

            columnDefs: [{
                    orderable: false,
                    targets: [0, 6]
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
        $('#search_staff_dets').keyup(function() {
            dataTable.draw();
        });
        $('#searchByBranch').change(function() {
            dataTable.draw();
        }); 
        $('#searchByDepartment').change(function() {
            dataTable.draw();
        }); 
        $('#searchByDesignation').change(function() {
            dataTable.draw();
        });

    });

    // $(".delete").click(function(e){
    $(document).on('click','.delete',function(e){
        e.preventDefault();
        if (confirm("Are you sure, you want to delete this Employee?")) {

            var id = $(this).attr('rel');
            // alert(id);
            var table = "mst_employee";
            var status = "rec_del_status";
            var rec_del_by = "rec_del_by";
            var rec_del_dtm = "rec_del_dtm";
            var value = "0";
            var where = "emp_id";

            var nRow = $(this).parents('tr')[0];

            $.ajax({
                type: 'post',
                url: 'inc/cis_ajax/jquery_delete_records_hr.php',
                data: {
                    "id": id,
                    "table": table,
                    "status": status,
                    "value": value,
                    "rec_del_by": rec_del_by,
                    "rec_del_dtm": rec_del_dtm,
                    "where": where
                },
                beforeSend: function() {
                    //launchpreloader();
                },
                complete: function() {
                    $.jGrowl('GSM deleted..!', { sticky: false, theme: 'growl-success',shutdown:'0.5', header: 'Success!' });
                },
                success: function(result) {
                    // alert(result);
                    window.location.href = "lst_staff.php";

                }
            });

        }
    });
    $('#modalEmplyeeDets').on('show.bs.modal', function (e) {
                var id = $(e.relatedTarget).data('id');
                // alert(id);
            // alert(id);
                if(id !=''){
                    $.ajax({
                        type : 'post',
                        url : 'inc/cis_ajax/jquery_modal_employee_det.php', 
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