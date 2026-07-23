<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");
require_once("inc/common/css-js.php");

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

if (isset($_POST['GET_ATTN'])) {
    header("location:attendance_report.php?month_id=" . $_REQUEST['month_id'] . "&atten_year=" . $_REQUEST['atten_year'] . "&emp_id=" . $_REQUEST['emp_id']);
    die();
}



if ($_REQUEST['atten_year'] == '') {
    $atten_year = date('Y');
} else {
    $atten_year = $_REQUEST['atten_year'];
}
if ($_REQUEST['emp_id'] != '') {
    $emp_id1 = $_REQUEST['emp_id'];
}

if ($_REQUEST['month_id'] == '') {
    $month_id = date('m');
} else {
    $month_id = $_REQUEST['month_id'];
}

$tot_days = "SELECT * FROM tbl_month_master WHERE month_master_status = 1 AND month = $month_id AND year = $atten_year";
// print_r($tot_days);
    // $total_days = $dbconn->GetSingleReconrd("tbl_month_master", "total_days", "year = " . $atten_year . " AND month", $_REQUEST['month_id']);

    
    
   

    $result = $conn->query($tot_days);
    $obj = $result->fetch();

   

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>
        <?php echo PAGE_TITLE; ?>-Attendance for Month
    </title>


    <?php include_once("inc/common/css-js.php"); ?>
    <script type="text/javascript" src="print_me.js"></script>
    <script src="js/jquery.table2excel.min.js"></script>
    <script src="js/html2pdf.bundle.min.js"></script>


    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <?php include_once("inc/common/css-js.php"); ?>


    <style>
    	#childtable{
    	border-width: 1px 1px;
	    border-spacing: 0;
	    border-collapse: collapse;
	    border-style: solid;
	    border-color: #999999;
	    font-size: 10.5px;
		}
    </style>
</head>

