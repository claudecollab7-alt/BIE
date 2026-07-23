<?PHP

ob_start();

session_start();

require_once("inc/common/userclass.php");

isAdmin();
$conn = new dbconnect();
$dbconn= new dbhandler();


//ini_set('display_errors', '1');ini_set('display_startup_errors', '1');error_reporting(E_ALL);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
<title><?php echo PAGE_TITLE; ?> - Item Price History</title>
<link href="css/main.css" rel="stylesheet" type="text/css" />
<!--[if IE 8]><link href="css/ie8.css" rel="stylesheet" type="text/css" /><![endif]-->

<?php include_once("inc/common/css-js.php"); ?>


<script type="text/javascript">

$(function() {
	
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

var dataTable = $('#history_table').DataTable({

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
                    'url': 'inc/datatable/ajaxItemPriceHistoryList.php',
                    'data': function(data) {
                        // Read values
                        var supp = $('#searchByCode').val();

                        // Append to data
                        data.searchByCode = supp;
                    }
                },
                'columns': [{
                        data: 'auto_id'
                    },
                    {
                        data: 'item_code'
                    },
					{
                        data: 'item_purchase_code'
                    },
                    {
                        data: 'item_desciption'
                    },
					/*{
                        data: 'new_price'
                    },
                    {
                        data: 'new_discount'
                    },
                    {
                        data: 'new_cost_price'
                    },*/
                    {
                        data: 'item_selling_price'
                    },
                    {
                        data: 'item_min_qty'
                    },
					{
                        data: 'item_max_qty'
                    },
					{
                        data: 'item_order_min_qty'
                    },
					{
                        data: 'itm_upt'
                    },
                    
                ],

                columnDefs: [{
                        orderable: false,
                        targets: [0,8]
                    },
                    {
                        targets: [5,6,7,8],
                        className: 'text-center'
                    },
                    {
                        targets: [4,5,6,7],
                        className: 'text-right'
                    },
                ],

            });

            $('#searchByCode').keyup(function() {
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
                            <a href="#" class="breadcrumb-item"> Item Master</a>
                            <span class="breadcrumb-item active">Item Price History </span>
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
                                <h6 class="card-title">List of Item Price History </h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="lst_item_details.php" title="Item Details List"><i class="icon-arrow-left52 mr-2"></i></a>
                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                                
                            </div>

                                <div class="table-overflow">
                                    <form name='thisForm' id="validate" method='post' action="" >
                                        <table class="table table-xs table-hover table-bordered mt-0" id="itemDetailsTable1" width="100%">
                                            <tr><td>
                                        
                                                <div class="form-group row pl-2">
                                                    <div class="col-md-9">
                                                        <div class="form-group">
                                                            <label>Search </label>
                                                            <input type='text' class="form-control" id='searchByCode' name="searchByCode" placeholder='Sale Item Code / Purchase Item Code / Selling Price / MSQ / MAQ / MOQ'>
                                                        </div>
                                                    </div>

                                                </div>
                                            </td></tr>	
                                        </table>
                                        <div class="card-body pt-2">
                                        <!-- <hr class="mt-0 mb-1"> -->
                                        
                                        <table class="table table-xs table-hover table-bordered mt-0" id="history_table">						
                                            <thead>
                                                <tr class="bg-table-header">
                                                    <th width="50px">#</th>
                                                    <th>Sale Item Code</th>
                                                    <th>Purchase Item Code</th>
                                                    <th>Item Description</th>
                                                   <!-- <th>Item Price</th>
                                                    <th>Item Discount</th>
                                                    <th>Cost Price</th>-->
                                                    <th>Selling Price</th>
                                                    <th>MSQ</th>
                                                    <th>MAQ</th>
                                                    <th>MOQ</th>
                                                    <th width="15%">Update On / By</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                
                                    
                                            </tbody>
                                        </table>
                                    </form>
                                </div>
                            </div>
						</div>
                    </div>
                </div>
		    </div>
    <?php include("inc/common/footer.php") ?>

		</div>
	</div>


</body>
</html>
