<?PHP

ob_start();

session_start();

require_once("inc/common/userclass.php");

isAdmin();
$conn = new dbconnect();
$dbconn= new dbhandler();

$searchByBranch='1';
$searchByYear=$dbconn->GetSingleReconrd("mst_finyear","finyr","finyr_active",1);

//ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
<title><?php echo PAGE_TITLE; ?> - GRN List</title>

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

var dataTable = $('#grn_table').DataTable({

                dom: '<"datatable-header length-left"lp><"datatable-scroll"rt><"datatable-footer"ip>',
                'processing': true,
                'responsive': true,
                "language": {
                    processing: '<i class="icon-spinner spinner mr-2"></i>Loading...'
                },
                'serverSide': true,
                'serverMethod': 'post',
                'lengthChange': true, // Remove default Page Length Control
                'searching': false, // Remove default Search Control
                "pageLength": 25,
                "order": [
                    [0, "DESC"]
                ],
                'ajax': {
                    'url': 'inc/datatable/ajaxGrnList.php',
                    'data': function(data) {
                        // Read values
                        var supp = $('#searchByCode').val();
                        var searchByBranch = $('#searchByBranch').val();
                        var searchByYear = $('#searchByYear').val();
                        


                        // Append to data
                        data.searchByCode = supp;
                        data.searchByBranch = searchByBranch;
                        data.searchByYear = searchByYear;


                    }
                },
                'columns': [{
                        data: 'grn_id'
                    },
                    {
                        data: 'grn_ref_code'
                    },
                    {
                        data: 'grn_refno'
                    },
                    {
                        data: 'itm_grn_date'
                    },
                    {
                        data: 'supp_name'
                    },
                    {
                        data: 'po_refno'
                    },
                    {
                        data: 'itm_grn_items'
                    },
                   {
                       data: 'bill_status'
                   },
					{
                        data: 'action'
                    },
                    
                ],

                columnDefs: [{
                        orderable: false,
                        targets: [0,3,5,6,7,8]
                    },
                    {
                        targets: [3,6,7,8],
                        className: 'text-center'
                    },
                ],

            });

            $('#searchByCode').keyup(function() {
                dataTable.draw();
            });
            $('#searchByBranch').change(function() {
                dataTable.draw();
            });
            $('#searchByYear').change(function() {
                dataTable.draw();
            });
    
});
</script>


</head>

<body>
    <!-- Main navbar -->
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
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Dashboard</a>
                            <a href="#" class="breadcrumb-item"> Work Area</a>
                            <span class="breadcrumb-item active">GRN List </span>
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
                                <h6 class="card-title">List of GRN </h6>
                            </div>
                            <div class="card-body pt-2">
				                <div class="form-group row pl-2">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Search </label>
                                            <input type='text' class="form-control" id='searchByCode' name="searchByCode" placeholder='Enter GRN No. / Ref. No. , Supplier Name'>
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
                                            <label>Financial Year</label>
                                            <select name="searchByYear" id="searchByYear" class="form-control form-control-select2">
                                            <option value="">-- All Financial Year--</option>
                                                <?php
                                                $dbconn = new dbhandler();
                                                echo $dbconn->fnFillComboFromTable_Where("finyr", "finyr", "mst_finyear", "finyr", " WHERE rec_del_status = '0'") ?>
                                            </select>
                                            <script>document.getElementById('searchByYear').value="<?php echo $searchByYear; ?>";</script>
                                        </div>
                                    </div>
                                </div>
                                 <hr class="mt-0 mb-1">
                                <table class="table table-xs table-hover table-bordered mt-0" id="grn_table">						
                                    <thead>
                                        <tr class="bg-table-header">
								            <th width="50px">#</th>
                                            <th>GRN No</th>
                                            <th>Party Bill No.</th>
                                            <th>GRN Date</th>
                                            <th>Supplier Name</th>
                                            <th>PO Ref. No.</th>
                                            <th>Items</th>
                                            <th>Party Bill Status</th>
                                            <th width="10%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        	
                                    </tbody>
                                </table>
                              
						    </div>
						</div>
                    </div>
                </div>
		    </div>
    <?php include("inc/common/footer.php") ?>

		</div>
	</div>
    <?php include("modal_grn_reject_dets.php") ?>

</body>
</html>
<script type="text/javascript">
$('#modalRejectGrnDets').on('show.bs.modal', function (e) {
                var id = $(e.relatedTarget).data('id');
                // var so_slno = $('#so_slno').html();
                // var inv_netamt = $('#inv_netamt').html();
            //    alert(id);
                if(id !=''){
                    $.ajax({
                        type : 'post',
                        url : 'inc/cis_ajax/jquery_modal_grn_cancel_reason.php', 
                        data :  'id='+ id, 
                        success : function(data){
                            // alert(data);
                            string = data.split("~");
                            //  $('#m_sales_amt').html(string[2]);
                            // $('#m_sales_code').html(data);
                            $('#m_sales_rej_code').html(string[1]);
                            $('#m_sales_rej_rec').html(string[0]);

                        }
                    });
                }		
            });
</script>
