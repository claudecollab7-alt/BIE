<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
// require_once ("inc/common/dbrowiterator.php");	

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

if(isset($_REQUEST['approve']) && $_REQUEST['approve'] == 0)
    $searchByStatus = 0;

$searchByBranch='1';

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
        $(document).ready(function() {
            <?php
            if (isset($_SESSION['_msg']) && $_SESSION['_msg'] != "") {
                echo "$.jGrowl('" . $_SESSION['_msg'] . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-success', position: 'bottom-right', life:'2000', header: 'Success!' });";
                $_SESSION['_msg'] = "";
            }
            if (isset($_SESSION['_msg_err']) && $_SESSION['_msg_err'] != "") {
                echo "$.jGrowl('" . $_SESSION['_msg_err'] . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-danger', position: 'top-right', shutdown:'3000', header: 'Error!' });";
                $_SESSION['_msg_err'] = "";
            }
            ?>

            //dom: '<"top"l>rt<"bottom"ip>',
            var dataTable = $('#supplierTable').DataTable({

                dom: '<"datatable-header length-left"lp><"datatable-scroll"rt><"datatable-footer"ip>',
                'processing': true,
                "language": {
                    processing: '<i class="icon-spinner spinner mr-2"></i>Loading...'
                },
                'serverSide': true,
                'serverMethod': 'post',
                'lengthChange': true, // Remove default Page Length Control
                'searching': false, // Remove default Search Control
                "pageLength": 25,
                "order": [
                    [1, "desc"]
                ],
                'ajax': {
                    'url': 'inc/datatable/ajaxSupplierList.php',
                    'data': function(data) {
                        // Read values
                        var supp = $('#searchByCode').val();
                        var searchByStatus = $('#searchByStatus').val();
                        var searchByBranch = $('#searchByBranch').val();


                        // Append to data
                        data.searchByCode = supp;
                        data.searchByStatus = searchByStatus;
                        data.searchByBranch = searchByBranch;

                    }
                },
                'columns': [{
                        data: 'sno'
                    },
                    {
                        data: 'supp_code'
                    },
                    {
                        data: 'supp_name'
                    },
                    {
                        data: 'supp_person'
                    },
                    {
                        data: 'supp_mobile'
                    },
                    {
                        data: 'supp_gst'
                    },
                    {
                        data: 'supp_approve_status'
                    },
                    {
                        data: 'branch_status'
                    },
                    {
                        data: 'supp_id'
                    },
                    {
                        data: 'action'
                    },
                ],

                columnDefs: [{
                        orderable: false,
                        targets: [0,3,4,5,7,8,9]
                    },
                    {
                        targets: [6,7,8,9],
                        className: 'text-center'
                    },
                ],

            });

            $('#searchByCode').keyup(function() {
                dataTable.draw();
            });
            $('#searchByStatus').change(function() {
                dataTable.draw();
            });
            $('#searchByBranch').change(function() {
                dataTable.draw();
            });

			$('#modalSuppDets').on('show.bs.modal', function (e) {
					var id = $(e.relatedTarget).data('id');
	//                alert(id);
					if(id !=''){
						$.ajax({
							type : 'post',
							url : 'inc/cis_ajax/modal_supplier_dets.php', 
							data :  'id='+ id, 
							success : function(data){
								//alert(data);
								string = data.split("~");
								$('#m_supp_name').html('Supplier Details - ');
								$('#m_supp_code').html(string[0]);
								$('#m_supp_dets').html(string[1]);
							}
						});
					}		
				});
            $('#modalSuppDets').on('show.bs.modal', function (e) {
                var id = $(e.relatedTarget).data('id');
//                alert(id);
                if(id !=''){
                    $.ajax({
                        type : 'post',
                        url : 'inc/cis_ajax/modal_supplier_dets.php', 
                        data :  'id='+ id, 
                        success : function(data){
                            //alert(data);
                            string = data.split("~");
                            $('#m_supp_code').html(string[0]);
                            $('#m_supp_dets').html(string[1]);
                        }
                    });
                }		
            });

            $('#supplierTable').on('click', 'a.delete', function (e) {
                e.preventDefault();
                if ( confirm( "Are you sure, you want to delete this Supplier?" ) ) {
                    
                    var id = $(this).attr('rel');
                    var table = "mst_supplier_new";
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
                                $('#supplierTable').dataTable().fnDeleteRow(nRow);
                                $.jGrowl('Supplier deleted..!', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-danger', position: 'top-right', shutdown:'3000', header: 'Success!' });
                            }
                            else if(result == 0)					
                                $.jGrowl('Supplier Not deleted..!', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-danger', position: 'top-right', shutdown:'3000', header: 'Error!' });					
                            else
                                $.jGrowl(result, { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-danger', position: 'top-right', shutdown:'3000', header: 'Error!' });
                            
                        }
                    });
                    
                }
            });
       

        });
    </script>
