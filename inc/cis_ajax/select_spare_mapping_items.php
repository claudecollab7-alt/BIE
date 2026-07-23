<?php
ob_start();
session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();

$q = strtolower($_GET["q"]);
if (!$q) return;

$stmt = null;
$stmt = $conn->prepare("SELECT item_id,item_code,item_desciption 
                        FROM tbl_item_details 
                        WHERE item_status = '1' 
                        AND item_type IN (2,5,6) 
                        AND item_desciption LIKE '".$q."%' 
                        ORDER BY item_desciption ASC");
$stmt->execute();

if($stmt->rowCount() > 0)
{
	while($row = $stmt->fetch(PDO::FETCH_ASSOC))
	{
		$sname = $row['item_desciption'];
		$scode = $row['item_code'];
		$sid   = $row['item_id'];

		echo "$sname - $scode | $sid\n";
	}
}
?>