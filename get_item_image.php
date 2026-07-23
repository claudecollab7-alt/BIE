<?PHP

require_once("inc/common/userclass.php");


$conn = new dbconnect();
$dbconn = new dbhandler();


$pisql = "SELECT item_id,item_type,item_code,item_purchase_code, item_image, item_status from tbl_item_details WHERE 1 = 1";	

	$result = $conn->query($pisql);

	if ($result->rowCount() > 0)
	{	
	echo'<table border="1" style="width: 100%; height: 100%">
	<th>Item Id</th>
	<th width="10%">Item Type</th>
	<th>Item Code</th>
	<th>Item Purchase Code</th>
	<th>Item Image</th>
	<th>Item Status</th>
	<th>Status</th>
	<th>Copy Status</th>';
		while ($pod = $result->fetch())
		{
				 //https://www.geeksforgeeks.org/how-to-copy-a-file-from-one-directory-to-another-using-php/
			$copy_status="";
			
			//echo file_exists("D:\xampp7.4\htdocs\bie\project_img\item_image");
			if(($pod->item_image) != ''){
				if(file_exists("project_img/item_image/".$pod->item_image)){
					$status = 'Yes';
					//$copy_status="project_img/item_image/".$pod->item_image;
					$copy_status="-";
				}else{
					$status = 'No';
					// code to copy file
					//copy( ,"project_img/item_image/")
					if(file_exists("../benzear/project_img/item_image/".$pod->item_image))
						$copy_status ="file found";
					
					if( !copy("../benzear/project_img/item_image/".$pod->item_image,"project_img/item_image/".$pod->item_image ) ) { 
                        $copy_status .= "Don't copied!"; 
                    } 
                    else { 
                        $copy_status .= "copied!"; 
                    } 
				}
			}
		    echo'<tr width="200%">
			    <td style = "text-align:center;">'.$pod->item_id.'</td>
			    <td style = "text-align:center;">'.$pod->item_type.'</td>
			    <td style = "text-align:center;">'.$pod->item_code.'</td>
			    <td style = "text-align:center;">'.$pod->item_purchase_code.'</td>
			    <td style = "text-align:center;">'.$pod->item_image.'</td>
			    <td style = "text-align:center;">'.$pod->item_status.'</td>
			    <td style = "text-align:center;">'.$status.'</td>
				<td style = "text-align:center;">'.$copy_status.'</td>
			</tr>';
		}
		echo '</table>';
	}
	
	

?>

