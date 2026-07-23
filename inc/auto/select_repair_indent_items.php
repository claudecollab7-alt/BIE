<?php
ob_start();
session_start();
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
$conn   = new dbconnect();
$dbconn = new dbhandler();

// ── NEW: direct item detail lookup (called when repair_item_id is selected) ──
if (isset($_REQUEST['mode']) && $_REQUEST['mode'] === 'get_item_details') {
    $repair_item_id = intval($_REQUEST['id']);

    $get_val = $conn->query("SELECT * FROM tbl_item_details WHERE item_id = " . $repair_item_id);
    if ($get_val->rowCount() > 0) {
        $get           = $get_val->fetch(PDO::FETCH_OBJ);
        $gst           = $dbconn->GetSingleReconrd("mst_hsn", "igst", "hsn_status = '1' AND hsn_id", $get->item_hsn);
        $selling_price = $get->item_selling_price;
        $qty           = $get->item_curr_stock;
        $uom           = $dbconn->GetSingleReconrd("mst_uom", "uom_code", "uom_status='1' AND uom_id", $get->item_uom);

        // format: inv_qty ~ item_id ~ selling_price ~ tax ~ unit
        echo $qty . "~" . $repair_item_id . "~" . $selling_price . "~" . $gst . "~" . $uom;
    }
    die();
}
// ── END NEW ──

// original autocomplete logic (spare mapping) — unchanged below
$q = strtolower($_GET["q"]);
if (!$q) return;
    if(isset($_GET['id']))
    {
        $repair_item_id = $_GET['id'];
        $stmt = null;
        $stmt = $conn->prepare("SELECT spare_item_id FROM tbl_spare_mapping WHERE item_id = '".$repair_item_id."' ");
        $stmt->execute();
        $count = $stmt->rowCount();
        if($count > 0)
        {
            while($row = $stmt->fetch(PDO::FETCH_ASSOC))
            {
                $get_val = $conn->query("SELECT * FROM tbl_item_details WHERE item_id = ".$row['spare_item_id']);  
                if ($get_val->rowCount()>0)
                {
                    $get = $get_val->fetch(PDO::FETCH_OBJ); 
                    $gst  = $dbconn->GetSingleReconrd("mst_hsn","igst","hsn_status = '1' AND hsn_id",$get->item_hsn);
                    $selling_price = $get->item_selling_price;
                    $sname = $get->item_desciption;
                    $scode = $get->item_code;
                    $sid = $get->item_id;
                    $qty = $get->item_curr_stock;
                    $uom = $dbconn->GetSingleReconrd("mst_uom","uom_code","uom_status='1' AND uom_id",$get->item_uom);
                    echo "$sname - $scode | 0 ~ $sid ~ $selling_price ~ $gst ~ $uom\n";
                }
            }
        }
    }
?>