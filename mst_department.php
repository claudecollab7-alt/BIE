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


if (isset($_POST['SAVE'])) {

    try {
        $is_exist = $dbconn->GetSingleReconrd("mst_department","department_id","(department_name = '" . trim($_REQUEST['department_name']) . "') AND rec_del_status ",1 );

        if ($is_exist != "") {
            $_SESSION['_msg_err'] = "Department Name / Code  already exist..!";
            header("location:mst_department.php");
            die();
        }


        $stmt = null;
        $stmt = $conn->prepare("INSERT INTO mst_department (department_name, created_by, created_dtm) VALUES 
											(:department_name, :created_by, :created_dtm)");
        $data = array(
            ':department_name' => ucwords($_REQUEST['department_name']),
            ':created_by' => $_SESSION['_user_id'],
            ':created_dtm' => date('Y-m-d H:i:s')
        );
        $stmt->execute($data);
        $_SESSION['_msg'] = "Department Succesfully Saved..!";
    } catch (Exception $e) {
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
    }

    header("location:mst_department.php");
    die();
}


if (isset($_POST['UPDATE'])) {

    $update_id = $_REQUEST['txtHid'];

    try {
        $mst_exist = $dbconn->GetSingleReconrd("mst_department", "department_id", "department_id <> " . $update_id . " AND 
					 department_name ='" . trim($_REQUEST['department_name']) . "' AND rec_del_status", 1);

        if ($mst_exist != "") {
            $_SESSION['_msg_err'] = "Department Already Exist..!";
            header("location:mst_department.php");
            die();
        }

        $stmt = null;
        $stmt = $conn->prepare("UPDATE mst_department SET department_name = :department_name,   modify_by = :modify_by, modify_dtm = :modify_dtm
				WHERE department_id = :department_id");
        $data = array(
            ':department_id' => $update_id,
            ':department_name' => ucwords($_REQUEST['department_name']),
            ':modify_by' => $_SESSION['_user_id'],
            ':modify_dtm' => date('Y-m-d H:i:s')
        );

        $stmt->execute($data);

        $_SESSION['_msg'] = "Department succesfully Updated..!";
    } catch (Exception $e) {
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
    }

    header("location:mst_department.php");
    die();
}


if (isset($_REQUEST['id']) && $_REQUEST['id'] != "") {
    $converter = new Encryption;
    $url_data = $converter->decode($_REQUEST['id']);
    $url_data = explode("~", $url_data);

    if ($url_data[1] == $_SESSION['_user_id']) {
        $_REQUEST['department_id'] = $url_data[0];
    } else {
        $_SESSION['_msg_err'] = "You don\'t have permission..!";
        header("location:mst_department.php");
        die();
    }
}

$department_id = "";
$department_name = "";


if (isset($_REQUEST['department_id']) && $_REQUEST['department_id'] != "") {
    $result = $conn->query("SELECT * FROM mst_department WHERE rec_del_status = '1' AND department_id = " . $_REQUEST['department_id']);
    if ($result->rowCount() > 0) {
        $obj = $result->fetch(PDO::FETCH_OBJ);
        $department_id = $obj->department_id;
        $department_name = $obj->department_name;
    }
}



?>
<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - Department</title>
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
                            <a href="#" class="breadcrumb-item"> Masters</a>
                            <span class="breadcrumb-item active">Department</span>
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

                    <!-- This Form UI Starts here --->


                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-pgheader text-white header-elements-inline">
                                <h6 class="card-title">List of Department</h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <table class="datatable-col2 table table-xs table-hover table-bordered" id="DepartmentTable">
                                    <thead>
                                        <tr class="bg-table-header">
                                            <th>Department Name</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php

                                        $sql = "SELECT * FROM mst_department WHERE rec_del_status = 1";
                                        $searchRes1 = $conn->query($sql);
                                        $iSno = 1;

                                        if ($searchRes1->rowCount() > 0) {
                                            while ($rs = $searchRes1->fetch()) {
                                                $converter = new Encryption;
                                                $token = $converter->encode($rs->department_id . '~' . $_SESSION['_user_id']);

                                                //$ref_records = $dbconn->GetSingleReconrd("mst_products","count(*)","prod_Branch",$rs->department_id);
                                                //$ref_records = 0;

                                                echo '<tr>';
                                                echo '<td>' . $rs->department_name . ' </td>';

                                                echo '<td class="text-center">';
                                                echo "<a href='mst_department.php?id=" . $token . "' data-popup='tooltip' title='Edit'>
															<i class='icon-pencil5 bg-edit mr-2'></i></a>";

                                                if ($_SESSION['_user_type'] == 'S' || $_SESSION['_user_type'] == 'A') {
                                                    echo '<a href="javascript:;" class="delete" rel="' . $rs->department_id . '" data-popup="tooltip" title="Delete">
																<i class="icon-bin bg-delete mr-2"></i></a>';
                                                } else {
                                                    echo "<a href='javascript:;' title='Delete'><i class='icon-bin bg-delete mr-2'></i></a>";
                                                }
                                                echo '</td>';
                                                echo '</tr>';
                                                $iSno++;
                                            }
                                        } 
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /basic datatable -->

                    <div class="col-md-6">
                        <form name='DepartmentForm' class="form-horizontal" method='POST' action="" onSubmit="return fnValidate();">

                            <div class="card">
                                <div class="card-header bg-pgheader text-white header-elements-inline">
                                <?php if (($_REQUEST["id"])!="") { ?>
											<h6 class="card-title">Edit Department</h6>
										<?php } else { ?>
											<h6 class="card-title">New Department</h6>
										<?php } ?>
                                </div>

                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <fieldset>
                                                <div class="form-group row">
                                                    <label class="col-lg-5 col-form-label">Department Name <span class="text-mandatory">*</span></label>
                                                    <div class="col-lg-7">
                                                        <input type="text" maxlength="35" class="form-control" id="department_name" name="department_name" maxlength="75" value="<?php echo $department_name; ?>">
                                                    </div>
                                                </div>

                                            </fieldset>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-center">
                                    <?php if (isset($_REQUEST["id"]) && $_REQUEST["department_id"] != '') { ?>
                                        <INPUT class="btn btn-custom" type="submit" name="UPDATE" value="Update">
                                        <INPUT class="btn btn-light" type="button" name="Cancel" value="Cancel" onClick="javascript:window.location.href='mst_department.php'">
                                        <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['department_id']; ?>">
                                    <?php } else { ?>
                                        <INPUT class="btn btn-custom" type="submit" name="SAVE" value="Save">
                                        <INPUT class="btn btn-light" type="button" name="Cancel" value="Cancel" onClick="javascript:window.location.href='mst_department.php'">
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

<script language="javascript" type="text/javascript">
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
        $('#department_name').focus();

        $('#DepartmentTable').on('click', 'a.delete', function(e) {
            e.preventDefault();
            var id = $(this).attr('rel');
            var table = "mst_department";
            var status = "rec_del_status";
            var value = "0";
            var where = "department_id";
            var rec_del_by = "rec_del_by";
            var rec_del_dtm = "rec_del_dtm";
            var nRow = $(this).parents('tr')[0];
            $.ajax({
                type: 'post',
                url: 'inc/cis_ajax/jquery_delete_records_hr.php',
                data: {
                    "id": id,
                    "table": table,
                    "status": status,
                    "value": value,
                    "rec_del_by": rec_del_by,
                    "rec_del_dtm": rec_del_dtm,
                    "where": where
                },
                beforeSend: function() {
                    if (confirm('Are your sure, to Delete this Record..?')) {} else {
                        return false();
                    }
                },
                complete: function() {},
                success: function(result) {
                    location.reload();  
                }
            });
        });
    });

    function fnValidate() {
        //alert("validations..");
        if (isNull(document.DepartmentForm.department_name, "Department Name...!")) {
            document.DepartmentForm.department_name.focus();
            return false;
        }


        document.DepartmentForm.submit();

    }
</script>

</html>