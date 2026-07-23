<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
session_start();
$conn = new dbconnect();
$dbconn= new dbhandler();

if(isset($_POST['item_id']))
{
	$temp_item_id = $_POST['item_id'];
	$temp_item_qty = $_POST['item_qty'];	
	$temp_item_position = $_POST['item_position'];	
}

if($_POST['mode']=="save" && $_POST['rec_type']=="ind")
{
	$temp_id = $dbconn->GetSingleReconrd("tbl_item_group_details_temp","temp_id","temp_item_id = '".$temp_item_id."' AND session_id",$_SESSION['session_id']);
	
	if($temp_id == "")
	{
		try
		{
			$stmt = null;				
			$stmt = $conn->prepare("INSERT INTO tbl_item_group_details_temp (temp_item_id, temp_item_qty, temp_item_position, session_id, temp_date) VALUES 
												(:temp_item_id, :temp_item_qty, :temp_item_position, :session_id, :temp_date)");		
			$data = array(
				':temp_item_id' => $temp_item_id,
				':temp_item_qty' => $temp_item_qty,
				':temp_item_position' => $temp_item_position,
				':session_id' => $_SESSION['session_id'],
				':temp_date' => date('Y-m-d')	
			);
			$stmt->execute($data);
		}
		catch (Exception $e)
		{		
			$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
			echo $_SESSION['_msg_err'] = $str;			
		}
	}
	else
	{
		try
		{
			$stmt = null;				
			$stmt = $conn->prepare("UPDATE tbl_item_group_details_temp SET temp_item_id = :temp_item_id, temp_item_qty = :temp_item_qty, temp_item_position = :temp_item_position, session_id = :session_id, temp_date = :temp_date WHERE temp_id = :temp_id");		
			$data = array(
				':temp_id' => $temp_id,
				':temp_item_id' => $temp_item_id,
				':temp_item_qty' => $temp_item_qty,
				':temp_item_position' => $temp_item_position,
				':session_id' => $_SESSION['session_id'],
				':temp_date' => date('Y-m-d')
			);
			$stmt->execute($data);
		}
		catch (Exception $e)
		{		
			$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
			echo $_SESSION['_msg_err'] = $str;			
		}
	}
}

if($_POST['mode']=="save" && $_POST['rec_type']=="group")
{
	try
	{
		$group_id= $_POST['group_id'];
		$item_qty= $_POST['item_qty'];
		$temp_item_position= $_POST['item_position'];
		
		$qry="select * from tbl_item_group_details where item_group_id=". $group_id." ORDER BY item_position ASC ";		
		$qry_res = $conn->query($qry);
    
		if ($qry_res->rowCount() > 0)
		{		 
			while ($qry_obj = $qry_res->fetch())
			{ 		
				$temp_id = $dbconn->GetSingleReconrd("tbl_item_group_details_temp","temp_id","temp_item_id = '".$qry_obj->item_id."'  AND session_id",$_SESSION['session_id']);				
							
				if($temp_id == "")
				{
					try
					{
						$stmt = null;				
						$stmt = $conn->prepare("INSERT INTO tbl_item_group_details_temp (temp_item_id, temp_item_qty, temp_item_position, session_id, temp_date) VALUES 
															(:temp_item_id, :temp_item_qty, :temp_item_position, :session_id, :temp_date)");		
						$data = array(
							':temp_item_id' => $qry_obj->item_id,
							':temp_item_qty' => $item_qty,
							':temp_item_position' => $qry_obj->item_position,
							':session_id' => $_SESSION['session_id'],
							':temp_date' => date('Y-m-d')	
						);
						$stmt->execute($data);
					}
					catch (Exception $e)
					{		
						$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
						$_SESSION['_msg_err'] = $str;			
					}
				}
				else
				{					
					try
					{
						/*$temp_qty = $dbconn->GetSingleReconrd("tbl_item_group_details_temp","temp_id","temp_item_id = '".$qry_obj->item_id."'  AND session_id",$_SESSION['session_id']);	*/
						
						$qty= $qry_obj->item_qty + $item_qty  ;
						
						$stmt = null;	
						
						$stmt = $conn->prepare("UPDATE tbl_item_group_details_temp SET temp_item_id = :temp_item_id, temp_item_qty = :temp_item_qty, temp_item_position = :temp_item_position, session_id = :session_id, temp_date = :temp_date WHERE temp_id = :temp_id");	
						
						$data = array(
							':temp_id' => $temp_id,
							':temp_item_id' => $qry_obj->item_id,
							':temp_item_qty' => $qty,
							':temp_item_position' => $qry_obj->item_position,
							':session_id' => $_SESSION['session_id'],
							':temp_date' => date('Y-m-d')	
						);
						$stmt->execute($data);
					}
					catch (Exception $e)
					{		
						$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
						$_SESSION['_msg_err'] = $str;			
					}
				}
		
			}
		}
	}
	catch (Exception $e)
	{		
		$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
		echo $_SESSION['_msg_err'] = $str;			
	}
}

if($_POST['mode']=="delete")
{

	$sql =  "DELETE FROM tbl_item_group_details_temp WHERE temp_id = '".$_POST['temp_id']."'";
	$result = $conn->prepare($sql);
	$result->execute();
	
}if($_POST['mode']=="update_temp")
{
	if($_POST['temp_pos']!=''){
	$sql =  "UPDATE tbl_item_group_details_temp SET temp_item_position = ".$_POST['temp_pos']." WHERE temp_id = '".$_POST['temp_id']."'";
	$result = $conn->query($sql);
	}if($_POST['temp_qty']!=''){
	$sql =  "UPDATE tbl_item_group_details_temp SET temp_item_qty = ".$_POST['temp_qty']." WHERE temp_id = '".$_POST['temp_id']."'";
	$result = $conn->query($sql);
	}
	
}

?>
<table class="table table-xs table-bordered">
    <thead>
        <tr class="bg-teal">
            <th width="1%">S.No</th><?php if($_SESSION['_user_type'] != 'S') { ?>
            <th width="1%">Position</th>
			<?php } ?>
            <th width="20%">Item Description-Sales Code</th>
            <th width="5%">Quantity</th>
            <th width="5%">Action</th>
            
        </tr>
    </thead>
                                                
<?php
	
	$sql_temp = "SELECT * FROM tbl_item_group_details_temp ORDER BY temp_item_position ASC";
	$result = $conn->query($sql_temp);
    
    if ($result->rowCount() > 0)
    {		 		
		$iSno=1;
		echo "<tbody>";
		while ($obj = $result->fetch())
		{ 
			$dbconn= new dbhandler(); 
				$product_decp = $dbconn->GetSingleReconrd("tbl_item_details","item_desciption","item_status = '1' AND item_id ",$obj->temp_item_id);
				$product_code = $dbconn->GetSingleReconrd("tbl_item_details","item_code","item_status = '1' AND item_id ",$obj->temp_item_id);
				
				echo '<tr>
						<td>'.$iSno.'</td>';
						if($_SESSION['_user_type'] != 'S') {
						echo '<td><input type="hidden" name="temp_id[]" class="temp_id" value="'.$obj->temp_id.'" /> <input type="text" name="temp_position[]" class="temp_position" onkeypress="return isNumberKey(event)" value="'.$obj->temp_item_position.'" maxlength="2" /> </td>';
						}
						echo '<td>'.$product_decp.' - '.$product_code.'</td>';
						if($_SESSION['_user_type'] != 'S') {
						echo '<td><input type="text" name="temp_qty[]" class="temp_qty" value="'.$obj->temp_item_qty.'"  onkeypress="return isNumberKey_With_Dot(event)" maxlength="3" /> </td>';
						}else{
							echo '<td align="center">'.$obj->temp_item_qty.'</td>';
						}
						
						echo '<td align="center"><a href="javascript:remove_item('.$obj->temp_id.');" class="" rel="'.$obj->temp_id.'"  title="Remove"><i class="icon-bin bg-delete mr-2"></i></a></td>
						</tr>';
            $iSno++;
        }
		
		echo "</tbody>";			
    }
?>
</table>
<script>

	$(".temp_position").change(function(){
		var temp_pos = $(this).val();
		var temp_id = $(this).closest('tr').find('.temp_id').val();
		$.ajax({
			type: "POST",
			url: "inc/cis_ajax/jquery_item_group_details.php",
			data: {temp_id:temp_id,temp_pos:temp_pos,mode:'update_temp'}
			}).done(function( msg ) {		
				$('#show_table').html(msg);
				var n = msg.indexOf("tbody");
				$('#trade_items').val(n);		
			});	
	});
	$(".temp_qty").change(function(){
		var temp_qty = $(this).val();
		var temp_id = $(this).closest('tr').find('.temp_id').val();
		$.ajax({
			type: "POST",
			url: "inc/cis_ajax/jquery_item_group_details.php",
			data: {temp_id:temp_id,temp_qty:temp_qty,mode:'update_temp'}
			}).done(function( msg ) {		
				$('#show_table').html(msg);
				var n = msg.indexOf("tbody");
				$('#trade_items').val(n);		
			});	
	});
</script>