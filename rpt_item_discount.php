<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Item Discount List</title>

    <?php include_once("inc/common/css-js.php"); ?>
    <script type="text/javascript" src="print_me.js"></script>
    <script src="js/jquery.table2excel.min.js"></script>
    <script src="js/html2pdf.bundle.min.js"></script>

    <script>
        function fnValidate() {
            document.rptForm.submit();
        }

        $(function() {
            $(".rpt_export").click(function(e) {
                var table = $('#discount_db_table');
                if (table && table.length) {
                    $(table).table2excel({
                        exclude: ".noExl",
                        name: "ItemDiscount",
                        filename: "ItemDiscount" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xls",
                        fileext: ".xls",
                        exclude_img: true,
                        exclude_links: true,
                        exclude_inputs: true,
                        preserveColors: true,
                    });
                }
            });

            $(".rpt_pdf").click(function(e) {
                var element = document.getElementById('discount_division');
                var opt = {
                    margin: 1,
                    filename: '<?php echo "ItemDiscount" . date("dMY"); ?>' + '.pdf',
                    image: { type: 'jpeg', quality: 1 },
                    html2canvas: { scale: 2, logging: true },
                    jsPDF: { unit: 'cm', format: 'A4', orientation: 'landscape' }
                };
                html2pdf().set(opt).from(element).save();
            });
        });
    </script>

    <style>
        #discount_db_table th {
            background-color: #b7b7b7;
            color: #000000;
            padding: 6px 8px;
            text-align: center;
            white-space: nowrap;
            border: 1px solid #999999;
        }
        #discount_db_table td {
            padding: 5px 8px;
            border: 1px solid #999999;
        }
        .branch-group-header {
            background-color: #e8f0fe;
            font-weight: bold;
            text-align: center;
            border: 1px solid #999999;
            padding: 4px 8px;
        }
        #discount_db_table tr:nth-child(even) td {
            background-color: #f9f9f9;
        }
        .no-data td {
            text-align: center;
            padding: 20px;
            color: #999;
        }
    </style>
</head>

