<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
session_start();


$conn = new dbconnect();
$dbconn= new dbhandler();


ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if(isset($_POST['item_id']))
{
    $item_id = $_POST["item_id"];

    // echo $item_id;
    // exit;
    
    $item_uom = $dbconn->GetSingleReconrd("tbl_item_details", "item_uom", "item_status = '1' AND item_id",$item_id);
   
    $multi_uom_id = $dbconn->GetSingleReconrd("tbl_item_details", "multi_uom_id", "item_status = '1' AND item_id", $item_id);
    
    // $multi_uom = explode(",",$multi_uom_id);
    if ($multi_uom_id != '') {
    $uom_get = $multi_uom_id.','.$item_uom;
    }else{
    $uom_get = $item_uom;

     }

    echo "Item UOM: " . $item_uom . "<br>";
    echo "Multi UOM ID: " . $multi_uom_id . "<br>";
    echo "UOM Get: " . $uom_get . "<br>";

    $stmt = null;
    // if ($multi_uom_id != '') {
        // echo "SELECT uom_id,uom_code FROM mst_uom  WHERE uom_id IN (" . $uom_get . ") AND uom_status='1'  order by uom_code asc ";
        $stmt =  $conn->prepare("SELECT uom_id,uom_code FROM mst_uom  WHERE uom_id IN (" . $uom_get . ") AND uom_status='1'  order by uom_code asc ");
    // } 
    // else {
    //     $stmt =  $conn->prepare( "SELECT uom_id,uom_code FROM mst_uom  WHERE uom_id = " . $item_uom ." AND uom_status='1'  order by uom_code asc ");
    // }
   
        $stmt->execute();
        $string = "";
        $string .= "0" . "~" . "--Select UOM--" . "#";
        $count = $stmt->rowCount();
        if($count > 0)
        {
            while($row = $stmt->fetch(PDO::FETCH_ASSOC))
            {
                if(isset($row['uom_id'])){
                $string .= $row['uom_id'] . "~" .$row['uom_code'] . "#";
                }
            }
        }
        echo $string;
  
}	





?>




