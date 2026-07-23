<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
$conn = new dbconnect();
	if(isset($_POST['district_id']))
	{
		$district_id = $_POST["district_id"];
		$stmt = null;
		$stmt = $conn->prepare("SELECT * FROM mst_city WHERE city_status = '1' AND district_id = ".$district_id." ORDER BY city_name");
		$stmt->execute();
		$string = "";
		$string .= "0" . "~" . "-- Select City --" . "#";
		$count = $stmt->rowCount();
		if($count > 0)
		{
			while($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$string .= $row['city_id'] . "~" .$row['city_name'] . "#";
			}
		}
		echo $string;
	}	
?>