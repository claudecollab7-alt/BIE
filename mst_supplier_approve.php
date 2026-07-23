<?PHP
ob_start();
session_start();
require_once("inc/common/userclass.php");
// require_once ("inc/common/dbrowiterator.php");	

isAdmin();


ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$conn = new dbconnect();
$dbconn = new dbhandler();


if(isset($_POST['APPROVE']))
{
    try
    {
		$_REQUEST['branch'] = $dbconn->GetSingleReconrd("mst_branch", "branch_code", "branch_id='".$_SESSION['_user_branch']."' AND branch_status", 1);

        $stmt = null;               
        $stmt = $conn->prepare("UPDATE  mst_supplier_new SET  supp_approve_status = :supp_approve_status, supp_approve_by = :supp_approve_by, supp_approve_dt = :supp_approve_dt WHERE supp_id = :supp_id");       
        $data = array(              
            ':supp_id' => $_REQUEST['supp_id'],
            ':supp_approve_status' => '1',
            ':supp_approve_by' => $_SESSION['_user_id'],                
            ':supp_approve_dt' => date('Y-m-d H:i:s')    
        );
        
        $stmt->execute($data);
        
        $_SESSION['_msg'] = "Supplier succesfully Approved..!";
    }
    catch (Exception $e)
    {       
        $str= filter_var($e->getMessage(), FILTER_SANITIZE_STRING);         
        $_SESSION['_msg_err'] = $str;           
    }
    
    header("location:lst_supplier.php");    
    die();
}


if (isset($_REQUEST['supp_id']) && $_REQUEST['supp_id'] != "") {

    $result = $conn->query("SELECT * FROM mst_supplier_new WHERE supp_id = " . $_REQUEST['supp_id']);
    if ($result->rowCount() > 0) {
        $obj = $result->fetch(PDO::FETCH_OBJ);
        $supp_id = $obj->supp_id;

        $state_name = $dbconn->GetSingleReconrd("mst_state","state_name","state_status = 1 AND state_id ",$obj->state_id);
        $district = $dbconn->GetSingleReconrd("mst_district","district_name","district_status = 1 AND district_id ",$obj->district_id);
        $city = $dbconn->GetSingleReconrd("mst_city","city_name","city_status = 1 AND city_id ",$obj->city_id);

    }

    $leg_result = $conn->query("SELECT * FROM mst_ledger WHERE ledger_status = '1' AND ledger_id = ".$obj->ledger_id);
    if ($leg_result->rowCount()>0)
    {
        $leg = $leg_result->fetch(PDO::FETCH_OBJ);

        $group_name = $dbconn->GetSingleReconrd("mst_accounts_group","group_name","group_id",$leg->group_id);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Supplier Approval</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <?php include_once("inc/common/css-js.php"); ?>

</head>

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



    });


    function fnValidate() {

        if (confirm('Are you sure to Approve this Supplier..?')) {} else {
            return false;
        }
        document.thisForm.submit();
    }
    function fnValidate1() {

        if (confirm('Are you sure to Reject this Supplier..?')) {} else {
            return false;
        }
        document.thisForm.submit();
    }
