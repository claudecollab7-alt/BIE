<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
?>


<!DOCTYPE html>
<html lang="en">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Sales Order</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <?php include_once("inc/common/css-js.php"); ?>
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
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
                            <a href="#" class="breadcrumb-item"> Work Area</a>
                            <span class="breadcrumb-item active">Sales Order</span>
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

                        <div class="card">
                            <div class="card-header bg-pgheader text-white header-elements-inline">
                                <h6 class="card-title"> Credit Sales Order for Approval </h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                    <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                            <table class="datatable-col8 table table-xs table-hover table-bordered" id="SalesTable">
                            <thead>
                                        <tr class="bg-table-header">
                                            <th>#</th>
                                            <th> SO No.</th>
                                            <th width="9%">SO Date</th>
                                            <th width="20%">Dealer Name</th>
                                            <th >Quotation</th>
                                            <th class="text-left">SO Value</th>
                                            <th class="text-left">Balance Value</th>
                                            <th width="10%" class="text-center">SO Status</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        $sql = "SELECT * FROM tbl_sales_order WHERE pay_status IN (3)";
                                        $searchRes1 = $conn->query($sql);
                                        $iSno = 1;
                                        if ($searchRes1->rowCount() > 0) {
                                            while ($rs = $searchRes1->fetch()) {
                                                // $so_refno = '<a data-toggle="modal" data-target="#modalSalesDets" data-id="'.$rs->so_id.'" data-popup="tooltip" title="Sales Details">'.
                                                // $dbconn->GetSingleReconrd("tbl_sales_order", "so_refno", "so_id", $rs->so_id) .'</a>';
                                                $so_refno = '<a data-toggle="modal" data-target="#modalSalesDets" href="" data-id="'.$rs->so_id.'" data-popup="tooltip">'.$rs->so_refno.'</a>';
                                                $quotation_link = '<a href="print_quotation.php?quo_id=' . $rs->quo_id . '" target="_blank">' .
                                                $dbconn->GetSingleReconrd("tbl_quotation", "quo_refno", "quo_id", $rs->quo_id) . '</a>';

                                                if($rs->so_verify_status == 1 && $rs->pay_status == 3) {
                                                    $so_status = '<span class="badge bg-warning">In Approval</span>';
                                                }
                                               
                                                $approve_link =  '<a data-toggle="modal" data-target="#modalSoDets" href="" data-id="'.$rs->so_id.'"  data-popup="tooltip" title="Approve the Sales Order" ><i class="fa fa-check bg-edit mr-2"></i></a>';
                                              
                                                $so_print = '<a href="print_sales_order.php?so_id=' . $rs->so_id . '" class="tip" title="Print Sales Order"><i class="icon-printer bg-edit mr-2"></i></a>';
                                                echo '<tr>';
                                                echo '<td>' . $iSno . ' </td>';
                                                echo '<td>' .  $so_refno. '</td>';
                                                echo '<td>' . date("d-m-Y", strtotime($rs->so_date)) . '</td>';
                                                echo '<td>' . $dbconn->GetSingleReconrd("mst_supplier_new","supp_name","supp_id",$rs->supp_id).'</td>';
                                                echo '<td class="text-center">' .$quotation_link .'</td>';
                                                echo '<td class="text-right">' . $rs->item_net_val . '</td>';
                                                echo '<td class="text-right">' . $rs->bal_value . '</td>';
                                                echo '<td class="text-center">' . $so_status.'</td>';
                                                echo '<td>'.$approve_link.$so_print.'</td>';
                                                echo '</tr>';
                                                $iSno++;
                                            }
                                        } 
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- End of This Form UI  --->
                    </div>
                </div>
                <!-- /dashboard content -->
            </div>
            <?php include("inc/common/footer.php") ?>
        </div>
        <?php include("modal_sales_det.php") ?>
    </div>
    <?php include("modal_so_det.php") ?>
    
</body>

</html>
<script>

$('#modalSoDets').on('show.bs.modal', function (e) {
                var id = $(e.relatedTarget).data('id');
                
            //    alert(id);
            var so_slno = $('#so_slno').html();
            // alert( id);
                if(id !=''){
                    $.ajax({
                        type : 'post',
                        url : 'inc/cis_ajax/jquery_modal_so_det.php', 
                        data :  'id='+ id, 
                        success : function(data){
                           
                            string = data.split("~");
                             $('#m_sales_code1').html(string[0]);
                            $('#m_sales_id').html(string[1]);
                            // $('#m_sales_name').html(string[2]);

                        }
                    });
                }		
            });


            $('#modalSalesDets').on('show.bs.modal', function (e) {
                var id = $(e.relatedTarget).data('id');
                
            // alert(id);
                if(id !=''){
                    $.ajax({
                        type : 'post',
                        url : 'inc/cis_ajax/jquery_modal_sales_det.php', 
                        data :  'id='+ id, 
                        success : function(data){
                            // alert(data);
                            string = data.split("~");
                            //  $('#m_sales_amt').html(string[2]);
                            // $('#m_sales_code').html(data);
                            $('#m_sales_code').html(string[1]);
                            $('#m_sales_rec').html(string[0]);

                        }
                    });
                }		
            });

</script>