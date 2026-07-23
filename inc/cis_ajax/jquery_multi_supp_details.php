<?php
require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");
session_start();
$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);


	


if ($_POST['mode'] == "getsupp") 
{
																						
	echo'
														
	
		<tr id="'.$_POST['id'].'" >
		    <td width="100%" style="border-bottom-style: none;border-top-style: none;"><select data-placeholder="Select Supplier" name="supp_supp_id[]" class=" select-search supp_supp_id itmid_'.$_POST['id'].'" data-suppitm = "'.$_POST['id'].'">
			    <option value="">--Select Supplier--</option> ';

				$supp_ids = $dbconn->GetSingleReconrd("tbl_item_details","supp_id","item_status = '1' AND item_id",$_POST['id']);
			
				$dbconn= new dbhandler(); 
			
				if($supp_ids != '')
				{																
					$SQL2 = "SELECT supp_id,supp_name FROM mst_supplier_new  WHERE supp_id IN (".$supp_ids.") AND supp_status='1' AND  company_branch_id= '".$_SESSION['_user_branch']."' AND  supp_type = 'S' order by supp_name asc ";
				}else
				{
					$SQL2 = "SELECT supp_id,supp_name FROM mst_supplier_new  WHERE supp_status='1' AND  company_branch_id= '".$_SESSION['_user_branch']."'  AND supp_type = 'S' order by supp_name asc ";
				}
				$res1 = $conn->query($SQL2);
				if ($res1->rowCount() > 0)
				{
					while($ob1 = $res1->fetch(PDO::FETCH_OBJ))
					{
						$selected = "";
						$supp_name = $dbconn->GetSingleReconrd("mst_supplier_new","supp_name","supp_id = '".$ob1->supp_id."' AND  company_branch_id= '".$_SESSION['_user_branch']."' AND  1",1);
						if(isset($_REQUEST['po_prepare_id']) && $_REQUEST['po_prepare_id'] > 0)
						{
							$supp_id_update = $dbconn->GetSingleReconrd("tbl_po_prepare as a,tbl_po_prepare_dets b","b.supp_id","a.po_prepare_id = b.po_prepare_id AND b.item_id = ".$_POST['id']." AND a.po_prepare_id",$_REQUEST['po_prepare_id']);
							$selected = "";
							if($supp_id_update == $ob1->supp_id)
							{
										$selected = " selected ";
							}
						}
		   
					   echo '<option value="'. $ob1->supp_id.'"  '.$selected.' >'. $supp_name.' </option>';	
					}
				}
			
		    echo '</select></td>'; 
			$si_qty = $dbconn->GetSingleReconrd("tbl_store_indent_details","si_qty","si_id ='".$_POST['si_id']."' AND item_id",$_POST['id']);
			echo '<td width="20%" style="border-bottom-style: none;border-top-style: none;"><input type="hidden" class="  new_item_id" name="new_item_id[]" value="'.$_POST['id'].'" />
			<input  size="5%"  type="text" onkeypress="return isNumberKey(event)" class="  pi_new_qty qty_'.$_POST['id'].'" maxlength="8" name="pi_new_qty[]" data-qtyid="'.$_POST['id'].'"  data-qtyval="'.$si_qty.'" value="" /></td>
			<td style="border-bottom-style: none;border-top-style: none;"><a class="delete" data-delid="'.$_POST['count'].'"  title="Remove"><i class="fa fa-times-circle" style="padding: 10px;"></i></a></td>';
		
		echo ' </tr>';
						
						
}                                                            
?>
 