<body>
    <?php include("inc/common/header.php") ?>
    <!-- Page content -->
    <div class="page-content">
        <!-- Main sidebar -->
        <?php include("inc/common/sidebar.php") ?>
        <!-- Main content -->
        <div class="content-wrapper">
            <!-- Page header -->
            <div class="page-header">

                <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
                    <div class="d-flex">
                        <div class="breadcrumb">
                            <a href="home.php" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
                            <a href="#" class="breadcrumb-item">Attendance and Salary</a>
                            <span class="breadcrumb-item active">Attendance Report</span>
                        </div>
                        <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
                    </div>
                </div>
            </div>

            <div class="content pt-0">
                <div class="row">
                    <div class="col-md-12">

                        <form name="thisform" class="form-horizontal" method='POST' action="attendance_report.php" onSubmit="return fnValidate();" enctype="multipart/form-data">

                        <?php
                        	$dateObj   = DateTime::createFromFormat('m', $month_id);
							$monthName = $dateObj->format('F');
                        ?>
                            <fieldset>
                                <div class="card">
                                    <div class="card-header bg-pgheader text-white header-elements-inline">
                                        <h6 class="card-title">Attendance Report</h6>
                                        <div class="header-elements">
                                            <div class="list-icons">
                                                <a class="list-icons-item" href="import_attendance.php" title="Import Attendance Log"><i class="icon-arrow-left52 mr-2"></i></a>
                                                <a class="list-icons-item" data-action="fullscreen"></a>
                                            </div>

                                        </div>
                                        <!-- <button type="button" class="btn btn-primary">Primary</button>
                                        <button type="button" class="btn btn-primary">Primary</button> -->
                                    </div>
                                    <div class="card-body pt-2" id="">

                                        <div class="form-group row">
                                            <div class="col-lg-12 row">
                                                <div class="col-lg-8 p-2">
                                                    <div class="row">
                                                        <div class="col-lg-3">
                                                            <select name="atten_year" id="atten_year" class="select">
                                                                <?php for ($y = date('Y') - 1; $y < (date('Y') + 1); $y++) {
                                                                    echo '<option value="' . $y . '">' . $y . '</option>';
                                                                } ?>
                                                            </select>
                                                            <script>
                                                                document.thisform.atten_year.value = "<?php echo $atten_year ?>";
                                                            </script>
                                                        </div>

                                                        <div class="col-lg-3">
                                                            <select name="month_id" id="month_id" class="select">
                                                                <option value="">--Select Month--</option>
                                                                <?php
                                                                $dbconn = new dbhandler();
                                                                echo $dbconn->fnFillComboFromTable_Where("month_id", "month_name", "mst_atten_month", "month_id", "WHERE month_status = 1") ?>
                                                            </select>

                                                        </div>
                                                        <script>
                                                            document.thisform.month_id.value = "<?php echo $_REQUEST['month_id']; ?>";
                                                        </script>

                                                        <div class="col-lg-4">
                                                            <select name="emp_id" id="emp_id" class="select-search">
                                                                <option value="">--Select Employee--</option>
                                                                <?php
                                                                $dbconn = new dbhandler();
                                                                echo $dbconn->fnFillComboFromTable_Where("emp_id", "emp_name", "mst_employee", "bio_id", " WHERE bio_id > 0 ") ?>
                                                            </select>
                                                            <script>
                                                                document.thisform.emp_id.value = "<?php echo $_REQUEST['emp_id']; ?>";
                                                            </script>
                                                        </div>


                                                        <div class="col-lg-2">
                                                            <INPUT class="btn btn-success" type="submit" name="GET_ATTN" id="GET_ATTN" value="GET">
                                                        </div>
                                                    </div>


                                                </div>
                                                <div class="col-lg-4 p-2">
                                                    <div class="row">
                                                        <div class="col-lg-6" style="text-align:right;">
                                                            <a href="import_attendance.php"><INPUT class="btn btn-info" type="button" name="" id="" value="Attendance Import Log"></a>
                                                        </div>
                                                        <div class="col-lg-6" style="text-align:right;">
                                                            <a href="attendance_new.php"><INPUT class="btn btn-info" type="button" name="" id="" value="Attendance Log Report"></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                       
                                        <?php 
                                        
                                        if ($result->rowCount() > 0) { 

                                           

                                            if (isset($_REQUEST['emp_id']) && $_REQUEST['emp_id'] == '') { 

                                                // echo "*****".isset($_REQUEST['emp_id']);

                                                echo '<hr><div class="col-md-12 text-right" >
                                          
                                                <a href="javascript:" class="rpt_export">
                                                    <button type="button" class="buttons-html5 btn btn-light" ><i class="icon-file-excel mr-1"></i> Excel</button>
                                                </a>
                                                <a href="javascript:" class="rpt_pdf">
                                                    <button type="button" class="buttons-html5 btn btn-light" >
                                                    <i class="icon-file-pdf mr-1"></i> PDF</button>
                                                </a>
                                                <a href="javascript:PrintPartsNew(new Array(\'stock_division\'),\'Day   Book\');" class="rpt_print">
                                                    <button type="button" class="buttons-html5 btn btn-light" ><i class="icon-printer mr-1"></i> Print</button>
                                                </a>
                                            </div>';

                                                $startdate = $atten_year."-".$_REQUEST['month_id']."-01";
                                                $enddate = $atten_year."-".$_REQUEST['month_id']."-".$obj->total_days."";
                                                $begin = new DateTime($startdate);
                                                $end = new DateTime($enddate);
                                                $end = $end->modify( '+1 day' );
                                                $interval = new DateInterval('P1D');
                                                $period = new DatePeriod($begin, $interval ,$end);

                                                
                                                echo '<H6><center><b>Attendance Report for '.$monthName . ' - ' . $atten_year.'</b></center></H6>
                                                <div class="table-responsive" style="width:100%;" >
                                                    <div id="stock_division">';
                                                
                                                        echo'<table class="table table-xs invoice_tbl" id="stock_db_table">
                                                        <thead>
                                                            <tr class ="rpt_heading">
                                                                <th >#</th>
                                                                <th>Emp. Code</th>
                                                                <th>Emp. Name</th>';
                                                                if ($result->rowCount() > 0) {

                                                                    foreach ($period as $dt) {
                                                                        $day = date('D', strtotime($dt->format("Y-m-d")));

                                                                        $style = "";

                                                                        if ($day == 'Sun') {
                                                                            $style = "attn_leave";
                                                                        }

                                                                        echo "<th class='".$style."' >".$dt->format("d")."</th>";
                                                                    }
                                                                        echo '<th>Present/Work days</th>';
                                                                    
                                                                }
                                                            
                                                            echo'</tr>
                                                        </thead>
                                                        <tbody>';
                                                                                        
                                                            $SQL = "SELECT * FROM mst_employee WHERE bio_id > 0  ORDER BY bio_id ASC";
                                                            // print_r($SQL);
                                                            $result = $conn->query($SQL);
                                                            
                                                            if ($result->rowCount() > 0)
                                                            {				
                                                                $Sno = 1;
                                                                $emp_id = 0;
                                                                $present = 0;

                                                                while ($row = $result->fetch())
                                                                {											
                                                                    $emp_det = $dbconn->GetSingleReconrd("mst_employee","CONCAT(emp_code,'~',emp_name)","emp_id",$row->emp_id);
                                                                    $emp = explode('~',$emp_det);
                                                                    
                                                                    
                                                                    echo '<tr>
                                                                            <td>'.$Sno.'</td>
                                                                            <td>'.$emp[0].'</td>
                                                                            <td>'.$emp[1].'</td>';

                                                                            if($emp_id!=$row->emp_id){
                                                                                $present = 0;
                                                                            }

                                                                    foreach ($period as $dt) {

                                                                        $day = date('D', strtotime($dt->format("Y-m-d")));

                                                                        $style = "";
                                                                        
                                                                        if ($day == 'Sun') {
                                                                            $style = "attn_leave";
                                                                        }

                                                                        $attn_qry = "SELECT * from tbl_attendance WHERE emp_id = " . $row->emp_id . " AND work_date = '" . $dt->format("Y-m-d") . "' ";

                                                                        $attn_result = $conn->query($attn_qry);

                                                                        if (strtotime($dt->format("Y-m-d")) <= strtotime('now')) {

                                                                            if ($attn_result->rowCount() > 0) {
                                                                                $present++;
                                                                                echo "<td class='" . $style . "' ><i class='fa fa-check' style='font-size:10px;' aria-hidden='true' ></i></td>";
                                                                            } else {
                                                                                echo "<td class='" . $style . "'><i class='fa fa-times ' style='font-size:10px;' aria-hidden='true'></i></td>";
                                                                            }
                                                                        } else {

                                                                            echo "<td class='" . $style . "'></td>";
                                                                        }
                                                                    }

                                                                    echo "<td>" . $present . "/" . $obj->working_days . "</td>";
                                                                    echo    "</tr>";
                                                                    $Sno++;
                                                                }

                                                                $row = null;
                                                            }								
                                                        
                                                            
                                                        
                                                        echo'</tbody>
                                                    </table>
                                                </div>
                                            </div>';
                                            }elseif(isset($_REQUEST['emp_id']) && $_REQUEST['emp_id'] != '' && $emp_id1 > 0 ){
                                                $emp_id = $emp_id1; 

                                                $SQL = "SELECT COUNT(attn_id) as total_working_days, SUM(work_time) as total_work_time FROM tbl_attendance WHERE emp_id='".$emp_id."' AND work_date LIKE '%".date('Y-m',strtotime($atten_year."-".$_REQUEST['month_id']))."%' AND status =  1 ";
                                                $result = $conn->query($SQL);
                                                   
                                                $obj = $result->fetch(PDO::FETCH_OBJ);

                                                $emp_photo =  $dbconn->GetSingleReconrd("mst_employee","emp_photo","rec_del_status = '1' AND emp_id",$_REQUEST['emp_id']);

                                                echo '<hr><div class="col-md-12 text-right" >
                                          
                                                <a href="javascript:" class="rpt_export">
                                                    <button type="button" class="buttons-html5 btn btn-light" ><i class="icon-file-excel mr-1"></i> Excel</button>
                                                </a>
                                                <a href="javascript:" class="rpt_pdf">
                                                    <button type="button" class="buttons-html5 btn btn-light" >
                                                    <i class="icon-file-pdf mr-1"></i> PDF</button>
                                                </a>
                                                <a href="javascript:PrintPartsNew(new Array(\'stock_division\'),\'Day   Book\');" class="rpt_print">
                                                    <button type="button" class="buttons-html5 btn btn-light" ><i class="icon-printer mr-1"></i> Print</button>
                                                </a>
                                            </div>';
                                                echo'
                                                
                                                <div class="col-md-8 offset-md-2 p-2">
                                                    <div id="stock_division">
                                                        <table class="table table-xs invoice_tbl " id="stock_db_table">
                                                        <thead>
                                                            <thead>
                                                            <tr><td colspan="3" style="text-align:center;"><b> Attendance Report for '.$monthName . ' - ' . $atten_year.'</b></td</tr>
                                                            </thead>
                                                                <tbody>
                                                                    <tr >
                                                                        <td rowspan="5" style="width:30%;">';
                                                                            if($emp_photo !=''){
                                                                                echo '<img src="project_img/emp_photo/'.$emp_photo.'" width="100%" hight="100%">';
                                                                            }else{
                                                                                echo'<img src="project_img/emp_photo/usravatar.png" width="100%" hight="100%">';
                                                                            }
                                                                        echo '
                                                                        </td>
                                                                        <td style="width:35%;"><b>Employee Code </b></td>
                                                                        <td style="width:35%;"><b>&nbsp;</b>' . $dbconn->GetSingleReconrd("mst_employee","emp_code","emp_id = '".$emp_id."' AND rec_del_status",1) . '</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><b>Employee Name </b></td>
                                                                        <td><b>&nbsp;</b>' . $dbconn->GetSingleReconrd("mst_employee","emp_name","emp_id = '".$emp_id."' AND rec_del_status",1) . '</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><b>Total Days </b></td>
                                                                        <td><b>&nbsp;</b>' . $dbconn->GetSingleReconrd("tbl_month_master","total_days","year = ".date('Y',strtotime($atten_year."-".$_REQUEST['month_id']))." AND month",date('m',strtotime($atten_year."-".$_REQUEST['month_id']))) . '</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><b>Total Working Days </b></td>
                                                                        <td><b>&nbsp;</b>' . $working_days = $dbconn->GetSingleReconrd("tbl_month_master","working_days","year = '".date('Y',strtotime($atten_year."-".$_REQUEST['month_id']))."' AND month",date('m',strtotime($atten_year."-".$_REQUEST['month_id']))) . '
                                                                    </tr>
                                                                    <tr>
                                                                        <td><b>Present </b></td>
                                                                        <td><b>&nbsp;</b>' . $obj->total_working_days . ' &nbsp; Days
                                                                    </tr>
                                                                        
                                                                </tbody>
                                                        </table>
                                                    </div>
                                                </div>';

                                            }

                                        }
                                        else{
                                            echo'<hr>';
                                            echo'<div style="text-align:center;">
                                            No Record Found
                                            </div>';
                                        }
                                        
                                        ?>
                                     
                                    </div>
                                </div>

                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
            <?php include("inc/common/footer.php") ?>
        </div>

    </div>
    <?php include("modal_employee_atta_det.php") ?>
