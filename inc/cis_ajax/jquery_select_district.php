<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
$conn = new dbconnect();
	if(isset($_POST['state_id']))
	{
		$state_id = $_POST["state_id"];
		$stmt = null;
		$stmt = $conn->prepare("SELECT * FROM mst_district WHERE district_status = '1' AND state_id = ".$state_id." ORDER BY district_name");
		$stmt->execute();
		$string = "";
		$string .= "" . "~" . "-- Select District --" . "#";
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
?>