<?php include("modal_supp_dets.php") ?>
</head>


<body>
    <!-- Main navbar -->
    <?php include("modal_supp_dets.php") ?>
    <?php include("inc/common/header.php") ?>
    <!-- /main navbar -->


    <!-- Page content -->
    <div class="page-content">

        <!-- Main sidebar -->
        <?php include("inc/common/sidebar.php") ?>
        <!-- /main sidebar -->


        <!-- Main content -->
        <div class="content-wrapper">

            <!-- Page header -->
            <div class="page-header">
                <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                    <div class="d-flex">
                        <div class="breadcrumb">
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Home</a>
                            <a href="#" class="breadcrumb-item"> Masters</a>
                            <span class="breadcrumb-item active">Supplier List</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>
            <!-- /page header -->

            <!-- Content area -->
            <div class="content pt-0">
                <!-- Dashboard content -->
                <div class="row">
                    <div class="col-md-12">
                        <!-- This Form UI Starts here --->

                        <!-- Basic datatable -->
                        <div class="card">
                            <div class="card-header bg-pgheader text-white header-elements-inline">
                                <h6 class="card-title">List of Supplier</h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" href="mst_supplier_new.php" data-popup='tooltip' title="New Supplier"><i class="icon-plus-circle2 mr-2"></i></a>
                                        <a class="list-icons-item" data-action="fullscreen"></a>

                                    </div>
                                </div>
                            </div>

                            <div class="card-body pt-2">
                                <div class="form-group row pl-2">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Search</label>
                                            <input type='text' class="form-control" id='searchByCode' name="searchByCode" placeholder='Enter Code / Name / Mobile / GST'>
                                        </div>
                                    </div>
                                    <?php if($_SESSION['_user_branch']==1){ ?>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Branch</label>
                                            <select name="searchByBranch" id="searchByBranch" class="form-control form-control-select2">
                                            <option value="">-- All Branch--</option>
                                                <?php
                                                $dbconn = new dbhandler();
                                                echo $dbconn->fnFillComboFromTable_Where("branch_id", "branch_name", "mst_branch", "branch_id", " WHERE branch_status = '1'") ?>
                                            </select>
                                            <script>document.getElementById('searchByBranch').value="<?php echo $searchByBranch; ?>";</script>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select name="searchByStatus" id="searchByStatus" class="form-control form-control-select2">
                                            <option value="">-- Select Status --</option>
                                            <option value="0">Not Approved</option>
                                            <option value="1">Approved</option>
                                            </select>
                                            <script>document.getElementById('searchByStatus').value="<?php echo $searchByStatus; ?>";</script>
                                        </div>
                                    </div>

                                </div>
                                <hr class="mt-0 mb-1">
                                <table class="table table-xs table-hover table-bordered mt-0" id="supplierTable">
                                    <thead>
                                        <tr class="bg-table-header">
                                            <th width="2%">#</th>
                                            <th>Code</th>
                                            <th>Business Name</th>
                                            <th>Contact Person</th>
                                            <th>Mobile</th>
                                            <th>GST No</th>
                                            <th>Status</th>
											 <th>Branch</th>
                                            <th>Item Mapping</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                                
                            </div>
                        </div>
                        <!-- /basic datatable -->


                        <?php //include ('modal_view_po.php'); 
                        ?>

                        <!-- End of This Form UI  --->
                    </div>
                </div>
                <!-- /dashboard content -->
            </div>
            <!-- /content area -->

            <!-- Footer -->
            <?php include("inc/common/footer.php") ?>
            <!-- /footer -->
        </div>
        <!-- /main content -->
    </div>
    <!-- /page content -->
</body>

</html>