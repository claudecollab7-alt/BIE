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

//----------------------------------- SAVE ---------------------------------------//

if (isset($_POST['SAVE'])) {

    $mst_exist = $dbconn->GetSingleReconrd("mst_state", "state_id", "state_status = 1 AND state_name", $_REQUEST['state_name']);
    if ($mst_exist != "") {
        $_SESSION['_msg_err'] = "State Already Exist..!";
        header("location:mst_state.php");
        die();
    }

    $mst_exist1 = $dbconn->GetSingleReconrd("mst_state", "state_id", "state_status = 1 AND state_shname", $_REQUEST['state_shname']);

    if ($mst_exist1 != "") {
        $_SESSION['_msg_err'] = "Short Name Already Exist..!";
        header("location:mst_state.php");
        die();
    }

    $mst_exist2 = $dbconn->GetSingleReconrd("mst_state", "state_id", "state_status = 1 AND state_code", $_REQUEST['state_code']);

    if ($mst_exist2 != "") {
        $_SESSION['_msg_err'] = "State Code Already Exist..!";
        header("location:mst_state.php");
        die();
    }


    try {
        $stmt = null;
        $stmt = $conn->prepare("INSERT INTO mst_state (country_id, state_name, state_shname, state_code) VALUES (:country_id, :state_name, :state_shname, :state_code)");
        $data = array(
            ':country_id' => '1',
            ':state_name' => ucwords($_REQUEST['state_name']),
            ':state_shname' => strtoupper($_REQUEST['state_shname']),
            ':state_code' => $_REQUEST['state_code']
        );
        $stmt->execute($data);
        $_SESSION['_msg'] = "State succesfully saved..!";
    } catch (Exception $e) {
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
    }

    header("location:mst_state.php");
    die();
}


