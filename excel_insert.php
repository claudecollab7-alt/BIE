<?php
ob_start();
session_start();
require_once("inc/common/userclass.php");
isAdmin();
$conn = new dbconnect();
$dbconn = new dbhandler();

$batch_size = 300;
$offset     = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

$file = fopen("bie_items_3.csv", "r");
fgetcsv($file); // Skip header row

// Skip rows before the current offset
for ($i = 0; $i < $offset; $i++) {
    if (fgetcsv($file) === FALSE) break;
}

$count        = $offset + 1;
$rows_done    = 0;
$has_more     = false;

while (($row = fgetcsv($file)) !== FALSE) {
    if ($rows_done >= $batch_size) {
        $has_more = true;
        break;
    }

    $sql = "INSERT INTO tbl_item_details
    (
        item_type, branch_id, item_principal, item_division,
        item_code, item_purchase_code, supp_item_code, item_category,
        item_desciption, item_uom, item_brand_make, item_price,
        item_discount, item_cost_price, item_selling_price, margin_percent,
        item_hsn, item_dept_sales, item_dept_purchase, new_item
    )
    VALUES
    (
        '{$row[0]}', '{$row[1]}', '{$row[2]}', '{$row[3]}',
        '{$row[4]}', '{$row[5]}', '{$row[6]}', '{$row[7]}',
        '{$row[8]}', '{$row[9]}', '{$row[10]}', '{$row[11]}',
        '{$row[12]}', '{$row[13]}', '{$row[14]}', '{$row[15]}',
        '{$row[16]}', '{$row[17]}', '{$row[18]}', '1'
    );";

    echo "<pre>" . $count . ". " . htmlspecialchars($sql) . "</pre><hr>";
    $conn->query($sql);

    $count++;
    $rows_done++;
}

fclose($file);

$next_offset = $offset + $rows_done;

echo "<h3>Batch Done: Rows " . ($offset + 1) . " to " . ($offset + $rows_done) . " inserted.</h3>";

if ($has_more) {
    $next_url = "?offset=" . $next_offset;
    echo "<p><strong>More rows remaining.</strong></p>";
    echo "<a href='{$next_url}' style='font-size:18px; padding:10px 20px; background:#007bff; color:#fff; text-decoration:none; border-radius:5px;'>▶ Next Batch (Rows " . ($next_offset + 1) . "+)</a>";
    // Auto-redirect after 3 seconds (optional — remove if you want manual control)
    echo "<script>setTimeout(() => { window.location.href = '{$next_url}'; }, 3000);</script>";
    echo "<p><small>Auto-continuing in 3 seconds... or click above to go now.</small></p>";
} else {
    echo "<h2 style='color:green;'>✅ All rows imported successfully!</h2>";
}
?>