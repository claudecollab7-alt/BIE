<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$conn = new dbconnect();
$dbconn = new dbhandler();

if(isset($_POST['state_name'])){   
$state_name =$_POST['state_name'];

$state_name = $_POST["state_name"];
		$stmt = null;
		$stmt = $conn->prepare("SELECT * FROM mst_district WHERE district_status = '1' AND state_id = ".$state_name." ORDER BY district_name");
		$stmt->execute();
		$string = "";
		$string .= "" . "~" . "--Select District--" . "#";
		$count = $stmt->rowCount();
		if($count > 0)
		{
			while($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$string .= $row['district_id'] . "~" .$row['district_name'] . "#";
			}
		}
		echo $string;
    }
