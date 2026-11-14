<?php
// 2026-11-13 units for the store indent: main unit first (the default), then the item's real extra units
// (was: three debug lines in the reply gave "--Select UOM--" a junk value saved as 0, and "NOT APPLICABLE" was offered)
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
session_start();

$conn = new dbconnect();
$dbconn = new dbhandler();

if (isset($_POST['item_id'])) {
    $item_id = (int)$_POST['item_id'];
    $main = $dbconn->GetSingleReconrd("tbl_item_details", "item_uom", "item_id", $item_id);
    $string = "";
    if ($main !== '') {
        $string .= (int)$main . "~" . $dbconn->GetSingleReconrd("mst_uom", "uom_code", "uom_id", (int)$main) . "#";
    }
    foreach (fnItemExtraUoms($conn, $item_id) as $uom_id => $u) {
        $string .= $uom_id . "~" . $u->uom_code . "#";
    }
    while (ob_get_level() > 0) {
        ob_end_clean();	// blank lines from the included files went in front of the first unit
    }
    echo $string;
}
