<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");

isAdmin();

$conn = new dbconnect();
$dbconn= new dbhandler();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
<title><?php echo PAGE_TITLE; ?> - Supplier</title>
<link href="css/main.css" rel="stylesheet" type="text/css" />
<!--[if IE 8]><link href="css/ie8.css" rel="stylesheet" type="text/css" /><![endif]-->

<?php include_once("inc/common/css-js.php"); ?>

<script type="text/javascript">

$(function() {
	
	<?php
		if(isset($_SESSION['_msg']) && !empty($_SESSION['_msg'])){
			echo "$.jGrowl('".$_SESSION['_msg']."', { sticky: false, theme: 'growl-success',shutdown:'0.5', header: 'Success!', position: 'bottom-right' });";
			unset($_SESSION['_msg']);
		}	
		
		if(isset($_SESSION['_msg_err'])&& !empty($_SESSION['_msg_err'])){
			echo "$.jGrowl('".$_SESSION['_msg_err']."', { sticky: false, theme: 'growl-error', shutdown:'0.5', header: 'Error!', position: 'bottom-right' });";
			unset($_SESSION['_msg_err']);
		}
	?>
	oTable = $('#lst_table').dataTable({
		"bJQueryUI": false,
		"bAutoWidth": false,
		"bStateSave": false,
		"sPaginationType": "full_numbers",
		"iDisplayLength": 25,
		"sDom": '<"datatable-header"fl>t<"datatable-footer"ip>',
		"oLanguage": {
			"sSearch": "<span>Search:</span> _INPUT_",
			"sLengthMenu": "<span>Show Records:</span> _MENU_",
			"oPaginate": { "sFirst": "First", "sLast": "Last", "sNext": ">", "sPrevious": "<" }
		},
		"aoColumnDefs": [
	      { "bSortable": false, "aTargets": [ 0,7] }
	    ]
    });

	$('#lst_table').on('click', 'a.delete', function (e) {
		e.preventDefault();
		if ( confirm( "Are you sure, you want to delete this Supplier?" ) ) {
			
			var id = $(this).attr('rel');
			var table = "mst_supplier";
			var status = "supp_status";
			var value = "0";
			var where = "supp_id";
			
			var nRow = $(this).parents('tr')[0];
			
			$.ajax({
				type:'post',
				url:'inc/cis_ajax/jquery_delete_records.php',
				data: {"id":id,"table":table,"status":status,"value":value,"where":where},
				beforeSend:function(){
					//launchpreloader();
				},
				complete:function(){
					//$.jGrowl('GSM deleted..!', { sticky: false, theme: 'growl-success',shutdown:'0.5', header: 'Success!' });
				},
				success:function(result){	
					//alert(result);
					if(result > 0)
					{
						$('#lst_table').dataTable().fnDeleteRow(nRow);
						$.jGrowl('Supplier deleted..!', { sticky: false, theme: 'growl-success',shutdown:'0.5', header: 'Success!', position :'bottom-right' });
					}
					else if(result == 0)					
						$.jGrowl('Supplier Not deleted..!', { sticky: false, theme: 'growl-error',shutdown:'0.5', header: 'Error!', position :'bottom-right' });					
					else
						$.jGrowl(result, { sticky: false, theme: 'growl-error',shutdown:'0.5', header: 'Error!', position :'bottom-right' });
					
				}
			});
			
		}
	});
	
});

</script>

</head>

<body>

    <?php include("inc/common/fixedtop.php") ?>
	<div id="container">
		<?php include("inc/common/sidebar.php") ?>
		<div id="content">
		    <div class="wrapper">
			    <div class="crumbs">
		            <ul id="breadcrumbs" class="breadcrumb"> 
		                <li><a href="home.php">Dashboard</a></li>
                        <li><a href="javascript:;">Marketing</a></li>
                        <li class="active"><a href="javascript:;">Supplier</a></li>
		            </ul>
		            <?php include("inc/common/recent_pages.php") ?>
			    </div>
                <div class="page-header margint20"></div>
                <div class="widget">
                	<div class="navbar">
                    	<div class="navbar-inner">
                        	<h6>List of Supplier</h6>
                            <ul class="nav pull-right">								
                                <!-- <li><a href="mst_supplier_add.php" data-toggle="collapse" data-target="#form_div"><i class="fa fa-plus"></i>New Supplier</a></li> -->
								
                            </ul>
                        </div>
                    </div>
                    
                    <div class="table-overflow">
                        <table class="table table-striped table-bordered table-hover" id="lst_table">
                            <thead>
                                <tr>
                                    <th width="5px">#</th>
                                    <th>Code</th>
                                    <th>Business Name</th>
                                    <th>Contact Person</th>
                                    <th>Mobile</th>
                                    <th>GST No</th>
                                    <th>Status</th>
                                  
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            
                            	<?php 									
									$SQL = "SELECT * FROM mst_supplier WHERE supp_status = '1' AND supp_type = 'S' AND supp_approve_status = '0' ORDER BY supp_id DESC";
									$result = $conn->query($SQL);
									
									if ($result->rowCount() > 0)
									{										
										$Sno = 1;
								
										while ($obj = $result->fetch())
										{

											if($obj->supp_type == 'S')
											{
												$supp_code = 'S-'.$obj->supp_code;
											}
											else
											{
												$supp_code = 'C-'.$obj->supp_code;

											}
											$contact_no = '';
											$contact_no .= $obj->supp_mobile1;
											if($obj->supp_mobile2 !='')
											{
												$contact_no .= ',<br/>'.$obj->supp_mobile2;
											}

											if($obj->supp_approval_status == '1')
											{
												$supp_status = '<span class="label label-success">Approved</span>';
											}
											else
											{
												$supp_status = '<span class="label label-info">Not Approved</span>';

											}


											echo '<tr>
													<td>'.$Sno.'</td>
													<td>'.$supp_code.'</td>
													<td><a class="fancybox fancybox.ajax" href="inc/popup/fancybox_supplier.php?supp_id='.$obj->supp_id.'">'.$obj->supp_name.'</a></td>
													<td>'.$obj->supp_contact_person1.'</td>
													<td>'.$contact_no.'</td>
													<td>'.$obj->supp_gst.'</td>
													<td>'.$supp_status.'</td>
													<td><ul  style = "text-align:left;" class="navbar-icons">
															<li><a href="mst_supplier_view.php?supp_id='.$obj->supp_id.'" class="tip" title="View"><i class="fa fa-eye"></i></a></li>
															'.$del_link .'															
														</ul></td></td>
												</tr>';												
											$Sno++;								
										}
										
										$obj=null;
								
									}								
								?>
							
                            </tbody>
                        </table>
                    </div>
                    
                </div>
		    </div>
		</div>
	</div>

    <?php include("inc/common/footer.php") ?>

</body>
</html>
