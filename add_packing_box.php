<?PHP

ob_start();

session_start();
require_once("inc/common/userclass.php");
isAdmin();


$conn = new dbconnect();
$dbconn= new dbhandler();


if ($_POST['mode'] == 'save')
{
	try
	{
		$temp_pack_id = $dbconn->GetSingleReconrd("tbl_package_box_details_temp","temp_pack_id","temp_dc_id='".$_REQUEST['dc_id']."' AND temp_item_id",$_REQUEST['item_id']);
		if($temp_pack_id == '')
		{
			$pack_box_no = implode(', ', $_REQUEST['box_no']);
			$pack_item_qty = implode(', ', $_REQUEST['qty']);
			$stmt = null;				
			$stmt = $conn->prepare("INSERT INTO tbl_package_box_details_temp (temp_so_id, temp_dc_id, temp_item_id, temp_pack_box_no, temp_pack_item_qty, temp_box_id, temp_dispatch_qty, session_id, token, temp_total_qty) VALUES (:temp_so_id, :temp_dc_id, :temp_item_id, :temp_pack_box_no, :temp_pack_item_qty, :temp_box_id, :temp_dispatch_qty, :session_id, :token, :temp_total_qty)");		
			$data = array(				
				':temp_so_id' => $_REQUEST['so_id'],
				':temp_dc_id' => $_REQUEST['dc_id'],
				':temp_item_id' => $_REQUEST['item_id'],
				':temp_pack_box_no' => $pack_box_no,
				':temp_pack_item_qty' => $pack_item_qty,
				':temp_box_id' => $_REQUEST['box_id'],
				':temp_dispatch_qty' => $_REQUEST['dispatch_qty'],
				':session_id' => $_SESSION['session_id'],
				':token' => $_REQUEST['token'],
				':temp_total_qty' => $_REQUEST['total']
			);
			$stmt->execute($data);
			$last_id = $conn->lastInsertId();
			
			//Corrugated Box
			$sql = "SELECT GROUP_CONCAT(temp_pack_box_no) as temp_pack_box_no  FROM tbl_package_box_details_temp 
			WHERE temp_box_id = 1 AND temp_so_id = ".$_REQUEST['so_id']." ";
			$res1 = $conn->query($sql);
			
			$boxtype1 = $boxtype2 = $boxtype3 = $boxtype4=0;
			
			if($res1->rowCount()>0)
			{
				while ($obj1 = $res1->fetch())
				{
					if($obj1->temp_pack_box_no !='')
					{
						$box_no = explode(',', $obj1->temp_pack_box_no);
						$result = array_unique($box_no, SORT_REGULAR);
						$boxtype1 = sizeof($result);
					}
				}
			}
			
			//Wooden Box
			$sql2 = "SELECT GROUP_CONCAT(temp_pack_box_no) as temp_pack_box_no  
					FROM tbl_package_box_details_temp WHERE temp_box_id = 2 AND temp_so_id = ".$_REQUEST['so_id']." ";
			$res2 = $conn->query($sql2);
			
			if ($res2->rowCount()>0)
			{
				while ($obj2 = $res2->fetch())
				{
					if($obj2->temp_pack_box_no !='')
					{
						$box_no = explode(',', $obj2->temp_pack_box_no);
						$result2 = array_unique($box_no, SORT_REGULAR);
						$boxtype2 = sizeof($result2);
					}
					
				}
			}
			
			//Gunny Bags
			$sql = "SELECT GROUP_CONCAT(temp_pack_box_no) as temp_pack_box_no  FROM tbl_package_box_details_temp WHERE temp_box_id = 3 AND temp_so_id = ".$_REQUEST['so_id']." ";
			$res3 = $conn->query($sql);
			
			if ($res3->rowCount()>0)
			{
				while ($obj3 = $res3->fetch()){
					if($obj3->temp_pack_box_no !='')
					{
						$box_no = explode(',', $obj3->temp_pack_box_no);
						$result3 = array_unique($box_no, SORT_REGULAR);
						$boxtype3 = sizeof($result3);
						
					}
				}
			}
			
			//Poly Bag
			$sql = "SELECT GROUP_CONCAT(temp_pack_box_no) as temp_pack_box_no  FROM tbl_package_box_details_temp WHERE temp_box_id = 4 AND temp_so_id = ".$_REQUEST['so_id']." ";
			$res4 = $conn->query($sql);
		
			if ($res4->rowCount()>0)
			{
				while ($obj4 = $res4->fetch()){
					if($obj4->temp_pack_box_no !='')
					{
						$box_no = explode(',', $obj4->temp_pack_box_no);
						$result4 = array_unique($box_no, SORT_REGULAR);
						$boxtype4 = sizeof($result4);
						
					}
				}
			}
			echo $temp_pack_id.'-'.$boxtype1.'-'.$boxtype2.'-'.$boxtype3.'-'.$boxtype4;
		}
		else
		{
			$pack_box_no = implode(', ', $_REQUEST['box_no']);
			$pack_item_qty = implode(', ', $_REQUEST['qty']);
			
			$stmt = $conn->prepare("UPDATE  tbl_package_box_details_temp SET temp_so_id = :temp_so_id, temp_pack_box_no = :temp_pack_box_no, temp_pack_item_qty = :temp_pack_item_qty, temp_box_id = :temp_box_id, temp_dispatch_qty = :temp_dispatch_qty, session_id = :session_id, token = :token, temp_total_qty = :temp_total_qty WHERE temp_dc_id = :temp_dc_id AND temp_item_id = :temp_item_id");		
			$data = array(
				':temp_dc_id' => $_REQUEST['dc_id'],				
				':temp_item_id' => $_REQUEST['item_id'],
				':temp_so_id' => $_REQUEST['so_id'],
				':temp_pack_box_no' => $pack_box_no,
				':temp_pack_item_qty' => $pack_item_qty,
				':temp_box_id' => $_REQUEST['box_id'],
				':temp_dispatch_qty' => $_REQUEST['dispatch_qty'],
				':session_id' => $_SESSION['session_id'],
				':token' => $_REQUEST['token'],
				':temp_total_qty' => $_REQUEST['total']
			);
		
			$stmt->execute($data);
			//echo '1';
			
			$sql = "SELECT GROUP_CONCAT(temp_pack_box_no) as temp_pack_box_no FROM tbl_package_box_details_temp WHERE temp_box_id = 1 AND temp_so_id = ".$_REQUEST['so_id']." ";
			$res = $conn->query($sql);
			$boxtype1 = $boxtype2 = $boxtype3 = $boxtype4=0;
			
			if ($res->rowCount()>0)
			{
				
				while ($obj = $res->fetch()){
					if($obj->temp_pack_box_no !='')
					{
						$box_no = explode(',', $obj->temp_pack_box_no);
						$result1 = array_unique($box_no, SORT_REGULAR);
						$boxtype1 = sizeof($result1);
					}
				}
			}
			
			$sql2 = "SELECT GROUP_CONCAT(temp_pack_box_no) as temp_pack_box_no FROM tbl_package_box_details_temp WHERE temp_box_id = 2 AND temp_so_id = ".$_REQUEST['so_id']." ";
			$res2 = $conn->query($sql2);
		
			if ($res2->rowCount()>0)
			{
				while ($obj2 = $res2->fetch())
				{
					if($obj2->temp_pack_box_no !='')
					{
						$box_no = explode(',', $obj2->temp_pack_box_no);
						$result2 = array_unique($box_no, SORT_REGULAR);
						$boxtype2 = sizeof($result2);
					}
				}
			}
			
			$sql = "SELECT GROUP_CONCAT(temp_pack_box_no) as temp_pack_box_no FROM tbl_package_box_details_temp WHERE temp_box_id = 3 AND temp_so_id = ".$_REQUEST['so_id']." ";
			$res3 = $conn->query($sql);
		
			if ($res3->rowCount()>0)
			{
				while ($obj3 = $res3->fetch()){
					if($obj3->temp_pack_box_no !='')
					{
						$box_no = explode(',', $obj3->temp_pack_box_no);
						$result3 = array_unique($box_no, SORT_REGULAR);
						$boxtype3 = sizeof($result3);
					}
				}
			}
			
			$sql = "SELECT GROUP_CONCAT(temp_pack_box_no) as temp_pack_box_no FROM tbl_package_box_details_temp WHERE temp_box_id = 4 AND temp_so_id = ".$_REQUEST['so_id']." ";
			$res4 = $conn->query($sql);
		
			if ($res4->rowCount()>0)
			{
				while ($obj4 = $res4->fetch())
				{
					if($obj4->temp_pack_box_no !='')
					{
						$box_no = explode(',', $obj4->temp_pack_box_no);
						$result4 = array_unique($box_no, SORT_REGULAR);
						$boxtype4 = sizeof($result4);
					}
				}
			}
			
			echo $temp_pack_id.'-'.$boxtype1.'-'.$boxtype2.'-'.$boxtype3.'-'.$boxtype4;
		}

	}
	catch (Exception $e)
	{		
		$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
		$_SESSION['_msg_err'] = $str;			
	}
}