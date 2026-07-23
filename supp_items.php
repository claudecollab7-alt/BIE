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

if (isset($_POST['submit'])) {
    $item_id = $_POST['select_item'];
    foreach ($item_id as $key => $value) {
        $stmt = null;
        $stmt = $conn->prepare("INSERT INTO tbl_supp_items (supp_id, item_id) VALUES 
											(:supp_id, :item_id)");
        $data = array(
            ':supp_id' => $_REQUEST['hide_supp_id'],
            ':item_id' => $value
        );
        $stmt->execute($data);
    }
    header("location:lst_supplier.php");
    // if($_REQUEST['supp_id']!='')
    // {
    // 	header("location:supp_items.php?supp_id=".$_REQUEST['supp_id']);
    // }
    // else
    // {
    // 	header("location:supp_items.php");
    // }
    die();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Supplier Item Mapping</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <?php include_once("inc/common/css-js.php"); ?>

</head>

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

        $("#select_all").click(function() {
            $('input:checkbox').not(this).prop('checked', this.checked);
        });


        var dataTable = $('#lst_table1').DataTable({

            dom: '<"datatable-header length-left"lp><"datatable-scroll"rt><"datatable-footer"ip>',
            'processing': true,
            "language": {
                processing: '<i class="icon-spinner spinner mr-2"></i>Loading...'
            },
            'serverSide': true,
            'serverMethod': 'post',
            'lengthChange': true, // Remove default Page Length Control
            'searching': true, // Remove default Search Control
            "pageLength": 10,
            "order": [
                [0, "desc"]
            ],
            'ajax': {
                'url': 'inc/cis_ajax/jquery_save_supp_items_bal.php?id=' + $("#hide_supp_id").val(),
                'data': function(data) {
                    var so = $('#searchByCode').val();
                    

                    data.searchByCode = so;
                }
            },

            'columns': [{
                    data: 'item_id'
                },
                {
                    data: 'item_code'
                },
                {
                    data: 'action'
                },
            ],
            columnDefs: [{
                    orderable: false,
                    targets: [0, 1, 2]
                },
                {
                    targets: [2],
                    className: 'text-center'
                },
            ],

        });
        $('#searchByCode').keyup(function() {
            dataTable.draw();
        });

        // oTable = $('#lst_table1').DataTable({
        // var dataTable = $('#lst_table12').DataTable({

        //     "bJQueryUI": false,
        //     "bAutoWidth": false,
        //     "sPaginationType": "full_numbers",
        //     "iDisplayLength": 100,
        //     "processing": true,
        //     "serverSide": true,
        //     "sDom": '<"datatable-header"fl>t<"datatable-footer"ip>',
        //     "sAjaxSource": "inc/cis_ajax/jquery_save_supp_items_bal.php?id=" + $("#hide_supp_id").val(),
        //     "oLanguage": {
        //         "sSearch": "<span>Search:</span> _INPUT_",
        //         "sLengthMenu": "<span>Show Records:</span> _MENU_",
        //         "oPaginate": {
        //             "sFirst": "First",
        //             "sLast": "Last",
        //             "sNext": ">",
        //             "sPrevious": "<"
        //         }
        //     },
        //     "aoColumnDefs": [{
        //             "bSortable": false,
        //             "aTargets": [2]
        //         },
        //         {
        //             "sDefaultContent": '',
        //             aTargets: ['_all']
        //         }
        //     ],
        //     "aaSorting": [],
        //     "fnDrawCallback": function() {
        //         $('.tip').tooltip();

        //     },
        // });

    });

    function fnValidate() {

        document.thisForm.submit();

    }

    function remove_item(auto_id) {

        var supp_id = $('#supp_id').val();

        $.ajax({
            type: "POST",
            url: "inc/cis_ajax/jquery_save_supp_items.php",
            data: {
                auto_id: auto_id,
                supp_id: supp_id,
                mode: 'delete'
            }
        }).done(function(msg) {

            // alert(msg);
            $('#lst_table').html(msg);
            var n = msg.indexOf("tbody");

            location.reload();
        });

    }
