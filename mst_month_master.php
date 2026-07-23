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

// $auto_id = "";
// $month = "";
// $year = "";
// if (isset($_REQUEST['auto_id'])) {
//     $result = $conn->query("SELECT * FROM tbl_month_master WHERE month_master_status = '1' AND auto_id = " . $_REQUEST['auto_id']);
//     if ($result->rowCount() > 0) {
//         $obj = $result->fetch(PDO::FETCH_OBJ);
//         $auto_id = $obj->auto_id;
//         $month = $obj->month;
//         $year = $obj->year;
//     }
// }

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>
        <?php echo PAGE_TITLE; ?>-Month Master
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
                            <a href="#" class="breadcrumb-item">Masters</a>
                            <span class="breadcrumb-item active">Month Master</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>

            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">

                        <form name="thisform" class="form-horizontal" method='POST' action="mst_month_master.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                            <fieldset>
                                <div class="card">
                                    <div class="card-header bg-pgheader text-white header-elements-inline">
                                        <h6 class="card-title">Pay Roll Months</h6>
                                        <div class="header-elements">
                                            <div class="list-icons">
                                            <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                                <a class="list-icons-item" href="mst_month_master_add.php" data-popup='tooltip' title="New Month"><i class="icon-plus-circle2 mr-2"></i></a>
                                                <a class="list-icons-item" data-action="fullscreen"></a>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- </div> -->
                                    <div class="card-body">
                                        <table class="datatable-co13 table table-xs table-hover table-bordered" id="lst_table" style="font-size: small !important;">
                                            <thead class="bg-table-header">
                                                <tr>
                                                    <th width="">#</th>
                                                    <th width="">Year</th>
                                                    <th width="">Month</th>
                                                    <th width="">Total Days</th>
                                                    <th width="">Working Days</th>
                                                    <th width="">Holidays</th>
                                                    <th width="">Function Holidays</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $SQL = "SELECT * FROM tbl_month_master WHERE month_master_status = '1' ORDER BY auto_id DESC";
                                                $result = $conn->query($SQL);

                                                if ($result->rowCount() > 0) {
                                                    $Sno = 1;

                                                    while ($obj = $result->fetch()) {
                                                        $year = $obj->year;

                                                        $dateObj   = DateTime::createFromFormat('!m', $obj->month);
                                                        $monthName = $dateObj->format('F');
                                                        echo '<tr>
                                                                <td>' . $Sno . '</td>
                                                                <td>' . $year . '</td>
                                                                <td>' . $monthName . '</td>
                                                                <td align="center">' . $obj->total_days . '</td>
                                                                <td align="center">' . $obj->working_days . '</td>
                                                                <td align="center">' . $obj->holidays . '</td>
                                                                <td align="center">' . $obj->function_holidays . '</td>
                                                                <td align="center">
                                                                <a href="mst_month_master_add.php?auto_id=' . $obj->auto_id . '"  data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit mr-2"></i></a>';
                                                        if ($_SESSION['_user_type'] == 'S' || $_SESSION['_user_type'] == 'A') {
                                                            echo '<a  href="javascript:;" class="delete" rel="' . $obj->auto_id . '" data-popup="tooltip" title="Delete">
                                                                                <i class="icon-bin bg-delete mr-2"></i></a>';
                                                        } else {
                                                            echo "<a href='javascript:;' title='Delete'><i class='icon-bin bg-delete mr-2'></i></a>";
                                                        }
                                                        echo '</td>
                                                            </tr>';
                                                        $Sno++;
                                                    }

                                                    $obj = null;
                                                }
                                                ?>
                                            </tbody>
                                        </table>
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

</body>

<script language="javascript" type="text/javascript">
    // var txtHid = $("#txtHid").val();

    // // if (txtHid != "") {
    //     $('#toogle').trigger('click');
    // // };

    // $("#toogle").click(function() {
    //     $("#toogleform").toggle();
    // });

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

        oTable = $('#lst_table').dataTable({
            "bJQueryUI": false,
            "bAutoWidth": false,
            "bStateSave": false,
            "sPaginationType": "full_numbers",
            "iDisplayLength": 25,
            "sDom": '<"datatable-header"fl>t<"datatable-footer"ip>',
            "oLanguage": {
                "sSearch": "<span>Search:</span> _INPUT_",
                "sLengthMenu": "<span>Show Records:</span> _MENU_",
                "oPaginate": {
                    "sFirst": "First",
                    "sLast": "Last",
                    "sNext": ">",
                    "sPrevious": "<"
                }
            },
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [0,1,2,3,4,5,6,7]
            }]
        });

        $('#lst_table').on('click', 'a.delete', function(e) {
            e.preventDefault();
            if (confirm("Are you sure, you want to delete this Month details?")) {

                var id = $(this).attr('rel');
                var table = "tbl_month_master";
                var status = "month_master_status";
                var value = "0";
                var where = "auto_id";

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
                            $('#lst_table').dataTable().fnDeleteRow(nRow);
                            $.jGrowl('Month deleted..!', {
                                sticky: false,
                                theme: 'growl-success',
                                shutdown: '0.5',
                                header: 'Success!',
                                position: 'bottom-right'
                            });
                        } else if (result == 0)
                            $.jGrowl('Month Not deleted..!', {
                                sticky: false,
                                theme: 'growl-error',
                                shutdown: '0.5',
                                header: 'Error!',
                                position: 'bottom-right'
                            });
                        else
                            $.jGrowl(result, {
                                sticky: false,
                                theme: 'growl-error',
                                shutdown: '0.5',
                                header: 'Error!',
                                position: 'bottom-right'
                            });

                    }
                });

            }
        });


    });


    // function fnValidate() {
    //     if (notSelected(document.thisform.year, "Year..")) {
    //         return false;
    //     }
    //     if (notSelected(document.thisform.month, "Month..")) {
    //         return false;
    //     }
    //     if (isNull(document.thisform.total_days, "Total Days..")) {
    //         return false;
    //     }
    //     if (isNull(document.thisform.working_days, "Working Days..")) {
    //         return false;
    //     }
    //     if (isNull(document.thisform.holidays, "Holidays..")) {
    //         return false;
    //     }
    //     if (isNull(document.thisform.function_holidays, "Function Holidays..")) {
    //         return false;
    //     }
    //     document.thisform.submit();
    // }
</script>
<!-- Footer -->