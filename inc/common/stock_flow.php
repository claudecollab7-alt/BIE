<?php
/* ===========================================================================
 *  BIE - Central stock movement helper
 * ---------------------------------------------------------------------------
 *  Every place that adds to or subtracts from tbl_item_stock must go through
 *  fnApplyStockMovement() so that a matching tbl_stock_flow ledger row is
 *  always written. Never UPDATE a *_stock column directly.
 *
 *  Usage:
 *      require_once("inc/common/stock_flow.php");      // from the web root
 *      require_once("../common/stock_flow.php");       // from inc/cis_ajax
 *
 *      fnApplyStockMovement($conn, array(
 *          'item_id'    => 123,
 *          'dir'        => 'I',          // 'I' = increase, 'O' = decrease
 *          'qty'        => 5,
 *          'trans_type' => 'GRN',
 *          'trans_id'   => $grn_id,
 *          'item_price' => 120.50,
 *          'remarks'    => 'GRN receipt'
 *      ));
 * ======================================================================== */

if (!defined('BIE_STOCK_FLOW_LOADED')) {

define('BIE_STOCK_FLOW_LOADED', 1);

/* Transaction types written to tbl_stock_flow.trans_type */
define('STOCK_TRANS_GRN',  'GRN');   // goods received
define('STOCK_TRANS_INV',  'INV');   // sales invoice / DC
define('STOCK_TRANS_ADJ',  'ADJ');   // manual stock adjustment
define('STOCK_TRANS_OPEN', 'OPEN');  // opening balance / data load
define('STOCK_TRANS_RET',  'RET');   // purchase return

/**
 * Human readable label for a ledger row type.
 */
function fnStockTransLabel($trans_type)
{
    switch ($trans_type) {
        case 'GRN':          return 'GRN';
        case 'INV':          return 'INV';
        case 'SALE':         return 'SALE';
        case 'ADJ':          return 'Adjustment';
        case 'OPEN':         return 'Opening';
        case 'RET':          return 'Purchase Return';
        default:             return ($trans_type != '' ? $trans_type : 'Other');
    }
}

/**
 * Direction of a ledger row. Rows written before trans_dir existed fall back
 * to the direction implied by their trans_type.
 *
 * @return string 'I' (stock in / +) or 'O' (stock out / -)
 */
function fnStockTransDir($obj)
{
    $dir = isset($obj->trans_dir) ? trim($obj->trans_dir) : '';
    if ($dir == 'I' || $dir == 'O') {
        return $dir;
    }
    /* legacy rows */
    $type = isset($obj->trans_type) ? $obj->trans_type : '';
    if ($type == 'INV' || $type == 'SALE' || $type == 'RET') {
        return 'O';
    }
    return 'I';
}

/**
 * Signed transaction quantity for display: negative when stock went out.
 */
function fnStockSignedQty($obj)
{
    $qty = isset($obj->trans_qty) ? (float)$obj->trans_qty : 0;
    return (fnStockTransDir($obj) == 'O') ? ($qty * -1) : $qty;
}

/**
 * The tbl_item_stock column that holds the quantity for a branch.
 *
 * @throws Exception when the branch has no stock column configured
 */
function fnGetBranchStockField($conn, $branch_id)
{
    $stmt = $conn->prepare("SELECT branch_stock_field FROM mst_branch WHERE branch_id = :branch_id");
    $stmt->execute(array(':branch_id' => $branch_id));
    $field = $stmt->fetchColumn();

    $field = trim((string)$field);
    if ($field == '') {
        throw new Exception("No stock column is configured for branch " . $branch_id . " (mst_branch.branch_stock_field).");
    }
    return $field;
}

/**
 * Guard for the column names that get interpolated into the UPDATE statement.
 * The value must look like an identifier AND be a column some branch actually
 * uses, so a tampered mst_branch row cannot turn into arbitrary SQL.
 *
 * @throws Exception when the column is not a known branch stock column
 */
function fnAssertBranchStockField($conn, $field)
{
    if (!preg_match('/^[A-Za-z0-9_]{1,30}$/', (string)$field)) {
        throw new Exception("Invalid stock column name: " . $field);
    }

    $stmt = $conn->prepare("SELECT COUNT(*) FROM mst_branch WHERE branch_stock_field = :field");
    $stmt->execute(array(':field' => $field));

    if ((int)$stmt->fetchColumn() == 0) {
        throw new Exception("Unknown stock column: " . $field);
    }
    return $field;
}

/**
 * Make sure the item has a tbl_item_stock row, so the UPDATE below can never
 * silently affect zero rows.
 */
function fnEnsureItemStockRow($conn, $item_id)
{
    $stmt = $conn->prepare("SELECT stock_id FROM tbl_item_stock WHERE item_id = :item_id");
    $stmt->execute(array(':item_id' => $item_id));
    $stock_id = $stmt->fetchColumn();

    if ($stock_id === false || $stock_id === null || $stock_id === '') {
        $ins = $conn->prepare("INSERT INTO tbl_item_stock (item_id) VALUES (:item_id)");
        $ins->execute(array(':item_id' => $item_id));
        $stock_id = $conn->lastInsertId();
    }
    return $stock_id;
}

/**
 * Current branch stock for an item.
 */
function fnGetItemBranchStock($conn, $item_id, $stock_field)
{
    fnAssertBranchStockField($conn, $stock_field);

    $stmt = $conn->prepare("SELECT " . $stock_field . " FROM tbl_item_stock WHERE item_id = :item_id");
    $stmt->execute(array(':item_id' => $item_id));
    $qty = $stmt->fetchColumn();

    return ($qty === false || $qty === null) ? 0 : (float)$qty;
}

/**
 * Apply a stock movement AND write the matching tbl_stock_flow ledger row.
 *
 * The balance is moved with a relative UPDATE (col = col +/- :delta) rather
 * than a read-then-overwrite, so two concurrent movements cannot lose each
 * other. Wrap the call in a transaction when it is part of a larger document
 * save - it joins the caller's transaction if one is already open.
 *
 * @param  PDO   $conn
 * @param  array $mv   item_id, dir ('I'|'O'), qty, trans_type, trans_id,
 *                     and optionally: branch_id, item_price, remarks,
 *                     rcvd_qty, reje_qty, pend_qty, trans_date, modify_by,
 *                     stock_field, stock_status
 * @return array|false array(before_qty, after_qty, stock_field, delta),
 *                     or false when qty is zero (nothing to record)
 * @throws Exception
 */
function fnApplyStockMovement($conn, $mv)
{
    $item_id = isset($mv['item_id']) ? (int)$mv['item_id'] : 0;
    if ($item_id <= 0) {
        throw new Exception("Stock movement needs an item_id.");
    }

    $dir = (isset($mv['dir']) && $mv['dir'] == 'O') ? 'O' : 'I';
    $qty = isset($mv['qty']) ? abs((float)$mv['qty']) : 0;

    /* Nothing moved - do not pollute the ledger with zero rows. */
    if ($qty == 0) {
        return false;
    }

    $branch_id = isset($mv['branch_id']) && $mv['branch_id'] != ''
               ? (int)$mv['branch_id']
               : (isset($_SESSION['_user_branch']) ? (int)$_SESSION['_user_branch'] : 0);

    $stock_field = isset($mv['stock_field']) && $mv['stock_field'] != ''
                 ? $mv['stock_field']
                 : fnGetBranchStockField($conn, $branch_id);

    fnAssertBranchStockField($conn, $stock_field);
    fnEnsureItemStockRow($conn, $item_id);

    $before = fnGetItemBranchStock($conn, $item_id, $stock_field);
    $delta  = ($dir == 'O') ? ($qty * -1) : $qty;
    $after  = $before + $delta;

    /* Relative update - safe against concurrent movements on the same item. */
    $upd = $conn->prepare("UPDATE tbl_item_stock SET " . $stock_field . " = " . $stock_field . " + :delta
                            WHERE item_id = :item_id");
    $upd->execute(array(
        ':delta'   => $delta,
        ':item_id' => $item_id
    ));

    $flow = $conn->prepare("INSERT INTO tbl_stock_flow
        (trans_type, trans_dir, trans_id, branch_id, stock_field, trans_date, item_id, item_price,
         before_qty, rcvd_qty, trans_qty, reje_qty, pend_qty, trans_remarks, after_qty,
         modify_by, modify_date_time, stock_status)
        VALUES
        (:trans_type, :trans_dir, :trans_id, :branch_id, :stock_field, :trans_date, :item_id, :item_price,
         :before_qty, :rcvd_qty, :trans_qty, :reje_qty, :pend_qty, :trans_remarks, :after_qty,
         :modify_by, :modify_date_time, :stock_status)");

    $flow->execute(array(
        ':trans_type'       => isset($mv['trans_type']) ? $mv['trans_type'] : STOCK_TRANS_ADJ,
        ':trans_dir'        => $dir,
        ':trans_id'         => isset($mv['trans_id']) ? (int)$mv['trans_id'] : 0,
        ':branch_id'        => $branch_id,
        ':stock_field'      => $stock_field,
        ':trans_date'       => isset($mv['trans_date']) && $mv['trans_date'] != '' ? $mv['trans_date'] : date('Y-m-d'),
        ':item_id'          => $item_id,
        ':item_price'       => isset($mv['item_price']) ? (float)$mv['item_price'] : 0,
        ':before_qty'       => $before,
        ':rcvd_qty'         => isset($mv['rcvd_qty']) ? (float)$mv['rcvd_qty'] : 0,
        ':trans_qty'        => $qty,
        ':reje_qty'         => isset($mv['reje_qty']) ? (float)$mv['reje_qty'] : 0,
        ':pend_qty'         => isset($mv['pend_qty']) ? (float)$mv['pend_qty'] : 0,
        ':trans_remarks'    => isset($mv['remarks']) ? substr(trim($mv['remarks']), 0, 255) : '',
        ':after_qty'        => $after,
        ':modify_by'        => isset($mv['modify_by']) && $mv['modify_by'] != ''
                               ? (int)$mv['modify_by']
                               : (isset($_SESSION['_user_id']) ? (int)$_SESSION['_user_id'] : 0),
        ':modify_date_time' => date('Y-m-d H:i:s'),
        ':stock_status'     => isset($mv['stock_status']) ? (int)$mv['stock_status'] : 0
    ));

    return array(
        'before_qty'  => $before,
        'after_qty'   => $after,
        'delta'       => $delta,
        'stock_field' => $stock_field
    );
}

/**
 * Move stock to an exact target quantity and log the difference.
 * Used where a screen posts an absolute stock value instead of a delta.
 *
 * @return array|false false when the target already matches current stock
 */
function fnSetStockToQty($conn, $item_id, $target_qty, $mv = array())
{
    $branch_id = isset($mv['branch_id']) && $mv['branch_id'] != ''
               ? (int)$mv['branch_id']
               : (isset($_SESSION['_user_branch']) ? (int)$_SESSION['_user_branch'] : 0);

    $stock_field = isset($mv['stock_field']) && $mv['stock_field'] != ''
                 ? $mv['stock_field']
                 : fnGetBranchStockField($conn, $branch_id);

    fnEnsureItemStockRow($conn, $item_id);
    $before = fnGetItemBranchStock($conn, $item_id, $stock_field);
    $delta  = (float)$target_qty - $before;

    if ($delta == 0) {
        return false;
    }

    $mv['item_id']     = $item_id;
    $mv['branch_id']   = $branch_id;
    $mv['stock_field'] = $stock_field;
    $mv['dir']         = ($delta < 0) ? 'O' : 'I';
    $mv['qty']         = abs($delta);

    if (!isset($mv['trans_type'])) {
        $mv['trans_type'] = STOCK_TRANS_ADJ;
    }
    return fnApplyStockMovement($conn, $mv);
}

} /* BIE_STOCK_FLOW_LOADED */
