<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
$conn = new dbconnect();
	if(isset($_POST['department_id']))
	{
		$department_id = $_POST["department_id"];
		$stmt = null;
		$stmt = $conn->prepare("SELECT * FROM mst_designation WHERE rec_del_status = '1' AND department_id = ".$department_id." ORDER BY designation_name");
		$stmt->execute();
		$string = "";
		$string .= "" . "~" . "--Select Designation--" . "#";
		$count = $stmt->rowCount();
		if($count > 0)
		{
			while($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$string .= $row['designation_id'] . "~" .$row['designation_name'] . "#";
			}
		}
		echo $string;
	}	
?>