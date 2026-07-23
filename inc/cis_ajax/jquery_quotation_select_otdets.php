<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
$conn = new dbconnect();

	if(isset($_POST['principal_id']))
	{
		$principal_id = $_POST["principal_id"];
		$stmt = null;
		$stmt = $conn->prepare("SELECT * FROM mst_quo_details WHERE quo_status = '1' AND principal_id = ".$principal_id." ORDER BY quo_pack_decp");
		$stmt->execute();
		$string = "";
		$string .= "" . "~" . "--Select--" . "#";
		$count = $stmt->rowCount();
		if($count > 0)
		{
			while($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$string .= $row['quo_id'] . "~" .$row['quo_pack_decp'] . "#";
			}
		}
		echo $string;
	}	
?>