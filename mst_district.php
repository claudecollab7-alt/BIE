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

    $mst_exist = $dbconn->GetSingleReconrd("mst_district", "district_id", "district_status = 1 AND district_name='" . $_REQUEST['district_name'] . "' AND state_id", $_REQUEST['state_name']);

    if ($mst_exist != "") {
        $_SESSION['_msg_err'] = "District Already Exist..!";
        header("location:mst_district.php");
        die();
    }

    try {
        $stmt = null;
        $stmt = $conn->prepare("INSERT INTO mst_district (district_name, state_id, country_id) VALUES (:district_name, :state_id, :country_id)");
        $data = array(
            ':district_name' => ucwords($_REQUEST['district_name']),
            ':state_id' => $_REQUEST['state_name'],
            ':country_id' => '1'
        );
        $stmt->execute($data);
        $_SESSION['_msg'] = "District succesfully saved..!";
    } catch (Exception $e) {
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
    }

    header("location:mst_district.php");
    die();
}

if (isset($_POST['UPDATE'])) {
    $dbconn = new dbhandler();
    $update_id = $_REQUEST['txtHid'];

    $mst_exist = $dbconn->GetSingleReconrd("mst_district", "district_id", "district_status = 1 AND district_name='" . $_REQUEST['district_name'] . "' AND state_id", $_REQUEST['state_name']);
    if ($mst_exist != "") {
        $_SESSION['_msg_err'] = "District Already Exist..!";
        header("location:mst_district.php");
        die();
    }

    try {
        $stmt = null;
        $stmt = $conn->prepare("UPDATE  mst_district SET district_name = :district_name, state_id= :state_id, country_id = :country_id
					WHERE district_id = :district_id");
        $data = array(
            ':district_id' => $update_id,
            ':district_name' => ucwords($_REQUEST['district_name']),
            ':state_id' => $_REQUEST['state_name'],
            ':country_id' => '1'
        );

        $stmt->execute($data);

        $_SESSION['_msg'] = "District succesfully Updated..!";
    } catch (Exception $e) {
        $str = filter_var($e->getMessage(), FILTER_SANITIZE_STRING);
        $_SESSION['_msg_err'] = $str;
    }

    header("location:mst_district.php");
    die();
}

$district_id = "";
$district_name = "";
if (isset($_REQUEST['district_id'])) {
    $result = $conn->query("SELECT * FROM mst_district WHERE district_status = '1' AND district_id = " . $_REQUEST['district_id']);
    if ($result->rowCount() > 0) {
        $obj = $result->fetch(PDO::FETCH_OBJ);
        $district_id = $obj->district_id;
        $district_name = $obj->district_name;
        $state_id = $obj->state_id;
    }
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta type="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo PAGE_TITLE; ?> - District</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />

    <?php include_once("inc/common/css-js.php"); ?>
</head>

<script language="javascript" type="text/javascript">
    function fnValidate() {


        if (isNull(document.thisForm.state_name, "State Name..")) {
            return false;
        }
        if (isNull(document.thisForm.district_name, "District Name..")) {
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
                targets: [3]
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

        // var dataTable = $('#lst_table').DataTable({

        // dom: '<"datatable-header length-left"lp><"datatable-scroll"rt><"datatable-footer"ip>',
        // 'processing': true,
        // "language": {
        //     processing: '<i class="icon-spinner spinner mr-2"></i>Loading...'
        // },
        // 'serverSide': true,
        // 'serverMethod': 'post',
        // 'lengthChange': true, // Remove default Page Length Control
        // 'searching': false, // Remove default Search Control
        // "pageLength": 25,
        // "order": [
        //     [1, "desc"]
        // ],

        // });

        $('#state_name').focus();

        $('#lst_table').on('click', 'a.delete', function(e) {
            e.preventDefault();
            var id = $(this).attr('rel');
            var table = "mst_district";
            var status = "district_status";
            var value = "0";
            var where = "district_id";
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
                            <span class="breadcrumb-item active">District</span>
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
                                <h6 class="card-title">List District</h6>
                                <div class="header-elements">
                                    <div class="list-icons">
                                        <a class="list-icons-item" data-action="fullscreen"></a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <table class=" table table-xs table-hover table-bordered" id="lst_table">
                                    <thead>
                                        <tr class="bg-table-header">
                                            <th>#</th>
                                            <th>State Name</th>
                                            <th>District Name</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <br>
                                    <tbody>
                                        <?php
                                        $SQL = "SELECT district_id, district_name, 	
											state_name 
											FROM mst_district as a
										 		 LEFT JOIN mst_state as b ON 
										 		 a.state_id = b.state_id
										 		 WHERE district_status = '1' ORDER BY state_name, district_name ASC";
                                        $result = $conn->query($SQL);

                                        $Sno = 1;

                                        if ($result->rowCount() > 0) {
                                            while ($obj = $result->fetch()) {

                                                $dbconn = new dbhandler();
                                                $category = $obj->state_name;


                                                if ($obj->district_name == 'Super Admin') {
                                                    $del_link = '<a href="javascript:;" title="Delete" ><i class="icon-bin bg-delete mr-2"></i></a>';
                                                } else {

                                                    $del_link = '<a href="javascript:;" class="tip delete" rel="' . $obj->district_id . '"  title="Delete"><i class="icon-bin bg-delete mr-2"></i></a>';
                                                }
                                                echo '<tr>

                                                       <td width=5%>' . $Sno . '</td>
                                                          <td style="font-size:13px;">' . $category . '</td>
                                                          <td style="font-size:13px;">' . $obj->district_name . '</td>
                                                          <td align="center"><a href="mst_district.php?district_id=' . $obj->district_id . '" class="tip" title="Edit"><i class="fa fa-edit"></i> ' . $del_link . '				

                                                        </tr>';
                                                $Sno++;
                                            }

                                            $obj = null;
                                        } else {
                                            echo '<tr><td colspan="4">No Records...</td></tr>';
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
                                    <h6 class="card-title">New District</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <fieldset>

                                                <div class="form-group row">
                                                    <label class="col-lg-3 col-form-label">State Name <span class="text-mandatory">*</span></label>
                                                    <div class="col-lg-9">
                                                        <select name="state_name" id="state_name" data-placeholder="Choose State.." class="select form-control">
                                                            <option value="">Select State</option>
                                                            <?php
                                                            $dbconn = new dbhandler();
                                                            echo $dbconn->fnFillComboFromTable_Where("state_id", "state_name", "mst_state", "state_id", " WHERE state_status = '1'") ?>

                                                        </select>
                                                        <script>
                                                            document.thisForm.state_name.value = "<?php echo $state_id; ?>";
                                                        </script>
                                                    </div>
                                                </div>


                                                <div class="form-group row">
                                                    <label class="col-lg-3 col-form-label">District Name <span class="text-mandatory">*</span></label>
                                                    <div class="col-lg-9">
                                                        <input type="text" class="form-control" placeholder="District Name" maxlength="50" name="district_name" id="district_name" value="<?php echo $district_name ?>" onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || (event.charCode == 32)" />
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-center">
                                    <?php if (isset($_REQUEST["district_id"])) { ?>
                                        <INPUT class="btn btn-custom mr-2" type="submit" name="UPDATE" value="UPDATE">
                                        <INPUT class="btn btn-light" type="button" name="cancel" value="Cancel" onClick="javascript:window.location.href='mst_district.php'">
                                        <input type="hidden" name="txtHid" id="txtHid" value="<?php echo $_REQUEST['district_id']; ?>">
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