<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");
require_once("inc/common/css-js.php");

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

if (isset($_POST['GET_ATTN'])) {
    header("location:attendance_new.php?month_id=" . $_REQUEST['month_id'] . "&atten_year=" . $_REQUEST['atten_year']);
    die();
}



if ($_REQUEST['atten_year'] == '') {
    $atten_year = date('Y');
} else {
    $atten_year = $_REQUEST['atten_year'];
}
if ($_REQUEST['month_id'] == '') {
    $month_id = date('m');
} else {
    $month_id = $_REQUEST['month_id'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>
        <?php echo PAGE_TITLE; ?>-Attendance for Month
    </title>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <?php include_once("inc/common/css-js.php"); ?>
</head>

<body>
    <?php include("inc/common/header.php") ?>
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
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
                            <a href="#" class="breadcrumb-item">Attendance and Salary</a>
                            <span class="breadcrumb-item active">Attendance Log Report</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>

            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">

                        <form name="thisform" class="form-horizontal" method='POST' action="attendance_new.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                            <fieldset>
                                <div class="card">
                                    <div class="card-header bg-pgheader text-white header-elements-inline">
                                        <h6 class="card-title">Attendance Log Report</h6>
                                        <div class="header-elements">
                                            <div class="list-icons">
                                                <a class="list-icons-item" href="import_attendance.php" title="Import Attendance Log"><i class="icon-arrow-left52 mr-2"></i></a> 
                                                <a class="list-icons-item" data-action="fullscreen"></a>
                                            </div>

                                        </div>
                                        <!-- <button type="button" class="btn btn-primary">Primary</button>
                                        <button type="button" class="btn btn-primary">Primary</button> -->
                                    </div>
                                    <div class="card-body pt-2" id="">

                                        <div class="form-group row">
                                            <div class="col-lg-12 row">
                                                <div class="col-lg-8 p-2">
                                                    <div class="row">
                                                        <div class="col-lg-3">
                                                            <select name="atten_year" id="atten_year" class="select">
                                                                <?php for ($y = date('Y') - 1; $y < (date('Y') + 1); $y++) {
                                                                    echo '<option value="' . $y . '">' . $y . '</option>';
                                                                } ?>
                                                            </select>
                                                            <script>
                                                                document.thisform.atten_year.value = "<?php echo $atten_year ?>";
                                                            </script>
                                                        </div>

                                                        <div class="col-lg-3">
                                                            <select name="month_id" id="month_id" class="select">
                                                                <option value="">--Select Month--</option>
                                                                <?php
                                                                $dbconn = new dbhandler();
                                                                echo $dbconn->fnFillComboFromTable_Where("month_id", "month_name", "mst_atten_month", "month_id", "WHERE month_status = 1") ?>
                                                            </select>

                                                        </div>
                                                        <script>
                                                            document.thisform.month_id.value = "<?php echo $_REQUEST['month_id']; ?>";
                                                        </script>

                                                        <div class="col-lg-2">
                                                            <INPUT class="btn btn-info" type="submit" name="GET_ATTN" id="GET_ATTN" value="GET">
                                                        </div>
                                                    </div>


                                                </div>
                                                <div class="col-lg-4 p-2">
                                                    <div class="row">
                                                        <div class="col-lg-6" style="text-align:right;">
                                                        <a href="attendance_report.php"><INPUT class="btn btn-info" type="button" name="" id="" value="Attendance Report"></a>
                                                        </div>
                                                        <div class="col-lg-6" style="text-align:right;">
                                                            <a href="import_attendance.php"><INPUT class="btn btn-info" type="button" name="" id="" value="Import Attendance Log"></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                                
                                            </div>
                                        </div>
                                        <hr>
                                        <!-- <div class="form-group row ">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Search</label>
                                                    <input type='text' class="form-control" id='search_emp_att_dets' name="search_emp_att_dets" placeholder='Search Employee Code / Name / Check in /Check Out / Shift...'>
                                                </div>
                                            </div>
                                        </div>
                                        <hr> -->
                                        <?php
                                        $dateObj   = DateTime::createFromFormat('!m', $month_id);
                                        $monthName = $dateObj->format('F');
                                        ?>
                                        <H6>
                                            <center><b> Attendance Log for <?php echo $monthName . ' - ' . $atten_year; ?></b></center>
                                        </H6>
                                        <div class="table-responsive">
                                            <table class="table table-xs table-hover table-bordered mt-0 " style="font-size: small !important;" id="emp_work_dets_lst">
                                                <thead>
                                                    <tr class="bg-table-header">
                                                        <th width="">#</th>
                                                        <th>Employee Code</th>
                                                        <th>Employee Name</th>
                                                        <th>Date </th>
                                                        <th>Check in</th>
                                                        <th>Check out</th>
                                                        <th>W.Hrs</th>
                                                        <th>B.Hrs</th>
                                                        <th>Late in</th>
                                                        <th>Early out</th>
                                                        <th>OT Hrs</th>
                                                        <th class="text-center">Shift</th>
                                                        <th class="text-center" width="">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>

                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
            <?php include("inc/common/footer.php") ?>
        </div>

    </div>
    <?php include("modal_employee_atta_det.php") ?>
</body>

<script language="javascript" type="text/javascript">
    <?php
    if (isset($_SESSION['_msg']) && $_SESSION['_msg'] != "") {
        echo "$.jGrowl('" . $_SESSION['_msg'] . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-success', position: 'top-right', life:'2000', header: 'Success!' });";
        $_SESSION['_msg'] = "";
    }
    if (isset($_SESSION['_msg_err']) && $_SESSION['_msg_err'] != "") {
        echo "$.jGrowl('" . $_SESSION['_msg_err'] . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-danger', position: 'top-right', shutdown:'3000', header: 'Error!' });";
        $_SESSION['_msg_err'] = "";
    }
    ?>

    var dataTable = $('#emp_work_dets_lst').DataTable({

        
        // dom: '<"datatable-searching placeholder-left"lp>',
        // "dom":' <"search"f><"top"l>rt<"bottom"ip><"clear">',
        'processing': true,
        "language": {
            processing: '<i class="icon-spinner spinner mr-2"></i>Loading...',
            searchPlaceholder: "Search Employee Code / Name / Date / ",
            "sSearch": "<span>Search :</span>_INPUT_",
        },
        'serverSide': true,
        'serverMethod': 'post',
        'lengthChange': true, // Remove default Page Length Control
        'searching': true, // Remove default Search Control
        "pageLength": 25,
        "order": [
            [1, "asc"]
        ],


        'ajax': {
            'url': 'inc/datatable/ajaxEmployeeWorkDetsList.php',
            'data': function(data) {



                // var search_emp_att_dets = $('#search_emp_att_dets').val();
                var atten_year = $('#atten_year').val();
                var month_id = $('#month_id').val();
                // alert(month_id);

                // data.search_emp_att_dets = search_emp_att_dets;
                data.atten_year = atten_year;
                data.month_id = month_id;
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
                data: 'work_date'
            },
            {
                data: 'check_in'
            },

            {
                data: 'check_out'
            },
            {
                data: 'work_time'
            },
            {
                data: 'break_time'
            },
            {
                data: 'late_in'
            },
            {
                data: 'early_out'
            },
            {
                data: 'ot_hrs'
            },

            {
                data: 'shift_name'
            },

            {
                data: 'action'
            },
        ],

        columnDefs: [{
                orderable: false,
                targets: [0, 10, 12]
            },
            {
                targets: [11, 12],
                className: 'text-center'
            },
            {
                targets: [],
                className: 'text-right'
            },
        ],

    });
    // $('#search_emp_att_dets').change(function() {
    //     dataTable.draw();
    // });

//     $('#search').on( 'keyup', function () {
//     table.search( this.value ).draw();
// } );

    $('#GET_ATTN').click(function() {
        dataTable.draw();
    });

    $('#GET_ATTN').click(function() {
        // dataTable.draw();
        var atten_year = $('#atten_year').val();
        var month_id = $('#month_id').val();
        // alert(atten_year);
        if (month_id == '') {
            alert("Please Select the Month... !");
            return false;
        }
    });

    $('#modalEmplyeeAttaDets').on('show.bs.modal', function(e) {
        var id = $(e.relatedTarget).data('id');
        var atta_date = $(e.relatedTarget).data('atta_date');
        // alert(atta_date);
        // alert(id);
        if (id != '') {
            $.ajax({
                type: 'post',
                url: 'inc/cis_ajax/jquery_modal_employee_atta_det.php',
                data: {'id': id,
                    'atta_date': atta_date
                },
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
<!-- Footer -->