<body>
    <!-- Main navbar -->
    <?php include("inc/common/header.php") ?>

    <div class="page-content">
        <?php include("inc/common/sidebar.php") ?>

        <div class="content-wrapper">

            <!-- Page header -->
            <div class="page-header">
                <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                    <div class="d-flex">
                        <div class="breadcrumb">
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Home</a>
                            <a href="#" class="breadcrumb-item">Reports</a>
                            <span class="breadcrumb-item active">Item Discount List</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>

            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">

                        <div class="card">
                            <div class="card-header bg-pgheader text-white header-elements-inline">
                                <h6 class="card-title">Item Discount List (Branch-wise)</h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>

                            <form name="rptForm" action="" method="POST" onSubmit="return fnValidate();">
                            <div class="card-body pt-2 pb-5">
                                <div class="form-group row">
                                    <div class="form-group col-md-4">
                                        <label>Item Name <small class="text-muted">(Leave empty for all items)</small></label>
                                        <select name="item_id" id="item_id" class="form-control select-search">
                                            <option value="">-- All Items --</option>
                                            <?php
                                            // Adjust table/column names to match your actual tbl_item_details schema
                                            echo $dbconn->fnFillComboFromTable_Where("item_id", "item_desciption", "tbl_item_details", "item_id", " WHERE item_status = 1");
                                            ?>
                                        </select>
                                        <script>
                                            document.getElementById('item_id').value = "<?php echo htmlspecialchars($_REQUEST['item_id'] ?? ''); ?>";
                                        </script>
                                    </div>

                                    <div class="form-group col-md-2">
                                        <button class="btn btn-info mt-4" name="Report" value="Report" type="submit">
                                            <i class="icon-statistics mr-1"></i>Generate Report
                                        </button>
                                    </div>
                                </div>
                                <hr>

                                <?php
                                if (isset($_POST['Report'])) {

                                    $item_id_filter = isset($_REQUEST['item_id']) ? trim($_REQUEST['item_id']) : '';

                                    // -----------------------------------------------------------
                                    // 1. Fetch all active branches
                                    // -----------------------------------------------------------
                                    $branch_sql = "SELECT branch_id, branch_name, branch_code 
                                                   FROM mst_branch 
                                                   WHERE branch_status = 1 
                                                   ORDER BY branch_id ASC";
                                    $branch_result = $conn->query($branch_sql);
                                    $branches = $branch_result->fetchAll();

                                    if (empty($branches)) {
                                        echo '<div class="alert alert-warning">No active branches found.</div>';
                                    } else {

                                        // Export / Print buttons
                                        echo '<div class="col-md-12 text-right mb-2">
                                                <a href="javascript:" class="rpt_export">
                                                    <button type="button" class="buttons-html5 btn btn-light"><i class="icon-file-excel mr-1"></i> Excel</button>
                                                </a>
                                                <a href="javascript:" class="rpt_pdf">
                                                    <button type="button" class="buttons-html5 btn btn-light"><i class="icon-file-pdf mr-1"></i> PDF</button>
                                                </a>
                                                <a href="javascript:PrintPartsNew(new Array(\'discount_division\'),\'Item Discount List\');" class="rpt_print">
                                                    <button type="button" class="buttons-html5 btn btn-light"><i class="icon-printer mr-1"></i> Print</button>
                                                </a>
                                              </div>';

                                        echo '<div id="discount_division">';
                                        echo '<div class="col-md-12 text-center mb-2">
                                                <span class="font-size-lg font-weight-semibold text-uppercase">Item Discount Report (Branch-wise)</span>
                                              </div>';

                                        echo '<div class="col-md-12 pt-2" style="overflow-x:auto;">';

                                        // -----------------------------------------------------------
                                        // 2. Build the main query
                                        //    Join tbl_item_details with tbl_item_stock, then mst_branch
                                        //    to get branch-specific min/max discounts per item.
                                        //
                                        //    The field name pattern in tbl_item_stock is:
                                        //       {branch_code}_item_min_discount
                                        //       {branch_code}_item_max_discount
                                        //    e.g.  ho_item_min_discount, kl_item_min_discount
                                        // -----------------------------------------------------------

                                        // Build dynamic SELECT columns for each branch
                                        $select_cols = "itd.item_id, itd.item_desciption, itd.item_code, itd.item_purchase_code";
                                        foreach ($branches as $branch) {
                                            $field = strtolower($branch->branch_code); // e.g. 'ho', 'kl', 'rjpm'
                                            $alias_min = $field . "_min_disc";
                                            $alias_max = $field . "_max_disc";
                                            $select_cols .= ", s.{$field}_item_min_discount AS {$alias_min}";
                                            $select_cols .= ", s.{$field}_item_max_discount AS {$alias_max}";
                                        }

                                        $SQL = "SELECT {$select_cols}
                                                FROM tbl_item_details AS itd
                                                LEFT JOIN tbl_item_stock AS s ON itd.item_id = s.item_id
                                                WHERE 1 = 1";

                                        if ($item_id_filter != '') {
                                            $SQL .= " AND itd.item_id = " . (int)$item_id_filter;
                                        }

                                        $SQL .= " ORDER BY itd.item_id ASC";

                                        $result = $conn->query($SQL);

                                        // -----------------------------------------------------------
                                        // 3. Build dynamic table header (2 cols per branch)
                                        // -----------------------------------------------------------
                                        echo '<table class="table table-xs table-bordered" id="discount_db_table">';
                                        echo '<thead>';

                                        // Row 1 – branch group headers
                                        echo '<tr>';
                                        echo '<th rowspan="2" style="vertical-align:middle;">#</th>';
                                        echo '<th rowspan="2" style="vertical-align:middle;">Item Code</th>';
                                        echo '<th rowspan="2" style="vertical-align:middle;">Purchase Code</th>';
                                        echo '<th rowspan="2" style="vertical-align:middle;">Item Description</th>';
                                        foreach ($branches as $branch) {
                                            echo '<th colspan="2" class="branch-group-header">' . htmlspecialchars($branch->branch_name) . '</th>';
                                        }
                                        echo '</tr>';

                                        // Row 2 – Min / Max sub-headers
                                        echo '<tr>';
                                        foreach ($branches as $branch) {
                                            echo '<th>Min Discount (%)</th>';
                                            echo '<th>Max Discount (%)</th>';
                                        }
                                        echo '</tr>';
                                        echo '</thead>';
                                        echo '<tbody>';

                                        // -----------------------------------------------------------
                                        // 4. Data rows
                                        // -----------------------------------------------------------
                                        if ($result->rowCount() > 0) {
                                            $sno = 1;
                                            while ($row = $result->fetch()) {
                                                echo '<tr>';
                                                echo '<td>' . $sno . '</td>';
                                                echo '<td>' . htmlspecialchars($row->item_code) . '</td>';
                                                echo '<td>' . htmlspecialchars($row->item_purchase_code) . '</td>';
                                                echo '<td>' . htmlspecialchars($row->item_desciption) . '</td>';

                                                foreach ($branches as $branch) {
                                                    $field   = strtolower($branch->branch_code);
                                                    $min_val = $row->{$field . "_min_disc"};
                                                    $max_val = $row->{$field . "_max_disc"};

                                                    echo '<td align="right">' . (($min_val !== null && $min_val != 0) ? number_format($min_val, 2) : '-') . '</td>';
                                                    echo '<td align="right">' . (($max_val !== null && $min_val != 0) ? number_format($max_val, 2) : '-') . '</td>';
                                                }

                                                echo '</tr>';
                                                $sno++;
                                            }
                                        } else {
                                            $total_cols = 2 + (count($branches) * 2);
                                            echo '<tr class="no-data"><td colspan="' . $total_cols . '">No records found.</td></tr>';
                                        }

                                        echo '</tbody></table>';
                                        echo '</div>'; // col-md-12
                                        echo '</div>'; // discount_division
                                    }
                                }
                                ?>

                            </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

            <?php include("inc/common/footer.php") ?>
        </div>
    </div>
</body>

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
    });
</script>
</html>