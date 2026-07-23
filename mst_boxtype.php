<?php
ob_start();
session_start();

require_once("inc/common/userclass.php");
//  require_once("inc/common/css-js.php");

isAdmin();

$conn = new dbconnect();
$dbconn = new dbhandler();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

//-----------------------------------SAVE BOX TYPE---------------------------------------//


if (isset($_POST['SAVE'])) {

    $mst_exist = $dbconn->GetSingleReconrd("tbl_dc_package_box", "box_id", "box_status = 1 AND box_name", $_REQUEST['box_name']);

    if ($mst_exist != "") {
        $_SESSION['_msg_err'] = "Box Type Already Exist..!";
        header("location:mst_boxtype.php");
        die();
    }

    try {

        $stmt = null;

        $stmt = $conn->prepare("INSERT INTO tbl_dc_package_box (box_name) VALUES (:box_name)");
        $data = array(':box_name' => ucwords($_REQUEST['box_name']));

        $stmt->execute($data);
        $_SESSION['_msg'] = "Box Type succesfully saved..!";
    } catch (Exception $e) {

        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
    }

    header("location:mst_boxtype.php");
    die();
}

//-----------------------------------UPDATE BOX TYPE---------------------------------------//


if (isset($_POST['UPDATE'])) {
    $update_id = $_REQUEST['txtHid'];
    $mst_exist = $dbconn->GetSingleReconrd("tbl_dc_package_box", "box_id", "box_id <> " . $update_id . " AND box_status = 1 AND box_name", $_REQUEST['box_name']);

    if ($mst_exist != "") {
        $_SESSION['_msg_err'] = "Box Type Already Exist..!";
        header("location:mst_boxtype.php");
        die();
    }

    try {

        $stmt = null;
        $stmt = $conn->prepare("UPDATE  tbl_dc_package_box SET box_name = :box_name WHERE box_id = :box_id");
        $data = array(
            ':box_id' => $update_id,
            ':box_name' => ucwords($_REQUEST['box_name'])
        );

        $stmt->execute($data);

        $_SESSION['_msg'] = "Box Type succesfully Updated..!";
    } catch (Exception $e) {

        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
    }

    header("location:mst_boxtype.php");
    die();
}


//-----------------------------------UPDATE BOX TYPE---------------------------------------//

$box_id = "";
$boxs_name = "";
if (isset($_REQUEST['box_id'])) {

    $result = $conn->query("SELECT * FROM tbl_dc_package_box WHERE box_status = '1' AND box_id = " . $_REQUEST['box_id']);
    if ($result->rowCount() > 0) {
        $get = $result->fetch(PDO::FETCH_OBJ);
        $box_id = $get->box_id;
        $boxs_name = $get->box_name;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo PAGE_TITLE; ?> - Box Type</title>
	<link rel="icon" href="favicon.ico" type="image/x-icon" />

	 <?php include_once("inc/common/css-js.php"); ?>
</head>

<script language="javascript" type="text/javascript">
    function fnValidate() {
        if (isNull(document.thisForm.box_name, "Box Type..")) {
            return false;
        }
        document.thisForm.submit();
    }


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

    $('#box_name').focus();
        
        $('#lst_table').on('click', 'a.delete', function (e) {
            e.preventDefault();
            var id = $(this).attr('rel');
            var table = "tbl_dc_package_box";
            var status = "box_status";
            var value = "0";
            var where = "box_id";				
            var nRow = $(this).parents('tr')[0];			
                $.ajax({
                    type:'post',
                    url:'inc/cis_ajax/jquery_delete_records.php',
                    data: {"id":id,"table":table,"status":status,"value":value,"where":where},
                    beforeSend:function(){
                        if (confirm('Are your sure, to Delete this Record..?')) {
                        } else {
                        return false();
                        }
                    },
                    complete:function(){
                    },
                    success:function(result){
                        location.reload();
                            //$('#hsnTable').DataTable().row(nRow).remove().draw();
                    }
                });	
        });	

    });
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
                            <a href="#" class="breadcrumb-item"> WorkArea</a>
                            <span class="breadcrumb-item active">New Box Type</span>
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


                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-pgheader text-white header-elements-inline">
                                <h6 class="card-title">List Box</h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-0">

                                <table class="datatable-col6 table table-xs table-hover table-bordered lst_table " id="lst_table">
                                    <thead>
                                        <tr style="font-size:14px;" class="bg-table-header">
                                            <th>#</th>
                                            <th>Box Type Name</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <br>

                                    <tbody>

                                        <?php

                                        $sql = "SELECT * FROM tbl_dc_package_box WHERE box_status = '1' ORDER BY box_name ASC";
                                        $result = $conn->query($sql);
                                        $Sno = 1;

                                        if ($result->rowCount() > 0) {

                                            while ($obj = $result->fetch()) {

                                                if ($obj->box_name == 'Super Admin') {
                                                    $del_link = '<a href="javascript:;" title="Delete" ><i class="icon-bin bg-delete mr-2"></i></a>';
                                                } else {

                                                    $del_link = '<a href="javascript:;" class="tip delete" rel="' . $obj->box_id . '"  title="Delete"><i class="icon-bin bg-delete mr-2"></i></a>';
                                                }
                                                // $edit_link = '<a href="mst_boxtype.php?box_id=' . $obj->box_id . '" class="tip" title="Edit"><i class="icon-pencil5 bg-edit-disabled mr-2"></i></a>';

                                                echo '<tr>


													            <td>' . $Sno . '</td>
													            <td style="font-size:13px;">' . $obj->box_name . '</td>

                                                            <td align="center"><a href="mst_boxtype.php?box_id=' . $obj->box_id . '" class="tip" title="Edit"><i class="fa fa-edit"></i> ' . $del_link . '				
                                                               
															

												        </tr>';
                                                $Sno++;
                                            }

                                            $obj = null;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <form name='thisForm' id="validate" class="form-horizontal" method='post' action="" onSubmit="return fnValidate();">

                            <div class="card">
                                <div class="card-header bg-pgheader text-white header-elements-inline">
                                    <h6 class="card-title">New Box Type</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <fieldset>
                                                <div class="form-group row">
                                                    <label class="col-lg-3 col-form-label">Box Type <span class="text-mandatory">*</span></label>
                                                    <div class="col-lg-9">
                                                        <input type="text" class="form-control" placeholder="Box Type" maxlength="50" name="box_name" id="box_name" value="<?php echo $boxs_name ?>" />
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-center">
                                    <?php if (isset($_REQUEST["box_id"])) { ?>
                                        <INPUT class="btn btn-custom mr-2" type="submit" name="UPDATE" value="UPDATE">
                                        <INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:window.location.href='mst_boxtype.php'">
                                        <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['box_id']; ?>">
                                    <?php } else { ?>
                                        <INPUT class="btn btn-custom mr-2" type="submit" name="SAVE" value="SAVE">
                                        <INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="$('#box_name').val('');javascript:history.go(0);" data-toggle="collapse" data-target="#form_div">
                                        <input type="hidden" name="txtHid" value="0">
                                    <?php } ?>
                                </div>

                            </div>
                        </form>
                    </div>
                    <!-- End of This Form UI  --->
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


