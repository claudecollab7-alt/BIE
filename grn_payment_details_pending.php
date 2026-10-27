<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

// 2026-10-27 bill due date = party bill date + supplier credit days - it read an unset variable, so it showed today + credit days
// 2026-10-26 supplier names / credit days and paid amounts read once for the list, not 3 queries per GRN
// 2026-10-13 list limited to the branches the user approves for (GRN_PAY in task_queues.php), the same rule as its dashboard tile
    // ini_set('display_errors', '1');
    // ini_set('display_startup_errors', '1');
    // error_reporting(E_ALL);
?>


<!DOCTYPE html>
<html lang="en">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - GRN Payment Penidng List</title>
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
                            <span class="breadcrumb-item active">Accounts</span>
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
                                <h6 class="card-title"> List of GRN Payments </h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                    <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                            <table class="datatable-col7 table table-xs table-hover table-bordered" id="divisionTable">
                            <thead>
                                        <tr class="bg-table-header">
                                        <th width="5px">#</th>
                                        <th>GRN No</th>
                                        <th>GRN Date</th>
                                        <th>Supplier Name</th>
                                        <th style="text-align:center"> Party Bill Date</th>
                                        <th style="text-align:center"> Bill Due Date</th>
                                        <th style="text-align:center"> Bill Amount</th>
                                        <th style="text-align:center"> Paid Amount</th>
                                        <th style="text-align:center">Bill Status</th>
                                        <th style="text-align:center">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                            
                                        <?php 									
                                            $SQL = "SELECT * FROM tbl_grn WHERE grn_status = '2' AND grn_bill_status != '1' AND grn_pay_status != '1' " . fnQueueScopeSql('GRN_PAY');
                                            
                                            $SQL .= " ORDER BY grn_id DESC";

                                            $result = $conn->query($SQL);
                                            $grn_rows = $result->fetchAll();

                                            // supplier names / credit days and amounts paid, read once for all the GRNs listed
                                            $grn_ids = $supp_ids = array(0);
                                            foreach ($grn_rows as $r) {
                                                $grn_ids[] = (int)$r->grn_id;
                                                $supp_ids[] = (int)$r->supp_id;
                                            }
                                            $in = implode(',', array_unique($supp_ids));
                                            $m_supp_name = $dbconn->GetMap("SELECT supp_id, supp_name FROM mst_supplier_new WHERE supp_id IN ($in)");
                                            $m_supp_days = $dbconn->GetMap("SELECT supp_id, supp_credit_days FROM mst_supplier_new WHERE supp_id IN ($in)");
                                            $m_paid = $dbconn->GetMap("SELECT grn_id, SUM(pay_amount) FROM tbl_grn_pay_receipt WHERE grn_id IN (" . implode(',', array_unique($grn_ids)) . ") GROUP BY grn_id");
                                            
                                            if ($result->rowCount() > 0)
                                            {										
                                                $Sno = 1;
                                                foreach ($grn_rows as $obj)
                                                {
                                                    $supp_name = $dbconn->MapValue($m_supp_name, $obj->supp_id);
                                                    $supp_credit_days = $dbconn->MapValue($m_supp_days, $obj->supp_id);
                                                    // $party_bill_date = $dbconn->GetSingleReconrd("tbl_purchase_inward","party_bill_date","pi_id",$obj->pi_id);


                                                    $grn_due_date =  date("Y-m-d", strtotime($obj->party_bill_date. ' +'.(int)$supp_credit_days.' day'));   // from the party bill date (was today + credit days)

                                                    if($obj->party_bill_date == "0000-00-00"){
                                                        $next_dt = "";
                                                    }
                                                    else{
                                                        if(($grn_due_date >= date("Y-m-d",strtotime('+6 days'))))
                                                        {
                                                            $next_dt = "<span class='badge bg-warning'>". date("d-m-Y", strtotime($grn_due_date)) ."</span>";
                                                        }
                                                        elseif(($grn_due_date < date("Y-m-d")))
                                                        {
                                                            $next_dt = "<span class='badge bg-danger'>". date("d-m-Y", strtotime($grn_due_date)) ."</span>";
                                                        }
                                                        else
                                                        {
                                                            $next_dt = "<span class='badge bg-success'>". date("d-m-Y", strtotime($grn_due_date)) ."</span>";
                                                        }
                                                    }
                                                    $pay_amount = $dbconn->MapValue($m_paid, $obj->grn_id, null);

                                                    if($obj->grn_pay_amount == $pay_amount){

                                                        $pay_status = "<span class='badge bg-success'>Completed</span>";
                                                        if($obj->grn_bill_copy !=''){
                                                            $bill_amt = '<a  target="_blank" onClick="window.open(\'project_img/grn_payment/'.$obj->grn_bill_copy.'\',\''.$obj->grn_bill_copy.'\',\'width=700,height=505,menubar=no,status=no,scrollbars=no\')" class=\'sub\' title=\''.$obj->grn_bill_copy.'\' >'.$obj->grn_pay_amount.'</a>';
                                                        }else{
                                                            $bill_amt = $obj->grn_pay_amount;

                                                        }
                                                        // $grn_pay_link = '<li><a href="" class="tip disable" title="GRN Payment"><i class="fa fa-rupee"></i></a></li>';
                                                        $paid_amt = $pay_amount;


                                                    }else if($obj->grn_pay_amount > 0){

                                                        $pay_status = "<span class='badge bg-warning'>Pending</span>";

                                                        if($obj->grn_bill_copy !=''){
                                                            $bill_amt = '<a onClick="window.open(\'project_img/grn_payment/'.$obj->grn_bill_copy.'\',\''.$obj->grn_bill_copy.'\',\'width=700,height=505,menubar=no,status=no,scrollbars=no\')"  class= \'sub\' title=\''.$obj->grn_bill_copy.'\' target="_blank" >'.$obj->grn_pay_amount.'</a>';
                                                        }else{
                                                            $bill_amt = $obj->grn_pay_amount;

                                                        }

                                                        $paid_amt = $pay_amount;


                                                    }else{

                                                        $pay_status = "";
                                                        $bill_amt = '';
                                                        $paid_amt = '';



                                                    }

                                                    $grn_pay_link = '<a href="grn_pay_receipt.php?grn_id='.$obj->grn_id.'" class="tip" title="GRN Payment"><i class="icon-pencil5 bg-edit mr-2"></i></a>';



                                                    echo '<tr>
                                                            <td>'.$Sno.'</td>
                                                            <td>'.$obj->grn_ref_code.'</td>
                                                            <td>'.date("d-m-Y", strtotime($obj->grn_date)).'</td>
                                                            <td width="40%">'.$supp_name.'</td>
                                                            <td style = "text-align:center;">'.date("d-m-Y", strtotime($obj->party_bill_date)).'</td>
                                                            <td style = "text-align:center;">'.($next_dt==""? "": $next_dt).'</td>
                                                            <td style = "text-align:center;">'.$bill_amt.'</td>
                                                            <td style = "text-align:center;">'.$paid_amt.'</td>
                                                            <td style = "text-align:center;">'.$pay_status.'</td>
                                                            <td><ul  style = "text-align:center;" class="navbar-icons">
                                                                    '.$grn_pay_link.'														
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