if (isset($_POST['UPDATE'])) {
    $dbconn = new dbhandler();
    $update_id = $_REQUEST['txtHid'];

    $mst_exist = $dbconn->GetSingleReconrd("mst_state", "state_id", "state_id <> " . $update_id . " AND state_status = 1 AND state_name", $_REQUEST['state_name']);

    if ($mst_exist != "") {
        $_SESSION['_msg_err'] = "State Already Exist..!";
        header("location:mst_state.php");
        die();
    }

    $mst_exist1 = $dbconn->GetSingleReconrd("mst_state", "state_id", "state_id <> " . $update_id . " AND state_status = 1 AND state_shname", $_REQUEST['state_shname']);

    if ($mst_exist1 != "") {
        $_SESSION['_msg_err'] = "Short Name Already Exist..!";
        header("location:mst_state.php");
        die();
    }

    $mst_exist2 = $dbconn->GetSingleReconrd("mst_state", "state_id", "state_id <> " . $update_id . " AND state_status = 1 AND state_code", $_REQUEST['state_code']);

    if ($mst_exist2 != "") {
        $_SESSION['_msg_err'] = "State Code Already Exist..!";
        header("location:mst_state.php");
        die();
    }

    try {
        $stmt = null;
        $stmt = $conn->prepare("UPDATE mst_state SET country_id = :country_id, state_name = :state_name, state_shname= :state_shname, state_code = :state_code
					WHERE state_id = :state_id");
        $data = array(
            ':state_id' => $update_id,
            ':country_id' => '1',
            ':state_name' => ucwords($_REQUEST['state_name']),
            ':state_shname' => strtoupper($_REQUEST['state_shname']),
            ':state_code' => $_REQUEST['state_code']
        );

        $stmt->execute($data);
        // echo $stmt->fullQuery;

        $_SESSION['_msg'] = "State succesfully Updated..!";
    } catch (Exception $e) {
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
    }
    header("location:mst_state.php");
    die();
}



$state_id = "";
$state_name = "";
$state_shname = "";
$state_code = "";

if (isset($_REQUEST['state_id'])) {
    $result = $conn->query("SELECT * FROM mst_state WHERE state_status = '1' AND state_id = " . $_REQUEST['state_id']);
    if ($result->rowCount() > 0) {
        $obj = $result->fetch(PDO::FETCH_OBJ);
        $state_id = $obj->state_id;
        $state_name = $obj->state_name;
        $state_shname = $obj->state_shname;
        $state_code = $obj->state_code;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - State</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />

    <?php include_once("inc/common/css-js.php"); ?>
</head>

<script language="javascript" type="text/javascript">
    function fnValidate() {


        if (isNull(document.thisForm.state_name, "State Name..")) {
            return false;
        }
        if (isNull(document.thisForm.state_shname, "Short Name..")) {
            return false;
        }
        if (isNull(document.thisForm.state_code, "State Code..")) {
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


        $('#lst_table').DataTable({
            autoWidth: false,
            processing: false,
            dataRender: true,
            pageLength: 25,
            columnDefs: [{
                orderable: false,
                targets: [4]
            }, ],

            language: {
                search: '&nbsp;&nbsp; _INPUT_',
                searchPlaceholder: 'Type to Search...',
                lengthMenu: ' _MENU_',
                paginate: {
                    'first': 'First',
                    'last': 'Last',
                    'next': $('html').attr('dir') == 'rtl' ? '&larr;' : '&rarr;',
                    'previous': $('html').attr('dir') == 'rtl' ? '&rarr;' : '&larr;'
                }
            },

        });




        $('#state_name').focus();

        $('#lst_table').on('click', 'a.delete', function(e) {
            e.preventDefault();
            var id = $(this).attr('rel');
            var table = "mst_state";
            var status = "state_status";
            var value = "0";
            var where = "state_id";
            var nRow = $(this).parents('tr')[0];
            $.ajax({
                type: 'post',
                url: 'inc/cis_ajax/jquery_delete_records.php',
                data: {
                    "id": id,
                    "table": table,
                    "status": status,
                    "value": value,
                    "where": where
                },
                beforeSend: function() {
                    if (confirm('Are your sure, to Delete this Record..?')) {} else {
                        return false();
                    }
                },
                complete: function() {},
                success: function(result) {
                    // alert(result);
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
                            <a href="#" class="breadcrumb-item">Masters</a>
                            <span class="breadcrumb-item active">State</span>
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
                                <h6 class="card-title">List State</h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-0">

                                <table class="datatable-col10 table table-xs table-hover table-bordered" id="lst_table">
                                    <thead>
                                        <tr class="bg-table-header">
                                            <th>#</th>
                                            <th>State Name</th>
                                            <th class="text-center">Short Name</th>
                                            <th class="text-center">State Code</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <br>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT * FROM mst_state WHERE state_status = '1' ORDER BY state_name ASC";
                                        $result = $conn->query($sql);
                                        $Sno = 1;

                                        if ($result->rowCount() > 0) {
                                            while ($obj = $result->fetch()) {

                                                if ($obj->state_name == 'Super Admin') {
                                                    $del_link = '<a href="javascript:;" title="Delete" ><i class="icon-bin bg-delete mr-2"></i></a>';
                                                } else {

                                                    $del_link = '<a href="javascript:;" class="tip delete" rel="' . $obj->state_id . '"  title="Delete"><i class="icon-bin bg-delete mr-2"></i></a>';
                                                }


                                                echo '<tr>

                                                       <td width=5%>' . $Sno . '</td>
                                                          <td style="font-size:13px;">' . $obj->state_name . '</td>
                                                          <td style="font-size:13px;">' . $obj->state_shname . '</td>
                                                          <td style="font-size:13px;">' . $obj->state_code . '</td>
                                                          <td align="center"><a href="mst_state.php?state_id=' . $obj->state_id . '" class="tip" title="Edit"><i class="fa fa-edit"></i> ' . $del_link . '				

                                                        </tr>';
                                                $Sno++;
                                            }

                                            $obj = null;
                                        } else {
                                            echo '<tr><td colspan="5">No Records...</td></tr>';
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
                                    <h6 class="card-title">New State</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <fieldset>
                                                <div class="form-group row">
                                                    <label class="col-lg-3 col-form-label">State Name <span class="text-mandatory">*</span></label>
                                                    <div class="col-lg-9">
                                                        <input type="text" class="form-control" placeholder="State Name" maxlength="50" name="state_name" id="state_name" value="<?php echo $state_name ?>" onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || (event.charCode == 32)" />
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-lg-3 col-form-label">Short Name <span class="text-mandatory">*</span></label>
                                                    <div class="col-lg-9">
                                                        <input type="text" class="form-control" placeholder="Short Name" maxlength="3" name="state_shname" id="state_shname" value="<?php echo $state_shname ?>" onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || (event.charCode == 32)" />
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-lg-3 col-form-label">State Code <span class="text-mandatory">*</span></label>
                                                    <div class="col-lg-9">
                                                        <input type="text" class="form-control" placeholder="State Code" maxlength="8" name="state_code" id="state_code" onkeypress='return event.charCode >= 48 && event.charCode <= 57' value="<?php echo $state_code ?>" />
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-center">
                                    <?php if (isset($_REQUEST["state_id"])) { ?>
                                        <INPUT class="btn btn-custom mr-2" type="submit" name="UPDATE" value="UPDATE">
                                        <INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:window.location.href='mst_state.php'">
                                        <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['state_id']; ?>">
                                    <?php } else { ?>
                                        <INPUT class="btn btn-custom mr-2" type="submit" name="SAVE" value="SAVE">
                                        <INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="$('#state_name').val('');javascript:history.go(0);" data-toggle="collapse" data-target="#form_div">
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