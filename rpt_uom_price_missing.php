<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

// 2026-11-11 items with an extra unit (multiple UOM) and their cost per branch in that unit - opened from Item Price History
// a branch with no price is shown "Not set": PO Prepare will not send an indent line in that unit to admin until it is set

$only_missing = !isset($_REQUEST['all']);

$branches = $conn->query("SELECT branch_id, branch_name FROM mst_branch WHERE branch_status = 1 ORDER BY branch_id")->fetchAll(PDO::FETCH_OBJ);
$uom_map = $dbconn->GetMap("SELECT uom_id, uom_name FROM mst_uom");

// every unit price at once: item_uom_branch => cost
$prices = array();
$res = $conn->query("SELECT item_id, uom_id, branch_id, cost_price FROM tbl_item_uom_price");
while ($p = $res->fetch(PDO::FETCH_OBJ)) {
    $prices[$p->item_id . '_' . $p->uom_id . '_' . $p->branch_id] = $p->cost_price;
}

$rows = array();
$missing_count = 0;
$res = $conn->query("SELECT item_id, item_code, item_desciption, item_uom, multi_uom_id FROM tbl_item_details
    WHERE item_status = '1' AND multi_uom_id <> '' AND multi_uom_id <> '0' ORDER BY item_code");
while ($it = $res->fetch(PDO::FETCH_OBJ)) {
    $seen = array();
    foreach (explode(',', $it->multi_uom_id) as $u) {
        $u = (int)trim($u);
        // same rule as fnItemExtraUoms(): a real unit, not the main one
        if ($u <= 0 || $u == (int)$it->item_uom || in_array($u, $seen) || !isset($uom_map[$u])) {
            continue;
        }
        $seen[] = $u;
        $cells = array();
        $missing = 0;
        foreach ($branches as $b) {
            $k = $it->item_id . '_' . $u . '_' . $b->branch_id;
            $cells[] = isset($prices[$k]) ? $prices[$k] : null;
            if (!isset($prices[$k])) {
                $missing++;
            }
        }
        if ($missing > 0) {
            $missing_count++;
        }
        if ($only_missing && $missing == 0) {
            continue;
        }
        $rows[] = array('item' => $it, 'uom' => $u, 'cells' => $cells);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Extra Unit Prices</title>

    <?php include_once("inc/common/css-js.php"); ?>
    <script src="js/jquery.table2excel.min.js"></script>
    <script>
        $(function() {
            $(".rpt_export").click(function() {
                $('#uom_price_table').table2excel({
                    exclude: ".noExl",
                    name: "Extra Unit Prices",
                    filename: "ExtraUnitPrices" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xls",
                    fileext: ".xls",
                    exclude_links: true,
                    exclude_inputs: true
                });
            });
        });
    </script>
</head>

<body>
    <?php include("inc/common/header.php") ?>
    <div class="page-content">
        <?php include("inc/common/sidebar.php") ?>
        <div class="content-wrapper">
            <div class="page-header">
                <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                    <div class="d-flex">
                        <div class="breadcrumb">
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Home</a>
                            <a href="mst_itemprice_history_list.php" class="breadcrumb-item"> Item Price History</a>
                            <span class="breadcrumb-item active">Extra Unit Prices</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content pt-0">
                <div class="card">
                    <div class="card-header bg-pgheader text-white header-elements-inline">
                        <h6 class="card-title">Extra Unit Prices (Multiple UOM)</h6>
                        <div class="header-elements">
                            <div class="list-icons">
                                <a class="list-icons-item" href="mst_itemprice_history_list.php" title="Item Price History"><i class="icon-arrow-left52 mr-2"></i></a>
                                <a class="list-icons-item" data-action="fullscreen"></a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        <div class="row mb-2">
                            <div class="col-md-8" style="line-height:2rem;">
                                <b><?php echo $missing_count; ?></b> item units have no price for at least one branch.
                                <?php if ($only_missing) { ?>
                                    <a href="rpt_uom_price_missing.php?all=1" class="ml-2">Show all items with an extra unit</a>
                                <?php } else { ?>
                                    <a href="rpt_uom_price_missing.php" class="ml-2">Show only the ones not set</a>
                                <?php } ?>
                            </div>
                            <div class="col-md-4 text-right">
                                <button type="button" class="btn btn-light btn-sm rpt_export"><i class="icon-file-excel mr-1"></i> Excel</button>
                            </div>
                        </div>
                        <table class="table table-xs table-bordered" id="uom_price_table">
                            <thead>
                                <tr class="bg-teal">
                                    <th>#</th>
                                    <th>Item</th>
                                    <th>Main Unit</th>
                                    <th>Extra Unit</th>
                                    <?php foreach ($branches as $b) { ?>
                                        <th class="text-right">Cost - <?php echo $b->branch_name; ?></th>
                                    <?php } ?>
                                    <th class="noExl">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                foreach ($rows as $r) {
                                    $it = $r['item'];
                                ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><b><?php echo $it->item_code; ?></b> - <?php echo $it->item_desciption; ?></td>
                                        <td><?php echo $dbconn->MapValue($uom_map, $it->item_uom); ?></td>
                                        <td><?php echo $uom_map[$r['uom']]; ?></td>
                                        <?php foreach ($r['cells'] as $c) { ?>
                                            <td class="text-right<?php echo $c === null ? ' table-danger' : ''; ?>"><?php echo $c === null ? 'Not set' : $c; ?></td>
                                        <?php } ?>
                                        <td class="noExl text-center"><a href="mst_itemprice_history.php?item_id=<?php echo $it->item_id; ?>">Set price</a></td>
                                    </tr>
                                <?php }
                                if (count($rows) == 0) { ?>
                                    <tr>
                                        <td colspan="<?php echo 5 + count($branches); ?>" class="text-center">Every extra unit has a price for every branch..!</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php include("inc/common/footer.php") ?>
        </div>
    </div>
</body>

</html>
