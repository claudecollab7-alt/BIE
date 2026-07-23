<?PHP
ob_start();
session_start();

require_once("inc/common/userclass.php");

include_once('package_list_print.php');

isAdmin();
$conn = new dbconnect();
$dbconn = new dbhandler();

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);


if ($_REQUEST['dc_id'] != "") {


    $result = $conn->query("SELECT * FROM tbl_dc WHERE dc_id = " . $_REQUEST['dc_id']);
    if ($result->rowCount() > 0) {
        $obj = $result->fetch(PDO::FETCH_OBJ);

        $supp_code = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_code", "supp_status = 1 AND supp_id ", $obj->supp_id);
        $supp_name = $dbconn->GetSingleReconrd("mst_supplier_new", "supp_name", "supp_status = 1 AND supp_id ", $obj->supp_id);

        if ($obj->dc_date != "0000-00-00" && $obj->dc_date != "") {
            $dc_date = date("d-m-Y", strtotime($obj->dc_date));
        }
    }


    $get_add = $conn->query("SELECT * FROM mst_supplier_new WHERE supp_id = " . $obj->supp_id);
    if ($get_add->rowCount() > 0) {
        $obj1 = $get_add->fetch(PDO::FETCH_OBJ);


        $add = "";
        $add .= $obj1->supp_add1;
        if ($obj1->supp_add2 != "") {
            $add .= ', ' . $obj1->supp_add2;
        }
        $add .= ', <br/>' . $dbconn->GetSingleReconrd("mst_city", "city_name", "city_status = 1 AND city_id ", $obj1->city_id);

        $add .= ', ' . $dbconn->GetSingleReconrd("mst_district", "district_name", "district_status = 1 AND district_id ", $obj1->district_id);

        $add .= ', <br/>' . $dbconn->GetSingleReconrd("mst_state", "state_name", "state_status = 1 AND state_id ", $obj1->state_id);

        $add .= ' - ' . $obj1->supp_pincode . '.';
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Package List</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <?php include_once("inc/common/css-js.php"); ?>

<script type="text/javascript" src="print_me.js"></script>
</head>

<body>
    <?php include("inc/common/header.php") ?>
    <div class="page-content">
        <?php include("inc/common/sidebar.php") ?>
        <div class="content-wrapper">
            <div class="page-header">
                <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                    <div class="d-flex">
                        <div class="breadcrumb">
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
                            <a href="#" class="breadcrumb-item"> Work Area</a>
                            <span class="breadcrumb-item active">Package List</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>
            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-pgheader text-white header-elements-inline">
                                <h6 class="card-title">Package List - <?php echo $obj->dc_refno; ?></h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                        <a class="list-icons-item" href="javascript:PrintPartsNew(new Array('print_content1'),'<?php echo $obj->dc_slno; ?>');" id="print_page" title="Print Package List"><i class="icon-printer2 mr-1"></i></a>
                                        <a class="list-icons-item" href="dc_list.php" title="Dc List"><i class="icon-arrow-left52 mr-2"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="invoice" id="print_content1" style="width:100%;">


                                        <?php

                                        $sql = "select * from tbl_package_box_details where dc_id='" . $_REQUEST['dc_id'] . "'";
                                        $result = $conn->query($sql);
                                        if ($result->rowCount() > 0) {

                                            $pack_box_no_c = array();
                                            $pack_item_qty_c = array();
                                            $item_c = array();

                                            $pack_box_no_w = array();
                                            $pack_item_qty_w = array();
                                            $item_w = array();

                                            $pack_box_no_g = array();
                                            $pack_item_qty_g = array();
                                            $item_g = array();

                                            $pack_box_no_p = array();
                                            $pack_item_qty_p = array();
                                            $item_p = array();

                                            while ($dc = $result->fetch()) {

                                                $box_no_temp = explode(', ', $dc->pack_box_no);
                                                $box_qty_temp = explode(', ', $dc->pack_item_qty);
                                                $item_id = $dc->item_id;

                                                for ($i = 0; $i < sizeof($box_no_temp); $i++) {
                                                    if ($dc->box_id == 1) {
                                                        array_push($pack_box_no_c, $box_no_temp[$i]);
                                                        array_push($pack_item_qty_c, $box_qty_temp[$i]);
                                                        array_push($item_c, $item_id);
                                                    } elseif ($dc->box_id == 2) {
                                                        array_push($pack_box_no_w, $box_no_temp[$i]);
                                                        array_push($pack_item_qty_w, $box_qty_temp[$i]);
                                                        array_push($item_w, $item_id);
                                                    } elseif ($dc->box_id == 3) {
                                                        array_push($pack_box_no_g, $box_no_temp[$i]);
                                                        array_push($pack_item_qty_g, $box_qty_temp[$i]);
                                                        array_push($item_g, $item_id);
                                                    } elseif ($dc->box_id == 4) {
                                                        array_push($pack_box_no_p, $box_no_temp[$i]);
                                                        array_push($pack_item_qty_p, $box_qty_temp[$i]);
                                                        array_push($item_p, $item_id);
                                                    }
                                                }
                                            }
                                        }



                                        $uni_box_no_c = array();
                                        $uni_box_no_w = array();
                                        $uni_box_no_g = array();
                                        $uni_box_no_p = array();

                                        for ($c = 0; $c < sizeof($pack_box_no_c); $c++) {

                                            // echo $pack_box_no_c[$c];


                                            if (!in_array($pack_box_no_c[$c], $uni_box_no_c))
                                                array_push($uni_box_no_c, $pack_box_no_c[$c]);
                                        }

                                        for ($c = 0; $c < sizeof($uni_box_no_c); $c++) {
                                            $box_no = $uni_box_no_c[$c];
                                            tbl_header($_REQUEST['dc_id'], 1, $box_no);

                                            // echo $box_no .'<br>';
                                            $sno = 1;
                                            for ($i = 0; $i < sizeof($pack_box_no_c); $i++) {

                                                if ($pack_box_no_c[$i] == $box_no) {
                                                    tbl_body($item_c[$i], $pack_item_qty_c[$i], $sno);

                                                    // echo  $pack_item_qty_c[$i] .'-'. $item_c[$i] .'<br>';
                                                    $sno++;
                                                }
                                            }
                                            tbl_foot();
                                        }

                                        for ($w = 0; $w < sizeof($pack_box_no_w); $w++) {
                                            // echo $pack_box_no_w[$w];
                                            if (!in_array($pack_box_no_w[$w], $uni_box_no_w))
                                                array_push($uni_box_no_w, $pack_box_no_w[$w]);
                                        }

                                        for ($w = 0; $w < sizeof($uni_box_no_w); $w++) {
                                            $box_no = $uni_box_no_w[$w];
                                            // echo $box_no .'<br>';
                                            tbl_header($_REQUEST['dc_id'], 2, $box_no);
                                            $sno = 1;
                                            for ($i = 0; $i < sizeof($pack_box_no_w); $i++) {

                                                if ($pack_box_no_w[$i] == $box_no) {
                                                    tbl_body($item_w[$i], $pack_item_qty_w[$i], $sno);
                                                    // echo  $pack_item_qty_w[$i] .'-'. $item_w[$i] .'<br>';
                                                    $sno++;
                                                }
                                            }
                                            tbl_foot();
                                        }

                                        for ($g = 0; $g < sizeof($pack_box_no_g); $g++) {
                                            // echo $pack_box_no_g[$g];
                                            if (!in_array($pack_box_no_g[$g], $uni_box_no_g))
                                                array_push($uni_box_no_g, $pack_box_no_g[$g]);
                                        }

                                        for ($g = 0; $g < sizeof($uni_box_no_g); $g++) {
                                            $box_no = $uni_box_no_g[$g];
                                            // echo $box_no .'<br>';
                                            tbl_header($_REQUEST['dc_id'], 3, $box_no);
                                            $sno = 1;
                                            for ($i = 0; $i < sizeof($pack_box_no_g); $i++) {

                                                if ($pack_box_no_g[$i] == $box_no) {
                                                    tbl_body($item_g[$i], $pack_item_qty_g[$i], $sno);
                                                    // echo  $pack_item_qty_g[$i] .'-'. $item_w[$i] .'<br>';
                                                    $sno++;
                                                }
                                            }
                                            tbl_foot();
                                        }

                                        for ($p = 0; $p < sizeof($pack_box_no_p); $p++) {
                                            // echo $pack_box_no_g[$g];
                                            if (!in_array($pack_box_no_p[$p], $uni_box_no_p))
                                                array_push($uni_box_no_p, $pack_box_no_p[$p]);
                                        }

                                        for ($p = 0; $p < sizeof($uni_box_no_p); $p++) {
                                            $box_no = $uni_box_no_p[$p];
                                            // echo $box_no .'<br>';
                                            tbl_header($_REQUEST['dc_id'], 4, $box_no);
                                            $sno = 1;
                                            for ($i = 0; $i < sizeof($pack_box_no_p); $i++) {

                                                if ($pack_box_no_p[$i] == $box_no) {
                                                    tbl_body($item_p[$i], $pack_item_qty_p[$i], $sno);
                                                    //echo  $pack_item_qty_p[$i] .'-'. $item_w[$i] .'<br>';
                                                    $sno++;
                                                }
                                            }
                                            tbl_foot();
                                        }

                                        ?>
                                    </div>
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