<?php
/* Modal body for lst_item_stock.php: the last 10 tbl_stock_flow movements
   for one item at one branch, plus a link into the full report. */

ob_start();
session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
require_once("../common/stock_flow.php");

$conn   = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$item_id   = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
$branch_id = isset($_POST['branch_id']) ? (int)$_POST['branch_id'] : 0;
$limit     = 10;

if ($item_id <= 0 || $branch_id <= 0 || !isset($_SESSION['_user_id'])) {
    echo '<div class="alert alert-danger m-3">Invalid request.</div>';
    die();
}

$item_name   = $dbconn->GetSingleReconrd("tbl_item_details", "CONCAT(item_code,' - ',item_desciption)", "item_id", $item_id);
$branch_name = $dbconn->GetSingleReconrd("mst_branch", "branch_name", "branch_id", $branch_id);
$uom_id      = $dbconn->GetSingleReconrd("tbl_item_details", "item_uom", "item_id", $item_id);
$uom_name    = $uom_id != '' ? $dbconn->GetSingleReconrd("mst_uom", "uom_name", "uom_id", $uom_id) : '';

try {
    $stock_field = fnGetBranchStockField($conn, $branch_id);
    $curr_stock  = fnGetItemBranchStock($conn, $item_id, $stock_field);
} catch (Exception $e) {
    echo '<div class="alert alert-danger m-3">' . htmlspecialchars($e->getMessage()) . '</div>';
    die();
}

/* Total movements on record, so the modal can say how many are not shown. */
$cnt = $conn->prepare("SELECT COUNT(*) FROM tbl_stock_flow WHERE item_id = :item_id AND branch_id = :branch_id");
$cnt->execute(array(':item_id' => $item_id, ':branch_id' => $branch_id));
$total_flows = (int)$cnt->fetchColumn();