</script>

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
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
                            <a href="#" class="breadcrumb-item"> Work Area</a>
                            <span class="breadcrumb-item active">Supplier Item Mapping</span>
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


                        <div class="card">
                            <div class="card-header bg-pgheader text-white header-elements-inline">
                                <h6>Supplier / Dealer Item Mapping</h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" href="lst_supplier.php" title="Supplier List"><i class="icon-arrow-left52 mr-2"></i></a>

                                        <a class="list-icons-item" data-action="fullscreen"></a>

                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <form name='thisForm' id="validate" class="form-horizontal" method='post' action="supp_items.php" onSubmit="return fnValidate();" enctype="multipart/form-data">
                                    <input type="hidden" name="hide_supp_id" id="hide_supp_id" value="<?php echo $_REQUEST['supp_id']; ?>">
                                    <fieldset>
                                        <div class="row-fluid well">
                                            <div class="form-group">
                                                <div class="col-lg-3">

                                                    <label class="control-label">Supplier / Dealer Name <span class="text-error"></span></label>
                                                    <div class="controls">
                                                        <select name="supp_id" id="supp_id" data-placeholder="Choose a Supplier Name.." class="select">
                                                            <option value="">Select Supplier Name</option>
                                                            <?php
                                                            $dbconn = new dbhandler();
                                                            echo $dbconn->fnFillComboFromTable_Where("supp_id", "supp_name", "mst_supplier_new", "supp_id", " WHERE supp_status = 1 ") ?>
                                                        </select>
                                                        <script>
                                                            document.thisForm.supp_id.value = "<?php echo $_REQUEST['supp_id']; ?>";
                                                        </script>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="row">

                                                    <div class="col-md-6">
                                                        <div class="card">
                                                            <div class="card-header bg-pgheader text-white header-elements-inline">
                                                                <h6 class="card-title">Supplier / Dealer Items</h6>
                                                                <div class="header-elements">
                                                                    <div class="list-icons">
                                                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="card-body pt-0">

                                                                <table class="datatable-col3 table table-xs table-hover table-bordered" id="lst_table36">
                                                                    <thead>
                                                                        <tr class="bg-table-header">
                                                                            <th width="50px">#</th>
                                                                            <th>Item Code</th>
                                                                            <th width="10%">Actions</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php

                                                                        $SQL = "SELECT * FROM tbl_supp_items WHERE supp_item_status = '1' AND supp_id = '" . $_REQUEST['supp_id'] . "'  ORDER BY item_id ASC";
                                                                        $result = $conn->query($SQL);

                                                                        if ($result->rowCount() > 0) {
                                                                            $Sno = 1;

                                                                            while ($obj = $result->fetch()) {

                                                                                $items = $dbconn->GetSingleReconrd("tbl_item_details", "CONCAT(item_desciption,' - ',item_code)", "item_status = '1' AND item_id", $obj->item_id);

                                                                                $del_link = '<li><a href="" class="tip delete" rel="' . $obj->supp_item_id . '"  title="Remove"><i class="fa fa-times-circle"></i></a></li>';

                                                                                echo '<tr >

                                                                                        <td>' . $Sno . '</td>
                                                                                        
                                                                                        
                                                                                        <td>' . $items . '</td>
                                                                                        
                                                                                        <td align="center"><a href="javascript:remove_item(' . $obj->supp_item_id . ');" class="" rel="' . $obj->supp_item_id . '"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a></td></td>
                                                                                    </tr>';
                                                                                $Sno++;
                                                                            }
                                                                        }

                                                                        ?>
                                                                    </tbody>

                                                                </table>

                                                                <script type="text/javascript">
                                                                    //remove_item(0);
                                                                </script>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="card">
                                                            <div class="card-header bg-pgheader text-white header-elements-inline">
                                                                <h6 class="card-title">Remaining Items</h6>
                                                                <div class="header-elements">
                                                                    <div class="list-icons">
                                                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="card-body pt-0">
                                                                <div class="col-md-5"><br>
                                                                    
                                                                        <label>Search:</label><input type='text' class="form-control" id='searchByCode' name="searchByCode" placeholder='Item Description / Item Code'>
                                                                    
                                                                </div>
                                                                    <table class="table table-striped table-bordered table-hover" id="lst_table1">
                                                                        <thead>
                                                                            <tr class="bg-table-header">
                                                                                <th width="50px">#</th>
                                                                                <th>Item Code</th>
                                                                                <th width="5%"><input type="checkbox" class="select_all styled" name="select_all" id="select_all"></th>
                                                                            </tr>
                                                                        </thead>
                                                                    </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                            </div>
                                    <div class="card-footer text-center pt-2">
                                        <INPUT class="btn btn-info" type="submit" name="submit" value="Assign to Supplier">
                                        <INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:window.location.href='lst_supplier.php'">
                                    </div>
                                </form>
                           
                           
                        </div>


                        <!-- End of This Form UI  --->
                    </div>
                </div>
                <!-- /dashboard content -->

            </div>
            <?php include("inc/common/footer.php") ?>
        </div>

    </div>

</body>

<script type="text/javascript">
    //var table = $('#lst_table1').dataTable().trigger();
    // table.button( '2-1' ).trigger();
    $(function() {
        //remove_item(0);
    });
</script>

</html>