<?php
ob_start();
session_start();
require_once("inc/common/userclass.php");
isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();


// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

$from_date = $dbconn->GetSingleReconrd("mst_finyear","finyr_startdt","finyr_active",1);
if(isset($_REQUEST['from_date']) && $_REQUEST['from_date'] != ''){
	$from_date = $_REQUEST['from_date'];
}
$to_date   = $dbconn->GetSingleReconrd("mst_finyear","finyr_enddt","finyr_active",1);
if(isset($_REQUEST['to_date']) && $_REQUEST['to_date'] != ''){
	$to_date = $_REQUEST['to_date'];
}

if ($_REQUEST['branch_id'] == '')
    $branch = 1;
else
    $branch = $_REQUEST['branch_id'];


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Sales Order - Report</title>

    <?php include_once("inc/common/css-js.php"); ?>

    <script type="text/javascript" src="print_me.js"></script>
    <script src="js/jquery.table2excel.min.js"></script>
    <script src="js/html2pdf.bundle.min.js"></script>

    <script>
        $(function() {

            $(".rpt_export").click(function(e) {
                var table = $('#lst_table');
                if (table && table.length) {
                    $(table).table2excel({
                        exclude: ".noExl",
                        name: "SR",
                        filename: "SR" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xls",
                        fileext: ".xls",
                        exclude_img: true,
                        exclude_links: true,
                        exclude_inputs: true,
                        preserveColors: true,
                    });
                }
            });

            $(".rpt_pdf").click(function(e) {
                var element = document.getElementById('rpt_division');
                var opt = {
                    margin: 1,
                    filename: '<?php echo "SR" . date("dMY"); ?>' + '.pdf',
                    image: {
                        type: 'jpeg',
                        quality: 1
                    },
                    html2canvas: {
                        scale: 2,
                        logging: true
                    },
                    jsPDF: {
                        unit: 'cm',
                        format: 'A4',
                        orientation: 'portrait'
                    }
                };
                html2pdf().set(opt).from(element).save();
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
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i>Home</a>
                            <a href="#" class="breadcrumb-item"> Report</a>
                            <span class="breadcrumb-item active">Sales Order </span>
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
                                <h6 class="card-title">Sales Order - Report </h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>

                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>

                            <form name='thisForm' id="validate" method='post' action="" onSubmit="return fnValidate();">
                                <!-- <table style="border:1px solid #d5d5d5; margin:1px 0px; background:#fafafa;" cellpadding="10" width="100%"> -->
                                <div class="card-body pt-2 pb-5">
                                        <div class="form-group row">


                                            <div class="form-group col-md-2">
                                                <label>From Date</label>
                                                <input type="date" class="form-control" name='from_date' id='from_date' value="<?php echo $from_date; ?>">
                                            </div>
                                            <div class="form-group col-md-2">
                                                <label>To Date</label>
                                                <input type="date" class="form-control" name='to_date' id='to_date' value="<?php echo  $to_date; ?>">
                                            </div>

                                            <div class="form-group col-md-2">
                                                <label>Search</label>
                                                <input type="text" class="form-control" placeholder="Ref No. / Customer Name" name='keyword' id='keyword' autocomplete="off" value="<?php echo $_REQUEST['keyword']; ?>">
                                            </div>

                                            <div class="form-group col-md-2">
                                                <label>All Branches</label>
                                                <select name="branch_id" id="branch_id" class="form-control select-search">

                                                    <?php
                                                    echo $dbconn->fnFillComboFromTable_Where("branch_id", "branch_name", "mst_branch", "branch_id", " WHERE branch_status = 1");
                                                    ?>
                                                </select>
                                                <script>
                                                    document.getElementById('branch_id').value = "<?php echo $branch; ?>";
                                                </script>
                                            </div>
                                            <div class="form-group col-md-2"> <label class="control-label">Status </label>
                                                <select name="sch_by" id="sch_by" data-placeholder="Search by.." class="select" style="width:150px">

                                                    <option value="3">All</option>
                                                    <option value="1">Pending</option>
                                                    <option value="2">Completed</option>
                                                    <option value="4">Partial</option>

                                                </select>
                                                <script>
                                                    document.thisForm.sch_by.value = "<?php echo $_REQUEST['sch_by']; ?>";
                                                </script>
                                            </div>

                                            <div class="form-group col-md-2">
                                                <button class="btn btn-info mt-4" name="Report" value="Report" type="submit">
                                                    <i class="icon-statistics mr-1"></i>Generate Report</button>
                                            </div>
                                        </div>
                                    <!-- </table> -->


                                    <?php
                                    if (isset($_REQUEST['Report'])) {

                                        if ($_REQUEST['keyword'] != '')
                                            $keyword = " | " . $_REQUEST['keyword'];
                                        else
                                            $keyword = '';


                                        if ($_REQUEST['keyword1'] != '')
                                            $keyword1 = " | " . $_REQUEST['keyword1'];
                                        else
                                            $keyword1 = '';

                                        echo ' 
                                                        <div class="invoice" id="print_content1" style="width:100%;">
                                                        <div class="table-responsive" style="width:100%" id="payment_tbl"><br>';
                                        echo '<table class="table table-xs invoice_tbl" id="lst_table">
                                                                    <thead>
                                                                        <tr style="background-color:lightgrey;">
                                                                            <th colspan="10" style="text-align: center;">
                                                                            <span style="font-size: 18px; color: #463ece;">Sales Order Details</span><br/>' . date("d-M-Y", strtotime($_REQUEST['from_date'])) . ' - ' . date("d-M-Y", strtotime($_REQUEST['to_date'])) . '' . $keyword . ' ' . $keyword1 . '
                                                                            </th>
                                                                        </tr>
                                                                        <tr style="background-color:lightgrey;">
                                                                            <th width="5px">#</th>
                                                                            <th width="5%">SO No.</th>
                                                                            <th width="5%">SO Date</th>
                                                                            <th width="40%">Customer Name</th>
                                                                            <th width="5%" style="text-align:right;">SO Sub Total</th>
                                                                            <th width="5%" style="text-align:right;">SO Value</th>
                                                                            <!--<th>Grand Value</th>-->
                                                                            <th width="5%" style="text-align:right;">Balance Value</th>
                                                                            <th width="10%">DC No.</th>
                                                                            <th width="10%">Invoice No.</th>';
                                                                            
                                                                            echo '<th width="10%" style="text-align:center;">Status</th>';
                                                                            
                                                                            
                                                                            
                                                                            
                                                                        echo'</tr>
                                                                    </thead>
                                                                <tbody>';

                                    $SQL = "SELECT *,a.supp_id as supp_id, b.supp_name,a.so_id as so_id_new
                                                                    FROM tbl_sales_order AS a 
                                                                    LEFT JOIN mst_supplier_new AS b ON b.supp_id = a.supp_id 
                                                                    WHERE a.so_approve_status = 1 AND a.so_status = 5 AND a.so_slno > 0 ";

                                        if ($_REQUEST['keyword'] != "") {
                                            $SQL .= " AND (a.so_refno LIKE '%" . $_REQUEST['keyword'] . "%' OR a.item_net_val LIKE '%" . $_REQUEST['keyword'] . "%'  OR b.supp_name LIKE '%" . $_REQUEST['keyword'] . "%' )";
                                        }

                                        if ($_REQUEST['supp_id'] != '') {
                                            $SQL .= " AND (a.supp_id ='" . $_REQUEST['supp_id'] . "') ";
                                        }
                                        if ($_REQUEST['keyword1'] != "") {
                                            $SQL .= " AND (b.supp_name LIKE '%" . $_REQUEST['keyword1'] . "%' ) ";
                                        }
                                        if (($_REQUEST['branch_id']) != '') {
        
                                            $SQL .= " AND (a.bie_branch_id =" . $_REQUEST['branch_id'].") ";
                                        }
                                        if ($_REQUEST['sch_by'] != "") {
                                        /*   if ($_REQUEST['sch_by'] == 1) {
                                                //$SQL .= " AND (c.jpo_approve_status = 0 || d.dc_approve_status = 0 || e.inv_status = 0)";
                                                $SQL .= " AND (c.inv_status = 0) ";
                                            } /*else if ($_REQUEST['sch_by'] == 2) {
                                                $SQL .= " AND (c.inv_status = 1) ";
                                            }*/
                                        }
                                        if ($_REQUEST['from_date'] != '' || $_REQUEST['from_date'] == '0000-00-00' && $_REQUEST['to_date'] != "" || $_REQUEST['to_date'] == '0000-00-00') {
                                            $SQL .= " AND a.so_date BETWEEN '" . date('Y-m-d', strtotime($_REQUEST['from_date'])) . "' AND '" . date('Y-m-d', strtotime($_REQUEST['to_date'])) . "' ";
                                        }

                                        $SQL .= " ORDER BY a.so_date DESC";

                                        //echo $SQL;
                                        $result = $conn->query($SQL);

                                        $c_jpo = $c_dc = $c_inv = $cc_dc = $tot_sub = $cdcc_dc = 0;
                                        if ($result->rowCount() > 0) {

                                            $Sno = 1;

                                            $total_quo_value = $net_value = 0;

                                            while ($obj = $result->fetch()) {
                                                $inv_stat = $inv_slno = '';

                                                // echo $_REQUEST['sch_by'] . '**';
                                                $so_qty = $dbconn->GetSingleReconrd("tbl_sales_order_details", "SUM(so_qty)", "so_id", $obj->so_id);
                                                
                                                $dc_count_ids = $dbconn->GetSingleReconrd("tbl_dc", "group_concat(dc_id)", "dc_status = 1 AND so_id", $obj->so_id);
                                                $dc_qty=0;
                                                if($dc_count_ids != ''){
                                                $dc_qty = $dbconn->GetSingleReconrd("tbl_dc_details", "SUM(dc_qty)","dc_id IN (".$dc_count_ids.") AND dc_dispatch_qty>0 AND 1", 1);
                                                }
                                                $count_dc_ids = $dbconn->GetSingleReconrd("tbl_dc", "count(dc_id)", " dc_status = 1 AND so_id", $obj->so_id);
                                                $count_inv_ids = $dbconn->GetSingleReconrd("tbl_invoice", "count(inv_id)", " inv_status = 1 AND so_id", $obj->so_id);

                                                $dc_ids = $dbconn->GetSingleReconrd("tbl_dc", "dc_id", "so_id", $obj->so_id);
                                                $inv_ids = $dbconn->GetSingleReconrd("tbl_invoice", "inv_id", "so_id", $obj->so_id);

                                                
                                                //echo $so_qty.' ~ '.$dc_qty.'**'.$count_dc_ids.'***'.$count_inv_ids;
                                                if($so_qty == $dc_qty && $count_dc_ids == $count_inv_ids){
                                                    $status = '<span class="badge bg-success"> Completed</sapn>';
                                                }
                                                else if($obj->so_id != '' && ($dc_ids == '' || $inv_ids == '')){
                                                    $status = '<span class="badge bg-warning">pending</span>';
                                                }
                                                else if($so_qty != $dc_qty || $count_dc_ids != $count_inv_ids){
                                                    
                                                    $status ='<a target="_blank" href="dc_add_view.php?so_id='.$obj->so_id.'&status=partial&view=0" class="tip" title="View DC"><span class="badge bg-grey"> Partial</span></a>';
                                                }
                                            
                                                else{
                                                    $status = '';
                                                }
                                                if ($_REQUEST['sch_by'] != "" && $_REQUEST['sch_by'] == 2) {
                                                    $inv_stat = $dbconn->GetSingleReconrd("tbl_invoice", "inv_status", "dc_id", $obj->dc_id);
                                                    $inv_slno = $dbconn->GetSingleReconrd("tbl_invoice", "inv_slno", "dc_id", $obj->dc_id);
                                                    //echo '~~' . $inv_stat;
                                                    if($so_qty == $dc_qty && $count_dc_ids == $count_inv_ids){
                                                    //if ($inv_stat == 1 && $inv_slno > 0) {
                                                        $dc_nos = "";
                                                        $dc_ids = $dbconn->GetSingleReconrd("tbl_dc", "group_concat(dc_id,'~',dc_slno,'~',dc_date)", "dc_slno > 0 AND so_id", $obj->so_id);
                                                        if ($dc_ids != "") {
                                                            $dc_idss = explode(",", $dc_ids);
                                                            foreach ($dc_idss as $value) {

                                                                $cdcc_dc++;
                                                                $ind_dc_dets = explode("~", $value);
                                                                $dc_date = date("d-m-Y", strtotime($ind_dc_dets[2]));
                                                                $dc_nos .=  '<a target="_blank" href="dc_print.php?dc_id=' . $ind_dc_dets[0] . '"><b>' . $ind_dc_dets[1] .  "</b></a> (".$dc_date."),<br>";
                                                            }
                                                        }
                                                        $invoice_ids = $dbconn->GetSingleReconrd("tbl_invoice", "group_concat(inv_id)", "dc_id", $obj->dc_id);


                                                        $item_total = $dbconn->GetSingleReconrd("tbl_sales_order_details", "SUM(item_total)", "so_id", $obj->so_id_new);
                                                        $pack_total = $dbconn->GetSingleReconrd("tbl_sales_order_pack_dts", "SUM(pack_total)", "so_id", $obj->so_id_new);

                                                        //$tot_subb = $item_total;
                                                        $tot_sub = $item_total + $pack_total;

                                                        if ($obj->supp_id != '0') {
                                                            $buss_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_id", $obj->supp_id);
                                                            $add2 = ' - ' . $dbconn->GetSingleReconrd("mst_supplier_new", "supp_add2", "supp_id", $obj->supp_id);
                                                        } else {
                                                            $buss_name = $dbconn->GetSingleReconrd("tbl_enquiry", "enq_buss_name", "enq_id", $obj->enq_id);
                                                        }
                                                        $supp_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_id", $obj->supp_id);
                                                        $add2 = ' - ' . $dbconn->GetSingleReconrd("mst_supplier_new", "supp_add2", "supp_id", $obj->supp_id);
                                                        if ($obj->branch_id > 0) {
                                                            $branch_name = $dbconn->GetSingleReconrd("mst_customer_branch", "branch_name", "branch_id", $obj->branch_id);
                                                            $add2 = ' - ' . $dbconn->GetSingleReconrd("mst_customer_branch", "branch_add2", "branch_id", $obj->branch_id);
                                                            $cus_details = $supp_name . '<b>Branch Name: </b></br>' . $branch_name . $add2;
                                                        } else {
                                                            $cus_details = '<b>' . $supp_name . '</br></b>' . $add2;
                                                        }

                                                        // if ($obj->jpo_code != '') {
                                                        //     $jpo = $obj->jpo_code . '<br>' . date("d-m-Y", strtotime($obj->jpo_date));
                                                        //     $c_jpo++;
                                                        // } else {
                                                        //     $jpo = '';
                                                        // }
                                                        $count_ids = $dbconn->GetSingleReconrd("tbl_dc", "count(dc_id)", "so_id", $obj->so_id);
                                                        if ($count_ids != '') {
                                                            $dc = '<b>' . $obj->dc_slno . '</b><br>' . date("d-m-Y", strtotime($obj->dc_date));
                                                            $c_dc++;
                                                        } else {
                                                            $dc = '';
                                                        }
                                                        $inv_ids = $dbconn->GetSingleReconrd("tbl_invoice", "count(inv_id)", "inv_slno > 0 AND dc_id", $obj->dc_id);
                                                        if ($inv_ids != '') {
                                                            $inv = '<b>' . $obj->inv_slno . '</b><br>' . date("d-m-Y", strtotime($obj->inv_date));
                                                            $c_inv++;
                                                        } else {
                                                            $inv = '';
                                                        }
                                                        if ($obj->so_id_new > 0) {

                                                            $so_link = '<a href="print_sales_order.php?so_id=' . $obj->so_id_new . '" target="_blank">' .
                                                                $dbconn->GetSingleReconrd("tbl_sales_order", "so_refno", "so_id", $obj->so_id_new) . '</a>';
                                                        } else {
                                                            $so_link = '';
                                                        }
                                                        $pack_total = $dbconn->GetSingleReconrd("tbl_sales_order_pack_dts", "SUM(pack_total)", "so_id", $obj->so_id_new);
                                                        $invoice_nos = "";

                                                        $inv_data = $dbconn->GetSingleReconrd("tbl_invoice", "group_concat(inv_id,'~',inv_slno,'~',inv_date)", "inv_slno > 0 AND so_id", $obj->so_id);

                                                        if ($inv_data != "") {
                                                            $inv_dets = explode(",", $inv_data);
                                                            foreach ($inv_dets as $value) {
                                                                $cc_dc++;
                                                                $ind_inv_dets = explode("~", $value);
                                                                $inv_date = date("d-m-Y", strtotime($ind_inv_dets[2]));
                                                                $invoice_nos .=  '<a target="_blank" href="quo_invoice_print.php?inv_id=' . $ind_inv_dets[0] . '"><b>' . $ind_inv_dets[1] . "</b></a> (".$inv_date."),<br>";
                                                            }
                                                        }

                                                        echo '<tr>
                                                                        <td>' . $Sno . '</td>
                                                                        <td>' . $so_link . '</td>
                                                                        <td>' . date("d-m-Y", strtotime($obj->so_date)) . '</td>
                                                                        <td colspan="1">' . strtoupper($cus_details) . '</td>
                                                                        
                                                                        <td align="right">' . number_format(round($item_total), 2, ".", "") . '</td>
                                                                        <td align="right">' . number_format(round($obj->item_net_val), 2, ".", "") . '</td>
                                                                        <!--<td align="right">' . number_format($pack_total, 2, ".", "") . '</td>-->
                                                                        <td align="right">' . number_format($obj->bal_value, 2, ".", "") . '</td>
                                                                        <td>' . $dc_nos . '</td>
                                                                        <td>' . $invoice_nos . '</td>
                                                                        <td align="center">'.$status.'</td>
                                                                        
                                                                        
                                                                    </tr>';
                                                        $total_quo_value += $obj->item_net_val;
                                                        $net_value += $obj->bal_value;
                                                        $Sno++;
                                                    }
                                                }
                                                
                                                else if ($_REQUEST['sch_by'] != "" && $_REQUEST['sch_by'] == 4) {
                                                    $inv_stat = $dbconn->GetSingleReconrd("tbl_invoice", "inv_status", "dc_id", $obj->dc_id);
                                                    $inv_slno = $dbconn->GetSingleReconrd("tbl_invoice", "inv_slno", "dc_id", $obj->dc_id);
                                                    //echo '~~' . $inv_stat;
                                                    if($so_qty != $dc_qty || $count_dc_ids != $count_inv_ids){
                                                    //if ($inv_stat != 1) {
                                                        $dc_nos = "";
                                                        $dc_ids = $dbconn->GetSingleReconrd("tbl_dc", "group_concat(dc_id,'~',dc_slno,'~',dc_date)", "dc_slno > 0 AND so_id", $obj->so_id);
                                                        if ($dc_ids != "") {
                                                            $dc_idss = explode(",", $dc_ids);
                                                            foreach ($dc_idss as $value) {

                                                                $cdcc_dc++;
                                                                $ind_dc_dets = explode("~", $value);
                                                                $dc_date = date("d-m-Y", strtotime($ind_dc_dets[2]));
                                                                $dc_nos .=  '<a target="_blank" href="dc_print.php?dc_id=' . $ind_dc_dets[0] . '"><b>' . $ind_dc_dets[1] .  "</b></a> (".$dc_date."),<br>";
                                                            }
                                                        }
                                                        if($dc_nos != ''){
                                                            $invoice_ids = $dbconn->GetSingleReconrd("tbl_invoice", "group_concat(inv_id)", "dc_id", $obj->dc_id);


                                                            $item_total = $dbconn->GetSingleReconrd("tbl_sales_order_details", "SUM(item_total)", "so_id", $obj->so_id_new);
                                                            $pack_total = $dbconn->GetSingleReconrd("tbl_sales_order_pack_dts", "SUM(pack_total)", "so_id", $obj->so_id_new);

                                                            //$tot_subb = $item_total;
                                                            $tot_sub = $item_total + $pack_total;

                                                            if ($obj->supp_id != '0') {
                                                                $buss_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_id", $obj->supp_id);
                                                                $add2 = ' - ' . $dbconn->GetSingleReconrd("mst_supplier_new", "supp_add2", "supp_id", $obj->supp_id);
                                                            } else {
                                                                $buss_name = $dbconn->GetSingleReconrd("tbl_enquiry", "enq_buss_name", "enq_id", $obj->enq_id);
                                                            }
                                                            $supp_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_id", $obj->supp_id);
                                                            $add2 = ' - ' . $dbconn->GetSingleReconrd("mst_supplier_new", "supp_add2", "supp_id", $obj->supp_id);
                                                            if ($obj->branch_id > 0) {
                                                                $branch_name = $dbconn->GetSingleReconrd("mst_customer_branch", "branch_name", "branch_id", $obj->branch_id);
                                                                $add2 = ' - ' . $dbconn->GetSingleReconrd("mst_customer_branch", "branch_add2", "branch_id", $obj->branch_id);
                                                                $cus_details = $supp_name . '<b>Branch Name: </b></br>' . $branch_name . $add2;
                                                            } else {
                                                                $cus_details = '<b>' . $supp_name . '</br></b>' . $add2;
                                                            }

                                                            // if ($obj->jpo_code != '') {
                                                            //     $jpo = $obj->jpo_code . '<br>' . date("d-m-Y", strtotime($obj->jpo_date));
                                                            //     $c_jpo++;
                                                            // } else {
                                                            //     $jpo = '';
                                                            // }
                                                            $count_ids = $dbconn->GetSingleReconrd("tbl_dc", "count(dc_id)", "so_id", $obj->so_id);
                                                            if ($count_ids != '') {
                                                                $dc = '<b>' . $obj->dc_slno . '</b><br>' . date("d-m-Y", strtotime($obj->dc_date));
                                                                $c_dc++;
                                                            } else {
                                                                $dc = '';
                                                            }
                                                            $inv_ids = $dbconn->GetSingleReconrd("tbl_invoice", "count(inv_id)", "inv_slno > 0 AND dc_id", $obj->sc_id);
                                                            if ($inv_ids != '') {
                                                                $inv = '<b>' . $obj->inv_slno . '</b><br>' . date("d-m-Y", strtotime($obj->inv_date));
                                                                $c_inv++;
                                                            } else {
                                                                $inv = '';
                                                            }
                                                            if ($obj->so_id_new > 0) {

                                                                $so_link = '<a href="print_sales_order.php?so_id=' . $obj->so_id_new . '" target="_blank">' .
                                                                    $dbconn->GetSingleReconrd("tbl_sales_order", "so_refno", "so_id", $obj->so_id_new) . '</a>';
                                                            } else {
                                                                $so_link = '';
                                                            }
                                                            $pack_total = $dbconn->GetSingleReconrd("tbl_sales_order_pack_dts", "SUM(pack_total)", "so_id", $obj->so_id_new);
                                                            $invoice_nos = "";

                                                            $inv_data = $dbconn->GetSingleReconrd("tbl_invoice", "group_concat(inv_id,'~',inv_slno,'~',inv_date)", "inv_slno > 0 AND inv_status = '1' AND so_id", $obj->so_id);

                                                            if ($inv_data != "") {
                                                                $inv_dets = explode(",", $inv_data);
                                                                foreach ($inv_dets as $value) {
                                                                    $cc_dc++;
                                                                    $ind_inv_dets = explode("~", $value);
                                                                    $inv_date = date("d-m-Y", strtotime($ind_inv_dets[2]));
                                                                    $invoice_nos .=  '<a target="_blank" href="quo_invoice_print.php?inv_id=' . $ind_inv_dets[0] . '"><b>' . $ind_inv_dets[1] . "</b></a> (".$inv_date."),<br>";
                                                                }
                                                            }

                                                            echo '<tr>
                                                                            <td>' . $Sno . '</td>
                                                                            <td>' . $so_link . '</td>
                                                                            <td>' . date("d-m-Y", strtotime($obj->so_date)) . '</td>
                                                                            <td colspan="1">' . strtoupper($cus_details) . '</td>
                                                                            
                                                                            <td align="right">' . number_format(round($item_total), 2, ".", "") . '</td>
                                                                            <td align="right">' . number_format(round($obj->item_net_val), 2, ".", "") . '</td>
                                                                            <!--<td align="right">' . number_format($pack_total, 2, ".", "") . '</td>-->
                                                                            <td align="right">' . number_format($obj->bal_value, 2, ".", "") . '</td>
                                                                            <td>' . $dc_nos . '</td>
                                                                            <td>' . $invoice_nos . '</td>
                                                                            <td align="center">'.$status.'</td>
                                                                            
                                                                            
                                                                        </tr>';
                                                            $total_quo_value += $obj->item_net_val;
                                                            $net_value += $obj->bal_value;
                                                            $Sno++;
                                                        }
                                                    }
                                                }
                                                else if ($_REQUEST['sch_by'] != "" && $_REQUEST['sch_by'] == 1) {
                                                    $status = '<span class="badge bg-warning"> Pending</span>';
                                                    $inv_stat = $dbconn->GetSingleReconrd("tbl_invoice", "inv_status", "dc_id", $obj->dc_id);
                                                    $inv_slno = $dbconn->GetSingleReconrd("tbl_invoice", "inv_slno", "dc_id", $obj->dc_id);

                                                    //echo '~~' . $inv_stat;
                                                    if($so_qty != $dc_qty || $count_dc_ids != $count_inv_ids){
                                                        
                                                    //if ($inv_stat != 1) {
                                                        $dc_nos = "";
                                                        $invoice_nos = "";
                                                        $dc_ids = $dbconn->GetSingleReconrd("tbl_dc", "group_concat(dc_id,'~',dc_slno,'~',dc_date)", "dc_slno > 0 AND dc_approve_status = '1' AND so_id", $obj->so_id);
                                                        if ($dc_ids != "") {
                                                            $dc_idss = explode(",", $dc_ids);
                                                            foreach ($dc_idss as $value) {

                                                                $cdcc_dc++;
                                                                $ind_dc_dets = explode("~", $value);
                                                                $dc_date = date("d-m-Y", strtotime($ind_dc_dets[2]));
                                                                $dc_nos .=  '<a target="_blank" href="dc_print.php?dc_id=' . $ind_dc_dets[0] . '"><b>' . $ind_dc_dets[1] .  "</b></a> (".$dc_date."),<br>";
                                                            }
                                                        }
                                                        if($dc_nos == '' || $inv_dets == ''){
                                                            $invoice_ids = $dbconn->GetSingleReconrd("tbl_invoice", "group_concat(inv_id)", "dc_id", $obj->dc_id);


                                                            $item_total = $dbconn->GetSingleReconrd("tbl_sales_order_details", "SUM(item_total)", "so_id", $obj->so_id_new);
                                                            $pack_total = $dbconn->GetSingleReconrd("tbl_sales_order_pack_dts", "SUM(pack_total)", "so_id", $obj->so_id_new);

                                                            //$tot_subb = $item_total;
                                                            $tot_sub = $item_total + $pack_total;

                                                            if ($obj->supp_id != '0') {
                                                                $buss_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_id", $obj->supp_id);
                                                                $add2 = ' - ' . $dbconn->GetSingleReconrd("mst_supplier_new", "supp_add2", "supp_id", $obj->supp_id);
                                                            } else {
                                                                $buss_name = $dbconn->GetSingleReconrd("tbl_enquiry", "enq_buss_name", "enq_id", $obj->enq_id);
                                                            }
                                                            $supp_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_id", $obj->supp_id);
                                                            $add2 = ' - ' . $dbconn->GetSingleReconrd("mst_supplier_new", "supp_add2", "supp_id", $obj->supp_id);
                                                            if ($obj->branch_id > 0) {
                                                                $branch_name = $dbconn->GetSingleReconrd("mst_customer_branch", "branch_name", "branch_id", $obj->branch_id);
                                                                $add2 = ' - ' . $dbconn->GetSingleReconrd("mst_customer_branch", "branch_add2", "branch_id", $obj->branch_id);
                                                                $cus_details = $supp_name . '<b>Branch Name: </b></br>' . $branch_name . $add2;
                                                            } else {
                                                                $cus_details = '<b>' . $supp_name . '</br></b>' . $add2;
                                                            }

                                                            // if ($obj->jpo_code != '') {
                                                            //     $jpo = $obj->jpo_code . '<br>' . date("d-m-Y", strtotime($obj->jpo_date));
                                                            //     $c_jpo++;
                                                            // } else {
                                                            //     $jpo = '';
                                                            // }
                                                            $count_ids = $dbconn->GetSingleReconrd("tbl_dc", "count(dc_id)", "so_id", $obj->so_id);
                                                            if ($count_ids != '') {
                                                                $dc = '<b>' . $obj->dc_slno . '</b><br>' . date("d-m-Y", strtotime($obj->dc_date));
                                                                $c_dc++;
                                                            } else {
                                                                $dc = '';
                                                            }
                                                            $inv_ids = $dbconn->GetSingleReconrd("tbl_invoice", "count(inv_id)", "inv_slno > 0 AND dc_id", $obj->dc_id);
                                                            if ($inv_ids != '') {
                                                                $inv = '<b>' . $obj->inv_slno . '</b><br>' . date("d-m-Y", strtotime($obj->inv_date));
                                                                $c_inv++;
                                                            } else {
                                                                $inv = '';
                                                            }
                                                            if ($obj->so_id_new > 0) {

                                                                $so_link = '<a href="print_sales_order.php?so_id=' . $obj->so_id_new . '" target="_blank">' .
                                                                    $dbconn->GetSingleReconrd("tbl_sales_order", "so_refno", "so_id", $obj->so_id_new) . '</a>';
                                                            } else {
                                                                $so_link = '';
                                                            }
                                                            $pack_total = $dbconn->GetSingleReconrd("tbl_sales_order_pack_dts", "SUM(pack_total)", "so_id", $obj->so_id_new);
                                                            $invoice_nos = "";

                                                            $inv_data = $dbconn->GetSingleReconrd("tbl_invoice", "group_concat(inv_id,'~',inv_slno,'~',inv_date)", "inv_slno > 0 AND so_id", $obj->so_id);

                                                            if ($inv_data != "") {
                                                                $inv_dets = explode(",", $inv_data);
                                                                foreach ($inv_dets as $value) {
                                                                    $cc_dc++;
                                                                    $ind_inv_dets = explode("~", $value);
                                                                    $inv_date = date("d-m-Y", strtotime($ind_inv_dets[2]));
                                                                    $invoice_nos .=  '<a target="_blank" href="quo_invoice_print.php?inv_id=' . $ind_inv_dets[0] . '"><b>' . $ind_inv_dets[1] . "</b></a> (".$inv_date."),<br>";
                                                                }
                                                            }

                                                            echo '<tr>
                                                                            <td>' . $Sno . '</td>
                                                                            <td>' . $so_link . '</td>
                                                                            <td>' . date("d-m-Y", strtotime($obj->so_date)) . '</td>
                                                                            <td colspan="1">' . strtoupper($cus_details) . '</td>
                                                                            
                                                                            <td align="right">' . number_format(round($item_total), 2, ".", "") . '</td>
                                                                            <td align="right">' . number_format(round($obj->item_net_val), 2, ".", "") . '</td>
                                                                            <!--<td align="right">' . number_format($pack_total, 2, ".", "") . '</td>-->
                                                                            <td align="right">' . number_format($obj->bal_value, 2, ".", "") . '</td>
                                                                            <td>' . $dc_nos . '</td>
                                                                            <td>' . $invoice_nos . '</td>
                                                                            <td align="center">'.$status.'</td>
                                                                            
                                                                            
                                                                        </tr>';
                                                            $total_quo_value += $obj->item_net_val;
                                                            $net_value += $obj->bal_value;
                                                            $Sno++;
                                                        }
                                                    }
                                                }
                                                else {
                                                    $dc_nos = "";
                                                    $dc_ids = $dbconn->GetSingleReconrd("tbl_dc", "group_concat(dc_id,'~',dc_slno,'~',dc_date)", " dc_slno > 0 AND so_id", $obj->so_id);
                                                    if ($dc_ids != "") {
                                                        $dc_idss = explode(",", $dc_ids);
                                                        foreach ($dc_idss as $value) {

                                                            $cdcc_dc++;
                                                            $ind_dc_dets = explode("~", $value);
                                                            $dc_date = date("d-m-Y", strtotime($ind_dc_dets[2]));
                                                            $dc_nos .=  '<a target="_blank" href="dc_print.php?dc_id=' . $ind_dc_dets[0] . '"><b>' . $ind_dc_dets[1] .  "</b></a> (".$dc_date."),<br>";
                                                        }
                                                    }
                                                    $invoice_ids = $dbconn->GetSingleReconrd("tbl_invoice", "group_concat(inv_id)", "inv_slno > 0 AND dc_id", $obj->dc_id);


                                                    $item_total = $dbconn->GetSingleReconrd("tbl_sales_order_details", "SUM(item_total)", "so_id", $obj->so_id_new);
                                                    $pack_total = $dbconn->GetSingleReconrd("tbl_sales_order_pack_dts", "SUM(pack_total)", "so_id", $obj->so_id_new);

                                                    //$tot_subb = $item_total;
                                                    $tot_sub = $item_total + $pack_total;

                                                    if ($obj->supp_id != '0') {
                                                        $buss_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_id", $obj->supp_id);
                                                        $add2 = ' - ' . $dbconn->GetSingleReconrd("mst_supplier_new", "supp_add2", "supp_id", $obj->supp_id);
                                                    } else {
                                                        $buss_name = $dbconn->GetSingleReconrd("tbl_enquiry", "enq_buss_name", "enq_id", $obj->enq_id);
                                                    }
                                                    $supp_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_id", $obj->supp_id);
                                                    $add2 = ' - ' . $dbconn->GetSingleReconrd("mst_supplier_new", "supp_add2", "supp_id", $obj->supp_id);
                                                    if ($obj->branch_id > 0) {
                                                        $branch_name = $dbconn->GetSingleReconrd("mst_customer_branch", "branch_name", "branch_id", $obj->branch_id);
                                                        $add2 = ' - ' . $dbconn->GetSingleReconrd("mst_customer_branch", "branch_add2", "branch_id", $obj->branch_id);
                                                        $cus_details = $supp_name . '<b>Branch Name: </b></br>' . $branch_name . $add2;
                                                    } else {
                                                        $cus_details = '<b>' . $supp_name . '</br></b>' . $add2;
                                                    }

                                                    // if ($obj->jpo_code != '') {
                                                    //     $jpo = $obj->jpo_code . '<br>' . date("d-m-Y", strtotime($obj->jpo_date));
                                                    //     $c_jpo++;
                                                    // } else {
                                                    //     $jpo = '';
                                                    // }
                                                    $count_ids = $dbconn->GetSingleReconrd("tbl_dc", "count(dc_id)", "so_id", $obj->so_id);
                                                    if ($count_ids != '') {
                                                        $dc = '<b>' . $obj->dc_slno . '</b><br>' . date("d-m-Y", strtotime($obj->dc_date));
                                                        $c_dc++;
                                                    } else {
                                                        $dc = '';
                                                    }
                                                    $inv_ids = $dbconn->GetSingleReconrd("tbl_invoice", "count(inv_id)", "inv_slno > 0 AND dc_id", $obj->dc_id);
                                                    if ($inv_ids != '') {
                                                        $inv = '<b>' . $obj->inv_slno . '</b><br>' . date("d-m-Y", strtotime($obj->inv_date));
                                                        $c_inv++;
                                                    } else {
                                                        $inv = '';
                                                    }
                                                    if ($obj->so_id_new > 0) {

                                                        $so_link = '<a href="print_sales_order.php?so_id=' . $obj->so_id_new . '" target="_blank">' .
                                                            $dbconn->GetSingleReconrd("tbl_sales_order", "so_refno", "so_id", $obj->so_id_new) . '</a>';
                                                    } else {
                                                        $so_link = '';
                                                    }
                                                    $pack_total = $dbconn->GetSingleReconrd("tbl_sales_order_pack_dts", "SUM(pack_total)", "so_id", $obj->so_id_new);
                                                    $invoice_nos = "";

                                                    $inv_data = $dbconn->GetSingleReconrd("tbl_invoice", "group_concat(inv_id,'~',inv_slno,'~',inv_date)", "inv_slno > 0 AND so_id", $obj->so_id);

                                                    if ($inv_data != "") {
                                                        $inv_dets = explode(",", $inv_data);
                                                        foreach ($inv_dets as $value) {
                                                            $ind_inv_dets = explode("~", $value);
                                                            $cc_dc++;
                                                            $inv_date = date("d-m-Y", strtotime($ind_inv_dets[2]));
                                                            $invoice_nos .=  '<a target="_blank" href="quo_invoice_print.php?inv_id=' . $ind_inv_dets[0] . '"><b>' . $ind_inv_dets[1] . "</b></a> (".$inv_date."),<br>";
                                                        }
                                                    }

                                                    echo '<tr>
                                                                        <td>' . $Sno . '</td>
                                                                        <td>' . $so_link . '</td>
                                                                        <td>' . date("d-m-Y", strtotime($obj->so_date)) . '</td>
                                                                        <td colspan="1">' . strtoupper($cus_details) . '</td>
                                                                        
                                                                        <td align="right">' . number_format(round($item_total), 2, ".", "") . '</td>
                                                                        <td align="right">' . number_format(round($obj->item_net_val), 2, ".", "") . '</td>
                                                                        <!--<td align="right">' . number_format($pack_total, 2, ".", "") . '</td>-->
                                                                        <td align="right">' . number_format($obj->bal_value, 2, ".", "") . '</td>
                                                                        <td>' . $dc_nos . '</td>
                                                                        <td>' . $invoice_nos . '</td>
                                                                        <td align="center">'.$status.'</td>

                                                                        
                                                                    </tr>';
                                                    $total_quo_value += $obj->item_net_val;
                                                    $net_value += $obj->bal_value;
                                                    $Sno++;
                                                }
                                            }
                                            if($_SESSION['_user_type'] == 'S'){
                                                echo '<tr style="color: #463ece; font-size: 18px;">
                                                                            
                                                                            <td colspan="3" align="right"><b>Total</B></td>
                                                                            <td align="right"><b>' . number_format(round($total_quo_value), 2, ".", "") . '<b></td>
                                                                            <td align="right"><b>' . number_format($net_value, 2, ".", "") . '<b></td>
                                                                            <td align="right"></td>
                                                                            <td align="right"></td>
                                                                            <td align="right"></td>
                                                                            <td align="right"></td>
                                                                            <td align="right"></td>
                                                                            
                                                                            
                                                                        </tr>';
                                            }
                                            echo '<tr style="font-size: 18px;">
                                                <td align="left" colspan="11">SUMMARY</td>
                                            </tr>';

                                            echo '<tr style="font-size: 18px;">
                                                    <td align="left" colspan="4"><b>Sales Order Total :</b></td>
                                                    <td align="left" colspan="8"> ' . ($Sno - 1) . '</td>
                                            </tr>';
                                        
                                            echo '<tr style="font-size: 18px;">
                                                <td align="left" colspan="4"><b>DC Total :</b></td>
                                                <td align="left" colspan="8"> ' . ($cdcc_dc) . '</td>
                                        </tr>';
                                            echo '<tr style="font-size: 18px;">
                                                <td align="left" colspan="4"><b>Invoice Total :</b></td>
                                                <td align="left" colspan="8"> ' . ($cc_dc) . '</td>
                                        </tr>';

                                            $obj = null;
                                        } else {
                                            echo '<tr style="color: #463ece; font-size: 18px;">
                                                                        
                                                                <td colspan="10" align="center">No Records Found</td>
                                                            </tr>';
                                        }


                                        echo '</tbody>
                                                        </table> </div></div>';
                                    }




                                    ?>
                                </div>
                            </form>

                        </div>

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
                                          