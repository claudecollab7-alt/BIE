<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
session_start();
$conn = new dbconnect();
$dbconn = new dbhandler();



// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

// $echo = '';

$cash_id= $_POST['cash_id'];
$cash_count= $_POST['cash_count'];
$row_count = $_POST['row_count'];
if ($_POST['mode'] == 'save' && isset($_POST['cash_id'])){
    $cash = $dbconn->GetSingleReconrd("tbl_cash_details", "cash_name", "cash_status = '1' AND cash_id",$cash_id);
    $cash_value =  $cash * $cash_count;
    $echo .='<tr id="' . $cash_id . '">						
    <td style = "text-align:center;">' . $row_count . '</td>
    <td style = "text-align:left;">' . $cash . '<input type="hidden" class="receipt_cash_id" name="receipt_cash_id[]" value="' .$cash_id. '" /></td>
    <td style = "text-align:center;">' . $cash_count . '<input type="hidden" class="cash_count" name="cash_count[]" value="' .$cash_count. '" /></td>
    <td style = "text-align:right;">' . number_format($cash_value, 2) . '<input type="hidden" class="cash_value" name="cash_value[]" value="' .$cash_value. '" /></td>
    <td align="center"><a href="javascript:remove_item(' . $cash_id . ');" class="" rel="' . $cash_id . '"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a></td>
    </tr>';
}
echo $echo;