</body>

<script language="javascript" type="text/javascript">
    <?php
    if (isset($_SESSION['_msg']) && $_SESSION['_msg'] != "") {
        echo "$.jGrowl('" . $_SESSION['_msg'] . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-success', position: 'top-right', life:'2000', header: 'Success!' });";
        $_SESSION['_msg'] = "";
    }
    if (isset($_SESSION['_msg_err']) && $_SESSION['_msg_err'] != "") {
        echo "$.jGrowl('" . $_SESSION['_msg_err'] . "', { sticky: false, theme: 'alert-styled-left alert-arrow-left alert-danger', position: 'top-right', shutdown:'3000', header: 'Error!' });";
        $_SESSION['_msg_err'] = "";
    }
    ?>

    $('#GET_ATTN').click(function() {
        // dataTable.draw();
        // alert();
        var atten_year = $('#atten_year').val();
        var month_id = $('#month_id').val();
        // alert(atten_year);
        if (month_id == '') {
            alert("Please Select the Month... !");
            return false;
        }
    });

    $(function() {



$(".rpt_export").click(function(e) {
    var table = $('#stock_db_table');
    if (table && table.length) {
        //var preserveColors = (table.hasClass('table2excel_with_colors') ? true : false);
        $(table).table2excel({
            // dom: 'Bfrtip',
            exclude: ".noExl",
            name: "AR",
            filename: "AR" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xls",
            fileext: ".xls",
            exclude_img: true,
            // exclude_icon: true,
            exclude_links: true,
            exclude_inputs: true,
            preserveColors: true,
        });
    }
});
$(".rpt_pdf").click(function(e) {
    // alert();
    var element = document.getElementById('stock_division');
    // html2pdf(element);
    var opt = {
        margin: 1,
        filename: '<?php echo "AR" . date("dMY"); ?>' + '.pdf',
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
            orientation: 'p'
            // tableWidth: 'auto'
        }
    };
    // New Promise-based usage:
    html2pdf().set(opt).from(element).save();
});


});
</script>
<!-- Footer -->