<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

    // ini_set('display_errors', '1');
    // ini_set('display_startup_errors', '1');
    // error_reporting(E_ALL);
?>


<!DOCTYPE html>
<html lang="en">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Item Details</title>
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
                            <a href="#" class="breadcrumb-item"> Dashboard</a>
                            <span class="breadcrumb-item active">Item Details</span>
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
                                <h6 class="card-title"> New Item for Approval </h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                    <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                            <table class="datatable table table-xs table-hover table-bordered" id="SalesTable">
                            <thead>
                                        <tr class="bg-table-header">
                                            <th style="text-align:left;">Purchase Code</th>
                                            <th style="text-align:left;">Sales Code</th>
                                            <th style="text-align:center;">Item Image</th>
                                            <th style="text-align:left;">Description</th>
                                            <th style="text-align:left;">Division</th>
                                            <th style="text-align:center;">HSN</th>
                                            <th style="text-align:center;">UOM</th>
                                            <th style="text-align:center;">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        $sql = "SELECT * FROM tbl_item_details WHERE branch_status IN (0)";
                                        $searchRes1 = $conn->query($sql);
                                        $iSno = 1;
                                        if ($searchRes1->rowCount() > 0) {
                                            while ($rs = $searchRes1->fetch()) {
                                                // $so_refno = '<a data-toggle="modal" data-target="#modalSalesDets" data-id="'.$rs->so_id.'" data-popup="tooltip" title="Sales Details">'.
                                                // $dbconn->GetSingleReconrd("tbl_sales_order", "so_refno", "so_id", $rs->so_id) .'</a>';
                                                // $so_refno = '<a data-toggle="modal" data-target="#modalSalesDets" href="" data-id="'.$rs->so_id.'" data-popup="tooltip">'.$rs->so_refno.'</a>';
                                                // $quotation_link = '<a href="print_quotation.php?quo_id=' . $rs->quo_id . '" target="_blank">' .
                                                // $dbconn->GetSingleReconrd("tbl_quotation", "quo_refno", "quo_id", $rs->quo_id) . '</a>';
                                                $item_division = $dbconn->GetSingleReconrd("mst_division", "division_name", "division_id", $rs->item_division);
                                                $item_hsn = $dbconn->GetSingleReconrd("mst_hsn", "hsn_code", "hsn_id", $rs->item_hsn);
                                                $item_uom = $dbconn->GetSingleReconrd("mst_uom", "uom_name", "uom_id", $rs->item_uom);

                                                // if($rs->so_verify_status == 1 && $rs->pay_status == 3) {
                                                //     $so_status = '<span class="badge bg-warning">In Approval</span>';
                                                // }
                                                if ($rs->item_image != "") {
                                                    $item_image = '<a class="fancybox" href="project_img/item_image/' . $rs->item_image . '"><img src="project_img/item_image/' . $rs->item_image . '" width="50px" height="50px" alt=""></a>';
                                                } else {
                                                    $item_image    = '<img class="fancybox"  src="project_img/no-image.jpg" width="50px" height="50px" >';
                                                }
                                               $item_desciption = '<a data-toggle="modal" data-target="#modalitemDets" href="" data-id="' . $rs->item_id . '" data-popup="tooltip" title="Item Details">' . $rs->item_desciption . '</a>';

                                               
                                                $branch_wise_link = '<a href="branch_wise_item_update.php?item_id=' . $rs->item_id . '" class="tip" title="Branch Wise Item Update" data-original-title="Item Price Update"><i class="icon-pencil4 mr-2"></i></a>';
                                              
                                                echo '<tr>';
                                                echo '<td class="text-left">' .  $rs->item_purchase_code. '</td>';
                                                echo '<td class="text-left">' . $rs->item_code. '</td>';
                                                echo '<td class="text-center">' . $item_image. '</td>';
                                                echo '<td class="text-left">' .$item_desciption .'</td>';
                                                echo '<td class="text-left">' . $item_division.'</td>';
                                                echo '<td class="text-center">' . $item_hsn . '</td>';
                                                echo '<td class="text-center">' . $item_uom . '</td>';
                                                echo '<td class="text-center">'.$branch_wise_link.'</td>';
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
        <?php include("modal_item_dets.php") ?>
    </div>
 
    
</body>

</html>
<script>

$(document).ready(function() {

        $('#modalitemDets').on('show.bs.modal', function (e) {
            var id = $(e.relatedTarget).data('id');
        //    alert(id);
            if(id !=''){
                $.ajax({
                    type : 'post',
                    url : 'inc/cis_ajax/modal_items_dets.php', 
                    data :  'id='+ id, 
                    success : function(data){
                        //alert(data);
                        string = data.split("~");
                        $('#m_item_id').html(string[0]);
                        $('#m_item_desciption').html(string[1]);
                    }
                });
            }		
        });
});

</script>