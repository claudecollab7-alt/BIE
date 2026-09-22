<?php
/* ===========================================================================
 *  Stock Adjustment
 * ---------------------------------------------------------------------------
 *  Manually increase or decrease the logged-in branch's stock for one item.
 *  Every adjustment writes a tbl_stock_adjustment header AND a tbl_stock_flow
 *  ledger row - the quantity is never touched outside fnApplyStockMovement().
 *  A reason is mandatory.
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

if (isset($_POST['SAVE'])) {

    $item_id    = isset($_REQUEST['item_id']) ? (int)$_REQUEST['item_id'] : 0;
    $adj_type   = (isset($_REQUEST['adj_type']) && $_REQUEST['adj_type'] == 'D') ? 'D' : 'I';
    $adj_qty    = isset($_REQUEST['adj_qty']) ? (float)$_REQUEST['adj_qty'] : 0;
    $adj_reason = isset($_REQUEST['adj_reason']) ? trim($_REQUEST['adj_reason']) : '';
    $adj_date   = isset($_REQUEST['adj_date']) && $_REQUEST['adj_date'] != ''
                ? date('Y-m-d', strtotime($_REQUEST['adj_date']))
                : date('Y-m-d');

    /* ---- validation (the browser checks this too, never trust that) ---- */
    if ($item_id <= 0) {
        $_SESSION['_msg_err'] = "Please select an item..!";
        header("location:stock_adjustment.php");
        die();
    }
    if ($adj_qty <= 0) {
        $_SESSION['_msg_err'] = "Adjustment quantity must be greater than zero..!";
        header("location:stock_adjustment.php");
        die();
    }
    if ($adj_reason == '') {
        $_SESSION['_msg_err'] = "Reason is mandatory for a stock adjustment..!";
        header("location:stock_adjustment.php");
        die();
    }

    try {
        $branch_id   = (int)$_SESSION['_user_branch'];
        $stock_field = fnGetBranchStockField($conn, $branch_id);

        $conn->beginTransaction();

        $before_qty = fnGetItemBranchStock($conn, $item_id, $stock_field);

        /* A decrease may not take the branch below zero. */
        if ($adj_type == 'D' && $adj_qty > $before_qty) {
            $conn->rollBack();
            $_SESSION['_msg_err'] = "Cannot decrease by " . $adj_qty
                                  . " - only " . $before_qty . " in stock at this branch..!";
            header("location:stock_adjustment.php");
            die();
        }

        $adj_finyr = $dbconn->GetSingleReconrd("mst_finyear", "finyr", "finyr_active", 1);
        $adj_slno  = $dbconn->GetMaxValue('tbl_stock_adjustment', 'adj_slno',
                        'adj_finyr = "' . $adj_finyr . '" AND branch_id = "' . $branch_id . '" AND 1', 1) + 1;

        $branch_code = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id", $branch_id);
        $adj_refno   = 'ADJ/' . leadingZeros($adj_slno, 4) . '/BIE/' . $branch_code . '/' . $adj_finyr;

        $item_price = $dbconn->GetSingleReconrd("tbl_item_details", "item_cost_price", "item_id", $item_id);
        $after_qty  = ($adj_type == 'D') ? ($before_qty - $adj_qty) : ($before_qty + $adj_qty);

        $stmt = $conn->prepare("INSERT INTO tbl_stock_adjustment
            (adj_refno, adj_slno, adj_finyr, adj_date, branch_id, stock_field, item_id, adj_type,
             adj_qty, before_qty, after_qty, item_price, adj_reason, adj_status, created_by, created_dtm)
            VALUES
            (:adj_refno, :adj_slno, :adj_finyr, :adj_date, :branch_id, :stock_field, :item_id, :adj_type,
             :adj_qty, :before_qty, :after_qty, :item_price, :adj_reason, 1, :created_by, :created_dtm)");

        $stmt->execute(array(
            ':adj_refno'   => $adj_refno,
            ':adj_slno'    => $adj_slno,
            ':adj_finyr'   => $adj_finyr,
            ':adj_date'    => $adj_date,
            ':branch_id'   => $branch_id,
            ':stock_field' => $stock_field,
            ':item_id'     => $item_id,
            ':adj_type'    => $adj_type,
            ':adj_qty'     => $adj_qty,
            ':before_qty'  => $before_qty,
            ':after_qty'   => $after_qty,
            ':item_price'  => ($item_price != '' ? $item_price : 0),
            ':adj_reason'  => $adj_reason,
            ':created_by'  => $_SESSION['_user_id'],
            ':created_dtm' => date('Y-m-d H:i:s')
        ));

        $adj_id = $conn->lastInsertId();

        /* Moves the balance AND writes the tbl_stock_flow ledger row. */
        fnApplyStockMovement($conn, array(
            'item_id'     => $item_id,
            'branch_id'   => $branch_id,
            'stock_field' => $stock_field,
            'dir'         => ($adj_type == 'D') ? 'O' : 'I',
            'qty'         => $adj_qty,
            'trans_type'  => STOCK_TRANS_ADJ,
            'trans_id'    => $adj_id,
            'trans_date'  => $adj_date,
            'item_price'  => ($item_price != '' ? $item_price : 0),
            'remarks'     => $adj_reason
        ));

        $conn->commit();

        $_SESSION['_msg'] = "Stock adjusted successfully - " . $adj_refno
                          . " (" . $before_qty . " &rarr; " . $after_qty . ")";

    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
    }

    header("location:stock_adjustment.php");
    die();
}

