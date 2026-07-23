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
    <title><?php echo PAGE_TITLE; ?> - Item Details</title>
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
            var dataTable = $('#itemDetailsTable').DataTable({

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
                    [1, "desc"]
                ],
                'ajax': {
                    'url': 'inc/datatable/ajaxItemDetailsList.php',
                    'data': function(data) {
                        // Read values
                        var supp = $('#searchByCode').val();
                        var Brand = $('#searchByBrand').val();

                        // Append to data
                        data.searchByCode = supp;
                        data.searchByBrand = Brand;
                    }
                },
                'columns': [{
                        data: 'item_purchase_code'
                    },
                    {
                        data: 'item_code'
                    },
                    {
                        data: 'itm_det_img'
                    },
                    {
                        data: 'itm_det_div'
                    },
                    {
                        data: 'item_desciption'
                    },
                    {
                        data: 'itm_det_hsn'
                    },
                    {
                        data: 'itm_det_uom'
                    },
                    {
                        data: 'itm_supp_map'
                    }, 
                    {
                        data: 'item_brand_make'
                    },
                    {
                        data: 'itm_branch_map'
                    },
                    {
                        data: 'action'
                    },
                ],

                columnDefs: [{
                        orderable: false,
                        targets: [2, 3, 5, 6, 7, 9, 10]
                    },
                    {
                        targets: [8, 7, 9, 10],
                        className: 'text-center'
                    },
                ],

            });

            $('#searchByCode').keyup(function() {
                dataTable.draw();
            });
            $('#searchByBrand').change(function() {
                dataTable.draw();
            });
            // $('#itemDetailsTable').DataTable({
            //         "search": {
            //             "caseInsensitive": true,
            //             "smart": true
            //         }
            //     });

            $('#modalitemDets').on('show.bs.modal', function(e) {
                var id = $(e.relatedTarget).data('id');
                //                alert(id);
                if (id != '') {
                    $.ajax({
                        type: 'post',
                        url: 'inc/cis_ajax/modal_items_dets.php',
                        data: 'id=' + id,
                        success: function(data) {
                            //alert(data);
                            string = data.split("~");
                            $('#m_item_id').html(string[0]);
                            $('#m_item_desciption').html(string[1]);
                        }
                    });
                }
            });


            $('#modalSuppMap').on('show.bs.modal', function(e) {
                var id = $(e.relatedTarget).data('id');
                //    alert(id);
                if (id != '') {
                    $.ajax({
                        type: 'post',
                        url: 'inc/cis_ajax/jquery_modal_supp_map.php',
                        data: 'id=' + id,
                        success: function(data) {
                            // alert(data);
                            string = data.split("~");
                            $('#m_supp_id').html(string[0]);
                            $('#m_supp_name').html(string[1]);
                        }
                    });
                }
            });

            $('#modalupdateitms').on('show.bs.modal', function(e) {
                var id = $(e.relatedTarget).data('id');
                //                alert(id);
                if (id != '') {
                    $.ajax({
                        type: 'post',
                        url: 'inc/cis_ajax/modal_update_items.php',
                        data: 'id=' + id,
                        success: function(data) {
                            //alert(data);
                            string = data.split("~");
                            $('#m_update_id').html(string[0]);
                            $('#m_item_description').html(string[1]);
                        }
                    });
                }
            });

            $('#itemDetailsTable').on('click', 'a.delete', function(e) {
                e.preventDefault();
                if (confirm("Are you sure, you want to delete this Item Details?")) {

                    var id = $(this).attr('rel');
                    var table = "tbl_item_details";
                    var status = "item_status";
                    var value = "0";
                    var where = "item_id";

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
                            if (result == 1) {
                                //$('#itemDetailsTable').dataTable().fnDeleteRow(nRow);
                                //$.jGrowl('item details deleted..!', { sticky: false, theme: 'growl-success',shutdown:'0.5', header: 'Success!', position :'bottom-right' });
                                alert("Item details has been deleted!");
                                location.reload();
                            } else if (result == 0) {
                                $.jGrowl('item details Not deleted..!', {
                                    sticky: false,
                                    theme: 'alert-styled-left alert-arrow-left alert-danger',
                                    position: 'top-right',
                                    shutdown: '3000',
                                    header: 'Error!'
                                });
                            } else {
                                $.jGrowl(result, {
                                    sticky: false,
                                    theme: 'alert-styled-left alert-arrow-left alert-danger',
                                    position: 'top-right',
                                    shutdown: '3000',
                                    header: 'Error!'
                                });
                            }
                        }
                    });

                }
            });

        });
    </script>
    <?php include("modal_item_dets.php") ?>
    <?php include("modal_update_item.php") ?>
    <?php include("modal_supp_map.php") ?>
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
                            <a href="#" class="breadcrumb-item"> Item Masters</a>
                            <span class="breadcrumb-item active">Item Details List</span>
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
                                <h6 class="card-title">List of Item Details</h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <?php //if($_SESSION['_user_type'] == 'A'){ 
                                        ?>
                                        <a class="list-icons-item" href="mst_item_details.php" data-popup='tooltip' title="New Item Details"><i class="icon-plus-circle2 mr-2"></i></a>
                                        <?php // } 
                                        ?>
                                        <a class="list-icons-item" data-action="fullscreen"></a>

                                    </div>
                                </div>
                            </div>

                            <div class="card-body pt-2">
                                <div class="form-group row pl-2">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input type='text' class="form-control" id='searchByCode' name="searchByCode" placeholder='Sale Code / Purchase Code / Description'>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <select name="searchByBrand" id="searchByBrand" class="form-control form-control-select2">
                                            <option value="">-- All Brand--</option>
                                            <?php
                                            $dbconn = new dbhandler();
                                            echo $dbconn->fnFillComboFromTable_Where("brand_id", "brand_name", "mst_brand", "brand_id", " WHERE brand_status = '1'") ?>
                                        </select>
                                        <script>
                                            document.getElementById('searchByBrand').value = "<?php echo $_REQUEST['searchByBrand']; ?>";
                                        </script>
                                    </div>

                                </div>
                                <hr class="mt-0 mb-1">
                                <table class="table table-xs table-hover table-bordered mt-0" id="itemDetailsTable">
                                    <thead>
                                        <tr class="bg-table-header">
                                            <th>Purchase Code</th>
                                            <th>Sale Code</th>
                                            <th>Item Image</th>
                                            <th>Division</th>
                                            <th>Description</th>
                                            <th>HSN</th>
                                            <th>UOM</th>
                                            <th width="10%">Supplier Mapping</th>
                                            <th width="10%">Brand / Make</th>
                                            <th>Item Used Branches</th>
                                            <th width="10%" class="text-center">Actions</th>
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