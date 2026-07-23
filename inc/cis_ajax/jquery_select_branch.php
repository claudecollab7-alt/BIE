<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
$conn = new dbconnect();
$dbconn = new dbhandler();

	if(isset($_POST['supp_id']))
	{
		$supp_id = trim($_POST["supp_id"]);
		$stmt = null;
		$stmt = $conn->prepare("SELECT * FROM mst_customer_branch WHERE branch_status = 1 AND supp_id = ".$supp_id." ORDER BY branch_name");
		$stmt->execute();
		$string = "";
		$string .= "" . "~" . "--Select Customer Branch--" . "#";
		$count = $stmt->rowCount();
		if($count > 0)
		{
			while($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$string .= $row['branch_id'] . "~".$row['branch_name']." - ".$row['branch_add2']."#";
			}
		}
		$discount_apply = $dbconn->GetSingleReconrd("mst_supplier_new", "discount_apply", "supp_id", $supp_id);
		// echo $string;
	     echo $discount_apply . "||" . $string;

	}	
?>