</script>

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
                            <span class="breadcrumb-item active">Supplier Approval</span>
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
                        <form name='thisForm' class="form-horizontal" method='POST' action="" onSubmit="">


                            <div class="card">
                                <div class="card-header bg-pgheader text-white header-elements-inline">
                                    <h6 class="card-title">Supplier Approval </h6>
                                    <div class="header-elements">
                                        <div class="list-icons">
                                            <a class="list-icons-item" href="home.php" title="Home"><i class="icon-home2 mr-2"></i></a>
                                            <a class="list-icons-item" href="lst_supplier.php" title="PO List"><i class="icon-arrow-left52 mr-2"></i></a>
                                            <a class="list-icons-item" data-action="fullscreen"></a>

                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12 pdf_page" id="print_content1">
                                        <table class="table table-xs table-bordered">
                                            <tbody>
                                               

                                                <tr>
                                                    <td width="15%"><b>Supplier Code</b></td>
                                                    <td width="35%"><?php echo $obj->supp_code; ?></td>
                                                    <td width="15%"><b>Business Name</b></td>
                                                    <td width="35%"><?php echo $obj->supp_name; ?></td>
                                                </tr>

                                                <tr>
                                                    <td width="15%"><b>Contact Person1</b></td>
                                                    <td width="35%"><?php echo $obj->supp_contact_person1; ?></td>
                                                    <td width="15%"><b>Contact Person2</b></td>
                                                    <td width="35%"><?php echo $obj->supp_contact_person2; ?></td>
                                                </tr>

                                                <tr>
                                                    <td width="15%"><b>Mobile No1</b></td>
                                                    <td width="35%"><?php echo $obj->supp_mobile1; ?></td>
                                                    <td width="15%"><b>Mobile No2</b></td>
                                                    <td width="35%"><?php echo $obj->supp_mobile2; ?></td>
                                                </tr>

                                                <tr>
                                                    <td width="15%"><b>Landline No1</b></td>
                                                    <td width="35%"><?php echo $obj->supp_landline1; ?></td>
                                                    <td width="15%"><b>Landline No2</b></td>
                                                    <td width="35%"><?php echo $obj->supp_landline2; ?></td>
                                                </tr>

                                                <tr>
                                                    <td width="15%"><b>Email Id</b></td>
                                                    <td width="35%"><?php echo $obj->supp_email; ?></td>
                                                    <td width="15%"><b>Website</b></td>
                                                    <td width="35%"><?php echo $obj->supp_website; ?></td>
                                                </tr>

                                                <tr>
                                                    <td width="15%"><b>GST IN.</b></td>
                                                    <td width="35%"><?php echo $obj->supp_gst; ?></td>
                                                    <td width="15%"><b>PAN No.</b></td>
                                                    <td width="35%"><?php echo $obj->supp_pan; ?></td>
                                                </tr>

                                                <tr bgcolor="#e6e6e6">
                                                    <td colspan="4"><strong>Contact Address</strong></td>
                                                </tr>

                                                <tr>
                                                    <td width="15%"><b>Address Line 1</b></td>
                                                    <td width="35%"><?php echo $obj->supp_add1; ?></td>
                                                    <td width="15%"><b>Address Line 2</b></td>
                                                    <td width="35%"><?php echo $obj->supp_add2; ?></td>
                                                </tr>

                                                <tr>
                                                    <td width="15%"><b>State</b></td>
                                                    <td width="35%"><?php echo $state_name; ?></td>
                                                    <td width="15%"><b>District</b></td>
                                                    <td width="35%"><?php echo $district; ?></td>
                                                </tr>

                                                <tr>
                                                    <td width="15%"><b>City</b></td>
                                                    <td width="35%"><?php echo $city; ?></td>
                                                    <td width="15%"><b>Pin Code</b></td>
                                                    <td width="35%"><?php echo $obj->supp_pincode; ?></td>
                                                </tr>

                                                <tr bgcolor="#e6e6e6">
                                                    <td colspan="4"><strong>Accounts Details</strong></td>
                                                </tr>

                                                <tr>
                                                    <td width="15%"><b>Ledger Name</b></td>
                                                    <td width="35%"><?php echo $leg->ledger_name; ?></td>
                                                    <td width="15%"><b>Under Group</b></td>
                                                    <td width="35%"><?php echo $group_name; ?></td>
                                                </tr>

                                                <tr>
                                                    <td width="15%"><b>Credit Days</b></td>
                                                    <td width="35%"><?php echo $obj->supp_credit_days; ?></td>
                                                    <td width="15%"><b>Pay Mode</b></td>
                                                    <td width="35%"><?php echo $obj->supp_pay_mode; ?></td>
                                                </tr>

                                                <tr>
                                                    <td style = "line-height: 20px;" align="left" colspan="4"><b>Opening Balance:</b> <?php echo $leg->open_bal_type.' '.$leg->open_bal; ?></td>
                                                    
                                                </tr>

                                                
                                                <tr bgcolor="#e6e6e6">
                                                    <td colspan="4"><strong>Item Assigned</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table class="table table-xs table-bordered">
                                            <thead>
                                                <tr style = "line-height: 25px;">
                                                    <td width="5%"><b>Sno</b></td>
                                                    <td><b>Product Description</b></td>
                                                    <td><b>Product Code</b></td>
                                                    <td><b>Unit</b></td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                
                                                    $item_sql = "SELECT * FROM tbl_supp_items WHERE supp_id = '".$_REQUEST['supp_id']."'";    
													//echo $item_sql;
                                                    $item_details = $conn->query($item_sql);
                                                    $tr_class = $height  = '';                                    
                                                    if ($item_details->rowCount() > 0)
                                                    {        
                                            
                                                        $iSno=1;
                                                        $netTotal=0;
                                                        while ($enq = $item_details->fetch())
                                                        {   
																//echo $enq->item_id;													
															$item_code = $dbconn->GetSingleReconrd("tbl_item_details","item_code","item_id",$enq->item_id);
															$item_name = $dbconn->GetSingleReconrd("tbl_item_details","item_desciption","item_id",$enq->item_id);
															$uom_id = $dbconn->GetSingleReconrd("tbl_item_details","item_uom","item_id",$enq->item_id);
                                                            
                                                            $uom = $dbconn->GetSingleReconrd("mst_uom","uom_code","uom_id",$uom_id);
                                                            

                                                            echo '<tr '.$tr_class.' valign="top">
                                                                    <td class="align-center">'.$iSno.'</td>
                                                                    <td class="align-left">'.$item_name.'</td>
                                                                    <td class="align-center">'.$item_code.'</td>
                                                                    <td class="align-center">'.$uom.'</td>
                                                                </tr>';
                                                            
                                                                 
                                                            $tr_class = 'class="topborderzero"';        
                                                            $iSno++;
                                                        }
                                                    }
                                                    // if ($item_details->rowCount() > 0)
                                                    // {  
                                                    // $no_items = $iSno;
                                                    // $items_height = $no_items * 100;
                                                    // // if($items_height < 500){
                                                    // //     $height = 500 - $items_height;
                                                    // // }else{
                                                    // //     $height = 10;
                                                    // // }
                                                    // //$height = 200;
                                                    // echo '<tr '.$tr_class.' valign="top">
                                                    //         <td colspan><p style="min-height:'.$height.'px;">&nbsp;</p></td>
                                                    //         <td>&nbsp;</td>
                                                    //         <td>&nbsp;</td>
                                                    //         <td>&nbsp;</td>
                                                    //     </tr>';
                                                    
                                                    
                                                    // }
                                                ?>
                                            </tbody>
                                        </table>
                                        </div>
                                    </div>
                                    
                                </div>
                                <div class="card-footer text-center">
                                        <?php if($obj->supp_approve_status != 1){ ?>
                                        <input type="hidden" name="supp_id" id="supp_id" value="<?php echo $_REQUEST['supp_id']; ?>" />
                                        <INPUT class="btn btn-custom" type="submit" id="APPROVE" name="APPROVE" value="Approve" onclick = "return fnValidate();">
                                        <INPUT class="btn btn-danger" type="submit" id="REJECT" name="REJECT" value="Reject" onclick = "return fnValidate1();">
                                        <?php } ?>
                                    </div>
                            </div>
                        </form>

                        <!-- End of This Form UI  --->
                    </div>
                </div>
                <!-- /dashboard content -->
            </div>
            <?php include("inc/common/footer.php") ?>
        </div>
    </div>
    
</body>

</html>