$stmt = $conn->prepare("SELECT * FROM tbl_stock_flow
                         WHERE item_id = :item_id AND branch_id = :branch_id
                         ORDER BY auto_id DESC LIMIT " . (int)$limit);
$stmt->execute(array(':item_id' => $item_id, ':branch_id' => $branch_id));
$rows = $stmt->fetchAll(PDO::FETCH_OBJ);

/* Reference code, who did it and the narration differ per movement type. */
function fnFlowRefAndUser($conn, $dbconn, $obj)
{
    $ref = '';
    $by  = $obj->modify_by;
    $dtm = $obj->modify_date_time;
    $remarks = isset($obj->trans_remarks) ? $obj->trans_remarks : '';

    if ($obj->trans_type == 'GRN') {
        $ref = $dbconn->GetSingleReconrd("tbl_grn", "grn_ref_code", "grn_id", $obj->trans_id);
        $g_by = $dbconn->GetSingleReconrd("tbl_grn", "modify_by", "grn_id", $obj->trans_id);
        if ($g_by != '') {
            $by  = $g_by;
            $dtm = $dbconn->GetSingleReconrd("tbl_grn", "modify_date_time", "grn_id", $obj->trans_id);
        }
    } elseif ($obj->trans_type == 'INV' || $obj->trans_type == 'SALE') {
        $ref = $dbconn->GetSingleReconrd("tbl_invoice", "inv_refno", "inv_id", $obj->trans_id);
        $i_by = $dbconn->GetSingleReconrd("tbl_invoice", "modify_by", "inv_id", $obj->trans_id);
        if ($i_by != '') {
            $by  = $i_by;
            $dtm = $dbconn->GetSingleReconrd("tbl_invoice", "modify_date_time", "inv_id", $obj->trans_id);
        }
    } elseif ($obj->trans_type == 'ADJ') {
        $ref = $dbconn->GetSingleReconrd("tbl_stock_adjustment", "adj_refno", "adj_id", $obj->trans_id);
        $a_by = $dbconn->GetSingleReconrd("tbl_stock_adjustment", "created_by", "adj_id", $obj->trans_id);
        if ($a_by != '') {
            $by  = $a_by;
            $dtm = $dbconn->GetSingleReconrd("tbl_stock_adjustment", "created_dtm", "adj_id", $obj->trans_id);
        }
        if ($remarks == '') {
            $remarks = $dbconn->GetSingleReconrd("tbl_stock_adjustment", "adj_reason", "adj_id", $obj->trans_id);
        }
    }

    $user = $by != '' ? $dbconn->GetSingleReconrd("tbl_user", "usr_name", "usr_id", $by) : '';

    return array('ref' => $ref, 'user' => $user, 'dtm' => $dtm, 'remarks' => $remarks);
}

function fnQtyTxt($qty)
{
    $txt = rtrim(rtrim(number_format((float)$qty, 3, '.', ''), '0'), '.');
    return ($txt === '' || $txt === '-') ? '0' : $txt;
}

$badge = array(
    'GRN'  => 'badge-success',
    'INV'  => 'badge-info',
    'SALE' => 'badge-info',
    'ADJ'  => 'badge-warning',
    'OPEN' => 'badge-secondary',
    'RET'  => 'badge-danger'
);
?>

<div class="px-3 pt-3">
    <div class="row">
        <div class="col-md-8">
            <div class="font-weight-bold text-primary"><?php echo htmlspecialchars($item_name); ?></div>
            <div class="text-muted">Branch: <b><?php echo htmlspecialchars($branch_name); ?></b><?php
                if ($uom_name != '') { echo ' &nbsp;|&nbsp; UOM: <b>' . htmlspecialchars($uom_name) . '</b>'; } ?></div>
        </div>
        <div class="col-md-4 text-right">
            <div class="text-muted">Current Stock</div>
            <div class="font-size-lg font-weight-bold <?php echo ($curr_stock > 0) ? 'text-success' : (($curr_stock < 0) ? 'text-danger' : 'text-muted'); ?>">
                <?php echo fnQtyTxt($curr_stock); ?>
            </div>
        </div>
    </div>
    <hr class="mt-2 mb-2">
</div>

<div class="table-responsive px-3">
    <table class="table table-xs table-bordered table-hover mb-2">
        <thead>
            <tr class="bg-table-header">
                <th>Date</th>
                <th class="text-center">Type</th>
                <th>Ref No</th>
                <th class="text-right">Before</th>
                <th class="text-right">Qty</th>
                <th class="text-right">After</th>
                <th>Reason / Remarks</th>
                <th>By</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (count($rows) > 0) {
                foreach ($rows as $obj) {
                    $meta   = fnFlowRefAndUser($conn, $dbconn, $obj);
                    $signed = fnStockSignedQty($obj);
                    $cls    = ($signed < 0) ? 'text-danger' : 'text-success';
                    $bcls   = isset($badge[$obj->trans_type]) ? $badge[$obj->trans_type] : 'badge-light';
                    ?>
                    <tr>
                        <td class="text-nowrap"><?php echo date('d-m-Y', strtotime($obj->trans_date)); ?></td>
                        <td class="text-center"><span class="badge <?php echo $bcls; ?>"><?php
                            echo htmlspecialchars(fnStockTransLabel($obj->trans_type)); ?></span></td>
                        <td><?php echo htmlspecialchars($meta['ref']); ?></td>
                        <td class="text-right"><?php echo fnQtyTxt($obj->before_qty); ?></td>
                        <td class="text-right font-weight-bold <?php echo $cls; ?>"><?php
                            echo ($signed > 0 ? '+' : '') . fnQtyTxt($signed); ?></td>
                        <td class="text-right"><?php echo fnQtyTxt($obj->after_qty); ?></td>
                        <td><?php echo htmlspecialchars($meta['remarks']); ?></td>
                        <td class="text-nowrap"><?php
                            echo htmlspecialchars($meta['user']);
                            if ($meta['dtm'] != '' && $meta['dtm'] != '0000-00-00 00:00:00') {
                                echo '<br><small class="text-muted">' . date('d-M-y h:i a', strtotime($meta['dtm'])) . '</small>';
                            } ?></td>
                    </tr>
                    <?php
                }
            } else {
                echo '<tr><td colspan="8" class="text-center text-muted py-3">No stock movements recorded for this item at this branch.</td></tr>';
            }
            ?>
        </tbody>
    </table>

    <div class="d-flex justify-content-between align-items-center pb-3">
        <small class="text-muted">
            <?php
            if ($total_flows > count($rows)) {
                echo 'Showing the latest ' . count($rows) . ' of ' . $total_flows . ' movements.';
            } elseif ($total_flows > 0) {
                echo 'Showing all ' . $total_flows . ' movement' . ($total_flows == 1 ? '' : 's') . '.';
            }
            ?>
        </small>
        <a class="btn btn-sm btn-custom"
           href="rpt_item_stock_history_branch_wise.php?Report=Report&item_id=<?php echo $item_id; ?>&branch_id=<?php echo $branch_id; ?>&from_dt=<?php echo date('Y-m-d', strtotime('-1 year')); ?>&to_dt=<?php echo date('Y-m-d'); ?>">
            <i class="icon-statistics mr-1"></i> View All
        </a>
    </div>
</div>
