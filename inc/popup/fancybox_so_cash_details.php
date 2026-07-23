<?php
ob_start();

session_start();

require_once("../common/dbconnect.php");
require_once("../common/functions.php");
require_once("../common/dbhandler.php");

$conn = new dbconnect();
$dbconn = new dbhandler();

$receipt_id = $_GET['receipt_id'];


$cash_amount = $dbconn->GetSingleReconrd("tbl_receipt","pay_amount","receipt_id",$receipt_id);
?>
	<div class="widget" style="width:600px; margin-bottom:0px;">
		<div class="navbar" style="margin-bottom:10px;">
			<div class="navbar-inner">
				<h6> Pay Mode - Cash </h6> 	
					<ul class="nav pull-right">
                        <li><h6>&nbsp; Recived Amount : <?php echo number_format($cash_amount,2); ?> </h6></li>                       
                    </ul>				
			</div>
		</div>			
		
		<div class="row-fluid" style="margin-bottom:5px;">
			<div class="span12">				
				

			</div>									
		</div>
		
		
		<div class="row-fluid" style="margin-bottom:5px;"> 						
			<div class="span12">
			
				<table class="table table-bordered table-hover table-responsive" >		
				<thead>
					<th> Cash Denomination </th>
					<th> Cash Count </th>
					<th> Cash Value </th>
				</thead>

				
				<?php
				
				$result = $conn->query("SELECT * FROM tbl_receipt as a LEFT JOIN tbl_receipt_details as b ON a.receipt_id = b.receipt_id WHERE b.receipt_id='".$receipt_id."'");	
				if ($result->rowCount() > 0)
				{										
					$Sno = 1;
					$total_amt=0;
					echo "<tbody>";
					while ($obj = $result->fetch())
					{
					
						$cash=$dbconn->GetSingleReconrd("tbl_cash_details","cash_name","cash_id",$obj->cash_id);

						echo '<tr class="align-left">						
								<td align="right">'.$cash.'</td>
								<td align="center">'.$obj->cash_count.'</td>
								<td align="right">'.number_format($obj->cash_value,2).'</td>									
							</tr>';	
						$total_amt =$total_amt +$obj->cash_value;
						$Sno++;				
				
					}	
					echo "</tbody>";		
				 }							 
				  
				 
				  
				  echo '<tr style="font-weight:bold">								
							<td align="right"></td>	
							<td align="right"> Total </td>
							<td align="right">'.number_format($total_amt,2).'</td>
						</tr>';
				
			echo '</table>';
			?>
			
				</table>
			</div>			
		</div>
		
                    
    </div>