$branch_stock_field = '';
try {
    $branch_stock_field = fnGetBranchStockField($conn, $_SESSION['_user_branch']);
} catch (Exception $e) {
    $_SESSION['_msg_err'] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Stock Adjustment</title>
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
                            <a href="#" class="breadcrumb-item"> Stores</a>
                            <span class="breadcrumb-item active">Stock Adjustment</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>
            <!-- /page header -->

            <!-- Content area -->
            <div class="content pt-0">
                <div class="row">

                    <!-- ---------------- Adjustment form ---------------- -->
                    <div class="col-md-5">
                        <form name="adjForm" id="adjForm" class="form-horizontal" method="POST" action="" onSubmit="return fnValidate();">
                            <div class="card">
                                <div class="card-header bg-pgheader text-white header-elements-inline">
                                    <h6 class="card-title">New Stock Adjustment</h6>
                                </div>

                                <div class="card-body">

                                    <div class="form-group row">
                                        <label class="col-lg-4 col-form-label">Branch</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control bg-light" readonly="readonly" tabindex="-1"
                                                value="<?php echo $dbconn->GetSingleReconrd("mst_branch", "branch_name", "branch_id", $_SESSION['_user_branch']); ?>">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-lg-4 col-form-label">Date <span class="text-mandatory">*</span></label>
                                        <div class="col-lg-8">
                                            <input type="date" class="form-control" name="adj_date" id="adj_date"
                                                max="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-lg-4 col-form-label">Item <span class="text-mandatory">*</span></label>
                                        <div class="col-lg-8">
                                            <select name="item_id" id="item_id" class="form-control select-search">
                                                <option value="">Select an Item</option>
                                                <?php echo $dbconn->fnFillComboFromTable_Where("item_id", "CONCAT(item_code,' ~ ',item_desciption)", "tbl_item_details", "item_id", " WHERE item_status = 1"); ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-lg-4 col-form-label">Current Stock</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control bg-light font-weight-bold" id="curr_stock" readonly="readonly" tabindex="-1" value="">
                                            <span class="form-text text-muted" id="uom_hint"></span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-lg-4 col-form-label">Adjustment <span class="text-mandatory">*</span></label>
                                        <div class="col-lg-8">
                                            <select name="adj_type" id="adj_type" class="form-control">
                                                <option value="I">Increase (+)</option>
                                                <option value="D">Decrease (&minus;)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-lg-4 col-form-label">Quantity <span class="text-mandatory">*</span></label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" name="adj_qty" id="adj_qty" maxlength="9"
                                                value="" placeholder="Enter quantity" onKeyPress="return isNumberKey_with_dot(event)">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-lg-4 col-form-label">Stock After</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control bg-light font-weight-bold" id="after_stock" readonly="readonly" tabindex="-1" value="">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-lg-4 col-form-label">Reason <span class="text-mandatory">*</span></label>
                                        <div class="col-lg-8">
                                            <textarea class="form-control" name="adj_reason" id="adj_reason" rows="3" maxlength="255"
                                                placeholder="Why is the stock being corrected? (mandatory)"></textarea>
                                        </div>
                                    </div>

                                </div>

                                <div class="card-footer text-center">
                                    <input class="btn btn-custom" type="submit" name="SAVE" value="Update Stock">
                                    <input class="btn btn-light" type="reset" name="cancel" value="Clear">
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- ---------------- Recent adjustments ---------------- -->
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header bg-pgheader text-white header-elements-inline">
                                <h6 class="card-title">Recent Adjustments - This Branch</h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-0 table-responsive">
                                <table class="table table-xs table-hover table-bordered" id="adjTable">
                                    <thead>
                                        <tr class="bg-table-header">
                                            <th>Ref No</th>
                                            <th>Date</th>
                                            <th>Item</th>
                                            <th class="text-center">Type</th>
                                            <th class="text-right">Qty</th>
                                            <th class="text-right">Before</th>
                                            <th class="text-right">After</th>
                                            <th>Reason</th>
                                            <th>By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT a.*, d.item_code, d.item_desciption, u.usr_name
                                                  FROM tbl_stock_adjustment a
                                                  LEFT JOIN tbl_item_details d ON d.item_id = a.item_id
                                                  LEFT JOIN tbl_user u ON u.usr_id = a.created_by
                                                 WHERE a.adj_status = 1 AND a.branch_id = " . (int)$_SESSION['_user_branch'] . "
                                                 ORDER BY a.adj_id DESC LIMIT 50";
                                        $adjRes = $conn->query($sql);

                                        if ($adjRes && $adjRes->rowCount() > 0) {
                                            while ($rs = $adjRes->fetch(PDO::FETCH_OBJ)) {
                                                $badge = ($rs->adj_type == 'D')
                                                       ? '<span class="badge badge-danger">Decrease</span>'
                                                       : '<span class="badge badge-success">Increase</span>';
                                                $sign = ($rs->adj_type == 'D') ? '-' : '+';

                                                echo '<tr>';
                                                echo '<td>' . htmlspecialchars($rs->adj_refno) . '</td>';
                                                echo '<td>' . date('d-m-Y', strtotime($rs->adj_date)) . '</td>';
                                                echo '<td>' . htmlspecialchars($rs->item_code . ' ~ ' . $rs->item_desciption) . '</td>';
                                                echo '<td class="text-center">' . $badge . '</td>';
                                                echo '<td class="text-right">' . $sign . rtrim(rtrim(number_format($rs->adj_qty, 3, '.', ''), '0'), '.') . '</td>';
                                                echo '<td class="text-right">' . rtrim(rtrim(number_format($rs->before_qty, 3, '.', ''), '0'), '.') . '</td>';
                                                echo '<td class="text-right">' . rtrim(rtrim(number_format($rs->after_qty, 3, '.', ''), '0'), '.') . '</td>';
                                                echo '<td>' . htmlspecialchars($rs->adj_reason) . '</td>';
                                                echo '<td>' . htmlspecialchars($rs->usr_name) . '<br><small class="text-muted">'
                                                   . date('d-M-y h:i a', strtotime($rs->created_dtm)) . '</small></td>';
                                                echo '</tr>';
                                            }
                                        } else {
                                            echo '<tr><td colspan="9" class="text-center">No adjustments yet...</td></tr>';
                                        }
                                        ?>
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
</body>

<script language="javascript" type="text/javascript">
    var currStock = null;

    $(document).ready(function() {
        <?php
        if (isset($_SESSION['_msg']) && $_SESSION['_msg'] != "") {
            echo "$.jGrowl('" . $_SESSION['_msg'] . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-success', position: 'bottom-right', life:'3000', header: 'Success!' });";
            $_SESSION['_msg'] = "";
        }
        if (isset($_SESSION['_msg_err']) && $_SESSION['_msg_err'] != "") {
            echo "$.jGrowl('" . $_SESSION['_msg_err'] . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-danger', position: 'top-right', shutdown:'4000', header: 'Error!' });";
            $_SESSION['_msg_err'] = "";
        }
        ?>

        $('#item_id').on('change', function() {
            var item_id = $(this).val();

            currStock = null;
            $('#curr_stock, #after_stock').val('');
            $('#uom_hint').text('');

            if (item_id == '') {
                return;
            }

            $.ajax({
                type: 'POST',
                url: 'inc/cis_ajax/jquery_get_item_stock.php',
                dataType: 'json',
                data: {
                    'item_id': item_id
                }
            }).done(function(res) {
                if (res && res.ok) {
                    currStock = parseFloat(res.curr_stock);
                    $('#curr_stock').val(trimQty(currStock));
                    $('#uom_hint').text(res.uom ? 'UOM: ' + res.uom : '');
                    recalcAfter();
                } else {
                    $.jGrowl((res && res.msg) ? res.msg : 'Could not read current stock.', {
                        theme: 'alert-styled-left alert-arrow-left alert-danger',
                        position: 'top-right',
                        header: 'Error!'
                    });
                }
            }).fail(function() {
                $.jGrowl('Could not read current stock.', {
                    theme: 'alert-styled-left alert-arrow-left alert-danger',
                    position: 'top-right',
                    header: 'Error!'
                });
            });
        });

        $('#adj_qty, #adj_type').on('keyup change', function() {
            recalcAfter();
        });
    });

    /* Digits and a single decimal point only (no minus - direction is the
       Increase/Decrease dropdown, not the sign of the quantity). */
    function isNumberKey_with_dot(evt) {
        var charCode = evt.which ? evt.which : evt.keyCode;
        var input = evt.target;
        var charStr = String.fromCharCode(charCode);

        if (charCode === 8 || charCode === 9 || charCode === 13 ||
            charCode === 37 || charCode === 39 || charCode === 46) {
            return true;
        }
        if (charStr === '.') {
            return input.value.indexOf('.') === -1;
        }
        return /[0-9]/.test(charStr);
    }

    function trimQty(n) {
        if (isNaN(n)) {
            return '';
        }
        return parseFloat(n.toFixed(3)).toString();
    }

    function recalcAfter() {
        if (currStock === null) {
            $('#after_stock').val('');
            return;
        }
        var qty = parseFloat($('#adj_qty').val());
        if (isNaN(qty) || qty <= 0) {
            $('#after_stock').val(trimQty(currStock));
            return;
        }
        var after = ($('#adj_type').val() == 'D') ? (currStock - qty) : (currStock + qty);
        $('#after_stock').val(trimQty(after));
        $('#after_stock').toggleClass('text-danger', after < 0);
    }

    function fnValidate() {
        if ($('#item_id').val() == '') {
            $.jGrowl('Please select an item.', {
                theme: 'alert-styled-left alert-arrow-left alert-danger',
                position: 'top-right',
                header: 'Required!'
            });
            $('#item_id').focus();
            return false;
        }

        var qty = parseFloat($('#adj_qty').val());
        if (isNaN(qty) || qty <= 0) {
            $.jGrowl('Enter a quantity greater than zero.', {
                theme: 'alert-styled-left alert-arrow-left alert-danger',
                position: 'top-right',
                header: 'Required!'
            });
            $('#adj_qty').focus();
            return false;
        }

        if ($('#adj_type').val() == 'D' && currStock !== null && qty > currStock) {
            $.jGrowl('Cannot decrease by ' + trimQty(qty) + ' - only ' + trimQty(currStock) + ' in stock.', {
                theme: 'alert-styled-left alert-arrow-left alert-danger',
                position: 'top-right',
                header: 'Not enough stock!'
            });
            $('#adj_qty').focus();
            return false;
        }

        if ($.trim($('#adj_reason').val()) == '') {
            $.jGrowl('Reason is mandatory for a stock adjustment.', {
                theme: 'alert-styled-left alert-arrow-left alert-danger',
                position: 'top-right',
                header: 'Required!'
            });
            $('#adj_reason').focus();
            return false;
        }

        return true;
    }
</script>

</html>
