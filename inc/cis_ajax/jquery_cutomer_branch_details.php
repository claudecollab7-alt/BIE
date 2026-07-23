<?php

	require_once("../common/dbconnect.php");
	require_once("../common/functions.php");
	require_once("../common/dbhandler.php");

/*
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);*/


$conn = new dbconnect();
$dbconn= new dbhandler();

if(isset($_POST['supp_id']))
{
	$supp_id = $_POST['supp_id'];
	$temp_branch_name = $_POST['branch_name'];
	$temp_prefix = $_POST['prefix'];	
	$temp_branch_contact_person = $_POST['branch_contact_person'];	
	$temp_branch_contact_no = $_POST['branch_contact_no'];
	$temp_branch_add1 = $_POST['branch_add1'];
	$temp_branch_add2 = $_POST['branch_add2'];
	$temp_state_id = $_POST['state_id'];
	$temp_district_id = $_POST['district_id'];
	$temp_city_id = $_POST['city_id'];
	$temp_branch_pincode = $_POST['branch_pincode'];
	$session_id = $_POST['session_id_no'];
	
}

if($_POST['mode']=="save"  && $_POST['rec_type']=="ind")
{
	$auto_id = $dbconn->GetSingleReconrd("mst_customer_branch_temp","temp_branch_id","temp_branch_name = '".$temp_branch_name."' AND temp_branch_contact_no = '".$temp_branch_contact_no."' AND session_id",$session_id);
	if($auto_id == "")
	{
		try
		{
			$stmt = null;				
			$stmt = $conn->prepare("INSERT INTO mst_customer_branch_temp (supp_id, temp_branch_name, temp_prefix, temp_branch_contact_person, temp_branch_contact_no, temp_branch_add1, temp_branch_add2, temp_state_id, temp_district_id, temp_city_id, temp_branch_pincode, session_id, temp_date)
			 VALUES (:supp_id, :temp_branch_name, :temp_prefix, :temp_branch_contact_person, :temp_branch_contact_no, :temp_branch_add1, :temp_branch_add2, :temp_state_id, :temp_district_id, :temp_city_id, :temp_branch_pincode, :session_id, :temp_date)");		
			$data = array(				
				':supp_id' => $supp_id,			
				':temp_branch_name' => $temp_branch_name,	
				':temp_prefix' => $temp_prefix,	
				':temp_branch_contact_person' => $temp_branch_contact_person,	
				':temp_branch_contact_no' => $temp_branch_contact_no,		
				':temp_branch_add1' => $temp_branch_add1,	
				':temp_branch_add2' => $temp_branch_add2,	
				':temp_state_id' => $temp_state_id,	
				':temp_district_id' => $temp_district_id,	
				':temp_city_id' => $temp_city_id,	
				':temp_branch_pincode' => $temp_branch_pincode,		
				':session_id' => $session_id,
				':temp_date' => date('Y-m-d')
			);
			$stmt->execute($data);
		}
		catch (Exception $e)
		{		
			$str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);			
			echo $_SESSION['_msg_err'] = $str;			exit;
		}
	}
	else
	{
		
		try
		{
			$stmt = null;				
			$stmt = $conn->prepare("UPDATE mst_customer_branch_temp SET supp_id = :supp_id, temp_branch_name = :temp_branch_name, temp_prefix = :temp_prefix, temp_branch_contact_person = :temp_branch_contact_person, temp_branch_contact_no = :temp_branch_contact_no, temp_branch_add1 = :temp_branch_add1, temp_branch_add2 = :temp_branch_add2, temp_state_id = :temp_state_id, temp_district_id = :temp_district_id, temp_city_id = :temp_city_id, temp_branch_pincode = :temp_branch_pincode, session_id = :session_id, temp_date = :temp_date WHERE temp_branch_id = :temp_branch_id");		
			$data = array(
				':temp_branch_id' => $auto_id,				
				':supp_id' => $supp_id,			
				':temp_branch_name' => $temp_branch_name,	
				':temp_prefix' => $temp_prefix,	
				':temp_branch_contact_person' => $temp_branch_contact_person,	
				':temp_branch_contact_no' => $temp_branch_contact_no,		
				':temp_branch_add1' => $temp_branch_add1,	
				':temp_branch_add2' => $temp_branch_add2,	
				':temp_state_id' => $temp_state_id,	
				':temp_district_id' => $temp_district_id,	
				':temp_city_id' => $temp_city_id,	
				':temp_branch_pincode' => $temp_branch_pincode,		
				':session_id' => $session_id,
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


if($_POST['mode']=="delete"){

	$sql =  "DELETE FROM mst_customer_branch_temp WHERE temp_branch_id = '".$_POST['temp_branch_id']."'";
	$result = $conn->prepare($sql);
	$result->execute();
	
}



if($_POST['mode']=="edit_inv")
{

	$result = $conn->query("SELECT * FROM mst_customer_branch_temp WHERE temp_branch_id = ".$_REQUEST['temp_branch_id']);	
	if ($result->rowCount()>0)
	{
		$get = $result->fetch(PDO::FETCH_OBJ);		

		
		echo $get->temp_branch_id.'~'.$get->supp_id.'~'.$get->temp_branch_name.'~'.$get->temp_prefix.'~'.$get->temp_branch_contact_person.'~'.$get->temp_branch_contact_no.'~'.$get->temp_branch_add1.'~'.$get->temp_branch_add2.'~'.$get->temp_state_id.'~'.$get->temp_district_id.'~'.$get->temp_city_id.'~'.$get->temp_branch_pincode.'~';
	}
	
}


?>
<table class="table table-bordered">
    <thead>
        <tr>
            <th width="5px">#</th>
			<th>Branch Name / Contact Person</th>
			<th>Branch Address</th>
			<th width="10%">Actions</th>
		                               
        </tr>
    </thead>
                                                

	<?php 
	
			$sql_temp = "SELECT * FROM `mst_customer_branch_temp`";

			$result = $conn->query($sql_temp);
			
			if ($result->rowCount() > 0)
			{		 
				$iSno=1;
				
				echo "<tbody>";
				while ($lst = $result->fetch())
				{
					
					$branch_add = '';
					$branch_add .= $lst->temp_branch_add1;
					if($lst->temp_branch_add2!='')
					{
						$branch_add .= ','.$lst->temp_branch_add2;
					}
					$branch_add .= ',<br/>'.$dbconn->GetSingleReconrd("mst_city","city_name","city_id",$lst->temp_city_id);

					$branch_add .= ',<br/>'.$dbconn->GetSingleReconrd("mst_district","district_name","district_id",$lst->temp_district_id);

					$branch_add .= ',<br/>'.$dbconn->GetSingleReconrd("mst_state","state_name","state_id",$lst->temp_state_id);

					$branch_add .= ' - '.$lst->temp_branch_pincode;
														

						
					echo '<tr>						
							<td style = "vertical-align:top;">'.$iSno.'</td>
							<td style = "vertical-align:top;" ><b>'.$lst->temp_branch_name.'<br>'.$lst->temp_branch_contact_person.'</b><br> '.$lst->temp_branch_contact_no.'</td>
							<td style = "vertical-align:top;">'.$branch_add.'</td>
							<td align="center" style = "vertical-align:top;">
							<a href="javascript:edit_item('.$lst->temp_branch_id.');" rel="'.$lst->temp_branch_id.'" data-popup="tooltip" title="Edit" data-original-title="Edit" ><i class="icon-pencil5 bg-edit mr-2"></i></a>
							<a href="javascript:remove_item('.$lst->temp_branch_id.');" rel="'.$lst->temp_branch_id.'" data-popup="tooltip" title="Remove" data-original-title="Remove" ><i class="icon-bin bg-delete mr-2"></i></a>
								<!--<ul style = "text-align:center;" class="navbar-icons">
									<li>
										<a href="javascript:edit_item('.$lst->temp_branch_id.');" class="" rel="'.$lst->temp_branch_id.'"  title="Edit"><i class="fa fa-edit"></i></a>
									</li>
									<li>
										<a href="javascript:remove_item('.$lst->temp_branch_id.');" class="" rel="'.$lst->temp_branch_id.'"  title="Remove"><i class="fa fa-trash"></i></a>
									</li>
								</ul>-->
							</td>
						</tr>';
						
					$iSno++;
				}
				
				
				echo "</tbody>";
			}
	
	
?>
</table>