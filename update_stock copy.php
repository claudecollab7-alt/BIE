<?php
ob_start();
session_start();
require_once("inc/common/userclass.php");
isAdmin();
$conn   = new dbconnect();
$dbconn = new dbhandler();

ini_set('max_execution_time', 120);
ini_set('memory_limit', '256M');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$start_id   = 6518;
$end_id     = 6565;
$batch_size = 300;

$offset     = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

$from_id    = $start_id + $offset;
$to_id      = min($from_id + $batch_size - 1, $end_id);
$total      = $end_id - $start_id + 1; // 1542 items

echo "<h4>📦 Processing item_id {$from_id} to {$to_id} (Total range: {$start_id}–{$end_id})</h4><hr>";

$inserted = 0;
$skipped  = 0;
$errors   = [];

for ($item_id = $from_id; $item_id <= $to_id; $item_id++) {

    $item_price      = (float)$dbconn->GetSingleReconrd("tbl_item_details", "item_price",      "item_id", $item_id);
    $item_brand_make = (int)$dbconn->GetSingleReconrd("tbl_item_details",   "item_brand_make", "item_id", $item_id);

    // Skip if item doesn't exist (price = 0 and brand = 0)
    if ($item_price == 0 && $item_brand_make == 0) {
        echo "<p style='color:orange;'>⚠️ Skipped item_id {$item_id} — not found in tbl_item_details.</p>";
        $skipped++;
        continue;
    }

    // -------------------------------------------------------
    // STANLEY (brand 12)
    //   selling  = item_price - 10%
    //   cost     = item_price - 45%
    //   discount = 10, margin = 0
    // -------------------------------------------------------
    // TAPARIA (brand 13)
    //   selling  = item_price - 10%
    //   cost     = item_price - 38%
    //   discount = 10, margin = 0
    // -------------------------------------------------------
    // OTHERS
    //   selling  = item_price + 25%
    //   cost     = item_price
    //   discount = 0, margin = 25
    // -------------------------------------------------------

    if ($item_brand_make == 12) {
        // STANLEY
        $price    = $item_price;
        $selling  = round($item_price - ($item_price * 10 / 100), 2);
        $cost     = round($item_price - ($item_price * 45 / 100), 2);
        $discount = 10;
        $margin   = 0;

    } elseif ($item_brand_make == 13) {
        // TAPARIA
        $price    = $item_price;
        $selling  = round($item_price - ($item_price * 10 / 100), 2);
        $cost     = round($item_price - ($item_price * 38 / 100), 2);
        $discount = 10;
        $margin   = 0;

    } else {
        // OTHERS
        $price    = $item_price;
        $selling  = round($item_price + ($item_price * 25 / 100), 2);
        $cost     = $item_price;
        $discount = 0;
        $margin   = 25;
    }

    // All branches same values
    $ho_price    = $kl_price    = $rjpm_price    = $price;
    $ho_selling  = $kl_selling  = $rjpm_selling  = $selling;
    $ho_cost     = $kl_cost     = $rjpm_cost     = $cost;
    $ho_discount = $kl_discount = $rjpm_discount = $discount;
    $ho_margin   = $kl_margin   = $rjpm_margin   = $margin;


    $sql = "INSERT INTO `tbl_item_stock`
    (
        `item_id`,
        `ho_stock`, `kl_stock`,
        `ho_loc_row`, `ho_loc_rack`,
        `kl_loc_row`, `kl_loc_rack`,
        `ho_item_price`, `ho_item_discount`, `ho_item_min_discount`, `ho_item_max_discount`,
        `ho_item_cost_price`, `ho_item_selling_price`, `ho_item_margin`,
        `ho_item_min_qty`, `ho_item_order_min_qty`, `ho_item_max_qty`,
        `kl_item_price`, `kl_item_discount`, `kl_item_min_discount`, `kl_item_max_discount`,
        `kl_item_cost_price`, `kl_item_selling_price`, `kl_item_margin`,
        `kl_item_min_qty`, `kl_item_order_min_qty`, `kl_item_max_qty`,
        `rjpm_stock`, `rjpm_loc_row`, `rjpm_loc_rack`,
        `rjpm_item_price`, `rjpm_item_discount`, `rjpm_item_min_discount`, `rjpm_item_max_discount`,
        `rjpm_item_cost_price`, `rjpm_item_selling_price`, `rjpm_item_margin`,
        `rjpm_item_min_qty`, `rjpm_item_order_min_qty`, `rjpm_item_max_qty`
    )
    VALUES
    (
        {$item_id},
        0, 0,
        0, 0,
        0, 0,
        {$ho_price}, {$ho_discount}, 0, 10,
        {$ho_cost}, {$ho_selling}, {$ho_margin},
        1, 1, 1,
        {$kl_price}, {$kl_discount}, 0, 10,
        {$kl_cost}, {$kl_selling}, {$kl_margin},
        1, 1, 1,
        0, 0, 0,
        {$rjpm_price}, {$rjpm_discount}, 0, 10,
        {$rjpm_cost}, {$rjpm_selling}, {$rjpm_margin},
        1, 1, 1
    )";

    $result = $conn->query($sql);

    $brand_label = $item_brand_make == 12 ? 'STANLEY' : ($item_brand_make == 13 ? 'TAPARIA' : 'OTHER');

    if ($result) {
        echo "<p style='color:green;'>✅ item_id {$item_id} | {$brand_label} | Price: {$price} | Selling: {$selling} | Cost: {$cost} | Discount: {$discount} | Margin: {$margin}</p>";
        $inserted++;
    } else {
        echo "<p style='color:red;'>❌ FAILED item_id {$item_id}</p>";
        echo "<pre style='color:red;'>" . htmlspecialchars($sql) . "</pre>";
        $errors[] = $item_id;
    }
}

$next_offset = $offset + $batch_size;
$done_so_far = min($to_id, $end_id) - $start_id + 1;
$remaining   = $end_id - $to_id;

echo "<hr>";
echo "<h3>Batch Done: item_id {$from_id} to {$to_id}</h3>";
echo "<p>✅ Inserted: <b>{$inserted}</b> | ⚠️ Skipped: <b>{$skipped}</b> | ❌ Failed: <b>" . count($errors) . "</b></p>";
echo "<p>Progress: <b>{$done_so_far}</b> of <b>{$total}</b> | Remaining: <b>{$remaining}</b></p>";

if (!empty($errors)) {
    echo "<p style='color:red;'>Failed item_ids: " . implode(', ', $errors) . "</p>";
}

if ($to_id < $end_id) {
    $next_url = "?offset=" . $next_offset;
    echo "<a href='{$next_url}' style='font-size:18px; padding:10px 20px; background:#007bff; color:#fff; text-decoration:none; border-radius:5px;'>▶ Next Batch (item_id " . ($to_id + 1) . "+)</a>";
    echo "<script>setTimeout(() => { window.location.href = '{$next_url}'; }, 3000);</script>";
    echo "<p><small>Auto-continuing in 3 seconds...</small></p>";
} else {
    echo "<h2 style='color:green;'>🎉 All items from {$start_id} to {$end_id} processed!</h2>";
}
?>