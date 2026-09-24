<?php
/* ===========================================================================
 *  Item Stock List  (Item Masters menu)
 * ---------------------------------------------------------------------------
 *  Every active item with its stock at each branch. Clicking a branch
 *  quantity opens a modal with that item's last 10 tbl_stock_flow movements
 *  at that branch, and a View All button into the full stock flow report.
 *
 *  Branch columns come from mst_branch.branch_stock_field, so adding a branch
 *  needs no change here.
 * ======================================================================== */

ob_start();
session_start();

require_once("inc/common/userclass.php");

isAdmin();

$conn   = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$branches   = array();
$branch_res = $conn->query("SELECT branch_id, branch_name, branch_code, branch_stock_field
                              FROM mst_branch
                             WHERE branch_status = 1 AND branch_stock_field <> ''
                             ORDER BY branch_id");
while ($b = $branch_res->fetch(PDO::FETCH_OBJ)) {
    if (preg_match('/^[A-Za-z0-9_]{1,30}$/', $b->branch_stock_field)) {
        $branches[] = $b;
    }
}

/* Fixed columns before the branch columns start. */
$fixed_cols = 7;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Item Stock List</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
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
        <!-- /main sidebar -->

        <!-- Main content -->
        <div class="content-wrapper">

            <!-- Page header -->
            <div class="page-header">
                <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                    <div class="d-flex">
                        <div class="breadcrumb">
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
                            <a href="#" class="breadcrumb-item"> Item Masters</a>
                            <span class="breadcrumb-item active">Item Stock List</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>
            <!-- /page header -->

            <!-- Content area -->
            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-pgheader text-white header-elements-inline">
                                <h6 class="card-title">Item Stock List</h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="stock_adjustment.php" data-popup="tooltip" title="Stock Adjustment"><i class="icon-pencil5 mr-2"></i></a>
                                        <a class="list-icons-item" href="home.php" data-popup="tooltip" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body pb-0">
                                <div class="form-group row">
                                    <div class="col-md-4">
                                        <label>Search</label>
                                        <input type="text" class="form-control" id="searchByCode" name="searchByCode"
                                            placeholder="Item code, purchase code or description">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Item Type</label>
                                        <select id="itemTypeId" class="form-control">
                                            <option value="">-- All Item Types --</option>
                                            <option value="2">Trading</option>
                                            <option value="3">Raw Materials</option>
                                            <option value="6">Consumable</option>
                                            <option value="7">Group Trading</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Stock</label>
                                        <select id="stockFilter" class="form-control">
                                            <option value="">-- All --</option>
                                            <option value="instock">In stock only</option>
                                            <option value="nostock">Zero / negative only</option>
                                        </select>
                                    </div>
                                </div>
                                <p class="text-muted mb-2"><i class="icon-info22 mr-1"></i>
                                    Click a branch quantity to see its last 10 stock movements.</p>
                            </div>

                            <div class="table-overflow">
                                <table class="table table-xs table-hover table-bordered mt-0" id="itemStockListTable" width="100%">
                                    <thead>
                                        <tr class="bg-table-header">
                                            <th width="3%">#</th>
                                            <th width="6%">Image</th>
                                            <th width="10%">Item Code</th>
                                            <th width="10%">Purchase Code</th>
                                            <th width="25%">Description</th>
                                            <th width="7%">UOM</th>
                                            <th width="12%">Category</th>
                                            <?php foreach ($branches as $b) { ?>
                                                <th class="text-right"><?php echo htmlspecialchars($b->branch_name); ?></th>
                                            <?php } ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
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

    <?php include("modal_item_stock_flow.php") ?>
</body>

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

        var branchCount = <?php echo count($branches); ?>;
        var fixedCols = <?php echo $fixed_cols; ?>;

        var columns = [
            { data: 'sno' },
            { data: 'item_image' },
            { data: 'item_code' },
            { data: 'item_purchase_code' },
            { data: 'item_desciption' },
            { data: 'uom_name' },
            { data: 'category_name' }
        ];
        <?php foreach ($branches as $b) { ?>
        columns.push({ data: 'stock_<?php echo (int)$b->branch_id; ?>' });
        <?php } ?>

        /* # and image are not sortable; every branch column is right aligned. */
        var branchTargets = [];
        for (var i = 0; i < branchCount; i++) {
            branchTargets.push(fixedCols + i);
        }

        var dataTable = $('#itemStockListTable').DataTable({
            dom: '<"datatable-header length-left"lp><"datatable-scroll"rt><"datatable-footer"ip>',
            'processing': true,
            'responsive': true,
            "language": {
                processing: '<i class="icon-spinner spinner mr-2"></i>Loading...'
            },
            'serverSide': true,
            'serverMethod': 'post',
            'lengthChange': true,
            'searching': false,
            "pageLength": 25,
            "order": [
                [2, "asc"]
            ],
            'ajax': {
                'url': 'inc/datatable/ajaxItemStockList.php',
                'data': function(data) {
                    data.searchByCode = $('#searchByCode').val();
                    data.itemTypeId = $('#itemTypeId').val();
                    data.stockFilter = $('#stockFilter').val();
                }
            },
            'columns': columns,
            'columnDefs': [
                { orderable: false, targets: [0, 1] },
                { className: 'text-center', targets: [1, 5] },
                { className: 'text-right', targets: branchTargets }
            ]
        });

        var searchTimer = null;
        $('#searchByCode').keyup(function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                dataTable.draw();
            }, 300);
        });

        $('#itemTypeId, #stockFilter').change(function() {
            dataTable.draw();
        });
    });

    /* The quantity links are drawn by DataTables after page load, so bind on
       the modal itself rather than on each link. */
    $('#modalItemStockFlow').on('show.bs.modal', function(e) {
        var link = $(e.relatedTarget);
        var itemId = link.data('item-id');
        var branchId = link.data('branch-id');

        $('#m_stock_flow').html('<div class="col-md-12 pt-5 pb-5 text-center">' +
            '<span class="text-loading"><i class="icon-spinner spinner mr-2"></i>Loading ...</span></div>');

        if (!itemId || !branchId) {
            $('#m_stock_flow').html('<div class="alert alert-danger m-3">Invalid request.</div>');
            return;
        }

        $.ajax({
            type: 'POST',
            url: 'inc/cis_ajax/jquery_modal_item_stock_flow.php',
            data: {
                'item_id': itemId,
                'branch_id': branchId
            }
        }).done(function(html) {
            $('#m_stock_flow').html(html);
        }).fail(function() {
            $('#m_stock_flow').html('<div class="alert alert-danger m-3">Could not load the stock flow.</div>');
        });
    });
</script>

</html>
